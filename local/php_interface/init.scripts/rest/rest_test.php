<? use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("ДЗ #12: Собственные обработчики REST ");

Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

// Конфигурация вебхука
$webhookUrl = "https://cw976115.tw1.ru/rest/1/epeg331gbi5wjhgz/";
$storagePath = $_SERVER["DOCUMENT_ROOT"] . "/local/logs/storage.json";

// Обработка действий при клике
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $params = [];
    
    // Параметры
    switch ($action) {
        case 'add':
            $params = ['NAME' => 'Task_Page_' . date('His')];
            break;
        case 'get':
            $storage = file_exists($storagePath) ? json_decode(file_get_contents($storagePath), true) : [];
            $lastId = !empty($storage) ? max(array_keys($storage)) : 1;
            $params = ['ID' => $lastId];
            break;
        case 'list':
            $params = [];
            break;
        case 'update':
            $storage = file_exists($storagePath) ? json_decode(file_get_contents($storagePath), true) : [];
            $lastId = !empty($storage) ? max(array_keys($storage)) : 1;
            $params = ['ID' => $lastId, 'NAME' => 'Upd_' . date('His')];
            break;
        case 'delete':
            $storage = file_exists($storagePath) ? json_decode(file_get_contents($storagePath), true) : [];
            $lastId = !empty($storage) ? max(array_keys($storage)) : 1;
            $params = ['ID' => $lastId];
            break;
    }

    // Выполняем запрос
    $queryUrl = $webhookUrl . "my_entity." . $action . ".json?" . http_build_query($params);
    $result = file_get_contents($queryUrl);
    
    header("Location: " . $_SERVER['PHP_SELF'] . "?last_result=" . urlencode($result));
    exit();
}

?>

<style>
    h2 {
        font-size: 15px;
        display: block;
        padding: 5px;
        border-bottom: 1px dashed #ccc;
        margin-top: 20px;
    }
    .item-list { list-style: none; padding-left: 0; }
    .item .icon {
        display: inline-block; width: 15px; height: 15px; background-size: cover; margin-right: 5px;
    }
    .done.item .icon {
        background-image: url(image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0iZ3JlZW4iIGNsYXNzPSJiaSBiaS1jaGVjay1jaXJjbGUtZmlsbCIgdmlld0JveD0iMCAwIDE2IDE2Ij4KICAgICAgICA8cGF0aCBkPSJNMTYgOEE4IDggMCAxIDEgMCA4YTggOCAwIDAgMSAxNiAwbS0zLjk3LTMuMDNhLjc1Ljc1IDAgMCAwLTEuMDguMDIyTDcuNDc3IDkuNDE3IDUuMzg0IDcuMzIzYS43NS43NSAwIDAgMC0xLjA2IDEuMDZMNi45NyAxMS4wM2EuNzUuNzUgMCAwIDAgMS4wNzktLjAybDMuOTkyLTQuOTlhLjc1Ljc1IDAgMCAwLS4wMS0xLjA1eiIvPgogICAgICAgIDwvc3ZnPg==);
    }
    .btn-rest { margin-right: 10px; margin-bottom: 10px; }
    
    .code-block { 
        background: #f8f9fa; 
        padding: 15px; 
        border: 1px solid #ddd; 
        border-radius: 4px; 
        max-height: 300px; 
        overflow: auto; 
        font-family: monospace; 
        font-size: 11px;
        white-space: pre-wrap;
    }
</style>

<section class="container-fluid">
    <h1 class="mb-3"><? $APPLICATION->ShowTitle() ?></h1>
    
    <div class="mb-4">
        <p><strong>Вызовы:</strong></p>
        <a href="?action=add" class="btn btn-success btn-sm btn-rest">Add</a>
        <a href="?action=list" class="btn btn-primary btn-sm btn-rest">List</a>
        <a href="?action=get" class="btn btn-info btn-sm btn-rest">Get</a>
        <a href="?action=update" class="btn btn-warning btn-sm btn-rest">Update</a>
        <a href="?action=delete" class="btn btn-danger btn-sm btn-rest">Delete</a>
    </div>

    <? if (isset($_GET['last_result'])): ?>
        <h2 class="item done">Ответ от REST API <i class="icon"></i></h2>
        <div class="code-block">
            <? 
            $resJson = json_decode($_GET['last_result']);
            echo htmlspecialchars(json_encode($resJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); 
            ?>
        </div>
    <? endif; ?>

    <h2 class="item done">Состояние <i class="icon"></i></h2>
    <div class="code-block">
        <? 
        if (file_exists($storagePath)) {
            $content = file_get_contents($storagePath);
            $json = json_decode($content);
            echo htmlspecialchars(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            echo "Пусто";
        }
        ?>
    </div>
</section>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
