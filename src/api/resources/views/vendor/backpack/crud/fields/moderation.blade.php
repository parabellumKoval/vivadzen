@include('crud::fields.inc.wrapper_start')
<div class="moderation-field-container">
    <div class="moderation-checkbox-container" style="display: none">
        <input
            type="checkbox"
            name="{{ $field['name'] }}"
            id="{{ $field['name'] }}"
            class="moderation-checkbox"
            {{ old($field['name']) ?? ($field['value'] ?? false) ? 'checked' : '' }}
        >
        <label for="{{ $field['name'] }}" class="rotated-label">{{ $field['label'] }}</label>
    </div>
    <div 
        class="moderation-wrapper {{ $field['wrapper_class'] ?? '' }}"
        data-wrap-items="{{ json_encode($field['wrap_items'] ?? []) }}"
        data-switch-class="{{ $field['switch_class'] ?? '' }}"
        data-enabled-when="{{ $field['enabled_when'] ?? '' }}"
    >
        <!-- Items will be wrapped here via JavaScript -->
    </div>
</div>
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <style>
            .moderation-field-container {
                display: flex;
                align-items: stretch;
                width: 100%;
                margin-bottom: 1rem;
            }
            .moderation-checkbox-container {
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 10px;
                margin-right: 15px;
                transition: all 0.3s ease;
            }
            .moderation-checkbox-container.checked {
                color: #155724;
                background-color: #d4edda;
                border-color: #c3e6cb;
            }
            .rotated-label {
                writing-mode: vertical-rl;
                transform: rotate(180deg);
                margin-top: 10px;
                text-align: center;
                white-space: nowrap;
            }
            .moderation-wrapper {
                flex-grow: 1;
                transition: all 0.3s ease;
            }

            .box-warning{
                color: #856404;
                background-color: #fff3cd;
                border-color: #ffeeba;
                padding: 20px 5px 0 5px;
            }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script>
            function initModerationField() {
                document.querySelectorAll('.moderation-field-container').forEach(container => {
                    const wrapper = container.querySelector('.moderation-wrapper');
                    const checkbox = container.querySelector('.moderation-checkbox');
                    const checkboxContainer = container.querySelector('.moderation-checkbox-container');
                    
                    if (!wrapper || !checkbox) return;

                    const wrapItems = JSON.parse(wrapper.dataset.wrapItems || '[]');
                    const switchClass = wrapper.dataset.switchClass;
                    const enabledWhen = wrapper.dataset.enabledWhen;

                    // Function to check if field is enabled based on various possible field types
                    const checkEnabled = () => {
                        if (!enabledWhen) return false;

                        // Find all possible elements that could control the enabled state
                        const hiddenField = document.querySelector(`input[type="hidden"][name="${enabledWhen}"]`);
                        const visibleCheckbox = document.querySelector(`input[type="checkbox"][name="${enabledWhen}"]`);
                        const checkboxContainer = document.querySelector(`.checkbox input[type="checkbox"][data-init-function="bpFieldInitCheckbox"]`);
                        
                        // Check hidden field value if it exists
                        if (hiddenField) {
                            return hiddenField.value === '1';
                        }
                        
                        // Check visible checkbox if it exists
                        if (visibleCheckbox) {
                            return visibleCheckbox.checked;
                        }
                        
                        // Check Backpack-style checkbox if it exists
                        if (checkboxContainer) {
                            return checkboxContainer.checked;
                        }

                        return false;
                    };

                    // Function to update moderation state
                    const updateModerationState = () => {
                        const isEnabled = checkEnabled();
                        
                        // Show/hide checkbox container
                        checkboxContainer.style.display = isEnabled ? 'flex' : 'none';
                        
                        // Toggle checked class on checkbox container
                        if (checkbox.checked) {
                            checkboxContainer.classList.add('checked');
                        } else {
                            checkboxContainer.classList.remove('checked');
                        }
                        
                        // Toggle switch class based on both conditions
                        if (isEnabled && !checkbox.checked) {
                            wrapper.classList.add(switchClass);
                        } else {
                            wrapper.classList.remove(switchClass);
                        }
                    };

                    // Find first wrap item and insert container there
                    if (wrapItems.length > 0) {
                        const firstItemName = wrapItems[0];
                        let firstField = null;

                        // Проверяем все возможные варианты для первого поля
                        const exactField = document.querySelector(`[name="${firstItemName}"]`);
                        const arrayField = document.querySelector(`[name="${firstItemName}[]"]`);
                        const dynamicField = document.querySelector(`[name^="${firstItemName}["][name$="]"]`);

                        firstField = exactField || arrayField || dynamicField;

                        if (firstField) {
                            let targetContainer = firstField.closest('.form-group');
                            while (targetContainer && targetContainer.parentElement && targetContainer.parentElement.classList.contains('form-group')) {
                                targetContainer = targetContainer.parentElement;
                            }
                            if (targetContainer && targetContainer.parentElement) {
                                targetContainer.parentElement.insertBefore(container, targetContainer);
                            }
                        }
                    }

                    // Move fields into wrapper
                    wrapItems.forEach(itemName => {
                        // Проверяем, есть ли точное совпадение
                        const escapedName = itemName.replace(/[[\]]/g, '\\$&');
                        let fields = [];
                        
                        // Если это обычное поле
                        const exactField = document.querySelector(`[name="${escapedName}"]`);
                        if (exactField) {
                            fields.push(exactField);
                        }
                        
                        // Если это поле с [] (массив)
                        const arrayFields = document.querySelectorAll(`[name="${escapedName}[]"]`);
                        if (arrayFields.length) {
                            fields = fields.concat(Array.from(arrayFields));
                        }
                        
                        // Если это поле с [number] (например props[123])
                        const dynamicFields = document.querySelectorAll(`[name^="${itemName}["][name$="]"]`);
                        if (dynamicFields.length) {
                            fields = fields.concat(Array.from(dynamicFields));
                        }

                        // Перемещаем все найденные поля
                        fields.forEach(field => {
                            let fieldContainer = field.closest('.form-group');
                            while (fieldContainer && fieldContainer.parentElement && fieldContainer.parentElement.classList.contains('form-group')) {
                                fieldContainer = fieldContainer.parentElement;
                            }
                            if (fieldContainer) {
                                wrapper.appendChild(fieldContainer);
                            }
                        });
                    });

                    // Set up event listeners
                    checkbox.addEventListener('change', updateModerationState);
                    
                    if (enabledWhen) {
                        // Watch for changes in hidden fields and checkboxes
                        const observer = new MutationObserver((mutations) => {
                            mutations.forEach((mutation) => {
                                if (mutation.type === 'attributes' || mutation.type === 'characterData') {
                                    updateModerationState();
                                }
                            });
                        });

                        // Observe changes in the parent container of the enabled_when field
                        const fieldContainer = document.querySelector(`[name="${enabledWhen}"]`)?.closest('.form-group');
                        if (fieldContainer) {
                            observer.observe(fieldContainer, {
                                attributes: true,
                                characterData: true,
                                childList: true,
                                subtree: true
                            });
                        }

                        // Also add direct event listeners for standard interactions
                        const fields = [
                            document.querySelector(`input[type="hidden"][name="${enabledWhen}"]`),
                            document.querySelector(`input[type="checkbox"][name="${enabledWhen}"]`),
                            document.querySelector(`.checkbox input[type="checkbox"][data-init-function="bpFieldInitCheckbox"]`)
                        ].filter(Boolean);

                        fields.forEach(field => {
                            field.addEventListener('change', updateModerationState);
                        });
                    }

                    // Initial state
                    updateModerationState();
                });
            }

            // Initialize on document ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initModerationField);
            } else {
                initModerationField();
            }
        </script>
    @endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
