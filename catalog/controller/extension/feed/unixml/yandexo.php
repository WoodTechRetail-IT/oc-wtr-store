<?php
class ControllerExtensionFeedUnixmlYandexo extends Controller {
  public function index() {

    $feed = 'yandexo';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="utf-8"?>';
      $xml .= '<feed version="1">';
      $xml .= '<offers>';

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
              $xml .= '<offer>';
              $xml .= '<id>' . $product_id . '</id>';

              $xml .= '<seller>';
                  $xml .= '<contacts>';
                      $xml .= '<phone>' . $data['phone'] . '</phone>';
                      $xml .= '<contact-method>only-phone</contact-method>';
                  $xml .= '</contacts>';
                  $xml .= '<locations>';
                      $xml .= '<location>';
                          $xml .= '<address>' . $data['address'] . '</address>';
                      $xml .= '</location>';
                  $xml .= '</locations>';
              $xml .= '</seller>';

              $xml .= '<title>' . $product['name'] .  '</title>';
              $xml .= '<description><![CDATA[' . $product['description'] .  ']]></description>';
              $xml .= '<condition>new</condition>';
              if(isset($startup['categories_xml'][$product['category_id']]['name']) && $startup['categories_xml'][$product['category_id']]['name']){
                $xml .= '<category>' . $startup['categories_xml'][$product['category_id']]['name'] .  '</category>';
              }
              $xml .= '<price>' . ($product['special']?$product['special']:$product['price']) .  '</price>';
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
      $xml .= '</feed>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
