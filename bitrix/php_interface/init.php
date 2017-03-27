<?

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

Loader::includeModule("iblock");
Loader::includeModule("catalog");
Loader::includeModule("sale");


AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "DoIBlockAfterSave");
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "DoIBlockAfterSave");
AddEventHandler("catalog", "OnPriceAdd", "DoIBlockAfterSave");
AddEventHandler("catalog", "OnPriceUpdate", "DoIBlockAfterSave");

function DoIBlockAfterSave($arg1, $arg2 = false) {
	$ELEMENT_ID = false;
	$IBLOCK_ID = false;
	$OFFERS_IBLOCK_ID = false;
	$OFFERS_PROPERTY_ID = false;
	
	if(CModule::IncludeModule('currency'))
		$strDefaultCurrency = CCurrency::GetBaseCurrency();
	
	if(is_array($arg2) && $arg2["PRODUCT_ID"] > 0) {
		$rsPriceElement = CIBlockElement::GetList(
			array(),
			array(
				"ID" => $arg2["PRODUCT_ID"],
			),
			false,
			false,
			array("ID", "IBLOCK_ID")
		);
		if($arPriceElement = $rsPriceElement->Fetch()) {
			$arCatalog = CCatalog::GetByID($arPriceElement["IBLOCK_ID"]);
			if(is_array($arCatalog)) {
				if($arCatalog["OFFERS"] == "Y") {
					$rsElement = CIBlockElement::GetProperty(
						$arPriceElement["IBLOCK_ID"],
						$arPriceElement["ID"],
						"sort",
						"asc",
						array("ID" => $arCatalog["SKU_PROPERTY_ID"])
					);
					$arElement = $rsElement->Fetch();
					if($arElement && $arElement["VALUE"] > 0) {
						$ELEMENT_ID = $arElement["VALUE"];
						$IBLOCK_ID = $arCatalog["PRODUCT_IBLOCK_ID"];
						$OFFERS_IBLOCK_ID = $arCatalog["IBLOCK_ID"];
						$OFFERS_PROPERTY_ID = $arCatalog["SKU_PROPERTY_ID"];
					}
				} elseif($arCatalog["OFFERS_IBLOCK_ID"] > 0) {
					$ELEMENT_ID = $arPriceElement["ID"];
					$IBLOCK_ID = $arPriceElement["IBLOCK_ID"];
					$OFFERS_IBLOCK_ID = $arCatalog["OFFERS_IBLOCK_ID"];
					$OFFERS_PROPERTY_ID = $arCatalog["OFFERS_PROPERTY_ID"];
				} else {
					$ELEMENT_ID = $arPriceElement["ID"];
					$IBLOCK_ID = $arPriceElement["IBLOCK_ID"];
					$OFFERS_IBLOCK_ID = false;
					$OFFERS_PROPERTY_ID = false;
				}
			}
		}
	} elseif(is_array($arg1) && $arg1["ID"] > 0 && $arg1["IBLOCK_ID"] > 0) {
		$ELEMENT_ID = $arg1["ID"];
		$IBLOCK_ID = $arg1["IBLOCK_ID"];
		$arOffers = CIBlockPriceTools::GetOffersIBlock($arg1["IBLOCK_ID"]);
		if(is_array($arOffers)) {			
			$OFFERS_IBLOCK_ID = $arOffers["OFFERS_IBLOCK_ID"];
			$OFFERS_PROPERTY_ID = $arOffers["OFFERS_PROPERTY_ID"];
		}
	}

	if($ELEMENT_ID) {
		static $arPropCache = array();
		if(!array_key_exists($IBLOCK_ID, $arPropCache)) {
			$rsProperty = CIBlockProperty::GetByID("MINIMUM_PRICE", $IBLOCK_ID);
			$arProperty = $rsProperty->Fetch();
			if($arProperty)
				$arPropCache[$IBLOCK_ID] = $arProperty["ID"];
			else
				$arPropCache[$IBLOCK_ID] = false;
		}

		if($arPropCache[$IBLOCK_ID]) {
			if($OFFERS_IBLOCK_ID) {
				$rsOffers = CIBlockElement::GetList(
					array(),
					array(
						"ACTIVE" => "Y",
						"IBLOCK_ID" => $OFFERS_IBLOCK_ID,
						"PROPERTY_".$OFFERS_PROPERTY_ID => $ELEMENT_ID,
					),
					false,
					false,
					array("ID")
				);
				while($arOffer = $rsOffers->Fetch())
					$arProductID[] = $arOffer["ID"];
					
				if(!is_array($arProductID))
					$arProductID = array($ELEMENT_ID);
			} else
				$arProductID = array($ELEMENT_ID);

			$minPrice = false;
			$minQuantity = false;
			
			$rsPrices = CPrice::GetList(
				array(),
				array(
					"PRODUCT_ID" => $arProductID,
				)
			);
			while($arPrice = $rsPrices->Fetch()) {
				if(CModule::IncludeModule('currency') && $strDefaultCurrency != $arPrice['CURRENCY'])
					$arPrice["PRICE"] = CCurrencyRates::ConvertCurrency($arPrice["PRICE"], $arPrice["CURRENCY"], $strDefaultCurrency);
				
				$PRICE = $arPrice["PRICE"];
				
				$ar_res = CCatalogProduct::GetByID($arPrice["PRODUCT_ID"]);
				$QUANTITY = $ar_res["QUANTITY"];
				
				if($minPrice === false || $minPrice > $PRICE) {
					$minPrice = $PRICE;
					$minQuantity = $QUANTITY;
				}
			}

			if($minPrice !== false) {
				CIBlockElement::SetPropertyValuesEx(
					$ELEMENT_ID,
					$IBLOCK_ID,
					array(
						"MINIMUM_PRICE" => $minPrice
					)
				);
								
				CCatalogProduct::Update(
					$ELEMENT_ID,
					array(
						"QUANTITY" => $minQuantity
					)
				);
			}
		}
	}
}





AddEventHandler('catalog','OnGetOptimalPrice',array("MyClass","MyGetOptimalPrice1"));
class MyClass{
    public static $finish_price = array();
    public function MyFunc(&$arResult){

    }
   public static function MyGetOptimalPrice1($productID, $quantity = 1, $arUserGroups = array(), $renewal = "N", $arPrices = array(), $siteID = false, $arDiscountCoupons = false){


        $iblockID = (int)\CIBlockElement::GetIBlockByID($productID);;
        $currency = \CCurrency::GetBaseCurrency();
        if (!strlen($_SESSION["VREGIONS_REGION"]["PRICE"])){
            return true;
        }

        $priceCode = $_SESSION["VREGIONS_REGION"]["PRICE"] ? $_SESSION["VREGIONS_REGION"]["PRICE"] : 'BASE';

        $arResultPrices = \CIBlockPriceTools::GetCatalogPrices($iblockID, Array($priceCode));

        $priceID = $arResultPrices[$priceCode]["ID"];
        $prod_price = GetCatalogProductPrice(
            $productID,
            $priceID
        );
        $realPrice = $prod_price["PRICE"];

        $arDiscounts = \CCatalogDiscount::GetDiscountByProduct(
            $productID,
            $arUserGroups,
            $renewal,
            Array($priceID),
            $siteID,
            $arDiscountCoupons
        );
        // echo "<pre>";
        // print_r($arDiscounts);
        // echo "</pre>";

        $discountPrice = \CCatalogProduct::CountPriceWithDiscount(
            $prod_price["PRICE"],
            $currency,
            $arDiscounts
        );

        if ($prod_price["CURRENCY"] != $currency){
            $realPrice = \CCurrencyRates::ConvertCurrency($realPrice, $prod_price["CURRENCY"], $currency);
            $realPrice = roundEx($realPrice, 0);

            $discountPrice = \CCurrencyRates::ConvertCurrency($discountPrice, $prod_price["CURRENCY"], $currency);
            $discountPrice = roundEx($discountPrice, 0);
        }

        $unroundDiscountPrice = $discountPrice;
        $discountPrice = \Bitrix\Catalog\Product\Price::roundPrice(
            $priceID,
            $discountPrice,
            $currency
        );
        $discountValue = $realPrice - $discountPrice;

        $answer = array();
        $answer['PRICE'] = array(
            'ID'                => $prod_price["ID"],
            'CATALOG_GROUP_ID'  => $priceID,
            'PRICE'             => $realPrice,
            'CURRENCY'          => $currency,
            'ELEMENT_IBLOCK_ID' => $iblockID,
            'VAT_RATE'          => 0,
            'VAT_INCLUDED'      => 'N',
        );
        // echo "<pre>";
        // print_r($answer['PRICE']);
        // echo "</pre>";
        $answer['RESULT_PRICE'] = array(
            'BASE_PRICE'             => $realPrice,
            'DISCOUNT_PRICE'         => $discountPrice,
            'UNROUND_DISCOUNT_PRICE' => $unroundDiscountPrice,
            'CURRENCY'               => $currency,
            'DISCOUNT'               => $discountValue ? $discountValue : 0,
            'PERCENT'                => $discountValue ? $discountValue / $realPrice * 100 : 0,
            'VAT_RATE'               => 0,
            'VAT_INCLUDED'           => 'Y',
        );
        // echo "<pre>";
        // print_r($answer['RESULT_PRICE']);
        // echo "</pre>";
        $answer["DISCOUNT_PRICE"] = $discountPrice;
        $answer["PRODUCT_ID"] = $productID;

        if ($arDiscounts[0]){
            $answer["DISCOUNT"] = $arDiscounts[0];
        }
        // echo "<pre>";
        // print_r($answer['DISCOUNT']);
        // echo "</pre>";
        $answer["DISCOUNT_LIST"] = $arDiscounts;
        // echo "<pre>";
        // print_r($answer['DISCOUNT_LIST']);
       global $resPrice;
       $resPrice = $answer;
        return $answer;

    }
};






class CCatalogProductProviderCustom extends CCatalogProductProvider
{
    public static function GetProductData($arParams)
    {
        $arResult = CCatalogProductProvider::GetProductData($arParams);

        $cur_Price_with_disc = curPriceWithDisc($arParams['PRODUCT_ID']);
        $min_price = minPrice($arParams['PRODUCT_ID']);

        $base_price = 100*$cur_Price_with_disc/(100-floatval($arResult['DISCOUNT_VALUE']));
        $discount_price = $base_price * (floatval($arResult['DISCOUNT_VALUE'])/100);

        if($min_price === 0 ){
            $arResult['BASE_PRICE'] = $base_price;
            $arResult['PRICE'] = $cur_Price_with_disc;
            $arResult['DISCOUNT_PRICE'] = $discount_price;

        } elseif(($cur_Price_with_disc > $min_price) && (float)$arResult['DISCOUNT_VALUE'] === 0){
            $arResult['BASE_PRICE'] = $min_price;
            $arResult['PRICE'] = $min_price;
            $arResult['DISCOUNT_PRICE'] = 0;


        } elseif($cur_Price_with_disc > $min_price & (float)$arResult['DISCOUNT_VALUE'] > 0){


            $arResult['BASE_PRICE'] = $min_price;
            $arResult['PRICE'] = $min_price;
            $arResult['DISCOUNT_PRICE'] = 0;
        } else{
            $arResult['BASE_PRICE'] = $min_price;
            $arResult['DISCOUNT_PRICE'] = 0;
            $arResult['PRICE'] = $min_price;
        }

        unset($priceArr, $price);
        return $arResult;
    }

    public static function OrderProduct($arParams){
        $arResult = CCatalogProductProvider::GetProductData($arParams);

        $cur_Price_with_disc = curPriceWithDisc($arParams['PRODUCT_ID']);
        $min_price = minPrice($arParams['PRODUCT_ID']);

        $base_price = 100*$cur_Price_with_disc/(100-floatval($arResult['DISCOUNT_VALUE']));
        $discount_price = $base_price * (floatval($arResult['DISCOUNT_VALUE'])/100);

        if($min_price === 0 ){
            $arResult['BASE_PRICE'] = $base_price;
            $arResult['PRICE'] = $cur_Price_with_disc;
            $arResult['DISCOUNT_PRICE'] = $discount_price;

        } elseif(($cur_Price_with_disc > $min_price) && (float)$arResult['DISCOUNT_VALUE'] === 0){
            $arResult['BASE_PRICE'] = $min_price;
            $arResult['PRICE'] = $min_price;
            $arResult['DISCOUNT_PRICE'] = 0;


        } elseif($cur_Price_with_disc > $min_price & (float)$arResult['DISCOUNT_VALUE'] > 0){


            $arResult['BASE_PRICE'] = $min_price;
            $arResult['PRICE'] = $min_price;
            $arResult['DISCOUNT_PRICE'] = 0;
        } else{
            $arResult['BASE_PRICE'] = $min_price;
            $arResult['DISCOUNT_PRICE'] = 0;
            $arResult['PRICE'] = $min_price;
        }

        unset($priceArr, $price);
        return $arResult;
    }
}
?>