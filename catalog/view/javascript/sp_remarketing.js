function remarketingAddToCart(json) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'add_to_cart_sent');
	heading = $('h1').text();
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}
	
	if (json['remarketing']) {
		if (json['remarketing']['google_status'] != '0') {
			if (typeof gtag != 'undefined') {
				gtag('event', 'add_to_cart', json['remarketing']['google_remarketing_event']);
			}
			
		if (json['remarketing']['google_ads_identifier_cart'] != '') {
			if (typeof gtag != 'undefined') {
				gtag('event', 'conversion', json['remarketing']['google_ads_event']);
			}
		}
		}
		
		if (json['remarketing']['facebook_status'] != '0' && json['remarketing']['facebook_pixel_status'] != '0') {
			if (typeof fbq != 'undefined') {
				fbq('track', 'AddToCart', json['remarketing']['facebook_pixel_event'], {eventID: json['remarketing']['time']}); 
			}
		}
		
		if (json['remarketing']['tiktok_status'] != '0') {
			if (typeof ttq != 'undefined') {
				ttq.track('AddToCart', json['remarketing']['tiktok_event']); 
			}
		}
		 
		if (json['remarketing']['vk_status'] != '0') {
			if (typeof VK != 'undefined') {
				VK.Retargeting.ProductEvent(json['remarketing']['vk_identifier'], 'add_to_cart',
					{"currency_code":json['remarketing']['currency'],
						"products":[{"id":json['remarketing']['vk_product_id'], "price":json['remarketing']['price']}]
					});
			}
		}
		
		if (json['remarketing']['ecommerce_status'] != '0') {
			window.dataLayer = window.dataLayer || [];
			dataLayer.push({
				'ecommerce': {
				'currencyCode': json['remarketing']['ecommerce_currency'],
				'actionField': {'list': heading},
					'add': {                                
						'products': [json['remarketing']['ecommerce_product']]
					}
				},
				'event': 'gtm-ee-event',
				'gtm-ee-event-category': 'Enhanced Ecommerce',
				'gtm-ee-event-action': 'Adding a Product to a Shopping Cart',
				'gtm-ee-event-non-interaction': 'False'
			});
		}
		
		if (json['remarketing']['ecommerce_ga4_status'] != '0') {
			if (typeof gtag != 'undefined') {
				json['remarketing']['ecommerce_ga4_event']['items'][0]['item_list_name'] = heading;
				gtag('event', 'add_to_cart', json['remarketing']['ecommerce_ga4_event']);
			}
		}
		
		if (json['remarketing']['retailrocket_status'] != '0' && typeof rrApi != 'undefined') {
			rrApi.addToBasket(json['remarketing']['product_id']); 
		}
		if (typeof events_cart_add != 'undefined') {
			events_cart_add();
		}
	}
}	  

function remarketingRemoveFromCart(json) {
	
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'remove_from_cart_sent');
	
	heading = $('h1').text();
	
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}

	if (json['remarketing']) {
		if (json['remarketing']['ecommerce_status'] != '0') {
			window.dataLayer = window.dataLayer || [];
			dataLayer.push({
				'ecommerce': {
				'currencyCode': json['remarketing']['ecommerce_currency'],
					'remove': {                                 
						'products': [[json['remarketing']['ecommerce_product']]]
					}
				},
				'event': 'gtm-ee-event',
				'gtm-ee-event-category': 'Enhanced Ecommerce',
				'gtm-ee-event-action': 'Removing a Product from a Shopping Cart',
				'gtm-ee-event-non-interaction': 'False'
			});
		}
		
		if (json['remarketing']['ecommerce_ga4_status'] != '0') {
			if (typeof gtag != 'undefined') {
				json['remarketing']['ecommerce_ga4_event']['items'][0]['item_list_name'] = heading;
				gtag('event', 'remove_from_cart', json['remarketing']['ecommerce_ga4_event']);
			}
		}

		if (json['remarketing']['vk_status'] != '0') {
			if (typeof VK != 'undefined') {
				VK.Retargeting.ProductEvent(json['remarketing']['vk_identifier'], 'remove_from_cart',
					{"currency_code":json['remarketing']['currency'],
						"products":[{"id": json['remarketing']['vk_product_id'], "price": json['remarketing']['price']}]
					});
			}
		}
	}
}	

function remarketingRemoveFromSimpleCart(cart_product_id, quantity) {
	if (cart_product_id && quantity) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/removeProduct',
		data: {'product_id' : cart_product_id, 'quantity': quantity},
			dataType: 'json',
            success: function(json) { 
				remarketingRemoveFromCart(json);
			}
		});
	}
}

function sendEcommerceClick(data) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'click_sent');
	heading = $('h1').text();
	
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}

	currency = $('.currency_ecommerce_code').val();
	window.dataLayer = window.dataLayer || [];
	if (data) {
		dataLayer.push({
			'ecommerce': {
			'currencyCode': currency,
				'click': {
					'actionField': {'list': heading},                                 
					'products': [data]
				}
			},
			'event': 'gtm-ee-event',
			'gtm-ee-event-category': 'Enhanced Ecommerce',
			'gtm-ee-event-action': 'Product Clicks',
			'gtm-ee-event-non-interaction': 'False'
		});
	}
}	 

function sendEcommerceGa4Click(data) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'ga4_click_sent');
	heading = $('h1').text();
	currency = $('.currency_ecommerce_code').val();
	
	if (data) {
		if (typeof gtag != 'undefined') {
			gtag('event', 'select_item', {
				'send_to': $('.ecommerce_ga4_identifier').val(),
				'currency': currency,
				'items': [data]
			});
		}
	}
}	 

function sendEcommerceMeasurementClick(data) {
	heading = $('h1').text();
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}

	window.dataLayer = window.dataLayer || [];
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendMeasurementClick',
		data: {products : data, heading: heading},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'click_sent');
			}
		});
	}
}	 

function sendEcommerceGa4MeasurementClick(data) {
	heading = $('h1').text();
	
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}

	window.dataLayer = window.dataLayer || [];
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendGa4MeasurementClick',
		data: {products : data, heading: heading},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'measurement_ga4_click_sent');
			}
		});
	}
}	 

function sendEcommerceImpressions(data, measurement = false) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'impressions_sent');
	heading = $('h1').text();
	
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}

	currency = $('.currency_ecommerce_code').val();
	window.dataLayer = window.dataLayer || [];
	
	if (data) {
		dataLayer.push({
			'ecommerce': {
				'currencyCode': currency,
				'impressions': data
			},
			'event': 'gtm-ee-event',
			'gtm-ee-event-category': 'Enhanced Ecommerce',
			'gtm-ee-event-action': 'Product Impressions',
			'gtm-ee-event-non-interaction': 'False'
		});
	}
	
	if (measurement) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendMeasurementImpressions',
		data: {products : data, heading: heading},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'impressions_measurement_sent');
			}
		});
	} 
}	 

function sendEcommerceGa4Impressions(data, search = false, measurement = false) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'ga4_impressions_sent');
	currency = $('.currency_ecommerce_code').val();
	heading = $('h1').text();
	
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}
	
	if (data && measurement == false) {
		if (typeof gtag != 'undefined') {
			if (!search) {
				event_name = 'view_item_list';
			} else {
				event_name = 'view_search_results';
			}

			gtag('event', event_name, {
				'send_to': $('.ecommerce_ga4_identifier').val(),
				'currency': currency,
				'items': data 
			});
		}
	}
	if (data && measurement == true) {
		if (!search) {
			event_name = 'view_item_list';
		} else {
			event_name = 'view_search_results';
		}
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendGa4MeasurementImpressions',
		data: {products: data, event_name: event_name, heading: heading},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_ga4_measurement_sent');
			}
		});
	}
}	 
 
function sendEcommerceDetails(data, measurement = false) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_sent');
	heading = $('h1').text();
	
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}

	window.dataLayer = window.dataLayer || [];
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendDetails',
		data: {products : data, heading: heading},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_measurement_sent');
			}
		});
	}
}	 

function sendEcommerceGa4Details(data, measurement = false) {
	if (data && measurement == false) {
		if (typeof gtag != 'undefined') {
			gtag('event', 'view_item', data);
		}
		console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_ga4_sent');
	}	
	if (data && measurement == true) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendGa4Details',
		data: {products : data},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_ga4_measurement_sent');
			}
		});
	}
}	 

function sendEcommerceCart(data) { 
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendEcommerceCart',
		data: {cart : data},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'ecommerce_cart_sent');
			}
		});
	}
}	 

function sendEcommerceGa4Cart(data) { 
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendEcommerceGa4Cart',
		data: {cart : data},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'ecommerce_ga4_cart_sent');
			}
		});
	}
}	 

function sendFacebookDetails(data) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendFacebookDetails',
		data: {products : data['products'], time : data['time'], url : window.location.href},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_facebook_sent');
			}
		});
	}
}	 

function sendFacebookCart(data) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendFacebookCart',
		data: {cart : data, url : window.location.href},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'facebook_cart_sent');
			}
		});
	}
}	 

function sendFacebookCategoryDetails(data) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendFacebookCategory',
		data: {products : data['products'], time : data['time'], url : window.location.href},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'category_details_facebook_sent');
			}
		});
	}
}	 

function sendEsputnikDetails(data) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendEsputnik',
		data: {product : data},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_esputnik_sent');
			}
		});
	}
}

function sendEsputnikCategoryDetails(data) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendEsputnikCategory',
		data: {category : data},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'category_esputnik_sent');
			}
		});
	}
}

function sendGoogleRemarketing(data) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'remarketing_event_sent');

	if (typeof gtag != 'undefined') {
		gtag('event', data['event'], data['data']);
	}
}	

function sendWishList(json) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'wishlist_sent');
	
	heading = $('h1').text();
	
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}
	
		if (json['remarketing']['vk_status'] != '0') {
			if (typeof  VK != 'undefined') {
				VK.Retargeting.ProductEvent(json['remarketing']['vk_identifier'], 'add_to_wishlist', {"currency_code":json['remarketing']['currency'],
					"products":[{"id": json['remarketing']['vk_product_id'], "price": json['remarketing']['price']}]
				});
			}
		}
		
		if (json['remarketing']['facebook_status'] != '0' && json['remarketing']['facebook_pixel_status'] != '0') {
			if (typeof fbq != 'undefined') {
				fbq('track', 'AddToWishlist', json['remarketing']['facebook_pixel_event'], {eventID: json['remarketing']['time']}); 
			}

		}
		
		if (json['remarketing']['ecommerce_ga4_status'] != '0') {
			if (typeof gtag != 'undefined') {
				json['remarketing']['ecommerce_ga4_event']['items'][0]['item_list_name'] = heading;
				gtag('event', 'add_to_wishlist', json['remarketing']['ecommerce_ga4_event']);
			}
		}
		
		if (typeof events_wishlist != 'undefined') {
			events_wishlist();
		}
}

function remarketingQuickOrder(json) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'quick_order_sent');
	
	if (json['remarketing']) {
		if (json['remarketing']['google_status'] != '0') {
			if (typeof gtag != 'undefined') {
				gtag('event', 'purchase', {
				'send_to': json['remarketing']['google_identifier'],
				'value': json['remarketing']['order_info']['google_total'],
				'items': json['remarketing']['google_products']
				});
			}
		}
	
		if (json['remarketing']['google_ads_identifier'] != '') {
			if (typeof gtag != 'undefined') {
				gtag('event', 'conversion', {
					'send_to': json['remarketing']['google_ads_identifier'],
					'value': json['remarketing']['order_info']['google_conversion_total'],
					'currency':  json['remarketing']['google_currency']
				});
			}
		}

		if (json['remarketing']['ecommerce_ga4_status'] != '0') {
			if (typeof gtag != 'undefined') {
			gtag('event', 'purchase', {
				'send_to': json['remarketing']['ecommerce_ga4_identifier'],
				'transaction_id': json['remarketing']['order_info']['order_id'],
				'value': json['remarketing']['order_info']['ecommerce_total'],
				'currency': json['remarketing']['ecommerce_currency'],
				'items': json['remarketing']['ecommerce_ga4_products'] 
			});	
			}			
		}
		
		if (json['remarketing']['facebook_status'] != '0' && json['remarketing']['facebook_pixel_status'] != '0') {
			 if (typeof fbq != 'undefined') {
				fbq('track', 'Purchase', {
				'contents': json['remarketing']['facebook_products'],
				'content_type': 'product', 
				'num_items': json['remarketing']['facebook_items'],
				'value': json['remarketing']['order_info']['facebook_total'],
				'currency': json['remarketing']['facebook_currency'] 
                }, 
				{'eventID': json['remarketing']['time']}
				); 
				if (json['remarketing']['facebook_lead'] != '0') {
					fbq('track', 'Lead', {
						'value': json['remarketing']['order_info']['facebook_total'],
						'currency': json['remarketing']['facebook_currency'] 
					},{'eventID': json['remarketing']['time']}  
				); 
				}
			}
		}
		
		if (json['remarketing']['tiktok_status'] != '0') {
			 if (typeof ttq != 'undefined') {
				ttq.track('Purchase', {
				'contents': json['remarketing']['tiktok_products'],
				'content_type': 'product', 
				'value': json['remarketing']['order_info']['default_total'],
				'currency': json['remarketing']['currency'] 
                }); 
			}
		}
		
		if (json['remarketing']['vk_status'] != '0') {
			if (typeof VK != 'undefined') {
				VK.Retargeting.ProductEvent(json['remarketing']['vk_identifier'], 'purchase',
					{"currency_code":json['remarketing']['currency'],
						"products":json['remarketing']['vk_products']
					});
			}
		}

		if (json['remarketing']['mytarget_status'] != '0') {
			var _tmr = _tmr || [];
			_tmr.push({
				'type': 'itemView',
				'productid': json['remarketing']['mytarget_products_list'],
				'pagetype': 'purchase',
				'list': json['remarketing']['mytarget_list'],
				'totalvalue': json['remarketing']['order_info']['default_total']
			});
		}
		
		if (json['remarketing']['ecommerce_status'] != '0') {
			window.dataLayer = window.dataLayer || [];
			dataLayer.push({
				'ecommerce': {
				'currencyCode': json['remarketing']['ecommerce_currency'],
					'purchase': { 
						'actionField': {
						'id': json['remarketing']['order_info']['order_id'],
						'affiliation': json['remarketing']['order_info']['store_name'],
						'revenue': json['remarketing']['order_info']['ecommerce_total'], 
						'shipping': json['remarketing']['order_info']['shipping'],
						},
						'products': json['remarketing']['ecommerce_products']
					}
				},
				'event': 'gtm-ee-event',
				'gtm-ee-event-category': 'Enhanced Ecommerce',
				'gtm-ee-event-action': 'Purchase Fast',
				'gtm-ee-event-non-interaction': 'False'
			});
		}
		
		if (json['remarketing']['reviews_status']) {
			$.getScript('https://apis.google.com/js/platform.js?onload=renderOptIn');
			window.renderOptIn = function() {  
				window.gapi.load('surveyoptin', function() {
					window.gapi.surveyoptin.render({
					"merchant_id": json['remarketing']['remarketing_google_merchant_identifier'],
					"order_id": json['remarketing']['order_info']['order_id'],
					"email": json['remarketing']['order_info']['email'],
					"delivery_country": json['remarketing']['remarketing_reviews_country'],
					"estimated_delivery_date": json['remarketing']['reviews_order_date'],
					"opt_in_style": "CENTER_DIALOG" 
				})
				})
			}
		}
		
		if (json['remarketing']['retailrocket_status'] != '0' && typeof rrApi != 'undefined') { 
			(window["rrApiOnReady"] = window["rrApiOnReady"] || []).push(function() {
			try {
				if (json['remarketing']['order_info']['email'] != '') {
					rrApi.setEmail(json['remarketing']['order_info']['email']);
				};
				rrApi.order({
					"transaction": json['remarketing']['order_info']['order_id'],
					"items": json['remarketing']['retailrocket_products']
				});
			} catch(e) {}
			})
		}
		
		if (typeof quickPurchase != 'undefined') {
			quickPurchase(json['remarketing']['order_info']['order_id'], json['remarketing']['order_info']['default_total']);
		}
		
	}
}
	
function decodePostParams(str) {
    return (str || document.location.search).replace(/(^\?)/,'').split("&").map(function(n){return n = n.split("="),this[n[0]] = n[1],this}.bind({}))[0];
}

$(document).ready(function() {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'sp remarketing 5.0 start');

	$.each($("[onclick*='cart.add'], [onclick*='get_revpopup_cart'], [onclick*='addToCart'], [onclick*='get_oct_popup_add_to_cart']"), function() {
		product_id = $(this).attr('onclick').match(/[0-9]+/);
		$(this).addClass('remarketing_cart_button').attr('data-product_id', product_id);
	});
	
	$(document).ajaxSuccess(function(event, xhr, settings) {
		if (settings.url == 'index.php?route=checkout/cart/add' || settings.url == 'index.php?route=checkout/cart/add&oct_dirrect_add=1' || settings.url == 'index.php?route=madeshop/cart/add' || settings.url == 'index.php?route=extension/module/frametheme/ft_cart/add' || settings.url == 'index.php?route=extension/basel/basel_features/add_to_cart') {
			if (typeof xhr.responseJSON['remarketing'] !== 'undefined') {
				if (typeof remarketingAddToCart == 'function') {
					remarketingAddToCart(xhr.responseJSON);
				}
			}
		}
		
		if (settings.url == 'index.php?route=checkout/cart/remove') {
			if (typeof xhr.responseJSON['remarketing'] !== 'undefined') {
				if (typeof remarketingRemoveFromCart == 'function') {
					remarketingRemoveFromCart(xhr.responseJSON);
				}
			}
		}
		
		if (settings.url == 'index.php?route=account/wishlist/add') {
			if (typeof xhr.responseJSON['remarketing'] !== 'undefined') {
				if (typeof sendWishList == 'function') {
					sendWishList(xhr.responseJSON);
				}
			}
		}
		
		if (settings.url == 'index.php?route=checkout/simplecheckout&group=0') {
			simple_data = decodePostParams(decodeURI(settings.data));

			if (simple_data.remove !== 'undefined' && simple_data.remove !== '') {
				quantity_key = 'quantity[' + simple_data.remove + ']';
				quantity = simple_data[quantity_key]; 
				
				if (typeof cart_products[simple_data.remove] !== 'undefined') {
					cart_product_id = cart_products[simple_data.remove]['product_id'];
					if (typeof remarketingRemoveFromSimpleCart == 'function') {
						remarketingRemoveFromSimpleCart(cart_product_id, quantity);
					}
				}
			}
		}
	});
});