<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();


if($arParams['USE_JQ'] == "Y") {
    $APPLICATION->AddHeadScript('https://yastatic.net/jquery/1.11.2/jquery.min.js');
}
if($arParams['USE_FB'] == "Y") {
    $APPLICATION->SetAdditionalCSS('https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.css');
    $APPLICATION->AddHeadScript('https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js');
}

//