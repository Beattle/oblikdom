<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
//vprint($arParams);?>
<? $this->setFrameMode(true); ?>
<?
$rand = rand();
?>
<? if (!empty($arResult["ITEMS"])){ ?>
	<div class="vregion-popup-link"
		 data-rand="<?=$rand;?>">
		<label><?=GetMessage("YOUR_CITY");?></label>
		<a class="popup-region-open"
		   href="#"
		   onclick="OpenVregionsPopUp('open', 'vregions-popup-with-all-cities<?=$rand;?>', 'vregions-sepia<?=$rand;?>'); return false;"><span
				class="my_city"><?=strlen($_SESSION["VREGIONS_PHP"]["city"]["name_ru"]) ? $_SESSION["VREGIONS_PHP"]["city"]["name_ru"] : GetMessage("DETERMINING")?></span>
		</a>
	</div>
	<div class="vregion-popup-link"
		 data-rand="<?=$rand;?>">
		<label><?=GetMessage("YOUR_REGION");?></label>
		<a class="popup-region-open"
		   href="#"
		   onclick="OpenVregionsPopUp('open', 'vregions-popup<?=$rand;?>', 'vregions-sepia<?=$rand;?>'); return false;"><?=(strlen($arResult["CURRENT_SESSION_ARRAY"]["NAME"]) ? $arResult["CURRENT_SESSION_ARRAY"]["NAME"] : $arResult["DEFAULT"]["NAME"]);?></a>
	</div>
	<div id="vregions-sepia<?=$rand;?>"
		 class="vregions-sepia"
		 onclick="OpenVregionsPopUp('close'); return false;"></div>
	<? if ($arParams["SHOW_POPUP_QUESTION"] == "Y"){ ?>
		<div class="vr-popup vregions-popup-que"
			 id="vregions-popup-que<?=$rand;?>">
			<div class="popup-white">
				<a class="close"
				   href="#"
				   onclick="OpenVregionsPopUp('close'); return false;">close
				</a>
				<div class="bw-title-shadow">
					<h3><?=$arParams["POPUP_QUESTION_TITLE"] ? $arParams["POPUP_QUESTION_TITLE"] : GetMessage("DID_WE_GUESS");?></h3>
				</div>
				<div class="vregions-que-body clearfix">
					<p><?=GetMessage("YOUR_REGION_IS");?>
						<span id="suggested-region"></span>
					</p>
					<a href="#"
					   onclick="ChangeVRegion(this); return false;"
					   id="we_guessed"
					   data-cookie=""><?=GetMessage("YES");?></a>
					<a href="#"
					   onclick="OpenVregionsPopUp('open', 'vregions-popup<?=$rand;?>', 'vregions-sepia<?=$rand;?>');"><?=GetMessage("NO");?></a>
				</div>
			</div>
		</div>
	<? } ?>
	<div class="vr-popup"
		 id="vregions-popup<?=$rand;?>">
		<div class="popup-white">
			<a class="close"
			   href="#"
			   onclick="OpenVregionsPopUp('close'); return false;">close
			</a>
			<div class="bw-title-shadow">
				<h3><?=GetMessage("SELECT_YOUR_REGION");?></h3>
			</div>
			<div class="vregions-list clearfix">
				<div class="col">
					<ul>
						<?
						foreach ($arResult["ITEMS"] as $arItem){
							if ($c >= $arResult["COUNT_SECTION"]){
								echo '</ul></div><div class="col"><ul>';
								$c = 0;
							}
							$c++; ?>
							<li class="<?=$arItem["CLASS"];?>">
								<a href="<?=$arItem["HREF"];?>"
								   data-cookie="<?=$arItem["~CODE"];?>"
								   onclick="ChangeVRegion(this); return false;"><?=$arItem["NAME"];?></a>
							</li>
						<? } ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="vr-popup"
		 id="vregions-popup-with-all-cities<?=$rand;?>">
		<div class="popup-white">
			<a class="close"
			   href="#"
			   onclick="OpenVregionsPopUp('close'); return false;">close
			</a>
			<div class="bw-title-shadow">
				<h3><?=GetMessage("SELECT_YOUR_CITY");?></h3>
			</div>
			<div class="vregions-list clearfix">
				<div class="col">
					<ul>
						<?
						if (CModule::IncludeModule('sale')){
							$cities = Array();
							$db_vars = CSaleLocation::GetList(array("CITY_NAME_LANG" => "ASC"), array("LID" => LANGUAGE_ID), false, false, array());
							while ($vars = $db_vars->Fetch()){
								if ($vars['CITY_ID'] > 0){
									$vars['NAME'] = $vars['CITY_NAME'];
									$cities[] = $vars;
								}
							}

							$c = 0;
							$arResult["COUNT_SECTION"] = count($cities) / 3;
							foreach ($cities as $arItem){
								if ($c >= $arResult["COUNT_SECTION"]){
									echo '</ul></div><div class="col"><ul>';
									$c = 0;
								}
								$c++; ?>
								<li class="<?=$arItem["CLASS"];?>">
									<a href="<?=$arItem["HREF"];?>" data-location_id="<?=$arItem["ID"];?>"
									   data-name="<?=$arItem["NAME"];?>"
									   onclick="ChangeVRegionCity(this); return false;"><?=$arItem["NAME"];?></a>
								</li>
								<?
							}
						} ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
<? }else{ ?>
	<?=GetMessage("ERROR_OF_NO_ELS");?>
<? } ?>
