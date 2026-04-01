<?php
namespace Otus\Custom\Grid\Table;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\DatetimeField;

class CustomDataTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'b_otus_custom_data';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))->configurePrimary()->configureAutocomplete(),
            (new IntegerField('ENTITY_ID'))->configureRequired(),
            (new StringField('ENTITY_TYPE'))->configureRequired()->configureSize(50),
            (new StringField('NAME'))->configureRequired()->configureSize(255),
            new TextField('DESCRIPTION'),
            (new DatetimeField('DATE_CREATED')),
        ];
    }
}
