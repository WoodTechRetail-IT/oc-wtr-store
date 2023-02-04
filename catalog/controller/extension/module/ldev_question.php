<?php

require_once(DIR_SYSTEM . 'library/ldevquestion.php');

class ControllerExtensionModuleLdevQuestion extends Controller {
    private $extensionPath = 'extension/extension';
    private $shopModulePath = '';
    private $path = 'extension/module/ldev_question';
    private $setPref = '';
    private $assets = [
        'styles' => [],
        'scripts' => []
    ];
    private $lang = [];


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->ldev_question = new LdevQuestion($registry);
        $this->version = $this->ldev_question->oc_version();

        if($this->version < 23){
            $this->path = 'module/ldev_question';
            $this->shopModulePath = 'module';
        }elseif($this->version == 23){
            $this->shopModulePath = 'extension/module';
        }else{
            $this->extensionPath = 'marketplace/extension';
            $this->setPref = 'module_';
            $this->shopModulePath = 'extension/module';
        }
        if($this->version <= 23){
            $this->tempExt = '.tpl';
        }

        $this->popup_types = ['popup', 'popup_image', 'popup_gallery', 'popup_gallery_title'];

    }

    public function index($setting = array()) {


        if(!isset($this->request->get['route']) && $this->request->server['REQUEST_URI'] != '/') return false;

        $data['lang'] = $this->lang = $this->load->language($this->path);



        if($this->config->get($this->setPref.'ldev_question_include_css_js')) {
            $this->assets['styles'][] = 'catalog/view/javascript/ldev_question/bootstrap_panel_tabs_collapse.css';
            $this->assets['scripts'][] = 'catalog/view/javascript/ldev_question/bootstrap_tabs_collapse.min.js';
        }

        $this->assets['styles'][] = 'catalog/view/theme/default/stylesheet/ldev_question.css';
        $this->assets['scripts'][] = 'catalog/view/javascript/ldev_question/stepper.js';


        $this->load->model($this->path);
        //model for getting a list of installed modules
        if($this->version <= 23){
            $this->load->model('extension/extension');
            $this->load->model('extension/module');

        }else{
            $this->load->model('setting/extension');
            $this->load->model('setting/module');
        }


        $filtered_data = [
            'sort' => 'qi.sort_order',
            'order' => 'ASC',
            'query' => '',
            'ldev_question_block_id' => 0,
        ];

        $params = $this->request->get;
        $route = isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home';

        switch ($route){
            case 'common/home' :
            case 'checkout/cart' :
            case 'checkout/checkout' :
            case 'checkout/simplecheckout' :
            case 'product/manufacturer' :
            case 'product/special' :
            case 'information/contact' :
            case 'account/account' :
            case 'account/login' :
            case 'affiliate/login' :
            case 'account/return/add' :
            case 'blog/latest' :
            case 'gallery/ldev_gallery' :
                $filtered_data['query'] = $route;
                break;

            case 'product/category' :


                if(!$this->checkPagination()) $filtered_data['query'] = '';
                if(!isset($params['path'])) $filtered_data['query'] = '';



                //oc_filter
                if(isset($this->request->get['filter_ocfilter'])){

                    $ocfilter_page_info = $this->load->controller('extension/module/ocfilter/getPageInfo');

                    if(isset($ocfilter_page_info['ocfilter_page_id'])){
                        $filtered_data['query'] = 'filter_ocfilter_page_id='.$ocfilter_page_info['ocfilter_page_id'];
                        break;
                    }
                }


                //mega_filter
                if(isset($this->request->get['mfp'])){
                    $filtered_data['query'] = '';
                    break;
                }
                //vier filter
                if(isset($this->request->get['attrb']) || isset($this->request->get['prs'])){
                    $filtered_data['query'] = '';
                    break;
                }
                //category
                $path = explode('_', $params['path']);
                $cat_id = (int)end($path);
                $filtered_data['query'] = 'category_id='.$cat_id;
                if(!$this->checkPagination()) $filtered_data['query'] = '';
                break;

            case 'product/product' :
                //for single products
                $filtered_data['query'] = array();
                $param_name = 'product_id';
                $param_id = $params[$param_name];
                $filtered_data['query'][] = $param_name.'='.$param_id;

                $this->load->model('catalog/product');
                $product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

                //product by manufacturer
                if($product_info['manufacturer_id']){
                    $param_name = 'product_by_man_id';
                    $filtered_data['query'][] = $param_name.'='.(int)$product_info['manufacturer_id'];
                }

                //product by category
                $param_name = 'product_by_cat_id';
                $product_categories = $this->model_catalog_product->getCategories($this->request->get['product_id']);
                foreach ($product_categories as $product_category){
                    $filtered_data['query'][] = $param_name.'='.(int)$product_category['category_id'];

                }
                break;

            case 'product/manufacturer/info' :
            case 'information/information' :
            case 'record/record' :
            case 'blog/article' :
                if(!$this->checkPagination()) $filtered_data['query'] = '';
                $param_name = isset(explode('/', $route)[1]) ? explode('/', $route)[1].'_id' : '';

                if(!isset($params[$param_name])) $filtered_data['query'] = '';
                $param_id = $params[$param_name];
                $filtered_data['query'] = $param_name.'='.$param_id;
                break;

            case 'gallery/ldev_gallery/category' :
                if(!isset($params['gallery_category_id'])) $filtered_data['query'] = '';
                $filtered_data['query'] = 'gallery_category_id='.$params['gallery_category_id'];
                break;

            default :
                $filtered_data['query'] = '';

        }


        $results = [];

        if(isset($setting['ldev_question_block_id'])){
            //single item (for shortcode)
            if(!isset($setting['rel']) || $setting['rel'] == 0) {
                $filtered_data['query'] = '';
            }
            if($this->version < 23) {
                $result_info = $this->model_module_ldev_question->getQuestion($setting['ldev_question_block_id'], $filtered_data['query']);
            }else {
                $result_info = $this->model_extension_module_ldev_question->getQuestion($setting['ldev_question_block_id'], $filtered_data['query']);
            }
            if($result_info) $results[] = $result_info;


        }else{
            //all results for current page
            if(!$filtered_data['query']) return '';


            if($this->version < 23) {
                $results = $this->model_module_ldev_question->getQuestions($filtered_data);
            }else{
                $results = $this->model_extension_module_ldev_question->getQuestions($filtered_data);
            }

        }



        $heading_status = $this->config->get($this->setPref.'ldev_question_heading_status');
        $view_type = $this->config->has($this->setPref.'ldev_question_view_type') ?  $this->config->get($this->setPref.'ldev_question_view_type') : 'collapse';
        $microdata = $this->config->has($this->setPref.'ldev_question_microdata') ?  $this->config->get($this->setPref.'ldev_question_microdata') : '';

        $show_more_height = $this->config->has($this->setPref.'ldev_question_show_more_height') ?  $this->config->get($this->setPref.'ldev_question_show_more_height') : 250;

        $container = $this->config->get($this->setPref.'ldev_question_container_status');
        $hide_sublings = $this->config->get($this->setPref.'ldev_question_hide_sublings');
        $collapse_activate_1st = $this->config->get($this->setPref.'ldev_question_collapse_activate_1st');
        $marker = $this->config->get($this->setPref.'ldev_question_marker');

        $data['exist_selectors'] = array_filter(array_column($results,'selector_for_append'), function ($val){
            return trim($val);
        });
        $data['include_css_js'] = $this->config->get($this->setPref.'ldev_question_include_css_js');

        $data['custom_css'] = $this->config->get($this->setPref.'ldev_question_custom_css');
        $data['custom_js'] = $this->config->get($this->setPref.'ldev_question_custom_js');
        $banner_rows = $this->config->has($this->setPref.'ldev_question_banner_rows') ?  $this->config->get($this->setPref.'ldev_question_banner_rows') : 1;
        $menu_style = $this->config->has($this->setPref.'ldev_question_menu_style') ?  $this->config->get($this->setPref.'ldev_question_menu_style') : 'default';
        $tooltip_position = $this->config->has($this->setPref.'ldev_question_tooltip_position') ?  $this->config->get($this->setPref.'ldev_question_tooltip_position') : 'top';



        $common_item_data = [
            'lang' =>   ($this->version == 20) ? $this->language->all_keys() : $this->language->all(),
            'heading_status' => $heading_status,
            'banner_rows' => $banner_rows,
            'show_more_height' => $show_more_height,
            'menu_style' => $menu_style,
            'tooltip_position' => $tooltip_position,
        ];


        $data['results'] = [];
        $popup_status = false;
        foreach ($results as $k => $result) {


            if(!isset($result['item_id'])) continue;
            $result_marker = isset($result['marker']) ? $result['marker'] : $marker;
            $result_type = isset($result['view_type']) ? $result['view_type'] : $view_type;
            $result['thumb'] = isset($result['list'][0]['image']['thumb']) ? $result['list'][0]['image']['thumb'] : '';
            $list = [];
            foreach ($result['list'] as $item) {
                $image_popup = '';
                if ($item['image']['link']) {
                    $image_popup = $this->model_tool_image->resize($item['image']['link'], $this->config->get($this->setPref . 'ldev_question_image_width_popup'), $this->config->get($this->setPref . 'ldev_question_image_height_popup'));
                }
                $module = $this->parseModule($item['module']);


                $list[] = [
                    'title' => $this->parseShortCodes(html_entity_decode($item['title'], ENT_QUOTES, 'UTF-8')),
                    'text' => $result_type != 'tooltip' ? $this->parseShortCodes(html_entity_decode($item['text'], ENT_QUOTES, 'UTF-8')) : $this->parseShortCodes(strip_tags(html_entity_decode($item['text']),'<br>,<b>,<em>,<i>,<u>')),
                    'thumb' => $item['image']['thumb'],
                    'link' => html_entity_decode($item['link']),
                    'marker' => htmlspecialchars_decode(html_entity_decode(($item['marker'] ? $item['marker'] : $result_marker), ENT_QUOTES, 'UTF-8')),
                    'popup' => $image_popup,
                    'thumb_microdata' => $this->prepareText($item['image']['thumb']),
                    'module' => $module,
                    'marker_microdata' => strip_tags(htmlspecialchars_decode(html_entity_decode(($item['marker'] ? $item['marker'] : $result_marker), ENT_QUOTES, 'UTF-8'))),
                    'title_microdata' => $this->parseShortCodes($this->prepareText(strip_tags($item['title']))),
                    'text_microdata' => $this->parseShortCodes($this->prepareText($item['text']) ? $this->prepareText($item['text']) : html_entity_decode($item['title'], ENT_QUOTES, 'UTF-8')),
                ];



            }
            $item_data = [
                'item_id' => $result['item_id'],
                'name' => $this->parseShortCodes(html_entity_decode($result['name'])),
                'about' => $this->parseShortCodes(html_entity_decode($result['about'], ENT_QUOTES, 'UTF-8')),
                'list' => $list,
                'thumb' => $result['thumb'],
                'thumb_microdata' => $this->prepareText($result['thumb']),
                'selector' => html_entity_decode($result['selector_for_append']),
                'container' => isset($result['container']) ? $result['container'] : $container,
                'hide_sublings' => isset($result['hide_sublings']) ? $result['hide_sublings'] : $hide_sublings,
                'heading_status' => isset($result['heading_status']) ? $result['heading_status'] : $heading_status,
                'collapse_activate_1st' => isset($result['collapse_activate_1st']) ? $result['collapse_activate_1st'] : $collapse_activate_1st,
                'type' => $result_type,
                'microdata' => isset($result['microdata']) ? $result['microdata'] : $microdata,
                'name_microdata' => $this->parseShortCodes($this->prepareText(strip_tags($result['name']))),
                'about_microdata' => $this->parseShortCodes($this->prepareText($result['about'])),
                'display' => $result['display']
            ];



            $item_data = array_merge($common_item_data, $item_data);
            $data['results'][$k] = $item_data;

            $file_microdata = DIR_TEMPLATE.'default/template/'.$this->path.'/microdata_'.$item_data['microdata'].$this->tempExt;
            if($this->version > 23) $file_microdata .= '.twig';

            if ($this->version < 22) {
                $data['results'][$k]['view'] = $this->load->view('default/template/' . $this->path . '/ldev_question_' . $item_data['type'] . $this->tempExt, $item_data);
                if ($item_data['microdata'] && $list && file_exists($file_microdata)) {
                    $data['results'][$k]['microdata_view'] = $this->load->view('default/template/' . $this->path . '/microdata_' . $item_data['microdata'] . $this->tempExt, $item_data);
                }
            } else {
                $data['results'][$k]['view'] = $this->load->view($this->path . '/ldev_question_' . $item_data['type'] . $this->tempExt, $item_data);

                if ($item_data['microdata'] && $list && file_exists($file_microdata)) {
                    $data['results'][$k]['microdata_view'] = $this->load->view($this->path . '/microdata_' . $item_data['microdata'] . $this->tempExt, $item_data);
                }
                if (in_array($item_data['type'], $this->popup_types)) {
                    $popup_status = true;
                }
            }

        }

        /*microdata*/
        $microdata_view = '';
        $single_flag = [];


        foreach ($data['results'] as $data_result){
            if(!isset($data_result['microdata_view'])) continue;

            if(!in_array($data_result['microdata'], $single_flag)){

                $microdata_view .= $data_result['microdata_view'];
                $single[] = $data_result['microdata'];
            }


        }

        if(!$data['results']) return false;


        if($popup_status){
            /*for popup and modal type*/
            $this->assets['scripts'][] = 'catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js';
            $this->assets['styles'][] = 'catalog/view/javascript/jquery/magnific/magnific-popup.css';
            /**/
        }

        $this->setAssets();

        if($this->version < 22){
            return $this->load->view('default/template/'.$this->path.'/ldev_question'.$this->tempExt, $data);
        }else{
            return $this->load->view($this->path.'/ldev_question'.$this->tempExt, $data);
        }
    }

    private function checkPagination(){
        $page_1st_only = $this->config->get($this->setPref.'ldev_question_pagination_1st');
        if(!$page_1st_only) return true;
        if(!isset($this->request->get['page'])) return true;
        if($this->request->get['page']==1 || $this->request->get['page']=='{page}') {
            return true;
        }
        return false;
    }

    public function parseShortCodes($string){
        $route = isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home';

        $codes = array(
            'product_name',
            'product_sku',
            'product_model',
            'product_quantity',
            'product_price',
            'category_name',
            'category_image',
            'manufacturer_name',
            'manufacturer_image',
            'min_price',
            'max_price',

            'product_description',
            'product_attribute_table',
            'product_attribute_tooltip',


        );


        $replaced_codes = array();
        $replaced_data = array();

        $pattern = '/.?';
        foreach ($codes as $code){

            $pattern .= '\['.$code.'\]|';
            $replaced_codes[] = '"\['.$code.'\]"';
            $replaced_data[$code] = '';
        }


        /*avoid exiting for single product attribute*/
        $pattern.='\[product_attribute_id\=\d{1,6}\]|';

        /*avoid exiting for single product card*/
        $pattern.='\[product_card_id\=\d{1,6}\]|';




        $pattern = trim($pattern,'|');
//        $pattern .= '.?$/m';
        $pattern .= '.?/m';

        if(!preg_match($pattern, $string)){
            return $string;
        };




        switch ($route) {
            case 'product/product' :

                $this->load->model('catalog/product');
                $product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
                if ($product_info) {
                    //product fields
                    $price = (float)$product_info['special'] ? $product_info['special'] : $product_info['price'];
                    $price = $this->currency->format($this->tax->calculate($price, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    $replaced_data['product_name'] = $product_info['name'];
                    $replaced_data['product_price'] = $price;
                    $replaced_data['product_sku'] = $product_info['sku'];
                    $replaced_data['product_model'] = $product_info['model'];
                    $replaced_data['product_quantity'] = $product_info['quantity'];
                    $replaced_data['product_description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');

                    //product manufacturer
                    if ($product_info['manufacturer_id']) {
                        $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);
                        if ($manufacturer_info) {
                            $replaced_data['manufacturer_name'] = $manufacturer_info['name'];
                            if ($manufacturer_info['image']) {
                                $replaced_data['manufacturer_image'] = '<img src="' . $this->model_tool_image->resize($manufacturer_info['image'], 100, 100) . '" alt="' . $manufacturer_info['name'] . '" title="' . $manufacturer_info['name'] . '">';
                            }
                        }


                    }

                    //product category
                    if (isset($this->request->get['path'])) {

                        $parts = explode('_', (string)$this->request->get['path']);
                        $category_id = (int)end($parts);
                        $category_info = $this->model_catalog_category->getCategory($category_id);
                        if ($category_info) {
                            $replaced_data['category_name'] = $category_info['name'];

                            if ($category_info['image']) {
                                $replaced_data['category_image'] = '<img src="' . $this->model_tool_image->resize($category_info['image'], 100, 100) . '" alt="' . $category_info['name'] . '" title="' . $category_info['name'] . '">';
                            }
                        }

                    }


                    //product_attribute table
                    $attribute_groups = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

                    if ($this->version < 22) {
                        $replaced_data['product_attribute_table'] = $this->load->view('default/template/' . $this->path . '/widget/product_attribute_table' . $this->tempExt, ['attribute_groups' => $attribute_groups]);
                        $replaced_data['product_attribute_tooltip'] = $this->load->view('default/template/' . $this->path . '/widget/product_attribute_tooltip' . $this->tempExt, ['attribute_groups' => $attribute_groups]);
                    } else {
                        $replaced_data['product_attribute_table'] = $this->load->view($this->path . '/widget/product_attribute_table' . $this->tempExt, ['attribute_groups' => $attribute_groups]);
                        $replaced_data['product_attribute_tooltip'] = $this->load->view($this->path . '/widget/product_attribute_tooltip' . $this->tempExt, ['attribute_groups' => $attribute_groups]);
                    }



                }



            break;
            case 'product/category' :
                $this->load->model('catalog/category');
                $parts = explode('_', (string)$this->request->get['path']);
                $category_id = (int)end($parts);
                $category_info = $this->model_catalog_category->getCategory($category_id);

                if($category_info){
                    $replaced_data['category_name'] = $category_info['name'];

                    $min_price = $this->model_extension_module_ldev_question->getPriceByCategory($category_id, 'min');
                    $max_price = $this->model_extension_module_ldev_question->getPriceByCategory($category_id, 'max');

                    if($min_price){
                        $replaced_data['min_price'] = preg_quote($this->currency->format($min_price, $this->session->data['currency']));
                    }
                    if($max_price){
                        $replaced_data['max_price'] = preg_quote($this->currency->format($max_price, $this->session->data['currency']));
                    }

                    if($category_info['image']){
                        $replaced_data['category_image'] = '<img src="'.$this->model_tool_image->resize($category_info['image'], 100, 100).'" alt="'.$category_info['name'].'" title="'.$category_info['name'].'">';
                    }



                }


            break;
            case 'product/manufacturer/info' :
                $this->load->model('catalog/manufacturer');
                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);
                if($manufacturer_info){
                    $replaced_data['manufacturer_name'] = $manufacturer_info['name'];

                    $min_price = $this->model_extension_module_ldev_question->getPriceByManufacturer($this->request->get['manufacturer_id'], 'min');
                    $max_price = $this->model_extension_module_ldev_question->getPriceByManufacturer($this->request->get['manufacturer_id'], 'max');

                    if($min_price){
                        $replaced_data['min_price'] = preg_quote($this->currency->format($min_price, $this->session->data['currency']));
                    }
                    if($max_price){
                        $replaced_data['max_price'] = preg_quote($this->currency->format($max_price, $this->session->data['currency']));
                    }


                    if($manufacturer_info['image']){
                        $replaced_data['manufacturer_image'] = '<img src="'.$this->model_tool_image->resize($manufacturer_info['image'], 100, 100).'" alt="'.$manufacturer_info['name'].'" title="'.$manufacturer_info['name'].'">';
                    }


                }

            break;
        }


        if($route == 'product/product') {

            /*product attribute single*/
            $attr_pattern = '/\[product_attribute_id\=\d{1,6}\]/m';

            $found_codes = [];
            preg_match_all($attr_pattern, $string, $found_codes);
            foreach ($found_codes[0] as $k => $found_code) {
                $code = $found_code;
                $found_code = str_replace([ '[', ']' ], [ '/\[', '\]/' ],$found_code);
                $code = str_replace(['[', ']'], '', $code);
                $value = isset(explode('=', $code)[1]) ? explode('=', $code)[1] : '';
                if($value){
                    $attribute_groups = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
                    foreach ($attribute_groups as $attribute_group){
                        foreach ($attribute_group['attribute'] as $attribute){
                            if($attribute['attribute_id'] == $value){
                                $string =  stripslashes(preg_replace($found_code, $attribute['text'] ,$string));
                                break;
                            }
                        }
                    }
                }
            }
            $string = stripslashes(preg_replace($attr_pattern,'',$string));
            /*end product attribute single*/

        }


            /*product card (common)*/

            $product_card_pattern = '/\[product_card_id\=\d{1,6}\]/m';

            $found_codes = [];

            preg_match_all($product_card_pattern, $string, $found_codes);

            foreach ($found_codes[0] as $k => $found_code) {
                $code = $found_code;
                $found_code = str_replace([ '[', ']' ], [ '/\[', '\]/' ],$found_code);
                $code = str_replace(['[', ']'], '', $code);
                $code = str_replace('&amp;', '&', $code);
                $value = isset(explode('=', $code)[1]) ? explode('=', $code)[1] : '';
                if($value){
                    $this->load->model('catalog/product');
                    $product_info = $this->model_catalog_product->getProduct($value);
                    if ($product_info) {
                        $card_widget_data['lang'] = $this->lang;
                        $card_widget_data['product'] = [];

                        $card_widget_data['product']['product_id'] = (int)$product_info['product_id'];

                        if ($product_info['image']) {
                            $card_widget_data['product']['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100);
                        } else {
                            $card_widget_data['product']['thumb'] = '';
                        }
                        $card_widget_data['product']['name'] = $product_info['name'];
                        $card_widget_data['product']['href'] = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
                        $card_widget_data['product']['rating'] = $product_info['rating'];


                        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                            $card_widget_data['product']['price'] = preg_quote($this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']));
                        } else {
                            $card_widget_data['product']['price'] = false;
                        }

                        if ((float)$product_info['special']) {
                            $card_widget_data['product']['special'] = preg_quote($this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']));
                        } else {
                            $card_widget_data['product']['special'] = false;
                        }

                        if ($this->config->get('config_tax')) {
                            $card_widget_data['product']['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
                        } else {
                            $card_widget_data['product']['tax'] = false;
                        }

                        if ($this->version < 22) {
                            $card_widget_view = $this->load->view('default/template/' . $this->path . '/widget/product_card' . $this->tempExt, $card_widget_data);
                        } else {
                            $card_widget_view = $this->load->view($this->path . '/widget/product_card' . $this->tempExt, $card_widget_data);
                        }


                        $string =  stripslashes(preg_replace($found_code, $card_widget_view ,$string));

                    }
                }
            }
            $string = stripslashes(preg_replace($product_card_pattern,'',$string));
            /*end product card (common)*/

        return stripslashes(preg_replace($replaced_codes,$replaced_data, $string));
    }

    private function parseModule($module_data){

        $module_key = isset(explode('.',$module_data)[0]) ? explode('.',$module_data)[0] : '';
        $module_id = isset(explode('.',$module_data)[1]) ? explode('.',$module_data)[1] : '';
        if(!$module_key) return '';

        if($module_id){
            if($this->version <= 23) {
                $module_setting = $this->model_extension_module->getModule($module_id);
            }else{
                $module_setting = $this->model_setting_module->getModule($module_id);
            }
            $module = $this->load->controller($this->shopModulePath.'/'.$module_key, $module_setting);
        }else{
            $module = $this->load->controller($this->shopModulePath.'/'.$module_key);
        }

        return $module;
    }

    private function prepareText($text){
        $text = str_replace('"', '\"', html_entity_decode($text,ENT_QUOTES, 'UTF-8'));
        $text = preg_replace('/\[ldev_question_block_id\=\d{1,6}\]/m', '', $text);
        $text = preg_replace('/\[product_attribute_id\=\d{1,6}\]/m', '', $text);
        $text = preg_replace('/\[product_card_id\=\d{1,6}\]/m', '', $text);
        $text = str_replace(PHP_EOL,' ', $text);
        return $text;
    }

    public function setAssets(){
        foreach ($this->assets['styles'] as $style){
            $this->document->addStyle($style);
        }
        foreach ($this->assets['scripts'] as $script){
            $this->document->addScript($script);
        }


    }

    public function getAssetsGlobal(){
        $assets = [];
        if($this->config->get($this->setPref.'ldev_question_include_css_js')) {
            $assets['styles'][] = 'catalog/view/javascript/ldev_question/bootstrap_panel_tabs_collapse.css';
            $assets['scripts'][] = 'catalog/view/javascript/ldev_question/bootstrap_tabs_collapse.min.js';
        }
        $assets['styles'][] = 'catalog/view/theme/default/stylesheet/ldev_question.css';
        $assets['scripts'][] = 'catalog/view/javascript/ldev_question/stepper.js';

        /*for popup and modal type*/
        $assets['scripts'][] = 'catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js';
        $assets['styles'][] = 'catalog/view/javascript/jquery/magnific/magnific-popup.css';
        /**/

        return $assets;
    }



}