<?php
class ControllerExtensionFeedOCFilterSitemap extends Controller {
  private $category_link = [];
  private $seo_url = false;
  private $url_suffix = null;
  
  private $langmark_settings = false;
   
  public function index() {   
    if (!$this->registry->has('ocfilter') || !$this->ocfilter->config('sitemap_status')) {
      return;
    }    
    
    $this->seo_url = $this->ocfilter->seo->isSeoUrlEnabled();   
        
    $language_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language WHERE status = '1'");

    $multi_language = ($language_query->num_rows > 1);
      
    // Langmark compatibility
    $this->langmark_settings = $this->config->get('asc_langmark_' . $this->config->get('config_store_id'));
    
    if ($this->langmark_settings && is_file(DIR_APPLICATION . 'controller/record/langmark.php')) {
      $this->load->controller('record/langmark');
    } else {
      $this->langmark_settings = false;
    }    

    if ($multi_language) {
      if ($this->ocfilter->opencart->version >= 30) {
        $seo_url_query = $this->db->query("SELECT `query` FROM " . DB_PREFIX . "seo_url WHERE `query` LIKE 'category_id=%' LIMIT 1");
      } else {
        $seo_url_query = $this->db->query("SELECT `query` FROM " . DB_PREFIX . "url_alias WHERE `query` LIKE 'category_id=%' LIMIT 1");
      }
      
      if ($seo_url_query->num_rows) {
        $compare_links = [];
               
        foreach ($language_query->rows as $language) {
          $this->setLanguage($language['language_id'], $language['code']);
                              
          $compare_links[] = $this->url->link('product/category', 'path=' . str_replace('category_id=', '', $seo_url_query->row['query']));  
        }
                
        $multi_language = (count(array_unique($compare_links)) > 1);        
      } else {
        $multi_language = false;
      }
    }
    
    // Output
    $output  = '<?xml version="1.0" encoding="UTF-8"?>';
    $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
         
    if (!$multi_language) {
      $output .= $this->getLanguageOutput(0);
    } else {
      foreach ($language_query->rows as $language) {
        $this->setLanguage($language['language_id'], $language['code']);
        
        $output .= $this->getLanguageOutput($language['language_id']);
      } 
    }

    $output .= '</urlset>';

    $this->ocfilter->opencart->responseXML($output);
  }
  
  protected function getLanguageOutput($language_id) {
    $output = '';
    
    $pages = $this->model_extension_module_ocfilter->getPages([ 'filter_sitemap' => true, ]);
    
    foreach ($pages as $page) {
      $cache_key = $language_id . '.' . $page['category_id'];
      
      if (isset($this->category_link[$cache_key])) {
        $link = $this->category_link[$cache_key];
      } else {
        $link = $this->url->link('product/category', 'path=' . $page['path'], 'SSL');  
        
        if (is_null($this->url_suffix)) {
          $this->url_suffix = $this->ocfilter->seo->getUrlSuffix($link);
        }
      
        $link = rtrim($link, '/');  
        
        if (false !== utf8_strpos($link, '.html')) {
          $link = utf8_substr($link, 0, utf8_strpos($link, '.html'));
        }  
        
        $this->category_link[$cache_key] = $link;
      }
        
      if ($page['keyword'] && $this->seo_url && false === strpos($link, 'index.php?route=')) {
        $link .= '/' . $page['keyword'] . $this->url_suffix;
      } else {
        $link .= (false === utf8_strpos($link, '?') ? '?' : '&amp;') . $this->ocfilter->params->getIndex() . '=' . $this->ocfilter->params->encode(json_decode($page['params'], true));
      }           
      
      $output .= '<url>';
      $output .= '<loc>' . $link . '</loc>';
      $output .= '<changefreq>weekly</changefreq>';
      $output .= '<priority>0.6</priority>';
      $output .= '</url>';
    }
    
    return $output;
  }
  
  private function setLanguage($language_id, $code) {
    $this->config->set('config_language', $code);  
    $this->config->set('config_language_id', $language_id);
    $this->session->data['language'] = $code;         
    
    if ($this->langmark_settings && !empty($this->langmark_settings['multi'])) {
      foreach ($this->langmark_settings['multi'] as $name => $settings_multi) {
        if (isset($settings_multi['name']) && $language_id == $settings_multi['language_id']) {
          $this->registry->set('langmark_multi', $settings_multi);
                                          
          break;
        }
      }                      
    }       
    
    if ($this->langmark_settings) {
      $this->load->controller('record/langmark/switchLanguage', $this->langmark_settings, $language_id, $code);
    }    
  }
}