const OCF_PREFIX = 'ocf';

/*! nouislider - 14.6.4 - 3/18/2021 */
!function(t){"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?module.exports=t():window.noUiSlider=t()}(function(){"use strict";var lt="14.6.4";function ut(t){t.parentElement.removeChild(t)}function ct(t){return null!=t}function pt(t){t.preventDefault()}function o(t){return"number"==typeof t&&!isNaN(t)&&isFinite(t)}function ft(t,e,r){0<r&&(mt(t,e),setTimeout(function(){gt(t,e)},r))}function dt(t){return Math.max(Math.min(t,100),0)}function ht(t){return Array.isArray(t)?t:[t]}function e(t){var e=(t=String(t)).split(".");return 1<e.length?e[1].length:0}function mt(t,e){t.classList&&!/\s/.test(e)?t.classList.add(e):t.className+=" "+e}function gt(t,e){t.classList&&!/\s/.test(e)?t.classList.remove(e):t.className=t.className.replace(new RegExp("(^|\\b)"+e.split(" ").join("|")+"(\\b|$)","gi")," ")}function vt(t){var e=void 0!==window.pageXOffset,r="CSS1Compat"===(t.compatMode||"");return{x:e?window.pageXOffset:r?t.documentElement.scrollLeft:t.body.scrollLeft,y:e?window.pageYOffset:r?t.documentElement.scrollTop:t.body.scrollTop}}function c(t,e){return 100/(e-t)}function p(t,e,r){return 100*e/(t[r+1]-t[r])}function f(t,e){for(var r=1;t>=e[r];)r+=1;return r}function r(t,e,r){if(r>=t.slice(-1)[0])return 100;var n,i,o=f(r,t),s=t[o-1],a=t[o],l=e[o-1],u=e[o];return l+(i=r,p(n=[s,a],n[0]<0?i+Math.abs(n[0]):i-n[0],0)/c(l,u))}function n(t,e,r,n){if(100===n)return n;var i,o,s=f(n,t),a=t[s-1],l=t[s];return r?(l-a)/2<n-a?l:a:e[s-1]?t[s-1]+(i=n-t[s-1],o=e[s-1],Math.round(i/o)*o):n}function s(t,e,r){var n;if("number"==typeof e&&(e=[e]),!Array.isArray(e))throw new Error("noUiSlider ("+lt+"): 'range' contains invalid value.");if(!o(n="min"===t?0:"max"===t?100:parseFloat(t))||!o(e[0]))throw new Error("noUiSlider ("+lt+"): 'range' value isn't numeric.");r.xPct.push(n),r.xVal.push(e[0]),n?r.xSteps.push(!isNaN(e[1])&&e[1]):isNaN(e[1])||(r.xSteps[0]=e[1]),r.xHighestCompleteStep.push(0)}function a(t,e,r){if(e)if(r.xVal[t]!==r.xVal[t+1]){r.xSteps[t]=p([r.xVal[t],r.xVal[t+1]],e,0)/c(r.xPct[t],r.xPct[t+1]);var n=(r.xVal[t+1]-r.xVal[t])/r.xNumSteps[t],i=Math.ceil(Number(n.toFixed(3))-1),o=r.xVal[t]+r.xNumSteps[t]*i;r.xHighestCompleteStep[t]=o}else r.xSteps[t]=r.xHighestCompleteStep[t]=r.xVal[t]}function i(t,e,r){var n;this.xPct=[],this.xVal=[],this.xSteps=[r||!1],this.xNumSteps=[!1],this.xHighestCompleteStep=[],this.snap=e;var i=[];for(n in t)t.hasOwnProperty(n)&&i.push([t[n],n]);for(i.length&&"object"==typeof i[0][0]?i.sort(function(t,e){return t[0][0]-e[0][0]}):i.sort(function(t,e){return t[0]-e[0]}),n=0;n<i.length;n++)s(i[n][1],i[n][0],this);for(this.xNumSteps=this.xSteps.slice(0),n=0;n<this.xNumSteps.length;n++)a(n,this.xNumSteps[n],this)}i.prototype.getDistance=function(t){var e,r=[];for(e=0;e<this.xNumSteps.length-1;e++){var n=this.xNumSteps[e];if(n&&t/n%1!=0)throw new Error("noUiSlider ("+lt+"): 'limit', 'margin' and 'padding' of "+this.xPct[e]+"% range must be divisible by step.");r[e]=p(this.xVal,t,e)}return r},i.prototype.getAbsoluteDistance=function(t,e,r){var n,i=0;if(t<this.xPct[this.xPct.length-1])for(;t>this.xPct[i+1];)i++;else t===this.xPct[this.xPct.length-1]&&(i=this.xPct.length-2);r||t!==this.xPct[i+1]||i++;var o=1,s=e[i],a=0,l=0,u=0,c=0;for(n=r?(t-this.xPct[i])/(this.xPct[i+1]-this.xPct[i]):(this.xPct[i+1]-t)/(this.xPct[i+1]-this.xPct[i]);0<s;)a=this.xPct[i+1+c]-this.xPct[i+c],100<e[i+c]*o+100-100*n?(l=a*n,o=(s-100*n)/e[i+c],n=1):(l=e[i+c]*a/100*o,o=0),r?(u-=l,1<=this.xPct.length+c&&c--):(u+=l,1<=this.xPct.length-c&&c++),s=e[i+c]*o;return t+u},i.prototype.toStepping=function(t){return t=r(this.xVal,this.xPct,t)},i.prototype.fromStepping=function(t){return function(t,e,r){if(100<=r)return t.slice(-1)[0];var n,i=f(r,e),o=t[i-1],s=t[i],a=e[i-1],l=e[i];return n=[o,s],(r-a)*c(a,l)*(n[1]-n[0])/100+n[0]}(this.xVal,this.xPct,t)},i.prototype.getStep=function(t){return t=n(this.xPct,this.xSteps,this.snap,t)},i.prototype.getDefaultStep=function(t,e,r){var n=f(t,this.xPct);return(100===t||e&&t===this.xPct[n-1])&&(n=Math.max(n-1,1)),(this.xVal[n]-this.xVal[n-1])/r},i.prototype.getNearbySteps=function(t){var e=f(t,this.xPct);return{stepBefore:{startValue:this.xVal[e-2],step:this.xNumSteps[e-2],highestStep:this.xHighestCompleteStep[e-2]},thisStep:{startValue:this.xVal[e-1],step:this.xNumSteps[e-1],highestStep:this.xHighestCompleteStep[e-1]},stepAfter:{startValue:this.xVal[e],step:this.xNumSteps[e],highestStep:this.xHighestCompleteStep[e]}}},i.prototype.countStepDecimals=function(){var t=this.xNumSteps.map(e);return Math.max.apply(null,t)},i.prototype.convert=function(t){return this.getStep(this.toStepping(t))};var l={to:function(t){return void 0!==t&&t.toFixed(2)},from:Number},u={target:"target",base:"base",origin:"origin",handle:"handle",handleLower:"handle-lower",handleUpper:"handle-upper",touchArea:"touch-area",horizontal:"horizontal",vertical:"vertical",background:"background",connect:"connect",connects:"connects",ltr:"ltr",rtl:"rtl",textDirectionLtr:"txt-dir-ltr",textDirectionRtl:"txt-dir-rtl",draggable:"draggable",drag:"state-drag",tap:"state-tap",active:"active",tooltip:"tooltip",pips:"pips",pipsHorizontal:"pips-horizontal",pipsVertical:"pips-vertical",marker:"marker",markerHorizontal:"marker-horizontal",markerVertical:"marker-vertical",markerNormal:"marker-normal",markerLarge:"marker-large",markerSub:"marker-sub",value:"value",valueHorizontal:"value-horizontal",valueVertical:"value-vertical",valueNormal:"value-normal",valueLarge:"value-large",valueSub:"value-sub"},bt={tooltips:".__tooltips",aria:".__aria"};function d(t){if("object"==typeof(e=t)&&"function"==typeof e.to&&"function"==typeof e.from)return!0;var e;throw new Error("noUiSlider ("+lt+"): 'format' requires 'to' and 'from' methods.")}function h(t,e){if(!o(e))throw new Error("noUiSlider ("+lt+"): 'step' is not numeric.");t.singleStep=e}function m(t,e){if(!o(e))throw new Error("noUiSlider ("+lt+"): 'keyboardPageMultiplier' is not numeric.");t.keyboardPageMultiplier=e}function g(t,e){if(!o(e))throw new Error("noUiSlider ("+lt+"): 'keyboardDefaultStep' is not numeric.");t.keyboardDefaultStep=e}function v(t,e){if("object"!=typeof e||Array.isArray(e))throw new Error("noUiSlider ("+lt+"): 'range' is not an object.");if(void 0===e.min||void 0===e.max)throw new Error("noUiSlider ("+lt+"): Missing 'min' or 'max' in 'range'.");if(e.min===e.max)throw new Error("noUiSlider ("+lt+"): 'range' 'min' and 'max' cannot be equal.");t.spectrum=new i(e,t.snap,t.singleStep)}function b(t,e){if(e=ht(e),!Array.isArray(e)||!e.length)throw new Error("noUiSlider ("+lt+"): 'start' option is incorrect.");t.handles=e.length,t.start=e}function x(t,e){if("boolean"!=typeof(t.snap=e))throw new Error("noUiSlider ("+lt+"): 'snap' option must be a boolean.")}function S(t,e){if("boolean"!=typeof(t.animate=e))throw new Error("noUiSlider ("+lt+"): 'animate' option must be a boolean.")}function w(t,e){if("number"!=typeof(t.animationDuration=e))throw new Error("noUiSlider ("+lt+"): 'animationDuration' option must be a number.")}function y(t,e){var r,n=[!1];if("lower"===e?e=[!0,!1]:"upper"===e&&(e=[!1,!0]),!0===e||!1===e){for(r=1;r<t.handles;r++)n.push(e);n.push(!1)}else{if(!Array.isArray(e)||!e.length||e.length!==t.handles+1)throw new Error("noUiSlider ("+lt+"): 'connect' option doesn't match handle count.");n=e}t.connect=n}function E(t,e){switch(e){case"horizontal":t.ort=0;break;case"vertical":t.ort=1;break;default:throw new Error("noUiSlider ("+lt+"): 'orientation' option is invalid.")}}function C(t,e){if(!o(e))throw new Error("noUiSlider ("+lt+"): 'margin' option must be numeric.");0!==e&&(t.margin=t.spectrum.getDistance(e))}function P(t,e){if(!o(e))throw new Error("noUiSlider ("+lt+"): 'limit' option must be numeric.");if(t.limit=t.spectrum.getDistance(e),!t.limit||t.handles<2)throw new Error("noUiSlider ("+lt+"): 'limit' option is only supported on linear sliders with 2 or more handles.")}function N(t,e){var r;if(!o(e)&&!Array.isArray(e))throw new Error("noUiSlider ("+lt+"): 'padding' option must be numeric or array of exactly 2 numbers.");if(Array.isArray(e)&&2!==e.length&&!o(e[0])&&!o(e[1]))throw new Error("noUiSlider ("+lt+"): 'padding' option must be numeric or array of exactly 2 numbers.");if(0!==e){for(Array.isArray(e)||(e=[e,e]),t.padding=[t.spectrum.getDistance(e[0]),t.spectrum.getDistance(e[1])],r=0;r<t.spectrum.xNumSteps.length-1;r++)if(t.padding[0][r]<0||t.padding[1][r]<0)throw new Error("noUiSlider ("+lt+"): 'padding' option must be a positive number(s).");var n=e[0]+e[1],i=t.spectrum.xVal[0];if(1<n/(t.spectrum.xVal[t.spectrum.xVal.length-1]-i))throw new Error("noUiSlider ("+lt+"): 'padding' option must not exceed 100% of the range.")}}function k(t,e){switch(e){case"ltr":t.dir=0;break;case"rtl":t.dir=1;break;default:throw new Error("noUiSlider ("+lt+"): 'direction' option was not recognized.")}}function U(t,e){if("string"!=typeof e)throw new Error("noUiSlider ("+lt+"): 'behaviour' must be a string containing options.");var r=0<=e.indexOf("tap"),n=0<=e.indexOf("drag"),i=0<=e.indexOf("fixed"),o=0<=e.indexOf("snap"),s=0<=e.indexOf("hover"),a=0<=e.indexOf("unconstrained");if(i){if(2!==t.handles)throw new Error("noUiSlider ("+lt+"): 'fixed' behaviour must be used with 2 handles");C(t,t.start[1]-t.start[0])}if(a&&(t.margin||t.limit))throw new Error("noUiSlider ("+lt+"): 'unconstrained' behaviour cannot be used with margin or limit");t.events={tap:r||o,drag:n,fixed:i,snap:o,hover:s,unconstrained:a}}function A(t,e){if(!1!==e)if(!0===e){t.tooltips=[];for(var r=0;r<t.handles;r++)t.tooltips.push(!0)}else{if(t.tooltips=ht(e),t.tooltips.length!==t.handles)throw new Error("noUiSlider ("+lt+"): must pass a formatter for all handles.");t.tooltips.forEach(function(t){if("boolean"!=typeof t&&("object"!=typeof t||"function"!=typeof t.to))throw new Error("noUiSlider ("+lt+"): 'tooltips' must be passed a formatter or 'false'.")})}}function V(t,e){d(t.ariaFormat=e)}function D(t,e){d(t.format=e)}function M(t,e){if("boolean"!=typeof(t.keyboardSupport=e))throw new Error("noUiSlider ("+lt+"): 'keyboardSupport' option must be a boolean.")}function O(t,e){t.documentElement=e}function L(t,e){if("string"!=typeof e&&!1!==e)throw new Error("noUiSlider ("+lt+"): 'cssPrefix' must be a string or `false`.");t.cssPrefix=e}function z(t,e){if("object"!=typeof e)throw new Error("noUiSlider ("+lt+"): 'cssClasses' must be an object.");if("string"==typeof t.cssPrefix)for(var r in t.cssClasses={},e)e.hasOwnProperty(r)&&(t.cssClasses[r]=t.cssPrefix+e[r]);else t.cssClasses=e}function xt(e){var r={margin:0,limit:0,padding:0,animate:!0,animationDuration:300,ariaFormat:l,format:l},n={step:{r:!1,t:h},keyboardPageMultiplier:{r:!1,t:m},keyboardDefaultStep:{r:!1,t:g},start:{r:!0,t:b},connect:{r:!0,t:y},direction:{r:!0,t:k},snap:{r:!1,t:x},animate:{r:!1,t:S},animationDuration:{r:!1,t:w},range:{r:!0,t:v},orientation:{r:!1,t:E},margin:{r:!1,t:C},limit:{r:!1,t:P},padding:{r:!1,t:N},behaviour:{r:!0,t:U},ariaFormat:{r:!1,t:V},format:{r:!1,t:D},tooltips:{r:!1,t:A},keyboardSupport:{r:!0,t:M},documentElement:{r:!1,t:O},cssPrefix:{r:!0,t:L},cssClasses:{r:!0,t:z}},i={connect:!1,direction:"ltr",behaviour:"tap",orientation:"horizontal",keyboardSupport:!0,cssPrefix:"noUi-",cssClasses:u,keyboardPageMultiplier:5,keyboardDefaultStep:10};e.format&&!e.ariaFormat&&(e.ariaFormat=e.format),Object.keys(n).forEach(function(t){if(!ct(e[t])&&void 0===i[t]){if(n[t].r)throw new Error("noUiSlider ("+lt+"): '"+t+"' is required.");return!0}n[t].t(r,ct(e[t])?e[t]:i[t])}),r.pips=e.pips;var t=document.createElement("div"),o=void 0!==t.style.msTransform,s=void 0!==t.style.transform;r.transformRule=s?"transform":o?"msTransform":"webkitTransform";return r.style=[["left","top"],["right","bottom"]][r.dir][r.ort],r}function H(t,b,o){var l,u,s,c,i,a,e,p,f=window.navigator.pointerEnabled?{start:"pointerdown",move:"pointermove",end:"pointerup"}:window.navigator.msPointerEnabled?{start:"MSPointerDown",move:"MSPointerMove",end:"MSPointerUp"}:{start:"mousedown touchstart",move:"mousemove touchmove",end:"mouseup touchend"},d=window.CSS&&CSS.supports&&CSS.supports("touch-action","none")&&function(){var t=!1;try{var e=Object.defineProperty({},"passive",{get:function(){t=!0}});window.addEventListener("test",null,e)}catch(t){}return t}(),h=t,y=b.spectrum,x=[],S=[],m=[],g=0,v={},w=t.ownerDocument,E=b.documentElement||w.documentElement,C=w.body,P=-1,N=0,k=1,U=2,A="rtl"===w.dir||1===b.ort?0:100;function V(t,e){var r=w.createElement("div");return e&&mt(r,e),t.appendChild(r),r}function D(t,e){var r=V(t,b.cssClasses.origin),n=V(r,b.cssClasses.handle);return V(n,b.cssClasses.touchArea),n.setAttribute("data-handle",e),b.keyboardSupport&&(n.setAttribute("tabindex","0"),n.addEventListener("keydown",function(t){return function(t,e){if(O()||L(e))return!1;var r=["Left","Right"],n=["Down","Up"],i=["PageDown","PageUp"],o=["Home","End"];b.dir&&!b.ort?r.reverse():b.ort&&!b.dir&&(n.reverse(),i.reverse());var s,a=t.key.replace("Arrow",""),l=a===i[0],u=a===i[1],c=a===n[0]||a===r[0]||l,p=a===n[1]||a===r[1]||u,f=a===o[0],d=a===o[1];if(!(c||p||f||d))return!0;if(t.preventDefault(),p||c){var h=b.keyboardPageMultiplier,m=c?0:1,g=at(e),v=g[m];if(null===v)return!1;!1===v&&(v=y.getDefaultStep(S[e],c,b.keyboardDefaultStep)),(u||l)&&(v*=h),v=Math.max(v,1e-7),v*=c?-1:1,s=x[e]+v}else s=d?b.spectrum.xVal[b.spectrum.xVal.length-1]:b.spectrum.xVal[0];return rt(e,y.toStepping(s),!0,!0),J("slide",e),J("update",e),J("change",e),J("set",e),!1}(t,e)})),n.setAttribute("role","slider"),n.setAttribute("aria-label",e?"Max":"Min"),n.setAttribute("aria-orientation",b.ort?"vertical":"horizontal"),0===e?mt(n,b.cssClasses.handleLower):e===b.handles-1&&mt(n,b.cssClasses.handleUpper),r}function M(t,e){return!!e&&V(t,b.cssClasses.connect)}function r(t,e){return!!b.tooltips[e]&&V(t.firstChild,b.cssClasses.tooltip)}function O(){return h.hasAttribute("disabled")}function L(t){return u[t].hasAttribute("disabled")}function z(){i&&(G("update"+bt.tooltips),i.forEach(function(t){t&&ut(t)}),i=null)}function H(){z(),i=u.map(r),$("update"+bt.tooltips,function(t,e,r){if(i[e]){var n=t[e];!0!==b.tooltips[e]&&(n=b.tooltips[e].to(r[e])),i[e].innerHTML=n}})}function j(e,i,o){var s=w.createElement("div"),a=[];a[N]=b.cssClasses.valueNormal,a[k]=b.cssClasses.valueLarge,a[U]=b.cssClasses.valueSub;var l=[];l[N]=b.cssClasses.markerNormal,l[k]=b.cssClasses.markerLarge,l[U]=b.cssClasses.markerSub;var u=[b.cssClasses.valueHorizontal,b.cssClasses.valueVertical],c=[b.cssClasses.markerHorizontal,b.cssClasses.markerVertical];function p(t,e){var r=e===b.cssClasses.value,n=r?a:l;return e+" "+(r?u:c)[b.ort]+" "+n[t]}return mt(s,b.cssClasses.pips),mt(s,0===b.ort?b.cssClasses.pipsHorizontal:b.cssClasses.pipsVertical),Object.keys(e).forEach(function(t){!function(t,e,r){if((r=i?i(e,r):r)!==P){var n=V(s,!1);n.className=p(r,b.cssClasses.marker),n.style[b.style]=t+"%",N<r&&((n=V(s,!1)).className=p(r,b.cssClasses.value),n.setAttribute("data-value",e),n.style[b.style]=t+"%",n.innerHTML=o.to(e))}}(t,e[t][0],e[t][1])}),s}function F(){c&&(ut(c),c=null)}function R(t){F();var m,g,v,b,e,r,x,S,w,n=t.mode,i=t.density||1,o=t.filter||!1,s=function(t,e,r){if("range"===t||"steps"===t)return y.xVal;if("count"===t){if(e<2)throw new Error("noUiSlider ("+lt+"): 'values' (>= 2) required for mode 'count'.");var n=e-1,i=100/n;for(e=[];n--;)e[n]=n*i;e.push(100),t="positions"}return"positions"===t?e.map(function(t){return y.fromStepping(r?y.getStep(t):t)}):"values"===t?r?e.map(function(t){return y.fromStepping(y.getStep(y.toStepping(t)))}):e:void 0}(n,t.values||!1,t.stepped||!1),a=(m=i,g=n,v=s,b={},e=y.xVal[0],r=y.xVal[y.xVal.length-1],S=x=!1,w=0,(v=v.slice().sort(function(t,e){return t-e}).filter(function(t){return!this[t]&&(this[t]=!0)},{}))[0]!==e&&(v.unshift(e),x=!0),v[v.length-1]!==r&&(v.push(r),S=!0),v.forEach(function(t,e){var r,n,i,o,s,a,l,u,c,p,f=t,d=v[e+1],h="steps"===g;if(h&&(r=y.xNumSteps[e]),r||(r=d-f),!1!==f)for(void 0===d&&(d=f),r=Math.max(r,1e-7),n=f;n<=d;n=(n+r).toFixed(7)/1){for(u=(s=(o=y.toStepping(n))-w)/m,p=s/(c=Math.round(u)),i=1;i<=c;i+=1)b[(a=w+i*p).toFixed(5)]=[y.fromStepping(a),0];l=-1<v.indexOf(n)?k:h?U:N,!e&&x&&n!==d&&(l=0),n===d&&S||(b[o.toFixed(5)]=[n,l]),w=o}}),b),l=t.format||{to:Math.round};return c=h.appendChild(j(a,o,l))}function T(){var t=l.getBoundingClientRect(),e="offset"+["Width","Height"][b.ort];return 0===b.ort?t.width||l[e]:t.height||l[e]}function _(n,i,o,s){var e=function(t){return!!(t=function(t,e,r){var n,i,o=0===t.type.indexOf("touch"),s=0===t.type.indexOf("mouse"),a=0===t.type.indexOf("pointer");0===t.type.indexOf("MSPointer")&&(a=!0);if("mousedown"===t.type&&!t.buttons&&!t.touches)return!1;if(o){var l=function(t){return t.target===r||r.contains(t.target)||t.target.shadowRoot&&t.target.shadowRoot.contains(r)};if("touchstart"===t.type){var u=Array.prototype.filter.call(t.touches,l);if(1<u.length)return!1;n=u[0].pageX,i=u[0].pageY}else{var c=Array.prototype.find.call(t.changedTouches,l);if(!c)return!1;n=c.pageX,i=c.pageY}}e=e||vt(w),(s||a)&&(n=t.clientX+e.x,i=t.clientY+e.y);return t.pageOffset=e,t.points=[n,i],t.cursor=s||a,t}(t,s.pageOffset,s.target||i))&&(!(O()&&!s.doNotReject)&&(e=h,r=b.cssClasses.tap,!((e.classList?e.classList.contains(r):new RegExp("\\b"+r+"\\b").test(e.className))&&!s.doNotReject)&&(!(n===f.start&&void 0!==t.buttons&&1<t.buttons)&&((!s.hover||!t.buttons)&&(d||t.preventDefault(),t.calcPoint=t.points[b.ort],void o(t,s))))));var e,r},r=[];return n.split(" ").forEach(function(t){i.addEventListener(t,e,!!d&&{passive:!0}),r.push([t,e])}),r}function B(t){var e,r,n,i,o,s,a=100*(t-(e=l,r=b.ort,n=e.getBoundingClientRect(),i=e.ownerDocument,o=i.documentElement,s=vt(i),/webkit.*Chrome.*Mobile/i.test(navigator.userAgent)&&(s.x=0),r?n.top+s.y-o.clientTop:n.left+s.x-o.clientLeft))/T();return a=dt(a),b.dir?100-a:a}function q(t,e){"mouseout"===t.type&&"HTML"===t.target.nodeName&&null===t.relatedTarget&&Y(t,e)}function X(t,e){if(-1===navigator.appVersion.indexOf("MSIE 9")&&0===t.buttons&&0!==e.buttonsProperty)return Y(t,e);var r=(b.dir?-1:1)*(t.calcPoint-e.startCalcPoint);Z(0<r,100*r/e.baseSize,e.locations,e.handleNumbers)}function Y(t,e){e.handle&&(gt(e.handle,b.cssClasses.active),g-=1),e.listeners.forEach(function(t){E.removeEventListener(t[0],t[1])}),0===g&&(gt(h,b.cssClasses.drag),et(),t.cursor&&(C.style.cursor="",C.removeEventListener("selectstart",pt))),e.handleNumbers.forEach(function(t){J("change",t),J("set",t),J("end",t)})}function I(t,e){if(e.handleNumbers.some(L))return!1;var r;1===e.handleNumbers.length&&(r=u[e.handleNumbers[0]].children[0],g+=1,mt(r,b.cssClasses.active));t.stopPropagation();var n=[],i=_(f.move,E,X,{target:t.target,handle:r,listeners:n,startCalcPoint:t.calcPoint,baseSize:T(),pageOffset:t.pageOffset,handleNumbers:e.handleNumbers,buttonsProperty:t.buttons,locations:S.slice()}),o=_(f.end,E,Y,{target:t.target,handle:r,listeners:n,doNotReject:!0,handleNumbers:e.handleNumbers}),s=_("mouseout",E,q,{target:t.target,handle:r,listeners:n,doNotReject:!0,handleNumbers:e.handleNumbers});n.push.apply(n,i.concat(o,s)),t.cursor&&(C.style.cursor=getComputedStyle(t.target).cursor,1<u.length&&mt(h,b.cssClasses.drag),C.addEventListener("selectstart",pt,!1)),e.handleNumbers.forEach(function(t){J("start",t)})}function n(t){t.stopPropagation();var i,o,s,e=B(t.calcPoint),r=(i=e,s=!(o=100),u.forEach(function(t,e){if(!L(e)){var r=S[e],n=Math.abs(r-i);(n<o||n<=o&&r<i||100===n&&100===o)&&(s=e,o=n)}}),s);if(!1===r)return!1;b.events.snap||ft(h,b.cssClasses.tap,b.animationDuration),rt(r,e,!0,!0),et(),J("slide",r,!0),J("update",r,!0),J("change",r,!0),J("set",r,!0),b.events.snap&&I(t,{handleNumbers:[r]})}function W(t){var e=B(t.calcPoint),r=y.getStep(e),n=y.fromStepping(r);Object.keys(v).forEach(function(t){"hover"===t.split(".")[0]&&v[t].forEach(function(t){t.call(a,n)})})}function $(t,e){v[t]=v[t]||[],v[t].push(e),"update"===t.split(".")[0]&&u.forEach(function(t,e){J("update",e)})}function G(t){var i=t&&t.split(".")[0],o=i?t.substring(i.length):t;Object.keys(v).forEach(function(t){var e,r=t.split(".")[0],n=t.substring(r.length);i&&i!==r||o&&o!==n||((e=n)!==bt.aria&&e!==bt.tooltips||o===n)&&delete v[t]})}function J(r,n,i){Object.keys(v).forEach(function(t){var e=t.split(".")[0];r===e&&v[t].forEach(function(t){t.call(a,x.map(b.format.to),n,x.slice(),i||!1,S.slice(),a)})})}function K(t,e,r,n,i,o){var s;return 1<u.length&&!b.events.unconstrained&&(n&&0<e&&(s=y.getAbsoluteDistance(t[e-1],b.margin,0),r=Math.max(r,s)),i&&e<u.length-1&&(s=y.getAbsoluteDistance(t[e+1],b.margin,1),r=Math.min(r,s))),1<u.length&&b.limit&&(n&&0<e&&(s=y.getAbsoluteDistance(t[e-1],b.limit,0),r=Math.min(r,s)),i&&e<u.length-1&&(s=y.getAbsoluteDistance(t[e+1],b.limit,1),r=Math.max(r,s))),b.padding&&(0===e&&(s=y.getAbsoluteDistance(0,b.padding[0],0),r=Math.max(r,s)),e===u.length-1&&(s=y.getAbsoluteDistance(100,b.padding[1],1),r=Math.min(r,s))),!((r=dt(r=y.getStep(r)))===t[e]&&!o)&&r}function Q(t,e){var r=b.ort;return(r?e:t)+", "+(r?t:e)}function Z(t,n,r,e){var i=r.slice(),o=[!t,t],s=[t,!t];e=e.slice(),t&&e.reverse(),1<e.length?e.forEach(function(t,e){var r=K(i,t,i[t]+n,o[e],s[e],!1);!1===r?n=0:(n=r-i[t],i[t]=r)}):o=s=[!0];var a=!1;e.forEach(function(t,e){a=rt(t,r[t]+n,o[e],s[e])||a}),a&&e.forEach(function(t){J("update",t),J("slide",t)})}function tt(t,e){return b.dir?100-t-e:t}function et(){m.forEach(function(t){var e=50<S[t]?-1:1,r=3+(u.length+e*t);u[t].style.zIndex=r})}function rt(t,e,r,n,i){return i||(e=K(S,t,e,r,n,!1)),!1!==e&&(function(t,e){S[t]=e,x[t]=y.fromStepping(e);var r="translate("+Q(10*(tt(e,0)-A)+"%","0")+")";u[t].style[b.transformRule]=r,nt(t),nt(t+1)}(t,e),!0)}function nt(t){if(s[t]){var e=0,r=100;0!==t&&(e=S[t-1]),t!==s.length-1&&(r=S[t]);var n=r-e,i="translate("+Q(tt(e,n)+"%","0")+")",o="scale("+Q(n/100,"1")+")";s[t].style[b.transformRule]=i+" "+o}}function it(t,e){return null===t||!1===t||void 0===t?S[e]:("number"==typeof t&&(t=String(t)),t=b.format.from(t),!1===(t=y.toStepping(t))||isNaN(t)?S[e]:t)}function ot(t,e,r){var n=ht(t),i=void 0===S[0];e=void 0===e||!!e,b.animate&&!i&&ft(h,b.cssClasses.tap,b.animationDuration),m.forEach(function(t){rt(t,it(n[t],t),!0,!1,r)});for(var o=1===m.length?0:1;o<m.length;++o)m.forEach(function(t){rt(t,S[t],!0,!0,r)});et(),m.forEach(function(t){J("update",t),null!==n[t]&&e&&J("set",t)})}function st(){var t=x.map(b.format.to);return 1===t.length?t[0]:t}function at(t){var e=S[t],r=y.getNearbySteps(e),n=x[t],i=r.thisStep.step,o=null;if(b.snap)return[n-r.stepBefore.startValue||null,r.stepAfter.startValue-n||null];!1!==i&&n+i>r.stepAfter.startValue&&(i=r.stepAfter.startValue-n),o=n>r.thisStep.startValue?r.thisStep.step:!1!==r.stepBefore.step&&n-r.stepBefore.highestStep,100===e?i=null:0===e&&(o=null);var s=y.countStepDecimals();return null!==i&&!1!==i&&(i=Number(i.toFixed(s))),null!==o&&!1!==o&&(o=Number(o.toFixed(s))),[o,i]}return mt(e=h,b.cssClasses.target),0===b.dir?mt(e,b.cssClasses.ltr):mt(e,b.cssClasses.rtl),0===b.ort?mt(e,b.cssClasses.horizontal):mt(e,b.cssClasses.vertical),mt(e,"rtl"===getComputedStyle(e).direction?b.cssClasses.textDirectionRtl:b.cssClasses.textDirectionLtr),l=V(e,b.cssClasses.base),function(t,e){var r=V(e,b.cssClasses.connects);u=[],(s=[]).push(M(r,t[0]));for(var n=0;n<b.handles;n++)u.push(D(e,n)),m[n]=n,s.push(M(r,t[n+1]))}(b.connect,l),(p=b.events).fixed||u.forEach(function(t,e){_(f.start,t.children[0],I,{handleNumbers:[e]})}),p.tap&&_(f.start,l,n,{}),p.hover&&_(f.move,l,W,{hover:!0}),p.drag&&s.forEach(function(t,e){if(!1!==t&&0!==e&&e!==s.length-1){var r=u[e-1],n=u[e],i=[t];mt(t,b.cssClasses.draggable),p.fixed&&(i.push(r.children[0]),i.push(n.children[0])),i.forEach(function(t){_(f.start,t,I,{handles:[r,n],handleNumbers:[e-1,e]})})}}),ot(b.start),b.pips&&R(b.pips),b.tooltips&&H(),G("update"+bt.aria),$("update"+bt.aria,function(t,e,s,r,a){m.forEach(function(t){var e=u[t],r=K(S,t,0,!0,!0,!0),n=K(S,t,100,!0,!0,!0),i=a[t],o=b.ariaFormat.to(s[t]);r=y.fromStepping(r).toFixed(1),n=y.fromStepping(n).toFixed(1),i=y.fromStepping(i).toFixed(1),e.children[0].setAttribute("aria-valuemin",r),e.children[0].setAttribute("aria-valuemax",n),e.children[0].setAttribute("aria-valuenow",i),e.children[0].setAttribute("aria-valuetext",o)})}),a={destroy:function(){for(var t in G(bt.aria),G(bt.tooltips),b.cssClasses)b.cssClasses.hasOwnProperty(t)&&gt(h,b.cssClasses[t]);for(;h.firstChild;)h.removeChild(h.firstChild);delete h.noUiSlider},steps:function(){return m.map(at)},on:$,off:G,get:st,set:ot,setHandle:function(t,e,r,n){if(!(0<=(t=Number(t))&&t<m.length))throw new Error("noUiSlider ("+lt+"): invalid handle number, got: "+t);rt(t,it(e,t),!0,!0,n),J("update",t),r&&J("set",t)},reset:function(t){ot(b.start,t)},__moveHandles:function(t,e,r){Z(t,e,S,r)},options:o,updateOptions:function(e,t){var r=st(),n=["margin","limit","padding","range","animate","snap","step","format","pips","tooltips"];n.forEach(function(t){void 0!==e[t]&&(o[t]=e[t])});var i=xt(o);n.forEach(function(t){void 0!==e[t]&&(b[t]=i[t])}),y=i.spectrum,b.margin=i.margin,b.limit=i.limit,b.padding=i.padding,b.pips?R(b.pips):F(),b.tooltips?H():z(),S=[],ot(ct(e.start)?e.start:r,t)},target:h,removePips:F,removeTooltips:z,getTooltips:function(){return i},getOrigins:function(){return u},pips:R}}return{__spectrum:i,version:lt,cssClasses:u,create:function(t,e){if(!t||!t.nodeName)throw new Error("noUiSlider ("+lt+"): create requires a single element, got: "+t);if(t.noUiSlider)throw new Error("noUiSlider ("+lt+"): Slider was already initialized.");var r=H(t,xt(e),e);return t.noUiSlider=r}}});

/*! @preserve
 * numeral.js
 * version : 2.0.6
 * author : Adam Draper
 * license : MIT
 * http://adamwdraper.github.com/Numeral-js/
 */
!function(a,b){"function"==typeof define&&define.amd?define(b):"object"==typeof module&&module.exports?module.exports=b():a.numeral=b()}(this,function(){function a(a,b){this._input=a,this._value=b}var b,c,d="2.0.6",e={},f={},g={currentLocale:"en",zeroFormat:null,nullFormat:null,defaultFormat:"0,0",scalePercentBy100:!0},h={currentLocale:g.currentLocale,zeroFormat:g.zeroFormat,nullFormat:g.nullFormat,defaultFormat:g.defaultFormat,scalePercentBy100:g.scalePercentBy100};return b=function(d){var f,g,i,j;if(b.isNumeral(d))f=d.value();else if(0===d||"undefined"==typeof d)f=0;else if(null===d||c.isNaN(d))f=null;else if("string"==typeof d)if(h.zeroFormat&&d===h.zeroFormat)f=0;else if(h.nullFormat&&d===h.nullFormat||!d.replace(/[^0-9]+/g,"").length)f=null;else{for(g in e)if(j="function"==typeof e[g].regexps.unformat?e[g].regexps.unformat():e[g].regexps.unformat,j&&d.match(j)){i=e[g].unformat;break}i=i||b._.stringToNumber,f=i(d)}else f=Number(d)||null;return new a(d,f)},b.version=d,b.isNumeral=function(b){return b instanceof a},b._=c={numberToFormat:function(a,c,d){var e,g,h,i,j,k,l,m=f[b.options.currentLocale],n=!1,o=!1,p=0,q="",r=1e12,s=1e9,t=1e6,u=1e3,v="",w=!1;if(a=a||0,g=Math.abs(a),b._.includes(c,"(")?(n=!0,c=c.replace(/[\(|\)]/g,"")):(b._.includes(c,"+")||b._.includes(c,"-"))&&(j=b._.includes(c,"+")?c.indexOf("+"):0>a?c.indexOf("-"):-1,c=c.replace(/[\+|\-]/g,"")),b._.includes(c,"a")&&(e=c.match(/a(k|m|b|t)?/),e=e?e[1]:!1,b._.includes(c," a")&&(q=" "),c=c.replace(new RegExp(q+"a[kmbt]?"),""),g>=r&&!e||"t"===e?(q+=m.abbreviations.trillion,a/=r):r>g&&g>=s&&!e||"b"===e?(q+=m.abbreviations.billion,a/=s):s>g&&g>=t&&!e||"m"===e?(q+=m.abbreviations.million,a/=t):(t>g&&g>=u&&!e||"k"===e)&&(q+=m.abbreviations.thousand,a/=u)),b._.includes(c,"[.]")&&(o=!0,c=c.replace("[.]",".")),h=a.toString().split(".")[0],i=c.split(".")[1],k=c.indexOf(","),p=(c.split(".")[0].split(",")[0].match(/0/g)||[]).length,i?(b._.includes(i,"[")?(i=i.replace("]",""),i=i.split("["),v=b._.toFixed(a,i[0].length+i[1].length,d,i[1].length)):v=b._.toFixed(a,i.length,d),h=v.split(".")[0],v=b._.includes(v,".")?m.delimiters.decimal+v.split(".")[1]:"",o&&0===Number(v.slice(1))&&(v="")):h=b._.toFixed(a,0,d),q&&!e&&Number(h)>=1e3&&q!==m.abbreviations.trillion)switch(h=String(Number(h)/1e3),q){case m.abbreviations.thousand:q=m.abbreviations.million;break;case m.abbreviations.million:q=m.abbreviations.billion;break;case m.abbreviations.billion:q=m.abbreviations.trillion}if(b._.includes(h,"-")&&(h=h.slice(1),w=!0),h.length<p)for(var x=p-h.length;x>0;x--)h="0"+h;return k>-1&&(h=h.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g,"$1"+m.delimiters.thousands)),0===c.indexOf(".")&&(h=""),l=h+v+(q?q:""),n?l=(n&&w?"(":"")+l+(n&&w?")":""):j>=0?l=0===j?(w?"-":"+")+l:l+(w?"-":"+"):w&&(l="-"+l),l},stringToNumber:function(a){var b,c,d,e=f[h.currentLocale],g=a,i={thousand:3,million:6,billion:9,trillion:12};if(h.zeroFormat&&a===h.zeroFormat)c=0;else if(h.nullFormat&&a===h.nullFormat||!a.replace(/[^0-9]+/g,"").length)c=null;else{c=1,"."!==e.delimiters.decimal&&(a=a.replace(/\./g,"").replace(e.delimiters.decimal,"."));for(b in i)if(d=new RegExp("[^a-zA-Z]"+e.abbreviations[b]+"(?:\\)|(\\"+e.currency.symbol+")?(?:\\))?)?$"),g.match(d)){c*=Math.pow(10,i[b]);break}c*=(a.split("-").length+Math.min(a.split("(").length-1,a.split(")").length-1))%2?1:-1,a=a.replace(/[^0-9\.]+/g,""),c*=Number(a)}return c},isNaN:function(a){return"number"==typeof a&&isNaN(a)},includes:function(a,b){return-1!==a.indexOf(b)},insert:function(a,b,c){return a.slice(0,c)+b+a.slice(c)},reduce:function(a,b){if(null===this)throw new TypeError("Array.prototype.reduce called on null or undefined");if("function"!=typeof b)throw new TypeError(b+" is not a function");var c,d=Object(a),e=d.length>>>0,f=0;if(3===arguments.length)c=arguments[2];else{for(;e>f&&!(f in d);)f++;if(f>=e)throw new TypeError("Reduce of empty array with no initial value");c=d[f++]}for(;e>f;f++)f in d&&(c=b(c,d[f],f,d));return c},multiplier:function(a){var b=a.toString().split(".");return b.length<2?1:Math.pow(10,b[1].length)},correctionFactor:function(){var a=Array.prototype.slice.call(arguments);return a.reduce(function(a,b){var d=c.multiplier(b);return a>d?a:d},1)},toFixed:function(a,b,c,d){var e,f,g,h,i=a.toString().split("."),j=b-(d||0);return e=2===i.length?Math.min(Math.max(i[1].length,j),b):j,g=Math.pow(10,e),h=(c(a+"e+"+e)/g).toFixed(e),d>b-e&&(f=new RegExp("\\.?0{1,"+(d-(b-e))+"}$"),h=h.replace(f,"")),h}},b.options=h,b.formats=e,b.locales=f,b.locale=function(a){return a&&(h.currentLocale=a.toLowerCase()),h.currentLocale},b.localeData=function(a){if(!a)return f[h.currentLocale];if(a=a.toLowerCase(),!f[a])throw new Error("Unknown locale : "+a);return f[a]},b.reset=function(){for(var a in g)h[a]=g[a]},b.zeroFormat=function(a){h.zeroFormat="string"==typeof a?a:null},b.nullFormat=function(a){h.nullFormat="string"==typeof a?a:null},b.defaultFormat=function(a){h.defaultFormat="string"==typeof a?a:"0.0"},b.register=function(a,b,c){if(b=b.toLowerCase(),this[a+"s"][b])throw new TypeError(b+" "+a+" already registered.");return this[a+"s"][b]=c,c},b.validate=function(a,c){var d,e,f,g,h,i,j,k;if("string"!=typeof a&&(a+="",console.warn&&console.warn("Numeral.js: Value is not string. It has been co-erced to: ",a)),a=a.trim(),a.match(/^\d+$/))return!0;if(""===a)return!1;try{j=b.localeData(c)}catch(l){j=b.localeData(b.locale())}return f=j.currency.symbol,h=j.abbreviations,d=j.delimiters.decimal,e="."===j.delimiters.thousands?"\\.":j.delimiters.thousands,k=a.match(/^[^\d]+/),null!==k&&(a=a.substr(1),k[0]!==f)?!1:(k=a.match(/[^\d]+$/),null!==k&&(a=a.slice(0,-1),k[0]!==h.thousand&&k[0]!==h.million&&k[0]!==h.billion&&k[0]!==h.trillion)?!1:(i=new RegExp(e+"{2}"),a.match(/[^\d.,]/g)?!1:(g=a.split(d),g.length>2?!1:g.length<2?!!g[0].match(/^\d+.*\d$/)&&!g[0].match(i):1===g[0].length?!!g[0].match(/^\d+$/)&&!g[0].match(i)&&!!g[1].match(/^\d+$/):!!g[0].match(/^\d+.*\d$/)&&!g[0].match(i)&&!!g[1].match(/^\d+$/))))},b.fn=a.prototype={clone:function(){return b(this)},format:function(a,c){var d,f,g,i=this._value,j=a||h.defaultFormat;if(c=c||Math.round,0===i&&null!==h.zeroFormat)f=h.zeroFormat;else if(null===i&&null!==h.nullFormat)f=h.nullFormat;else{for(d in e)if(j.match(e[d].regexps.format)){g=e[d].format;break}g=g||b._.numberToFormat,f=g(i,j,c)}return f},value:function(){return this._value},input:function(){return this._input},set:function(a){return this._value=Number(a),this},add:function(a){function b(a,b,c,e){return a+Math.round(d*b)}var d=c.correctionFactor.call(null,this._value,a);return this._value=c.reduce([this._value,a],b,0)/d,this},subtract:function(a){function b(a,b,c,e){return a-Math.round(d*b)}var d=c.correctionFactor.call(null,this._value,a);return this._value=c.reduce([a],b,Math.round(this._value*d))/d,this},multiply:function(a){function b(a,b,d,e){var f=c.correctionFactor(a,b);return Math.round(a*f)*Math.round(b*f)/Math.round(f*f)}return this._value=c.reduce([this._value,a],b,1),this},divide:function(a){function b(a,b,d,e){var f=c.correctionFactor(a,b);return Math.round(a*f)/Math.round(b*f)}return this._value=c.reduce([this._value,a],b),this},difference:function(a){return Math.abs(b(this._value).subtract(a).value())}},b.register("locale","en",{delimiters:{thousands:",",decimal:"."},abbreviations:{thousand:"k",million:"m",billion:"b",trillion:"t"},ordinal:function(a){var b=a%10;return 1===~~(a%100/10)?"th":1===b?"st":2===b?"nd":3===b?"rd":"th"},currency:{symbol:"$"}}),function(){b.register("format","bps",{regexps:{format:/(BPS)/,unformat:/(BPS)/},format:function(a,c,d){var e,f=b._.includes(c," BPS")?" ":"";return a=1e4*a,c=c.replace(/\s?BPS/,""),e=b._.numberToFormat(a,c,d),b._.includes(e,")")?(e=e.split(""),e.splice(-1,0,f+"BPS"),e=e.join("")):e=e+f+"BPS",e},unformat:function(a){return+(1e-4*b._.stringToNumber(a)).toFixed(15)}})}(),function(){var a={base:1e3,suffixes:["B","KB","MB","GB","TB","PB","EB","ZB","YB"]},c={base:1024,suffixes:["B","KiB","MiB","GiB","TiB","PiB","EiB","ZiB","YiB"]},d=a.suffixes.concat(c.suffixes.filter(function(b){return a.suffixes.indexOf(b)<0})),e=d.join("|");e="("+e.replace("B","B(?!PS)")+")",b.register("format","bytes",{regexps:{format:/([0\s]i?b)/,unformat:new RegExp(e)},format:function(d,e,f){var g,h,i,j,k=b._.includes(e,"ib")?c:a,l=b._.includes(e," b")||b._.includes(e," ib")?" ":"";for(e=e.replace(/\s?i?b/,""),h=0;h<=k.suffixes.length;h++)if(i=Math.pow(k.base,h),j=Math.pow(k.base,h+1),null===d||0===d||d>=i&&j>d){l+=k.suffixes[h],i>0&&(d/=i);break}return g=b._.numberToFormat(d,e,f),g+l},unformat:function(d){var e,f,g=b._.stringToNumber(d);if(g){for(e=a.suffixes.length-1;e>=0;e--){if(b._.includes(d,a.suffixes[e])){f=Math.pow(a.base,e);break}if(b._.includes(d,c.suffixes[e])){f=Math.pow(c.base,e);break}}g*=f||1}return g}})}(),function(){b.register("format","currency",{regexps:{format:/(\$)/},format:function(a,c,d){var e,f,g,h=b.locales[b.options.currentLocale],i={before:c.match(/^([\+|\-|\(|\s|\$]*)/)[0],after:c.match(/([\+|\-|\)|\s|\$]*)$/)[0]};for(c=c.replace(/\s?\$\s?/,""),e=b._.numberToFormat(a,c,d),a>=0?(i.before=i.before.replace(/[\-\(]/,""),i.after=i.after.replace(/[\-\)]/,"")):0>a&&!b._.includes(i.before,"-")&&!b._.includes(i.before,"(")&&(i.before="-"+i.before),g=0;g<i.before.length;g++)switch(f=i.before[g]){case"$":e=b._.insert(e,h.currency.symbol,g);break;case" ":e=b._.insert(e," ",g+h.currency.symbol.length-1)}for(g=i.after.length-1;g>=0;g--)switch(f=i.after[g]){case"$":e=g===i.after.length-1?e+h.currency.symbol:b._.insert(e,h.currency.symbol,-(i.after.length-(1+g)));break;case" ":e=g===i.after.length-1?e+" ":b._.insert(e," ",-(i.after.length-(1+g)+h.currency.symbol.length-1))}return e}})}(),function(){b.register("format","exponential",{regexps:{format:/(e\+|e-)/,unformat:/(e\+|e-)/},format:function(a,c,d){var e,f="number"!=typeof a||b._.isNaN(a)?"0e+0":a.toExponential(),g=f.split("e");return c=c.replace(/e[\+|\-]{1}0/,""),e=b._.numberToFormat(Number(g[0]),c,d),e+"e"+g[1]},unformat:function(a){function c(a,c,d,e){var f=b._.correctionFactor(a,c),g=a*f*(c*f)/(f*f);return g}var d=b._.includes(a,"e+")?a.split("e+"):a.split("e-"),e=Number(d[0]),f=Number(d[1]);return f=b._.includes(a,"e-")?f*=-1:f,b._.reduce([e,Math.pow(10,f)],c,1)}})}(),function(){b.register("format","ordinal",{regexps:{format:/(o)/},format:function(a,c,d){var e,f=b.locales[b.options.currentLocale],g=b._.includes(c," o")?" ":"";return c=c.replace(/\s?o/,""),g+=f.ordinal(a),e=b._.numberToFormat(a,c,d),e+g}})}(),function(){b.register("format","percentage",{regexps:{format:/(%)/,unformat:/(%)/},format:function(a,c,d){var e,f=b._.includes(c," %")?" ":"";return b.options.scalePercentBy100&&(a=100*a),c=c.replace(/\s?\%/,""),e=b._.numberToFormat(a,c,d),b._.includes(e,")")?(e=e.split(""),e.splice(-1,0,f+"%"),e=e.join("")):e=e+f+"%",e},unformat:function(a){var c=b._.stringToNumber(a);return b.options.scalePercentBy100?.01*c:c}})}(),function(){b.register("format","time",{regexps:{format:/(:)/,unformat:/(:)/},format:function(a,b,c){var d=Math.floor(a/60/60),e=Math.floor((a-60*d*60)/60),f=Math.round(a-60*d*60-60*e);return d+":"+(10>e?"0"+e:e)+":"+(10>f?"0"+f:f)},unformat:function(a){var b=a.split(":"),c=0;return 3===b.length?(c+=60*Number(b[0])*60,c+=60*Number(b[1]),c+=Number(b[2])):2===b.length&&(c+=60*Number(b[0]),c+=Number(b[1])),Number(c)}})}(),b});
!function(a,b){"function"==typeof define&&define.amd?define(["numeral"],b):b("object"==typeof module&&module.exports?require("./numeral"):a.numeral)}(this,function(a){!function(){a.register("locale","bg",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"хил",million:"млн",billion:"млрд",trillion:"трлн"},ordinal:function(a){return""},currency:{symbol:"лв"}})}(),function(){a.register("locale","chs",{delimiters:{thousands:",",decimal:"."},abbreviations:{thousand:"千",million:"百万",billion:"十亿",trillion:"兆"},ordinal:function(a){return"."},currency:{symbol:"¥"}})}(),function(){a.register("locale","cs",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"tis.",million:"mil.",billion:"b",trillion:"t"},ordinal:function(){return"."},currency:{symbol:"Kč"}})}(),function(){a.register("locale","da-dk",{delimiters:{thousands:".",decimal:","},abbreviations:{thousand:"k",million:"mio",billion:"mia",trillion:"b"},ordinal:function(a){return"."},currency:{symbol:"DKK"}})}(),function(){a.register("locale","de-ch",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"k",million:"m",billion:"b",trillion:"t"},ordinal:function(a){return"."},currency:{symbol:"CHF"}})}(),function(){a.register("locale","de",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"k",million:"m",billion:"b",trillion:"t"},ordinal:function(a){return"."},currency:{symbol:"€"}})}(),function(){a.register("locale","en-au",{delimiters:{thousands:",",decimal:"."},abbreviations:{thousand:"k",million:"m",billion:"b",trillion:"t"},ordinal:function(a){var b=a%10;return 1===~~(a%100/10)?"th":1===b?"st":2===b?"nd":3===b?"rd":"th"},currency:{symbol:"$"}})}(),function(){a.register("locale","en-gb",{delimiters:{thousands:",",decimal:"."},abbreviations:{thousand:"k",million:"m",billion:"b",trillion:"t"},ordinal:function(a){var b=a%10;return 1===~~(a%100/10)?"th":1===b?"st":2===b?"nd":3===b?"rd":"th"},currency:{symbol:"£"}})}(),function(){a.register("locale","en-za",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"k",million:"m",billion:"b",trillion:"t"},ordinal:function(a){var b=a%10;return 1===~~(a%100/10)?"th":1===b?"st":2===b?"nd":3===b?"rd":"th"},currency:{symbol:"R"}})}(),function(){a.register("locale","es-es",{delimiters:{thousands:".",decimal:","},abbreviations:{thousand:"k",million:"mm",billion:"b",trillion:"t"},ordinal:function(a){var b=a%10;return 1===b||3===b?"er":2===b?"do":7===b||0===b?"mo":8===b?"vo":9===b?"no":"to"},currency:{symbol:"€"}})}(),function(){a.register("locale","es",{delimiters:{thousands:".",decimal:","},abbreviations:{thousand:"k",million:"mm",billion:"b",trillion:"t"},ordinal:function(a){var b=a%10;return 1===b||3===b?"er":2===b?"do":7===b||0===b?"mo":8===b?"vo":9===b?"no":"to"},currency:{symbol:"$"}})}(),function(){a.register("locale","et",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:" tuh",million:" mln",billion:" mld",trillion:" trl"},ordinal:function(a){return"."},currency:{symbol:"€"}})}(),function(){a.register("locale","fi",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"k",million:"M",billion:"G",trillion:"T"},ordinal:function(a){return"."},currency:{symbol:"€"}})}(),function(){a.register("locale","fr-ca",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"k",million:"M",billion:"G",trillion:"T"},ordinal:function(a){return 1===a?"er":"e"},currency:{symbol:"$"}})}(),function(){a.register("locale","fr-ch",{delimiters:{thousands:"'",decimal:"."},abbreviations:{thousand:"k",million:"m",billion:"b",trillion:"t"},ordinal:function(a){return 1===a?"er":"e"},currency:{symbol:"CHF"}})}(),function(){a.register("locale","fr",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"k",million:"m",billion:"b",trillion:"t"},ordinal:function(a){return 1===a?"er":"e"},currency:{symbol:"€"}})}(),function(){a.register("locale","hu",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"E",million:"M",billion:"Mrd",trillion:"T"},ordinal:function(a){return"."},currency:{symbol:" Ft"}})}(),function(){a.register("locale","it",{delimiters:{thousands:".",decimal:","},abbreviations:{thousand:"mila",million:"mil",billion:"b",trillion:"t"},ordinal:function(a){return"º"},currency:{symbol:"€"}})}(),function(){a.register("locale","ja",{delimiters:{thousands:",",decimal:"."},abbreviations:{thousand:"千",million:"百万",billion:"十億",trillion:"兆"},ordinal:function(a){return"."},currency:{symbol:"¥"}})}(),function(){a.register("locale","lv",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:" tūkst.",million:" milj.",billion:" mljrd.",trillion:" trilj."},ordinal:function(a){return"."},currency:{symbol:"€"}})}(),function(){a.register("locale","nl-be",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"k",million:" mln",billion:" mld",trillion:" bln"},ordinal:function(a){var b=a%100;return 0!==a&&1>=b||8===b||b>=20?"ste":"de"},currency:{symbol:"€ "}})}(),function(){a.register("locale","nl-nl",{delimiters:{thousands:".",decimal:","},abbreviations:{thousand:"k",million:"mln",billion:"mrd",trillion:"bln"},ordinal:function(a){var b=a%100;return 0!==a&&1>=b||8===b||b>=20?"ste":"de"},currency:{symbol:"€ "}})}(),function(){a.register("locale","no",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"k",million:"m",billion:"b",trillion:"t"},ordinal:function(a){return"."},currency:{symbol:"kr"}})}(),function(){a.register("locale","pl",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"tys.",million:"mln",billion:"mld",trillion:"bln"},ordinal:function(a){return"."},currency:{symbol:"PLN"}})}(),function(){a.register("locale","pt-br",{delimiters:{thousands:".",decimal:","},abbreviations:{thousand:"mil",million:"milhões",billion:"b",trillion:"t"},ordinal:function(a){return"º"},currency:{symbol:"R$"}})}(),function(){a.register("locale","pt-pt",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"k",million:"m",billion:"b",trillion:"t"},ordinal:function(a){return"º"},currency:{symbol:"€"}})}(),function(){a.register("locale","ru-ua",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"тыс.",million:"млн",billion:"b",trillion:"t"},ordinal:function(){return"."},currency:{symbol:"₴"}})}(),function(){a.register("locale","ru",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"тыс.",million:"млн.",billion:"млрд.",trillion:"трлн."},ordinal:function(){return"."},currency:{symbol:"руб."}})}(),function(){a.register("locale","sk",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"tis.",million:"mil.",billion:"b",trillion:"t"},ordinal:function(){return"."},currency:{symbol:"€"}})}(),function(){a.register("locale","sl",{delimiters:{thousands:".",decimal:","},abbreviations:{thousand:"k",million:"mio",billion:"mrd",trillion:"trilijon"},ordinal:function(){return"."},currency:{symbol:"€"}})}(),function(){a.register("locale","th",{delimiters:{thousands:",",decimal:"."},abbreviations:{thousand:"พัน",million:"ล้าน",billion:"พันล้าน",trillion:"ล้านล้าน"},ordinal:function(a){return"."},currency:{symbol:"฿"}})}(),function(){var b={1:"'inci",5:"'inci",8:"'inci",70:"'inci",80:"'inci",2:"'nci",7:"'nci",20:"'nci",50:"'nci",3:"'üncü",4:"'üncü",100:"'üncü",6:"'ncı",9:"'uncu",10:"'uncu",30:"'uncu",60:"'ıncı",90:"'ıncı"};a.register("locale","tr",{delimiters:{thousands:".",decimal:","},abbreviations:{thousand:"bin",million:"milyon",billion:"milyar",trillion:"trilyon"},ordinal:function(a){if(0===a)return"'ıncı";var c=a%10,d=a%100-c,e=a>=100?100:null;return b[c]||b[d]||b[e]},currency:{symbol:"₺"}})}(),function(){a.register("locale","uk-ua",{delimiters:{thousands:" ",decimal:","},abbreviations:{thousand:"тис.",million:"млн",billion:"млрд",trillion:"блн"},ordinal:function(){return""},currency:{symbol:"₴"}})}(),function(){a.register("locale","vi",{delimiters:{thousands:".",decimal:","},abbreviations:{thousand:" nghìn",million:" triệu",billion:" tỷ",trillion:" nghìn tỷ"},ordinal:function(){return"."},currency:{symbol:"₫"}})}()});

/* POPOVER */
+function(t){"use strict";function e(e){return this.each(function(){var i=t(this),n=i.data(OCF_PREFIX+".popover"),r="object"==typeof e&&e;!n&&/destroy|hide/.test(e)||(n||i.data(OCF_PREFIX+".popover",n=new o(this,r)),"string"==typeof e&&n[e]())})}var o=function(t,e){this.options=null,this.enabled=null,this.timeout=null,this.hoverState=null,this.$element=null,this.inState=null,this.destroyed=null,this.init(t,e)};o.TRANSITION_DURATION=200,o.DEFAULTS={animation:!0,placement:"right",selector:!1,template:'<div class="'+OCF_PREFIX+'-popover" role="'+OCF_PREFIX+'-popover"><div class="'+OCF_PREFIX+'-arrow"></div><h3 class="'+OCF_PREFIX+'-popover-title"></h3><div class="'+OCF_PREFIX+'-popover-content"></div></div>',trigger:"click",title:"",content:"",delay:0,html:!1,container:!1,viewport:{selector:"body",padding:0}},o.prototype.init=function(e,o){if(this.enabled=!0,this.$element=t(e),this.options=this.getOptions(o),this.$viewport=this.options.viewport&&t(t.isFunction(this.options.viewport)?this.options.viewport.call(this,this.$element):this.options.viewport.selector||this.options.viewport),this.inState={click:!1,hover:!1,focus:!1},this.$element[0]instanceof document.constructor&&!this.options.selector)throw new Error("`selector` option must be specified when initializing ocfPopover on the window.document object!");for(var i=this.options.trigger.split(" "),n=i.length;n--;){var r=i[n];if("click"==r)this.$element.on("click."+OCF_PREFIX+".popover",this.options.selector,t.proxy(this.toggle,this));else if("manual"!=r){var s="hover"==r?"mouseenter":"focusin",p="hover"==r?"mouseleave":"focusout";this.$element.on(s+"."+OCF_PREFIX+".popover",this.options.selector,t.proxy(this.enter,this)),this.$element.on(p+"."+OCF_PREFIX+".popover",this.options.selector,t.proxy(this.leave,this))}}this.options.selector?this._options=t.extend({},this.options,{trigger:"manual",selector:""}):this.fixTitle()},o.prototype.getDefaults=function(){return o.DEFAULTS},o.prototype.getOptions=function(e){return e=t.extend({},this.getDefaults(),this.$element.data(),e),e.delay&&"number"==typeof e.delay&&(e.delay={show:e.delay,hide:e.delay}),e},o.prototype.getDelegateOptions=function(){var e={},o=this.getDefaults();return this._options&&t.each(this._options,function(t,i){o[t]!=i&&(e[t]=i)}),e},o.prototype.enter=function(e){var o=e instanceof this.constructor?e:t(e.currentTarget).data(OCF_PREFIX+".popover");return o||(o=new this.constructor(e.currentTarget,this.getDelegateOptions()),t(e.currentTarget).data(OCF_PREFIX+".popover",o)),e instanceof t.Event&&(o.inState["focusin"==e.type?"focus":"hover"]=!0),o.destroyed?void 0:o.tip().hasClass(OCF_PREFIX+"-in")||"in"==o.hoverState?void(o.hoverState="in"):(clearTimeout(o.timeout),o.hoverState="in",o.options.delay&&o.options.delay.show?void(o.timeout=setTimeout(function(){"in"==o.hoverState&&o.show()},o.options.delay.show)):o.show())},o.prototype.isInStateTrue=function(){for(var t in this.inState)if(this.inState[t])return!0;return!1},o.prototype.leave=function(e){var o=e instanceof this.constructor?e:t(e.currentTarget).data(OCF_PREFIX+".popover");return o||(o=new this.constructor(e.currentTarget,this.getDelegateOptions()),t(e.currentTarget).data(OCF_PREFIX+".popover",o)),e instanceof t.Event&&(o.inState["focusout"==e.type?"focus":"hover"]=!1),o.destroyed||o.isInStateTrue()?void 0:(clearTimeout(o.timeout),o.hoverState="out",o.options.delay&&o.options.delay.hide?void(o.timeout=setTimeout(function(){"out"==o.hoverState&&o.hide()},o.options.delay.hide)):o.hide())},o.prototype.show=function(){var e=t.Event("show."+OCF_PREFIX+".popover");if(this.hasContent()&&this.enabled){this.$element.trigger(e);var i=t.contains(this.$element[0].ownerDocument.documentElement,this.$element[0]);if(e.isDefaultPrevented()||!i)return;var n=this,r=this.tip(),s=this.getUID(OCF_PREFIX+"-popover");this.setContent(),r.attr("id",s),this.$element.attr("aria-describedby",s),this.options.animation&&r.addClass(OCF_PREFIX+"-fade");var p="function"==typeof this.options.placement?this.options.placement.call(this,r[0],this.$element[0]):this.options.placement,a=/\s?auto?\s?/i,l=a.test(p);l&&(p=p.replace(a,"")||"top"),r.detach().css({top:0,left:0,display:"block"}).addClass(OCF_PREFIX+"-"+p).data(OCF_PREFIX+".popover",this),this.options.container?r.appendTo(this.options.container):r.insertAfter(this.$element),this.$element.trigger("inserted."+OCF_PREFIX+".popover");var h=this.getPosition(),c=r[0].offsetWidth,f=r[0].offsetHeight;if(l){var d=p,u=this.getPosition(this.$viewport);p="bottom"==p&&h.bottom+f>u.bottom?"top":"top"==p&&h.top-f<u.top?"bottom":"right"==p&&h.right+c>u.width?"left":"left"==p&&h.left-c<u.left?"right":p,r.removeClass(OCF_PREFIX+"-"+d).addClass(OCF_PREFIX+"-"+p)}var v=this.getCalculatedOffset(p,h,c,f);this.applyPlacement(v,p);var F=function(){var t=n.hoverState;n.$element.trigger("shown."+OCF_PREFIX+".popover"),n.hoverState=null,"out"==t&&n.leave(n)};t.support[OCF_PREFIX+"Transition"]&&this.$tip.hasClass(OCF_PREFIX+"-fade")?r.one(OCF_PREFIX+"TransitionEnd",F)[OCF_PREFIX+"EmulateTransitionEnd"](o.TRANSITION_DURATION):F()}},o.prototype.applyPlacement=function(e,o){var i=this.tip(),n=i[0].offsetWidth,r=i[0].offsetHeight,s=parseInt(i.css("margin-top"),10),p=parseInt(i.css("margin-left"),10);isNaN(s)&&(s=0),isNaN(p)&&(p=0),e.top+=s,e.left+=p,t.offset.setOffset(i[0],t.extend({using:function(t){i.css({top:Math.round(t.top),left:Math.round(t.left)})}},e),0),i.addClass(OCF_PREFIX+"-in");var a=i[0].offsetWidth,l=i[0].offsetHeight;"top"==o&&l!=r&&(e.top=e.top+r-l);var h=this.getViewportAdjustedDelta(o,e,a,l);h.left?e.left+=h.left:e.top+=h.top;var c=/top|bottom/.test(o),f=c?2*h.left-n+a:2*h.top-r+l,d=c?"offsetWidth":"offsetHeight";i.offset(e),this.replaceArrow(f,i[0][d],c)},o.prototype.replaceArrow=function(t,e,o){this.arrow().css(o?"left":"top",50*(1-t/e)+"%").css(o?"top":"left","")},o.prototype.setContent=function(){var t=this.tip(),e=this.getTitle(),o=this.getContent();t.find("."+OCF_PREFIX+"-popover-title")[this.options.html?"html":"text"](e),t.find("."+OCF_PREFIX+"-popover-content").children().detach().end()[this.options.html?"string"==typeof o?"html":"append":"text"](o),t.removeClass(OCF_PREFIX+"-fade "+OCF_PREFIX+"-top "+OCF_PREFIX+"-bottom "+OCF_PREFIX+"-left "+OCF_PREFIX+"-right "+OCF_PREFIX+"-in"),t.find("."+OCF_PREFIX+"-popover-title").html()||t.find("."+OCF_PREFIX+"-popover-title").hide()},o.prototype.hide=function(e){function i(){"in"!=n.hoverState&&r.detach(),n.$element&&n.$element.removeAttr("aria-describedby").trigger("hidden."+OCF_PREFIX+".popover"),e&&e()}var n=this,r=t(this.$tip),s=t.Event("hide."+OCF_PREFIX+".popover");return this.$element.trigger(s),s.isDefaultPrevented()?void 0:(r.removeClass(OCF_PREFIX+"-in"),t.support[OCF_PREFIX+"Transition"]&&r.hasClass(OCF_PREFIX+"-fade")?r.one(OCF_PREFIX+"TransitionEnd",i)[OCF_PREFIX+"EmulateTransitionEnd"](o.TRANSITION_DURATION):i(),this.hoverState=null,this)},o.prototype.fixTitle=function(){var t=this.$element;(t.attr("title")||"string"!=typeof t.attr("data-original-title"))&&t.attr("data-original-title",t.attr("title")||"").attr("title","")},o.prototype.hasContent=function(){return this.getTitle()||this.getContent()},o.prototype.getContent=function(){var t=this.$element,e=this.options;return t.attr("data-content")||("function"==typeof e.content?e.content.call(t[0]):e.content)},o.prototype.getPosition=function(e){e=e||this.$element;var o=e[0],i="BODY"==o.tagName,n=o.getBoundingClientRect();null==n.width&&(n=t.extend({},n,{width:n.right-n.left,height:n.bottom-n.top}));var r=window.SVGElement&&o instanceof window.SVGElement,s=i?{top:0,left:0}:r?null:e.offset(),p={scroll:i?document.documentElement.scrollTop||document.body.scrollTop:e.scrollTop()},a=i?{width:t(window).width(),height:t(window).height()}:null;return t.extend({},n,p,a,s)},o.prototype.getCalculatedOffset=function(t,e,o,i){return"bottom"==t?{top:e.top+e.height,left:e.left+e.width/2-o/2}:"top"==t?{top:e.top-i,left:e.left+e.width/2-o/2}:"left"==t?{top:e.top+e.height/2-i/2,left:e.left-o}:{top:e.top+e.height/2-i/2,left:e.left+e.width}},o.prototype.getViewportAdjustedDelta=function(t,e,o,i){var n={top:0,left:0};if(!this.$viewport)return n;var r=this.options.viewport&&this.options.viewport.padding||0,s=this.getPosition(this.$viewport);if(/right|left/.test(t)){var p=e.top-r-s.scroll,a=e.top+r-s.scroll+i;p<s.top?n.top=s.top-p:a>s.top+s.height&&(n.top=s.top+s.height-a)}else{var l=e.left-r,h=e.left+r+o;l<s.left?n.left=s.left-l:h>s.right&&(n.left=s.left+s.width-h)}return n},o.prototype.getTitle=function(){var t,e=this.$element,o=this.options;return t=e.attr("data-original-title")||("function"==typeof o.title?o.title.call(e[0]):o.title)},o.prototype.getUID=function(t){do t+=~~(1e6*Math.random());while(document.getElementById(t));return t},o.prototype.tip=function(){if(!this.$tip&&(this.$tip=t(this.options.template),1!=this.$tip.length))throw new Error("ocfPopover `template` option must consist of exactly 1 top-level element!");return this.$tip},o.prototype.arrow=function(){return this.$arrow=this.$arrow||this.tip().find(".arrow")},o.prototype.enable=function(){this.enabled=!0},o.prototype.disable=function(){this.enabled=!1},o.prototype.toggleEnabled=function(){this.enabled=!this.enabled},o.prototype.toggle=function(e){var o=this;e&&(o=t(e.currentTarget).data(OCF_PREFIX+".popover"),o||(o=new this.constructor(e.currentTarget,this.getDelegateOptions()),t(e.currentTarget).data(OCF_PREFIX+".popover",o))),e?(o.inState.click=!o.inState.click,o.isInStateTrue()?o.enter(o):o.leave(o)):o.tip().hasClass(OCF_PREFIX+"-in")?o.leave(o):o.enter(o)},o.prototype.destroy=function(){var t=this;this.destroyed=!0,clearTimeout(this.timeout),this.hide(function(){t.$element.off("."+OCF_PREFIX+".popover").removeData(OCF_PREFIX+".popover"),t.$tip&&t.$tip.detach(),t.$tip=null,t.$arrow=null,t.$viewport=null,t.$element=null,t.destroyed=null})},t.fn[OCF_PREFIX+"Popover"]=e,t.fn[OCF_PREFIX+"Popover"].Constructor=o}(jQuery);

/* COLLAPSE */
+function(t){"use strict";function e(e){var a,i=e.attr("data-target")||(a=e.attr("href"))&&a.replace(/.*(?=#[^\s]+$)/,"");return t(i)}function a(e){return this.each(function(){var a=t(this),s=a.data(OCF_PREFIX+".collapse"),n=t.extend({},i.DEFAULTS,a.data(),"object"==typeof e&&e);!s&&n.toggle&&/show|hide/.test(e)&&(n.toggle=!1),s||a.data(OCF_PREFIX+".collapse",s=new i(this,n)),"string"==typeof e&&s[e]()})}var i=function(e,a){this.$element=t(e),this.options=t.extend({},i.DEFAULTS,a),this.$trigger=t("[data-"+OCF_PREFIX+'="collapse"][href="#'+e.id+'"], [data-'+OCF_PREFIX+'="collapse"][data-target="#'+e.id+'"]'),this.transitioning=null,this.options.parent?this.$parent=this.getParent():this.addAriaAndCollapsedClass(this.$element,this.$trigger),this.options.toggle&&this.toggle()};i.TRANSITION_DURATION=400,i.DEFAULTS={toggle:!0},i.prototype.dimension=function(){var t=this.$element.hasClass(OCF_PREFIX+"-width");return t?"width":"height"},i.prototype.show=function(){if(!this.transitioning&&!this.$element.hasClass(OCF_PREFIX+"-in")){var e,s=this.$parent&&this.$parent.children("."+OCF_PREFIX+"-panel").children("."+OCF_PREFIX+"-in, ."+OCF_PREFIX+"-collapsing");if(!(s&&s.length&&(e=s.data(OCF_PREFIX+".collapse"),e&&e.transitioning))){var n=t.Event("show."+OCF_PREFIX+".collapse");if(this.$element.trigger(n),!n.isDefaultPrevented()){s&&s.length&&(a.call(s,"hide"),e||s.data(OCF_PREFIX+".collapse",null));var l=this.dimension();this.$element.removeClass(OCF_PREFIX+"-collapse").addClass(OCF_PREFIX+"-collapsing")[l](0).attr("aria-expanded",!0),this.$trigger.removeClass(OCF_PREFIX+"-collapsed").attr("aria-expanded",!0),this.transitioning=1;var o=function(){this.$element.removeClass(OCF_PREFIX+"-collapsing").addClass(OCF_PREFIX+"-collapse "+OCF_PREFIX+"-in")[l](""),this.transitioning=0,this.$element.trigger("shown."+OCF_PREFIX+".collapse")};if(!t.support[OCF_PREFIX+"Transition"])return o.call(this);var r=t.camelCase(["scroll",l].join("-"));this.$element.one(OCF_PREFIX+"TransitionEnd",t.proxy(o,this))[OCF_PREFIX+"EmulateTransitionEnd"](i.TRANSITION_DURATION)[l](this.$element[0][r])}}}},i.prototype.hide=function(){if(!this.transitioning&&this.$element.hasClass(OCF_PREFIX+"-in")){var e=t.Event("hide."+OCF_PREFIX+".collapse");if(this.$element.trigger(e),!e.isDefaultPrevented()){var a=this.dimension();this.$element[a](this.$element[a]())[0].offsetHeight,this.$element.addClass(OCF_PREFIX+"-collapsing").removeClass(OCF_PREFIX+"-collapse "+OCF_PREFIX+"-in").attr("aria-expanded",!1),this.$trigger.addClass(OCF_PREFIX+"-collapsed").attr("aria-expanded",!1),this.transitioning=1;var s=function(){this.transitioning=0,this.$element.removeClass(OCF_PREFIX+"-collapsing").addClass(OCF_PREFIX+"-collapse").trigger("hidden."+OCF_PREFIX+".collapse")};return t.support[OCF_PREFIX+"Transition"]?void this.$element[a](0).one(OCF_PREFIX+"TransitionEnd",t.proxy(s,this))[OCF_PREFIX+"EmulateTransitionEnd"](i.TRANSITION_DURATION):s.call(this)}}},i.prototype.toggle=function(){this[this.$element.hasClass(OCF_PREFIX+"-in")?"hide":"show"]()},i.prototype.getParent=function(){return t(this.options.parent).find("[data-"+OCF_PREFIX+'="collapse"][data-parent="'+this.options.parent+'"]').each(t.proxy(function(a,i){var s=t(i);this.addAriaAndCollapsedClass(e(s),s)},this)).end()},i.prototype.addAriaAndCollapsedClass=function(t,e){var a=t.hasClass(OCF_PREFIX+"-in");t.attr("aria-expanded",a),e.toggleClass(OCF_PREFIX+"-collapsed",!a).attr("aria-expanded",a)},t.fn[OCF_PREFIX+"Collapse"]=a,t.fn[OCF_PREFIX+"Collapse"].Constructor=i,t(document).on("click."+OCF_PREFIX+".collapse.data-api","[data-"+OCF_PREFIX+'="collapse"]',function(i){var s=t(this);s.attr("data-target")||i.preventDefault();var n=e(s),l=n.data(OCF_PREFIX+".collapse"),o=l?"toggle":s.data();a.call(n,o)})}(jQuery);

/* BUTTON */
+function(t){"use strict";function e(e){return this.each(function(){var n=t(this),o=n.data(OCF_PREFIX+".button"),s="object"==typeof e&&e;o||n.data(OCF_PREFIX+".button",o=new i(this,s)),e&&o.setState(e)})}var i=function(e,n){this.$element=t(e),this.options=t.extend({},i.DEFAULTS,n),this.isLoading=!1};i.DEFAULTS={loadingText:"loading..."},i.prototype.setState=function(e){var i=OCF_PREFIX+"-disabled",n="disabled",o=this.$element,s=o.is("input")?"val":"html",a=o.data();e+="Text",null==a.resetText&&o.data("resetText",o[s]()),setTimeout(t.proxy(function(){o[s](null==a[e]?this.options[e]:a[e]),"loadingText"==e?(this.isLoading=!0,o.addClass(i).attr(n,n).prop(i,!0)):this.isLoading&&(this.isLoading=!1,o.removeClass(i).removeAttr(n).prop(i,!1))},this),0)},t.fn[OCF_PREFIX+"Button"]=e,t.fn[OCF_PREFIX+"Button"].Constructor=i}(jQuery);

/* TRANSITION */
+function(n){"use strict";function i(){var n=document.createElement(OCF_PREFIX),i={WebkitTransition:"webkitTransitionEnd",MozTransition:"transitionend",OTransition:"oTransitionEnd otransitionend",transition:"transitionend"};for(var t in i)if(void 0!==n.style[t])return{end:i[t]};return!1}n.fn[OCF_PREFIX+"EmulateTransitionEnd"]=function(i){var t=!1,r=this;n(this).one(OCF_PREFIX+"TransitionEnd",function(){t=!0});var e=function(){t||n(r).trigger(n.support[OCF_PREFIX+"Transition"].end)};return setTimeout(e,i),this},n(function(){n.support[OCF_PREFIX+"Transition"]=i(),n.support[OCF_PREFIX+"Transition"]&&(n.event.special[OCF_PREFIX+"TransitionEnd"]={bindType:n.support[OCF_PREFIX+"Transition"].end,delegateType:n.support[OCF_PREFIX+"Transition"].end,handle:function(i){return n(i.target).is(this)?i.handleObj.handler.apply(this,arguments):void 0}})})}(jQuery);

/* OCFILTER */
+function($) {
  'use strict';

  // https://codepen.io/martinAnsty/pen/BCotE
  Math.easeIn = function(val, min, max, strength) {
    val /= max;

    return (max - 1) * Math.pow(val, strength) + min;
  };

  function isSet(value) {
    return value !== null && value !== undefined;
  }
  
  function $isSet(element) {   
    if (typeof element == 'string') {
      return (element.length > 0 && $isSet($(element)));
    } else if (typeof element == 'object' && element instanceof $) {
      return element.length > 0;
    }
    
    return false;
  }
  
  var OCFilter = function(element, options) {
    this.button = OCF_PREFIX + 'Button';
    this.popover = OCF_PREFIX + 'Popover';
    this.collapse = OCF_PREFIX + 'Collapse';
    
    this.filterListClass = OCF_PREFIX + '-filter-list';
    this.filterItemClass = OCF_PREFIX + '-filter';
    this.valueListClass = OCF_PREFIX + '-value-list';
    this.valueItemClass = OCF_PREFIX + '-value';
    this.valueSelectedClass = OCF_PREFIX + '-selected';
    this.valueCounterClass = OCF_PREFIX + '-value-count';
    this.valueNameClass = OCF_PREFIX + '-value-name';
    this.sliderItemClass = OCF_PREFIX + '-value-scale';
    this.collapseClass = OCF_PREFIX + '-collapse';   
    this.disabledClass = OCF_PREFIX + '-disabled';

    this.$element = $(element);
    this.$button = this.$element.find('[data-' + OCF_PREFIX + '="button"]');      
    this.$values = this.$element.find('.' + this.valueItemClass);
    this.$sliders = this.$element.find('.' + this.sliderItemClass);
    
    this.options = $.extend({}, OCFilter.DEFAULTS, options);
    
    this.paramsDecodeCache = {};
    this.paramsEncodeCache = {};
    
    this.params = this.options.params;   
    this.responseSlider = null;
    this.mobileActive = false;
             
    this.linkSearch = this.options.urlHost + OCFilter.LINK_SEARCH + this.options.urlParams;
    this.linkFilters = this.options.urlHost + OCFilter.LINK_FILTERS + this.options.urlParams;
    this.linkValues = this.options.urlHost + OCFilter.LINK_VALUES + this.options.urlParams;
             
    this.init();
  };
  
  OCFilter.LINK_SEARCH = 'index.php?route=extension/module/ocfilter/search';
  OCFilter.LINK_FILTERS = 'index.php?route=extension/module/ocfilter/filters';
  OCFilter.LINK_VALUES = 'index.php?route=extension/module/ocfilter/values';  
  
  OCFilter.PARAMS_PATTERN            = /F(\d+)S(\d+)V((N?[0-9DTN]|V\d+)+)/g;
  OCFilter.CHECK_RANGE_PARAM_PATTERN = /^(N?\d*D?\d+)T(N?\d*D?\d+)$/;
  OCFilter.CHECK_RANGE_PATTERN = /^(-?\d*\.?\d+)\-(-?\d*\.?\d+)$/;
  OCFilter.GET_RANGE_PATTERN = /^.*?(-?\d+\.?\d*)\D?.*?(-?\d*\.?\d*)?\D*$/g;

  OCFilter.POPOVER = {
    html: true,
    placement: 'right',
    delay: { 
      'show': 150, 
      'hide': 8000 
    },
    trigger: 'hover',
    container: 'body',
    viewport: { 
      'selector': 'body',
      'padding' : 0 
    }    
  };
  
  OCFilter.DEFAULTS = {
    
  };

  OCFilter.prototype.init = function() {   
    var that = this;

    // Set filter items fixed width   
    if (this.options.layout == 'horizontal' && !this.isMobile()) {
      this.setFilterItemsWidth(this.$element.find('.ocf-body > .' + this.filterListClass + ' > .' + this.filterItemClass));
    }

    // TODO: Add saving position when search button is on
    //this.restoreWinPosition();

    numeral.locale(this.options.numeralLocale);
    
    this.$element.on('click.' + OCF_PREFIX, '.' + OCF_PREFIX + '-disabled, [disabled]', function(e) {
      e.stopPropagation();
      e.preventDefault();
    });
          
    // Toggle dropdown
    this.$element.on('click.' + OCF_PREFIX, '[data-ocf="expand"]', function() {
      var $filterItem = $(this).closest('.' + that.filterItemClass);
      
      if (!(that.isMobile() || $filterItem.hasClass(OCF_PREFIX + '-dropdown'))) {
        return;
      }
      
      $filterItem.toggleClass(OCF_PREFIX + '-open');
      
      $(this).trigger(($filterItem.hasClass(OCF_PREFIX + '-open') ? 'show' : 'hide') + '.' + OCF_PREFIX + '.dropdown.' + that.options.index);
    });
        
    // Specify btn
    this.$element.on('click.' + OCF_PREFIX, '[data-ocf="specify"]', function() {
      $('html, body').stop().animate({ scrollTop: that.$element.find('.' + that.filterListClass).offset().top - 20 }, 300, 'swing');
    });

    // Popovers
    var hovered = false;

    $('body')
      .on('mouseenter.' + OCF_PREFIX, '.' + OCF_PREFIX + '-popover', function(e) {
        hovered = true;
      })
      .on('mouseleave.' + OCF_PREFIX, '.' + OCF_PREFIX + '-popover', function(e) {
        hovered = false;

        $('[aria-describedby="' + $(this).attr('id') + '"]')[that.popover]('toggle');
      })
      .on('hide.' + OCF_PREFIX + '.popover', '[aria-describedby^="' + OCF_PREFIX + '-popover"]', function(e) {
        //setTimeout(() => $(e.target).show(), 0);

        hovered && e.preventDefault();
      });

    $(document).on('hide.' + OCF_PREFIX + '.dropdown.' + this.options.index, '[data-ocf="expand"]', function(e) {      
      var $filterItem = $(this).closest('.' + that.filterItemClass);

      // Hide on dropdown manipulation  
      if ($filterItem.attr('aria-describedby')) {
        // Horizontal
        $filterItem[that.popover]('hide');
      } else {
        $filterItem.find('[aria-describedby^="' + OCF_PREFIX + '-popover"]')[that.popover]('hide');    
      }      
        
      // Set new popover for selecteds
      if (!that.isMobile() && $isSet($filterItem.find('.ocf-more-selected[data-ocf="popover"]'))) {
        $filterItem.find('.ocf-more-selected[data-ocf="popover"]')[that.popover]($.extend({}, OCFilter.POPOVER, { delay: { 'show': 150, 'hide': 200 } }));              
      }               
    }).on('show.' + OCF_PREFIX + '.dropdown.' + this.options.index, '[data-ocf="expand"]', function(e) {       
      if (that.options.layout == 'vertical') {
        that.$element.find('[aria-describedby^="' + OCF_PREFIX + '-popover"]')[that.popover]('hide');
      }      
    });

    this.$element.find('.ocf-more-selected[data-ocf="popover"]')[this.popover]($.extend({}, OCFilter.POPOVER, { delay: { 'show': 150, 'hide': 200 } }));

    this.setDescriptionPopover();

    this.$element.find('.' + this.collapseClass).on('hidden.' + OCF_PREFIX + '.collapse', function(e) {      
      $(this).find('[aria-describedby^="' + OCF_PREFIX + '-popover"]')[that.popover]('hide');    
    });    
    
    // Toggle mobile
    if (this.options.index < 2) {       
      if (this.isMobile()) {
        if (this.isMobileOpened()) {
          this.showMobile();
        } else if (this.isMobileSearched()) {
          // TODO: scroll to first product
          //$('html, body').stop().animate({ scrollTop: that.$element.find('.' + that.filterListClass).offset().top - 20 }, 300, 'swing');      
        }
      }    
        
      $(document).on('click.' + OCF_PREFIX, '[data-ocf="mobile"]', $.proxy(this.toggleMobile, this, undefined));    
    }
    
    // Close dropdown, mobile
    $(document).on('click.' + OCF_PREFIX + '.' + this.options.index, { ocfIndex: this.options.index }, function(e) {
      if (e.data.ocfIndex != that.options.index) {
        return;
      }
      
      var $open = that.$element.find('.' + that.filterItemClass + '.' + OCF_PREFIX + '-open'), $parent;
      
      // Close dropdown
      if ($isSet($open) && !that.isMobile()) {
        $parent = $(e.target).closest('.' + that.filterItemClass);
        $open = $open.not($parent);
        
        if (!$isSet($parent) || $parent.get(0) != $open.get(0)) {
          $open.removeClass(OCF_PREFIX + '-open');
          
          $open.find('[data-ocf="expand"]').trigger('hide.' + OCF_PREFIX + '.dropdown.' + that.options.index);
        }
      } else if (that.isMobile() && that.mobileActive && $(e.target).closest('[data-ocf="mobile"]').length < 1 && $(e.target).closest(that.$element).length < 1) {
        // Close mobile
        e.preventDefault();
        e.stopPropagation();
                
        that.hideMobile();
      }
    });       
    
    // Close mobile on swipe
    // https://gist.github.com/SleepWalker/da5636b1abcbaff48c4d#gistcomment-3421665
    var startX = 0, startY = 0, mobilePosition = this.$element.hasClass(OCF_PREFIX + '-mobile-right') ? 'right' : 'left';
   
    this.$element.on('touchstart touchend', function(e) {
      if ($isSet($(e.target).closest('.ocf-value-slider'))) {
        return true;
      }
            
      if (e.type == 'touchstart') {
        startX = e.originalEvent.changedTouches[0].screenX;
        startY = e.originalEvent.changedTouches[0].screenY;
      } else {
        var diffX = e.originalEvent.changedTouches[0].screenX - startX, diffY = e.originalEvent.changedTouches[0].screenY - startY,
            ratioX = Math.abs(diffX / diffY), ratioY = Math.abs(diffY / diffX),
            absDiff = Math.abs(ratioX > ratioY ? diffX : diffY);

        // Ignore small movements.
        if (absDiff < 30) {
          return;
        }

        if (ratioX > ratioY) {
          if (diffX >= 0) { // Right swipe
            that.toggleMobile(mobilePosition == 'left');        
          } else { // Left swipe
            that.toggleMobile(mobilePosition == 'right');   
          }      
        }/* else {
          if (diffY >= 0) { // Down swipe
            
          } else { // Up swipe

          }
        }*/
      }
    });
    
    // Close mobile on resize
    if (this.options.index < 2) {
      $(window).on('resize.' + OCF_PREFIX + '.body.overflow', function() {
        if (that.mobileActive && window.innerWidth > that.options.mobileMaxWidth) {
          that.hideMobile();
        }
      });
    }
    
    this.$button.on('mousedown', function(e) {
      if (that.isMobile()) {
        localStorage.setItem(OCF_PREFIX + '.mobile.opened', -1); 
      } else if ('undefined' != typeof localStorage) {
        localStorage.removeItem(OCF_PREFIX + '.mobile.opened'); 
      }
    });    
          
    // Search
    this.$element.on('click.' + OCF_PREFIX, function(e) {
      var $value = $(e.target).closest('.' + that.valueItemClass);
    
      if (!$isSet($value)) {
        return;        
      }
      
      var isRadio = $value.hasClass(OCF_PREFIX + '-radio'), $filterItem = $value.closest('.' + that.filterItemClass), filter_key = $value.attr('data-filter-key');

      if (isRadio) {
        $filterItem.find('.' + that.valueItemClass).not($value).removeClass(that.valueSelectedClass);
      }

      $value.toggleClass(that.valueSelectedClass, !$value.hasClass(that.valueSelectedClass));

      if (isRadio) {
        that.removeParams(filter_key);
        
        if ($value.attr('data-value-id') != 'all' && $value.hasClass(that.valueSelectedClass)) {
          that.addParams(filter_key, $value.attr('data-value-id'));
        }        
      } else {
        if ($value.hasClass(that.valueSelectedClass)) {
          that.addParams(filter_key, $value.attr('data-value-id'));
        } else {
          that.removeParams(filter_key, $value.attr('data-value-id'));
        }
      }
           
      var selecteds = [], html = '';     
           
      $filterItem.find('.' + that.valueSelectedClass).each(function() {
        selecteds.push($(this).find('.' + that.valueNameClass).text());
      });
           
      $filterItem.find('.ocf-active-label').html('');
           
      if (selecteds.length > 0) {
        html = selecteds.shift();
        
        if (selecteds.length > 0) {
          html += ' + <span class="ocf-more-selected" data-ocf="popover" data-content="' + selecteds.join(', ') + '">' + selecteds.length + '</span>';
        }
        
        $filterItem.addClass('ocf-active').find('.ocf-active-label').html(html);    
      } else {
        $filterItem.removeClass('ocf-active');
      }         
      
      that.search($value, filter_key);
    });   
    
    // Discard filter(s)
    this.$element.on('click.' + OCF_PREFIX, '[data-ocf-discard]', $.proxy(this.discard, this));    
       
    // Set sliders
    this.$sliders.each(function(_, node) {
      that.setSlider.call(that, node);
    });
    
    // Lazy loading
    function loadData(e) {
      e.preventDefault();  
      
      var $collapse = $(this), $button = $('[data-target="#' + $collapse.attr('id') + '"]')[that.button]('loading'), filter_key = $collapse.attr('data-filter-key'), data = {};

      if (filter_key) {
        data.filter_key = filter_key;
      }      

      if (that.options.params) {
        data[that.options.paramsIndex] = that.options.params;
      }

      if (that.getParams() && that.options.params != that.getParams()) {
        data[that.options.paramsIndex + '_actual'] = that.getParams();
      }

      if (that.responseSlider) {
        data[that.options.paramsIndex + '_slider'] = that.responseSlider;
      }
      
      $collapse.removeAttr('data-ocf-load');     

      $.get(that[filter_key ? 'linkValues' : 'linkFilters'], data, function(response) {
        $collapse.html(response);
        
        if (!filter_key) {
          that.$sliders = that.$sliders.add($collapse.find('.' + that.sliderItemClass).each(function(_, node) { 
            that.setSlider.call(that, node); 
          }));
        }

        that.$values = that.$values.add($collapse.find('.' + that.valueItemClass));
 
        $collapse[that.collapse]('show');
        
        $button[that.button]('reset');
        
        that.setDescriptionPopover($collapse);
        
        if (that.options.layout == 'horizontal' && !that.isMobile()) {
          setTimeout(function(_that) {
            _that.setFilterItemsWidth(_that.$element.find('.ocf-collapse-filter > .' + _that.filterListClass + ' > .' + _that.filterItemClass));
          }, 100, that);          
        } 
      }, 'html');
    }
    
    if (this.options.lazyLoadFilters) {
      this.$element.one('show.' + OCF_PREFIX + '.collapse', '[data-ocf-load="filters"]', loadData);    
    } else if (this.options.layout == 'horizontal' && !that.isMobile()) {
      this.$element.one('shown.' + OCF_PREFIX + '.collapse', '[data-ocf-load="filters"]', function() {
        that.setFilterItemsWidth(that.$element.find('.ocf-collapse-filter > .' + that.filterListClass + ' > .' + that.filterItemClass));
      });   
    }
    
    if (this.options.lazyLoadValues) {
      this.$element.on('show.' + OCF_PREFIX + '.collapse', '[data-ocf-load="values"]', loadData);    
    }      
  };

  OCFilter.prototype.setDescriptionPopover = function($node) {    
    ($node || this.$element).find('.ocf-filter-description[data-ocf="popover"]')[this.popover]($.extend({}, OCFilter.POPOVER, { 
      delay: { 'show': 150, 'hide': 200 },
      placement: (this.options.layout == 'horizontal' ? 'top' : (this.options.position == 'left' ? 'right' : 'left'))
    }));    
  };

  OCFilter.prototype.showMobile = function() {
    this.toggleMobile(true);    
  };
  
  OCFilter.prototype.hideMobile = function() {   
    this.toggleMobile(false);   
  };  
  
  OCFilter.prototype.toggleMobile = function(status) {    
    if ('undefined' == typeof status) {
      status = !this.mobileActive;
    }
       
    if (this.isMobileOpened()) {
      this.$element.toggleClass(OCF_PREFIX + '-mobile-open', status);
    }
    
    this.$element.toggleClass(OCF_PREFIX + '-mobile-active', status);
    
    $('body').toggleClass(OCF_PREFIX + '-overflow-hidden', status);
    
    this.$element.trigger((status ? 'show' : 'hide') + '.' + OCF_PREFIX + '.mobile');
    
    this.mobileActive = !!status;
    
    if (this.options.mobileRememberState) {
      localStorage.setItem(OCF_PREFIX + '.mobile.opened', this.mobileActive + 0); 
    }        
  };    

  OCFilter.prototype.setFilterItemsWidth = function($nodes) {
    $nodes.each(function() { 
      $(this).attr('data-width', $(this).width());
    }).each(function() { 
      $(this).css({ 'width': $(this).attr('data-width') });
    });     
  };

  OCFilter.prototype.disableSliders = function() {
    var that = this;
    
    this.$sliders.attr('disabled', 'disabled').addClass(OCF_PREFIX + '-loading').each(function(i) {
      var $element = $(this);

      if (that.options.sliderInput) {
        $($element.data().inputMin + ',' + $element.data().inputMax).attr('disabled', 'disabled');
      } else {
        return false;
      }
    });
  };

  OCFilter.prototype.disableValues = function() {
    this.$values.addClass(this.disabledClass);
  };

  OCFilter.prototype.preload = function() {
    this.disableValues();
    this.disableSliders();   
  };  
  
  OCFilter.prototype.isMobile = function() {
    return this.$element.find('.ocf-is-mobile').is(':visible');
  };
  
  OCFilter.prototype.isMobileOpened = function() {   
    return (this.options.mobileRememberState && localStorage.getItem(OCF_PREFIX + '.mobile.opened') > 0);
  };   
  
  OCFilter.prototype.isMobileSearched = function() {   
    return (localStorage.getItem(OCF_PREFIX + '.mobile.opened') === -1);
  };     
  
  OCFilter.prototype.showValuePopover = function($target) {
    if (this.isMobile()) {
      return;
    }
    
    if ($target.is(':hidden')) {
      $target = $target.closest(':visible:first');
    }

    if (this.options.layout == 'horizontal' || $target.closest('.' + OCF_PREFIX + '-value-list-body').hasClass(OCF_PREFIX + '-auto-column')) {
      $target = $target.closest('.' + this.filterItemClass);
    } else if ($target.hasClass('ocf-value-scale')) {
      $target = $target.find('.ocf-noUi-base');
    }

    if ($target.attr('aria-describedby') && $target.data('ocf.popover')) {       
      return;
    }

    var that = this, options = $.extend({}, OCFilter.POPOVER);

    options.content = function() {      
      var $btn = that.$button.filter('.ocf-search-btn-popover').remove();
    
      that.$button = that.$button.add($btn);
          
      return $btn;
    }
    
    options.container = this.$element;
           
    if (this.options.mobile) {
      options.placement = 'bottom';
    } else if (this.options.layout == 'horizontal') {
      options.placement = 'top';
    } else if (this.options.position == 'right') {
      options.placement = 'left';
    } else if (this.options.position == 'left') {
      options.placement = 'right';
    }        

    $target[this.popover](options)[this.popover]('show');
    
    this.$element.find('[data-original-title]').not($target).not('.ocf-more-selected, .ocf-filter-description')[this.popover]('destroy');
  };  
  
  OCFilter.prototype.saveWinPosition = function() {
    localStorage.setItem('ocfPositionY', window.pageYOffset);
  };
  
  OCFilter.prototype.restoreWinPosition = function() {
    if (null !== localStorage.getItem('ocfPositionY')) {
      window.scrollTo(0, parseInt(localStorage.getItem('ocfPositionY')));
      
      localStorage.removeItem('ocfPositionY');
    }        
  };
  
  OCFilter.prototype.discard = function(e) {
    e.preventDefault();
    e.stopPropagation();
  
    var that = this, $this = $(e.target), $filterItem, filter_key = $this.attr('data-ocf-discard') || 0;
    
    if (filter_key == '*') {     
      this.$values.filter('.' + this.valueSelectedClass).removeClass(this.valueSelectedClass);     
      
      this.$element.find('.' + this.filterItemClass).removeClass('ocf-active').filter(':not(.ocf-slider)').find('.ocf-active-label').html('');
      
      this.clearParams();
      
      this.search($this); 
    } else {
      $filterItem = $this.closest('.' + this.filterItemClass);      
     
      $filterItem.removeClass('ocf-active').find('.' + this.valueSelectedClass).each(function() {
        $(this).removeClass(that.valueSelectedClass);
      });
      
      if (!$filterItem.hasClass('ocf-slider')) {
        $filterItem.find('.ocf-active-label').html('');
      }      
      
      this.removeParams(filter_key);
      
      this.search($this, filter_key);    
    }
  };
  
  OCFilter.prototype.search = function($value, filter_key) {
    var that = this, timer, loaded = false, isSlider = $value.hasClass(this.sliderItemClass), url = this.linkSearch, data = {};

    this.$button[this.button]('loading');

    $('[data-ocf-discard="*"]').toggleClass(this.disabledClass, !(!!this.getParams())).prop('disabled', !(!!this.getParams()));

    if (filter_key) {
      data.filter_key = filter_key;
    }    

    if (this.getParams()) {
      data[this.options.paramsIndex] = this.getParams();
    }   
    
    if (!$value.attr('data-ocf-discard') && (this.options.searchButton || isSlider)) {
      this.showValuePopover($value);
    } else if ($value.attr('data-ocf-discard')) {
      this.$element.find('[aria-describedby^="' + OCF_PREFIX + '-popover"]')[that.popover]('hide');  
    }

    timer = setTimeout(function(_that) {
      !loaded && _that.preload();
    }, 300, this);

    $.get(url, data, function(json) {
      clearTimeout(timer);
      
      loaded = true;

      that.preload();
      
      that.responseSlider = null;
    
      /* Start update */
      var id, value, $target, total, selected;
      
      for (id in json.values) {
        value = json.values[id];
        
        $target = $('#ocf-v-' + id + '-' + that.options.index);

        if (!$isSet($target)) {         
          continue;
        }
        
        total = value[0];
        selected = value[1];

        if (total === 0 && !selected) {
          $target.addClass(that.disabledClass).removeClass(that.valueSelectedClass);
          
          that.removeParams($target.attr('data-filter-key'), $target.attr('data-value-id'));
        } else {
          $target.removeClass(that.disabledClass);
        }

        if (that.options.showCounter) {
          $target.find('.' + that.valueCounterClass).html(total);
        }
      } // end values each

      if (json.total === 0 || (that.options.params + that.getParams()).length < 1) {
        that.$button.removeAttr('onclick').addClass(that.disabledClass).html(that.options.textSelect);
      } else {
        if (that.options.searchButton || isSlider) {
          that.$button.attr('onclick', 'location = \'' + json.href + '\'').removeClass(that.disabledClass).removeAttr('disabled').html(json.button_total);
        } else {
          //that.saveWinPosition();
                  
          window.location = json.href;

          return;
        }
      }

      if (that.options.showCounter) {
        that.$values.filter('.' + that.disabledClass).find('.' + that.valueCounterClass).text(0);
      }      
      
      that.$values.filter('.' + that.valueSelectedClass + '.' + that.disabledClass).each(function() {
        $(this).removeClass(that.valueSelectedClass);
        
        that.removeParams($(this).attr('data-filter-key'), $(this).attr('data-value-id'));
      });

      that.$sliders.removeClass(OCF_PREFIX + '-loading').each(function(i) {
        var $element = $(this);

        if ($element.is($value) || that.hasParam($element.data().filterKey)) {
          $element.add($element.data().inputMin + ',' + $element.data().inputMax).removeAttr('disabled');
        } 
      });      

      // Update sliders
      if (!$.isPlainObject(json.sliders) || $.isEmptyObject(json.sliders)) {
        return;
      }

      // Setting availabe sliders for the hidden filters lazy loading
      that.responseSlider = Object.keys(json.sliders); 

      if (that.responseSlider.length > 0) {
        that.responseSlider = that.responseSlider.map(function(v) { return v.replace(/-/g, '.'); });
      }

      for (var filter_key in json.sliders) {
        var $element = $('#ocf-s-' + filter_key + '-' + that.options.index);

        if (!$isSet($element) || !$element.get(0).hasOwnProperty('noUiSlider')) {
          continue;
        }

        var
          sliderObj = $element.get(0).noUiSlider,
          sliderValues = sliderObj.get(),
          min = parseFloat(json.sliders[filter_key]['min']),
          max = parseFloat(json.sliders[filter_key]['max']),
          minStart = min,
          maxStart = max;
          
        if (!$.isArray(sliderValues)) {
          sliderValues = [ sliderValues, sliderValues ];
        }

        if (that.hasParam(filter_key)) {
          if (sliderValues[0] >= min) {
            minStart = sliderValues[0];
          }

          if (sliderValues[1] <= max) {
            maxStart = sliderValues[1];
          }
        }

        if (min != max) {
          $element.data({ min: min, max: max, minStart: minStart, maxStart: maxStart });

          $element.add($element.data().inputMin + ',' + $element.data().inputMax).removeAttr('disabled');   

          if (sliderObj.hasUpdate) {
            delete sliderObj.hasUpdate;
          }

          that.setSlider($element.get(0));                      
        }
      }
      /* End update */
    }, 'json');
  };  
   
  OCFilter.prototype.setSlider = function(element) {   
    var

    that = this,    
    $element = $(element),
    $filterItem = $element.closest('.' + this.filterItemClass),
    
    options = $element.data(),
    
    min = parseFloat(options.min),
    max = parseFloat(options.max),      
    
    isRange = options.range,
    
    minStart = parseFloat(options.minStart),
    maxStart = parseFloat(options.maxStart),    
    
    $textMin = $isSet(options.textMin) ? $(options.textMin) : false,
    $textMax = $isSet(options.textMax) ? $(options.textMax) : false,
    $inputMin = $isSet(options.inputMin) ? $(options.inputMin) : false,
    $inputMax = $isSet(options.inputMax) ? $(options.inputMax) : false,            
       
    decimals = 0,
    pipsCount = 5;

    // Decimal   
    if (/*(max - min).toString().replace(/\./, '').length < 4 && */(/\./.test(min) || /\./.test(max))) {
      decimals = Math.max(
        min.toString().replace(/^\d+(\.|$)/, '').length,
        max.toString().replace(/^\d+(\.|$)/, '').length
      );
    }    
    
    if (decimals > 2) {
      decimals = 2;
    } else if (!decimals && max > 1000 && ((max - min) / pipsCount < 1000)) {
      decimals = 1;
    }
    
    var formatSlide = {
      to: function(v, h, r) {        
        var format = '(0';
        
        if (v < 100 && decimals) {
          format += '.' + '0'.repeat(decimals);
        } else if (v > 1000) {
          format += '.0';
        }  
        
        format += ' a)';
        
        return (v > 1000 ? numeral(v).format(format) : v.toFixed(decimals));
      },
      from: function(v) {
        return v;
      }
    }, 
    
    formatPips = $.extend({}, formatSlide, {
      to: function(v) {
        var dec = '';
        
        if (decimals && (v < 100 || v > 1000)) {
          dec = '0'.repeat(decimals);          
        }
        
        if (dec) {
          dec = '.' + dec;
        }
        
        return numeral(v).format('(0' + dec + ' a)');
      }
    });
    
    var slider = {
      animate: false,
      cssPrefix: OCF_PREFIX + '-noUi-',
      behaviour: 'snap',
      connect: true,
      range: {
        'min': min,
        'max': max
      },   
      format: formatSlide      
    };
    
    if (this.options.sliderPips) {
      slider.pips = { 
        mode: 'count', 
        values: pipsCount, 
        density: 4,
        filter: function(value, type) {
          if (type === 0) {
            return -1;
          }
            
          return 1;
        },  
        format: formatPips
      };
    }
    
    if (isRange) {
      slider.start = [ minStart, maxStart ];
    } else {
      slider.start = minStart;
    }    

    // Logarithmic scale
    if (this.options.priceLogarithmic && options.filterKey == '2.0' && (max - min) > 10000) {
      var _i = 25, _strength = 1.5;

      for (; _i < 100; _i += 25) {
        slider.range[_i + '%'] = Math.ceil(Math.easeIn(((max - min) / 100 * _i), min, max, _strength));
      }
    }

    // Create, Update
    if (element.hasOwnProperty('noUiSlider')) {     
      element.noUiSlider.updateOptions(slider, false);
      
      return true;
    }     
      
    noUiSlider.create(element, slider);

    // Events
    element.noUiSlider.on('update', function(formatted, handle, values, tap, positions, sliderObj) {
      if (false === sliderObj.hasUpdate) {
        sliderObj.hasUpdate = true;
      }
       
      $textMin && $textMin.html(formatted[0]);
      $textMax && $textMax.html(formatted[1]);

      $inputMin && $inputMin.val(values[0].toFixed(decimals));
      $inputMax && $inputMax.val(values[1].toFixed(decimals));
    });
    
    element.noUiSlider.on('set', function(formatted, handle, values, tap, positions, sliderObj) {
      if (tap || false === sliderObj.hasUpdate) {
        return;
      }

      sliderObj.hasUpdate = false;
    
      var params = that.getParams(), hasAdded = false;

      that.removeParams(options.filterKey);

      if (positions.length > 1 && (positions[1] - positions[0]) < 100) {
        that.addParams(options.filterKey, values[0].toFixed(decimals) + '-' + values[1].toFixed(decimals));
        
        hasAdded = true;
      } else if (positions.length === 1) {
        that.addParams(options.filterKey, values[0].toFixed(decimals) + '-' + values[0].toFixed(decimals));
        
        hasAdded = true;
      }

      $filterItem.toggleClass('ocf-active', hasAdded);

      if (params != that.getParams()) {
        that.search($element);

        $textMin && $textMin.html(formatted[0]);
        $textMax && $textMax.html(formatted[1]);      
      }          
    });

    if (that.options.sliderInput && $inputMin) {
      var timer, update = function(input) {
        if (input.value == '') {
          return false;
        }

        if (input.value < min || input.value > max) {
          input.value = $(input).is(options.inputMin) ? min : max;
        }

        if ($(input).is(options.inputMin)) {
          this.set([ input.value, null ]);
        } else {
          this.set([ null, input.value ]);
        }                    
      };

      $(options.inputMin + ',' + options.inputMax).on('change.slider-input-' + options.filterKey + ' keyup.slider-input-' + options.filterKey, function(e) {       
        clearTimeout(timer);

        timer = setTimeout($.proxy(update, element.noUiSlider, this), (e.type == 'change' ? 400 : 1500));
      });
    }
  };  

  /* Params */
  OCFilter.prototype.getParams = function() {
    return this.params;
  };

  OCFilter.prototype.setParams = function(params) {    
    this.params = params;
  };
  
  OCFilter.prototype.clearParams = function() {
    this.params = '';
  };
  
  OCFilter.prototype.addParams = function(filter_key, value_id) {
    var paramsObj = this.decodeParams(this.params);

    if (isSet(paramsObj[filter_key])) {
      paramsObj[filter_key].push(value_id);
    } else {
      paramsObj[filter_key] = [ value_id ];
    }

    this.setParams(this.encodeParams(paramsObj));
  };  

  OCFilter.prototype.hasParam = function(filter_key) {
    var paramsObj = this.decodeParams(this.params);
    
    return isSet(paramsObj[filter_key]);
  };
  
  OCFilter.prototype.removeParams = function(filter_key, value_id) {
    if (!this.params) {
      return;      
    }

    var paramsObj = this.decodeParams(this.params);    
    
    if (!isSet(paramsObj[filter_key])) {
      return;
    }
    
    if (!isSet(value_id)) {
      delete paramsObj[filter_key];
    } else if (paramsObj[filter_key].indexOf(value_id) != -1) {
      if (paramsObj[filter_key].length === 1) {
        delete paramsObj[filter_key];
      } else {
        paramsObj[filter_key].splice(paramsObj[filter_key].indexOf(value_id), 1);
      }     
    } else {
      return;
    }

    this.setParams(this.encodeParams(paramsObj));
  };  

  OCFilter.prototype.decodeParams = function(paramsStr) {
    if ($.trim(paramsStr).length < 1) {
      return {};
    }

    // TODO: Object caching
    /*
    var key = paramsStr;

    if (isSet(this.paramsDecodeCache[key])) {
      return $.extend({}, this.paramsDecodeCache[key]);
    }
    */    
    var 
      paramsObj = {}, match, range,
      reSneg = new RegExp(this.options.sepSneg, 'g'), 
      reSdot = new RegExp(this.options.sepSdot, 'g');

    while (match = OCFilter.PARAMS_PATTERN.exec(paramsStr)) {     
      if (isSet(match[4])) {
        if (OCFilter.CHECK_RANGE_PARAM_PATTERN.test(match[3])) {
          range = match[3]
            .replace(reSneg, '-')
            .replace(reSdot, '.')
            .replace(this.options.sepSran, '-');  
          
          paramsObj[match[1] + '.' + match[2]] = [ range ];
        } else {
          paramsObj[match[1] + '.' + match[2]] = match[3].split(this.options.sepVals);
        }
      }
    }

    //this.paramsDecodeCache[key] = paramsObj;

    return $.extend({}, paramsObj);
  };
  
  OCFilter.prototype.encodeParams = function(paramsObj) {   
    if ($.isEmptyObject(paramsObj)) {
      return '';
    }
       
    // TODO: Object caching
    /*
    var key = JSON.stringify(paramsObj);
    
    if (isSet(this.paramsEncodeCache[key])) {
      return this.paramsEncodeCache[key];
    }    
    */
    var paramsArr = [], paramsStr = '', i, filterKey;

    for (i in paramsObj) {
      if (!paramsObj.hasOwnProperty(i) || typeof paramsObj[i] != 'object' || paramsObj[i].length < 1) {
        continue;
      }
      
      filterKey = i.replace('.', this.options.sepFsrc);
      
      if (OCFilter.CHECK_RANGE_PATTERN.test(paramsObj[i][0])) {
        paramsArr.push(filterKey + this.options.sepVals + paramsObj[i][0]
          .replace(OCFilter.GET_RANGE_PATTERN, '$1' + this.options.sepSran + '$2')
          .replace(/\./g, this.options.sepSdot)
          .replace(/-/g, this.options.sepSneg)
        );
      } else {
        paramsArr.push(filterKey + this.options.sepVals + paramsObj[i].join(this.options.sepVals));
      }            
    }

    if (paramsArr.length) {
      paramsStr = this.options.sepFilt + paramsArr.join(this.options.sepFilt);
    }
    
    //this.paramsEncodeCache[key] = paramsStr;
    
    return paramsStr;
  };  

  function Plugin(option) {
    return this.each(function() {
      var $this   = $(this);
      var data    = $this.data('ocfilter');
      var options = $.extend({}, OCFilter.DEFAULTS, $this.data(), typeof option == 'object' && option);

      if (!data) {
        $this.data('ocfilter', (data = new OCFilter(this, options)));
      }
      
      if (typeof option == 'string') {
        data[option]();
      }
    });
  }

  $.fn.ocfilter             = Plugin;
  $.fn.ocfilter.Constructor = OCFilter;
}(jQuery);