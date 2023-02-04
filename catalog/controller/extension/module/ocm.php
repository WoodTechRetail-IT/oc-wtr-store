<?php
/*
 author: opencartmart
*/
class ControllerExtensionModuleOcm extends Controller {
    private $shortcode_pattern = '/information\/information|product\/product|product\/category|product\/manufacturer_info|popup/';
    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry = $registry;
        $this->ocm = ($ocm = $this->registry->get('ocm_front')) ? $ocm : new OCM\Front($this->registry);
    }
    public function onViewAfter($route, $input, &$output) {
        if (preg_match('/(common|checkout)\/.?footer$/', $route)) {
            $output = $this->ocm->getScript(true) . $output;
        }
        else if (is_string($output) && preg_match($this->shortcode_pattern, $route)) {
            $this->ocm->applyShortcode($output);
        }
    }
    public function onProductAfter($route, $input, &$output) {
        if (is_array($output) && $output) {
            // price modificaiton 
            if ($this->ocmprice) {
                $keys = array('product_id', 'price', 'special');
                $is_row = $this->arrayKeyExists($output, $keys);
                $is_rows = !$is_row && $this->arrayKeyExists($output, $keys, true);
                if ($is_row) {
                    if (!isset($output['discount'])) $output['discount'] = 0;
                    $output['_ocm_'] = true;
                    $this->ocmprice->getDiscountedProduct(array('product' => &$output));
                } else if ($is_rows) {
                    foreach ($output as &$_output) {
                        if (isset($_output['_ocm_'])) continue;
                        if (!isset($_output['discount'])) $_output['discount'] = 0;
                        $_output['_ocm_'] = true;
                        $this->ocmprice->getDiscountedProduct(array('product' => &$_output));
                    }
                } else {
                    $keys = array('product_discount_id', 'product_id', 'price');
                    if ($this->arrayKeyExists($output, $keys, true)) {
                        foreach ($output as &$_output) {
                            $this->ocmprice->getQuantityDiscount(array('quantity' => &$_output));
                        }
                    }
                    // product options
                    $keys = array('product_option_id', 'product_option_value', 'option_id');
                    if ($this->arrayKeyExists($output, $keys, true)) {
                        if (VERSION >= '2.3.0.0') {
                            $product_id = isset($input[0]) ? $input[0] : 0;
                        } else {
                            $product_id = (int)$input;
                        }
                        foreach ($output as &$_output) {
                            if (is_array($_output['product_option_value']) && $_output['product_option_value']) {
                                foreach($_output['product_option_value'] as &$_option) {
                                    $_option['ocm_price'] = $_option['price'];
                                    $_option['ocm_line'] = false;
                                    $this->ocmprice->getOptionPrice(array(
                                        'price'         => &$_option['price'],
                                        'ocm_line'      => &$_option['ocm_line'],
                                        'ocm_price'     => &$_option['ocm_price'],
                                        'product_id'    => $product_id,
                                        'price_prefix'  => $_option['price_prefix']
                                    ));
                                }    
                            }
                        }
                    }
                }
            }
            // image modification
            if ($this->ocm->getConfig('restricted_status', 'module')) {
                $keys = array('product_id', 'image');
                $is_row = $this->arrayKeyExists($output, $keys);
                $is_rows = !$is_row && $this->arrayKeyExists($output, $keys, true);
                if ($is_row) {
                    $ext_key = $this->ocm->getModel('restricted', 'module');
                    $output['image'] = $this->{$ext_key}->applyMask($output['product_id'], $output['image']);
                } else if ($is_rows) {
                    $ext_key = $this->ocm->getModel('restricted', 'module');
                    foreach ($output as &$_output) {
                        $_output['image'] = $this->{$ext_key}->applyMask($_output['product_id'], $_output['image']);
                    }
                }
            }
        }
    }
    public function onOrderHistory(&$route, &$input) {
        $order_id = isset($input[0]) ? $input[0] : 0;
        $order_status_id = isset($input[1]) ? $input[1] : 0;
        $this->ocm->onOrderHistory($order_id, $order_status_id);
    }
    public function onExtensions($route, $input, &$output) {
        $type = is_array($input) ? $input[0] : $input;
        if ($output && is_array($output)) {
            if ($type === 'payment' || $type === 'shipping') {
                $_codes = array(
                    'pmm'          => false, 
                    'xshippingpro' => false,
                    'xpayment'     => false
                );
                foreach($output as $i => $row) {
                    if (isset($_codes[$row['code']])) {
                        $_codes[$row['code']] = $row;
                        unset($output[$i]);
                    }
                }
                foreach ($_codes as $code => $row) {
                    if ($row) {
                        $position = 'first';
                        if ($code === 'xshippingpro') {
                            $xshippingpro_position = $this->cache->get('ocm.xshippingpro_position');
                            $position = $xshippingpro_position ? $xshippingpro_position : 'first';
                        }
                        if ($position === 'first') {
                            array_unshift($output, $row);
                        } else {
                            array_push($output, $row);
                        }
                    }
                }
            }
        }
    }
    /* FB business extension */
    public function onFbProductsAfter($route, $input, &$output) {
        if (is_array($output) && $output) {
            if ($this->ocmprice) {
                foreach ($output as &$_output) {
                    if (isset($_output['_ocm_'])) continue;
                    if (!isset($_output['discount'])) $_output['discount'] = 0;
                    if (!isset($_output['special'])) $_output['special'] = 0;
                    $_output['_ocm_'] = true;
                    $this->ocmprice->getDiscountedProduct(array('product' => &$_output));
                }
            }
        }
    }
    /* Utility */
    private function arrayKeyExists($array, $keys, $multi = false) {
        $return = true;
        if ($multi) {
            foreach ($array as $_array) {
                break;
            }
        } else {
            $_array = $array;
        }
        if (is_array($_array)) {
            foreach ($keys as $key) {
                $return &= array_key_exists($key, $_array);
            }
        } else {
            $return = false;
        }
        return $return;
    }
}