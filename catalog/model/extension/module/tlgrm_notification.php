<?php
/*
@author	Artem Serbulenko
@link	http://cmsshop.com.ua
@link	https://opencartforum.com/profile/762296-bn174uk/
@email 	serfbots@gmail.com
*/  
class ModelExtensionModuleTlgrmNotification extends Model { 
 	
 	public function SendMessage($message) {
		
		$this->load->language('extension/module/tlgrm_notification');
		
        $chat_ids = $this->config->get('module_tlgrm_notification_id');

        $chat_ids = explode(",", $chat_ids);

        $message = strip_tags($message, '<b><a><i>');        

        if (strlen($message) > 4090) {
        	$message = $this->language->get('text_max_length');
    	}
       	
        foreach ($chat_ids as $chat_id) {
        	$this->sendNotification($message, $chat_id);      		
        }
    }

    public function sendNotification($message, $chat_id) {
    	$link = 'https://api.telegram.org/bot';
    
        $bot_token = $this->config->get('module_tlgrm_notification_token');
        $sendToTelegram = $link . $bot_token;

        $chat_id = trim($chat_id);
        $message = strip_tags($message, '<b><a><i>');        

		$params = array(
		    'chat_id' => $chat_id,
		    'text' => $message,
		    'parse_mode' =>'html'
		);

		$ch = curl_init($sendToTelegram . '/sendMessage');
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		if ($this->config->get('module_tlgrm_notification_log')) {
			$log = new Log('telegram_notification.log');
			$log->write($chat_id . ' - ' . $result);
		}
		curl_close($ch);
    }

    public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}
	
	public function getOrderStatus($order_status_id) {
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "'");

		return $query->row;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getOrderCustomerGroup($order_id) {
		$query = $this->db->query("SELECT cgd.name FROM " . DB_PREFIX . "order AS o JOIN " . DB_PREFIX . "customer_group_description AS cgd ON (o.customer_group_id = cgd.customer_group_id) WHERE o.order_id = '" . (int)$order_id . "' AND cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		if ($query->row) {
			return $query->row['name'];
		}
	}

	public function getOrderSimpleFields($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_simple_fields WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->row;
	}

	public function getReturn($return_id) {
		$query = $this->db->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, r.email, r.telephone, r.product, r.model, r.quantity, r.opened, (SELECT rr.name FROM " . DB_PREFIX . "return_reason rr WHERE rr.return_reason_id = r.return_reason_id AND rr.language_id = '" . (int)$this->config->get('config_language_id') . "') AS reason, r.comment, r.date_ordered FROM `" . DB_PREFIX . "return` r WHERE r.return_id = '" . (int)$return_id . "'");

		return $query->row;
	}
}


