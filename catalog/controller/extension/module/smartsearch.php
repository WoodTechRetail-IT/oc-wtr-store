<?php
class ControllerExtensionModuleSmartsearch extends Controller {
	public function index() {

		$json = array();
		if (isset($this->request->post['search']) && $this->request->post['search']) {

			$data['module_smartsearch_image'] = $this->config->get('module_smartsearch_image');
			$data['module_smartsearch_price'] = $this->config->get('module_smartsearch_price');
			$data['module_smartsearch_model'] = $this->config->get('module_smartsearch_model');
			$data['module_smartsearch_oldprice'] = $this->config->get('module_smartsearch_oldprice');
			$data['module_smartsearch_button_all'] = $this->config->get('module_smartsearch_button_all');


			$this->load->language('extension/module/smartsearch');
			$this->load->model('extension/module/smartsearch');
			$this->load->model('tool/image');
			
			$data['products'] = array();

			$sort = 'pd.name';
			$order = 'ASC';
			$page = 1;
			$limit = ($this->config->get('module_smartsearch_limit') > 0 ? $this->config->get('module_smartsearch_limit') : 10);

			$filter_data = array(
				'filter_name'         => mb_strtolower($this->request->post['search']),
				'sort'                => $sort,
				'order'               => $order,
				'start'               => ($page - 1) * $limit,
				'limit'               => $limit
			);

			$data['text_model'] = $this->language->get('text_model');
			if ($this->config->get('module_smartsearch_button_all')) {
				
				$data['button_all'] = $this->language->get('button_all');
				$data['button_all_href'] = $this->url->link('product/search', 'search=' . $this->request->post['search']);
			} 
			
			$products = $this->model_extension_module_smartsearch->getProducts($filter_data);
			$first_name = '';

			foreach ($products as $keyproduct => $product) {

				$image = '';
				if ($this->config->get('module_smartsearch_image')) {
					if ($this->config->get('module_smartsearch_image_width')) {
						$smartsearch_image_width = $this->config->get('module_smartsearch_image_width');
					} else {
						$smartsearch_image_width = 50;
					}

					if ($this->config->get('module_smartsearch_image_height')) {
						$smartsearch_image_height = $this->config->get('module_smartsearch_image_height');
					} else {
						$smartsearch_image_height = 50;
					}

					if ($product['image']) {
						$image = $this->model_tool_image->resize($product['image'], $smartsearch_image_width, $smartsearch_image_height);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $smartsearch_image_width, $smartsearch_image_height);
					}
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product['special']) {
					$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				$name = $product['name'];
				$product_name = $product['name'];
				$name = mb_strtolower($product['name']);
				$search = mb_strtolower($this->request->post['search']);
				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $search)));
				$words = array_unique($words);
				$parts = array();

				if (mb_stripos($name, $search) !== false) {
					$s = mb_substr($product['name'],mb_stripos($name, $search), mb_strlen($search));
					$product_name = str_replace($s,'<b>' . $s . '</b>',$product_name);
					if (!$first_name) {
						$first_name = mb_strtolower($product['name']);
					}					
				} else {
					foreach ($words as $word) {
						if (mb_stripos($name, $word) !== false) {
							$s = mb_substr($product['name'],mb_stripos($name, $word), mb_strlen($word));
							$product_name = str_replace($s,'<b>' . $s . '</b>',$product_name);
						} 						
					}
				}
				

				$data['products'][] = array(
					'product_id'  => $product['product_id'],
					'model'  	  => $product['model'],
					'search_name'  => $product['name'],					
					'thumb'       => $image,
					'name'        => $product_name,
					'price'       => $price,
					'special'     => $special,
					'href'        => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}

			$json['html'] = $this->load->view('extension/module/smartsearch', $data);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function search_index() {
		$this->load->model('extension/module/smartsearch');
		$this->model_extension_module_smartsearch->search_index();
	}

	public function searching() {
		$start = microtime(true);

		$this->load->model('extension/module/smartsearch');
		$search = $this->request->get['search'];		
		$this->model_extension_module_smartsearch->getProducts(array('filter_name' => $search));

		$time = microtime(true) - $start;
		echo "<br><br>Время выполнения: " . $time;
	}
}