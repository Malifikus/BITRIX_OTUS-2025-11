<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CBPCompanySearchActivity extends CBPActivity
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'Title' => '',
            'Inn' => '',
            'Found' => false,
            'INN' => '',
            'COMPANY_NAME_FULL' => '',
            'KPP' => '',
            'ERROR_MESSAGE' => '',
        ];
    }

    public function Execute()
    {
        $inn = trim($this->Inn ?? '');
        $apiKey = '';

        if (file_exists(__DIR__ . '/config.php')) {
            $cfg = include __DIR__ . '/config.php';
            $apiKey = $cfg['api_key'] ?? '';
        }

        if (!$inn) {
            $this->arProperties['ERROR_MESSAGE'] = 'Не указан ИНН';
            return CBPActivityExecutionStatus::Closed;
        }

        if (!$apiKey) {
            $this->arProperties['ERROR_MESSAGE'] = 'Не указан API-ключ';
            return CBPActivityExecutionStatus::Closed;
        }

        $url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/findById/party";
        $headers = [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Token $apiKey"
        ];
        $postData = json_encode(["query" => $inn, "count" => 1]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $this->arProperties['ERROR_MESSAGE'] = 'Ошибка HTTP ' . $httpCode;
            return CBPActivityExecutionStatus::Closed;
        }

        $data = json_decode($response, true);
        if (empty($data["suggestions"])) {
            $this->arProperties['ERROR_MESSAGE'] = 'Компания не найдена';
            return CBPActivityExecutionStatus::Closed;
        }

        $company = $data["suggestions"][0];
        $info = $company["data"] ?? [];

        $this->arProperties['Found'] = true;
        $this->arProperties['INN'] = $info["inn"] ?? "";
        $this->arProperties['COMPANY_NAME_FULL'] = $company["value"] ?? "";
        $this->arProperties['KPP'] = $info["kpp"] ?? "";

        return CBPActivityExecutionStatus::Closed;
    }

    public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
    {
        $runtime = CBPRuntime::GetRuntime();
        
        if (!is_array($arCurrentValues)) {
            $arCurrentValues = ['Inn' => ''];
            $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
            if (is_array($arCurrentActivity["Properties"])) {
                $arCurrentValues['Inn'] = $arCurrentActivity["Properties"]["Inn"] ?? '';
            }
        }
        
        return $runtime->ExecuteResourceFile(__FILE__, "properties_dialog.php", [
            "arCurrentValues" => $arCurrentValues,
            "formName" => $formName,
        ]);
    }
    
    public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
    {
        $arErrors = [];
        $arProperties = [
            'Inn' => $arCurrentValues['Inn'] ?? '',
        ];
        $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
        $arCurrentActivity["Properties"] = $arProperties;
        return true;
    }
}
