// Character counters for textareas that declare a maxlength.
document.addEventListener('input', (event) => {
    const field = event.target;

    if (!(field instanceof HTMLTextAreaElement) || !field.maxLength || field.maxLength < 0) {
        return;
    }

    const counter = document.querySelector(`[data-counter-for="${field.name}"]`);

    if (counter) {
        counter.textContent = field.value.length;
    }
});

document.querySelectorAll('[data-leadership-toggle]').forEach((toggle) => {
    if (!(toggle instanceof HTMLInputElement)) {
        return;
    }

    const form = toggle.closest('form');
    const fields = form?.querySelector('[data-leadership-fields]');

    if (!(form instanceof HTMLFormElement) || !(fields instanceof HTMLElement)) {
        return;
    }

    const syncLeadershipFields = () => {
        const hasLeadership = form.querySelector('input[name="has_leadership"]:checked')?.value === 'yes';

        fields.classList.toggle('hidden', !hasLeadership);
        fields.querySelectorAll('input, textarea, select').forEach((field) => {
            if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement)) {
                return;
            }

            const isRequired = field.name === 'leadership_organization'
                || field.name === 'leadership_position'
                || field.name === 'leadership_description';

            field.disabled = !hasLeadership;
            field.required = hasLeadership && isRequired;
        });
    };

    toggle.addEventListener('change', syncLeadershipFields);
    syncLeadershipFields();
});

document.querySelectorAll('[data-qualification-select]').forEach((select) => {
    if (!(select instanceof HTMLSelectElement)) {
        return;
    }

    const form = select.closest('form');
    const fields = form?.querySelector('[data-qualification-other]');
    const input = fields?.querySelector('[data-qualification-other-input]');

    if (!(fields instanceof HTMLElement) || !(input instanceof HTMLInputElement)) {
        return;
    }

    const syncOtherQualification = () => {
        const isOther = select.value === 'Other';

        fields.classList.toggle('hidden', !isOther);
        input.disabled = !isOther;
        input.required = isOther;
    };

    select.addEventListener('change', syncOtherQualification);
    syncOtherQualification();
});

const menuToggle = document.querySelector('[data-mobile-menu-toggle]');
const primaryNavigation = document.querySelector('#primary-navigation');

if (menuToggle instanceof HTMLButtonElement && primaryNavigation instanceof HTMLElement) {
    const closeNavigation = () => {
        primaryNavigation.classList.add('hidden');
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-label', 'Open navigation menu');
    };

    menuToggle.addEventListener('click', () => {
        const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';

        primaryNavigation.classList.toggle('hidden', isExpanded);
        menuToggle.setAttribute('aria-expanded', String(!isExpanded));
        menuToggle.setAttribute('aria-label', isExpanded ? 'Open navigation menu' : 'Close navigation menu');
    });

    primaryNavigation.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeNavigation);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menuToggle.getAttribute('aria-expanded') === 'true') {
            closeNavigation();
            menuToggle.focus();
        }
    });
}

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    if (!(toggle instanceof HTMLButtonElement)) {
        return;
    }

    const password = document.getElementById(toggle.getAttribute('aria-controls'));

    if (!(password instanceof HTMLInputElement)) {
        return;
    }

    const showIcon = toggle.querySelector('[data-password-show-icon]');
    const hideIcon = toggle.querySelector('[data-password-hide-icon]');

    toggle.addEventListener('click', () => {
        const isVisible = password.type === 'text';

        password.type = isVisible ? 'password' : 'text';
        toggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
        toggle.setAttribute('aria-pressed', String(!isVisible));
        showIcon?.classList.toggle('hidden', !isVisible);
        hideIcon?.classList.toggle('hidden', isVisible);
    });
});

const confirmActions = () => {
    document.addEventListener('click', (event) => {
        const trigger = event.target instanceof Element ? event.target.closest('[data-confirm]') : null;

        if (!(trigger instanceof HTMLElement)) {
            return;
        }

        const message = trigger.dataset.confirm || 'Are you sure you want to continue?';
        const form = trigger.closest('form');

        if (trigger instanceof HTMLButtonElement && trigger.type === 'submit' && form instanceof HTMLFormElement) {
            event.preventDefault();

            if (window.confirm(message)) {
                form.requestSubmit();
            }

            return;
        }

        if (trigger instanceof HTMLAnchorElement) {
            event.preventDefault();

            if (window.confirm(message)) {
                window.location.href = trigger.href;
            }
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
            return;
        }

        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
};

confirmActions();

document.addEventListener('click', (event) => {
    const trigger = event.target instanceof Element ? event.target.closest('[data-reset-form]') : null;

    if (!(trigger instanceof HTMLElement)) {
        return;
    }

    const form = trigger.closest('form');

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    event.preventDefault();

    form.querySelectorAll('input, textarea, select').forEach((field) => {
        if (field instanceof HTMLInputElement) {
            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = false;
                return;
            }

            field.value = '';
            return;
        }

        if (field instanceof HTMLTextAreaElement) {
            field.value = '';
            return;
        }

        if (field instanceof HTMLSelectElement) {
            field.selectedIndex = 0;
        }
    });
});

const emailSubject = document.querySelector('[data-email-subject]');
const emailBody = document.querySelector('[data-email-body]');
const emailEditor = document.querySelector('[data-email-editor]');
const emailSubjectPreview = document.querySelector('[data-email-subject-preview]');
const emailBodyPreview = document.querySelector('[data-email-body-preview]');

if (emailSubject instanceof HTMLInputElement
    && emailBody instanceof HTMLTextAreaElement
    && emailEditor instanceof HTMLElement
    && emailSubjectPreview instanceof HTMLElement
    && emailBodyPreview instanceof HTMLIFrameElement) {
    const allowedTags = new Set(['A', 'B', 'BR', 'DIV', 'EM', 'I', 'LI', 'OL', 'P', 'STRONG', 'U', 'UL']);
    const blockedTags = new Set(['IFRAME', 'OBJECT', 'SCRIPT', 'STYLE', 'SVG', 'MATH']);

    const sanitizeEmailMarkup = (markup) => {
        const parsed = new DOMParser().parseFromString(markup, 'text/html');
        const cleanChildren = (parent) => {
            const fragment = document.createDocumentFragment();

            [...parent.childNodes].forEach((node) => {
                if (node.nodeType === Node.TEXT_NODE) {
                    fragment.append(document.createTextNode(node.textContent ?? ''));
                    return;
                }

                if (!(node instanceof HTMLElement) || blockedTags.has(node.tagName)) {
                    return;
                }

                const children = cleanChildren(node);

                if (!allowedTags.has(node.tagName)) {
                    fragment.append(children);
                    return;
                }

                const element = document.createElement(node.tagName.toLowerCase());

                if (node.tagName === 'A') {
                    const href = node.getAttribute('href')?.trim() ?? '';

                    if (href === '{{tracking_url}}'
                        || /^https?:\/\//i.test(href)) {
                        element.setAttribute('href', href);
                    }
                }

                element.append(children);
                fragment.append(element);
            });

            return fragment;
        };

        const container = document.createElement('div');
        container.append(cleanChildren(parsed.body));

        return container.innerHTML;
    };

    const previewBrandName = emailBodyPreview.dataset.brand ?? 'FINTEC Digital Skills Academy';
    const previewLogo = emailBodyPreview.dataset.logo ?? '';
    const previewLogoOrigin = new URL(previewLogo, window.location.origin).origin;
    const sampleValues = {
        applicant_name: 'Ada Okafor',
        application_reference: 'APP-2026-8F42K',
        position_title: 'Digital Skills Programme Officer',
        application_status: 'Under Review',
        organization_name: previewBrandName,
        tracking_url: `${window.location.origin}/track?reference=APP-2026-8F42K`,
        submitted_at: '16 Mar 2026, 10:30 AM',
    };
    const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);

    const subjectCount = document.querySelector('[data-email-subject-count]');
    const bodyCount = document.querySelector('[data-email-body-count]');
    const dirtyLabel = document.querySelector('[data-email-dirty-label]');
    const form = emailBody.closest('form');
    const editorError = document.querySelector('[data-email-editor-error]');
    const enabledToggle = document.getElementById('enabled');
    const enabledStatus = document.querySelector('[data-email-enabled-status]');
    const enabledCopy = document.querySelector('[data-email-enabled-copy]');
    const enabledIndicator = document.querySelector('[data-email-enabled-indicator]');
    let savedSelection = null;
    let hasEdited = false;

    const markUnsaved = () => {
        if (dirtyLabel instanceof HTMLElement) {
            hasEdited = true;
            dirtyLabel.innerHTML = '<span class="h-2 w-2 rounded-full bg-amber-500"></span> Unsaved changes';
            dirtyLabel.classList.remove('text-emerald-700', 'text-blue-700');
            dirtyLabel.classList.add('text-amber-700');
        }
    };

    const updateEmailPreview = () => {
        const renderSample = (content) => content.replace(/\{\{([a-z_]+)}}/g, (placeholder, key) => {
            return Object.hasOwn(sampleValues, key) ? escapeHtml(sampleValues[key]) : placeholder;
        });

        emailSubjectPreview.textContent = renderSample(emailSubject.value);
        emailBodyPreview.srcdoc = `<!doctype html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <meta http-equiv="Content-Security-Policy" content="default-src 'none'; img-src data: ${previewLogoOrigin}; style-src 'unsafe-inline'; base-uri 'none'; form-action 'none'">
                    <style>
                        * { box-sizing: border-box; }
                        body { color: #334155; font: 14px/1.65 Arial, sans-serif; margin: 0; background: #f1f5f9; overflow-wrap: anywhere; }
                        table { border-collapse: collapse; }
                        a { color: #173b73; }
                        p { margin: 0 0 16px; }
                    </style>
                </head>
                <body>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:20px 10px;">
                        <tr><td align="center">
                            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#fff;border:1px solid #dce3ed;border-radius:12px;overflow:hidden;">
                                <tr><td style="height:5px;background:#ed6a24;font-size:0;"></td></tr>
                                <tr><td style="padding:22px 26px;background:#102c55;color:#fff;font-size:15px;font-weight:bold;">
                                    <img src="${escapeHtml(previewLogo)}" width="56" height="38" alt="${escapeHtml(previewBrandName)} logo" style="display:inline-block;width:56px;height:38px;object-fit:contain;vertical-align:middle;background:#fff;border-radius:8px;margin-right:10px;">
                                    ${escapeHtml(previewBrandName)}
                                    <span style="display:block;margin:4px 0 0 46px;color:#cbd5e1;font-size:10px;font-weight:normal;letter-spacing:1px;text-transform:uppercase;">Recruitment team</span>
                                </td></tr>
                                <tr><td style="padding:28px 26px;color:#334155;font-size:14px;line-height:1.75;">
                                    <p style="margin:0 0 16px;color:#ed6a24;font-size:10px;font-weight:bold;letter-spacing:1.2px;text-transform:uppercase;">Application update</p>
                                    ${renderSample(emailBody.value)}
                                </td></tr>
                                <tr><td style="padding:16px 26px;background:#f8fafc;border-top:1px solid #e2e8f0;color:#64748b;font-size:11px;line-height:1.6;">
                                    This message was sent by ${escapeHtml(previewBrandName)} regarding your recruitment application.
                                </td></tr>
                            </table>
                        </td></tr>
                    </table>
                </body>
            </html>`;
    };

    const syncEmailEditor = (markEdited = true) => {
        emailBody.value = sanitizeEmailMarkup(emailEditor.innerHTML);

        if (subjectCount instanceof HTMLElement) {
            subjectCount.textContent = String(emailSubject.value.length);
        }

        if (bodyCount instanceof HTMLElement) {
            bodyCount.textContent = String(emailEditor.innerText.length);
        }

        if (markEdited) {
            markUnsaved();
        }

        updateEmailPreview();
    };

    emailEditor.innerHTML = sanitizeEmailMarkup(emailBody.value);
    emailBody.classList.add('hidden');
    emailBody.required = false;
    emailEditor.classList.remove('hidden');
    syncEmailEditor(false);

    emailSubject.addEventListener('input', () => {
        markUnsaved();

        if (subjectCount instanceof HTMLElement) {
            subjectCount.textContent = String(emailSubject.value.length);
        }
        updateEmailPreview();
    });

    enabledToggle?.addEventListener('change', () => {
        markUnsaved();

        if (enabledStatus instanceof HTMLElement
            && enabledCopy instanceof HTMLElement
            && enabledIndicator instanceof HTMLElement) {
            const isEnabled = enabledToggle.checked;

            enabledCopy.textContent = isEnabled ? 'Automatic delivery enabled' : 'Automatic delivery paused';
            enabledStatus.classList.toggle('bg-emerald-50', isEnabled);
            enabledStatus.classList.toggle('text-emerald-800', isEnabled);
            enabledStatus.classList.toggle('bg-slate-100', !isEnabled);
            enabledStatus.classList.toggle('text-slate-700', !isEnabled);
            enabledIndicator.classList.toggle('bg-emerald-500', isEnabled);
            enabledIndicator.classList.toggle('bg-slate-400', !isEnabled);
        }
    });

    emailEditor.addEventListener('input', () => {
        syncEmailEditor();

        if (editorError instanceof HTMLElement) {
            editorError.textContent = '';
            editorError.classList.add('hidden');
        }
    });

    emailEditor.addEventListener('paste', (event) => {
        event.preventDefault();
        document.execCommand('insertText', false, event.clipboardData?.getData('text/plain') ?? '');
    });

    document.addEventListener('selectionchange', () => {
        const selection = document.getSelection();

        if (selection?.rangeCount && emailEditor.contains(selection.anchorNode)) {
            savedSelection = selection.getRangeAt(0).cloneRange();
        }
    });

    document.querySelectorAll('[data-email-command]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.addEventListener('mousedown', (event) => event.preventDefault());
        button.addEventListener('click', () => {
            emailEditor.focus();
            document.execCommand(button.dataset.emailCommand, false);
            syncEmailEditor();
        });
    });

    document.querySelectorAll('[data-email-insert]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.addEventListener('mousedown', (event) => event.preventDefault());
        button.addEventListener('click', () => {
            if (button.dataset.emailTarget === 'subject') {
                const start = emailSubject.selectionStart;
                const end = emailSubject.selectionEnd;
                const value = emailSubject.value;
                const before = value.slice(0, start);
                const after = value.slice(end);
                const token = button.dataset.emailInsert ?? '';
                const insertion = `${before && !/\s$/.test(before) ? ' ' : ''}${token}${after && !/^\s/.test(after) ? ' ' : ''}`;

                emailSubject.focus();
                emailSubject.setRangeText(insertion, start, end, 'end');
                emailSubject.dispatchEvent(new Event('input', { bubbles: true }));
                return;
            }

            emailEditor.focus();
            const selection = document.getSelection();
            const range = savedSelection?.cloneRange() ?? document.createRange();

            if (!savedSelection) {
                range.selectNodeContents(emailEditor);
                range.collapse(false);
            }

            const startNode = range.startContainer;
            const endNode = range.endContainer;
            const before = startNode.nodeType === Node.TEXT_NODE
                ? (startNode.textContent ?? '').slice(0, range.startOffset)
                : '';
            const after = endNode.nodeType === Node.TEXT_NODE
                ? (endNode.textContent ?? '').slice(range.endOffset)
                : '';
            const token = button.dataset.emailInsert ?? '';
            const insertion = `${before && !/\s$/.test(before) ? ' ' : ''}${token}${after && !/^\s/.test(after) ? ' ' : ''}`;

            range.deleteContents();
            range.insertNode(document.createTextNode(insertion));
            range.collapse(false);
            selection?.removeAllRanges();
            selection?.addRange(range);
            savedSelection = range.cloneRange();
            syncEmailEditor();
        });
    });

    document.querySelectorAll('[data-email-preview-size]').forEach((button) => {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        button.addEventListener('click', () => {
            const isMobile = button.dataset.emailPreviewSize === 'mobile';

            document.querySelectorAll('[data-email-preview-size]').forEach((option) => {
                if (option instanceof HTMLButtonElement) {
                    const selected = option === button;
                    option.setAttribute('aria-pressed', String(selected));
                    option.classList.toggle('bg-white', selected);
                    option.classList.toggle('text-brand-blue', selected);
                    option.classList.toggle('shadow-sm', selected);
                    option.classList.toggle('text-slate-600', !selected);
                }
            });
            emailBodyPreview.style.width = isMobile ? '320px' : '100%';
            emailBodyPreview.style.maxWidth = '100%';
            emailBodyPreview.style.height = isMobile ? '560px' : '500px';
        });
    });

    form?.addEventListener('submit', (event) => {
        syncEmailEditor(false);

        if (!emailEditor.innerText.trim() || emailBody.value.length > 20000) {
            event.preventDefault();

            if (editorError instanceof HTMLElement) {
                editorError.textContent = emailEditor.innerText.trim()
                    ? 'This message is a little too long. Please shorten it before saving.'
                    : 'Add a few words to your message before saving.';
                editorError.classList.remove('hidden');
            }

            emailEditor.focus();
            return;
        }

        if (hasEdited && dirtyLabel instanceof HTMLElement) {
            dirtyLabel.innerHTML = '<span class="h-2 w-2 rounded-full bg-blue-500"></span> Saving…';
            dirtyLabel.classList.remove('text-amber-700');
            dirtyLabel.classList.add('text-blue-700');
        }
    });
}

const templateSearch = document.querySelector('[data-template-search-box]');

if (templateSearch instanceof HTMLInputElement) {
    const templateCards = [...document.querySelectorAll('[data-template-card]')];
    const visibleCount = document.querySelector('[data-template-visible-count]');
    const noResults = document.querySelector('[data-template-no-results]');
    const templateGrid = document.querySelector('[data-template-grid]');

    templateSearch.addEventListener('input', () => {
        const term = templateSearch.value.trim().toLocaleLowerCase();
        let visible = 0;

        templateCards.forEach((card) => {
            const matches = card instanceof HTMLElement
                && (card.dataset.templateSearch ?? '').toLocaleLowerCase().includes(term);

            card.classList.toggle('hidden', !matches);
            visible += Number(matches);
        });

        if (visibleCount instanceof HTMLElement) {
            visibleCount.textContent = String(visible);
        }

        noResults?.classList.toggle('hidden', visible !== 0 || term === '');
        templateGrid?.classList.toggle('hidden', visible === 0 && term !== '');
    });
}

// Skills step: add / remove free-text skill rows without a page reload.
window.addSkill = function () {
    const template = document.querySelector('[data-skill-template]');
    const list = document.querySelector('[data-skill-list]');

    if (!template || !list) {
        return;
    }

    const indexes = [...list.querySelectorAll('input')].map((input) => {
        const match = input.name.match(/\[(\d+)]/);
        return match ? Number.parseInt(match[1], 10) : -1;
    });

    const row = template.content.cloneNode(true);
    const input = row.querySelector('input');

    input.value = '';
    input.name = `skills[${Math.max(-1, ...indexes) + 1}]`;
    list.appendChild(row);
    input.focus();
};

window.removeSkill = function (button) {
    button.closest('[data-skill-row]')?.remove();
};

// Local Government Area: dynamically populate LGAs when state changes.
window.populateLGAs = function () {
    // Helper: fetch LGAs for a state and populate the target select
    async function loadLGAs(stateSelectEl, lgaSelectEl) {
        const stateId = stateSelectEl.value;
        if (!stateId) {
            lgaSelectEl.innerHTML = '<option value="">Select LGA</option>';
            return;
        }

        try {
            const response = await fetch(`/api/state/${stateId}/lgas`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Network error');

            const lgas = await response.json();
            lgaSelectEl.innerHTML = '<option value="">Select LGA</option>';
            lgas.forEach(lga => {
                const option = document.createElement('option');
                option.value = lga.id;
                option.textContent = lga.name;
                lgaSelectEl.appendChild(option);
            });
        } catch (e) {
            console.error('Failed to load LGAs:', e);
            lgaSelectEl.innerHTML = '<option value="">Error loading LGAs</option>';
        }
    }

    // --- Origin LGA ---
    const originStateSelect = document.querySelector('select[name="state_of_origin_id"]');
    const originLGASelect = document.querySelector('select[name="origin_lga_id"]');

    if (originStateSelect && originLGASelect) {
        // Initial load if already selected
        loadLGAs(originStateSelect, originLGASelect);

        originStateSelect.addEventListener('change', () => {
            loadLGAs(originStateSelect, originLGASelect);
        });
    }

    // --- Residence LGA ---
    const residenceStateSelect = document.querySelector('select[name="residence_state_id"]');
    const residenceLGASelect = document.querySelector('select[name="residence_lga_id"]');

    if (residenceStateSelect && residenceLGASelect) {
        // Initial load if already selected
        loadLGAs(residenceStateSelect, residenceLGASelect);

        residenceStateSelect.addEventListener('change', () => {
            loadLGAs(residenceStateSelect, residenceLGASelect);
        });
    }
};

// Run after DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.populateLGAs);
} else {
    window.populateLGAs();
}