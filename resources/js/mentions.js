/**
 * @mention autocomplete + chip insert for thesis discussion.
 * Canonical token inserted: @Name (matches Comment::parseMentionedUserIds name pattern).
 */

function parseMentionables(root) {
    try {
        const raw = root.getAttribute('data-mentionables');
        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

function getActiveMention(value, cursor) {
    const before = value.slice(0, cursor);
    const match = before.match(/(^|[\s([{])@([^\s@]*)$/);

    if (!match) {
        return null;
    }

    return {
        start: before.length - match[2].length - 1,
        query: match[2],
    };
}

function filterMentionables(users, query) {
    const q = query.toLowerCase();

    return users.filter((user) => {
        return (
            user.name.toLowerCase().includes(q) ||
            user.email.toLowerCase().includes(q)
        );
    });
}

function insertAtCursor(textarea, start, end, text) {
    const value = textarea.value;
    const before = value.slice(0, start);
    const after = value.slice(end);
    const needsLeadingSpace = before.length > 0 && !/\s$/.test(before);
    const token = (needsLeadingSpace ? ' ' : '') + text;
    const needsTrailingSpace = after.length === 0 || /^\w/.test(after);
    const insertion = token + (needsTrailingSpace ? ' ' : '');

    textarea.value = before + insertion + after;
    const caret = before.length + insertion.length;
    textarea.setSelectionRange(caret, caret);
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.focus();
}

function insertMention(textarea, name, replaceRange = null) {
    const token = '@' + name;

    if (replaceRange) {
        insertAtCursor(textarea, replaceRange.start, replaceRange.end, token);
        return;
    }

    const start = textarea.selectionStart ?? textarea.value.length;
    const end = textarea.selectionEnd ?? start;
    insertAtCursor(textarea, start, end, token);
}

function renderListbox(listbox, users, activeIndex) {
    listbox.replaceChildren();

    users.forEach((user, index) => {
        const option = document.createElement('li');
        option.setAttribute('role', 'option');
        option.id = `${listbox.id}-option-${index}`;
        option.dataset.index = String(index);
        option.dataset.email = user.email;
        option.className =
            'cursor-pointer px-3 py-2 text-sm text-stone-800 hover:bg-stone-50' +
            (index === activeIndex ? ' bg-navy-50 text-navy-900' : '');
        option.setAttribute('aria-selected', index === activeIndex ? 'true' : 'false');

        const name = document.createElement('span');
        name.className = 'block font-medium';
        name.textContent = user.name;

        const email = document.createElement('span');
        email.className = 'block text-xs text-stone-500';
        email.textContent = user.email;

        option.append(name, email);
        listbox.append(option);
    });
}

function bindMentionRoot(root) {
    if (root.dataset.mentionBound === '1') {
        return;
    }
    root.dataset.mentionBound = '1';

    const textarea = root.querySelector('[data-mention-input]');
    const listbox = root.querySelector('[data-mention-listbox]');

    if (!textarea || !listbox) {
        return;
    }

    const users = parseMentionables(root);
    let filtered = [];
    let activeIndex = 0;
    let mentionRange = null;

    const closeList = () => {
        listbox.hidden = true;
        listbox.replaceChildren();
        textarea.setAttribute('aria-expanded', 'false');
        textarea.removeAttribute('aria-activedescendant');
        filtered = [];
        mentionRange = null;
        activeIndex = 0;
    };

    const openList = (matches, range) => {
        filtered = matches;
        mentionRange = range;
        activeIndex = 0;

        if (matches.length === 0) {
            closeList();
            return;
        }

        renderListbox(listbox, matches, activeIndex);
        listbox.hidden = false;
        textarea.setAttribute('aria-expanded', 'true');
        textarea.setAttribute('aria-activedescendant', `${listbox.id}-option-0`);
    };

    const selectUser = (user) => {
        if (!user || !mentionRange) {
            return;
        }

        const end = textarea.selectionStart ?? mentionRange.start + 1 + mentionRange.query.length;
        insertMention(textarea, user.name, {
            start: mentionRange.start,
            end,
        });
        closeList();
    };

    const updateFromInput = () => {
        const cursor = textarea.selectionStart ?? 0;
        const active = getActiveMention(textarea.value, cursor);

        if (!active) {
            closeList();
            return;
        }

        openList(filterMentionables(users, active.query), active);
    };

    root.querySelectorAll('[data-mention-chip]').forEach((chip) => {
        chip.addEventListener('click', () => {
            const name = chip.getAttribute('data-mention-name');
            if (!name) {
                return;
            }
            insertMention(textarea, name);
            closeList();
        });
    });

    textarea.addEventListener('input', updateFromInput);

    textarea.addEventListener('keydown', (event) => {
        if (listbox.hidden || filtered.length === 0) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeIndex = (activeIndex + 1) % filtered.length;
            renderListbox(listbox, filtered, activeIndex);
            textarea.setAttribute('aria-activedescendant', `${listbox.id}-option-${activeIndex}`);
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = (activeIndex - 1 + filtered.length) % filtered.length;
            renderListbox(listbox, filtered, activeIndex);
            textarea.setAttribute('aria-activedescendant', `${listbox.id}-option-${activeIndex}`);
            return;
        }

        if (event.key === 'Enter' || event.key === 'Tab') {
            event.preventDefault();
            selectUser(filtered[activeIndex]);
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeList();
        }
    });

    listbox.addEventListener('mousedown', (event) => {
        const option = event.target.closest('[role="option"]');
        if (!option) {
            return;
        }
        event.preventDefault();
        const index = Number(option.dataset.index);
        selectUser(filtered[index]);
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            closeList();
        }
    });
}

export function initMentions(root = document) {
    root.querySelectorAll('[data-mention-root]').forEach(bindMentionRoot);
}

function bootMentions() {
    initMentions();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootMentions);
} else {
    bootMentions();
}

document.addEventListener('livewire:navigated', bootMentions);
document.addEventListener('turbo:load', bootMentions);