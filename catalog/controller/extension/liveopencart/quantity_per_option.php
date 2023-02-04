<?php
//  Quantity per Option / Количество для опций
//  Support: support@liveopencart.com / Поддержка: help@liveopencart.ru

class ControllerExtensionLiveopencartQuantityPerOption extends Controller {
	
	public function __construct() {
		call_user_func_array( array('parent', '__construct') , func_get_args());
		
		\liveopencart\ext\qpo::getInstance($this->registry);
	}
	
  private function addProductPageResources() {
    if ( $this->liveopencart_ext_qpo->installed() ) {
			// styles and secripts should be added before the product page template state (to be applied on rendering common/header)
			$this->liveopencart_ext_qpo->addProductPageStyles();
			$this->liveopencart_ext_qpo->addProductPageScripts();
		}
  }
  
  private function addProductPageData($data) {
    
    if ( $this->liveopencart_ext_qpo->installed() ) {
			$product_id = isset($this->request->get['product_id']) ? $this->request->get['product_id'] : 0;
			
			$data['qpo_installed'] 			= $this->liveopencart_ext_qpo->installed();
			$data['qpo_template_path'] 	= $this->liveopencart_ext_qpo->getProductPageOptionTemplatePath();
			
			$data['qpo_data'] 					= $this->liveopencart_ext_qpo->getProductPageQPOData($product_id);
			
		}
    
    return $data;
  }
  
  public function addProductPageResourcesAndData($data) {
    $this->addProductPageResources();
    return $this->addProductPageData($data);
  }
  
	// catalog/controller/product/product/before
	public function eventProductPageControllerIndexBefore(&$route, &$data) {
		$this->addProductPageResources();
	}
	
	// catalog/view/product/product/before
	public function eventProductPageViewBefore(&$route, &$data, &$template) {
		$data = $this->addProductPageData($data);
	}
	
}