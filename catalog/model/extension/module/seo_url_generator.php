<?php

/**
 * @category   OpenCart
 * @package    SEO URL Generator PRO
 * @copyright  © Serge Tkach, 2018-2021, http://sergetkach.com/
 */
class ModelExtensionModuleSeoUrlGenerator extends Model {
	private $stdelog;

	function __construct($registry) {
		parent::__construct($registry);

		if ($registry->get('stdelog')) {
			$this->stdelog = $registry->get('stdelog');
		} else {
			// Для методов модели, которые вызываны из контроллера сущностей, а не модуля
			$this->stdelog = new StdeLog('seo_url_generator');
			$this->stdelog->setDebug($this->config->get('module_seo_url_generator_debug'));
		}
	}
	
	

	/*
	 * Фильтрация названия сущности
	 * Данная функция вызывается перед транслитом
	 * То есть, тут можно сокращать название товаров до заданного кол-ва слов
	 */

	public function essenceNameFilter($name, $essence, $setting) {
		// Put your code here
		if ('product' == $essence) {
			if (mb_strlen($name) > 100) {
				// Укоротить название товара, если в его названии содержится больше 100 символов
			}
		}

		return $name;
	}




	/*
	------------------------------------------------------------------------------
	GENERATING
	------------------------------------------------------------------------------
	*/

	public function generateProductKeyword($a_data, $setting) {
		$this->stdelog->write(3, 'generateProductKeyword() is called');
		$this->stdelog->write(4, $a_data, 'generateProductKeyword() : $a_data');
		$this->stdelog->write(4, $setting, 'generateProductKeyword() : $setting');

		$keyword = '';

		$search = array(
			'[product_name]',
			'[product_id]',
			'[model]',
			'[sku]',
			'[manufacturer_name]'
		);

		$replace = array();

		$replace[] = isset($a_data['name']) ? trim($a_data['name']) : '';
		$replace[] = isset($a_data['essence_id']) ? trim($a_data['essence_id']) : '';
		$replace[] = isset($a_data['model']) ? trim($a_data['model']) : '';
		$replace[] = isset($a_data['sku']) ? trim($a_data['sku']) : '';

		if (false === strstr($setting['formula'], '[manufacturer_name]')) {
			$this->stdelog->write(4, 'generateProductKeyword() : formula not contain var [manufacturer_name]');

			$replace[] = '';
		} else {
			$this->stdelog->write(4, $setting, 'generateProductKeyword() : formula contain var [manufacturer_name]');

			// (A!) ocStore 3 has only 1 name as there in Opencart pure it is
			$manufacturer_name = $this->getManufacturerNameById($a_data['manufacturer_id']);

			$this->stdelog->write(4, $manufacturer_name, 'generateProductKeyword() : $manufacturer_name after $this->getManufacturerNameById()');

			$replace[] = trim($manufacturer_name);
		}
		
		$this->stdelog->write(4, $search, 'generateProductKeyword() : $search');
		
		$this->stdelog->write(4, $replace, 'generateProductKeyword() : $replace');

		$keyword = str_replace($search, $replace, $setting['formula']);

		$this->stdelog->write(3, $keyword, 'generateProductKeyword() : return $keyword');

		return $keyword;
	}

	public function generateOtherSystemsEssenceKeyword($a_data, $setting) {
		$this->stdelog->write(3, 'generateOtherSystemsEssenceKeyword() is called');
		$this->stdelog->write(4, $a_data, 'generateOtherSystemsEssenceKeyword() : $a_data');
		$this->stdelog->write(4, $setting, 'generateOtherSystemsEssenceKeyword() : $setting');

		$keyword = '';

		if ('category' == $a_data['essence']) {
			$essence_name_var = '[category_name]';
		} elseif ('manufacturer' == $a_data['essence']) {
			$essence_name_var = '[manufacturer_name]';
		} elseif ('information' == $a_data['essence']) {
			$essence_name_var = '[information_title]';
		}

		$essence_id_var = '[' . $a_data['essence'] . '_id]';

		$search = array(
			$essence_name_var,
			$essence_id_var
		);

		$replace = array();
		$replace[] = isset($a_data['name']) ? trim($a_data['name']) : ''; // !important - even if information
		$replace[] = isset($a_data['essence_id']) ? trim($a_data['essence_id']) : '';

		$keyword = str_replace($search, $replace, $setting['formula']);

		$this->stdelog->write(3, $keyword, 'generateOtherSystemsEssenceKeyword() : return $keyword');

		return $keyword;
	}

	public function generateNotSystemsEssenceKeyword($a_data, $setting) {
		$this->stdelog->write(3, 'generateNotSystemsEssenceKeyword() is called');
		$this->stdelog->write(4, $a_data, 'generateNotSystemsEssenceKeyword() : $a_data');
		$this->stdelog->write(4, $setting, 'generateNotSystemsEssenceKeyword() : $setting');
		
		// TMP
		$setting['formula'] = '[essence_name]';

		$keyword = '';

		$search = array(
			'[essence_name]',
			'[essence_id]'
		);

		$replace = array();
		$replace[] = isset($a_data['name']) ? trim($a_data['name']) : '';
		$replace[] = isset($a_data['essence_id']) ? trim($a_data['essence_id']) : '';

		$keyword = str_replace($search, $replace, $setting['formula']);

		$this->stdelog->write(3, $keyword, 'generateNotSystemsEssenceKeyword() : return $keyword');

		return $keyword;
	}

	public function countEssenceItems($essence) {
		$this->stdelog->write(3, 'countEssenceItems() is called');

		$sql = "SELECT COUNT(*) AS number FROM `" . DB_PREFIX . "" . $this->db->escape($essence) . "`";

		$this->stdelog->write(4, $sql, 'countEssenceItems() : $sql');

		$res = $this->db->query($sql);

		$this->stdelog->write(4, $res, 'countEssenceItems() : $res');

		if ($res) {
			$this->stdelog->write(3, $res, 'countEssenceItems() : $res');			
			return $res->row['number'];
		} else {
			$this->stdelog->write(1, $res, 'countEssenceItems() : $res');
			return false;
		}
	}

	public function getEssenceList($essence, $limits, $order) {
		$this->stdelog->write(3, 'getEssenceList() is called');

		if ('category' == $essence) {
			$sql = "SELECT category_id FROM " . DB_PREFIX . "category ORDER BY category_id " . $this->db->escape($order) . " LIMIT " . (int)$limits['first_element'] . "," . (int)$limits['limit_n'];
		}

		if ('product' == $essence) {
			$sql = "SELECT product_id FROM " . DB_PREFIX . "product ORDER BY product_id " . $this->db->escape($order) . " LIMIT " . (int)$limits['first_element'] . "," . (int)$limits['limit_n'];
		}

		if ('manufacturer' == $essence) {
			$sql = "SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer ORDER BY manufacturer_id " . $this->db->escape($order) . " LIMIT " . (int)$limits['first_element'] . "," . (int)$limits['limit_n'];
		}

		if ('information' == $essence) {
			$sql = "SELECT information_id FROM " . DB_PREFIX . "information ORDER BY information_id " . $this->db->escape($order) . " LIMIT " . (int)$limits['first_element'] . "," . (int)$limits['limit_n'];
		}

		// Доп табы для массовой генерации

		// Example for customization . begin
		if ('newsblog_article' == $essence) {
			$sql = "SELECT article_id FROM " . DB_PREFIX . "newsblog_article ORDER BY article_id " . $this->db->escape($order) . " LIMIT " . (int)$limits['first_element'] . "," . (int)$limits['limit_n'];
		}

		if ('newsblog_category' == $essence) {
			$sql = "SELECT category_id FROM " . DB_PREFIX . "newsblog_category ORDER BY category_id " . $this->db->escape($order) . " LIMIT " . (int)$limits['first_element'] . "," . (int)$limits['limit_n'];
		}

		// Example for customization . end

		$this->stdelog->write(4, $sql, 'getEssenceList() : $sql');

		$res = $this->db->query($sql);

		if ($res) {
			$this->stdelog->write(3, $sql, 'getEssenceList() : return $res');
			return $res->rows;
		} else {
			$this->stdelog->write(1, $sql, 'getEssenceList() : return $res');
			return false;
		}
	}
	
	/*
	 * Is different from 2.x! ...
	 */
	public function getEssenceNames($essence, $primary_key, $essence_id) {
		$this->stdelog->write(3, 'getEssenceName() is called');

		$column_name = 'name';

		// Warning I (!)
		if ('information' == $essence) {
			$column_name = 'title';
		}

		$sql = "SELECT `language_id`, `$column_name` FROM `" . DB_PREFIX . $essence . "_description` WHERE `" . $primary_key . "` = '" . (int)$essence_id . "'";
		
		$this->stdelog->write(4, $sql, 'getEssenceName() : $sql');

		$query = $this->db->query($sql);

		$names = array();
		
		if ($query->num_rows > 0) {
			foreach ($query->rows as $row) {
				$names[$row['language_id']] = $row[$column_name];
			}			
			
			$this->stdelog->write(3, $names, 'getEssenceName() : return $names');
			return $names;
			
		} else {
			$this->stdelog->write(3, false, 'getEssenceName() : NO RESULT');
			return array();
		}
	}

	public function getURLs($query_key, $essence_id) {
		$this->stdelog->write(3, 'getURLs() is called');

		$sql = "SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = '" . $this->db->escape($query_key) . "=" . (int)$essence_id . "'";

		$this->stdelog->write(4, $sql, 'getURLs() : $sql');

		$res = $this->db->query($sql);

		$this->stdelog->write(4, $res, 'getURLs() : $res');
		
		$urls = array();
		
		if ($res->num_rows > 0) {
			
			// A! ExistEmptyURLs
			foreach ($res->rows as $key => $row) {		
				$row['keyword'] = trim($row['keyword']);
				
				if (empty($row['keyword'])) {
					$this->stdelog->write(1, 'getURLs() : SEO URL IS EMPTY! - DELETE IT!');
					
					$sql_delete_empty = "DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `seo_url_id` = '" . (int)$row['seo_url_id'] . "'";
					
					$this->stdelog->write(4, $sql_delete_empty, 'getURLs() : $sql_delete_empty');
					
					$res_delete_empty = $this->db->query($sql_delete_empty);
					
					$this->stdelog->write(4, $res_delete_empty, 'getURLs() : $res_delete_empty');
					
					unset($res->rows[$key]);
				}
			}			
			
			// check doubles here! - it is more readable with new foreach
			foreach ($res->rows as $row) {
				if (!isset($urls[$row['store_id']][$row['language_id']])) {
					// It is first SEO URL for this store & language - ok
					$urls[$row['store_id']][$row['language_id']] = trim($row['keyword']); // A! EmptyExistURLs-1
				} else {
					// It's not first SEO URL in new array - bad					
					$this->stdelog->write(1, 'getURLs() : essence already has more than 1 SEO URL for store & language! Delete it!!!');
					
					$sql_delete = "DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = '" . $this->db->escape($query_key) . "=" . (int)$essence_id . "' AND `store_id` = '" . $row['store_id'] . "' AND `language_id` = '" . $row['language_id'] . "'";
					
					$res_delete = $this->db->query($sql_delete);
					
					$this->stdelog->write(4, $res_delete, 'getURLs() : $res_delete DOUBLES');
					
					unset($urls[$row['store_id']][$row['language_id']]);				
				}				
			}
			
			$this->stdelog->write(4, $urls, 'getURLs() : $urls');		
			
			return $urls;
		} else {
			$this->stdelog->write(4, 'getURLs() : $res->num_rows 0');
			return array();
		}
	}
	
	public function setURL($query_key, $essence_id, $keyword, $store_id, $language_id) {
		$this->stdelog->write(3, 'setURL() is called');
		
		$sql = "INSERT INTO `" . DB_PREFIX . "seo_url` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `query` = '" . $this->db->escape($query_key) . "=" . (int)$essence_id . "', `keyword` = '" . $this->db->escape($keyword) . "'";
		
		$this->stdelog->write(4, $sql, 'setURL() is called : $sql');

		$this->db->query($sql);

		$res = $this->db->getLastId();
		
		$this->stdelog->write(3, $res, 'setURL() is called : $res');

		if ($res > 0) {
			return true;
		}
	}
	
	public function deleteURL($query_key, $essence_id, $store_id, $language_id) {
		$this->stdelog->write(3, 'deleteURL() is called');
		
		$sql = "DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = '" . $this->db->escape($query_key) . "=" . (int)$essence_id . "' AND `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . (int)$language_id . "'";

		$query = $this->db->query($sql);
		
		$this->stdelog->write(4, $query, 'deleteURL() : $query');

		return false;
	}

	public function setRedirect($keyword_actual, $keyword_old, $query_key, $essence_id, $store_id, $language_id) {
		$this->stdelog->write(3, 'setRedirect() is called');
		
		if ($this->issetUrlByEssence($query_key, $essence_id, $store_id, $language_id)) {
			$this->stdelog->write(4, 'setRedirect() : $this->issetUrlByEssence() returned true');
			
			// if was changed repeatedly
			$sql_0 = "DELETE FROM `" . DB_PREFIX . "seo_url_generator_redirects` WHERE `seo_url_old` = '" . $this->db->escape($keyword_actual) . "' AND `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . (int)$language_id . "'";

			$this->stdelog->write(4, $sql_0, 'setRedirect() : $sql_0');
			
			$res = $this->db->query($sql_0);
			
			$this->stdelog->write(4, $res, 'setRedirect() : $res for $sql_0');

			$sql_1 = "UPDATE `" . DB_PREFIX . "seo_url_generator_redirects` SET " . "`seo_url_actual` = '" . $this->db->escape($keyword_actual) . "' " . "WHERE `query` = '" . $this->db->escape($query_key) . "=" . (int)$essence_id . "' AND `store_id` = '" . (int)$store_id . "' AND `language_id` = '" . (int)$language_id . "'";
			
			$this->stdelog->write(4, $sql_1, 'setRedirect() : $sql_1');

			$this->db->query($sql_1);
		} else {
			$this->stdelog->write(4, 'setRedirect() : $this->issetUrlByEssence() returned false');
		}

		// insert second
		$sql_2 = "INSERT INTO `" . DB_PREFIX . "seo_url_generator_redirects` SET `store_id` = '" . (int)$store_id . "', `language_id` = '" . (int)$language_id . "', `seo_url_old`= '" . $this->db->escape($keyword_old) . "', " . "`seo_url_actual` = '" . $this->db->escape($keyword_actual) . "', " . "`query` = '" . $this->db->escape($query_key) . "=" . (int)$essence_id . "'";
		
		$this->stdelog->write(4, $sql_2, 'setRedirect() : $sql_2');

		$query = $this->db->query($sql_2);

		$res = $this->db->getLastId();

		if ($res > 0) {
			$this->stdelog->write(3, $res, 'setRedirect() : $res');
			
			return true;
		}
		
		$this->stdelog->write(1, $res, 'setRedirect() : $res');

		return false;
	}
	
	public function getRedirects($query_key, $essence_id) {
		$this->stdelog->write(3, 'getRedirects() is called');

		$sql = "SELECT * FROM `" . DB_PREFIX . "seo_url_generator_redirects` WHERE `query` = '" . $this->db->escape($query_key) . "=" . (int)$essence_id . "' ";
		
		$this->stdelog->write(4, $sql, 'getRedirects() : $sql');
		
		$query = $this->db->query($sql);

		$this->stdelog->write(4, $query, 'getRedirects() : $query');
		
		$redirects = array();

		if ($query->num_rows > 0) {
			foreach ($query->rows as $row) {
				$redirects[$row['store_id']][$row['language_id']][] = $row['seo_url_old'];
			}
			
			$this->stdelog->write(4, $redirects, 'getRedirects() : $redirects');

			return $redirects;
		} else {
			$this->stdelog->write(4, 'getRedirects() : $res->num_rows 0');
			return false;
		}
		
		return array();
	}

	public function issetUrlByEssence($query_key, $essence_id) {
		$this->stdelog->write(3, 'issetUrlByEssence() is called');
		
		$sql = "SELECT `seo_url_id` FROM `" . DB_PREFIX . "seo_url_generator_redirects` WHERE `query` = '" . $this->db->escape($query_key) . "=" . (int)$essence_id . "' ";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			$this->stdelog->write(3, true, 'issetUrlByEssence() : return true');
			
			return true;
		}
		
		$this->stdelog->write(3, false, 'issetUrlByEssence() : return false');

		return false;
	}
	
	public function isUnique($keyword, $query_key, $essence_id, $store_id) {
		$this->stdelog->write(3, 'isUnique() is called');
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `keyword`='" . $this->db->escape($keyword) . "' AND `store_id` = '" . (int)$store_id . "' AND `query` !='" . $query_key . "=" . $essence_id . "'";

		$this->stdelog->write(4, $sql, 'isUnique() : $sql');

		$query = $this->db->query($sql);

		if (0 == $query->num_rows) {
			$this->stdelog->write(3, 'getUniqueUrl() : return true');
			return true;
		}
		
		$this->stdelog->write(3, 'getUniqueUrl() : return false');
		return false;
	}

	public function makeUniqueUrl($keyword, $store_id) {
		$this->stdelog->write(3, 'makeUniqueUrl() is called');
		
		$valid = false;
		$i = 0;
		
		$delimiter_char = '-';
			
		if ('underscore' == $this->config->get('seo_url_generator_delimiter_char')) {
			$delimiter_char = '_';
		}

		while (false === $valid) {
			$unique_keyword = $keyword;
			
			if ($i > 0) {
				$unique_keyword .= $delimiter_char . $i;
			}

			$sql = "SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `keyword`='" . $this->db->escape($unique_keyword) . "' AND `store_id` = '" . (int)$store_id . "'";

			$this->stdelog->write(4, $sql, 'makeUniqueUrl() : $sql');
			
			$query = $this->db->query($sql);
			
			$this->stdelog->write(4, $query, 'makeUniqueUrl() : $query');
			
			if (0 == $query->num_rows) {
				$valid = true;
				break;
			}

			$i++;
		}
		
		$this->stdelog->write(3, $unique_keyword, 'makeUniqueUrl() : return $unique_keyword');

		return $unique_keyword;
	}

	public function getProductData($product_id) {
		$this->stdelog->write(3, 'getProductData() is called');

		$query = $this->db->query("SELECT `sku`, `model`, `manufacturer_id` FROM `" . DB_PREFIX . "product` WHERE `product_id` = '" . (int)$product_id . "'");

		if ($query->row) {
			return $query->row;
		} else {
			$this->stdelog->write(1, $query->row, 'getProductData() : $query->row is empty');
		}

		return false;
	}

	public function getManufacturerNameById($manufacturer_id) {
		$this->stdelog->write(3, 'getManufacturerNameById() is called');

		$this->stdelog->write(4, $manufacturer_id, 'getManufacturerNameById() : $manufacturer_id');

		if (!$manufacturer_id) {
			$this->stdelog->write(1, 'NO VALUE', 'getManufacturerNameById() : $manufacturer_id');
			return false;
		}
		
		$sql = "SELECT `name` FROM `" . DB_PREFIX . "manufacturer` WHERE `manufacturer_id` = '" . (int)$manufacturer_id . "'";
		
		$this->stdelog->write(4, $sql, 'getManufacturerNameById() : $sql');
		
		$query = $this->db->query($sql);
		
		$this->stdelog->write(4, $query, 'getManufacturerNameById() : $query');

		if (isset($query->row['name'])) {
			$this->stdelog->write(3, $query->row['name'], 'getManufacturerNameById() : return $query->row["name"]');
			
			return $query->row['name'];			
		} else {
			$this->stdelog->write(1, '!isset', 'getManufacturerNameById() : $query->row["name"]');
		}	

		return false;
	}




	/*
	------------------------------------------------------------------------------
	TRANSLIT
	------------------------------------------------------------------------------
	*/

	public function getFunctionsList() {
		$array = array();
		
		$target_dir = DIR_SYSTEM . 'library/seo_url_generator/translit/';

		$files = scandir($target_dir);
		
		foreach ($files as $file) {
			if ('.' != $file && '..' != $file) {
				if (is_file($target_dir . '/' . $file)) {
					$basename = basename($file, '.php');
					
					if (is_file($target_dir . '/' . $file)) {
						require_once $target_dir . '/' . $file;			
					
						$function = 'sug_translit_' . $basename;
						$function_title = 'sug_translit_' . $basename . '_title';					

						if (is_callable($function) && is_callable($function_title)) {
							$array[$function] = $function_title();
						} else {
							$this->log->write('ERROR -- SEO URL Generator: Not callable function ' . $function . '() or ' . $function_title . '()'
								. ' in admin/model/extension/module/seo_url_generator_translit.php on line ' . ( __LINE__ - 4) . '.'
								. ' Code: if (is_callable($function) && is_callable($function_title)) {');
						}
					}
				}
			}
		}
		
		return $array;
	}

	/*
	 * Вырезает все лишние символы, в том числе кириллицу...
	 * TODO: как сделать так, чтобы кириллицу не вырезало?
	 */
	public function translit($string, $setting) {
		$this->stdelog->write(3, '$this->model_extension_module_seo_url_generator->translit() is called');
		
		$this->stdelog->write(3, $string, '$this->model_extension_module_seo_url_generator->translit() : $string on start');

		$string = trim($string);
		$string = mb_strtolower($string); // Attention 1!

		// custom_replace
		$custom_replace_from = str_replace(array("\r\n", "\n"), '<br>', $setting['custom_replace_from']);
		$custom_replace_from_array = explode('<br>', $custom_replace_from);
		
		$custom_replace_to = str_replace(array("\r\n", "\n"), '<br>', $setting['custom_replace_to']);
		$custom_replace_to_array = explode('<br>', $custom_replace_to);
		
		$this->stdelog->write(4, $custom_replace_from, '$this->model_extension_module_seo_url_generator->translit() : $custom_replace_from');
		$this->stdelog->write(4, $custom_replace_to, '$this->model_extension_module_seo_url_generator->translit() : $custom_replace_to');
		$this->stdelog->write(4, $custom_replace_from_array, '$this->model_extension_module_seo_url_generator->translit() : $custom_replace_from_array');
		$this->stdelog->write(4, $custom_replace_to_array, '$this->model_extension_module_seo_url_generator->translit() : $custom_replace_to_array');
		
		$this->stdelog->write(3, $string, '$this->model_extension_module_seo_url_generator->translit() : $string BEFORE custom_replace');
		
		// for NON ASCII as ø
		$string = htmlentities($string, ENT_QUOTES, 'UTF-8');
		
		$this->stdelog->write(3, $string, '$this->model_extension_module_seo_url_generator->translit() : $string custom_replace NON ASCII');
		
		foreach ($custom_replace_from_array as $key => $value) {
			// some values were changed by htmlspecialchars in time of saving $this->request-post!
			$custom_replace_from_array[$key] = htmlentities(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
			
			$custom_replace_from_array[$key] = mb_strtolower($custom_replace_from_array[$key]); // Attention 1!
		}
		
		$this->stdelog->write(4, $custom_replace_from_array, '$this->model_extension_module_seo_url_generator->translit() : $custom_replace_from_array AFTER htmlentities()');
		
		$string = str_replace($custom_replace_from_array, $custom_replace_to_array, $string);
		
		$this->stdelog->write(3, $string, '$this->model_extension_module_seo_url_generator->translit() : $string AFTER custom_replace');

		// Take attention
		// OpenCart save name with htmlspecialchars in $this->request->post
		// so " is saved as &quot;
		// It was converted again with htmlentities in time of custom_replace 
		// and &quot; is as &amp;quot; now
		
		// that's why we need html_entity_decode()
		$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
				
		// ++
		// htmlspecialchars
		$string = str_replace(array('&nbsp;', '&quot;', '&lt;', '&gt;', '&amp;'), ' ', $string);
		
		// translit function
		$translit_function = $setting['translit_function'];
		
		$this->stdelog->write(3, $translit_function, '$this->model_extension_module_seo_url_generator->translit() : $translit_function');

		if ($translit_function) {
			$string = $this->sug->translitDo(DIR_SYSTEM . 'library/seo_url_generator/translit/', $string, $translit_function, $this->config->get('module_seo_url_generator_licence'));
			
			$this->stdelog->write(3, $string, '$this->model_extension_module_seo_url_generator->translit() : $string after call $translit_function()');
		}

		// Remove all not allowed chars
		if ('sug_translit_none' != $translit_function) {
				$string = preg_replace('/[^a-zA-Z0-9\-_]/', ' ', $string);
			} else {
				$this->sug->clearString($string, $setting, $this->config->get('module_seo_url_generator_licence'));
			}
		
		// delimiter_char
		if ('underscore' == $setting['delimiter_char']) {
			$string = preg_replace('| |', '_', $string);
			$string = preg_replace('/\s+/', '_', $string);
			$string = preg_replace('|-_|', '_', $string);
			$string = preg_replace('|_-|', '_', $string);
			$string = preg_replace('|_+|', '_', $string);
		}

		if ('hyphen' == $setting['delimiter_char']) {
			$string = preg_replace('| |', '-', $string);
			$string = preg_replace('/\s+/', '-', $string);
			$string = preg_replace('|-_|', '-', $string);
			$string = preg_replace('|_-|', '-', $string);
			$string = preg_replace('|-+|', '-', $string);
		}

		// change_delimiter_char
		if ('underscore_to_hyphen' == $setting['change_delimiter_char']) {
			$string = preg_replace('|_|', '-', $string);
		}

		if ('hyphen_to_underscore' == $setting['change_delimiter_char']) {
			$string = preg_replace('|-|', '_', $string);
		}

		// Remove delimeter char from beginning and end of a string
		$string = preg_replace(
			array('|^-|', '|-$|', '|^_|', '|_$|'),
			array('', '', '', ''),
			$string
		);

		$this->stdelog->write(3, $string, '$this->model_extension_module_seo_url_generator->translit() : return $string');
		
		return $string;
	}
}
