<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accession Barcode Labels</title>
    <style>
        :root { --label-width: {{ $labelPreset['width_mm'] }}mm; --label-height: {{ $labelPreset['height_mm'] }}mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #0f172a; background: #eef2f7; font-family: Arial, sans-serif; }
        .print-toolbar { position: sticky; top: 0; z-index: 5; display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 20px; background: #fff; border-bottom: 1px solid #dbe2ea; box-shadow: 0 4px 14px rgba(15, 23, 42, .08); }
        .print-toolbar strong { display: block; font-size: 15px; }.print-toolbar span { color: #64748b; font-size: 12px; }
        .print-toolbar button { border: 0; border-radius: 8px; padding: 10px 16px; cursor: pointer; font-weight: 700; }.print-toolbar .secondary { border: 1px solid #cbd5e1; background: #fff; color: #334155; }.print-toolbar .primary { margin-left: 8px; background: #2563eb; color: #fff; }
        .label-sheet { display: grid; grid-template-columns: repeat({{ $settings['columns'] }}, var(--label-width)); align-content: start; justify-content: center; gap: 3mm; margin: 8mm auto; padding: 8mm; width: fit-content; min-height: 100vh; background: #fff; box-shadow: 0 12px 35px rgba(15, 23, 42, .12); }
        .print-label { display: flex; flex-direction: column; align-items: center; justify-content: center; width: var(--label-width); height: var(--label-height); overflow: hidden; padding: 2.2mm 2.5mm; background: #fff; color: #000; border: {{ $settings['show_border'] ? '0.25mm solid #111' : '0.25mm solid transparent' }}; break-inside: avoid; page-break-inside: avoid; }
        .label-brand { display: flex; align-items: center; justify-content: center; gap: 1.4mm; width: 100%; margin-bottom: 1mm; font-size: {{ $settings['label_size'] === 'small' ? '7pt' : '8pt' }}; font-weight: 700; line-height: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .label-brand img { display: block; width: 4mm; height: 4mm; object-fit: contain; }
        .barcode-wrap { display: flex; align-items: center; justify-content: center; width: 100%; min-height: 0; flex: 1; padding: 1mm 0; background: #fff; }
        .accession-barcode-svg { display: block; width: 100%; height: 100%; max-height: {{ $settings['label_size'] === 'small' ? '12mm' : ($settings['label_size'] === 'medium' ? '17mm' : '25mm') }}; }
        .label-accession { margin-top: .8mm; color: #000; font-family: Arial, sans-serif; font-size: {{ $settings['label_size'] === 'small' ? '8pt' : ($settings['label_size'] === 'medium' ? '10pt' : '12pt') }}; font-weight: 700; letter-spacing: .4px; line-height: 1; }
        .label-book-title { max-width: 100%; margin-top: .6mm; overflow: hidden; font-size: 7pt; line-height: 1; text-overflow: ellipsis; white-space: nowrap; }
        @page { size: {{ $settings['page_size'] }} {{ $settings['orientation'] }}; margin: 8mm; }
        @media print {
            html, body { margin: 0; background: #fff; }
            .print-toolbar { display: none !important; }
            .label-sheet { margin: 0; padding: 0; width: 100%; min-height: 0; box-shadow: none; gap: 3mm; }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <div><strong>Accession Barcode Print Preview</strong><span>{{ $summary['unique_count'] }} unique · {{ $summary['copies_per_label'] }} {{ Str::plural('copy', $summary['copies_per_label']) }} each · {{ $summary['total_labels'] }} total · {{ $settings['page_size'] }} {{ ucfirst($settings['orientation']) }} · {{ $settings['columns'] }} columns</span></div>
        <div><button class="secondary" type="button" onclick="window.close()">Close</button><button class="primary" type="button" onclick="window.print()">Print Labels</button></div>
    </div>
    <main class="label-sheet">
        @foreach ($labels as $label)
            <article class="print-label">
                @if ($settings['show_library_name'] || ($settings['show_logo'] && $branding['image_url']))
                    <div class="label-brand">
                        @if ($settings['show_logo'] && $branding['image_url'])<img src="{{ $branding['image_url'] }}" alt="">@endif
                        @if ($settings['show_library_name'])<span>{{ $branding['name'] }}</span>@endif
                    </div>
                @endif
                <div class="barcode-wrap">{!! $label['svg'] !!}</div>
                @if ($settings['show_accession'])<div class="label-accession">{{ $label['accession'] }}</div>@endif
                @if ($label['book_title'] && $settings['label_size'] !== 'small')<div class="label-book-title">{{ $label['book_title'] }}</div>@endif
            </article>
        @endforeach
    </main>
</body>
</html>
