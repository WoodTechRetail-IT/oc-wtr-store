<?php
class ControllerExtensionFeedUnixmlGudriem extends Controller {
  public function index() {

    $feed = 'gudriem';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="UTF-8" ?>';
      $xml .= '<root>';

      $xml = $this->unixml->exportToXml($startup, $xml, "start");
      //headerXML

      //generateXML
        for($startup['iteration'] = 0; 1; $startup['iteration']++){

          $controller_data = $this->load->controller($controller, $startup);
          $startup['stat'] = $controller_data['data']['stat'];

          if($controller_data['products']){

            foreach($controller_data['products'] as $product_id => $product){
              $xml .= '<item>';
              $xml .= '<name>' . $product['name'] .  '</name>';
              $xml .= '<model>' . $product['model'] .  '</model>';
              $xml .= '<link>' . $product['url'] .  '</link>';
              $xml .= '<price>' . ($product['special']?$product['special']:$product['price']) .  '</price>';
              $xml .= '<image>' . $product['image'] .  '</image>';
              $xml .= '<category>' . $product['image'] .  '</category>';
              $xml .= '<manufacturer>' . $product['manufacturer'] .  '</manufacturer>';
              $xml .= '<category_link>' . $this->url->link('product/category', 'path=' . $product['category_id']) .  '</category_link>';
              $xml .= '<category_full>' . $product['category_full'] .  '</category_full>';
              $xml .= '<in_stock>' . $product['quantity'] .  '</in_stock>';
              $xml .= '<description><![CDATA[' . $product['description'] .  ']]></description>';
              foreach($product['attributes_full'] as $attribute){
                $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
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
      $xml .= '</root>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
