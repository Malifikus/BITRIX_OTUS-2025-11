<?php
class OTUSFileExceptionHandlerLog extends \Bitrix\Main\Diag\FileExceptionHandlerLog
{
    protected function format($message, array $context = [])
    {
        return 'OTUS: ' . parent::format($message, $context);
    }
}