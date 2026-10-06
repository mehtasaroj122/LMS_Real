These OFL-licensed Noto Sans Devanagari fonts are used only for PDF embedding
and print fallback. Normal web typography is unchanged.

Source: https://github.com/google/fonts/tree/main/ofl/notosansdevanagari

The regular and bold files are static instances of
`NotoSansDevanagari[wdth,wght].ttf`, created with fontTools at width 100 and
weights 400 and 700. This version includes Latin and Devanagari glyphs, so
report headings and Nepali currency amounts can use the same embedded font.
The license is included in `OFL.txt`.

Vite publishes the two assets for the browser exporter. The font loader and
fontkit are loaded only when a PDF is requested; exported PDFs embed subsets.
