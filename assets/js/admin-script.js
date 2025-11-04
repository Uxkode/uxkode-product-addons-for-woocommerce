/**
 * Uxkode Product Addons for WooCommerce - Admin JS
 *
 * Handles admin UI interactions for the Uxkode Product Addons Features using vanilla JS.
 */

document.addEventListener('DOMContentLoaded', function () {

    /**
     * Top Navigation Menu Toggle
     */
    const menuToggle = document.querySelector('.uxkode-admin-toggle');
    const navMenu = document.querySelector('.uxkode-admin-menu');
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function () {
            navMenu.classList.toggle('show');
        });
    }

    /**
     * Live Preview for Custom Button Styles
     * Updates preview button styles based on color input changes.
     * Handles both buttons separately for correct preview.
     * Now loops through both preview buttons and matches inputs by button type.
     */
    const styleTables = document.querySelectorAll('.uxkode-style-table');
    styleTables.forEach(table => {

        const previewBtns = table.querySelectorAll('.uxkode-custom-btn-1, .uxkode-custom-btn-2');
        previewBtns.forEach(previewBtn => {
            const btnType = previewBtn.classList.contains('uxkode-custom-btn-1') ? 'btn1' : 'btn2';
            // Convert btnType to match input names (btn1 -> button1, btn2 -> button2)
            const inputPrefix = btnType === 'btn1' ? 'button1' : 'button2';

            const styles = getComputedStyle(document.documentElement);
            let hoverStyles = {
                bg: styles.getPropertyValue(`--uxkode-custom-${btnType}-bg-hover-color`).trim(),
                text: styles.getPropertyValue(`--uxkode-custom-${btnType}-text-hover-color`).trim(),
                border: styles.getPropertyValue(`--uxkode-custom-${btnType}-border-hover-color`).trim()
            };

            const inputs = table.querySelectorAll('input[type=color]');
            inputs.forEach(input => {

                const name = input.getAttribute('name');
                // Only update styles for the correct button using inputPrefix
                if (name.includes(inputPrefix)) {
                    input.addEventListener('input', function () {
                        const value = input.value;

                        // Update normal styles
                        if (name.includes('bg_color') && !name.includes('_hover')) {
                            previewBtn.style.backgroundColor = value;
                        } else if (name.includes('text_color') && !name.includes('_hover')) {
                            previewBtn.style.color = value;
                        } else if (name.includes('border_color') && !name.includes('_hover')) {
                            previewBtn.style.borderColor = value;
                        }

                        // Update hover styles
                        if (name.includes('bg_hover_color')) {
                            hoverStyles.bg = value;
                        } else if (name.includes('text_hover_color')) {
                            hoverStyles.text = value;
                        } else if (name.includes('border_hover_color')) {
                            hoverStyles.border = value;
                        }
                    });
                }
            });

            previewBtn.addEventListener('mouseenter', function () {
                previewBtn.style.backgroundColor = hoverStyles.bg;
                previewBtn.style.color = hoverStyles.text;
                previewBtn.style.borderColor = hoverStyles.border;
            });

            previewBtn.addEventListener('mouseleave', function () {
                // Get correct input values for this button using inputPrefix
                const bgInput = table.querySelector(`input[name="uxkode_${inputPrefix}_bg_color"]`);
                const textInput = table.querySelector(`input[name="uxkode_${inputPrefix}_text_color"]`);
                const borderInput = table.querySelector(`input[name="uxkode_${inputPrefix}_border_color"]`);

                const bg = bgInput ? bgInput.value : '';
                const text = textInput ? textInput.value : '';
                const border = borderInput ? borderInput.value : '';

                previewBtn.style.backgroundColor = bg || styles.getPropertyValue(`--uxkode-custom-${btnType}-bg-color`).trim();
                previewBtn.style.color = text || styles.getPropertyValue(`--uxkode-custom-${btnType}-text-color`).trim();
                previewBtn.style.borderColor = border || styles.getPropertyValue(`--uxkode-custom-${btnType}-border-color`).trim();
            });
        });
    });

    /**
     * Product Add-Ons Enable Checkbox
     * Shows/hides the add-ons selection box based on checkbox state.
     */
    const addonsToggle = document.getElementById('_uxkode_product_addons_enabled');
    const addonsBox = document.getElementById('_uxkode_product_addons_selected');
    if (addonsToggle && addonsBox) {
        addonsBox.style.display = addonsToggle.checked ? 'flex' : 'none';
        addonsToggle.addEventListener('change', function () {
            addonsBox.style.display = addonsToggle.checked ? 'flex' : 'none';
        });
    }

    /**
     * Custom Buttons Enable Checkbox
     * Shows/hides the custom buttons box based on checkbox state.
     */
    const customBtnToggle = document.getElementById('_uxkode_custom_buttons_enabled');
    const customBtnBox = document.getElementById('_uxkode_custom_buttons_selected');
    if (customBtnToggle && customBtnBox) {
        customBtnBox.style.display = customBtnToggle.checked ? 'flex' : 'none';
        customBtnToggle.addEventListener('change', function () {
            customBtnBox.style.display = customBtnToggle.checked ? 'flex' : 'none';
        });
    }

    /**
     * Single or Dual Buttons Radio Toggle
     * Shows/hides the second button group based on selected type.
     */
    const radios = document.querySelectorAll('input[name="_uxkode_custom_buttons_type"]');
    const buttonGroup2 = document.querySelector('.uxkode-custom-btn-group[data-button-index="2"]');
    function toggleButtonFields() {
        const selected = document.querySelector('input[name="_uxkode_custom_buttons_type"]:checked');
        if (selected && buttonGroup2) {
            buttonGroup2.style.display = (selected.value === 'dual') ? 'block' : 'none';
        }
    }
    radios.forEach(radio => {
        radio.addEventListener('change', toggleButtonFields);
    });
    toggleButtonFields();

    /**
     * uxkode Addons Tab Panel Lazy Loading
     * Only show tab panel when its tab is clicked.
     */
    const tabLinks = document.querySelectorAll('.wc-tabs li a');
    const panels = [
        document.getElementById('uxkode-product-addons-data'),
        document.getElementById('uxkode-custom-buttons-data')
    ];

    panels.forEach(panel => {
        if (panel) {
            panel.style.display = 'none';
        }
    });

    const activeTab = document.querySelector('.wc-tabs li.active a');
    if (activeTab) {
        const targetId = activeTab.getAttribute('href').replace('#', '');
        const targetPanel = document.getElementById(targetId);
        if (targetPanel && panels.includes(targetPanel)) {
            targetPanel.style.display = 'block';
        }
    }

    tabLinks.forEach(link => {

        link.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href').replace('#', '');
            const targetPanel = document.getElementById(targetId);

            if (targetPanel && panels.includes(targetPanel)) {
                e.preventDefault();

                panels.forEach(panel => {
                    if (panel) {
                        panel.style.display = 'none';
                    }
                });

                tabLinks.forEach(l => {
                    l.parentElement.classList.remove('active');
                });

                targetPanel.style.display = 'block';
                this.parentElement.classList.add('active');
            }
        });
    });
});