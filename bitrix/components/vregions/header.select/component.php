<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\Page\Asset::getInstance()->addJs($this->GetPath()."/script.js");

$subdomainCookie = $APPLICATION->get_cookie("VREGION_SUBDOMAIN");
$arParams["CODE_BY_COOKIE"] = $subdomainCookie;

if (!isset($arParams["CACHE_TIME"])){
	$arParams["CACHE_TIME"] = 3600;
}else{
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
}

// �������� �� ��� ���������
$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if (strlen($arParams["IBLOCK_TYPE"]) <= 0)
	$arParams["IBLOCK_TYPE"] = "";
if ($arParams["IBLOCK_TYPE"] == "-")
	$arParams["IBLOCK_TYPE"] = "";

// �������� �� id ���������
$arParams["IBLOCK_ID"] = trim($arParams["IBLOCK_ID"]);
if (strlen($arParams["IBLOCK_ID"]) <= 0)
	$arParams["IBLOCK_ID"] = "";
if ($arParams["IBLOCK_ID"] == "-")
	$arParams["IBLOCK_ID"] = "";
if (!$arParams["IBLOCK_ID"]){
	$arParams["IBLOCK_ID"] = COption::GetOptionString("aristov.vregions", "vregions_iblock_id");
}

// �������� �� ���������� ����� ����������
$arParams["SORT_BY1"] = trim($arParams["SORT_BY1"]);
if (strlen($arParams["SORT_BY1"]) <= 0)
	$arParams["SORT_BY1"] = "SORT";
if ($arParams["SORT_ORDER1"] != "ASC")
	$arParams["SORT_ORDER1"] = "DESC";
if (strlen($arParams["SORT_BY2"]) <= 0)
	$arParams["SORT_BY2"] = "NAME";
if ($arParams["SORT_ORDER2"] != "DESC")
	$arParams["SORT_ORDER2"] = "ASC";

// vprint($arParams);

$arParams["CURRENT_SESSION_ARRAY"] = $_SESSION["VREGIONS_REGION"];

if ($this->StartResultCache()){
	if (!CModule::IncludeModule("iblock")){
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));

		return;
	}
	if (!CModule::IncludeModule("aristov.vregions")){
		$this->AbortResultCache();
		ShowError(GetMessage("VREGIONS_MODULE_NOT_INSTALLED"));

		return;
	}

	$arOrder = array(
		$arParams["SORT_BY1"] => $arParams["SORT_ORDER1"],
		$arParams["SORT_BY2"] => $arParams["SORT_ORDER2"],
		"ID"                  => "DESC",
	);
	$arFilter = array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID"   => $arParams["IBLOCK_ID"],
		"ACTIVE"      => "Y"
	);
	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"NAME",
		"CODE",
	);

	$arResult = array(
		"ITEMS" => array(),
	);

	$VREGION_DEFAULT = COption::GetOptionString("aristov.vregions", "vregions_default");

	$rs = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
	while ($ob = $rs->GetNextElement()){
		$arFields = $ob->GetFields();
		$arFields["PROPERTIES"] = $ob->GetProperties();

		if ($arFields["CODE"] == $arParams["CURRENT_SESSION_ARRAY"]["CODE"]){
			$arFields["CLASS"] = "active";
		}

		$arFields["HREF"] = Aristov\Vregions\Tools::generateRegionLink($arFields["CODE"], $arFields["PROPERTIES"]["HTTP_PROTOCOL"]["VALUE"]);

		if ($arFields["CODE"] == $VREGION_DEFAULT){
			$arResult["DEFAULT"] = $arFields;
		}

		if ($arFields["CODE"] == $VREGION_DEFAULT){
			$arFields["CODE"] = "";
		}

		$arResult["ITEMS"][] = $arFields;
	}

	$arResult["CURRENT_SESSION_ARRAY"] = $arParams["CURRENT_SESSION_ARRAY"];

	$this->IncludeComponentTemplate();
}
?>