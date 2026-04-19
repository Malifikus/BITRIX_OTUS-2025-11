<?php
require_once(__DIR__ . '/crest.php');
$result = CRest::installApp();
if (!empty($result['install'])) {
    CRest::call('event.bind', [
        'event' => 'onCrmActivityAdd',
        'handler' => 'https://cw976115.tw1.ru/local/rest_app/last_communication/handler.php'
    ]);
}
echo json_encode($result);
exit;
