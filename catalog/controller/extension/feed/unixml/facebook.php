<?php
class ControllerExtensionFeedUnixmlFacebook extends Controller {
  public function index() {

    $feed = 'facebook';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml  = '<?xml version="1.0"?>';
      $xml .= '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">';
      $xml .= '<title>' . $startup['name'] . '</title>';
      $xml .= '<link>' . HTTPS_SERVER . '</link>';
      $xml .= '<updated>' . date('Y-m-d H:i', time()) . '</updated>';

      $xml = $this->unixml->exportToXml($startup, $xml, "start");
      //headerXML

      //generateXML
        for($startup['iteration'] = 0; 1; $startup['iteration']++){

          $controller_data = $this->load->controller($controller, $startup);
          $startup['stat'] = $controller_data['data']['stat'];

          if($controller_data['products']){

            foreach($controller_data['products'] as $product_id => $product){
              $xml .= '<entry>';
              $xml .= '<g:title>' . $product['name'] . '</g:title>';
              $xml .= '<g:link>' . $product['url'] .  '</g:link>';
              $xml .= '<g:id>' . $product_id . '</g:id>';
              if(isset($product['group_id'])){
                $xml .= '<g:item_group_id>' . $product['group_id'] . '</g:item_group_id>';
              }
              if($product['special']){
                $xml .= '<g:price>' . $product['price'] . ' ' . $startup['currency_xml'] . '</g:price>';
                $xml .= '<g:sale_price>' . $product['special'] . ' ' . $startup['currency_xml'] . '</g:sale_price>';
                $xml .= '<g:sale_price_effective_date>' . date("Y-m-d", strtotime("yesterday")) . '/' . date("Y-m-d", strtotime("tomorrow")) . '</g:sale_price_effective_date>';
              }else{
                $xml .= '<g:price>' . $product['price'] . ' ' . $startup['currency_xml'] . '</g:price>';
              }
              $xml .= '<g:description><![CDATA[' . $product['description'] .  ']]></g:description>';

              if(isset($startup['categories_xml'][$product['category_id']]['market_id']) && $startup['categories_xml'][$product['category_id']]['market_id']){
                $xml .= '<g:google_product_category>' . $startup['categories_xml'][$product['category_id']]['market_id'] . '</g:google_product_category>';
              }
              $xml .= '<g:brand>' . $product['manufacturer'] . '</g:brand>';
              $xml .= '<g:condition>new</g:condition>';
              $xml .= '<g:image_link>' . $product['image'] .  '</g:image_link>';
              if($product['images']){
                foreach($product['images'] as $image){
                  $xml .= '<g:additional_image_link>' . $image .  '</g:additional_image_link>';
                }
              }
              $xml .= '<g:availability>' . ($product['quantity'] ? 'in stock' : 'out of stock') . '</g:availability>';
              foreach($product['attributes_full'] as $attribute){
                $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
              }
              foreach($product['attributes'] as $attribute){
                $xml .= '<g:product_detail>';
                  $xml .= '<g:attribute_name>' . $attribute['name'] . '</g:attribute_name>';
                  $xml .= '<g:attribute_value>' . $attribute['text'] . '</g:attribute_value>';
                $xml .= '</g:product_detail>';
              }
              if($startup['delivery_cost']) {
                $xml .= '<g:shipping><g:service>' . $startup['delivery_time'] . '</g:service><g:price>' . $startup['delivery_cost'] . '</g:price></g:shipping>';
              }
              $xml .= '</entry>';
            }
          } else {
            break;
          }

          $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
        }
      //generateXML

      //footerXML
      $xml .= '</feed>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
