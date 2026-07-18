const MAX_UPLOAD_BYTES = 10 * 1024 * 1024;

function syncReviewNotesRequired(form) {
    const decision = form.querySelector('#decision');
    const notes = form.querySelector('#review_notes');

    if (!(decision instanceof HTMLSelectElement) || !(notes instanceof HTMLTextAreaElement)) {
        return;
    }

    const needsNotes = decision.value === 'reject' || decision.value === 'request_revision';
    notes.required = needsNotes;
    notes.setCustomValidity('');
}

function syncAdminPromotionRequired(form) {
    const role = form.querySelector('#role');
    const confirm = form.querySelector('input[name="confirm_admin_promotion"]');
    const panel = form.querySelector('#admin-promotion-confirm');

    if (!(role instanceof HTMLSelectElement) || !(confirm instanceof HTMLInputElement)) {
        return;
    }

    const isAdmin = role.value === 'admin';
    if (panel) {
        panel.classList.toggle('hidden', !isAdmin);
    }
    confirm.required = isAdmin;
    if (!isAdmin) {
        confirm.checked = false;
    }
}

function validateFileSize(input) {
    if (!(input instanceof HTMLInputElement) || input.type !== 'file') {
        return true;
    }

    const maxBytes = Number(input.dataset.maxBytes || MAX_UPLOAD_BYTES);
    const file = input.files?.[0];

    if (!file) {
        input.setCustomValidity('');
        return true;
    }

    if (file.size > maxBytes) {
        input.setCustomValidity(`File must be ${Math.round(maxBytes / (1024 * 1024))} MB or smaller.`);
        return false;
    }

    input.setCustomValidity('');
    return true;
}

document.addEventListener('change', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
        return;
    }

    if (target instanceof HTMLSelectElement && target.id === 'decision') {
        const form = target.form;
        if (form) {
            syncReviewNotesRequired(form);
        }
        return;
    }

    if (target instanceof HTMLSelectElement && target.id === 'role') {
        const form = target.form;
        if (form) {
            syncAdminPromotionRequired(form);
        }
        return;
    }

    if (target instanceof HTMLInputElement && target.type === 'file') {
        validateFileSize(target);
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    syncReviewNotesRequired(form);
    syncAdminPromotionRequired(form);

    for (const input of form.querySelectorAll('input[type="file"]')) {
        if (!validateFileSize(input)) {
            event.preventDefault();
            input.reportValidity();
            return;
        }
    }
});

function isPanelOpen(panel) {
    return panel instanceof HTMLElement && !panel.classList.contains('hidden') && !panel.hidden;
}

function syncDisclosureButtons(root = document) {
    root.querySelectorAll('[data-disclosure-toggle][aria-controls]').forEach((button) => {
        if (!(button instanceof HTMLElement)) {
            return;
        }
        const panel = document.getElementById(button.getAttribute('aria-controls') ?? '');
        if (!panel) {
            return;
        }
        button.setAttribute('aria-expanded', isPanelOpen(panel) ? 'true' : 'false');
    });
}

function setPanelOpen(panel, open) {
    if (!(panel instanceof HTMLElement)) {
        return;
    }
    panel.classList.toggle('hidden', !open);
    document.querySelectorAll(`[data-disclosure-toggle][aria-controls="${panel.id}"]`).forEach((button) => {
        if (button instanceof HTMLElement) {
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
    });
}

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) {
        return;
    }

    const toggle = target.closest('[data-disclosure-toggle]');
    if (toggle instanceof HTMLElement) {
        const panel = document.getElementById(toggle.getAttribute('aria-controls') ?? '');
        if (!panel) {
            return;
        }
        setPanelOpen(panel, !isPanelOpen(panel));
        return;
    }

    const closer = target.closest('[data-disclosure-close]');
    if (closer instanceof HTMLElement) {
        const panel = document.getElementById(closer.getAttribute('aria-controls') ?? '');
        if (!panel) {
            return;
        }
        setPanelOpen(panel, false);
    }
});

function bootForms(root = document) {
    root.querySelectorAll('form').forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        syncReviewNotesRequired(form);
        syncAdminPromotionRequired(form);
    });
    syncDisclosureButtons(root);
}

document.addEventListener('DOMContentLoaded', () => {
    bootForms();
});

document.addEventListener('livewire:navigated', () => {
    bootForms();
});
