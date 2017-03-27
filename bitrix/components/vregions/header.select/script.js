window.AristovVregions = (function(){
	this.ajaxPath = '/bitrix/components/vregions/header.select/ajax.php';
});

AristovVregions.prototype.setCookie = function(cookie, callback){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "set-cookie",
		cookie : cookie
	};

	BX.ajax({
		url      : this.ajaxPath,
		data     : ajaxArr,
		method   : "POST",
		dataType : "json",
		onsuccess: function(answer){
			// console.log(ajaxArr);
			console.log(answer);

			if (typeof callback !== 'undefined'){
				callback(answer);
			}
		},
		onfailure: function(){

		}
	});

	return false;
};

AristovVregions.prototype.isNeedLocationCheck = function(callback){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "check-auto-geo-ness"
	};

	BX.ajax({
		url      : this.ajaxPath,
		data     : ajaxArr,
		method   : "POST",
		dataType : "json",
		onsuccess: function(answer){
			// console.log(ajaxArr);
			console.log(answer);

			if (typeof callback !== 'undefined'){
				callback(answer);
			}
		},
		onfailure: function(){

		}
	});
};

AristovVregions.prototype.checkLocation = function(method, callback){
	if (method == "google"){
		this.getCoordsByHtml5(
			function(answer){
				if (typeof callback !== 'undefined'){
					callback(answer);
				}
			}
		);
	}
	if (method == "sxgeo"){
		this.getCoordsByPHP(
			function(answer){
				if (typeof callback !== 'undefined'){
					callback(answer);
				}
			}
		);
	}
};

AristovVregions.prototype.getCoordsByPHP = function(callback){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "get-php-coords"
	};

	BX.ajax({
		url      : this.ajaxPath,
		data     : ajaxArr,
		method   : "POST",
		dataType : "json",
		onsuccess: function(answer){
			// console.log(ajaxArr);
			console.log(answer);

			if (typeof callback !== 'undefined'){
				callback(answer);
			}
		},
		onfailure: function(){

		}
	});
};

AristovVregions.prototype.getClosestRegion = function(longitude, latitude, callback){
	var ajaxArr = {
		sessid   : BX.bitrix_sessid(),
		site_id  : BX.message('SITE_ID'),
		action   : "get-closest-region",
		longitude: longitude,
		latitude : latitude,
	};

	BX.ajax({
		url      : this.ajaxPath,
		data     : ajaxArr,
		method   : "POST",
		dataType : "json",
		onsuccess: function(answer){
			// console.log(ajaxArr);
			console.log(answer);

			if (typeof callback !== 'undefined'){
				callback(answer);
			}
		},
		onfailure: function(){

		}
	});
};

AristovVregions.prototype.getCoordsByHtml5 = function(callback){
	if (navigator.geolocation){
		navigator.geolocation.getCurrentPosition(
			function(position){
				console.log(position);

				if (typeof callback !== 'undefined'){
					callback({
						lat: position.coords.latitude,
						lon: position.coords.longitude
					});
				}
			},
			function(positionError){
				console.log(positionError.message);
			}
		);
	}
	else{
		console.log("Html5 geolocation fail")
	}
};

AristovVregions.prototype.findRegionByNameMask = function(mask, callback){
	var ajaxArr = {
		sessid : BX.bitrix_sessid(),
		site_id: BX.message('SITE_ID'),
		action : "find-region-by-name-mask",
		mask   : mask,
	};

	BX.ajax({
		url      : this.ajaxPath,
		data     : ajaxArr,
		method   : "POST",
		dataType : "json",
		onsuccess: function(answer){
			// console.log(ajaxArr);
			// console.log(answer);

			if (typeof callback !== 'undefined'){
				callback(answer);
			}
		},
		onfailure: function(){

		}
	});
};