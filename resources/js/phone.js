const PHONE_PATTERN = /^01\d{9}$/;
const PHONE_HINT = 'Must be 11 digits starting with 01 (e.g. 01712345678).';

function digitsOnly(value) {
    return value.replace(/\D/g, '').slice(0, 11);
}

function setPhoneValidity(input) {
    const value = input.value.trim();

    if (!value) {
        input.setCustomValidity('');
        return true;
    }

    if (!PHONE_PATTERN.test(value)) {
        input.setCustomValidity(PHONE_HINT);
        return false;
    }

    input.setCustomValidity('');
    return true;
}

function bindPhoneInput(input) {
    if (!(input instanceof HTMLInputElement) || input.dataset.phoneBound === '1') {
        return;
    }

    input.dataset.phoneBound = '1';
    input.setAttribute('inputmode', 'numeric');
    input.setAttribute('autocomplete', 'tel');
    input.setAttribute('maxlength', '11');
    input.setAttribute('pattern', '01[0-9]{9}');
    input.setAttribute('title', PHONE_HINT);

    if (!input.placeholder) {
        input.placeholder = '01712345678';
    }

    input.addEventListener('input', () => {
        const next = digitsOnly(input.value);
        if (input.value !== next) {
            input.value = next;
        }
        setPhoneValidity(input);
    });

    input.addEventListener('blur', () => {
        setPhoneValidity(input);
    });

    const form = input.form;
    if (form && !form.dataset.phoneValidateBound) {
        form.dataset.phoneValidateBound = '1';
        form.addEventListener('submit', (event) => {
            const phones = form.querySelectorAll('[data-phone-input], input[name="phone"]');
            for (const phone of phones) {
                if (!(phone instanceof HTMLInputElement)) {
                    continue;
                }
                if (!setPhoneValidity(phone)) {
                    event.preventDefault();
                    phone.reportValidity();
                    phone.focus();
                    break;
                }
            }
        });
    }

    setPhoneValidity(input);
}

export function initPhoneValidation(root = document) {
    root.querySelectorAll('[data-phone-input], input[name="phone"]').forEach(bindPhoneInput);
}

document.addEventListener('DOMContentLoaded', () => {
    initPhoneValidation();
});

document.addEventListener('livewire:navigated', () => {
    initPhoneValidation();
});
