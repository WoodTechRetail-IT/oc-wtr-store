<?php
class ControllerExtensionFeedUnixmlBeru extends Controller {
  public function index() {

    $feed = 'beru';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<shop>';
      $xml .= '<currencies>';
      $xml .= '<currency id="' . $startup['currency_xml'] . '" rate="1"/>';
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
              $xml .= '<offer id="' . $product_id . '">';
              $xml .= '<name>' . $product['name'] .  '</name>';
              $xml .= '<vendorCode>' . $product['model'] .  '</vendorCode>';
              $xml .= '<shop-sku>' . $product_id .  '</shop-sku>';
              if(isset($product['sku'])){
                $xml .= '<market-sku>' . $product['sku'] .  '</market-sku>';
              }
              $xml .= '<categoryId>' . $product['category_id'] .  '</categoryId>';
              $xml .= '<vendor>' . $product['manufacturer'] .  '</vendor>';
              $xml .= '<manufacturer>' . $product['manufacturer'] .  '</manufacturer>';
              $xml .= '<url>' . $product['url'] .  '</url>';

              if($product['special']){
                $xml .= '<price>' . $product['special'] .  '</price>';
                $xml .= '<oldprice>' . $product['price'] .  '</oldprice>';
              }else{
                $xml .= '<price>' . $product['price'] .  '</price>';
              }

              $xml .= '<description><![CDATA[' . $product['description'] .  ']]></description>';
              foreach($product['attributes_full'] as $attribute){
                $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
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

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
