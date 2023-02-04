<?php
class ControllerExtensionMayAdvancedOptions extends Controller {
	public function vProductProductBefore($route, &$data) {
		if (!$this->config->get('may_advanced_options_status')) {
			return;
		}

		if (!isset($data['options'])) {
			return;
		}

		$may_advanced_options = array();

		$this->load->model('tool/image');

		$may_advanced_options_prefix = array();
		foreach ($data['options'] as $option_key => $option) {
			if (($option['type'] == 'select' || $option['type'] == 'radio') && $option['value'] != '') {
				$may_advanced_info = explode(':::', $option['value']);

				$tmp = isset($may_advanced_info[2]) ? json_decode($may_advanced_info[2], true) : array();
				
				$skus = isset($tmp['sku']) ? $tmp['sku'] : array();
				$images = isset($tmp['image']) ? $tmp['image'] : array();
				$prices = isset($tmp['price']) ? $tmp['price'] : array();
				$hides = isset($tmp['hide']) ? $tmp['hide'] : array();
				$subtracts = isset($tmp['subtract']) ? $tmp['subtract'] : array();
				$quantities = isset($tmp['quantity']) ? $tmp['quantity'] : array();
				
				foreach ($option['product_option_value'] as $option_value_key => $option_value) {
					if (isset($hides[$option_value['option_value_id']]) && $hides[$option_value['option_value_id']]) {
						unset($option['product_option_value'][$option_value_key]);
						continue;
					}

					if (isset($skus[$option_value['option_value_id']])) {
						$option['product_option_value'][$option_value_key]['sku'] = $skus[$option_value['option_value_id']];
					}

					if (isset($prices[$option_value['option_value_id']])) {
						$option['product_option_value'][$option_value_key]['base_price'] = $this->currency->format($prices[$option_value['option_value_id']], $this->session->data['currency']);
					}

					if (isset($images[$option_value['option_value_id']]) && count(array_filter($images[$option_value['option_value_id']]))) {
						$option['product_option_value'][$option_value_key]['product_images'] = array();
						foreach ($images[$option_value['option_value_id']] as $image) {
							$option['product_option_value'][$option_value_key]['product_images'][] = array(
								'origin' => $image,
								'popup' => $this->model_tool_image->resize($image, $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
								'thumb' => $this->model_tool_image->resize($image, $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'))
							);
						}
					}

					if (isset($subtracts[$option_value['option_value_id']])) {
						$option['product_option_value'][$option_value_key]['subtract'] = $subtracts[$option_value['option_value_id']];
					}

					if (isset($quantities[$option_value['option_value_id']])) {
						$option['product_option_value'][$option_value_key]['quantity'] = $quantities[$option_value['option_value_id']];
					}
				}

				$option['advanced_option_id'] = isset($may_advanced_info[1]) ? $may_advanced_info[1] : '';

				if (isset($may_advanced_info[0])) {
					if (in_array($may_advanced_info[0], $may_advanced_options_prefix)) {
						$option['init_disable'] = 1;
					} else {
						$may_advanced_options_prefix[] = $may_advanced_info[0];
						$option['init_disable'] = 0;
					}
				} else {
					$option['init_disable'] = 0;
				}

				$may_advanced_options[] = $option;
				unset($data['options'][$option_key]);
			}
		}

		uasort($may_advanced_options, function($a, $b) {
			return ($a['product_option_id'] < $b['product_option_id']) ? -1 : 1;
		});

		if (count($may_advanced_options)) {
			$may_advanced_options = array_values($may_advanced_options);

			$init_visible_options = array();
			foreach ($may_advanced_options as $index => $option) {
				if (!in_array($option['option_id'], $init_visible_options)) {
					$may_advanced_options[$index]['init_visible'] = 1;
					$init_visible_options[] = $option['option_id'];
				} else {
					$may_advanced_options[$index]['init_visible'] = 0;
				}
			}

			$may_advanced_options[0]['init_disable'] = 0;
		}

		$currency = $this->session->data['currency'];
		$symbol_left = $this->currency->getSymbolLeft($currency);
		$symbol_right = $this->currency->getSymbolRight($currency);
		$data['currency'] = array(
			'code' => $currency,
			'symbol_position' => ($symbol_left !== "") ? "left" : "right",
			'symbol' => ($symbol_left !== "") ? $symbol_left : $symbol_right,
			'decimal_place' => $this->currency->getDecimalPlace($currency),
			'decimal_point' => $this->language->get('decimal_point'),
			'thousand_point' => $this->language->get('thousand_point')
		);
		$data['may_advanced_options'] = $may_advanced_options;

		if (!isset($data['may_advanced_options_config'])) {
			$data['may_advanced_options_config'] = array(
				'show_option_price' => $this->config->get('may_advanced_options_show_option_price'),
				'wrapper' => $this->config->get('may_advanced_options_wrapper'),
				'swatches' => $this->config->get('may_advanced_options_swatches'),
				'swatch_image' => isset($tmp['swatch_image']) ? $tmp['swatch_image'] : $this->config->get('may_advanced_options_swatch_image'),
		
				'swatch_style_shape' => $this->config->get('may_advanced_options_swatch_style_shape'),
				'swatch_style_size_width' => $this->config->get('may_advanced_options_swatch_style_size_width'),
				'swatch_style_size_height' => $this->config->get('may_advanced_options_swatch_style_size_height'),
				'swatch_style_size_radius' => $this->config->get('may_advanced_options_swatch_style_size_radius'),
				'swatch_style_border_width' => $this->config->get('may_advanced_options_swatch_style_border_width'),
				'swatch_style_border_color_selected' => $this->config->get('may_advanced_options_swatch_style_border_color_selected'),
				'swatch_style_border_color_default' => $this->config->get('may_advanced_options_swatch_style_border_color_default'),
				'swatch_style_space_padding' => $this->config->get('may_advanced_options_swatch_style_space_padding'),
		
				'swatch_css' => $this->config->get('may_advanced_options_swatch_css'),
				'sku_js' => htmlspecialchars_decode($this->config->get('may_advanced_options_sku_js')),
				'price_js' => htmlspecialchars_decode($this->config->get('may_advanced_options_price_js')),
				'stock_js' => htmlspecialchars_decode($this->config->get('may_advanced_options_stock_js')),
				'theme' => $this->config->get('config_theme'),
			);
		}

		$this->load->language('extension/may/advanced_options');
		$data['may_advanced_options_language'] = array(
			'error_option_stock' => $this->language->get('error_option_stock'),
		);

		$data['footer'] .= $this->load->view('extension/may/advanced_options/product/product', $data);
	}

	public function vProductCategoryBefore($route, &$data) {
		/* In Progress
		$this->load->model('catalog/product');

		foreach ($data['products'] as $product) {
			$options = $this->model_catalog_product->getProductOptions($product['product_id']);
		}
		*/
	}

	public function vCommonCartBefore($route, &$data) {
		if (!$this->config->get('may_advanced_options_status')) {
			return;
		}

		if (!isset($data['products'])) {
			return;
		}

		$this->load->model('extension/may/advanced_options');
		$cart_product_data = $this->model_extension_may_advanced_options->getCartProductVariableData();
		$cart_product_skus = $cart_product_data['skus'];
		$cart_product_images = $cart_product_data['images'];

		$this->load->model('tool/image');
		foreach ($data['products'] as $key => $product) {
			if (isset($cart_product_skus[$product['cart_id']]) && $cart_product_skus[$product['cart_id']] != "") {
				$data['products'][$key]['model'] = $data['products'][$key]['model'] . '-' . $cart_product_skus[$product['cart_id']];
				//$data['products'][$key]['option'] = array_merge(array(array('name' => 'SKU', 'value' => $cart_product_skus[$product['cart_id']])), $data['products'][$key]['option']);
			}

			if (isset($cart_product_images[$product['cart_id']])) {
				$data['products'][$key]['thumb'] = $this->model_tool_image->resize($cart_product_images[$product['cart_id']], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
			}
		}
	}

	public function vCheckoutCartBefore($route, &$data) {
		if (!$this->config->get('may_advanced_options_status')) {
			return;
		}

		$this->vCommonCartBefore($route, $data);
	}

	public function mCheckoutOrderAddOrderBefore($route, &$args) {
		if (!$this->config->get('may_advanced_options_status')) {
			return;
		}

		$this->load->model('extension/may/advanced_options');
		$cart_product_data = $this->model_extension_may_advanced_options->getCartProductVariableData();
		$cart_product_skus = array_values($cart_product_data['skus']);		

		foreach ($args[0]['products'] as $key => $product) {
			if ($cart_product_skus[$key] != "") {
				$args[0]['products'][$key]['model'] .= '-' . $cart_product_skus[$key];
			}
		}
	}

	public function vCheckoutConfirmBefore($route, &$args) {
		if (!$this->config->get('may_advanced_options_status')) {
			return;
		}

		$this->load->model('extension/may/advanced_options');
		$cart_product_data = $this->model_extension_may_advanced_options->getCartProductVariableData();
		$cart_product_skus = array_values($cart_product_data['skus']);		

		foreach ($args['products'] as $key => $product) {
			if ($cart_product_skus[$key] != "") {
				$args['products'][$key]['model'] .= '-' . $cart_product_skus[$key];
			}
		}
	}
}
