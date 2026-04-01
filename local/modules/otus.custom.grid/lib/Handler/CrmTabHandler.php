<?php
namespace Otus\Custom\Grid\Handler;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;

class CrmTabHandler
{
    public static function onTabsInitialized(Event $event): EventResult
    {
        if (!Loader::includeModule('crm')) {
            return new EventResult(EventResult::ERROR);
        }

        $entityTypeId = $event->getParameter('entityTypeID');
        $entityId = $event->getParameter('entityID');
        $tabs = $event->getParameter('tabs');

        $tabs[] = [
            'id' => 'otus_custom_tab',
            'name' => 'Дополнительные данные',
            'enabled' => true,
            'loader' => [
                'serviceUrl' => '/local/modules/otus.custom.grid/lib/Component/CustomGridComponent.php',
                'componentData' => [
                    'params' => [
                        'ENTITY_ID' => $entityId,
                    ],
                ],
            ],
        ];

        return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
    }
}
