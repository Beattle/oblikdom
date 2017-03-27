<? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
IncludeModuleLangFile(__FILE__);

if (!check_bitrix_sessid() || $_SERVER["REQUEST_METHOD"] != "POST"){
	return;
}

if (!CModule::IncludeModule("iblock"))
	return;

global $USER, $APPLICATION;

// �� ������ �� utf
CUtil::JSPostUnescape();

// echo "<pre>";
// print_r($_REQUEST);
// echo "</pre>";

$answer = Array();
$answer["success"] = 0;
// $answer = array_merge($answer, $_REQUEST); // ��� ������
// $answer = array_merge($answer, $_SERVER); // ��� ������
$action = $_REQUEST["action"];

$IBLOCK_ID = COption::GetOptionString("aristov.vregions", "vregions_iblock_id");
$defaultRegion = COption::GetOptionString("aristov.vregions", "vregions_default");

$cookieLifetime = COption::GetOptionString("aristov.vregions", "vregions_cookie_lifetime");
if ($cookieLifetime === ""){
	$cookieLifetime = 3600 * 24 * 30 * 2;
}else{
	$cookieLifetime = intval($cookieLifetime);
}

// na kakom urovne iskat poddomen
$subdomainLevel = intval(\COption::GetOptionString("aristov.vregions", "vregions_subdomain_level"));
if (!$subdomainLevel){
	$subdomainLevel = 3; // default
}

$serverName = $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
$serverName = preg_replace('/\:\d+/is', '', $serverName);
$domains = explode(".", $serverName);

// est li www
$www = "";
if ($hostArr[0] == "www"){
	$www = "www.";
}

// url sayta
$siteURL = "";
for ($i = $subdomainLevel - 1; $i > 0; $i--){
	$siteURL .= $domains[count($domains) - $i];
	if ($i != 1){
		$siteURL .= ".";
	}
}

// tekuschiy poddomen
$currentSubdomain = $domains[count($domains) - $subdomainLevel];
$currentSubdomain = $currentSubdomain == 'www' ? '' : $currentSubdomain;

// ��������
if ($action == "check-auto-geo-ness"){ // �������� ������������� ����������
	$answer["request-action"] = "check-auto-geo-ness";
	$allowAutoGeo = COption::GetOptionString("aristov.vregions", "vregions_auto_geoposition");
	$geoMethod = COption::GetOptionString("aristov.vregions", "vregions_auto_geoposition_method");

	if ($allowAutoGeo == "Y"){
		$answer["success"] = 1;
		$answer["method"] = $geoMethod;
	}
}

if ($action == "get-php-coords"){
	$answer["request-action"] = "get-php-coords";

	if ($_SESSION["VREGIONS_PHP"]["city"]['lat'] && $_SESSION["VREGIONS_PHP"]["city"]['lon']){
		$answer["success"] = 1;
		$answer["lat"] = $_SESSION["VREGIONS_PHP"]["city"]['lat'];
		$answer["lon"] = $_SESSION["VREGIONS_PHP"]["city"]['lon'];
	}else{ // esli pochemu-to net etogo massiva
		$userIP = \Aristov\Vregions\Tools::getUserIP();
		$city = \Aristov\Vregions\Tools::getLocationByIP($userIP);
		if ($city["city"]['lat'] && $city["city"]['lon']){
			$answer["success"] = 1;
			$answer["lat"] = $city["city"]['lat'];
			$answer["lon"] = $city["city"]['lon'];
		}
	}
}

if ($action == "get-closest-region"){ // ��������� ���������� �������
	// $PROPERTY_LATITUDE � $PROPERTY_LONGITUDE ��������� ��� �������������, ��������� � ��� ���� �����
	$answer["redirect"] = 0;
	$answer["request-action"] = "get-closest-region";

	$PROPERTY_LATITUDE = COption::GetOptionString("aristov.vregions", "vregions_iblock_latitude_prop");
	$PROPERTY_LONGITUDE = COption::GetOptionString("aristov.vregions", "vregions_iblock_longitude_prop");
	$PROPERTY_REGION_CENTRE = COption::GetOptionString("aristov.vregions", "vregions_iblock_region_centre_prop");
	$permit_redirect = COption::GetOptionString("aristov.vregions", "vregions_auto_geoposition_redirect_for_new");
	$permit_redirect_always = COption::GetOptionString("aristov.vregions", "vregions_auto_redirect") == 'Y' ? 0 : 0;

	if (!$PROPERTY_REGION_CENTRE){
		$answer["text"] = GetMessage("NOT_SET_PROP_FOR_REGION_CENTRE");
		// return;
	}

	$region = \Aristov\Vregions\Tools::getClosestToCoordsRegion($_REQUEST["latitude"], $_REQUEST["longitude"]);
	// $answer = array_merge($answer, $region);

	// �������� ��������
	$subdomain = $region["CODE"].".";
	if ($region["CODE"] == $defaultRegion){ // ���� ����� �� ���������
		$subdomain = "";
	}

	$exCookie = $APPLICATION->get_cookie("VREGION_SUBDOMAIN");
	$answer["ex-cookie"] = $exCookie;
	if ($permit_redirect == "Y"){ // ���� ����� ������ �������� ��� ������ ������

		if (!$exCookie || $permit_redirect_always){ // ���� ������ �����
			// ������ ����
			$cookie = "";
			$cookie = $region["CODE"];
			$APPLICATION->set_cookie("VREGION_SUBDOMAIN", $cookie, time() + $cookieLifetime, "/", ".".$siteURL.""); // ����� ����������� �����
			$answer["success"] = 1;
			$answer["redirect"] = 1;
		}
	}

	$answer["url_without_path"] = "http://".$www."".$subdomain."".$siteURL."";
	$answer["subdomain"] = $subdomain;
	$answer["region"] = iconv(LANG_CHARSET, 'UTF-8', $region["NAME"]);
	$answer["region_code"] = $region["CODE"];
	$answer["cookie"] = $cookie;
}

if ($action == "set-cookie"){ // ������ ������������ ��������
	$exCookie = $APPLICATION->get_cookie("VREGION_SUBDOMAIN");
	// $answer["exCookie"] = $exCookie;
	$newCookie = $_REQUEST["cookie"];

	// if ($exCookie != $newCookie){ // ���� ���������� ������� ������
	$APPLICATION->set_cookie("VREGION_SUBDOMAIN", $newCookie, time() + $cookieLifetime, "/", ".".$siteURL."");
	$answer["redirect"] = 1;
	$answer["success"] = 1;
}

if ($action == "change-city"){ // smenit gorod polzovatelya
	// todo podbirat podhodyzschyy region po koordinatam goroda
	$_SESSION["VREGIONS_PHP"]["city"]["name_ru"] = urldecode($_POST["cityName"]);
	$answer["success"] = 1;
}

if ($action == "find-region-by-name-mask"){
	$answer["regions"] = \Aristov\Vregions\Tools::findRegionByNameMask($_REQUEST["mask"]);
}

echo json_encode($answer);
?>