<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class ControllerExtensionModuleBuyoneclickCheckoutBuyoneclick extends Controller {
	private function replace_shablon($string,$shop_name,$product,$name,$phone,$email,$order_number){
		$patterns[0] = "/\{shop_name\}/";
		$patterns[1] = "/\{product\}/";
		$patterns[2] = "/\{name\}/";
		$patterns[3] = "/\{phone\}/";
		$patterns[4] = "/\{email\}/";
		$patterns[5] = "/\{order_number\}/";
		$replacements[0] = $shop_name;
		$replacements[1] = $product;
		$replacements[2] = $name;
		$replacements[3] = $phone;
		$replacements[4] = $email;
		$replacements[5] = $order_number;
		return preg_replace($patterns, $replacements, $string);
	}

	public function send_smsc($login, $password, $number, $message, $sender, $query = ''){
		$res = $this->_read_url('https://smsc.ua/sys/send.php?login='.urlencode($login).'&psw='.urlencode(html_entity_decode($password)).
								'&phones='.urlencode($number).'&mes='.urlencode(html_entity_decode(str_replace('\n', "\n", $message), ENT_QUOTES, 'UTF-8')).
								($sender ? '&sender='.urlencode($sender) : '').'&maxsms='.$this->config->get('oc_smsc_maxsms').
								'&cost=3&fmt=1&charset=utf-8&userip='.$_SERVER['REMOTE_ADDR'].($query ? '&'.$query : ''));
	}

	// Функция чтения URL. Для работы должно быть доступно:
	// curl или fsockopen (только http) или включена опция allow_url_fopen для file_get_contents

	private function _read_url($url){
		$ret = "";

		if (function_exists("curl_init"))
		{
			static $c = 0; // keepalive

			if (!$c) {
				$c = curl_init();
				curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 10);
				curl_setopt($c, CURLOPT_TIMEOUT, 10);
				curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
			}

			curl_setopt($c, CURLOPT_URL, $url);

			$ret = curl_exec($c);
		}
		elseif (function_exists("fsockopen") && strncmp($url, 'http:', 5) == 0) // not https
		{
			$m = parse_url($url);

			$fp = fsockopen($m["host"], 80, $errno, $errstr, 10);

			if ($fp) {
				fwrite($fp, "GET $m[path]?$m[query] HTTP/1.1\r\nHost: smsc.ua\r\nUser-Agent: PHP\r\nConnection: Close\r\n\r\n");

				while (!feof($fp))
					$ret = fgets($fp, 1024);

				fclose($fp);
			}
		}
		else
			$ret = file_get_contents($url);

		return $ret;
	}	

	private function send_sms($product, $name, $phone, $email, $order_number) {
		$shop_name = $this->config->get('config_name');
		$buyoneclick = $this->config->get('buyoneclick');

		// SMSC.ua
		$smscua_admin_status = $buyoneclick['smscua_admin_status'];
		$smscua_client_status = $buyoneclick['smscua_client_status'];
		$smscua_login = $buyoneclick['smscua_login'];
		$smscua_password = $buyoneclick['smscua_password'];
		$smscua_number = $buyoneclick['smscua_number'];
		$smscua_name = $buyoneclick['smscua_name'];
		$smscua_admin_sms = $buyoneclick['smscua_admin_sms'];
		$smscua_client_sms = $buyoneclick['smscua_client_sms'];

		//SMS.ru
		$smsru_admin_status = $buyoneclick['smsru_admin_status'];
		$smsru_client_status = $buyoneclick['smsru_client_status'];
		$smsru_api = $buyoneclick['smsru_api'];
		$smsru_login = $buyoneclick['smsru_login'];
		$smsru_password = $buyoneclick['smsru_password'];
		$smsru_number = $buyoneclick['smsru_number'];
		$smsru_name = $buyoneclick['smsru_name'];
		$smsru_admin_sms = $buyoneclick['smsru_admin_sms'];
		$smsru_client_sms = $buyoneclick['smsru_client_sms'];
		$shop_name = mb_convert_encoding($shop_name, 'UTF-8', mb_detect_encoding($shop_name));

		/*********** SMS клиенту **************/
		$client_sms = "Thank you for your order in our online store '$shop_name'! Your order number: $order_number. We will contact you to confirm the order!";
		/*********** SMS админу **************/
		$admin_sms = "Order $order_number: $product. Customer: $name $phone $email";

		// SMSC.ua
		if ($smscua_admin_status == '1' || $smscua_client_status == '1') {
			/*********** SMSC.ua клиенту **************/
			if ($smscua_client_sms != '') {
				$client_sms = $this->replace_shablon($smscua_client_sms,$shop_name,$product,$name,$phone,$email,$order_number);
			}
			/*********** SMSC.ua админу **************/
			if ($smscua_admin_sms != '') {
				$admin_sms = $this->replace_shablon($smscua_admin_sms,$shop_name,$product,$name,$phone,$email,$order_number);
			}
			try {
				//отправка SMS админу
				if ($smscua_admin_status == '1') {
					$smscua_sender = mb_convert_encoding($smscua_name, 'UTF-8', mb_detect_encoding($smscua_name));
					$smsc_result = $this->send_smsc($smscua_login, $smscua_password, $smscua_number, $admin_sms, $smscua_sender);
					// var_dump($smsc_result);
				}				
				//отправка SMS покупателю
				if ($smscua_client_status == '1') {
					$client_number = '+';
					$client_number .= preg_replace('/[^0-9]+/', '', $phone);
					$smscua_sender = mb_convert_encoding($smscua_name, 'UTF-8', mb_detect_encoding($smscua_name));
					$smsc_result = $this->send_smsc($smscua_login, $smscua_password, $client_number, $client_sms, $smscua_sender);
					// var_dump($smsc_result);
				}

			} catch(Exception $e) {
				/* $message_to_myemail .= 'Ошибка: ' . $e->getMessage() . '<br />'; */
			}
		}
		if ($smsru_admin_status == '1' || $smsru_client_status == '1') {
			/*********** SMS.ru клиенту **************/
			if ($smsru_client_sms != '') {
				$client_sms = $this->replace_shablon($smsru_client_sms,$shop_name,$product,$name,$phone,$email,$order_number);
			}
			/*********** SMS.ru админу **************/
			if ($smsru_admin_sms != '') {
				$admin_sms = $this->replace_shablon($smsru_admin_sms,$shop_name,$product,$name,$phone,$email,$order_number);
			}
			$smsru_number = preg_replace('/[^0-9]+/', '', $smsru_number);
			$admin_sms = mb_convert_encoding($admin_sms, 'UTF-8', mb_detect_encoding($admin_sms));
			if ($smsru_name != '') {
				$sms_sender = $smsru_name;
				$sms_sender = mb_convert_encoding($sms_sender, 'UTF-8', mb_detect_encoding($sms_sender));
				$sending_string1 = 'https://sms.ru/sms/send?api_id=' . $smsru_api . '&to=' . $smsru_number . '&text=' .  urlencode($admin_sms) . '&from=' . $sms_sender . '&partner_id=188307';
			} else {
				$sending_string1 = 'https://sms.ru/sms/send?api_id=' . $smsru_api . '&to=' . $smsru_number . '&text=' .  urlencode($admin_sms) . '&partner_id=188307';
			}
			$body=file_get_contents($sending_string1);
			list($code,$text) = explode("\n", $body);
			//отправка SMS покупателю
			if ($smsru_client_status == '1') {
				$client_number = preg_replace('/[^0-9]+/', '', $phone);
				$client_sms = mb_convert_encoding($client_sms, 'UTF-8', mb_detect_encoding($client_sms));
				if (isset($smsru_name)) {
					$sms_sender = $smsru_name;
					$sms_sender = mb_convert_encoding($sms_sender, 'UTF-8', mb_detect_encoding($sms_sender));
					$sending_string2 = 'https://sms.ru/sms/send?api_id=' . $smsru_api . '&to=' . $client_number . '&text=' . urlencode($client_sms) . '&from=' . $sms_sender . '&partner_id=188307';
				} else {
					$sending_string2 = 'https://sms.ru/sms/send?api_id=' . $smsru_api . '&to=' . $client_number . '&text=' . urlencode($client_sms) . '&partner_id=188307';
				}
				$body=file_get_contents($sending_string2);
				list($code,$text) = explode("\n", $body);
			}
		}
	}

	public function submit() {
		$buyoneclick = $this->config->get('buyoneclick');
		$buyoneclick_exan_status = $buyoneclick['exan_status'];
		$buyoneclick_success_type = $buyoneclick['success_type'];

		$data['logged'] = $this->customer->isLogged();
		// var_dump($data['logged']);
		if ($data['logged']) {
			$this->load->model('account/customer');
			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
		}

		$this->load->language('extension/module/buyoneclick');
		$json = array();
		if (!empty($this->session->data['boc_product_id'])) {
			$product_id = (int)$this->session->data['boc_product_id'];
		} else if (isset($this->request->post['boc_product_id'])) {
			$product_id = (int)$this->request->post['boc_product_id'];
		} else {
			$product_id = 0;
		}

		$boc_title = $this->language->get('buyoneclick_title');

		if ($data['logged']) {
			if (isset($this->request->post['boc_name']) && $this->request->post['boc_name'] != '') {
				$fullname = $this->request->post['boc_name'];
				$data = preg_split('/\s+/', $fullname);
				if (count($data) > 1){
					$firstname = $data[0];
					$lastname = $data[1];
				} else {
					$firstname = $data[0];
					$lastname = '(' . $boc_title . ')';
				}
			} else {
				$firstname = $customer_info['firstname'];
				$lastname = $customer_info['lastname'];	
			}			

			if (isset($this->request->post['boc_email']) && $this->request->post['boc_email'] !='') {
				$email = $this->request->post['boc_email'];
			} else {
				$email = $customer_info['email'];
			}
			if (isset($this->request->post['boc_phone'])) {
				$phone = $this->request->post['boc_phone'];
			} else {
				$phone = $customer_info['telephone'];
			}	
			if (isset($customer_info['fax'])) {
				$fax = $customer_info['fax'];
			} else {
				$fax = '';
			}

			// Customer ID
			if (isset($customer_info['customer_id'])) {
				$customer_id = $customer_info['customer_id'];
			} else {
				$customer_id = '';
			}

			// Customer Group
			if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
				$customer_group_id = $this->request->post['customer_group_id'];
			} elseif (isset($customer_info['customer_group_id']) && $customer_info['customer_group_id'] !='') {
				$customer_group_id = $customer_info['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
		} else {
			if (isset($this->request->post['boc_name']) && $this->request->post['boc_name'] != '') {
				$fullname = $this->request->post['boc_name'];
				$data = preg_split('/\s+/', $fullname);
				if (count($data) > 1){
					$firstname = $data[0];
					$lastname = $data[1];
				} else {
					$firstname = $data[0];
					$lastname = '(' . $boc_title . ')';
				}
			} else {
				$firstname = $boc_title;
				$lastname = '';
			}

			if (isset($this->request->post['boc_email']) && $this->request->post['boc_email'] !='') {
				$email = $this->request->post['boc_email'];
			} else {
				$email = '';
			}
			if (isset($this->request->post['boc_phone'])) {
				$phone = $this->request->post['boc_phone'];
			} else {
				$phone = '';
			}	
			$fax = '';

			// Customer ID
			$customer_id = '';

			// Customer Group
			if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
				$customer_group_id = $this->request->post['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}

			$this->session->data['guest']['customer_group_id'] = $customer_group_id;
			$this->session->data['guest']['firstname'] = $firstname;
			$this->session->data['guest']['lastname'] = $lastname;
			$this->session->data['guest']['email'] = $email;
			$this->session->data['guest']['telephone'] = $phone;
			$this->session->data['guest']['fax'] = '';			
		}

		if (isset($this->request->post['boc_message'])) {
			$comment = $this->request->post['boc_message'];
		} else {
			$comment = '';
		}
		
		$this->load->model('catalog/product');
		$product_info = $this->model_catalog_product->getProduct($product_id);
		if ($product_info) {
			if (!empty($this->session->data['boc_product_quantity'])) {
				$product_quantity = (int)$this->session->data['boc_product_quantity'];
			} else {
				$product_quantity = $product_info['minimum'] ? $product_info['minimum'] : 1;
			}
			if (!empty($this->session->data['boc_product_option'])) {
				$product_option = $this->session->data['boc_product_option'];
			} else {
				$product_option = array();
			}

            if (!$json) {

				// Display prices
				$boc_price = (float)$product_info['price'];
				if ((float)$product_info['special']) {
					$boc_price = (float)$product_info['special'];
				}

				$discounts = $this->model_catalog_product->getProductDiscounts($product_id);
				if ($discounts) {
					foreach ($discounts as $discount) {
						if ($discount['quantity'] <= $product_quantity) {
							$boc_price = (float)$discount['price'];
						}
					}
				}

				// Calculate total
				if (!$product_option) {
					$boc_unit_price = $boc_price;
					$boc_total = $boc_price * $product_quantity;
					$boc_unit_tax = $this->tax->getTax($boc_price, $product_info['tax_class_id']);
				} else {
					$option_total = 0;
					foreach ($product_option as $option) {
						if ($option['value_price_prefix'] == '+') {
							$option_total += (float)$option['value_price_value'];
						} else if ($option['value_price_prefix'] == '-') {
							$option_total -= (float)$option['value_price_value'];
						}
					}
					$boc_unit_price = $boc_price + $option_total;
					$boc_total = ($boc_price + $option_total) * $product_quantity;
					$boc_unit_tax = $this->tax->getTax(($boc_price + $option_total), $product_info['tax_class_id']);
				}
				$boc_tax_total = $boc_unit_tax * $product_quantity;
				$boc_order_total = $boc_total + $boc_tax_total;

				// $order_data['products'] = array();
				$order_data['products'][] = array (
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name'],
					'model'      => $product_info['model'],
					'option'     => $product_option,
					'download'   => '',
					'quantity'   => $product_quantity,
					'subtract'   => $product_info['subtract'],
					'price'      => $boc_unit_price,
					'total'      => $boc_total,
					'tax'        => $boc_unit_tax,
					'reward'     => $product_info['reward']
				);

				$order_data['totals'] = array();

				$this->load->language('checkout/checkout');
				$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
				$order_data['store_id'] = $this->config->get('config_store_id');
				$order_data['store_name'] = $this->config->get('config_name');
				if ($order_data['store_id']) {
					$order_data['store_url'] = $this->config->get('config_url');
				} else {
					$order_data['store_url'] = HTTP_SERVER;
				}
				
				$exan = '';
				if ($buyoneclick_exan_status) {
					// Source first visit
					$exan = $this->language->get('buyoneclick_exan_title');
					$exan .= $this->language->get('sb_first_visit_title') . "<br />";
					if (isset($this->request->post['sb_first_typ']) && $this->request->post['sb_first_typ'] != '') {
						$sb_first_typ = $this->request->post['sb_first_typ'];
						$exan .= $this->language->get('sb_first_typ') . $sb_first_typ . "<br />";
					}
					if (isset($this->request->post['sb_first_src']) && $this->request->post['sb_first_src'] != '') {
						$sb_first_src = $this->request->post['sb_first_src'];
						$exan .= $this->language->get('sb_first_src') . $sb_first_src . "<br />";
					}
					if (isset($this->request->post['sb_first_mdm']) && $this->request->post['sb_first_mdm'] != '') {
						$sb_first_mdm = $this->request->post['sb_first_mdm'];
						$exan .= $this->language->get('sb_first_mdm') . $sb_first_mdm . "<br />";
					}
					if (isset($this->request->post['sb_first_cmp']) && $this->request->post['sb_first_cmp'] != '') {
						$sb_first_cmp = $this->request->post['sb_first_cmp'];
						$exan .= $this->language->get('sb_first_cmp') . $sb_first_cmp . "<br />";
					}
					if (isset($this->request->post['sb_first_cnt']) && $this->request->post['sb_first_cnt'] != '') {
						$sb_first_cnt = $this->request->post['sb_first_cnt'];
						$exan .= $this->language->get('sb_first_cnt') . $sb_first_cnt . "<br />";
					}
					if (isset($this->request->post['sb_first_trm']) && $this->request->post['sb_first_trm'] != '') {
						$sb_first_trm = $this->request->post['sb_first_trm'];
						$exan .= $this->language->get('sb_first_trm') . $sb_first_trm . "<br />";
					}
					if (isset($this->request->post['sb_first_add_fd']) && $this->request->post['sb_first_add_fd'] != '') {
						$sb_first_add_fd = $this->request->post['sb_first_add_fd'];
						$exan .= $this->language->get('sb_first_add_fd') . $sb_first_add_fd . "<br />";
					}
					if (isset($this->request->post['sb_first_add_ep']) && $this->request->post['sb_first_add_ep'] != '') {
						$sb_first_add_ep = $this->request->post['sb_first_add_ep'];
						$exan .= $this->language->get('sb_first_add_ep') . $sb_first_add_ep . "<br />";
					}
					if (isset($this->request->post['sb_first_add_rf']) && $this->request->post['sb_first_add_rf'] != '') {
						$sb_first_add_rf = $this->request->post['sb_first_add_rf'];
						$exan .= $this->language->get('sb_first_add_rf') . $sb_first_add_rf . "<br />";
					}
					// Source first visit end

					// Source current visit
					$exan .= "<br />" . $this->language->get('sb_current_visit_title') . "<br />";
					if (isset($this->request->post['sb_current_typ']) && $this->request->post['sb_current_typ'] != '') {
						$sb_current_typ = $this->request->post['sb_current_typ'];
						$exan .= $this->language->get('sb_current_typ') . $sb_current_typ . "<br />";
					}
					if (isset($this->request->post['sb_current_src']) && $this->request->post['sb_current_src'] != '') {
						$sb_current_src = $this->request->post['sb_current_src'];
						$exan .= $this->language->get('sb_current_src') . $sb_current_src . "<br />";
					}
					if (isset($this->request->post['sb_current_mdm']) && $this->request->post['sb_current_mdm'] != '') {
						$sb_current_mdm = $this->request->post['sb_current_mdm'];
						$exan .= $this->language->get('sb_current_mdm') . $sb_current_mdm . "<br />";
					}
					if (isset($this->request->post['sb_current_cmp']) && $this->request->post['sb_current_cmp'] != '') {
						$sb_current_cmp = $this->request->post['sb_current_cmp'];
						$exan .= $this->language->get('sb_current_cmp') . $sb_current_cmp . "<br />";
					}
					if (isset($this->request->post['sb_current_cnt']) && $this->request->post['sb_current_cnt'] != '') {
						$sb_current_cnt = $this->request->post['sb_current_cnt'];
						$exan .= $this->language->get('sb_current_cnt') . $sb_current_cnt . "<br />";
					}
					if (isset($this->request->post['sb_current_trm']) && $this->request->post['sb_current_trm'] != '') {
						$sb_current_trm = $this->request->post['sb_current_trm'];
						$exan .= $this->language->get('sb_current_trm') . $sb_current_trm . "<br />";
					}
					if (isset($this->request->post['sb_current_add_fd']) && $this->request->post['sb_current_add_fd'] != '') {
						$sb_current_add_fd = $this->request->post['sb_current_add_fd'];
						$exan .= $this->language->get('sb_current_add_fd') . $sb_current_add_fd . "<br />";
					}
					if (isset($this->request->post['sb_current_add_ep']) && $this->request->post['sb_current_add_ep'] != '') {
						$sb_current_add_ep = $this->request->post['sb_current_add_ep'];
						$exan .= $this->language->get('sb_current_add_ep') . $sb_current_add_ep . "<br />";
					}
					if (isset($this->request->post['sb_current_add_rf']) && $this->request->post['sb_current_add_rf'] != '') {
						$sb_current_add_rf = $this->request->post['sb_current_add_rf'];
						$exan .= $this->language->get('sb_current_add_rf') . $sb_current_add_rf . "<br />";
					}
					// Source current visit end

					// Current session
					$exan .= "<br />" . $this->language->get('sb_session_title') . "<br />";
					if (isset($this->request->post['sb_session_pgs']) && $this->request->post['sb_session_pgs'] != '') {
						$sb_session_pgs = $this->request->post['sb_session_pgs'];
						$exan .= $this->language->get('sb_session_pgs') . $sb_session_pgs . "<br />";
					}
					if (isset($this->request->post['sb_session_cpg']) && $this->request->post['sb_session_cpg'] != '') {
						$sb_session_cpg = $this->request->post['sb_session_cpg'];
						$exan .= $this->language->get('sb_session_cpg') . $sb_session_cpg . "<br />";
					}
					// Current session end

					// Private data
					$exan .= "<br />" . $this->language->get('sb_private_title') . "<br />";
					if (isset($this->request->post['sb_udata_vst']) && $this->request->post['sb_udata_vst'] != '') {
						$sb_udata_vst = $this->request->post['sb_udata_vst'];
						$exan .= $this->language->get('sb_udata_vst') . $sb_udata_vst . "<br />";
					}
					if (isset($this->request->post['sb_udata_uip']) && $this->request->post['sb_udata_uip'] != '') {
						$sb_udata_uip = $this->request->post['sb_udata_uip'];
						$exan .= $this->language->get('sb_udata_uip') . $this->request->server['REMOTE_ADDR'] . "<br />";
					}
					if (isset($this->request->post['sb_udata_uag']) && $this->request->post['sb_udata_uag'] != '') {
						$sb_udata_uag = $this->request->post['sb_udata_uag'];
						$exan .= $this->language->get('sb_udata_uag') . $sb_udata_uag . "<br />";
					}
					if (isset($this->request->post['sb_promo_code']) && $this->request->post['sb_promo_code'] != '') {
						$sb_promo_code = $this->request->post['sb_promo_code'];
						$exan .= $this->language->get('sb_promo_code') . $sb_promo_code . "<br />";
					}
					// Private data end		
				}					
				
				$order_data['customer_id'] = $customer_id;
				$order_data['customer_group_id'] = $customer_group_id;
				$order_data['firstname'] = $firstname;
				$order_data['lastname'] = $lastname;
				$order_data['email'] = $email;
				$order_data['telephone'] = $phone;
				$order_data['fax'] = $fax;
				$order_data['custom_field'] = array();
				$order_data['payment_firstname'] = $firstname;
				$order_data['payment_lastname'] = $lastname;
				$order_data['payment_company'] = '';
				$order_data['payment_address_1'] = $boc_title;
				$order_data['payment_address_2'] = '';
				$order_data['payment_city'] = '';
				$order_data['payment_postcode'] = '';
				$order_data['payment_zone'] = '';
				$order_data['payment_zone_id'] = '';
				$order_data['payment_country'] = '';
				$order_data['payment_country_id'] = '';
				$order_data['payment_address_format'] = '';
				$order_data['payment_custom_field'] = array();
				$order_data['payment_method'] = $boc_title;
				$order_data['payment_code'] = 'cod';
				$order_data['shipping_firstname'] = $firstname;
				$order_data['shipping_lastname'] = $lastname;
				$order_data['shipping_company'] = '';
				$order_data['shipping_address_1'] = $boc_title;
				$order_data['shipping_address_2'] = '';
				$order_data['shipping_city'] = '';
				$order_data['shipping_postcode'] = '';
				$order_data['shipping_zone'] = '';
				$order_data['shipping_zone_id'] = '';
				$order_data['shipping_country'] = '';
				$order_data['shipping_country_id'] = '';
				$order_data['shipping_address_format'] = '';
				$order_data['shipping_custom_field'] = array();
				$order_data['shipping_method'] = '';
				$order_data['shipping_code'] = '';
				$order_data['comment'] = $comment;
				$order_data['total'] = $boc_order_total;
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
				$order_data['marketing_id'] = 0;
				$order_data['tracking'] = '';
				$order_data['language_id'] = $this->config->get('config_language_id');
				$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
				$order_data['currency_code'] = $this->session->data['currency'];
				$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
				$order_data['ip'] = $this->request->server['REMOTE_ADDR'];
				if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
					$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
				} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
					$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
				} else {
					$order_data['forwarded_ip'] = '';
				}
				if (isset($this->request->server['HTTP_USER_AGENT'])) {
					$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
				} else {
					$order_data['user_agent'] = '';
				}
				if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
					$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
				} else {
					$order_data['accept_language'] = '';
				}				
				$this->load->model('extension/module/bocorder');
				$data['order_id'] = $this->model_extension_module_bocorder->addBocorder($order_data);
				if (empty($data['order_id'])) {
					$json['error']['order'] = $this->language->get('error_order');
				} else {
					$this->session->data['order_id'] = $data['order_id'];
					$this->model_extension_module_bocorder->addBocorderHistory($data['order_id'], $this->config->get('config_order_status_id'), $exan);
					$json['success'] = sprintf($this->language->get('text_success'), $data['order_id'], $phone);
					if ($buyoneclick_success_type == '1') {
						$json['redirect'] = $this->url->link('checkout/success', '', 'SSL');
					}
					$this->send_sms($product_info['name'], $firstname, $phone, $email, $data['order_id']);
				}
			} else {
				$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
			}
		} else {
			$json['error']['product'] = $this->language->get('error_product');
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}