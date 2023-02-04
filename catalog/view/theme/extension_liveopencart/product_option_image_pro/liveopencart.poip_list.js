//  Product Option Image PRO / Изображения опций PRO
//  Support: support@liveopencart.com / Поддержка: help@liveopencart.ru

var poip_list = (function($){
	let poip_list = {
		
		proxied : false,
		custom_methods : {},
		module_settings : {},
		error_cnt : 0,
		
		wait_server_answer : false,
		image_cache : {},
		url_get_images : 'index.php?route=extension/liveopencart/product_option_image_pro/product_list_images', // some SEO extensions do require route to be lowercase
		//url_get_images : 'index.php?route=extension/liveopencart/product_option_image_pro/getProductListImages',
		product_count : 0,
		timer_get_product_list_images : false,
	
		init : function() {
			
			if ( typeof(poip_settings) != 'undefined' ) {
				poip_list.module_settings = $.extend(true, {}, poip_settings);
			}
			
			poip_list.checkProductListImages();
			
			$(document).ajaxComplete(function( event, xhr, settings ) {
				poip_list.checkProductListImages();
			});
			
		},
		
		changeProductImageByThumb : function(thumb_elem) { // showThumb
			
			let $thumb_elem = $(thumb_elem);
							
			if ( $thumb_elem.attr('data-poip-thumb') && $thumb_elem.attr('data-poip-product-index')) {
							
				let $main_img = $('img[data-poip-product-index="'+$thumb_elem.attr('data-poip-product-index')+'"]:not([data-poip="thumb"])');
				
				if ( !$main_img.attr('data-poip-original-src') ) {
					$main_img.attr('data-poip-original-src', $main_img.attr('src'));
				}
				$main_img.attr('src', $thumb_elem.attr('data-poip-thumb'));
				
				if ( !$main_img.closest('a').attr('data-poip-original-href') ) {
					$main_img.closest('a').attr('data-poip-original-href', $main_img.closest('a').attr('href'));
				}
				$main_img.closest('a').attr('href', $thumb_elem.attr('href'));
						
				if ( $thumb_elem.attr('data-poip-srcset') && $main_img.attr('srcset') ) {
					if ( !$main_img.attr('data-poip-original-srcset') ) {
						$main_img.attr('data-poip-original-srcset', $main_img.attr('srcset'));
					}
					$main_img.attr('srcset', $thumb_elem.attr('data-poip-srcset'));			
				}
			}
		},
		
		eventThumbMouseOver : function(thumb_elem) {
			if ( !poip_list.module_settings.img_category_click ) {
				poip_list.changeProductImageByThumb(thumb_elem);
			}
		},
		
		eventThumbClick : function(thumb_elem) {
			if ( poip_list.module_settings.img_category_click ) {
				poip_list.changeProductImageByThumb(thumb_elem);
				return false;
			}
		},
		
		eventThumbMouseOut : function(thumb_elem) {
			// dummy for some themes
		},
		
		getProductListImages : function(poip_products_ids) {
			
			if ( poip_list.error_cnt > 5 ) {
				return;
			}
			
			clearTimeout( poip_list.timer_get_product_list_images );
			if ( poip_list.wait_server_answer ) {
				poip_list.timer_get_product_list_images = setTimeout(function(){
					poip_list.getProductListImages(poip_products_ids);
				}, 500);
			
			} else {
				poip_list.wait_server_answer = true;
			
				let params = {products_ids: poip_products_ids};
				$.ajax({
					type: 'POST',
					url: poip_list.url_get_images,
					data: params,
					dataType: 'json',
					//dataType: 'text',
					beforeSend: function() {},
					complete: function() {},
					success: function(json) {
						poip_list.wait_server_answer = false;
						if (json && typeof(json.products)!='undefined' && json.products) {
							poip_list.displayProductListImages(json);
						}
					},
					error: function(error) {
						poip_list.error_cnt++;
						console.log(error);
						poip_list.wait_server_answer = false;
					}
				});
			}
		},
		
		checkProductListImages : function() {
			
			let poip_products_ids = [];
			let images_from_cache = {};
			
			let elements_with_updated_images = [];
			
			$('[data-poip-product-id][data-poip-status!="loaded"]').each(function(){
				let poip_product_id = $(this).attr('data-poip-product-id');
				let poip_element_images = $(this).attr('data-poip-images');
				if ( poip_element_images ) { // used stored in the data stored in the element
					poip_list.displayOneProductImages( $(this), JSON.parse(poip_element_images) );
					elements_with_updated_images.push($(this));
				} else { // get
					if ( typeof(poip_list.image_cache[poip_product_id]) != 'undefined' ) {
						images_from_cache[poip_product_id] = poip_list.image_cache[poip_product_id];
					} else if (poip_product_id && $.inArray(poip_product_id,poip_products_ids)==-1 ) {
						poip_products_ids.push(poip_product_id);
					}
				}
			});
			
			if (elements_with_updated_images.length) {
				poip_list.updateElementsWithImages(elements_with_updated_images);
			}
			
			if ( Object.keys(images_from_cache).length ) {
				poip_list.displayProductListImages(images_from_cache);
			}
			
			if (poip_products_ids.length) {
				poip_list.getProductListImages(poip_products_ids);
			}
		},
		
		updateElementsWithImages: function(elements_with_updated_images) {
			// for alternating in theme adaptations
		},
		
		displayProductListImages : function(server_response) {
			
			let	products = server_response.products;
			let elements_with_updated_images = [];
			
			poip_common.each(products, function(poip_data, poip_product_id){
				
				if ( typeof(poip_list.image_cache[poip_product_id]) == 'undefined' ) {
					poip_list.image_cache[poip_product_id] = poip_data;
				}
				
				$('[data-poip-product-id="'+poip_product_id+'"][data-poip-status!="loaded"]').each(function(){
					poip_list.displayOneProductImages($(this), poip_data);
					elements_with_updated_images.push($(this));
				});
			});
			
			if (elements_with_updated_images.length) {
				poip_list.updateElementsWithImages(elements_with_updated_images);
			}
			
		},
		
		displayOneProductImages : function($product_image_element, poip_data) {
			
			$product_image_element.attr('data-poip-status', 'loaded'); // with or without images
			
			if ( !poip_data || $.isEmptyObject(poip_data) ) {
				return;
			}
			
			let $product_anchor = poip_list.getProductAnchorByElement($product_image_element);
			let product_href		= encodeURI( poip_list.getProductHrefByAnchor($product_anchor) );
			
			let current_product_index = poip_list.product_count++; // increments the variable but returns an old value (all in one step)
			$product_image_element.attr('data-poip-product-index', current_product_index );
		
			
			poip_common.each(poip_data, function(poip_dt){

				let html = '';
				poip_common.each(poip_dt, function(option_image){

					let product_option_value_id = option_image.product_option_value_id;
					
					let title = (typeof(option_image.title)!='undefined' && option_image.title) ? option_image.title : '';
					let current_href = product_href+(product_href.indexOf('?')==-1?'?':'&amp;')+'poip_ov='+product_option_value_id;
					
					html+= poip_list.getThumbHtml($product_image_element, option_image, current_product_index, current_href, title);
	
				});
				if ( html ) {
			
					html = poip_list.wrapThumbsHtml(html);
					
					poip_list.displayThumbs($product_anchor, html);
					
				}
			});
	
			
		},
		
		getProductHrefByAnchor : function($elem) {
			return $elem.attr('href');
		},
		
		getProductAnchorByElement : function($elem) {
			return $elem.is('img') ? $elem.closest('a') : $elem;
		},
		
		getThumbHtml : function($product_image_element, option_image, current_product_index, current_href, title) {
			
			let html = '';
			html+='<a onmouseover="poip_list.eventThumbMouseOver(this)" onmouseout="poip_list.eventThumbMouseOut(this)" onclick="return poip_list.eventThumbClick(this);" ';
			html+=' href="'+current_href+'"';
			html+=' title="'+title+'"';
			html+=' data-poip-thumb="'+option_image.thumb+'"';
			if ( option_image.srcset ) {
				html+=' data-poip-srcset="'+option_image.srcset+'"';
			}
			if ( option_image.thumb_second ) {
				html+=' data-poip-thumb-second="'+option_image.thumb_second+'"';
			}
			html+=' data-poip-product-index="'+current_product_index+'"';
			html+=' style="display:inline;"';
			html+='>';
			html+='<img class="img-thumbnail" data-poip="thumb"';
			html+=' src="'+option_image.icon+'" ';
			html+=' alt="'+title+'"';
			html+=' style="width:'+option_image.width+'px; height:'+option_image.height+'px;"';
			html+='>';
			html+='</a>';
			return html;
		},
		
		wrapThumbsHtml : function(html) {
			return '<div data-poip_id="poip_img" class="poip_img" style="  text-align: center; margin-top: 3px;">'+html+'</div>';
		},
		
		displayThumbs : function($product_anchor, html) {
			$product_anchor.closest('.image').after(html);
		},
		
	};
	return poip_list;
})(jQuery);	

poip_common.initObject(poip_list);


