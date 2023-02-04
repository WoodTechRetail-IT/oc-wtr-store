<?php

//microdatapro 7.7 (info@microdata.pro)
require_once(DIR_SYSTEM . 'library/microdatapro.php');

class ControllerExtensionModuleMicrodataPro extends Controller {

	public function __construct($registry) {
		parent::__construct($registry);
		$this->mdp = new Microdatapro($this->registry);

		if ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == '1' || $_SERVER['HTTPS'])) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'))) {
			$this->host = $this->config->get('config_ssl');
		} else {
			$this->host = $this->config->get('config_url');
		}
	}

	public function index() {
		/************copyright**************/
		/*                                 */
		/*   site:  https://microdata.pro  */
		/*   email: info@microdata.pro     */
		/*                                 */
		/************copyright**************/
	}

	private function mdc($key) {
		$data = $this->config->get('module_microdatapro_' . $key);
		return $data;
	}

	//getLogo - функция получения логотипа (для поддержки мультилого)
		private function getLogo() {
			$logo = $this->config->get('config_logo');
			if(is_array($logo) && isset($logo[$this->config->get('config_language_id')])){
			  $logo = $logo[$this->config->get('config_language_id')];
			}

			return $logo;
		}
	//getLogo

	//company - функция разметки информации о компании
		public function company() {
			if($this->mdc('status') && $this->mdc('company') && $this->mdp->status()){
				$data['company_syntax']     = $this->mdc('company_syntax');
				$data['store_type']     		= $this->mdc('store_type')?$this->mdp->storeType($this->mdc('store_type')):'Store';
				$data['config_hcard']	    	= $this->mdc('hcard');
				$data['version'] 	        	= $this->mdp->moduleInfo('version');
				$data['code']			    			= $this->session->data['currency'];
				$data['organization_name']  = $this->mdp->clear($this->config->get('config_name'));
				$data['organization_url']   = $this->host;
				$data['organization_logo']  = $this->mdp->clearImage($this->host . "image/" . ($this->config->get('config_image')?$this->config->get('config_image'):$this->getLogo()));
				$data['organization_email'] = $this->mdc('email')?$this->mdc('email'):$this->config->get('config_email');
				$data['organization_phones']= $this->mdc('phones')?array_diff(array_map('trim', explode(",", $this->mdc('phones'))), array('')):false;
				$data['organization_groups']= $this->mdc('groups')?array_diff(array_map('trim', explode(",", $this->mdc('groups'))), array('')):false;
				$data['organization_map']		= trim($this->mdc('map'));

				$this->load->model('tool/image');
				$data['logo'] = $this->model_tool_image->resize($this->getLogo(), ($this->mdc('logosize_1')?$this->mdc('logosize_1'):144), ($this->mdc('logosize_2')?$this->mdc('logosize_2'):144));

				$data['organization_oh'] = array();
				foreach(array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday") as $i => $day){$i++;
					if($this->mdc('oh_'.$i)){
						$pre_data = explode("-", $this->mdc('oh_'.$i));
						if(isset($pre_data[0]) && isset($pre_data[1])){
							$data['organization_oh'][$day] = array('open' => trim($pre_data[0]),'close' => trim($pre_data[1]));
						}
					}
				}

				$data['organization_locations'] = $this->organization();

				$store_id = false; //multistore
				$front_stores = $this->mdc('front_stores');

				if($front_stores){
					foreach($front_stores as $result){
						$url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
						if (strpos($url, $result['url']) !== false) {
							if(!isset($data['organization_phones'])){$data['organization_phones'] = array();}
							if(!isset($data['organization_groups'])){$data['organization_groups'] = array();}
							if(!isset($data['organization_map'])){$data['organization_map'] = array();}
							$store_id = $result['store_id'];
							$data['organization_phones'] = $this->mdc('phones'.$store_id)?array_diff(array_map('trim', explode(",", $this->mdc('phones'.$store_id))), array('')):$data['organization_phones'];
							$data['organization_groups'] = $this->mdc('groups'.$store_id)?array_diff(array_map('trim', explode(",", $this->mdc('groups'.$store_id))), array('')):$data['organization_groups'];
							$data['organization_map']	= $this->mdc('map'.$store_id)?$this->mdc('map'.$store_id):$data['organization_map'];
							$data['organization_locations'] = $this->organization($store_id);
						}
					}
				}

				//3.0
				$data['organization_locations_json'] = array();
				foreach($data['organization_locations'] as $location_item){
					$data['organization_locations_json'][] = $location_item;
					break;
				}
				$data['count_organization_phones'] = 0;
				$data['count_organization_groups'] = 0;
				$data['count_organization_oh'] = 0;
				if($data['organization_phones']){
					$data['count_organization_phones'] = count($data['organization_phones']);
				}
				if($data['organization_groups']){
					$data['count_organization_groups'] = count($data['organization_groups']);
				}
				if($data['organization_oh']){
					$data['count_organization_oh'] = count($data['organization_oh']);
				}
				//3.0

				return $this->view("company", $data);
			}
		}
	//company

	//category - функция разметки категории
		public function category($microdatapro_data) {
			if($this->mdc('status') && $this->mdc('category') && $this->mdp->status()){
				$this->mdp->checkVariable($microdatapro_data, 'category_manufacturer');

				$data['version'] = $this->mdp->moduleInfo('version');
				$data['syntax']  = $this->mdc('category_syntax');
				$data['name'] = $this->mdp->clear($this->document->getTitle());
				$data['image'] = $this->mdp->clearImage($microdatapro_data['thumb']);
				if(!$data['image']){
					$data['image'] = $this->mdp->clearImage($this->host . 'image/' . ($microdatapro_data['microdatapro_data']['image']?$microdatapro_data['microdatapro_data']['image']:$this->getLogo()));
				}

				$data['description'] = trim($this->mdp->clear($microdatapro_data['description']));
				if(!$data['description']){
					$data['description'] = $this->mdp->clear($this->document->getDescription()?$this->document->getDescription():$data['name']);
				}

				//разметка фото товаров как галереи
					$data['images'] = false;
					if($this->mdc('category_gallery')){
						$data['author'] = $this->mdp->clear($this->config->get('config_name'));

						foreach($microdatapro_data['results'] as $product){
							$thumb_width = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
							$thumb_height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');
							if((int)$this->mdc('category_thumb_1') && (int)$this->mdc('category_thumb_2')){
								$thumb_width = (int)$this->mdc('category_thumb_1');
								$thumb_height = (int)$this->mdc('category_thumb_2');
							}

							$popup = $this->host . 'image/' . $product['image'];
							if((int)$this->mdc('category_popup_1') && (int)$this->mdc('category_popup_2')){
								$popup = $this->model_tool_image->resize($product['image'], (int)$this->mdc('category_popup_1'), (int)$this->mdc('category_popup_2'));
							}

							$data['images'][] = array(
								'name' => $this->mdp->clear($product['name']),
								'thumb' => $this->mdp->clearImage($this->model_tool_image->resize($product['image'], $thumb_width, $thumb_height)),
								'popup' => $this->mdp->clearImage($popup),
								'date_added' => date("Y-m-d", strtotime($product['date_added']))
							);
						}
					}
				//разметка фото товаров как галереи

				//разметка цен от и до
					$data['range'] = $this->mdc('category_range'); //разметка от и до
					$data['sku'] = $data['name'];
					$data['code'] = $this->session->data['currency'];
					$this->mdp->getMinMaxCategory($data);
				//разметка цен от и до

				//размечаем отзывы от и до - пока что только с первой страницы!!!
					$data['review'] = $this->mdc('category_review');
					if($data['review']){
						$data['rating_count'] = 0;
						$rating_summ = 0;
						foreach($microdatapro_data['results'] as $product){
							if($product['rating']){
								$data['rating_count']++;
								$rating_summ += $product['rating'];
							}
						}
						if($rating_summ && $data['rating_count']){
							$data['rating_value'] = round($rating_summ/$data['rating_count'], 2);
						}
						$data['review'] = $data['rating_count']; //если нет отзывов то не размечаем
					}
				//размечаем отзывы от и до

				$data['breadcrumbs'] = $this->breadcrumbs($microdatapro_data['breadcrumbs']);

				//3.0
				$data['count_images'] = 0;
				$data['count_breadcrumbs'] = 0;

				if(is_array($data['images']) && $data['images']){
					$data['count_images'] = count($data['images']);
				}
				if(is_array($data['breadcrumbs']) && $data['breadcrumbs']){
					$data['count_breadcrumbs'] = count($data['breadcrumbs']);
				}
				//3.0

				return $this->view("category_manufacturer", $data);
			}
		}
	//category

	//manufacturer - функция разметки производителя, пока что размечаются хлебные крошки
		public function manufacturer($microdatapro_data) {
			if($this->mdc('status') && $this->mdc('manufacturer') && $this->mdp->status()){
				$this->mdp->checkVariable($microdatapro_data, 'category_manufacturer');

				$data['version'] = $this->mdp->moduleInfo('version');
				$data['syntax']  = $this->mdc('manufacturer_syntax');
				$data['range'] = false;
				$data['review'] = false;
				$data['images'] = false;
				$data['breadcrumbs'] = $this->breadcrumbs($microdatapro_data['breadcrumbs']);
				//3.0
				$data['count_images'] = 0;
				$data['count_breadcrumbs'] = count($data['breadcrumbs']);
				//3.0
				return $this->view("category_manufacturer", $data);
			}
		}
	//manufacturer

	//product - функция разметки товара
		public function product($microdatapro_data) {
			if($this->mdc('status') && $this->mdc('product') && $this->mdp->status()){
				$this->mdp->checkVariable($microdatapro_data, 'product');

				$url = end($microdatapro_data['breadcrumbs']);
				$data['version']  	 = $this->mdp->moduleInfo('version');
				$data['code']	  	   = $this->session->data['currency'];
				$data['syntax']   	 = $this->mdc('product_syntax');
				$data['related']  	 = $this->mdc('product_related');
				$data['reviews']  	 = $this->mdc('product_reviews');
				$data['attribute']	 = $this->mdc('product_attribute');
				$data['name'] 		 	 = $this->mdp->clear($microdatapro_data['heading_title']?$microdatapro_data['heading_title']:$this->document->getTitle());
				$data['url']  		 	 = isset($url['href'])?$url['href']:$microdatapro_data['share'];
				$data['popup']		 	 = $this->mdp->clearImage($microdatapro_data['popup']);
				$data['thumb']		 	 = $this->mdp->clearImage($microdatapro_data['thumb']);
				$data['model'] 			 = $this->mdp->clear($microdatapro_data['model']);
				$data['author'] 		 = $this->mdp->clear($this->config->get('config_name'));
				$data['manufacturer']= $this->mdp->clear($microdatapro_data['manufacturer']);
				$data['description'] = trim($this->mdp->clear($microdatapro_data['description']));
				$data['date_added']  = date("Y-m-d", strtotime($microdatapro_data['microdatapro_data']['date_added']));

				$data['images'] = array();
				if($this->mdc('product_gallery')){
					foreach($microdatapro_data['images'] as $image){
						if(is_array($image)){
							$data['images'][] = array(
								'thumb' => $this->mdp->clearImage($image['thumb']),
								'popup' => $this->mdp->clearImage($image['popup'])
							);
						}
					}
				}

				$data['price'] = '';
				if(!$this->mdc('hide_price')){
					$microdatapro_data['special'] = str_replace(",",".",$microdatapro_data['special']);
					$microdatapro_data['price'] = str_replace(",",".",$microdatapro_data['price']);
					$data['price'] = (float)rtrim(preg_replace('/[^\d.]/', '', $microdatapro_data['special']?$microdatapro_data['special']:$microdatapro_data['price']), ".");

					$option_prices = array();
					if(!$data['price'] && $microdatapro_data['options']){
						foreach($microdatapro_data['options'] as $option){
							if(isset($option['product_option_value'])){
								foreach($option['product_option_value'] as $value){
									$option_price = (float)rtrim(preg_replace('/[^\d.]/', '', $value['price']), ".");
									$option_prices[$option_price] = $option_price;
								}
							}
						}
					}
					if($option_prices){$data['price'] = min($option_prices);}

					$roption_prices = array();
					if(isset($microdatapro_data['ro_prices']) && $microdatapro_data['ro_prices']){
						foreach($microdatapro_data['ro_prices'] as $rprice){
							$ro_price = $rprice['special']?$rprice['special']:$rprice['price'];
							$roption_prices[$ro_price] = $ro_price;
						}
						if($option_prices){$data['price'] = min($roption_prices);}
					}
				}

				if($this->mdc('product_in_stock')){ //если всегда в наличии
					$data['stock'] = 'InStock';
				}else{ //если нет настройки всегда в наличии
					//статус товара
					$stock_status_id = 0;
					$stock_status_id_query = $this->db->query("SELECT stock_status_id FROM `" . DB_PREFIX . "product` WHERE product_id = '" . $microdatapro_data['product_id'] . "'");
					if($stock_status_id_query->row['stock_status_id']){
						$stock_status_id = $stock_status_id_query->row['stock_status_id'];
					}
					//статус товара

					//статусы - статусы в разметке
					$microdatapro_stock_statuses = $this->mdc('stock_statuses');
					//статусы - статусы в разметке

					if($microdatapro_data['microdatapro_data']['quantity']){ //если товар в наличии
						$data['stock'] = 'InStock';
					}else{ //если товара нет в наличии
						if(isset($microdatapro_stock_statuses[$stock_status_id])){ //если проставлен статус в разметке на статус товара
							$data['stock'] = $microdatapro_stock_statuses[$stock_status_id];
						}
					}
				}

				$data['reviews'] = array();
				if($this->mdc('product_reviews')){
					$reviews_query = $this->db->query("SELECT author, date_added, rating, text FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$microdatapro_data['product_id'] . "' AND status = '1' ORDER BY date_added DESC");
					if($reviews_query->rows){
						$all_rating = 0;
						foreach($reviews_query->rows as $key => $review){
							$data['reviews'][$key+1] = array(
								'author'     => $this->mdp->clear($review['author']),
								'date_added' => date("Y-m-d", strtotime($review['date_added'])),
								'rating'     => $review['rating'],
								'text'       => $this->mdp->clear($review['text'])
							);
							$all_rating += $review['rating'];
						}
						$data['reviewCount'] = $reviews_query->num_rows;
						$data['rating'] = round($all_rating/$data['reviewCount'], 2);
					}
				}

				foreach(array('sku','upc','ean','isbn','mpn') as $item){
					$data[$item] = ($microdatapro_data['microdatapro_data'][$item] && $this->mdc(''.$item))?$this->mdp->clear($microdatapro_data['microdatapro_data'][$item]):false;
				}

				if(!$data['sku']){$data['sku'] = $data['model'];}
				if(!$data['mpn']){$data['mpn'] = $data['model'];}
				$data['price_valid'] = date('Y-m-d', strtotime('+1 years'));

				$data['attributes'] = array();
				if($this->mdc('product_attribute')){
					foreach($microdatapro_data['attribute_groups'] as $attribute_group){
						if(isset($attribute_group['attribute'])){
							foreach($attribute_group['attribute'] as $attribute){
								$data['attributes'][count($data['attributes'])+1] = array(
									'text' => $this->mdp->clear($attribute['text']),
									'name' => $this->mdp->clear($attribute['name'])
								);
							}
						}
					}
				}

				$data['products'] = array();
				if($this->mdc('product_related')){
					foreach($microdatapro_data['products'] as $related){
						$data['products'][count($data['products'])+1] = array(
							'name' => $this->mdp->clear($related['name']),
							'href' => $related['href'],
							'thumb'=> $related['thumb'],
							'price'=> $this->mdc('hide_price')?'':(float)rtrim(preg_replace('/[^\d.]/', '', $related['special']?str_replace(",",".",$related['special']):str_replace(",",".",$related['price'])), "."),
						);
					}
				}


				$data['category'] = false;
				$count = count($microdatapro_data['breadcrumbs'])-2;
				if($count && isset($microdatapro_data['breadcrumbs'][$count]['text'])){
					$data['category'] = $this->mdp->clear($microdatapro_data['breadcrumbs'][$count]['text']);
				}

				$data['breadcrumbs'] = array();
				if($this->mdc('product_breadcrumb')){
					$data['breadcrumbs'] = $this->breadcrumbs($microdatapro_data['breadcrumbs']);
				}

				//3.0
				$data['count_breadcrumbs'] = count($data['breadcrumbs']);
				$data['count_images'] = count($data['images']);
				$data['count_attributes'] = count($data['attributes']);
				$data['count_products'] = count($data['products']);
				$data['count_reviews'] = count($data['reviews']);
				//3.0

				return $this->view("product", $data);
			}
		}
	//product

	//information - функция разметки информации
		public function information($information) {
			if($this->mdc('status') && $this->mdc('information') && $this->mdp->status()){
				$this->mdp->checkVariable($information, 'information');
				$url = end($information['breadcrumbs']);

				$width = $height = 0;
				$logo = $this->getLogo();
				if($logo && is_file(DIR_IMAGE . $logo)){
					list($width, $height) = getimagesize(DIR_IMAGE . $logo);
				}

				$data['version'] 	   = $this->mdp->moduleInfo('version');
				$data['syntax']  	   = $this->mdc('information_syntax');
				$data['name'] 	 	   = $this->mdp->clear($information['heading_title']);
				$data['url']  	 	   = isset($url['href'])?$url['href']:'';
				$data['logo']    	   = $this->mdp->clearImage($this->host . "image/" . $this->getLogo());
				$data['author']  	   = $this->mdp->clear($this->config->get('config_name'));
				$data['author_url']  = HTTP_SERVER;
				$data['image_width'] = $width;
				$data['image_height']= $height;
				$data['date'] 		   = date('Y-m-d', filectime(DIR_SYSTEM . 'library/microdatapro.php'));
				$data['description'] = trim($this->mdp->clear($information['description']));
				$data['breadcrumbs'] = $this->breadcrumbs($information['breadcrumbs']);
				$data['organization']= $this->organization();
				$data['phones'] = $this->mdc('phones')?array_diff(array_map('trim', explode(",", $this->mdc('phones'))), array('')):false;

				//3.0
				$data['count_breadcrumbs'] = count($data['breadcrumbs']);
				//3.0

				return $this->view("information", $data);
			}
		}
	//information

	//tc_og - функция разметки twitter cards & open graph
		public function tc_og($tc_og) {
			if(($this->mdc('opengraph') || $this->mdc('twitter_account')) && $this->mdc('status') && $this->mdp->status()){
				$this->mdp->checkVariable($tc_og, 'tc_og');
				$url = end($tc_og['breadcrumbs']);

				if($this->mdc('opengraph_meta') && $tc_og['microdatapro_data']['meta_description']){
					$desc =$tc_og['microdatapro_data']['meta_description'];
					if(is_array($desc) && isset($desc[(int)$this->config->get('config_language_id')])){
						$desc = $desc[(int)$this->config->get('config_language_id')];
					}
					$description = $this->mdp->clear($desc);
				}elseif($tc_og['description']){
					$description = $this->mdp->mbCutString($this->mdp->clear($tc_og['description']), 290);
				}else{
					$description = $this->mdp->clear($tc_og['heading_title']);
				}

				$data['image'] = $this->mdp->clearImage($this->host . "image/" . ($tc_og['microdatapro_data']['image']?$tc_og['microdatapro_data']['image']:$this->getLogo()));

				$data['images'] = array();
				if(isset($tc_og['images']) && $tc_og['images']){
					foreach($tc_og['images'] as $image){
						$data['images'][$image['popup']] = $image['popup'];
					}
				}

				$data['twitter'] = $this->mdc('twitter_account');
				$data['opengraph'] = $this->mdc('opengraph');
				$data['twitter_account'] = $this->mdc('twitter_account');
				$data['version'] = $this->mdp->moduleInfo('version');
				$data['title'] = $this->mdp->clear($tc_og['heading_title']);
				$data['description'] = $description;
				$data['url'] = isset($url['href'])?$url['href']:'';
				$data['locale'] = strtolower($this->session->data['language']);
				$data['site_name'] = $this->config->get('config_name');
				$data['og_type'] = "business.business";
				$data['product_page'] = false;
				if(isset($this->request->get['route']) && $this->request->get['route'] == "product/product"){
					$data['og_type'] = "product";
					$data['product_page'] = true;
					$data['product_manufacturer'] = isset($tc_og['manufacturer'])?$tc_og['manufacturer']:'';
					$data['product_category'] = false;
					$count = count($tc_og['breadcrumbs'])-2;
					if($count && isset($tc_og['breadcrumbs'][$count]['text'])){
						$data['product_category'] = $this->mdp->clear($tc_og['breadcrumbs'][$count]['text']);
					}
					$data['product_stock'] = ($tc_og['microdatapro_data']['quantity'] > 0)?"instock":"pending";

					$data['ean'] = $tc_og['microdatapro_data']['ean'];
					$data['isbn'] = $tc_og['microdatapro_data']['isbn'];
					$data['upc'] = $tc_og['microdatapro_data']['upc'];

					$data['color'] = false;
					$data['material'] = false;
					$data['size'] = false;

					if($this->mdc('attr_color') or $this->mdc('attr_material') or $this->mdc('attr_size')){
						foreach($tc_og['attribute_groups'] as $group){
							foreach($group['attribute'] as $attribute){
								if($attribute['attribute_id'] == $this->mdc('attr_color')){
									$data['color'] = $this->mdp->clear($attribute['text']);
								}
								if($attribute['attribute_id'] == $this->mdc('attr_material')){
									$data['material'] = $this->mdp->clear($attribute['text']);
								}
								if($attribute['attribute_id'] == $this->mdc('attr_size')){
									$data['size'] = $this->mdp->clear($attribute['text']);
								}
							}
						}
					}
					$data['price'] = false;
					$data['special'] = false;
					$data['currency'] = $this->session->data['currency'];

					if(!$this->mdc('hide_price')){
						$tc_og['special'] = str_replace(",",".",$tc_og['special']);
						$tc_og['price'] = str_replace(",",".",$tc_og['price']);
						$data['price'] = (float)rtrim(preg_replace('/[^\d.]/', '', $tc_og['price']), ".");
						$data['special'] = (float)rtrim(preg_replace('/[^\d.]/', '', $tc_og['special']), ".");
					}

					//relateds
					$data['relateds'] = array();
					if(isset($tc_og['products']) && $tc_og['products'] && is_array($tc_og['products'])){
						foreach($tc_og['products'] as $product){
							$data['relateds'][$product['href']] = $product['href'];
						}
					}

				}

				if(isset($this->request->get['route']) && $this->request->get['route'] == "information/information"){
					$data['og_type'] = "article";
				}

				$data['age_group'] = $this->mdc('age_group');
				$data['target_gender'] = $this->mdc('target_gender');
				$data['microdatapro_profile_id'] = $this->mdc('profile_id');

				$data['contacts'] = $this->mdc('company');
				if($data['contacts']){

					$store_id = false; //multistore
					$front_stores = $this->mdc('front_stores');

					if($front_stores){
						foreach($front_stores as $result){
							$url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
							$pos = strpos($url, $result['url']);
							if ($pos !== false) {
								$store_id = $result['store_id'];
								$data['organization_phones']= $this->mdc('phones'.$store_id)?array_diff(array_map('trim', explode(",", $this->mdc('phones'.$store_id))), array('')):$data['organization_phones'];
								$data['organization_groups']= $this->mdc('groups'.$store_id)?array_diff(array_map('trim', explode(",", $this->mdc('groups'.$store_id))), array('')):$data['organization_groups'];
								$data['organization_map']	= $this->mdc('map'.$store_id)?$this->mdc('map'.$store_id):$data['organization_map'];
								$data['organization_locations'] = $this->organization($store_id);
							}
						}
					}

					$organizations = $this->organization($store_id);
					foreach($organizations as $organization){
						$data['street_address'] = $organization['streetAddress'];
						$data['postal_code'] = $organization['postalCode'];
						$country_data = explode(",", $organization['addressLocality']);
						$data['country_name'] = isset($country_data[1])?trim($country_data[1]):'';
						$data['locality'] = isset($country_data[0])?trim($country_data[0]):'';
						$data['latitude'] = $organization['latitude'];
						$data['longitude'] = $organization['longitude'];
						break;
					}
					$data['email'] = $this->mdc('email')?$this->mdc('email'):$this->config->get('config_email');
					$data['telephone'] = false;
					if($this->mdc('phones')){
						$phones_data = $this->mdc('phones')?array_diff(array_map('trim', explode(",", $this->mdc('phones'))), array('')):false;
						foreach($phones_data as $phone){
							$data['telephone'] = $phone;
							break;
						}
					}
				}

				return $this->view("tc_og", $data);
			}
		}
	//tc_og

	//tc_og_prefix - функция добавления префикса
		public function tc_og_prefix() {
			$prefix = 'prefix="og: https://ogp.me/ns# fb: https://ogp.me/ns/fb# business: https://ogp.me/ns/business# place: http://ogp.me/ns/place#"';

			if(isset($this->request->get['route'])){
				$route = explode('/', $this->request->get['route']);

				if($route[0] == "product"){
					$prefix = 'prefix="og: https://ogp.me/ns# fb: https://ogp.me/ns/fb# product: https://ogp.me/ns/product# business: https://ogp.me/ns/business# place: http://ogp.me/ns/place#"';
				}
			}

			return $prefix;
		}
	//tc_og_prefix

	//view - функция вывода разметки в шаблон
		public function view($template, $data) {
			if(($this->mdp->opencartVersion(0) == 2 && $this->mdp->opencartVersion(1) >= 2) or $this->mdp->opencartVersion(0) == 3){ //over 2.2
				return $this->load->view('extension/module/microdatapro/' . $template, $data);
			}else{ //2.0
				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/microdatapro/' . $template . '.tpl')) {
					return $this->load->view($this->config->get('config_template') . '/template/module/microdatapro/' . $template . '.tpl', $data);
				} else {
					return $this->load->view('default/template/module/microdatapro/' . $template . '.tpl', $data);
				}
			}
		}
	//view

	//breadcrumbs - функция формирования хлебных крошек
		public function breadcrumbs($breadcrumb_data = array()){
		  $breadcrumbs = array();
		  foreach($breadcrumb_data as $breadcrumb){
		    if(isset($breadcrumb['href'])){
		      $breadcrumb_text = $this->mdp->clear($breadcrumb['text']);
		  		if($breadcrumb_text == "" || $breadcrumb_text == " " || $breadcrumb_text == "  " || $breadcrumb_text == "   "){
		  			$breadcrumb_text = "Main";
		  			if(isset($this->session->data['language'])){
		  				if($this->session->data['language'] == "ru" || $this->session->data['language'] == "ru-ru" || $this->session->data['language'] == "russian"){
		  					$breadcrumb_text = "Главная";
		  				}

							if($this->session->data['language'] == "ua" || $this->session->data['language'] == "uk-ua" || $this->session->data['language'] == "ukrainian"){
								$breadcrumb_text = "Головна";
							}

		  			}
		  		}
		      $breadcrumbs[count($breadcrumbs)+1] = array(
		        'text' => $breadcrumb_text,
		        'href' => $breadcrumb['href'],
		      );
		    }
		  }

		  return $breadcrumbs;
		}
	//breadcrumbs

	//organization - функция получения информации о компании
		public function organization($store_id = ''){
		  $location_data = array();
		  if($this->mdc('locations'.$store_id)){
		    $microdatapro_locations = explode(PHP_EOL, $this->mdc('locations'.$store_id));
		    foreach($microdatapro_locations as $location){
		      $geo = explode("//", trim($location));
		      if($geo[0]){
						$geo[0] = str_replace(",", ";", $geo[0]);
		        $coordinates = explode(";", $geo[0]);
		        if(isset($coordinates[0]) && isset($coordinates[1]) && isset($geo[1]) && isset($geo[2]) && isset($geo[3])){
		          $location_data[] = array(
		            'latitude'  	   => trim($coordinates[0]),
		            'longitude' 	   => trim($coordinates[1]),
		            'addressLocality'=> trim($geo[1]),
		            'streetAddress'  => trim($geo[2]),
		            'postalCode' 	   => trim($geo[3])
		          );
		        }
		      }
		    }
		  }

		  return $location_data;
		}
	//organization

	//cron
	public function cron(){
		if(isset($this->request->get['key']) && $this->request->get['key'] == $this->mdc('cron_key')){
			$this->mdp->getMinMaxCron();
		}else{
			exit();
		}
	}
	//cron

}
