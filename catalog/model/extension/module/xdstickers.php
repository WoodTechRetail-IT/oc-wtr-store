<?php
class ModelExtensionModuleXDStickers extends Model {
	public function getCustomXDSticker($xdsticker_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "xdstickers WHERE xdsticker_id = '" . (int)$xdsticker_id . "'");
		return $query->row;
	}

	public function getCustomXDStickersProduct($product_id) {
		$query = $this->db->query("SELECT `xdsticker_id` FROM " . DB_PREFIX . "xdstickers_product WHERE product_id = '" . (int)$product_id . "'");
		if ($query) {
			return $query->rows;
		} else {
			return false;
		}
	}

	public function getCustomXDStickers($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "xdstickers";
		$sort_data = array(
			'name',
			'status'
		);
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY xdsticker_id";
		}
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}
			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		if ($query) {
			return $query->rows;
		} else {
			return false;
		}
	}
}