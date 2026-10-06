{{-- Shared read-only transaction modal for Admin and Staff Student Details pages. --}}
<div class="book-details-overlay" id="bookDetailsOverlay" aria-hidden="true">
    <div class="book-details-modal" role="dialog" aria-modal="true" aria-labelledby="issuedBookDetailsHeading" aria-describedby="issuedBookDetailsSubtitle" tabindex="-1">
        <header class="book-details-header">
            <div>
                <h2 id="issuedBookDetailsHeading">Issued Book Details</h2>
                <p id="issuedBookDetailsSubtitle">Borrowing and transaction information</p>
            </div>
            <button type="button" class="book-details-close admin-ui-button admin-ui-neutral admin-ui-icon-button" id="closeBookDetailsBtn" aria-label="Close">&times;</button>
        </header>
        <div class="book-details-body" tabindex="0" aria-label="Transaction information">
            <section class="issued-book-overview" aria-labelledby="bookDetailsTitle">
                <div id="bookCoverContent" class="issued-book-cover"></div>
                <div class="issued-book-identity">
                    <h3 id="bookDetailsTitle"></h3>
                    <p id="bookDetailsAuthor"></p>
                    <span class="issued-book-category" id="bookDetailsCategory"></span>
                    <dl class="issued-book-identifiers">
                        <div><dt>ISBN</dt><dd id="bookDetailsISBN"></dd></div>
                        <div><dt>Accession No.</dt><dd id="bookDetailsAccession"></dd></div>
                    </dl>
                </div>
            </section>

            <dl class="issued-book-status-strip" aria-label="Borrowing status summary">
                <div><dt>Borrowing Status</dt><dd id="bookDetailsStatus"></dd></div>
                <div><dt>Due Date</dt><dd id="bookDetailsDueSummary"></dd></div>
                <div><dt>Days Overdue</dt><dd id="bookDetailsDaysOverdue"></dd></div>
                <div><dt>Fine Amount</dt><dd id="bookDetailsFine"></dd></div>
                <div><dt>Fine Status</dt><dd id="bookDetailsFineStatus"></dd></div>
            </dl>
            <div class="issued-book-overdue-alert" id="overdueInfo" hidden>
                <strong id="bookDetailsOverdueMessage"></strong>
                <span id="bookDetailsOverdueContext"></span>
            </div>

            <section class="issued-book-section" aria-labelledby="issuedBookTransactionHeading">
                <h3 id="issuedBookTransactionHeading">Transaction Details</h3>
                <dl class="issued-book-information">
                    <div><dt>Transaction ID</dt><dd id="bookDetailsTransactionId"></dd></div>
                    <div><dt>Issue Date</dt><dd id="bookDetailsIssueDate"></dd></div>
                    <div><dt>Due Date</dt><dd id="bookDetailsDueDate"></dd></div>
                    <div><dt>Return Date</dt><dd id="bookDetailsReturnDate"></dd></div>
                    <div><dt>Issued By</dt><dd id="bookDetailsIssuedBy"></dd></div>
                    <div><dt>Renewal Count</dt><dd id="bookDetailsRenewalCount"></dd></div>
                </dl>
            </section>

            <section class="issued-book-section" aria-labelledby="issuedBookTimelineHeading">
                <h3 id="issuedBookTimelineHeading">Borrowing Timeline</h3>
                <ol class="issued-book-timeline">
                    <li><strong>Issued</strong><span id="bookDetailsTimelineIssued"></span></li>
                    <li><strong>Due</strong><span id="bookDetailsTimelineDue"></span></li>
                    <li id="bookDetailsTimelineReturnStep"><strong>Returned</strong><span id="bookDetailsTimelineReturned"></span></li>
                </ol>
            </section>

            <section class="issued-book-section" aria-labelledby="issuedBookInformationHeading">
                <h3 id="issuedBookInformationHeading">Book / Copy Information</h3>
                <dl class="issued-book-information">
                    <div><dt>Publisher</dt><dd id="bookDetailsPublisher"></dd></div>
                    <div><dt>Copy Condition</dt><dd id="bookDetailsCopyCondition"></dd></div>
                    <div><dt>Physical Copy Status <small>(current)</small></dt><dd id="bookDetailsCopyStatus"></dd></div>
                    <div><dt>Rack / Shelf</dt><dd id="bookDetailsCopyShelf"></dd></div>
                    <div><dt>Issue Condition</dt><dd id="bookDetailsCondition"></dd></div>
                </dl>
            </section>

            <section class="issued-book-section" id="bookDetailsFineSection" aria-labelledby="issuedBookFineHeading" hidden>
                <h3 id="issuedBookFineHeading">Fine Details</h3>
                <dl class="issued-book-information">
                    <div><dt>Fine Amount</dt><dd id="bookDetailsFineAmount"></dd></div>
                    <div><dt>Fine Status</dt><dd id="bookDetailsFineDetailStatus"></dd></div>
                    <div id="bookDetailsFineDaysRow" hidden><dt>Recorded Days Late</dt><dd id="bookDetailsFineDays"></dd></div>
                    <div id="bookDetailsPaymentMethodRow" hidden><dt>Payment Method</dt><dd id="bookDetailsPaymentMethod"></dd></div>
                    <div id="bookDetailsPaidDateRow" hidden><dt>Paid On</dt><dd id="bookDetailsPaidDate"></dd></div>
                    <div id="bookDetailsWaivedDateRow" hidden><dt>Waived On</dt><dd id="bookDetailsWaivedDate"></dd></div>
                </dl>
            </section>
            <p class="issued-book-no-fine" id="bookDetailsNoFine">No fine recorded for this transaction.</p>

            <section class="issued-book-section issued-book-description" aria-labelledby="issuedBookDescriptionHeading">
                <h3 id="issuedBookDescriptionHeading">Book Description</h3>
                <p id="bookDetailsDescription"></p>
            </section>
        </div>
        <footer class="modal-footer issued-book-footer">
            <button type="button" class="btn btn-secondary" id="closeBookDetailsFooterBtn">Close</button>
        </footer>
    </div>
</div>
