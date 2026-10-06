/* Shared currency presentation. Numeric API and database fields remain numeric. */
(function () {
    'use strict';
    const options = window.LmsCurrencyOptions || { code: 'NPR', symbol: 'रु' };
    const prefix = `${options.symbol} `;
    window.LmsCurrency = Object.freeze({
        code: options.code,
        symbol: options.symbol,
        prefix,
        label(amount) { return prefix + String(amount); },
        format(amount, numberOptions = {}, locale = undefined) {
            return prefix + Number(amount).toLocaleString(locale, {
                minimumFractionDigits: 2, maximumFractionDigits: 2, ...numberOptions,
            });
        },
        normalizeText(value) {
            return String(value ?? '').replace(/(?:\u20b9\s*|\b(?:Rs\.?|INR)\s*|रु\s*)(?=[+-]?\d)/gu, prefix);
        },
    });
})();
