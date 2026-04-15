<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("DADATA_SEARCH_DESCR_NAME"),
    "DESCRIPTION" => Loc::getMessage("DADATA_SEARCH_DESCR_DESCR"),
    "TYPE" => "activity",
    "CLASS" => "CompanySearchActivity",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "integrations",
        "OWN_ID" => "integrations",
        "OWN_NAME" => "Интеграции",
    ],
    "RETURN" => [
        "FOUND" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_FOUND") ?: "Найдено",
            "TYPE" => "bool",
        ],
        "ERROR_MESSAGE" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_ERROR") ?: "Ошибка",
            "TYPE" => "string",
        ],
        "COMPANY_NAME_FULL" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_NAME_FULL") ?: "Полное название",
            "TYPE" => "string",
        ],
        "INN" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_INN") ?: "ИНН",
            "TYPE" => "string",
        ],
        "KPP" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_KPP") ?: "КПП",
            "TYPE" => "string",
        ],
        "OGRN" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_OGRN") ?: "ОГРН",
            "TYPE" => "string",
        ],
        "ADDRESS_FULL" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_ADDRESS") ?: "Адрес",
            "TYPE" => "string",
        ],
        "ADDRESS_CITY" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_CITY") ?: "Город",
            "TYPE" => "string",
        ],
        "PHONE" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_PHONE") ?: "Телефон",
            "TYPE" => "string",
        ],
        "EMAIL" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_EMAIL") ?: "Email",
            "TYPE" => "string",
        ],
        "DIRECTOR_NAME" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_DIRECTOR") ?: "Руководитель",
            "TYPE" => "string",
        ],
        "STATUS" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_STATUS") ?: "Статус",
            "TYPE" => "string",
        ],
        "SITE" => [
            "NAME" => Loc::getMessage("DADATA_SEARCH_RETURN_SITE") ?: "Сайт",
            "TYPE" => "string",
        ],
    ],
];
