<?php

require_once(DIR_SYSTEM . "/engine/neoseo_controller.php");

class ControllerApiOrderHistory extends NeoSeoController
{

	public function __construct($registry)
	{
		parent::__construct($registry);
		$this->_sysModuleName = "neoseo_exchange1c";
		$this->_module_code = "neoseo_exchange1c";
		$this->_logFile = $this->_moduleSysName() . ".log";
		$this->debug = $this->config->get($this->_moduleSysName() . "_debug") == 1;
	}

	public function index()
	{
		$this->load->language('api/order');

		// Проверка IP-адреса
		$ipList = $this->config->get("neoseo_exchange1c_ip_list");
		if (trim($ipList) != "") {
			$found = false;
			foreach (explode("\n", $ipList) as $ip) {
				if (trim($ip) == $_SERVER['REMOTE_ADDR']) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$this->log('Несанкционированное обращение с адреса: ' . $_SERVER['REMOTE_ADDR']);
				return;
			}
		}

		if (!isset($this->request->post['order_id'])) {
			$this->log('Не указан order_id');
			return;
		}
		$order_id = $this->request->post['order_id'];

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
		if (!$order_info) {
			$this->log('Не найден заказ с номером ' . $order_id);
			return;
		}

		$this->log("Устанавливаем заказу с номером $order_id новые параметры: " . print_r($this->request->post, true));
		$this->load->model('checkout/order');
		$this->model_checkout_order->addOrderHistory(
		    $order_id, $this->request->post['order_status_id'], $this->request->post['comment'], $this->request->post['notify'], $this->request->post['override']
		);

		return;
	}

	public function download(){
		$filename = isset($this->request->get['code']) ? trim($this->request->get['code']) : '';

		if (empty($filename)) {
			exit('Error: Could not find file');
		}
		$name = $filename;

		$filename_chunks = explode('.',$filename);
		if(count($filename_chunks) && isset($filename_chunks[0]) && isset($filename_chunks[1])){
			$name = $filename_chunks[0] . '.' . $filename_chunks[1];
		}

		if ($filename) {
			$content = $this->download_from_server($filename);

			if (!headers_sent()) {
				header('Content-Type: application/octet-stream');
				header('Content-Description: File Transfer');
				header('Content-Disposition: attachment; filename=' . $name);
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' . strlen($content));
				echo $content;
				exit;
			} else {
				exit('Error: Headers already sent out');
			}
		} else {
			exit('Error: Could not find file');
		}

	}

	private function download_from_server($filename) {
		$path = ($this->getOpencartVersion() < 200 ? DIR_DOWNLOAD : DIR_UPLOAD) . $this->mb_basename($filename);

		if (@file_exists($path)) {
			return file_get_contents($path);
		} else {
			exit('Error: Could not find file 1');
		}

		return '';
	}

	private function getOpencartVersion() {
		$opencartVersion = explode('.', VERSION);
		return floatval($opencartVersion[0].$opencartVersion[1].$opencartVersion[2].'.'.(isset($opencartVersion[3]) ? $opencartVersion[3] : 0));
	}

	private function mb_basename($path) {
		if (preg_match('@^.*[\\\\/]([^\\\\/]+)$@s', $path, $matches)) {
			return $matches[1];
		} else if (preg_match('@^([^\\\\/]+)$@s', $path, $matches)) {
			return $matches[1];
		}
		return '';
	}

}
