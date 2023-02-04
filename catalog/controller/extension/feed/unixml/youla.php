<?php
class ControllerExtensionFeedUnixmlYoula extends Controller {
  public function index() {

    $feed = 'youla';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
      $xml .= '<yml_catalog date="' . date('Y-m-d H:i', time()) . '">';
      $xml .= '<shop>';

      if($startup['categories_xml']) {
        $xml .= '<categories>';
        foreach($startup['categories_xml'] as $category) {
          if($category['parent_id']){
            $xml .= '<category id="' . $category['category_id'] .'" parentId="' . $category['parent_id'] . '">' . $category['name'] .'</category>';
          } else{
            $xml .= '<category id="' . $category['category_id'] .'">' . $category['name'] .'</category>';
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
              $xml .= '<offer id="' . $product_id . '" available="' . ($product['stock']?'true':'') .'">';
              $xml .= '<name>' . $product['name'] .  '</name>';
              $xml .= '<vendorCode>' . $product['model'] .  '</vendorCode>';
              $xml .= '<url>' . $product['url'] .  '</url>';
              $xml .= '<currencyId>' . $startup['currency_xml'] . '</currencyId>';
              $xml .= '<categoryId>' . $product['category_id'] .  '</categoryId>';

              if($product['special']){
                $xml .= '<price>' . $product['special'] .  '</price>';
                $xml .= '<oldprice>' . $product['price'] .  '</oldprice>';
              }else{
                $xml .= '<price>' . $product['price'] .  '</price>';
              }

              if($product['image']){
                $xml .= '<picture>' . $product['image'] .  '</picture>';
              }

              if($product['images']){
                foreach($product['images'] as $image){
                  $xml .= '<picture>' . $image .  '</picture>';
                }
              }

              $xml .= '<vendor>' . $product['manufacturer'] .  '</vendor>';
              $xml .= '<description><![CDATA[' . $product['description'] .  ']]></description>';
              if(isset($product['youlaCategoryId'])){
                $xml .= '<youlaCategoryId>' . $product['youlaCategoryId'] .  '</youlaCategoryId>';
              }
              if(isset($product['youlaSubcategoryId'])){
                $xml .= '<youlaSubcategoryId>' . $product['youlaSubcategoryId'] .  '</youlaCategoryId>';
              }
              if(isset($product['address'])){
                $xml .= '<address>' . $product['address'] .  '</address>';
              }
              if(isset($product['phone'])){
                $xml .= '<phone>' . $product['phone'] .  '</phone>';
              }
              if(isset($product['managerName'])){
                $xml .= '<managerName>' . $product['managerName'] .  '</managerName>';
              }
              foreach($product['attributes_full'] as $attribute){
                $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
              }
              if($product['attributes']){
                foreach($product['attributes'] as $attribute){
                  $xml .= '<param name="' . $attribute['name'] . '">' . $attribute['text'] .  '</param>';
                }
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
