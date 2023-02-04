<?php

/**
 * @category   OpenCart
 * @package    Branched Sitemap
 * @copyright  © Serge Tkach, 2018–2022, http://sergetkach.com/
 */

class ModelExtensionFeedBranchedSitemap extends Model {

		public function getCategories($data) {
      $sql = "SELECT c.category_id, c.image, c.date_added, c.date_modified FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)";

			if ($this->config->get('feed_branched_sitemap_multishop')) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) ";
			}
			
			$sql .= " WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ";
			
			if ($this->config->get('feed_branched_sitemap_multishop')) {
				$sql .= " AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
			}
			
			$sql .= " AND c.status = '1' ORDER BY c.category_id ASC, LCASE(cd.name)";

    if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 200;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}


	public function getTotalCategories() {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category c";

		if ($this->config->get('feed_branched_sitemap_multishop')) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id)";
		}
		
		$sql .= " WHERE c.status = '1'";		
		
		if ($this->config->get('feed_branched_sitemap_multishop')) {
			$sql .= " AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		}
			
		$query = $this->db->query($sql);
			

		return $query->row['total'];
	}


  public function getProducts($data = array()) {
		$sql = "SELECT p.product_id, p.image, p.date_added, p.date_modified";

    if ($data['image'] && $data['image_caption'] && !$data['images']) {
      $sql .= ", pd.name";
    }

    $sql .= " FROM " . DB_PREFIX . "product p";

    if (!$this->config->get('feed_branched_sitemap_off_description') || ($data['image'] && $data['image_caption'] && !$data['images'])) {
      $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
    }

    if ($this->config->get('feed_branched_sitemap_multishop')) {
      $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";
    }

    $sql .= " WHERE p.status = '1' AND p.date_available <= NOW() ";

    if (!$this->config->get('feed_branched_sitemap_off_description') || ($data['image'] && $data['image_caption'] && !$data['images'])) {
      $sql .= " AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
    }

    if ($this->config->get('feed_branched_sitemap_multishop')) {
      $sql .= " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
    }

		$sql .= " GROUP BY p.product_id";

		$sql .= " ORDER BY p.product_id ASC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			//$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			$product_data[$result['product_id']] = array(
        'product_id'       => $result['product_id'],
        'image'            => $result['image'],
				'name'             => isset($result['name']) ? $result['name'] : '',
				'date_added'       => $result['date_added'],
				'date_modified'    => $result['date_modified']
      );
		}

		return $product_data;
	}

  /*
  public function getProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT p.product_id,  p.image, p.date_added, p.date_modified, pd.name AS name FROM " . DB_PREFIX . "product p WHERE p.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW()");

		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
        'image'            => $query->row['image'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified']
			);
		} else {
			return false;
		}
	}
   *
   */


  public function getProductName($product_id) {
		$query = $this->db->query("SELECT DISTINCT p.product_id,  p.image, p.date_added, p.date_modified, pd.name AS name FROM " . DB_PREFIX . "product p WHERE p.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW()");

		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
        'image'            => $query->row['image'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified']
			);
		} else {
			return false;
		}
	}


  public function getTotalProducts() {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p";

    if (!$this->config->get('feed_branched_sitemap_off_description')) {
      $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
    }

    if ($this->config->get('feed_branched_sitemap_multishop')) {
      $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";
    }

    $sql .= " WHERE p.status = '1' AND p.date_available <= NOW() ";

    if (!$this->config->get('feed_branched_sitemap_off_description')) {
      $sql .= " AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
    }

    if ($this->config->get('feed_branched_sitemap_multishop')) {
      $sql .= " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
    }

    $query = $this->db->query($sql);

		return $query->row['total'];
	}


  public function getManufacturers($data) {
    $system = $this->config->get('feed_branched_sitemap_system');

		// It does not matter for ocStore 3
		/*
    // OpenCart
    if ('OpenCart' == $system) {
      $sql = "SELECT * FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY m.manufacturer_id ASC, LCASE(m.name)";
    }

    // OpenCart PRO
    if ('OpenCart.PRO' == $system) {
      $sql = "SELECT * FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY m.manufacturer_id ASC, LCASE(m.name)";
    }

    // ocStore
    if ('ocStore' == $system) {
      $sql = "SELECT * FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_description md ON (m.manufacturer_id = md.manufacturer_id) LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE md.language_id = '" . (int)$this->config->get('config_language_id') . "' AND m2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY m.manufacturer_id ASC, LCASE(md.name)";
    }
		 * 
		 */
		
		$sql = "SELECT * FROM " . DB_PREFIX . "manufacturer m";

		if ($this->config->get('feed_branched_sitemap_multishop')) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		}
		
		$sql .= " ORDER BY m.manufacturer_id ASC, LCASE(m.name)";

    if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 200;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}


	public function getTotalManufacturers() {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer m";

		if ($this->config->get('feed_branched_sitemap_multishop')) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		}
		
		$query = $this->db->query($sql);

		return $query->row['total'];
	}


	public function getInformation($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) ";

		if ($this->config->get('feed_branched_sitemap_multishop')) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id)";
		}
		
		$sql .= " WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.status = '1'";
		
		if ($this->config->get('feed_branched_sitemap_multishop')) {
			$sql .= " AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		}
		
		$sql .= " ORDER BY i.information_id ASC, LCASE(id.title)";

    if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 200;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}



	public function getTotalInformation() {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information i";

		if ($this->config->get('feed_branched_sitemap_multishop')) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id)";
		}
		
		$sql .= " WHERE i.status = '1'";
		
		if ($this->config->get('feed_branched_sitemap_multishop')) {
			$sql .= " AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		}
		
		$query = $this->db->query($sql);

		return $query->row['total'];
	}


  public function existMainCat() {
    $exist_main_cat = false;
    $sql = "SHOW COLUMNS FROM " . DB_PREFIX . "category_description;";
    $result = $this->db->query($sql);

    foreach ($result->rows as $field) {
      if ('main_category' == $field['Field']) {
        return true;
      }
    }

    return false;
  }


  // from seo pro . begin
  public function getPathByCategory($category_id) {

		$category_id = (int)$category_id;
		if ($category_id < 1) return false;

		static $path = null;
		if (!isset($path)) {
			$path = $this->cache->get('category.seopath');
			if (!isset($path)) $path = array();
		}

		if (!isset($path[$category_id])) {
			$max_level = 10;

			$sql = "SELECT CONCAT_WS('_'";
			for ($i = $max_level-1; $i >= 0; --$i) {
				$sql .= ",t$i.category_id";
			}
			$sql .= ") AS path FROM " . DB_PREFIX . "category t0";
			for ($i = 1; $i < $max_level; ++$i) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "category t$i ON (t$i.category_id = t" . ($i-1) . ".parent_id)";
			}
			$sql .= " WHERE t0.category_id = '" . $category_id . "'";

			$query = $this->db->query($sql);

			$path[$category_id] = $query->num_rows ? $query->row['path'] : false;

			$this->cache->set('category.seopath', $path); // ?? OC 3??
		}

		return $path[$category_id];
	}


  public function getPathByProduct($product_id) {
		$product_id = (int)$product_id;
		if ($product_id < 1) return false;

		static $path = null;
		if (!isset($path)) {
			$path = $this->cache->get('product.seopath');
			if (!isset($path)) $path = array();
		}

		if (!isset($path[$product_id])) {
			$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . $product_id . "' ORDER BY main_category DESC LIMIT 1");

			$path[$product_id] = $this->getPathByCategory($query->num_rows ? (int)$query->row['category_id'] : 0);

			$this->cache->set('product.seopath', $path);
		}

		return $path[$product_id];
	}
  // from seo pro . end

}
