<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<script src="http://maps.google.com/maps/api/js?sensor=true"></script>
<?if (!empty($arResult["ITEMS"])){?>
<div class="dropdown vregion-hrefs">
	<button class="btn btn-default dropdown-toggle" type="button" id="regions-menu" data-toggle="dropdown" aria-expanded="true">
	<?=(strlen($arResult["CURRENT_SESSION_ARRAY"]["NAME"])?$arResult["CURRENT_SESSION_ARRAY"]["NAME"]:$arResult["DEFAULT"]["NAME"]);?>
	<span class="caret"></span>
	</button>
	<ul class="dropdown-menu" role="menu" aria-labelledby="regions-menu">
		<li role="presentation" class="dropdown-header"><?=GetMessage("REGIONS_SELECT");?></li>
		<?foreach($arResult["ITEMS"] as $arItem){
			// vprint($arItem);?>
			<li role="presentation" class="<?=$arItem["CLASS"];?>">
				<a role="menuitem" data-cookie="<?=$arItem["~CODE"];?>" tabindex="-1" onclick="ChangeVRegion(this); return false;" href="<?=$arItem["HREF"];?>"><?=$arItem["NAME"];?></a>
			</li>
		<?}?>
	</ul>
</div>
<?}else{?>
	<?=GetMessage("ERROR_OF_NO_ELS");?>
<?}?>