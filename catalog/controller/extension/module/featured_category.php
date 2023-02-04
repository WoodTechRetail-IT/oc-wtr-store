<?php
class ControllerExtensionModuleFeaturedCategory extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/featured_category');

		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('tool/imagequadr');

		$data['heading_title']='';

		if (isset($setting['module_description'][$this->config->get('config_language_id')])) {
			$data['heading_title'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['title'], ENT_QUOTES, 'UTF-8');
		}

		// Categories
		$data['categories'] = array();

		if (!empty($setting['product_category'])) {
			$categories = $setting['product_category'];

			foreach ($categories as $id) {
				$category=$this->model_catalog_category->getCategory($id);
				if ($category){

					if ($category['image']) {
						$image = $this->model_tool_imagequadr->resize($category['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_imagequadr->resize('placeholder.png', $setting['width'], $setting['height']);
					}
					$filter_data = array(
						'filter_category_id'  => $category['category_id'],
						'filter_sub_category' => true
					);

					$data['categories'][] = array(
						'category_id' => $category['category_id'],
						'thumb'       => $image,
						'name'        => $category['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
						'href'        => $this->url->link('product/category', 'path=' . $category['category_id'])
					);
				}
			}
		}
		
		if ($data['categories']) {
			return $this->load->view('extension/module/featured_category', $data);
		}
	}
}