{{-- Presentation only: shared audit payload renderer. --}}
<div class="activity-modal-overlay" id="activityDetailsOverlay" aria-hidden="true">
    <div class="activity-modal" role="dialog" aria-modal="true" aria-labelledby="activityDetailsTitle" aria-describedby="activityDetailsSubtitle" tabindex="-1">
        <header class="activity-modal-header">
            <div>
                <h2 id="activityDetailsTitle">Audit Event Details</h2>
                <p id="activityDetailsSubtitle">Activity, changes and technical audit information</p>
            </div>
            <button type="button" class="activity-modal-close admin-ui-button admin-ui-neutral admin-ui-icon-button" id="closeActivityDetailsBtn" aria-label="Close">&times;</button>
        </header>
        <div class="activity-modal-body" tabindex="0" aria-label="Audit event information">
            <section class="audit-summary" aria-labelledby="auditEventTitle">
                <div class="audit-summary-heading"><h3 id="auditEventTitle"></h3><span id="auditEventSeverity" class="audit-severity"></span></div>
                <span id="auditEventCategory" class="audit-modal-category" hidden></span>
                <p id="auditEventSummary" hidden></p>
                <time id="auditEventTimestamp"></time>
            </section>
            <section class="audit-section audit-change-section" id="auditChangeSection" aria-labelledby="auditChangeHeading" hidden>
                <h3 id="auditChangeHeading">Change Summary</h3>
                <div id="auditChanges"></div>
            </section>
            <div class="audit-context">
                <section class="audit-section" aria-labelledby="auditResourceHeading">
                    <h3 id="auditResourceHeading">Affected Resource</h3>
                    <strong id="auditResourceName"></strong>
                    <dl class="audit-resource-fields" id="auditResourceFields"></dl>
                </section>
                <section class="audit-section" aria-labelledby="auditActorHeading">
                    <h3 id="auditActorHeading">Performed By</h3>
                    <div class="audit-actor"><span class="audit-avatar" id="auditActorInitials" aria-hidden="true"></span><div><strong id="auditActorName"></strong><span id="auditActorRole"></span></div></div>
                </section>
            </div>
            <div class="audit-narrative">
                <section class="audit-section" aria-labelledby="auditDescriptionHeading">
                    <h3 id="auditDescriptionHeading">Description</h3>
                    <p class="audit-description" id="auditDescription"></p>
                </section>
                <section class="audit-section" aria-labelledby="auditRemarksHeading">
                    <h3 id="auditRemarksHeading">Reason / Remarks</h3>
                    <div class="audit-note" id="auditRemarks"></div>
                </section>
            </div>
            <section class="audit-section" id="auditBusinessSection" aria-labelledby="auditBusinessHeading" hidden>
                <h3 id="auditBusinessHeading">Details</h3>
                <dl class="audit-fields" id="auditBusinessFields"></dl>
            </section>
            <details class="audit-section audit-disclosure" id="auditAdditionalSection" hidden>
                <summary>Additional Details</summary>
                <dl class="audit-fields" id="auditAdditionalFields"></dl>
            </details>
            <details class="audit-section audit-disclosure" id="auditTechnicalSection" hidden>
                <summary><span class="audit-show-technical">Show Technical Details</span><span class="audit-hide-technical">Hide Technical Details</span></summary>
                <dl class="audit-fields" id="auditTechnicalFields"></dl>
            </details>
            <span id="auditCopyFeedback" class="audit-sr-only" role="status" aria-live="polite"></span>
        </div>
        <footer class="activity-modal-actions">
            <a class="admin-ui-button admin-ui-secondary" id="activityDetailsResourceLink" target="_blank" rel="noopener" hidden>View Resource <i class="fas fa-external-link-alt" aria-hidden="true"></i></a>
            <button type="button" class="admin-ui-button admin-ui-primary" onclick="closeActivityDetails()">Close</button>
        </footer>
    </div>
</div>
