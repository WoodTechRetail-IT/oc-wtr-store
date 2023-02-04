<?php

/**
 * @category   OpenCart
 * @package    SEO URL Generator PRO
 * @copyright  © Serge Tkach, 2018-2021, http://sergetkach.com/
 */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// for different PHP versions...
if (version_compare(PHP_VERSION, '7.2') >= 0) {
	$php_v = '72_73';
} elseif (version_compare(PHP_VERSION, '7.1') >= 0) {
	$php_v = '71';
} elseif (version_compare(PHP_VERSION, '5.6.0') >= 0) {
	$php_v = '56_70';
} elseif (version_compare(PHP_VERSION, '5.4.0') >= 0) {
	$php_v = '54_56';
} else {
	echo "Sorry! Version for PHP 5.3 Not Supported!<br>Please contact to author!";
	exit;
}

$file = DIR_SYSTEM . 'library/seo_url_generator/seo_url_generator_' . $php_v . '.php';

if (is_file($file)) {
	include $file;
} else {
	echo "No file '$file'<br>";
	exit;
}

class ControllerExtensionModuleSeoUrlGenerator extends Controller {
	private $code = 'seo_url_generator';
	private $stde; // StdE
	private $stdelog;

	function __construct($registry) {
		parent::__construct($registry);
		
		$this->sug = new SeoUrlGenerator();

    // StdE Require
		// $this->load->library('stde'); autoload
		$this->stde = new StdE($registry);
		$this->registry->set('stde', $this->stde);
		$this->stde->setCode($this->code);
		$this->stde->setType('extension_monolithic');

    // StdeLog require
		$this->stdelog = new StdeLog($this->code);
		$this->registry->set('stdelog', $this->stdelog);
		$this->stdelog->setDebug($this->config->get('module_' . $this->code . '_debug'));
	}

	public function index() {
		// Нужны ли нам логи?
		// Надо же делать репорт
		// А можно даже слать письма на почту, если что-то пошло не так...
		
		// Надо отслеживать максимальное время исполнения скрипта
	
		// Надо определить, как хранить данные от вызова скрипта до следующего вызова по cron
		
		// Надо проходить по всем товарам с 1 до x
		// Что это даст?
		
		// Проверять ли актуализацию названий товаров при переборе? Тогда надо выбирать все товары, а не только с пустыми ЧПУ
		
		return $this->actionMassGenerateURL();
	}
	
	/*
	 * Base method
	 * Это у нас основой метод, который генерирует SEO URL
	 *
	 * Ему все равно, откуда поступают данные о товаре - из формы в админке или из базы при массовом редактировании
	 *
	 * Определить сущность
	 * Определить, какие переменные есть в формуле
	 * Вырезать из формулы лишние - (транслит сам это сделает)
	 * Транлитировать
	 * Запросить уникальность
	 * Если URL не уникален, то использовать индекс N - причем, это не зависит от того, есть ли в формуле генерации доп переменные или нет
	 */

	public function generateSeoUrl($a_data) {
		$this->stdelog->write(3, 'generateSeoUrl() is called');
		
		$this->stdelog->write(4, $a_data, 'generateSeoUrl() : $a_data');

		$this->load->model('extension/module/' . $this->code);
		
		// Setting
		// Нельзя, чтобы запрос настроек шел при каждой иттерации
		// В 3-ке надо, чтобы в данные этого метода передавались и настройки
		
		$setting = $a_data['setting'];
		
		$this->stdelog->write(4, $setting, 'generateSeoUrl() : $setting');
		
		$this->stdelog->write(3, $a_data['essence'], 'generateSeoUrl() : $a_data["essence"]');

		$name = $this->model_extension_module_seo_url_generator->essenceNameFilter($a_data['name'], $a_data['essence'], $setting);

		$this->stdelog->write(4, $name, 'generateSeoUrl() : $name after $this->model_extension_module_seo_url_generator->essenceNameFilter()');

		$keyword = '';
		
		$this->stdelog->write(4, $a_data['essence'], 'generateSeoUrl() : $a_data["essence"]');

		if (isset($a_data['essence']) && $a_data['essence']) {
			if ('category' == $a_data['essence']) {			
				$this->stdelog->write(4, 'generateSeoUrl() : prepare to call to generateOtherSystemsEssenceKeyword() in category essence');
				$keyword = $this->model_extension_module_seo_url_generator->generateOtherSystemsEssenceKeyword($a_data, $setting);
			} elseif ('product' == $a_data['essence']) {
				$this->stdelog->write(4, 'generateSeoUrl() : prepare to call to generateProductKeyword() in product essence');
				$keyword = $this->model_extension_module_seo_url_generator->generateProductKeyword($a_data, $setting);
			} elseif ('manufacturer' == $a_data['essence']) {
				$this->stdelog->write(4, 'generateSeoUrl() : prepare to call to generateOtherSystemsEssenceKeyword() in manufacturer essence');
				$keyword = $this->model_extension_module_seo_url_generator->generateOtherSystemsEssenceKeyword($a_data, $setting);
			} elseif ('information' == $a_data['essence']) {
				$this->stdelog->write(4, 'generateSeoUrl() : prepare to call to generateOtherSystemsEssenceKeyword() in information essence');
				$keyword = $this->model_extension_module_seo_url_generator->generateOtherSystemsEssenceKeyword($a_data, $setting);
			} else {
				$this->stdelog->write(4, 'generateSeoUrl() : prepare to call to generateProductKeyword() in nonsystem essence');
				$keyword = $this->model_extension_module_seo_url_generator->generateNotSystemsEssenceKeyword($a_data, $setting);
			}

			$this->stdelog->write(4, $keyword, 'generateSeoUrl() : $keyword returned from generate function()');
		} else {
			$this->stdelog->write(1, 'generateSeoUrl() : $a_data["essence"] is empty');
		}

		$this->stdelog->write(4, $keyword, 'generateSeoUrl() : call to $this->model_extension_module_seo_url_generator->translit()');

		$keyword = $this->model_extension_module_seo_url_generator->translit($keyword, $setting);

		$this->stdelog->write(4, $keyword, 'generateSeoUrl() : $keyword after $this->model_extension_module_seo_url_generator->translit()');
		
		// Make unique
		if (!$this->model_extension_module_seo_url_generator->isUnique($keyword, $a_data['essence'] . '_id', $a_data['essence_id'], $a_data['store_id'])) {
			$keyword = $this->model_extension_module_seo_url_generator->makeUniqueUrl($keyword, $a_data['store_id']);
			
			$this->stdelog->write(3, $keyword, 'generateSeoUrl() : $keyword after $this->model_extension_module_seo_url_generator->makeUniqueUrl()');
		}

		$this->stdelog->write(3, $keyword, 'generateSeoUrl() : return $keyword');
		
		return $keyword;
	}
	

	/*
	------------------------------------------------------------------------------
	Массовая генерация
	------------------------------------------------------------------------------
	*/

	public function actionMassGenerateURL() {
		// Узнаю кол-во итемов в базе сущности всего
		// Определяю лимит итемов за одно обращение (по умолчанию 200 товаров за 1 иттерацию через ajax)
		// Делаю выборку и в цикле присваиваю URL
		// Возращаю порядковый номер товара для начала следующей иттерации
		$this->stdelog->write(2, 'CRON actionMassGenerateURL() is called');
		$this->stdelog->write(3, $this->request->get, 'actionMassGenerateURL() : $this->request->get');

		$this->load->language('extension/module/' . $this->code);
		$this->load->model('extension/module/' . $this->code);

		### DATA
		$json = array();
		
		$this->stdelog->write(3, $this->config->get('module_seo_url_generator_licence'), 'actionMassGenerateURL() : $this->config->get("module_seo_url_generator_licence")');
		
		if (!$this->sug->isValidLicence($this->config->get('module_seo_url_generator_licence'))) {
			$json['error'] = true;
			$json['answer'] = $this->language->get('error_licence');
		}		

		// A!
		// For customized modules $primary_key may be different with key in seo_url.query!!
		// So it is necessary to use $query_key also
		// For example NewsBlog!

		//$step = $this->request->get['step'];
		if (isset($this->request->get['step'])) {
			$step = $this->request->get['step'];
		} else {
			$step = 1;
		}
		//$essence = $this->request->get['essence'];
		if (isset($this->request->get['essence'])) {
			$essence = $this->request->get['essence'];
		} else {
			$essence = 'product';
		}
		$primary_key = $essence . '_id'; // A! for defaults essences
		$query_key = $essence . '_id'; // A! dummy for default essences
		//$generation_type = $this->request->get['generationType'];
		if (isset($this->request->get['generationType'])) {
			$generation_type = $this->request->get['generationType'];
		} else {
			$generation_type = 'empty';
		}
		
		$this->stdelog->write(4, $step, 'actionMassGenerateURL() : $step');
		$this->stdelog->write(4, $essence, 'actionMassGenerateURL() : $essence');
		$this->stdelog->write(4, $primary_key, 'actionMassGenerateURL() : $primary_key');
		$this->stdelog->write(4, $generation_type, 'actionMassGenerateURL() : $generation_type');
		
		// For custom_tab
		if (isset($this->request->get['custom_tab']) && 'false' !== $this->request->get['custom_tab']) { // (A!) Value was send with ajax
			$this->stdelog->write(4, 'actionMassGenerateURL() : It has custom_tab');

			$custom_tabs = $this->model_extension_module_seo_url_generator->getCustomTabs();
			$primary_key = $custom_tabs[$essence]['primary_key']; // A! for custom essences
			$query_key = $custom_tabs[$essence]['query_key']; // A!

			$this->stdelog->write(4, $custom_tabs, 'actionMassGenerateURL() : $custom_tabs');
		}
		
		### SETTING		
		$this->load->model('setting/store');
		
		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name') . $this->language->get('text_default'),
		);

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name'],
			);
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('setting/setting');
		
		$data['store_setting'] = array();
		
		foreach ($data['stores'] as $store) {
			$store_setting = $this->model_setting_setting->getSetting('module_' . $this->code, $store['store_id']);

			if (in_array($essence, array('category', 'product', 'manufacturer', 'information'))) {
				$data['store_setting'][$store['store_id']]['formula'] = $store_setting['module_seo_url_generator_' . $essence . '_formula'];
			} else {
				$this->stdelog->write(1, 'actionMassGenerateURL() : is not system essence');

				$data['store_setting'][$store['store_id']]['formula'] = false;
			}

			$data['store_setting'][$store['store_id']]['translit_function'] = $store_setting['module_seo_url_generator_translit_function'];
			$data['store_setting'][$store['store_id']]['delimiter_char'] = $store_setting['module_seo_url_generator_delimiter_char'];
			$data['store_setting'][$store['store_id']]['change_delimiter_char'] = $store_setting['module_seo_url_generator_change_delimiter_char'];
			$data['store_setting'][$store['store_id']]['rewrite_on_save'] = $store_setting['module_seo_url_generator_rewrite_on_save'];
			$data['store_setting'][$store['store_id']]['custom_replace_from'] = $store_setting['module_seo_url_generator_custom_replace_from'];
			$data['store_setting'][$store['store_id']]['custom_replace_to'] = $store_setting['module_seo_url_generator_custom_replace_to'];
		}

		// limit
		//$limit_n = $this->config->get('module_seo_url_generator_limit');
		$limit_n = 10000;
		if (isset($this->request->get['limit'])) {
			$limit_n = $this->request->get['limit'];
		}

		// default 200
		if (!$limit_n) {
			$limit_n = 200;
		}
		
		$this->stdelog->write(3, $limit_n, 'actionMassGenerateURL() : $limit_n');

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = "DESC";
		}
		
		### ACTION
		$this->stdelog->write(4, 'actionMassGenerateURL() : call to $this->model_extension_module_seo_url_generator->countEssenceItems()');

		$n_essence_items = $this->model_extension_module_seo_url_generator->countEssenceItems($essence);

		if (false === $n_essence_items) {
			$this->stdelog->write(1, $n_essence_items, 'actionMassGenerateURL() : $n_essence_items is false - goto block_finish;');

			$json['error'] = true;
			$json['answer'] = sprintf($this->language->get('answer_step_item_error'), $step, $steps_all) . $this->language->get('error_steps_no_count');
			goto block_finish;
		}

		$steps_all = ceil($n_essence_items / $limit_n);

		$limits = array(
			'first_element'=>$limit_n * $step - $limit_n,
			'limit_n'=>$limit_n
		);

		$this->stdelog->write(
			3, 
			"\r\n\$n_essence_items = $n_essence_items" . PHP_EOL 
				. "\$steps_all = $steps_all" . PHP_EOL 
				. "\$first_element = " . ($limit_n * $step - $limit_n) . PHP_EOL 
				. "\$limit_n = $limit_n" . PHP_EOL 
				. "------------------------------------------" . PHP_EOL,
			'actionMassGenerateURL() variables'
		);

		$essence_list = $this->model_extension_module_seo_url_generator->getEssenceList($essence, $limits, $order);		

		$this->stdelog->write(3, count($essence_list), 'actionMassGenerateURL() : count($essence_list) for foreach');

		if (count($essence_list) > 0) {
			$this->stdelog->write(4, 'actionMassGenerateURL() : foreach ($essence_list as $essence_item) {');

			foreach ($essence_list as $essence_item) {
				$this->stdelog->write(3, "\r\n\r\n\r\n\r\n\r\n====================================================================================\r\n"
					. "------------------------------------------------------------------------------------\r\n"
					. "----- actionMassGenerateURL() : NEW ITTERATION ------\r\n"
					. "------------------------------------------------------------------------------------\r\n"
					. "====================================================================================\r\n\r\n");
				
				$essence_id = $essence_item[$primary_key];
				
				$this->stdelog->write(4, $essence_id, 'actionMassGenerateURL() : $essence_id');
				
				$keywords_old_not_all_are_present = false;
				
				$keywords_old = $this->model_extension_module_seo_url_generator->getURLs($query_key, $essence_id);
				
				$this->stdelog->write(4, $keywords_old, 'actionMassGenerateURL() : $keywords_old');
				
				foreach ($data['stores'] as $store) {
					foreach ($data['languages'] as $language) {
						if (!isset($keywords_old[$store['store_id']][$language['language_id']])) {
							$keywords_old_not_all_are_present = true;
							$keywords_old[$store['store_id']][$language['language_id']] = false;
						}						
					}
				}
				
				$this->stdelog->write(4, $keywords_old, 'actionMassGenerateURL() : $keywords_old');

				if ($keywords_old_not_all_are_present || 'replace' == $generation_type) {
					// Default
					$a_data = array(
						'essence'=>$essence,
						'essence_id'=>$essence_id
					);
					
					// Names with all languages
					$names = array();
					
					// Manufacturer - is different from other essences
					if ('manufacturer' == $essence) {
						$name = $this->model_extension_module_seo_url_generator->getManufacturerNameById($essence_id, $data['languages']);
						
						foreach ($data['languages'] as $language) {
							$names[$language['language_id']] = $name;
						}
					} else {
						$names = $this->model_extension_module_seo_url_generator->getEssenceNames($essence, $primary_key, $essence_id);
					}

					if (count($names) < 1) {
						$this->stdelog->write(1, $names, 'actionMassGenerateURL() : $names');
						continue;
					}
										
					$this->stdelog->write(4, $names, 'actionMassGenerateURL() : $names');
					
					// Product
					if ('product' == $essence) {
						// It is necessary to get these data ony once a essence itteration . Begin						
						$product_data = $this->model_extension_module_seo_url_generator->getProductData($essence_id);
						
						// if [manufacturer_name] is used in even one formula
						$use_manufacturer_name = false;
						
						foreach ($data['stores'] as $store) {
							foreach ($data['languages'] as $language) {
								if (false !== strstr($data['store_setting'][$store['store_id']]['formula'][$language['language_id']], '[manufacturer_name]')) {
									$use_manufacturer_name = true;
									break;
								}
							}
						}
						
						if ($use_manufacturer_name) {
							$product_data['manufatrurer_name'] = $this->model_extension_module_seo_url_generator->getManufacturerNameById($product_data['manufacturer_id']);

							$this->stdelog->write(4, $product_data['manufatrurer_name'], 'actionMassGenerateURL() : $product_data["manufatrurer_name"] after $this->model_extension_module_seo_url_generator->getManufacturerNameById()');
						}
						
						$a_data = array_merge($a_data, $product_data);
						// It is necessary to get these data ony once a essence itteration . End
					}
					
					// Stores & Languages Itterations . Begin
					foreach ($data['stores'] as $store) {
						foreach ($data['languages'] as $language) {							
							$keyword_old = $keywords_old[$store['store_id']][$language['language_id']];
							
							if (!isset($names[$language['language_id']])) {			
								$this->log->write('ERROR -- name or title is empty for `' . $essence . '` :: ' . $primary_key . '=' . $essence_id . ', store_id: ' . $store['store_id'] . ', language_id: ' . $language['language_id']);
								
								continue;
							}
							
							$a_data['name'] = $names[$language['language_id']];
							$a_data['store_id'] = $store['store_id'];

							$a_data['setting'] = array(
								'translit_function'     =>$data['store_setting'][$store['store_id']]['translit_function'][$language['language_id']],
								'formula'               =>$data['store_setting'][$store['store_id']]['formula'][$language['language_id']],
								'delimiter_char'        =>$data['store_setting'][$store['store_id']]['delimiter_char'][$language['language_id']],
								'change_delimiter_char' =>$data['store_setting'][$store['store_id']]['change_delimiter_char'][$language['language_id']],
								'rewrite_on_save'       =>$data['store_setting'][$store['store_id']]['rewrite_on_save'][$language['language_id']],
								'custom_replace_from'   =>$data['store_setting'][$store['store_id']]['custom_replace_from'][$language['language_id']],
								'custom_replace_to'     =>$data['store_setting'][$store['store_id']]['custom_replace_to'][$language['language_id']],
							);

							$keyword_new = $this->generateSeoUrl($a_data);
							
							if (!$keyword_new) {
								$this->stdelog->write(1, $keyword_new, 'actionMassGenerateURL() : $keyword_new');
								continue;
							}

							// (A!)
							// rewrite_on_save : 1
							// change_delimiter_char : 0
							// Выходит, что данные актуализируются, тогда как Замена разделителя отключена
							// Генерация ЧПУ $keyword_new происходит с нуля
							// Может так случиться, что раннее был  _, а теперь - (или же наоброт)
							// В таком случае $keyword_new и $keyword_new будут отличаться именно за счет разделителя, а не за счет изменения данных
							// (Q?)
							// Но разве включенная актуализация не означает, что человек готов к перезаписи ЧПУ?
							// Может если не меняется название товара, то ему не надо портить настроенную контектстую рекламу.
							// А что, если человек захочет, чтобы у него менялись ЧПУ не только, когда меняют данные товара, но и когда изменился разделитель
							// Значит, надо ориентироваться на настройку change_delimiter_char
							
							$this->stdelog->write(3, $data['store_setting'][$store['store_id']]['change_delimiter_char'][$language['language_id']], 'actionMassGenerateURL() : $data["store_setting"][$store["store_id"]]["change_delimiter_char"][$language["language_id"]]');

							// Тут однозначно записываем новый ЧПУ в базу - но только не при непосредственном редактировании товара
							if (!$keyword_old) {								
								$this->model_extension_module_seo_url_generator->setURL($query_key, $a_data['essence_id'], $keyword_new, $store['store_id'], $language['language_id']);
								continue;
							}

							// Далее блок кода касается только случаев, когда выбрана смена разделителя слов
							// 
							// Актуализация SEO URL при редактировании не имеет значение, потому что
							// это не редактирование, а генерация
							// нажатие на кнопку replace в интерфейсе массовой генерации означает осознанный выбор в пользу необходимости актуализации

							// change_delimiter_char
							if ('donot' != $data['store_setting'][$store['store_id']]['change_delimiter_char'][$language['language_id']]) {
								// Если в этой настройке выбрал замены, значит он готов к перезаписи ЧПУ из-за смееы разделителя слов
								if ($keyword_old != $keyword_new) {
									$this->model_extension_module_seo_url_generator->deleteURL($query_key, $essence_id, $store['store_id'], $language['language_id']);
									$this->model_extension_module_seo_url_generator->setURL($query_key, $a_data['essence_id'], $keyword_new, $store['store_id'], $language['language_id']);
									$this->model_extension_module_seo_url_generator->setRedirect($keyword_new, $keyword_old, $query_key, $a_data['essence_id'], $store['store_id'], $language['language_id']);
								}
							} else {
								// В противном случае, надо сравнивать ЧПУ вообще без разделителей, чтобы менять его только лишь в случае замены данных
								$keyword_old_compare = preg_replace(array('|_+|', '|-+|'), array('', ''), $keyword_old);
								$keyword_new_compare = preg_replace(array('|_+|', '|-+|'), array('', ''), $keyword_new);

								$this->stdelog->write(3, $keyword_old_compare, 'actionMassGenerateURL() : $keyword_old_compare');
								$this->stdelog->write(3, $keyword_new_compare, 'actionMassGenerateURL() : $keyword_new_compare');

								if ($keyword_old_compare != $keyword_new_compare) {
									$this->model_extension_module_seo_url_generator->deleteURL($query_key, $essence_id, $store['store_id'], $language['language_id']);
									$this->model_extension_module_seo_url_generator->setURL($query_key, $a_data['essence_id'], $keyword_new, $store['store_id'], $language['language_id']);
									$this->model_extension_module_seo_url_generator->setRedirect($keyword_new, $keyword_old, $query_key, $a_data['essence_id'], $store['store_id'], $language['language_id']);
								}
							}
						}
					}
					// Stores & Languages Itterations . End
					
				} else {
					$this->stdelog->write(3, 'actionMassGenerateURL() : Nothing to do. This essence already has SEO URL and no reason to replace . ');
					
				}
				
			}
		} else {
			$this->stdelog->write(1, 'actionMassGenerateURL() : count($essence_list) < 0');
			
		}

		if (!isset($json['error'])) {
			// success

			if ($step == $steps_all) {
				// todo...
				// $json['answer'] = $this->language->get('success_item_step_finish'); - поставить финальную запись
				$json['answer'] = sprintf($this->language->get('answer_step_item_success'), $step, $steps_all);

				// $this->cache->delete('seo_pro');

				$this->cache->delete($essence . '.seopath');
			} else {
				$json['answer'] = sprintf($this->language->get('answer_step_item_success'), $step, $steps_all);
			}
			
		} else {
			// error
			// todo - !!
			$this->stdelog->write(1, 'actionMassGenerateURL() : error marker is present');
			
		}

		block_finish:

		$json['step'] = $step ++;
		$json['steps_all'] = $steps_all;

		$this->stdelog->write(3, $json, 'actionMassGenerateURL() : $json before json_encode()');

		$this->response->addHeader('Content-type: application/json; charset=UTF-8');
		$this->response->setOutput(json_encode($json));
	}
}