<?php

class ModelExtensionModuleOCFilter extends Model { 
  /* FILTERS */  
  public function getFilter($filter_key) {
    $special_filter = $this->ocfilter->params->key($filter_key)->special();   
    
    if ($special_filter == 'price') {
      $cache_key = (string)$this->ocfilter->cache->key('filter', $filter_key, $this->config->get('config_language_id'), $this->currency->getId($this->session->data['currency'])); 
    } else {
      $cache_key = (string)$this->ocfilter->cache->key('filter', $filter_key, $this->config->get('config_language_id'));
    }    

    $filter_data = $this->ocfilter->cache->get($cache_key);

    if (false !== $filter_data) {
      return $filter_data;
    }

    // Price filter
    if ($special_filter == 'price' && $this->ocfilter->config('special_price')) {
      $filter_data = [
        'filter_key'  => $filter_key,
        'type'        => 'slide_dual',  
        'sort_order'  => $this->ocfilter->config('special_price_sort_order'),
                
        'name'        => $this->language->get('text_price'),
        'prefix'      => $this->currency->getSymbolLeft($this->session->data['currency']),
        'suffix'      => $this->currency->getSymbolRight($this->session->data['currency']), 
      ];
    }       

    // Manufacturers filter
    if ($special_filter == 'manufacturer' && $this->ocfilter->config('special_manufacturer')) {
      $filter_data = [
        'filter_key'  => $filter_key,
        'type'        => $this->ocfilter->config('special_manufacturer_type'),
        'dropdown'    => $this->ocfilter->config('special_manufacturer_dropdown'),
        'image'       => $this->ocfilter->config('special_manufacturer_image'),
        'sort_order'  => $this->ocfilter->config('special_manufacturer_sort_order'),
        
        'name'        => $this->language->get('text_manufacturer'),     
      ];
    }
    
    // Stock status filter
    if ($special_filter == 'stock' && $this->ocfilter->config('special_stock')) {
      if ($this->ocfilter->config('special_stock_method') == 'quantity') {
        $type = ($this->ocfilter->config('special_stock_out_value') ? 'radio' : 'checkbox');
      } else {
        $type = $this->ocfilter->config('special_stock_type'); 
      }
      
      $filter_data = [
        'filter_key'  => $filter_key,
        'type'        => $type,        
        'name'        => $this->language->get('text_stock'),
        'sort_order'  => $this->ocfilter->config('special_stock_sort_order'),
      ];      
    }
    
    // Discount, Newest
    if (($special_filter == 'discount' || $special_filter == 'newest') && $this->ocfilter->config('special_' . $special_filter)) {
      $filter_data = [
        'filter_key'  => $filter_key,
        'type'        => 'checkbox',        
        'name'        => $this->language->get('text_' . $special_filter),
        'sort_order'  => $this->ocfilter->config('special_' . $special_filter . '_sort_order'),
      ];      
    }    
        
    // Dimensions
    if (($special_filter == 'weight' || $special_filter == 'width' || $special_filter == 'length' || $special_filter == 'height') && $this->ocfilter->config('special_' . $special_filter)) {
      $filter_data = [
        'filter_key'  => $filter_key,
        'type'        => 'slide_dual',          
        'sort_order'  => $this->ocfilter->config('special_' . $special_filter . '_sort_order'),
                
        'name'        => $this->language->get('text_' . $special_filter),
        'prefix'      => '',
        'suffix'      => '', 
      ];
    }    
    
    if (!$filter_data && !$this->ocfilter->params->key($filter_key)->is('special')) {
      $this->ocfilter->params->key($filter_key)->expand($filter_id, $source);
      
      $query = $this->ocfilter->query("SELECT * FROM " . DB_PREFIX . "ocfilter_filter f LEFT JOIN " . DB_PREFIX . "ocfilter_filter_description fd ON (f.filter_id = fd.filter_id AND f.source = fd.source) WHERE f.filter_id = '" . (int)$filter_id . "' AND f.source = '" . (int)$source . "' AND f.status = '1' AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

      $filter_data = $query->row;   
    }               

    if (!$filter_data) {
      return [];
    }
  
    $filter_data = array_merge(array(
      'filter_key' => $filter_key,
      'dropdown' => 0,
      'color' => 0,
      'image' => 0,      
      'prefix' => '',
      'suffix' => '',
      'description' => '',
    ), $filter_data);

    $this->ocfilter->cache->set($cache_key, $filter_data);

    return $filter_data;
  }
  
  public function getFilters($data = []) {
    $results = [];    
    
    // Custom placement filters
    if (isset($data['filter_key'])) {
      if (!is_array($data['filter_key'])) {
        $data['filter_key'] = [ $data['filter_key'] ];
      }
            
      $results = $data['filter_key'];   
    }
         
    // Special Filters  
    foreach ($this->ocfilter->params->getSpecialKeys() as $name => $filter_key) {
      if ($this->ocfilter->config('special_' . $name) && (!isset($data['filter_key']) || in_array($filter_key, $data['filter_key']))) {
        if ($name == 'discount' && !empty($data['filter_special'])) {
          continue;
        }
        
        if ($name == 'manufacturer' && isset($data['filter_manufacturer_id']) && $data['filter_manufacturer_id'] > 0) {
          continue;
        }        
        
        $results[] = $filter_key;
      }
    }
       
    if (isset($data['filter_manufacturer_id']) && $data['filter_manufacturer_id'] > 0) {
      $results = array_merge($results, $this->getManufacturerFilters($data));
    } else if (!empty($data['filter_special'])) {
      $results = array_merge($results, $this->getSpecialProductFilters($data));
    } else if (!empty($data['filter_search']) && utf8_strlen(trim($data['filter_search'])) > 1) {
      $results = array_merge($results, $this->getSearchProductFilters($data));
    } else if (!empty($data['filter_category_id'])) {
      $results = array_merge($results, $this->getCategoryFilters($data));
    }

    $filters_data = [];

    foreach ($results as $filter_key) {
      $filter_info = $this->getFilter($filter_key);
      
      if ($filter_info) {
        $filters_data[] = $filter_info;
      }
    }

    return $filters_data;
  }

  protected function getFiltersByProductsSQL($product_sql) {
    return sprintf("SELECT CONCAT(f.filter_id, '.', f.source) AS filter_key FROM " . DB_PREFIX . "ocfilter_filter f LEFT JOIN " . DB_PREFIX . "ocfilter_filter_to_store f2s ON (f.filter_id = f2s.filter_id AND f.source = f2s.source) RIGHT JOIN (SELECT IFNULL(fv2p.filter_id, fr2p.filter_id) AS filter_id, IFNULL(fv2p.source, fr2p.source) AS source FROM (%s) p LEFT JOIN " . DB_PREFIX . "ocfilter_filter_value_to_product fv2p ON (p.product_id = fv2p.product_id) LEFT JOIN " . DB_PREFIX . "ocfilter_filter_range_to_product fr2p ON (p.product_id = fr2p.product_id) GROUP BY filter_id, source) f2p ON (f.filter_id = f2p.filter_id AND f.source = f2p.source) WHERE f.status = '1' AND f2s.store_id = '" . (int)$this->config->get('config_store_id') . "'", $product_sql);
  }

  protected function getCategoryFilters($data = []) {
    $sql = "SELECT CONCAT(f.filter_id, '.', f.source) AS filter_key FROM " . DB_PREFIX . "ocfilter_filter f LEFT JOIN " . DB_PREFIX . "ocfilter_filter_to_store f2s ON (f.filter_id = f2s.filter_id AND f.source = f2s.source)";

    if (isset($data['filter_category_id'])) {
      $sql .= " LEFT JOIN " . DB_PREFIX . "ocfilter_filter_to_category f2c ON (f.filter_id = f2c.filter_id)";

      if ($this->ocfilter->config('category_visibility') == 'parent') {
        $sql .= " LEFT JOIN " . DB_PREFIX . "category_path cp ON (f2c.category_id = cp.category_id)";
      }
    }

    $sql .= " WHERE f.status = '1' AND f2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

    if (isset($data['filter_category_id'])) {
      if ($this->ocfilter->config('category_visibility') == 'parent') {
        $sql .= " AND (cp.path_id = '" . (int)$data['filter_category_id'] . "' OR f2c.category_id = '0')";
      } else {
        $sql .= " AND (f2c.category_id = '" . (int)$data['filter_category_id'] . "' OR f2c.category_id = '0')";
      }
    }

    $sql .= " GROUP BY f.filter_id, f.source";

    $query = $this->ocfilter->query($sql);

    return array_column($query->rows, 'filter_key');
  }

  public function getManufacturerFilters($data = []) {
    $sql = sprintf("SELECT DISTINCT p.product_id FROM " . DB_PREFIX . "product p %s WHERE %s",
      $this->getProductJoinSQL(array_diff_key($data, [ 'filter_manufacturer_id' => true ]), 'p'),
      $this->getProductWhereSQL($data, 'p')
    );

    $query = $this->ocfilter->query($this->getFiltersByProductsSQL($sql));

    return array_column($query->rows, 'filter_key');
  }

  public function getSpecialProductFilters($data = []) {
    $sql = sprintf("SELECT DISTINCT ps.product_id FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) %s WHERE %s",
      $this->getProductJoinSQL(array_diff_key($data, [ 'filter_special' => true ]), 'p'),
      $this->getProductWhereSQL($data, 'p')
    );

    $query = $this->ocfilter->query($this->getFiltersByProductsSQL($sql));

    return array_column($query->rows, 'filter_key');
  }

  public function getSearchProductFilters($data = []) {
    $sql = sprintf("SELECT DISTINCT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) %s WHERE %s",
      $this->getProductJoinSQL(array_diff_key($data, [ 'filter_search' => true ]), 'p'),
      $this->getProductWhereSQL($data, 'p')
    );

    $query = $this->ocfilter->query($this->getFiltersByProductsSQL($sql));

    return array_column($query->rows, 'filter_key');
  }  

  /* VALUES, SLIDERS */
  public function getFilterSliderRange($filter_key, $data = []) {
    $cached = !$this->ocfilter->params->hasSlider();
    
    $special_filter = $this->ocfilter->params->key($filter_key)->special();        
    
    if ($cached) {
      if ($special_filter == 'price') {
        $cache_key = (string)$this->ocfilter->cache->key('filter', $filter_key, 'slider',
          $this->config->get('config_store_id'),
          $this->config->get('config_customer_group_id'),
          $this->currency->getId($this->session->data['currency']),
          $this->ocfilter->config('special_price_consider_tax'),
          $this->ocfilter->config('special_price_consider_regular_price'),
          $this->ocfilter->config('special_price_consider_discount'),
          $this->ocfilter->config('special_price_consider_special'),
          $this->ocfilter->config('special_price_consider_option'),
          $this->ocfilter->config('category_visibility'),
          $this->ocfilter->config('use_kj_series'),
          $this->ocfilter->config('use_hpmodel'),
          $this->ocfilter->config('module_hpm_group_products'),
          $this->ocfilter->config('use_product_master'),
          $this->ocfilter->config('use_product_multistore'),             
          $data
        );        
      } else {
        $cache_key = (string)$this->ocfilter->cache->key('filter', $filter_key, 'slider', 
          $this->config->get('config_store_id'), 
          $this->config->get('config_customer_group_id'),
          $this->ocfilter->config('category_visibility'), 
          $this->ocfilter->config('use_kj_series'),
          $this->ocfilter->config('use_hpmodel'),
          $this->ocfilter->config('module_hpm_group_products'),
          $this->ocfilter->config('use_product_master'),
          $this->ocfilter->config('use_product_multistore'),          
          $data
        );   
      }

      $range_data = $this->ocfilter->cache->get($cache_key);

      if (false !== $range_data) {
        return $range_data;
      }
    }
    
    if ($special_filter == 'price') {
      $range_data = $this->getPriceSliderRange($data);
    } else if ($special_filter == 'weight' || $special_filter == 'width' || $special_filter == 'length' || $special_filter == 'height') {  
      $range_data = $this->getDimensionSliderRange($filter_key, $data);    
    } else {
      $range_data = $this->getRegularSliderRange($filter_key, $data);
    }

    if ($cached) {
      $this->ocfilter->cache->set($cache_key, $range_data); 
    }      

    return $range_data;
  }
  
  protected function getDimensionSliderRange($filter_key, $data = []) {
    $range_data = [
      'min' => 0,
      'max' => 0
    ];
    
    $special_filter = $this->ocfilter->params->key($filter_key)->special();    

    if (!($special_filter == 'weight' || $special_filter == 'width' || $special_filter == 'length' || $special_filter == 'height')) {  
      return $range_data;
    }
  
    $sql = sprintf("SELECT MIN(p.`%s`) AS `min`, MAX(p.`%s`) AS `max` FROM " . DB_PREFIX . "product p %s WHERE p.`%s` > '0' AND %s", 
      $special_filter,
      $special_filter,      
      $this->getProductJoinSQL($data, 'p'),
      $special_filter,
      $this->getProductWhereSQL($data, 'p')
    );

    $query = $this->ocfilter->query($sql);

    if ($query->num_rows) {
      $range_data['min'] = (float)$query->row['min'];
      $range_data['max'] = (float)$query->row['max'];
    }

    return $range_data;
  }
  
  protected function getRegularSliderRange($filter_key, $data = []) {
    $range_data = [ 'min' => 0, 'max' => 0, ];

    $this->ocfilter->params->key($filter_key)->expand($filter_id, $source);

    $sql = sprintf("SELECT MIN(fr2p.`min`) AS `min`, GREATEST(MAX(fr2p.`max`), MAX(fr2p.`min`)) AS `max` FROM " . DB_PREFIX . "ocfilter_filter_range_to_product fr2p LEFT JOIN " . DB_PREFIX . "product p ON (fr2p.product_id = p.product_id) %s WHERE fr2p.filter_id = '" . (int)$filter_id . "' AND fr2p.source = '" . (int)$source . "' AND %s",
      $this->getProductJoinSQL($data, 'p'),
      $this->getProductWhereSQL($data, 'p')
    );
    
    $query = $this->ocfilter->query($sql);

    if ($query->num_rows) {
      $range_data['min'] = (float)$query->row['min'];
      $range_data['max'] = (float)$query->row['max'];
    }
    
    return $range_data;
  }  
  
  protected function getTaxJoinSQL() {
    return "LEFT JOIN (SELECT * FROM (SELECT DISTINCT tclass.tax_class_id, trate.rate, trate.type, trule.priority
              FROM " . DB_PREFIX . "tax_class tclass
              LEFT JOIN " . DB_PREFIX . "tax_rule trule ON (tclass.tax_class_id = trule.tax_class_id)
              LEFT JOIN " . DB_PREFIX . "tax_rate trate ON (trule.tax_rate_id = trate.tax_rate_id)
              LEFT JOIN " . DB_PREFIX . "tax_rate_to_customer_group trate2cg ON (trate.tax_rate_id = trate2cg.tax_rate_id)
              WHERE trate2cg.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') result 
             GROUP BY result.tax_class_id ORDER BY result.priority
           ) tx ON (p.tax_class_id = tx.tax_class_id)";
  }
  
  public function getPriceSliderRange($data) {
    if (isset($data['filter_params'])) {
      $data['filter_params'] = $this->ocfilter->params->getWithout($this->ocfilter->params->special('price')->key());
    }
        
    $range = [ 'min' => [], 'max' => [], ];

    // Get default price range
    if ($this->ocfilter->config('special_price_consider_regular_price')) {
      $this->getRegularPriceRange($data, $range);
    }

    // Get special price range
    if ($this->ocfilter->config('special_price_consider_special')) {
      $this->getSpecialPriceRange($data, $range);
    }

    // Get discount price range
    if ($this->ocfilter->config('special_price_consider_discount')) {
      $this->getDiscountPriceRange($data, $range);
    }

    // Get options price range
    if ($this->ocfilter->config('special_price_consider_option')) {
      $this->getOptionsPriceRange($data, $range);
    }

    if ($range['min'] && $range['max']) {
      return [
        'min' => $this->currency->format(min($range['min']), $this->session->data['currency'], '', false),
        'max' => $this->currency->format(max($range['max']), $this->session->data['currency'], '', false),
      ];
    }

    return [ 'min' => 0, 'max' => 0, ];
  }  
  
  protected function getRegularPriceRange($data, &$range) {   
    $sql = "SELECT MIN(price) AS `min`, MAX(price) AS `max` FROM (SELECT";
    
    if ($this->ocfilter->config('special_price_consider_tax')) {
      $sql .= " (p.price + IFNULL(IF(tx.type = 'F', tx.rate, (p.price / 100 * tx.rate)), 0)) AS price";
    } else {
      $sql .= " p.price";
    }
    
    $sql .= " FROM " . DB_PREFIX . "product p";
    
    $sql .= " " . $this->getProductJoinSQL($data, 'p');     
    
    if ($this->ocfilter->config('special_price_consider_tax')) {     
      $sql .= " " . $this->getTaxJoinSQL();
    }    
    
    $sql .= " WHERE p.price > '0'";
    
    $sql .= " AND " . $this->getProductWhereSQL($data, 'p');
    
    $sql .= ") result";
    
    $query = $this->ocfilter->query($sql);  
    
    if ($query->num_rows && $query->row['min'] > 0) {
      $range['min'][] = $query->row['min'];
      $range['max'][] = $query->row['max'];
    }  
  }
  
  protected function getSpecialPriceRange($data, &$range) {
    $sql = "SELECT MIN(price) AS `min`, MAX(price) AS `max` FROM (SELECT";
    
    if ($this->ocfilter->config('special_price_consider_tax')) {
      $sql .= " (ocf_ps.price + IFNULL(IF(tx.type = 'F', tx.rate, (ocf_ps.price / 100 * tx.rate)), 0)) AS price";
    } else {
      $sql .= " ocf_ps.price";
    }
    
    $sql .= " FROM " . DB_PREFIX . "product_special ocf_ps LEFT JOIN " . DB_PREFIX . "product p ON (ocf_ps.product_id = p.product_id)";
    
    $sql .= " " . $this->getProductJoinSQL($data, 'p');     
    
    if ($this->ocfilter->config('special_price_consider_tax')) {     
      $sql .= " " . $this->getTaxJoinSQL();
    }    
    
    $sql .= " WHERE ocf_ps.price > '0' AND ocf_ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ocf_ps.date_start = '0000-00-00' OR ocf_ps.date_start < '" . $this->db->escape(date('Y-m-d')) . "') AND (ocf_ps.date_end = '0000-00-00' OR ocf_ps.date_end > '" . $this->db->escape(date('Y-m-d')) . "'))";
    
    $sql .= " AND " . $this->getProductWhereSQL($data, 'p');
    
    $sql .= ") result";
    
    $query = $this->ocfilter->query($sql);  
    
    if ($query->num_rows && $query->row['min'] > 0) {
      $range['min'][] = $query->row['min'];
      $range['max'][] = $query->row['max'];
    }    
  }

  protected function getDiscountPriceRange($data, &$range) {    
    $sql = "SELECT MIN(price) AS `min`, MAX(price) AS `max` FROM (SELECT";
    
    if ($this->ocfilter->config('special_price_consider_tax')) {
      $sql .= " (ocf_pd.price + IFNULL(IF(tx.type = 'F', tx.rate, (ocf_pd.price / 100 * tx.rate)), 0)) AS price";
    } else {
      $sql .= " ocf_pd.price";
    }
    
    $sql .= " FROM " . DB_PREFIX . "product_discount ocf_pd LEFT JOIN " . DB_PREFIX . "product p ON (ocf_pd.product_id = p.product_id)";
    
    $sql .= " " . $this->getProductJoinSQL($data, 'p');     
    
    if ($this->ocfilter->config('special_price_consider_tax')) {     
      $sql .= " " . $this->getTaxJoinSQL();
    }    
    
    $sql .= " WHERE ocf_pd.price > '0' AND ocf_pd.quantity > '0' AND ocf_pd.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ocf_pd.date_start = '0000-00-00' OR ocf_pd.date_start < '" . $this->db->escape(date('Y-m-d')) . "') AND (ocf_pd.date_end = '0000-00-00' OR ocf_pd.date_end > '" . $this->db->escape(date('Y-m-d')) . "'))";
    
    $sql .= " AND " . $this->getProductWhereSQL($data, 'p');
    
    $sql .= ") result";
    
    $query = $this->ocfilter->query($sql);  
    
    if ($query->num_rows && $query->row['min'] > 0) {
      $range['min'][] = $query->row['min'];
      $range['max'][] = $query->row['max'];
    }           
  }

  protected function getOptionsPriceRange($data, &$range) {
    $sql = "SELECT MIN(price) AS `min`, MAX(price) AS `max` FROM (SELECT";

    if ($this->ocfilter->config('special_price_consider_tax')) {
      $sql .= " (option_price + IFNULL(IF(tax_type = 'F', tax_rate, (option_price / 100 * tax_rate)), 0)) AS price";
    } else {
      $sql .= " option_price AS price";
    }
              
    $sql .= " FROM (SELECT COALESCE(
      IF(ocf_pov.price_prefix = '-', p.price - ocf_pov.price, NULL), 
      IF(ocf_pov.price_prefix = '+', p.price + ocf_pov.price, NULL), 
      IF(ocf_pov.price_prefix = '=', ocf_pov.price, NULL),       
      IF(ocf_pov.price_prefix = '*', p.price + p.price * ocf_pov.price, NULL), 
      IF(ocf_pov.price_prefix = '%', p.price + p.price * (ocf_pov.price / 100), NULL),       
      p.price
    ) AS option_price";

    if ($this->ocfilter->config('special_price_consider_tax')) {     
      $sql .= ", tx.type AS tax_type, tx.rate AS tax_rate";
    }

    $sql .= " FROM " . DB_PREFIX . "product_option_value ocf_pov LEFT JOIN " . DB_PREFIX . "product p ON (ocf_pov.product_id = p.product_id)";

    $sql .= " " . $this->getProductJoinSQL($data, 'p');     
    
    if ($this->ocfilter->config('special_price_consider_tax')) {     
      $sql .= " " . $this->getTaxJoinSQL();
    }    

    $sql .= " WHERE ocf_pov.price > '0'/* AND ocf_pov.quantity > '0'*/";
    
    $sql .= " AND " . $this->getProductWhereSQL($data, 'p');

    $sql .= ") results WHERE option_price > '0') results2";    
        
    $query = $this->ocfilter->query($sql);  

    if ($query->num_rows && $query->row['min'] > 0) {
      $range['min'][] = $query->row['min'];
      $range['max'][] = $query->row['max'];
    }       
  } 
    
  public function getFilterChartData($filter_key, $min, $max, $data = []) {
    $cached = !$this->ocfilter->params->hasSlider();
    
    $special_filter = $this->ocfilter->params->key($filter_key)->special();        
    
    if ($cached) {
      if ($special_filter == 'price') {
        $cache_key = (string)$this->ocfilter->cache->key('filter', $filter_key, 'chart', $min, $max,
          $this->config->get('config_store_id'),
          $this->config->get('config_customer_group_id'),          
          $this->currency->getId($this->session->data['currency']),          
          $this->ocfilter->config('special_price_consider_tax'),
          $this->ocfilter->config('special_price_consider_regular_price'),
          $this->ocfilter->config('special_price_consider_discount'),
          $this->ocfilter->config('special_price_consider_special'),
          $this->ocfilter->config('special_price_consider_option'),
          $this->ocfilter->config('category_visibility'),
          $this->ocfilter->config('use_kj_series'),
          $this->ocfilter->config('use_hpmodel'),
          $this->ocfilter->config('module_hpm_group_counter'),
          $this->ocfilter->config('use_product_master'),
          $this->ocfilter->config('use_product_multistore'),         
          $data
        );        
      } else {
        $cache_key = (string)$this->ocfilter->cache->key('filter', $filter_key, 'chart', $min, $max, 
          $this->config->get('config_store_id'), 
          $this->config->get('config_customer_group_id'), 
          $this->ocfilter->config('category_visibility'),
          $this->ocfilter->config('use_kj_series'),
          $this->ocfilter->config('use_hpmodel'),
          $this->ocfilter->config('module_hpm_group_counter'),
          $this->ocfilter->config('use_product_master'),
          $this->ocfilter->config('use_product_multistore'),         
          $data
        );                                                           
      }

      $chart_data = $this->ocfilter->cache->get($cache_key);

      if (false !== $chart_data) {
        return $chart_data;
      }
    }
    
    $chart_data = [];
    
    $factor = round(($max - $min) / 7);
    
    if ($special_filter == 'price') {
      $chart_data = $this->getPriceChartData($factor, $data);
    } /*else if ($special_filter == 'weight' || $special_filter == 'width' || $special_filter == 'length' || $special_filter == 'height') {  
      $chart_data = $this->getDimensionChartData($filter_key, $data);    
    } else {
      $chart_data = $this->getRegularChartData($filter_key, $data);
    }*/

    if ($cached) {
      $this->ocfilter->cache->set($cache_key, $chart_data); 
    }      

    return $chart_data;    
  }
  
  public function getPriceChartData($factor = 10, $data = []) {
    $sql = "SELECT COUNT(*) AS total, p.price FROM " . DB_PREFIX . "product p";
    
    $sql .= " " . $this->getProductJoinSQL($data, 'p');     
        
    $sql .= " WHERE p.price > '0'";
    
    $sql .= " AND " . $this->getProductWhereSQL($data, 'p');
    
    $sql .= " GROUP BY ROUND(p.price / " . (int)$factor . ")";
    
    $query = $this->ocfilter->query($sql);  
    
    return $query->rows;    
  }

  /* VALUES */
  public function getFilterValueName($filter_key, $value_id) {
    $special_filter = $this->ocfilter->params->key($filter_key)->special();  
   
    if ($special_filter == 'manufacturer') {
      if ($this->ocfilter->config('use_manufacturer_description')) {
        $query = $this->ocfilter->query("SELECT name FROM " . DB_PREFIX . "manufacturer_description WHERE manufacturer_id = '" . (int)$value_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
      } else {
        $query = $this->ocfilter->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$value_id . "'"); 
      }      
      
      return $query->num_rows ? $query->row['name'] : '';
    }
    
    if ($special_filter == 'stock') {
      if ($this->ocfilter->config('special_stock_method') == 'quantity') {        
        if ($value_id == 2) {
          return $this->language->get('text_in_stock');
        } else if ($value_id == 1) {
          return $this->language->get('text_out_of_stock');
        }
      } else {
        $query = $this->ocfilter->query("SELECT name FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int)$value_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
      
        return $query->num_rows ? $query->row['name'] : '';
      }  
    }
    
    if ($special_filter == 'discount') {
      return $this->language->get('text_discount_yes');
    }
    
    if ($special_filter == 'newest') {
      return $this->language->get('text_newest_yes');
    }    
    
    if (!$this->ocfilter->params->key($filter_key)->is('special')) {
      $this->ocfilter->params->key($filter_key)->expand($filter_id, $source);

      $query = $this->ocfilter->query("SELECT name FROM " . DB_PREFIX . "ocfilter_filter_value_description WHERE value_id = '" . $this->db->escape((string)$value_id) . "' AND source = '" . (int)$source . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

      return $query->num_rows ? $query->row['name'] : '';
    }

    return '';
  }
  
  public function getFilterValues($filter_key) {
    $special_filter = $this->ocfilter->params->key($filter_key)->special();  
        
    $cached = true;//(!isset($data['start']) || $data['start'] < 1) && !$this->ocfilter->params->hasSlider();

    if ($cached) {
      $cache_key = (string)$this->ocfilter->cache->key('filter', $filter_key, 'values', $this->config->get('config_language_id'));
      
      $filter_value_data = $this->ocfilter->cache->get($cache_key);

      if (false !== $filter_value_data) {
        return $filter_value_data;
      }
    }

    $filter_value_data = [];

    if ($special_filter == 'manufacturer') {
      $filter_value_data = $this->getManufacturerValues();    
    }
    
    if ($special_filter == 'stock') {
      $filter_value_data = $this->getStockStatusValues();    
    }
    
    if ($special_filter == 'discount') {
      $filter_value_data[] = [
        'value_id' => 1,        
        'name' => $this->language->get('text_discount_yes')
      ];    
    }   

    if ($special_filter == 'newest') {
      $filter_value_data[] = [
        'value_id' => 1,        
        'name' => $this->language->get('text_newest_yes')
      ];    
    }        
    
    if ($filter_value_data) {
      $filter_value_data = array_map(function($v) {
        return array_merge(array(          
          'color' => '',
          'image' => '',
        ), $v);
      }, $filter_value_data);
    }
    
    if (!$filter_value_data && !$this->ocfilter->params->key($filter_key)->is('special')) {
      $this->ocfilter->params->key($filter_key)->expand($filter_id, $source);

      $sort = [
        "fv.sort_order",
        //"CAST(REPLACE(fvd.name, ',', '.') AS DECIMAL)",
        "(fvd.name = '-') DESC",
        "(fvd.name = '0') DESC",
        "(fvd.name + 0 > 0) DESC",
        "(fvd.name + 0)",
        "IF(CONCAT('', (SUBSTRING(fvd.name, 1, 1) * 1)) = SUBSTRING(fvd.name, 1, 1), LENGTH(fvd.name), 1)",
        "fvd.name",
      ];
      
      $sql = "SELECT fv.value_id, fv.color, fv.image, fvd.name FROM " . DB_PREFIX . "ocfilter_filter_value fv LEFT JOIN " . DB_PREFIX . "ocfilter_filter_value_description fvd ON (fv.value_id = fvd.value_id AND fv.source = fvd.source) WHERE fv.filter_id = '" . (int)$filter_id . "' AND fv.source = '" . (int)$source . "' AND fvd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY " . implode(", ", $sort);

      if (isset($data['start']) && isset($data['limit'])) {
        $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
      }

      $query = $this->ocfilter->query($sql);

      $filter_value_data = $query->rows;      
    }

    if ($cached) {
      $this->ocfilter->cache->set($cache_key, $filter_value_data);
    }

    return $filter_value_data;
  }

  /* SPECIAL FILTERS */
  public function getStockStatusValues() {
    $filter_value_data = [];
    
    if ($this->ocfilter->config('special_stock_method') == 'quantity') {        
      $filter_value_data[] = [
        'value_id'    => 2,
        'name'        => $this->language->get('text_in_stock'),        
      ];

      if ($this->ocfilter->config('special_stock_out_value')) {
        $filter_value_data[] = [
          'value_id'    => 1,
          'name'        => $this->language->get('text_out_of_stock'),          
        ];
      }    
    } else {
      $query = $this->ocfilter->query("SELECT stock_status_id AS value_id, name FROM " . DB_PREFIX . "stock_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name");
    
      $filter_value_data = $query->rows;
    }      

    return $filter_value_data;
  }

  public function getManufacturerValues($data = []) {   
    $sql = "SELECT m.manufacturer_id AS value_id, m.image"; 
    
    if ($this->ocfilter->config('use_manufacturer_description')) {
      $sql .= ", md.name AS name";
    } else {
      $sql .= ", m.name AS name";
    }
    
    $sql .= " FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) LEFT JOIN " . DB_PREFIX . "product p ON (m.manufacturer_id = p.manufacturer_id)";
    
    if ($this->ocfilter->config('use_manufacturer_description')) {
      $sql .= " LEFT JOIN " . DB_PREFIX . "manufacturer_description md ON (m.manufacturer_id = md.manufacturer_id)";
    }    

    $sql .= " WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
    
    if ($this->ocfilter->config('use_manufacturer_description')) {
      $sql .= " AND md.language_id = '" . (int)$this->config->get('config_language_id') . "'";
    }
    
    $sql .= " GROUP BY m.manufacturer_id ORDER BY m.sort_order ASC, name ASC";

    // TODO: Values pagination
    if (isset($data['start']) && isset($data['limit'])) {
      $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
    }

    $query = $this->ocfilter->query($sql);

    return $query->rows;
  }

  /* FILTER PRODUCT COUNTER */
  public function getCounters($data = []) {
    // Do not caching price and sliders
    $cached = !$this->ocfilter->params->hasSlider();   

    if ($cached) {
      $cache_key = (string)$this->ocfilter->cache->key('counter', 
        $this->config->get('config_store_id'),  
        $this->ocfilter->config('category_visibility'),
        $this->ocfilter->config('use_kj_series'),
        $this->ocfilter->config('use_hpmodel'),
        $this->ocfilter->config('module_hpm_group_counter'),
        $this->ocfilter->config('use_product_master'),
        $this->ocfilter->config('use_product_multistore'),        
        $data
      );

      $ocfilter_counter_data = $this->ocfilter->cache->get($cache_key);

      if (false !== $ocfilter_counter_data) {
        return $ocfilter_counter_data;
      }
    }
    
    $ocfilter_counter_data = [];

    $set_filter_value_count = function($filter_key, $value_id, $total) use (&$ocfilter_counter_data) {
      if (!isset($ocfilter_counter_data[$filter_key])) {
        $ocfilter_counter_data[$filter_key] = [];
      }

      $ocfilter_counter_data[$filter_key][$value_id] = (int)$total;
    };

    // Manufacturers
    if ($this->ocfilter->config('special_manufacturer') && !isset($data['filter_manufacturer_id']) && (!isset($data['count_filter_key']) || $this->ocfilter->params->key($data['count_filter_key'])->is('manufacturer'))) {
      $sql = $this->getManufacturersCounterSQL($data);
    
      $query = $this->ocfilter->query($sql);

      foreach ($query->rows as $result) {
        $set_filter_value_count($this->ocfilter->params->special('manufacturer')->key(), $result['vid'], $result['total']);
      }
    }

    // Stock Status
    if ($this->ocfilter->config('special_stock') && (!isset($data['count_filter_key']) || $this->ocfilter->params->key($data['count_filter_key'])->is('stock'))) {
      if ($this->ocfilter->config('special_stock_method') == 'stock_status_id') {
        $sql = $this->getStockStatusCounterSQL($data);
      } else {
        $sql = $this->getQuantityCounterSQL($data);
      }
      
      $query = $this->ocfilter->query($sql);

      foreach ($query->rows as $result) {
        $set_filter_value_count($this->ocfilter->params->special('stock')->key(), $result['vid'], $result['total']);
      }
    }
    
    // Newest
    if ($this->ocfilter->config('special_newest') && $this->ocfilter->config('special_newest_interval') > 0 && (!isset($data['count_filter_key']) || $this->ocfilter->params->key($data['count_filter_key'])->is('newest'))) {
      $sql = $this->getNewestCounterSQL($data);
      
      $query = $this->ocfilter->query($sql);

      foreach ($query->rows as $result) {
        $set_filter_value_count($this->ocfilter->params->special('newest')->key(), $result['vid'], $result['total']);
      }
    }    

    // Discount 
    if ($this->ocfilter->config('special_discount') && empty($data['filter_special']) && ($this->ocfilter->config('special_discount_consider_special') || $this->ocfilter->config('special_discount_consider_discount')) && (!isset($data['count_filter_key']) || $this->ocfilter->params->key($data['count_filter_key'])->is('discount'))) {
      $sql = $this->getDiscountCounterSQL($data);
      
      $query = $this->ocfilter->query($sql);

      foreach ($query->rows as $result) {
        $set_filter_value_count($this->ocfilter->params->special('discount')->key(), $result['vid'], $result['total']);
      }
    }       

    // Filter Values
    $union = $this->getFilterValuesCounterSQL($data);
       
    foreach ($union as $sql) {
      $query = $this->ocfilter->query($sql);

      foreach ($query->rows as $result) {
        $set_filter_value_count($result['fk'], $result['vid'], $result['total']);
      }
    }

    if ($cached) {
      $this->ocfilter->cache->set($cache_key, $ocfilter_counter_data);
    }

    return $ocfilter_counter_data;
  }

  private function getNewestCounterSQL($data) {
    if (!in_array($this->ocfilter->config('special_newest_period'), [ 'hour', 'day', 'week', 'month' ])) {
      return '';
    }
    
    $filter_key = $this->ocfilter->params->special('newest')->key();
    
    if (isset($data['filter_params']) && isset($data['filter_params'][$filter_key])) {
      unset($data['filter_params'][$filter_key]);
    }

    $where = "p.date_added > '" . $this->db->escape(date('Y-m-d H:i:s', strtotime('-' . (int)$this->ocfilter->config('special_newest_interval') . ' ' . $this->ocfilter->config('special_newest_period')))) . "'";

    $where .= " AND " . $this->getProductWhereSQL($data, 'p');
    
    if ($this->ocfilter->config('show_counter')) {
      $sql = sprintf("SELECT " . $this->getProductCountSQL() . " AS total, '1' AS vid FROM " . DB_PREFIX . "product p %s WHERE %s", $this->getProductJoinSQL($data, 'p'), $where);
    } else {
      $sql = sprintf("SELECT 1 AS total, '1' AS vid FROM " . DB_PREFIX . "product p %s WHERE %s LIMIT 1", $this->getProductJoinSQL($data, 'p'), $where);
    }
    
    return $sql;
  }
  
  private function getDiscountCounterSQL($data) {  
    $filter_key = $this->ocfilter->params->special('discount')->key();
    
    if (isset($data['filter_params']) && isset($data['filter_params'][$filter_key])) {
      unset($data['filter_params'][$filter_key]);
    }

    $join = "";
    
    if ($this->ocfilter->config('special_discount_consider_special')) {
      $join .= " LEFT JOIN " . DB_PREFIX . "product_special ps_yes ON (p.product_id = ps_yes.product_id)";
    }  

    if ($this->ocfilter->config('special_discount_consider_discount')) {
      $join .= " LEFT JOIN " . DB_PREFIX . "product_discount pd_yes ON (p.product_id = pd_yes.product_id)";
    }    

    $join .= " " . $this->getProductJoinSQL($data, 'p');

    $where = [];

    if ($this->ocfilter->config('special_discount_consider_special')) {
      $where[] = "(ps_yes.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps_yes.date_start = '0000-00-00' OR ps_yes.date_start < '" . $this->db->escape(date('Y-m-d')) . "') AND (ps_yes.date_end = '0000-00-00' OR ps_yes.date_end > '" . $this->db->escape(date('Y-m-d')) . "')))";
    }
    
    if ($this->ocfilter->config('special_discount_consider_discount')) {
      $where[] = "(pd_yes.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((pd_yes.date_start = '0000-00-00' OR pd_yes.date_start < '" . $this->db->escape(date('Y-m-d')) . "') AND (pd_yes.date_end = '0000-00-00' OR pd_yes.date_end > '" . $this->db->escape(date('Y-m-d')) . "')) AND pd_yes.quantity > '0')";
    }    
    
    $where = "(" . implode(" OR ", $where) . ") AND " . $this->getProductWhereSQL($data, 'p');

    if ($this->ocfilter->config('show_counter')) {
      $sql = sprintf("SELECT " . $this->getProductCountSQL() . " AS total, '1' AS vid FROM " . DB_PREFIX . "product p %s WHERE %s", $join, $where);
    } else {
      $sql = sprintf("SELECT 1 AS total, '1' AS vid FROM " . DB_PREFIX . "product p %s WHERE %s LIMIT 1", $join, $where);
    }

    return $sql;
  }  

  private function getManufacturersCounterSQL($data) {
    $filter_key = $this->ocfilter->params->special('manufacturer')->key();

    if (isset($data['filter_params']) && isset($data['filter_params'][$filter_key])) {
      unset($data['filter_params'][$filter_key]);
    }

    $sql = sprintf("SELECT " . $this->getProductCountSQL() . " AS total, p.manufacturer_id AS vid FROM " . DB_PREFIX . "product p %s WHERE %s GROUP BY vid", $this->getProductJoinSQL($data, 'p'), $this->getProductWhereSQL($data, 'p'));    

    return $sql;
  }

  private function getStockStatusCounterSQL($data) {
    $filter_key = $this->ocfilter->params->special('stock')->key();
    
    if (isset($data['filter_params']) && isset($data['filter_params'][$filter_key])) {
      unset($data['filter_params'][$filter_key]);
    }
    
    $sql = sprintf("SELECT " . $this->getProductCountSQL() . " AS total, p.stock_status_id AS vid FROM " . DB_PREFIX . "product p %s WHERE %s GROUP BY vid", $this->getProductJoinSQL($data, 'p'), $this->getProductWhereSQL($data, 'p'));

    return $sql;
  }

  private function getQuantityCounterSQL($data) {
    $filter_key = $this->ocfilter->params->special('stock')->key();
    
    if (isset($data['filter_params']) && isset($data['filter_params'][$filter_key])) {
      unset($data['filter_params'][$filter_key]);
    }

    if ($this->ocfilter->config('show_counter')) {
      $sql = sprintf("SELECT " . $this->getProductCountSQL() . " AS total, IF(p.quantity > '0', '2', '1') AS vid FROM " . DB_PREFIX . "product p %s WHERE %s GROUP BY vid", $this->getProductJoinSQL($data, 'p'), $this->getProductWhereSQL($data, 'p'));
    } else {
      $sql = sprintf("(SELECT '2' AS vid, 1 AS total FROM " . DB_PREFIX . "product p %s WHERE %s AND p.quantity > 0 LIMIT 1)", $this->getProductJoinSQL($data, 'p'), $this->getProductWhereSQL($data, 'p')); 

      $sql .= " UNION ";
      
      $sql .= sprintf("(SELECT '1' AS vid, 1 AS total FROM " . DB_PREFIX . "product p %s WHERE %s AND p.quantity < 1 LIMIT 1)", $this->getProductJoinSQL($data, 'p'), $this->getProductWhereSQL($data, 'p')); 
    }
    
    return $sql;
  }

  private function getFilterValuesCounterSQL($data) {
    if (isset($data['filter_params'])) {
      $params = $data['filter_params'];
    } else {
      $params = [];
    }

    $union = [];

    $join_filter_data = array_diff_key($data, [ 'filter_key' => true ]);

    // All Filters and values
    $where = $this->getProductWhereSQL($data, 'p');
    
    if (!empty($data['count_filter_key']) && !$this->ocfilter->params->key($data['count_filter_key'])->is('special')) {
      $this->ocfilter->params->key($data['count_filter_key'])->expand($filter_id, $source);      
      
      $where .= " AND fv2p.filter_id = '" . (int)$filter_id . "' AND fv2p.source = '" . (int)$source . "'";
    }
    
    $union[] = sprintf("SELECT " . $this->getProductCountSQL() . " AS total, CONCAT(fv2p.filter_id, '.', fv2p.source) AS fk, fv2p.value_id AS vid FROM " . DB_PREFIX . "ocfilter_filter_value_to_product fv2p LEFT JOIN " . DB_PREFIX . "product p ON (fv2p.product_id = p.product_id) %s WHERE %s GROUP BY fk, vid",
      $this->getProductJoinSQL($join_filter_data, 'p'),
      $where
    );    

    // Selecteds   
    if ($params) {
      $added = [];

      foreach ($params as $filter_key => $values) {
        if ($this->ocfilter->params->key($filter_key)->is('special') || $this->ocfilter->params->isRange($values[0])) {
          continue;
        }
        
        if (!empty($data['count_filter_key']) && $data['count_filter_key'] != $filter_key) {
          continue;
        }  

        $_params = $params;

        if (!in_array($filter_key, $added)) {
          unset($_params[$filter_key]);

          $added[] = $filter_key;
        }

        $data['filter_params'] = $_params;

        $join_filter_data = array_diff_key($data, [ 'filter_key' => true ]);

        $this->ocfilter->params->key($filter_key)->expand($filter_id, $source);     

        $union[] = sprintf("SELECT " . $this->getProductCountSQL() . " AS total, CONCAT(fv2p.filter_id, '.', fv2p.source) AS fk, fv2p.value_id AS vid FROM " . DB_PREFIX . "ocfilter_filter_value_to_product fv2p LEFT JOIN " . DB_PREFIX . "product p ON (fv2p.product_id = p.product_id) %s WHERE fv2p.filter_id = '" . (int)$filter_id . "' AND fv2p.source = '" . (int)$source . "' AND %s GROUP BY fk, vid",
          $this->getProductJoinSQL($join_filter_data, 'p'),
          $this->getProductWhereSQL($data, 'p')
        );
      }
    }

    return $union;
  }

  /* PRODUCT SEARCHING */
  public function getProductSearchSQL(array $filter_params = [], $include_custom = false) {
    $join = [];
    $where = [];

    if ($include_custom && $this->ocfilter->placement->isCustomPage()) {
      $join[] = "LEFT JOIN " . DB_PREFIX . "ocfilter_filter_value_to_product fv2p_custom ON (p.product_id = fv2p_custom.product_id)";

      $implode = [];

      $filters = $this->ocfilter->placement->getCustomPageFilters();

      foreach ($filters as $filter_key) {
        $this->ocfilter->params->key($filter_key)->expand($filter_id, $source);
        
        $implode[] = "(fv2p_custom.filter_id = '" . (int)$filter_id . "' AND fv2p_custom.source = '" . (int)$source . "')";
      }

      if ($implode) {
        $where[] = "(" . implode(" OR ", $implode) . ")";
      }
    }

    foreach ($filter_params as $filter_key => $values) {
      $special_filter = $this->ocfilter->params->key($filter_key)->special();    
      
      // Filter by price
      if ($special_filter == 'price') {
        list($from, $to) = $this->ocfilter->params->parseRange(array_shift($values));

        if (isset($from) && isset($to)) {
          $price_from = floor((float)$from / $this->currency->getValue($this->session->data['currency']));
          $price_to = ceil((float)$to / $this->currency->getValue($this->session->data['currency']));

          if ($this->ocfilter->config('special_price_consider_tax')) {
            $join[] = $this->getTaxJoinSQL();

            $tax_sql = "IFNULL(IF(tx.type = 'F', tx.rate, (%s / 100 * tx.rate)), 0)";
          } else {
            $tax_sql = "";
          }

          $or = [];

          if ($this->ocfilter->config('special_price_consider_regular_price')) {
            if ($tax_sql) {
              $or[] = "(p.price + " . sprintf($tax_sql, 'p.price') . ") BETWEEN '" . (float)$price_from . "' AND '" . (float)$price_to . "'";
            } else {
              $or[] = "p.price BETWEEN '" . (float)$price_from . "' AND '" . (float)$price_to . "'";
            }            
          }

          if ($this->ocfilter->config('special_price_consider_discount')) {
            $join[] = "LEFT JOIN " . DB_PREFIX . "product_discount pd_ocf ON (pd_ocf.product_id = p.product_id)";

            $or['pd'] = "(pd_ocf.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd_ocf.quantity > '0' AND ((pd_ocf.date_start = '0000-00-00' OR pd_ocf.date_start < '" . $this->db->escape(date('Y-m-d')) . "') AND (pd_ocf.date_end = '0000-00-00' OR pd_ocf.date_end > '" . $this->db->escape(date('Y-m-d')) . "'))";
            
            if ($tax_sql) {            
              $or['pd'] .= " AND (pd_ocf.price + " . sprintf($tax_sql, 'pd_ocf.price') . ") BETWEEN '" . (float)$price_from . "' AND '" . (float)$price_to . "'";
            } else {
              $or['pd'] .= " AND pd_ocf.price BETWEEN '" . (float)$price_from . "' AND '" . (float)$price_to . "'";
            }
            
            $or['pd'] .= ")";
          }

          if ($this->ocfilter->config('special_price_consider_special')) {
            $join[] = "LEFT JOIN " . DB_PREFIX . "product_special ps_ocf ON (ps_ocf.product_id = p.product_id)";

            $or['ps'] = "(ps_ocf.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps_ocf.date_start = '0000-00-00' OR ps_ocf.date_start < '" . $this->db->escape(date('Y-m-d')) . "') AND (ps_ocf.date_end = '0000-00-00' OR ps_ocf.date_end > '" . $this->db->escape(date('Y-m-d')) . "'))";
            
            if ($tax_sql) {            
              $or['ps'] .= " AND (ps_ocf.price + " . sprintf($tax_sql, 'ps_ocf.price') . ") BETWEEN '" . (float)$price_from . "' AND '" . (float)$price_to . "'";
            } else {
              $or['ps'] .= " AND ps_ocf.price BETWEEN '" . (float)$price_from . "' AND '" . (float)$price_to . "'";
            }
            
            $or['ps'] .= ")";            
          }

          if ($this->ocfilter->config('special_price_consider_option')) {
            $join[] = "LEFT JOIN " . DB_PREFIX . "product_option_value pov_ocf ON (pov_ocf.product_id = p.product_id)";

            $option_price_sql = "COALESCE(
              IF(pov_ocf.price_prefix = '-', p.price - pov_ocf.price, NULL), 
              IF(pov_ocf.price_prefix = '+', p.price + pov_ocf.price, NULL), 
              IF(pov_ocf.price_prefix = '=', pov_ocf.price, NULL), 
              IF(pov_ocf.price_prefix = '*', p.price + p.price * pov_ocf.price, NULL), 
              IF(pov_ocf.price_prefix = '%', p.price + p.price * (pov_ocf.price / 100), NULL), 
              p.price + pov_ocf.price, 
              p.price
            )";
            
            if ($tax_sql) {
              $or[] = "(pov_ocf.quantity > '0' AND ((" . $option_price_sql . " + " . sprintf($tax_sql, $option_price_sql) . ") BETWEEN '" . (float)$price_from . "' AND '" . (float)$price_to . "'))"; 
            } else {
              $or[] = "(pov_ocf.quantity > '0' AND (" . $option_price_sql . " BETWEEN '" . (float)$price_from . "' AND '" . (float)$price_to . "'))"; 
            }                                     
          }

          if ($or) {
            $where[] = "(" . implode(" OR ", $or) . ")";
          }
        }

        unset($filter_params[$filter_key]);
        // Filter by manufacturer
      } else if ($special_filter == 'manufacturer') {
        $implode = [];

        foreach ($values as $value_id) {
          $implode[] = "p.manufacturer_id = '" . (int)$value_id . "'";
        }

        if ($implode) {
          $where[] = "(" . implode(" OR ", $implode) . ")";
        }

        unset($filter_params[$filter_key]);
        // Filter by stock status
      } else if ($special_filter == 'stock') {
        $implode = [];

        if ($this->ocfilter->config('special_stock_method') == 'stock_status_id') {
          foreach ($values as $value_id) {
            $implode[] = "p.stock_status_id = '" . (int)$value_id . "'";
          }

          if ($implode) {
            $where[] = "(" . implode(" OR ", $implode) . ")";
          }
        } else {
          $stock_status = array_shift($values);

          if ($stock_status == 2) {
            $where[] = "p.quantity > '0'";
          } else {
            $where[] = "p.quantity < '1'";
          }
        }

        unset($filter_params[$filter_key]);
      } else if ($special_filter == 'newest' && $this->ocfilter->config('special_newest_interval') > 0 && in_array($this->ocfilter->config('special_newest_period'), [ 'hour', 'day', 'week', 'month' ])) {
        $where['newest'] = "p.date_added > '" . $this->db->escape(date('Y-m-d H:i:s', strtotime('-' . (int)$this->ocfilter->config('special_newest_interval') . ' ' . $this->ocfilter->config('special_newest_period')))) . "'";

        unset($filter_params[$filter_key]);
      } else if ($special_filter == 'discount') {  
        $or = [];
      
        if ($this->ocfilter->config('special_discount_consider_special')) {
          $join[] = "LEFT JOIN " . DB_PREFIX . "product_special ps_yes ON (p.product_id = ps_yes.product_id)";
          
          $or[] = "(ps_yes.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps_yes.date_start = '0000-00-00' OR ps_yes.date_start < '" . $this->db->escape(date('Y-m-d')) . "') AND (ps_yes.date_end = '0000-00-00' OR ps_yes.date_end > '" . $this->db->escape(date('Y-m-d')) . "')))";          
        }  

        if ($this->ocfilter->config('special_discount_consider_discount')) {
          $join[] = "LEFT JOIN " . DB_PREFIX . "product_discount pd_yes ON (p.product_id = pd_yes.product_id)";
          
          $or[] = "(pd_yes.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((pd_yes.date_start = '0000-00-00' OR pd_yes.date_start < '" . $this->db->escape(date('Y-m-d')) . "') AND (pd_yes.date_end = '0000-00-00' OR pd_yes.date_end > '" . $this->db->escape(date('Y-m-d')) . "')) AND pd_yes.quantity > '0')";          
        }   
        
        if ($or) {
          $where[] = "(" . implode(" OR ", $or) . ")";
        }

        unset($filter_params[$filter_key]);
      } else if ($special_filter == 'weight' || $special_filter == 'width' || $special_filter == 'height' || $special_filter == 'length') {
        list($from, $to) = $this->ocfilter->params->parseRange(array_shift($values));
        
        if (isset($from) && isset($to)) {
          $where[] = "p.`" . $this->db->escape($special_filter) . "` BETWEEN '" . (float)$from . "' AND '" . (float)$to . "'";
        }
               
        unset($filter_params[$filter_key]);
      } else if (!$this->ocfilter->params->isKEY($filter_key) || !$values) {
        // Remove any other special filters
        unset($filter_params[$filter_key]);
      }
    } // End params foreach

    // Search by regular filters
    if ($filter_params) {
      $implode_where = [];
      $implode_join = [];
      
      $i = 1;

      foreach ($filter_params as $filter_key => $values) {         
        $this->ocfilter->params->key($filter_key)->expand($filter_id, $source);
      
        if ($this->ocfilter->params->isRange($values[0])) {
          list($min, $max) = $this->ocfilter->params->parseRange($values[0]);

          if (isset($min) && isset($max)) {
            $implode_join[] = "ocfilter_filter_range_to_product fr2p" . (int)$i . " ON (p.product_id = fr2p" . (int)$i . ".product_id)";
                        
            $implode_where[] = "fr2p" . (int)$i . ".filter_id = '" . (int)$filter_id . "' AND fr2p" . (int)$i . ".source = '" . (int)$source . "' AND (fr2p" . (int)$i . ".`min` BETWEEN '" . (float)$min . "' AND '" . (float)$max . "' OR fr2p" . (int)$i . ".`max` BETWEEN '" . (float)$min . "' AND '" . (float)$max . "')";
          } else {
            continue;
          }
        } else {
          $or = [];

          foreach ($values as $value_id) {
            $or[] = "fv2p" . (int)$i . ".value_id = '" . $this->db->escape($value_id) . "'";
          }               
          
          if ($or) {             
            $implode_join[] = "ocfilter_filter_value_to_product fv2p" . (int)$i . " ON (p.product_id = fv2p" . (int)$i . ".product_id)";  
          
            $implode_where[] = "fv2p" . (int)$i . ".filter_id = '" . (int)$filter_id . "' AND fv2p" . (int)$i . ".source = '" . (int)$source . "' AND (" . implode(" OR ", $or) . ")";
          }          
        }
        
        $i++;
      }

      if ($implode_where) {
        if ($implode_join) {
          $join[] = "LEFT JOIN " . DB_PREFIX . implode(" LEFT JOIN " . DB_PREFIX, $implode_join);
        }

        $where[] = implode(' AND ', $implode_where);
      }
    }

    return [
      'join'  => implode(" ", $join),
      'where' => implode(" AND ", $where)
    ];
  }

  private function getProductJoinSQL($data, $prefix = '') {
    if ($prefix) {
      $prefix .= '.';
    }

    $sql = [];

    if (isset($data['filter_category_id'])) {
      $sql[] = "LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON ({prefix}product_id = p2c.product_id)";

      if ($this->ocfilter->config('category_visibility') == 'parent') {
        $sql[] = "LEFT JOIN " . DB_PREFIX . "category_path cp ON (p2c.category_id = cp.category_id)";
      }
    }

    if (isset($data['filter_special']) && $data['filter_special']) {
      $sql[] = "LEFT JOIN " . DB_PREFIX . "product_special ps ON ({prefix}product_id = ps.product_id)";
    }

    if (isset($data['filter_search']) && trim($data['filter_search'])) {
      $sql[] = "LEFT JOIN " . DB_PREFIX . "product_description pd ON ({prefix}product_id = pd.product_id)";
    }

    if (!empty($data['filter_key'])) {
      $sql[] = "LEFT JOIN " . DB_PREFIX . "ocfilter_filter_value_to_product fv2p ON ({prefix}product_id = fv2p.product_id)";
    }
   
    if ($this->ocfilter->config('use_hpmodel') && $this->ocfilter->config('module_hpm_group_products')) {
      $sql[] = "LEFT JOIN " . DB_PREFIX . "hpmodel_product_hidden hph ON ({prefix}product_id = hph.pid) LEFT JOIN " . DB_PREFIX . "hpmodel_links hpl ON ({prefix}product_id = hpl.product_id) LEFT JOIN " . DB_PREFIX . "hpmodel_to_store h2s ON (hpl.type_id = h2s.type_id AND h2s.store_id = '" . (int)$this->config->get('config_store_id') . "')"; 
    }

    if (isset($data['filter_params'])) {
      $product_sql = $this->getProductSearchSQL($data['filter_params']);

      if ($product_sql['join']) {
        $sql[] = $product_sql['join'];
      }
    }

    return str_replace('{prefix}', $this->db->escape($prefix), implode(" ", $sql));
  }

  private function getProductWhereSQL($data, $prefix = '') {
    if ($prefix) {
      $prefix .= '.';
    }

    $sql = [];

    $sql[] = "{prefix}status = '1'";
    $sql[] = "{prefix}date_available <= '" . date('Y-m-d') . "'";

    if ($this->ocfilter->config('only_instock')) {
      $sql[] = "{prefix}quantity > '0'";
    }

    if (isset($data['filter_category_id'])) {
      if (is_array($data['filter_category_id'])) {
        if ($this->ocfilter->config('category_visibility') == 'parent') {  
          $sql[] = "cp.path_id IN('" . implode("','", array_map('intval', $data['filter_category_id'])) . "')";
        } else {
          $sql[] = "p2c.category_id IN('" . implode("','", array_map('intval', $data['filter_category_id'])) . "')";
        }
      } else {
        if ($this->ocfilter->config('category_visibility') == 'parent') {  
          $sql[] = "cp.path_id = '" . (int)$data['filter_category_id'] . "'";
        } else {
          $sql[] = "p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
        }
      }          
    }

    if (isset($data['filter_manufacturer_id'])) {
      $sql[] = "{prefix}manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
    }

    if (isset($data['filter_special']) && $data['filter_special']) {
      $sql[] = "ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))";
    }

    if (isset($data['filter_search']) && trim($data['filter_search'])) {
      $search = trim(preg_replace('/\s+/', ' ', utf8_strtolower(urldecode($data['filter_search']))));

      $sql['search'] = "(";
      $sql['search'] .= "LCASE(pd.name) LIKE '%" . $this->db->escape(str_replace(' ', '%', $data['filter_search'])) . "%'";

      if (isset($data['filter_search_description']) && $data['filter_search_description']) {
        $sql['search'] .= " OR pd.description LIKE '%" . $this->db->escape(str_replace(' ', '%', $data['filter_search'])) . "%'";
      }

      $sql['search'] .= " OR LCASE({prefix}model) LIKE '%" . $this->db->escape($data['filter_search']) . "%'";
      $sql['search'] .= " OR LCASE({prefix}sku) = '" . $this->db->escape($data['filter_search']) . "'";
      
      /* TODO: Move to global setting
      $product_sql .= " OR LCASE(p.upc) = '" . $this->db->escape($search) . "'";
      $product_sql .= " OR LCASE(p.ean) = '" . $this->db->escape($search) . "'";
      $product_sql .= " OR LCASE(p.jan) = '" . $this->db->escape($search) . "'";
      $product_sql .= " OR LCASE(p.isbn) = '" . $this->db->escape($search) . "'";
      $product_sql .= " OR LCASE(p.mpn) = '" . $this->db->escape($search) . "'";
      */

      $sql['search'] .= ")";
    }

    if (isset($data['filter_params'])) {
      $product_sql = $this->getProductSearchSQL($data['filter_params']);

      if ($product_sql['where']) {
        $sql[] = $product_sql['where'];
      }
    }
    
    if (!empty($data['filter_key'])) {
      $implode = [];

      if (!is_array($data['filter_key'])) {
        $data['filter_key'] = [ $data['filter_key'] ];
      }
    
      foreach ($data['filter_key'] as $filter_key) {
        $this->ocfilter->params->key($filter_key)->expand($filter_id, $source);
        
        $implode[] = "(fv2p.filter_id = '" . (int)$filter_id . "' AND fv2p.source = '" . (int)$source . "')";
      }

      if ($implode) {
        $sql[] = "(" . implode(" OR ", $implode) . ")";
      }
    }

    return str_replace('{prefix}', $this->db->escape($prefix), implode(" AND ", $sql));
  }
  
  public function getProductCountSQL() {
    if ($this->ocfilter->config('use_hpmodel') && $this->ocfilter->config('module_hpm_group_counter')) {
      return "COUNT(DISTINCT IF(hpl.parent_id IS NOT NULL AND h2s.store_id IS NOT NULL AND hph.pid IS NOT NULL, hpl.parent_id, p.product_id))";
    } else {
      return "COUNT(DISTINCT p.product_id)";
    }
  }

  public function getProductValues($product_id) {
    $query = $this->ocfilter->query("SELECT *, CONCAT(filter_id, '.', source) AS filter_key FROM " . DB_PREFIX . "ocfilter_filter_value_to_product WHERE product_id = '" . (int)$product_id . "'");

    return $query->rows;
  }
  
  public function getProductRangeValues($product_id) {
    $query = $this->ocfilter->query("SELECT *, CONCAT(filter_id, '.', source) AS filter_key FROM " . DB_PREFIX . "ocfilter_filter_range_to_product WHERE product_id = '" . (int)$product_id . "'");

    return $query->rows;
  }  

  /* SEO Pages */
  
  public function getFilterDescriptions($filter_key) { 
    $this->ocfilter->params->key($filter_key)->expand($filter_id, $source); 
    
    $filter_description_data = [];

    $query = $this->ocfilter->query("SELECT * FROM " . DB_PREFIX . "ocfilter_filter_description WHERE filter_id = '" . (int)$filter_id . "' AND source = '" . (int)$source . "'");

    foreach ($query->rows as $result) {
      $filter_description_data[$result['language_id']] = [
        'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
        'description' => strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
        'suffix'      => strip_tags(html_entity_decode($result['suffix'], ENT_QUOTES, 'UTF-8'))
      ];
    }

    return $filter_description_data;
  }      
  
  public function getManufacturerDescriptions($manufacturer_id) {
    if ($this->ocfilter->config('use_manufacturer_description')) {
      $query = $this->ocfilter->query("SELECT language_id, name FROM " . DB_PREFIX . "manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");      
    } else {     
      $query = $this->ocfilter->query("SELECT l.language_id, m.name FROM " . DB_PREFIX . "manufacturer m, " . DB_PREFIX . "language l WHERE m.manufacturer_id = '" . (int)$manufacturer_id . "'");
    }
    
    return array_combine(array_column($query->rows, 'language_id'), array_column($query->rows, 'name'));
  }

  // Stock status
  public function getStockStatusDescriptions($stock_status_id) {
    $query = $this->ocfilter->query("SELECT * FROM " . DB_PREFIX . "stock_status WHERE stock_status_id = '" . (int)$stock_status_id . "'");

    return array_combine(array_column($query->rows, 'language_id'), array_column($query->rows, 'name'));
  }   
  
  public function getFilterValueDescriptions($filter_key, $value_id) {
    $this->ocfilter->params->key($filter_key)->expand($filter_id, $source); 
    
    $query = $this->ocfilter->query("SELECT language_id, name FROM " . DB_PREFIX . "ocfilter_filter_value_description WHERE value_id = '" . $this->db->escape((string)$value_id) . "' AND source = '" . (int)$source . "'");

    return array_combine(array_column($query->rows, 'language_id'), array_column($query->rows, 'name'));
  }
  
  // Clone dynamic page to static
  public function clonePage($page_id, $params) {     
    $query = $this->ocfilter->query("SELECT * FROM " . DB_PREFIX . "ocfilter_page WHERE page_id = '" . (int)$page_id . "'"); 
    
    if (!$query->num_rows) {
      return false;
    }              
       
    $params = $this->ocfilter->params->normalizeArray($params);
       
    $page_data = [
      'dynamic' => 0,
      'dynamic_id' => $page_id,
      'params' => json_encode($params),
      'params_key' => crc32($this->ocfilter->params->encode($params)),
      'params_count' => count($params),
      'description' => [],
      'keyword' => null,
      'category_id' => $query->row['category_id'],
      'status' => 1, 
      'module' => $query->row['module'],
      'category' => $query->row['category'],
      'product' => $query->row['product'],
      'sitemap' => $query->row['sitemap'],
    ];      
    
    // Set value descriptions
    $values_name = [];
    
    $set_value_name = function($filter_key, $value_id, $language_id, $name, $filter_description = null) use (&$values_name) { 
      $_name = strip_tags(html_entity_decode($name, ENT_QUOTES, 'UTF-8'));
      
      if ($filter_description && isset($filter_description[$language_id]['suffix'])) {
        $_name .= strip_tags(html_entity_decode($filter_description[$language_id]['suffix'], ENT_QUOTES, 'UTF-8'));
      }
    
      if (!isset($values_name[$filter_key])) {
        $values_name[$filter_key] = [];
      }  
      
      if (!isset($values_name[$filter_key][$value_id])) {
        $values_name[$filter_key][$value_id] = [];
      }          
    
      $values_name[$filter_key][$value_id][$language_id] = $_name;          
    };
    
    // Set special filters value names from lang files
    $language_query = $this->ocfilter->query("SELECT * FROM " . DB_PREFIX . "language");
    
    foreach ($language_query->rows as $language) {
      $_ = [];
      
      $file = DIR_LANGUAGE . $language['code'] . '/extension/module/ocfilter.php';

      if (!is_file($file)) {
        continue;
      }
      
      include($file);
      
      // Stock status
      if (isset($params[$this->ocfilter->params->special('stock')->key()])) {
        if (isset($_['text_in_stock']) && (in_array(0, $params[$this->ocfilter->params->special('stock')->key()]) || in_array(2, $params[$this->ocfilter->params->special('stock')->key()]))) {
          $set_value_name($this->ocfilter->params->special('stock')->key(), 2, $language['language_id'], $_['text_in_stock']);
        }
        
        if (isset($_['text_out_of_stock']) && (in_array(0, $params[$this->ocfilter->params->special('stock')->key()]) || in_array(1, $params[$this->ocfilter->params->special('stock')->key()]))) {
          $set_value_name($this->ocfilter->params->special('stock')->key(), 1, $language['language_id'], $_['text_out_of_stock']);
        }                
      }      
      
      // Discount
      if (isset($_['text_discount_only']) && isset($params[$this->ocfilter->params->special('discount')->key()])) {
        $set_value_name($this->ocfilter->params->special('discount')->key(), 1, $language['language_id'], $_['text_discount_only']);
      }
      
      // Newest
      if (isset($_['text_newest_only']) && isset($params[$this->ocfilter->params->special('newest')->key()])) {
        $set_value_name($this->ocfilter->params->special('newest')->key(), 1, $language['language_id'], $_['text_newest_only']);
      }     
      
      // Price
      if (isset($_['text_slider_selected_range']) && isset($params[$this->ocfilter->params->special('price')->key()])) {
        list($min, $max) = $this->ocfilter->params->parseRange($params[$this->ocfilter->params->special('price')->key()][0]);
        
        $name = sprintf($_['text_slider_selected_range'], 
          '{cb}', 
          '{c:' . $min . '|' . $this->session->data['currency'] . '}', 
          '{c:' . $max . '|' . $this->session->data['currency'] . '}',  
          '{ca}'
        );
        
        $set_value_name($this->ocfilter->params->special('price')->key(), 0, $language['language_id'], $name);
      }     
    } // foreach languages

    // Another values descriptions
    foreach ($params as $filter_key => $values) {           
      $filter_description = null;
      
      if (!$this->ocfilter->params->key($filter_key)->is('special')) {        
        $filter_description = $this->getFilterDescriptions($filter_key);
        
        if (!$filter_description) {
          continue;
        }
      }
      
      if (!$this->ocfilter->params->key($filter_key)->is('special') && $this->ocfilter->params->isRange($values[0])) {
        list($min, $max) = $this->ocfilter->params->parseRange($values[0]);         
        
        foreach ($filter_description as $language_id => $description) {
          if ($min != $max) {
            $name = sprintf($this->language->get('text_slider_selected_range'), $description['prefix'], $min, $max, $description['suffix']);
          } else {
            $name = sprintf($this->language->get('text_slider_selected_single'), $description['prefix'], $min, $description['suffix']);
          }      
        
          $set_value_name($filter_key, 0, $language_id, $name);          
        }
      } else {
        foreach ($values as $value_id) {        
          if ($this->ocfilter->params->key($filter_key)->is('manufacturer')) {           
            $value_description = $this->getManufacturerDescriptions($value_id); 

            foreach ($value_description as $language_id => $name) {                  
              $set_value_name($filter_key, $value_id, $language_id, $name);
            }                
          } else if ($this->ocfilter->params->key($filter_key)->is('stock') && $this->ocfilter->config('stock_status_method') == 'stock_status_id') {
            $value_description = $this->getStockStatusDescriptions($value_id); 
            
            foreach ($value_description as $language_id => $name) {                  
              $set_value_name($filter_key, $value_id, $language_id, $name);
            }                                         
          } else if (!$this->ocfilter->params->key($filter_key)->is('special')) {  
            $value_description = $this->getFilterValueDescriptions($filter_key, $value_id); 
          
            foreach ($value_description as $language_id => $name) {                  
              $set_value_name($filter_key, $value_id, $language_id, $name, $filter_description);
            }                         
          }
        } // foreach $values        
      } // if not a slider         
    } // foreach $params  
       
    $set_page_description = function($mask, $name, $description) {
      return [
        'name'                => str_replace($mask, $name, $description['name']),
        'heading_title'       => str_replace($mask, $name, $description['heading_title']),
        'meta_title'          => str_replace($mask, $name, $description['meta_title']),
        'description_top'     => str_replace($mask, $name, $description['description_top']),
        'description_bottom'  => str_replace($mask, $name, $description['description_bottom']),
        'meta_description'    => str_replace($mask, $name, $description['meta_description']),
        'meta_keyword'        => str_replace($mask, $name, $description['meta_keyword']),
      ];        
    };    
          
    $description_query = $this->ocfilter->query("SELECT * FROM " . DB_PREFIX . "ocfilter_page_description WHERE page_id = '" . (int)$page_id . "'");          
          
    $page_data['description'] = array_combine(array_column($description_query->rows, 'language_id'), $description_query->rows);      

    $store_query = $this->ocfilter->query("SELECT * FROM " . DB_PREFIX . "ocfilter_page_to_store WHERE page_id = '" . (int)$page_id . "'"); 

    if ($this->ocfilter->opencart->version >= 30) {
      $page_data['keyword'] = [ [] ];      
    }

    foreach ($params as $filter_key => $values) {
      foreach ($page_data['description'] as $language_id => $description) {
        $value_name = '';
        
        foreach ($values as $value_id) {
          if ($this->ocfilter->params->isRange($value_id)) {
            $value_id = 0; 
          }  

          if (isset($values_name[$filter_key][$value_id][$language_id])) {
            $name = $values_name[$filter_key][$value_id][$language_id];
            
            $value_name .= $value_name ? ', ' . $name : $name;
          }            
        }
                          
        // Default               
        $description = $set_page_description('{F' . $filter_key . '}', $value_name, $description);

        // Lowercase
        $description = $set_page_description('{F' . $filter_key . '|L}', utf8_strtolower($value_name), $description);
 
        $page_data['description'][$language_id] = $description;
        
        // Keyword
        $_value_name = str_replace([ 
          '{cb}', 
          '{ca}', 
          '{c:',
          '|' . $this->session->data['currency'] . '}'
        ], '', $value_name);  
          
        if ($this->ocfilter->opencart->version >= 30) {         
          foreach ($store_query->rows as $store) {
            $keyword_query = $this->ocfilter->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE language_id = '" . (int)$language_id . "' AND store_id = '" . (int)$store['store_id'] . "' AND `query` = 'ocfilter_page_id=" . (int)$page_id . "'");
          
            if ($keyword_query->num_rows && utf8_strlen($keyword_query->row['keyword']) > 0) {
              $keyword = $keyword_query->row['keyword'];
              
              $keyword = str_replace('{F' . $filter_key . '}', $this->ocfilter->helper->translit($_value_name), $keyword);                 
              $keyword = str_replace('{F' . $filter_key . '|L}', $this->ocfilter->helper->translit($_value_name), $keyword); 
              
              $page_data['keyword'][$store['store_id']][$language_id] = $keyword;
            }            
          }
        } else if ($language_id == $this->config->get('config_language_id')) {
          $keyword_query = $this->ocfilter->query("SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE `query` = 'ocfilter_page_id=" . (int)$page_id . "'");
        
          if ($keyword_query->num_rows && utf8_strlen($keyword_query->row['keyword']) > 0) {
            $keyword = $keyword_query->row['keyword'];
            
            $keyword = str_replace('{F' . $filter_key . '}', $this->ocfilter->helper->translit($_value_name), $keyword);                 
            $keyword = str_replace('{F' . $filter_key . '|L}', $this->ocfilter->helper->translit($_value_name), $keyword); 
            
            $page_data['keyword'] = $keyword;
          } 
        }
      } // foreach page descriptions
    } // foreach $params
  
    $this->ocfilter->query("INSERT INTO " . DB_PREFIX . "ocfilter_page SET category_id = '" . (int)$page_data['category_id'] . "', dynamic_id = '" . (int)$page_data['dynamic_id'] . "', dynamic = '" . (int)$page_data['dynamic'] . "', status = '" . (int)$page_data['status'] . "', category = '" . (int)$page_data['category'] . "', module = '" . (int)$page_data['module'] . "', product = '" . (int)$page_data['product'] . "', sitemap = '" . (int)$page_data['sitemap'] . "', params = '" . $this->db->escape($page_data['params']) . "', params_key = '" . $this->db->escape((string)$page_data['params_key']) . "', params_count = '" . $this->db->escape($page_data['params_count']) . "'");

    $new_page_id = $this->db->getLastId();

    foreach ($page_data['description'] as $language_id => $value) {
      $this->ocfilter->query("INSERT INTO " . DB_PREFIX . "ocfilter_page_description SET page_id = '" . (int)$new_page_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', heading_title = '" . $this->db->escape($value['heading_title']) . "', description_top = '" . $this->db->escape($value['description_top']) . "', description_bottom = '" . $this->db->escape($value['description_bottom']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
    }
    
    $this->ocfilter->query("INSERT INTO " . DB_PREFIX . "ocfilter_page_to_store (page_id, store_id) SELECT '" . (int)$new_page_id . "', store_id FROM " . DB_PREFIX . "ocfilter_page_to_store WHERE page_id = '" . (int)$page_id . "'");

    $this->ocfilter->query("INSERT INTO " . DB_PREFIX . "ocfilter_page_to_layout (page_id, store_id, layout_id) SELECT '" . (int)$new_page_id . "', store_id, layout_id FROM " . DB_PREFIX . "ocfilter_page_to_layout WHERE page_id = '" . (int)$page_id . "'");
         
    // Add SEO URL keyword
    if ($this->ocfilter->opencart->version >= 30) {
      foreach ($page_data['keyword'] as $store_id => $languages) {
        foreach ($languages as $language_id => $keyword) {        
          $this->ocfilter->query("INSERT INTO " . DB_PREFIX . "seo_url SET language_id = '" . (int)$language_id . "', store_id = '" . (int)$store_id . "', `query` = 'ocfilter_page_id=" . (int)$new_page_id . "', keyword = '" . $this->db->escape($keyword) . "'");
        }
      }
    } else if ($page_data['keyword'] && is_string($page_data['keyword'])) {
      $this->ocfilter->query("INSERT INTO " . DB_PREFIX . "url_alias SET `query` = 'ocfilter_page_id=" . (int)$new_page_id . "', keyword = '" . $this->db->escape($page_data['keyword']) . "'");
    }
    
    // Seo pro cache
    if ($this->config->get('config_seo_url_type') == 'seo_pro' || $this->config->get('config_seo_pro') || ($this->ocfilter->opencart->version < 30 && (bool)$this->cache->get('seo_pro'))) {
      $url_query = 'ocfilter_page_id=' . (int)$new_page_id;
      
      if ($this->ocfilter->opencart->version >= 30 && ($this->config->get('config_seo_url_cache') || (bool)$this->cache->get('seopro.keywords'))) {
        $seo_pro_keywords = $this->cache->get('seopro.keywords');
        $seo_pro_queries = $this->cache->get('seopro.queries');

        if ($seo_pro_keywords && is_array($seo_pro_keywords) && $seo_pro_queries && is_array($seo_pro_queries)) {
          $query = $this->ocfilter->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = 'ocfilter_page_id=" . (int)$new_page_id . "'");
          
          foreach ($query->rows as $result) {
            $seo_pro_keywords[$url_query][$result['store_id']][$result['language_id']] = $result['keyword'];
            $seo_pro_queries[$result['keyword']][$result['store_id']][$result['language_id']] = $url_query;
          }

          $this->cache->set('seopro.keywords', $seo_pro_keywords);
          $this->cache->set('seopro.queries', $seo_pro_queries);
        } 
      } else if ($this->ocfilter->opencart->version < 30 && is_string($page_data['keyword']) && $page_data['keyword'] && (bool)$this->cache->get('seo_pro')) {
        $seo_pro_data = $this->cache->get('seo_pro');
        
        if (isset($seo_pro_data['keywords']) && is_array($seo_pro_data['keywords']) && isset($seo_pro_data['queries']) && is_array($seo_pro_data['queries'])) {
          $seo_pro_data['keywords'][$page_data['keyword']] = $url_query;
          $seo_pro_data['queries'][$url_query] = $page_data['keyword'];

          $this->cache->set('seo_pro', $seo_pro_data);                
        } 
      }
    }
        
    return $new_page_id;
  }
  
  public function getPageByParams($category_id, $filter_params = []) {
    $page_data = [];

    $params_count = count($filter_params);  
    
    // Search static
    $filter_params = $this->ocfilter->params->normalizeArray($filter_params);
    
    $params_key = crc32($this->ocfilter->params->encode($filter_params));
    
    $query = $this->ocfilter->query("SELECT *, " . $this->getKeywordSQL('p.') . " AS keyword, (SELECT GROUP_CONCAT(DISTINCT cp.path_id ORDER BY cp.`level` SEPARATOR '_') AS path FROM " . DB_PREFIX . "category_path cp WHERE cp.category_id = p.category_id) AS path FROM " . DB_PREFIX . "ocfilter_page p LEFT JOIN " . DB_PREFIX . "ocfilter_page_description pd ON (p.page_id = pd.page_id) WHERE p.status = '1' AND p.dynamic = '0' AND p.category_id = '" . (int)$category_id . "' AND p.params_count = '" . (int)$params_count . "' AND pd.language_id = '" . $this->config->get('config_language_id') . "' AND `params_key` = '" . $this->db->escape((string)$params_key) . "'"); 
    
    $page_data = $query->row;
    
    // Search dynamic
    if (!$page_data) {
      $is_multivalue = (bool)array_filter($filter_params, function($v) {
        return (is_array($v) && count($v) > 1);
      });
      
      if ($is_multivalue) {
        return $page_data;
      }
      
      $query = $this->ocfilter->query("SELECT page_id, params, dynamic FROM " . DB_PREFIX . "ocfilter_page WHERE status = '1' AND dynamic = '1' AND category_id = '" . (int)$category_id . "' AND params_count = '" . (int)$params_count . "'"); 
        
      foreach ($query->rows as $result) {
        $page_params = json_decode($result['params'], true);
               
        // Compare filter keys and ignore empty values param
        if (array_diff_key($page_params, $filter_params) || in_array([], $page_params)) {
          continue;
        }
               
        // One param with `any` value?
        if ($params_count == 1) {
          $first = reset($page_params);
          
          if ($first[0] < 1) {
            if (count(reset($filter_params)) > 1) {   
              continue;
            } else {
              $page_data = $result;
            }
            
            break;
          }
        }
        
        // Search further
        $finded = 0;
               
        foreach ($page_params as $filter_key => $values) {
          if ($this->ocfilter->params->isRange($values[0]) && $this->ocfilter->params->isRange($filter_params[$filter_key][0])) {
            list($page_min, $page_max) = $this->ocfilter->params->parseRange($values[0]);
            list($filter_min, $filter_max) = $this->ocfilter->params->parseRange($filter_params[$filter_key][0]);

            if ($page_min == $page_max && ($filter_min + $filter_max) == ($page_min + $page_max)) {
              $finded++;
            } else if ($page_min != $page_max && $page_min <= $filter_min && $page_max >= $filter_max) {
              $finded++;
            }         
          } else if ($values[0] < 1) { // All values
            if (count($filter_params[$filter_key]) < 2) {
              $finded++; 
            } else {
              continue;
            }
          } else if (count(array_intersect($values, $filter_params[$filter_key])) == count($filter_params[$filter_key])) {
            $finded++;
          }
        }

        if ($finded == $params_count) {
          $page_data = $result;
          
          break;
        }
      }
    } 
    
    if ($page_data && $page_data['dynamic']) {     
      $page_data = $this->getPage($this->clonePage($page_data['page_id'], $filter_params));
    }

    return $page_data;
  }

  public function getPage($page_id, $category_id = null) {
    $query = $this->ocfilter->query("SELECT *, (SELECT GROUP_CONCAT(DISTINCT cp.path_id ORDER BY cp.`level` SEPARATOR '_') AS path FROM " . DB_PREFIX . "category_path cp WHERE cp.category_id = p.category_id) AS path, " . $this->getKeywordSQL('p.') . " AS keyword FROM " . DB_PREFIX . "ocfilter_page p LEFT JOIN " . DB_PREFIX . "ocfilter_page_description pd ON (p.page_id = pd.page_id) WHERE p.page_id = '" . (int)$page_id . "' AND p.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

    // Search by destination category and same keyword
    if (!is_null($category_id) && $query->num_rows && $query->row['category_id'] != $category_id) {
      $sql = "SELECT p.page_id FROM " . DB_PREFIX . "ocfilter_page p";

      if ($this->ocfilter->opencart->version >= 30) {
        $sql .= " LEFT JOIN " . DB_PREFIX . "seo_url su ON (CONCAT('ocfilter_page_id=', p.page_id) = su.`query`)";
      } else {
        $sql .= " LEFT JOIN " . DB_PREFIX . "url_alias ua ON (CONCAT('ocfilter_page_id=', p.page_id) = ua.`query`)";
      }

      $sql .= " WHERE p.category_id = '" . (int)$category_id . "'";
      
      if ($this->ocfilter->opencart->version >= 30) {
        $sql .= " AND su.language_id = '" . (int)$this->config->get('config_language_id') . "' AND su.store_id = '" . (int)$this->config->get('config_store_id') . "' AND su.keyword = '" . $this->db->escape($query->row['keyword']) . "'";
      } else {
        $sql .= " AND ua.keyword = '" . $this->db->escape($query->row['keyword']) . "'";
      }      
              
      $query = $this->ocfilter->query($sql); 
       
      if ($query->num_rows) {
        return $this->getPage($query->row['page_id'], $category_id);
      }
    }

    return $query->row;
  }

  public function getPageByManufacturer($manufacturer_id, $category_id = null) {
    if ($this->ocfilter->opencart->version >= 30) {     
      $query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND `query` = 'manufacturer_id=" . (int)$manufacturer_id . "'");
    } else {
      $query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE `query` = 'manufacturer_id=" . (int)$manufacturer_id . "'");
    }
    
    if ($query->num_rows && $query->row['keyword']) {
      if ($this->ocfilter->opencart->version >= 30) {
        $query = $this->db->query("SELECT `query` FROM " . DB_PREFIX . "seo_url WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND keyword = '" . $this->db->escape($query->row['keyword']) . "' AND `query` LIKE 'ocfilter_page_id=%'");
      } else {
        $query = $this->db->query("SELECT `query` FROM " . DB_PREFIX . "url_alias WHERE keyword = '" . $this->db->escape($query->row['keyword']) . "' AND `query` LIKE 'ocfilter_page_id=%'");
      }
      
      if ($query->num_rows) {
        return $this->getPage(str_replace('ocfilter_page_id=', '', $query->row['query']), $category_id);
      }
    }
    
    return [];
  }

  public function getPages($data = []) {
    $page_data = [];
    
    $sql = "SELECT p.page_id, p.params, p.params_count, p.category_id, pd.name, pd.heading_title, (SELECT GROUP_CONCAT(DISTINCT cp.path_id ORDER BY cp.`level` SEPARATOR '_') AS path FROM " . DB_PREFIX . "category_path cp WHERE cp.category_id = p.category_id) AS path, " . $this->getKeywordSQL('p.') . " AS keyword FROM " . DB_PREFIX . "ocfilter_page p LEFT JOIN " . DB_PREFIX . "ocfilter_page_description pd ON (p.page_id = pd.page_id)";

    if (!empty($data['filter_product_id'])) {
      $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.category_id = p2c.category_id)";   
    }

    $sql .= " WHERE p.status = '1' AND p.dynamic = '0' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

    if (isset($data['filter_category_id'])) {
      $sql .= " AND p.category_id = '" . (int)$data['filter_category_id'] . "'";
    }
    
    if (!empty($data['filter_product_id'])) {
      $sql .= " AND p2c.product_id = '" . (int)$data['filter_product_id'] . "'";
      
      if ($this->ocfilter->config('use_main_category')) {
        //$sql .= " AND p2c.main_category = '1'";
      }
    }
    
    if (isset($data['filter_category'])) {
      $sql .= " AND p.category = '" . (int)$data['filter_category'] . "'";
    }

    if (isset($data['filter_module'])) {
      $sql .= " AND p.module = '" . (int)$data['filter_module'] . "'";
    }

    if (isset($data['filter_product'])) {
      $sql .= " AND p.product = '" . (int)$data['filter_product'] . "'";
    }   

    if (isset($data['filter_sitemap'])) {
      $sql .= " AND p.sitemap = '" . (int)$data['filter_sitemap'] . "'";
    }        
    
    $sql .= " ORDER BY p.page_id DESC";

    $query = $this->ocfilter->query($sql);
    
    return $query->rows;
  }

  public function getPageLayoutId($page_id) {
    $query = $this->ocfilter->query("SELECT layout_id FROM " . DB_PREFIX . "ocfilter_page_to_layout WHERE page_id = '" . (int)$page_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");    
    
    return ($query->num_rows ? $query->row['layout_id'] : 0);
  }

  protected function getKeywordSQL($prefix = 'p.') {   
    if ($this->ocfilter->opencart->version >= 30) {
      return "(SELECT su.keyword FROM " . DB_PREFIX . "seo_url su WHERE su.language_id = '" . (int)$this->config->get('config_language_id') . "' AND su.store_id = '" . (int)$this->config->get('config_store_id') . "' AND su.`query` = CONCAT('ocfilter_page_id=', " . $prefix . "page_id) LIMIT 1)";
    } else {
      return "(SELECT ua.keyword FROM " . DB_PREFIX . "url_alias ua WHERE ua.`query` = CONCAT('ocfilter_page_id=', " . $prefix . "page_id) LIMIT 1)";
    }
  }

  /* Specials */
  public function getTotalProductSpecials($params = []) {
    $sql = "SELECT COUNT(DISTINCT ps.product_id) AS total FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id)";

    if ($params) {
      $ocfilter_product_sql = $this->getProductSearchSQL($params);
    } else {
      $ocfilter_product_sql = false;
    }

    if ($ocfilter_product_sql && $ocfilter_product_sql['join']) {
      $sql .= " " . $ocfilter_product_sql['join'];
    }

    $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))";

    if ($ocfilter_product_sql && $ocfilter_product_sql['where']) {
      $sql .= " AND " . $ocfilter_product_sql['where'];
    }

    $query = $this->ocfilter->query($sql);

    return $query->num_rows ? $query->row['total'] : 0;
  }
}