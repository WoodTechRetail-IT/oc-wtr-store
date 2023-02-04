<?php
class ControllerExtensionModuleOCFilter extends Controller {
  private static $index = 0;
  
  public function index() {
    if (!$this->registry->has('ocfilter') || !$this->ocfilter->startup()) {
      return;
    }

    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 6);

    // Fix duplicate calling module
    if (in_array('Journal2Page', array_column($backtrace, 'class'))) {
      return;
    }

    $data = $this->load->language('extension/module/ocfilter');             
        
    $module_heading_title = $this->ocfilter->config('module_heading_title');

    if ($module_heading_title && isset($module_heading_title[$this->config->get('config_language_id')])) {
      $data['heading_title'] = $module_heading_title[$this->config->get('config_language_id')];
    } else {
      $data['heading_title'] = $this->language->get('heading_title');
    }

    $mobile_button_text = $this->ocfilter->config('mobile_button_text');
    
    if ($mobile_button_text && isset($mobile_button_text[$this->config->get('config_language_id')])) {
      $data['button_ocfilter_mobile'] = $mobile_button_text[$this->config->get('config_language_id')];
    } else {
      $data['button_ocfilter_mobile'] = $this->language->get('button_ocfilter_mobile');
    }   

    $page_module_link_title = $this->ocfilter->config('page_module_link_title');
    
    if ($page_module_link_title && isset($page_module_link_title[$this->config->get('config_language_id')])) {
      $data['page_module_link_title'] = $page_module_link_title[$this->config->get('config_language_id')];
    } else {
      $data['page_module_link_title'] = $this->language->get('page_module_link_title');
    }        

    // Fix `fastore` double calling
    if (in_array('getModules', array_column($backtrace, 'function'))) {
      $data['index'] = $this->index;
    } else {
      $data['index'] = ++$this->index;
    }

    $data['position'] = 'left';
    $data['layout'] = 'vertical';

    /*
    _¯\_(ツ)_/¯
    */
    foreach ($backtrace as $level) {
      if (isset($level['class'])) {
        if (false !== stripos($level['class'], 'column')) {
          $data['layout'] = 'vertical';
          $data['position'] = (false !== stripos($level['class'], 'right') ? 'right' : 'left');

          break;
        }

        if (false !== stripos($level['class'], 'content')) {
          $data['layout'] = 'horizontal';
          $data['position'] = (false !== stripos($level['class'], 'bottom') ? 'bottom' : 'top');

          break;
        }
      }
    }

    $url = '';

    $url .= '&index=' . $this->index;
    $url .= '&layout=' . $data['layout'];

    if ($this->ocfilter->seo->getPath()) {
      $url .= '&ocf_path=' . (string)$this->ocfilter->seo->getPath();
    } else if (isset($this->request->get['filter_category_id'])) {
      $url .= '&filter_category_id=' . (int)$this->request->get['filter_category_id'];
    } else if (isset($this->request->get['category_id'])) {
      $url .= '&category_id=' . (int)$this->request->get['category_id'];
    }

    if ($this->ocfilter->seo->getRoute()) {
      $url .= '&ocf_route=' . $this->ocfilter->seo->getRoute();
    }

    if ($this->ocfilter->seo->isSeoUrlEnabled()) {
      $url .= '&seo_url_enabled=1';
    }

    if ($this->ocfilter->placement->isManufacturer()) {
      $url .= '&ocf_manufacturer_id=' . $this->ocfilter->seo->getManufacturerId();
    }

    if ($this->ocfilter->placement->isCustomPage()) {
      $url .= '&ocf_layout_id=' . $this->ocfilter->placement->getCustomPageLayoutId();
      
      $url .= '&ocf_custom_route=' . $this->ocfilter->placement->getCustomPageRoute();
    }

    if ($this->ocfilter->placement->isSearch() && $this->ocfilter->seo->getSearchKeyword()) {
      $url .= '&search=' . $this->ocfilter->seo->getSearchKeyword();
    }

    if (isset($this->request->get['sort'])) {
      $url .= '&sort=' . (string)$this->request->get['sort'];
    }

    if (isset($this->request->get['order'])) {
      $url .= '&order=' . (string)$this->request->get['order'];
    }

    if (isset($this->request->get['limit'])) {
      $url .= '&limit=' . (int)$this->request->get['limit'];
    }
    
    // For js links
    $data['url_params'] = str_replace('&amp;', '&', $url);
    $data['url_host'] = $this->ocfilter->seo->getHost();    
   
    $data['link'] = str_replace('&amp;', '&', $this->ocfilter->seo->link());
       
    $data['filter_params'] = $this->ocfilter->seo->getParams();
    
    $data['url_index'] = $this->ocfilter->params->getIndex();   
    $data['sep_filt'] = $this->ocfilter->params->getSepFilter();
    $data['sep_fsrc'] = $this->ocfilter->params->getSepSource();
    $data['sep_vals'] = $this->ocfilter->params->getSepValues();
    $data['sep_sdot'] = $this->ocfilter->params->getSepSliderDot();
    $data['sep_sneg'] = $this->ocfilter->params->getSepSliderNegative();
    $data['sep_sran'] = $this->ocfilter->params->getSepSliderRange();
        
    $data['theme'] = $this->ocfilter->config('theme');
    $data['search_button'] = (int)$this->ocfilter->config('search_button');
    $data['show_counter'] = (int)$this->ocfilter->config('show_counter');
    $data['show_values_limit'] = (int)$this->ocfilter->config('show_values_limit');
    $data['show_filters_limit'] = (int)$this->ocfilter->config('show_filters_limit');
    $data['hidden_filters_lazy_load'] = (int)$this->ocfilter->config('hidden_filters_lazy_load');
    $data['hidden_values_lazy_load'] = (int)$this->ocfilter->config('hidden_values_lazy_load');
    $data['slider_input'] = (int)$this->ocfilter->config('slider_input');
    $data['slider_pips'] = (int)$this->ocfilter->config('slider_pips');
    $data['price_logarithmic'] = (int)$this->ocfilter->config('special_price_logarithmic');
    $data['values_auto_column'] = (int)$this->ocfilter->config('values_auto_column');
    $data['page_module_link_status'] = (int)$this->ocfilter->config('page_module_link_status');

    $data['mobile_max_width'] = (int)$this->ocfilter->config('mobile_max_width');
    $data['mobile_remember_state'] = (int)$this->ocfilter->config('mobile_remember_state');
    $data['mobile_placement'] = ($this->ocfilter->config('mobile_placement') ? $this->ocfilter->config('mobile_placement') : 'left');
    $data['mobile_button_position'] = ($this->ocfilter->config('mobile_button_position') ? $this->ocfilter->config('mobile_button_position') : 'fixed');

    $data['numeral_locale'] = 'en';
    
    if (isset($this->session->data['language'])) {
      $finded_locale = '';
      $finded_count = 0;
      
      $language_codes = explode('-', strtolower($this->session->data['language']));
      
      foreach ($this->ocfilter->helper->getNumeralLocales() as $locale) {
        if (($count = count(array_intersect(explode('-', $locale), $language_codes))) > $finded_count) {
          $finded_locale = $locale;        
          $finded_count = $count;
        }
      }
      
      if ($finded_locale) {
        $data['numeral_locale'] = $finded_locale;
      }      
    }

    // Filters
    $data['filters'] = [];
    $data['hidden_filters'] = [];  
                    
    $this->ocfilter->filter->setValuesCounter(); 
                    
    $results = $this->model_extension_module_ocfilter->getFilters($this->ocfilter->filter->getFilterModelData());

    foreach ($results as $result) { 
      $filter = $this->ocfilter->filter->formatFilter($result);
    
      if ($filter['type'] == 'slide' || $filter['type'] == 'slide_dual') {
        if (!$this->ocfilter->filter->setFilterSlider($filter)) {
          continue;
        }     

        // TODO
        //$this->ocfilter->filter->setFilterChart($filter);                
      } else {
        if (!$this->ocfilter->filter->setFilterValues($filter)) {
          continue;
        }
        
        // Slicing
        $this->ocfilter->filter->slicingValues($filter);
        
        if ($filter['selected']) {
          $selecteds = array_column(array_filter($filter['values'], function($v) {
            return $v['selected'];
          }), 'name');
          
          $first = array_shift($selecteds);
          
          if ($selecteds) {
            $filter['text_selected'] = sprintf($this->language->get('text_selected'), $first, implode(', ', $selecteds), count($selecteds));
          } else {
            $filter['text_selected'] = $first;
          }          
        }
      }   
    
      $filter['id'] .= '-' . $this->index;
    
      $data['filters'][] = $filter;
    }
    
    if (!$data['filters']) {
      return;
    }
    
    $this->ocfilter->filter->sortFilters($data['filters']);
    
    // Slicing  
    $data['button_show_more_filters'] = '';    
    
    if ($this->ocfilter->config('show_filters_limit') > 0 && $this->ocfilter->config('show_filters_limit') < count($data['filters'])) {
      $hidden_filters = array_splice($data['filters'], (int)$this->ocfilter->config('show_filters_limit'));
      
      // Add selected filters
      foreach ($hidden_filters as $key => $filter) {
        if ($filter['selected']) {
          $data['filters'][] = $filter;
          
          unset($hidden_filters[$key]);
        }
      } 
      
      if ($hidden_filters) {
        if (!$this->ocfilter->config('hidden_filters_lazy_load')) {
          $data['hidden_filters'] = $hidden_filters;
        }  

        $data['button_show_more_filters'] = $this->ocfilter->helper->declOfNum(count($hidden_filters), [
          $this->language->get('button_show_more_filters_1'),
          $this->language->get('button_show_more_filters_2'),
          $this->language->get('button_show_more_filters_3')
        ]); 
      }
    }
       
    if ($this->ocfilter->config('show_selected')) {
      $data['selecteds'] = $this->ocfilter->filter->getSelectedFilters();
    } else {
      $data['selecteds'] = [];
    }
    
    if ($this->index < 2 && $this->ocfilter->config('page_module_link_status')) {
      $data['seo_pages'] = $this->ocfilter->seo->getModulePages();
    } else {
      $data['seo_pages'] = [];
    }    

    $this->ocfilter->seo->setLastBreadcrumb();
    
    // Output   
    $data['stylesheet'] = $this->ocfilter->helper->getRenderedStyle();  
    
    $data['javascript'] = 'catalog/view/javascript/ocfilter48/ocfilter.js?v=' . OCF_VERSION;
       
    $this->outputDebug($data);

    return $this->ocfilter->opencart->renderTemplate('extension/module/ocfilter48/module', $data);
  }

  public function search() {
    header('X-Robots-Tag: noindex, nofollow', true);       
    
    if (!$this->registry->has('ocfilter')) {
      exit('Error: OCFilter library is not loaded. Please, reinstall OCFilter');
    }
    
    if (!$this->ocfilter->startup()) {
      exit('OCFilter is disabled');
    }
   
    $this->load->language('extension/module/ocfilter');     
      
    $this->load->model('extension/module/ocfilter');
    $this->load->model('catalog/product');
    $this->load->model('tool/image');            

    $this->ocfilter->seo->setLastBreadcrumb(); 

    $json = [];

    if ($this->ocfilter->config('search_button') || $this->ocfilter->params->hasSlider() || (!isset($this->request->get[$this->ocfilter->params->getIndex()]) && isset($this->request->get['filter_key']))) {         
      $this->ocfilter->filter->setValuesCounter();     
      
      if (isset($this->request->get['filter_key'])) {
        $filter_key = $this->request->get['filter_key'];
      } else {
        $filter_key = 0;
      }

      // Total products
      if ($this->ocfilter->placement->isCustomPage() && !$this->ocfilter->seo->getParams()) {
        $json['total'] = 0;
        
        $json['text_total'] = '';      
      } else {  
        if ($this->ocfilter->placement->isSpecial()) {
          $total_products = $this->model_extension_module_ocfilter->getTotalProductSpecials($this->ocfilter->params->get());
        } else {
          $filter_data = [
            // Requred by some custom product model methods
            'limit' => 1
          ];

          if ($this->ocfilter->placement->isCategory()) {
            $filter_data['filter_category_id'] = $this->ocfilter->seo->getCategoryId();

            if ($this->ocfilter->config('category_visibility') == 'parent') {
              $filter_data['filter_sub_category'] = true;
            }
          }

          if ($this->ocfilter->placement->isManufacturer()) {
            $filter_data['filter_manufacturer_id'] = $this->ocfilter->seo->getManufacturerId();
          }

          if ($this->ocfilter->placement->isSearch() && $this->ocfilter->seo->getSearchKeyword()) {
            $filter_data['filter_name'] = $this->ocfilter->seo->getSearchKeyword();
          }

          $total_products = $this->model_catalog_product->getTotalProducts($filter_data);
        }

        $json['total'] = $total_products;
        
        $json['button_total'] = $this->ocfilter->helper->declOfNum($total_products, [
          $this->language->get('button_show_total_1'),
          $this->language->get('button_show_total_2'),
          $this->language->get('button_show_total_3')
        ]);        
      }
      
      // Values counter
      $json['values'] = [];
      $json['sliders'] = [];
        
      $results = $this->model_extension_module_ocfilter->getFilters($this->ocfilter->filter->getFilterModelData());
            
      foreach ($results as $result) { 
        $filter = $this->ocfilter->filter->formatFilter($result);

        if (($filter['type'] == 'slide' || $filter['type'] == 'slide_dual') && !$filter['selected']) {         
          if (!$this->ocfilter->filter->setFilterSlider($filter)) {
            continue;
          }        
          
          $json['sliders'][$filter['id']] = [
            'min' => $filter['min'],
            'max' => $filter['max'],
          ];
        } else {         
          if (!$this->ocfilter->filter->setFilterValues($filter, true)) {
            continue;
          }
          
          foreach ($filter['values'] as $value) {
            $json['values'][$value['id']] = [ 
              $value['count'], 
              (int)$value['selected'], 
            ];
          }          
        }   
      }
    }
    
    $json['params'] = $this->ocfilter->seo->getParams();
    $json['decode'] = $this->ocfilter->params->get();
    
    $json['href'] = str_replace('&amp;', '&', $this->ocfilter->seo->link($this->ocfilter->seo->getParams()));

    $this->outputDebug($json);

    if (!$json['debug']) {
      unset($json['debug']);
    }

    $this->ocfilter->opencart->responseJSON($json);
  }

  public function filters() {
    header('X-Robots-Tag: noindex, nofollow', true);
    
    if (!$this->registry->has('ocfilter')) {
      exit('Error: OCFilter library is not loaded. Please, reinstall OCFilter');
    }

    if (!$this->ocfilter->startup()) {
      exit('OCFilter is disabled');
    }
   
    if ($this->ocfilter->config('show_filters_limit') < 1 || !$this->ocfilter->config('hidden_filters_lazy_load')) {
      exit('All filters is loaded');
    }
      
    $this->load->language('extension/module/ocfilter');     
      
    $this->load->model('extension/module/ocfilter');
    $this->load->model('tool/image');                 
     
    if (isset($this->request->get['index'])) {
      $data['index'] = $this->request->get['index'];
    } else {
      $data['index'] = 1;
    }    
    
    if (isset($this->request->get['layout'])) {
      $data['layout'] = $this->request->get['layout'];
    } else {
      $data['layout'] = 'vertical';
    }        
    
    $this->ocfilter->filter->setValuesCounter(); 
    
    $data['filters'] = [];
                                       
    $results = $this->model_extension_module_ocfilter->getFilters($this->ocfilter->filter->getFilterModelData()); 
              
    foreach ($results as $result) {
      $filter = $this->ocfilter->filter->formatFilter($result);

      if ($filter['type'] == 'slide' || $filter['type'] == 'slide_dual') {
        if (!$this->ocfilter->filter->setFilterSlider($filter)) {
          continue;
        }                     
      } else {
        if (!$this->ocfilter->filter->setFilterValues($filter)) {
          continue;
        }
        
        // Slicing
        $this->ocfilter->filter->slicingValues($filter);
      }           

      $filter['id'] .= '-' . $data['index'];
    
      $data['filters'][] = $filter;
    }
    
    $this->ocfilter->filter->sortFilters($data['filters']);   

    // Slicing   
    $filters_limit = (int)$this->ocfilter->config('show_filters_limit');
    
    $offset = $filters_limit;

    foreach ($data['filters'] as $key => $filter) {      
      if ($filter['selected']) {
        unset($data['filters'][$key]);
        
        if (($key + 1) < $filters_limit) {
          $offset--;
        }        
      }
    }

    if ($offset > 0) {
      $data['filters'] = array_slice($data['filters'], $offset);    
    }    

    // Set actual count and ranges
    if (isset($this->request->get[$this->ocfilter->params->getIndex() . '_actual']) && $this->request->get[$this->ocfilter->params->getIndex() . '_actual'] != $this->ocfilter->seo->getParams()) {
      $actual_params = $this->ocfilter->params->decode($this->request->get[$this->ocfilter->params->getIndex() . '_actual']);
    } else {
      $actual_params = [];
    }    
    
    if ($actual_params) {
      $this->ocfilter->params->set($this->request->get[$this->ocfilter->params->getIndex() . '_actual']);
            
      if ($this->ocfilter->params->get()) {
        $this->ocfilter->filter->setValuesCounter();  

        foreach ($data['filters'] as $key_f => $filter) {
          if ($filter['type'] == 'slide' || $filter['type'] == 'slide_dual') {
            $this->ocfilter->filter->setFilterSlider($data['filters'][$key_f]);
            
            continue;
          }
          
          foreach ($filter['values'] as $key_v => $value) {
            $count = $this->ocfilter->filter->getValueProductTotal($filter['filter_key'], $value['value_id']);

            if ($count && $filter['selected'] && $filter['type'] == 'checkbox') {
              $count = '+' . $count;
            }

            $data['filters'][$key_f]['values'][$key_v]['count'] = $count;
          }          
        }
      }
    }  

    $data['button_show_more_filters'] = '';
    $data['button_hide'] = $this->language->get('button_hide');
    $data['text_any'] = $this->language->get('text_any');
    $data['text_loading'] = $this->language->get('text_loading');
    
    $data['slider_input'] = (int)$this->ocfilter->config('slider_input');
    $data['slider_pips'] = (int)$this->ocfilter->config('slider_pips');
    $data['show_counter'] = (int)$this->ocfilter->config('show_counter');
    $data['search_button'] = (int)$this->ocfilter->config('search_button');
    $data['hidden_values_lazy_load'] = (int)$this->ocfilter->config('hidden_values_lazy_load');
    $data['values_auto_column'] = (int)$this->ocfilter->config('values_auto_column');
    $data['has_loaded_filters'] = true;

    $this->outputDebug($data);

    $this->ocfilter->opencart->responseTemplate('extension/module/ocfilter48/filter_list', $data, true);
  }

  public function values() {
    header('X-Robots-Tag: noindex, nofollow', true);
    
    if (!$this->registry->has('ocfilter')) {
      exit('Error: OCFilter library is not loaded. Please, reinstall OCFilter');
    }

    if (!$this->ocfilter->startup()) {
      exit('OCFilter is disabled');
    }
   
    if ($this->ocfilter->config('show_values_limit') < 1 || !$this->ocfilter->config('hidden_values_lazy_load')) {
      exit('All filters is loaded');
    }
    
    if (!isset($this->request->get['filter_key'])) {
      exit('Filter Key required');
    }
      
    $this->load->language('extension/module/ocfilter');     
      
    $this->load->model('extension/module/ocfilter');
    $this->load->model('tool/image');                

    $this->ocfilter->filter->setValuesCounter();    
    
    $filter_info = $this->model_extension_module_ocfilter->getFilter($this->request->get['filter_key']);        
    
    if (!$filter_info) {
      exit('Filter not found');
    }    
       
    $filter = $this->ocfilter->filter->formatFilter($filter_info);
  
    if (!$this->ocfilter->filter->setFilterValues($filter)) {
      exit('Values not found');
    }
          
    // Slicing   
    $filter['values'] = array_slice($filter['values'], $this->ocfilter->config('show_values_limit'));   

    foreach ($filter['values'] as $key => $value) {
      if ($value['selected']) {
        unset($filter['values'][$key]);
      }
    }   

    // Set actual count
    if (isset($this->request->get[$this->ocfilter->params->getIndex() . '_actual']) && $this->request->get[$this->ocfilter->params->getIndex() . '_actual'] != $this->ocfilter->seo->getParams()) {
      $this->ocfilter->params->set($this->request->get[$this->ocfilter->params->getIndex() . '_actual']);
            
      $filter['selected'] = $this->ocfilter->params->has($filter['filter_key']);
            
      if ($this->ocfilter->params->get()) {
        $filter_data = $this->ocfilter->filter->getFilterModelData();
        
        $filter_data['count_filter_key'] = $this->request->get['filter_key'];
        
        $this->ocfilter->filter->setValuesCounter($filter_data);  

        foreach ($filter['values'] as $key => $value) {         
          $count = $this->ocfilter->filter->getValueProductTotal($filter['filter_key'], $value['value_id']);

          if ($count && $filter['selected'] && $filter['type'] == 'checkbox') {
            $count = '+' . $count;
          }

          $filter['values'][$key]['count'] = $count;
        }
      }
    }     

    $data['filter'] = $filter;

    $data['button_hide'] = $this->language->get('button_hide');
    $data['text_loading'] = $this->language->get('text_loading');
    $data['show_counter'] = (int)$this->ocfilter->config('show_counter');
    $data['search_button'] = (int)$this->ocfilter->config('search_button');
    $data['values_auto_column'] = (int)$this->ocfilter->config('values_auto_column');
    $data['has_loaded_values'] = true;
    
    if (isset($this->request->get['index'])) {
      $data['index'] = $this->request->get['index'];
    } else {
      $data['index'] = 1;
    }
    
    if (isset($this->request->get['layout'])) {
      $data['layout'] = $this->request->get['layout'];
    } else {
      $data['layout'] = 'vertical';
    }    
    
    $this->outputDebug($data);

    $this->ocfilter->opencart->responseTemplate('extension/module/ocfilter48/value_list', $data, true);
  }
  
  public function eventApi($_, &$data) {
    if ($this->registry->get('ocfilter')) {
      $data['ocfilter'] = $this->registry->get('ocfilter')->api->view;
    } else {
      $data['ocfilter'] = (object)[];
    }
  }
  
  protected function outputDebug(&$data, $format = false) {
    $data['debug'] = [];
    
    if (!$this->ocfilter->config('debug') || !$this->ocfilter->opencart->isAdminLogged()) { 
      return;
    }    
    
    $replace = [
      'GROUP_CONCAT'  => '<b class="group-concat">GROUP_CONCAT</b>',
      'CONCAT_WS'     => '<b class="concat">CONCAT_WS</b>',
      'UNION ALL'     => '<br /><b class="union-all">UNION ALL</b><br />',
      'SEPARATOR'     => '<b class="separator">SEPARATOR</b>',
      'DISTINCT'      => '<b class="distinct">DISTINCT</b>',
      'COALESCE'      => '<b class="coalesce">COALESCE</b>',
      'ORDER BY'      => '<br /><b class="order-by">ORDER BY</b>',
      'GROUP BY'      => '<br /><b class="group-by">GROUP BY</b>',

      'REPLACE'       => '<b class="replace">REPLACE</b>',
      'BETWEEN'       => '<b class="between">BETWEEN</b>',

      'SELECT'        => '<b class="select">SELECT</b>',
      'INSERT'        => '<b class="insert">INSERT</b>',
      'UPDATE'        => '<b class="update">UPDATE</b>',
      'DELETE'        => '<b class="delete">DELETE</b>',
      'CONCAT'        => '<b class="concat">CONCAT</b>',
      'IFNULL'        => '<b class="ifnull">IFNULL</b>',
      'HAVING'        => '<br /><b class="having">HAVING</b>',
      'EXISTS'        => '<b class="exists">EXISTS</b>',
      'NOT IN'        => '<b class="not-in">NOT IN</b>',

      'INNER'         => '<br /><b class="inner">INNER</b>',
      'OUTER'         => '<br /><b class="outer">OUTER</b>',
      'RIGHT'         => '<br /><b class="right">RIGHT</b>',
      'LIMIT'         => '<br /><b class="limit">LIMIT</b>',
      'UNION'         => '<br /><b class="union">UNION</b><br />',
      'COUNT'         => '<b class="count">COUNT</b>',
      'FIELD'         => '<b class="field">FIELD</b>',
      'WHERE'         => '<br /><b class="where">WHERE<br />&nbsp;&nbsp;</b>',
      'USING'         => '<b class="using">USING</b>',
      'LCASE'         => '<b class="lcase">LCASE</b>',

      'LEFT'          => '<br /><b class="left">LEFT</b>',
      'JOIN'          => '<b class="join">JOIN</b>',
      'LIKE'          => '<b class="like">LIKE</b>',
      'FROM'          => '<br /><b class="from">FROM</b>',
      'RAND'          => '<b class="rand">RAND</b>',
      'DESC'          => '<b class="desc">DESC</b>',
      'NULL'          => '<b class="null">NULL</b>',

      'ALL'           => '<b class="all">ALL</b>',
      'AVG'           => '<b class="avg">AVG</b>',
      'NOW'           => '<b class="now">NOW</b>',
      'MIN'           => '<b class="min">MIN</b>',
      'MAX'           => '<b class="max">MAX</b>',
      'ASC'           => '<b class="asc">ASC</b>',
      'SET'           => '<b class="set">SET</b>',
      'AND'           => '<b class="and">AND</b><br />&nbsp;&nbsp;',

      'IN'            => '<b class="in">IN</b>',
      'AS'            => '<b class="as">AS</b>',
      'OR'            => '<b class="or">OR</b>',
      'IF'            => '<b class="if">IF</b>',
      'ON'            => '<b class="on">ON</b>'
    ];

    $data['debug']['queries'] = [];

    $queries = $this->ocfilter->debug_queries;

    uasort($queries, function($a, $b) {
      return $a['time'] - $b['time'];
    });

    if ($format) {
      $format_caller = '%s -> <b>%s</b> <span title="%s">#%s</span>';
    } else {
      $format_caller = '%s -> %s #%s';
    }

    foreach ($queries as $query) {
      $sql = $query['sql'];
      
      if ($format) {
        // Numbers
        $sql = preg_replace('/(\d+)/', '<i>$1</i>', $sql);

        // Strings
        $sql = preg_replace('/((\'|").*?(\'|"))/', '<span class="string">$1</span>', $sql);

        // Tables
        $sql = preg_replace('/FROM\s\b(\w+?)\b/', 'FROM <span class="sql-table">$1</span>', $sql);
        $sql = preg_replace('/JOIN\s\b(\w+?)\b/', 'JOIN <span class="sql-table">$1</span>', $sql);

        // Fields
        $sql = preg_replace('/(`.*?`)/', '<span class="keyword">$1</span>', $sql);

        // Subquery
        $sql = preg_replace('/(\(SELECT.+?FROM.+?\))(\sAS\s.+?(,|\s))/', '<div class="sub-query">$1</div>$2', $sql);

        $sql = strtr($sql, $replace);        
      }

      $caller_chain = array();

      array_shift($query['caller']);

      foreach ($query['caller'] as $caller) {
        if (!isset($caller['function']) || $caller['function'] == '{closure}' || $caller['function'] == '__call') {
          continue;
        }
        
        if (!isset($caller['class']) || $caller['class'] == 'Action') {
          continue;
        }          
        
        $caller_chain[] = sprintf($format_caller, 
          $caller['class'], 
          $caller['function'], 
          isset($caller['file']) ? $caller['file'] : '',
          isset($caller['line']) ? $caller['line'] : ''
        );
      }

      $data['debug']['queries'][] = array(
        'sql' => trim($sql),
        'time' => (float)number_format($query['time'], 3),
        'caller' => implode(($format ? '<br>' : "\n"), $caller_chain)
      );
    }

    $data['debug']['total_queries'] = count($queries);
    $data['debug']['total_time'] = (float)number_format($this->ocfilter->debug_queries_time, 3);
    
    $this->response->addHeader('OCF_DEBUG: Q: ' . $data['debug']['total_queries'] . '; T: ' . $data['debug']['total_time']);
  }
}