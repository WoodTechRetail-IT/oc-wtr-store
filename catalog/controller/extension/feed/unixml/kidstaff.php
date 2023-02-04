<?php
class ControllerExtensionFeedUnixmlKidstaff extends Controller {
  public function index() {

    $feed = 'kidstaff';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<yml_catalog date="' . date('Y-m-d H:i', time()) . '">';
      $xml .= '<shop>';
      $xml .= '<currencies>';
      $xml .= '<currency id="' . $startup['currency'] . '" rate="1"/>';
      $xml .= '</currencies>';
      $xml .= '<offers>';

      $xml = $this->unixml->exportToXml($startup, $xml, "start");
      //headerXML

      //generateXML
        for($startup['iteration'] = 0; 1; $startup['iteration']++){

          $controller_data = $this->load->controller($controller, $startup);
          $startup['stat'] = $controller_data['data']['stat'];

          if($controller_data['products']){

            foreach($controller_data['products'] as $product_id => $product){
              $xml .= '<offer id="' . $product_id . '" available="' . ($product['stock']?'true':'false') .'">';
              $xml .= '<name>' . $product['name'] .  '</name>';
              $xml .= '<model>' . $product['model'] .  '</model>';
              $xml .= '<warranty>true</warranty>';
              $xml .= '<currencyId>' . $startup['currency'] . '</currencyId>';
              $xml .= '<categoryId>' . $product['category_id'] .  '</categoryId>';
              $xml .= '<price>' . ($product['special']?$product['special']:$product['price']) .  '</price>';
              $xml .= '<picture main="true">' . $product['image'] .  '</picture>';
              if($product['images']){
                foreach($product['images'] as $image){
                  $xml .= '<picture main="false">' . $image .  '</picture>';
                }
              }
              $xml .= '<description><![CDATA[' . $product['description'] .  ']]></description>';
              foreach($product['attributes_full'] as $attribute){
                $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
              }
              if($product['attributes']){
                $xml .= '<characteristics>';
                foreach($product['attributes'] as $attribute){
                  $xml .= '<characteristic name="' . $attribute['name'] . '">' . $attribute['text'] .  '</characteristic>';
                }
                $xml .= '</characteristics>';
              }
              $xml .= '</offer>';
            }
          } else {
            break;
          }

          $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
        }
      //generateXML

      //footerXML
      $xml .= '</offers>';
      $xml .= '</shop>';
      $xml .= '</yml_catalog>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
