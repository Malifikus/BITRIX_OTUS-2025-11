<?php

class OTUSLogger {
    public static function log($message) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $log = "[".date('Y-m-d H:i:s')."] OTUS: $message | IP: $ip\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/logs/log_custom.log', $log, FILE_APPEND);
    }
}
// class OTUSLogger
// {
//     # Записывает префикс OTUS в лог
//     public static function log($message)
//     {
//         # Делаем строку с датой и префиксом OTUS
//         $entry = "[" . date('Y-m-d H:i:s') . "] OTUS " . $message . "\n";
        
//         # Путь к файлу лога
//         $logFile = $_SERVER['DOCUMENT_ROOT'] . '/otus.log';
        
//         # Запись в конец файла
//         return file_put_contents($logFile, $entry, FILE_APPEND) !== false;
//     }
// }