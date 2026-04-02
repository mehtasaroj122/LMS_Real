(function () {
    function buildReportQuery(filters) {
        const params = new URLSearchParams();
        const source = filters || {};

        params.set('report_type', source.reportType || 'inventory');
        params.set('time_period', source.timePeriod || '30days');

        if (source.timePeriod === 'custom') {
            params.set('start_date', source.startDateInput || '');
            params.set('end_date', source.endDateInput || '');
        }

        return params;
    }

    async function fetchReportData(endpoint, filters, options) {
        const settings = options || {};
        const params = buildReportQuery(filters);
        const separator = String(endpoint || '').includes('?') ? '&' : '?';
        const response = await fetch(`${endpoint}${separator}${params.toString()}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: settings.signal,
        });

        let payload = null;

        try {
            payload = await response.json();
        } catch (error) {
            payload = null;
        }

        if (!response.ok) {
            const message = payload?.message || `Request failed with status ${response.status}`;
            throw new Error(message);
        }

        return payload;
    }

    window.AdminReportApi = {
        buildReportQuery,
        fetchReportData,
    };
})();
