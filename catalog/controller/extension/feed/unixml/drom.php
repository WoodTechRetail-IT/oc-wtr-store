<?php
class ControllerExtensionFeedUnixmlRozetka extends Controller {
  public function index() {

    $feed = 'rozetka';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
      $xml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">';
      $xml .= '<yml_catalog date="' . date('Y-m-d H:i', time()) . '">';
      $xml .= '<shop>';
      $xml .= '<url>' . HTTPS_SERVER . '</url>';
      $xml .= '<name>' . $this->config->get('config_name') . '</name>';
      $xml .= '<company>' . $startup['name'] . '</company>';
      $xml .= '<phone>' . $this->config->get('config_telephone') . '</phone>';
      $xml .= '<currencies>';
      $xml .= '<currency id="' . $startup['currency'] . '" rate="1"/>';
      $xml .= '</currencies>';

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
              $xml .= '<offer id="' . $product_id . '" available="' . ($product['stock']?'true':'false') .'">';
              $xml .= '<name>' . $product['name'] .  '</name>';
              $xml .= '<url>' . $product['url'] .  '</url>';
              $xml .= '<currencyId>' . $startup['currency'] . '</currencyId>';
              $xml .= '<categoryId>' . $product['category_id'] .  '</categoryId>';
              if($product['special']){
                $xml .= '<price>' . $product['special'] .  '</price>';
                $xml .= '<oldprice>' . $product['price'] .  '</oldprice>';
              }else{
                $xml .= '<price>' . $product['price'] .  '</price>';
              }
              $xml .= '<picture>' . $product['image'] .  '</picture>';
              if($product['images']){
                foreach($product['images'] as $image){
                  $xml .= '<picture>' . $image .  '</picture>';
                }
              }

              if(isset($product['cars'])){
                $xml .= '<compatibility>';
                foreach($product['cars'] as $car){
                  $xml .= '<car>' . $car . '</car>';
                }
                $xml .= '</compatibility>';
              }

              $xml .= '<vendor>' . $product['manufacturer'] .  '</vendor>';
              $xml .= '<description><![CDATA[' . $product['description'] .  ']]></description>';
              if($startup['delivery_cost']) {
                $xml .= '<delivery-options><option cost="' . $startup['delivery_cost'] . '" days="' . ($startup['delivery_time']?$startup['delivery_time']:1) . '" order-before="' . ($startup['delivery_jump']?$startup['delivery_jump']:18) . '"/></delivery-options>';
              }
              foreach($product['attributes_full'] as $attribute){
                $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
              }
              foreach($product['attributes'] as $attribute){
                $xml .= '<param name="' . $attribute['name'] . '">' . $attribute['text'] .  '</param>';
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
