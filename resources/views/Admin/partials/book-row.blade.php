<!-- Book Table Row Partial -->
@php
    $conditionClass = $book->condition === 'new' ? 'condition-new' : ($book->condition === 'damaged' ? 'condition-damaged' : 'condition-good');
    $conditionIcon = $book->condition === 'new' ? 'fa-star' : ($book->condition === 'damaged' ? 'fa-exclamation-triangle' : 'fa-check-circle');
    $conditionText = ucfirst($book->condition);
@endphp

<tr data-book-id="{{ $book->id }}" data-category="{{ $book->category_id ?? '' }}" data-condition="{{ $book->condition }}">
    <td>{{ $book->isbn }}</td>
    <td>
        <div style="display: flex; align-items: center; gap: 12px;">
            @if ($book->cover_image)
                <img src="{{ str_starts_with($book->cover_image, 'http') ? $book->cover_image : asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" style="width: 40px; height: 50px; border-radius: 4px; object-fit: cover; border: 1px solid #e5e7eb;">
            @else
                <div style="width: 40px; height: 50px; min-width: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #ffffff; font-weight: 700; font-size: 20px; flex-shrink: 0;">
                    {{ strtoupper(substr($book->title, 0, 1)) }}
                </div>
            @endif
            <div>
                <strong>{{ $book->title }}</strong>
                <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">{{ $book->publisher ?? 'Unknown Publisher' }}</div>
            </div>
        </div>
    </td>
    <td>{{ $book->author }}</td>
    <td>{{ $book->category?->name ?? 'N/A' }}</td>
    <td>{{ $book->shelf_no ?? 'N/A' }}</td>
    <td>
        <div class="copy-count">
            <span class="copy-total">{{ $book->total_copies }}</span>
        </div>
    </td>
    <td>
        <div class="copy-count">
            <span class="copy-available">{{ $book->available_copies }}</span>
        </div>
    </td>
    <td>
        <span class="condition-badge {{ $conditionClass }}">
            <i class="fas {{ $conditionIcon }}"></i>
            {{ $conditionText }}
        </span>
    </td>
    <td>
        <div class="action-buttons">
            <button class="action-btn view" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
            <button class="action-btn edit" title="Edit Book">
                <i class="fas fa-edit"></i>
            </button>
            <button class="action-btn delete" title="Delete Book">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
