(() => {
    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

    $('[data-sidebar-toggle]')?.addEventListener('click', () => document.body.classList.toggle('ceemes-sidebar-open'));
    $('[data-sidebar-close]')?.addEventListener('click', () => document.body.classList.remove('ceemes-sidebar-open'));
    $('[data-site-switch] select')?.addEventListener('change', (event) => event.target.form?.submit());
    $$('[data-nav-group]').forEach((group) => {
        const key = `ceemes-nav-${group.dataset.navGroup}`;
        const button = $('[data-nav-collapse]', group);
        const children = $('[data-nav-children]', group);
        let collapsed = false;
        try { collapsed = localStorage.getItem(key) === 'collapsed'; } catch (_) {}
        const render = () => {
            group.classList.toggle('is-collapsed', collapsed);
            if (children) children.hidden = collapsed;
            button?.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        };
        button?.addEventListener('click', () => {
            collapsed = !collapsed;
            try { localStorage.setItem(key, collapsed ? 'collapsed' : 'expanded'); } catch (_) {}
            render();
        });
        render();
    });
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
    $$('[data-dynamic-select]').forEach((select) => {
        const root = select.closest('dialog') || select.closest('form') || document;
        const toggle = () => $$('[data-dynamic-fields]', root).forEach((group) => group.hidden = group.dataset.dynamicFields !== select.value);
        select.addEventListener('change', toggle); toggle();
    });
    $$('[data-config-tabs]').forEach((tabs) => {
        const buttons = $$('[data-config-tab]', tabs);
        const panels = $$('[data-config-panel]', tabs);
        buttons.forEach((button) => button.addEventListener('click', () => {
            buttons.forEach((item) => item.classList.toggle('is-active', item === button));
            panels.forEach((panel) => { panel.hidden = panel.dataset.configPanel !== button.dataset.configTab; });
        }));

        const enabled = $('[data-visibility-enabled]', tabs);
        const operator = $('[data-visibility-operator]', tabs);
        const source = $('[data-visibility-source]', tabs);
        const value = $('[data-visibility-value]', tabs);
        const datalist = $('[data-visibility-options]', tabs);
        const syncVisibilitySettings = () => {
            $$('[data-visibility-setting]', tabs).forEach((field) => field.classList.toggle('is-disabled', !enabled?.checked));
            if (value) value.hidden = !enabled?.checked || !['equals', 'not_equals', 'contains', 'not_contains'].includes(operator?.value || '');
            if (datalist && source) {
                let options = {};
                try { options = JSON.parse(source.selectedOptions[0]?.dataset.options || '{}'); } catch (_) {}
                datalist.replaceChildren(...Object.entries(options).map(([optionValue, optionLabel]) => {
                    const option = document.createElement('option');
                    option.value = optionValue; option.label = typeof optionLabel === 'string' ? optionLabel : optionValue;
                    return option;
                }));
            }
        };
        enabled?.addEventListener('change', syncVisibilitySettings);
        operator?.addEventListener('change', syncVisibilitySettings);
        source?.addEventListener('change', syncVisibilitySettings);
        syncVisibilitySettings();
    });
    $$('[data-field-type-select]').forEach((select) => {
        const toggle = () => {
            const form = select.closest('form');
            const sectionsConfig = $('[data-sections-config]', form);
            const contentConfig = $('[data-content-config]', form);
            const repeaterConfig = $('[data-repeater-config]', form);
            const contentSet = $('[data-content-set-config]', form);
            if (sectionsConfig) sectionsConfig.hidden = select.value !== 'sections';
            if (contentConfig) contentConfig.hidden = select.value !== 'content';
            if (repeaterConfig) {
                repeaterConfig.hidden = select.value !== 'repeater';
                $$('input,select,textarea,button', repeaterConfig).forEach((control) => control.disabled = select.value !== 'repeater');
            }
            if (contentSet) contentSet.disabled = select.value !== 'content';
            $$('[data-validation-for]', form).forEach((group) => {
                group.hidden = !(group.dataset.validationFor || '').split(' ').includes(select.value);
            });
            $$('input[name="config[after_or_equal]"],input[name="config[before_or_equal]"]', form).forEach((input) => {
                input.type = select.value === 'datetime' ? 'datetime-local' : 'date';
            });
        };
        select.addEventListener('change', toggle); toggle();
    });

    const conditionalValue = (field) => {
        if (!field) return null;
        const controls = $$('input,select,textarea', field).filter((control) => !control.disabled && control.name && !control.name.startsWith('picker-') && !control.matches('[type="button"],[type="submit"]'));
        const checkbox = controls.find((control) => control.type === 'checkbox');
        if (checkbox) return checkbox.checked ? checkbox.value : '0';
        const select = controls.find((control) => control.tagName === 'SELECT');
        if (select) return select.multiple ? [...select.selectedOptions].map((option) => option.value) : select.value;
        const values = controls.filter((control) => control.type !== 'hidden' || !controls.some((other) => other !== control && other.name === control.name && other.type === 'checkbox')).map((control) => control.value).filter((value) => value !== '');
        return values.length > 1 ? values : (values[0] ?? '');
    };
    const conditionalMatch = (actual, operator, expected) => {
        const values = Array.isArray(actual) ? actual.map(String) : [String(actual ?? '')];
        const empty = actual === null || actual === '' || (Array.isArray(actual) && actual.length === 0);
        if (operator === 'empty') return empty;
        if (operator === 'equals') return values.length === 1 && values[0] === expected;
        if (operator === 'not_equals') return !(values.length === 1 && values[0] === expected);
        if (operator === 'contains') return Array.isArray(actual) ? values.includes(expected) : values[0].includes(expected);
        if (operator === 'not_contains') return Array.isArray(actual) ? !values.includes(expected) : !values[0].includes(expected);
        if (operator === 'truthy') return ['1', 'true', 'yes', 'on'].includes(values[0].toLowerCase());
        if (operator === 'falsy') return !['1', 'true', 'yes', 'on'].includes(values[0].toLowerCase());
        return !empty;
    };
    const refreshConditionalFields = (scope = document) => {
        $$('[data-conditional-field]', scope).forEach((field) => {
            const root = field.closest('.ceemes-field-stack') || field.closest('form') || scope;
            const source = $$('[data-field-handle]', root).find((candidate) => candidate.dataset.fieldHandle === field.dataset.visibilitySource);
            const visible = conditionalMatch(conditionalValue(source), field.dataset.visibilityOperator || 'filled', field.dataset.visibilityValue || '');
            field.hidden = !visible;
            field.setAttribute('aria-hidden', visible ? 'false' : 'true');
            $$('[required]', field).forEach((control) => { control.dataset.conditionRequired = 'true'; });
            $$('[data-condition-required]', field).forEach((control) => { control.required = visible; });
        });
    };
    document.addEventListener('input', (event) => refreshConditionalFields(event.target.closest('form') || document));
    document.addEventListener('change', (event) => refreshConditionalFields(event.target.closest('form') || document));
    refreshConditionalFields();

    $$('select[name="template_mode"]').forEach((mode) => {
        const form = mode.closest('form');
        const customField = $('[data-resource-field="template"]', form);
        const routeField = $('[data-resource-field="route"]', form);
        const publishableField = $('[data-resource-field="is_publishable"]', form);
        const customInput = $('[name="template"]', customField);
        const sync = () => {
            const isCustom = mode.value === 'custom';
            const isDataOnly = mode.value === 'none';
            if (customField) customField.hidden = !isCustom;
            if (customInput) customInput.disabled = !isCustom;
            if (routeField) routeField.hidden = isDataOnly;
            if (publishableField) publishableField.hidden = isDataOnly;
        };
        mode.addEventListener('change', sync);
        sync();
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

    $$('[data-content-picker]').forEach((picker) => {
        const set = $('[data-content-picker-set]', picker);
        const searchInput = $('[data-content-picker-search]', picker);
        const choices = $$('[data-content-choice]', picker);
        const filter = () => {
            const setValue = set?.value || '';
            const query = (searchInput?.value || '').trim().toLowerCase();
            let visible = 0;
            $$('[data-content-option]', picker).forEach((item) => {
                const match = setValue !== '' && item.dataset.set === setValue && (query === '' || (item.dataset.search || '').includes(query));
                item.hidden = !match;
                if (match) visible += 1;
            });
            const empty = $('[data-content-picker-empty]', picker);
            if (empty) { empty.hidden = visible !== 0; empty.textContent = setValue === '' ? 'Pilih Set terlebih dahulu.' : 'Tidak ada Content yang cocok.'; }
        };
        const updateCount = () => {
            const count = $('[data-content-selected-count]', picker);
            if (count) count.textContent = String(choices.filter((choice) => choice.checked).length);
        };
        set?.addEventListener('change', () => {
            if (!set.hasAttribute('data-locked')) choices.forEach((choice) => { choice.checked = false; });
            updateCount(); filter();
        });
        searchInput?.addEventListener('input', filter);
        choices.forEach((choice) => choice.addEventListener('change', updateCount));
        picker.addEventListener('ceemes:dialog-open', () => {
            const field = picker.previousElementSibling;
            const selectedValues = $$('input[type="hidden"]', $('[data-content-picker-inputs]', field)).map((input) => input.value);
            choices.forEach((choice) => { choice.checked = selectedValues.includes(choice.value); });
            if (set && !set.hasAttribute('data-locked') && selectedValues.length > 0) {
                const selectedChoice = choices.find((choice) => choice.checked);
                if (selectedChoice) set.value = selectedChoice.closest('[data-content-option]')?.dataset.set || '';
            }
            filter(); updateCount();
        });
        $('[data-content-picker-apply]', picker)?.addEventListener('click', () => {
            const field = picker.previousElementSibling;
            const inputs = $('[data-content-picker-inputs]', field);
            const values = $('[data-content-picker-values]', field);
            const selected = choices.filter((choice) => choice.checked);
            if (inputs) {
                inputs.replaceChildren(...selected.map((choice) => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden'; hidden.name = picker.dataset.inputName; hidden.value = choice.value;
                    return hidden;
                }));
            }
            if (values) {
                if (selected.length === 0) values.innerHTML = '<span class="is-placeholder">Belum ada Content dipilih</span>';
                else values.replaceChildren(...selected.map((choice) => {
                    const chip = document.createElement('span');
                    chip.textContent = choice.dataset.label || choice.value;
                    const small = document.createElement('small'); small.textContent = choice.dataset.setLabel || ''; chip.appendChild(small);
                    return chip;
                }));
            }
            picker.close();
            field?.dispatchEvent(new Event('change', { bubbles: true }));
        });
        filter(); updateCount();
    });
    $$('[data-relation-picker]').forEach((picker) => {
        const choices = $$('[data-relation-choice]', picker);
        const updateCount = () => {
            const count = $('[data-relation-selected-count]', picker);
            if (count) count.textContent = String(choices.filter((choice) => choice.checked).length);
        };
        choices.forEach((choice) => choice.addEventListener('change', updateCount));
        picker.addEventListener('ceemes:dialog-open', () => {
            const field = picker.previousElementSibling;
            const inputs = $('[data-relation-picker-inputs]', field);
            const selectedValues = $$('input[type="hidden"]', inputs).map((input) => input.value);
            choices.forEach((choice) => { choice.checked = selectedValues.includes(choice.value); });
            updateCount();
        });
        $('[data-relation-picker-apply]', picker)?.addEventListener('click', () => {
            const field = picker.previousElementSibling;
            const inputs = $('[data-relation-picker-inputs]', field);
            const values = $('[data-relation-picker-values]', field);
            const selected = choices.filter((choice) => choice.checked);
            if (inputs) inputs.replaceChildren(...selected.map((choice) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden'; hidden.name = picker.dataset.inputName; hidden.value = choice.value;
                return hidden;
            }));
            if (values) {
                if (selected.length === 0) {
                    const placeholder = document.createElement('span');
                    placeholder.className = 'is-placeholder';
                    placeholder.textContent = picker.dataset.emptyLabel || 'Belum ada pilihan';
                    values.replaceChildren(placeholder);
                } else values.replaceChildren(...selected.map((choice) => {
                    const chip = document.createElement('span');
                    chip.textContent = choice.dataset.label || choice.value;
                    const small = document.createElement('small');
                    small.textContent = choice.dataset.meta || '';
                    chip.appendChild(small);
                    return chip;
                }));
            }
            picker.close();
            field?.dispatchEvent(new Event('change', { bubbles: true }));
        });
        updateCount();
    });
    $$('[data-media-picker-search]').forEach((searchInput) => {
        const picker = searchInput.closest('[data-media-picker]');
        const type = $('[data-media-picker-type]', picker);
        const folder = $('[data-media-picker-folder]', picker);
        const filterMedia = () => {
            const query = searchInput.value.trim().toLowerCase();
            $$('[data-media-picker-option]', picker).forEach((item) => {
                item.hidden = (query !== '' && !(item.dataset.search || '').includes(query))
                    || (type?.value && item.dataset.mediaType !== type.value)
                    || (folder?.value && item.dataset.mediaFolder !== folder.value);
            });
        };
        searchInput.addEventListener('input', filterMedia);
        type?.addEventListener('change', filterMedia);
        folder?.addEventListener('change', filterMedia);
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
