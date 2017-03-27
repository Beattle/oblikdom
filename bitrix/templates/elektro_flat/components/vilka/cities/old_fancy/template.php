<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$this->setFrameMode(true);

//echo '<pre>'.print_r($arResult, true).'</pre>';

?>
<div class="regions">
	<div class="regions_sel"><?=GetMessage("VSEO_CITY")?>: <a class="region_name" href="#cities_block"><?=$arResult['ITEMS'][$arResult["REGION"]]['f']['NAME']?></a></div>
	<div style="display: none;">
<div class="region_list" id="cities_block"><?
$result_city = array();
foreach($arResult['ITEMS'] as $cities)
{
    //echo '<pre>'.print_r($cities['f'], true).'</pre>';
    $first = $cities['f']['NAME']{0};
    if(isset($result_city[$first]))
        array_push($result_city[$first], $cities);
    else
        $result_city[$first] = array($cities);
}
foreach($result_city as $char=>$values){
    ?><div class="item">
		<div class="bukva"><b><?=$char?></b></div><?
    foreach($values as $city) {
        $key = $city['p']['VS_PODDOMEN']['VALUE'];
        if($key == '') $key = 'default';
        $cname = $city['f']['NAME'];
        //if($key != 'default') {
	?><a href="http://<?=$key!="default"?$key.'.':''?><?=$arResult['DOMEN']?><?=$APPLICATION->GetCurPageParam('setreg=Y', array('setreg'))?>" class="<?=$key==$arResult["REGION"]?'active':''?>"><?=$cname?></a><?
        //}
    }
    ?></div><?
}
?></div>
</div>
</div>