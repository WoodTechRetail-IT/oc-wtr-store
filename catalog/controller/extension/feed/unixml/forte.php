<?php
class ControllerExtensionFeedUnixmlForte extends Controller {
  public function index() {

    $feed = 'forte';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
      $xml .= '<fm_catalog date="' . date('Y-m-d H:i', time()) . '">'; //2017-02-05 17:22
      $xml .= '<shop>';
      $xml .= '<merchant-id>' . $startup['name'] . '</merchant-id>';
      $xml .= '<offers>';

      $xml = $this->unixml->exportToXml($startup, $xml, "start");
      //headerXML

      //generateXML
        for($startup['iteration'] = 0; 1; $startup['iteration']++){

          $controller_data = $this->load->controller($controller, $startup);
          $startup['stat'] = $controller_data['data']['stat'];

          if($controller_data['products']){

            foreach($controller_data['products'] as $product_id => $product){
              $xml .= '<offer sku="' . $product_id . '">';
              $xml .= '<name>' . $product['name'] .  '</name>';
              $xml .= '<vendor>' . $product['manufacturer'] .  '</vendor>';
              if(isset($product['model'])){
                $xml .= '<barcodes><barcode>' . $product['model'] .  '</barcode></barcodes>';
              }
              if($startup['delivery_cost']) {
                $xml .= '<delivery-options><delivery-option city-id="' . $startup['delivery_jump'] . '" cost="' . $startup['delivery_cost'] . '" days="' . ($startup['delivery_time']?$startup['delivery_time']:1) . '" /></delivery-options>';
              }
              if($product['special']){
                $xml .= '<price>' . $product['special'] .  '</price>';
                $xml .= '<oldprice>' . $product['price'] .  '</oldprice>';
              }else{
                $xml .= '<price>' . $product['price'] .  '</price>';
              }
              foreach($product['attributes_full'] as $attribute){
                $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
              }
              if(isset($product['pickup_options'])){
                $xml .= '<pickup-options>';
                foreach($product['pickup_options'] as $pickup_option){
                  $xml .= '<pickup-option id="' . $pickup_option . '" />';
                }
                $xml .= '</pickup-options>';
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
      $xml = '</offers>';
      $xml .= '</shop>';
      $xml .= '</fm_catalog>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
