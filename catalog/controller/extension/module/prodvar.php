<?php
class ControllerExtensionModuleProdvar extends Controller {	
	private $error = array();
	private $modpath = 'extension/module/prodvar'; 
	private $modtpl = 'extension/module/prodvar'; 
	private $modname = 'module_prodvar';
	private $modssl = true;
	private $langid = 0;
	
	public function __construct($registry) {
		parent::__construct($registry);
		$this->langid = (int)$this->config->get('config_language_id');
 	} 
	
	public function index() {
		$data['prodvar_status'] = $this->setvalue($this->modname.'_status');
		if ($data['prodvar_status'] && isset($this->request->get['product_id'])) {
			
			$this->load->model('tool/image');
			$this->load->model('catalog/product');
			
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
		
			$prodvar_data = $this->getprodvardata($this->request->get['product_id']);
 			
			$data['prodvar_title'] = json_decode($prodvar_data['prodvar_title'], true);
 			$data['prodvar_title'] = $data['prodvar_title'][$this->langid];
			
			$products = ($prodvar_data['prodvar_product_str_id']) ? explode(",", $prodvar_data['prodvar_product_str_id']) : array();
			if(!empty($products)) $products = array_diff($products, array($this->request->get['product_id']));
			
			$data['products'] = array();
			 			
			foreach ($products as $product_id) {
				$result = $this->model_catalog_product->getProduct($product_id);
	
				if ($result) {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], 50, 50);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', 50, 50);
					}
					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = (int)$result['rating'];
					} else {
						$rating = false;
					}

					$data['products'][] = array(
						'product_id'  => $result['product_id'],
						'image' 	  => $image,
						'name'        => $result['name'],
						'model'       => $result['model'],
						'sku'         => $result['sku'],
						'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
						'rating'      => $result['rating'],
						'href' 		  => $this->url->link('product/product', $url . '&product_id=' . $product_id, $this->modssl),
					); 
				}
			}
			
			return $this->load->view($this->modtpl, $data);
 		} 
	}
	
	private function setvalue($postfield) {
		return $this->config->get($postfield);
	}
	
	public function getprodvardata($product_id) { 
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "prodvar WHERE product_id = '" . (int)$product_id . "' limit 1");
		if($query->num_rows){
			return $query->row;
		} 
	}
}