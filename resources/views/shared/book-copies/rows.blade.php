@forelse($copies as $copy)
    @php($student = $copy->issuedBooks->first()?->student)
    <tr>
        <td class="accession">{{ $copy->accession_number }}</td>
        <td>{{ $copy->entry_date?->toDateString() }}</td>
        <td>{{ ucfirst($copy->book_type) }}</td>
        <td>{{ ucfirst($copy->status) }}</td>
        <td>@if($student)<div>{{ $student->user?->name ?? 'Unknown student' }}</div><small class="book-copies-muted">{{ $student->roll_no ?: ($student->student_id ?: 'N/A') }}</small>@else<span class="book-copies-muted">—</span>@endif</td>
        <td>{{ $copy->shelf_location ?: '—' }}</td>
        <td>{{ ucfirst($copy->condition) }}</td>
        <td>{{ $copy->price !== null ? number_format((float) $copy->price, 2) : '—' }}</td>
        <td>{{ $copy->remarks ?: '—' }}</td>
        <td><button type="button" class="book-copies-action edit-copy-btn" data-copy-id="{{ $copy->id }}">Edit</button> <button type="button" class="book-copies-action delete delete-copy-btn" data-copy-id="{{ $copy->id }}">Delete</button></td>
    </tr>
    <tr class="book-copies-edit-row" id="edit-row-{{ $copy->id }}"><td colspan="10">
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
@empty
    <tr><td colspan="10" class="book-copies-empty">{{ $search !== '' || $status !== 'all' || $bookType !== 'all' ? 'No copies match these filters.' : 'No physical copies have been registered.' }}</td></tr>
@endforelse
