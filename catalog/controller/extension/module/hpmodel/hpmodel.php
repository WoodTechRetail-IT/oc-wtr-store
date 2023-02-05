<?php

class ControllerExtensionModuleHpmodelHpmodel extends Controller {
    private $types;    
    
    public function getForm() {
        if (!empty($this->request->post['hpmodel_orig']) || !isset($this->request->get['product_id'])) return;
        
        $product_id = (int)$this->request->get['product_id'];
        
        $data = array();
        
        $this->load->model('extension/module/hpmodel');

        $parent = $this->model_extension_module_hpmodel->getParent($product_id);
        if (!$parent) return;
                    
        $this->load->model('tool/image');
        $this->load->model('tool/imagequadr');
        $this->load->model('catalog/product');
        
        if (empty($this->types)) $this->types = $this->model_extension_module_hpmodel->getTypes();
        if (empty($parent['type_id']) || empty($this->types[$parent['type_id']])) return;
        
        $language_id = (int)$this->config->get('config_language_id');
        $type = $this->types[$parent['type_id']];

        if (!empty($type['setting']['redirect']) && $product_id != $parent['parent_id']) {
            $this->response->redirect($this->url->link('product/product', 'product_id=' . $parent['parent_id'], 'SSL') . (!empty($type['setting']['hash']) ? '#' . $product_id : ''), 301);
        }
            
        $type['setting']['child_image_width'] = !empty($type['setting']['product_image_width']) ? $type['setting']['product_image_width'] : 50;
        $type['setting']['child_image_height'] = !empty($type['setting']['product_image_height']) ? $type['setting']['product_image_height'] : 50;
        $type['setting']['limit'] = 0;
        $type['setting']['product_columns'] = !empty($type['setting']['product_columns']) ? $type['setting']['product_columns'] : array();
        $type['setting']['product_attributes'] = !empty($type['setting']['product_attributes']) ? $type['setting']['product_attributes'] : array();
        $type['setting']['replace_image'] = !empty($type['setting']['replace_image']) ? $type['setting']['replace_image'] : false;
        $type['setting']['variant'] = $type['setting']['product_variant'];
        $type['setting']['title'] = !empty($type['setting']['product_title'][$language_id]) ? $type['setting']['product_title'][$language_id] : '';
        $type['setting']['product_id'] = $product_id;
        
        $childs = $this->model_extension_module_hpmodel->getChilds($parent['parent_id'], 0, $parent['type_id']);
        
        $result = $this->prepareProducts($type['setting'], $childs, $parent);
        $data['products'] = $result['products'];
        $data['groups'] = $result['groups'];
        
        
        if (!empty($type['setting']['hidden_if_next'])) {
            $no_stock = true;
            foreach ($data['products'] as $product) {
                if ($product['product_id'] == $product_id) {
                    if ($product['quantity'] > 0) $no_stock = false;
                    break;
                }
            }
            if ($no_stock) {
                foreach ($data['products'] as $product) {
                    if ($product['quantity'] > 0) {
                        $product_id = $product['product_id'];
                        $this->request->get['product_id'] = $product_id;
                        break;
                    }
                }
            }
        }

        if (count($data['products']) <= 1) {
            if (isset($data['products'][0]) && $data['products'][0]['product_id'] != $product_id) {
                $this->request->get['product_id'] = $data['products'][0]['product_id'];
            }
            return;
        }
        
        if (!empty($type['setting']['plink'])) {
            $agroups = array();
            foreach ($data['groups'] as $gkey => &$group) {
                $group['type'] = 'type_plink';
                foreach ($group['value'] as $vkey => &$value) {
                    $value['active'] = in_array($product_id, $value['id']);                    
                    if ($value['active']) $agroups[$gkey] = $value['id'];
                }
            }
            foreach ($data['groups'] as $gkey => &$group) {
                $group['type'] = 'type_plink';
                foreach ($group['value'] as $vkey => &$value) {
                    if (!$value['active']) {
                        $products = $value['id'];
                        foreach ($agroups as $akey => $agroup) {
                            if ($gkey != $akey) $products = array_intersect($products, $agroup);
                        }
                        if ($products) {
                            $item_id = array_shift($products);
                            $value['disabled'] = false;
                        } else {
                            $item_id = array_shift($value['id']);
                            $value['disabled'] = true;
                        }                        
                        $value['href'] = $this->url->link('product/product', 'product_id=' . $item_id, 'SSL');
                    }
                }
            }
        }
        
        $data['product_id'] = $product_id;
        $data['selected_product_id'] = $product_id;

        $data['custom_css'] = !empty($type['setting']['custom_css']) ? $type['setting']['custom_css'] : false;
        $data['custom_js'] = !empty($type['setting']['custom_js']) ? $type['setting']['custom_js'] : false;
        
        $data['selector'] = !empty($type['setting']['product_selector']) ? $type['setting']['product_selector'] : false;
        $data['position'] = !empty($type['setting']['product_position']) ? $type['setting']['product_position'] : false;
        
        $data['hash'] = !empty($type['setting']['hash']) ? $type['setting']['hash'] : false;
        $data['redirect'] = !empty($type['setting']['redirect']) ? $type['setting']['redirect'] : false;
        $data['plink'] = !empty($type['setting']['plink']) ? $type['setting']['plink'] : false;
        $data['name_as_title'] = !empty($type['setting']['product_name_as_title']) ? $type['setting']['product_name_as_title'] : false;
        $data['name_a'] = !empty($type['setting']['after_title']) ? $type['setting']['after_title'] : false;
        
        $data['replace_h1'] = !empty($type['setting']['replace_h1']) ? $type['setting']['replace_h1'] : false;
        $data['replace_image'] = !empty($type['setting']['replace_image']) ? $type['setting']['replace_image'] : false;
        $data['replace_desc'] = !empty($type['setting']['replace_desc']) ? $type['setting']['replace_desc'] : false;
        $data['replace_att'] = !empty($type['setting']['replace_att']) ? $type['setting']['replace_att'] : false;
        $data['variant'] = $type['setting']['product_variant'];
        $data['selector'] = !empty($type['setting']['product_selector']) ? $type['setting']['product_selector'] : false;
        $data['position'] = !empty($type['setting']['product_position']) ? $type['setting']['product_position'] : false;
        
        $data['path'] = !empty($this->request->get['path']) ? '&path='.$this->request->get['path'] : '';
        
        $data['text_select'] = $this->language->get('text_select');
        
        $result = array();
        
        if (floatval(VERSION) >= 2.2) {
            $data['config'] = $this->load->view('extension/module/hpmodel/config', $data);

            $result['html'] = $this->load->view('extension/module/hpmodel/hpmodel', $data);
        } else {
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/module/hpmodel/config.tpl')) {
                $data['config'] = $this->load->view($this->config->get('config_template') . '/template/extension/module/hpmodel/config.tpl', $data);
            } else {
                $data['config'] = $this->load->view('default/template/extension/module/hpmodel/config.tpl', $data);
            }
            
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/module/hpmodel/hpmodel.tpl')) {
                $result['html'] = $this->load->view($this->config->get('config_template') . '/template/extension/module/hpmodel/hpmodel.tpl', $data);
            } else {
                $result['html'] = $this->load->view('default/template/extension/module/hpmodel/hpmodel.tpl', $data);
            }
        }
        
        return $result;
    }
    
    private function prepareProducts($setting, $childs, $parent) {
        $language_id = (int)$this->config->get('config_language_id');
        
        $product_ids = array();
        foreach ($childs as $child) {
            $product_ids[] = $child['product_id']; 
        }

        $products_data = $this->model_extension_module_hpmodel->getProducts($product_ids);
        
        if (empty($setting['product_columns'])) $setting['product_columns'] = array();        
        
        $attributes_id = array();
        $filters_id = array();
        $config_groups = array();
        
        $col_data = array();
        foreach ($setting['product_columns'] as $key => $column) {            
            if ($column == 'none') continue;
            
            $col_data[$key]['type'] = $column;
            if ($column == 'attribute') {
                $attribute_id = $setting['product_attributes'][$key];
                $col_data[$key]['id'] = $attribute_id;
                if (!in_array($attribute_id, $attributes_id)) $attributes_id[] = $attribute_id;
            }            
            if ($column == 'filter') {
                $filter_id = $setting['product_filters'][$key];
                $col_data[$key]['id'] = $filter_id;
                if (!in_array($filter_id, $filters_id)) $filters_id[] = $filter_id;
            }            
        }
        
        $key_data = array();

        if (!empty($setting['after_title']) && $setting['after_title'] != 'none') {
            $key_data['type'] = $setting['after_title'];
            if ($key_data['type'] == 'attribute') {
                $attribute_id = $setting['after_title_attribute'];
                $key_data['id'] = $attribute_id;
                if (!in_array($attribute_id, $attributes_id)) $attributes_id[] = $attribute_id;
            }            
            if ($key_data['type'] == 'filter') {
                $filter_id = $setting['after_title_filter'];
                $key_data['id'] = $filter_id;
                if (!in_array($filter_id, $filters_id)) $filters_id[] = $filter_id;
            }            
        } else {
            $key_data['type'] = 'product_id';
        }
        
        $config_groups[] = array(
            'key'         => $key_data,
            'cols'        => $col_data,
            'title'       => html_entity_decode($setting['title'], ENT_QUOTES, 'UTF-8'),
            'type'        => $setting['variant'],
            'value'       => array(),
        );
        
        if (!empty($setting['sub_group'])) {
            foreach ($setting['sub_group'] as $sub_group) {
                $col_data = array();
                
                foreach ($sub_group['col'] as $key => $column) {            
                    if ($column == 'none') continue;
                    
                    $values = array('type' => $column);        
                    
                    if ($column == 'attribute') {
                        $attribute_id = $sub_group['col_attr'][$key];
                        if ($attribute_id == 0) {
                            continue;
                        }
                        $values['id'] = $attribute_id;
                        if (!in_array($attribute_id, $attributes_id)) $attributes_id[] = $attribute_id;
                    }
                    if ($column == 'filter') {
                        $filter_id = $sub_group['col_filter'][$key];
                        if ($filter_id == 0) {
                            continue;
                        }
                        $values['id'] = $filter_id;
                        if (!in_array($filter_id, $filters_id)) $filters_id[] = $filter_id;
                    }
                    
                    $col_data[$key] = $values;
                }
                
                if (empty($sub_group['key']) || $sub_group['key'] == 'none') {
                    continue;
                }
                
                $key_data = array('type' => $sub_group['key']);
                
                if ($key_data['type'] == 'attribute') {
                    $attribute_id = $sub_group['attr'];
                    if ($attribute_id == 0) {
                        continue;
                    }
                    $key_data['id'] = $attribute_id;
                    if (!in_array($attribute_id, $attributes_id)) $attributes_id[] = $attribute_id;
                }
                if ($key_data['type'] == 'filter') {
                    $filter_id = $sub_group['filter'];
                    if ($filter_id == 0) {
                        continue;
                    }
                    $key_data['id'] = $filter_id;
                    if (!in_array($filter_id, $filters_id)) $filters_id[] = $filter_id;
                }
                
                if (!$col_data) $col_data[0] = $key_data;
        
                $config_groups[] = array(
                    'key'         => $key_data,
                    'cols'        => $col_data,
                    'title'       => isset($sub_group['title'][$language_id]) ? html_entity_decode($sub_group['title'][$language_id], ENT_QUOTES, 'UTF-8') : '',
                    'type'        => $sub_group['variant'],
                    'value'       => array(),
                );
            }
        }
        
        
        $products = array();
        
        foreach ($products_data as $product_info) {
            $product_id = $product_info['product_id'];

            if (!empty($setting['hidden_if_null']) && $product_info['quantity'] < 1) continue;
            
            if ($attributes_id) {
                $attributes = $this->model_extension_module_hpmodel->getProductAttributes($product_id, $attributes_id);
            } else {
                $attributes = array();
            }

            if ($filters_id) {
                $filters = $this->model_extension_module_hpmodel->getProductFilters($product_id, $filters_id);
            } else {
                $filters = array();
            }
            
            $keys = array($product_info['product_id']);
            
            foreach ($config_groups as $group_id => &$config_group) {
                $column = $config_group['key']['type'];
                
                if ($config_group['key']['type'] == 'attribute') {
                    $key_value = !empty($attributes[$config_group['key']['id']]) ? $attributes[$config_group['key']['id']] : '';
                } else if ($config_group['key']['type'] == 'filter') {
                    $key_value = !empty($filters[$config_group['key']['id']]) ? $filters[$config_group['key']['id']] : '';
                } else {
                    $key_value = isset($product_info[$column]) ? $product_info[$column] : '';
                }
                if (count($config_groups) == 1 || ($group_id == 0 && !$key_value)) $key_value = $product_id;
                
                if (!$key_value) {
                    continue;
                }
                
                $key_value = utf8_strtolower($key_value);
                $key_value = str_replace(array(' ','-','+','=','%','#','@',',','.','/','\\'), '_', $key_value);
                if (count($config_groups) > 1) $keys[] = $key_value;
                
                if (!isset($config_group['value'][$key_value])) {
                    $config_group['value'][$key_value] = array(
                        'view'      => array(),
                        'view_text' => '',
                        'id'        => array()
                    );
                }
                
                $config_group['value'][$key_value]['id'][] = $product_id;
                
                if ($product_info['product_id'] == $setting['product_id']) {
                    $config_group['value'][$key_value]['active'] = true;
                } else if (!isset($config_group['value'][$key_value]['active'])) {
                    $config_group['value'][$key_value]['active'] = false;
                }
                
                if ($config_group['value'][$key_value]['view']) continue;
                
                $view = array();
                $view_text = array();
                
                foreach ($config_group['cols'] as $key => $col) {
                    $value = isset($product_info[$col['type']]) ? $product_info[$col['type']] : '';
                    $value_text = $value;
                    
                    switch ($col['type']) {
                        case 'attribute':
                            if (!empty($attributes[$col['id']])) {
                                $value = $attributes[$col['id']];
                            } else {  
                                $value = false;
                            }
                            $value_text = $value;
                            break;
                        case 'filter':
                            if (!empty($filters[$col['id']])) {
                                $value = $filters[$col['id']];
                            } else {  
                                $value = false;
                            }
                            $value_text = $value;
                            break;
                        case 'col_weight':
                            $value = (float)$product_info['weight'];
                            $value_text = $value;
                            break;
                        case 'col_size':
                            $value = (float)$product_info['length'].'x'.(float)$product_info['width'].'x'.(float)$product_info['height'];
                            $value_text = $value;
                            break;
                        case 'name_exp_last':
                            $name_arr = explode(' ', $product_info['name']);
                            $value = array_pop($name_arr);
                            $value_text = $value;
                            break;
                        case 'price':
                            if ((float)$product_info['special']) {
                                $value = '<span class="hprice-old">' . $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) . '</span> <span class="hprice-new">' . $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) . '</span>';
                            } else {
                                $value = '<span class="hprice">' . $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) . '</span>';
                            }
                            $value_text = $this->currency->format($this->tax->calculate((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                            break;
                        case 'image':
                            $view = array();
                            $text = implode(', ', $view_text);
                            $thumb_image = !empty($product_info['image']) ? $product_info['image'] : 'no_image.png';
                            $value = '<img src="' . $this->model_tool_image->resize($thumb_image, $setting['child_image_width'], $setting['child_image_height']) . '" alt="' . ($text ? $text : $product_info['name']) . '" ' . ($text ? 'data-toggle="tooltip" title="'.$text.'"' : 'title="'.$product_info['name'].'"') . ' />';
                            $value_text = false;                            
                            break;
                    }
                    
                    if ($value) $view[$col['type']] = $value;
                    if ($value_text) $view_text[$key] = $value_text;
                }
                
                if (!$view) {
                    unset($config_group['value'][$key_value]);
                    continue;
                }
                
                $config_group['value'][$key_value]['view'] = $view;
                $config_group['value'][$key_value]['view_text'] = implode(' ', $view_text);
            }
            
            $product_info['view_text'] = $product_info['model'];
            $product_info['price_value'] = $product_info['price'];
            $product_info['special_value'] = $product_info['special'];
            
            if (!empty($setting['redirect'])) {
                $product_info['href'] = $this->url->link('product/product', 'product_id=' . $parent['parent_id'] . (!empty($this->request->get['path']) ? '&path='.$this->request->get['path'] : '')) . '#' . implode('-', $keys);
            } else {
                $product_info['href'] = $this->url->link('product/product', 'product_id=' . $product_info['product_id'] . (!empty($this->request->get['path']) ? '&path='.$this->request->get['path'] : ''));
            }
            $product_info['href'] = html_entity_decode($product_info['href'], ENT_QUOTES, 'UTF-8');
            
            $product_info['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            $product_info['special'] = (float)$product_info['special'] ? $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) : false;
            
            if (!empty($setting['replace_image'])) {
                if (!$product_info['product_image']) $product_info['product_image'] = 'no_image.png';





                if ($product_info['pmtemplate'] == 'productpol') {
                    if (version_compare(VERSION, '3.0', '>=')) {
                        $product_info['thumb'] = $this->model_tool_imagequadr->resize($product_info['product_image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                    } else {
                        $product_info['thumb'] = $this->model_tool_imagequadr->resize($product_info['product_image'], $this->config->get($this->config->get('config_theme') . '_image_product_width') ? $this->config->get($this->config->get('config_theme') . '_image_product_width') : $this->config->get('config_image_product_width'), $this->config->get($this->config->get('config_theme') . '_image_product_height') ? $this->config->get($this->config->get('config_theme') . '_image_product_height') : $this->config->get('config_image_product_height'));
                    }
                } elseif ($product_info['pmtemplate'] == 'productdver') {
                    if (version_compare(VERSION, '3.0', '>=')) {
                        $product_info['thumb'] = $this->model_tool_image->resize($product_info['product_image'], 112, 228);
                    } else {
                        $product_info['thumb'] = $this->model_tool_image->resize($product_info['product_image'], $this->config->get($this->config->get('config_theme') . '_image_product_width') ? $this->config->get($this->config->get('config_theme') . '_image_product_width') : $this->config->get('config_image_product_width'), $this->config->get($this->config->get('config_theme') . '_image_product_height') ? $this->config->get($this->config->get('config_theme') . '_image_product_height') : $this->config->get('config_image_product_height'));
                    }
                } else {
                    if (version_compare(VERSION, '3.0', '>=')) {
                        $product_info['thumb'] = $this->model_tool_image->resize($product_info['product_image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                    } else {
                        $product_info['thumb'] = $this->model_tool_image->resize($product_info['product_image'], $this->config->get($this->config->get('config_theme') . '_image_product_width') ? $this->config->get($this->config->get('config_theme') . '_image_product_width') : $this->config->get('config_image_product_width'), $this->config->get($this->config->get('config_theme') . '_image_product_height') ? $this->config->get($this->config->get('config_theme') . '_image_product_height') : $this->config->get('config_image_product_height'));
                    }
                }

            }
            
            $products[$product_id] = $product_info;
            if ((int)$setting['limit'] && count($products) >= $setting['limit']) break;
        }
        
        $product_group = array();
        
        foreach ($config_groups as $group) {
            if (!$group['value']) {
                continue;
            }
            foreach ($group['value'] as &$value) {
                $value['ids'] = implode(',', $value['id']);
            }
            $product_group[] = $group;
        }
        
        return array(
            'products'  => $products,
            'groups'    => $product_group,
        );
    }
    
    public function getCategoryBlock($product_info) {
        $data = array();
        
        if (!isset($product_info['product_id'])) return $product_info;
        
        $this->load->model('extension/module/hpmodel');

        $parent = $this->model_extension_module_hpmodel->getParent($product_info['product_id']);
        if (!$parent) return $product_info;
        
        $this->load->model('catalog/product');
        
        if (empty($this->types)) $this->types = $this->model_extension_module_hpmodel->getTypes();
        if (empty($parent['type_id']) || empty($this->types[$parent['type_id']]) || empty($this->types[$parent['type_id']]['setting']['category_show'])) return $product_info;
        
        $language_id = (int)$this->config->get('config_language_id');
        $type = $this->types[$parent['type_id']];

        $type['setting']['child_image_width'] = !empty($type['setting']['category_image_width']) ? $type['setting']['category_image_width'] : 50;
        $type['setting']['child_image_height'] = !empty($type['setting']['category_image_height']) ? $type['setting']['category_image_height'] : 50;
        $type['setting']['limit'] = !empty($type['setting']['category_limit']) ? (int)$type['setting']['category_limit'] : 0;
        $type['setting']['product_columns'] = !empty($type['setting']['category_columns']) ? $type['setting']['category_columns'] : array();
        $type['setting']['product_attributes'] = !empty($type['setting']['category_attributes']) ? $type['setting']['category_attributes'] : array();
        $type['setting']['replace_image'] = !empty($type['setting']['category_replace_image']) ? $type['setting']['category_replace_image'] : false;
        $type['setting']['variant'] = $type['setting']['category_variant'];
        $type['setting']['title'] = !empty($type['setting']['category_title'][$language_id]) ? $type['setting']['category_title'][$language_id] : '';
        $type['setting']['sub_group'] = !empty($type['setting']['category_sub_group']) ? $type['setting']['category_sub_group'] : array(); 
        $type['setting']['after_title'] = !empty($type['setting']['category_after_title']) ? $type['setting']['category_after_title'] : 'none';
        $type['setting']['after_title_attribute'] = !empty($type['setting']['category_after_title_attribute']) ? $type['setting']['category_after_title_attribute'] : false;        
        $type['setting']['product_id'] = $product_info['product_id'];
        
        $childs = $this->model_extension_module_hpmodel->getChilds($parent['parent_id'], 0, $parent['type_id']);
        
        $result = $this->prepareProducts($type['setting'], $childs, $parent);
        $data['products'] = $result['products'];
        $data['groups'] = $result['groups'];
        
        /*
        if (!empty($result['groups'][0])) {
            $data['products'] = array();
            foreach ($result['groups'][0]['value'] as $value) {
                $product_id = $value['id'][0];
                $data['products'][$product_id] = $gproducts[$product_id];
                $data['products'][$product_id]['view'] = $value['view'];
                $data['products'][$product_id]['view_text'] = $value['view_text'];
            }
        }
        */
        
        $has_main_product = false;
        foreach ($data['products'] as $product) {
            if ($product['product_id'] == $product_info['product_id']) {
                $has_main_product = true;
                break;
            }
        }
        //if (isset($data['products'][0]) && !$has_main_product) {
        //    $product_info = $this->model_catalog_product->getProduct($data['products'][0]['product_id']);
        //}
                
        $product_id = $product_info['product_id'];
        if (!empty($type['setting']['hidden_if_next']) && $product_info['quantity'] < 1) {
            foreach ($data['products'] as $product) {
                if ($product['quantity'] > 0) {
                    $product_info = $this->model_catalog_product->getProduct($product['product_id']);
                    break;
                }
            }
        }
        
        if (count($data['products']) <= 1) {
            return $product_info;
        }
        
        $data['product_id'] = $product_info['product_id'];
        $data['title'] = !empty($type['setting']['category_title'][$language_id]) ? html_entity_decode($type['setting']['category_title'][$language_id], ENT_QUOTES, 'UTF-8') : '';
        $data['replace_h1'] = !empty($type['setting']['category_replace_h1']) ? $type['setting']['category_replace_h1'] : false;
        $data['replace_image'] = !empty($type['setting']['category_replace_image']) ? $type['setting']['category_replace_image'] : false;
        $data['variant'] = $type['setting']['category_variant'];

        if (!empty($type['setting']['replace_image'])) {
            $image = $product_info['image'];
            if (!$image) $image = 'no_image.png';
            if (version_compare(VERSION, '3.0', '>=')) {
                $data['thumb'] = $this->model_tool_image->resize($image, $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            } else {
                $data['thumb'] = $this->model_tool_image->resize($image, $this->config->get($this->config->get('config_theme') . '_image_product_width') ? $this->config->get($this->config->get('config_theme') . '_image_product_width') : $this->config->get('config_image_product_width'), $this->config->get($this->config->get('config_theme') . '_image_product_height') ? $this->config->get($this->config->get('config_theme') . '_image_product_height') : $this->config->get('config_image_product_height'));
            }
        }
        
        $data['text_select'] = $this->language->get('text_select');
        
        if (floatval(VERSION) >= 2.2) {
            $product_info['hpm_block'] = $this->load->view('extension/module/hpmodel/category_block', $data);
        } else {
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/module/hpmodel/category_block.tpl')) {
                $product_info['hpm_block'] = $this->load->view($this->config->get('config_template') . '/template/extension/module/hpmodel/category_block.tpl', $data);
            } else {
                $product_info['hpm_block'] = $this->load->view('default/template/extension/module/hpmodel/category_block.tpl', $data);
            }
        }        
        return $product_info;
    }
    
    
    public function get_product_data() {
        $json = array();
        
        $data_class = !empty($this->request->get['class']) ? explode('|', $this->request->get['class']) : array();
        $product_id = !empty($this->request->get['id']) ? $this->request->get['id'] : 0;
        
        if (!$product_id) return;
        
        if (in_array('option', $data_class)) {
            $data = array();
            
            $data['product_id'] = $product_id;
            $data['options'] = array();

            $this->load->model('catalog/product');
            $this->load->model('tool/image');

            foreach ($this->model_catalog_product->getProductOptions($product_id) as $option) {
                $product_option_value_data = array();

                foreach ($option['product_option_value'] as $option_value) {
                    if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
                        if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
                            $price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
                        } else {
                            $price = false;
                        }

                        $product_option_value_data[] = array(
                            'product_option_value_id' => $option_value['product_option_value_id'],
                            'option_value_id'         => $option_value['option_value_id'],
                            'name'                    => $option_value['name'],
                            'image'                   => $option_value['image'] ? $this->model_tool_image->resize($option_value['image'], 50, 50) : '',
                            'price'                   => $price,
                            'price_prefix'            => $option_value['price_prefix']
                        );
                    }
                }

                $data['options'][] = array(
                    'product_option_id'    => $option['product_option_id'],
                    'product_option_value' => $product_option_value_data,
                    'option_id'            => $option['option_id'],
                    'name'                 => $option['name'],
                    'type'                 => $option['type'],
                    'value'                => $option['value'],
                    'required'             => $option['required']
                );
            }
            
            
            if (floatval(VERSION) >= 2.2) {
                $json['option'] = $this->load->view('extension/module/hpmodel/pd_option', $data);
            } else {
                if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/module/hpmodel/pd_option.tpl')) {
                    $json['option'] = $this->load->view($this->config->get('config_template') . '/template/extension/module/hpmodel/pd_option.tpl', $data);
                } else {
                    $json['option'] = $this->load->view('default/template/extension/module/hpmodel/pd_option.tpl', $data);
                }
            }                    
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
