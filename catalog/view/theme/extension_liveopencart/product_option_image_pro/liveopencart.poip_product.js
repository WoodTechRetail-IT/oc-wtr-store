//  Product Option Image PRO / Изображения опций PRO
//  Support: support@liveopencart.com / Поддержка: help@liveopencart.ru

function getPoipProduct(custom_setPoipProductCustomMethods) {
	
	return (function($){
		var poip_product = {
			
			proxied : false,
			custom_methods : {},
			custom_data : {},
			$container : false,
			$container_of_options : false,
			
			product_option_ids : [],
			images : [],
			images_by_povs: [],
			module_settings : {},
			options_settings : {},
			theme_settings : {},
			default_image_title : '',
			
			option_prefix : "option",
			option_prefix_length : 0,
			may_prefix: 'may_advanced_option_',
			main_image_default_src : '', 	// std_src
			main_image_default_href : '', // std_href
			
			timers : {
				update_images_on_option_change: 0,
			},
			works : {
				update_images_on_option_change: false,
				set_visible_images : false,
			},
			poip_ov : false,
			
		
			init : function(params) {
				
				poip_common.each(poip_product, function(setting_val, setting_key){
					if ( typeof(params[setting_key]) != 'undefined' && typeof(setting_val) != 'function' ) {
						poip_product[setting_key] = params[setting_key];
					}
				});
				
				if ( !poip_product.$container ) {
					poip_product.$container = poip_product.getDefaultContainer();
				}
				poip_product.$container.data('poip_product', poip_product);
				
				if ( poip_product.getContainerOfOptions().find('[name^="'+poip_product.option_prefix+'["]').length == 0 && poip_product.getContainerOfOptions().find('[name^="option_oc["]').length ) { // jcart or analog
					poip_product.option_prefix = 'option_oc';
				}
				
				poip_product.option_prefix_length = poip_product.option_prefix.length;
				
				poip_product.main_image_default_src 	= poip_product.getMainImage().attr('src');
				poip_product.main_image_default_href 	= poip_product.getMainImage().closest('a').attr('href');
				
				poip_product.addInitialVideosToImages();
				
				poip_product.getContainerOfOptions().on('change', poip_product.getSelectorOfOptions(), function(e){
					poip_product.eventOptionValueChange(e, $(this));
				});
				
				poip_product.getContainerOfOptions().find(':radio[name^="'+poip_product.may_prefix+'"]').change(function(e){ // for this mod event should be attached direct to elements
					poip_product.eventOptionValueChange(e, $(this));
				});
				
				if ( poip_product.product_option_ids.length && poip_product.getElement( poip_product.getSelectorOfOptions() ).length ) {
					if ( !poip_product.setDefaultOptionsByURL() ) {
						poip_product.updateImagesOnProductOptionChange(poip_product.product_option_ids[0]);
					}
				} else { // no product option images
					poip_product.initActionWithNoOptionImages();
					
				}
			},
			
			addInitialVideosToImages : function(){
				
				poip_common.each(poip_product.images, function(poip_img){
					if (poip_img.video) {
						poip_img.is_video = true;
					}
				});
				
				// videos should be linked with all possible option values
				let po_ids = poip_product.getPOIdsHavingImages();
				let pov_ids = poip_product.getPOVIdsHavingImages();
				
				poip_product.getAdditionalImagesBlock().find('a').each(function(){
					let $a = $(this);
					if ( poip_product.elementIsVideo($a) ) {
						
						let img = $a.find('img').attr('src');
						let href = $a.attr('href');
						let poip_img = poip_product.getImageBySrc(href);
						if ( !poip_img ) {
							poip_img = poip_product.getImageBySrc(img);
						}
						
						let video_href = (($a.attr('data-href') && poip_product.hrefIsVideo($a.attr('data-href'))) ? $a.attr('data-href') : href);
						
						if ( poip_img ) {
							poip_img.is_video = true;
							poip_img.video_href = video_href;
							poip_img.video_img = img;
						} else { // add new
							
							poip_product.images.splice($a.index(), 0, {
								is_video: true,
								image: $a.attr('href'),
								popup: $a.attr('href'),
								main: $a.attr('href'),
								thumb: $a.find('img').attr('src'),
								sort_order: $a.index(),
								product_option_id: po_ids.slice(0),
								product_option_value_id: pov_ids.slice(0),
								video_href: video_href,
								video_img: img,
							});
						}
					}
				});
				
			},
			
			getPOIdsHavingImages: function() {
				let po_ids = [];
				poip_common.each(poip_product.images, function(poip_img){
					if ( poip_img.product_option_id ) {
						poip_common.each(poip_img.product_option_id, function(po_id){
							poip_common.addToArrayIfNotExists(po_id, po_ids);
						});
					}
				});
				return po_ids;
			},
			getPOVIdsHavingImages: function() {
				let pov_ids = [];
				poip_common.each(poip_product.images, function(poip_img){
					if ( poip_img.product_option_value_id ) {
						poip_common.each(poip_img.product_option_value_id, function(pov_id){
							poip_common.addToArrayIfNotExists(pov_id, pov_ids);
						});
					}
				});
				return pov_ids;
			},
			
			initActionWithNoOptionImages : function(svi_called){ // no product option images
				
				if ( !svi_called ) {
					poip_product.setVisibleImages( poip_product.getAdditionalImagesToDisplay() );
				}
				if ( poip_product.works.set_visible_images ) {
					setTimeout(function(){
						poip_product.initActionWithNoOptionImages(true);
					}, 10);
					return;
				}
				poip_product.updateImageAdditionalMouseOver();
				poip_product.updatePopupImages();
			},
			
			getDefaultContainer : function(){
				return $('body');
			},
			
			getElement : function(selector){
				if ( selector.indexOf(' ') == -1 && poip_product.$container.is(selector) ) {
					return poip_product.$container;
				} else {
					return poip_product.$container.find(selector);
				}
			},
			
			getContainerOfOptions : function(){
				if ( poip_product.$container_of_options && poip_product.$container_of_options.length ) {
					return poip_product.$container_of_options;
				} else if ( poip_product.$container && poip_product.$container.length ) {
					return poip_product.$container;
				} else {
					return poip_product.getElement('#product');
				}
				
			},
			
			getSelectorOfOptions : function(select_addition, radio_addition, checkbox_addition){
				
				select_addition 	= select_addition || '';
				radio_addition 		= radio_addition || '';
				checkbox_addition = checkbox_addition || '';
				
				var selector = '';
				selector+= 'select[name^="'+poip_product.option_prefix+'["]'+select_addition;
				selector+= ', input:radio[name^="'+poip_product.option_prefix+'["]'+radio_addition+', input:radio[name^="'+poip_product.may_prefix+'"]'+radio_addition;
				selector+= ', input:checkbox[name^="'+poip_product.option_prefix+'["]'+checkbox_addition;
				return selector;
			},
			
			getSelectorOfOptionsChecked : function(){
				return poip_product.getSelectorOfOptions('', ':checked', ':checked');
			},
			
			getProductOptionIdByName : function(name) {
				if ( name.indexOf(poip_product.may_prefix) === 0 ) { // ext. changing options
					return name.substr(poip_product.may_prefix.length);
				} else { 
					return name.substr(poip_product.option_prefix_length+1, name.indexOf(']')-(poip_product.option_prefix_length+1) );
				}
			},
			
			getProductOptionIdByProductOptionValueId: function(pov_id) {
				let selected_po_id = '';
				poip_common.each(poip_product.options_settings, function(pos, po_id){
					if ( $.inArray(pov_id, pos.povs) != -1 ) {
						selected_po_id = po_id;
						return false;
					}
				});
				return selected_po_id;
			},
			
			eventOptionValueChange : function( event, $option_element ) { // changeVisibleImages
				
				var product_option_id = poip_product.getProductOptionIdByName( $option_element.attr('name') );
				
				if ( $.inArray(product_option_id, poip_product.product_option_ids) != -1 ) {
					poip_product.updateImagesOnProductOptionChange(product_option_id, $option_element);
				}
				
			},
			
			updateImagesOnProductOptionChange : function(product_option_id, $option_element, param_visible_images) { // $option_element - optional
				
				clearTimeout( poip_product.timers.update_images_on_option_change );
				
				if ( (!param_visible_images && poip_product.works.update_images_on_option_change) || poip_product.works.set_visible_images ) {
					
					if ( param_visible_images ) {
						clearTimeout( poip_product.timers.update_images_on_option_change_second_stage );
						poip_product.timers.update_images_on_option_change_second_stage = setTimeout(function(){
							poip_product.updateImagesOnProductOptionChange(product_option_id, $option_element, param_visible_images);
						},10);
					} else {
						poip_product.timers.update_images_on_option_change = setTimeout(function(){
							poip_product.updateImagesOnProductOptionChange(product_option_id, $option_element, param_visible_images);
						},10);
					}	
					return;
				}
				
				poip_product.works.update_images_on_option_change = true;
				
				if ( $.inArray(product_option_id, poip_product.product_option_ids) != -1 ) {
					
					if ( !param_visible_images ) {
						
						let visible_additional_images = poip_product.updateAdditionalImages(product_option_id);
						
						// specific call to wait for possible delays in updateAdditionalImages (control by poip_product.works.set_visible_images)
						poip_product.updateImagesOnProductOptionChange(product_option_id, $option_element, visible_additional_images); 
						return;
					
					} else {
						
						let images_to_display = poip_product.getAdditionalImagesToDisplay();
						//let visible_additional_images = poip_product.updateAdditionalImages(product_option_id);
						
						// if list of visible images changed - restart updating (it can be changed with no onchange event, for example Journal3 set first option values selected on page loading)
						if ( images_to_display.toString() != param_visible_images.toString() ) { 
							poip_product.works.update_images_on_option_change = false;
							poip_product.updateImagesOnProductOptionChange(product_option_id, $option_element);
							return;
						}
						
						poip_product.updateImageAdditionalMouseOver();
						
						poip_product.updatePopupImages();
						poip_product.updateMainImage(product_option_id, param_visible_images, $option_element);
						poip_product.updateImagesBelowOption(product_option_id, $option_element);
						poip_product.updateDependentThumbnails(param_visible_images);
			
					}
				}
				
				poip_product.works.update_images_on_option_change = false;
			},
			
			getSelectedProductOptionValueIds : function(product_option_id, p_product_options) { // getSelectedOptionValues
		
				var product_options = p_product_options || poip_product.getSelectedProductOptions();
				var values = [];
				
				if ( product_option_id ) {
					if ( typeof(product_options[product_option_id]) != 'undefined' ) {
						values = product_options[product_option_id];
					}
				} else {
					
					poip_common.each(product_options, function(pov_ids){
						values = poip_common.getConcatenationOfArraysUnique(values, pov_ids);
					});
				}
				return values;
			},
			
			getSelectedProductOptions : function(){
				var product_options = {};
				poip_product.getElement( poip_product.getSelectorOfOptionsChecked() ).each(function () {
					var product_option_id = poip_product.getProductOptionIdByName($(this).attr('name'));
					
					if ( $(this).val() ) {
						if ( $.inArray(product_option_id, poip_product.product_option_ids) != -1 ) {
							if ( typeof(product_options[product_option_id]) == 'undefined' ) {
								product_options[product_option_id] = [];
							}
							product_options[product_option_id].push($(this).val());
						}
					}
				});
				return product_options;
			},
			
			getSelectedProductOptionIds : function(){
				return Object.keys( poip_product.getSelectedProductOptions() );
			},
			
			getMainImagePOIP : function(){ 
				let poip_img_main = false;
				poip_common.each(poip_product.images, function(poip_img){
					if ( poip_img.product_image_id && poip_img.product_image_id == -1 ) {
						poip_img_main = poip_img;
						return false;
					}
				});
				return poip_img_main;
			},
		
			getBasicVisibleAdditionalImages : function() { // additional images (incliding the main image, if enabled)
				
				var images = [];
				
				// the main product image should be first
				// if it is linked to an option but this option is not selected
				// or it is linked to an option which is not set to be displayed in the list of additional images
				let poip_img_main = poip_product.getMainImagePOIP();
				if ( poip_img_main && poip_img_main.product_option_id && poip_img_main.product_option_id.length ) {
					let po_ids_selected = poip_product.getSelectedProductOptionIds();
					let po_ids_selected_main = poip_common.getIntersectionOfArrays(poip_img_main.product_option_id, po_ids_selected);
					if ( !po_ids_selected_main.length ) {
						poip_common.addToArrayIfNotExists(poip_img_main.popup, images);
					} else {
						let img_use = false;
						poip_common.each(po_ids_selected_main, function(po_id){
							img_use = img_use && poip_product.options_settings[po_id].img_use;
						});
						if ( !img_use ) {
							poip_common.addToArrayIfNotExists(poip_img_main.popup, images);
						}
					}
				}
				
				poip_common.each(poip_product.images, function(poip_img){
					if ( typeof(poip_img.product_image_id) != 'undefined' || poip_img.is_video ) { // main or additional image or video
						poip_common.addToArrayIfNotExists(poip_img.popup, images);
					}
				});
				return images;
				
			},
		
			getBasicImagesForMainImage : function() { // ???
				
				var images_for_main_image = poip_product.getBasicVisibleAdditionalImages();
				if ( poip_product.std_href ) {
					if ( $.inArray(poip_product.std_href, images_for_main_image) == -1 ) {
						images_for_main_image = [poip_product.std_href].concat(images_for_main_image);
					}
				}
				return images_for_main_image;
			},
			
			getImagesForProductOptionValueIds : function(pov_ids, without_by_any_value, only_strict_linked_with_all_pov_ids) {
				
				var images = [];
				poip_common.each(poip_product.images, function(poip_img){
					if ( poip_img.product_option_value_id ) {
						
						if (without_by_any_value && poip_img.product_option_value_id_any && poip_common.existsIntersectionOfArrays(poip_img.product_option_value_id_any, pov_ids)) {
							return;
						}
						
						if (only_strict_linked_with_all_pov_ids ) {
							
							if ( poip_img.po_ids_pov_ids ) {
								let pov_ids_intersection = poip_common.getIntersectionOfArrays(poip_img.product_option_value_id, pov_ids);
								if (pov_ids_intersection.length == pov_ids.length) { // looks similar - check deeplier
									
									poip_common.each(poip_img.po_ids_pov_ids, function(po_pov_ids){
										if (po_pov_ids.length == pov_ids.length ) {
											let po_pov_ids_intersection = poip_common.getIntersectionOfArrays(po_pov_ids, pov_ids);
											if (po_pov_ids_intersection.length == pov_ids.length) {
												poip_common.addToArrayIfNotExists(poip_img.popup, images);
											}
										}
									});
									
								}
							}
							return;
						}
						
						if ( poip_common.existsIntersectionOfArrays(poip_img.product_option_value_id, pov_ids) ) {
							poip_common.addToArrayIfNotExists(poip_img.popup, images);
						}
					}
				});
				return images;
			},
			
			getCommonImagesForProductOptionValueIds : function(pov_ids) {
				
				let images = false;
				
				let pov_ids_by_po = {};
				poip_common.each(pov_ids, function(pov_id){
					let po_id = poip_product.getProductOptionIdByProductOptionValueId(pov_id);
					if (!pov_ids_by_po[po_id]) {
						pov_ids_by_po[po_id] = [];
					}
					pov_ids_by_po[po_id].push(pov_id);
				});
				
				poip_common.each(pov_ids_by_po, function(po_povs_ids, po_id){
					
					let pov_poip_images = poip_product.images.filter(function(poip_img){
						
						return poip_img.po_ids_pov_ids && poip_img.po_ids_pov_ids[po_id] && poip_common.areArraysContentsEqual(poip_img.po_ids_pov_ids[po_id], po_povs_ids);
						
						//return poip_img.product_option_value_id && $.inArray(pov_id, poip_img.product_option_value_id) != -1;
					});
					let pov_images = pov_poip_images.map(function(poip_img){
						return poip_img.popup;
					});
					images = images === false ? pov_images : poip_common.getIntersectionOfArrays(images, pov_images);
				});
				
				//let images = false;
				//poip_common.each(pov_ids, function(pov_id){
				//	
				//	let pov_poip_images = poip_product.images.filter(function(poip_img){
				//		return poip_img.product_option_value_id && $.inArray(pov_id, poip_img.product_option_value_id) != -1;
				//	});
				//	let pov_images = pov_poip_images.map(function(poip_img){
				//		return poip_img.popup;
				//	});
				//	
				//	images = images === false ? pov_images : poip_common.getIntersectionOfArrays(images, pov_images);
				//});
				
				return images || [];
			},
			
			getImagesForProductOption : function(product_option_id) {
				var images = [];
				poip_common.each(poip_product.images, function(poip_img){
					if ( poip_img.product_option_id && $.inArray(product_option_id, poip_img.product_option_id) != -1 ) {
						poip_common.addToArrayIfNotExists(poip_img.popup, images);
					}
				});
				return images;
			},
			
			getImagesForNotSelectedProductOption : function(product_option_id) {
				
				var images = [];
				poip_common.each(poip_product.images, function(poip_img){
					if ( poip_img.product_option_value_id ) {
						// -product_option_id is used for images checked to be shown when option is not selected
						if ( $.inArray(-product_option_id, poip_img.product_option_value_id) != -1 ) {
							poip_common.addToArrayIfNotExists(poip_img.popup, images);
						}
					}
				});
				return images;
			},
			
			getImagesNotLinkedToProductOption : function(product_option_id) {
				
				var images = [];
				poip_common.each(poip_product.images, function(poip_img){
					if ( poip_img.product_option_value_id ) {
						if ( !poip_img.product_option_id || $.inArray(product_option_id, poip_img.product_option_id) == -1 ) {
							poip_common.addToArrayIfNotExists(poip_img.popup, images);
						}
					}
				});
				return images;
			},
			
			updatePopupImages : function() {
				
				if ( poip_product.getElement('li.image-additional').length ) { // default
					
					if ( typeof($.magnificPopup) != 'undefined' ) {
						
						let mfp_params = {
							type:'image',
							delegate: '.image-additional a:visible',
							gallery: {
								enabled:true
							}
						};
						if ( poip_product.getElement('.thumbnails').data('magnificPopup') && poip_product.getElement('.thumbnails').data('magnificPopup').callbacks ) {
							mfp_params.callbacks = poip_product.getElement('.thumbnails').data('magnificPopup').callbacks;
						}
						
						poip_product.getElement('.thumbnails').magnificPopup(mfp_params);
					}
					
					poip_product.getMainImage().off('click');
					poip_product.getMainImage().on('click', function(event) {
						poip_product.eventMainImgClick(event, this); }
					);
					if ( poip_product.getMainImage().closest('a').length ) {
						poip_product.getMainImage().closest('a').off('click').on('click', function(event){
							event.preventDefault();
							poip_product.getMainImage().click();
						});
					}
				}
			},
			
			eventMainImgClick : function(event, element) { // ???
				event.preventDefault();
				event.stopPropagation();
				
				let main_href = $(element).parent().attr('href');
				//var $additional_image = poip_product.getAdditionalImagesBlock().find('a
				let $additional_images = poip_product.getAdditionalImagesBlock().find('a');
				let $additional_image = $additional_images.filter('[href="'+main_href+'"]');
				
				
				if ( $additional_image.length ) {
					if ( poip_product.module_settings.img_click && $additional_image.closest('.thumbnails').length ) {
						$additional_image.closest('.thumbnails').magnificPopup('open', $additional_images.index($additional_image));
					} else {
						$additional_image.click();
					}
				}
			},
			
			sortImagesBySelectedOptions : function(p_images) {
				
				var images = [];
				
				if ( poip_product.module_settings.options_images_edit == 1 ) { // use basic sort order (set on the Image tab)
					
					poip_common.each(poip_product.images, function(poip_img){
						if ( $.inArray(poip_img.popup, p_images) != -1 ) {
							poip_common.addToArrayIfNotExists(poip_img.popup, images);
						}
					});
					
				} else { // use option sort order (from the Option tab)
				
					// standard/basic images first
					images = poip_common.getIntersectionOfArrays( poip_product.getBasicVisibleAdditionalImages(), p_images);
				
					let po_selected = poip_product.getSelectedProductOptions();
					let pov_ids_to_po_ids = {};
					poip_common.each(po_selected, function(po_pov_ids_selected, po_id){
						pov_ids_to_po_ids[po_pov_ids_selected] = po_id;
					});
					//let pov_ids = poip_product.getSelectedProductOptionValueIds();
					
					poip_common.each(pov_ids_to_po_ids, function(po_id, pov_id){
					//poip_common.each(pov_ids, function(pov_id){
						let product_option_settings = poip_product.options_settings[po_id];
						
						if (product_option_settings.img_limit != 0) { // place selected option image to the beginning only if image filter is enabled
					
							if (poip_product.images_by_povs && poip_product.images_by_povs[pov_id] && poip_product.images_by_povs[pov_id].length) {
								poip_common.each(poip_product.images_by_povs[pov_id], function(poip_img_by_pov){
								
									var poip_img = poip_product.getImageBySrc(poip_img_by_pov.image,'image');
									if ( poip_img && $.inArray(poip_img.popup, p_images) != -1) {
										poip_common.addToArrayIfNotExists(poip_img.popup, images);
									}
								});
							}
						}
					});
					
				}
				
				poip_common.each(p_images, function(p_image){
				
					if ( $.inArray(p_image, images) == -1 ) {
						images.push(p_image);
					}
				});
				
				if ( poip_product.module_settings.videos_to_end ) {
					let poip_imgs = poip_product.getImagesBySrc(images);
					images = [];
					poip_common.each(poip_imgs, function(poip_img){
						if (!poip_img.is_video) {
							images.push(poip_img.popup);
						}
					});
					poip_common.each(poip_imgs, function(poip_img){
						if (poip_img.is_video) {
							images.push(poip_img.popup);
						}
					});
				}
			
				return images;
			},
			
			getCurrentlyVisibleImages : function(){
				images = [];
				poip_product.getAdditionalImagesBlock().find('a:visible').each(function(){
					var href = $(this).attr('href');
					if ( href ) {
						images.push(href);
					}
				});
				return images;
			},
			
			// fn_svi
			setVisibleImages : function(images) {
				
				poip_product.works.set_visible_images = true;
				
				var currently_visible_images = poip_product.getCurrentlyVisibleImages();
				if ( currently_visible_images.toString() != images.toString() ) {
					var $elements_to_remove = poip_product.getAdditionalImagesBlock();
				
					var html = '';
					poip_common.each(images, function(image){
						
						var poip_img = poip_product.getImageBySrc(image, 'popup');
						var title = poip_img.title || poip_product.default_image_title;
						html+= '<li class="image-additional" >';
						
						html+= '<a class="thumbnail" href="'+image+'" title="'+title+'"> <img src="'+poip_img.thumb+'" title="'+title+'" alt="'+title+'"></a>';
						html+= '</li>';
						
					});
					
					$elements_to_remove.remove();
					poip_product.getElement('.thumbnails').append(html);
					
				}
				
				poip_product.works.set_visible_images = false;
			},
			
			// params { carousel_selector, image_selector, parent_selector, callbefore, callafter, callback, is_ready_fn }
			setVisibleImagesOwl: function(images, counter, params) {
				
				poip_product.works.set_visible_images = true;
		
				clearTimeout(poip_product.timers.set_visible_images);
				if (!counter) counter = 1;
				if (counter == 1000) {
					poip_product.works.set_visible_images = false;
					return;
				}
				
				let carousel_selector = params.carousel_selector;
				let $carousel_elem = poip_product.getElement(carousel_selector);
				
				if ( $carousel_elem.length ) {
					
					let current_carousel = $carousel_elem.data('owlCarousel') || $carousel_elem.data('owl.carousel'); 
					let is_ready = current_carousel && $carousel_elem.find('.owl-item').length && (!params.is_ready_fn || params.is_ready_fn());
					if ( !poip_product.theme_adaptation.updateShouldBeProcessed($carousel_elem.find('a'), 'href', images, images, counter, is_ready ) ) {
						return; // will the image updating by continued or not - set in the function
					}
					
					let image_selector = params.image_selector || 'img';
					let parent_selector = params.parent_selector || '';
					
					poip_product.theme_adaptation.storeImageElementsToImageCollection( {
						$images: $carousel_elem.find(image_selector),
						parent_selector: parent_selector,
					} );
					
					if ( params.callbefore ) {
						params.callbefore();
					}
					
					let elems_cnt = current_carousel.itemsAmount;
					for (let i = 1; i<=elems_cnt; i++ ) {
						current_carousel.removeItem();
					}
					
					if ( $carousel_elem.data('owl.carousel') ) { // specific owl version
						
						let html = '';
						poip_common.each(images, function(image){
							let poip_img = poip_product.getImageBySrc(image);
							if ( poip_img ) {
								html+= poip_img.html;
							}
						});
						
						// new owlCarousel version
						//current_carousel.reinit();
						let owl_settings = current_carousel.settings;
						
						$carousel_elem.html(html);
						$carousel_elem.replaceWith( poip_common.getOuterHTML($carousel_elem) );
						$carousel_elem = poip_product.getElement(carousel_selector);
						$carousel_elem.owlCarousel(owl_settings);
						
					} else {
					
						poip_common.each(images, function(image){
							
							let poip_img = poip_product.getImageBySrc(image, 'popup');
							//let title = (poip_img && poip_img.title) ? poip_img.title : poip_product.default_image_title;
							
							current_carousel.addItem( poip_img.html );
							//current_carousel.addItem( '<div class="item">'+poip_img.html+'</div>' );
						});
					}	
					//current_carousel.reinit();
					
					if ( params.callafter ) {
						params.callafter();
					}
				}
		
				poip_product.works.set_visible_images = false;
				
				if ( params.callback ) {
					params.callback();
				}
			},
			
			setVisibleImagesSlickDouble: function(images, $slider_main, $slider_additional, call_settings, counter, params) {
				
				poip_product.works.set_visible_images = true;
		
				clearTimeout(poip_product.timers.set_visible_images);
				if (!counter) counter = 1;
				if (counter == 1000) {
					poip_product.works.set_visible_images = false;
					return;
				}
				
				params = params || {};
				
				if ( !poip_product.setVisibleImagesOnlyUpdateSlickDouble(images, $slider_main, $slider_additional, counter, call_settings, params) ) {
					return;
				}
			
			
				if ( params.callafter ) {
					params.callafter();
				}
			
		
				poip_product.works.set_visible_images = false;
				
				if ( params.callback ) {
					params.callback();
				}
			},
			// call_settings {
			//	selector_to_check_img_add
			//	att_to_check_img_add
			//	poip_img_src_to_check_img_add
			//  selector_to_store_main
			//  sub_img_selector_to_store_main
			//  getAttr_main
			//  selector_to_store_add
			//	sub_img_selector_to_store_add
			//	sub_img_attr_main
			//	sub_img_attr_add
			//  update_main_slick
			//  getAttr_add
			//}
			setVisibleImagesOnlyUpdateSlickDouble: function(images, $slider_main, $slider_additional, counter, call_settings, params) {
				
				call_settings = $.extend({
					att_to_check_img_add: 'data-href',
					selector_to_check_img_add: 'a',
					selector_to_store_main: '.slick-slide:not(.slick-cloned)',
					sub_img_selector_to_store_main: 'img',
					getAttr_main: false,
					selector_to_store_add: '.slick-slide:not(.slick-cloned)',
					sub_img_selector_to_store_add: 'img',
					sub_img_attr_add: '',
					getAttr_add: false,
				}, call_settings);
				
				let images_to_check = images;
				if (call_settings.poip_img_src_to_check_img_add) {
					images_to_check = poip_product.getSrcImagesBySrc(call_settings.poip_img_src_to_check_img_add, images);
				}
				
				
				let is_ready = $slider_main.hasClass('slick-initialized') && $slider_main.find('.slick-slide') && $slider_additional.hasClass('slick-initialized') && $slider_additional.find('.slick-slide');
				if ( !poip_product.theme_adaptation.updateShouldBeProcessed( $slider_additional.find(call_settings.selector_to_check_img_add), call_settings.att_to_check_img_add, images_to_check, images, counter, is_ready, false, params ) ) {
					return false; // will the image updating by continued or not - set in the function
				}
				
				let $slider_main_slides = $slider_main.find( call_settings.selector_to_store_main + call_settings.sub_img_selector_to_store_main );
				let update_main_slick = (typeof(call_settings.update_main_slick) != 'undefined') ? call_settings.update_main_slick : !poip_product.theme_adaptation.checkDisplayedImagesAreActual($slider_main_slides, 'src', images, 'main');
				
				poip_product.theme_adaptation.storeImageElementsToImageCollection( {
					$images: $slider_main_slides,
					parent_selector: '.slick-slide',
					html_version: 'html_main',
					attr_name: call_settings.sub_img_attr_main,
					getAttr: call_settings.getAttr_main,
				} );
				poip_product.theme_adaptation.storeImageElementsToImageCollection( {
					$images: $slider_additional.find( call_settings.selector_to_store_add + call_settings.sub_img_selector_to_store_add ),
					parent_selector: '.slick-slide',
					html_version: 'html_additional',
					attr_name: call_settings.sub_img_attr_add,
					getAttr: call_settings.getAttr_add,
				} );
				
				//$slider_main.slick('slickSetOption', 'asNavFor', false, false);
				//$slider_additional.slick('slickSetOption', 'asNavFor', false, false);
				
				if (update_main_slick) {
					$slider_main.slick('slickRemove', 0, 0, true);
				}
				$slider_additional.slick('slickRemove', 0, 0, true);
				
				$slider_main.slick('slickSetOption', 'waitForAnimate', false, false);
				$slider_additional.slick('slickSetOption', 'waitForAnimate', false, false);
				
				$slider_main.slick('slickGoTo', 0, true); // otherwise the slider can switch to another image on its content update
				$slider_additional.slick('slickGoTo', 0, true);
				
				let poip_imgs = poip_product.getImagesBySrc(images, 'popup');
				
				let html_main = '';
				let html_add = '';
				poip_common.each(poip_imgs, function(poip_img){
					
					if ( poip_img.html_main && poip_img.html_additional ) {
						html_main+= poip_img.html_main;
						html_add+= poip_img.html_additional;
					}
				});

				if (update_main_slick) {
					$slider_main.slick('slickAdd', html_main );
				}
				$slider_additional.slick('slickAdd', html_add );
				
				
				$slider_main.off('setPosition.poip');
				$slider_main.on('setPosition.poip', function(){ // sometimes we have to wait the carousel for being ready
					$slider_main.off('setPosition.poip');
					$slider_main.slick('slickGoTo', 0, true);
					//$slider_additional.slick('slickGoTo', 0, true);
				});
				
				// added here for remarket (othewise displays last img on mobile)
				$slider_main.slick('slickGoTo', 0, true); // otherwise the slider can switch to another image on its content update
				//$slider_additional.slick('slickGoTo', 0, true);
				
				//$slider_main.slick('unslick').slick('reinit');
				//$slider_additional.slick('unslick').slick('reinit');
				//
				$slider_main.slick('refresh', true); // uncommented for remarket (othewise displays last img on mobile)
				//$slider_additional.slick('refresh', true);
				
				//$slider_main.slick('slickSetOption', 'waitForAnimate', false);
				//$slider_additional.slick('slickSetOption', 'waitForAnimate', false, true);
				
				return true;
			},
			
			setVisibleImagesOnlyUpdateSlick: function(images, $slider_additional) {
				
				poip_product.theme_adaptation.storeImageElementsToImageCollection( {
					$images: $slider_additional.find('.slick-slide:not(.slick-cloned) img'),
					parent_selector: '.slick-slide',
					html_version: 'html_additional',
				} );
				
				$slider_additional.slick('slickRemove', 0, 0, true);
				
				let poip_imgs = poip_product.getImagesBySrc(images, 'popup');
				
				let html_add = '';
				poip_common.each(poip_imgs, function(poip_img){
					
					if ( poip_img.html_additional ) {
						html_add+= poip_img.html_additional;
					}
				});
				
				$slider_additional.slick('slickAdd', html_add );
				
				//let slide_index = $slider_main.find('.slick-slide:not(.slick-cloned) img[src="'+poip_product.getMainImage().attr('src')+'"]').closest('[data-slick-index]').attr('data-slick-index');
				
				//$slider_main.slick('slickGoTo', slide_index, true );
				//$slider_additional.slick('slickGoTo', slide_index, true );
			},
			
			getDocumentReadyState : function() {
				return document.readyState == 'complete';
			},
			
			setMainImage : function(image) { // // updateZoomImage should be here too
				
				let poip_img = poip_product.getImageBySrc(image, 'popup', 'main');
				
				poip_product.getMainImage().attr('src', poip_img.main);
				poip_product.getMainImage().closest('a').attr('href', poip_img.popup);
		
			},
			
			displayImagesBelowOption : function( product_option_id, $p_option_element, images) {
				
				let $option_element = $p_option_element || poip_product.getElement(poip_product.getSelectorOfOptions()).filter('[name*="['+product_option_id+']"]').first();
				let below_container_id = '#option-images-'+product_option_id;
				
				poip_product.getElement(below_container_id).remove();
				if ( images.length ) {
					
					let html = '';
					poip_common.each(images, function(image){
						var poip_img = poip_product.getImageBySrc(image, 'popup');
						html+= '<a href="'+poip_img.popup+'" class="image-additional" style="margin: 5px;"><img src="'+poip_img.thumb+'" ></a>';
					});
				
					poip_product.updateContainerOfImagesBelowOptions(product_option_id, $option_element, html);
					
					if ( poip_product.getElement(below_container_id+' a').length ) {
						if ( $.magnificPopup ) {
							poip_product.getElement(below_container_id).magnificPopup({
								type:'image',
								delegate: 'a',
								gallery: {
									enabled:true
								}
							});
						}
					}
				}
			},
			
			updateContainerOfImagesBelowOptions : function(product_option_id, $option_element, html) {
				$option_element.parent().append('<div id="option-images-'+product_option_id+'" style="margin-top: 10px;display: block; width: 100%;">'+html+'</div>');
			},
			
			updateImagesBelowOption : function(product_option_id, $option_element) {

				if ( poip_product.options_settings[product_option_id].img_option && poip_product.options_settings[product_option_id].img_option == 1 ) {
					let images = poip_product.getImagesForProductOptionValueIds( poip_product.getSelectedProductOptionValueIds(product_option_id) );
					poip_product.displayImagesBelowOption(product_option_id, $option_element, images);
				}
				
				// update for all options where images below should be filtered by all selected options
				poip_common.each(poip_product.options_settings, function(po_settings, po_id){
					if ( po_settings.img_option && po_settings.img_option == 2 ) {
						let images = poip_product.getCommonImagesForProductOptionValueIds( poip_product.getSelectedProductOptionValueIds() );
						poip_product.displayImagesBelowOption(po_id, '', images);
					}
				});
			},
			
			getImagesFilteredByComb : function(selected_product_options) {
				
				let images = [];
				poip_common.each(poip_product.images, function(poip_img){
					
					if ( poip_img.product_option_id && poip_img.product_option_id.length ) {
						
						let count_option_fit = 0;
						poip_common.each(poip_img.product_option_id, function(po_id){
							let current_selected_povs = selected_product_options[po_id] ? selected_product_options[po_id] : [-po_id];
							if ( poip_common.getIntersectionOfArrays(current_selected_povs, poip_img.product_option_value_id).length ) {
								count_option_fit++;
							}
						});
						
						if ( count_option_fit == poip_img.product_option_id.length ) {
							images.push(poip_img.popup);
						}
					}
					
					
				});
				
				return images;
				
			},
			
			getProductOptionImagesToDisplay : function(product_option_id, p_selected_product_options) {
				
				let selected_product_options = p_selected_product_options ? $.extend({}, p_selected_product_options) : poip_product.getSelectedProductOptions();
				let images = [];
				
				
				if ( typeof(selected_product_options[product_option_id]) == 'undefined' ) { // no selected values
					selected_product_options[product_option_id] = [-product_option_id]; // -product_option_id is used instead of normal product_option_value_id in case of "no value"
				}
				
				if ( poip_product.module_settings.img_filter_by_comb ) { // always filter images by complete set of selected options
					images = poip_product.getImagesFilteredByComb( selected_product_options );
				} else { // classic
					images = poip_product.getImagesForProductOptionValueIds( selected_product_options[product_option_id] ); 
				}
				
				return images;
			},
			
			getAdditionalImagesToDisplay : function(){
				
				var basic_visible_images_initial = poip_product.getBasicVisibleAdditionalImages();
				var basic_visible_images = basic_visible_images_initial.slice();
				
				var selected_product_options = poip_product.getSelectedProductOptions();
				
				var images_to_filter_all_images			= false;
				var images_to_filter_option_images	= false;
				var option_images_add_to_additional = [];
				
				poip_common.each(poip_product.product_option_ids, function(product_option_id){
					var product_option_settings = poip_product.options_settings[product_option_id];
					
					var current_option_images_add_to_additional = [];
					if ( product_option_settings.img_use == 1 ) {
						current_option_images_add_to_additional = poip_product.getImagesForProductOption(product_option_id);
						
					} else if ( product_option_settings.img_use == 2 ) {
						current_option_images_add_to_additional = poip_product.getProductOptionImagesToDisplay(product_option_id, selected_product_options);
						
					} else if ( product_option_settings.img_use == 3 ) {
						
						if ( $.isEmptyObject(selected_product_options) ) { // exclide option images from the additional product images
							let product_option_images = poip_product.getImagesForProductOption(product_option_id);
							
							basic_visible_images = poip_common.excludeItemsFromArray(basic_visible_images, product_option_images);
							
							// show no additional images if nothing to show ?
							basic_visible_images_initial = poip_common.excludeItemsFromArray(basic_visible_images_initial, product_option_images);
							
							current_option_images_add_to_additional = [];
							
						} else {
							current_option_images_add_to_additional = poip_product.getProductOptionImagesToDisplay(product_option_id, selected_product_options);
						}
					}
					
					option_images_add_to_additional = poip_common.getConcatenationOfArraysUnique(option_images_add_to_additional, current_option_images_add_to_additional);
					
					current_images_to_filter_images = poip_product.getProductOptionImagesToDisplay(product_option_id, selected_product_options);
					
					if ( current_images_to_filter_images.length ) {
						
						if ( product_option_settings.img_limit == 1 ) { // filter all additionail images
							
							if ( images_to_filter_all_images === false ) {
								images_to_filter_all_images = current_images_to_filter_images;
							} else {
								images_to_filter_all_images = poip_common.getIntersectionOfArrays(images_to_filter_all_images, current_images_to_filter_images);
							}
							
						} else if ( product_option_settings.img_limit == 2 || product_option_settings.img_limit == 3 ) { // filter only option images
							if ( images_to_filter_option_images === false ) {
								images_to_filter_option_images = current_images_to_filter_images;
							} else {
								images_to_filter_option_images = poip_common.getIntersectionOfArrays(images_to_filter_option_images, current_images_to_filter_images);
							}
						} 
					}
					
					if ( product_option_settings.img_limit == 3 ) {
						let product_option_images = poip_product.getImagesForProductOption(product_option_id);
						basic_visible_images = poip_common.excludeItemsFromArray(basic_visible_images, product_option_images);
					}
					
				});
				
				if ( images_to_filter_all_images ) {
					basic_visible_images 			= poip_common.getIntersectionOfArrays(basic_visible_images, images_to_filter_all_images);
					option_images_add_to_additional = poip_common.getIntersectionOfArrays(option_images_add_to_additional, images_to_filter_all_images);
				}
				if ( images_to_filter_option_images ) {
					option_images_add_to_additional = poip_common.getIntersectionOfArrays(option_images_add_to_additional, images_to_filter_option_images);
				}
				
				var all_visible_images = poip_common.getConcatenationOfArraysUnique(basic_visible_images, option_images_add_to_additional);
				
				if ( !all_visible_images.length ) {
					all_visible_images = basic_visible_images_initial.slice();
				}
				
				return poip_product.sortImagesBySelectedOptions(all_visible_images);
			},
			
			updateAdditionalImages : function(product_option_id) { // changeAvailableImages
				
				if ($.inArray(product_option_id, poip_product.product_option_ids)==-1) {
					return;
				}
				
				var additional_images_to_display = poip_product.getAdditionalImagesToDisplay();
				
				poip_product.setVisibleImages(additional_images_to_display, 0, {product_option_id: product_option_id});
				
				return additional_images_to_display;
			},
			
			getImageToDisplayAsMain: function(product_option_id, p_visible_additional_images, $option_element, no_default) {
				
				if ( $.inArray(product_option_id, poip_product.product_option_ids) != -1 ) {
					
					let product_option_settings = poip_product.options_settings[product_option_id];
					
					if ( product_option_settings.img_change ) {
						
						let visible_additional_images = p_visible_additional_images || poip_product.getAdditionalImagesToDisplay();
						
						let all_options_selected_values = poip_product.getSelectedProductOptionValueIds();
						
						let images_of_all_selected_povs = all_options_selected_values.length ? poip_product.getCommonImagesForProductOptionValueIds(all_options_selected_values) : [];
						
						let images_of_all_selected_povs_strict = [];
						if (poip_product.module_settings.img_filter_by_comb) {
							images_of_all_selected_povs_strict = poip_product.getImagesFilteredByComb(poip_product.getSelectedProductOptions());
						}
						
						let images_of_current_pov_checkbox_all = [];
						
						let current_option_selected_values 	= poip_product.getSelectedProductOptionValueIds(product_option_id);
						if ($option_element && $option_element.is(':checkbox')) {
						//if ($option_element && $option_element.is(':checkbox:checked')) {
							images_of_current_pov_checkbox_all = poip_product.getImagesForProductOptionValueIds(current_option_selected_values, false, true);
							if ($option_element.is(':checkbox:checked')) {
								current_option_selected_values = [$option_element.val()]; // get only for currently selected checkbox
							}
						}
						let images_of_current_pov 				= current_option_selected_values.length ? poip_product.getImagesForProductOptionValueIds(current_option_selected_values) : [];
						let images_of_current_pov_without_any 	= current_option_selected_values.length ? poip_product.getImagesForProductOptionValueIds(current_option_selected_values, true) : [];
						
						
						let image_to_display = '';
						
						if ( !image_to_display && images_of_all_selected_povs.length ) {
							
							let visible_images_of_all_selected_povs = visible_additional_images.length ? poip_common.getIntersectionOfArrays(visible_additional_images, images_of_all_selected_povs) : images_of_all_selected_povs.slice();
							
							if (!visible_images_of_all_selected_povs.length && product_option_settings.img_use == 0) {
								visible_images_of_all_selected_povs = images_of_all_selected_povs;
							}
							
							if ( visible_images_of_all_selected_povs.length ) {
								
								let visible_images_of_all_selected_povs_by_current_pov = poip_common.getIntersectionOfArrays(visible_images_of_all_selected_povs, images_of_current_pov);
								let visible_images_of_all_selected_povs_by_current_pov_without_any = poip_common.getIntersectionOfArrays(visible_images_of_all_selected_povs, images_of_current_pov_without_any);
								
								if (poip_product.module_settings.img_filter_by_comb) {
									let visible_images_of_all_selected_povs_by_current_pov_without_any_strict = poip_common.getIntersectionOfArrays(visible_images_of_all_selected_povs_by_current_pov_without_any, images_of_all_selected_povs_strict);
									let visible_images_of_all_selected_povs_by_current_pov_strict = poip_common.getIntersectionOfArrays(visible_images_of_all_selected_povs_by_current_pov, images_of_all_selected_povs_strict);
									let visible_images_of_all_selected_povs_strict = poip_common.getIntersectionOfArrays(visible_images_of_all_selected_povs, images_of_all_selected_povs_strict);
									if (visible_images_of_all_selected_povs_by_current_pov_without_any_strict.length) {
										image_to_display = poip_product.getFirstNotVideoFromArray(visible_images_of_all_selected_povs_by_current_pov_without_any_strict);
									} else if (visible_images_of_all_selected_povs_by_current_pov_strict.length) {
										image_to_display = poip_product.getFirstNotVideoFromArray(visible_images_of_all_selected_povs_by_current_pov_strict);
									} else if (visible_images_of_all_selected_povs_strict.length) {
										image_to_display = poip_product.getFirstNotVideoFromArray(visible_images_of_all_selected_povs_strict);
									}
									
								}
								
								if (!image_to_display) {
									if (visible_images_of_all_selected_povs_by_current_pov_without_any.length) {
										image_to_display = poip_product.getFirstNotVideoFromArray(visible_images_of_all_selected_povs_by_current_pov_without_any);
									} else if (visible_images_of_all_selected_povs_by_current_pov.length) {
										image_to_display = poip_product.getFirstNotVideoFromArray(visible_images_of_all_selected_povs_by_current_pov);
									} else {
										image_to_display = poip_product.getFirstNotVideoFromArray(visible_images_of_all_selected_povs);
									}
								}
							}
						}
						
						if ( !image_to_display && images_of_current_pov_checkbox_all.length ) {
							
							let visible_images_of_current_pov = poip_common.getIntersectionOfArrays(visible_additional_images, images_of_current_pov_checkbox_all);
							
							if ( visible_images_of_current_pov.length ) {
								image_to_display = poip_product.getFirstNotVideoFromArray(visible_images_of_current_pov);
							} else if ( product_option_settings.img_use == 0 ) {
								image_to_display = poip_product.getFirstNotVideoFromArray(images_of_current_pov_checkbox_all);
							}
						}
						
						if ( !image_to_display && images_of_current_pov.length ) {
							let visible_images_of_current_pov = poip_common.getIntersectionOfArrays(visible_additional_images, images_of_current_pov);
							if ( visible_images_of_current_pov.length ) {
								image_to_display = poip_product.getFirstNotVideoFromArray(visible_images_of_current_pov);
							}
						}
						
						if ( !image_to_display && images_of_all_selected_povs.length ) {
							if ( !poip_product.module_settings.img_filter_by_comb ) {
								image_to_display = poip_product.getFirstNotVideoFromArray(images_of_all_selected_povs);
							}
						}
						
						if ( !image_to_display && images_of_current_pov_checkbox_all.length ) {
							if ( (product_option_settings.img_limit == 0) || !poip_product.module_settings.img_filter_by_comb ) {
								image_to_display = poip_product.getFirstNotVideoFromArray(images_of_current_pov_checkbox_all);
							}
						}
						
						if ( !image_to_display && images_of_current_pov.length ) {
							if ( (product_option_settings.img_limit == 0 || !poip_product.module_settings.img_filter_by_comb ) && product_option_settings.img_use != 0 ) {
							//if ( (product_option_settings.img_limit == 0) || !poip_product.module_settings.img_filter_by_comb ) {
								image_to_display = poip_product.getFirstNotVideoFromArray(images_of_current_pov);
							}
						}
						
						if ( !image_to_display && !images_of_current_pov.length ) {
							if ( visible_additional_images.length ) {
								image_to_display = poip_product.getFirstNotVideoFromArray(visible_additional_images);
							}
						}
						
						if ( !image_to_display && !images_of_current_pov.length ) {
							
							if ( visible_additional_images.length ) {
								image_to_display = visible_additional_images[0];
							}
						}
						
						if (!image_to_display && !no_default) {
							image_to_display = poip_product.main_image_default_src;
						}
						
						return image_to_display;
					}
				}
			},
			
			updateMainImage : function(product_option_id, p_visible_additional_images, $option_element) {
				
				let image_to_display = poip_product.getImageToDisplayAsMain(product_option_id, p_visible_additional_images, $option_element);
				if (poip_product.isImageVideo(image_to_display)) {
					let image_to_display_no_video = poip_product.getImageToDisplayAsMain(product_option_id, poip_product.filterOffImagesVideos(p_visible_additional_images), $option_element, true);
					if (image_to_display_no_video) {
						image_to_display = image_to_display_no_video;
					}
				}
				
				if ( image_to_display ) {
					poip_product.setMainImage(image_to_display);
				}
				
			},
			
			isImageVideo : function(src) {
				let poip_img = poip_product.getImageBySrc(src);
				if (poip_img) {
					return poip_img.is_video;
				}
			},
			
			filterOffImagesVideos : function (srcs) {
				let srcs_new = [];
				poip_common.each(srcs, function(src){
					let poip_img = poip_product.getImageBySrc(src);
					if (poip_img && !poip_img.is_video) {
						srcs_new.push(src);
					}
				});
				return srcs_new;
			},
			
			getFirstNotVideoFromArray: function(images) {
				
				let first_image = images[0];
				poip_common.each(images, function(image){
					
					let poip_img = poip_product.getImageBySrc(image);
					if ( !poip_img.is_video ) {
					//if ( !poip_product.hrefIsVideo(image) ) {
						first_image = image;
						return false;
					}
				});
				
				return first_image;
			},
			
			getProductOptionValueImages : function(product_option_value_id) {
				
				var images = [];
				poip_common.each(poip_product.images, function(poip_img){
					if ( poip_img.product_option_value_id && $.inArray(product_option_value_id, poip_img.product_option_value_id) !=-1) {
						poip_common.addToArrayIfNotExists(poip_img.popup, images);
					}
				});
				return images;
			},
			
			getImageSrc : function(image, src) {
				
				for (var i in poip_product.images) {
					if (poip_product.images[i].image == image) {
						return poip_product.images[i][src];
					}
				}
				return '';
			},
			
			getImageBySrc : function(image, src1, src2, src3, src4) {
				
				let poip_img_found = poip_product.getImageBySrcExact(image, src1, src2, src3, src4);
				if ( !poip_img_found && image.substr(-5).toLowerCase() == '.webp' ) {
					poip_img_found = poip_product.getImageBySrcExact(image.substr(0, image.length-5), src1, src2, src3, src4);
					if ( !poip_img_found ) {
						poip_img_found = poip_product.getImageBySrcExact(image.substr(0, image.length-5), src1, src2, src3, src4, true);
					}
				}
				return poip_img_found;
			},
			getImageBySrcExact : function(image, src1, src2, src3, src4, ignore_poip_image_extension) {
				
				let search_in_extra_images = false;
				if ( !src1 && !src2 && !src3 && !src4 ) {
					src1 = 'popup';
					src2 = 'main';
					src3 = 'thumb';
					src4 = 'option_thumb';
					search_in_extra_images = true;
				}
				
				let srcs = [src1, src2, src3, src4, 'video', 'video_href', 'video_img'];
				let poip_img_found = '';
				poip_common.each(poip_product.images, function(poip_img){
					
					poip_common.each(srcs, function(src){
						if ( src && poip_img[src] && poip_product.compareImagePaths(poip_img[src], image, ignore_poip_image_extension) ) {
							poip_img_found = poip_img;
							return false;
						}
					});
					
					if (poip_img_found) {
						return false;
					}
					
					//if ( (src1 && poip_img[src1] == image) || (src2 && poip_img[src2] == image) || (src3 && poip_img[src3] == image) || (src4 && poip_img[src4] == image) || (search_in_extra_images && poip_img.extra_images && $.inArray(image, poip_img.extra_images) != -1 ) ) {
					//	poip_img_found = poip_img;
					//	return false;
					//}
				});
				return poip_img_found;
			},
			
			compareImagePaths: function(img1, img2, img1_ignore_ext, img2_ignore_ext, decoded){
				
				if ( img1 == img2 ) {
					return true;
				} else {
					
					let img1_comp = img1;
					let img2_comp = img2;
					
					if (img1_ignore_ext && img1.lastIndexOf('.') !== -1) {
						img1_comp = img1.substr(0, img1.lastIndexOf('.'));
						
						if ( img1_comp == img2 ) {
							return true;
						}
					}
					
					if (img2_ignore_ext && img2.lastIndexOf('.') !== -1) {
						img2_comp = img2.substr(0, img2.lastIndexOf('.'));
						if ( img1 == img2_comp || img1_comp == img2_comp ) {
							return true;
						}
					}
					
					if (poip_product.compareNitroPaths(img1, img2)) {
						return true;
					}
					
					if (img1.lastIndexOf('.') !== -1) {
						img2_comp = img2.substr(0, img2.lastIndexOf('.'));
						if ( img1 == img2_comp || img1_comp == img2_comp ) {
							return true;
						}
					}
					
				}
				
				if (!decoded) {
					return poip_product.compareImagePaths(decodeURI(img1), decodeURI(img2), img1_ignore_ext, img2_ignore_ext, true);
				}
			},
			
			compareNitroPaths: function(img1, img2) { // one of the images is hosted on nitro cdn
				let nitro_cdn_search = '.nitrocdn.com/';
				let img_nitro = '';
				let img = '';
				let nitro_first = false;
				
				if (img1 && img2) {
					if (img1.indexOf(nitro_cdn_search)!=-1) {
						img_nitro = img1;
						img = img2;
						nitro_first = true;
					} else if (img2.indexOf(nitro_cdn_search)!=-1) {
						img_nitro = img2;
						img = img1;
					}
					
					if (img_nitro) {
						let base_url = window.location.origin; //  + window.location.pathname does not work as needed in case of seo urls with /bla-bla.html
						//img_nitro = img_nitro.substr(img_nitro.indexOf(nitro_cdn_search)+nitro_cdn_search.length);
						img = img.substr(base_url.indexOf(base_url)+base_url.length);
						if (img_nitro.indexOf(img) != -1) {
							return true;
						}
					}
				}
			},
			
		
			getImagesBySrc : function(images, src1, src2, src3, src4) {
			  let poip_imgs = [];
			  poip_common.each(images, function(image){
				let poip_img = poip_product.getImageBySrc(image, src1, src2, src3, src4);
				if (poip_img) {
				  poip_imgs.push(poip_img);
				}
			  });
			  return poip_imgs;
			},
			
			
			getSrcImagesBySrc : function(src, images, src1, src2, src3, src4) {
				let poip_imgs = poip_product.getImagesBySrc(images, src1, src2, src3, src4);
				let src_images = [];
				poip_common.each(poip_imgs, function(poip_img){
					src_images.push(poip_img[src]);
				});
				return src_images;
			},
			
			getProductOptionValueIds : function(product_option_id) {
				var values = [];
				poip_product.getElement( poip_product.getSelectorOfOptions() ).filter(':radio, :checkbox').filter('[name*="['+product_option_id+']"]').each(function(){
					values.push( $(this).val() );
				});
				return values;
			},
			
			getProductOptionIdsDependentByImages : function(product_option_id) {
				
				var po_ids = [];
				poip_common.each( poip_product.images, function(poip_img){
					if ( poip_img.product_option_id && poip_img.product_option_id.length > 1 && $.inArray(product_option_id, poip_img.product_option_id) != -1 ) {
						poip_common.each(poip_img.product_option_id, function(current_po_id){
							if ( current_po_id != product_option_id ) {
								poip_common.addToArrayIfNotExists(current_po_id, po_ids);
							}
						});
					}
				});
				return po_ids;
			},
			
			getImagesRelevantToProductOptions : function(product_options){
				var images = false;
				
				poip_common.each(product_options, function(pov_ids){
					
					var current_images = poip_product.getImagesForProductOptionValueIds(pov_ids);
					if ( images === false ) {
						images = current_images;
					} else {
						images = poip_common.getIntersectionOfArrays(images, current_images);
					}
					
				});
				
				return images;
			},
			
			setVisibleProductOptionValueThumb : function(product_option_value_id, thumb){
				
				var $element = poip_product.getElement('[value="'+product_option_value_id+'"]').filter(':radio, :checkbox'); // usually the necessary element is already received by value, so this way works a bit faster
				if ( $element.next().is('img') ) {
					$element.next().attr('src', thumb);
				}
				
			},
			
			updateDependentThumbnails : function(visible_additional_images) {
				
				//var common_images_for_selected_options = false;
				poip_common.each(poip_product.product_option_ids, function(product_option_id){
		
					var product_option_settings = poip_product.options_settings[product_option_id];
		
					if ( product_option_settings.dependent_thumbnails ) {
		
						var pov_ids = poip_product.getProductOptionValueIds(product_option_id);
		
						if ( pov_ids.length ) {
							
							var dependend_product_option_ids = poip_product.getProductOptionIdsDependentByImages(product_option_id);
							var selected_product_options = poip_product.getSelectedProductOptions();
							
							
							
							var selected_dependent_product_options = {};
							poip_common.each(dependend_product_option_ids, function(dependent_po_id){
								if ( selected_product_options[dependent_po_id] ) {
									selected_dependent_product_options[dependent_po_id] = selected_product_options[dependent_po_id];
								}
							});
							
							var images_of_dependend_product_options = poip_product.getImagesRelevantToProductOptions(selected_dependent_product_options);
							
							poip_common.each(pov_ids, function(product_option_value_id){
								var pov_images 	= poip_product.getProductOptionValueImages(product_option_value_id);
								var pov_image 	= pov_images[0];
								
								if ( pov_images.length ) {
		
									var pov_images_visible = poip_common.getIntersectionOfArrays(visible_additional_images, pov_images);
									if ( pov_images_visible.length ) {
										pov_image = pov_images_visible[0];
										
									} else if ( images_of_dependend_product_options.length ) {
		
										var pov_images_common = poip_common.getIntersectionOfArrays(pov_images, images_of_dependend_product_options);
										if ( pov_images_common && pov_images_common.length ) {
											pov_image = pov_images_common[0];
										}
									}
								}
								if ( pov_image ) {
									var poip_img = poip_product.getImageBySrc(pov_image, 'popup');
									poip_product.setVisibleProductOptionValueThumb(product_option_value_id, poip_img.option_thumb);
								}
							});
						}
					}
				});
				
			},
			
			// return IMG element relevant to main image
			getMainImage : function() {
				return poip_product.getElement('ul.thumbnails li').not('.image-additional').find('a img');
			},
			
			// returns element/elements (div, ul, li etc, depend on theme) containing links to additional images (а)
			getAdditionalImagesBlock : function() {
				
				return poip_product.getElement('li.image-additional');
			
			},
			
			hrefIsVideo : function(href) {
				
				if ( href ) {
					if ( href.indexOf('https://www.youtube.com')==0 || href.indexOf('http://www.youtube.com')==0 || href.indexOf('https://youtube.com')==0 || href.indexOf('http://youtube.com')==0 || href.indexOf('https://www.vimeo.com')==0 || href.indexOf('http://www.vimeo.com')==0 || href.indexOf('https://vimeo.com')==0 || href.indexOf('http://vimeo.com')==0 || href.indexOf('www.youtube.com')==0 || href.indexOf('youtube.com')==0 || href.indexOf('//www.youtube.com')==0 || href.indexOf('//youtube.com')==0 || href.indexOf('www.vimeo.com')==0 || href.indexOf('vimeo.com')==0 || href.indexOf('#iproductvideo_local')!=-1 ) {
						return true;
					}
				}
				return false;
			},
			
			elementIsVideo : function($elem){
				if ( $elem.attr('data-ipvitem') ) {
					try {
						let ipvitem = $.parseJSON($elem.attr('data-ipvitem'));
						if ( ipvitem && ipvitem.ipv && ipvitem.ipv != 'false' ) {
							return true;
						}
					} catch(e) {
						// ignore
					}
				}
				if ( $elem.prop('nodeName') == 'A' ) {
					if ( poip_product.hrefIsVideo($elem.attr('href')) ) {
						return true;
					} else if ( $elem.attr('data-href') && poip_product.hrefIsVideo( $elem.attr('data-href') ) ) { // Video Product Page by xprolance
						return true; 
					}

				}
			},
			
			getAdditionalImageSrc : function($element, attrs_names) {
				
				let href = '';
				let attr_name = '';
				
				if (Array.isArray(attrs_names)) {
					let result = '';
					poip_common.each(attrs_names, function(current_attr_name){
						if ($element.attr(current_attr_name)) {
							result = $element.attr(current_attr_name);
							return false;
						}
					});
					if (result) {
						return result;
					}
				} else {
					attr_name = attrs_names;
				}
				
				if ( attr_name ) {
					href = $element.attr(attr_name);
				} else {
					if ( $element.is('img') ) {
						href = $element.attr('src');
					} else {
						href = $element.attr('href');
					}
				}
				return href;
			},
			
			eventAdditionalImageMouseover : function(event, $element) {
				
				let image = poip_product.getAdditionalImageSrc($element);
				let poip_img = poip_product.getImageBySrc(image);
				
				if ( image ) {
					if ( poip_product.hrefIsVideo(poip_img.popup) ) {
						return;
					}
					poip_product.setMainImage(poip_img.popup);
				}
			},
			
			eventAdditionalImageClick : function(e, $element) {
				
				e.preventDefault();
				e.stopPropagation();
				
				poip_product.eventAdditionalImageMouseover(e, $element);
				
			},
			
			updateImageAdditionalMouseOver : function() {
				if ( poip_product.module_settings.img_hover ) {
					poip_product.getAdditionalImagesBlock().find('a').off('mouseover').on('mouseover', function(e){
						poip_product.eventAdditionalImageMouseover(e, $(this));
					});
				}
				if ( poip_product.module_settings.img_click ) {
					poip_product.getAdditionalImagesBlock().find('a').off('click').on('click', function(e){
						poip_product.eventAdditionalImageClick(e, $(this));
					});
				}
			},
			
			setDefaultOptionsByURL : function() {
				
				let pov_ids = poip_product.getDefaultOptionValuesToSet();
				
				if ( pov_ids.length ) {
					poip_product.setDefaultOptionValues(pov_ids);
					return true;
				}
		
			},
		
			setDefaultOptionValues : function(pov_ids) {
				poip_common.each(pov_ids, function(pov_id){
					poip_product.setProductOptionValue(pov_id);
				});
			},
		
			getDefaultOptionValuesToSet : function(){
				let pov_ids = [];
				if (poip_product.poip_ov) {
					pov_ids.push(poip_product.poip_ov);
				}
				
				// for Yandex sync module by Toporchillo
				var hash = window.location.hash;
				if (hash) {
					var hashpart = hash.split('#');
					var hashvals = hashpart[1].split('-');
					var hashvals_numbers = hashvals.filter(function(val){
						return val == parseInt(val);
					});
					if ( hashvals_numbers.length == hashvals.length ) { // if not all values are numbers, the has param is not from sync module by Toporchillo
						for (i=0; i<hashvals.length; i++) {
							if ( !hashvals.hasOwnProperty(i) ) continue;
							
							if ( $.inArray(hashvals[i], pov_ids) == -1 ) {
							  pov_ids.push(hashvals[i]);
							}
						}
					}
				}
				return pov_ids;
			},
			
			setProductOptionValue : function(value) {
			
				var $option_element = poip_product.getElement( poip_product.getSelectorOfOptions(' option') ).filter('[value="'+value+'"]:not(:disabled)');
				if ( !$option_element.length ) {
					return;
				}
				
				if ( $option_element.is('option') ) { // select
					$option_element.parent().val(value);
					$option_element.parent().trigger('change');
				} else { // radio or checkbox
					$option_element.prop('checked', true);
					$option_element.trigger('change');
				}
				
				// color option comp
				if ( value && poip_product.getElement('#color-option-'+value).length ) {
					poip_product.getElement('#color-option-'+value).click();
				}
				
				return true;
			},
			
			
			externalOptionChange : function() {
				if ( poip_product.product_option_ids.length ) {
					poip_product.updateImagesOnProductOptionChange(poip_product.product_option_ids[0]);
				}
			},
			
			elevateZoomDirectChange : function(img_click, timeout, elem_img) {
			
				if ( timeout ) {
					setTimeout(function(){
						poip_product.elevateZoomDirectChange(img_click, 0, elem_img);
					}, timeout);
				} else {
					let $zoom_container = $('.zoomContainer, .ZoomContainer').first();
					$zoom_container.find('.zoomWindowContainer').find('div').css({"background-image": 'url("'+img_click+'")'});
					let zoom_type = '';
					if ( poip_product.getMainImage().data('elevateZoom') && poip_product.getMainImage().data('elevateZoom').options ) {
						zoom_type = poip_product.getMainImage().data('elevateZoom').options.zoomType;
					}
					if ( zoom_type != 'window' ) {
						$zoom_container.find('.zoomLens').css({"background-image": 'url("'+img_click+'")'});
					}
				}
			},
			
			theme_adaptation : {
				
				stored_image_elements_by_collections : [],
				
				// {$images: , attr_name: , getAttr: , parent_selector: , child_selector: , html_version:, getImageBySrc:, extra_actions:, getElementHTML: , store_clone:, ignore_missed }
				storeImageElementsToImageCollection : function(params) {
					
					let html_version = params.html_version || 'html';
					if ( $.inArray(html_version, poip_product.theme_adaptation.stored_image_elements_by_collections) == -1 ) {
						
						poip_product.theme_adaptation.stored_image_elements_by_collections.push(html_version);
						
						params.$images.each(function(){
							let $image = $(this);
							
							let image = '';
							if ( params.getAttr ) {
								image = params.getAttr($image);	
							} else {
								if ($image.attr('nitro-lazy-src')) {
									image = $image.attr('nitro-lazy-src');
								} else {
									let attr_name = params.attr_name || ( $image.is('img') ? 'src' : 'href' );
									image = $image.attr(attr_name);
								}
							}
							
							let poip_img = params.getImageBySrc ? params.getImageBySrc(image) : poip_product.getImageBySrc(image);
							
							if ( poip_img ) {
								
								if ( params.extra_actions ) {
									params.extra_actions(poip_img, $image);
								}
								
								let $element = $image;
								if ( params.child_selector ) {
									$element = $element.find(params.child_selector).first();
								} else if ( params.parent_selector ) {
									$element = $element.closest(params.parent_selector);
								} 
								let html = params.getElementHTML ? params.getElementHTML($element) : poip_common.getOuterHTML( $element );
									
								poip_img[html_version] = html;
								
								if ( params.store_clone ) {
									if ( !poip_img.html_clone ) {
										poip_img.html_clone = {};
									}
									poip_img.html_clone[html_version] = $element.clone(true, true);
								}
							} else {
								if ( !params.ignore_missed ) {
									console.debug('poip img not found (to store): '+image);
								}
							}
						});
					}
				},
				
				getStoredImagesContainer : function($put_before_element, $carousel_items, data_key) {
					data_key = data_key || 'images';
					var selector = '[data-poip="'+data_key+'"]';
					if ( !poip_product.getElement(selector).length ) {
						$put_before_element.before('<div data-poip="'+data_key+'" style="display:none;!important"></div>');
						$carousel_items.each(function(){
							poip_product.getElement(selector).append( poip_common.getOuterHTML($(this)) );
						});
					}
					return poip_product.getElement(selector);
				},
			
				updateShouldBeProcessed : function($carousel_items, href_attr_name, images_to_check, images, counter, carousel_is_ready, ignore_image_coincidence, params, poip_img_src ) {
					
					if ( !poip_product.custom_data.set_visible_images_is_called ) {
						if ( !carousel_is_ready ) {
							poip_product.timers.set_visible_images = setTimeout(function(){
								poip_product.custom_methods['setVisibleImages.instead'](images, counter+1, params);
							}, 100);
							return false;
						}
					}
						
					
					if ( (poip_product.custom_data.set_visible_images_is_called && !ignore_image_coincidence) || ignore_image_coincidence === 'CHECKALWAYS' ) {
						if ( poip_product.theme_adaptation.checkDisplayedImagesAreActual($carousel_items, href_attr_name, images_to_check, poip_img_src) ) {
							poip_product.works.set_visible_images = false;
							return false; // nothing to change
						}
					}
					poip_product.custom_data.set_visible_images_is_called = true;
					
					return true;
				},
				
				checkDisplayedImagesAreActual: function($carousel_items, href_attr_name, images_to_check, poip_img_src) {
					
					let current_imgs = [];
					$carousel_items.each( function(){
						current_imgs.push($(this).attr(href_attr_name));
					});
					
					if (poip_img_src) {
						current_imgs = poip_product.getSrcImagesBySrc(poip_img_src, current_imgs);
					}
					
					return current_imgs.toString() == images_to_check.toString();
				},
				
			},
		};
		
		if ( custom_setPoipProductCustomMethods ) { // custom
			custom_setPoipProductCustomMethods(poip_product);
		} else if ( typeof(setPoipProductCustomMethods) == 'function' ) { // global
			setPoipProductCustomMethods(poip_product);
		}
		
		return poip_product;
	})(jQuery);
}


