<?php

/**
 * @category   OpenCart
 * @package    Branched Sitemap
 * @copyright  © Serge Tkach, 2018–2022, http://sergetkach.com/
 */

class ControllerExtensionFeedBranchedSitemap extends Controller {
  private $sitemap;
  private $exist_main_cat;
  private $type;
	private $type4cache = '';
  private $rn = PHP_EOL;
  private $xml_image_href;
  private $base_url;
  private $page;
  private $limit;
	private $changefreq;
  private $priority;
	private $cachetime;

	private $language;

  function __construct($registry) {
    parent::__construct($registry);

    if (!$this->config->get('feed_branched_sitemap_status')) {
      $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
      exit;
    }

		$this->language = '';
		
		// Явное указание языка через GET - совместимо с OCDEV.pro - Мультиязык SEO PRO, код языка в url и правильный hreflang
		if (isset($this->request->get['lang_code']) && $this->request->get['lang_code']) {
			$this->language = '&lang_code=' . $this->request->get['lang_code'];
		
			$this->config->set('config_language_id', $this->getLanguageIdByCode($this->request->get['lang_code']));
		} else {
			// Надежда на ЧПУ
			$this->language = '&lang_code=' . $this->getLanguageCodeById($this->config->get('config_language_id'));
		}
		
    if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$this->base_url = $this->config->get('config_ssl');
		} else {
			$this->base_url = $this->config->get('config_url');
		}

    $this->page = 1;

		if (isset($this->request->get['page'])) {
      $this->page = $this->request->get['page'];
    }

		// cachetime
		$this->cachetime = $this->config->get('feed_branched_sitemap_cachetime');
		
		// limit
    $this->limit = $this->config->get('feed_branched_sitemap_limit');

    if (!$this->limit) {
      $this->limit = 200;
    }
		
    // image...
    $this->load->model('extension/feed/branched_sitemap');
    $this->exist_main_cat = $this->model_extension_feed_branched_sitemap->existMainCat();

    if ((isset($this->request->get['type']) && 'image' == $this->request->get['type']) || false != strpos($this->request->get['route'], 'image')) {
			$this->type = '&type=image';
			
			$this->type4cache = '_image';

			$this->xml_image_href = 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';

			$this->limit = $this->config->get('feed_branched_sitemap_limit_image');

			if (!$this->limit) {
				$this->limit = 50;
			}
		}

    if (version_compare(PHP_VERSION, '7.2') >= 0) {
      $php_v = '72_73';
    } elseif (version_compare(PHP_VERSION, '7.1') >= 0) {
			$php_v = '71';
    } elseif (version_compare(PHP_VERSION, '5.6.0') >= 0) {
      $php_v = '56_70';
    } elseif (version_compare(PHP_VERSION, '5.4.0') >= 0) {
      $php_v = '54_56';
    } else {
      echo "Sorry! Version for PHP 5.3 Not Supported!<br>Please contact to author!";
      exit;
    }

    $file = DIR_SYSTEM . 'library/branched_sitemap/branched_sitemap_' . $php_v . '.php';

    if (is_file($file)){
      require_once $file;
    } else {
      echo "No file '$file'<br>";
      exit;
    }

    // todo...
    // get licence
    $this->sitemap = new Sitemap($this->config->get('feed_branched_sitemap_licence'));

    $this->changefreq = array(
      'category_changefreq_default' => 'yearly', // Более 1 года
      'category_changefreq_correlation' => array(
        '1'  => 'daily',
        '7'  => 'weekly',
        '30' => 'monthly',
        '365' => 'yearly',
      ),
      'product_changefreq_default' => 'yearly', // Более 1 года
      'product_changefreq_correlation' => array(
        '1'  => 'daily',
        '7'  => 'weekly',
        '30' => 'monthly',
        '365' => 'yearly',
      ),
    );

    $this->priority = array(
      'category_priority_correlation' => array(
        '5'  => '1.0',
        '10' => '1.0',
        '15' => '0.9',
        '20' => '0.8',
        '30' => '0.7',
        '60' => '0.6', // default priority https://www.sitemaps.org/ru/protocol.html
        '65' => '0.6',
        '70' => '0.5',
        '80' => '0.5',
      ),
      'product_priority_correlation' => array(
        '5'  => '1.0',
        '10' => '0.9',
        '15' => '0.8',
        '20' => '0.7',
        '30' => '0.5',
        '60' => '0.4', // default priority https://www.sitemaps.org/ru/protocol.html
        '65' => '0.3',
        '70' => '0.2',
        '80' => '0.1',
      ),
    );
  }

  public function index() {
    $output  = '<?xml version="1.0" encoding="UTF-8"?>'. $this->rn;
    /*$output .= '<?xml-stylesheet type="text/xsl" href="' . $this->base_url. 'catalog/view/theme/default/stylesheet/xml-sitemap.xls"?>' . $this->rn;*/
    $output .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $this->rn;

		$output .= '<sitemap>'
			. '<loc>' . $this->url->link('extension/feed/branched_sitemap/main', $this->language . $this->type) . '</loc>'
			. '</sitemap>';

    $output .= $this->getCategoriesIndex();
    $output .= $this->getProductsIndex();
    $output .= $this->getManufacturersIndex();
    $output .= $this->getInformationIndex();


    $output .= '</sitemapindex>' . $this->rn;

    $this->response->addHeader('Content-Type: text/xml; charset=UTF-8');
    $this->response->setOutput($output);
  }

  public function image() {

		$this->type = '&type=image';
		
    $output  = '<?xml version="1.0" encoding="UTF-8"?>'. $this->rn;
    $output .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $this->rn;

//    $output .= $this->getCategoriesIndex();
    $output .= $this->getProductsIndex();
//    $output .= $this->getManufacturersIndex();
//    $output .= $this->getInformationIndex();


    $output .= '</sitemapindex>' . $this->rn;

    $this->response->addHeader('Content-Type: text/xml; charset=UTF-8');
    $this->response->setOutput($output);
  }

  /* Main
  --------------------------------------------------------------------------- */
  public function main(){
    $output  = '<?xml version="1.0" encoding="UTF-8"?>'. $this->rn;
    $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $this->rn;
			$output .= '<url>';
			$output .= '<loc>' . $this->url->link('common/home') . '</loc>';
			$output .= '<lastmod>' . date('Y-m-d\TH:i:sP', time()) . '</lastmod>';
			$output .= '<changefreq>daily</changefreq>';
			$output .= '<priority>1.0</priority>';
			$output .= '</url>';
    $output .= '</urlset>' . $this->rn;

    $this->response->addHeader('Content-Type: text/xml; charset=UTF-8');
    $this->response->setOutput($output);
  }

  /* Categories
  --------------------------------------------------------------------------- */
  public function categories(){
    if (!$this->page) {
      return $this->getCategoriesIndex();
    } else {
      return $this->getCategoriesOnPage();
    }
  }

  private function getCategoriesIndex() {
    $output  = '';

    // No Levels - important date modified
    $this->load->model('extension/feed/branched_sitemap');

    $categories_total = $this->model_extension_feed_branched_sitemap->getTotalCategories();

    $n_pages = ceil($categories_total / $this->limit);

    $i = 1;
    while($i <= $n_pages) {
      $output .= '<sitemap>' . $this->rn;
      $output .= '<loc>' . $this->url->link('extension/feed/branched_sitemap/categories', $this->language . $this->type . '&page=' . $i) . '</loc>' . $this->rn;
      $output .= '<lastmod>' . date('Y-m-d\TH:i:sP', time()) . '</lastmod>' . $this->rn;
      $output .= '</sitemap>' . $this->rn;
      $i++;
    }

    return $output;
  }

  protected function getCategoriesOnPage() {
		$file = DIR_CACHE . 'branched_sitemap_store' . $this->config->get('config_store_id') . '_lang' . $this->config->get('config_language_id') . '_' . 'categories' . $this->type4cache . '_' . $this->page . '.xml';
		
		if ($this->cachedFile($file, $this->cachetime)) {
			$this->readFile($file);
			exit;
		}

    $this->load->model('extension/feed/branched_sitemap');

    $output  = '<?xml version="1.0" encoding="UTF-8"?>' . $this->rn;
    /*$output .= '<?xml-stylesheet type="text/xsl" href="' . $this->base_url. 'catalog/view/theme/default/stylesheet/xml-sitemap.xls"?>' . $this->rn;*/
    $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $this->rn;

    $filter_data = array(
      'start' => ($this->page - 1) * $this->limit,
      'limit' => $this->limit
    );

    // No Levels - important date modified
    $categories = $this->model_extension_feed_branched_sitemap->getCategories($filter_data);

    foreach ($categories as $category) {
      $output .= '<url>' . $this->rn;

      //$output .= '<loc>' . $this->url->link('product/category', 'path=' . $category['category_id']) . '</loc>' . $this->rn;
      $output .= '<loc>' . $this->url->link('product/category', 'path=' . $this->model_extension_feed_branched_sitemap->getPathByCategory($category['category_id'])) . '</loc>' . $this->rn;

      if($category['date_modified'] > '0000-00-00 00:00:00') $date = $category['date_modified'];
      else $date = $category['date_added'];
      $output .= '<lastmod>' . date('Y-m-d\TH:i:sP', strtotime($date)) . '</lastmod>' . $this->rn;

      $data = array(
        'date' => $date,
        'changefreq_correlation' => $this->changefreq['category_changefreq_correlation'],
        'changefreq_default' => $this->changefreq['category_changefreq_default']
      );

      $output .= '<changefreq>' . $this->sitemap->getCategoryChangefreq($data) . '</changefreq>' . $this->rn;

      $data = array(
        'date' => $date,
        'priority_correlation' => $this->priority['category_priority_correlation']
      );

      $output .= '<priority>' . $this->sitemap->getCategoryPriority($data) . '</priority>' . $this->rn;

      $output .= '</url>' . $this->rn;
    }

    $output .= '</urlset>' . $this->rn;

		if ($this->cachetime > 0) {
			$this->saveFile($file, $output);
		}

    $this->response->addHeader('Content-Type: text/xml; charset=UTF-8');
    $this->response->setOutput($output);
	}

  /* Products
  --------------------------------------------------------------------------- */

	public function products() {
    if (!$this->page) {
      return $this->getProductsIndex();
    } else {
      return $this->getProductsOnPage();
    }
  }

  private function getProductsIndex() {
    $output = '';

    $this->load->model('extension/feed/branched_sitemap');

    $product_total = $this->model_extension_feed_branched_sitemap->getTotalProducts();

    $n_pages = ceil($product_total / $this->limit);

    $i = 1;
    while($i <= $n_pages) {
      $output .= '<sitemap>' . $this->rn;
      $output .= '<loc>' . $this->url->link('extension/feed/branched_sitemap/products', $this->language . $this->type . '&page=' . $i) . '</loc>' . $this->rn;
      $output .= '<lastmod>' . date('Y-m-d\TH:i:sP', time()) . '</lastmod>' . $this->rn;
      $output .= '</sitemap>' . $this->rn;
      $i++;
    }

    return $output;
  }


  private function getProductsOnPage() {
		$file = DIR_CACHE . 'branched_sitemap_store' . $this->config->get('config_store_id') . '_lang' . $this->config->get('config_language_id') . '_' . 'products' . $this->type4cache . '_' . $this->page . '.xml';
		
		if ($this->cachedFile($file, $this->cachetime)) {
			$this->readFile($file);
			exit;
		}
		
    $this->load->model('extension/feed/branched_sitemap');
    $this->load->model('catalog/product');
    $this->load->model('tool/image'); // image

    $output  = '<?xml version="1.0" encoding="UTF-8"?>'. $this->rn;
    /*$output .= '<?xml-stylesheet type="text/xsl" href="' . $this->base_url. 'catalog/view/theme/default/stylesheet/xml-sitemap.xls"?>' . $this->rn;*/
    $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' . $this->xml_image_href . '>' . $this->rn;

    $filter_data = array(
      'image' => true,
      'image_caption' => $this->config->get('feed_branched_sitemap_require_image_caption'),
      'images' => false,
      'start' => ($this->page - 1) * $this->limit,
      'limit' => $this->limit
    );

    $products = $this->model_extension_feed_branched_sitemap->getProducts($filter_data);

    foreach ($products as $product) {
      $output .= '<url>' . $this->rn;

      if ($this->exist_main_cat) {
        $output .= '<loc>' . $this->url->link('product/product', 'path=' . $this->model_extension_feed_branched_sitemap->getPathByProduct($product['product_id']) . '&product_id=' . $product['product_id']) . '</loc>' . $this->rn;
      } else {
        $output .= '<loc>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</loc>' . $this->rn;
      }

      if($product['date_modified'] > '0000-00-00 00:00:00') $date = $product['date_modified'];
      else $date = $product['date_added'];
      $output .= '<lastmod>' . date('Y-m-d\TH:i:sP', strtotime($date)) . '</lastmod>' . $this->rn;

      $data = array(
        'date' => $date,
        'changefreq_correlation' => $this->changefreq['product_changefreq_correlation'],
        'changefreq_default' => $this->changefreq['product_changefreq_default']
      );

      $output .= '<changefreq>' . $this->sitemap->getProductChangefreq($data) . '</changefreq>' . $this->rn;

      $data = array(
        'date' => $date,
        'priority_correlation' => $this->priority['product_priority_correlation']
      );

      $output .= '<priority>' . $this->sitemap->getProductPriority($data) . '</priority>' . $this->rn;

      // image
      if ('&type=image' == $this->type && $filter_data['image']) {
        if ($product['image']) {
          $image_info = pathinfo($product['image']);
					
					// Sometimes can be 'undefined' ... - bug of filemanager or...
					if (isset($image_info['extension'])) {
						// Image Config is defferent for 2.1 (2.2), for 2.3 & for 3.0.2 !!
						
						// Image Resize create hight load - so we can joke :)
						if ($this->config->get('feed_branched_sitemap_off_check_image_file')) {
							$image = $image_info['dirname'] . '/' . $image_info['filename'] . '-' . $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width') . 'x' . $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height') . '.' . $image_info['extension'];

							$image = HTTPS_SERVER . 'image/cache/' . $image;
							
							if (!is_file(DIR_IMAGE . 'cache/' . $image)) {
								// Report :)
								$this->log->write('Branched Sitemap :: Image "' . $image . '" not exists on page ' . $this->url->link('product/product', '&product_id=' . $product['product_id'])); 
							}
						} else {
							$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
						}
					}

          if ($image) {
            $output .= '<image:image>' . $this->rn;
            $output .= '<image:loc>' . $image . '</image:loc>' . $this->rn;
            if ($filter_data['image_caption'] && $product['name']) {
            $output .= '<image:caption>' . $this->cleanup($product['name']) . '</image:caption>' . $this->rn;
            $output .= '<image:title>' . $this->cleanup($product['name']) . '</image:title>' . $this->rn;
            }
            $output .= '</image:image>' . $this->rn;
          }
        }
      }

      $output .= '</url>' . $this->rn;
    }

    $output .= '</urlset>' . $this->rn;

		if ($this->cachetime > 0) {
			$this->saveFile($file, $output);
		}

    $this->response->addHeader('Content-Type: text/xml; charset=UTF-8');
    $this->response->setOutput($output);
  }

  /* Manufacturers
  --------------------------------------------------------------------------- */
  public function manufacturers(){
    if (!$this->page) {
      return $this->getManufacturersIndex();
    } else {
      return $this->getManufacturersOnPage();
    }
  }

  private function getManufacturersIndex() {
    $output  = '';

    $this->load->model('extension/feed/branched_sitemap');

    $manufacturers_total = $this->model_extension_feed_branched_sitemap->getTotalManufacturers();

    $n_pages = ceil($manufacturers_total / $this->limit);

    $i = 1;
    while($i <= $n_pages) {
      $output .= '<sitemap>' . $this->rn;
      $output .= '<loc>' . $this->url->link('extension/feed/branched_sitemap/manufacturers', $this->language . $this->type . '&page=' . $i) . '</loc>' . $this->rn;
			$output .= '<lastmod>' . date('Y-m-d\TH:i:sP', time()) . '</lastmod>' . $this->rn;
      $output .= '</sitemap>' . $this->rn;
      $i++;
    }

    return $output;
  }


  private function getManufacturersOnPage() {
		$file = DIR_CACHE . 'branched_sitemap_store' . $this->config->get('config_store_id') . '_lang' . $this->config->get('config_language_id') . '_' . 'manufacturers' . $this->type4cache . '_' . $this->page . '.xml';
		
		if ($this->cachedFile($file, $this->cachetime)) {
			$this->readFile($file);
			exit;
		}
		
    $output  = '<?xml version="1.0" encoding="UTF-8"?>'. $this->rn;
    /*$output .= '<?xml-stylesheet type="text/xsl" href="' . $this->base_url. 'catalog/view/theme/default/stylesheet/xml-sitemap-manufacturers.xls"?>' . $this->rn;*/
    $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $this->rn;

    $this->load->model('extension/feed/branched_sitemap');

    $filter_data = array(
      'start' => ($this->page - 1) * $this->limit,
      'limit' => $this->limit
    );

    $manufacturers = $this->model_extension_feed_branched_sitemap->getManufacturers($filter_data);

    foreach ($manufacturers as $manufacturer) {
      $output .= '<url>' . $this->rn;
      $output .= '<loc>' . $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']) . '</loc>' . $this->rn;
      $output .= '<priority>0.3</priority>' . $this->rn;
      $output .= '</url>' . $this->rn;
    }

    $output .= '</urlset>' . $this->rn;

		if ($this->cachetime > 0) {
			$this->saveFile($file, $output);
		}

    $this->response->addHeader('Content-Type: text/xml; charset=UTF-8');
    $this->response->setOutput($output);
  }

  /* Information
  --------------------------------------------------------------------------- */
  public function information(){
    if (!$this->page) {
      return $this->getInformationIndex();
    } else {
      return $this->getInformationOnPage();
}
  }

  private function getInformationIndex() {
    $output = '';

    $this->load->model('extension/feed/branched_sitemap');

    $information_total = $this->model_extension_feed_branched_sitemap->getTotalInformation();

    $n_pages = ceil($information_total / $this->limit);

    $i = 1;
    while($i <= $n_pages) {
      $output .= '<sitemap>' . $this->rn;
      $output .= '<loc>' . $this->url->link('extension/feed/branched_sitemap/information', $this->language . $this->type . '&page=' . $i) . '</loc>' . $this->rn;
			$output .= '<lastmod>' . date('Y-m-d\TH:i:sP', time()) . '</lastmod>' . $this->rn;
      $output .= '</sitemap>' . $this->rn;
      $i++;
    }

    return $output;
  }


  private function getInformationOnPage() {
		$file = DIR_CACHE . 'branched_sitemap_store' . $this->config->get('config_store_id') . '_lang' . $this->config->get('config_language_id') . '_' . 'information' . $this->type4cache . '_' . $this->page . '.xml';
		
		if ($this->cachedFile($file, $this->cachetime)) {
			$this->readFile($file);
			exit;
		}
		
    $output  = '<?xml version="1.0" encoding="UTF-8"?>'. $this->rn;
    /*$output .= '<?xml-stylesheet type="text/xsl" href="' . $this->base_url. 'catalog/view/theme/default/stylesheet/xml-sitemap-information.xls"?>' . $this->rn;*/
    $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $this->rn;

    $this->load->model('extension/feed/branched_sitemap');

    $filter_data = array(
      'start' => ($this->page - 1) * $this->limit,
      'limit' => $this->limit
    );

    $information = $this->model_extension_feed_branched_sitemap->getInformation($filter_data);

    foreach ($information as $information) {
      $output .= '<url>' . $this->rn;
      $output .= '<loc>' . $this->url->link('information/information', 'information_id=' . $information['information_id']) . '</loc>' . $this->rn;
      $output .= '<priority>0.3</priority>' . $this->rn;
      $output .= '</url>' . $this->rn;
    }

    $output .= '</urlset>' . $this->rn;

		if ($this->cachetime > 0) {
			$this->saveFile($file, $output);
		}

    $this->response->addHeader('Content-Type: text/xml; charset=UTF-8');
    $this->response->setOutput($output);
  }

	public function cleanup($str) {
		//htmlentities($product['name'], ENT_QUOTES, "UTF-8"); // &laquo; - not valid char - see protocol...
		return str_replace(array('&', '\'', '"', '>', '<'), array('&amp;', '&apos;', '&quot;', '&gt;', '&lt;'), $str);
	}


	

	/* Helpers
		--------------------------------------------------------------------------- */

	public function cachedFile($file, $cachetime) {
		if ('0' == $cachetime) return false;
		
		if (!is_file($file)) return false;
		
		if (time() - filemtime($file) > $cachetime) {
			unlink($file);
			return false;
	}
		
		clearstatcache(true, $file);
		
		if (@filesize($file) > 0) {
			return true;
		}
		
		return false;		
	}

	public function saveFile($file, $data) {
		$res = @file_put_contents($file, $data);
		
		if (false !== $res) {
			return true;
		} else {
			return false;
		}
	}
	
	public function readFile($file) {
		header('Content-Type: text/xml; charset=UTF-8');
		readfile($file);
	}

	// for ocdev.pro - multilang compatibility
	private function getLanguageIdByCode($code) {
		$this->load->model('localisation/language');

		$data['languages'] = array();

		$results = $this->model_localisation_language->getLanguages();

		return $results[$code]['language_id'];
}
	
	private function getLanguageCodeById($language_id) {
		$this->load->model('localisation/language');

		$result = $this->model_localisation_language->getLanguage($language_id);
		
		return $result['code'];
	}
}
	
