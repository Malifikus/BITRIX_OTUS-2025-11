<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Loader::includeModule('iblock');

$doctorId = (int)($_POST['DOCTOR_ID'] ?? 0);

$procedures = [];
if ($doctorId > 0) {
    $db = \CIBlockElement::GetList(
        ['SORT' => 'ASC'],
        ['IBLOCK_ID' => 17, 'ACTIVE' => 'Y', 'PROPERTY_DOCTORS' => $doctorId],
        false,
        false,
        ['ID', 'NAME']
    );
    while ($p = $db->Fetch()) {
        $procedures[] = $p;
    }
}

header('Content-Type: application/json');
echo json_encode(['procedures' => $procedures]);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
