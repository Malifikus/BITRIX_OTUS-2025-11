<?php
function clearExceptionLog() {
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/otus.log', '');
}
clearExceptionLog();
header('Location: /otus/debug.php');
exit;