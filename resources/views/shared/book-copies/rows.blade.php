@forelse($copies as $copy)
    @php
        $student = $copy->activeIssue?->student;
        $deletionEligibility = app(\App\Services\PhysicalBookCopyDeletionService::class)->eligibility($copy);
        $archiveEligibility = app(\App\Services\PhysicalBookCopyDeletionService::class)->archiveEligibility($copy);
    @endphp
    <tr>
        <td class="book-copies-select-cell">
            <input
                type="checkbox"
                class="copy-select-checkbox"
                value="{{ $copy->id }}"
                data-accession="{{ $copy->accession_number }}"
                data-archive-eligible="{{ $archiveEligibility['eligible'] ? '1' : '0' }}"
                data-delete-eligible="{{ $deletionEligibility['eligible'] ? '1' : '0' }}"
                data-delete-reason="{{ $deletionEligibility['reason'] }}"
                aria-label="Select copy {{ $copy->accession_number }}"
                @disabled(! $archiveEligibility['eligible'])
                @if(! $archiveEligibility['eligible']) title="{{ $archiveEligibility['reason'] }}" aria-describedby="copy-protection-{{ $copy->id }}" @endif
            >
            @if(! $archiveEligibility['eligible'])
                <span class="book-copies-protected" id="copy-protection-{{ $copy->id }}" title="{{ $archiveEligibility['reason'] }}"><i class="fas fa-lock" aria-hidden="true"></i><span class="book-copies-sr-only">{{ $archiveEligibility['reason'] }}</span></span>
            @endif
        </td>
        <td class="accession">{{ $copy->accession_number }}</td>
        <td>{{ $copy->entry_date?->toDateString() }}</td>
        <td>{{ ucfirst($copy->book_type) }}</td>
        <td>{{ $copy->trashed() ? 'Archived' : ucfirst($copy->status) }}</td>
        <td>@if($student)<div>{{ $student->user?->name ?? 'Unknown student' }}</div><small class="book-copies-muted">{{ $student->roll_no ?: ($student->student_id ?: 'N/A') }}</small>@else<span class="book-copies-muted">—</span>@endif</td>
        <td>{{ $copy->shelf_location ?: '—' }}</td>
        <td>{{ ucfirst($copy->condition) }}</td>
        <td>{{ $copy->price !== null ? number_format((float) $copy->price, 2) : '—' }}</td>
        <td>
            {{ $copy->remarks ?: '—' }}
            @if($copy->trashed())
                <small class="book-copies-archive-details">Archived {{ $copy->archived_at?->format('Y-m-d H:i') }}@if($copy->archivedBy) by {{ $copy->archivedBy->name }}@endif</small>
                @if($copy->archive_reason)<small class="book-copies-archive-details">Reason: {{ $copy->archive_reason }}</small>@endif
            @endif
        </td>
        <td>
            @if($copy->trashed())
                <button type="button" class="book-copies-action restore restore-copy-btn" data-copy-id="{{ $copy->id }}" data-accession="{{ $copy->accession_number }}">Restore</button>
            @else
                <button type="button" class="book-copies-action edit-copy-btn" data-copy-id="{{ $copy->id }}">Edit</button>
                <button type="button" class="book-copies-action archive archive-copy-btn" data-copy-id="{{ $copy->id }}" data-accession="{{ $copy->accession_number }}" @disabled(! $archiveEligibility['eligible']) @if(! $archiveEligibility['eligible']) title="{{ $archiveEligibility['reason'] }}" @endif>Archive</button>
                <button type="button" class="book-copies-action delete delete-copy-btn" data-copy-id="{{ $copy->id }}" @disabled(! $deletionEligibility['eligible']) @if(! $deletionEligibility['eligible']) title="{{ $deletionEligibility['reason'] }}" @endif>Delete</button>
            @endif
        </td>
    </tr>
    @if(! $copy->trashed())
    <tr class="book-copies-edit-row" id="edit-row-{{ $copy->id }}"><td colspan="11">
        <form class="book-copies-edit-form copy-edit-form" data-copy-id="{{ $copy->id }}">
            <label>Accession<input value="{{ $copy->accession_number }}" readonly></label>
            <label>Entry Date<input name="entry_date" type="date" value="{{ $copy->entry_date?->toDateString() }}" required></label>
            <label>Book Type<select name="book_type"><option value="borrowing" @selected($copy->book_type === 'borrowing')>Borrowing</option><option value="reference" @selected($copy->book_type === 'reference')>Reference</option></select></label>
            <label>Condition<select name="condition">@foreach(['new', 'good', 'fair', 'damaged', 'lost'] as $condition)<option value="{{ $condition }}" @selected($copy->condition === $condition)>{{ ucfirst($condition) }}</option>@endforeach</select></label>
            <label>Shelf<input name="shelf_location" value="{{ $copy->shelf_location }}" maxlength="100"></label>
            <label>Price<input name="price" type="number" min="0" max="99999999.99" step="0.01" value="{{ $copy->price }}"></label>
            <label class="remarks">Remarks<textarea name="remarks" maxlength="1000" rows="1">{{ $copy->remarks }}</textarea></label>
            <button type="submit" class="book-copies-button primary">Save Changes</button>
        </form>
    </td></tr>
    @endif
@empty
    <tr><td colspan="11" class="book-copies-empty">{{ $search !== '' || $status !== 'all' || $bookType !== 'all' ? 'No copies match these filters.' : ($inventory === 'archived' ? 'No archived copies.' : 'No active physical copies.') }}</td></tr>
@endforelse
