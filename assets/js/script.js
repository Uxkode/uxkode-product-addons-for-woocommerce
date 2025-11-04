/**
 * Uxkode Product Addons for WooCommerce - Frontend JS
 * Handles price updates, validation, and error display using vanilla JS.
 */

document.addEventListener('DOMContentLoaded', function () {

    /**
     * Show error message below the input field.
     *
     * @param {HTMLElement} input
     * @param {string} message
     */
    function showError(input, message) {

        if (!input.parentNode) return;
        let error = input.parentNode.querySelector('.uxkode-error');
        if (!error) {
            error = document.createElement('div');
            error.className = 'uxkode-error';
            error.style.color = 'red';
            error.style.fontSize = '13px';
            input.parentNode.appendChild(error);
        }
        error.textContent = String(message);
    }

    /**
     * Remove error message from the input field.
     *
     * @param {HTMLElement} input
     */
    function clearError(input) {
        if (!input.parentNode) return;
        const error = input.parentNode.querySelector('.uxkode-error');
        if (error) {
            error.remove();
        }
    }

    /**
     * Validate required fields in the product add-ons form.
     *
     * @param {HTMLFormElement} form
     * @returns {boolean}
     */
    function validateFields(form) {

        let valid = true;
        const addonInputs = form.querySelectorAll(
            '.uxkode-input-field, .uxkode-textarea-field'
        );

        addonInputs.forEach(input => {
            clearError(input);

            // If checkbox/radio is required
            if (
                (input.type === 'checkbox' || input.type === 'radio') &&
                input.dataset.required === 'true'
            ) {
                const groupName = input.name;
                const checked = form.querySelector(
                    'input[name="' + (window.CSS && CSS.escape ? CSS.escape(groupName) : groupName) + '"]:checked'
                );
                if (!checked) {
                    showError(input, 'This option is required.');
                    valid = false;
                }
            }

            // If text/number/textarea is required
            if (
                (input.type === 'text' ||
                    input.type === 'number' ||
                    input.tagName.toLowerCase() === 'textarea') &&
                input.dataset.required === 'true'
            ) {
                if (!input.value.trim()) {
                    showError(input, 'This field is required.');
                    valid = false;
                }
            }
        });

        return valid;
    }

    const productForms = document.querySelectorAll('form.cart');

    productForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!validateFields(form)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // Show/hide addon fields based on toggle state
    document.querySelectorAll('.uxkode-addon-toggle').forEach(checkbox => {
        const wrapper = checkbox.closest('.uxkode-addon-wrapper');
        if (!wrapper) return;
        const field = wrapper.querySelector('.uxkode-addon-field');
        if (!field) return;

        // Set initial state
        field.style.display = checkbox.checked ? 'block' : 'none';

        // Toggle on change
        checkbox.addEventListener('change', function () {
            field.style.display = checkbox.checked ? 'block' : 'none';
        });
    });
});