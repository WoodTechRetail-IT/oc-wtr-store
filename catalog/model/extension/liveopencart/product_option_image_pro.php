<?php
//  Product Option Image PRO / Изображения опций PRO
//  Support: support@liveopencart.com / Поддержка: help@liveopencart.ru

class ModelExtensionLiveopencartProductOptionImagePro extends Model {
	
	use \liveopencart\lib\v0023\traits\cache;
  
	protected $theme_name = '';
	protected $installed = null;
	protected $cache_product_option_settings = array();
	protected $theme_methods = null;
	
	public function getThemeName() {
		
		if (!$this->theme_name) {
			$theme_name = '';
			
			$settings = $this->getSettings();
			if ( !empty($settings['custom_theme_id']) ) {
				$theme_name = $settings['custom_theme_id'];
			} else {
			
				if ($this->config->get('config_theme') == 'theme_default' || $this->config->get('config_theme') == 'default') {
					$theme_name = $this->config->get('theme_default_directory');
				} else {
					$theme_name = substr($this->config->get('config_theme'), 0, 6) == 'theme_' ? substr($this->config->get('config_theme'), 6) : $this->config->get('config_theme') ;
				}
				
				if ($theme_name == 'BurnEngine') {
					$theme_info = (array) $this->config->get( 'BurnEngine_theme');
					if ($theme_info && !empty($theme_info['id']) ) {
						$theme_name = $theme_name.'_'.$theme_info['id']; 
					}
				}
				
				// shorten theme name
				$themes_shorten = $this->getAdaptedThemes();
				foreach ( $themes_shorten as $theme_shorten ) {
					$theme_shorten_length = strlen($theme_shorten);
					if ( substr($theme_name, 0, $theme_shorten_length) == $theme_shorten ) {
						$theme_name = substr($theme_name, 0, $theme_shorten_length);
						break;
					}
				}
				
				$theme_name = $this->replaceThemeNameIfSibling($theme_name);
				
			}
			$this->theme_name = $theme_name;
		}
		return $this->theme_name;
	}
	
	public function setThemeName($theme_name) {
		$this->theme_name = $theme_name;
	}
	public function resetThemeName() {
		$this->theme_name = '';
	}
	
	protected function replaceThemeNameIfSibling($theme_name) {
		$sibling_file_name = $this->getBasicDirOfExtension().'theme_sibling/'.$theme_name.'.php';
		if ( file_exists($sibling_file_name) ) {
			require($sibling_file_name); // $sibling_main_theme should be defined there
			if ( !empty($sibling_main_theme) ) {
				return $sibling_main_theme;
			}
		}
		return $theme_name;
	}
	
	protected function getAdaptedThemes() {
		
		$dir_of_themes = $this->getBasicDirOfTemplates();
		
		$themes = glob($dir_of_themes . '*' , GLOB_ONLYDIR);
		if ( $themes ) {
			$themes = array_map( 'basename', $themes );
			
			if ( ($default_key = array_search('default', $themes)) !== false ) {
				unset($themes[$default_key]);
			}
			
			usort($themes, function($a, $b) {
				return strlen($b) - strlen($a);
			});
			return $themes;
		} else {
			return array();
		}
		
	}
	
	public function getBasicDirOfExtension() {
		return DIR_TEMPLATE.'extension_liveopencart/product_option_image_pro/';
	}
	public function getBasicDirOfTemplates() {
		return $this->getBasicDirOfExtension().'theme/';
	}
	public function getDirOfCurrentTemplate() {
		$theme_dir = $this->getBasicDirOfTemplates().$this->getThemeName().'/';
		if ( !file_exists($theme_dir) || !is_dir($theme_dir) ) {
			$theme_dir = $this->getBasicDirOfTemplates().'default/';
		} 
		return $theme_dir;
	}
	
	
	protected function getThemeMethods() {
		if ( is_null($this->theme_methods) ) {
			$this->theme_methods = false;
			$file_name = $this->getDirOfCurrentTemplate().'theme_methods.php';
			if ( file_exists($file_name) ) {
				require_once($file_name);
				
				$this->theme_methods = new theme_methods($this->registry);
			}
		}
		return $this->theme_methods;
	}
	
	protected function hasThemeMethod($method_name) {
		return $this->getThemeMethods() && method_exists($this->getThemeMethods(), $method_name);
	}
	
	protected function getThemeSettings() {
		if ( !$this->liveopencart_ext_poip->hasCachedValue('theme_settings') ) {
			if ( $this->hasThemeMethod('getSettings') ) {
				$theme_settings = $this->getThemeMethods()->getSettings();
			} else {
				$file_name = $this->getDirOfCurrentTemplate().'theme_settings.php';
				if ( file_exists($file_name) ) {
					require_once($file_name);
					$theme_settings = $_;
				} else {
					$theme_settings = array();
				}
			}
			$this->liveopencart_ext_poip->setCachedValue('theme_settings', $theme_settings);
		}
		return ( $this->liveopencart_ext_poip->getCachedValue('theme_settings') );
	}
	
	protected function getThemeSetting($setting_key) {
		
		$theme_settings = $this->getThemeSettings();
		if ( isset($theme_settings[$setting_key]) ) {
			return $theme_settings[$setting_key];
		}
	}
  
	protected function render($route, $data) {
		// $this->registry is added for compatibility with d_twig_manager.xml
		$template = new Template($this->registry->get('config')->get('template_engine'), $this->registry);
				
		foreach ($data as $key => $value) {
			$template->set($key, $value);
		}
		
		$classMethod = new ReflectionMethod($template,'render');
		if ( count($classMethod->getParameters()) > 2 )  { 
			if ( $classMethod->getParameters()[1]->name == 'cache' && $classMethod->getParameters()[2]->name == 'registry' ) {
				$output = $template->render( $route, false, $this->registry ); // for some mods ($route, $cache=false, $registry)
			} else {
				$output = $template->render( $route, $this->registry ); // for some mods ($route, $registry, $cache=false)
			}
		} else { // std
			$output = $template->render( $route );
		}
		
		return $output;
	}

	protected function existsImageFile($image) {
		return trim($image) && (file_exists(DIR_IMAGE.$image) || strtolower(substr($image, 0, 7)) == 'http://' || strtolower(substr($image, 0, 8)) == 'https://');
	}
	
	public function addExtraDataToProductOptionValues($product_id, $p_product_option_values) {
		$product_option_values = $p_product_option_values;
		
		if ( $this->liveopencart_ext_poip->installed() ) {
			$product_options_images = $this->getProductOptionImages($product_id, false, false);
			$product_options_settings = $this->liveopencart_ext_poip->getProductSettings($product_id);
			
			foreach ($product_option_values as &$product_option_value) {
				if ( isset($product_options_settings[(int)$product_option_value['product_option_id']]['img_first']) && $product_options_settings[(int)$product_option_value['product_option_id']]['img_first'] ) {
					if ( isset($product_options_images[(int)$product_option_value['product_option_id']][$product_option_value['product_option_value_id']][0]['image'])) {
						$product_option_value['image'] = $product_options_images[(int)$product_option_value['product_option_id']][$product_option_value['product_option_value_id']][0]['image'];
					}
				}
				if ( isset($product_options_settings[(int)$product_option_value['product_option_id']]['img_radio_checkbox']) && $product_options_settings[(int)$product_option_value['product_option_id']]['img_radio_checkbox'] ) {
					
					$product_option_value['poip_image'] = $product_option_value['image'];
				
					if ( isset($product_options_settings[(int)$product_option_value['product_option_id']]['img_first']) && $product_options_settings[(int)$product_option_value['product_option_id']]['img_first'] ) {
						if ( isset($product_options_images[(int)$product_option_value['product_option_id']][$product_option_value['product_option_value_id']][0]['image'])) {
							$product_option_value['poip_image'] = $product_options_images[(int)$product_option_value['product_option_id']][$product_option_value['product_option_value_id']][0]['image'];
						}
					}
				}
			}
			unset($product_option_value);
		}
		return $product_option_values;
	}
	
	
	protected function getLinksForResources($scripts) {
		$results = [];
		foreach ( $scripts as $script ) {
			$results[] = $this->liveopencart_ext_poip->getResourceLinkWithVersion($script, 'catalog/');
		}
		return $results;
	}
	
	public function getHeaderScripts() {
		$scripts = array(	'view/theme/extension_liveopencart/product_option_image_pro/liveopencart.poip_common.js',
											'view/theme/extension_liveopencart/product_option_image_pro/liveopencart.poip_list.js',
										);
		$custom_script = 'view/theme/extension_liveopencart/product_option_image_pro/theme/'.$this->getThemeName().'/theme_list.js';
		if ( file_exists( DIR_APPLICATION.$custom_script ) ) {
			$scripts[] = $custom_script;
		}
		return $this->getLinksForResources($scripts);
	}
  
	public function addHeaderResources() {
		$this->addScripts( $this->getHeaderScripts() );
	}
	
	public function getProductPageScripts() {
		$scripts = array();
		if ( $this->liveopencart_ext_poip->installed() ) {
			$scripts[] = 'view/theme/extension_liveopencart/product_option_image_pro/liveopencart.poip_product.js';
			
			$custom_script = 'view/theme/extension_liveopencart/product_option_image_pro/theme/'.$this->getThemeName().'/theme_product.js';
			if ( file_exists( DIR_APPLICATION.$custom_script ) ) {
				$scripts[] = $custom_script;
			}

		}
		return $this->getLinksForResources($scripts);
	}
	
	public function addProductListingScript() {
		$custom_script = 'view/theme/extension_liveopencart/product_option_image_pro/theme/'.$this->getThemeName().'/theme_product_listing.js';
		if ( file_exists(DIR_APPLICATION.$custom_script) ) {
			$this->addScripts( $this->getLinksForResources( array($custom_script) ) );
		}
	}
	
	public function addProductPageResources() {
		$this->addScripts( $this->getProductPageScripts() );
	}
	
	protected function addScripts($scripts_links) {
		if ( $this->liveopencart_ext_poip->installed() ) {
			foreach ( $scripts_links as $script_link ) {
				$this->document->addScript( $script_link );
			}
		}
	}
	
	protected function getProductMainImage($p_product_id) {
		$query = $this->db->query("SELECT `image` FROM `".DB_PREFIX."product` WHERE `product_id` = '".(int)$p_product_id."' ");
		if ( $query->num_rows && $query->row['image'] ) {
			return $this->applyExternalWatermarkToImage($p_product_id, $query->row['image'], false);
		}
		return '';
	}
  
	protected function addMainProductImageToAdditionalIfNeeded($product_id, $product_images) {
		
		if ($this->liveopencart_ext_poip->installed()) {
			
			$poip_settings = $this->getSettings();
			
			if ( !$poip_settings['img_main_to_additional'] ) {
				$poip_settings['img_main_to_additional'] = $this->getThemeSetting('img_main_to_additional_forced');
			}
			
			if ( !empty($poip_settings['img_main_to_additional']) ) {
				
				if ( $poip_settings['img_main_to_additional'] == 1 || ( $poip_settings['img_main_to_additional'] == 2 && $product_images ) ) {
					
					// if there's no the main image in the list of additional images, let's add it
				
					$product_main_image = $this->getProductMainImage($product_id);
					if ( $product_main_image ) {
						
						$have_image = false;
						foreach ($product_images as &$product_image) {
							if ($product_image['image'] == $product_main_image) {
								$have_image = true;
								if (!isset($product_image['product_image_id'])) { // the main image is probably added to additinal images by external mod or a theme
									$product_image['product_image_id'] = -1; 
								}
								break;
							}
						}
						unset($product_image);
						if (!$have_image) {
							array_unshift($product_images, array('product_id'=>$product_id, 'image'=>$product_main_image, 'sort_order'=>0, 'product_image_id'=>"-1"));
						}
					}
				}	
			}
		}
    
		return $product_images;
    
	}
	
	protected function filterOffNonexistentProductImages($product_images) {
		$results = array();
		foreach ( $product_images as $product_image ) {
			
			if ( $product_image['image'] ) {
				if ( $this->existsImageFile($product_image['image']) ) {
					$results[] = $product_image;
				}
			}
			
		}
		return $results;
	}
	
	protected function removeMainProductImageFromAdditionalIfNeeded($results) {
		if ( $this->getThemeSetting('do_not_add_main_image_to_additional_images') ) {
			$new_results = array();
			foreach ( $results as $result ) {
				if ( !(isset($result['product_image_id']) && $result['product_image_id'] == -1) ) {
					$new_results[] = $result;
				}
			}
		} else {
			$new_results = $results;
		}
		return $new_results;
	}
	
	protected function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");
		return $query->rows;
	}
	
	public function getDataForProductPage($product_id, $p_results=array(), $data=array()) {
		
		if ( !$product_id && isset($data['product_id']) ) {
			$product_id = $data['product_id'];
		}
		
		if ( !empty($data['poip_custom_theme_name']) ) {
			$this->setThemeName($data['poip_custom_theme_name']);
		}
		
		$data['poip_installed'] = $this->liveopencart_ext_poip->installed();
		
		$data['poip_theme_name'] = $this->getThemeName();
		
		
		if ($this->getThemeSetting('use_own_function_to_get_additional_product_images_for_product_page')) {
			$results = $this->getProductImages($product_id);
		} else {
			$results = $p_results;
		}
		
		if ( $data['poip_installed'] ) {
			
			$results = $this->filterOffNonexistentProductImages($results);
			
			$image_sizes = !empty($data['poip_img_sizes']) ? $data['poip_img_sizes'] : $this->getThemeSetting('image_sizes');
			
			$results = $this->addMainProductImageToAdditionalIfNeeded($product_id, $this->addOptionImagesToProductImages($results, $product_id, array(), $image_sizes )['results']);
			//$results = $this->addMainProductImageToAdditionalIfNeeded($product_id, $results);
			
			$data['poip_product_settings'] = $this->liveopencart_ext_poip->getProductSettings($product_id);
			$data['poip_settings'] = $this->getSettings();
			
			$poip_results = $this->addOptionImagesToProductImages($results, $product_id, array(), $image_sizes );
			//$poip_results['results'] = $this->addMainProductImageToAdditionalIfNeeded($product_id, $poip_results['results']);
			
			if ( !$results ) { // fill with option images only if the array additional images is initially empty
				$results = $poip_results['results']; 
			} else {
				// normally leave only standard/initial images, but with all additional data
				// but if the specific theme setting is enabled, then add all images
				if ( $this->getThemeSetting('add_option_images_to_additional_on_server_side') ) {
					
					$result_images = array();
					foreach ( $results as $result ) {
						$result_images[] = $result['image'];
					}
					
					foreach ( $poip_results['results'] as $result) {
						if ( !in_array($result['image'], $result_images) ) {
							$results[] = $result;
						}
					}
					
				} 
			}
			
			$product_images = $poip_results['product_images'];
			
			if ( $this->getThemeSetting('poip_custom_js_settings') ) {
				$data['poip_custom_js_settings'] = $this->getThemeSetting('poip_custom_js_settings');
			}
			
			$data['poip_images'] = $product_images;
			$data['poip_product_option_ids'] = $this->getProductOptionsIdsWithImages($product_images);
			$data['poip_images_by_povs'] = $this->getProductOptionImagesByValues($product_id);
			
			// for some themes using twig file for custom scripts
			$poip_theme_script_route = 'extension_liveopencart/product_option_image_pro/theme/'.$this->getThemeName().'/theme_product';
			if ( file_exists( DIR_TEMPLATE.$poip_theme_script_route.'.twig' ) ) {
				$data['poip_theme_script'] = $this->render($poip_theme_script_route, $data);
			}
			
			// for old-style themes using .tpl engine (like Fastor)
			$poip_include_tpl = DIR_APPLICATION.'view/theme/extension_liveopencart/product_option_image_pro/theme/'.$this->getThemeName().'/theme_product.tpl';
			if ( file_exists( $poip_include_tpl ) ) {
				$data['poip_include_tpl'] = $poip_include_tpl;
			}
			
			$results = $this->removeMainProductImageFromAdditionalIfNeeded($results);
			
			if ( $this->hasThemeMethod('prepareProductImagesForProductPage') ) {
				$results = $this->getThemeMethods()->prepareProductImagesForProductPage($results);
			}
			
			$poip_ov = $this->getPOIPIdFromRequest();
			if ( $poip_ov ) {
				$data['poip_ov'] = $poip_ov;
			}
			
			if ($this->config->get('video_product_page_setting')) { // comp with Video Product Page by xprolance
				$video_product_page_setting = $this->config->get('video_product_page_setting');
				if (isset($video_product_page_setting['first']) && !$video_product_page_setting['first']) {
					$data['poip_settings']['videos_to_end'] = true;
				}
			}
			
			$poip_script_route = 'extension_liveopencart/product_option_image_pro/product_page';
			$data['poip_script'] = $this->render($poip_script_route, $data);
			
			$data['poip_scripts_product_page'] = $this->getProductPageScripts(); // to use scripts in some specific templates (like some quickviews)
			if ( !empty($data['poip_return_scripts']) ) {
				$data['poip_scripts'] = $data['poip_scripts_product_page'];
			}
			
		}
		
		if ( !empty($data['poip_custom_theme_name']) ) {
			$this->resetThemeName();
		}
		
		return array('data'=>$data, 'results'=>$results);
	}
	
	public function getProductOptionValuePOIPThumbResized($option_value) {
		
		if ( !empty($option_value['poip_image']) ) {
			return $this->model_tool_image->resize($option_value['poip_image'], 50, 50);
		}
		return '';
	}
	
	public function getPOIPIdFromRequest() {
		$poip_ov = false;
		if (isset($_GET['poip_ov'])) {
			if ($_GET['poip_ov']) {
				$poip_ov = $_GET['poip_ov'];
			}
		} elseif ( isset($_SERVER['REQUEST_URI']) ) {
		
			$poip_ov_name = "poip_ov=";
			if (strpos($_SERVER['REQUEST_URI'], $poip_ov_name) !== false) {
			
				$poip_str = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $poip_ov_name)+strlen($poip_ov_name));
				$poip_ov = "";
				while ($poip_str != "" && strpos("0123456789", substr($poip_str, 0, 1))!==false ) {
					$poip_ov.= substr($poip_str, 0, 1);
					$poip_str = substr($poip_str, 1);
				}
				if ($poip_ov != "") {
					$poip_ov = $poip_ov;
				}
			}
		}
		return $poip_ov;
	}
	
	// module settings
	public function getSettings() {
    
		$poip_settings = $this->config->get('poip_module');
    
		return $poip_settings;
    
	}
	
	protected function applyExternalWatermarkToImage($product_id, $image, $is_additional=false) {
		if ($this->config->get('module_iwatermark_status')) {
			$this->load->model('extension/module/iwatermark');
			$image = $this->model_extension_module_iwatermark->imageSymlinkLinkImage($product_id, $image, $is_additional, false);
		}
		
		if ( !$this->hasCacheSimple('model_extension_module_labelmaker') ) {
			if ( !$this->model_extension_module_labelmaker ) {
				if ( file_exists(DIR_APPLICATION.'model/extension/module/labelmaker.php') ) {
					$this->load->model('extension/module/labelmaker');
				}
			}
			if ( $this->model_extension_module_labelmaker ) {
				$this->setCacheSimple('model_extension_module_labelmaker', $this->model_extension_module_labelmaker);
			} else {
				$this->setCacheSimple('model_extension_module_labelmaker', false);
			}
		}
		
		if ( $this->getCacheSimple('model_extension_module_labelmaker') ) {
			$image = $this->getCacheSimple('model_extension_module_labelmaker')->imageSymlinkLinkImage($product_id, $image, $is_additional, false);
		}
		
		
		return $image;
	}
	
	protected function getOptionImagesBasicData($product_id) {
		$cache_key = json_encode(func_get_args());
	
		if ( !$this->hasCache(__FUNCTION__, $cache_key) ) {
			
			$poip_settings = $this->liveopencart_ext_poip->getProductSettings($product_id);
			
			$po_ids = array();
			foreach ( $poip_settings as $po_id => $po_settings ) {
				if ( !empty($po_settings['img_first']) && $po_settings['img_first'] == 2 ) {
					$po_ids[] = (int)$po_id;
				}
			}
			
			
			$all_product_option_images = array();
			
			if ( $po_ids ) {
			
				$language_id = (int)$this->config->get('config_language_id');
				
				// add standard options image into option value images list (if needed)
				$query = $this->db->query("
					SELECT POV.*, OV.image, OD.name option_name, OVD.name value_name
					FROM ".DB_PREFIX."product_option_value POV
						LEFT JOIN ".DB_PREFIX."option_description OD ON (POV.option_id = OD.option_id AND OD.language_id = ".$language_id.")
					  , ".DB_PREFIX."option_value OV 
						LEFT JOIN ".DB_PREFIX."option_value_description OVD ON (OVD.option_value_id = OV.option_value_id AND OVD.language_id = ".$language_id.")
					  
					WHERE POV.option_value_id = OV.option_value_id
					  AND POV.product_id = ".(int)$product_id."
					  AND POV.product_option_id IN (".implode(",", $po_ids).")
					ORDER BY OV.sort_order ASC  
				");
				
					
				foreach ($query->rows as $row) {
					//if ( isset($poip_settings[$row['product_option_id']]['img_first']) && $poip_settings[$row['product_option_id']]['img_first'] == 2 ) {
						if ( $this->existsImageFile($row['image']) ) {
							$row['image'] = $this->applyExternalWatermarkToImage($product_id, $row['image'], true);
							$all_product_option_images[] = array( 'option_name'=>$row['option_name'],
								'value_name'=>$row['value_name'],
								'product_id'=>$row['product_id'],
								'product_option_id'=>$row['product_option_id'],
								'product_option_value_id'=>$row['product_option_value_id'],
								'image'=>$row['image'],
								'sort_order'=>0,
								'thumb'=>$row['image'],
								'option_id'=>$row['option_id'],
								'option_value_id'=>$row['option_value_id'],
							);
						}
					//}
				}
			}
			$this->setCache(__FUNCTION__, $cache_key, $all_product_option_images);
		}
		return $this->getCache(__FUNCTION__, $cache_key);
	}
	
	protected function getProductOptionImagesBasicData($product_id, $stock_control) {
		$cache_key = json_encode(func_get_args());
	
		if ( !$this->hasCache(__FUNCTION__, $cache_key) ) {
			
			$language_id = (int)$this->config->get('config_language_id');
			
			$existing_images = array();
			
			$query = $this->db->query("
				SELECT POIP.*, OV.image thumb, OD.name option_name, OVD.name value_name, PO.option_id, POV.option_value_id, POV.product_option_value_id pov_product_option_value_id, (POIP.product_option_value_id = -1) by_any_value
				FROM ".DB_PREFIX."poip_option_image POIP
					LEFT JOIN ".DB_PREFIX."product_option_value POV
						ON ( POIP.product_option_id = POV.product_option_id
									AND (POIP.product_option_value_id = POV.product_option_value_id OR POIP.product_option_value_id = -1) )
					LEFT JOIN ".DB_PREFIX."option_value OV ON (POV.option_value_id = OV.option_value_id)
					LEFT JOIN ".DB_PREFIX."option_value_description OVD ON (OVD.option_value_id = OV.option_value_id AND OVD.language_id = ".$language_id.")
					,`".DB_PREFIX."option` O
					LEFT JOIN ".DB_PREFIX."option_description OD ON (OD.option_id = O.option_id AND OD.language_id = ".$language_id.")
					,".DB_PREFIX."product_option PO
				WHERE POIP.product_id = ".(int)$product_id."
				  AND PO.product_option_id = POIP.product_option_id
				  AND PO.option_id = O.option_id
				  ".($stock_control ? "AND ( (POV.product_option_value_id IS NULL) OR (NOT POV.subtract OR POV.quantity > 0) )" : "")."
				ORDER BY O.sort_order ASC, POIP.product_option_id ASC, OV.sort_order ASC, POIP.product_option_value_id, POIP.sort_order ASC
			");
		
			foreach ( $query->rows as $row ) {
					
				if ( $row['product_option_value_id'] == 0 ) {
					$row['product_option_value_id'] = -$row['product_option_id']; // display image when the options is not selected ("no value"), leave pov_id=0 does not fit, it should be unique
				} elseif ( $row['product_option_value_id'] == -1 ) {
					$row['product_option_value_id'] = $row['pov_product_option_value_id']; // display image if any option value is selected
				}
					
				if ( $this->existsImageFile($row['image']) ) {
					$row['image'] = $this->applyExternalWatermarkToImage($product_id, $row['image'], true);
					$existing_images[] = $row;
				}
			}
			
			$this->setCache(__FUNCTION__, $cache_key, $existing_images);
		}
		return $this->getCache(__FUNCTION__, $cache_key);
	}
  
	// if other sort order is needed, it should be by option values (may be needed for categories pages)
	public function getProductOptionImages($product_id, $return_table=false, $stock_control=true) {
		
		$poip_global_settings = $this->getSettings();
		$stock_control = $stock_control && empty($poip_global_settings['img_load_outofstock']);
    
		$cache_key = json_encode(func_get_args());
	
		if ( !$this->hasCache(__FUNCTION__, $cache_key) ) {
			
			$images = array();
				
			if (!$this->liveopencart_ext_poip->installed()) return $images;
			
			$language_id = (int)$this->config->get('config_language_id');
			
			$poip_settings = $this->liveopencart_ext_poip->getProductSettings($product_id);
			
			$cache_key_additonal = json_encode(array($product_id, $stock_control)).'_all_product_option_images';
			
			if ( !$this->hasCache(__FUNCTION__, $cache_key_additonal) ) {
				
				$all_product_option_images = $this->getOptionImagesBasicData($product_id);
				
				$existing_images = $this->getProductOptionImagesBasicData($product_id, $stock_control);
				
				$all_product_option_images = array_merge($all_product_option_images, $existing_images);
			
				$this->setCache(__FUNCTION__, $cache_key_additonal, $all_product_option_images);
			}
			$all_product_option_images = $this->getCache(__FUNCTION__, $cache_key_additonal);
			
			// use first image as icon/thumb (if needed)
			$thumbs = array();
			foreach ($all_product_option_images as $row) {
				if (!isset($thumbs[$row['product_option_value_id']])) {
					if ( isset($poip_settings[$row['product_option_id']]['img_first']) && $poip_settings[$row['product_option_id']]['img_first'] ) {
						$thumbs[$row['product_option_value_id']] = $row['image'];
					} else {
						$thumbs[$row['product_option_value_id']] = $row['thumb'];
					}
				}
			}
			
			if ( $return_table === 'table_by_po' ) {
				foreach ($all_product_option_images as $row) {
					if (!isset($images[$row['product_option_id']])) {
						$images[$row['product_option_id']] = array();
					}
	
					$images[$row['product_option_id']][] = array(
						'product_option_value_id'=>$row['product_option_value_id'],
						'image'=>$row['image'],
						'thumb'=>( (isset($thumbs[$row['product_option_value_id']]) && $thumbs[$row['product_option_value_id']]) ? $thumbs[$row['product_option_value_id']] : 'no_image.jpg'),
						'srt'=>$row['sort_order'],
						'product_option_id'=>$row['product_option_id'],
						'option_id'=>$row['option_id'],
						'option_value_id'=>$row['option_value_id'],
						'option_name'=> (isset($row['option_name']) && $row['option_name']) ? $row['option_name'] : '',
						'value_name'=> (isset($row['value_name']) && $row['value_name']) ? $row['value_name'] : '',
					);
				}
				
				foreach ($images as &$po_images) {
					usort($po_images, function($a, $b){
						return $a['srt']-$b['srt'];
					});
				}
				unset($po_images);
				
				$this->setCache(__FUNCTION__, $cache_key, $images);
				return $this->getCache(__FUNCTION__, $cache_key);
				//return $images;
			}
		
			if ($return_table) {
				$this->setCache(__FUNCTION__, $cache_key, $all_product_option_images);
				return $this->getCache(__FUNCTION__, $cache_key);
				//return $all_product_option_images;
			}
		
			foreach ($all_product_option_images as $row) {
				if (!isset($images[$row['product_option_id']])) {
					$images[$row['product_option_id']] = array();
				}
				if (!isset($images[$row['product_option_id']][$row['product_option_value_id']])) {
					$images[$row['product_option_id']][$row['product_option_value_id']] = array();
				}
				$images[$row['product_option_id']][$row['product_option_value_id']][] = array(  'image'=>$row['image']
																							  , 'thumb'=>( (isset($thumbs[$row['product_option_value_id']]) && $thumbs[$row['product_option_value_id']]) ? $thumbs[$row['product_option_value_id']] : 'no_image.jpg')
																							  , 'srt'=>$row['sort_order']
																							  , 'product_option_id'=>$row['product_option_id']
																							  , 'option_id'=>$row['option_id']
																							  , 'option_value_id'=>$row['option_value_id']
																							  , 'option_name'=> (isset($row['option_name']) && $row['option_name']) ? $row['option_name'] : ''
																							  , 'value_name'=> (isset($row['value_name']) && $row['value_name']) ? $row['value_name'] : ''
																							  );
			}
			
			$this->setCache(__FUNCTION__, $cache_key, $images);
		}
		
		return $this->getCache(__FUNCTION__, $cache_key);
		//return $images;
    
	}
  
	public function getProductOptionImagesByValues($product_id) {
    
		$images = array();
		
		if (!$this->liveopencart_ext_poip->installed()) return $images;
		
		$product_images = $this->getProductOptionImages($product_id);
		foreach ($product_images as $product_option_id => $po_images) {
		  
			if ($po_images && is_array($po_images)) {
				foreach ($po_images as $product_option_value_id => $pov_images) {
					$images[$product_option_value_id] = $pov_images;
				}
			}
		  
		}
		
		return $images;
    
	}
	
	protected function getArrayColumn($array, $subkey) { // old PHP does not have the function array_column
		$result = array();
		foreach ( $array as $key => $val ) {
			$result[$key] = isset($val[$subkey]) ? $val[$subkey] : null;
		}
		return $result;
	}
	
	protected function getArrayValueBySubKeyValue($array, $subkey, $subval) {
		$key = array_search($subval, $this->getArrayColumn($array, $subkey));
		if ( $key !== false ) {
			return $array[$key];
		}
	}
  
	// $product_images_old - standard product images array
	// $product_id
	// $product_images - empty array
	public function addOptionImagesToProductImages($product_images_old, $product_id, $product_images, $sizes=array()) {
		
		$poip_settings = $this->getSettings();
		
		$product_images = array();
		$added_images = array();
			
		$pov_images = $this->getProductOptionImages($product_id, true);
		
		if ( isset($poip_settings['options_images_edit']) && $poip_settings['options_images_edit'] == 1 ) {
			// add in product images order
				  
			foreach ($product_images_old as $image_old) {
			  
				$image_is_added_by_option = false;
				foreach ($pov_images as $pov_image) {
							
					if ($image_old['image'] == $pov_image['image']) {
								  
						$image_is_added_by_option = true;
					  
						if ( !in_array($pov_image['image'], $added_images) ) {
							$product_image = $image_old;
							$product_image['sort_order'] = $pov_image['sort_order'];
							$product_images[] = $product_image;
							$added_images[] = $pov_image['image'];
						}
								  
						foreach ( $product_images as &$image ) {
							if ($image['image'] == $pov_image['image']) {
								
								$po_id = $pov_image['product_option_id'];
								$pov_id = $pov_image['product_option_value_id'];
								
								if (!isset($image['product_option_id'])) $image['product_option_id'] = array();
								if ( !isset($image['product_option_value_id']) || !is_array($image['product_option_value_id']) ) $image['product_option_value_id'] = array();
								if (!in_array($po_id, $image['product_option_id'])) {
									$image['product_option_id'][] = $po_id;
								}
								
								if (!isset($image['po_ids_pov_ids'])) {
									$image['po_ids_pov_ids'] = [];
								}
								if (!isset($image['po_ids_pov_ids'][$po_id])) {
									$image['po_ids_pov_ids'][$po_id] = [];
								}
								$image['po_ids_pov_ids'][$po_id][] = $pov_id;
								
								
								if ( !in_array($pov_id, $image['product_option_value_id']) ) {
									$image['product_option_value_id'][] = $pov_id;
									
									if ( !$this->getThemeSetting('use_default_additional_image_title') ) {
										if (isset($pov_image['option_name']) && $pov_image['option_name'] && isset($pov_image['value_name']) && $pov_image['value_name']) {
											if (!isset($image['title'])) {
												$image['title'] = '';
											}
											$image['title'] = trim($image['title']."\n".$pov_image['option_name'].": ".$pov_image['value_name']);
										}
									}
								}
								break;
							}
						}
						unset($image);
					}
				}
				if ( !$image_is_added_by_option ) {
					$product_images[] = $image_old;
					$added_images[] = $image_old['image'];
				}
			}
		}
		
		foreach ($pov_images as $pov_image) {
		  
			if ( !in_array($pov_image['image'], $added_images) ) {
				$product_image = $this->getArrayValueBySubKeyValue($product_images_old, 'image', $pov_image['image']);
				if ( !$product_image ) {
					$product_image = array('product_id'=>$product_id, 'image'=>$pov_image['image']);
				}
				$product_image['sort_order'] = $pov_image['sort_order'];
				$product_images[] = $product_image;
				$added_images[] = $pov_image['image'];
			}
			foreach ($product_images as &$image) {
				if ($image['image'] == $pov_image['image']) {
					
					$po_id = $pov_image['product_option_id'];
					$pov_id = $pov_image['product_option_value_id'];
					
					if (!isset($image['product_option_id'])) $image['product_option_id'] = array();
					if ( !isset($image['product_option_value_id']) || !is_array($image['product_option_value_id']) ) $image['product_option_value_id'] = array();
					if (!in_array($po_id, $image['product_option_id'])) {
						$image['product_option_id'][] = $po_id;
					}
					
					if (!isset($image['po_ids_pov_ids'])) {
						$image['po_ids_pov_ids'] = [];
					}
					if (!isset($image['po_ids_pov_ids'][$po_id])) {
						$image['po_ids_pov_ids'][$po_id] = [];
					}
					$image['po_ids_pov_ids'][$po_id][] = $pov_id;
					
					if ( !in_array($pov_id, $image['product_option_value_id']) ) {
						$image['product_option_value_id'][] = $pov_id;
						
						if (isset($pov_image['option_name']) && $pov_image['option_name'] && isset($pov_image['value_name']) && $pov_image['value_name']) {
							if (!isset($image['title'])) {
								$image['title'] = '';
							}
							$image['title'] = trim($image['title']."\n".$pov_image['option_name'].": ".$pov_image['value_name']);
						}
					}
					
					if (!empty($pov_image['by_any_value']) ) {
						if (!isset($image['product_option_value_id_any'])) {
							$image['product_option_value_id_any'] = [];
						}
						if ( !in_array($pov_id, $image['product_option_value_id_any']) ) {
							$image['product_option_value_id_any'][] = $pov_id;
						}
					}
					
					break;
				}
			}
			unset($image);
		  
		}
    
    
		//foreach ($product_images_old as $product_image) {
		foreach (array_reverse($product_images_old) as $product_image) {
		  if (!in_array($product_image['image'], $added_images)) {
			array_unshift ($product_images, $product_image);
		  }
		}
		
		$poip_product_settings = $this->liveopencart_ext_poip->getProductSettings($product_id);
		
		if ($this->config->get('video_product_page_setting')) { // comp with Video Product Page by xprolance
			$video_product_page_setting = $this->config->get('video_product_page_setting');
			if (isset($video_product_page_setting['first']) && !$video_product_page_setting['first']) { // it may be placing videos to the end of the image list
				$videos_product_page_to_end = array_filter($product_images, function($result){
					return !empty($result['video']);
				});
				
				$product_images = array_values(array_filter($product_images, function($result){
					return empty($result['video']);
				}));
				if (!empty($videos_product_page_to_end)) {
					foreach ($videos_product_page_to_end as $video_product_page_to_end) {
						$product_images[] = $video_product_page_to_end;
					}
				}
			}
		}
		
		$results = array();
		foreach ($product_images as &$result) {
		  
			if (isset($result['product_option_id']) && is_array($result['product_option_id'])) {
				$show_image = false;
				foreach ($result['product_option_id'] as $product_option_id) {
					if (isset($poip_product_settings[$product_option_id]) && $poip_product_settings[$product_option_id]['img_use']) {
						$show_image = true;
						break;
					}
				}
			} else {
				$show_image = true;
			}
				
			if ( empty($sizes['popup_width']) || empty($sizes['popup_height']) ) {
				$result['popup'] = $this->image_resize($product_id, $result['image'], $this->getImageSizeSetting('popup_width'), $this->getImageSizeSetting('popup_height'), 'popup');
			} else {
				$result['popup'] = $this->image_resize($product_id, $result['image'], $sizes['popup_width'], $sizes['popup_height'], 'popup');
			}

			if ( empty($sizes['thumb_width']) || empty($sizes['thumb_height']) ) {
				$result['thumb'] = $this->image_resize($product_id, $result['image'], $this->getImageSizeSetting('additional_width'), $this->getImageSizeSetting('additional_height'), 'thumb');
			} else {
				$result['thumb'] = $this->image_resize($product_id, $result['image'], $sizes['thumb_width'], $sizes['thumb_height'], 'thumb');
			}
			
			if ( empty($sizes['main_width']) || empty($sizes['main_height']) ) {
				$result['main'] = $this->image_resize($product_id, $result['image'], $this->getImageSizeSetting('thumb_width'), $this->getImageSizeSetting('thumb_height'), 'main');
			} else {
				$result['main'] = $this->image_resize($product_id, $result['image'], $sizes['main_width'], $sizes['main_height'], 'main');
			}
			
			$option_thumb_sizes = $this->getThemeSetting('option_thumb_sizes') ? $this->getThemeSetting('option_thumb_sizes') : array('width'=>50, 'height'=>50);
				
			$result['option_thumb'] = $this->image_resize($product_id, $result['image'], $option_thumb_sizes['width'], $option_thumb_sizes['height'], 'thumb');
			
			if ( $this->hasThemeMethod('addImageProperties') ) {
				$result = $this->getThemeMethods()->addImageProperties($result);
			}
		  
			if ($show_image) {  
				$results[] = $result;
			}
		}
		unset($result);
			
		$results = $this->addVideoIfNeeded($product_images_old, $results);
		
		return array('results'=>$results, 'product_images'=>$product_images);
      
	}
	
	// some other modules may require 'video' element in image array
	protected function addVideoIfNeeded($product_images_old, $results) {
		
		// let's think that it's always needed
		$videoNeeded = true;

		if ($videoNeeded) {
			foreach ($results as &$result) {
				if (!isset($result['video'])) {
					$result['video'] = '';
				}
			}
			unset($result);
		}
		return $results;
	}
  
	public function image_resize($product_id, $image, $width, $height, $image_type='') {
		
		if ( $this->hasThemeMethod('resize') ) {
			return $this->getThemeMethods()->resize($image, $width, $height, $image_type);
		}
		
		if ( class_exists('ModelToolImageOriginal') && class_exists('ImageWatermark') ) { // Watermark Pro 3
			if ( $image_type == 'popup' ) {
				return $this->model_tool_image->resize($image, $width, $height, 'product_popup');
			} elseif ( $image_type == 'thumb' || $image_type == 'main' ) {
				return $this->model_tool_image->resize($image, $width, $height, 'product_thumb');
			}
		}
		
		return $this->model_tool_image->resize($image, $width, $height);
    
	}
  
	public function getProductOptionsIdsWithImages($results) {
	  
		$ids = array();
		
		foreach ($results as $result) {
			if (isset($result['product_option_id']) && is_array($result['product_option_id'])) {
				foreach ($result['product_option_id'] as $product_option_id) {
					if (!in_array($product_option_id, $ids)) {
						$ids[] = $product_option_id;
					}
				}
			}
		}
		
		return $ids;
	}
	
	public function getCategoryImagesForController($product_id, $module_setting=false) { // returns images only if it is needed (old style option images in product lists displaying - not trough additional ajax call)
		// always return option images in hope to do not make extra ajax calls
		return $this->getCategoryImages($product_id, $module_setting, true);
	}
	
	protected function getImageSizeSetting($setting_name) {
		return $this->config->get( 'theme_' . $this->config->get('config_theme') . '_image_'.$setting_name );
	}
	
	protected function getPOVImagesAssocByPOIdAndPOVId($product_id) {
		$query = $this->db->query("
			SELECT POV.*, OV.image
			FROM `".DB_PREFIX."product_option_value` POV, `".DB_PREFIX."option_value` OV
			WHERE POV.product_id = ".(int)$product_id."
			  AND POV.option_value_id = OV.option_value_id
			");
		$povs_assoc = [];
		foreach ($query->rows as $pov) {
			if ( trim($pov['image']) ) {
				$po_id = $pov['product_option_id'];
				$pov_id = $pov['product_option_value_id'];
				if (!isset($povs_assoc[$po_id])) {
					$povs_assoc[$po_id] = [];
				}
				$povs_assoc[$po_id][$pov_id] = $pov['image'];
			}
		}
		return $povs_assoc;
	}
  
	public function getCategoryImages($product_id, $module_setting=false, $return_ordered=false) { // $return_ordered currently not used
    
		if (!$this->liveopencart_ext_poip->installed()) return false;
		
		$category_images = array();
		
		$product_settings = $this->liveopencart_ext_poip->getProductSettings($product_id);
		
		$has_category_images = array_filter($product_settings, function($po_settings){
			return !empty($po_settings['img_category']);
		});
    
		if ( !$this->model_tool_image ) {
			$this->load->model('tool/image');
		}
		
		if ( !empty($module_setting) && isset($module_setting['width']) && isset($module_setting['height'])  ) {
				
			$image_product_width = $module_setting['width'];
			$image_product_height = $module_setting['height'];  
		} elseif ($module_setting == "related_products") {
			$image_product_width = $this->getImageSizeSetting('related_width');
			$image_product_height = $this->getImageSizeSetting('related_height');
		} else {
			$image_product_width = $this->getImageSizeSetting('product_width');
			$image_product_height = $this->getImageSizeSetting('product_height');
		}
		
		$poip_settings = $this->config->get('poip_module');
		
		if ( !empty($poip_settings['custom_thumb_width']) && (int)$poip_settings['custom_thumb_width']>0 && !empty($poip_settings['custom_thumb_height']) && (int)$poip_settings['custom_thumb_height']>0 ) {
			
			$icon_width = (int)$poip_settings['custom_thumb_width'];
			$icon_height = (int)$poip_settings['custom_thumb_height'];
			
		} elseif ( $this->getThemeSetting('product_list_thumb_sizes') ) {
			$product_list_thumb_sizes = $this->getThemeSetting('product_list_thumb_sizes');
			$icon_width = $product_list_thumb_sizes['width'];
			$icon_height = $product_list_thumb_sizes['height'];
		} else {
			// base image size used for calculation is 120 (128 with magins/paddings), icon/thumb size is 24 (with margins/paddings 32)
			// (120)/4-6=24
			$icon_width = round(($image_product_width)/4-6);
			$icon_height = round(($image_product_height)/4-6);
		}
		
		$images = $this->getProductOptionImages($product_id, 'table_by_po');
		
		if ( !$this->model_catalog_product ) {
			$this->load->model('catalog/product');
		}
    
		// collect standard images of option values
		$basic_option_images = $this->getPOVImagesAssocByPOIdAndPOVId($product_id);
		//$basic_option_images = array();
		//$product_options = $this->model_catalog_product->getProductOptions($product_id);
		//foreach ( $product_options as $product_option ) {
		//	$product_option_id = $product_option['product_option_id'];
		//	$basic_option_images[$product_option_id] = array();
		//	if ( !empty($product_option['product_option_value']) ) {
		//		foreach ( $product_option['product_option_value'] as $product_option_value ) {
		//			if ( trim($product_option_value['image']) ) {
		//				$product_option_value_id = $product_option_value['product_option_value_id'];
		//				$basic_option_images[$product_option_id][$product_option_value_id] = $product_option_value['image'];
		//			}
		//		}
		//	}
		//}
		
		
		$added_pov_ids = array();
		
		foreach ($images as $product_option_id => $po_images) {
        
			if ( !empty($product_settings[$product_option_id]['img_category']) ) {
				
				$imgs_by_povs = array();
				foreach ($po_images as $po_image) {
					$product_option_value_id = $po_image['product_option_value_id'];
					if ( !isset($imgs_by_povs[$product_option_value_id]) ) {
						$imgs_by_povs[$product_option_value_id] = array();
					}
					$imgs_by_povs[$product_option_value_id][] = $po_image;
				}
				
				foreach ($po_images as $po_image) { // go through images in the original order
					$product_option_value_id = $po_image['product_option_value_id'];
					
					if ( in_array($product_option_value_id, $added_pov_ids) ) {
						continue;
					}
				
					if (!isset($category_images[$product_option_id])) {
						$category_images[$product_option_id] = array();
					}
					
					$pov_images = $imgs_by_povs[$product_option_value_id];
					$pov_image = $pov_images[0];
					
					$option_icon = '';
					if ( !empty($basic_option_images[$product_option_id][$product_option_value_id]) ) {
						$option_icon = $basic_option_images[$product_option_id][$product_option_value_id];
					}
					if ( !$option_icon || !is_file(DIR_IMAGE.$option_icon) || ( !empty($product_settings[$product_option_id]['img_first']) && $product_settings[$product_option_id]['img_first'] == 1 ) ) {
						$option_icon = $pov_image['image'];
						//$option_icon = $image_pov['image'];
					}
											
					$category_image_data = array(
						'icon'=>$this->image_resize($product_id, $option_icon, $icon_width, $icon_height, 'icon'),
						'thumb'=>$this->image_resize($product_id, $pov_image['image'], $image_product_width, $image_product_height, 'product'),
						//'thumb'=>$this->image_resize($product_id, $image_pov['image'], $image_product_width, $image_product_height),
						'product_option_value_id'=>$product_option_value_id,
						'width' => $icon_width,
						'height' => $icon_height,
					);
					if ( $this->hasThemeMethod('getProductListImageExtras') ) {
						$category_image_data = array_merge($category_image_data, $this->getThemeMethods()->getProductListImageExtras($product_id, $product_option_value_id, $pov_image['image'], $image_product_width, $image_product_height));
					}
					
					if (isset($pov_image['option_name']) && $pov_image['option_name'] && isset($pov_image['value_name']) && $pov_image['value_name'] ) {
						if ( $this->getThemeSetting('use_only_option_value_names_as_titles_for_category_images') ) {
							$category_image_data['title'] = "".$pov_image['value_name'];
						} else {
							$category_image_data['title'] = "".$pov_image['option_name'].": ".$pov_image['value_name'];
						}
					}
					
					if ( $this->getThemeSetting('use_second_option_thumbs_for_product_lists') ) {
						if ( count($pov_images) > 1 ) {
							$category_image_data['thumb_second'] = $this->image_resize($product_id, $pov_images[1]['image'], $image_product_width, $image_product_height);
						}
					}
				
					if ($return_ordered) {
						$category_images[$product_option_id][] = $category_image_data;
					} else {
						$category_images[$product_option_id][$product_option_value_id] = $category_image_data;
					}
					$added_pov_ids[] = $product_option_value_id;
				
				}   
			}
		}
		return $category_images;
	}
	
	public function getProductCartImages($product_id, $option_data) { // comp for older code
		
		return $this->liveopencart_ext_poip->getProductCartOrderImages($product_id, $option_data); 
		
		//$result_images = array();
		//
		//if ( $this->liveopencart_ext_poip->installed() ) {
		//	$selected_product_option = array();
		//	$selected_product_option_value = array();
		//	foreach ($option_data as $option_value_data) {
		//		if (!in_array($option_value_data['product_option_id'], $selected_product_option)) {
		//			$selected_product_option[] = $option_value_data['product_option_id'];
		//		}
		//		if (!in_array($option_value_data['product_option_value_id'], $selected_product_option_value)) {
		//			$selected_product_option_value[] = $option_value_data['product_option_value_id'];
		//		}
		//	}
		//	
		//	$product_images = $this->getProductOptionImages($product_id);
		//	
		//	if ( count($product_images) > 0 ) {
		//		
		//		$product_settings = $this->liveopencart_ext_poip->getProductSettings($product_id);
		//		
		//		$cart_options = array();
		//		$filter_options = array();
		//		foreach ($product_images as $product_option_id => $product_option_images ) {
		//			
		//			if ( in_array($product_option_id, $selected_product_option) ) { // option value is selected
		//				
		//				$images_count = 0;
		//				
		//				foreach ($product_option_images as $product_option_value => $product_option_value_images) {
		//					$images_count = $images_count + count($product_option_value_images);
		//				}
		//				
		//				if ($images_count > 0) {
		//					if ( !empty($product_settings[$product_option_id]['img_cart']) ) {
		//						$cart_options[] = $product_option_id;
		//
		//					}
		//				}
		//			}
		//		}
		//		
		//		
		//		$images = false;
		//		$all_option_images = array();
		//		
		//		if ( $cart_options ) { 
		//		
		//			// for main image in the shopping cart always try to use filtering (get image relevant to all selected options)
		//			foreach ($product_images as $product_option_id => $product_option_images) {
		//				if ( in_array($product_option_id, $cart_options) ) {
		//					$current_images = array();
		//					foreach ( $product_images[$product_option_id] as $product_option_value_id => $product_option_value_images ) {
		//						if ( in_array($product_option_value_id, $selected_product_option_value) ) { // selected option value
		//							foreach ($product_option_value_images as $image_info) {
		//								if (!in_array($image_info['image'], $current_images)) {
		//									$current_images[] = $image_info['image'];
		//								}
		//							}
		//						} 
		//					}
		//					
		//					$all_option_images = array_values( array_unique( array_merge($all_option_images, $current_images) ) );
		//					
		//					if (count($current_images) > 0) {
		//						if ($images === false) {
		//							$images = $current_images;
		//						} else {
		//							$images = array_values(array_intersect($images, $current_images));
		//						}
		//					}
		//					
		//				}
		//			}
		//			
		//			if ( !$images ) {
		//				$images = $all_option_images;
		//			}
		//				
		//			$result_images = $images;
		//			
		//		}
		//		
		//	}
		//}
		//return $result_images;
	}
  
	public function getProductCartImage($product_id, $option_data, $image) { // comp for older code
		
		return $this->liveopencart_ext_poip->getProductCartOrderImage($product_id, $option_data, $image);  
		
		//if (!$this->liveopencart_ext_poip->installed()) {
		//	return $image;
		//}
		//
		//$images = $this->getProductCartImages($product_id, $option_data);
		//if ($images && count($images)>0) {
		//	$image = $images[0];
		//}
		//
		//return $image;
	  
	}
  
	protected function getFilterOptionsByFilterString($filter_str) {
		
		$options = array();
		$used_options = array();
		$filter_ids = array_map('intval', explode(',', $filter_str));
		
		if ( $filter_ids ) {
			
			$query = $this->db->query("
				SELECT OV.*
				FROM `".DB_PREFIX."filter` F
						,`".DB_PREFIX."option_value` OV
				WHERE F.poip_ocfilter_option_value_id = OV.option_value_id
					AND F.filter_id IN (".implode(',', $filter_ids).")
			");
			
			$options = array();
			foreach ( $query->rows as $row ) {
				if ( !isset($options[$row['option_id']]) ) {
					$options[$row['option_id']] = array();
				}
				$options[$row['option_id']][] = $row['option_value_id'];
			}
			
		}
		
		return $options;
	}
	
	protected function getOptionDataForProductByOptions($product_id, $options) {
		$option_data = array();
		
		$all_option_value_ids = array();
		foreach ( $options as $option_value_ids ) {
			$all_option_value_ids = array_merge($all_option_value_ids, $option_value_ids);
		}
		
		if ( $all_option_value_ids ) {
			
			$all_option_value_ids = array_map('intval', $all_option_value_ids);
			
			$query = $this->db->query("
				SELECT DISTINCT POV.*
				FROM `".DB_PREFIX."poip_option_image` POIP
						,`".DB_PREFIX."product_option_value` POV
				WHERE POIP.product_id = ".(int)$product_id."
					AND POV.product_option_value_id = POIP.product_option_value_id
					AND POV.option_value_id IN (".implode(',',$all_option_value_ids).")
			");
			foreach ( $query->rows as $row ) {
				$option_data[] = array(
					'product_option_id' => $row['product_option_id'],
					'product_option_value_id' => $row['product_option_value_id'],
				);
			}
		}
		
		return $option_data;
	}
	
	public function changeImagesInProductsByFilter($products, $filter_data) {
		
		if ( $this->liveopencart_ext_poip->installed() ) {
			if ( !empty($filter_data['filter_filter']) ) { // tt-like filter (string with filter ids)
				$options = $this->getFilterOptionsByFilterString($filter_data['filter_filter']);
			} elseif ( !empty($filter_data['options']) && is_array($filter_data['options']) ) { // j3-like filter (array(o_id=>array(ov_ids)))
				$options = $filter_data['options'];
			}
			
			if ( !empty($options) ) {
				foreach ( $products as &$product ) {
					$option_data = $this->getOptionDataForProductByOptions($product['product_id'], $options);
					if ( $option_data ) {
						$images = $this->getProductCartImages($product['product_id'], $option_data);
						if ( $images ) {
							$product['image'] = $images[0];
							$product['poip_filtered_images'] = array_slice($images, 1);
						}
						$product['poip_ov'] = $option_data[0]['product_option_value_id'];
					}
				}
				unset($product);
			}
		}
		
		return $products;
	}
  
}