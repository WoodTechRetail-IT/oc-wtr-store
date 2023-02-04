<?php 
class ModelExtensionShippingXshippingpro extends Model {
    use OCM\Traits\Front\Address;
    use OCM\Traits\Front\Common_params;
    use OCM\Traits\Front\Product;
    use OCM\Traits\Front\Validator;
    use OCM\Traits\Front\Range_price;
    use OCM\Traits\Front\Equation;
    use OCM\Traits\Front\Grouping;
    use OCM\Traits\Front\Distance;
    use OCM\Traits\Front\Shortcode;
    use OCM\Traits\Front\Crucify;
    use OCM\Traits\Front\Util;
    use OCM\Traits\Front\Event_hide;
    private $ext_path;
    private $mtype;
    private $mname;
    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry = $registry;
        $this->ocm = ($ocm = $this->registry->get('ocm_front')) ? $ocm : new OCM\Front($this->registry);
        $this->mtype = 'shipping';
        $this->mname = 'xshippingpro';
        $this->ext_path = 'extension/shipping/xshippingpro';
    }
    function getQuote($address) {
        $_start = microtime(true);
        $this->load->language($this->ext_path);
        $language_id = $this->config->get('config_language_id');

        $address = $this->_replenishAddress($address);
        $compare_with = $this->_getCommonParams($address);
        $only_address_rule = isset($address['only_address_rule']) ? true : false;

        $method_data = array();
        $quote_data = array();
        $sort_data = array(); 

        $heading = $this->ocm->getConfig('xshippingpro_heading', $this->mtype);
        $group = $this->ocm->getConfig('xshippingpro_group', $this->mtype);
        $group_limit = $this->ocm->getConfig('xshippingpro_group_limit', $this->mtype);
        $sub_group = $this->ocm->getConfig('xshippingpro_sub_group', $this->mtype);
        $sub_group_limit = $this->ocm->getConfig('xshippingpro_sub_group_limit', $this->mtype);
        $sub_group_name = $this->ocm->getConfig('xshippingpro_sub_group_name', $this->mtype);
        $debug = $this->ocm->getConfig('xshippingpro_debug', $this->mtype);
        $map_key = $this->ocm->getConfig('xshippingpro_map_api', $this->mtype); 
        $admin_all = $this->ocm->getConfig('xshippingpro_admin_all', $this->mtype);
        $store_geocode = $this->config->get('config_geocode');
        $group = $group ? $group : 'no_group';
        $group_limit = $group_limit ? (int)$group_limit : 1;
        $sub_group = $sub_group ? $sub_group : array();
        $sub_group_limit = $sub_group_limit ?$sub_group_limit : array();
        $sub_group_name = $sub_group_name ? $sub_group_name : array();

        $sorting = $this->ocm->getConfig('xshippingpro_sorting', $this->mtype);
        $sorting = ($sorting)?(int)$sorting:1;

        $currency_code = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');

        $_vweight_cache     = array();
        $debugging          = array();
        $method_level_group = false;
        $hiddenMethods      = array();
        $hiddenInactiveMethods = array();
        $sub_options        = array();
        $free_options       = array();
        $shipping_halt      = false;
        $shipping_error     = '';

        $xshippings = $this->getShippings();

        $xmethods = $xshippings['xmethods'];
        $xmeta = $xshippings['xmeta'];

        $cart_products = $this->getProducts();
        $_cart_data =  $this->getProductProfile($cart_products, $xmeta);
        $_xtaxes = $_cart_data['tax_data'];
        
        if ($xmeta['grand'] || $xmeta['coupon']) {
            $xtotals = $this->ocm->getTotals($_xtaxes);
            $_shipping = 0;
            foreach ($xtotals['totals'] as $single) {
                if ($single['code'] == 'coupon') {
                    $_cart_data['coupon'] = $single['value'];
                }
                if ($single['code'] == 'reward') {
                    $_cart_data['reward'] = $single['value'];
                }
                if (isset($single['xcode']) && $single['xcode']) {
                    $_cart_data['xfeepro'][$single['xcode']] = $single['value'];
                }
                if ($single['code'] == 'shipping') {
                    $_shipping = $single['value'];
                }
                if ($single['value'] < 0) {
                    $_cart_data['negative'] += abs($single['value']);
                }
            }
            $_cart_data['grand'] = $xtotals['total'] - $_shipping;
            $_cart_data['grand_shipping'] = $xtotals['_before_shipping'];
            $_cart_data['grand_wtax'] = $_cart_data['grand'];
            foreach ($xtotals['taxes'] as $tax) {
                $_cart_data['grand_wtax'] -= $tax;
            }
        }
        if (isset($xmeta['xlevel']) && $xmeta['xlevel']) {
            if ($this->xlevel && $this->customer->isLogged()) {
               $_xlevel = $this->xlevel->getLevelCustomer($this->customer->getId());  
               $compare_with['level_id'] = $_xlevel ? $_xlevel['level_id'] : 0;
            } else {
                $compare_with['level_id'] = 0;
            }
        }

        $_cart_data = $this->fixRounding($_cart_data);
        $_cart_data['grouping'] = array();
        $geo_ids = array();
        if ($xmeta['geo']) {
            $geo_rows = $this->db->query("SELECT geo_zone_id FROM " . DB_PREFIX . "zone_to_geo_zone WHERE country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')")->rows; 
            foreach ($geo_rows as $geo_row) {
                $geo_ids[] = $geo_row['geo_zone_id'];
            }
        }

        if ($xmeta['distance']) {
            $zone_row = $this->db->query("SELECT name FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int)$address['zone_id'] . "'")->row;
            $dest = (isset($address['address_1']) && $address['address_1']) ? $address['address_1'] : '';
            $dest .= $address['city'] ? ' '.$address['city'] : '';
            $dest .= $address['postcode'] ? ' '.$address['postcode'] : '';
            $dest .= ($zone_row && $zone_row['name']) ? ' '.$zone_row['name'] : '';
            $_cart_data['distance'] = $this->getDistance($store_geocode, $dest, $map_key, $debug);
            if ($zone_row && $zone_row['name']) {
                $address['zoneName'] = $zone_row['name'];
            }
        }
        $compare_with['products'] = $_cart_data['products'];
        $compare_with['geo'] = $geo_ids;
        $compare_with['product'] = $_cart_data['product'];
        $compare_with['category'] = $_cart_data['category'];
        $compare_with['manufacturer'] = $_cart_data['manufacturer'];
        $compare_with['option'] = $_cart_data['option'];
        $compare_with['attribute'] = $_cart_data['attribute'];
        $compare_with['location'] = $_cart_data['location'];
        $compare_with['total'] = $_cart_data['total'];
        $compare_with['weight'] = $_cart_data['weight'];
        $compare_with['quantity'] = $_cart_data['quantity'];
        if ($xmeta['payment_rule'] && !$compare_with['payment_method']) {
            $compare_with['payment_method'] = $this->getDefaultPaymentMethod($address, $_cart_data['total']);
        }
        
        $acc_cf_flag = true;
        foreach($xmethods as $xshippingpro) {
            $rules = $xshippingpro['rules'];
            $rates = $xshippingpro['rates'];
            $tab_id = $xshippingpro['tab_id'];
            $product_or = $xshippingpro['product_or'];
            $ingore_product_rule = $xshippingpro['ingore_product_rule'];
            $debugging_message = array();
            
            /* adjust multiple values */
            $this->_adjustMultiValues($rules, $_cart_data['products']);
            if ($product_or) {
                $this->_adjustProductsOr($rules, $_cart_data['products']);
            }
            if (isset($rules['custom']) && $acc_cf_flag && $this->customer->isLogged()) {
                $compare_with['custom_field'] = $this->syncAccountFields($compare_with['custom_field']);
                $acc_cf_flag = false;
            }

            $_cart_data['dimensional'] = 0;
            $_cart_data['volumetric'] = 0;

            $alive_or_dead = $this->_crucify($rules, $compare_with, $product_or, $ingore_product_rule, $only_address_rule);

            // set true if admin all set true
            if ($admin_all && $this->ocm->isAdmin()) {
                $alive_or_dead['status'] = true;
            }
            if (!$alive_or_dead['status']) {
                if ($xshippingpro['need_inactive_hide_method']) {
                    $hiddenInactiveMethods[$tab_id] = array(
                        'hide' => $xshippingpro['hide_inactive'],
                        'display' => $xshippingpro['display']
                    );
                }
                $debugging_message = $alive_or_dead['debugging'];
                $debugging[] = array('name' => $xshippingpro['display'],'filter' => $debugging_message,'index' => $tab_id);
            } else {
                $status = true;
                $applicable_cart = $this->_getApplicableProducts($rules, $_cart_data);

                if ($rates['type'] == 'dimensional' || $rates['type'] ==  'volumetric') {
                    $cache_key = (int)$rates['factor'].'_'.(int)$rates['overrule'];
                    if (isset($_vweight_cache[$cache_key]) && $_vweight_cache[$cache_key]) {
                        $vweight = $_vweight_cache[$cache_key];
                    } else {
                        $vweight = $this->_calVirtualWeight($_cart_data['products'], $rates['factor'], $rates['overrule']);
                        $_vweight_cache[$cache_key] = $vweight;
                    }
                    $_cart_data['dimensional'] = $vweight['dimensional'];
                    $_cart_data['volumetric'] = $vweight['volumetric'];
                    $_cart_data['product_dimensional'] = $vweight['product_dimensional'];
                    $_cart_data['product_volumetric'] = $vweight['product_volumetric'];
                }
                /* Calculate method wise data if needed*/
                $need_specified = ($xshippingpro['have_product_specified'] && ($xshippingpro['method_specific'] || ($rates['type'] == 'equation' && $rates['equation_specified_param'])));
                $method_specific_data = $this->_getMethodSpecificData($need_specified, $rules, $applicable_cart, $_cart_data, $product_or);
                $equation = $this->ocm->html_decode($rates['equation']);
                // find no. of package for applciable products only
                if (($rates['type'] == 'no_package' || preg_match('/{noOfPackage}/', $equation)) && isset($rules['package_dimension'])) {
                    $method_specific_data['no_package'] = $this->getTotalPackage($rules['package_dimension']['value'], $method_specific_data['products']);
                }

                $cost = 0;
                $percent_of = $method_specific_data[$rates['percent_of']];
                $iterate_all = preg_match('/{product\w+}/', $equation);

                if ($rates['type'] == 'flat') {
                    $cost = $rates['percent'] ? ($rates['value'] * $percent_of) : $rates['value'];
                }
                else if ($rates['type'] == 'per_manufacturer') {
                    $per_manufacturer_status = false;
                    foreach ($method_specific_data['per_manufacturer'] as $target_value) {
                        $price_result = $this->getPrice($rates, $target_value, $percent_of);
                        if ($price_result['status']) {
                            $per_manufacturer_status = true;
                            $cost += $rates['final'] == 'single' ? $price_result['cost'] : $price_result['cumulative'];
                        }
                    }
                    if (!$per_manufacturer_status) {
                        $debugging_message[] = 'Per Manufacturer (' . print_r($method_specific_data['per_manufacturer'], true) . ')';
                        $status = false;
                    }
                }
                else if ($rates['type'] == 'product') {
                    $per_product_status = false;
                    $products = $rates['ranges'];
                    foreach ($method_specific_data['products'] as $product) {
                        $rate = isset($products[$product['product_id']]) ? $products[$product['product_id']] : $rates['additional'];
                        if ($rate) {
                            $per_product_status = true;
                            $no_of_blocks = 1;
                            if ($rate['block']) {
                                $block_type = !empty($rate['type']) ? $rate['type'] : 'quantity';
                                $target_value = $rates['cart_adjust'] ? $this->adjustValue($rates['cart_adjust'], $product[$block_type]) : $product[$block_type];
                                $no_of_blocks = floor($target_value / $rate['block']);
                            }
                            $cost += ($rate['percent'] ? ($rate['value'] * $product['price']) : $rate['value']) * $no_of_blocks;
                        }
                    }
                    if (!$per_product_status) {
                        $debugging_message[] = 'Per Product - no matching products';
                        $status = false;
                    }
                }
                else if ($rates['type'] == 'equation' && $iterate_all) {
                    $iteration_result = $this->iterateEquation($equation, 'range', $rates, $method_specific_data, $_cart_data, $quote_data, $percent_of);
                    if ($iteration_result === false) {
                        $debugging_message[] = 'Shipping By - '.$rates['type'].' (iteration Over Products nothing matched)';
                        $status = false;
                    } else {
                        $cost = $iteration_result;
                    }
                }
                else {
                    if ($rates['type'] == 'equation') {
                        $equation_result = $this->getEquationValue($equation, $_cart_data, $method_specific_data, $quote_data, $percent_of);
                        $method_specific_data['equation'] = $equation_result['value'];
                    }
                    $target_value = $method_specific_data[$rates['type']];
                    $target_value = $rates['cart_adjust'] ? $this->adjustValue($rates['cart_adjust'], $target_value) : $target_value;
                    $price_result = $this->getPrice($rates, $target_value, $percent_of);
                    if ($price_result['status']) {
                        $method_specific_data['no_block'] = $price_result['block'];
                        if ($xmeta['block'] && $price_result['block']) {
                            $this->setBlockInfo($method_specific_data, $price_result, $rates['type']);
                        }
                    }
                    $_equation_check = $rates['type'] == 'equation' ? false : !!$rates['equation'] && empty($rates['ranges']);
                    if (!$price_result['status'] && !$_equation_check) {
                        $debugging_message[] ='Shipping By - '.$rates['type'].' ('.$target_value.')';
                        $status = false;
                    } else {
                        $cost = $rates['final'] == 'single' ? $price_result['cost'] : $price_result['cumulative'];
                    }
                }
                /* Price adjustment Start */
                $modifier_amount = 0;
                if ($rates['price_adjust']) {
                    /* Update percent of with shipping */
                    $method_specific_data['sub_shipping'] = $method_specific_data['sub'] + $cost;
                    $method_specific_data['total_shipping'] = $method_specific_data['total'] + $cost;
                    $method_specific_data['shipping'] = $cost;
                    $percent_of = $method_specific_data[$rates['percent_of']];

                    if (isset($rates['price_adjust']['min'])) {
                        $min = $rates['price_adjust']['min'];
                        $min_amount = $min['percent'] ? ($min['value'] * $percent_of) : $min['value'];
                        $cost = $min_amount > $cost ? $min_amount : $cost;
                    }
                    if (isset($rates['price_adjust']['max'])) {
                        $max = $rates['price_adjust']['max'];
                        $max_amount = $max['percent'] ? ($max['value'] * $percent_of) : $max['value'];
                        $cost = $max_amount < $cost ? $max_amount : $cost;
                    }
                    if (isset($rates['price_adjust']['modifier'])) {
                        $modifier = $rates['price_adjust']['modifier'];
                        $modifier_amount = $modifier['percent'] ? ($modifier['value'] * $percent_of) : $modifier['value'];
                        $cost = $this->tiniestCalculator($cost, $modifier_amount, $modifier['operator']);
                    }
                }

                /* If `method specified` was not true but equation was defined with method specific placeholders, let calculate method specifed values if it is not yet done  */
                if ($rates['equation']
                    && $xshippingpro['have_product_specified']
                    && $rates['equation_specified_param']
                    && !$need_specified) {
                     $method_specific_data = $this->_getMethodSpecificData(true, $rules, $applicable_cart, $_cart_data, $product_or);
                }
                if ($rates['equation'] && $rates['type'] != 'equation') {
                    $method_specific_data['shipping'] = $cost;
                    $percent_of = $method_specific_data[$rates['percent_of']];

                    if (preg_match('/{anyProduct\w+}/', $equation)) {
                        $iteration_type = strpos($equation, '@') === false ? 'single' : 'multiple';
                        $iteration_result = $this->iterateEquation($equation, $iteration_type, $rates, $method_specific_data, $_cart_data, $quote_data, $percent_of, $cost, $modifier_amount);
                        $cost = $iteration_result === false ? -1 : $iteration_result;
                    } else {
                        $equation_result = $this->getEquationValue($equation, $_cart_data, $method_specific_data, $quote_data, $percent_of, $cost, $modifier_amount);
                        $cost = $equation_result['value'];
                        if ($cost < 0 && $xshippingpro['equation_neg']) {
                            $cost = 0;
                        }
                        // Let's set cost to -1 so that method get failed as price range return false 
                        //if (isset($price_result) && !$price_result['status'] && !$cost) {
                            //$cost = -1;
                        //}
                    }
                    if ($cost < 0) {
                        $status = false; 
                        $debugging_message[] = 'Final Equation  (Return '.$cost.')';
                    }
                }
                /*Ended rate cal*/
                if (!isset($xshippingpro['display'])) $xshippingpro['display'] = '';
                if (!$xshippingpro['display']) {
                   $xshippingpro['display'] = isset($xshippingpro['name'][$language_id]) ? isset($xshippingpro['name'][$language_id]) : '';
                }
                if (!isset($xshippingpro['name'][$language_id]) || !$xshippingpro['name'][$language_id]) {
                   $xshippingpro['name'][$language_id] = 'Untitled Item';
                }

                if (!$status) {
                   $debugging[] = array('name' => $xshippingpro['display'],'filter' => $debugging_message,'index' => $tab_id);
                }

                if ($xshippingpro['inc_weight'] == 1 && $_cart_data['weight'] > 0) {
                    $_weight = $method_specific_data['weight'];
                    if ($rates['type'] == 'equation' && !$iterate_all && strpos($equation, 'weight') !== false) {
                        $_weight = $method_specific_data['equation'];
                    }
                    $xshippingpro['name'][$language_id] .= ' ('.$this->weight->format($_weight, $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point')).')';
                }

                if (intval($xshippingpro['group'])) {
                   $method_level_group = true;
                }

                /* cache for inactive hide */
                if (!$status) { 
                    if ($xshippingpro['need_inactive_hide_method']) {
                        $hiddenInactiveMethods[$tab_id] = array(
                            'hide' => $xshippingpro['hide_inactive'],
                            'display' => $xshippingpro['display']
                        );
                    }
                }

               if ($status) { 
                    if ($xshippingpro['disable']) {
                        $shipping_error = !empty($xshippingpro['error'][$language_id]) ? $this->ocm->html_decode($xshippingpro['error'][$language_id]) : '';
                        if ($shipping_error) {
                            $shipping_error = $this->getFormattedError($shipping_error, $_cart_data['products'], $address, $applicable_cart, $rules);
                        }
                        $shipping_halt = true;
                        break;
                    }
                    if ($xshippingpro['disable_other']) {
                        $quote_data = array();
                    }
                    if ($xshippingpro['need_hide_method']) {
                         $hiddenMethods[$tab_id] = array(
                            'hide' => $xshippingpro['hide'],
                            'display' => $xshippingpro['display']
                          );
                    }
                    if ($xshippingpro['sub_options']) {
                        $sub_options[$tab_id] = $xshippingpro['sub_options'];
                    }
                    // update group cost for equation placeholders
                    if (!isset($_cart_data['grouping'][$xshippingpro['group']])) {
                        $group_mode = !empty($sub_group[$xshippingpro['group']]) ? $sub_group[$xshippingpro['group']] : 'sum';
                        $_cart_data['grouping'][$xshippingpro['group']] = array('costs' => array(), 'mode' => $group_mode);  
                    }
                    $_cart_data['grouping'][$xshippingpro['group']]['costs'][] = $cost;
                    // end of grouping equation

                    $text = $xshippingpro['exc_vat'] ? $this->currency->format($cost,$currency_code) : $this->currency->format($this->tax->calculate($cost, $xshippingpro['tax_class_id'], $this->config->get('config_tax')),$currency_code);
                    $mask = isset($xshippingpro['mask'][$language_id]) ? $this->ocm->html_decode($xshippingpro['mask'][$language_id]) : '';

                    $quote_data['xshippingpro'.$tab_id] = array(
                        'code'         => 'xshippingpro'.'.xshippingpro'.$tab_id,
                        'tab_id'       => $tab_id,
                        'visibility'   => !isset($xshippingpro['visibility']) || $xshippingpro['visibility'], //1st part for legacy version
                        'fo'           => $xshippingpro['free_option'],
                        'sf'           => $xshippingpro['sub_title'],
                        'xkey'         => 'xshippingpro'.$tab_id,
                        'title'        => $this->ocm->html_decode($xshippingpro['name'][$language_id]),
                        'display'      => $xshippingpro['display'],
                        'cost'         => $cost,
                        'group'        => $xshippingpro['group'],
                        'sort_order'   => $xshippingpro['sort_order'],
                        'tax_class_id' => $xshippingpro['tax_class_id'],
                        'exc_vat'      => $xshippingpro['exc_vat'],
                        'mask'         => $mask,
                        'text'         => $mask ? $mask : $text
                    );
                    if ($xshippingpro['free_option'] && !$cost) {
                        $free_options[] = $tab_id;
                    }
                    if ($xshippingpro['disable_other']) {
                        break;
                    }
                }
            } 
        }
        /* Hide methods from hide option*/
        if (!$this->ocm->isAdmin() || ($this->ocm->isAdmin() && !$admin_all)) {
            $quote_data = $this->hideMethodsOnActive($quote_data, $hiddenMethods, $debugging);
            $quote_data = $this->hideMethodsOnInactive($quote_data, $hiddenInactiveMethods, $debugging);
        }
        /* Finding sub grouping Or method level grouping  */
        if ($method_level_group) { 
            $grouping_methods = array();
            foreach($quote_data as $single) {
                $grouping_methods[$single['group']][] = $single;
            }
            
            $new_quote_data=array();
            foreach($grouping_methods as $group_id => $grouping_method) {
                if (count($grouping_method) == 1 || empty($group_id) || $sub_group[$group_id] == 'no_group') {
                    $append_methods = array();
                    foreach($grouping_method as $single) {
                        $append_methods[$single['xkey']] = $single;
                    }
                    $new_quote_data = array_merge($new_quote_data, $append_methods);
                    continue;
                }
                
                $sub_group_type   = $sub_group[$group_id];
                $_sub_group_limit = isset($sub_group_limit[$group_id]) ? $sub_group_limit[$group_id] : 1;
                $_sub_group_name  = isset($sub_group_name[$group_id]) ? $this->ocm->html_decode($sub_group_name[$group_id]) : '';
                if (isset($grouping_method)) {
                    $new_quote_data = array_merge($new_quote_data, $this->findGroup($grouping_method, $sub_group_type, $_sub_group_limit, $_sub_group_name));
                }
            }
            $quote_data = $new_quote_data;
       }
       /* calculuate top level grouping if method level grouping active */
       if ($group != 'no_group' && $method_level_group) {
            $grouping_methods=array();
            foreach($quote_data as $single) {
                $grouping_methods[$single['sort_order']][]=$single;
            }
            $new_quote_data=array();
            foreach($grouping_methods as $group_id => $grouping_method) {
                if (count($grouping_method) == 1 || empty($group_id)) { // Not treating 0 as eligible group indentifer
                    $append_methods = array();
                    foreach($grouping_method as $single) {
                       $append_methods[$single['xkey']] = $single;
                    }
                    $new_quote_data = array_merge($new_quote_data, $append_methods);
                    continue;
                }
                if (isset($grouping_method)) {
                   $new_quote_data = array_merge($new_quote_data, $this->findGroup($grouping_method, $group, $group_limit));
                }
            }
            $quote_data= $new_quote_data;
        }

        /* Remove visivility-hidden methods */
        foreach ($quote_data as $key => $value) {
            if (!$value['visibility']) {
                $debugging[] = array('name' => $quote_data[$key]['display'],'filter' => array('Visibility - hidden'),'index' => $value['tab_id']);
                unset($quote_data[$key]); 
            }
        }
        /*Sorting final methods */
        $sort_order = array();
        foreach ($quote_data as $key => $value) {
            if ($sorting == 2 || $sorting == 3) {
                $sort_order[$key] = $value['cost'];
            } else if ($sorting == 4 || $sorting == 5) {
                $sort_order[$key] = $value['title'];
            } else {
                $sort_order[$key] = $value['sort_order'];
            }
            /* Unset unwanted keys */
            unset($quote_data[$key]['group']);
            unset($quote_data[$key]['xkey']);
            unset($quote_data[$key]['exc_vat']);
            unset($quote_data[$key]['display']);
            unset($quote_data[$key]['mask']);
            unset($quote_data[$key]['visibility']);
        }
        $sort_type = ($sorting == 3 || $sorting == 5) ? SORT_DESC : SORT_ASC;
        array_multisort($sort_order, $sort_type, $quote_data);

        /* Apply Sub-options */
        // if new approach works fine, remove sub_options array as from this method
        //$quote_data = $this->addSubOptions($quote_data, $sub_options, $language_id, $currency_code);  

        $quote_data = $this->addActiveSuboptions($quote_data, $xmethods); // new approach
        
        $heading = isset($heading[$language_id])?$heading[$language_id] : '';
        $method_data = array(
            'code'       => 'xshippingpro',
            'title'      => $heading,
            'quote'      => $quote_data,
            'sort_order' => $this->ocm->getConfig('xshippingpro_sort_order', $this->mtype),
            'error'      => false
        );
        if ($debug) {
            $_end = microtime(true);
            $_req_time = $_end - $_start;
            array_unshift($debugging, array('name' => 'It took ' . $_req_time . ' seconds to completed', 'filter' => array(),'index' => ''));
            $this->ocm->writeLog($debugging, 'xshippingpro');
        }
        if ($shipping_halt) {
            $method_data['quote'] = array();
            $method_data['error'] = $shipping_error;
            return $method_data;
        }
        if ($free_options) {
            $this->response->addHeader('_xs_: ' .implode(',', $free_options)); // send free option list as header
        }
        return $quote_data ? $method_data : array();
    }
    private function getFormattedError($error, $cart_products, $address, $applicable_products, $rules) {
        $placeholders = array('{postalCode}', '{city}', '{products}', '{zoneName}', '{countryName}');
        $replacers = array($address['postcode'] , $address['city'], '', '', '');
        if (strpos($error, '{products}') !== false) {
            $_product = array();
            foreach ($cart_products as $cart_product) {
                foreach ($applicable_products as $type => $items) {
                    $is_found = false;
                    if (!$items || !isset($rules[$type])) continue;
                    if ($type =='category') {
                        $is_found = (boolean)$this->array_intersect_faster($items, $cart_product['category']);
                    } else if ($type =='manufacturer' || $type =='location' || $type =='product') {
                        $is_found = in_array($cart_product[$type], $items);
                    }
                    //additional check for except rule 
                    if ($rules[$type]['rule_type']==5 || $rules[$type]['rule_type']==7) {
                        $is_found = ($type == 'category' || $key == 'attribute' || $type == 'option') ? $this->array_intersect_faster($cart_product[$type], $rules[$type]['value']) : in_array($cart_product[$type], $rules[$type]['value']);
                    }

                    if ($is_found) {
                        $url =  $this->url->link('product/product', 'product_id=' . $cart_product['product_id']); $cart_product['product_id'];
                        $_product[$cart_product['product_id']]= '<a href="'.$url.'">'.$cart_product['name'].'</a>';
                        break;
                    }
                }
            }
            $replacers[2] = implode(',&nbsp;&nbsp;', $_product);
        }
        if (strpos($error, '{zoneName}') !== false) {
            if (!isset($address['zoneName'])) {
                $zone_row = $this->db->query("SELECT name FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int)$address['zone_id'] . "'")->row;
                $address['zoneName'] = $zone_row && $zone_row['name'] ? $zone_row['name'] : '';
            }
            $replacers[3] = $address['zoneName'];
        }
        if (strpos($error, '{countryName}') !== false) {
            $country_row = $this->db->query("SELECT name FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$address['country_id'] . "'")->row;
            $replacers[4]  = $country_row && $country_row['name'] ? $country_row['name'] : '';
        }
        return str_replace($placeholders, $replacers, $error);
    }
    public function setSubOptions($code) {
        $return = false;
        $_code = str_replace('xshippingpro.xshippingpro', '', $code);
        $tab_id = explode('_', $_code)[0];
        $option_id = ltrim(ltrim($_code, $tab_id),'_');
        $shipping_method = 'xshippingpro' . $tab_id;
        if (!empty($this->session->data['shipping_methods']['xshippingpro']) && !empty($this->session->data['shipping_methods']['xshippingpro']['quote'][$shipping_method])) {
            $quote = $this->session->data['shipping_methods']['xshippingpro']['quote'][$shipping_method];
            $xshippings = $this->getShippings();
            $xmethods = $xshippings['xmethods'];
            $xshippingpro = $xmethods[$tab_id];
            if (!empty($xshippingpro['sub_options'])) {
                $sub_options = $xshippingpro['sub_options'];
                if (!empty($sub_options[$option_id])) {
                    $sub_option = $sub_options[$option_id];
                    $cost = $quote['cost'];
                    $title = $quote['title'];
                    $option_quote = $this->getOptionQuote($xshippingpro, $sub_option, $code, $title, $cost);
                    $this->session->data['shipping_methods']['xshippingpro']['quote']['xshippingpro' . $tab_id . '_' . $option_id] = $option_quote;
                    $this->session->data['xs_sub'] = array('id' => $tab_id, 'oid' => $option_id); 
                    $return = true;
                }
            }
        }
        return $return;
    }
    private function addActiveSuboptions($quote_data, $xmethods) {
        /* Don't add Sub-Options if it is on the estimator or quote page */
        if (isset($this->request->post['_xestimator'])
            || $this->ocm->isQuotePage()
            || empty($this->session->data['xs_sub'])
            || empty($this->session->data['xs_sub']['id'])) {
            return $quote_data;
        }
        $tab_id = $this->session->data['xs_sub']['id'];
        $option_id = $this->session->data['xs_sub']['oid'];
        if (!empty($quote_data['xshippingpro'.$tab_id])) {
            $xshippingpro = $xmethods[$tab_id];
            if (!empty($xshippingpro['sub_options'])) {
                $sub_options = $xshippingpro['sub_options'];
                if (!empty($sub_options[$option_id])) {
                    $sub_option = $sub_options[$option_id];
                    $code = 'xshippingpro'.'.xshippingpro' . $tab_id . '_' . $option_id;
                    $cost = $quote_data['xshippingpro'.$tab_id]['cost'];
                    $title = $quote_data['xshippingpro'.$tab_id]['title'];
                    $option_quote = $this->getOptionQuote($xshippingpro, $sub_option, $code, $title, $cost);
                    $quote_data['xshippingpro' . $tab_id . '_' . $option_id] = $option_quote;
                }
            }
        }
        return $quote_data;
    }
    private function getOptionQuote($xshippingpro, $sub_option, $code, $title, $cost) {
        $language_id = $this->config->get('config_language_id');
        $currency_code = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
        $tax_class_id = $xshippingpro['tax_class_id'];
        if ($xshippingpro['free_option'] && !$cost) {
            $sub_option['cost'] = 0;
        }
        $operator = $sub_option['operator'];
        if ($sub_option['cost']) {
            if ($operator == '+') {
                $cost += $sub_option['cost'];
            } else if ($operator == '-') {
                $cost -= $sub_option['cost'];
                $cost = $cost > 0 ? $cost : 0;
            } else {
                $cost = $sub_option['cost'];
            }
        }
        $text = $this->currency->format($this->tax->calculate($cost, $tax_class_id, $this->config->get('config_tax')), $currency_code);
        if ($xshippingpro['sub_title'] == 'sub') {
            $sub_title = $sub_option['path'][$language_id];
        } else if ($xshippingpro['sub_title'] == 'sub_last') {
            $sub_title = $sub_option['name'][$language_id];
        } else if ($xshippingpro['sub_title'] == 'main_sub_last') {
            $sub_title = $title .' - ' . $sub_option['name'][$language_id];
        } else {
            $sub_title = $title .' - ' . $sub_option['path'][$language_id];
        }
        return array(
            'code'         => $code,
            'title'        => $sub_title,
            'cost'         => $cost,
            'tax_class_id' => $tax_class_id,
            'text'         => $text
        );
    }

    // depreated - will remove soon if new approch work out
    private function addSubOptions($quote_data, $sub_options, $language_id, $currency_code) {
        /* Don't add Sub-Options if it is on the estimator or quote page */
        if (isset($this->request->post['_xestimator']) || $this->ocm->isQuotePage()) {
            return $quote_data;
        }
        if ($sub_options) {
            foreach ($sub_options as $tab_id => $sub_option) {
                if(isset($quote_data['xshippingpro'.$tab_id])) {
                    foreach ($sub_option as $option_id => $single_option) {
                        if (!$single_option['child']) continue;
                        if (!isset($single_option['name'][$language_id]) || !$single_option['name'][$language_id]) {
                            $single_option['name'][$language_id] = 'Untitled Option';
                        }
                        $cost = $quote_data['xshippingpro'.$tab_id]['cost'];
                        $title = $quote_data['xshippingpro'.$tab_id]['title'];
                        $tax_class_id = $quote_data['xshippingpro'.$tab_id]['tax_class_id'];
                        $fo = $quote_data['xshippingpro'.$tab_id]['fo'];
                        $text = '';
                        if ($fo && !$cost) {
                            $single_option['cost'] = 0;
                            //$text = '!!--';
                        }
                        $operator = $single_option['operator'];
                        
                        if ($single_option['cost']) {
                            if ($operator == '+') {
                                $cost += $single_option['cost'];
                            } else if ($operator == '-') {
                                $cost -= $single_option['cost'];
                                $cost = $cost > 0 ? $cost : 0;
                            } else {
                                $cost = $single_option['cost'];
                            }
                            $text = $this->currency->format($this->tax->calculate($cost, $tax_class_id, $this->config->get('config_tax')), $currency_code);
                        }
                        if ($quote_data['xshippingpro'.$tab_id]['sf'] == 'sub') {
                            $sub_title = $single_option['path'][$language_id];
                        } else if ($quote_data['xshippingpro'.$tab_id]['sf'] == 'sub_last') {
                            $sub_title = $single_option['name'][$language_id];
                        } else if ($quote_data['xshippingpro'.$tab_id]['sf'] == 'main_sub_last') {
                            $sub_title = $title .' - ' . $single_option['name'][$language_id];
                        } else {
                            $sub_title = $title .' - ' . $single_option['path'][$language_id];
                        }
                        $option_quote = array(
                            'code'         => 'xshippingpro'.'.xshippingpro' . $tab_id . '_' . $option_id,
                            'title'        => $sub_title,
                            'cost'         => $cost,
                            'tax_class_id' => $tax_class_id,
                            'text'         => $text
                        );
                        $quote_data['xshippingpro' . $tab_id . '_' . $option_id] = $option_quote;
                    }
                }
            }
        }
        return $quote_data;
    }
    public function getSubOptions($child_only = false) {
        $language_id = $this->config->get('config_language_id');
        $currency_code = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
        $sub_options = array();
        $xshippings = $this->getShippings();
        foreach($xshippings['xmethods'] as $xshippingpro) {
            $tab_id = $xshippingpro['tab_id'];
            $tax_class_id = $xshippingpro['tax_class_id'];
            $method_options = array();
            foreach ($xshippingpro['sub_options'] as $option_id => $single_option) {
                if ($child_only && !$single_option['child']) {
                    continue;
                }
                $operator = $single_option['operator'];
                $text = '';
                if ($single_option['cost'] && $single_option['child']) {
                    $option_text = $xshippingpro['exc_vat'] ? $this->currency->format($single_option['cost'], $currency_code) : $this->currency->format($this->tax->calculate($single_option['cost'], $tax_class_id, $this->config->get('config_tax')), $currency_code);
                    $text = ' (' . $operator . $option_text . ')';
                }
                if (!isset($single_option['name'][$language_id]) || !$single_option['name'][$language_id]) {
                    $single_option['name'][$language_id] = 'Untitled Option';
                }
                $single_option['name'][$language_id] .= $text;
                $method_options[$option_id] = array(
                    'code'         => 'xshippingpro'.'.xshippingpro' . $tab_id . '_' . $option_id,
                    'child'        => $single_option['child'],
                    'level'        => $single_option['level'],
                    'title'        => $single_option['name'][$language_id],
                    'label'        => $single_option['label'][$language_id],
                    'cost'         => $single_option['cost']
                );
            }
            if ($method_options) {
                $sub_options[$tab_id] = $method_options;
            }
        }
        return $sub_options;
    }
    public function getOptionalSubOptions() {
        $optional_options = array();
        $xshippings = $this->getShippings();
        foreach($xshippings['xmethods'] as $xshippingpro) {
            if (isset($xshippingpro['optional_option']) && $xshippingpro['optional_option']) {
                $optional_options[] = $xshippingpro['tab_id'];
            }
        }
        return $optional_options;
    }
    public function getShippingDesc() {
        $language_id = $this->config->get('config_language_id');
        $desc = array();
        $logo = array();
        $xshippings = $this->getShippings();
        foreach($xshippings['xmethods'] as $xshippingpro) {
            $tab_id = $xshippingpro['tab_id'];
            $_desc = (isset($xshippingpro['desc'][$language_id]) && $xshippingpro['desc'][$language_id]) ? $this->ocm->html_decode($xshippingpro['desc'][$language_id]) : '';
            if ($_desc) {
               $desc[$tab_id] = $_desc;
            }
            if ($xshippingpro['logo']) {
                $logo[$tab_id] = $xshippingpro['logo'];
            }
        }
        return array('desc' => $desc, 'logo' => $logo, 'city' => $xshippings['xmeta']['city_rule'], 'payment' => $xshippings['xmeta']['payment_rule']);
    }
    private function iterateEquation($equation, $type, $rates, $method_specific_data, $_cart_data, $quote_data, $percent_of, $cost = 0, $modifier_amount = 0) {
        $all = array('{productWidth}', '{productHeight}', '{productLength}', '{productWeight}', '{productQuantity}', '{productPrice}', '{productSpecialPrice}', '{productVolume}');
        $any = array('{anyProductWidth}', '{anyProductHeight}', '{anyProductLength}', '{anyProductWeight}', '{anyProductQuantity}', '{anyProductPrice}', '{anyProductSpecialPrice}', '{anyProductVolume}');
        $multiply_not_req = trim($equation) == '{productQuantity}' || $equation == '{productPrice}' || $equation == '{productSpecialPrice}';
        $_equation_status = false;
        $_equation_cost = 0;
        $_prev_cost = $cost;
        $_placeholders = $type == 'range' ? $all : $any;
        $_equation = $equation;
        foreach ($method_specific_data['products'] as $product) {
            $_replacers = array($product['width_self'], $product['height_self'], $product['length_self'], $product['weight_self'], $product['quantity'], $product['price_self'], $product['special_self'], $product['volume_self']);
            $equation = str_replace($_placeholders, $_replacers, $_equation);
            $equation_result = $this->getEquationValue($equation, $_cart_data, $method_specific_data, $quote_data, $percent_of, $_prev_cost, $modifier_amount);
            $target_value = $equation_result['value'];
            if ($type == 'range') {
                $target_value = $rates['cart_adjust'] ? $this->adjustValue($rates['cart_adjust'], $target_value) : $target_value;
                $price_result = $this->getPrice($rates, $target_value, $percent_of);
                if ($price_result['status']) {
                    $_equation_status = true;
                    $multiple_by = $multiply_not_req ? 1 : $product['quantity'];
                    $_equation_cost += ($rates['final'] == 'single' ? $price_result['cost'] : $price_result['cumulative']) * $multiple_by;
                    $method_specific_data['no_block'] = $price_result['block'];
                }
            } else if ($target_value >= 0) {
                $_equation_status = true;
                if ($type == 'single') {
                    $_equation_cost = $target_value;
                    if ($equation_result['status']) {
                        break;
                    }
                } else {
                    $_equation_cost += $target_value;
                }
            }
            $_prev_cost = $_equation_cost;
            $method_specific_data['shipping'] = $_equation_cost; // update shipping for placeholder
        }
        return $_equation_status ? $_equation_cost : false;
    }
    private function getShippings() {
        $xshippingpro = $this->cache->get('ocm.xshippingpro');
        if (!$xshippingpro) {
            $language_id = $this->config->get('config_language_id');
            $xmethods = array();
            $xmeta = array(
                'grand' => false,
                'coupon' => false,
                'geo' => false,
                'category_query'=> false,
                'product_query' => false,
                'attribute_query' => false,
                'payment_rule'  => false,
                'distance'      => false,
                'city_rule'     => false,
                'block'         => false,
                'xlevel'        => false
            );
            $priority = 'first';
            $rows = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xshippingpro` order by `sort_order` asc")->rows;
            foreach($rows as $row) {
                $method_data = $row['method_data'];
                $method_data = json_decode($method_data, true);
                /* cache only valid shipping */
                if ($method_data && is_array($method_data) && $method_data['status']) {
                    $method_data =  $this->_resetEmptyRule($method_data);
                    $rules = $this->_findValidRules($method_data);
                    $rates = $this->_findRawRate($method_data);

                    $have_product_specified = false;
                    if ($method_data['category'] > 1
                        || $method_data['product'] > 1
                        || $method_data['manufacturer_rule'] > 1
                        || $method_data['option'] > 1
                        || $method_data['attribute'] > 1
                        || $method_data['location_rule'] > 1) {
                            $have_product_specified = true;
                    }
                    $xmethods[$row['tab_id']] = array(
                       'tab_id' => (int)$row['tab_id'],
                       'name' => $method_data['name'],
                       'desc' => $method_data['desc'],
                       'mask' => $method_data['mask'],
                       'error' => $method_data['error'],
                       'display' => $method_data['display'],
                       'rules' => $rules,
                       'rates' => $rates,
                       'group' => (int)$method_data['group'],
                       'inc_weight' => !!$method_data['inc_weight'],
                       'exc_vat' => !!$method_data['exc_vat'],
                       'equation_neg' => !!$method_data['equation_neg'],
                       'tax_class_id' => (int)$method_data['tax_class_id'],
                       'sort_order' => (int)$method_data['sort_order'],
                       'logo' => $method_data['logo'],
                       'ingore_product_rule' => !!$method_data['ingore_product_rule'],
                       'product_or' => !!$method_data['product_or'],
                       'method_specific' => !!$method_data['method_specific'],
                       'free_option' => !!$method_data['free_option'],
                       'optional_option' => !!$method_data['optional_option'],
                       'sub_title'  => $method_data['sub_title'],
                       'hide' => $method_data['hide'],
                       'hide_inactive' => $method_data['hide_inactive'],
                       'need_hide_method' => !!count($method_data['hide']),
                       'need_inactive_hide_method' => !!count($method_data['hide_inactive']),
                       'have_product_specified' => $have_product_specified,
                       'disable' => !!$method_data['disable'],
                       'visibility' => !!$method_data['visibility'],
                       'disable_other' => !!$method_data['disable_other'],
                       'sub_options' => $this->getSubOptionComponent($method_data['sub_options'])
                    );

                    if ($method_data['geo_zone_all'] != 1) {
                        $xmeta['geo'] = true;
                    }
                    if ($method_data['payment_all'] != 1) {
                        $xmeta['payment_rule'] = true;
                    }
                    if ($method_data['city_all'] != 1) {
                        $xmeta['city_rule'] = true;
                    }
                    if ($method_data['rate_type'] == 'grand_shipping'
                        || $method_data['rate_type'] == 'grand'
                        || $method_data['rate_type'] == 'sub_negative'
                        || $method_data['rate_type'] == 'equation'
                        || strpos($method_data['equation'], 'grandTotal') !== false
                        || strpos($method_data['equation'], 'grandBeforeShipping') !== false) {
                        $xmeta['grand'] = true;
                    }
                    if ($method_data['rate_type'] == 'total_coupon' 
                        || $method_data['rate_type'] == 'sub_coupon' 
                        || $method_data['equation']
                        || strpos($method_data['equation'], 'rewardValue') !== false
                        ) {
                        $xmeta['coupon'] = true;
                    }
                    if ($method_data['attribute'] > 1) {
                        $xmeta['attribute_query'] = true;
                    }
                    if ($method_data['category'] > 1
                        || $method_data['rate_type'] == 'no_category'
                        || strpos($method_data['equation'], 'noOfCategory') !== false) {
                            $xmeta['category_query'] = true;
                    }
                    if ($method_data['manufacturer_rule'] > 1
                        || $method_data['location_rule'] > 1
                        || $method_data['rate_type'] == 'no_manufacturer'
                        || $method_data['rate_type'] == 'no_location'
                        || $method_data['rate_type'] == 'per_manufacturer'
                        || strpos($method_data['equation'], 'noOfManufacturer') !== false
                        || strpos($method_data['equation'], 'noOfLocation') !== false) {
                            $xmeta['product_query'] = true;
                    }
                    if ($method_data['rate_type'] == 'distance'
                        || strpos($method_data['equation'], 'distance') !== false) {
                            $xmeta['distance'] = true;
                    }
                    if (stripos($method_data['equation'], '{mod') !== false) {
                        $priority = 'last';
                    }
                    if (stripos($method_data['equation'], '{blockPrice') !== false) {
                        $xmeta['block'] = true;
                    }
                    if (!empty($rules['xlevel'])) {
                        $xmeta['xlevel'] = true;
                    }
                }
            }
            $xshippingpro = array('xmeta' => $xmeta, 'xmethods' => $xmethods);
            $this->cache->set('ocm.xshippingpro', $xshippingpro);
            $this->cache->set('ocm.xshippingpro_position', $priority);
        }
        return $xshippingpro;
    }
    private function _resetEmptyRule($data) {
        $rules = array(
            'store' => 'store_all',
            'geo_zone' => 'geo_zone_all',
            'city' => 'city_all',
            'country' => 'country_all',
            'zone' => 'zone_all',
            'customer_group' => 'customer_group_all',
            'currency' => 'currency_all',
            'payment' => 'payment_all',
            'postal' => 'postal_all',
            'coupon' => 'coupon_all',
            'days' => 'days_all',
            'product_category' => 'category',
            'product_product' => 'product',
            'product_option' => 'option',
            'product_attribute' => 'attribute',
            'manufacturer' => 'manufacturer_rule',
            'location' => 'location_rule',
            'customers' => 'customer_all',
            'xlevel' => 'xlevel_all',
            'custom' => 'custom_all'
        );
        foreach ($rules as $key => $value) {
            if (!isset($data[$value])) {
                $data[$value] = '';
            }
            if (!isset($data[$key]) || !$data[$key]) {
                $data[$value] = 1;
            }
            /* make empty product entry if all is selected */
            if ($data[$value] < 2 && in_array($key, array('product_category', 'product_product', 'product_option', 'product_attribute', 'manufacturer', 'location'))) {
                $data[$key] = array();
            }
        }
        /* reset delimitter to comma */
        $fields = array(
            'city',
            'coupon',
            'postal'
        );
        foreach ($fields as $field) {
            if (isset($data[$field]) && $data[$field]) {
                $data[$field] = str_replace(PHP_EOL, ',', $data[$field]);
            }
        }
        /* reset cost params  */ 
        if (empty($data['additional_per'])) $data['additional_per'] = 1;
        if (empty($data['additional_limit'])) $data['additional_limit'] = PHP_INT_MAX;
        if (empty($data['dimensional_factor'])) $data['dimensional_factor'] = 5000;
        if (empty($data['max_weight'])) $data['max_weight'] = PHP_INT_MAX;
        if (empty($data['display'])) $data['display'] = 'Untitled Item';
        if (empty($data['sub_title'])) $data['sub_title'] = '';
        /* checkboxes */
        if (empty($data['dimensional_overfule'])) $data['dimensional_overfule'] = '';
        if (empty($data['inc_weight'])) $data['inc_weight'] = '';
        if (empty($data['ingore_product_rule'])) $data['ingore_product_rule'] = '';
        if (empty($data['product_or'])) $data['product_or'] = '';
        if (empty($data['method_specific'])) $data['method_specific'] = '';
        if (empty($data['free_option'])) $data['free_option'] = '';
        if (empty($data['optional_option'])) $data['optional_option'] = '';
        if (empty($data['dimensional_overfule'])) $data['dimensional_overfule'] = '';
        if (empty($data['exc_vat'])) $data['exc_vat'] = '';
        if (empty($data['equation_neg'])) $data['equation_neg'] = '';
        if (empty($data['disable'])) $data['disable'] = '';
        if (empty($data['disable_other'])) $data['disable_other'] = '';
        if (!isset($data['visibility'])) $data['visibility'] = 1; // must be checked isset NOT empty
        /* Reset other */
        if (empty($data['ranges'])) $data['ranges'] = array();
        if (empty($data['hook'])) $data['hook'] = array();
        if (empty($data['days'])) $data['days'] = array();
        if (empty($data['name']) || !is_array($data['name'])) $data['name'] = array();
        if (empty($data['desc']) || !is_array($data['desc'])) $data['desc'] = array();
        if (empty($data['mask']) || !is_array($data['mask'])) $data['mask'] = array();
        if (empty($data['hide']) || !is_array($data['hide'])) $data['hide'] = array();
        if (empty($data['hide_inactive']) || !is_array($data['hide_inactive'])) $data['hide_inactive'] = array();
        
        /* Adjust Sub-Options */
        if (!isset($data['sub_options']) || !is_array($data['sub_options'])) $data['sub_options'] = array();
        //free version to pro cause warning issue so reset
        $proversion = array('date_start', 'time_start', 'max_length', 'max_width', 'max_height', 'max_weight', 'weight_start', 'quantity_start', 'equation', 'rate_final', 'cart_adjust', 'rate_min', 'rate_max', 'rate_add', 'additional', 'error', 'group', 'logo', 'order_total_start');
        foreach ($proversion as $field) {
            if (!isset($data[$field])) {
                $data[$field] = '';
            }
        }
        if (!isset($data['rate_percent'])) {
            $data['rate_percent'] = 'sub';
        }
        if (!empty($data['order_total_start']) && empty($data['order_total_end'])) $data['order_total_end'] = PHP_INT_MAX;
        if (!empty($data['weight_start']) && empty($data['weight_end'])) $data['weight_end'] = PHP_INT_MAX;
        if (!empty($data['quantity_start']) && empty($data['quantity_end'])) $data['quantity_end'] = PHP_INT_MAX;
        return $data;
    }
    private function _findValidRules($data) {
        $rules = array();
        if ($data['store_all'] != 1) {
            $rules['store'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['store'],
                'compare_with' => 'store_id',
                'false_value' => false
            );
        }
        if ($data['geo_zone_all'] != 1) {
            $rules['geo_zone'] = array(
                'type' => 'intersect',
                'product_rule' => false,
                'address_rule' => true,
                'value' => $data['geo_zone'],
                'compare_with' => 'geo',
                'false_value' => false
            );
        }
        if ($data['customer_all'] != 1) {
            $false_value = ($data['customer_rule'] == 'inclusive') ? false : true;
            $rules['customers'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['customers'],
                'compare_with' => 'customer_id',
                'false_value' => $false_value
            );
        }
        if ($data['xlevel_all'] != 1 && $this->xlevel) {
            $rules['xlevel'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['xlevel'],
                'compare_with' => 'level_id',
                'false_value' => false
            );
        }
        if ($data['city_all'] != 1) {
            $false_value = ($data['city_rule'] == 'inclusive') ? false : true;
            $cities = explode(',',trim($data['city']));
            $cities = array_map('strtolower', $cities);
            $cities = array_map('trim', $cities);

            $rules['city'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => true,
                'value' => $cities,
                'compare_with' => 'city',
                'false_value' => $false_value
            );
        }
        if ($data['country_all'] != 1) {
            $rules['country'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => true,
                'value' => $data['country'],
                'compare_with' => 'country_id',
                'false_value' => false
            );
        }
        if ($data['zone_all'] != 1) {
            $rules['zone'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => true,
                'value' => $data['zone'],
                'compare_with' => 'zone_id',
                'false_value' => false
            );
        }
        if ($data['customer_group_all'] != 1) {
            $rules['customer_group'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['customer_group'],
                'compare_with' => 'customer_group_id',
                'false_value' => false
            );
        }
        if ($data['currency_all'] != 1) {
            $rules['currency'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['currency'],
                'compare_with' => 'currency_id',
                'false_value' => false
            );
        }
        if ($data['payment_all'] != 1) {
            $rules['payment'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['payment'],
                'compare_with' => 'payment_method',
                'false_value' => false
            );
        }
        if ($data['postal_all'] != 1) {
            $postcodes = explode(',',trim($data['postal']));
            $postcodes = array_map('trim', $postcodes);
            $rules['postal'] = array(
                'type' => 'function',
                'func' => '_validatePostal',
                'product_rule' => false,
                'address_rule' => true,
                'value' => $postcodes,
                'compare_with' => 'postcode',
                'rule_type' => $data['postal_rule'],
                'false_value' => false
            );
        }
        if ($data['coupon_all'] != 1) {
            $false_value = ($data['coupon_rule'] == 'inclusive') ? false : true;
            $coupons = explode(',',trim($data['coupon']));
            $coupons = array_map('trim', $coupons);
            $coupons = array_map('strtolower', $coupons);
            $rules['coupon'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $coupons,
                'compare_with' => 'coupon_code',
                'false_value' => $false_value
            );
        }
        if ($data['custom_all'] != 1) {
            $rules['custom'] = array(
                'type' => 'intersect',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['custom'],
                'compare_with' => 'custom_field',
                'false_value' => false
            );
        }
        if ((int)$data['product'] > 1) {
            $rules['product'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $data['product_product'],
                'compare_with' => 'product',
                'rule_type' => $data['product'],
                'false_value' => false
            );
        }
        if ((int)$data['category'] > 1) {
            $rules['category'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $data['product_category'],
                'compare_with' => 'category',
                'rule_type' => $data['category'],
                'false_value' => false
            );
        }
        if ((int)$data['manufacturer_rule'] > 1) {
            $rules['manufacturer'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $data['manufacturer'],
                'compare_with' => 'manufacturer',
                'rule_type' => $data['manufacturer_rule'],
                'false_value' => false
            );
        }
        if ((int)$data['option'] > 1) {
            $rules['option'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $data['product_option'],
                'compare_with' => 'option',
                'rule_type' => $data['option'],
                'false_value' => false
            );
        }
        if ((int)$data['attribute'] > 1) {
            $rules['attribute'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $data['product_attribute'],
                'compare_with' => 'attribute',
                'rule_type' => $data['attribute'],
                'false_value' => false
            );
        }
        if ((int)$data['location_rule'] > 1) {
            $location = array_map('strtolower', $data['location']);
            $location = array_map('trim', $location);
            $rules['location'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $location,
                'compare_with' => 'location',
                'rule_type' => $data['location_rule'],
                'false_value' => false
            );
        }

        if ($data['days_all'] != 1 && is_array($data['days']) && $data['days'] && count($data['days']) !== 7) {
            $rules['days'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['days'],
                'compare_with' => 'day',
                'false_value' => false
            );
        }
        if ($data['date_start'] != "" && $data['date_end']) {
            $rules['date'] = array(
                'type' => 'in_between',
                'product_rule' => false,
                'address_rule' => false,
                'start' => $data['date_start'],
                'end' => $data['date_end'],
                'compare_with' => 'date'
            );
        }
        if ($data['time_start'] != "" && $data['time_end'] != "") {
            $valid_hours = array();
            $time_start = (int)$data['time_start'];
            $time_end = (int)$data['time_end'];

            if ($time_start <= $time_end) {
               for ($i = $time_start; $i < $time_end ; $i++) { 
                  $valid_hours[] = $i;
               }
            } else {
               for ($i = 0; $i < $time_end ; $i++) { 
                  $valid_hours[] = $i;
               }
               for ($i = $time_start; $i <= 23 ; $i++) { 
                  $valid_hours[] = $i;
               }
            }
            if ($valid_hours) {
                $rules['time'] = array(
                    'type' => 'in_array',
                    'product_rule' => false,
                    'address_rule' => false,
                    'value' => $valid_hours,
                    'compare_with' => 'time',
                    'false_value' => false
                );
            }
        }
        /* Special rule if only ending time and date range set */
        if ($data['date_start'] != "" && $data['date_end'] && !$data['time_start'] && $data['time_end']) {
            $valid_hours = array();
            $time_start = 0;
            $time_end = (int)$data['time_end'];
            for ($i = $time_start; $i < $time_end ; $i++) { 
                  $valid_hours[] = $i;
            }
            $rules['date_time'] = array(
                'type' => 'in_array_not_equal',
                'product_rule' => false,
                'value' => $valid_hours,
                'compare_with' => 'time',
                'not_equal_value' => $data['date_end'],
                'not_equal_with' => 'date',
                'false_value' => false
            );
        }
        if ($data['max_length'] || $data['max_width'] || $data['max_height']) {
            $rules['package_dimension'] = array(
                'type' => 'function',
                'func' => '_validateDimension',
                'product_rule' => false,
                'address_rule' => false,
                'value' => array('length' => (float)$data['max_length'], 'width' => (float)$data['max_width'], 'height' => (float)$data['max_height'], 'weight' => (float)$data['max_weight']),
                'compare_with' => 'products',
                'rule_type' => 'max_dimension',
                'false_value' => false
            );
        }
        if ($data['rate_type'] != 'sub'
            && $data['rate_type'] != 'total'
            && $data['rate_type'] != 'sub_coupon'
            && $data['rate_type'] != 'total_coupon'
            && $data['rate_type'] != 'grand_shipping'
            && $data['rate_type'] != 'grand'
            && $data['order_total_start'] != "" 
            && (float)$data['order_total_end']) {
                $rules['additional_total'] = array(
                    'type' => 'in_between',
                    'product_rule' => false,
                    'address_rule' => false,
                    'start' => (float)$data['order_total_start'],
                    'end' => (float)$data['order_total_end'],
                    'compare_with' => 'total'
                );
        }
        if ($data['rate_type'] != 'weight'
            && $data['weight_start'] != ""
            && (float)$data['weight_end']) {
                $rules['additional_weight'] = array(
                    'type' => 'in_between',
                    'product_rule' => false,
                    'address_rule' => false,
                    'start' => (float)$data['weight_start'],
                    'end' => (float)$data['weight_end'],
                    'compare_with' => 'weight'
                );
        }
        if ($data['rate_type'] != 'quantity'
            && $data['quantity_start'] != ""
            && (int)$data['quantity_end']) {
                $rules['additional_qunatity'] = array(
                    'type' => 'in_between',
                    'product_rule' => false,
                    'address_rule' => false,
                    'start' => (int)$data['quantity_start'],
                    'end' => (int)$data['quantity_end'],
                    'compare_with' => 'quantity'
                );
        }
        /* Hooking fields */
        if ($data['hook']) {
            foreach ($data['hook'] as $key => $value) {
                $rules[$key] = array(
                    'type' => 'function',
                    'func' => 'hook_' . $key,
                    'product_rule' => false,
                    'address_rule' => false,
                    'value' => $value,
                    'false_value' => false,
                    'rule_type' => $key,
                    'compare_with' => 'products'
                );
            }
        }
        return $rules;
    }
    private function _findRawRate($data) {
        $operators= array('+','-','/','*');
        $rates = array();
        $rates['type'] = $data['rate_type'];
        $rates['equation'] = $data['equation'];
        $rates['equation_specified_param'] = (strpos($data['equation'], 'PerProductRule') !== false);
        $rates['final'] = $data['rate_final'];
        $rates['percent_of'] = $data['rate_percent'];
        $rates['overrule'] = !!$data['dimensional_overfule'];
        $rates['factor'] = $data['dimensional_factor'];
        $rates['additional'] = array();
        $rates['cart_adjust'] = array();
        $rates['price_adjust'] = array();

        /* Shipping Cost */
        if ($data['rate_type'] == 'flat') {
            $cost = trim($data['cost']);
            if (substr($cost, -1) == '%') {
                $cost = rtrim($cost,'%');
                $rates['percent'] = true;
                $rates['value'] = (float)$cost / 100;
            } else {
                $rates['percent'] = false;
                $rates['value'] = (float)$cost;
            }
        } else {
           $ranges = array();
           foreach($data['ranges'] as $range) {
               $start = (float)$range['start'];
               $end = (float)$range['end'];
               $cost = trim(trim($range['cost']), '-');
               $block = (float)$range['block'];
               $partial = (int)$range['partial'];
               $product_id = isset($range['product_id']) ? (int)$range['product_id'] : 0; // legacy compatiblity 
               $type = isset($range['type']) ? $range['type'] : '';
               if (substr($cost, -1) == '%') {
                    $cost = rtrim($cost,'%');
                    $percent = true;
                    $value = (float)$cost / 100;
                } else {
                    $percent = false;
                    $value = (float)$cost;
                }
                if ($data['rate_type'] == 'product') {
                    $ranges[$product_id] = array('percent' => $percent, 'value' => $value, 'block' => $block, 'type' => $type);
                } else {
                    $ranges[] = array('start' => round($start, 8), 'end' => round($end, 8), 'percent' => $percent, 'value' => $value, 'block' => $block, 'partial' => $partial);
                }
            }
            $rates['ranges'] = $ranges;
        }
      
       /* Other price parameters */
       if ($data['cart_adjust']) {
            $operator = substr(trim($data['cart_adjust']),0,1);
            $operator = in_array($operator,$operators) ? $operator : '+';
            $adjust = ltrim($data['cart_adjust'], '+-*/');
            if (substr($adjust, -1) == '%') {
                $adjust = rtrim($adjust,'%');
                $rates['cart_adjust']['percent'] = true;
                $rates['cart_adjust']['value'] = (float)$adjust / 100;
                $rates['cart_adjust']['operator'] = $operator;
            } else {
                $rates['cart_adjust']['percent'] = false;
                $rates['cart_adjust']['value'] = (float)$adjust;
                $rates['cart_adjust']['operator'] = $operator;
            }
        }
        if ($data['rate_min'] && $data['rate_type'] != 'flat') {
             $rate_min = $data['rate_min'];
             $rates['price_adjust']['min'] = array();
             if (substr($rate_min, -1) == '%') {
                $rate_min = rtrim($rate_min,'%');
                $rates['price_adjust']['min']['percent'] = true;
                $rates['price_adjust']['min']['value'] = (float)$rate_min / 100;
             } else {
                $rates['price_adjust']['min']['percent'] = false;
                $rates['price_adjust']['min']['value'] = (float)$rate_min;
             }
        }
        if ($data['rate_max'] && $data['rate_type'] != 'flat') {
             $rate_max = $data['rate_max'];
             $rates['price_adjust']['max'] = array();
             if (substr($rate_max, -1) == '%') {
                $rate_max = rtrim($rate_max,'%');
                $rates['price_adjust']['max']['percent'] = true;
                $rates['price_adjust']['max']['value'] = (float)$rate_max / 100;
             } else {
                $rates['price_adjust']['max']['percent'] = false;
                $rates['price_adjust']['max']['value'] = (float)$rate_max;
             }
        }
        if ($data['rate_add'] && $data['rate_type'] != 'flat') {
            $modifier = $data['rate_add'];
            $rates['price_adjust']['modifier'] = array();
            $operator = substr(trim($modifier),0,1);
            $operator = in_array($operator,$operators) ? $operator : '+';
            $modifier = ltrim($modifier, '+-*/');
            if (substr($modifier, -1) == '%') {
                $modifier = rtrim($modifier,'%');
                $rates['price_adjust']['modifier']['percent'] = true;
                $rates['price_adjust']['modifier']['value'] = (float)$modifier / 100;
                $rates['price_adjust']['modifier']['operator'] = $operator;
            } else {
                $rates['price_adjust']['modifier']['percent'] = false;
                $rates['price_adjust']['modifier']['value'] = (float)$modifier;
                $rates['price_adjust']['modifier']['operator'] = $operator;
            }
        }
        if ($data['additional']) {
            $additional = $data['additional'];
            $rates['additional']['repeat'] = strpos($additional, '@') !== false;
            $additional = trim(str_replace('@', '', $additional));
            if (substr($additional, -1) == '%') {
                $additional = rtrim($additional,'%');
                $rates['additional']['percent'] = true;
                $rates['additional']['value'] = (float)$additional / 100;
            } else {
                $rates['additional']['percent'] = false;
                $rates['additional']['value'] = (float)$additional;
            }
            $rates['additional']['block'] = (float)$data['additional_per'];
            $rates['additional']['max'] = (float)$data['additional_limit'];
        }
        return $rates;
    }
    private function adjustValue($adjust_rate, $value) {
        $amount = $adjust_rate['percent'] ? ($adjust_rate['value'] * $value) : $adjust_rate['value'];
        return $this->tiniestCalculator($value, $amount, $adjust_rate['operator']);
    }
    private function getProducts() {
        $product_id = isset($this->request->post['_xestimator']) && isset($this->request->post['_xestimator']['product_id']) ? $this->request->post['_xestimator']['product_id'] : 0;
        if ($product_id) {
            $product_info = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'")->row;
            if ($product_info) {
                $quantity = isset($this->request->post['quantity']) && $this->request->post['quantity'] ? $this->request->post['quantity'] : 1;
                $quantity = isset($this->request->get['quantity']) && $this->request->get['quantity'] ? $this->request->get['quantity'] : $quantity;
                $product_info['stock'] = $product_info['quantity'];
                $product_info['quantity'] = $quantity;
                $price = $product_info['price'];

                $discount_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity <= '" . (int)$quantity . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");
                if ($discount_query->num_rows) {
                    $price = $discount_query->row['price'];
                }

                $special_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");
                if ($special_query->num_rows) {
                    $price = $special_query->row['price'];
                }
                $option_price = 0;
                $option_weight = 0;

                $_picked_options = array();
                if (!empty($this->request->post['option']) && is_array($this->request->post['option'])) {
                    $_picked_options = $this->request->post['option'];
                }
                if ($_picked_options) {
                    foreach($_picked_options as $product_option_value_ids) {
                        if ($product_option_value_ids) {
                            if (!is_array($product_option_value_ids)) {
                                $product_option_value_ids = array($product_option_value_ids);
                            }
                            foreach($product_option_value_ids as $product_option_value_id) {
                                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id = '" . (int)$product_option_value_id . "'");
                                if ($query->row) {
                                    if ($query->row['price_prefix']=='+') {
                                        $option_price += (float)$query->row['price'];
                                        $option_weight += (float)$query->row['weight'];
                                    }
                                    if ($query->row['price_prefix']=='-') {
                                        $option_price -= (float)$query->row['price'];
                                        $option_weight -= (float)$query->row['weight'];
                                    }
                                }
                            }
                        }
                    }
                }
               // $product_info['jan'] = (float)$product_info['ean'] * $quantity;
               // $product_info['ean'] = (float)$product_info['jan'] * $quantity;
                $product_info['price'] = ($price + $option_price);
                $product_info['total'] = ($price + $option_price) * $quantity;
                $product_info['weight'] = ($product_info['weight'] + $option_weight) * $quantity;
                $product_info['ocm_special'] = $special_query->num_rows ? true : false;
            }
            return array($product_info);
        }
        return $this->cart->getProducts();
    }
    private function getEquationValue($equation, $_cart_data, $method_specific_data, $quote_data, $percent_of, $shipping_cost = 0, $modifier_amount = 0) {
        $placholder = array(
            '{subTotal}',
            '{subTotalWithTax}',
            '{special}',
            '{quantity}',
            '{weight}',
            '{volume}',
            '{dimension}',
            '{dimensional}', 
            '{volumetric}',
            '{noOfProduct}', 
            '{noOfCategory}', 
            '{noOfManufacturer}', 
            '{noOfLocation}',
            '{noOfFreeProduct}',
            '{weightOfFreeProduct}',
            '{weightOfGiftProduct}',
            '{totalOfGiftProduct}',
            '{noOfBlock}',
            '{blockPriceAsc}',
            '{blockPriceDesc}',
            '{noOfPackage}',
            '{noOfProductAsPerProductRule}', 
            '{noOfOutOfStockProduct}',
            '{subTotalAsPerProductRule}',
            '{subTotalWithTaxAsPerProductRule}',
            '{quantityAsPerProductRule}',
            '{weightAsPerProductRule}',
            '{volumeAsPerProductRule}',
            '{dimensionAsPerProductRule}',
            '{couponValue}',
            '{rewardValue}',
            '{vouchers}',
            '{shipping}',
            '{modifier}',
            '{grandTotal}',
            '{grandBeforeShipping}',
            '{distance}',
            '{highest}',
            '{lowest}',
            '{highestQnty}',
            '{lowestQnty}',
            '{nonMethodSub}',
            '{nonMethodQnty}',
            '{nonShippableCost}',
            '@'
        );
        $replacer = array(
            $_cart_data['sub'],
            $_cart_data['total'],
            $_cart_data['special'],
            $_cart_data['quantity'],
            $_cart_data['weight'],
            $_cart_data['volume'],
            $_cart_data['dimension'],
            $_cart_data['dimensional'],
            $_cart_data['volumetric'],
            $_cart_data['no_product'],
            $_cart_data['no_category'],
            $_cart_data['no_manufacturer'],
            $_cart_data['no_location'],
            $method_specific_data['no_of_free'],
            $method_specific_data['weight_of_free'],
            $method_specific_data['xgift']['weight'],
            $method_specific_data['xgift']['total'],
            $method_specific_data['no_block'],
            $method_specific_data['block_asc'],
            $method_specific_data['block_desc'],
            $method_specific_data['no_package'],
            $method_specific_data['no_product'],
            $method_specific_data['out_of_stock'],
            $method_specific_data['sub'],
            $method_specific_data['total'],
            $method_specific_data['quantity'],
            $method_specific_data['weight'],
            $method_specific_data['volume'],
            $method_specific_data['dimension'],
            $_cart_data['coupon'],
            $_cart_data['reward'],
            $_cart_data['vouchers'],
            $shipping_cost,
            $modifier_amount,
            $_cart_data['grand'],
            $_cart_data['grand_shipping'],
            $_cart_data['distance'],
            $method_specific_data['highest'],
            $method_specific_data['lowest'],
            $method_specific_data['highest_qnty'],
            $method_specific_data['lowest_qnty'],
            $method_specific_data['non_method_sub'],
            $method_specific_data['non_method_quantity'],
            $_cart_data['non_shippable'],
            ''
        );
        if (preg_match('/minHeight|maxHeight|sumHeight|minWidth|maxWidth|sumWidth|minLength|maxLength|sumLength/', $equation)) {
            $placholder[] = '{minHeight}';
            $placholder[] = '{maxHeight}';
            $placholder[] = '{sumHeight}';
            $placholder[] = '{minWidth}';
            $placholder[] = '{maxWidth}';
            $placholder[] = '{sumWidth}';
            $placholder[] = '{minLength}';
            $placholder[] = '{maxLength}';
            $placholder[] = '{sumLength}';
            
            $minHeight = $minWidth = $minLength = PHP_INT_MAX;
            $maxHeight = $maxWidth = $maxLength = PHP_INT_MIN;
            $sumHeight = $sumWidth = $sumLength = 0;
            foreach ($method_specific_data['products'] as $product) {
                $sumHeight += ($product['height_self'] * $product['quantity']);
                if ($minHeight > $product['height_self']) {
                    $minHeight = $product['height_self'];
                }
                if ($maxHeight < $product['height_self']) {
                    $maxHeight = $product['height_self'];
                }
                $sumWidth += ($product['width_self'] * $product['quantity']);
                if ($minWidth > $product['width_self']) {
                    $minWidth = $product['width_self'];
                }
                if ($maxWidth < $product['width_self']) {
                    $maxWidth = $product['width_self'];
                }
                $sumLength += ($product['length_self'] * $product['quantity']);
                if ($minLength > $product['length_self']) {
                    $minLength = $product['length_self'];
                }
                if ($maxLength < $product['length_self']) {
                    $maxLength = $product['length_self'];
                }
            }
            $replacer[] = $minHeight;
            $replacer[] = $maxHeight;
            $replacer[] = $sumHeight;
            $replacer[] = $minWidth;
            $replacer[] = $maxWidth;
            $replacer[] = $sumWidth;
            $replacer[] = $minLength;
            $replacer[] = $maxLength;
            $replacer[] = $sumLength;
        }
        
        /* append other shipping method cost as placeholders */
        foreach ($quote_data as $value) {
            $placholder[] = '{shipping'.$value['tab_id'].'}';
            $replacer[] = $value['cost'];
        }
        /* grouping value */
        for ($i=1; $i <= 10 ; $i++) { 
            $placholder[] = '{group'.$i.'}';
            $group_value = 0;
            if (isset($_cart_data['grouping'][$i])) {
                if ($_cart_data['grouping'][$i]['mode'] == 'sum') {
                    $group_value = array_sum($_cart_data['grouping'][$i]['costs']);
                } else if ($_cart_data['grouping'][$i]['mode'] == 'lowest') {
                    $group_value = min($_cart_data['grouping'][$i]['costs']);
                } else if ($_cart_data['grouping'][$i]['mode'] == 'highest') {
                    $group_value = max($_cart_data['grouping'][$i]['costs']);
                } else if ($_cart_data['grouping'][$i]['mode'] == 'average') {
                    $group_value = array_sum($_cart_data['grouping'][$i]['costs']);
                    if (count($_cart_data['grouping'][$i]['costs']) > 0) {
                        $group_value = $group_value / count($_cart_data['grouping'][$i]['costs']);  
                    }
                }
            }
            $replacer[] = $group_value;
        }
        /* xfeepro value */
        foreach ($_cart_data['xfeepro'] as $code => $value) {
            $placholder[] = '{'.$code.'}';
            $replacer[] = $value;
        }
        /* Other shipping modules */
        $ocm_shipping = $this->ocm->getCache('ocm_shipping');
        if ($ocm_shipping) {
            foreach ($ocm_shipping as $code => $value) {
                $placholder[] = '{mod'.$code.'}';
                $replacer[] = true;
            }
        }

        /* custom fields */
        if (!empty($method_specific_data['custom_fields']) && $method_specific_data['custom_fields']) {
            foreach ($method_specific_data['custom_fields'] as $code => $value) {
                $placholder[] = '{_'.$code.'}';
                $replacer[] = $value;
            }
        }

        $equation = str_replace($placholder, $replacer, $equation);
        /* replace percentage value finally so it won't replace mod operator */
        $equation = preg_replace('/(\d+)%/', '$1*' . ($percent_of/100), $equation);
        /* Removing unwanted placeholder */
        if (strpos($equation, '{') !== false) {
            $equation = preg_replace('/{.*?}/', 0, $equation);
        }
        $condition_status = false;
        $value = (float)$this->calculate_string($equation, $condition_status);
        return array(
            'value'    => $value,
            'status'  => $condition_status 
        );
    }
    private function getDefaultPaymentMethod($address, $total) {
        $this->load->model($this->ocm->setting_ext . '/extension');
        $extension = $this->{'model_' . $this->ocm->setting_ext . '_extension'};
        $results = $extension->getExtensions('payment');
        $method_data = array();
        foreach ($results as $result) {
            if ($this->ocm->getConfig($result['code'] . '_status', 'payment')) {
                $this->load->model('extension/payment/' . $result['code']);
                $method = $this->{'model_extension_payment_' . $result['code']}->getMethod($address, $total);
                if ($method) {
                    $method_data[$result['code']] = $method;
                }
            }
        }
        if ($method_data) {
            $sort_order = array();
            foreach ($method_data as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }
            array_multisort($sort_order, SORT_ASC, $method_data);
            $method_data = array_shift($method_data);
            return isset($method_data['code']) ? $method_data['code'] : '';
        } else {
            return '';
        }
    }
    private function getTotalPackage($container, $items) {
        $keys = array('width', 'length', 'height');
        $letMeTry = function($space, $box) use ($keys) {
            $capable = true;
            foreach ($keys as $key) {
                if ($box[$key] > $space[$key]) {
                    $capable = false;
                    break;
                }
            }
            return $capable;
        };
        $canYouContainMe = function($space, $box) use($letMeTry) {
            if ($letMeTry($space, $box)) {
                return true;
            }
            /* lets swap dimension if it can fit */
            $combinations = array(
                array('width', 'length'),
                array('length', 'height'),
                array('height', 'width')
            );
            foreach ($combinations as $combination) {
                $_box = $box;
                $_box[$combination[0]] = $box[$combination[1]];
                $_box[$combination[1]] = $box[$combination[0]];
                if ($letMeTry($space, $_box)) {
                    return true;
                }
            }
            return false;
        };
        $containerization = function(&$spaces, &$boxes, &$capacity) use ($keys, $canYouContainMe, &$containerization) {
            $space = array_pop($spaces);
            $which = false;
            foreach ($boxes as $i => $box) {
                if ($box['volume'] <= $space['volume']) {
                    if ($canYouContainMe($space, $box)) {
                        $capacity -= $box['weight'];
                        $which = $i;
                        break;
                    }
                }
            }
            if ($which !== false) {
                unset($boxes[$which]);
                foreach ($keys as $key) {
                    if ($space[$key] - $box[$key] > 0) {
                        $_space = array();
                        $_space[$key] = $space[$key] - $box[$key];
                        if ($key == 'height') {
                            $_space['length'] = $box['length'];
                            $_space['width'] = $box['width'];
                        } else {
                            $_space['height'] = $space['height'];
                            if ($key == 'width') {
                                $_space['length'] = $space['length'] - $box['length'] > 0 ? $box['length'] : $space['length'];
                            } else {
                                $_space['width'] = $space['width'];
                            }
                        }
                        $_space['volume'] = $_space['length'] * $_space['width'] * $_space['height'];
                        array_unshift($spaces, $_space);
                    }
                }
            }
            if ($spaces && $boxes && $capacity > 0) {
                $containerization($spaces, $boxes, $capacity);
            }
        };
        /* default container value */
        foreach ($keys as $key) {
            if (empty($container[$key])) {
                $container[$key] = 100000;
            }
        }
        $container['volume'] = $container['length'] * $container['width'] * $container['height'];
        /* sort boxes */
        $boxes = array();
        $sort_order = array();
        $index = 0;
        foreach ($items as $item) {
            for ($i = 1; $i <= $item['quantity']; $i++) {
                $boxes[$index] = $item['bin'];
                $sort_order[$index] = $item['bin']['capacity'];
                $index++;
            }
        }
        array_multisort($sort_order, SORT_DESC, $boxes);
        $count = 0;
        while ($boxes) {
            $spaces = array($container);
            $capacity = $container['weight'];
            $containerization($spaces, $boxes, $capacity);
            $count++;
        }
        return $count;
    }
    private function syncAccountFields($custom_field) {
        $this->load->model('account/customer');
        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
        $acc_custom_field = VERSION >= '2.1.0.0' ? json_decode($customer_info['custom_field'], true) : unserialize($customer_info['custom_field']);
        if (is_array($acc_custom_field)) {
            foreach ($acc_custom_field as $_custom_field) {
                if (is_array($_custom_field)) {
                    $custom_field = array_merge($custom_field, $_custom_field);
                } else {
                    $custom_field[] = $_custom_field;
                }
            }
        }
        return $custom_field;
    }
    private function getCSS($estimator) {
        $css = '<style type="text/css">
                    .xshippingpro-box {
                        background: #f5f5f5;
                        margin-bottom: 10px;
                    }
                    .popup-quickview .xshippingpro-box, .popup-options .xshippingpro-box {
                        display: none;
                    }
                    .xshippingpro-box .shipping-header {
                        font-size: 15px;
                        padding: 7px 10px;
                    }
                    .xshippingpro-box .shipping-fields {
                        padding: 0px 8px 8px 8px;
                    }
                    .xshippingpro-box .shipping-field {
                        margin-bottom: 5px;
                    }
                    .xshippingpro-box .xshippingpro-error {
                        border: 1px solid #fb6969;
                    }
                    .xshippingpro-quotes {
                        background: #f5f5f5;
                        padding: 5px 10px;
                        margin-bottom: 10px;
                    }
                    .xshippingpro-quotes .xshippingpro-quote {
                        margin-bottom: 5px;
                    }
                    .xshippingpro-quotes .xshippingpro-quote:last-child {
                        margin-bottom: 0px;
                    }
                    .xshippingpro-option-error {
                        color: #dc4747;
                    }
                    .xshippingpro-options {
                        margin: 5px 0px;
                    }
                    .xshippingpro-desc {
                        color: #999999;
                        font-size: 11px;
                        display:block
                    }
                    .xshippingpro-logo {
                        margin-right: 3px; 
                        vertical-align: middle;
                        max-height: 50px;
                    }
                    .xshippingpro-option-wrapper {
                        margin-bottom: 5px;
                    }
                    /* Journal 3 laytout for suboption */
                    .quick-checkout-wrapper .radio {
                        flex-direction: column;
                        align-items: start;
                    }
                    .xform-form {
                        background: #f7f7f7;
                        padding: 10px;
                    }
            </style>';

        if ($estimator && $estimator['css']) {
          $css .= '<style type="text/css">'.$estimator['css'].'</style>';
        }
        return $css;
    }
    private function getJS($estimator) {
        $this->load->language($this->ext_path);
        $selectors = array();
        $selectors['estimator'] = '#product';
        $selectors['shipping_error'] = '#content'; // TODO - define in selector section
        $meta = array();
        $meta['country_id'] = !$this->ocm->isCheckoutPage() ? $this->config->get('config_country_id') : false;
        $meta['product_id'] = isset($this->request->get['product_id']) ? $this->request->get['product_id'] : 0;
        if ($estimator) {
            if (!isset($estimator['fields']) || !is_array($estimator['fields'])) {
                $estimator['fields'] = array();
            }
            if (in_array('country', $estimator['fields'])) {
                $meta['country'] = true;
            }
            if (in_array('zone', $estimator['fields'])) {
                $meta['zone'] = true;
            }
            if (in_array('postal', $estimator['fields'])) {
                $meta['postal'] = true;
            }
            if (isset($estimator['selector']) && $estimator['selector']) {
                $selectors['estimator'] = $estimator['selector'];
            }
        }
        $_selector = $this->ocm->getConfig('xshippingpro_selector', $this->mtype);
        if (isset($_selector['logo']) && $_selector['logo']) {
            $selectors['logo'] = $this->ocm->html_decode($_selector['logo']);
        }
        if (isset($_selector['desc']) && $_selector['desc']) {
            $selectors['desc'] = $this->ocm->html_decode($_selector['desc']);
        }
        $url = array(
            'country' => VERSION >=  '2.1.0.1' ? 'index.php?route=extension/total/shipping/country' : 'index.php?route=checkout/shipping/country',
            'estimate' => 'index.php?route=extension/shipping/xshippingpro/estimate_shipping',
            'update'   => 'index.php?route=extension/shipping/xshippingpro/update_shipping'
        );

        $lang = array();
        $lang['header'] = $this->language->get('xshippingpro_estimator_header');
        $lang['tab'] = $this->language->get('xshippingpro_estimator_tab');
        $lang['country'] = $this->language->get('xshippingpro_estimator_country');
        $lang['zone'] = $this->language->get('xshippingpro_estimator_zone');
        $lang['postal'] = $this->language->get('xshippingpro_estimator_postal');
        $lang['no_data'] = $this->language->get('xshippingpro_estimator_no_data');
        $lang['btn'] = $this->language->get('xshippingpro_estimator_button');
        $lang['select'] = $this->language->get('xshippingpro_select');
        $lang['error'] = $this->language->get('xshippingpro_select_error');
        if (function_exists('mb_convert_encoding')) {
            foreach ($lang as $key => $value) {
                $lang[$key] = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
            }
        }
        $_xshippingpro = array();
        $_xshippingpro['url'] = $url;
        $_xshippingpro['meta'] = $meta;
        $_xshippingpro['lang'] = $lang;
        $_xshippingpro['selectors'] = $selectors;
        $_xshippingpro['sub_options'] = false;
        $_xshippingpro['desc'] = false;
        $_xshippingpro['logo'] = false;
        $_xshippingpro['tab'] = isset($estimator['tab']) ? (boolean)$estimator['tab'] : false;
        $_xshippingpro['is_checkout'] = $this->ocm->isCheckoutPage() ? true : false;
        if ($this->ocm->isCheckoutPage()) {
           $sub_options = $this->getSubOptions();
           $desc_logo = $this->getShippingDesc();
           $shortcodes = $this->applyShortcode($desc_logo['desc']);
           $_xshippingpro['xform'] = isset($shortcodes['xform']) ? $shortcodes['xform'] : false;
           if ($sub_options) {
              $_xshippingpro['sub_options'] = $sub_options;
           }
           if ($desc_logo['desc']) {
              $_xshippingpro['desc'] = $desc_logo['desc'];
           }
           if ($desc_logo['logo']) {
              $_xshippingpro['logo'] = $desc_logo['logo'];
           }
           $_xshippingpro['city'] = $desc_logo['city']; 
           $_xshippingpro['payment'] = $desc_logo['payment'];
           $_xshippingpro['optional_options'] = $this->getOptionalSubOptions();
        }

        if (!$this->ocm->isCheckoutPage() && isset($meta['country'])) {
            $this->load->model('localisation/country');
            $_xshippingpro['country'] = $this->model_localisation_country->getCountries();
        }
        $js = '<script type="text/javascript">';
        $js .= 'var _xshippingpro = '.json_encode($_xshippingpro).';';
        $js .= 'if (!window.xshippingproestimator && window.XshippingproEstimator) window.xshippingproestimator = new XshippingproEstimator();';
        $js .= 'if (!window.xshippingproextender && window.XshippingproExtender) window.xshippingproextender = new XshippingproExtender();';
        $js .= '</script>';
        $js .= $this->getXFormAsset($js);
        return $js;
    }
    public function getScript() {
        $estimator =  $this->ocm->getConfig('xshippingpro_estimator', $this->mtype);
        $shipping_xshippingpro = $this->ocm->getConfig('xshippingpro_status', $this->mtype);
        $product_id = isset($this->request->get['product_id']) ? $this->request->get['product_id'] : 0;

        $store_id = $this->config->get('config_store_id');
        $estimator_on_store = true;
        if (isset($estimator['store']) && !in_array($store_id, $estimator['store'])) {
            $estimator_on_store = false;
        }
        $html = '';
        if ($shipping_xshippingpro && (($this->ocm->isCheckoutPage() && !$this->ocm->isCartPage()) || ($estimator_on_store && isset($estimator['status'])))) {
            $shipping_row = $this->db->query("SELECT shipping FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'")->row;
            $shipping_req = true;
            if ($shipping_row) {
                $shipping_req = $shipping_row['shipping'] ? true : false;
            }
            if ($shipping_req) {
                $html .= $this->getCSS($estimator);
                $html .= $this->getJS($estimator);
                $html .= '<script src="catalog/view/javascript/xshippingpro.min.js?v=4.1.3" defer type="text/javascript"></script>';
            }
        }
        return $html;
    }
    //expose api for controller 
    public function getFinalDesc($desc) {
        if ($desc) {
            $desc = array('0' => $desc);
            $this->applyShortcode($desc);
            return $desc ? $desc[0] : '';
        }
        return $desc;
    }
    public function applyShortcode(&$desc) {
        $rex = '/\[(\w+)\s?([^]]*)\](.*?)\[\/\w+\]/m';
        $xform_installed = -1;
        $return = array();
        foreach($desc as $tab_id => $text) {
            if ($shortcodes = $this->ocm->parseShortcode($text)) {
                foreach ($shortcodes as $shortcode) {
                    $render = '_' . $shortcode['name'] . 'Render';
                    if (method_exists($this, $render) && $_return = $this->{$render}($shortcode)) {
                        $output = $_return['apply'] ?  $_return['output'] : '';
                        $desc[$tab_id] = str_replace($shortcode['full'], $output, $desc[$tab_id]);
                        if (!isset($return[$shortcode['name']])) $return[$shortcode['name']] = array();
                        $return[$shortcode['name']][$tab_id] = $_return['output'];
                        // if shortcode made it empty, reset 
                        if (!$desc[$tab_id]) {
                            unset($desc[$tab_id]);
                        }
                    }
                }
            }
        }
        return $return;
    }
    private function getXFormAsset($xform_str) {
        $return = '';
        if ($this->ocm->getConfig('xform_status', 'module') && strpos($xform_str, 'bootstrap-xform') !== false) {
            $xform = new \Xform($this->registry);
            $fake_fields = array();
            if (strpos($xform_str, 'xform-date') !== false || strpos($xform_str, 'xform-time') !== false) {
                $fake_fields[] = array('field_type' => 'date');
            }
            $return = $xform->getAssets(false, $fake_fields, array(), true);
        }
        return $return;
    }
    private function getSubOptionComponent($sub_options) {
        $language_id = $this->config->get('config_language_id');
        $language_ids = array();
        $components = array();
        // detection if multi-level suboptions available
        $is_multi_level = false;
        $prev = 1;
        foreach($sub_options as $i => $sub_option) {
            if ($is_multi_level) {
                break;
            }
            $key = $i;
            $str = isset($sub_option['name'][$language_id]) ? trim($sub_option['name'][$language_id]) : '';
            if ($str && preg_match_all('/^(\d\.?)+/', $str, $matches)) {
                $key = str_replace('.', '_', rtrim($matches[0][0], '.'));
            }
            $parts = explode('_', $key);
            if ($prev == 1 && count($parts) == 2) {
                $is_multi_level = true;
            } else {
                $prev = 1;
            }
        }

        foreach($sub_options as $i => $sub_option) {
            $str = isset($sub_option['name'][$language_id]) ? trim($sub_option['name'][$language_id]) : '';
            $key = $i;
            if ($str && $is_multi_level && preg_match_all('/^(\d\.?)+/', $str, $matches)) {
                $key = str_replace('.', '_', rtrim($matches[0][0], '.'));
            }
            // sanitize and cleanse name
            $names = array();
            $labels = array();

            foreach($sub_option['name'] as $language_id => $name) {
                $label = '';
                if ($is_multi_level) {
                    $name = trim(preg_replace('/^(\d\.?)+/', '', $name));
                }
                if (strpos($name, '|') !== false) {
                   list($name, $label) = explode('|', $name);
                }
                $names[$language_id] = $name;
                $labels[$language_id] = $label;
            }

            $operator = substr(trim($sub_option['cost']),0,1);
            $operator = ($operator == '+' || $operator == '-') ? $operator : '';
            $cost = (float)(trim($sub_option['cost'], '+-'));
            $components[$key] = array(
                'name'      => $names,
                'label'     => $labels,
                'path'      => array(),
                'cost'      => $cost,
                'operator'  => $operator,
                'child'     => true,
                'level'     => count(explode('_', $key)) - 1
            );
            $path = array();
            $parts = explode('_', $key);
            $_key = '';
            foreach($parts as $j => $part) {
                $_key .= ($_key ? '_' : '') . $part;
                if (isset($components[$_key])) {
                    // full name for all languages
                    foreach($components[$key]['name'] as $language_id => $name) {
                        if (!isset($path[$language_id])) {
                            $path[$language_id] = '';
                        }
                        $path[$language_id] .= ($path[$language_id] ? ' - ' : '') . $components[$_key]['name'][$language_id];
                    }
                    if (empty($components[$key]['cost'])) {
                        $components[$key]['cost'] = $components[$_key]['cost']; // inherit cost from parent
                        $components[$key]['operator'] = $components[$_key]['operator']; // inherit operator from parent
                    }
                    if ($j != count($parts) -1) {
                        $components[$_key]['child'] = false; 
                    }
                }
            }
            $components[$key]['path'] = $path;
        }
        return $components;
    }
    private function setBlockInfo(&$method_specific_data, $price_result, $type) {
        $sort_order = array();
        $products = $method_specific_data['products'];
        foreach ($products as $key => $product) {
            $sort_order[$key] = $product['price'];
        }
        array_multisort($sort_order, SORT_ASC, $products);
        $block_asc = 0;
        $block_value = 0;

        foreach ($products as $product) {
            for ($i=1; $i <= $product['quantity']; $i++) { 
                if ($block_value >= $price_result['blockValue']) {
                    break;
                }
                $block_asc += $product['price'];
                $block_value += $product[$type] / $product['quantity'];
            }
        }
        array_multisort($sort_order, SORT_DESC, $products);
        $block_desc = 0;
        $block_value = 0;
        foreach ($products as $product) {
            for ($i=1; $i <= $product['quantity']; $i++) { 
                if ($block_value >= $price_result['blockValue']) {
                    break;
                }
                $block_desc += $product['price'];
                $block_value += $product[$type] / $product['quantity'];
            }
        }
        $method_specific_data['block_asc'] = $block_asc;
        $method_specific_data['block_desc'] = $block_desc;
    }
    /* HOOK METHOD HERE */
    /* must start with hook_
    public function hook_custom_field($value, $cart_products, $name) {
        return true;
    } */
}