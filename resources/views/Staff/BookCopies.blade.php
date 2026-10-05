@extends('Staff.layouts.app')

@section('title', 'Manage Book Copies')

@section('content')
<div style="padding: 24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:22px;">
        <div>
            <h1 style="font-size:24px;font-weight:700;">Manage Copies</h1>
            <p style="color:#64748b;margin-top:5px;">{{ $book->title }} · ISBN {{ $book->isbn }}</p>
        </div>
        <a href="{{ route('staff.book-management.index') }}" style="color:#2563eb;">Back to books</a>
    </div>

    <form id="copyForm" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;padding:18px;background:#f8fafc;border-radius:12px;margin-bottom:22px;">
        @csrf
        <label>Number of copies<input name="quantity" type="number" min="1" value="1" required style="width:100%;padding:9px;margin-top:5px;border:1px solid #cbd5e1;border-radius:7px;"></label>
        <label>Book type<select name="book_type" style="width:100%;padding:9px;margin-top:5px;border:1px solid #cbd5e1;border-radius:7px;"><option value="borrowing">Borrowing</option><option value="reference">Reference</option></select></label>
        <label>Entry date<input name="entry_date" type="date" value="{{ today()->toDateString() }}" style="width:100%;padding:9px;margin-top:5px;border:1px solid #cbd5e1;border-radius:7px;"></label>
        <label>Shelf location<input name="shelf_location" value="{{ $book->shelf_no }}" style="width:100%;padding:9px;margin-top:5px;border:1px solid #cbd5e1;border-radius:7px;"></label>
        <button style="align-self:end;padding:10px 14px;background:#2563eb;color:#fff;border:0;border-radius:7px;cursor:pointer;">Add copies</button>
    </form>
    <p id="copyMessage" style="margin-bottom:12px;"></p>

    <div style="overflow:auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;">
        <table style="width:100%;border-collapse:collapse;min-width:800px;">
            <thead><tr style="background:#1e3a8a;color:#fff;text-align:left;"><th style="padding:12px;">Accession Number</th><th>Entry Date</th><th>Book Type</th><th>Status</th><th>Shelf Location</th><th>Condition</th><th>Remarks</th></tr></thead>
            <tbody>
            @forelse($book->copies as $copy)
                <tr style="border-top:1px solid #e2e8f0;"><td style="padding:12px;font-weight:600;">{{ $copy->accession_number }}</td><td>{{ optional($copy->entry_date)->toDateString() }}</td><td>{{ ucfirst($copy->book_type) }}</td><td>{{ ucfirst($copy->status) }}</td><td>{{ $copy->shelf_location ?: '—' }}</td><td>{{ ucfirst($copy->condition) }}</td><td>{{ $copy->remarks ?: '—' }}</td></tr>
            @empty
                <tr><td colspan="7" style="padding:20px;text-align:center;color:#64748b;">No physical copies have been registered.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('copyForm')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const message = document.getElementById('copyMessage');
    const response = await fetch(@json(route('staff.books.copies.store', $book)), { method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json'}, body: new FormData(event.currentTarget) });
    const data = await response.json();
    message.textContent = data.message || 'Unable to add copies.';
    message.style.color = response.ok ? '#15803d' : '#b91c1c';
    if (response.ok) window.location.reload();
});
</script>
@endpush
