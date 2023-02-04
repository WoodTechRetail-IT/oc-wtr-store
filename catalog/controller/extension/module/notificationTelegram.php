<?php
class ControllerExtensionModuleNotificationTelegram extends Controller {
        
	public function sendOrderAlert(&$route, &$data, &$output) {

		$order_id = $data[0];
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);

		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('module_notificationTelegram');

		if (isset($setting['module_notificationTelegram_order_alert'])) {
			
			$this->load->model('account/order');
			if (count($this->model_account_order->getOrderHistories($order_id)) <= 1) {
				$message = $this->replaceMessage($setting['module_notificationTelegram_meassage'],$order_info);
//                  $message .= $this->buldArray($order_info);

				$this->sendMessagetoTelegam($message);

				if (strpos(strtolower($setting['module_notificationTelegram_meassage']), '{products}') !== false) {
					$order_products = $this->model_checkout_order->getOrderProducts($order_id);
					$products = $this->bulidProducts($order_products);
					$this->sendMessagetoTelegam($products);

				}
			}
			
		}
		
	}
	
	public function sendAccountAlert(&$route, &$data, &$output) {
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('module_notificationTelegram');
		if (isset($setting['module_notificationTelegram_customer_alert'])) {

			$message = $this->replaceMessage($setting['module_notificationTelegram_new_account_meassage'],$data[0]);
			$this->sendMessagetoTelegam( $message);
			
		}
	}
	
	public function sendReturnProductAlert(&$data,&$output) {
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('module_notificationTelegram');
		if (isset($setting['module_notificationTelegram_return_alert'])) {
			
			$message = "Return request \n ";
			$this->sendMessagetoTelegam( $message);
		}
	}
	
	
	//Send  message To notificationTelegram
	public function sendMessagetoTelegam($msg) {
		
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('module_notificationTelegram');
		
		//print_r($setting);
		$botToken = $setting['module_notificationTelegram_boot_token'];
		$website = "https://api.telegram.org/bot" . $botToken;
		$chatIds = $setting['module_notificationTelegram_chat_ids'];  //Receiver Chat Id
		
		if (is_array($chatIds)) {
			foreach ($chatIds as $val) {
				$this->initMessage($botToken, $val, $msg);
			}
		} else {
			$this->initMessage($botToken, $chatIds, $msg);
		}
	}
	private function initMessage($botToken, $chatID, $msg) {
		
		$website = "https://api.telegram.org/bot" . $botToken;
		
		$params = [
			'chat_id' => $chatID,
			'text' => $msg,
		];
		$ch = curl_init($website . '/sendMessage');
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);            
	}
	
	public function buldArray($arr) {
		if (is_array($arr)) {
			$dataAttributes = array_map(function ($value, $key) {
				return @"$key ---> $value  \n";
			}, array_values($arr), array_keys($arr));
			
			return $dataAttributes = implode(' ', $dataAttributes);
		}
	}
	
	public function replaceMessage($string,$arr) {
		return   $str = preg_replace_callback('/{(\w+)}/', function($match) use($arr) {
			return $arr[$match[1]];
		}, $string );
		
	}
	
	protected function  bulidProducts($products){

		$pr = array();

		foreach ($products as $product){
			$pr[] = "Name : $product[name]  \n    Price: $product[price] \n qty : $product[quantity] ";
		}

		return implode("------- \n",$pr);
		
	}
		
}