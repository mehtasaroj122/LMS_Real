@props(['rows' => 5, 'columns' => 5, 'label' => 'Loading data...'])

@for($row = 0; $row < max(1, (int) $rows); $row++)
    <tr class="table-skeleton-row">
        @for($column = 0; $column < max(1, (int) $columns); $column++)
            <td>
                @if($row === 0 && $column === 0)
                    <span class="table-skeleton-sr-only" role="status">{{ $label }}</span>
                @endif
                <span class="table-skeleton-bar" aria-hidden="true"></span>
            </td>
        @endfor
    </tr>
@endfor
