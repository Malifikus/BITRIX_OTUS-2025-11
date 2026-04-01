<?php
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$entityId = isset($_POST['PARAMS']['params']['ENTITY_ID']) ? (int)$_POST['PARAMS']['params']['ENTITY_ID'] : 0;

if ($entityId <= 0) {
    die('No entity ID');
}

$connection = \Bitrix\Main\Application::getConnection();
$result = $connection->query("
    SELECT ID, NAME, DESCRIPTION, DATE_CREATED 
    FROM b_otus_custom_data 
    WHERE ENTITY_ID = {$entityId} 
    ORDER BY ID DESC
");

// Вывод простого HTML
echo '<table class="adm-list-table" style="width:100%; border:1px solid #ccc;">';
echo '<thead><tr class="adm-list-table-header">';
echo '<td class="adm-list-table-cell"><b>ID</b></td>';
echo '<td class="adm-list-table-cell"><b>Название</b></td>';
echo '<td class="adm-list-table-cell"><b>Описание</b></td>';
echo '<td class="adm-list-table-cell"><b>Дата</b></td>';
echo '</tr></thead><tbody>';

$count = 0;
while ($row = $result->fetch()) {
    $count++;
    echo '<tr class="adm-list-table-row">';
    echo '<td class="adm-list-table-cell">' . htmlspecialchars($row['ID']) . '</td>';
    echo '<td class="adm-list-table-cell">' . htmlspecialchars($row['NAME']) . '</td>';
    echo '<td class="adm-list-table-cell">' . htmlspecialchars($row['DESCRIPTION'] ?? '-') . '</td>';
    echo '<td class="adm-list-table-cell">' . ($row['DATE_CREATED'] ? date('d.m.Y H:i', strtotime($row['DATE_CREATED'])) : '-') . '</td>';
    echo '</tr>';
}

if ($count == 0) {
    echo '<tr><td colspan="4" style="padding:20px; text-align:center;">Нет данных</td></tr>';
}

echo '</tbody></table>';
die();
