<?php
class ControllerJetcacheTool extends Controller {

	protected $config_language_id;
	protected $config_language_code;
	protected $langcode_all;

	public function get_buildcache_array() {
		if (!defined('SC_VERSION')) define('SC_VERSION', (int)substr(str_replace('.','',VERSION), 0,2));
        if (file_exists(DIR_SYSTEM . 'helper/seocmsprofunc.php')) {
			if (!function_exists('loadlibrary')) {
				if (function_exists('modification')) {
					require_once(modification(DIR_SYSTEM . 'helper/seocmsprofunc.php'));
				} else {
					require_once(DIR_SYSTEM . 'helper/seocmsprofunc.php');
				}
			}
        }
         // Hey idiots burn override
		//if (!is_callable(array('User', 'hasPermission'))) {
			if (SC_VERSION > 21) {
				$place_user = 'cart/user';
				$class_user = 'Cart\User';
			} else {
				$place_user = 'user';
				$class_user = 'User';
			}
			loadlibrary($place_user);
			$this->registry->set('user', new $class_user($this->registry));
		//}

        if ($this->registry->get('user')->hasPermission('modify', 'jetcache/jetcache')) {

			$this->load->model('jetcache/tool');

			$this->load->model('catalog/category');

			$this->load->model('catalog/information');

			if (!$this->model_jetcache_tool->from_admin()) return;

	        agoo_cont('jetcache/jetcache', $this->registry);
	        $this->data = $this->controller_jetcache_jetcache->cacheremove('noaccess');

			$opencart_urls = array(
				array(
					'route' => 'common/home',
					'params' => ''
				),

				array(
					'route' => 'product/special',
					'params' => ''
				),
				array(
					'route' => 'information/contact',
					'params' => ''
				)
			);

			$result = array();

			$this->config_language_id = $this->config->get('config_language_id');
			$this->config_language_code = $this->config->get('config_language');

	        if (isset($this->request->post['buildcache_lang']) && $this->request->post['buildcache_lang'] == 'true') {
	        	$languages = $this->model_jetcache_tool->getLanguages();
	        } else {
	        	$languages[$this->config_language_id] = $this->config_language_code;
	        }

			foreach ($languages as $language_id => $language_code) {

	            $this->switchLanguage($language_id, $language_code);

				foreach ($this->model_jetcache_tool->getStores() as $store) {

					foreach ($opencart_urls as $standard_url) {
						$result[] = $this->model_jetcache_tool->url($standard_url);
					}

					$categories = $this->model_jetcache_tool->getCategoriesByStoreId($store['store_id']);

					foreach ($categories as $category) {
						$result[] = $this->model_jetcache_tool->url(array(
							'route' => 'product/category',
							'params' => http_build_query($category)
						));
					}

					$informations = $this->model_jetcache_tool->getInformationsByStoreId($store['store_id']);

					foreach ($informations as $information) {
						$result[] = $this->model_jetcache_tool->url(array(
							'route' => 'information/information',
							'params' => 'information_id=' . $information['information_id']
						));
					}

		            if (isset($this->request->post['buildcache_products']) && $this->request->post['buildcache_products'] == 'true') {
			            $products = $this->model_jetcache_tool->getProductsByStoreId($store['store_id']);

			            foreach ($products as $product) {
			                $result[] = $this->model_jetcache_tool->url(array(
			                    'route' => 'product/product',
			                    'params' => 'product_id=' . $product['product_id']
			                ));
			            }
		            }
				}
			}

	        $this->switchLanguage($this->config_language_id, $this->config_language_code);

			$this->response->setOutput(json_encode($result));
		}
	}
	private function switchLanguage($language_id, $code) {
		if ($code != '') {
			$this->session->data['language'] = $_SESSION['language'] = $code;
			setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $this->registry->get('request')->server['HTTP_HOST']);
			$this->config->set('config_language_id', $language_id);
			$this->config->set('config_language', $code);

			if (SC_VERSION > 21) {
				$language_construct = $code;
			} else {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language WHERE status = '1'");
				foreach ($query->rows as $result) {
					$this->langcode_all[$result['code']] = $result;
				}
				$language_construct = $this->langcode_all[$code]['directory'];
			}

			$language = new Language($language_construct);

			if (SC_VERSION > 15) {
				if (SC_VERSION > 21) {
					$language->load($code);
				} else {
					$language->load('default');
					$language->load($language_construct);
				}

			} else {
				$language->load($this->langcode_all[$code]['filename']);
			}
			$this->registry->set('language', $language);
		}
	}

}