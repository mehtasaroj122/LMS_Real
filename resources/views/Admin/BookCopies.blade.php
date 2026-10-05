@extends('Admin.layouts.app')

@section('title', 'Manage Book Copies')

@section('content')
<div style="padding:24px;">
    <h1 style="font-size:24px;font-weight:700;">Manage Copies</h1>
    <p style="color:#64748b;margin:5px 0 22px;">{{ $book->title }} · ISBN {{ $book->isbn }}</p>
    <form id="copyForm" style="display:flex;gap:12px;align-items:end;padding:18px;background:#f8fafc;border-radius:12px;margin-bottom:22px;">
        @csrf
        <label>Number of copies<input name="quantity" type="number" min="1" value="1" required style="display:block;padding:9px;margin-top:5px;border:1px solid #cbd5e1;border-radius:7px;"></label>
        <label>Book type<select name="book_type" style="display:block;padding:9px;margin-top:5px;border:1px solid #cbd5e1;border-radius:7px;"><option value="borrowing">Borrowing</option><option value="reference">Reference</option></select></label>
        <button style="padding:10px 14px;background:#2563eb;color:#fff;border:0;border-radius:7px;">Add copies</button>
    </form>
    <p id="copyMessage"></p>
    <div style="overflow:auto;margin-top:12px;"><table style="width:100%;border-collapse:collapse;min-width:760px;"><thead><tr style="background:#1e3a8a;color:#fff;text-align:left;"><th style="padding:12px;">Accession Number</th><th>Entry Date</th><th>Book Type</th><th>Status</th><th>Shelf</th><th>Condition</th></tr></thead><tbody>
    @forelse($book->copies as $copy)<tr style="border-top:1px solid #e2e8f0;"><td style="padding:12px;font-weight:600;">{{ $copy->accession_number }}</td><td>{{ optional($copy->entry_date)->toDateString() }}</td><td>{{ ucfirst($copy->book_type) }}</td><td>{{ ucfirst($copy->status) }}</td><td>{{ $copy->shelf_location ?: '—' }}</td><td>{{ ucfirst($copy->condition) }}</td></tr>@empty<tr><td colspan="6" style="padding:20px;text-align:center;">No copies registered.</td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection
@push('scripts')<script>document.getElementById('copyForm')?.addEventListener('submit',async(e)=>{e.preventDefault();const r=await fetch(@json(route('admin.books.copies.store',$book)),{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('input[name="_token"]').value,'Accept':'application/json'},body:new FormData(e.currentTarget)});const d=await r.json();document.getElementById('copyMessage').textContent=d.message||'Unable to add copies.';if(r.ok)location.reload();});</script>@endpush
