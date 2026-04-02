<?php
// Интеграция смарт-процесса с задачами
include_once(__DIR__ . '/init.scripts/event_handlers/task_sp_integration.php');

// Кастомные поля в календаре
include_once(__DIR__ . '/init.scripts/event_handlers/calendar_js_handler.php');

// Кастомное свойство бронирования
include_once(__DIR__ . '/init.scripts/event_handlers/booking_procedure_field.php');

require_once $_SERVER['DOCUMENT_ROOT'].'/local/classes/OTUSFileExceptionHandlerLog.php';
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e) {
        $msg = "OTUS: [" . date('Y-m-d H:i:s') . "] {$e['type']}: {$e['message']} in {$e['file']}:{$e['line']}\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/otus.log', $msg, FILE_APPEND);
    }
});
