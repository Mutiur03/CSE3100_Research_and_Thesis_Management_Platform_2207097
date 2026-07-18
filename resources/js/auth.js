const EMAIL_PLACEHOLDERS = {
    student: 'rahman21041@stud.kuet.ac.bd',
    supervisor: 'karim@cse.kuet.ac.bd',
};

const EMAIL_HINTS = {
    student: 'Use your student email (e.g. lastnameroll@stud.kuet.ac.bd).',
    supervisor: 'Use your faculty email (e.g. name@dept.kuet.ac.bd).',
};

function togglePasswordVisibility(button) {
    const input = button
        .closest('div')
        ?.querySelector('[data-password-input]');

    if (!(input instanceof HTMLInputElement)) {
        return;
    }

    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    button.setAttribute('aria-pressed', show ? 'true' : 'false');
    button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');

    const showIcon = button.querySelector('[data-password-icon="show"]');
    const hideIcon = button.querySelector('[data-password-icon="hide"]');
    showIcon?.classList.toggle('hidden', show);
    hideIcon?.classList.toggle('hidden', !show);
}

function setSubmitLoading(form, isLoading) {
    const submit = form.querySelector('[type="submit"]');
    if (!(submit instanceof HTMLButtonElement)) {
        return;
    }

    if (isLoading) {
        if (!submit.dataset.originalLabel) {
            submit.dataset.originalLabel = submit.textContent?.trim() ?? '';
        }
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        submit.textContent = submit.dataset.loadingLabel || 'Please wait…';
        return;
    }

    submit.disabled = false;
    submit.removeAttribute('aria-busy');
    if (submit.dataset.originalLabel) {
        submit.textContent = submit.dataset.originalLabel;
    }
}

function syncRegisterEmailGuidance(form) {
    const selected = form.querySelector('input[name="role"]:checked');
    const email = form.querySelector('#email');
    const hint = form.querySelector('[data-email-hint]');
    const role = selected instanceof HTMLInputElement ? selected.value : 'student';

    if (email instanceof HTMLInputElement) {
        email.placeholder = EMAIL_PLACEHOLDERS[role] ?? EMAIL_PLACEHOLDERS.student;
    }

    if (hint) {
        hint.textContent = EMAIL_HINTS[role] ?? EMAIL_HINTS.student;
    }
}

function syncPasswordMatch(form) {
    const password = form.querySelector('#password');
    const confirmation = form.querySelector('#password_confirmation');
    const matchHint = form.querySelector('[data-password-match]');

    if (!(password instanceof HTMLInputElement) || !(confirmation instanceof HTMLInputElement) || !matchHint) {
        return;
    }

    if (!confirmation.value) {
        matchHint.textContent = '';
        matchHint.classList.add('hidden');
        return;
    }

    const matches = password.value === confirmation.value;
    matchHint.textContent = matches ? 'Passwords match.' : 'Passwords do not match yet.';
    matchHint.classList.toggle('text-emerald-700', matches);
    matchHint.classList.toggle('text-amber-700', !matches);
    matchHint.classList.remove('hidden');
}

document.addEventListener('click', (event) => {
    const button = event.target instanceof Element
        ? event.target.closest('[data-password-toggle]')
        : null;

    if (button) {
        togglePasswordVisibility(button);
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-auth-form')) {
        return;
    }

    const password = form.querySelector('#password');
    const confirmation = form.querySelector('#password_confirmation');
    if (
        password instanceof HTMLInputElement
        && confirmation instanceof HTMLInputElement
        && confirmation.value
        && password.value !== confirmation.value
    ) {
        confirmation.setCustomValidity('Passwords do not match.');
        confirmation.reportValidity();
        event.preventDefault();
        return;
    }

    if (confirmation instanceof HTMLInputElement) {
        confirmation.setCustomValidity('');
    }

    setSubmitLoading(form, true);
});

window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll('form[data-auth-form]').forEach((form) => {
        if (form instanceof HTMLFormElement) {
            setSubmitLoading(form, false);
        }
    });
});

document.addEventListener('change', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLInputElement) || target.name !== 'role') {
        return;
    }

    const form = target.closest('[data-auth-form]');
    if (form instanceof HTMLFormElement) {
        syncRegisterEmailGuidance(form);
    }
});

document.addEventListener('input', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLInputElement)) {
        return;
    }

    if (target.id !== 'password' && target.id !== 'password_confirmation') {
        return;
    }

    const form = target.closest('[data-auth-form]');
    if (form instanceof HTMLFormElement) {
        syncPasswordMatch(form);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-auth-form]').forEach((form) => {
        if (form instanceof HTMLFormElement) {
            syncRegisterEmailGuidance(form);
            syncPasswordMatch(form);
        }
    });
});
