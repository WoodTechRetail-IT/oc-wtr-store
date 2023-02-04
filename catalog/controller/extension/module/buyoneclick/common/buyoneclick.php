<?php
class ControllerExtensionModuleBuyoneclickCommonBuyoneclick extends Controller {
	public function info() {
		$this->load->model('setting/setting');
		$buyoneclick = $this->config->get('buyoneclick');
		$data['buyoneclick_field1_status'] 		= $buyoneclick['field1_status'];
		$data['buyoneclick_field1_required'] 	= ($data['buyoneclick_field1_status'] == 2) ?  true : false;
		$data['buyoneclick_field2_status'] 		= $buyoneclick['field2_status'];
		$data['buyoneclick_field2_required'] 	= ($data['buyoneclick_field2_status'] == 2) ?  true : false;
		$data['buyoneclick_field3_status'] 		= $buyoneclick['field3_status'];
		$data['buyoneclick_field3_required'] 	= ($data['buyoneclick_field3_status'] == 2) ?  true : false;
		$data['buyoneclick_field4_status'] 		= $buyoneclick['field4_status'];
		$data['buyoneclick_field4_required'] 	= ($data['buyoneclick_field4_status'] == 2) ?  true : false;

		$data['buyoneclick_validation_type'] 	= $buyoneclick['validation_type'];

		$data['buyoneclick_stock_status'] 		= $buyoneclick['stock_status'];

		$data['buyoneclick_ya_status'] 					= $buyoneclick['ya_status'];
		$data['buyoneclick_ya_counter'] 				= $buyoneclick['ya_counter'];
		$data['buyoneclick_ya_identificator'] 			= $buyoneclick['ya_identificator'];
		$data['buyoneclick_ya_identificator_send'] 		= $buyoneclick['ya_identificator_send'];
		$data['buyoneclick_ya_identificator_success'] 	= $buyoneclick['ya_identificator_success'];

		$data['buyoneclick_google_status'] 				= $buyoneclick['google_status'];
		$data['buyoneclick_google_category_btn'] 		= $buyoneclick['google_category_btn'];
		$data['buyoneclick_google_action_btn'] 			= $buyoneclick['google_action_btn'];
		$data['buyoneclick_google_category_send'] 		= $buyoneclick['google_category_send'];
		$data['buyoneclick_google_action_send'] 		= $buyoneclick['google_action_send'];
		$data['buyoneclick_google_category_success'] 	= $buyoneclick['google_category_success'];
		$data['buyoneclick_google_action_success'] 		= $buyoneclick['google_action_success'];

		$data['buyoneclick_exan_status'] 				= $buyoneclick['exan_status'];

		$buyoneclick_option_status 						= $buyoneclick['option_status'];

		$this->load->language('extension/module/buyoneclick');
		$data['buyoneclick_title'] 					= $this->language->get('buyoneclick_title');
		$data['buyoneclick_button'] 				= $this->language->get('buyoneclick_button');
		$data['buyoneclick_field1_title'] 			= $this->language->get('buyoneclick_field1_title');
		$data['buyoneclick_field2_title'] 			= $this->language->get('buyoneclick_field2_title');
		$data['buyoneclick_field3_title'] 			= $this->language->get('buyoneclick_field3_title');
		$data['buyoneclick_field4_title'] 			= $this->language->get('buyoneclick_field4_title');
		$data['buyoneclick_button_order'] 			= $this->language->get('buyoneclick_button_order');

		$data['buyoneclick_agree_status'] = $buyoneclick['agree_status'];
		if ($buyoneclick['agree_status']) {
			$this->load->model('catalog/information');
			$information_info = $this->model_catalog_information->getInformation($buyoneclick['agree_status']);
			if ($information_info) {
				$data['buyoneclick_text_agree'] = sprintf($this->language->get('buyoneclick_text_agree'), $this->url->link('information/information', 'information_id=' . $buyoneclick['agree_status'], 'SSL'), $information_info['title'], $information_info['title']);
			} else {
				$data['buyoneclick_text_agree'] = '';
			}
		} else {
			$data['buyoneclick_text_agree'] = '';
		}

		$data['text_empty'] = $this->language->get('text_empty');
		$data['text_checkout'] = $this->language->get('text_checkout');

		unset($this->session->data['boc_product_id']);
		unset($this->session->data['boc_product_quantity']);
		unset($this->session->data['boc_product_option']);

		$data['logged'] = $this->customer->isLogged();
		if ($data['logged']) {
			$this->load->model('account/customer');
			$data['customer_info'] = $this->model_account_customer->getCustomer($this->customer->getId());
		}

		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
			$this->session->data['boc_product_id'] = $product_id;
		} else {
			$product_id = 0;
		}
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('tool/upload');
		$product = $this->model_catalog_product->getProduct($product_id);
		if ($product) {
			if ($product['quantity'] <= 0 && $data['buyoneclick_stock_status']) {
				$data['stock'] = $this->language->get('text_stock') . ' ' . $product['stock_status'];
			} else {
				$data['stock'] = '';
			}

			if (isset($this->request->post['quantity']) && ((int)$this->request->post['quantity'] >= $product['minimum'])) {
				$product['quantity'] = (int)$this->request->post['quantity'];
			} else {
				$product['quantity'] = $product['minimum'] ? $product['minimum'] : 1;
			}
			$this->session->data['boc_product_quantity'] = $product['quantity'];
			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			} else {
				$image = '';
			}
			// options
			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();
			}

			if ($buyoneclick_option_status) {
				$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);
				foreach ($product_options as $product_option) {
					if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
						$this->load->language('checkout/cart');
						$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
					}
				}
			}

			if (!$json) {
				$option_data = array();
				if (!empty($option)) {
					$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);
					foreach ($product_options as $product_option) {
						if (!empty($option[$product_option['product_option_id']])) {
							if ($product_option['type'] != 'checkbox' && $product_option['type'] != 'file' && $product_option['type'] != 'text' && $product_option['type'] != 'textarea' && $product_option['type'] != 'date' &&$product_option['type'] != 'time' && $product_option['type'] != 'datetime') {
								// var_dump($product_option['value']);
								foreach ($product_option['product_option_value'] as $option_value) {
									if ($option_value['product_option_value_id'] == $option[$product_option['product_option_id']]) {
										$product_option_value_id = $option_value['product_option_value_id'];
										$option_value_id = $option_value['option_value_id'];
										$value = $option_value['name'];
										$value_price_value = $option_value['price'];
										if ($value_price_value != 0) {
											$value_price = $this->currency->format($this->tax->calculate($option_value['price'], $product['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
											// $value_price = $option_value['price'];
											$value_price_prefix = $option_value['price_prefix'];
										} else {
											$value_price = false;
											$value_price_prefix = false;
										}
									}
								}
								$option_data[] = array(
									'product_option_id' => $product_option['product_option_id'],
									'product_option_value_id' => $product_option_value_id,
									'option_id' => $product_option['option_id'],
									'option_value_id' => $option_value_id,
									'name' => $product_option['name'],
									'value' => $value,
									'value_price_value' => $value_price_value,
									'value_price' => $value_price,
									'value_price_prefix' => $value_price_prefix,
									'type'  => $product_option['type']
								);
							} else if ($product_option['type'] != 'checkbox' && $product_option['type'] != 'file') {
								// var_dump($product_option);
								$value = $option[$product_option['product_option_id']];
								$value_price_value = false;
								$value_price = false;
								$value_price_prefix = false;
								$option_data[] = array(
									'product_option_id' => $product_option['product_option_id'],
									'product_option_value_id' => 0,
									'option_id' => $product_option['option_id'],
									'option_value_id' => 0,
									'name'  => $product_option['name'],
									'value' => $value,
									'value_price_value' => $value_price_value,
									'value_price' => $value_price,
									'value_price_prefix' => $value_price_prefix,
									'type'  => $product_option['type']
								);
							} else if ($product_option['type'] != 'file') {
								foreach ($product_option['product_option_value'] as $option_value) {
									// var_dump ($option_value);
									foreach ($option[$product_option['product_option_id']] as $checkbox_option) {
										if ($option_value['product_option_value_id'] == $checkbox_option) {
											$product_option_value_id = $option_value['product_option_value_id'];
											$option_value_id = $option_value['option_value_id'];
											$value = $option_value['name'];
											$value_price_value = $option_value['price'];
											if ($value_price_value != 0) {
												$value_price = $this->currency->format($this->tax->calculate($option_value['price'], $product['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
												// $value_price = $option_value['price'];
												$value_price_prefix = $option_value['price_prefix'];
											} else {
												$value_price = false;
												$value_price_prefix = false;
											}
											$option_data[] = array(
												'product_option_id' => $product_option['product_option_id'],
												'product_option_value_id' => $product_option_value_id,
												'option_id' => $product_option['option_id'],
												'option_value_id' => $option_value_id,
												'name'  => $product_option['name'],
												'value' => $value,
												'value_price_value' => $value_price_value,
												'value_price' => $value_price,
												'value_price_prefix' => $value_price_prefix,
												'type'  => $product_option['type']
											);
										}
									}
								}
							} else {
								$upload_info = $this->model_tool_upload->getUploadByCode($product_option['value']);
								if ($upload_info) {
									$value = $upload_info['name'];
								} else {
									$value = '';
								}
								$value_price_value = false;
								$value_price = false;
								$value_price_prefix = false;
								$option_data[] = array(
									'product_option_id' => $product_option['product_option_id'],
									'product_option_value_id' => 0,
									'option_id' => $product_option['option_id'],
									'option_value_id' => 0,
									'name'  => $product_option['name'],
									'value' => $value,
									'value_price_value' => $value_price_value,
									'value_price' => $value_price,
									'value_price_prefix' => $value_price_prefix,
									'type'  => $product_option['type']
								);
							}
						}
					}
				}
				if (!empty($option_data)) {
					$this->session->data['boc_product_option'] = $option_data;
				}
				// Display prices
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$boc_price = $product['price'];
					if ((float)$product['special']) {
						$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$boc_price = $product['special'];
					} else {
						$special = false;
					}
				} else {
					$price = false;
					$special = false;
				}

				$discounts = $this->model_catalog_product->getProductDiscounts($this->request->post['product_id']);

				// var_dump($discounts);

				if ($discounts) {
					foreach ($discounts as $discount) {
						if ($discount['quantity'] <= $product['quantity']) {
							$boc_price = $discount['price'];
						}
					}
				}

				// Display total
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					if (!$option_data) {
						$total = $this->currency->format($this->tax->calculate($boc_price, $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']);
					} else {
						$option_total = 0;
						foreach ($option_data as $option) {
							if ($option['value_price_prefix'] == '+') {
								$option_total += (float)$option['value_price_value'];
							} else if ($option['value_price_prefix'] == '-') {
								$option_total -= (float)$option['value_price_value'];
							}
						}
						$total = $this->currency->format($this->tax->calculate($boc_price + $option_total, $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']);
					}
				} else {
					$total = false;
				}

				$data['product'] = array (
					'thumb'     => $image,
					'name'      => $product['name'],
					'model'     => $product['model'],
					'option'    => $option_data,
					'quantity'  => $product['quantity'],
					'price'     => $price,
					'special'   => $special,
					'total'     => $total,
					'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);

				$json['success'] = $this->load->view('common/buyoneclick', $data);

			} else {
				$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
			}
		}

		// var_dump($json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}