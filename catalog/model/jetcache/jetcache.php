<?php
/* All rights reserved belong to the module, the module developers https://support.opencartadmin.com */
// https://support.opencartadmin.com © 2011-2022 All Rights Reserved
// Distribution, without the author's consent is prohibited
// Commercial license
if (!class_exists('ModelJetcacheJetcache', false)) {
class ModelJetcacheJetcache extends Model
{
	public function getSettings($table, $key) {
		$sql = "SELECT value_db FROM " . DB_PREFIX . $table. " WHERE `key_db` = '" . $this->db->escape($key) . "' AND `time_expire_db` > ".time()."";
		$query = $this->db->query($sql);
		if (isset($query->row['value_db']))	return $query->row['value_db'];
		else
		return false;
	}

	public function setSettings($table, $key, $value, $time) {
		$sql = "INSERT INTO " . DB_PREFIX . $table . " (key_db, value_db, time_expire_db) VALUES('".$this->db->escape($key)."','".$this->db->escape($value)."','".$this->db->escape($time)."')";
		$this->db->query($sql);
	}

	public function deleteSettings($table, $key) {
		$sql = "DELETE FROM " . DB_PREFIX . $table. " WHERE `key_db` = '" . $this->db->escape($key) . "'";
		$query = $this->db->query($sql);
	}

	public function edit_product_id($product_id, $filecache, $fileexpires) {
	    $sql = "
	    INSERT INTO `" . DB_PREFIX . "jetcache_product_cache`
	    SET
	    product_id='" . (int)$product_id . "',
	    filecache = '" . $filecache . "',
	    expires='" . date('Y-m-d H:i:s', time() + $fileexpires) . "' ON DUPLICATE KEY UPDATE `expires` = '" . date('Y-m-d H:i:s', time() + $fileexpires) . "'";

		$query = $this->db->query($sql);
	}
	public function editSetting($group, $data, $store_id = 0) {
		$code = $group;

        if (SC_VERSION > 15) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
			foreach ($data as $key => $value) {
				if (substr($key, 0, strlen($code)) == $code) {
					if (!is_array($value)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
					} else {
						$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");
					}
				}
			}
		} else {
			$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");
			foreach ($data as $key => $value) {
				if (!is_array($value)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
				}
			}

		}
	}

}
}
