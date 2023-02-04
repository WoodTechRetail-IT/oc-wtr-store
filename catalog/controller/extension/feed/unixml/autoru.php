<?php
class ControllerExtensionFeedUnixmlAutoru extends Controller {
  public function index() {

    $feed = 'autoru';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
      $xml .= '<parts>';
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
              $xml .= '<part>';
              $xml .= '<id>' . $product_id . '</id>';
              $xml .= '<title>' . $product['name'] .  '</title>';

              if(isset($product['stores'])){
                $xml .= '<stores>';
                foreach($product['stores'] as $store){
                  $xml .= '<store>' . $store . '</store>';
                }
                $xml .= '</stores>';
              }

              $xml .= '<offer_url>' . $product['url'] .  '</offer_url>';
              if(isset($product['mpn'])){
                $xml .= '<part_number>' . $product['mpn'] .  '</part_number>';
              }
              if(isset($product['cars'])){
                $xml .= '<compatibility>';
                foreach($product['cars'] as $car){
                  $xml .= '<car>' . $car . '</car>';
                }
                $xml .= '</compatibility>';
              }
              $xml .= '<manufacturer>' . $product['manufacturer'] .  '</manufacturer>';
              $xml .= '<description><![CDATA[' . $product['description'] .  ']]></description>';
              if($product['special']){
                $xml .= '<price>' . $product['special'] .  '</price>';
                $xml .= '<oldprice>' . $product['price'] .  '</oldprice>';
              }else{
                $xml .= '<price>' . $product['price'] .  '</price>';
              }
              $xml .= '<availability>';
                $xml .= '<isAvailable>' . ($product['stock']?'True':'False') .'</isAvailable>';
                if(isset($product['daysFrom'])){
                  $xml .= '<daysFrom>' . $product['daysFrom'] .'</daysFrom>';
                }
                if(isset($product['daysTo'])){
                  $xml .= '<daysTo>' . $product['daysTo'] .'</daysTo>';
                }
              $xml .= '</availability>';
              $xml .= '<images>';
              $xml .= '<image>' . $product['image'] .  '</image>';
              if($product['images']){
                foreach($product['images'] as $image){
                  $xml .= '<image>' . $image .  '</image>';
                }
              }
              $xml .= '</images>';
              foreach($product['attributes_full'] as $attribute){
                $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
              }
              if($product['attributes']){
                $xml .= '<properties>';
                foreach($product['attributes'] as $attribute){
                  $xml .= '<property name="' . $attribute['name'] . '">' . $attribute['text'] .  '</property>';
                }
                $xml .= '</properties>';
              }
              $xml .= '</part>';
            }
          } else {
            break;
          }

          $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
        }
      //generateXML

      //footerXML
      $xml .= '</parts>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
