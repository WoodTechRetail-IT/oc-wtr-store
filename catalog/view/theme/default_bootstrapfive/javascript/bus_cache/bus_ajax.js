/*  Аўтар: "БуслікДрэў" ( https://buslikdrev.by/ )
    © 2016-2022; BuslikDrev - Усе правы захаваныя. 
    busAjax v0.4 */
'use strict';
'use asm';
var busAjax = function(url, setting) {
	if (typeof setting['metod'] === 'undefined') {
		setting['metod'] = 'GET';
	}
	if (typeof setting['responseType'] === 'undefined') {
		setting['responseType'] = 'json';
	}
	if (typeof setting['dataType'] === 'undefined') {
		setting['dataType'] = 'text';
	}
	if (typeof setting['data'] === 'undefined') {
		setting['data'] = '';
	}
	if (typeof setting['async'] === 'undefined') {
		setting['async'] = true;
	}
	if (typeof setting['user'] === 'undefined') {
		setting['user'] = null;
	}
	if (typeof setting['password'] === 'undefined') {
		setting['password'] = null;
	}
	if (typeof setting['success'] === 'undefined') {
		setting['success'] = function(json) {};
	}
	if (typeof setting['error'] === 'undefined') {
		setting['error'] = function(error) {};
	}
	if (typeof setting['complete'] === 'undefined') {
		setting['complete'] = function(json) {};
	}
	if (typeof setting['debug'] === 'undefined') {
		setting['debug'] = false;
	}
	var datanew = null;
	if (setting['data']) {
		if (setting['dataType'] == 'json') {
			datanew = JSON.stringify(setting['data']);
		} else {
			if (typeof FormData !== 'undefined') {
				datanew = new FormData();
				if (typeof setting['data'] == 'object') {
					for (var i in setting['data']) {
						if (typeof setting['data'][i] == 'object') {
							for (var i2 in setting['data'][i]) {
								if (typeof setting['data'][i][i2] == 'object') {
									for (var i3 in setting['data'][i][i2]) {
										datanew.append(i + '[' + i2 + ']' + '[' + i3 + ']', setting['data'][i][i2][i3]);
									}
								} else {
									datanew.append(i + '[' + i2 + ']', setting['data'][i][i2]);
								}
							}
						} else {
							datanew.append(i, setting['data'][i]);
						}
					}
				} else {
					datanew = setting['data'];
				}
			} else {
				datanew = [];
				if (typeof setting['data'] == 'object') {
					for (var i in setting['data']) {
						if (typeof setting['data'][i] == 'object') {
							for (var i2 in setting['data'][i]) {
								if (typeof setting['data'][i][i2] == 'object') {
									for (var i3 in setting['data'][i][i2]) {
										datanew.push(encodeURIComponent(i) + '[' + encodeURIComponent(i2) + ']' + '[' + encodeURIComponent(i3) + ']=' + encodeURIComponent(setting['data'][i][i2][i3]));
									}
								} else {
									datanew.push(encodeURIComponent(i) + '[' + encodeURIComponent(i2) + ']=' + encodeURIComponent(setting['data'][i][i2]));
								}
							}
						} else {
							datanew.push(encodeURIComponent(i) + '=' + encodeURIComponent(setting['data'][i]));
						}
					}
				} else {
					datanew = setting['data'];
				}

				datanew = datanew.join('&').replace(/%20/g, '+');
			}
		}
	}

	var xhr = new XMLHttpRequest();
	xhr.open(setting['metod'], url, setting['async'], setting['user'], setting['password']);
	xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	if (typeof FormData === 'undefined') {
		if (setting['dataType'] == 'json') {
			xhr.setRequestHeader('Content-type', 'application/json;charset=UTF-8');
		} else if (setting['dataType'] == 'text') {
			xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');
		}
	}
	if (setting['responseType']) {
		xhr.responseType = setting['responseType']; //\"text\" – строка,\"arraybuffer\", \"blob\", \"document\", \"json\" – JSON (парсится автоматически).
	}
	if (setting['debug']) {
		console.log('xhr data: ', datanew);
	}
	xhr.send(datanew);
	xhr.onload = function(oEvent) {
		if (xhr.status == 200) {
			setting['success'](xhr.response, xhr);
			setting['complete'](xhr.response, xhr);
			return xhr;
		} else {
			var ajaxOptions = setting;
			var thrownError = false;
			setting['error'](xhr, ajaxOptions, thrownError);
			setting['complete'](xhr, ajaxOptions, thrownError);
			return xhr;
		}
	};
};

if (typeof window.CustomEvent !== 'function') {
	window.CustomEvent = function(event, params) {
		params = params || {bubbles:false, cancelable:false, detail:null};

		var evt = document.createEvent('CustomEvent');
		evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);

		return evt;
	};
}

document.dispatchEvent(new CustomEvent('busAjax', {bubbles: true}));