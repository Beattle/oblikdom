<?php
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$arParams["EVENT_NAME"] = trim($arParams["EVENT_NAME"]);
if($arParams["EVENT_NAME"] == '')
	$arParams["EVENT_NAME"] = "FEEDBACK_FORM";
$arParams["EMAIL_TO"] = trim($arParams["EMAIL_TO"]);
if($arParams["EMAIL_TO"] == '')
	$arParams["EMAIL_TO"] = COption::GetOptionString("main", "email_from");



if (!CModule::IncludeModule("vilka.seo"))
{
    die('NO VILKA.seo module');
}
$domen = ltrim(str_replace('http://','',$_SERVER["HTTP_HOST"]),'www.');
$reg = explode(".", $domen);
if(count($reg) == 2) {$region = 'default';}
else {
    $region = $reg[0];
}
$domen = str_replace($region.'.','',$domen);

$cities = CVilkaSEO::RESULT();
$arResult['ITEMS'] = $cities['CITY'];
$arResult["DOMEN"] = $domen;
$arResult["REGION"] = $region;

$this->IncludeComponentTemplate();