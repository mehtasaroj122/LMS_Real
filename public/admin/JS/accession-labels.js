document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-accession-page]');
    if (!page) return;

    const fromInput = page.querySelector('#from');
    page.querySelector('[data-use-next]')?.addEventListener('click', (event) => {
        if (!fromInput) return;
        fromInput.value = event.currentTarget.dataset.useNext || '';
        fromInput.focus();
    });

    const generateForm = page.querySelector('[data-generate-form]');
    generateForm?.addEventListener('submit', () => {
        const button = generateForm.querySelector('[data-generate-button]');
        if (!button) return;
        button.disabled = true;
        button.querySelector('span').textContent = 'Generating preview...';
    });

    const printForm = document.getElementById('accessionPrintForm');
    const checkboxes = [...page.querySelectorAll('.accession-label-preview input[type="checkbox"]')];
    const selectedCount = page.querySelector('[data-selected-count]');
    const selectedFooter = page.querySelector('[data-selected-footer]');

    const refreshSelection = () => {
        const count = checkboxes.filter((checkbox) => checkbox.checked).length;
        if (selectedCount) selectedCount.textContent = String(count);
        if (selectedFooter) selectedFooter.textContent = String(count);
        checkboxes.forEach((checkbox) => checkbox.closest('.accession-label-preview')?.classList.toggle('is-selected', checkbox.checked));
    };

    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', refreshSelection));
    page.querySelector('[data-select-all]')?.addEventListener('click', () => { checkboxes.forEach((checkbox) => { checkbox.checked = true; }); refreshSelection(); });
    page.querySelector('[data-clear-selection]')?.addEventListener('click', () => { checkboxes.forEach((checkbox) => { checkbox.checked = false; }); refreshSelection(); });

    const labelSize = page.querySelector('[data-label-size]');
    const columns = page.querySelector('[data-columns]');
    const updateColumnOptions = () => {
        if (!labelSize || !columns) return;
        const max = Number(labelSize.selectedOptions[0]?.dataset.maxColumns || 5);
        [...columns.options].forEach((option) => { option.disabled = Number(option.value) > max; });
        if (Number(columns.value) > max) columns.value = String(max);
    };
    labelSize?.addEventListener('change', updateColumnOptions);
    updateColumnOptions();

    page.querySelectorAll('[data-print]').forEach((button) => button.addEventListener('click', () => {
        if (!printForm) return;
        const scope = button.dataset.print;
        if (scope === 'selected' && checkboxes.every((checkbox) => !checkbox.checked)) {
            window.alert('Select at least one label to print.');
            return;
        }
        printForm.querySelector('[data-action-type]').value = 'range';
        printForm.querySelector('[data-print-scope-input]').value = scope;
        printForm.querySelector('[data-copy-id]').value = '';
        printForm.requestSubmit();
    }));

    page.querySelectorAll('[data-reprint-copy]').forEach((button) => button.addEventListener('click', () => {
        if (!printForm) return;
        printForm.querySelector('[data-action-type]').value = 'reprint';
        printForm.querySelector('[data-copy-id]').value = button.dataset.reprintCopy;
        printForm.requestSubmit();
    }));
});
