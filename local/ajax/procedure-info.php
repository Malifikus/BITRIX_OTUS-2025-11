<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Loader::includeModule('iblock');

$procId = (int)($_POST['PROC_ID'] ?? 0);

if ($procId > 0) {
    $proc = \CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => 17, 'ID' => $procId],
        false,
        false,
        ['NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT']
    )->Fetch();
    
    if ($proc) {
        echo '<div style="padding:20px;">';
        echo '<h3>' . htmlspecialchars($proc['NAME']) . '</h3>';
        echo '<p>' . nl2br(htmlspecialchars($proc['PREVIEW_TEXT'] ?: $proc['DETAIL_TEXT'] ?: 'Описание не заполнено')) . '</p>';
        echo '</div>';
    } else {
        echo '<p>Информация не найдена</p>';
    }
} else {
    echo '<p>Не выбрано</p>';
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
