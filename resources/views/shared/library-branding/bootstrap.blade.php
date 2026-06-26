@once
    @php($resolvedLibraryBranding = $libraryBranding ?? \App\Support\LibraryBranding::resolve())
    <script>
        window.__LIBRARY_BRANDING__ = @json($resolvedLibraryBranding);
    </script>
@endonce
