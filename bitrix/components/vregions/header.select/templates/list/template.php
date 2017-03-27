<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<script src="http://maps.google.com/maps/api/js?sensor=true"></script>
<?if (!empty($arResult["ITEMS"])){?>
<div class="vregion-hrefs">
	<h3><?=GetMessage("REGIONS");?></h3>
	<ul>
		<?foreach($arResult["ITEMS"] as $arItem){?>
			<li class="<?=$arItem["CLASS"];?>">
				<a href="<?=$arItem["HREF"];?>" data-cookie="<?=$arItem["~CODE"];?>" onclick="ChangeVRegion(this); return false;"><?=$arItem["NAME"];?></a>
			</li>
		<?}?>
	</ul>
</div>
<?}else{?>
	<?=GetMessage("ERROR_OF_NO_ELS");?>
<?}?>
