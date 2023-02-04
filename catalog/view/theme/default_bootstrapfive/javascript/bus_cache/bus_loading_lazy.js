// *   Аўтар: "БуслікДрэў" ( https://buslikdrev.by/ )
// *   © 2016-2022; BuslikDrev - Усе правы захаваныя.
'use strict';
'use asm';
var busLoadingLazy = {
	'validate':!('loading' in HTMLImageElement.prototype && 'loading' in HTMLIFrameElement.prototype) && 'onscroll' in window,
	'setting':{
		browser:{name:'', version:0},
		elements: '[loading="lazy"][data-busloadinglazy-src], [loading="lazy"][data-busloadinglazy-id]',
		element:'',
		fps: 10,
		fpsNow: 0,
		offsetTop: 50,
		width: 0,
		lazy: false,
		start: 0,
		quantity: 10,
		exception: {},
		debug: false
	},
	'browser':function() {
		var userAgent = self.navigator.userAgent.toLowerCase();
		var browsers = userAgent.match(/(firefox|chrome|safari|brave|vivaldi|edge|msie|opera|yabrowser|huaweibrowser|miuibrowser)\/(\d+\.)/);
		if (browsers && browsers[1] == 'chrome' && browsers[2] >= '40.0') {
			var browsers2 = userAgent.match(/(brave|vivaldi|edge|msie|opera|yabrowser|huaweibrowser|miuibrowser)\/(\d+\.)/);
			if (browsers2) {
				browsers = browsers2;
			}
		}
		if (!browsers) {
			browsers = ['','',1000];
		}
		if (busLoadingLazy.setting['debug']) {
			console.log(browsers)
		}

		return {name:browsers[1], version:parseFloat(browsers[2])};
	},
	'status':false,
	'start':function(setting) {
		busLoadingLazy.status = true;
		busLoadingLazy.setting['fpsNow'] += 1;
		if (busLoadingLazy.setting['fpsNow'] > busLoadingLazy.setting['fps']) {
			document.dispatchEvent(new CustomEvent('busLoadingLazy', {bubbles: true}));
			busLoadingLazy.setting['fpsNow'] = 0;
		}

		if (typeof setting !== 'undefined' && typeof setting === 'object' && !('target' in setting)) {
			for (var i in setting) {
				busLoadingLazy.setting[i] = setting[i];
			}
		}

		if (typeof busLoadingLazy.setting['browser'] != 'undefined') {
			var status;

			if (typeof busLoadingLazy.setting['browser']['name'] != busLoadingLazy.browser['name']) {
				status = true;

				if (typeof busLoadingLazy.setting['browser']['version'] != 'undefined' && busLoadingLazy.setting['browser']['version'] <= busLoadingLazy.browser['version']) {
					status = false;
				}
			}

			if (status) {
				return false;
			}
		}

		var elements, i, i1, i2, ne, nel, width, remove;

		if (busLoadingLazy.setting['element']) {
			elements = document.querySelectorAll(busLoadingLazy.setting['element']);
			busLoadingLazy.setting['element'] = '';
		} else {
			elements = document.querySelectorAll(busLoadingLazy.setting['elements']);
		}

		if (elements.length) {
			for (i = busLoadingLazy.setting['start']; i < busLoadingLazy.setting['quantity']; ++i) {
				if (elements[i] && elements[i].getAttribute('data-busloadinglazy-res')) {
					width = elements[i].getAttribute('data-busloadinglazy-res');
				} else {
					width = busLoadingLazy.setting['width'];
				}
				if (elements[i] && window.getComputedStyle(elements[i]).display != 'none' && (busLoadingLazy.setting['lazy'] || (elements[i].getBoundingClientRect().top-busLoadingLazy.setting['offsetTop']) <= window.innerHeight || (elements[i].getBoundingClientRect().bottom-busLoadingLazy.setting['offsetTop']) <= window.innerHeight)) {
					if (elements[i].getAttribute('data-busloadinglazy-src') && !busLoadingLazy.setting['exception'][elements[i].getAttribute('data-busloadinglazy-src')]) {
						elements[i].setAttribute('src', elements[i].getAttribute('data-busloadinglazy-src'));
						elements[i].removeAttribute('data-busloadinglazy-src');
						elements[i].style['opacity'] = 1;
					} else if (busLoadingLazy.setting['lazy'] || window.innerWidth > width && elements[i].getAttribute('data-busloadinglazy-id') && !busLoadingLazy.setting['exception'][elements[i].getAttribute('data-busloadinglazy-id')]) {
						busLoadingLazy.setting['lazy'] = false;
						ne = document.createElement('div');
						ne.innerHTML = elements[i].textContent || elements[i].getElementsByTagName('noscript')[0].innerHTML;
						if (ne.children.length) {
							for (i1 = 0; i1 < ne.children.length; ++i1) {
								nel = document.createElement(ne.children[i1].tagName);
								nel.innerHTML = ne.children[i1].innerHTML;
								for (i2 = 0; i2 < ne.children[i1].attributes.length; ++i2) {
									nel.setAttribute(ne.children[i1].attributes[i2].name, ne.children[i1].attributes[i2].value);
								}

								remove = nel.querySelectorAll('div[data-busloadinglazy-remove]');
								if (remove) {
									for (i2 = 0; i2 < remove.length; ++i2) {
										remove[i2].parentNode.removeChild(remove[i2]);
									}
								}
								elements[i].parentNode.insertBefore(nel, elements[i]);
							}
						}

						document.dispatchEvent(new CustomEvent('busLoadingLazyId-' + elements[i].getAttribute('data-busloadinglazy-id'), {bubbles: true}));
						//elements[i].parentNode.insertBefore(ne, elements[i].parentNode);
						elements[i].parentNode.removeChild(elements[i]);
					}
				}
			}
		} else {
			window.addEventListener('scroll', busLoadingLazy.start);
			window.addEventListener('resize', busLoadingLazy.start);
			window.addEventListener('click', busLoadingLazy.start);
			window.addEventListener('orientationchange', busLoadingLazy.start);
		}
	}
};

if (!('getComputedStyle' in window)) {
	busLoadingLazy.status = true;
}

if (busLoadingLazy.status == false) {
	if (typeof window.CustomEvent !== 'function') {
		window.CustomEvent = function(event, params) {
			params = params || {bubbles:false, cancelable:false, detail:null};

			var evt = document.createEvent('CustomEvent');
			evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);

			return evt;
		};
	}
	busLoadingLazy.browser = busLoadingLazy.browser();
	document.dispatchEvent(new CustomEvent('busLoadingLazyBefore', {bubbles: true}));
}
if (busLoadingLazy.status == false) {
	if ('readyState' in document && document.readyState != 'complete') {
		window.addEventListener('load', busLoadingLazy.start);
	} else {
		busLoadingLazy.start();
	}
	window.addEventListener('scroll', busLoadingLazy.start, false);
	window.addEventListener('resize', busLoadingLazy.start, false);
	window.addEventListener('click', busLoadingLazy.start, false);
	window.addEventListener('orientationchange', busLoadingLazy.start, false);
	document.dispatchEvent(new CustomEvent('busLoadingLazyAfter', {bubbles: true}));
}