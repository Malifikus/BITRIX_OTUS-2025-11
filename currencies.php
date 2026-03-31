<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Курсы валют");

$APPLICATION->IncludeComponent(
	"otus:currency.selector", 
	".default", 
	[
		"COMPONENT_TEMPLATE" => ".default",
		"DEFAULT_CURRENCY" => "99",
		"CURRENCY_CODE" => "RUB"
	],
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>