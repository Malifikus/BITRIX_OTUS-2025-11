<?php
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;

class otus_custom_grid extends CModule
{
    public $MODULE_ID = 'otus.custom.grid';
    public $MODULE_VERSION = '1.0.0';
    public $MODULE_VERSION_DATE = '2026-04-01 12:00:00';
    public $MODULE_NAME = 'Кастомная вкладка CRM с GRID';
    public $MODULE_DESCRIPTION = 'Вывод данных из внешней таблицы в карточке CRM';
    public $PARTNER_NAME = 'OTUS';
    public $PARTNER_URI = 'https://otus.ru';

    public function DoInstall()
    {
        $this->InstallDB();
        $this->InstallEvents();
        return true;
    }

    public function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->UnInstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);
        return true;
    }

    public function InstallDB()
    {
        $connection = Application::getConnection();
        $connection->queryExecute("
            CREATE TABLE IF NOT EXISTS b_otus_custom_data (
                ID INT AUTO_INCREMENT PRIMARY KEY,
                ENTITY_ID INT NOT NULL,
                ENTITY_TYPE VARCHAR(50) NOT NULL,
                NAME VARCHAR(255) NOT NULL,
                DESCRIPTION TEXT,
                DATE_CREATED DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX IDX_ENTITY (ENTITY_ID, ENTITY_TYPE)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        ModuleManager::registerModule($this->MODULE_ID);
        return true;
    }

    public function UnInstallDB()
    {
        $connection = Application::getConnection();
        $connection->queryExecute("DROP TABLE IF EXISTS b_otus_custom_data");
        return true;
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Custom\\Grid\\Handler\\CrmTabHandler',
            'onTabsInitialized'
        );
        return true;
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'crm',
            'onEntityDetailsTabsInitialized',
            $this->MODULE_ID,
            '\\Otus\\Custom\\Grid\\Handler\\CrmTabHandler',
            'onTabsInitialized'
        );
        return true;
    }
}
