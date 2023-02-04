<?php
class ControllerExtensionFeedUnixmlFotos extends Controller {
  public function index() {

    $feed = 'fotos';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
      $xml .= '<price>';
      $xml .= '<currencies>';
      $xml .= '<currency id="' . $startup['currency'] . '" rate="1"/>';
      $xml .= '</currencies>';

      if($startup['categories_xml']) {
        $xml .= '<categories>';
        foreach($startup['categories_xml'] as $category) {
          if($category['parent_id']){
            $xml .= '<category id="' . $category['category_id'] .'" parentID="' . $category['parent_id'] . '">' . $category['name'] .'</category>';
          } else{
            $xml .= '<category id="' . $category['category_id'] .'">' . $category['name'] .'</category>';
          }
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
              $xml .= '<name>' . $product['name'] .  '</name>';
              $xml .= '<art>' . $product_id .  '</art>';
              $xml .= '<categoryId>' . $product['category_id'] .  '</categoryId>';
              if($product['special']){
                $xml .= '<price>' . $product['special'] .  '</price>';
                $xml .= '<old>' . $product['price'] .  '</old>';
              }else{
                $xml .= '<price>' . $product['price'] .  '</price>';
              }
              $xml .= '<priceCurrency>' . $startup['currency'] . '</priceCurrency>';
              $xml .= '<amount>' . $product['quantity'] .  '</amount>';
              $xml .= '<image>' . $product['image'] .  '</image>';
              if($product['images']){
                foreach($product['images'] as $image){
                  $xml .= '<extraimage>' . $image .  '</extraimage>';
                }
              }
              $xml .= '<vendor>' . $product['manufacturer'] .  '</vendor>';
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
