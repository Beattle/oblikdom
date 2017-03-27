var positionForAjax = {};
var ajax_url = "/bitrix/components/vregions/header.select/ajax.php";

// ������ ������������ �������
function ChangeVRegion(sender){
	var href = sender.getAttribute("href");
	var cookie = sender.getAttribute("data-cookie");

	var vRegionsArr = {
		sessid: BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action: "set-cookie",
		cookie: cookie
	};

	BX.ajax({
		url: "/bitrix/components/vregions/header.select/ajax.php",
		data: vRegionsArr,
		method: "POST",
		dataType: "json",
		onsuccess: function(answer){
			console.log(vRegionsArr);
			console.log(answer);
			if (answer.redirect){
				location.href = href + location.pathname;
			}
			OpenVregionsPopUp("close"); // �� ������ ������ �� �������
		},
		onfailure: function(){

		}
	});
	return false
}

// �������� ��������� ������
function askRegion(region_name, cookie, url_without_path){
	var vregions_popup = document.getElementById("vregions-popup-que");
	if (vregions_popup){
		var region_name_elem = vregions_popup.querySelector("#suggested-region");
		var success_quess_button = vregions_popup.querySelector("#we_guessed");
		var sepia = document.getElementById("vregions-sepia");

		OpenVregionsPopUp("close"); // ������� ��� �������� ��������� ���� �� ������

		// ��������� ������
		region_name_elem.innerHTML = region_name;
		success_quess_button.setAttribute("data-cookie", cookie);
		success_quess_button.setAttribute("href", url_without_path);

		// ���������� ����
		sepia.style.display = "block";
		vregions_popup.style.display = "block";
	}

	return false;
}

// �������� � �������� ���� � ���������
function OpenVregionsPopUp(action){
	var popup_id = "vregions-popup";
	var vregions_popup = document.getElementById(popup_id);

	var popup_class = "vr-popup";
	var vregions_popups = document.getElementsByClassName(popup_class);

	var sepia = document.getElementById("vregions-sepia");

	for (var i = 0; i < vregions_popups.length; i++){
		vregions_popups[i].style.display = "none";
	}

	if (!action || action == "open"){
		sepia.style.display = "block";
		vregions_popup.style.display = "block";
	}
	if (action == "close"){
		sepia.style.display = "none";
		//console.log(vregions_popup);
		for (var i = 0; i < vregions_popups.length; i++){
			vregions_popups[i].style.display = "none";
		}
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
		sessid: BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action: "check-auto-geo-ness"
	};

	BX.ajax({
		url: ajax_url,
		data: vRegionsArr,
		method: "POST",
		dataType: "json",
		onsuccess: function(answer){
			// console.log(vRegionsArr);
			console.log(answer);
			if (answer.success){ // ���� ���� ���������� ��������������
				// �������� ���������� �������������� ������������
				if (answer.method == "google"){
					if (navigator.geolocation){
						navigator.geolocation.getCurrentPosition(
							geolocationSuccess,
							geolocationFailure
						);
					}
					else{ // ���� �� ���������� (��������, �� ���� ����������)
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
		sessid: BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action: "get-php-coords"
	};

	BX.ajax({
		url: ajax_url,
		data: vRegionsArr,
		method: "POST",
		dataType: "json",
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
	var location = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
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
	vRegionsArr = {};
	vRegionsArr.sessid = BX.bitrix_sessid();
	vRegionsArr.site_id = BX.message("SITE_ID");
	vRegionsArr.action = "get-closest-region";
	// vRegionsArr.longitude = gvRegionsArr.coords.longitude;
	vRegionsArr.longitude = lon;
	// vRegionsArr.latitude = gvRegionsArr.coords.latitude;
	vRegionsArr.latitude = lat;
	// console.log(vRegionsArr);
	// debugger

	BX.ajax({
		url: ajax_url,
		data: vRegionsArr,
		method: "POST",
		dataType: "json",
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