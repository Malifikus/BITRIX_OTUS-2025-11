<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/** @global $APPLICATION */
$APPLICATION->SetTitle('Множественное свойство');
$APPLICATION->SetAdditionalCSS('/doctors/style.css');

// получение одной записи из инфоблока Докторы в виде объекта

$doctorsId = 16; // Element{Doctors}Table
$doctors = \Bitrix\Iblock\Elements\ElementCountryTable::getByPrimary(
    $doctorsId, 
    array(
        'select' => [
            '*',
            // 'NAME',
            'CURRENCY',
            'PROCEDURES.ELEMENT.NAME', 
        ] 
    )
)->fetchObject();

// echo $country->getId().PHP_EOL;// ID элемента
// echo $country->getName().PHP_EOL; // имя элемента

// $arFile = CFile::MakeFileArray($country->getDetailPicture());
// pr($arFile);
pr($country->getId()); // ID элемента
pr($country->getName()); // имя элемента

// pr($country->getCode()); // символьный код элемента
// pr($country->getCurrency()->getValue()); // свойство элемента Валюта  

// свойство элемента Столица  
// pr($country->getCapital()->getElement()->getId()); 
// pr($country->getCapital()->getElement()->getName()); 
// pr($country->getCapital()->getElement()->getEnglish()->getValue()); 

// свойство элемента Города  
// foreach($country->getCities()->getAll() as $prItem) {
//     // pr($prItem->getElement()->getEnglish()->getValue().' '.$prItem->getElement()->getName());
//     pr($prItem->getElement()->get('ID').' '.$prItem->getElement()->get('ENGLISH')->getValue().' '.$prItem->getElement()->getName());
// }



// получение одной записи из инфоблока Страна в виде массива
/*$countryId = 70; 
$res = \Bitrix\Iblock\Elements\ElementCountryTable::getByPrimary($countryId, 
    array('select' => [
            '*', 
            'CITIES.ELEMENT.NAME', 
            'CITIES.ELEMENT.ENGLISH',
            'CAPITAL.ELEMENT.NAME',
            'CAPITAL.ELEMENT.ENGLISH',
        ]
    )
)->fetch();
*/
// pr($res['NAME']); // имя элемента
// pr($res['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CAPITAL_ELEMENT_NAME']); // CAPITAL - единственное свойство Столица, тип привязка к элементам в виде списка
// pr($res); 


// получение списка записей из инфоблока Cтрана в вие коллекции
/*$countryId = 71;
$countries = \Bitrix\Iblock\Elements\ElementCountryTable::getList([
        'select' => [
            'ID', 
            'NAME', 
            'CURRENCY', // CURRENCY - единственное свойство Валюта, тип строка
            'CAPITAL.ELEMENT', // CAPITAL - единственное свойство Столица, тип привязка к элементам в виде списка
            'CITIES.ELEMENT', // CITIES - множественное свойство Города, тип привязка к элементам в виде списка 
            'CITIES.ELEMENT.ENGLISH' // ENGLISH - единственное свойство En, инфоблок Города
        ], 
        'filter' => [
            // 'ID' => $countryId,
            'ACTIVE' => 'Y'
        ],
   ])->fetchCollection();


foreach ($countries as $element) {
    pr($element->getName());
    pr($element->getCurrency()->getValue()); 
    pr($element->getCapital()->getElement()->getName());

    foreach($element->getCities()->getAll() as $prItem) {
        pr($prItem->getElement()->get('ID').' '.$prItem->getElement()->getName().' '.$prItem->getElement()->get('ENGLISH')->getValue());
    }
}
*/


/*// получение списка записей из инфоблока Cтрана в виде массива
$countries = \Bitrix\Iblock\Elements\ElementCountryTable::getList([
        'select' => [
            'ID', 
            'NAME', 
            'CURRENCY', // CURRENCY - единственное свойство Валюта, тип строка, инфоблок Страна
            'CAPITAL.ELEMENT', // CAPITAL - единственное свойство Столица, тип привязка к элементам в виде списка, инфоблок Страна
            'CITIES.ELEMENT', // CITIES - множественное свойство Города, тип привязка к элементам в виде списка, инфоблок Страна
            'CITIES.ELEMENT.ENGLISH' // ENGLISH - единственное свойство En, инфоблок Города
        ], 
        'filter' => [
            //'ID' => $countryId,
            'ACTIVE' => 'Y'
        ],
   ])->fetchAll();

foreach ($countries as $key => $item) {
    pr($item['NAME'].' '.$item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CURRENCY_VALUE'].' '.$item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CAPITAL_ELEMENT_NAME'].' '.$item['IBLOCK_ELEMENTS_ELEMENT_COUNTRY_CITIES_ELEMENT_NAME']);
    pr($item);
}*/

?>

