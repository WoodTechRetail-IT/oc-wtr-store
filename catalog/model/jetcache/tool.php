<?php
class ModelJetcacheTool extends Model {
	private $ssl = false;

	public function __construct($register) {
        if ((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on'))) {
        	$this->ssl = true;
        } else {
        	$this->ssl = false;
        }
        parent::__construct($register);
    }

	public function from_admin() {
	  	if (SC_VERSION > 23) {
	       	$data['token_name'] = 'user_token';
	    } else {
	       	$data['token_name'] = 'token';
	    }
	    if (empty($this->session->data['user_id'])) return false;
	    if (empty($this->session->data[$data['token_name']])) return false;
	    if (empty($this->request->get[$data['token_name']])) return false;
	    if ($this->request->get[$data['token_name']] != $this->session->data[$data['token_name']]) return false;
	    return true;
  	}

  	public function url($params) {
	    $url = $this->url->link($params['route'], $params['params'], $this->ssl);
	    return html_entity_decode($url, ENT_NOQUOTES, 'UTF-8');
  	}

  	public function getLanguages() {
		$query_lang = $this->db->query("SELECT * FROM " . DB_PREFIX . "language WHERE status = '1' ORDER BY `code`='" . (int)$this->config->get('config_language') . "' DESC");
		foreach ($query_lang->rows as $result_lang) {
			$languages[$result_lang['language_id']] = $result_lang['code'];
		}
        return $languages;
  	}

	public function getStores() {
	    $this->load->model('setting/store');

	    $stores_full = array_merge(
	      array(
	        0 => array(
	          'store_id' => '0',
	          'name' => $this->config->get('config_name'),
	          'url' => substr(HTTP_SERVER, 0, strripos(HTTP_SERVER, '/', -2) + 1),
	          'ssl' => substr(HTTPS_SERVER, 0, strripos(HTTPS_SERVER, '/', -2) + 1)
	        )
	      ),
	      $this->model_setting_store->getStores()
	    );

	    $stores = array();
	    foreach ($stores_full as $store_full) {
	      $stores[] = array(
	        'url' => substr($store_full['url'], stripos($store_full['url'], '/')),
	        'store_id' => $store_full['store_id']
	      );
	    }

	    return $stores;
	}

	public function getCategoriesByStoreId($store_id = 0) {
	    $cache_key = 'category.jetcache.' . (int)$store_id;

	    $data = $this->cache->get($cache_key);

	    if (!empty($data)) return $data;

	    $result = $this->db->query("SELECT c.category_id FROM " . DB_PREFIX . "category_to_store c2s LEFT JOIN " . DB_PREFIX . "category c ON (c.category_id = c2s.category_id) WHERE c2s.store_id='" . (int)$store_id . "' AND c.status=1");

	    foreach ($result->rows as &$row) {
	      $parent_id = $row['category_id'];
	      $path = array();
	      $used = array();

	      while ($parent_id > 0 && !in_array($parent_id, $used)) {
	        array_unshift($path, $parent_id);

	        $used[] = $parent_id;

	        $result_parent = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id='" . (int)$parent_id . "'");

	        if ($result_parent->num_rows) {
	          $parent_id = $result_parent->row['parent_id'];
	        }
	      };

	      $row['path'] = implode('_', $path);
	      unset($row['category_id']);
	    }

	    $this->cache->set($cache_key, $result->rows);

	    return $result->rows;
	  }

	  public function getInformationsByStoreId($store_id = 0) {
	    $result = $this->db->query("SELECT i.information_id FROM " . DB_PREFIX . "information_to_store i2s LEFT JOIN " . DB_PREFIX . "information i ON (i.information_id = i2s.information_id) WHERE i2s.store_id='" . (int)$store_id . "' AND i.status=1");

	    return $result->rows;
	  }

	  public function getProductsByStoreId($store_id = 0) {
	      $result = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product_to_store p2s LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = p2s.product_id) WHERE p2s.store_id='" . (int)$store_id . "' AND p.status=1");

	      return $result->rows;
	  }


}
