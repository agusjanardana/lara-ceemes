(() => {
    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

    $('[data-sidebar-toggle]')?.addEventListener('click', () => document.body.classList.toggle('ceemes-sidebar-open'));
    $('[data-sidebar-close]')?.addEventListener('click', () => document.body.classList.remove('ceemes-sidebar-open'));
    $$('[data-dialog-open]').forEach((button) => button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.dialogOpen);
        dialog?.showModal ? dialog.showModal() : dialog?.setAttribute('open', '');
        dialog?.dispatchEvent(new Event('ceemes:dialog-open'));
    }));
    $$('[data-dialog-close]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));
    $$('dialog').forEach((dialog) => dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); }));
    $('[data-open-on-error]')?.showModal();
    $$('[data-confirm]').forEach((form) => form.addEventListener('submit', (event) => { if (!window.confirm(form.dataset.confirm || 'Lanjutkan tindakan ini?')) event.preventDefault(); }));
    $$('[data-dismiss]').forEach((button) => button.addEventListener('click', () => button.parentElement?.remove()));

    const list = $('[data-resource-list]');
    const search = $('[data-table-search]');
    const globalSearch = $('[data-global-search]');
    const filter = $('[data-table-filter]');
    const applyFilters = () => {
        if (!list) return;
        const query = (search?.value || '').trim().toLowerCase();
        const filterValue = filter?.value || '';
        let visible = 0;
        $$('[data-table-row]', list).forEach((row) => {
            const key = filter?.dataset.tableFilter;
            const match = (!query || (row.dataset.search || '').includes(query)) && (!filterValue || !key || row.dataset[key] === filterValue);
            row.hidden = !match;
            if (match) visible += 1;
        });
        const count = $('[data-visible-count]', list);
        if (count) count.textContent = String(visible);
        const noResults = $('[data-no-results]', list);
        if (noResults) noResults.hidden = visible !== 0;
    };
    search?.addEventListener('input', applyFilters);
    filter?.addEventListener('change', applyFilters);
    globalSearch?.addEventListener('input', () => { if (search) { search.value = globalSearch.value; applyFilters(); } });
    document.addEventListener('keydown', (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); (globalSearch || search)?.focus(); }
    });
    $$('[data-blueprint-select]').forEach((select) => {
        const root = select.closest('dialog') || select.closest('form') || document;
        const toggle = () => $$('[data-blueprint-fields]', root).forEach((group) => group.hidden = group.dataset.blueprintFields !== select.value);
        select.addEventListener('change', toggle); toggle();
    });
    $$('[data-field-type-select]').forEach((select) => {
        const toggle = () => {
            const form = select.closest('form');
            const sectionsConfig = $('[data-sections-config]', form);
            const entryConfig = $('[data-entry-config]', form);
            const repeaterConfig = $('[data-repeater-config]', form);
            const entryCollection = $('[data-entry-collection-config]', form);
            if (sectionsConfig) sectionsConfig.hidden = select.value !== 'sections';
            if (entryConfig) entryConfig.hidden = select.value !== 'entry';
            if (repeaterConfig) {
                repeaterConfig.hidden = select.value !== 'repeater';
                $$('input,select,textarea,button', repeaterConfig).forEach((control) => control.disabled = select.value !== 'repeater');
            }
            if (entryCollection) entryCollection.disabled = select.value !== 'entry';
        };
        select.addEventListener('change', toggle); toggle();
    });
    $$('[data-set-field-type]').forEach((button) => button.addEventListener('click', () => {
        const root = button.closest('form') || document.getElementById(button.dataset.dialogOpen);
        const select = $('[data-field-type-select]', root);
        if (select) { select.value = button.dataset.setFieldType; select.dispatchEvent(new Event('change')); }
    }));
    $$('[data-field-type-option]').forEach((option) => option.addEventListener('click', () => {
        const picker = option.closest('[data-field-type-picker]');
        const form = picker?.closest('form');
        const input = $('[data-field-type-select]', form);
        if (!input) return;
        input.value = option.dataset.fieldTypeOption;
        input.dispatchEvent(new Event('change'));
        const label = $('[data-field-type-label]', form);
        const description = $('[data-field-type-description]', form);
        if (label) label.textContent = option.dataset.label || option.dataset.fieldTypeOption;
        if (description) description.textContent = option.dataset.description || '';
        $$('[data-field-type-option]', picker).forEach((item) => item.classList.toggle('is-selected', item === option));
        picker.close();
    }));
    $$('[data-picker-search]').forEach((input) => input.addEventListener('input', () => {
        const picker = input.closest('dialog');
        const query = input.value.trim().toLowerCase();
        $$('[data-picker-item]', picker).forEach((item) => item.hidden = query !== '' && !(item.dataset.search || '').includes(query));
    }));

    $$('[data-repeater-config]').forEach((builder) => {
        const list = $('[data-repeater-schema-list]', builder);
        const template = $('[data-repeater-schema-template]', builder);
        const empty = $('[data-repeater-schema-empty]', builder);
        let activeRow = null;
        const refresh = () => {
            const rows = $$('[data-repeater-schema-row]', list);
            rows.forEach((row, index) => { const number = $('[data-schema-number]', row); if (number) number.textContent = String(index + 1); });
            if (empty) empty.hidden = rows.length !== 0;
        };
        const bindSlug = (row) => {
            const label = $('[data-schema-label]', row);
            const handle = $('[data-schema-handle]', row);
            if (!label || !handle) return;
            handle.addEventListener('input', () => handle.dataset.touched = 'true');
            label.addEventListener('input', () => {
                if (handle.dataset.touched === 'true') return;
                handle.value = label.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
            });
        };
        $$('[data-repeater-schema-row]', list).forEach(bindSlug);
        $('[data-repeater-schema-add]', builder)?.addEventListener('click', () => {
            if (!template || !list) return;
            const index = Number(builder.dataset.nextIndex || 0);
            builder.dataset.nextIndex = String(index + 1);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(index)).trim();
            const row = wrapper.firstElementChild;
            if (row) { list.append(row); bindSlug(row); refresh(); }
        });
        builder.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-repeater-schema-remove]');
            if (remove) { remove.closest('[data-repeater-schema-row]')?.remove(); refresh(); return; }
            const open = event.target.closest('[data-repeater-type-open]');
            if (open) {
                activeRow = open.closest('[data-repeater-schema-row]');
                const dialog = document.getElementById(open.dataset.repeaterTypeOpen);
                dialog?.showModal ? dialog.showModal() : dialog?.setAttribute('open', '');
            }
        });
        const picker = $('[data-repeater-type-picker]', builder.closest('form'));
        $$('[data-repeater-type-option]', picker).forEach((option) => option.addEventListener('click', () => {
            if (!activeRow) return;
            const input = $('[data-repeater-schema-type]', activeRow);
            const label = $('[data-repeater-schema-type-label]', activeRow);
            const description = $('[data-repeater-schema-type-description]', activeRow);
            if (input) input.value = option.dataset.repeaterTypeOption;
            if (label) label.textContent = option.dataset.label || option.dataset.repeaterTypeOption;
            if (description) description.textContent = option.dataset.description || '';
            option.closest('dialog')?.close();
        }));
        refresh();
    });

    $$('[data-repeater]').forEach((repeater) => {
        const list = $('[data-repeater-list]', repeater);
        const template = $('[data-repeater-template]', repeater);
        const empty = $('[data-repeater-empty]', repeater);
        const add = $('[data-repeater-add]', repeater);
        const refresh = () => {
            const rows = $$('[data-repeater-item]', list);
            rows.forEach((row, index) => { const number = $('[data-repeater-number]', row); if (number) number.textContent = String(index + 1); });
            if (empty) empty.hidden = rows.length !== 0;
            if (add) add.disabled = Number(repeater.dataset.maxRows || 0) > 0 && rows.length >= Number(repeater.dataset.maxRows);
        };
        add?.addEventListener('click', () => {
            if (!template || !list || add.disabled) return;
            const index = Number(repeater.dataset.nextIndex || 0);
            repeater.dataset.nextIndex = String(index + 1);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(index)).trim();
            if (wrapper.firstElementChild) list.append(wrapper.firstElementChild);
            refresh();
        });
        repeater.addEventListener('click', (event) => {
            const row = event.target.closest('[data-repeater-item]');
            if (!row) return;
            if (event.target.closest('[data-repeater-remove]')) {
                if ($$('[data-repeater-item]', list).length <= Number(repeater.dataset.minRows || 0)) return;
                row.remove();
            } else if (event.target.closest('[data-repeater-up]') && row.previousElementSibling) {
                list.insertBefore(row, row.previousElementSibling);
            } else if (event.target.closest('[data-repeater-down]') && row.nextElementSibling) {
                list.insertBefore(row.nextElementSibling, row);
            } else return;
            refresh();
        });
        refresh();
    });

    $$('[data-entry-picker]').forEach((picker) => {
        const collection = $('[data-entry-picker-collection]', picker);
        const searchInput = $('[data-entry-picker-search]', picker);
        const choices = $$('[data-entry-choice]', picker);
        const filter = () => {
            const collectionValue = collection?.value || '';
            const query = (searchInput?.value || '').trim().toLowerCase();
            let visible = 0;
            $$('[data-entry-option]', picker).forEach((item) => {
                const match = collectionValue !== '' && item.dataset.collection === collectionValue && (query === '' || (item.dataset.search || '').includes(query));
                item.hidden = !match;
                if (match) visible += 1;
            });
            const empty = $('[data-entry-picker-empty]', picker);
            if (empty) { empty.hidden = visible !== 0; empty.textContent = collectionValue === '' ? 'Pilih Collection terlebih dahulu.' : 'Tidak ada Entry yang cocok.'; }
        };
        const updateCount = () => {
            const count = $('[data-entry-selected-count]', picker);
            if (count) count.textContent = String(choices.filter((choice) => choice.checked).length);
        };
        collection?.addEventListener('change', () => {
            if (!collection.hasAttribute('data-locked')) choices.forEach((choice) => { choice.checked = false; });
            updateCount(); filter();
        });
        searchInput?.addEventListener('input', filter);
        choices.forEach((choice) => choice.addEventListener('change', updateCount));
        picker.addEventListener('ceemes:dialog-open', () => {
            const field = picker.previousElementSibling;
            const selectedValues = $$('input[type="hidden"]', $('[data-entry-picker-inputs]', field)).map((input) => input.value);
            choices.forEach((choice) => { choice.checked = selectedValues.includes(choice.value); });
            if (collection && !collection.hasAttribute('data-locked') && selectedValues.length > 0) {
                const selectedChoice = choices.find((choice) => choice.checked);
                if (selectedChoice) collection.value = selectedChoice.closest('[data-entry-option]')?.dataset.collection || '';
            }
            filter(); updateCount();
        });
        $('[data-entry-picker-apply]', picker)?.addEventListener('click', () => {
            const field = picker.previousElementSibling;
            const inputs = $('[data-entry-picker-inputs]', field);
            const values = $('[data-entry-picker-values]', field);
            const selected = choices.filter((choice) => choice.checked);
            if (inputs) {
                inputs.replaceChildren(...selected.map((choice) => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden'; hidden.name = picker.dataset.inputName; hidden.value = choice.value;
                    return hidden;
                }));
            }
            if (values) {
                if (selected.length === 0) values.innerHTML = '<span class="is-placeholder">Belum ada Entry dipilih</span>';
                else values.replaceChildren(...selected.map((choice) => {
                    const chip = document.createElement('span');
                    chip.textContent = choice.dataset.label || choice.value;
                    const small = document.createElement('small'); small.textContent = choice.dataset.collectionLabel || ''; chip.appendChild(small);
                    return chip;
                }));
            }
            picker.close();
        });
        filter(); updateCount();
    });
    $$('[data-slug-source]').forEach((input) => input.addEventListener('input', () => {
        const target = document.querySelector(input.dataset.slugSource);
        if (!target || target.dataset.touched === 'true') return;
        target.value = input.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        const uriTarget = target.dataset.uriSource ? document.querySelector(target.dataset.uriSource) : null;
        if (uriTarget && uriTarget.dataset.touched !== 'true') uriTarget.value = `/${target.value}`;
    }));
    $$('[data-slug-target]').forEach((input) => input.addEventListener('input', () => input.dataset.touched = 'true'));
    $$('[data-uri-source]').forEach((input) => input.addEventListener('input', () => {
        const target = document.querySelector(input.dataset.uriSource);
        if (!target || target.dataset.touched === 'true') return;
        target.value = `/${input.value.replace(/^\/+|\/+$/g, '')}`;
    }));
    $$('[data-uri-target]').forEach((input) => input.addEventListener('input', () => input.dataset.touched = 'true'));
})();
