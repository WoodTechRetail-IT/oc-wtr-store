<?php

class ControllerExtensionModuleUniMegamenu extends Controller {
    public function index() {
        $this->load->language('extension/module/uni_megamenu');

        $this->load->model('extension/module/uni_megamenu');
        $this->load->model('tool/image');
        $this->load->model('tool/imagequadr');

        if ($this->config->get('module_uni_megamenu_status')) {
	        if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && ($this->request->server['REQUEST_METHOD'] == 'POST')) {
		        if ($this->config->get('module_uni_megamenu_mobile_st_categories')) {
			        $data['standart_menu'] = $this->load->controller('common/menu', ['mobile' => 1]);
		        }

		        if ($this->config->get('module_uni_megamenu_mobile_categories')) {
			        $data['uni_megamenu_mobile_categories'] = true;
		        }

		        if ($this->config->get('module_uni_megamenu_brands')) {
			        $data['uni_megamenu_brands'] = true;
		        }

		        if ($this->config->get('module_uni_megamenu_informations')) {
			        $data['uni_megamenu_informations'] = true;
		        }

		        if ($this->config->get('module_uni_megamenu_links')) {
			        $data['uni_megamenu_links'] = true;
		        }

		        if ($this->config->get('module_uni_megamenu_blog')) {
			        $data['uni_megamenu_blog'] = true;
		        }

                if ($this->config->get('module_uni_megamenu_mobile_width')) {
			        $data['mobile_width'] = $this->config->get('module_uni_megamenu_mobile_width');
			    } else {
			        $data['mobile_width'] = '280';
		        }
				
                if ($this->config->get('module_uni_megamenu_level_open') == 3) {
			        $data['level_open'] = 'none';
				} else if ($this->config->get('module_uni_megamenu_level_open') == 2) {
			        $data['level_open'] = 'expand';			
			    } else {
			        $data['level_open'] = 'overlap';
		        }

                if ($this->config->get('module_uni_megamenu_mobile_position') == 2) {
			        $data['mobile_position'] = 'right';		
			    } else {
			        $data['mobile_position'] = 'left';
		        }

			    $data['mobile'] = 1;
				//module_uni_megamenu_menu_status
		    } elseif ($this->config->get('module_uni_megamenu_menu_status')) {
			    $data['standart_menu'] = $this->load->controller('common/menu');
		    }

	        if(isset($this->request->server['HTTP_ACCEPT']) && strpos($this->request->server['HTTP_ACCEPT'], 'webp')) {
				$oct_webP = 1 . '-' . $this->session->data['currency'];
			} else {
				$oct_webP = 0 . '-' . $this->session->data['currency'];
			}

            $cat_title = $this->config->get('module_uni_megamenu_title');

			$data['text_category'] = (isset($cat_title[(int)$this->config->get('config_language_id')]) && $cat_title[(int)$this->config->get('config_language_id')]) ? $cat_title[(int)$this->config->get('config_language_id')] : $this->language->get('text_category');
			
			$cat_mobile_title = $this->config->get('module_uni_megamenu_mobile_title');

			$data['uni_mobile_menu'] = (isset($cat_mobile_title[(int)$this->config->get('config_language_id')]) && $cat_mobile_title[(int)$this->config->get('config_language_id')]) ? $cat_mobile_title[(int)$this->config->get('config_language_id')] : 'Меню';

	        $data['items'] = $this->cache->get('unistore.megamenu.' . (int) $this->config->get('config_language_id') . '.' . (int) $this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . $oct_webP);

	        if (isset($data['items']) && empty($data['items'])) {
	            $results = $this->model_extension_module_uni_megamenu->getMegamenus();

	            foreach ($results as $result) {
	                if ($result['image']) {
	                    $image = $this->model_tool_image->resize($result['image'], 35, 35);
	                } else {
	                    $image = false;
	                }

	                $childrens = [];

	                if ($result['item_type'] == 2) {
	                    $children_data = $this->model_extension_module_uni_megamenu->getMegamenuCategory($result['megamenu_id']);

	                    if ($children_data) {
		                    $this->load->model('catalog/category');

		                    foreach ($children_data as $category_id) {
		                        $category_info = $this->model_catalog_category->getCategory($category_id);

		                        if ($category_info) {
		                            if ($category_info['image'] && is_file(DIR_IMAGE . $category_info['image'])) {
		                                $category_image = $this->model_tool_imagequadr->resize($category_info['image'], $result['img_width'], $result['img_height']);
		                            } else {
		                                $category_image = $this->model_tool_image->resize('no_image.png', $result['img_width'], $result['img_height']);
		                            }

		                            $sub_categories = [];

		                            if ($result['sub_categories']) {
		                                $category_children = $this->model_catalog_category->getUNImenuCategories($category_id, $result['limit_item']);

		                                foreach ($category_children as $child) {
		                                    $sub_categories[] = [
		                                        'name' => $child['name'],
		                                        'href' => $this->url->link('product/category', 'path=' . $category_id . '_' . $child['category_id'])
		                                    ];
		                                }
		                            }

		                            $childrens[] = [
		                                'category_id' => $category_info['category_id'],
		                                'thumb' => $result['show_img'] ? $category_image : false,
		                                'name' => $category_info['name'],
		                                'children' => $sub_categories,
										'sort_order' => $category_info['sort_order'],
		                                'href' => $this->url->link('product/category', 'path=' . $category_info['category_id'])
		                            ];
		                        }
		                    }

		                    $i_sort_order = array();

		                    foreach ($childrens as $key => $value) {
		                        $i_sort_order[$key] = $value['sort_order'];
		                    }

		                    array_multisort($i_sort_order, SORT_ASC, $childrens);

	                    }
	                }

                    if ($result['item_type'] == 3) {
	                    $children_data = $this->model_extension_module_uni_megamenu->getMegamenuManufacturer($result['megamenu_id']);

	                    if ($children_data) {
		                    $this->load->model('catalog/manufacturer');

		                    foreach ($children_data as $manufacturer_id) {
		                        $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

		                        if ($manufacturer_info) {
		                            if ($manufacturer_info['image'] && is_file(DIR_IMAGE . $manufacturer_info['image'])) {
		                                $manufacturer_image = $this->model_tool_image->resize($manufacturer_info['image'], $result['img_width'], $result['img_height']);
		                            } else {
		                                $manufacturer_image = $this->model_tool_image->resize('no_image.png', $result['img_width'], $result['img_height']);
		                            }

		                            $childrens[] = [
		                                'manufacturer_id' => $manufacturer_info['manufacturer_id'],
		                                'sort_order' => $manufacturer_info['sort_order'],
		                                'thumb' => ($result['show_img']) ? $manufacturer_image : false,
		                                'name' => $manufacturer_info['name'],
		                                'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id'])
		                            ];
		                        }
		                    }

                            $i_sort_order = [];

		                    foreach ($childrens as $key => $value) {
		                        $i_sort_order[$key] = $value['name'];
		                    }

		                    array_multisort($i_sort_order, SORT_ASC, SORT_STRING, $childrens);
	                    }
	                }

	                if ($result['item_type'] == 4) {
	                    $children_data = $this->model_extension_module_uni_megamenu->getMegamenuProduct($result['megamenu_id']);

	                    if ($children_data) {
		                    $this->load->model('catalog/product');

		                    foreach ($children_data as $product_id) {
		                        $product_info = $this->model_catalog_product->getProduct($product_id);

		                        if ($product_info) {
		                            if ($product_info['image'] && is_file(DIR_IMAGE . $product_info['image'])) {
		                                $product_image = $this->model_tool_image->resize($product_info['image'], $result['img_width'], $result['img_height']);
		                            } else {
		                                $product_image = $this->model_tool_image->resize('no_image.png', $result['img_width'], $result['img_height']);
		                            }

		                            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
		                                $product_price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
		                            } else {
		                                $product_price = false;
		                            }

		                            if ((float) $product_info['special']) {
		                                $ptoduct_special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
		                            } else {
		                                $ptoduct_special = false;
		                            }

		                            $childrens[] = [
		                                'product_id' => $product_info['product_id'],
		                                'sort_order' => $product_info['sort_order'],
		                                'thumb' => ($result['show_img']) ? $product_image : false,
		                                'name' => $product_info['name'],
		                                'price' => $product_price,
		                                'special' => $ptoduct_special,
		                                'href' => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
		                            ];
		                        }
		                    }
	                    }
	                }

	                if ($result['item_type'] == 5) {
	                    $children_data = $this->model_extension_module_uni_megamenu->getMegamenuInformation($result['megamenu_id']);

	                    if ($children_data) {
		                    $this->load->model('catalog/information');

		                    foreach ($children_data as $information_id) {
		                        $information_info = $this->model_catalog_information->getInformation($information_id);
		                        if ($information_info) {
		                            $childrens[] = [
		                                'href' => $this->url->link('information/information', 'information_id=' . $information_id),
		                                'title' => $information_info['title'],
		                                'sort_order' => $information_info['sort_order']
		                            ];
		                        }
		                    }

		                    $i_sort_order = array();

		                    foreach ($childrens as $key => $value) {
		                        $i_sort_order[$key] = $value['sort_order'];
		                    }

		                    array_multisort($i_sort_order, SORT_ASC, $childrens);
	                    }
	                }

                    if ($result['item_type'] == 8) {
		            $query = $this->db->query("SHOW TABLES LIKE '".DB_PREFIX."uni_article'");
	                if (!$query->num_rows) {
	                $data['uni_blogsettings_status'] = false;
                    } else {  
	                $data['uni_blogsettings_status'] = true;
		            $uni_blogsettings_status = true;
	                }
					
					if (isset($uni_blogsettings_status)) {
	                    $children_data = $this->model_extension_module_uni_megamenu->getMegamenuBlogCategory($result['megamenu_id']);

	                    if ($children_data) {
		                    $this->load->model('blog/category');

		                    foreach ($children_data as $blog_category_id) {
		                     //   $category_info = $this->model_catalog_category->getCategory($category_id);
							$blog_info = $this->model_blog_category->getCategory($blog_category_id);

		                      //  if ($category_info) {
							  if ($blog_info) {
		                            if ($blog_info['image'] && is_file(DIR_IMAGE . $blog_info['image'])) {
		                                $blog_image = $this->model_tool_image->resize($blog_info['image'], $result['img_width'], $result['img_height']);
		                            } else {
		                                $blog_image = $this->model_tool_image->resize('no_image.png', $result['img_width'], $result['img_height']);
		                            }

		                            $sub_categories = [];

		                            if ($result['sub_blogcategories']) {//		 //sub_blogcategories
		                               $category_children = $this->model_blog_category->getUNImenuBlogCategories($blog_category_id, $result['limit_item']);

		                                foreach ($category_children as $child) {
		                                    $sub_categories[] = [
		                                        'name' => $child['name'],
		                                    //    'href' => $this->url->link('product/category', 'path=' . $category_id . '_' . $child['category_id'])
											    'href' => $this->url->link('blog/category', 'blogpath=' . $blog_category_id . '_' . $child['blog_category_id'])
		                                    ];
		                                }
		                            }

		                            $childrens[] = [
		                                'blog_category_id' => $blog_info['blog_category_id'],
		                                'thumb' => $result['show_img'] ? $blog_image : false,
		                                'name' => $blog_info['name'],
		                                'children' => $sub_categories,
										'sort_order' => $blog_info['sort_order'],
		                                'href' => $this->url->link('blog/category', 'blogpath=' . $blog_info['blog_category_id'])
		                            ];
		                        }
		                    }

		                    $i_sort_order = array();

		                    foreach ($childrens as $key => $value) {
		                        $i_sort_order[$key] = $value['sort_order'];
		                    }

		                    array_multisort($i_sort_order, SORT_ASC, $childrens);

	                    }
	                }
                    }


	                $banner = (!empty($result['banner_title']) || $result['banner_text'] || $result['banner_link'] || $result['banner_button'] || $result['banner_image']) ? true : false;

	                $banner_image = $this->request->server['HTTPS'] ? $this->config->get('config_ssl') . 'image/' . $result['banner_image'] : $this->config->get('config_url') . 'image/' . $result['banner_image'];
					$uni_image = $this->request->server['HTTPS'] ? $this->config->get('config_ssl') . 'image/' . $result['uni_image'] : $this->config->get('config_url') . 'image/' . $result['uni_image'];
					//$result['uni_image'];

	                $data['items'][] = [
	                    'megamenu_id' => $result['megamenu_id'],
	                    'title' => $result['title'],
						//'uni_image' => $uni_image,
						'uni_image' => !empty($result['uni_image']) ? $uni_image : false,
	                    'image' => $image,
	                    'href' => ($result['link'] == "#" || empty($result['link'])) ? "javascript:void(0);" : $result['link'],
	                    'open_link_type' => $result['open_link_type'],
	                    'description' => $result['info_text'] ? $banner : false,
	                    'custom_html' => $result['custom_html'] ? html_entity_decode($result['custom_html'], ENT_QUOTES, 'UTF-8') : '',
						'custom_contacts' => $result['custom_contacts'] ? html_entity_decode($result['custom_contacts'], ENT_QUOTES, 'UTF-8') : '',
                        'custom_map' => $result['custom_map'] ? html_entity_decode($result['custom_map'], ENT_QUOTES, 'UTF-8') : '',
	                    'display_type' => $result['display_type'],
	                    'limit_item' => $result['limit_item'],
	                    'show_img' => $result['show_img'],
	                    'children' => $childrens,
	                    'item_type' => $result['item_type'],
	                    'banner_title' => $result['banner_title'],
	                    'banner_text' => $result['banner_text'],
	                    'banner_link' => ($result['banner_link'] == "#" || empty($result['banner_link'])) ? "javascript:void(0);" : $result['banner_link'],
	                    'banner_button' => $result['banner_button'],
	                    'banner_image' => !empty($result['banner_image']) ? $banner_image : false,
	                    'banner_image_color' => !empty($result['banner_image_color']) ? $result['banner_image_color'] : false,
	                    'banner_image_text_color' => !empty($result['banner_image_text_color']) ? $result['banner_image_text_color'] : false,
	                    'banner_image_title_color' => !empty($result['banner_image_title_color']) ? $result['banner_image_title_color'] : false,
	                    'banner_image_button_color' => !empty($result['banner_image_button_color']) ? $result['banner_image_button_color'] : false,
	                    'banner_image_link_color' => !empty($result['banner_image_link_color']) ? $result['banner_image_link_color'] : false,
	                    'banner_image_button_hover_color' => !empty($result['banner_image_button_hover_color']) ? $result['banner_image_button_hover_color'] : false,
	                    'banner_image_link_hover_color' => !empty($result['banner_image_link_hover_color']) ? $result['banner_image_link_hover_color'] : false,
	                ];
	            }

	            $this->cache->set('unistore.megamenu.' . (int) $this->config->get('config_language_id') . '.' . (int) $this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . $oct_webP, $data['items']);
	        }

	        if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && ($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['mobile']) && $this->request->post['mobile']) {
				$this->response->setOutput($this->load->view('extension/module/uni_megamenu', $data));
			} else {
				return $this->load->view('extension/module/uni_megamenu', $data);
			}
        } elseif (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && ($this->request->server['REQUEST_METHOD'] == 'POST')) {
	        $data['standart_menu'] = $this->load->controller('common/menu', ['mobile' => 1]);
	        $data['mobile'] = 1;
	        $data['deff'] = 1;
	        $this->response->setOutput($this->load->view('extension/module/uni_megamenu', $data));
        }
    }
}
