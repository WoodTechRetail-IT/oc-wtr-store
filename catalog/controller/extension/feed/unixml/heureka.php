<?php
class ControllerExtensionFeedUnixmlHeureka extends Controller {
  public function index() {

    $feed = 'heureka';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
			$xml .= '<?xml version="1.0" encoding="utf-8"?>';
	    $xml .= '<SHOP>';

      if($startup['categories_xml']) {
        $xml .= '<categories>';
        foreach($startup['categories_xml'] as $category) {
          if($category['parent_id']){
            $xml .= '<category id="' . $category['category_id'] .'" ' . (($category['market_id'])?'rz_id="' . $category['market_id'] . '"':'') . ' parentId="' . $category['parent_id'] . '">' . $category['name'] .'</category>';
          } else{
            $xml .= '<category id="' . $category['category_id'] .'" ' . (($category['market_id'])?'rz_id="' . $category['market_id'] . '"':'') . '>' . $category['name'] .'</category>';
          }
        }
        $xml .= '</categories>';
      }

      $xml .= '<offers>';

      $xml = $this->unixml->exportToXml($startup, $xml, "start");
      //headerXML

      //generateXML
        for($startup['iteration'] = 0; 1; $startup['iteration']++){

          $controller_data = $this->load->controller($controller, $startup);
          $startup['stat'] = $controller_data['data']['stat'];

          if($controller_data['products']){

            foreach($controller_data['products'] as $product_id => $product){
							$xml .= '<SHOPITEM>';
              $xml .= '<ITEM_ID>' . $product_id .  '</ITEM_ID>';
              $xml .= '<PRODUCTNAME>' . $product['name'] .  '</PRODUCTNAME>';
              $xml .= '<PRODUCT>' . $product['name'] .  '</PRODUCT>';
              $xml .= '<DESCRIPTION>' . $product['description'] .  '</DESCRIPTION>';
              $xml .= '<URL>' . $product['url'] .  '</URL>';
              $xml .= '<IMGURL>' . $product['image'] .  '</IMGURL>';
              if($product['images']){
                foreach($product['images'] as $image){
                  $xml .= '<IMGURL_ALTERNATIVE>' . $image .  '</IMGURL_ALTERNATIVE>';
                }
              }
              $xml .= '<PRICE_VAT>' . ($product['special']?$product['special']:$product['price']) .  '</PRICE_VAT>';
              if(isset($product['cpc']) && $product['cpc']){
                $xml .= '<HEUREKA_CPC>' . $product['cpc'] .  '</HEUREKA_CPC>';
              }
              $xml .= '<MANUFACTURER>' . $product['manufacturer'] .  '</MANUFACTURER>';
							if(isset($startup['categories_xml'][$product['category_id']]['name']) && $startup['categories_xml'][$product['category_id']]['name']){
								$xml .= '<CATEGORYTEXT>' . $startup['categories_xml'][$product['category_id']]['name'] .  '</CATEGORYTEXT>';
							}
              foreach($product['attributes'] as $attribute){
                $xml .= '<PARAM><PARAM_NAME>' . $attribute['name'] . '</PARAM_NAME><VAL>' . $attribute['text'] .  '</VAL></PARAM>';
              }
              if($product['stock']){
                $xml .= '<DELIVERY_DATE>0</DELIVERY_DATE>';
              }
              if(isset($product['group_id'])){
                $xml .= '<ITEMGROUP_ID>' . $product['group_id'] . '</ITEMGROUP_ID>';
              }
              foreach($product['attributes_full'] as $attribute){
                if(trim($attribute['text']) == ''){
                  $xml .= '<' . $attribute['name'] . '/>';
                }else{
                  $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
                }
              }
	            $xml .= '</SHOPITEM>';
            }
          } else {
            break;
          }

          $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
        }
      //generateXML

      //footerXML
      $xml .= '</SHOP>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
