/**
 * Course Builder & assessment page behaviours:
 *  - "+ Add Content" panel with content type tabs
 *  - lightweight rich text editor for Blog / Text lessons
 *  - thumbnail preview
 *  - essay rubric rows (instructor)
 *  - assessment timer, word counter and scoring overlay (student)
 */

export function initBuilder() {
    // Add-content panels
    document.querySelectorAll('[data-add-content]').forEach((wrapper) => {
        const toggle = wrapper.querySelector('[data-add-toggle]');
        const panel = wrapper.querySelector('.add-content-panel');
        if (!toggle || !panel) return;

        const setOpen = (open) => {
            panel.classList.toggle('hidden', !open);
            wrapper.classList.toggle('is-open', open);
            toggle.textContent = open ? '− Close' : '+ Add Content';
        };

        setOpen(wrapper.classList.contains('is-open'));
        toggle.addEventListener('click', () => setOpen(panel.classList.contains('hidden')));

        const tabs = wrapper.querySelectorAll('[data-tab]');
        const panes = wrapper.querySelectorAll('[data-pane]');
        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                tabs.forEach((t) => t.classList.toggle('is-active', t === tab));
                panes.forEach((pane) => pane.classList.toggle('hidden', pane.dataset.pane !== tab.dataset.tab));
            });
        });
    });

    // Thumbnail preview
    const thumbInput = document.getElementById('thumbnail');
    const thumbPreview = document.getElementById('thumbnail-preview');
    if (thumbInput && thumbPreview) {
        thumbInput.addEventListener('change', () => {
            const file = thumbInput.files?.[0];
            if (file) {
                thumbPreview.src = URL.createObjectURL(file);
                thumbPreview.classList.remove('opacity-40');
            }
        });
    }

    initRichTextEditors();
    initRubricBuilder();
}

function initRichTextEditors() {
    document.querySelectorAll('[data-rte]').forEach((editor) => {
        const area = editor.querySelector('[data-rte-area]');
        const input = editor.querySelector('[data-rte-input]');
        const form = editor.closest('form');
        if (!area || !input) return;

        const sync = () => {
            input.value = area.innerHTML.trim();
        };

        editor.querySelectorAll('.rte-toolbar button').forEach((button) => {
            button.addEventListener('mousedown', (e) => e.preventDefault()); // keep selection
            button.addEventListener('click', () => {
                area.focus();

                if (button.dataset.cmd) {
                    document.execCommand(button.dataset.cmd, false, button.dataset.value || null);
                } else if (button.dataset.action === 'link') {
                    const url = window.prompt('Link URL (https://...)');
                    if (url) document.execCommand('createLink', false, url);
                } else if (button.dataset.action === 'image') {
                    const url = window.prompt('Image URL (https://...)');
                    if (url) document.execCommand('insertImage', false, url);
                } else if (button.dataset.action === 'table') {
                    document.execCommand(
                        'insertHTML',
                        false,
                        '<table><thead><tr><th>Heading 1</th><th>Heading 2</th></tr></thead><tbody><tr><td>Cell</td><td>Cell</td></tr><tr><td>Cell</td><td>Cell</td></tr></tbody></table><p><br></p>'
                    );
                }
                sync();
            });
        });

        area.addEventListener('input', sync);
        form?.addEventListener('submit', sync);
        sync();
    });
}

function initRubricBuilder() {
    const rows = document.getElementById('rubric-rows');
    const addButton = document.getElementById('rubric-add');
    const totalLabel = document.getElementById('rubric-total');
    if (!rows || !addButton) return;

    const updateTotal = () => {
        let total = 0;
        rows.querySelectorAll('.rubric-weight').forEach((input) => {
            total += parseInt(input.value, 10) || 0;
        });
        if (totalLabel) {
            totalLabel.textContent = `Total: ${total}%`;
            totalLabel.className = `text-xs font-semibold ${total === 100 ? 'text-brand-dark' : 'text-amber-600'}`;
        }
    };

    const reindex = () => {
        rows.querySelectorAll('.rubric-row').forEach((row, index) => {
            row.querySelectorAll('input').forEach((input) => {
                input.name = input.name.replace(/rubric\[\d+\]/, `rubric[${index}]`);
            });
        });
    };

    addButton.addEventListener('click', () => {
        const count = rows.querySelectorAll('.rubric-row').length;
        if (count >= 8) return;

        const row = document.createElement('div');
        row.className = 'grid grid-cols-[1fr_5rem_2rem] sm:grid-cols-[10rem_1fr_5rem_2rem] gap-2 items-center rubric-row';
        row.innerHTML = `
            <input type="text" name="rubric[${count}][criteria]" class="dashboard-input" placeholder="Criteria">
            <input type="text" name="rubric[${count}][description]" class="dashboard-input hidden sm:block" placeholder="Description">
            <input type="number" name="rubric[${count}][weight]" min="0" max="100" class="dashboard-input rubric-weight" placeholder="%">
            <button type="button" class="rubric-remove text-gray-400 hover:text-red-500" aria-label="Remove criterion">✕</button>`;
        rows.appendChild(row);
        updateTotal();
    });

    rows.addEventListener('click', (e) => {
        const remove = e.target.closest('.rubric-remove');
        if (!remove) return;
        remove.closest('.rubric-row')?.remove();
        reindex();
        updateTotal();
    });

    rows.addEventListener('input', updateTotal);
    updateTotal();
}

export function initAssessmentPage() {
    const form = document.getElementById('assessment-form');
    if (!form) return;

    // Word counters for essay answers
    form.querySelectorAll('textarea').forEach((textarea) => {
        const counter = textarea.parentElement.querySelector('[data-word-count]');
        if (!counter) return;
        textarea.addEventListener('input', () => {
            const words = textarea.value.trim().split(/\s+/).filter(Boolean).length;
            counter.textContent = words;
        });
    });

    // Scoring overlay
    form.addEventListener('submit', () => {
        document.getElementById('scoring-overlay')?.classList.remove('hidden');
        const button = document.getElementById('submit-assessment');
        if (button) button.disabled = true;
    });

    // Countdown timer with auto-submit
    const timer = document.getElementById('assessment-timer');
    const value = document.getElementById('assessment-timer-value');
    if (!timer || !value) return;

    let remaining = parseInt(timer.dataset.remaining, 10) || 0;

    const format = (seconds) => {
        const m = Math.floor(seconds / 60).toString().padStart(2, '0');
        const s = (seconds % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    };

    const tick = () => {
        value.textContent = format(remaining);
        timer.classList.toggle('timer-warning', remaining <= 60);

        if (remaining <= 0) {
            clearInterval(interval);
            value.textContent = '00:00';
            form.requestSubmit ? form.requestSubmit() : form.submit();
            return;
        }
        remaining -= 1;
    };

    const interval = setInterval(tick, 1000);
    tick();
}
