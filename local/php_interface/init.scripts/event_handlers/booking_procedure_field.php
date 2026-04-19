<?php
use Bitrix\Main\Loader;
use CIBlockElement;

/**
 * Кастомное свойство: выбор процедуры врача
 * @package Local\Booking
 */
class BookingProcedureProperty
{
    // Регистрация типа свойства в ядре
    public static function GetUserTypeDescription()
    {
        return [
            'USER_TYPE' => 'booking_procedure',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'Процедура врача',
            'PROPERTY_TYPE' => 'N',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetPublicEditHTML' => [__CLASS__, 'GetPublicEditHTML'],
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
            'CheckFields' => [__CLASS__, 'CheckFields'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
        ];
    }

    // Генерация поля в админке
    public static function GetPropertyFieldHtml($arProperty, $value, &$strControlVariableName)
    {
        return self::renderField($arProperty, $value, $strControlVariableName);
    }

    // Публичное поле редактирования
    public static function GetPublicEditHTML($arProperty, $value, &$strHTMLControlName)
    {
        return self::renderField($arProperty, $value, $strHTMLControlName, true);
    }

    // Подготовка структуры поля
    protected static function renderField($arProperty, $value, $strControlVariableName, $isPublic = false)
    {
        \CJSCore::Init(['popup', 'ajax']);
        $procId = 'proc_' . (int)$arProperty['ID'];
        $currentVal = is_array($value) ? ($value['VALUE'] ?? '') : ($value ?: '');

        // Формирование корректного имени
        $inputName = is_array($strControlVariableName) 
            ? ($strControlVariableName['VALUE'] ?? '') 
            : (string)$strControlVariableName;
        if ($inputName === '') {
            $inputName = 'PROPERTY_' . $arProperty['ID'] . '[n0][VALUE]';
        }

        $html = '<select name="' . htmlspecialchars($inputName) . '" id="' . $procId . '" class="booking-proc-select">';
        $html .= '<option value="">-- Выберите процедуру --</option>';
        
        $doctorId = self::getDoctorId();
        if ($doctorId > 0) {
            $html .= self::buildOptionsHtml($doctorId, $currentVal);
        }
        $html .= '</select> <button type="button" class="proc-info-btn" onclick="window.showProcPopup(this)">ℹ️</button>';

        if ($isPublic) {
            $html .= '<script>
            BX.ready(function() {
                var procSelect = document.getElementById("' . $procId . '");
                if (!procSelect) return;

                var hiddenInput = document.querySelector(\'input[name="PROPERTY_72"]\');
                var visibleInput = document.querySelector(\'input[name*="asxtus_search"]\');
                var doctorSource = hiddenInput || visibleInput;
                if (!doctorSource) return;

                var getDoctorId = function() {
                    var raw = doctorSource.value || "";
                    var match = raw.match(/\[(\d+)\]/);
                    return match ? parseInt(match[1], 10) : (parseInt(raw, 10) || 0);
                };

                var loadProcedures = function(restoreVal) {
                    var docId = getDoctorId();
                    if (!docId) {
                        procSelect.innerHTML = \'<option value="">-- Выберите процедуру --</option>\';
                        return;
                    }
                    BX.ajax({
                        url: "/local/ajax/booking/get_procedures.php",
                        method: "POST",
                        data: { doctor_id: docId },
                        dataType: "json",
                        onsuccess: function(res) {
                            procSelect.innerHTML = \'<option value="">-- Выберите процедуру --</option>\';
                            if (res.success && Array.isArray(res.procedures)) {
                                res.procedures.forEach(function(p) {
                                    var opt = document.createElement("option");
                                    opt.value = p.ID;
                                    opt.text = p.NAME;
                                    opt.dataset.info = p.DESC || "";
                                    if (p.ID == restoreVal) opt.selected = true;
                                    procSelect.appendChild(opt);
                                });
                            }
                        },
                        onfailure: function() { console.error("[Booking] AJAX ошибка"); }
                    });
                };

                doctorSource.addEventListener("change", function() { loadProcedures(""); });
                doctorSource.addEventListener("input", function() { loadProcedures(""); });
                if (getDoctorId()) loadProcedures(procSelect.value);

                setInterval(function() {
                    var v = getDoctorId();
                    if (v > 0 && v != procSelect.dataset.lastDoc) {
                        procSelect.dataset.lastDoc = v;
                        loadProcedures(procSelect.value);
                    }
                }, 2000);

                window.showProcPopup = function(btn) {
                    var sel = btn.previousElementSibling;
                    if (!sel) return;
                    var opt = sel.options[sel.selectedIndex];
                    if (!opt || !opt.value) { alert("Сначала выберите процедуру"); return; }
                    
                    var name = opt.text || "Без названия";
                    var info = opt.dataset.info || "";
                    
                    var content = BX.create("div", {
                        props: { style: "padding:10px; min-width:220px;" },
                        html: "<div style=\'font-weight:600; margin-bottom:5px;\'>" + name + "</div>" +
                              "<div style=\'font-size:13px; line-height:1.4; white-space:pre-wrap;\'>" + info + "</div>"
                    });
                    
                    new BX.PopupWindow("proc_popup_" + Date.now(), btn, {
                        content: content,
                        closeIcon: true, titleBar: "Информация о процедуре",
                        autoHide: true, destroyOnClose: true
                    }).show();
                };
            });
            </script>';
        }
        return $html;
    }

    // Поиск идентификатора врача
    protected static function getDoctorId()
    {
        if (!empty($_REQUEST['PROPERTY_DOKTOR'])) return (int)$_REQUEST['PROPERTY_DOKTOR'];
        if (!empty($_REQUEST['PROPERTY_72'])) return (int)$_REQUEST['PROPERTY_72'];
        return 0;
    }

    // Загрузка процедур из базы
    protected static function buildOptionsHtml($doctorId, $currentValue)
    {
        if ($doctorId <= 0) return '';
        Loader::includeModule('iblock');
        $rs = CIBlockElement::GetList([], ['IBLOCK_ID' => 16, 'ID' => $doctorId, 'ACTIVE' => 'Y'], false, false, ['ID', 'PROPERTY_PROTSEDURY']);
        $doc = $rs->Fetch();
        if (!$doc) return '';
        
        $raw = $doc['PROPERTY_PROTSEDURY_VALUE'];
        $ids = is_array($raw) ? array_filter($raw) : [$raw];
        if (empty($ids)) return '';

        $rsP = CIBlockElement::GetList(['SORT' => 'ASC'], ['IBLOCK_ID' => 17, 'ID' => $ids, 'ACTIVE' => 'Y'], false, false, ['ID', 'NAME', 'PREVIEW_TEXT']);
        $out = '';
        while ($p = $rsP->Fetch()) {
            $sel = ($currentValue == $p['ID']) ? 'selected' : '';
            $info = htmlspecialchars($p['PREVIEW_TEXT'] ?? '');
            $out .= '<option value="' . $p['ID'] . '" data-info="' . $info . '" ' . $sel . '>' . htmlspecialchars($p['NAME']) . '</option>';
        }
        return $out;
    }

    // Проверка обязательности заполнения
    public static function CheckFields($arProperty, $value)
    {
        if (empty($arProperty['ID'])) return true;
        $val = is_array($value) ? ($value['VALUE'] ?? null) : $value;
        if (empty($val)) return ['Выберите процедуру'];
        return true;
    }

    // Преобразование перед сохранением
    public static function ConvertToDB($arProperty, $value)
    {
        $val = is_array($value) ? ($value['VALUE'] ?? null) : $value;
        return ['VALUE' => (int)$val, 'VALUE_NUM' => (int)$val];
    }

    // Вывод названия в списке
    public static function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        if (empty($value['VALUE'])) return '—';
        Loader::includeModule('iblock');
        $el = CIBlockElement::GetByID($value['VALUE'])->Fetch();
        return $el ? htmlspecialchars($el['NAME']) : '—';
    }

    // Вывод результата
    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return self::GetAdminListViewHTML($arProperty, $value, $strHTMLControlName);
    }
}

// Регистрация обработчика
AddEventHandler('iblock', 'OnIBlockPropertyBuildList', ['BookingProcedureProperty', 'GetUserTypeDescription']);
