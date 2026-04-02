<?php
use Bitrix\Main\Loader;

class BookingProcedurePropertyType
{
    const PROCEDURES_IBLOCK_ID = 17;

    public static function GetUserTypeDescription()
    {
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'booking_procedure',
            'DESCRIPTION' => 'Выбор процедуры врача',
            'GetPropertyFieldHtml' => ['BookingProcedurePropertyType', 'GetPropertyFieldHtml'],
            'GetAdminListViewHTML' => ['BookingProcedurePropertyType', 'GetAdminListViewHTML'],
            'GetPublicViewHTML' => ['BookingProcedurePropertyType', 'GetPublicViewHTML'],
            'GetSearchContent' => ['BookingProcedurePropertyType', 'GetSearchContent'],
            'ConvertToDB' => ['BookingProcedurePropertyType', 'ConvertToDB'],
            'ConvertFromDB' => ['BookingProcedurePropertyType', 'ConvertFromDB'],
            'CheckFields' => ['BookingProcedurePropertyType', 'CheckFields'],
        ];
    }

    public static function GetPropertyFieldHtml($arProperty, $arValue, $strHTMLControlName)
    {
        Loader::includeModule('iblock');
        
        $fieldId = $strHTMLControlName['VALUE'];
        $safeId = preg_replace('/[^a-zA-Z0-9_]/', '_', $fieldId);
        $selectId = 'proc_select_' . $safeId;
        $btnId = 'proc_info_' . $safeId;
        
        $doctorId = 0;
        $elementId = (int)($_REQUEST['ID'] ?? 0);
        if ($elementId > 0) {
            $el = \CIBlockElement::GetList(false, ['ID' => $elementId], false, false, ['PROPERTY_DOKTOR'])->Fetch();
            if ($el && !empty($el['PROPERTY_DOKTOR_VALUE'])) {
                $doctorId = (int)$el['PROPERTY_DOKTOR_VALUE'];
            }
        }
        
        $html = '<div style="margin:5px 0;">';
        $html .= '<select name="' . $fieldId . '" id="' . $selectId . '">';
        $html .= '<option value="">-- Выберите процедуру --</option>';
        
        $filter = ['IBLOCK_ID' => 17, 'ACTIVE' => 'Y'];
        if ($doctorId > 0) {
            $filter['PROPERTY_DOCTORS'] = $doctorId;
        }
        
        $db = \CIBlockElement::GetList(['SORT' => 'ASC'], $filter, false, false, ['ID', 'NAME']);
        while ($proc = $db->Fetch()) {
            $sel = ($arValue['VALUE'] == $proc['ID']) ? 'selected' : '';
            $html .= '<option value="' . $proc['ID'] . '" ' . $sel . '>' . htmlspecialchars($proc['NAME']) . '</option>';
        }
        
        $html .= '</select>';
        $html .= '<input type="button" value="Info" id="' . $btnId . '" style="margin-left:5px;" />';
        $html .= '</div>';
        
        $html .= '<script>
        (function(){
            var sel = document.getElementById("' . $selectId . '");
            var btn = document.getElementById("' . $btnId . '");
            var doctorInput = document.getElementById("PROP[72][n0]") || document.querySelector("input[name=\'PROP[72][n0]\']");
            
            if(doctorInput && sel){
                var lastValue = doctorInput.value;
                doctorInput.onchange = checkDoctorChange;
                var checkInterval = setInterval(function(){
                    if(doctorInput.value !== lastValue){
                        lastValue = doctorInput.value;
                        checkDoctorChange();
                    }
                }, 500);
                function checkDoctorChange(){
                    var dId = doctorInput.value;
                    if(!dId){ sel.innerHTML = "<option value=\'\'>-- Выберите процедуру --</option>"; return; }
                    BX.ajax({
                        method: "POST",
                        url: "/local/ajax/get-procedures.php",
                        data: {DOCTOR_ID: dId, sessid: BX.bitrix_sessid()},
                        onsuccess: function(json){
                            try {
                                var data = JSON.parse(json);
                                sel.innerHTML = "<option value=\'\'>-- Выберите процедуру --</option>";
                                if(data.procedures && data.procedures.length > 0){
                                    data.procedures.forEach(function(p){
                                        var opt = document.createElement("option");
                                        opt.value = p.ID; opt.text = p.NAME; sel.add(opt);
                                    });
                                }
                            } catch(e) { console.error(e); }
                        }
                    });
                }
            }
            if(btn && sel){
                btn.onclick = function(){
                    var pid = sel.value;
                    if(!pid){alert("Выберите процедуру");return;}
                    BX.ajax({
                        method: "POST",
                        url: "/local/ajax/procedure-info.php",
                        data: {PROC_ID: pid, sessid: BX.bitrix_sessid()},
                        onsuccess: function(r){
                            if(typeof BX.PopupWindow !== "undefined"){
                                new BX.PopupWindow("proc_popup_' . $btnId . '", null, {
                                    content: r, closeIcon: true, titleBar: "Информация",
                                    overlay: {backgroundColor: "black", opacity: "50"},
                                    buttons: [new BX.PopupWindowButton({text: "Закрыть", events: {click: function(){this.popupWindow.close();}}})]
                                }).show();
                            }else{ alert(r.replace(/<[^>]*>/g,"")); }
                        }
                    });
                };
            }
        })();
        </script>';
        return $html;
    }

    public static function GetAdminListViewHTML($arProperty, $arValue, $strHTMLControlName)
    {
        if(empty($arValue['VALUE'])) return '-';
        $p = \CIBlockElement::GetList([],['IBLOCK_ID'=>17,'ID'=>$arValue['VALUE']],false,false,['NAME'])->Fetch();
        return htmlspecialchars($p['NAME'] ?? 'Не найдено');
    }

    public static function GetPublicViewHTML($arProperty, $arValue, $strHTMLControlName)
    {
        if(empty($arValue['VALUE'])) return '';
        $p = \CIBlockElement::GetList([],['IBLOCK_ID'=>17,'ID'=>$arValue['VALUE']],false,false,['NAME'])->Fetch();
        return '<span>'.htmlspecialchars($p['NAME'] ?? '').'</span>';
    }

    public static function GetSearchContent($arProperty, $value){ return (string)($value['VALUE'] ?? ''); }
    public static function ConvertToDB($arProperty, $value){ return empty($value)?null:['VALUE'=>(int)$value,'DESCRIPTION'=>'']; }
    public static function ConvertFromDB($arProperty, $value){ return ['VALUE'=>$value['VALUE']??$value,'DESCRIPTION'=>$value['DESCRIPTION']??'']; }
    public static function CheckFields($arProperty, $value){
        $r=[];
        if($arProperty['IS_REQUIRED']==='Y' && empty($value)) $r[]=['id'=>'REQUIRED','text'=>'Обязательно'];
        return empty($r)?true:$r;
    }
}

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->AddEventHandler('iblock','OnIBlockPropertyBuildList',['BookingProcedurePropertyType','GetUserTypeDescription']);
