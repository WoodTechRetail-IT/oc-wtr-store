/*  Аўтар: "БуслікДрэў" ( https://buslikdrev.by/ )
    © 2016-2022; BuslikDrev - Усе правы захаваныя. 
    busCritical v0.6 */
'use strict';
'use asm';
var busCritical = {
	'setting':{
		'html_all':false,
		'html_elements':['font-face', 'keyframes', '*', '::after, ::before', ']']
	},
	'toUnicodeIcon':function(s) {
		if (typeof s != 'string') {
			s = this;
		}
		var r = '';
		var x = s.length

		for (var i = 0; i < x; i++) {
			r += s[i].charCodeAt(0).toString(16);
		};

		return '\\' + r;
	},
	'html':function(element, setting, length) {
		if (typeof setting === 'undefined') {
			setting = {};
		}
		if (typeof setting['all'] === 'undefined') {
			setting['all'] = false;
		}
		var html = {};

		if (element.tagName) {
			html[element.tagName.toLowerCase()] = element.tagName.toLowerCase();
			if (element.id) {
				html['#' + element.id.toLowerCase()] = '#' + element.id.toLowerCase();
			}
			for (var i = 0; i < element.classList.length; i++) {
				html['.' + element.classList[i].toLowerCase()] = '.' + element.classList[i].toLowerCase();
			}
			if (element.children.length) {
				for (var i = 0; i < element.children.length; i++) {
					if (setting['all'] || element.children[i].tagName && element.children[i].offsetTop <= window.innerHeight) {
						var child = busCritical.html(element.children[i], setting);
						for (var i2 in child) {
							html[i2] = child[i2];
						}
					}
				}
			}
		}

		return html;
	},
	'css':function(file) {
		var s, sh, e, c, y, z;
		s = {length:0};
		sh = document.styleSheets;
		z = sh.length;
		for (var i = 0; i < z; i++) {
			if (typeof sh[i].cssRules == 'undefined') {
				sh[i].cssRules = sh[i].rules;
			}
			if (sh[i].cssRules != 'undefined') {
				y = sh[i].cssRules.length;
				for (var i2 = 0; i2 < y; i2++) {
					e = sh[i].cssRules[i2];
					if (1 == 0 && e.type == 1 && e.style) {
						c = e.style.getPropertyValue('content');

						if (c && c.indexOf('url') == -1 && c != '"/"') {
							c = busCritical.toUnicodeIcon(c.replace(/^[\"]+|[\"]+$/g, ''));
						}

						if (c != '\\' && c.substring(0, 1) == '\\') {
							/* fix */
							s[s.length++] = e.cssText.replace(/\bcontent: \"(.[^\"]*?)\"/, 'content: "' + c + '"') + '\r\n';
							/* fix */
						} else {
							s[s.length++] = e.cssText + '\r\n';
						}
					} else if (e.type == 1 && e.style) {
						s[s.length++] = e.cssText + '\r\n';
					} else if (e.type == 4 && e.cssRules) {
						if (e.cssText.indexOf('.') != -1) {
							s[s.length++] = e.cssText + '\r\n';
						}
					} else {
						s[s.length++] = e.cssText + '\r\n';
					}
				}
			}
		}

		return s;
	},
	'critical':function(search) {
		var critical = '';
		var element = document.querySelector(search);

		if (element) {
			// авто - tag, class, id
			var auto = busCritical.html(element, {'all':busCritical.setting['html_all']});
			// ручное - tag, class, id
			var manual = busCritical.setting['html_elements'];

			for (var i in manual) {
				auto[manual[i]] = manual[i];
			}

			// все стили
			var styles = busCritical.css()

			//console.log(styles);
			//console.log(auto);
			//console.log(1 + ' ', critical);

			var x, y, z;
			z = styles.length;
			for (var i = 0; i < z; i++) {
				search = false;
				x = styles[i];

				for (var i2 in auto) {
					y = auto[i2];
					if (x.indexOf(y + ',') != -1 || x.indexOf(y + ':') != -1 || x.indexOf(y + ' ') != -1) {
						search = true;
					}
				}

				if (search) {
					critical += styles[i];
				}
			}

			//console.log(2 + ' ', critical);
		}

		return critical;
	}
};

if (typeof window.CustomEvent !== 'function') {
	window.CustomEvent = function(event, params) {
		params = params || {bubbles:false, cancelable:false, detail:null};

		var evt = document.createEvent('CustomEvent');
		evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);

		return evt;
	};
}

if (document.readyState == 'complete') {
	document.dispatchEvent(new CustomEvent('busCritical', {bubbles: true}));
} else {
	window.addEventListener('load', function() {
		document.dispatchEvent(new CustomEvent('busCritical', {bubbles: true}));
	});
}