import { validationSchemas } from './schemas';

function collectFormData(form) {
    const data = {};

    new FormData(form).forEach((value, key) => {
        data[key] = value;
    });

    form.querySelectorAll('input[type="checkbox"][name]').forEach((checkbox) => {
        if (!(checkbox.name in data)) {
            data[checkbox.name] = checkbox.checked ? checkbox.value : undefined;
        }
    });

    return data;
}

function getFieldInput(form, fieldName) {
    const named = form.querySelector(`[name="${fieldName}"]`);
    if (named) return named;

    if (fieldName.includes('.')) {
        const baseName = fieldName.split('.')[0];
        return form.querySelector(`[name="${baseName}"]`);
    }

    return null;
}

function getFieldContainer(input) {
    if (!input) return null;

    if (input.type === 'checkbox') {
        return input.closest('label')?.parentElement || input.parentElement;
    }

    return input.closest('.form-field') || input.parentElement;
}

function clearFieldError(input) {
    if (!input) return;

    input.classList.remove('is-invalid');
    input.removeAttribute('aria-invalid');

    const container = getFieldContainer(input);
    container?.querySelector('[data-client-error]')?.remove();
}

function clearFormErrors(form) {
    form.querySelectorAll('.is-invalid').forEach((input) => {
        input.classList.remove('is-invalid');
        input.removeAttribute('aria-invalid');
    });

    form.querySelectorAll('[data-client-error]').forEach((error) => error.remove());
}

function showFieldError(form, fieldName, message) {
    const input = getFieldInput(form, fieldName);
    if (!input) return;

    const container = getFieldContainer(input);
    if (!container) return;

    input.classList.add('is-invalid');
    input.setAttribute('aria-invalid', 'true');

    let errorEl = container.querySelector('[data-client-error]');
    if (!errorEl) {
        errorEl = document.createElement('p');
        errorEl.className = 'field-error';
        errorEl.dataset.clientError = fieldName;
        container.appendChild(errorEl);
    }

    errorEl.textContent = message;
}

function displayValidationErrors(form, zodError) {
    zodError.issues.forEach((issue) => {
        const fieldName = issue.path[0];
        if (!fieldName) return;
        showFieldError(form, fieldName, issue.message);
    });

    const firstInvalid = form.querySelector('.is-invalid');
    firstInvalid?.focus({ preventScroll: true });
    firstInvalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function validateForm(form) {
    const schemaKey = form.dataset.validate;
    const schema = validationSchemas[schemaKey];

    if (!schema) return true;

    clearFormErrors(form);

    const result = schema.safeParse(collectFormData(form));

    if (result.success) {
        return true;
    }

    displayValidationErrors(form, result.error);
    return false;
}

export function initFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach((form) => {
        form.setAttribute('novalidate', 'novalidate');

        form.addEventListener('submit', (event) => {
            if (!validateForm(form)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });

        form.querySelectorAll('input, textarea, select').forEach((input) => {
            input.addEventListener('input', () => clearFieldError(input));
            input.addEventListener('change', () => clearFieldError(input));
        });
    });
}

export { validateForm };
