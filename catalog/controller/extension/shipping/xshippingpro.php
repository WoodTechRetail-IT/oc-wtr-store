<?php
class ControllerExtensionShippingXshippingpro extends Controller {
    private $ext_path;
    private $mtype;
    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry = $registry;
        $this->ocm = ($ocm = $this->registry->get('ocm_front')) ? $ocm : new OCM\Front($this->registry);
        $this->mtype = 'shipping';
        $this->ext_path = 'extension/shipping/xshippingpro';
    }
    public function listenQuote($route, $data, &$method) {
        if ($method) {
            $code = $method['code'];
            $quote = $method['quote'];
            if (isset($quote) && is_array($quote)) {
                $ocm_shipping = $this->ocm->getCache('ocm_shipping');
                if (!$ocm_shipping) $ocm_shipping = array();
                $ocm_shipping[$code] = $quote;
                $this->ocm->setCache('ocm_shipping', $ocm_shipping);
            }
            // hide method if it is found in hidden list
            $ocm_shipping_hide = $this->ocm->getCache('ocm_shipping_hide');
            if (!$ocm_shipping_hide) $ocm_shipping_hide = array();
            if ($ocm_shipping_hide && in_array($code, $ocm_shipping_hide)) {
                $method = array();
            }
        }
    }
    public function onOrderEmail($route, &$data) {
        $xshippingpro_desc_mail = $this->ocm->getConfig('xshippingpro_desc_mail', $this->mtype);
        if ($xshippingpro_desc_mail) {
            $order_info = $this->model_checkout_order->getOrder($data['order_id']);
            $language_id = $order_info['language_id'];
            if (strpos($order_info['shipping_code'], 'xshippingpro') !== false) {
                $this->load->model($this->ext_path);
                $this->load->language($this->ext_path);
                $tab_id = str_replace('xshippingpro.xshippingpro', '', $order_info['shipping_code']);
                $desc_logo =  $this->model_extension_shipping_xshippingpro->getShippingDesc();
                if ($desc_logo && $desc_logo['desc'] && isset($desc_logo['desc'][$tab_id])) {
                    $desc = $this->model_extension_shipping_xshippingpro->getFinalDesc($desc_logo['desc'][$tab_id]);
                    if ($desc) {
                        $data['shipping_method'] .= '<br /><span style="color: #999999;font-size: 11px;display:block" class="x-shipping-desc">' . $desc . '</span>';
                    }
                }
            }
        }
    }
    public function estimate_shipping() {
        $json=array();
        $this->load->model($this->ext_path);
        $this->load->language($this->ext_path);
        $xshippingpro_estimator =  $this->ocm->getConfig('xshippingpro_estimator', $this->mtype);
        $estimator_type = (isset($xshippingpro_estimator['type']) && $xshippingpro_estimator['type']) ? $xshippingpro_estimator['type'] : 'method';
        $address = array();
        if ($estimator_type == 'avail') {
            $address = array('only_address_rule' => true);
        }
        $json =  $this->model_extension_shipping_xshippingpro->getQuote($address);
        if ($estimator_type == 'avail') {
            if ($json) {
                $json = array();
                $json['message'] = $this->language->get('xshippingpro_available');
                $json['class'] = 'avail';
            } else {
                $json = array();
                $json['message'] = $this->language->get('xshippingpro_no_available');
                $json['class'] = 'no_avail';
            }
        }
        $desc_logo =  $this->model_extension_shipping_xshippingpro->getShippingDesc();
        if ($desc_logo && $desc_logo['desc'] && $json && isset($json['quote']) && $json['quote']) {
            foreach ($json['quote'] as &$quote) {
                $tab_id = $quote['tab_id'];
                if (isset($desc_logo['desc'][$tab_id])) {
                    $desc = $this->model_extension_shipping_xshippingpro->getFinalDesc($desc_logo['desc'][$tab_id]);
                } else {
                    $desc = '';
                }
                $quote['desc'] = $desc;
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    public function update_shipping() {
        $json = array();
        $json['success'] = false;
        $this->load->model($this->ext_path);
        if (!empty($this->request->post['xshippingpro_code'])) {
            $json['success'] = $this->model_extension_shipping_xshippingpro->setSubOptions($this->request->post['xshippingpro_code']);
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    /* admin-end */
    public function getSubOptions() {
        $json = array();
        $this->load->model($this->ext_path);
        $json['sub_options'] = $this->model_extension_shipping_xshippingpro->getSubOptions(true);
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    /* Events for API call as OC 3.x does not allow to add files in api directory during intallation */
    public function on_update_sup_options(&$route, &$data) {
        $this->update_shipping();
        return false;
    }
    public function on_get_sup_options(&$route, &$data) {
        $this->getSubOptions();
        return false;
    }
}
