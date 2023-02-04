<?php
class ModelExtensionMayAdvancedOptions extends Model {
	public function getCartProductVariableData() {
		$cart_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

		$cart_product_skus = array();
		$cart_product_images = array();

		foreach ($cart_query->rows as $cart) {
			$cart_option = json_decode($cart['option'], true);

			$cart_product_skus[$cart['cart_id']] = "";
			if (isset($cart_option['sku']) && $cart_option['sku'] != '') {
				$cart_product_skus[$cart['cart_id']] = $cart_option['sku'];
			}

			if (isset($cart_option['image']) && $cart_option['image'] != '') {
				$cart_product_images[$cart['cart_id']] = $cart_option['image'];
			}
		}

		return array(
			'skus' => $cart_product_skus,
			'images' => $cart_product_images
		);
	}
}
