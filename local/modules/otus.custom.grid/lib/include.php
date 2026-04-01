<?php
use Bitrix\Main\Loader;

Loader::registerAutoloadClasses(
    'otus.custom.grid',
    [
        'Otus\Custom\Grid\Handler\CrmTabHandler' => 'lib/Handler/CrmTabHandler.php',
        'Otus\Custom\Grid\Table\CustomDataTable' => 'lib/Table/CustomDataTable.php',
        'Otus\Custom\Grid\Component\CustomGridComponent' => 'lib/Component/CustomGridComponent.php',
    ]
);
