<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/local/classes/OTUSFileExceptionHandlerLog.php';
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e) {
        $msg = "OTUS: [" . date('Y-m-d H:i:s') . "] {$e['type']}: {$e['message']} in {$e['file']}:{$e['line']}\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/otus.log', $msg, FILE_APPEND);
    }
});