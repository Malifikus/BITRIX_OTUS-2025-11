<?php
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php';

if ($_POST['action'] === 'add') {
    $projectId = (int)$_POST['project_id'];
    $departmentId = (int)$_POST['department_id'];
    $amount = (float)$_POST['amount'];
    $description = trim($_POST['description']);

    if ($projectId > 0 && $departmentId > 0 && $amount > 0) {
        $connection = \Bitrix\Main\Application::getConnection();
        $connection->query("
            INSERT INTO app_otus_budgets (PROJECT_ID, DEPARTMENT_ID, AMOUNT, DESCRIPTION)
            VALUES ($projectId, $departmentId, $amount, '" . $connection->getSqlHelper()->forSql($description) . "')
        ");
        LocalRedirect('/local/test-budget.php');
    }
}

use Bitrix\Main\Application;
$connection = Application::getConnection();

# Получаем список проектов и отделов
$projects = [];
$departments = [];

$res = CIBlockElement::GetList(
    ['ID' => 'ASC'],
    ['ACTIVE' => 'Y'],
    false,
    false,
    ['ID', 'NAME', 'IBLOCK_ID']
);

while ($el = $res->Fetch()) {
    if ($el['IBLOCK_ID'] == 18) {
        $projects[$el['ID']] = $el['NAME'];
    } elseif ($el['IBLOCK_ID'] == 19) {
        $departments[$el['ID']] = $el['NAME'];
    }
}

# Выбираем данные из таблицы
$sql = "
    SELECT 
        b.ID,
        b.AMOUNT,
        b.DESCRIPTION,
        p.NAME AS PROJECT_NAME,
        d.NAME AS DEPARTMENT_NAME
    FROM app_otus_budgets b
    LEFT JOIN b_iblock_element p ON b.PROJECT_ID = p.ID
    LEFT JOIN b_iblock_element d ON b.DEPARTMENT_ID = d.ID
";

$result = $connection->query($sql);

# Форма
echo '
<form method="post">
    <input type="hidden" name="action" value="add">
    Проект ID: <input type="number" name="project_id" required> 
    Отдел ID: <input type="number" name="department_id" required><br>
    Сумма: <input type="number" step="0.01" name="amount" required>
    Описание: <input type="text" name="description" required><br>
    <button type="submit">Добавить запись</button>
</form>
<hr>
';

# Значения
echo '<div><h4>ID:</h4>';

if (!empty($projects)) {
    echo '<strong>Проекты:</strong><br>';
    foreach ($projects as $id => $name) {
        echo "$id — " . htmlspecialchars($name) . "<br>";
    }
} else {
    echo '<strong>Проекты:</strong> не найдены (проверь IBLOCK_ID)<br>';
}

echo '<br>';

if (!empty($departments)) {
    echo '<strong>Отделы:</strong><br>';
    foreach ($departments as $id => $name) {
        echo "$id — " . htmlspecialchars($name) . "<br>";
    }
} else {
    echo '<strong>Отделы:</strong> не найдены (проверь IBLOCK_ID)<br>';
}

echo '</div>';

# Таблица
echo "<table border=1><tr>
    <th>ID</th>
    <th>Проект</th>
    <th>Отдел</th>
    <th>Сумма</th>
    <th>Описание</th>
</tr>";

while ($row = $result->fetch()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
    echo "<td>" . htmlspecialchars($row['PROJECT_NAME'] ?: '—') . "</td>";
    echo "<td>" . htmlspecialchars($row['DEPARTMENT_NAME'] ?: '—') . "</td>";
    echo "<td>" . number_format($row['AMOUNT'], 2) . "</td>";
    echo "<td>" . htmlspecialchars($row['DESCRIPTION']) . "</td>";
    echo "</tr>";
}
echo "</table>";

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php';
