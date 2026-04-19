<?php
define("NO_KEEP_STATISTIC", "Y");
define("PUBLIC_AJAX_MODE", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use CIBlockElement;

ini_set('display_errors', 1);
error_reporting(E_ALL);

Loader::includeModule('iblock');

$doctorId = (int)($_POST['doctor_id'] ?? 0);
$result = ['success' => false, 'procedures' => [], 'debug' => []];

$result['debug']['doctor_id'] = $doctorId;

if ($doctorId > 0) {
    $rs = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => 16, 'ID' => $doctorId, 'ACTIVE' => 'Y'],
        false, false,
        ['ID', 'PROPERTY_PROTSEDURY']
    );
    $doc = $rs->Fetch();
    $result['debug']['doctor_found'] = ($doc !== false);

    if ($doc) {
        $raw = $doc['PROPERTY_PROTSEDURY_VALUE'];
        $ids = is_array($raw) ? array_filter($raw) : [$raw];
        $result['debug']['proc_ids'] = $ids;
        
        if (!empty($ids)) {
            $rsP = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                ['IBLOCK_ID' => 17, 'ID' => $ids, 'ACTIVE' => 'Y'],
                false, false,
                ['ID', 'NAME', 'PREVIEW_TEXT']
            );
            while ($p = $rsP->Fetch()) {
                $result['procedures'][] = ['ID' => $p['ID'], 'NAME' => $p['NAME'], 'DESC' => $p['PREVIEW_TEXT']];
            }
            $result['debug']['procedures_count'] = count($result['procedures']);
        }
    }
}

$result['success'] = true;
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
\CMain::FinalActions();
