<?php
class ControllerExtensionFeedUnixmlHalyk extends Controller {
  public function index() {

    $feed = 'halyk';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<goods>';

      $xml = $this->unixml->exportToXml($startup, $xml, "start");
      //headerXML

      //generateXML
        for($startup['iteration'] = 0; 1; $startup['iteration']++){

          $controller_data = $this->load->controller($controller, $startup);
          $startup['stat'] = $controller_data['data']['stat'];

          if($controller_data['products']){

            foreach($controller_data['products'] as $product_id => $product){
              $xml .= '<good sku="' . $product_id . '">';
              $xml .= '<name>' . $product['name'] .  '</name>';
              if(isset($startup['categories_xml'][$product['category_id']]['name']) && $startup['categories_xml'][$product['category_id']]['name']){
                $xml .= '<category>' . $startup['categories_xml'][$product['category_id']]['name'] .  '</category>';
              }
              if(isset($product['availabilities'])){
                $xml .= '<stocks>';
                  foreach($product['availabilities'] as $availability){
                    $xml .= '<stock available="yes" storeId="' . $availability['store_id'] . '" />';
                  }
                $xml .= '</stocks>';
              }
              $xml .= '<price>' . ($product['special']?$product['special']:$product['price']) .  '</price>';
              foreach($product['attributes_full'] as $attribute){
                $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
              }
              $xml .= '</good>';
            }
          } else {
            break;
          }

          $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
        }
      //generateXML

      //footerXML
      $xml = '</goods>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
