/* Audit presentation only; no requests or changes to persisted records. */
(function () {
    'use strict';
    const present = value => value !== null && value !== undefined && value !== '';
    const normalize = key => String(key).replace(/([a-z0-9])([A-Z])/g, '$1_$2').replace(/[\s.-]+/g, '_').toLowerCase();
    const sensitive = key => /password|passwd|passphrase|token|secret|cookie|authorization|credential|api_?key|private_?key|headers?|csrf|remember/i.test(normalize(key));
    const labels = { student_label: 'Student', student_name: 'Student', roll_no: 'Student ID', student_roll_no: 'Student ID', book_name: 'Book', book_title: 'Book', action_type: 'Action', amount_change: 'Amount Changed', status: 'Status', isbn: 'ISBN', ip_address: 'IP Address', url: 'URL', user_agent: 'User Agent', request_method: 'Request Method' };
    function label(key) {
        return labels[key] || normalize(key).split('_').filter(Boolean).map(word => ['id', 'isbn', 'ip', 'url'].includes(word) ? word.toUpperCase() : word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    }
    function safeUrl(value, sameOrigin = false) {
        try {
            const url = new URL(String(value), window.location.href);
            if (!['http:', 'https:'].includes(url.protocol) || url.username || url.password || (sameOrigin && url.origin !== window.location.origin)) return null;
            for (const key of [...url.searchParams.keys()]) if (sensitive(key)) url.searchParams.delete(key);
            url.hash = '';
            return url.href;
        } catch { return null; }
    }
    function clean(value, depth = 0) {
        if (depth > 8) return 'Further nested details omitted';
        if (typeof value === 'string') {
            const trimmed = value.trim();
            if (/^[{[]/.test(trimmed)) {
                try { return clean(JSON.parse(trimmed), depth + 1); } catch { /* ordinary text */ }
            }
            return value.replace(/((?:password|token|secret|api_key|authorization|cookie)=)[^\s&#]+/gi, '$1[redacted]');
        }
        if (Array.isArray(value)) return value.map(item => clean(item, depth + 1));
        if (value && typeof value === 'object') {
            return Object.fromEntries(Object.entries(value).filter(([key]) => !sensitive(key)).map(([key, item]) => [normalize(key), clean(item, depth + 1)]));
        }
        return value;
    }
    function text(value) {
        if (value === null || value === undefined) return 'Not recorded';
        if (typeof value === 'boolean') return value ? 'Yes' : 'No';
        if (Array.isArray(value)) return value.length ? value.map(text).join('; ') : 'None';
        if (typeof value === 'object') return Object.entries(value).map(([key, item]) => `${label(key)}: ${text(item)}`).join('\n') || 'None';
        return String(value);
    }
    function numeric(value) { return present(value) && ['string', 'number'].includes(typeof value) && String(value).trim() !== '' && Number.isFinite(Number(value)); }
    function money(value) { return numeric(value) ? `₹${Number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : text(value); }
    function friendly(value) { return typeof value === 'string' && /^[a-z]+(?:[_-][a-z]+)*$/.test(value) ? label(value) : text(value); }
    const technicalKeys = new Set(['ip_address', 'ip', 'device_type', 'device', 'browser', 'session_id', 'user_agent', 'route', 'url', 'request_url', 'request_method', 'method']);
    const businessKeys = new Set(['days_late', 'fine_status', 'status', 'action', 'action_type', 'issued_book_id', 'fine_id', 'book_id', 'student_id', 'transaction_id', 'accession_number', 'accession_no', 'issue_date', 'due_date', 'return_date', 'condition', 'return_condition', 'fine_generated', 'fine_amount', 'issued_by', 'processed_by', 'payment_method', 'paid_date', 'waived_date', 'setting', 'privilege', 'renewal_count']);
    function model(log) {
        const cleaned = clean(log.metadata || {});
        const metadata = cleaned && typeof cleaned === 'object' && !Array.isArray(cleaned) ? cleaned : { details: cleaned };
        const used = new Set();
        const take = (...keys) => {
            const key = keys.find(key => present(metadata[key]));
            if (!key) return undefined;
            // Collapse aliases only when they represent the same value.
            keys.forEach(alias => { if (!present(metadata[alias]) || text(metadata[alias]) === text(metadata[key])) used.add(alias); });
            return metadata[key];
        };
        let title = log.title || label(log.event_type || log.type || 'Activity recorded');
        const action = metadata.action_type ?? metadata.action;
        const fine = /fine/i.test(`${log.type} ${title} ${log.resourceType}`);
        if (fine && /^(adjust|adjusted|fine_adjusted)$/i.test(String(action))) title = 'Fine Adjusted';
        const book = take('book_title', 'book_name', 'book');
        const student = take('student_label', 'student_name', 'student');
        const studentId = take('student_roll_no', 'roll_no', 'roll_number');
        if (present(student)) {
            for (const key of ['student_name', 'student_label']) {
                if (present(metadata[key]) && String(student).includes(String(metadata[key]))) used.add(key);
            }
        }
        const isbn = take('isbn', 'book_isbn');
        used.add('isbn'); used.add('book_isbn');
        const accession = take('accession_number', 'accession_no', 'book_accession_number') ?? log.accessionNumber;
        // Only replace the identifier in the modal; the recorded description stays intact.
        const description = String(log.description || 'No description recorded.').replace(/\s*\(ISBN:\s*[^)]*\)/gi,
            () => present(accession) ? ` (Accession Number: ${text(accession)})` : '');
        const remarks = take('remarks', 'reason', 'remark', 'notes');
        const changes = [];
        let summary = '';
        if (present(metadata.old_amount) && present(metadata.new_amount)) {
            used.add('old_amount'); used.add('new_amount');
            const deltaKey = ['amount_change', 'amount_difference', 'difference'].find(key => numeric(metadata[key]));
            const delta = deltaKey ? Number(metadata[deltaKey]) : numeric(metadata.old_amount) && numeric(metadata.new_amount) ? Math.round((Number(metadata.new_amount) - Number(metadata.old_amount)) * 100) / 100 : null;
            if (deltaKey) used.add(deltaKey);
            changes.push({ name: fine ? 'Fine Amount' : 'Amount', old: money(metadata.old_amount), next: money(metadata.new_amount), delta });
            if (numeric(metadata.amount) && numeric(metadata.new_amount) && Number(metadata.amount) === Number(metadata.new_amount)) used.add('amount');
            if (numeric(metadata.fine_amount) && numeric(metadata.new_amount) && Number(metadata.fine_amount) === Number(metadata.new_amount)) used.add('fine_amount');
            if (fine) summary = `The fine${present(book) ? ` for “${text(book)}”` : ''} was changed from ${money(metadata.old_amount)} to ${money(metadata.new_amount)}.`;
        } else if (fine && present(metadata.amount)) {
            used.add('amount');
            changes.push({ name: 'Fine Amount', next: money(metadata.amount) });
        }
        for (const [oldKey, newKey, name] of [['old_value', 'new_value', friendly(metadata.setting || metadata.privilege || 'Value')], ['old_status', 'new_status', 'Account Status'], ['old_role', 'new_role', 'User Role']]) {
            if (present(metadata[oldKey]) || present(metadata[newKey])) {
                used.add(oldKey); used.add(newKey);
                changes.push({ name, old: present(metadata[oldKey]) ? friendly(metadata[oldKey]) : 'Not recorded', next: present(metadata[newKey]) ? friendly(metadata[newKey]) : 'Not recorded' });
            }
        }
        if (metadata.changes && typeof metadata.changes === 'object' && !Array.isArray(metadata.changes)) {
            const remaining = {};
            for (const [key, value] of Object.entries(metadata.changes)) {
                if (value && typeof value === 'object' && ('old' in value || 'new' in value || 'from' in value || 'to' in value)) {
                    changes.push({ name: label(key), old: text(value.old ?? value.from), next: text(value.new ?? value.to) });
                } else if (/privilege/i.test(`${log.type} ${title}`) && (value === null || typeof value !== 'object')) {
                    changes.push({ name: label(key), next: text(value) });
                } else remaining[key] = value;
            }
            if (!Object.keys(remaining).length) used.add('changes');
            else metadata.changes = remaining;
            if (Array.isArray(metadata.changed_fields) && metadata.changed_fields.every(key => changes.some(change => change.name === label(key)))) used.add('changed_fields');
            if (Array.isArray(metadata.updated_fields) && metadata.updated_fields.every(key => changes.some(change => change.name === label(key)))) used.add('updated_fields');
        }
        const resourceType = log.resourceType || 'Resource';
        const resourceId = present(log.resourceId) && !['N/A', 'Not captured'].includes(String(log.resourceId)) ? log.resourceId : null;
        const resourceName = `${resourceType}${present(resourceId) ? ` #${resourceId}` : ''}`;
        const resourceKey = normalize(resourceType) + '_id';
        if (present(resourceId) && text(metadata[resourceKey]) === String(resourceId)) used.add(resourceKey);
        const resource = [];
        if (present(book)) resource.push({ label: 'Book', value: text(book) });
        if (present(accession) || present(book) || present(isbn)) resource.push({ label: 'Accession Number', value: present(accession) ? text(accession) : 'Not recorded', copy: present(accession) });
        if (present(student)) resource.push({ label: 'Student', value: text(student) });
        if (present(studentId) && !String(student || '').includes(String(studentId))) resource.push({ label: 'Student ID', value: text(studentId) });
        const technical = [];
        const technicalSeen = new Set();
        function addTechnical(key, value) {
            if (!present(value) || /^(Not captured|Unknown device|Unknown browser|N\/A)$/i.test(String(value))) return;
            const canonical = { ip: 'ip_address', device: 'device_type', request_url: 'url', method: 'request_method' }[key] || key;
            const identity = `${canonical}:${text(value)}`;
            if (technicalSeen.has(identity)) return;
            technicalSeen.add(identity);
            technical.push({ label: label(canonical), value: canonical === 'url' ? safeUrl(value) || 'Unavailable URL' : text(value), copy: ['session_id', 'ip_address'].includes(canonical), session: canonical === 'session_id' });
        }
        for (const [key, value] of Object.entries({ ip_address: log.ipAddress, device_type: log.deviceType, browser: log.browser, session_id: log.sessionId, user_agent: log.userAgent, url: log.requestUrl, request_method: log.requestMethod })) addTechnical(key, value);
        const business = [], additional = [], seen = new Set(resource.map(row => `${row.label}:${row.value}`));
        for (const [key, value] of Object.entries(metadata)) {
            if (used.has(key)) continue;
            if (technicalKeys.has(key)) { addTechnical(key, value); continue; }
            if (['session', 'request', 'technical'].includes(key) && value && typeof value === 'object') {
                for (const [nestedKey, nestedValue] of Object.entries(value)) {
                    const mappedKey = key === 'session' && nestedKey === 'id' ? 'session_id' : nestedKey;
                    if (technicalKeys.has(mappedKey)) addTechnical(mappedKey, nestedValue);
                }
                continue;
            }
            if (sensitive(key)) continue;
            if (['actor_name', 'performed_by'].includes(key) && text(value) === String(log.userName)) continue;
            if (['user_role', 'actor_role'].includes(key) && String(value).toLowerCase() === String(log.userRole).toLowerCase()) continue;
            const name = key === 'status' && fine ? 'Fine Status' : label(key);
            const formatted = ['action', 'action_type'].includes(key) && /^(adjust|adjusted|fine_adjusted)$/i.test(String(value)) ? 'Adjusted' : /(?:^|_)amount$|^amount_change$|^fine_generated$/.test(key) && numeric(value) ? money(value) : businessKeys.has(key) ? friendly(value) : text(value);
            const identity = `${name}:${formatted}`;
            if (seen.has(identity)) continue;
            seen.add(identity);
            const row = { label: name, value: formatted, copy: ['fine_id', 'transaction_id', 'isbn'].includes(key) && present(value) };
            (businessKeys.has(key) || key === 'amount' ? business : additional).push(row);
        }
        const rawSeverity = String(log.status || 'info').toLowerCase();
        const severity = ['failed', 'error', 'critical', 'denied', 'rejected'].includes(rawSeverity) ? 'error' : ['warning', 'pending', 'partial'].includes(rawSeverity) ? 'warning' : ['success', 'completed'].includes(rawSeverity) ? 'success' : 'info';
        return { title, summary, description, changes, resourceName, resource, business, additional, technical, remarks: present(remarks) ? text(remarks) : 'No remarks recorded.', severity };
    }
    const byId = id => document.getElementById(id);
    function node(tag, className, content) {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (content !== undefined) element.textContent = content;
        return element;
    }
    function fields(id, rows) {
        const container = byId(id);
        container.replaceChildren();
        for (const row of rows) {
            const wrapper = node('div');
            const value = node('dd', row.copy ? 'audit-technical-value' : '');
            const content = node('span', row.session ? 'audit-session' : '', row.value);
            if (row.session) content.title = row.value;
            value.append(content);
            if (row.copy) {
                const button = node('button', 'audit-copy', 'Copy');
                button.type = 'button'; button.title = `Copy ${row.label}`; button.setAttribute('aria-label', button.title);
                button.addEventListener('click', async () => {
                    try {
                        if (navigator.clipboard?.writeText && window.isSecureContext) await navigator.clipboard.writeText(row.value);
                        else {
                            const input = node('textarea', 'audit-sr-only'); input.value = row.value; container.append(input); input.select();
                            let copied;
                            try { copied = document.execCommand('copy'); } finally { input.remove(); button.focus(); }
                            if (!copied) throw new Error('Copy failed');
                        }
                        button.textContent = 'Copied'; byId('auditCopyFeedback').textContent = `${row.label} copied.`;
                        setTimeout(() => { if (button.isConnected) button.textContent = 'Copy'; }, 2000);
                    } catch { byId('auditCopyFeedback').textContent = `Unable to copy ${row.label}. Select and copy the value manually.`; }
                });
                value.append(button);
            }
            wrapper.append(node('dt', '', row.label), value); container.append(wrapper);
        }
    }
    let previousFocus = null;
    function open(log, trigger) {
        const overlay = byId('activityDetailsOverlay');
        const data = model(log);
        previousFocus = trigger || document.activeElement;
        for (const disclosure of overlay.querySelectorAll('details')) disclosure.open = false;
        byId('auditCopyFeedback').textContent = '';
        byId('auditEventTitle').textContent = data.title;
        byId('auditEventSummary').textContent = data.summary;
        byId('auditEventSummary').hidden = !data.summary;
        byId('auditEventTimestamp').textContent = log.fullTimestamp || log.time || 'Time not recorded';
        byId('auditEventSeverity').textContent = { error: 'Error / Critical', warning: 'Warning', success: 'Success', info: 'Info' }[data.severity];
        byId('auditEventSeverity').dataset.severity = data.severity;
        // The audit center supplies the same server-resolved tokens used by its rows and analytics.
        const summary = overlay.querySelector('.audit-summary');
        const category = byId('auditEventCategory');
        summary.className = 'audit-summary';
        if (log.eventStyle) {
            summary.classList.add(log.eventStyle.class);
            byId('auditEventSeverity').textContent = log.eventStyle.severityLabel;
            byId('auditEventSeverity').className = 'audit-severity audit-badge';
            if (category) {
                category.className = `audit-modal-category audit-badge ${log.eventStyle.categoryClass}`;
                category.textContent = log.eventStyle.categoryLabel;
                category.hidden = false;
            }
        } else {
            byId('auditEventSeverity').className = 'audit-severity';
            if (category) category.hidden = true;
        }
        byId('auditResourceName').textContent = data.resourceName;
        fields('auditResourceFields', data.resource);
        byId('auditActorName').textContent = log.userName || 'System';
        byId('auditActorRole').textContent = friendly(log.userRole || 'system');
        byId('auditActorRole').className = log.roleClass ? `audit-modal-role ${log.roleClass}` : '';
        byId('auditActorInitials').textContent = String(log.userName || 'System').trim().split(/\s+/).slice(0, 2).map(part => part[0]?.toUpperCase() || '').join('');
        byId('auditDescription').textContent = data.description;
        byId('auditRemarks').textContent = data.remarks;
        for (const [section, container, rows] of [['auditBusinessSection', 'auditBusinessFields', data.business], ['auditAdditionalSection', 'auditAdditionalFields', data.additional], ['auditTechnicalSection', 'auditTechnicalFields', data.technical]]) {
            fields(container, rows); byId(section).hidden = !rows.length;
        }
        byId('auditChanges').replaceChildren();
        byId('auditChangeSection').hidden = !data.changes.length;
        for (const change of data.changes) {
            const row = node('div', 'audit-change'); row.append(node('span', 'audit-change-name', change.name));
            if (present(change.old)) {
                const old = node('span', 'audit-change-value audit-change-old', change.old); old.setAttribute('aria-label', `Old value: ${change.old}`);
                const arrow = node('span', 'audit-change-arrow', '→'); arrow.setAttribute('aria-hidden', 'true'); row.append(old, arrow);
            }
            const next = node('span', 'audit-change-value', change.next); next.setAttribute('aria-label', `${present(change.old) ? 'New value' : change.name}: ${change.next}`); row.append(next);
            if (change.delta !== null && change.delta !== undefined) {
                const difference = node('span', 'audit-difference', `Change: ${change.delta > 0 ? '+' : change.delta < 0 ? '−' : ''}${money(Math.abs(change.delta))}`);
                difference.dataset.direction = change.delta < 0 ? 'decrease' : change.delta > 0 ? 'increase' : 'same'; row.append(difference);
            }
            byId('auditChanges').append(row);
        }
        const link = byId('activityDetailsResourceLink');
        const url = log.resourceAvailable === true ? safeUrl(log.resourceUrl, true) : null;
        link.hidden = !url; link.removeAttribute('href'); if (url) link.href = url;
        overlay.classList.add('show'); overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('audit-event-details-open');
        overlay.querySelector('.activity-modal-body').scrollTop = 0;
        byId('closeActivityDetailsBtn').focus({ preventScroll: true });
    }
    function close() {
        const overlay = byId('activityDetailsOverlay');
        if (!overlay?.classList.contains('show')) return;
        overlay.classList.remove('show'); overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('audit-event-details-open');
        if (previousFocus?.isConnected) previousFocus.focus({ preventScroll: true });
        previousFocus = null;
    }
    window.LMSAuditDetails = { open, close, model };
}());
