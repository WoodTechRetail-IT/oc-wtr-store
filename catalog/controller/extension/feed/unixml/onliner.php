<?php
class ControllerExtensionFeedUnixmlOnliner extends Controller {
  public function index() {

    $feed = 'onliner';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
      $xml .= '<price-list version="1.0">';
      $xml .= '<items-list>';

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
              $xml .= '<item>';
              if(isset($startup['categories_xml'][$product['category_id']]['name']) && $startup['categories_xml'][$product['category_id']]['name']){
                $xml .= '<category>' . $startup['categories_xml'][$product['category_id']]['name'] .  '</category>';
              }
              $xml .= '<vendor>' . $product['manufacturer'] .  '</vendor>';
              $xml .= '<model>' . $product['name'] .  '</model>';
              $xml .= '<article>' . $product['model'] .  '</article>';
              $xml .= '<id>' . $product_id .  '</id>';
              $xml .= '<price>' . ($product['special']?$product['special']:$product['price']) .  '</price>';

              foreach($product['attributes_full'] as $attribute){
                if(trim($attribute['text']) == ''){
                  $xml .= '<' . $attribute['name'] . '/>';
                }else{
                  $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
                }
              }
              foreach($product['attributes'] as $attribute){
                $xml .= '<param name="' . $attribute['name'] . '">' . $attribute['text'] .  '</param>';
              }
              $xml .= '</item>';
            }
          } else {
            break;
          }

          $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
        }
      //generateXML

      //footerXML
      $xml .= '</items-list>';
      $xml .= '</price-list>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
