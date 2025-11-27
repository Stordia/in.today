/**
 * Contact Form AJAX Handler
 *
 * Handles:
 * - AJAX form submission
 * - Success modal display
 * - Inline validation errors
 * - Double-submit prevention
 */

/**
 * Initialize contact form with AJAX handling
 */
function initContactForm() {
    const form = document.getElementById('contact-form');
    if (!form) return;

    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton?.textContent || 'Submit';

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Prevent double submission
        if (form.dataset.submitting === 'true') return;
        form.dataset.submitting = 'true';

        // Update button state
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = '...';
        }

        // Clear previous errors
        clearFormErrors(form);

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await response.json();

            if (response.ok && data.ok) {
                // Success - show modal and reset form
                showSuccessModal(data.message);
                form.reset();
            } else if (response.status === 422 && data.errors) {
                // Validation errors
                displayFormErrors(form, data.errors);
            } else {
                // Other error - show generic message
                console.error('Form submission error:', data);
                showErrorAlert(data.message || 'An error occurred. Please try again.');
            }
        } catch (error) {
            console.error('Form submission failed:', error);
            // On network error, fall back to regular form submission
            form.dataset.submitting = 'false';
            form.submit();
            return;
        }

        // Reset button state
        form.dataset.submitting = 'false';
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
        }
    });
}

/**
 * Clear all form error messages
 */
function clearFormErrors(form) {
    // Remove error messages
    form.querySelectorAll('.form-error').forEach(el => el.remove());

    // Remove error styling from inputs
    form.querySelectorAll('.border-error').forEach(el => {
        el.classList.remove('border-error');
    });
}

/**
 * Display validation errors on form fields
 */
function displayFormErrors(form, errors) {
    Object.entries(errors).forEach(([field, messages]) => {
        // Handle array fields like 'services'
        const fieldName = field.replace(/\.\d+$/, '');
        const input = form.querySelector(`[name="${fieldName}"], [name="${fieldName}[]"]`);

        if (input) {
            // Add error styling
            input.classList.add('border-error');

            // Find the parent container for the error message
            const container = input.closest('div');
            if (container) {
                // Create error message element
                const errorEl = document.createElement('p');
                errorEl.className = 'form-error mt-1 text-xs text-error';
                errorEl.textContent = Array.isArray(messages) ? messages[0] : messages;

                // Insert after the input or at the end of container
                const existingError = container.querySelector('.form-error');
                if (!existingError) {
                    container.appendChild(errorEl);
                }
            }
        }
    });

    // Scroll to first error
    const firstError = form.querySelector('.border-error');
    if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstError.focus();
    }
}

/**
 * Show success modal
 */
function showSuccessModal(message) {
    const modal = document.getElementById('contact-success-modal');
    const messageEl = document.getElementById('contact-success-message');

    if (modal && messageEl) {
        messageEl.textContent = message;
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');

        // Focus the close button for accessibility
        const closeButton = modal.querySelector('[data-close-modal]');
        if (closeButton) {
            closeButton.focus();
        }
    }
}

/**
 * Hide success modal
 */
function hideSuccessModal() {
    const modal = document.getElementById('contact-success-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
    }
}

/**
 * Show error alert (fallback for non-validation errors)
 */
function showErrorAlert(message) {
    // Try to use the existing error display if available
    const errorContainer = document.querySelector('.alert-error');
    if (errorContainer) {
        const messageEl = errorContainer.querySelector('p');
        if (messageEl) {
            messageEl.textContent = message;
        }
        errorContainer.classList.remove('hidden');
        errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        // Fallback to alert
        alert(message);
    }
}

/**
 * Set up modal close handlers
 */
function setupModalHandlers() {
    const modal = document.getElementById('contact-success-modal');
    if (!modal) return;

    // Close button handler
    modal.querySelectorAll('[data-close-modal]').forEach(button => {
        button.addEventListener('click', hideSuccessModal);
    });

    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            hideSuccessModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            hideSuccessModal();
        }
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initContactForm();
        setupModalHandlers();
    });
} else {
    initContactForm();
    setupModalHandlers();
}

export { initContactForm, hideSuccessModal };
