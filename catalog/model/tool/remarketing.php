<?php
class ModelToolRemarketing extends Model {
	public function sendEcommerce($ecommerce_data) {
		
		if(!$this->isBot()) {
		if (isset($this->request->server['HTTP_CLIENT_IP'])) {
            $ip = $this->request->server['HTTP_CLIENT_IP'];
        } elseif (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $ip = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $this->request->server['REMOTE_ADDR'];
        }
		
		$ecommerce_data['uip'] = $ip;

		if (!empty($this->request->server['HTTP_USER_AGENT'])) {
            $ecommerce_data['ua'] = $this->request->server['HTTP_USER_AGENT'];
        }
		if (!empty($this->session->data['gclid'])) {
            $ecommerce_data['gclid'] = $this->session->data['gclid'];
        }
		if (!empty($this->session->data['dclid'])) {
            $ecommerce_data['dclid'] = $this->session->data['dclid'];
        }
		if (!empty($this->session->data['utm_source'])) {
            $ecommerce_data['cs'] = $this->session->data['utm_source'];
        }
		if (!empty($this->session->data['utm_medium'])) {
            $ecommerce_data['cm'] = $this->session->data['utm_medium'];
        }
		if (!empty($this->session->data['utm_term'])) {
            $ecommerce_data['ck'] = $this->session->data['utm_term'];
        }
		if (!empty($this->session->data['utm_content'])) {
            $ecommerce_data['cc'] = $this->session->data['utm_content'];
        }
		if (!empty($this->session->data['utm_campaign'])) {
            $ecommerce_data['cn'] = $this->session->data['utm_campaign'];
        }
		$url = 'https://www.google-analytics.com/collect';
		$content = http_build_query($ecommerce_data);
		$content = utf8_encode($content);
		$ch = curl_init();
		if (!empty($this->request->server['HTTP_USER_AGENT'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
        }
		
		if ($this->config->get('remarketing_ecommerce_measurement_log')) {
			$this->writeLog('ecommerce_measurement', $ecommerce_data);
		}
		
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	    $response = curl_exec($ch);
	    curl_close($ch); 
		}
	}
	
	public function sendEsputnik($esputnik_data, $event_url = 'https://esputnik.com/api/v1/event') {
		if (!$this->isBot() && $this->config->get('remarketing_esputnik_login') && $this->config->get('remarketing_esputnik_password')) {
			$user = $this->config->get('remarketing_esputnik_login');
			$password = $this->config->get('remarketing_esputnik_password');
			 
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($esputnik_data));
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json;charset=UTF-8'));
			curl_setopt($ch, CURLOPT_URL, $event_url);
			curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $password);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
			$output = curl_exec($ch);
			
			curl_close($ch);
		}
	}
	
	public function sendEcommerceGa4($ecommerce_data) {
		
		if(!$this->isBot() && $this->config->get('remarketing_ecommerce_ga4_analytics_id') && $this->config->get('remarketing_ecommerce_ga4_measurement_api_secret')) {
		if (isset($this->request->server['HTTP_CLIENT_IP'])) {
            $ip = $this->request->server['HTTP_CLIENT_IP'];
        } elseif (isset($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $ip = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $this->request->server['REMOTE_ADDR'];
        }

		$url = 'https://www.google-analytics.com/mp/collect?measurement_id=' . $this->config->get('remarketing_ecommerce_ga4_analytics_id') . '&api_secret=' . $this->config->get('remarketing_ecommerce_ga4_measurement_api_secret');
		$ecommerce_data_send = [];
		$ecommerce_data_send = json_encode($ecommerce_data); 
		$content = $ecommerce_data_send;
		
		if ($this->config->get('remarketing_ecommerce_ga4_measurement_log')) {
			$this->writeLog('ecommerce_measurement_ga4', $ecommerce_data);
		}

		$ch = curl_init();
		if (isset($this->request->server['HTTP_USER_AGENT'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
        }

	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Content-Length: ' . mb_strlen($content)));
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	    curl_setopt($ch, CURLOPT_POST, true); 
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	    $response = curl_exec($ch);
		
	    curl_close($ch); 
		}
	}
	
	public function sendFacebook($facebook_data, $order_info = false) {
		if(!$this->isBot()) {
			$data = [];
			
			$data['event_name'] = $facebook_data['event_name'];
			$data['event_time'] = $data['event_id'] = time();
			
			$data['event_source_url'] = rtrim(HTTPS_SERVER, '/') . $this->request->server['REQUEST_URI'];
			
			if (isset($facebook_data['url'])) {
				$data['event_source_url'] = $facebook_data['url'];		
			}
			
			$data['custom_data'] = $facebook_data['custom_data'];
			
			
		if (isset($this->request->server['HTTP_CLIENT_IP'])) {
            $ip = $this->request->server['HTTP_CLIENT_IP'];
        } elseif (isset($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $ip = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $this->request->server['REMOTE_ADDR'];
        }
		
		$ua = '';
		
		if (isset($this->request->server['HTTP_USER_AGENT'])) {
            $ua = $this->request->server['HTTP_USER_AGENT'];
        }
		
		$data['user_data'] = [
			'client_ip_address' => $ip,
			'client_user_agent' => $ua
		];
		
		if (isset($this->session->data['fbc'])) {
			$data['user_data']['fbc'] = $this->session->data['fbc'];
		}
		
		if (isset($this->session->data['fbp'])) {
			$data['user_data']['fbp'] = $this->session->data['fbp'];
		}
		
		if ($this->customer->isLogged()) {
			if ($this->customer->getEmail()) {
				$data['user_data']['em'] = hash('sha256', $this->customer->getEmail());
			}
			if ($this->customer->getFirstName()) {
				$data['user_data']['fn'] = hash('sha256', mb_strtolower($this->customer->getFirstName()));
			}
			if ($this->customer->getLastName()) {
				$data['user_data']['ln'] = hash('sha256', mb_strtolower($this->customer->getLastName()));
			}
			if ($this->customer->getTelephone()) {
				$data['user_data']['ph'] = hash('sha256', preg_replace("/[^0-9]/", '', $this->customer->getTelephone()));
			}
			
			$data['user_data']['external_id'] = hash('sha256', $this->customer->getEmail());
		}
		
		if ($order_info) {
			if (!empty($order_info['email'])) {
				$data['user_data']['em'] = hash('sha256', $order_info['email']);
			}
			if (!empty($order_info['firstname'])) {
				$data['user_data']['fn'] = hash('sha256', mb_strtolower($order_info['firstname']));
			}
			if (!empty($order_info['lastname'])) {
				$data['user_data']['ln'] = hash('sha256', mb_strtolower($order_info['lastname']));
			}
			if (!empty($order_info['telephone'])) {
				$data['user_data']['ph'] = hash('sha256', preg_replace("/[^0-9]/", '', $order_info['telephone']));
			}
		}
		
		if (!empty($facebook_data['time'])) {
			$data['event_time'] = $data['event_id'] = $facebook_data['time'];
		}
		
		$data['action_source'] = 'website';
		
		$fb_data['data'] = [json_encode($data)]; 
		if ($this->config->get('remarketing_facebook_test_code') != '') {
			$fb_data['test_event_code'] = $this->config->get('remarketing_facebook_test_code'); // YOUR TEST EVENT CODE
		}
		$fb_send_data = http_build_query($fb_data); 
		$fb_send_data = utf8_encode($fb_send_data);

		$url = 'https://graph.facebook.com/v' . $this->config->get('remarketing_facebook_api_ver') . '/' . $this->config->get('remarketing_facebook_identifier') . '/events?access_token=' . $this->config->get('remarketing_facebook_token');
		$ch = curl_init();
		if (isset($this->request->server['HTTP_USER_AGENT'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
        } 
		
		if ($this->config->get('remarketing_facebook_log')) {
			$this->writeLog('facebook', $fb_data);
		}

	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
	    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $fb_send_data);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	    $response = curl_exec($ch); 
		
	    curl_close($ch); 
		
		}
	}
	
	public function sendTelegram($order_id) {
		$order_info = $this->getOrderRemarketing($order_id);
		if ($order_info) {
			$tg_url = 'https://api.telegram.org/bot';
    
			$tg_token = $this->config->get('remarketing_telegram_bot_id');
			$tg_link = $tg_url . $tg_token . '/sendMessage';
	
			$tg_user_id = $this->config->get('remarketing_telegram_send_to_id');
			$tg_message = $this->config->get('remarketing_telegram_message');        
	
			$find = [
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}',
				'{total}',
				'{shipping_method}',
				'{payment_method}',
				'{order_status}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			];
	
			$replace = [ 
				'firstname'       => $order_info['firstname'],
				'lastname'        => $order_info['lastname'],
				'email'  	      => $order_info['order_info']['email'],
				'telephone'       => $order_info['order_info']['telephone'],
				'total'           => $order_info['default_total'],
				'shipping_method' => $order_info['order_info']['shipping_method'],
				'payment_method'  => $order_info['order_info']['payment_method'],
				'order_status'    => $order_info['order_info']['order_status'],
				'company'         => $order_info['order_info']['shipping_company'],
				'address_1'       => $order_info['order_info']['shipping_address_1'],
				'address_2'       => $order_info['order_info']['shipping_address_2'],
				'city'            => $order_info['order_info']['shipping_city'],
				'postcode'        => $order_info['order_info']['shipping_postcode'],
				'zone'            => $order_info['order_info']['shipping_zone'],
				'zone_code'       => $order_info['order_info']['shipping_zone_code'],
				'country'         => $order_info['order_info']['shipping_country']
			];
			
			$tg_message = str_replace($find, $replace, $tg_message);
			 
			$products = '';
			foreach ($order_info['products'] as $product) {
				$products .= $product['name'] . ' - ' . $product['price'] . ' х ' . $product['quantity'] . "\n";
			}
			
			$tg_message = str_replace('{products}', $products, $tg_message);
			
			$tg_data = [
				'chat_id'    => $tg_user_id,
				'text'       => $tg_message,
				'parse_mode' => 'html'
			];
	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $tg_link);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $tg_data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			$response = curl_exec($ch); 
			
			curl_close($ch); 
		}
	}
	
	public function sendEcommerceImpressions($products, $heading_title = 'Not Set') {
		$this->load->model('catalog/product');
		if (empty($this->session->data['uuid'])) return;
		$uuid = $this->session->data['uuid'];		
		$ecommerce_data = [
			'v'   => 1,
			'tid' => $this->config->get('remarketing_ecommerce_analytics_id'),
			'cid' => $uuid,
			't'   => 'event',
			'ec'  => 'Enhanced Ecommerce',
			'ea'  => 'Product Impressions',
			'ni'  => 1,
			'cu'  => $this->config->get('remarketing_ecommerce_currency')
		];
		if ($this->customer->isLogged()) {
			$ecommerce_data['uid'] = $this->customer->isLogged();
			unset($ecommerce_data['cid']);
		}
		$i = 1;
		if (!empty($products)) {
			$ecommerce_data['il1nm'] = $heading_title;
			foreach ($products as $product) {
				$ecommerce_data['il1pi' . $i . 'nm'] = $product['name'];
				$ecommerce_data['il1pi' . $i . 'id'] = $product['id'];
				$ecommerce_data['il1pi' . $i . 'pr'] = $product['price'];
				if (!empty($product['brand'])) $ecommerce_data['il1pi' . $i . 'br'] = $product['brand'];
				if (!empty($product['category'])) $ecommerce_data['il1pi' . $i . 'ca'] = $product['category'];
				$ecommerce_data['il1pi' . $i . 'ps'] = $i;
				$i++;
			}
		}
		 
		$this->sendEcommerce($ecommerce_data);
	}
	
	public function sendEcommerceCart($ecommerce_data) {
		$this->sendEcommerce($ecommerce_data);
	}
	
	public function sendEcommerceDetails($product, $impressions, $heading_title = 'Not Set') {
		$this->load->model('catalog/product');
			$product_info = !empty($product['products'][0]) ? $product['products'][0] : false;
			if (!$product_info) return; 
			
				if (empty($this->session->data['uuid'])) return;
				$uuid = $this->session->data['uuid'];	
				
				$ecommerce_data = [
					'v' => 1,
					'tid'   => $this->config->get('remarketing_ecommerce_analytics_id'),
					'cid'   => $uuid,
					't'     => 'event',
					'ec'    => 'Enhanced Ecommerce',
					'ea'    => 'Product Details',
					'ni'    => 1,
					'cu'    => $this->config->get('remarketing_ecommerce_currency'),
					'pa'    => 'detail',
					'pal'   => $heading_title,
					'pr1nm' => $product_info['name'],
					'pr1id' => $product_info['id'],
					'pr1pr' => $product_info['price']
				];
				if (!empty($product_info['brand'])) $ecommerce_data['pr1br'] = $product_info['brand'];
				if (!empty($product_info['category'])) $ecommerce_data['pr1ca'] = $product_info['category'];
					
				if ($this->customer->isLogged()) {
					$ecommerce_data['uid'] = $this->customer->isLogged();
					unset($ecommerce_data['cid']);
				}
		
				if (!empty($impressions)) {
					$ecommerce_data['il1nm'] = 'Featured';
					foreach ($impressions as $product){
						$ecommerce_data['il1pi' . $product['position'] . 'nm'] = $product['name'];
						$ecommerce_data['il1pi' . $product['position'] . 'id'] = $product['id'];
						$ecommerce_data['il1pi' . $product['position'] . 'pr'] = $product['price'];
						if (!empty($product['brand'])) $ecommerce_data['il1pi' . $product['position'] . 'br'] = $product['brand'];
						if (!empty($product['category'])) $ecommerce_data['il1pi' . $product['position'] . 'ca'] = $product['category'];
						$ecommerce_data['il1pi' . $product['position'] . 'ps'] = $product['position'];
					}
				}
				
				$this->sendEcommerce($ecommerce_data);
	}
	
	public function sendMeasurementClick($product, $heading_title = 'Not Set') {
			$product_info = $product;
			if (empty($this->session->data['uuid'])) return;
			$uuid = $this->session->data['uuid'];		
			$ecommerce_data = [
				'v'     => 1,
				'tid'   => $this->config->get('remarketing_ecommerce_analytics_id'),
				'cid'   => $uuid,
				't'     => 'event',
				'ec'    => 'Enhanced Ecommerce',
				'ea'    => 'Product Clicks',
				'ni'    => 1,
				'cu'    => $this->config->get('remarketing_ecommerce_currency'),
				'pa'    => 'click',
				'pal'   => $heading_title,
				'pr1nm' => $product_info['name'],
				'pr1id' => $product_info['id'],
				'pr1pr' => $product_info['price']
			];
			
			if (!empty($product_info['brand'])) $ecommerce_data['pr1br'] = $product_info['brand'];
			if (!empty($product_info['category'])) $ecommerce_data['pr1ca'] = $product_info['category'];

			if ($this->customer->isLogged()) {
				$ecommerce_data['uid'] = $this->customer->isLogged();
				unset($ecommerce_data['cid']);
			}
			
			$this->sendEcommerce($ecommerce_data);
	}

	public function getQuickOrderOpen($product_info) {
		$json = [];
		if ($product_info) {
		$this->load->model('catalog/product'); 
		$categories = $this->model_catalog_product->getRemarketingCategories($product_info['product_id']);
		$json['remarketing'] = [];
		$json['remarketing']['google_product_id'] = $this->config->get('remarketing_google_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
		$json['remarketing']['google_identifier'] = $this->config->get('remarketing_google_identifier');
		$json['remarketing']['google_ads_identifier'] = $this->config->get('remarketing_google_ads_identifier');
		$json['remarketing']['google_ads_identifier_cart'] = $this->config->get('remarketing_google_ads_identifier_cart');
		$json['remarketing']['facebook_product_id'] = $this->config->get('remarketing_facebook_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
		$json['remarketing']['mytarget_product_id'] = $this->config->get('remarketing_mytarget_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
		$json['remarketing']['vk_product_id'] = $this->config->get('remarketing_vk_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
		$json['remarketing']['product_id'] = $product_info['product_id'];
		$json['remarketing']['vk_identifier'] = $this->config->get('remarketing_vk_identifier');
		$json['remarketing']['ecommerce_product_id'] = $this->config->get('remarketing_ecommerce_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
		$json['remarketing']['ecommerce_ga4_product_id'] = $this->config->get('remarketing_ecommerce_ga4_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
		$json['remarketing']['ecommerce_status'] = $this->config->get('remarketing_ecommerce_status');
		$json['remarketing']['ecommerce_ga4_status'] = $this->config->get('remarketing_ecommerce_ga4_status');
		$json['remarketing']['ecommerce_ga4_identifier'] = $this->config->get('remarketing_ecommerce_ga4_identifier');
		$json['remarketing']['google_status'] = $this->config->get('remarketing_google_status');
		$json['remarketing']['facebook_status'] = $this->config->get('remarketing_facebook_status');
		$json['remarketing']['facebook_lead_status'] = $this->config->get('remarketing_facebook_lead');
		$json['remarketing']['tiktok_status'] = $this->config->get('remarketing_tiktok_status');
		$json['remarketing']['retailrocket_status'] = $this->config->get('remarketing_retailrocket_status');
		$json['remarketing']['vk_status'] = $this->config->get('remarketing_vk_status');
		$json['remarketing']['quantity'] = 1;
		$current_price = $product_info['special'] ? $product_info['special'] : $product_info['price'];
		$json['remarketing']['price'] = $this->currency->format($current_price, $this->session->data['currency'], '', false);
		$json['remarketing']['google_price'] = $this->currency->format($current_price, $this->config->get('remarketing_google_currency'), '', false);
		$json['remarketing']['google_conversion_price'] = $this->currency->format($current_price * (float)$this->config->get('remarketing_google_ads_ratio'), $this->config->get('remarketing_google_currency'), '', false);
		$json['remarketing']['facebook_price'] = $this->currency->format($current_price, $this->config->get('remarketing_facebook_currency'), '', false);
		$json['remarketing']['ecommerce_price'] = $this->currency->format($current_price, $this->config->get('remarketing_ecommerce_currency'), '', false);
		$json['remarketing']['brand'] = addslashes($product_info['manufacturer']);
		$json['remarketing']['name'] = addslashes($product_info['name']);
		$json['remarketing']['category'] = addslashes($categories);
		$json['remarketing']['currency'] = $this->session->data['currency'];
		$json['remarketing']['google_currency'] = $this->config->get('remarketing_google_currency');
		$json['remarketing']['facebook_currency'] = $this->config->get('remarketing_facebook_currency');
		$json['remarketing']['ecommerce_currency'] = $this->config->get('remarketing_ecommerce_currency');
					$json['remarketing']['google_remarketing_event'] = [
				'send_to' => $this->config->get('remarketing_google_identifier'),
				'value'   => $json['remarketing']['google_price'],
				'items'   => [[
					'id' => $json['remarketing']['google_product_id'],
					'google_business_vertical' => 'retail'
				]],
			];
			$json['remarketing']['google_ads_event'] = [
				'send_to' => $this->config->get('remarketing_google_ads_identifier_cart'),
				'value'   => $json['remarketing']['google_conversion_price'],
				'currency'   => $this->config->get('remarketing_google_currency')
			];
			$json['remarketing']['facebook_pixel_event'] = [
				'content_name' => $json['remarketing']['name'],
				'content_ids' => [$json['remarketing']['facebook_product_id']],
				'content_type' => 'product',
				'content_category' => $json['remarketing']['category'],
				'value'   => $json['remarketing']['facebook_price'],
				'currency'   => $this->config->get('remarketing_facebook_currency')
			];
			$json['remarketing']['tiktok_event'] = [
				'content_name' => $json['remarketing']['name'],
				'content_id' => $json['remarketing']['product_id'],
				'content_type' => 'product',
				'content_category' => $json['remarketing']['category'],
				'value'   => $json['remarketing']['price'],
				'currency'   => $json['remarketing']['currency']
			];
			$ecommerce_product = [
				'name' => $json['remarketing']['name'],
				'id' => [$json['remarketing']['ecommerce_product_id']],
				'price' => $json['remarketing']['ecommerce_price'],
				'quantity' => $json['remarketing']['quantity']
			];
			if (!empty($json['remarketing']['brand'])) $ecommerce_product['brand'] = $json['remarketing']['brand'];
			if (!empty($json['remarketing']['category'])) $ecommerce_product['category'] = $json['remarketing']['category'];
			$json['remarketing']['ecommerce_product'] = $ecommerce_product;
			
			$ecommerce_ga4_product = [
				'item_name' => $json['remarketing']['name'],
				'index' => 1,
				'price' => $json['remarketing']['ecommerce_price'],
				'quantity' => $json['remarketing']['quantity']
			];
			if (!empty($json['remarketing']['brand'])) $ecommerce_ga4_product['item_brand'] = $json['remarketing']['brand'];
			if (!empty($json['remarketing']['category'])) $ecommerce_ga4_product['item_category'] = $json['remarketing']['category'];
			$json['remarketing']['ecommerce_ga4_event'] = [
				'send_to' => $this->config->get('remarketing_ecommerce_ga4_identifier'),
				'currency' => $json['remarketing']['ecommerce_currency'],
				'items' => [$ecommerce_ga4_product]
			];
			
		$fb_time = time();
		$json['remarketing']['time'] = $fb_time;
		//options todo			
		
		if ($this->config->get('remarketing_ecommerce_measurement_status')) {
			if (empty($this->session->data['uuid'])) return;
			$uuid = $this->session->data['uuid'];		
			$ecommerce_data = [
				'v'     => 1,
				'tid'   => $this->config->get('remarketing_ecommerce_analytics_id'),
				'cid'   => $uuid,
				't'     => 'event',
				'ec'    => 'Enhanced Ecommerce',
				'ea'    => 'Adding a Product to a Shopping Cart',
				'ni'    => 1,
				'cu'    => $this->config->get('remarketing_ecommerce_currency'),
				'pa'    => 'add',
				'pr1nm' => $product_info['name'],
				'pr1id' => ($this->config->get('remarketing_ecommerce_measurement_id') == 'id') ? $product_info['product_id'] : $product_info['model'],
				'pr1pr' => $json['remarketing']['ecommerce_price'],
				'pr1qt' => 1
			];
			
			if (!empty($json['remarketing']['brand'])) $ecommerce_data['pr1br'] = $json['remarketing']['brand'];
			if (!empty($json['remarketing']['category'])) $ecommerce_data['pr1ca'] = $json['remarketing']['category'];

			$this->sendEcommerce($ecommerce_data);
		}
		
		if ($this->config->get('remarketing_facebook_server_side') && $this->config->get('remarketing_facebook_token')) {
			$facebook_data['event_name'] = 'AddToCart';
			$facebook_data['custom_data'] = [
				'value' => $json['remarketing']['facebook_price'],
				'currency' => $this->config->get('remarketing_facebook_currency'),
				'content_ids' => [
					$json['remarketing']['facebook_product_id']
				],
				'content_name'     => addslashes($product_info['name']),
				'content_category' => $categories,
				'content_type'     => 'product',
				'opt_out'          => false
			];
			$facebook_data['time'] = $fb_time;
			$this->sendFacebook($facebook_data);
		}
		}
		return $json; 
    }
	
	public function getQuickOrderSuccess($order_id) {
		$json['remarketing'] = [];
		$this->load->model('catalog/product'); 
		$this->load->model('checkout/order'); 
		$order_info = $this->getOrderRemarketing($order_id);
		if ($order_info) {
			$json['remarketing'] = [];
			
			$json['remarketing']['google_products'] = [];
			$json['remarketing']['facebook_products'] = [];
			$json['remarketing']['mytarget_products'] = [];
			$json['remarketing']['vk_products'] = [];
			$json['remarketing']['ecommerce_products'] = [];
			$json['remarketing']['ecommerce_ga4_products'] = [];
			$json['remarketing']['retailrocket_products'] = [];
			$json['remarketing']['tiktok_products'] = [];
			if ($order_info['products']) {
				$i = 1;
				foreach ($order_info['products'] as $product) {
					$json['remarketing']['google_products'][] = [
						'id' => $this->config->get('remarketing_google_id') == 'id' ? $product['product_id'] : $product['model'],
						'google_business_vertical' => 'retail'
					];
					
					$json['remarketing']['facebook_products'][] = [
						'id' => $this->config->get('remarketing_facebook_id') == 'id' ? $product['product_id'] : $product['model'],
						'price' => $product['facebook_price'],
						'quantity' => $product['quantity']
					];
					
					$json['remarketing']['tiktok_products'][] = [
						'content_id' => $product['product_id'],
						'price' => $product['price'],
						'quantity' => $product['quantity']
					];
					
					$json['remarketing']['retailrocket_products'][] = [
						'id' =>  $product['product_id'],
						'qnt' =>  $product['quantity'],
						'price' => $product['price'],
					];
					
					$json['remarketing']['mytarget_products'][] = $this->config->get('remarketing_mytarget_id') == 'id' ? $product['product_id'] : $product['model'];
					
					$json['remarketing']['vk_products'][] = [
						'id' => $this->config->get('remarketing_vk_id') == 'id' ? $product['product_id'] : $product['model'],
						'price' => $product['price'],
					];
					
					$json['remarketing']['ecommerce_products'][] = [
						'name'     => $product['name'],
						'id'       => $this->config->get('remarketing_ecommerce_id')== 'id' ? $product['product_id'] : $product['model'],
						'price'    => $product['ecommerce_price'],
						'quantity' => $product['quantity'],
						'category' => $product['category'],
						'variant'  => $product['variant'],
						'brand'    => $product['product_info']['manufacturer']
					];
					
					$item = [
						'item_name'      => $product['name'],
						// Google refuses id 'item_id'        => $this->config->get('remarketing_ecommerce_ga4_id')== 'id' ? $product['product_id'] : $product['model'],
						'price'          => $product['ecommerce_price'],
						'quantity'       => $product['quantity'],
						'index'          => $i,
						'affiliation'    => $order_info['store_name']
					];
					if (!empty($product['product_info']['manufacturer'])) {
						$item['item_brand'] = $product['product_info']['manufacturer'];
					}
					if (!empty($product['category'])) {
						$item['item_category'] = $product['category'];
						$item['item_list_name'] = $product['category'];
					}
					if (!empty($product['variant'])) {
						$item['item_variant'] = $product['variant'];
					}
					$json['remarketing']['ecommerce_ga4_products'][] = $item;
					$i++;
				}
			}
			
			if (count($json['remarketing']['mytarget_products']) > 1) {
				$target_itemid = '[\'' . implode('\',\'', $json['remarketing']['mytarget_products']) . '\']';
			} elseif (!empty($json['remarketing']['mytarget_products'])) {
				$target_itemid = $json['remarketing']['mytarget_products'][0];
			} else {
				$target_itemid = ''; 
			}
			
			$json['remarketing']['google_identifier'] = $this->config->get('remarketing_google_identifier');
			$json['remarketing']['google_ads_identifier'] = $this->config->get('remarketing_google_ads_identifier');
			$json['remarketing']['google_ads_identifier_cart'] = $this->config->get('remarketing_google_ads_identifier_cart');
			$json['remarketing']['vk_identifier'] = $this->config->get('remarketing_vk_identifier');
			$json['remarketing']['ecommerce_status'] = $this->config->get('remarketing_ecommerce_status');
			$json['remarketing']['ecommerce_ga4_status'] = $this->config->get('remarketing_ecommerce_ga4_status');
			$json['remarketing']['ecommerce_ga4_identifier'] = $this->config->get('remarketing_ecommerce_ga4_identifier');
			$json['remarketing']['google_status'] = $this->config->get('remarketing_google_status');
			$json['remarketing']['facebook_status'] = $this->config->get('remarketing_facebook_status');
			$json['remarketing']['tiktok_status'] = $this->config->get('remarketing_tiktok_status');
			$json['remarketing']['facebook_pixel_status'] = $this->config->get('remarketing_facebook_pixel_status');
			$json['remarketing']['retailrocket_status'] = $this->config->get('remarketing_retailrocket_status');
			$json['remarketing']['facebook_lead'] = $this->config->get('remarketing_facebook_lead');
			$json['remarketing']['vk_status'] = $this->config->get('remarketing_vk_status');
			$json['remarketing']['mytarget_status'] = $this->config->get('remarketing_mytarget_status');
			$json['remarketing']['mytarget_list'] = $this->config->get('remarketing_mytarget_identifier');
			$json['remarketing']['mytarget_products_list'] = $target_itemid;
			$json['remarketing']['order_info'] = $order_info;
			
			$num_items = 0;
			foreach ($order_info['products'] as $product) {
				$num_items += $product['quantity'];
			}
			$json['remarketing']['num_items'] = $num_items;
			$json['remarketing']['currency'] = $this->session->data['currency'];
			$json['remarketing']['google_currency'] = $this->config->get('remarketing_google_currency');
			$json['remarketing']['facebook_currency'] = $this->config->get('remarketing_facebook_currency');
			$json['remarketing']['ecommerce_currency'] = $this->config->get('remarketing_ecommerce_currency');
			$json['remarketing']['remarketing_google_merchant_identifier'] = $this->config->get('remarketing_google_merchant_identifier');
			$json['remarketing']['remarketing_reviews_country'] = $this->config->get('remarketing_reviews_country');
			$json['remarketing']['reviews_order_date'] = date('Y-m-d', time() + 3600 * 24 * (int)$this->config->get('remarketing_reviews_date'));
			$json['remarketing']['reviews_status'] = $this->config->get('remarketing_reviews_status') && $order_info['email'] && strpos($order_info['email'], 'localhost') === false;
			$fb_time = time();
			$json['remarketing']['time'] = $fb_time;
			 
			// Если быстрый заказ не работает через addOrderHistory
			// false поменять на true чтобы прогнать серверную отправку
			if (false) {
				$this->model_checkout_order->addOrderHistory($order_id, $order_info['order_status_id'], 'remarketing_quick_order');
			}

		}
		
		return $json['remarketing'];
    }

	
	public function isBot() {
		if (!empty($this->request->server['HTTP_USER_AGENT']) && !$this->config->get('remarketing_bot_status')) {
			if (preg_match('/abacho|accona|AddThis|AdsBot|ahoy|AhrefsBot|AISearchBot|alexa|altavista|anthill|appie|applebot|arale|araneo|AraybOt|ariadne|arks|aspseek|ATN_Worldwide|Atomz|baiduspider|baidu|bbot|bingbot|bing|Bjaaland|BlackWidow|BotLink|bot|boxseabot|bspider|calif|CCBot|ChinaClaw|christcrawler|CMC\/0\.01|combine|confuzzledbot|contaxe|CoolBot|cosmos|crawler|crawlpaper|crawl|curl|cusco|cyberspyder|cydralspider|dataprovider|digger|DIIbot|DotBot|downloadexpress|DragonBot|DuckDuckBot|dwcp|EasouSpider|ebiness|ecollector|elfinbot|esculapio|ESI|esther|eStyle|Ezooms|facebookexternalhit|facebook|facebot|fastcrawler|FatBot|FDSE|FELIX IDE|fetch|fido|find|Firefly|fouineur|Freecrawl|froogle|gammaSpider|gazz|gcreep|geona|Getterrobo-Plus|get|girafabot|golem|googlebot|\-google|grabber|GrabNet|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|HTTrack|ia_archiver|iajabot|IDBot|Informant|InfoSeek|InfoSpiders|INGRID\/0\.1|inktomi|inspectorwww|Internet Cruiser Robot|irobot|Iron33|JBot|jcrawler|Jeeves|jobo|KDD\-Explorer|KIT\-Fireball|ko_yappo_robot|label\-grabber|larbin|legs|libwww-perl|linkedin|Linkidator|linkwalker|Lockon|logo_gif_crawler|Lycos|m2e|majesticsEO|marvin|mattie|mediafox|mediapartners|MerzScope|MindCrawler|MJ12bot|mod_pagespeed|speed|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|NationalDirectory|naverbot|NEC\-MeshExplorer|NetcraftSurveyAgent|NetScoop|NetSeer|newscan\-online|nil|none|Nutch|ObjectsSearch|Occam|openstat.ru\/Bot|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pingdom|pinterest|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|rambler|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Scrubby|Search\-AU|searchprocess|search|SemrushBot|Senrigan|seznambot|Shagseeker|sharp\-info\-agent|sift|SimBot|Site Valet|SiteSucker|skymob|SLCrawler\/2\.0|slurp|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|spider|suke|tach_bw|TechBOT|TechnoratiSnoop|templeton|teoma|titin|topiclink|twitterbot|twitter|UdmSearch|Ukonline|UnwindFetchor|URL_Spider_SQL|urlck|urlresolver|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|wapspider|WebBandit\/1\.0|webcatcher|WebCopier|WebFindBot|WebLeacher|WebMechanic|WebMoose|webquest|webreaper|webspider|webs|WebWalker|WebZip|wget|whowhere|winona|wlm|WOLP|woriobot|WWWC|XGET|xing|yahoo|YandexBot|Lighthouse|lighthouse|YandexMobileBot|yandex|Chrome-Lighthouse|yeti|Zeus/i', $this->request->server['HTTP_USER_AGENT'])) {
				return true; 
			}
		}
		return false;
	}
	
	public function writeLog($source, $event) {
        if ($event) {
            $log = new Log($source . '-' . date('d-m-Y') . '.log');
            $log->write($event);
        }
    }
	
	public function setSuccessPage($order_id) {
        if (!empty($order_id)) {
			$this->db->query("UPDATE `" . DB_PREFIX . "remarketing_orders` SET `success_page` = NOW() WHERE order_id = '" . (int)$order_id . "'");
		}
    }
	
	public function setSend($order_id, $source) {
        if (!empty($order_id)) {
			$this->db->query("UPDATE `" . DB_PREFIX . "remarketing_orders` SET `" . $this->db->escape($source) . "` = NOW() WHERE order_id = '" . (int)$order_id . "'");
		}
    }
	
	public function getCid() {
		$cid = '';
        if (isset($this->request->cookie['_ga'])) {
			$cookie = explode('.', $this->request->cookie['_ga']);
			if (isset($cookie['2']) && isset($cookie['3'])) {
				$uuid = $cookie['2'] . '.' . $cookie['3'];
				$cid = $uuid;
			}
		} elseif (isset($this->request->cookie['__utma'])) {
			$cookie = explode('.', $this->request->cookie['__utma']);
			if (isset($cookie['1']) && isset($cookie['2'])) {
				$uuid = $cookie['1'] . '.' . $cookie['2'];
				$cid = $uuid;
			}
		} elseif (isset($this->request->cookie['remarketing_cid'])) {
			$cid = $this->request->cookie['remarketing_cid'];
		} else {
			$cid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',  mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),  mt_rand( 0, 0xffff ),  mt_rand( 0, 0x0fff ) | 0x4000,  mt_rand( 0, 0x3fff ) | 0x8000,  mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ));		
			setcookie('remarketing_cid', $cid, time() + 24 * 3600 * 30, '/');
		} 
		
		$this->session->data['uuid'] = $cid;
		
		return $cid;
    }
	
	public function trackUtm() {
		
		if (isset($this->request->get['gclid'])) {
			$this->session->data['gclid'] = $this->request->get['gclid'];
		}
		
		if (isset($this->request->get['dclid'])) {
			$this->session->data['dclid'] = $this->request->get['dclid'];
		}
		
		if (isset($this->request->get['utm_source'])) {
			$this->session->data['utm_source'] = $this->request->get['utm_source'];
		}
		
		if (isset($this->request->get['utm_campaign'])) {
			$this->session->data['utm_campaign'] = $this->request->get['utm_campaign'];
		}
		
		if (isset($this->request->get['utm_term'])) {
			$this->session->data['utm_term'] = $this->request->get['utm_term'];
		}
		
		if (isset($this->request->get['utm_medium'])) {
			$this->session->data['utm_medium'] = $this->request->get['utm_medium'];
		}
		
		if (isset($this->request->get['utm_content'])) {
			$this->session->data['utm_content'] = $this->request->get['utm_content'];
		}
		
		if (isset($this->request->cookie['_fbp'])) {
			$this->session->data['fbp'] = $this->request->cookie['_fbp'];
		}

		if (isset($this->request->cookie['_fbc'])) {
			$this->session->data['fbc'] = $this->request->cookie['_fbc'];
		}
		
		if (isset($this->request->get['yclid'])) {
			$this->session->data['yclid'] = $this->request->get['yclid'];
		}
		
		if (isset($this->request->get['ymclid'])) {
			$this->session->data['ymclid'] = $this->request->get['ymclid'];
		}
		
		if (isset($this->request->get['fbclid'])) {
			$this->session->data['fbc'] = 'fb' . '.' . '1' . '.' . time() . '.' . $this->request->get['fbclid'];
		}
 
		if ($this->config->get('remarketing_esputnik_status') && $this->customer->isLogged()) {
			if (empty($this->session->data['esputnik_email']) && $this->customer->getEmail()) {
				$this->session->data['esputnik_email'] = $this->customer->getEmail();
			}
			
			if (empty($this->session->data['esputnik_telephone']) && $this->customer->getTelephone()) {
				$this->session->data['esputnik_telephone'] = $this->customer->getTelephone();
			}
			
			if (empty($this->session->data['esputnik_uniq'])) {
				$this->session->data['esputnik_uniq'] = uniqid();
			}
		}
    }
	
	public function getOrderRemarketing($order_id) {
		
		$this->load->model('catalog/product'); 
		$this->load->model('checkout/order'); 

        $order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");
        if ($order_query->num_rows) {
            $language_id = $order_query->row['language_id'];
          
            $order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
            foreach ($order_product_query->rows as $product) {
                $option_data = '';
                $order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");
                foreach ($order_option_query->rows as $option) {
                    if ($option['type'] != 'file') {
                        $option_data .= $option['name'] . ':' . $option['value'] . ';';
                    }
                }
                $option_data = rtrim($option_data, ';');
				
                if ($option_data) {
                    $variant = str_replace("\n", " ", addslashes($option_data));
                } else {
                    $variant = '';
                }

				$product_info = $this->model_catalog_product->getProduct($product['product_id']);
				
                $products[] = array(
                    'name'            => $product['name'],
                    'product_id'      => $product['product_id'],
                    'product_info'    => $product_info,
                    'sku'             => $product['model'],
                    'model'           => $product['model'],
                    'category'        => $this->model_catalog_product->getRemarketingCategories($product['product_id']),
                    'variant'         => $variant,
                    'price'           => $this->currency->format($product['price'], $this->session->data['currency'], '', false),
                    'google_price'    => $this->currency->format($product['price'], $this->config->get('remarketing_google_currency'), '', false),
                    'facebook_price'  => $this->currency->format($product['price'], $this->config->get('remarketing_facebook_currency'), '', false),
                    'ecommerce_price' => $this->currency->format($product['price'], $this->config->get('remarketing_ecommerce_currency'), '', false),
                    'quantity'        => $product['quantity']
                );
            }
			
			$shipping_query = $this->db->query("SELECT value FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'shipping'");
			if ($shipping_query->rows) {
				$shipping = $shipping_query->row['value'];
			} else {
				$shipping = 0;
			}

			$coupon_query = $this->db->query("SELECT title FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'coupon'");
			if ($coupon_query->rows) {
				$coupon = $coupon_query->row['title'];
			} else {
				$coupon = false;
			}
			
			$order_info = $this->model_checkout_order->getOrder($order_query->row['order_id']);
			
			$order_data = [
                'order_id'        => $order_query->row['order_id'],
                'store_name'      => $order_query->row['store_name'],
                'email'           => $order_query->row['email'],
                'telephone'       => $order_query->row['telephone'],
                'firstname'       => $order_query->row['firstname'],
                'lastname'        => $order_query->row['lastname'],
                'products'        => $products,
                'order_info'      => $order_info,
                'total'           => $order_query->row['total'], 
                'default_total'   => $this->currency->format($order_query->row['total'], $this->session->data['currency'], '', false),
                'google_total'    => $this->currency->format($order_query->row['total'], $this->config->get('remarketing_google_currency'), '', false),
                'google_conversion_total' => $this->currency->format($order_query->row['total'] * (float)$this->config->get('remarketing_google_ads_ratio'), $this->config->get('remarketing_google_currency'), '', false),
                'facebook_total'  => $this->currency->format($order_query->row['total'], $this->config->get('remarketing_facebook_currency'), '', false),
                'ecommerce_total' => $this->currency->format($order_query->row['total'], $this->config->get('remarketing_ecommerce_currency'), '', false),
				'shipping'        => $this->currency->format($shipping, $this->config->get('remarketing_ecommerce_currency'), '', false), 
				'coupon'          => $coupon, 
                'email'           => $order_query->row['email'],
                'order_status_id' => $order_query->row['order_status_id'],
                'currency_code'   => $order_query->row['currency_code']
            ];
			
			$remarketing_check_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "remarketing_orders` WHERE order_id = '" . (int)$order_id . "'");
			$parameters = [
				'uuid', 'fbclid', 'fbc', 'fbp', 'gclid', 'dclid', 'utm_source', 'utm_campaign', 'utm_medium', 'utm_term', 'utm_content', 'yclid', 'ymclid' 
			];
			if (!$remarketing_check_query->num_rows) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "remarketing_orders` SET `order_id` = '" . (int)$order_id . "', `order_data` = '" . $this->db->escape(print_r($order_data, true)) . "', `date_added` = NOW()");
				
				foreach ($parameters as $parameter) {
					if (!empty($this->session->data[$parameter])) {
						$this->db->query("UPDATE `" . DB_PREFIX . "remarketing_orders` SET `" . $parameter . "` = '" . $this->db->escape($this->session->data[$parameter]) . "' WHERE order_id = '" . (int)$order_id . "'");
					}
				}
			}
			
			$remarketing_orders_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "remarketing_orders` WHERE `order_id` = '" . (int)$order_id . "'");
			
			$order_data['sent_data'] = [];
			
			if ($remarketing_orders_query->rows) {
				$order_data['sent_data'] = $remarketing_orders_query->row;
			}
			
			foreach($order_data['sent_data'] as $key => $val) {
				if (!empty($order_data['sent_data'][$key]) && in_array($key, $parameters)) {
					$this->session->data[$key] = $val;
				}
			}
			
			if (empty($this->session->data['uuid'])) {
				$this->getCid();		
			} 

			return $order_data;
        } else {
            return false;
        }  
    }
}