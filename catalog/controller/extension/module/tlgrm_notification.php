<?php
/*
@author	Artem Serbulenko
@link	http://cmsshop.com.ua
@link	https://opencartforum.com/profile/762296-bn174uk/
@email 	serfbots@gmail.com
*/  
class ControllerExtensionModuleTlgrmNotification extends Controller {
	
	public function messageOrder($arg = '') {
		
		$tlgrm_ntf_id = $this->config->get('module_tlgrm_notification_id');

		if (strlen($tlgrm_ntf_id) > 0) {

			$common_info = array(
				'order_id',
				'store_name',
				'store_url',
				'date_added',
				'firstname',
				'lastname',
				'email',
				'telephone',
				'comment',
				'total',
				'order_status',
				'customer_id',
				'payment_method',
				'shipping_method',
            );

            $shipping_info = array(
				'shipping_firstname',
				'shipping_lastname',
				'shipping_company',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_postcode',
				'shipping_city',
				'shipping_zone',
				'shipping_country',
			);

			$payment_info = array(
                'payment_firstname',
                'payment_lastname',
                'payment_company',
                'payment_address_1',
                'payment_address_2',
                'payment_postcode',
                'payment_city',
                'payment_zone',
                'payment_country',
            );
            
            $order_product = array(
				'name',
				'sku',
				'model',
				'id',
				'link',
				'quantity',
				'price',
				'option',
				'total',
				'upc',
				'ean',
				'jan',
				'isbn',
				'mpn',
				'location',
            );

            $msg_title = array(
            	'title_order',
            	'title_shipping',
            	'title_payment',
            	'title_product',
            	'title_simple',
            );

            $msg_separator = array(
            	'separator_order',
            	'separator_shipping',
            	'separator_payment',
            	'separator_product',
            	'separator_simple',
            );

            $message = '';
            $order_id = '';

			$this->load->language('extension/module/tlgrm_notification');

		    $this->load->model('extension/module/tlgrm_notification');
		    $this->load->model('checkout/order');
		    $this->load->model('account/order');
		    $this->load->model('catalog/product');

		    if (isset($this->session->data['order_id'])) {
		    	$order_id = $this->session->data['order_id'];
		    }

		    if (isset($arg['order_id']) && !empty($arg['order_id'])) {
		    	$order_id = $arg['order_id'];
		    }
			
		   	$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {

				foreach ($msg_title as $key => $value) {
					$title_text = $this->language->get("text_$value");
		   			$title_config = $this->config->get('module_tlgrm_notification_'.$value);
					if (strlen($title_config) > 0) {
						$title_text = $title_config;
					}
					$msg_title[$key] = $title_text;
				}	

				foreach ($msg_separator as $key => $value) {
					$separator_text = PHP_EOL;
		   			$separator_config = $this->config->get('module_tlgrm_notification_'.$value);
					if (strlen($separator_config) > 0) {
						$separator_text = $separator_config . PHP_EOL;
					}
					$msg_separator[$key] = $separator_text;
				}	

				foreach ($common_info as $key => $value) {
					if ($this->config->get('module_tlgrm_notification_'.$value) && isset($order_info[$value])) {
						$title_text = $this->language->get("text_$value");
			   			$title_config = $this->config->get('module_tlgrm_notification_text_'.$value);
						if (strlen($title_config) > 0) {
							$title_text = $title_config;
						}

						$temp = '';

						switch ($value) {
							case 'order_status':
								if (!empty($order_info['order_status_id'])) {
								   	$order_status = $this->model_extension_module_tlgrm_notification->getOrderStatus($order_info['order_status_id']);
								   	if (!empty($order_status)) {
									   	$temp = $order_status['name'];
						   			}
								}
								break;
							case 'customer_id':
								$temp = $this->model_extension_module_tlgrm_notification->getOrderCustomerGroup($order_id);
								break;
							case 'total':
			   					$order_totals = $this->model_extension_module_tlgrm_notification->getOrderTotals($order_id);
			   					$temp = PHP_EOL;
								foreach ($order_totals as $total) {
									if ($total['code'] == 'shipping') {
										$temp .= '-'. $this->language->get("text_shipping_price") . ': <b>'. $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']) . '</b>' . PHP_EOL;
									} else {
										$temp .= '-'. $total['title'] . ': <b>' . $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']) . '</b>' . PHP_EOL;
									}
								}
								$temp = rtrim($temp);
								break;
							default:
								$temp = $order_info[$value];
								break;
						}
						
						$common_info[$key] = array(
							'title' => '<b>'. $title_text .': </b>',
							'sort'	=> $this->config->get('module_tlgrm_notification_sort_'.$value),
							'value'	=> $temp,
						);
					} else {
						$common_info[$key] = array('sort'	=> 'a','value'	=> '');
					}
				}

				foreach ($shipping_info as $key => $value) {
					if ($this->config->get('module_tlgrm_notification_'.$value) && isset($order_info[$value])) {
						$title_text = $this->language->get("text_$value");
			   			$title_config = $this->config->get('module_tlgrm_notification_text_'.$value);
						if (strlen($title_config) > 0) {
							$title_text = $title_config;
						}

						$shipping_info[$key] = array(
							'title' => '<b>'. $title_text .': </b>',
							'sort'	=> $this->config->get('module_tlgrm_notification_sort_'.$value),
							'value'	=> $order_info[$value],
						);
					} else {
						$shipping_info[$key] = array('sort' => 'a','value'	=> '');
					}
				}	

				foreach ($payment_info as $key => $value) {
					if ($this->config->get('module_tlgrm_notification_'.$value) && isset($order_info[$value])) {
						$title_text = $this->language->get("text_$value");
			   			$title_config = $this->config->get('module_tlgrm_notification_text_'.$value);
						if (strlen($title_config) > 0) {
							$title_text = $title_config;
						}

						$payment_info[$key] = array(
							'title' => '<b>'. $title_text .': </b>',
							'sort'	=> $this->config->get('module_tlgrm_notification_sort_'.$value),
							'value'	=> $order_info[$value],
						);
					} else {
						$payment_info[$key] = array('sort' => 'a','value'	=> '');
					}
				}

				$simple = $this->config->get('module_tlgrm_notification_simple');

				if ($simple !== null) {
					$simple_info = array();
					$simple_fields = $this->model_extension_module_tlgrm_notification->getOrderSimpleFields($order_id);
					if (!empty($simple_fields)) {
						$simple_fields_order = explode(',', $simple_fields['metadata']);

						$simple_settings = json_decode($this->config->get('simple_settings'), true);
						$result = array();
						if (!empty($simple_settings['fields'])) {
						    foreach ($simple_settings['fields'] as $fieldSettings) {
						        if ($fieldSettings['custom']) {
						            $result[$fieldSettings['id']] = $fieldSettings;
						        }
						    }
						    $simple_count = 0;
						    foreach ($result as $key => $value) {
			    				$lang_code = str_replace('-', '_', $this->config->get('config_language'));
			    				if (isset($simple[$value['id']]) && $simple[$value['id']]) {
			    					$title_text = $value['label'][$lang_code];
						   			$title_config = $this->config->get('module_tlgrm_notification_text_simple')[$value['id']];
									if (strlen($title_config) > 0) {
										$title_text = $title_config;
									}

									$simple_field = '';

									if (!isset($simple_fields[$value['id']])) {
										foreach ($simple_fields_order as $sfo_key => $sfo_value) {
											if (strstr($sfo_value, $value['id'])) {
												$simple_field = $simple_fields[$sfo_value];
											}
										}
									} else {
										$simple_field = $simple_fields[$value['id']];
									}

									$sort = $this->config->get('module_tlgrm_notification_sort_simple');
									$simple_info[$simple_count] = array(
										'title' => '<b>'. $title_text .': </b>',
										'sort'	=> $sort[$value['id']],
										'value'	=> $simple_field,
									);
			    				} else {
			    					$simple_info[$simple_count] = array('sort' => 'a','value' => '');
			    				}
			    				$simple_count++;
			    			}
			    		}
			    	}
				}

				$products = $this->model_account_order->getOrderProducts($order_id); 
				$order_products = array();

				$product_info_array = array('sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location');
				
				foreach ($products as $pk => $product) {
					$product_info = '';
					foreach ($order_product as $key => $value) {
						$product_id = $product['product_id'];	

						if ($this->config->get('module_tlgrm_notification_product_'.$value)) {

							$title_text = $this->language->get("text_product_$value");
				   			$title_config = $this->config->get('module_tlgrm_notification_text_product_'.$value);
							if (strlen($title_config) > 0) {
								$title_text = $title_config;
							}							

							if (empty($product_info) && in_array($value, $product_info_array)) {
								$product_info = $this->model_catalog_product->getProduct($product_id);
							}

							switch ($value) {
								case 'id':
									$temp = $product_id;
									break;
								case 'sku':
									$temp = $product_info['sku'];
									break;
								case 'upc':
									$temp = $product_info['upc'];
									break;
								case 'ean':
									$temp = $product_info['ean'];
									break;
								case 'jan':
									$temp = $product_info['jan'];
									break;
								case 'isbn':
									$temp = $product_info['isbn'];
									break;
								case 'mpn':
									$temp = $product_info['mpn'];
									break;
								case 'location':
									$temp = $product_info['location'];
									break;
								case 'link':
									$link = $this->url->link('product/product', 'product_id=' . $product_id);
									$temp = '<a href="'. $link . '">'. $title_text .'</a>';
									break;
								case 'name':
									$link = $this->url->link('product/product', 'product_id=' . $product_id);
									$temp = '<a href="'. $link . '">'. $product[$value] .'</a>';
									break;
								case 'price':
									$temp = $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']);
									break;
								case 'total':
									$temp = $this->currency->format($product['total'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']);
									break;
								case 'option':
									$product_options = $this->model_extension_module_tlgrm_notification->getOrderOptions($product['order_id'], $product['order_product_id']);
									$temp = PHP_EOL;
					  				if (!empty($product_options)) {
						            	foreach ($product_options as $option) {
						            		$temp .= $option['name'] .' - '. $option['value'] . PHP_EOL;
										}
									}
									$temp = rtrim($temp);
									break;
								default:
									$temp = $product[$value];
									break;
							}

							$order_products[$pk][$key] = array(
								'title' => '<b>'. $title_text .': </b>',
								'sort'	=> $this->config->get('module_tlgrm_notification_sort_product_'.$value),
								'value'	=> $temp,
							);
						} else {
							$order_products[$pk][$key] = array('sort' => 'a','value' => '');
						}
					}
				}

				if ($this->config->get('module_tlgrm_notification_field_merger')) {
					foreach ($shipping_info as $key => $value) {
						if ($value['value'] == $payment_info[$key]['value']) {
							$payment_info[$key]['value'] = '';
						}
					}
				}
				
				$message = $this->setMessage($this->sortField($common_info), $msg_title[0], $msg_separator[0]);
				$message .= $this->setMessage($this->sortField($shipping_info), $msg_title[1], $msg_separator[1]);
				$message .= $this->setMessage($this->sortField($payment_info), $msg_title[2], $msg_separator[2]);

				if (!empty($simple_info)) {
					$message .= $this->setMessage($this->sortField($simple_info), $msg_title[4], $msg_separator[4]);
				}

				$product_flag = true;

				foreach ($order_products as $key => $value) {
					$msg_product = $this->setMessage($this->sortField($value));
					if (!empty($msg_product) && $product_flag) {
						$message .= '<i>'.$msg_title[3].'</i>';
						$product_flag = false;
					}
					$message .= rtrim($msg_product) . PHP_EOL . rtrim($msg_separator[3]);
				}

		        $this->model_extension_module_tlgrm_notification->SendMessage($message);
		    }
        }  
	}

	public function changeStatus($arg = '') {
		
		$tlgrm_ntf_id = $this->config->get('module_tlgrm_notification_id');

		if (strlen($tlgrm_ntf_id) > 0 && isset($arg['order_status_id']) && !empty($arg['order_status_id'])) {

			$info_order = array(
				'order_id',
				'store_name',
				'store_url',
				'date_added',
				'firstname',
				'lastname',
				'email',
				'telephone',
				'comment',
				'total',
				'order_status',
				'customer_id',
				'payment_method',
				'shipping_method',
				'shipping_firstname',
				'shipping_lastname',
				'shipping_company',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_postcode',
				'shipping_city',
				'shipping_zone',
				'shipping_country',
				'payment_firstname',
                'payment_lastname',
                'payment_company',
                'payment_address_1',
                'payment_address_2',
                'payment_postcode',
                'payment_city',
                'payment_zone',
                'payment_country',
                'status_comment',
			);

			$order_status_id = $arg['order_status_id'] == -1 ? 0 : $arg['order_status_id'];

            $message_temp = array();
            $message = '';

		    $this->load->model('extension/module/tlgrm_notification');
		    $this->load->model('checkout/order');

		    if (isset($arg['order_id']) && !empty($arg['order_id'])) {
		    	$order_id = $arg['order_id'];
			   	$order_info = $this->model_checkout_order->getOrder($order_id);
				
				if ($order_info) {
					foreach ($info_order as $key => $value) {
						if (isset($order_info[$value])) {
							$temp = '';
							switch ($value) {
								case 'order_status':
									if ($order_status_id) {
									   	$order_status = $this->model_extension_module_tlgrm_notification->getOrderStatus($order_status_id);
									   	if (!empty($order_status)) {
										   	$temp = $order_status['name'];
							   			}
									}
									break;
								case 'customer_id':
									$temp = $this->model_extension_module_tlgrm_notification->getOrderCustomerGroup($order_id);
									break;
								case 'total':
				   					$temp = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']);
				   					break;
								default:
									$temp = $order_info[$value];
									break;
							}
							
							$message_temp[$value] = $temp;
						}
						if ($value == 'status_comment' && isset($arg['status_comment']) && !empty($arg['status_comment'])) {
							$message_temp[$value] = $arg['status_comment'];
						}
					}
					foreach ($info_order as $key => $value) {
						$info_order[$key] = '{'.$value.'}';
					}

					$template = $this->config->get('module_tlgrm_notification_template_order_status');
					
					if (isset($template[$order_status_id]) && !empty($template[$order_status_id])) {
						$message = html_entity_decode(str_replace($info_order, $message_temp, $template[$order_status_id]));

						$this->model_extension_module_tlgrm_notification->SendMessage($message);
					}
			    }
		    }
        }  
	}

	protected function sortField($sort) {
		for ($i = 0; $i < count($sort); $i++) {
           	for ($j = $i+1; $j < count($sort); $j++) {
               	if ($sort[$i]['sort'] > $sort[$j]['sort']) {
                   $temp = $sort[$j];
                   $sort[$j] = $sort[$i];
                   $sort[$i] = $temp;
           		}
          	}         
       	}
       	return $sort;
	}

	protected function setMessage($info, $title = '', $separator = '') {
		$title = '<i>'.$title.'</i>' . PHP_EOL;
		$msg = '';
		foreach ($info as $key => $value) {
			if (isset($value['title']) && !empty($value['value'])) {
				$msg .= $value['title'] . $value['value'] . PHP_EOL;
			}
		}
		if (!empty($msg)) {
			$msg = $title . $msg . $separator;
		}
		return $msg;
	}
}
?>