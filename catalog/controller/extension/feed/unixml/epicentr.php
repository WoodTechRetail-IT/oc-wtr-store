<?php
class ControllerExtensionFeedUnixmlEpicentr extends Controller {
  public function index() {

    $feed = 'epicentr';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
      $xml .= '<yml_catalog date="' . date('Y-m-d H:i', time()) . '">';
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
              if($product['special']){
                $xml .= '<price>' . $product['special'] . '</price>';
                $xml .= '<price_old>' . $product['price'] . '</price_old>';
              }else{
                $xml .= '<price>' . $product['price'] . '</price>';
              }

              if(isset($startup['categories_xml'][$product['category_id']]['name']) && $startup['categories_xml'][$product['category_id']]['name']){
                $xml .= '<category>' . $startup['categories_xml'][$product['category_id']]['name'] .  '</category>';
              }

              $xml .= '<picture>' . $product['image'] .  '</picture>';
              if($product['images']){
                foreach($product['images'] as $image){
                  $xml .= '<picture>' . $image .  '</picture>';
                }
              }
              $xml .= '<vendor>' . $product['manufacturer'] .  '</vendor>';
              $xml .= '<name lang="ua">' . $product['name'] .  '</name>';
              if(isset($product['langdata'])){
                foreach($product['langdata'] as $langdata){
                  $xml .= '<name lang="ru">' . $langdata['name'] .  '</name>';
                }
              }
              $xml .= '<description lang="ua"><![CDATA[' . $product['description'] .  ']]></description>';
              if(isset($product['langdata'])){
                foreach($product['langdata'] as $langdata){
                  $xml .= '<description lang="ru"><![CDATA[' . $langdata['description'] .  ']]></description>';
                }
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
      $xml .= '</yml_catalog>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
