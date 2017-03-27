<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CAristovVregionsMapComponent extends CBitrixComponent{
	public function addResources(){
		\Bitrix\Main\Page\Asset::getInstance()->addJs('https://api-maps.yandex.ru/2.1/?lang=ru_RU');
	}

	public function executeComponent(){
		$this->addResources();

		if ($this->startResultCache()){
			$this->includeComponentTemplate();
		}
	}
}
?>