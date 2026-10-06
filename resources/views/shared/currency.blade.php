<script>
    window.LmsCurrencyOptions = @json(['code' => \App\Support\Currency::CODE, 'symbol' => \App\Support\Currency::SYMBOL]);
</script>
<script src="{{ asset('shared/currency.js') }}?v={{ filemtime(public_path('shared/currency.js')) }}"></script>
