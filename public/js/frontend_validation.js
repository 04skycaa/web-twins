document.addEventListener('DOMContentLoaded', function() {
    // Styling is injected directly to avoid needing a separate CSS file for these few rules
    const style = document.createElement('style');
    style.innerHTML = `
        .input-error {
            border: 1px solid #dc2626 !important;
            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.1) !important;
            outline: none !important;
        }
        .input-error:focus {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2) !important;
            outline: none !important;
        }
        .error-message {
            color: #dc2626;
            font-size: 11px;
            margin-top: 4px;
            display: block;
            font-weight: 500;
        }
    `;
    document.head.appendChild(style);

    // Apply to all forms except those with custom JS validation
    const forms = document.querySelectorAll('form:not([onsubmit*="validateProductForm"])');
    
    forms.forEach(form => {
        // Disable browser default validation UI
        form.setAttribute('novalidate', 'true');

        // Ignore forms without standard inputs (like simple delete forms)
        const allInputs = form.querySelectorAll('input, select, textarea');
        if (allInputs.length === 0) return;

        // Find all required inputs (both standard 'required' and 'required_js' if used)
        const requiredElements = form.querySelectorAll('input[required], select[required], textarea[required], input[required_js], select[required_js], textarea[required_js]');
        
        // Setup realtime validation on input/change
        requiredElements.forEach(el => {
            el.addEventListener('input', function() {
                validateElement(this);
            });
            el.addEventListener('change', function() {
                validateElement(this);
            });
        });

        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Re-select in case of dynamic elements
            const reqElems = form.querySelectorAll('input[required], select[required], textarea[required], input[required_js], select[required_js], textarea[required_js]');
            
            reqElems.forEach(el => {
                // If element is visible
                if (el.offsetParent !== null) {
                    if (!validateElement(el)) {
                        isValid = false;
                    }
                }
            });

            if (!isValid) {
                e.preventDefault(); // Stop submission
            }
        });
    });

    function validateElement(el) {
        // Remove existing error if any
        let wrapper = el.parentElement;
        
        // If wrapped in custom wrappers like .nominal-wrapper or .form-group, append to the correct parent
        let errorContainer = wrapper;
        if (wrapper.classList.contains('nominal-wrapper') || wrapper.classList.contains('search-wrapper') || wrapper.classList.contains('input-group')) {
            errorContainer = wrapper.parentElement;
        }

        const existingError = errorContainer.querySelector('.error-message.val-error');
        if (existingError) {
            existingError.remove();
        }
        el.classList.remove('input-error');

        // Check if empty
        if (!el.value || !el.value.trim()) {
            el.classList.add('input-error');
            const errorSpan = document.createElement('span');
            errorSpan.classList.add('error-message', 'val-error');
            
            // Generate friendly message based on label, placeholder, or name
            let labelText = '';
            
            // Try to find a label
            const label = errorContainer.querySelector('label');
            if (label) {
                labelText = label.innerText.replace('*', '').trim();
            } else if (el.getAttribute('placeholder')) {
                labelText = el.getAttribute('placeholder');
            } else if (el.name) {
                // Convert snake_case to Title Case (e.g., nominal_uang -> Nominal Uang)
                labelText = el.name.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
            } else {
                labelText = 'Field ini';
            }
            
            errorSpan.innerText = labelText + ' wajib diisi!';
            errorContainer.appendChild(errorSpan);
            return false;
        }
        return true;
    }

    // Reset validation when a modal is closed
    const modals = document.querySelectorAll('.modal-overlay');
    
    if (modals.length > 0) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const modal = mutation.target;
                    // If modal is being hidden
                    if (modal.style.display === 'none') {
                        // Find all validation errors inside this modal and remove them
                        const errorInputs = modal.querySelectorAll('.input-error');
                        errorInputs.forEach(el => el.classList.remove('input-error'));
                        
                        const errorMessages = modal.querySelectorAll('.error-message.val-error');
                        errorMessages.forEach(el => el.remove());

                        // Optional: Reset form fields so it's clean for the next open
                        const form = modal.querySelector('form');
                        if (form) {
                            form.reset();
                        }
                    }
                }
            });
        });

        modals.forEach(modal => {
            observer.observe(modal, { attributes: true });
        });
    }
});
