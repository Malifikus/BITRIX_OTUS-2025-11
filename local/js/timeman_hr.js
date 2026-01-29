// Timeman HR Analytics - ФИНАЛЬНЫЙ КОД С ПРОВЕРКОЙ ДЛИНЫ ОТЧЕТА
(function() {
    'use strict';

    const TOTAL_HOURS_LIMIT = 8;
    const MIN_REPORT_CHARS = 50; // Минимальное количество символов в отчете
    const WEBHOOK_URL = 'https://cw976115.tw1.ru/rest/1/afira57gv1j0pm82/';
    let activeSuggestionsBox = null;

    // ==================== ОСНОВНАЯ ИНИЦИАЛИЗАЦИЯ ====================
    function init() {
        console.log('[HR Analytics] Инициализация...');
        setupPopupObserver();
        restoreRecentEmployees();
        setupAjaxFormHandler();
    }

    function restoreRecentEmployees() {
        window.recentEmployees = JSON.parse(localStorage.getItem('hr_recent_employees') || '[]');
    }

    // ==================== ОСНОВНОЙ НАБЛЮДАТЕЛЬ ====================
    function setupPopupObserver() {
        const observer = new MutationObserver(() => {
            const popup = document.querySelector('.popup-window.--open');
            if (popup && !popup.classList.contains('hr-fields-added')) {
                console.log('[HR Analytics] Попап найден через observer');
                addHrFieldsToPopup(popup);
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    // ==================== ОБРАБОТКА AJAX-ФОРМ ====================
    function setupAjaxFormHandler() {
        console.log('[HR Analytics] Настраиваем обработку AJAX-форм...');
        
        // 1. Перехват AJAX-запросов Bitrix24
        if (typeof BX !== 'undefined' && BX.ajax && BX.ajax.submit) {
            const originalSubmit = BX.ajax.submit;
            BX.ajax.submit = function(form, options) {
                const result = originalSubmit.call(this, form, options);
                
                setTimeout(() => {
                    const popup = document.querySelector('.popup-window.--open');
                    if (popup && !popup.classList.contains('hr-fields-added')) {
                        console.log('[HR Analytics] Форма через AJAX');
                        addHrFieldsToPopup(popup);
                    }
                }, 800);
                
                return result;
            };
        }
        
        // 2. Наблюдатель для динамического контента
        const ajaxObserver = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) {
                            const popup = node.querySelector ? 
                                node.querySelector('.popup-window.--open') : 
                                (node.classList && node.classList.contains('popup-window') && 
                                 node.classList.contains('--open') ? node : null);
                            
                            if (popup && !popup.classList.contains('hr-fields-added')) {
                                console.log('[HR Analytics] Форма через AJAX observer');
                                addHrFieldsToPopup(popup);
                            }
                        }
                    });
                }
            });
        });
        
        ajaxObserver.observe(document.body, { childList: true, subtree: true });
        
        // 3. Fallback: периодическая проверка
        let checkCount = 0;
        const checkInterval = setInterval(() => {
            const popup = document.querySelector('.popup-window.--open');
            if (popup && !popup.classList.contains('hr-fields-added')) {
                console.log('[HR Analytics] Форма через интервал');
                addHrFieldsToPopup(popup);
            }
            
            checkCount++;
            if (checkCount > 15) clearInterval(checkInterval);
        }, 500);
    }

    // ==================== ДОБАВЛЕНИЕ ПОЛЕЙ В ФОРМУ ====================
    function addHrFieldsToPopup(popup) {
        const oldContainer = popup.querySelector('.hr-fields-container');
        if (oldContainer) oldContainer.remove();

        const container = document.createElement('div');
        container.className = 'hr-fields-container';
        container.style.cssText = 'width: 100%; max-width: 100%; box-sizing: border-box;';

        container.innerHTML = `
            <div style="margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e5e9; width: 100%; box-sizing: border-box;">
                <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 15px; font-weight: 600; color: #2c3e50;">Работа с сотрудниками</div>
                    <div style="font-size: 13px; color: #6c757d;">
                        Часы: <span id="total-hrs" style="font-weight: 700;">0</span>/<span style="font-weight: 600; color: #28a745;">${TOTAL_HOURS_LIMIT}</span>
                    </div>
                </div>
                
                <div id="hr-rows-container">
                    <!-- Строки добавляются через JS -->
                </div>
                
                <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" id="add-hr-row-btn" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">+ Добавить сотрудника</button>
                    <div id="hours-warning" style="display: none; font-size: 11px; color: #dc2626; padding: 4px 8px; background: #fef2f2; border-radius: 3px; border: 1px solid #fecaca;">⚠️ Превышен лимит ${TOTAL_HOURS_LIMIT} часов</div>
                </div>
                
                <div id="report-length-status" style="margin-top: 10px; padding: 8px; border-radius: 4px; font-size: 12px; text-align: center; display: none;">
                    <!-- Статус длины отчета появится здесь -->
                </div>
            </div>
        `;

        const buttons = popup.querySelector('.popup-window-buttons');
        if (buttons && buttons.parentNode) {
            buttons.parentNode.insertBefore(container, buttons);
            popup.classList.add('hr-fields-added');
            
            initHrRowsContainer();
            attachAddRowListener();
            updateTotalHours();
            
            // Патчим обе кнопки
            setTimeout(() => {
                patchSaveButton();   // Кнопка сохранения
                patchSendButton();   // Кнопка отправки руководителю
                setupEditorChangeObserver(); // Наблюдатель за изменениями
                checkReportLengthAndBlockButtons(); // Первая проверка
            }, 300);
            
            console.log('[HR Analytics] Поля добавлены в форму');
        }
    }

    // ==================== ПАТЧ ШТАТНОЙ КНОПКИ СОХРАНЕНИЯ ====================
    function patchSaveButton() {
        const saveBtn = document.getElementById('tm-work-report-save');
        if (!saveBtn) return;
        if (saveBtn.dataset.hrPatched) return;
        
        console.log('[HR Analytics] Патчим кнопку сохранения');
        
        const originalClick = saveBtn.onclick;
        
        saveBtn.onclick = function(event) {
            console.log('💾 КНОПКА СОХРАНЕНИЯ НАЖАТА!');
            
            // Проверяем длину отчета
            if (!checkReportLengthAndBlockButtons()) {
                alert(`❌ Отчет слишком короткий! Нужно минимум ${MIN_REPORT_CHARS} символов.`);
                return false; // Блокируем отправку
            }
            
            const hrData = collectHrFormData();
            console.log('HR данных для сохранения:', hrData.length);
            
            if (hrData.length > 0) {
                console.log('Сохраняем HR данные в смарт-процесс...');
                saveHrDataToSmartProcess(hrData);
            }
            
            if (originalClick) {
                return originalClick.call(this, event);
            }
            
            return true;
        };
        
        saveBtn.dataset.hrPatched = 'true';
    }

    // ==================== ПАТЧ КНОПКИ ОТПРАВКИ РУКОВОДИТЕЛЮ ====================
    function patchSendButton() {
        const sendBtn = document.getElementById('tm-work-report-send');
        if (!sendBtn) {
            console.log('[HR Analytics] Кнопка tm-work-report-send не найдена');
            return;
        }
        
        if (sendBtn.dataset.hrSendPatched) {
            return;
        }
        
        console.log('[HR Analytics] Патчим кнопку отправки руководителю');
        
        const originalClick = sendBtn.onclick;
        
        sendBtn.onclick = function(event) {
            console.log('📤 КНОПКА ОТПРАВКИ РУКОВОДИТЕЛЮ НАЖАТА!');
            
            // Проверяем длину отчета
            if (!checkReportLengthAndBlockButtons()) {
                alert(`❌ Отчет слишком короткий! Нужно минимум ${MIN_REPORT_CHARS} символов.`);
                return false; // Блокируем отправку
            }
            
            const hrData = collectHrFormData();
            console.log('HR данных для сохранения:', hrData.length);
            
            if (hrData.length > 0) {
                console.log('Сохраняем HR данные перед отправкой...');
                saveHrDataToSmartProcess(hrData);
            }
            
            if (originalClick) {
                return originalClick.call(this, event);
            }
            
            return true;
        };
        
        sendBtn.dataset.hrSendPatched = 'true';
    }

    // ==================== ПРОВЕРКА И БЛОКИРОВКА КНОПОК ====================
    function checkReportLengthAndBlockButtons() {
        const sendBtn = document.getElementById('tm-work-report-send');
        const saveBtn = document.getElementById('tm-work-report-save');
        
        if (!sendBtn && !saveBtn) return false;
        
        // 1. Получаем длину текста отчета
        const reportLength = getReportTextLength();
        const isValid = reportLength >= MIN_REPORT_CHARS;
        
        // 2. Обновляем статус
        updateLengthStatus(reportLength, isValid);
        
        // 3. Блокируем/разблокируем кнопки
        if (sendBtn) {
            sendBtn.disabled = !isValid;
            sendBtn.style.opacity = isValid ? '1' : '0.5';
            sendBtn.style.cursor = isValid ? 'pointer' : 'not-allowed';
            sendBtn.title = isValid ? '' : `Нужно минимум ${MIN_REPORT_CHARS} символов в отчете`;
        }
        
        if (saveBtn) {
            saveBtn.disabled = !isValid;
            saveBtn.style.opacity = isValid ? '1' : '0.5';
            saveBtn.style.cursor = isValid ? 'pointer' : 'not-allowed';
            saveBtn.title = isValid ? '' : `Нужно минимум ${MIN_REPORT_CHARS} символов в отчете`;
        }
        
        return isValid;
    }

    // Получаем длину текста отчета
    function getReportTextLength() {
        let text = '';
        const iframes = document.querySelectorAll('iframe[id*="LHE_iframe_obReportWeekly"]');
        
        if (iframes.length > 0) {
            try {
                const iframe = iframes[0];
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                text = iframeDoc.body.innerText || iframeDoc.body.textContent || '';
            } catch(e) {
                console.log('Не могу прочитать iframe для проверки длины');
            }
        }
        
        return text.trim().length;
    }

    // Обновляем отображение статуса
    function updateLengthStatus(currentLength, isValid) {
        const statusElement = document.getElementById('report-length-status');
        if (!statusElement) return;
        
        if (currentLength === 0) {
            statusElement.style.display = 'none';
            return;
        }
        
        statusElement.style.display = 'block';
        
        if (isValid) {
            statusElement.innerHTML = `
                <div style="color: #10b981; background: #f0fdf4; border: 1px solid #86efac; padding: 6px 10px; border-radius: 4px;">
                    ✅ Отчет готов к отправке: ${currentLength}/${MIN_REPORT_CHARS} символов
                </div>
            `;
        } else {
            statusElement.innerHTML = `
                <div style="color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; padding: 6px 10px; border-radius: 4px;">
                    ⚠️ Нужно еще ${MIN_REPORT_CHARS - currentLength} символов: ${currentLength}/${MIN_REPORT_CHARS}
                </div>
            `;
        }
    }

    // Наблюдаем за изменениями текста в редакторе
    function setupEditorChangeObserver() {
        // Проверяем каждые 500мс
        setInterval(() => {
            checkReportLengthAndBlockButtons();
        }, 500);
    }

    // ==================== СОХРАНЕНИЕ В СМАРТ-ПРОЦЕСС ====================
    function saveHrDataToSmartProcess(hrData) {
        if (!hrData || hrData.length === 0) return;
        
        console.log('💾 Сохраняем в смарт-процесс...');
        
        // 1. Получаем текст отчета из iframe редактора
        let mainReportText = '';
        const iframes = document.querySelectorAll('iframe[id*="LHE_iframe_obReportWeekly"]');
        
        if (iframes.length > 0) {
            const iframe = iframes[0];
            console.log('📝 Iframe редактора:', iframe.id);
            
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                const iframeBody = iframeDoc.body;
                mainReportText = iframeBody.innerText || iframeBody.textContent || '';
                console.log('📝 Текст из iframe (длина):', mainReportText.length);
            } catch(e) {
                console.log('⚠️ Не могу прочитать iframe:', e.message);
            }
        }
        
        // 2. Сохраняем каждую запись
        hrData.forEach((item, index) => {
            const fields = {
                'title': 'HR: ' + item.employee + ' (' + item.hours + 'ч)',
                'ufCrm3TypeActive': String(item.interaction || ''),
                'ufCrm3TimeSpent': String(item.hours || '0'),
                'ufCrm3TextReport': mainReportText || '',
                'ufCrm3EmployeeComment': String(item.comment || '')
            };
            
            if (item.employeeId) {
                fields['ufCrm3Employee'] = String(item.employeeId);
            }
            
            const requestData = {
                entityTypeId: "1038",
                fields: fields
            };
            
            console.log(`📤 Запись ${index + 1}:`, {
                employee: item.employee,
                hours: item.hours,
                type: item.interaction,
                reportLength: mainReportText.length
            });
            
            fetch(WEBHOOK_URL + 'crm.item.add.json', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(requestData)
            })
            .then(response => response.text())
            .then(text => {
                console.log(`📥 Ответ ${index + 1}:`, text);
            })
            .catch(error => {
                console.log(`🔴 Ошибка ${index + 1}:`, error.message);
            });
        });
    }

    // ==================== УПРАВЛЕНИЕ СТРОКАМИ ФОРМЫ ====================
    function initHrRowsContainer() {
        const container = document.getElementById('hr-rows-container');
        if (!container) return;
        addNewHrRow();
    }

    function createHrRow() {
        const row = document.createElement('div');
        row.className = 'hr-row';
        row.style.cssText = 'display: grid; grid-template-columns: minmax(120px, 2fr) 70px minmax(100px, 1.5fr) minmax(140px, 2.5fr) 32px; align-items: center; gap: 8px; padding: 8px 10px; background: white; border-radius: 6px; border: 1px solid #e1e5e9; margin-bottom: 6px; width: 100%; box-sizing: border-box;';
        
        row.innerHTML = `
            <div style="position: relative;">
                <input type="text" class="hr-employee-input" placeholder="Введите ФИО..." style="width: 100%; height: 36px; padding: 0 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; box-sizing: border-box;">
                <div class="employee-suggestions" style="display: none; position: absolute; top: 40px; left: 0; right: 0; background: white; border: 1px solid #d1d5db; border-radius: 4px; max-height: 180px; overflow-y: auto; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></div>
            </div>
            <div>
                <input type="number" class="hr-hours-input" placeholder="0" min="0" max="24" step="0.5" style="width: 100%; height: 36px; padding: 0 6px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; text-align: center; box-sizing: border-box;">
            </div>
            <div>
                <select class="hr-interaction-type" style="width: 100%; height: 36px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; color: #374151; background-color: white; box-sizing: border-box; cursor: pointer;">
                    <option value="">Тип</option>
                    <option value="Собеседование">Собеседование</option>
                    <option value="Онбординг">Онбординг</option>
                    <option value="1-on-1 встреча">1-on-1</option>
                    <option value="Оценка эффективности">Оценка</option>
                    <option value="Обучение/Коучинг">Обучение</option>
                </select>
            </div>
            <div>
                <textarea class="hr-comment-input" placeholder="Комментарий..." rows="1" style="width: 100%; height: 36px; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; resize: none; overflow-y: auto; box-sizing: border-box; line-height: 1.3;"></textarea>
            </div>
            <div style="display: flex; justify-content: center;">
                <button type="button" class="remove-hr-row-btn" style="width: 28px; height: 28px; background: none; border: none; color: #ef4444; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; border-radius: 4px;" title="Удалить">×</button>
            </div>
        `;

        const employeeInput = row.querySelector('.hr-employee-input');
        const hoursInput = row.querySelector('.hr-hours-input');
        const removeBtn = row.querySelector('.remove-hr-row-btn');

        employeeInput.addEventListener('input', (e) => handleEmployeeSearch(e.target));
        
        hoursInput.addEventListener('input', updateTotalHours);
        hoursInput.addEventListener('change', updateTotalHours);
        
        removeBtn.addEventListener('click', () => removeHrRow(row));
        
        return row;
    }

    function addNewHrRow() {
        const container = document.getElementById('hr-rows-container');
        if (!container) return;
        const row = createHrRow();
        container.appendChild(row);
        updateTotalHours();
    }

    function attachAddRowListener() {
        const addBtn = document.getElementById('add-hr-row-btn');
        if (addBtn) {
            addBtn.addEventListener('click', addNewHrRow);
        }
    }

    function removeHrRow(rowElement) {
        const rows = document.querySelectorAll('.hr-row');
        if (rows.length <= 1) {
            alert('Должна остаться хотя бы одна строка');
            return;
        }
        rowElement.remove();
        updateTotalHours();
    }

    // ==================== РАСЧЕТ ОБЩЕГО ВРЕМЕНИ ====================
    function updateTotalHours() {
        let total = 0;
        const allInputs = document.querySelectorAll('.hr-hours-input');
        
        allInputs.forEach(input => {
            const value = parseFloat(input.value);
            if (!isNaN(value) && value >= 0) total += value;
        });
        
        const totalElement = document.getElementById('total-hrs');
        const warningElement = document.getElementById('hours-warning');
        
        if (totalElement) {
            totalElement.textContent = total.toFixed(1);
            
            if (total > TOTAL_HOURS_LIMIT) {
                totalElement.style.color = '#dc2626';
                if (warningElement) warningElement.style.display = 'block';
            } else {
                totalElement.style.color = '';
                if (warningElement) warningElement.style.display = 'none';
            }
        }
    }

    // ==================== ПОИСК СОТРУДНИКОВ ====================
    function handleEmployeeSearch(inputElement) {
        const query = inputElement.value.trim();
        const suggestionsBox = inputElement.parentNode.querySelector('.employee-suggestions');
        
        if (activeSuggestionsBox && activeSuggestionsBox !== suggestionsBox) {
            activeSuggestionsBox.style.display = 'none';
        }
        activeSuggestionsBox = suggestionsBox;
        
        if (!query) {
            showRecentEmployees(suggestionsBox, inputElement);
            return;
        }
        
        if (query.length < 2) {
            if (suggestionsBox) suggestionsBox.style.display = 'none';
            return;
        }
        
        searchRealEmployees(query, (suggestions) => {
            displayEmployeeSuggestions(suggestionsBox, suggestions, inputElement);
        });
    }

    function searchRealEmployees(query, callback) {
        const apiUrl = WEBHOOK_URL + 'user.search.json?FILTER[%LOGIC%]=OR&FILTER[%NAME%]=' + encodeURIComponent(query) + '&FILTER[%LAST_NAME%]=' + encodeURIComponent(query) + '&FILTER[%SECOND_NAME%]=' + encodeURIComponent(query);
        
        fetch(apiUrl)
            .then(response => {
                if (!response.ok) {
                    callback([]);
                    return;
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch(e) {
                        throw new Error('Некорректный JSON');
                    }
                });
            })
            .then(data => {
                if (data.error) {
                    callback([]);
                    return;
                }
                
                const suggestions = (data.result || []).map(user => {
                    const nameParts = [];
                    if (user.LAST_NAME) nameParts.push(user.LAST_NAME);
                    if (user.NAME) nameParts.push(user.NAME);
                    if (user.SECOND_NAME) nameParts.push(user.SECOND_NAME);
                    const fullName = nameParts.join(' ') || user.EMAIL || 'Без имени';
                    
                    let initials = '';
                    if (user.LAST_NAME && user.NAME) {
                        initials = (user.LAST_NAME[0] + user.NAME[0]).toUpperCase();
                    } else if (user.NAME) {
                        initials = user.NAME[0].toUpperCase();
                    } else if (user.EMAIL) {
                        initials = user.EMAIL[0].toUpperCase();
                    } else {
                        initials = '?';
                    }
                    
                    const colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316'];
                    const colorIndex = Math.abs(user.ID) % colors.length;
                    
                    return {
                        id: user.ID,
                        name: fullName,
                        position: user.WORK_POSITION || '',
                        initials: initials,
                        avatarColor: colors[colorIndex]
                    };
                });
                
                suggestions.sort((a, b) => a.name.localeCompare(b.name));
                callback(suggestions);
            })
            .catch(error => {
                console.error('Ошибка поиска сотрудников:', error);
                callback([]);
            });
    }

    function displayEmployeeSuggestions(container, suggestions, inputElement) {
        if (!container) return;
        container.innerHTML = '';
        
        if (suggestions.length === 0) {
            container.innerHTML = '<div style="padding: 10px; color: #666; font-size: 13px; text-align: center;">Сотрудники не найдены</div>';
            container.style.display = 'block';
            return;
        }
        
        suggestions.forEach(employee => {
            const div = document.createElement('div');
            div.style.cssText = 'padding: 8px 10px; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-size: 13px;';
            
            div.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: ${employee.avatarColor}; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; flex-shrink: 0;">${employee.initials}</div>
                    <div>
                        <div style="font-weight: 500;">${employee.name}</div>
                        ${employee.position ? `<div style="font-size: 11px; color: #666;">${employee.position}</div>` : ''}
                    </div>
                </div>
            `;
            
            div.addEventListener('click', () => {
                inputElement.value = employee.name;
                inputElement.setAttribute('data-employee-id', employee.id);
                container.style.display = 'none';
                addToRecentEmployees(employee);
            });
            
            container.appendChild(div);
        });
        
        container.style.display = 'block';
    }

    function showRecentEmployees(container, inputElement) {
        if (!window.recentEmployees || window.recentEmployees.length === 0) {
            if (container) container.style.display = 'none';
            return;
        }
        
        const recent = window.recentEmployees.slice(0, 5).map(emp => ({
            ...emp,
            isRecent: true
        }));
        
        displayEmployeeSuggestions(container, recent, inputElement);
    }

    function addToRecentEmployees(employee) {
        if (!employee || !employee.id) return;
        
        window.recentEmployees = window.recentEmployees.filter(e => e.id !== employee.id);
        window.recentEmployees.unshift({
            id: employee.id,
            name: employee.name,
            position: employee.position || '',
            initials: employee.initials || '',
            avatarColor: employee.avatarColor || '#3b82f6',
            timestamp: Date.now()
        });
        window.recentEmployees = window.recentEmployees.slice(0, 5);
        localStorage.setItem('hr_recent_employees', JSON.stringify(window.recentEmployees));
    }

    // ==================== СОБИРАЕМ ДАННЫХ ИЗ ФОРМЫ ====================
    function collectHrFormData() {
        const rows = document.querySelectorAll('.hr-row');
        const formData = [];
        
        rows.forEach(row => {
            const employeeInput = row.querySelector('.hr-employee-input');
            const hoursInput = row.querySelector('.hr-hours-input');
            const interactionSelect = row.querySelector('.hr-interaction-type');
            const commentTextarea = row.querySelector('.hr-comment-input');
            
            if (employeeInput && employeeInput.value.trim()) {
                formData.push({
                    employee: employeeInput.value.trim(),
                    employeeId: employeeInput.getAttribute('data-employee-id') || '',
                    hours: hoursInput.value || '0',
                    interaction: interactionSelect ? interactionSelect.value : '',
                    comment: commentTextarea ? commentTextarea.value.trim() : ''
                });
            }
        });
        
        return formData;
    }

    // ==================== ЗАПУСК СКРИПТА ====================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 100);
    }

})();