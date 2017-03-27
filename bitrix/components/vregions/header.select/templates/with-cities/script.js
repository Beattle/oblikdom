var positionForAjax = {};
var ajax_url        = "/bitrix/components/vregions/header.select/ajax.php";

// ������ ������������ �������
function ChangeVRegion(sender){
	var href   = sender.getAttribute("href");
	var cookie = sender.getAttribute("data-cookie");

	var vRegionsArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "set-cookie",
		cookie : cookie
	};

	BX.ajax({
		url      : "/bitrix/components/vregions/header.select/ajax.php",
		data     : vRegionsArr,
		method   : "POST",
		dataType : "json",
		onsuccess: function(answer){
			console.log(vRegionsArr);
			console.log(answer);
			if (answer.redirect){
				location.href = href + location.pathname;
			}
			OpenVregionsPopUp("close");
		},
		onfailure: function(){

		}
	});
	return false
}

function askRegion(region_name, cookie, url_without_path){
	var vregions_popup = document.getElementsByClassName("vregions-popup-que");
	if (vregions_popup[0]){
		var region_name_elem     = vregions_popup[0].querySelector("#suggested-region");
		var success_quess_button = vregions_popup[0].querySelector("#we_guessed");
		var sepia                = document.getElementById("vregions-sepia");

		OpenVregionsPopUp("close");

		region_name_elem.innerHTML = region_name;
		success_quess_button.setAttribute("data-cookie", cookie);
		success_quess_button.setAttribute("href", url_without_path);

		sepia.style.display             = "block";
		vregions_popup[0].style.display = "block";
		addClass(document.getElementsByTagName('body')[0], 'modal-open');
	}

	return false;
}

function OpenVregionsPopUp(action, popup_id, sepia_id){
	if (!popup_id){
		popup_id = "vregions-popup";
	}
	var vregions_popup  = document.getElementById(popup_id);
	var vregions_popups = document.getElementsByClassName("vr-popup");

	var sepia           = document.getElementById(sepia_id);
	var vregions_sepias = document.getElementsByClassName('vregions-sepia');

	for (var i = 0; i < vregions_popups.length; i++){
		vregions_popups[i].style.display = "none";
	}

	if (!action || action !== "close"){
		sepia.style.display          = "block";
		vregions_popup.style.display = "block";
		addClass(document.getElementsByTagName('body')[0], 'modal-open');
	}

	if (action === "close"){
		for (var i = 0; i < vregions_popups.length; i++){
			vregions_popups[i].style.display = "none";
		}
		for (var i = 0; i < vregions_sepias.length; i++){
			vregions_sepias[i].style.display = "none";
		}
		removeClass(document.getElementsByTagName('body')[0], 'modal-open');
	}
	return false;
}
/* $(document).keydown(function(e) {
 // ESCAPE key pressed
 if (e.keyCode == 27) {
 OpenVregionsPopUp("close");
 }
 }); */

window.onload = function(){
	var vRegionsArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "check-auto-geo-ness"
	};

	BX.ajax({
		url      : ajax_url,
		data     : vRegionsArr,
		method   : "POST",
		dataType : "json",
		onsuccess: function(answer){
			// console.log(vRegionsArr);
			console.log(answer);
			if (answer.success){
				if (answer.method == "google"){
					if (navigator.geolocation){
						navigator.geolocation.getCurrentPosition(
							geolocationSuccess,
							geolocationFailure
						);
					}
					else{
						goToDefaultLocation();
					}
				}
				if (answer.method == "sxgeo"){
					goCoordsByPHP();
				}
			}
		},
		onfailure: function(){

		}
	});
};

// �������� ���������� ����� php
function goCoordsByPHP(position){
	var vRegionsArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "get-php-coords"
	};

	BX.ajax({
		url      : ajax_url,
		data     : vRegionsArr,
		method   : "POST",
		dataType : "json",
		onsuccess: function(answer){
			// console.log(vRegionsArr);
			// console.log(answer);

			if (answer.lat && answer.lon){
				console.log(answer);
				// ��������� �� ��������
				redirectToSubDomain(answer.lat, answer.lon);
			}
		},
		onfailure: function(){

		}
	});
}

// ���� �� ���������� ����������
function geolocationSuccess(position){
	// ����������� �������������� � ������ LatLng
	var location    = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
	positionForAjax = position
	// console.log(positionForAjax)

	// ��������� �� ��������
	redirectToSubDomain(positionForAjax.coords.latitude, positionForAjax.coords.longitude);
}

// ��������� ������
function geolocationFailure(positionError){
	console.log("Your browser does not support geolocation")
	goToDefaultLocation();
}

// ���� ������ �� ����������
function goToDefaultLocation(){
	console.log("fail")
}

// ������� �� ��������
function redirectToSubDomain(lat, lon){
	vRegionsArr           = {};
	vRegionsArr.sessid    = BX.bitrix_sessid();
	vRegionsArr.site_id   = BX.message("SITE_ID");
	vRegionsArr.action    = "get-closest-region";
	// vRegionsArr.longitude = gvRegionsArr.coords.longitude;
	vRegionsArr.longitude = lon;
	// vRegionsArr.latitude = gvRegionsArr.coords.latitude;
	vRegionsArr.latitude  = lat;
	// console.log(vRegionsArr);
	// debugger

	BX.ajax({
		url      : ajax_url,
		data     : vRegionsArr,
		method   : "POST",
		dataType : "json",
		onsuccess: function(answer){
			// console.log(vRegionsArr);
			console.log(answer);
			if (answer.redirect){
				location.href = answer.url_without_path + location.pathname
			}
			else{
				if (!answer["ex-cookie"]){ // ���� ������ �� ����� �� ���������
					askRegion(answer.region, answer.region_code, answer.url_without_path);
				}
			}
		},
		onfailure: function(){

		}
	});
}

function addClass(o, c){
	var re = new RegExp("(^|\\s)" + c + "(\\s|$)", "g")
	if (re.test(o.className)) return
	o.className = (o.className + " " + c).replace(/\s+/g, " ").replace(/(^ | $)/g, "")
}

function removeClass(o, c){
	var re      = new RegExp("(^|\\s)" + c + "(\\s|$)", "g")
	o.className = o.className.replace(re, "$1").replace(/\s+/g, " ").replace(/(^ | $)/g, "")
}
// ручное переключение города
function ChangeVRegionCity(sender){
	var cityName    = sender.getAttribute("data-name");
	var location_id = sender.getAttribute("data-location_id");
	// console.log(cityName);

	var vRegionsArr = {
		sessid     : BX.bitrix_sessid(),
		site_id    : BX.message('SITE_ID'),
		action     : "change-city",
		cityName   : encodeURIComponent(cityName),
		location_id: location_id
	};

	BX.ajax({
		url      : ajax_url,
		data     : vRegionsArr,
		method   : "POST",
		dataType : "json",
		onsuccess: function(answer){
			console.log(vRegionsArr);
			console.log(answer);

			location.reload();
		},
		onfailure: function(answer){
		}
	});

	return false;
}