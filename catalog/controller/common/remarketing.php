<?php
class ControllerCommonRemarketing extends Controller {
	public function header() {
		$this->load->model('tool/remarketing');
		if ($this->config->get('remarketing_status')) {
			
			$data['output'] = ''; 
		
			if (!$this->model_tool_remarketing->isBot()) {
				if ($this->config->get('remarketing_counter1')) {
					$data['output'] .= "\n" . $this->config->get('remarketing_counter1');
				}
				
				if ($this->config->get('remarketing_google_status') && $this->config->get('remarketing_google_gtag_status')) {
					$data['output'] .= "\n" . '<script src="https://www.googletagmanager.com/gtag/js?id=' . $this->config->get('remarketing_google_identifier') . '"></script>' . "\n";
					$data['output'] .= '<script>' . "\n";
					$data['output'] .= 'window.dataLayer = window.dataLayer || [];' . "\n";
					$data['output'] .= 'function gtag(){dataLayer.push(arguments);}' . "\n";
					$data['output'] .= 'gtag(\'js\', new Date());' . "\n";
					$data['output'] .= 'gtag(\'config\', \'' . $this->config->get('remarketing_google_identifier') . '\');' . "\n";
					$data['output'] .= '</script>' . "\n";
				}
				
				if ($this->config->get('remarketing_retailrocket_status') && $this->config->get('remarketing_retailrocket_identifier')) {
					$data['output'] .= "\n" . '<script>var rrPartnerId = "' . $this->config->get('remarketing_retailrocket_identifier') . '";var rrApi = {}; var rrApiOnReady = rrApiOnReady || [];rrApi.addToBasket = rrApi.order = rrApi.categoryView = rrApi.view = rrApi.recomMouseDown = rrApi.recomAddToCart = function() {};(function(d) {var ref = d.getElementsByTagName(\'script\')[0];var apiJs, apiJsId = \'rrApi-jssdk\';if (d.getElementById(apiJsId)) return;apiJs = d.createElement(\'script\');apiJs.id = apiJsId;apiJs.async = true;apiJs.src = "//cdn.retailrocket.ru/content/javascript/tracking.js";ref.parentNode.insertBefore(apiJs, ref);}(document));</script>' . "\n";
				}
			
				if ($this->config->get('remarketing_facebook_status') && $this->config->get('remarketing_facebook_script_status') && $this->config->get('remarketing_facebook_identifier')) {
					$data['output'] .= "\n" . '<!-- Facebook Pixel Code -->' . "\n";
					$data['output'] .= '<script>' . "\n";
					$data['output'] .= '!function(f,b,e,v,n,t,s)';
					$data['output'] .= '{if(f.fbq)return;n=f.fbq=function(){n.callMethod?';
					$data['output'] .= 'n.callMethod.apply(n,arguments):n.queue.push(arguments)};';
					$data['output'] .= 'if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version=\'2.0\';';
					$data['output'] .= 'n.queue=[];t=b.createElement(e);t.async=!0;';
					$data['output'] .= 't.src=v;s=b.getElementsByTagName(e)[0];';
					$data['output'] .= 's.parentNode.insertBefore(t,s)}(window, document,\'script\',';
					$data['output'] .= '\'https://connect.facebook.net/en_US/fbevents.js\');' . "\n";
					$parameters = [];
					if ($this->customer->isLogged()) {
						if (!empty($this->customer->getEmail())) {
							$parameters[] = "em: '" . $this->customer->getEmail() . "'";
							$parameters[] = "external_id: '" . $this->customer->getEmail() . "'";
						}
						if (!empty($this->customer->getFirstName())) {
							$parameters[] = "fn: '" . $this->customer->getFirstName() . "'";
						}
						if (!empty($this->customer->getLastName())) {
							$parameters[] = "ln: '" . $this->customer->getLastName() . "'";
						}
						if (!empty($this->customer->getTelephone())) {
							$parameters[] = "ph: '" . $this->customer->getTelephone() . "'";
						}
					} 
					$data['output'] .= 'fbq(\'init\', \'' . $this->config->get('remarketing_facebook_identifier') . '\',{' . implode(",\n", $parameters) . '});' . "\n";
					$data['output'] .= 'fbq(\'track\', \'PageView\');' . "\n";
					$data['output'] .= '</script>' . "\n";
					$data['output'] .= '<noscript><img height="1" width="1" style="display:none" ';
					$data['output'] .= 'src="https://www.facebook.com/tr?id=' . $this->config->get('remarketing_facebook_identifier') . '&ev=PageView&noscript=1"';
					$data['output'] .= '/></noscript>' . "\n";
					$data['output'] .= '<!-- End Facebook Pixel Code -->' . "\n";
				}
			}
		
			if ($this->config->get('remarketing_counter_bot')) {
				$data['output'] .= "\n" . $this->config->get('remarketing_counter_bot');
			}
		
			return html_entity_decode($data['output'], ENT_QUOTES, 'UTF-8');
		}
	}
	
	public function body() {
		$this->load->model('tool/remarketing');
		if ($this->config->get('remarketing_status') && !$this->model_tool_remarketing->isBot()) {
			$data['output'] = '';
		
			if ($this->config->get('remarketing_counter2') && $this->config->get('remarketing_counter2')) {
				$data['output'] .= "\n" . $this->config->get('remarketing_counter2');
			}
			
			return html_entity_decode($data['output'], ENT_QUOTES, 'UTF-8');
		}
	}
	
	public function footer() {
		$this->load->model('tool/remarketing');
		if ($this->config->get('remarketing_status') && !$this->model_tool_remarketing->isBot()) {
			
			$data['google_output'] = '';
			$data['google_reviews_output'] = '';
			$data['facebook_output'] = '';
			$data['tiktok_output'] = '';
			$data['mytarget_output'] = '';
			$data['vk_output'] = '';
			$data['rocket_output'] = '';
			$data['events_output'] = '';
			$data['ecommerce_output'] = '';
			$data['counter_output'] = '';
			
			$this->load->model('catalog/product');
			$this->load->model('checkout/order');	
		
			$route = !empty($this->request->get['route']) ? $this->request->get['route'] : '';
			$uuid = $this->model_tool_remarketing->getCid();
			$google_id = $this->config->get('remarketing_google_id') == 'id' ? 'product_id' : 'model';
			$facebook_id = $this->config->get('remarketing_facebook_id') == 'id' ? 'product_id' : 'model';
			$ecommerce_id = $this->config->get('remarketing_ecommerce_id') == 'id' ? 'product_id' : 'model';
			$ecommerce_measurement_id = $this->config->get('remarketing_ecommerce_measurement_id') == 'id' ? 'product_id' : 'model';
			$ecommerce_ga4_id = $this->config->get('remarketing_ecommerce_ga4_id') == 'id' ? 'product_id' : 'model';
			$ecommerce_ga4_measurement_id = $this->config->get('remarketing_ecommerce_ga4_measurement_id') == 'id' ? 'product_id' : 'model';
			$mytarget_id = $this->config->get('remarketing_mytarget_id') == 'id' ? 'product_id' : 'model';
			$vk_id = $this->config->get('remarketing_vk_id') == 'id' ? 'product_id' : 'model';
			
			$data['google_ids'] = [];
			$data['facebook_ids'] = [];
			$data['tiktok_ids'] = [];
			$data['mytarget_ids'] = [];
			$data['vk_ids'] = [];
			$data['totalvalue'] = '';
			$data['google_page'] = false;
			$data['facebook_page'] = false;
			$data['tiktok_page'] = false;
			$data['mytarget_page'] = false;
			$data['vk_page'] = false;
			$data['google_reviews_page'] = false;
			$google_currency = $this->config->get('remarketing_google_currency'); 
			$facebook_currency = $this->config->get('remarketing_facebook_currency'); 
			$ecommerce_currency = $this->config->get('remarketing_ecommerce_currency'); 
			$fb_time = time();

			switch ($route) {
				case '':			
				case 'common/home':	
					$data['vk_page'] = 'view_home';
				break;
				case 'product/category':
					if (isset($this->request->get['path'])) {
						$parts = explode('_', (string)$this->request->get['path']);
						$category_id = (int)array_pop($parts);
						if ($this->config->get('remarketing_retailrocket_status') && $this->config->get('remarketing_retailrocket_identifier') && $category_id) {
							$data['rocket_output'] .= '<script>(window["rrApiOnReady"] = window["rrApiOnReady"] || []).push(function() {	try { rrApi.categoryView(' . $category_id . '); } catch(e) {}})</script>';
						}
					} 
				break;
				case 'product/search':
				case 'product/special':
				case 'product/manufacturer/info':
					break;	
				case 'product/product':
					$data['google_page'] = 'view_item';
					$data['facebook_page'] = false;
					$data['mytarget_page'] = 'product';
					$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
					$product_price = $product_info['special'] ? $product_info['special'] : $product_info['price'];
					$price = $this->currency->format($this->tax->calculate($product_price, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
					$data['google_ids'][] = $product_info[$google_id];
					$data['facebook_ids'][] = $product_info[$facebook_id];
					$data['mytarget_ids'][] = $product_info[$mytarget_id];
					$data['totalvalue'] = $price;
					
					$data['google_totalvalue'] = $this->currency->format($product_price, $google_currency, '', false); 
					$data['facebook_totalvalue'] = $this->currency->format($product_price, $facebook_currency, '', false); 
					$data['ecommerce_totalvalue'] = $this->currency->format($product_price, $ecommerce_currency, '', false); 
					if ($this->config->get('remarketing_retailrocket_status') && $this->config->get('remarketing_retailrocket_identifier')) {
						$data['rocket_output'] .= '<script>(window["rrApiOnReady"] = window["rrApiOnReady"] || []).push(function() {try{ rrApi.view(' . $product_info['product_id'] . '); } catch(e) {}})</script>';
					}
					break;					
				case 'checkout/cart':
				case 'checkout/simplecheckout':
				case 'checkout/checkout':
				case 'checkout/unicheckout':
				case 'checkout/uni_checkout':
				case 'checkout/revcheckout':
				case 'revolution/revcheckout':
				case 'checkout/oct_fastorder':
				case 'extension/quickcheckout/checkout':
					$data['google_page'] = 'add_to_cart';
					$data['google_page'] = false; 
					$data['facebook_page'] = 'initiate';
					$data['tiktok_page'] = 'initiate';
					$data['mytarget_page'] = 'cart';
					$data['vk_page'] = 'init_checkout';
					
					$step = ($route == 'checkout/cart') ? '1' : '2';
					
					if ($this->config->get('remarketing_events_cart')) {
						$data['events_output'] .= "<script>\n";
						$data['events_output'] .= html_entity_decode($this->config->get('remarketing_events_cart'));
						$data['events_output'] .= "</script>\n";     
					}
					
					$products = $this->cart->getProducts();
					$cart_json = [];
					
					foreach ($products as $product) {
						$data['google_ids'][]   = $product[$google_id];
						$data['facebook_ids'][] = $product;
						$data['tiktok_ids'][] = $product;
						$data['mytarget_ids'][] = $product[$mytarget_id];					
						$data['vk_ids'][]       = $product;
						$cart_json[$product['cart_id']] = $product;
					} 
					
					$data['events_output'] .= "<script>\n";
					$data['events_output'] .= "window.cart_products = " . json_encode($cart_json) . "\n";
					$data['events_output'] .= "</script>\n";
					
					$cart_total = $this->cart->getTotal();
					
					$data['totalvalue'] = $this->currency->format($cart_total, $this->session->data['currency'], '', false); 
					$data['google_totalvalue'] = $this->currency->format($cart_total, $google_currency, '', false); 
					$data['google_conversion_totalvalue'] = $this->currency->format($cart_total * $this->config->get('remarketing_google_ads_ratio'), $google_currency, '', false); 
					$data['facebook_totalvalue'] = $this->currency->format($cart_total, $facebook_currency, '', false); 
					$data['ecommerce_totalvalue'] = $this->currency->format($cart_total, $ecommerce_currency, '', false); 
					
					if ($this->config->get('remarketing_ecommerce_status')) {
						$data['ecommerce_output'] .= '<script>' . "\n";
						$data['ecommerce_output'] .= 'window.dataLayer = window.dataLayer || [];' . "\n";
						$data['ecommerce_output'] .= 'dataLayer.push({' . "\n";
						$data['ecommerce_output'] .= "'ecommerce': {" . "\n";
						$data['ecommerce_output'] .= "'currencyCode': '" . $ecommerce_currency. "'," . "\n";
						$data['ecommerce_output'] .= "'checkout': {" . "\n";
						$data['ecommerce_output'] .= "'actionField': {'step': " . $step . "}," . "\n";
						$data['ecommerce_output'] .= "'products': [" . "\n";
						foreach ($products as $product) {
							$product_info = $this->model_catalog_product->getProduct($product['product_id']);
							$categories = addslashes($this->model_catalog_product->getRemarketingCategories($product['product_id']));
							$data['ecommerce_output'] .= "{"."\n";
							$data['ecommerce_output'] .= "'name': '" . addslashes($product['name']) . "',"."\n";
							$data['ecommerce_output'] .= "'id': '" . $product[$ecommerce_id] . "'," . "\n";
							$data['ecommerce_output'] .= "'price': " . $this->currency->format($product['price'], $ecommerce_currency, '', false) . "," . "\n";
							if (!empty($product_info['manufacturer'])) $data['ecommerce_output'] .= "'brand': '" . addslashes($product_info['manufacturer']) . "'," . "\n";
							$data['ecommerce_output'] .= "'category': '" . $categories . "'," . "\n";
							$data['ecommerce_output'] .= "'quantity': " . $product['quantity'] . "}," . "\n";
						}
						$data['ecommerce_output'] = rtrim($data['ecommerce_output'], ',');
						$data['ecommerce_output'] .= "]}},\n";
						$data['ecommerce_output'] .= "'event': 'gtm-ee-event',
						'gtm-ee-event-category': 'Enhanced Ecommerce',
						'gtm-ee-event-action': 'Checkout Step " . $step . "',
						'gtm-ee-event-non-interaction': 'False'";
						$data['ecommerce_output'] .= '});' . "\n</script>\n";
					}
					
					if ($this->config->get('remarketing_ecommerce_ga4_status')) {
						$data['ecommerce_output'] .= '<script>' . "\n";
						$data['ecommerce_output'] .= "if (typeof gtag != 'undefined') {" . "\n";
						$data['ecommerce_output'] .= 'gtag("event", "' . ($route != 'checkout/cart' ? 'begin_checkout' : 'view_cart') . '" , {'."\n";
						$data['ecommerce_output'] .= "'send_to': '" . $this->config->get('remarketing_ecommerce_ga4_identifier') . "',\n";
						$data['ecommerce_output'] .= "'currency': '" . $ecommerce_currency . "',\n";
						$data['ecommerce_output'] .= "'items': ["."\n";
						$i = 1;
						foreach ($products as $product) {
							$product_info = $this->model_catalog_product->getProduct($product['product_id']);
							$categories = addslashes($this->model_catalog_product->getRemarketingCategories($product['product_id']));
							$data['ecommerce_output'] .= "{"."\n";
							// Google refuses id $data['ecommerce_output'] .= "'item_id': '" . ($this->config->get('remarketing_ecommerce_ga4_id') == 'id' ? $product['product_id'] : $product['model']) . "'," . "\n";
							$data['ecommerce_output'] .= "'item_name': '" . addslashes($product['name']) . "'," . "\n";
							$data['ecommerce_output'] .= "'item_list_name': '" . $categories . "'," . "\n";
							if (!empty($product_info['manufacturer'])) $data['ecommerce_output'] .= "'item_brand': '" . addslashes($product_info['manufacturer']) . "'," . "\n";
							$data['ecommerce_output'] .= "'item_category': '" . $categories . "'," . "\n";
							$data['ecommerce_output'] .= "'index': " . $i . "," . "\n";
							$data['ecommerce_output'] .= "'quantity': " . $product['quantity'] . ","."\n";
							$data['ecommerce_output'] .= "'price': " . $this->currency->format($product['price'], $ecommerce_currency, '', false) . "," . "\n";
							$data['ecommerce_output'] .= "'affiliation': '" . addslashes($this->config->get('config_name')) . "'}," . "\n"; 
							$i++;
						}
						$data['ecommerce_output'] = rtrim($data['ecommerce_output'], ',');
						$data['ecommerce_output'] .= ']})};' . "\n</script>\n";
					}
					
					if ($this->config->get('remarketing_google_ads_identifier_cart_page')) {
						$data['google_output'] .= '<script>' . "\n";
						$data['google_output'] .= "if (typeof gtag != 'undefined') {" . "\n";
						$data['google_output'] .= 'gtag("event", "conversion", {' . "\n";
						$data['google_output'] .= "'send_to': '" . $this->config->get('remarketing_google_ads_identifier_cart_page') ."'," . "\n";
						$data['google_output'] .= "'value': " . $data['google_conversion_totalvalue'] . ",\n";
						$data['google_output'] .= "'currency': '" . $google_currency . "'\n";
						$data['google_output'] .= '})};'."\n</script>\n";
					} 
					
					if ($this->config->get('remarketing_ecommerce_measurement_status') && $uuid) {

						$ecommerce_data = [
							'v'   => 1,
							'tid' => $this->config->get('remarketing_ecommerce_analytics_id'),
							'cid' => $uuid,
							't'   => 'event',
							'ec'  => 'Enhanced Ecommerce',
							'ea'  => 'Checkout Step ' . $step, 
							'ni'  => 1,
							'cu'  => $ecommerce_currency,
							'pa'  => 'checkout'
						]; 
				
					if ($this->customer->isLogged()) {
						$ecommerce_data['uid'] = $this->customer->isLogged();
						unset($ecommerce_data['cid']);
					}
		
					$i = 1;
					if ($products) {
						foreach ($products as $product){
							$ecommerce_data['pr' . $i .'nm'] = $product['name'];
							$ecommerce_data['pr' . $i .'id'] = $product[$ecommerce_measurement_id];
							$ecommerce_data['pr' . $i .'pr'] = $this->currency->format($product['price'], $ecommerce_currency, '', false);
							$ecommerce_data['pr' . $i .'br'] = $this->model_catalog_product->getProduct($product['product_id'])['manufacturer'];
							$ecommerce_data['pr' . $i .'ca'] = $this->model_catalog_product->getRemarketingCategories($product['product_id']);
							if (!empty($product['variant'])) $ecommerce_data['pr' . $i .'va'] = $product['variant'];
							$ecommerce_data['pr' . $i .'qt'] = $product['quantity'];
							$i++;
						}
					}
					
					$data['ecommerce_output'] .= '<script>window.ecommerce_data = window.ecommerce_data || {};' . "\n";
					$data['ecommerce_output'] .= 'ecommerce_data = ' . json_encode($ecommerce_data) . ";\n";
					$data['ecommerce_output'] .= "if (typeof sendEcommerceCart !== 'undefined') {\n";
					$data['ecommerce_output'] .= "sendEcommerceCart(ecommerce_data); \n";
					$data['ecommerce_output'] .= "}\n";
					$data['ecommerce_output'] .= '</script>' . "\n";
					}
					
					if ($this->config->get('remarketing_ecommerce_ga4_measurement_status') && $uuid) {
						
						$list_products = [];
						foreach ($products as $product) {
							$product_info = $this->model_catalog_product->getProduct($product['product_id']);
							$categories = addslashes($this->model_catalog_product->getRemarketingCategories($product['product_id']));
							
							$ga4_product = [
								'item_name' => addslashes($product['name']),
								'quantity' => $product['quantity'],
								'affiliation' => $this->config->get('config_name'),
								'price' => $this->currency->format($product['price'], $ecommerce_currency, '', false),
								'currency' => $ecommerce_currency
							];
							
							if (!empty($categories)) $ga4_product['item_category'] = $categories;
							if (!empty($product_info['manufacturer'])) $ga4_product['item_brand'] = $product_info['manufacturer'];
							$list_products[] = $ga4_product;
						}

						$ecommerce_ga4_data = [
							'client_id' => $uuid,
							'events' => [[
								'name' => $route != 'checkout/cart' ? 'begin_checkout' : 'view_cart',
								'params' => [
									'currency' => $this->config->get('remarketing_ecommerce_currency'),
									'items' => $list_products,										
									'value' => $data['ecommerce_totalvalue']
								]],
							],
						];
					
						$data['ecommerce_output'] .= '<script>window.ecommerce_ga4_data = window.ecommerce_ga4_data || {};' . "\n";
						$data['ecommerce_output'] .= 'ecommerce_ga4_data = ' . json_encode($ecommerce_ga4_data) . ";\n";
						$data['ecommerce_output'] .= "if (typeof sendEcommerceGa4Cart !== 'undefined') {\n";
						$data['ecommerce_output'] .= "sendEcommerceGa4Cart(ecommerce_ga4_data); \n";
						$data['ecommerce_output'] .= "}\n";
						$data['ecommerce_output'] .= '</script>' . "\n";
					}
					
					if ($this->config->get('remarketing_facebook_status') && $this->config->get('remarketing_facebook_server_side') && $this->config->get('remarketing_facebook_token')) {
						$facebook_data['event_name'] = 'InitiateCheckout';
						$facebook_data['time'] = $fb_time;
						$fb_products = [];
						foreach ($data['facebook_ids'] as $product) {
							$fb_products[] = [
								'id'         => $product[$facebook_id],
								'quantity'   => $product['quantity'],
								'item_price' => $this->currency->format($product['price'], $facebook_currency, '', false)
							];
						}
						$facebook_data['custom_data'] = [
							'value'        => $data['facebook_totalvalue'],
							'currency'     => $facebook_currency,
							'contents'     => $fb_products,
							'num_items'    => count($fb_products),
							'content_type' => 'product',
							'opt_out'      => false
						];
						
						$data['ecommerce_output'] .= '<script>window.facebook_data = window.facebook_data || {};' . "\n";
						$data['ecommerce_output'] .= 'facebook_data = ' . json_encode($facebook_data) . ";\n";
						$data['ecommerce_output'] .= "if (typeof sendFacebookCart !== 'undefined') {\n";
						$data['ecommerce_output'] .= "sendFacebookCart(facebook_data); \n";
						$data['ecommerce_output'] .= "}\n";
						$data['ecommerce_output'] .= '</script>' . "\n";
					}
					break;	
				case 'checkout/success':
				case 'extension/payment/yandexplusplus/status':
					$data['google_page'] = 'purchase';
					$data['facebook_page'] = 'purchase';
					$data['tiktok_page'] = 'purchase';
					$data['mytarget_page'] = 'purchase';
					$data['vk_page'] = 'purchase';
					if (!empty($this->request->cookie['remarketing_order_id']) || !empty($this->session->data['order_id']) || !empty($this->session->data['remarketing_order_id'])) {
						$remarketing_order_id = !empty($this->session->data['order_id']) ? $this->session->data['order_id'] : $this->session->data['remarketing_order_id'];
						if (!empty($this->request->cookie['remarketing_order_id'])) $remarketing_order_id = $this->request->cookie['remarketing_order_id'];
						$order_info = $this->model_tool_remarketing->getOrderRemarketing($remarketing_order_id);
						if ($order_info) {
							if ($order_info['products']) {
								foreach ($order_info['products'] as $product) {
									$data['google_ids'][] = $product[$google_id];
									$data['facebook_ids'][] = $product;
									$data['tiktok_ids'][] = $product;
									$data['mytarget_ids'][] = $product[$mytarget_id];		
									$data['vk_ids'][] = $product;		
								}							
							}
							$data['totalvalue'] = $this->currency->format($order_info['total'], $this->session->data['currency'], '', false);
							$data['google_totalvalue'] = $this->currency->format($order_info['total'], $google_currency, '', false); 
							$data['google_conversion_totalvalue'] = $this->currency->format($order_info['total'] * $this->config->get('remarketing_google_ads_ratio'), $google_currency, '', false); 
							$data['facebook_totalvalue'] = $this->currency->format($order_info['total'], $facebook_currency, '', false); 
							$data['ecommerce_totalvalue'] = $this->currency->format($order_info['total'], $ecommerce_currency, '', false); 
							
							$data['google_reviews_page'] = true;
							$data['reviews_order_id'] = $order_info['order_id'];
							$data['reviews_order_email'] = $order_info['email'];
							$data['reviews_order_date'] = date('Y-m-d', time() + 3600 * 24 * (int)$this->config->get('remarketing_reviews_date'));
							if ($this->config->get('remarketing_events_purchase')) {
								$data['events_output'] .= "<script>\n";
								$remarketing_events_purchase = html_entity_decode($this->config->get('remarketing_events_purchase'));
								$remarketing_events_purchase = str_replace(['{order_id}', '{order_total}'], [$order_info['order_id'], $order_info['default_total']], $remarketing_events_purchase);
								$data['events_output'] .= $remarketing_events_purchase;
								$data['events_output'] .= "</script>\n";     
							}
					
							if ($this->config->get('remarketing_google_ads_identifier')) {
								$data['google_output'] .= '<script>'."\n";
								$data['google_output'] .= "if (typeof gtag != 'undefined') {"."\n";
								$data['google_output'] .= 'gtag("event", "conversion", {'."\n";
								$data['google_output'] .= "'send_to': '" . $this->config->get('remarketing_google_ads_identifier') ."'," . "\n";
								$data['google_output'] .= "'value': " . $order_info['google_conversion_total'] . ",\n";
								$data['google_output'] .= "'currency': '". $google_currency . "'\n";
								$data['google_output'] .= '})};'."\n</script>\n";
							}
				 
							if ($this->config->get('remarketing_ecommerce_status')) {		
								$data['ecommerce_output'] = '';
								$data['ecommerce_output'] .= '<script>' . "\n";
								$data['ecommerce_output'] .= 'window.dataLayer = window.dataLayer || [];' . "\n";
								$data['ecommerce_output'] .= 'dataLayer.push({' . "\n"; 
								$data['ecommerce_output'] .= "'ecommerce': {" . "\n";
								$data['ecommerce_output'] .= "'currencyCode': '" . $ecommerce_currency . "'," . "\n";
								$data['ecommerce_output'] .= "'purchase': {" . "\n";
								$data['ecommerce_output'] .= "'actionField': {'id': '". $order_info['order_id'] . "'," . "\n";
								$data['ecommerce_output'] .= "'affiliation': '" . $order_info['store_name'] . "'," . "\n";
								if (!empty($order_info['coupon'])) $data['ecommerce_output'] .= "'coupon': '" . $order_info['coupon'] . "',\n";
								$data['ecommerce_output'] .= "'revenue': '" . $order_info['ecommerce_total'] . "',\n";
								$data['ecommerce_output'] .= "'shipping': " . $order_info['shipping'] . "\n},";
								$data['ecommerce_output'] .= "'products': ["."\n";
								foreach ($order_info['products'] as $product) {
									$data['ecommerce_output'] .= "{"."\n";
									$data['ecommerce_output'] .= "'name': '" . addslashes($product['name']) . "'," . "\n";
									$data['ecommerce_output'] .= "'id': '" . $product[$ecommerce_id] . "'," . "\n";
									$data['ecommerce_output'] .= "'price': " . $product['ecommerce_price'] . "," . "\n";
									if (!empty($product['product_info']['manufacturer'])) $data['ecommerce_output'] .= "'brand': '" . addslashes($product['product_info']['manufacturer']) . "'," . "\n";
									$data['ecommerce_output'] .= "'category': '" . addslashes($product['category']) . "'," . "\n";
									if (!empty($product['variant'])) $data['ecommerce_output'] .= "'variant': '" . addslashes($product['variant']) . "'," . "\n";
									$data['ecommerce_output'] .= "'quantity': '" . $product['quantity'] . "'}," . "\n";
								}
								$data['ecommerce_output'] = rtrim($data['ecommerce_output'], ',');
								$data['ecommerce_output'] .= "]}},\n";
								$data['ecommerce_output'] .= "'event': 'gtm-ee-event',
								'gtm-ee-event-category': 'Enhanced Ecommerce',
								'gtm-ee-event-action': 'Purchase',
								'gtm-ee-event-non-interaction': 'False'";
								$data['ecommerce_output'] .= '});' . "\n</script>\n";
							}
							
							if ($this->config->get('remarketing_ecommerce_ga4_status')) {
								$data['ecommerce_output'] .= '<script>'."\n";
								$data['ecommerce_output'] .= "if (typeof gtag != 'undefined') {"."\n";
								$data['ecommerce_output'] .= 'gtag("event", "purchase", {'."\n";
								$data['ecommerce_output'] .= "'send_to': '" . $this->config->get('remarketing_ecommerce_ga4_identifier') ."'," . "\n";
								$data['ecommerce_output'] .= "'transaction_id': '" . $order_info['order_id'] ."'," . "\n";
								$data['ecommerce_output'] .= "'value': " . $order_info['ecommerce_total'] . ",\n";
								$data['ecommerce_output'] .= "'currency': '". $ecommerce_currency . "'\n,";
								if (!empty($order_info['coupon'])) $data['ecommerce_output'] .= "'coupon': '" . $order_info['coupon'] . "',\n";
								$data['ecommerce_output'] .= "'shipping': " . $order_info['shipping'] . ",\n";
								$data['ecommerce_output'] .= "'items': ["."\n";
								$i = 1;
								foreach ($order_info['products'] as $product) {
									$data['ecommerce_output'] .= "{"."\n";
									// Google refuses id $data['ecommerce_output'] .= "'item_id': '" . ($this->config->get('remarketing_ecommerce_ga4_id') == 'id' ? $product['product_id'] : $product['model']) . "'," . "\n";
									$data['ecommerce_output'] .= "'item_name': '" . addslashes($product['name']) . "'," . "\n";
									$data['ecommerce_output'] .= "'item_list_name': '" . addslashes($product['category']) . "'," . "\n";
									if (!empty($product['product_info']['manufacturer'])) $data['ecommerce_output'] .= "'item_brand': '" . addslashes($product['product_info']['manufacturer']) . "'," . "\n";
									if (!empty($product['category'])) $data['ecommerce_output'] .= "'item_category': '" . addslashes($product['category']) . "'," . "\n";
									if (!empty($product['variant'])) $data['ecommerce_output'] .= "'item_variant': '" . addslashes($product['variant']) . "'," . "\n";
									$data['ecommerce_output'] .= "'index': " . $i . "," . "\n";
									$data['ecommerce_output'] .= "'quantity': " . $product['quantity'] . ","."\n";
									$data['ecommerce_output'] .= "'price': " . $product['ecommerce_price'] . "," . "\n";
									$data['ecommerce_output'] .= "'affiliation': '" . $order_info['store_name'] . "'}," . "\n";
									$i++;
								}
								$data['ecommerce_output'] = rtrim($data['ecommerce_output'], ',');
								$data['ecommerce_output'] .= ']})};' . "\n</script>\n";
							}
							
							if ($this->config->get('remarketing_retailrocket_status') && $this->config->get('remarketing_retailrocket_identifier')) {
								$rocket_products = [];
								foreach ($order_info['products'] as $product) {
									$rocket_products[] = [
										'id'    => $product['product_id'],
										'qnt'   => $product['quantity'],
										'price' => $product['price']
									];
								}
								if ($rocket_products) {
									$data['rocket_output'] .= '<script>(window["rrApiOnReady"] = window["rrApiOnReady"] || []).push(function() {try { ' . (!empty($order_info['email']) ? ' rrApi.setEmail("' . $order_info['email'] . '");' : '') . 'rrApi.order({transaction: "' . $order_info['order_id'] . '",items: ' .  json_encode($rocket_products) . '});} catch(e) {}})</script>';
								}
							}
						} 
						$this->model_tool_remarketing->setSuccessPage($remarketing_order_id);
						unset($this->session->data['remarketing_order_id']); 
						unset($this->session->data['order_id']); 
						setcookie('remarketing_order_id', $remarketing_order_id, time() - 3600, '/');
					} else {
						$data['vk_page'] = 'view_other';
						$data['google_page'] = false;
						$data['facebook_page'] = false;
						$data['mytarget_page'] = false;
					}
					break;	 
				default:
					$data['vk_page'] = 'view_other';
					$data['google_page'] = false;
					$data['facebook_page'] = false;
					$data['mytarget_page'] = false;
					break;
			}
		
			if ($this->config->get('remarketing_google_status') && $this->config->get('remarketing_google_identifier')) {
		
				if ($data['google_page']) {
					$data['google_output'] .= '<script>' . "\n";
					$data['google_output'] .= "if (typeof gtag != 'undefined') {" . "\n";
					$data['google_output'] .= 'gtag("event", "' . $data['google_page'] . '", {' . "\n";
					$data['google_output'] .= "'send_to': '" . $this->config->get('remarketing_google_identifier') . "'," . "\n";
					$data['google_output'] .= "'value': ". (isset($data['google_totalvalue']) ? $data['google_totalvalue'] : $data['totalvalue']) . ",\n";
					if (!empty($data['google_ids'])) {
						$data['google_output'] .= "'items': [\n";
							foreach($data['google_ids'] as $item) {
								$data['google_output'] .= "{\n";
								$data['google_output'] .= "'id': '" . $item . "',\n";
								$data['google_output'] .= "'google_business_vertical': 'retail'\n";
								$data['google_output'] .= "},\n";
							}
						$data['google_output'] .= "]\n";
					}
					$data['google_output'] .= '})};' . "\n</script>\n";
				}
			}
		
			if ($this->config->get('remarketing_facebook_status') && $this->config->get('remarketing_facebook_identifier') && $this->config->get('remarketing_facebook_pixel_status')) {

				if ($data['facebook_page'] == 'purchase' || $data['facebook_page'] == 'initiate') {
					if (!empty($data['facebook_ids'])) {
						$data['facebook_output'] .= '<script>' . "\n";
						$data['facebook_output'] .= "$(document).ready(function() {" . "\n";
						$data['facebook_output'] .= "if (typeof fbq != 'undefined') {" . "\n";
						if ($data['facebook_page'] == 'purchase') {
							$data['facebook_output'] .= "fbq('track', 'Purchase', {" . "\n";
						} else {
							$data['facebook_output'] .= "fbq('track', 'InitiateCheckout', {" . "\n";	
						}
						$data['facebook_output'] .= "content_type: 'product'," . "\n";
						
						$num_items = 0;
						foreach ($data['facebook_ids'] as $product) {
							$num_items += $product['quantity'];
						}

						$data['facebook_output'] .= "num_items: " . $num_items . "," . "\n";
						if (count($data['facebook_ids']) == 1) {
							$data['facebook_output'] .= "content_ids: ['" . $data['facebook_ids'][0][$facebook_id] . "']," . "\n";
							$data['facebook_output'] .= "content_name: '" . addslashes($data['facebook_ids'][0]['name']) . "'," . "\n";
							if (!empty($data['facebook_ids'][0]['category'])) $data['facebook_output'] .= "content_category: '" . $data['facebook_ids'][0]['category'] . "'," . "\n";
						} else {  
							$data['facebook_output'] .= "contents: [" . "\n";
							foreach ($data['facebook_ids'] as $product) {
								$data['facebook_output'] .= "{" . "'id': '" . $product[$facebook_id] . "', 'price': " . $this->currency->format($product['price'], $facebook_currency, '', false) . ", 'quantity': " . $product['quantity'] . "},";
							}
							$data['facebook_output'] = rtrim($data['facebook_output'], ',');
							$data['facebook_output'] .= "],\n";
						}
						$data['facebook_output'] .= 'value: ' . $data['facebook_totalvalue'] . ',' . "\n";
						$data['facebook_output'] .= "currency: '" .  $facebook_currency . "'" . "\n";
						$data['facebook_output'] .= '}, {eventID: ' . $fb_time . '})}});' . "\n</script>\n";
						if ($this->config->get('remarketing_facebook_lead') && $data['facebook_page'] == 'purchase') {
							$data['facebook_output'] .= '<script>' . "\n";
							$data['facebook_output'] .= "$(document).ready(function() {" . "\n";
							$data['facebook_output'] .= "if (typeof fbq != 'undefined') {" . "\n";
							$data['facebook_output'] .= "fbq('track', 'Lead', {" . "\n";
							$data['facebook_output'] .= 'value: ' . $data['facebook_totalvalue'] . ',' . "\n";
							$data['facebook_output'] .= "currency: '" .  $facebook_currency . "'" . "\n";
							$data['facebook_output'] .= '}, {eventID: ' . $fb_time . '})}});' . "\n</script>\n";
						}
					}
				}
			}
			
			if ($this->config->get('remarketing_tiktok_status')) {
			
				if ($data['tiktok_page'] == 'purchase' || $data['tiktok_page'] == 'initiate') {
					if (!empty($data['tiktok_ids'])) {
						$data['tiktok_output'] .= '<script>' . "\n";
						$data['tiktok_output'] .= "$(document).ready(function() {" . "\n";
						$data['tiktok_output'] .= "if (typeof ttq != 'undefined') {" . "\n";
						if ($data['facebook_page'] == 'purchase') {
							$data['tiktok_output'] .= "ttq.track('Purchase', {" . "\n";
						} else {
							$data['tiktok_output'] .= "ttq.track('StartCheckout', {" . "\n";	
						}
						$data['tiktok_output'] .= "content_type: 'product'," . "\n";
						
						if (count($data['tiktok_ids']) == 1) {
							$data['tiktok_output'] .= "content_id: '" . $data['tiktok_ids'][0]['product_id'] . "'," . "\n";
							$data['tiktok_output'] .= "content_name: '" . addslashes($data['tiktok_ids'][0]['name']) . "'," . "\n";
							if (!empty($data['tiktok_ids'][0]['category'])) $data['tiktok_output'] .= "content_category: '" . $data['tiktok_ids'][0]['category'] . "'," . "\n";
						} else {  
							$data['tiktok_output'] .= "contents: [" . "\n";
							foreach ($data['tiktok_ids'] as $product) {
								$data['tiktok_output'] .= "{" . "'content_id': '" . $product['product_id'] . "', 'price': " . $this->currency->format($product['price'], $this->session->data['currency'], '', false) . ", 'quantity': " . $product['quantity'] . "},";
							}
							$data['tiktok_output'] = rtrim($data['tiktok_output'], ',');
							$data['tiktok_output'] .= "],\n";
						}
						$data['tiktok_output'] .= 'value: ' . $data['totalvalue'] . ',' . "\n";
						$data['tiktok_output'] .= "currency: '" .  $this->session->data['currency'] . "'" . "\n";
						$data['tiktok_output'] .= '})}});' . "\n</script>\n";
					}
				}
			}
		
			if ($this->config->get('remarketing_mytarget_status') && $this->config->get('remarketing_mytarget_identifier')) {	
				if ($data['mytarget_page']) {		
					if (!empty($data['mytarget_ids']) && count($data['mytarget_ids']) > 1) {
						$target_id = '[\'' . implode('\',\'', $data['mytarget_ids']) . '\']';
					} elseif (!empty($data['mytarget_ids'])) {
						$target_id =  "'" . $data['mytarget_ids'][0] . "'";
					} else {
						$target_id = '';	
					}
			
					$data['mytarget_output'] .= '<!-- Rating@Mail.ru counter dynamic remarketing appendix -->' . "\n";
					$data['mytarget_output'] .= '<script>' . "\n";
					$data['mytarget_output'] .= 'var _tmr = _tmr || [];' . "\n";
					$data['mytarget_output'] .= '_tmr.push({' . "\n";
					$data['mytarget_output'] .= "type: 'itemView'," . "\n";
					if (!empty($target_id)) $data['mytarget_output'] .= "productid: " . $target_id . "," . "\n";
					if (!empty($data['mytarget_page'])) $data['mytarget_output'] .= "pagetype: '" . $data['mytarget_page'] . "'," . "\n"; 
					$data['mytarget_output'] .= "list: '" . $this->config->get('remarketing_mytarget_identifier') . "'," . "\n";
					if (!empty($data['totalvalue'])) $data['mytarget_output'] .= "totalvalue: '" . $data['totalvalue'] . "'" . "\n"; 
					$data['mytarget_output'] .= '});' . "\n";
					$data['mytarget_output'] .= '</script>' . "\n";
					$data['mytarget_output'] .= '<!-- // Rating@Mail.ru counter dynamic remarketing appendix -->' . "\n";
				}
			}
			
			if ($this->config->get('remarketing_vk_status') && $this->config->get('remarketing_vk_identifier')) {	
				if ($data['vk_page']) {		
					$eventParams = [];
					$eventParams['currency_code'] = $this->session->data['currency'];
					if (!empty($data['vk_ids'])) {
						$eventParams['products'] = [];
						foreach ($data['vk_ids'] as $product) {
							$eventParams['products'][] = [
								'id'    => $product[$vk_id],
								'price' => $product['price']
							]; 
						}
					}
					if ($data['totalvalue'] > 0) $eventParams['total_price'] = $data['totalvalue']; 
					
					$data['vk_output'] .= '<script>' . "\n";
					$data['vk_output'] .= "$(document).ready(function() { setTimeout(function() { if (typeof VK != 'undefined') {" . "\n";
					$data['vk_output'] .= "VK.Retargeting.ProductEvent(" . $this->config->get('remarketing_vk_identifier') . ", '" . $data['vk_page'] . "', " . json_encode($eventParams) . ");" . "\n";
					$data['vk_output'] .= '}}, 1000)})' . "\n";
					$data['vk_output'] .= '</script>' . "\n"; 
				}
			}
	
			if ($data['google_reviews_page'] && $this->config->get('remarketing_reviews_status') && $this->config->get('remarketing_google_merchant_identifier')  && strpos($data['reviews_order_email'], 'localhost') === false) {			
				$data['google_reviews_output'] .= '<script src="https://apis.google.com/js/platform.js?onload=renderOptIn"  async defer></script>' . "\n";
				$data['google_reviews_output'] .= "<script>\n";     
				$data['google_reviews_output'] .= "window.renderOptIn = function() {\n";  
				$data['google_reviews_output'] .= "window.gapi.load('surveyoptin', function() {\n"; 
				$data['google_reviews_output'] .= "window.gapi.surveyoptin.render(\n"; 
				$data['google_reviews_output'] .= "{\n"; 
				$data['google_reviews_output'] .= " // ОБЯЗАТЕЛЬНО\n"; 
				$data['google_reviews_output'] .= '"merchant_id": ' . $this->config->get('remarketing_google_merchant_identifier') . ",\n"; 
				$data['google_reviews_output'] .= '"order_id": "' . $data['reviews_order_id'] . "\",\n"; 
				$data['google_reviews_output'] .= '"email": "' . $data['reviews_order_email'] . "\",\n"; 
				$data['google_reviews_output'] .= '"delivery_country": "' . $this->config->get('remarketing_reviews_country') . "\",\n"; 
				$data['google_reviews_output'] .= '"estimated_delivery_date": "' . $data['reviews_order_date'] . "\",\n"; 
				$data['google_reviews_output'] .= '"opt_in_style": "CENTER_DIALOG"' . "\n"; 
				$data['google_reviews_output'] .=  "}); });}\n"; 
				$data['google_reviews_output'] .=  "</script>"; 
			}
		
			if ($this->config->get('remarketing_counter3') && $this->config->get('remarketing_counter3')) {
				$data['counter_output'] .=  html_entity_decode($this->config->get('remarketing_counter3'));
			}
			
			if ($this->config->get('remarketing_events_cart_add')) {
				$data['events_output'] .= "<script>\n";
				$data['events_output'] .= "function events_cart_add() {\n";
				$data['events_output'] .= html_entity_decode($this->config->get('remarketing_events_cart_add')) . "\n";
				$data['events_output'] .= "}\n";     
				$data['events_output'] .= "</script>\n";     
			}
			
			if ($this->config->get('remarketing_events_wishlist')) {
				$data['events_output'] .= "<script>\n";
				$data['events_output'] .= "function events_wishlist() {\n";
				$data['events_output'] .= html_entity_decode($this->config->get('remarketing_events_wishlist')) . "\n";
				$data['events_output'] .= "}\n";     
				$data['events_output'] .= "</script>\n";     
			}
			if ($this->config->get('remarketing_events_purchase')) {
				$data['events_output'] .= "<script>\n";
				$data['events_output'] .= "function quickPurchase(order_id = false, order_total = false) {\n";
				$remarketing_events_purchase = html_entity_decode($this->config->get('remarketing_events_purchase'));
				$remarketing_events_purchase = str_replace(['{order_id}', '{order_total}'], ['order_id', 'order_total'], $remarketing_events_purchase);
				$data['events_output'] .= html_entity_decode($remarketing_events_purchase);
				$data['events_output'] .= "\n}\n";     
				$data['events_output'] .= "</script>\n";     
			}
			
			if ($this->config->get('remarketing_retailrocket_status') && $this->config->get('remarketing_retailrocket_identifier') && $this->config->get('remarketing_retailrocket_email_field')) {
				$data['rocket_output'] .= "<script>$(document).on('blur', '" . $this->config->get('remarketing_retailrocket_email_field'). "', function(){email = $(this).val();(window[\"rrApiOnReady\"] = window[\"rrApiOnReady\"] || []).push(function() {rrApi.setEmail(email);});});</script>";
			}
			
			return $data['google_output'] . "\n\n" . $data['facebook_output'] . "\n\n" . $data['tiktok_output'] . "\n\n" . $data['mytarget_output'] . "\n\n" . $data['vk_output'] . "\n\n" . $data['rocket_output'] . "\n\n" . $data['google_reviews_output'] . "\n\n" . $data['events_output'] . "\n\n" . $data['ecommerce_output'] . "\n\n" . $data['counter_output']; 
		
		}
	}
	
	public function sendMeasurementImpressions() {
		if (!empty($this->request->post)) {
			if (!empty($this->request->post['products'])) {
				$products_data = $this->request->post['products'];
				$this->load->model('tool/remarketing');
				$this->model_tool_remarketing->sendEcommerceImpressions($products_data, $this->request->post['heading']);
			}
		}
	}
	
	public function sendMeasurementManual() {
		$this->user = new Cart\User($this->registry);
		$post_key = '';
		if (!$this->user->isLogged() && $post_key == '') return;
		if ($post_key != '' && !empty($this->request->post['key']) && $this->request->post['key'] != $post_key) return;
		$json = [];
		$this->load->model('tool/remarketing');
		$this->load->model('catalog/product');
		if (!empty($this->request->post)) {
			if (!empty($this->request->post['manual_id'])) {
				$transaction_id = $this->request->post['manual_id'];
				if (!empty($this->request->post['manual_shipping'])) {
					$shipping = (int)$this->request->post['manual_shipping'];
				}
				if (!empty($this->request->post['manual_total'])) {
					$total = (int)$this->request->post['manual_total'];
				} else {
					$total = 0;
				}
				$this->model_tool_remarketing->getCid();
				$data['ecommerce_totalvalue'] = $this->currency->format($total, $this->config->get('remarketing_ecommerce_currency'), '', false); 
				$uuid = $this->model_tool_remarketing->getCid();	
				$ecommerce_data = [
					'v'   => 1,
					'tid' => $this->config->get('remarketing_ecommerce_analytics_id'),
					'cid' => $uuid,
					't'   => 'event',
					'ti'  => $transaction_id,
					'ta'  => $this->config->get('config_name'),
					'tr'  => $data['ecommerce_totalvalue'],
					'ec'  => 'Enhanced Ecommerce',
					'ea'  => 'Purchase', 
					'ni'  => 1,
					'cu'  => $this->config->get('remarketing_ecommerce_currency'),
					'pa'  => 'purchase'
				]; 
				if (!empty($shipping)) $ecommerce_data['ts'] = $this->currency->format($shipping, $this->config->get('remarketing_ecommerce_currency'), '', false);
				
				$i = 1;
				if (!empty($this->request->post['manual_products'])) {
					foreach ($this->request->post['manual_products'] as $manual_product) {
						$product = $this->model_catalog_product->getProduct($manual_product['product_id']);
						$ecommerce_data['pr' . $i .'nm'] = $manual_product['name'];
						$ecommerce_data['pr' . $i .'id'] = $manual_product['product_id'];
						$ecommerce_data['pr' . $i .'pr'] = $this->currency->format($manual_product['price'], $this->config->get('remarketing_ecommerce_currency'), '', false);
						if (!empty($product['manufacturer'])) $ecommerce_data['pr' . $i .'br'] = $product['manufacturer'];
						$ecommerce_data['pr' . $i .'ca'] = $this->model_catalog_product->getRemarketingCategories($product['product_id']);
						$ecommerce_data['pr' . $i .'qt'] = $manual_product['quantity'];
						$i++;
					}
				}
				$this->model_tool_remarketing->sendEcommerce($ecommerce_data);
				$json['success'] = 'Transaction Sent!';
			} else {
				$json['error'] = 'No transaction ID';
			}
		} else {
			$json['error'] = 'No data';
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function sendFacebookManual() {
		$this->user = new Cart\User($this->registry);
		$post_key = '';
		if (!$this->user->isLogged() && $post_key == '') return;
		if ($post_key != '' && !empty($this->request->post['key']) && $this->request->post['key'] != $post_key) return;
		$json = [];
		$this->load->model('tool/remarketing');
		$this->load->model('catalog/product');
		if (!empty($this->request->post)) {
			if (!empty($this->request->post['manual_facebook_total'])) {
				$total = (int)$this->request->post['manual_facebook_total'];
				$fb_time = time();
				$facebook_data = [];
				$facebook_data['event_name'] = 'Purchase';
				$fb_products = [];
				$num_items = 0;
				if (!empty($this->request->post['manual_facebook_products'])) {
				foreach ($this->request->post['manual_facebook_products'] as $manual_facebook_product) {
					$fb_products[] = [
						'id'         => $manual_facebook_product['product_id'],
						'quantity'   => $manual_facebook_product['quantity'],
						'item_price' => $this->currency->format($manual_facebook_product['price'], $this->config->get('remarketing_facebook_currency'), '', false)
					];
					$num_items += $manual_facebook_product['quantity'];
				}
				}
				$facebook_data['custom_data'] = [
					'value'        => $this->currency->format($total, $this->config->get('remarketing_facebook_currency'), '', false),
					'currency'     => $this->config->get('remarketing_facebook_currency'),
					'contents'     => $fb_products,
					'num_items'    => $num_items,
					'content_type' => 'product',
					'opt_out'      => false
				];
				
				$facebook_data['time'] = $fb_time;
				 
				$this->model_tool_remarketing->sendFacebook($facebook_data, false);

				$json['success'] = 'Transaction Sent!';
			} else {
				$json['error'] = 'No total';
			}
		} else {
			$json['error'] = 'No data';
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function sendSuccessManual() {
		$this->user = new Cart\User($this->registry);
		$post_key = '';
		if (!$this->user->isLogged() && $post_key == '') return;
		if ($post_key != '' && !empty($this->request->post['key']) && $this->request->post['key'] != $post_key) return;
		$order_id = !empty($this->request->get['order_id']) ? $this->request->get['order_id'] : false;
		if (!$order_id) return;
		$this->load->model('tool/remarketing');
		$order_info = $this->model_tool_remarketing->getOrderRemarketing($order_id);
		if (!$order_info) return;
		if ($order_info['sent_data']['success_page'] == '0000-00-00 00:00:00') {
			$url = '';
			$get_parameters = [
				'fbclid', 'gclid', 'dclid', 'utm_source', 'utm_campaign', 'utm_medium', 'utm_term', 'utm_content', 'yclid', 'ymclid' 
			];
			foreach ($order_info['sent_data'] as $key => $value) {
				if (in_array($key, $get_parameters) && !empty($value)) $url .= '&' . $key . '=' . $value;
			}
			
			$this->session->data['remarketing_order_id'] = $order_id;
			$this->response->redirect($this->url->link('checkout/success', $url, 'SSL'));
		}
	}
	
	public function sendGa4MeasurementImpressions() {
		if (isset($this->request->post)) {
			if (isset($this->request->post['products'])) {
				$product_data = $this->request->post['products'];
				$list_products = [];
				foreach($product_data as $product) {
					unset($product['item_id']); // Google refuse
					unset($product['index']); // Google refuse
					$product['currency'] = $this->config->get('remarketing_ecommerce_currency');
					$list_products[] = $product;
				}
				
				$this->load->model('tool/remarketing');
				
				$uuid = $this->model_tool_remarketing->getCid();
				
				$ecommerce_data = [
					'client_id' => $uuid,
					'events' => [[
						'name' => $this->request->post['event_name'],
						'params' => [
							'item_list_name' => $this->request->post['heading'],
							'items' => $list_products
						]],
					],
				];
				
				$this->model_tool_remarketing->sendEcommerceGa4($ecommerce_data);
			}
		}
	}
	
	public function sendDetails() {
		if (isset($this->request->post)) {
			if (isset($this->request->post['products'])) {
				$products_data = $this->request->post['products'];
				$this->load->model('tool/remarketing'); 
				$this->model_tool_remarketing->sendEcommerceDetails($products_data['ecommerce']['detail'], !empty($products_data['ecommerce']['impressions']) ? $products_data['ecommerce']['impressions'] : [], $this->request->post['heading']);
			}
		}
	}
	
	public function sendGa4Details() {
		if (isset($this->request->post)) {
			if (isset($this->request->post['products']['items'][0])) {
				$product_data = $this->request->post['products']['items'][0];
				unset($product_data['item_id']); // Google refuse
				unset($product_data['index']); // Google refuse
				$this->load->model('tool/remarketing'); 
				$uuid = $this->model_tool_remarketing->getCid();
				
				$ecommerce_data = [
					'client_id' => $uuid,
					'events' => [[
						'name' => 'view_item',
						'params' => [
							'currency' => $this->config->get('remarketing_ecommerce_currency'),
							'items' => [$product_data],
							'value' => $product_data['price']
						]],
					],
				];
				
				$this->model_tool_remarketing->sendEcommerceGa4($ecommerce_data);
			}
		}
	}
	
	public function sendEcommerceCart() {
		if (isset($this->request->post)) {
			if (isset($this->request->post['cart'])) {
				$data = $this->request->post['cart'];
				$this->load->model('tool/remarketing'); 
				$this->model_tool_remarketing->sendEcommerceCart($data);
			}
		}
	}
	
	public function sendEcommerceGa4Cart() {
		if (isset($this->request->post)) {
			if (isset($this->request->post['cart'])) {
				$data = $this->request->post['cart'];
				$this->load->model('tool/remarketing'); 
				$this->model_tool_remarketing->sendEcommerceGa4($data);
			}
		}
	}
	
	public function sendFacebookDetails() {
		if (isset($this->request->post)) {
			if (isset($this->request->post['products']) && $this->config->get('remarketing_facebook_server_side') && $this->config->get('remarketing_facebook_token')) {
				$facebook_data['event_name'] = 'ViewContent';
				$facebook_data['custom_data'] = $this->request->post['products'];
				$facebook_data['time'] = $this->request->post['time'];
				$facebook_data['url'] = $this->request->post['url'];
				$this->load->model('tool/remarketing');
				$this->model_tool_remarketing->sendFacebook($facebook_data);
			}
		}
	}
	
	public function sendFacebookCart() {
		if (isset($this->request->post)) {
			if (isset($this->request->post['cart']) && $this->config->get('remarketing_facebook_server_side') && $this->config->get('remarketing_facebook_token')) {
				$facebook_data['event_name'] = 'InitiateCheckout';
				$facebook_data['custom_data'] = $this->request->post['cart']['custom_data'];
				$facebook_data['url'] = $this->request->post['url'];
				$facebook_data['time'] = $this->request->post['cart']['time'];
				$this->load->model('tool/remarketing');
				$this->model_tool_remarketing->sendFacebook($facebook_data);
			}
		}
	}
	
	public function sendFacebookCategory() {
		if (isset($this->request->post)) {
			if (isset($this->request->post['products']) && $this->config->get('remarketing_facebook_server_side') && $this->config->get('remarketing_facebook_token')) {
				$facebook_data['event_name'] = 'ViewCategory';
				$facebook_data['custom_data'] = $this->request->post['products'];
				$facebook_data['time'] = $this->request->post['time'];
				$facebook_data['url'] = $this->request->post['url'];
				$this->load->model('tool/remarketing');
				$this->model_tool_remarketing->sendFacebook($facebook_data);
			}
		}
	}
	
	public function sendEsputnik() {
		if (isset($this->request->post) && isset($this->session->data['esputnik_email'])) {
			if (isset($this->request->post['product']) && $this->config->get('remarketing_esputnik_status') && $this->customer->isLogged()) {
				$event_type = 'productViewed';
				if ($event_type) {
					$event = new stdClass();
					$event->eventTypeKey = $event_type;
					$event->keyValue = $this->session->data['esputnik_email'];
					$event->params = [];
					if (isset($this->session->data['esputnik_telephone'])) {
						$event->params[] = ['name' => 'phone', 'value' => $this->session->data['esputnik_telephone']];
					}
					$event->params[] = ['name' => 'email', 'value' => $this->session->data['esputnik_email']];
					$event->params[] = ['name' => 'currencyCode', 'value' => $this->session->data['currency']];
					if ($this->customer->isLogged()) {
						$event->params[] = ['name' => 'externalCustomerId', 'value' => $this->customer->isLogged()];
					}
					
					$event->params[] = ['name' => 'product', 'value' => json_encode($this->request->post['product'])];
	
					$this->load->model('tool/remarketing');
					$this->model_tool_remarketing->sendEsputnik($event);
				}
			}
		}
	}
	
	public function sendEsputnikCategory() {
		if (isset($this->request->post) && isset($this->session->data['esputnik_email'])) {
			if (isset($this->request->post['category']) && $this->config->get('remarketing_esputnik_status') && $this->customer->isLogged()) {
				$event_type = 'productCategoryViewed';
				if ($event_type) {
					$event = new stdClass();
					$event->eventTypeKey = $event_type;
					$event->keyValue = $this->session->data['esputnik_email'];
					$event->params = [];
					if (isset($this->session->data['esputnik_telephone'])) {
						$event->params[] = ['name' => 'phone', 'value' => $this->session->data['esputnik_telephone']];
					}
					$event->params[] = ['name' => 'email', 'value' => $this->session->data['esputnik_email']];
					$event->params[] = ['name' => 'currencyCode', 'value' => $this->session->data['currency']];
					if ($this->customer->isLogged()) {
						$event->params[] = ['name' => 'externalCustomerId', 'value' => $this->customer->isLogged()];
					}
					
					$event->params[] = ['name' => 'category', 'value' => json_encode($this->request->post['category'])];
	
					$this->load->model('tool/remarketing');
					$this->model_tool_remarketing->sendEsputnik($event);
				}
			}
		}
	}

	public function sendMeasurementClick() {
		if (isset($this->request->post)) {
			if (isset($this->request->post['products'])) {
				$products_data = $this->request->post['products'];
				$this->load->model('tool/remarketing'); 
				$this->model_tool_remarketing->sendMeasurementClick($products_data, $this->request->post['heading']);
			}
		}
	}
	
	public function sendGa4MeasurementClick() {
		if (isset($this->request->post)) {
			
			if (isset($this->request->post['products'])) {
				$product_data = $this->request->post['products'];
				unset($product_data['item_id']); // Google refuse
				unset($product_data['index']); // Google refuse
				$this->load->model('tool/remarketing'); 
				$uuid = $this->model_tool_remarketing->getCid();
				
				$ecommerce_data = [
					'client_id' => $uuid,
					'events' => [[
						'name' => 'select_item',
						'params' => [
							'currency' => $this->config->get('remarketing_ecommerce_currency'),
							'items' => [$product_data],
							'value' => $product_data['price']
						]],
					],
				];
				
				$this->model_tool_remarketing->sendEcommerceGa4($ecommerce_data);
			}
		}
	}
	
	public function removeProduct() {
		if (isset($this->request->post)) {
			if (isset($this->request->post['product_id']) && isset($this->request->post['quantity'])) {
				if ($this->config->get('remarketing_status')) {
					$this->load->model('catalog/product');
					$this->load->model('tool/remarketing');
					
					$product_info = $this->model_catalog_product->getProduct($this->request->post['product_id']);
					$quantity = $this->request->post['quantity'];
					$json = [];
					$json['remarketing'] = [];
					if ($product_info) {
						$categories = $this->model_catalog_product->getRemarketingCategories($product_info['product_id']);
						$json['remarketing']['google_product_id'] = $this->config->get('remarketing_google_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
						$json['remarketing']['google_identifier'] = $this->config->get('remarketing_google_identifier');;
						$json['remarketing']['facebook_product_id'] = $this->config->get('remarketing_facebook_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
						$json['remarketing']['mytarget_product_id'] = $this->config->get('remarketing_mytarget_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
						$json['remarketing']['vk_product_id'] = $this->config->get('remarketing_vk_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
						$json['remarketing']['vk_identifier'] = $this->config->get('remarketing_vk_identifier');
						$json['remarketing']['ecommerce_product_id'] = $this->config->get('remarketing_ecommerce_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
						$json['remarketing']['ecommerce_ga4_product_id'] = $this->config->get('remarketing_ecommerce_ga4_id') == 'id' ? $product_info['product_id'] : $product_info['model'];
						$json['remarketing']['ecommerce_status'] = $this->config->get('remarketing_ecommerce_status');
						$json['remarketing']['ecommerce_ga4_status'] = $this->config->get('remarketing_ecommerce_ga4_status');
						$json['remarketing']['ecommerce_ga4_identifier'] = $this->config->get('remarketing_ecommerce_ga4_identifier');
						$json['remarketing']['google_status'] = $this->config->get('remarketing_google_status');
						$json['remarketing']['facebook_status'] = $this->config->get('remarketing_facebook_status');
						$json['remarketing']['facebook_pixel_status'] = $this->config->get('remarketing_facebook_pixel_status');
						$json['remarketing']['vk_status'] = $this->config->get('remarketing_vk_status');
						$json['remarketing']['quantity'] = $quantity; 
						$json['remarketing']['price'] = $this->currency->format(($product_info['special'] ? $product_info['special'] : $product_info['price']), $this->session->data['currency'], '', false);
						$json['remarketing']['google_price'] = $this->currency->format(($product_info['special'] ? $product_info['special'] : $product_info['price']), $this->config->get('remarketing_google_currency'), '', false);
						$json['remarketing']['facebook_price'] = $this->currency->format(($product_info['special'] ? $product_info['special'] : $product_info['price']), $this->config->get('remarketing_facebook_currency'), '', false);
						$json['remarketing']['ecommerce_price'] = $this->currency->format(($product_info['special'] ? $product_info['special'] : $product_info['price']), $this->config->get('remarketing_ecommerce_currency'), '', false);
						$json['remarketing']['brand'] = addslashes($product_info['manufacturer']);
						$json['remarketing']['name'] = addslashes($product_info['name']);
						$json['remarketing']['category'] = addslashes($categories);
						$json['remarketing']['currency'] = $this->session->data['currency'];
						$json['remarketing']['google_currency'] = $this->config->get('remarketing_google_currency');
						$json['remarketing']['facebook_currency'] = $this->config->get('remarketing_facebook_currency');
						$json['remarketing']['ecommerce_currency'] = $this->config->get('remarketing_ecommerce_currency');
						$uuid = $this->model_tool_remarketing->getCid();

						if ($this->config->get('remarketing_ecommerce_measurement_status')) {
							if ($uuid) {
							$ecommerce_data = [ 
								'v'     => 1,
								'tid'   => $this->config->get('remarketing_ecommerce_analytics_id'),
								'cid'   => $uuid,
								't'     => 'event',
								'ec'    => 'Enhanced Ecommerce',
								'ea'    => 'Removing a Product from a Shopping Cart',
								'ni'    => 1,
								'cu'    => $json['remarketing']['ecommerce_currency'],
								'pa'    => 'remove',
								'pr1nm' => $product_info['name'],
								'pr1id' => $json['remarketing']['ecommerce_product_id'],
								'pr1pr' => $json['remarketing']['ecommerce_price'],
								'pr1ca' => $categories,
								'pr1qt' => $quantity
							];
							
							$this->model_tool_remarketing->sendEcommerce($ecommerce_data);
							}
						}
						
						if ($this->config->get('remarketing_ecommerce_ga4_measurement_status')) {
							$uuid = $this->model_tool_remarketing->getCid();
							if ($uuid) {							
							$ecommerce_data = [
								'client_id' => $uuid,
								'events' => [[
									'name' => 'remove_from_cart',
									'params' => [
										'currency' => $this->config->get('remarketing_ecommerce_currency'),
										'items' => [[
											// Google refuses id 'item_id' => ($this->config->get('remarketing_ecommerce_ga4_measurement_id') == 'id') ? $product_info['product_id'] : $product_info['model'],
											'item_name' => $product_info['name'],
											'affiliation' => $this->config->get('config_name'),
											'item_brand' => $json['remarketing']['brand'],
											'item_category' => $categories,
											'price' => $json['remarketing']['ecommerce_price'],
											'currency' => $this->config->get('remarketing_ecommerce_currency'),
											'quantity' => $quantity,
										]],
										'value' => $json['remarketing']['ecommerce_price']
									]],
								],
							]; 
							
							$this->model_tool_remarketing->sendEcommerceGa4($ecommerce_data);
							}
						}
						
						$this->response->addHeader('Content-Type: application/json');
						$this->response->setOutput(json_encode($json));
					}
				}
			}
		}
	}
}