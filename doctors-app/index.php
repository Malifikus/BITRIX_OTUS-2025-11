<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle("Врачи и процедуры");

# Подключаем модуль
use Bitrix\Main\Loader;
Loader::includeModule('iblock');

# Ищем докторов по ID
$doctorId = $_GET['doctor_id'] ?? 0;

if ($doctorId > 0) {
    $doctor = \Bitrix\Iblock\Elements\ElementDoctorsTable::getByPrimary($doctorId, [
        'select' => ['ID', 'NAME', 'PROCEDURES', 'PROCEDURES.ELEMENT.NAME']
    ])->fetchObject();

    if ($doctor) {
        echo '<h2>Процедуры: ' . $doctor->getName() . '</h2>';

        $procedures = $doctor->getProcedures()->getAll();
        if (!empty($procedures)) {
            echo '<ul>';
            foreach ($procedures as $item) {
                echo '<li>' . $item->getElement()->getName() . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>У этого врача нет процедур.</p>';
        }

        echo '<p><a href="?">← Назад</a></p>';
        require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
        exit;
    }

    echo '<p>Врач не найден.</p>';
}

# Получаем поля инфоблока врачей
$doctors = \Bitrix\Iblock\Elements\ElementDoctorsTable::getList([
    'select' => ['ID', 'NAME'],
    'filter' => ['ACTIVE' => 'Y'],
    'order'  => ['NAME' => 'ASC']
])->fetchAll();
?>

<h1>Список врачей</h1>

<?php if (empty($doctors)): ?>
    <p>Нет ни одного врача.</p>
<?php else: ?>
    <ul>
        <?php foreach ($doctors as $doctor): ?>
            <li>
                <a href="?doctor_id=<?= $doctor['ID'] ?>">
                    <?= $doctor['NAME'] ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
?>

