<?php
class ControllerExtensionFeedUnixmlEsputnik extends Controller {
  public function index() {

    $feed = 'esputnik';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0"?>';
      $xml .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
      $xml .= '<channel>';
      $xml .= '<title>' . $startup['name'] . '</title>';
      $xml .= '<link>' . HTTPS_SERVER . '</link>';

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
              $xml .= '<g:id>' . $product_id . '</g:id>';
              $xml .= '<g:title>' . $product['name'] . '</g:title>';
              $xml .= '<g:link>' . $product['url'] .  '</g:link>';
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
              $xml .= '<g:brand>' . html_entity_decode($product['manufacturer'], ENT_QUOTES, 'UTF-8') . '</g:brand>';
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
              $xml .= '</item>';
            }
          } else {
            break;
          }

          $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
        }
      //generateXML

      //footerXML
      $xml .= '</channel>';
      $xml .= '</rss>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
