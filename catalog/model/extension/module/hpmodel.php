<?php
class ModelExtensionModuleHpmodel extends Model {

    public function getTypes() {
        $query = $this->db->query("SELECT ht.* FROM `" . DB_PREFIX . "hpmodel_type` ht LEFT JOIN " . DB_PREFIX . "hpmodel_to_store h2s ON (ht.id = h2s.type_id) WHERE ht.status = 1 AND h2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
        
        $type_data = array();
        
        foreach ($query->rows as $type) {
            $type_data[$type['id']] = array (
                'id'          => $type['id'],
                'name'        => $type['name'],
                'type'        => $type['type'],
                'description' => $type['description'],
                'status'      => $type['status'],
                'setting'     => json_decode($type['setting'], true),
            );
        }
        
        return $type_data;        
    }

    public function getChilds($parent_id, $limit = 0, $type_id = 0) {
        $query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "hpmodel_links` WHERE `parent_id` = '" . (int)$parent_id . "' " . ($type_id > 0 ? "AND `type_id` = '" . (int)$type_id . "'" : 0) . " ORDER BY `sort`" . ($limit > 0 ? " LIMIT " . (int)$limit : ''));
        return $query->rows;

    }

    public function getParent($child_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hpmodel_links` WHERE `product_id` = '" . (int)$child_id . "' OR `parent_id` = '" . (int)$child_id . "'");
        return $query->row;
    }

    public function getChild($child_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hpmodel_links` WHERE `product_id` = '" . (int)$child_id . "'");
        return $query->row;
    }

    public function getProducts($product_ids, $limit = 0) {
        if (!$product_ids) {
            return array();
        }
        
        $filtered_product_ids = array();
        foreach ($product_ids as $product_id) {
            $filtered_product_ids[(int)$product_id] = (int)$product_id;
        }        

        $query = $this->db->query("SELECT p.*, pd.*, m.name as manufacturer, p.manufacturer_id as manufacturer_id, p.image AS product_image, IF(hl.image = '', p.image, hl.image) AS image, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status,  (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "hpmodel_links hl ON (p.product_id = hl.product_id) WHERE p.product_id IN (" . implode(',',$filtered_product_ids) . ") AND p.status = 1 AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY hl.sort, p.price, IF(hl.parent_id = hl.product_id, 0, 1) " . ($limit > 0 ? " LIMIT " . (int)$limit : ''));

        $product_data = array();
        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $result;
        }

        return $product_data;
    }
    
    public function getProductAttributes($product_id, $attributes) {
        if (!$attributes) return array();

        $filtered_attribute_id = array();
        foreach ($attributes as $attribute_id) {
            $filtered_attribute_id[(int)$attribute_id] = (int)$attribute_id;
        }
        
        $query = $this->db->query("SELECT attribute_id, text FROM " . DB_PREFIX . "product_attribute pa WHERE product_id = '" . (int)$product_id . "' AND attribute_id IN (" . implode(',', $filtered_attribute_id) . ") AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

        $attribute_data = array();
        
        foreach ($query->rows as $result) {
            $attribute_data[$result['attribute_id']] = $result['text'];
        }

        return $attribute_data;
    }
    
    public function getProductFilters($product_id, $filters) {
        if (!$filters) return array();

        $filtered_filter_id = array();
        foreach ($filters as $filter_id) {
            $filtered_filter_id[(int)$filter_id] = (int)$filter_id;
        }
        
        $query = $this->db->query("SELECT DISTINCT f.filter_group_id AS filter_id, fd.name AS text FROM `" . DB_PREFIX . "product_filter` pf LEFT JOIN `" . DB_PREFIX . "filter` f ON (pf.filter_id = f.filter_id) LEFT JOIN `" . DB_PREFIX . "filter_description` fd ON (f.filter_id = fd.filter_id) WHERE pf.product_id = '" . (int)$product_id . "' AND f.filter_group_id IN (" . implode(',', $filtered_filter_id) . ") AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        $attribute_data = array();
        
        foreach ($query->rows as $result) {
            $attribute_data[$result['filter_id']] = $result['text'];
        }

        return $attribute_data;
    }
}
