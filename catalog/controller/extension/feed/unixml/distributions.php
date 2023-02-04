<?php
class ControllerExtensionFeedUnixmlDistributions extends Controller {
  public function index() {

    $feed = 'distributions';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
      $xml .= '<price>';
      $xml .= '<date>' . date('Y-m-d H:i', time()) . '</date>';
      $xml .= '<firmName>' . $startup['name'] . '</firmName>';

      if($startup['categories_xml']) {
        $xml .= '<categories>';
        foreach($startup['categories_xml'] as $category) {
          $xml .= '<category>';
          $xml .= '<id>' . $category['category_id'] . '</id>';
          if($category['parent_id']){
            $xml .= '<parentId>' . $category['parent_id'] . '</parentId>';
          }
          $xml .= '<name>' . $category['name'] . '</name>';
          $xml .= '</category>';
        }
        $xml .= '</categories>';
      }

      $xml .= '<items>';

      $xml = $this->unixml->exportToXml($startup, $xml, "start");
      //headerXML

      //generateXML
        for($startup['iteration'] = 0; 1; $startup['iteration']++){

          $controller_data = $this->load->controller($controller, $startup);
          $startup['stat'] = $controller_data['data']['stat'];

          if($controller_data['products']){

            foreach($controller_data['products'] as $product_id => $product){
              $xml .= '<item>';
              $xml .= '<id>' . $product_id . '</id>';
              if($startup['option_multiplier_status'] && $startup['option_multiplier_id'] && $product['product_option_id']){
                $xml .= '<group_id>' . $product['product_original_id'] . '</group_id>';
              }
              $xml .= '<categoryId>' . $product['category_id'] .  '</categoryId>';
              $xml .= '<code>' . $product['model'] . '</code>';
              $xml .= '<vendor>' . $product['manufacturer'] .  '</vendor>';
              $xml .= '<name>' . $product['name'] .  '</name>';
              $xml .= '<url>' . $product['url'] .  '</url>';
              $xml .= '<image>' . $product['image'] .  '</image>';
              if($product['images']){
                foreach($product['images'] as $image){
                  $xml .= '<image>' . $image .  '</image>';
                }
              }
              if($product['stock']){
                $xml .= '<stock>' . $product['stock'] .  '</stock>';
              }
              if($product['special']){
                $xml .= '<priceRUAH>' . $product['special'] .  '</priceRUAH>';
                $xml .= '<oldprice>' . $product['price'] .  '</oldprice>';
              }else{
                $xml .= '<priceRUAH>' . $product['price'] .  '</priceRUAH>';
              }
              $xml .= '<description><![CDATA[' . $product['description'] .  ']]></description>';
              foreach($product['attributes_full'] as $attribute){
                $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
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
      $xml .= '</items>';
      $xml .= '</price>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
