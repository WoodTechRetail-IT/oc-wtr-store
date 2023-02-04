<?php

/************************copyright***************************/
/*                                                          */
/* telegram: https://t.me/PrutNikolay                       */
/* forum: https://opencartforum.com/profile/18336-exploits/ */
/* email: info@microdata.pro                                */
/* site: https://unixml.pro                                 */
/*                                                          */
/************************copyright***************************/

require_once(DIR_SYSTEM . 'library/unixml.php');
class ControllerExtensionFeedUnixml extends Controller {

  public function __construct($registry) {
		parent::__construct($registry);
		$this->unixml = new UniXML($this->registry);
	}

  //Функции экспорта

    //index - функция заглушка кто перейдет по ссылке */unixml
      public function index() {
        $html = '<div style="' . $this->unixml->style . '">';
          $html .= '<h3>Модуль UniXML v' . $this->unixml->ver . '</h3>';
          $html .= '<p>Модуль выгрузки в маркетплейсы</p>';
          $html .= '<div>support: info@microdata.pro</div>';
        $html .= '</div>';

        $this->response->setOutput($html);
      }
    //index

    //startup - функция инициализации экспорта */unixml/export_link
      public function startup($data = array()) {

        if(!isset($data['status'])){ //первый запуск 7 строка фида ($startup = $this->load->controller($controller, array('feed'=>$feed));)
          $data = array_merge($this->unixml->exportGetSetting(false, $data['feed']), $data); //забираем все настроки фида и объединяем с входными данными
        }

        $this->startupChecken($data); //проверка запускa

        if ($data['status']) { //если выгрузка включена
          if(isset($data['iteration'])) { //если итерация - отдаем товары
            $products = $this->unixml->exportGetProducts($data);
            return array('products' => $products, 'data' => $data);
          } else { //если инициализация - забираем данные для старта
            $this->unixml->exportGetCategories($data); //берем категории если выбрали (добавляется $data['categories_xml'])
            $this->unixml->exportGetCurrencyCode($data); //берем код валюты (добавляется $data['currency_xml'])
            $this->unixml->exportGetCurrencyValue($data); //берем значение валюты (заменяется $data['currency'])
            $this->unixml->exportGetProductFields($data); //берем все поля товара те что есть в базе (добавляется $data['all_access_column'])
            $this->unixml->exportGetAdditionalParams($data); //берем дополнительные статические параметры (добавляется  $data['additional_params'])
            $this->unixml->exportGetSqlFields($data); //берем все поля для выборки (добавляется $data['selected_vars'])
            $this->unixml->exportGetProductMarkup($data); //берем наценки на группы товаров (добавляется $data['product_markup'])
            $this->unixml->exportGetReplaceList($data); //берем списки замен (добавляется $data['replace_list'])

            return $data;
          }
        } else { //если выгрузка выключена - выводим уведомление
          echo "<div style='" . $this->unixml->style . "'><h2>Выгрузка " . $data['feed'] . " выключена в настройках модуля UniXML v" . $this->unixml->ver . "</h2></div>"; exit();
        }
      }
    //startup

    //startupChecken - функция проверки запуска
      private function startupChecken(&$data){
        if (!$data['feed']) { header("Location: /",1,302); } //если запускаем ссылку unixml/startup

        if (!isset($data['iteration'])) { //если инициализация
          set_time_limit(12000);
          ini_set('max_execution_time', 12000);

          //запуск статистики
          $data['stat']['products'] = 0;
          $data['stat']['iteration'] = 0;
          $data['stat']['time_start'] = microtime(true);
          $data['stat']['memory_start'] = round(((memory_get_usage())/1024/1024), 3);
          $data['stat']['memory'] = $data['stat']['memory_start'];
          $data['stat']['sql'] = 1;
          //запуск статистики
        }

        if($data['secret']){ //если есть защита по get параметру
          if(!isset($this->request->get['key'])) { $this->request->get['key'] = ''; }
          if($this->request->get['key'] != $data['secret']){
            header("Location: /",1,302); exit();
          }
        }
      }
    //startupChecken

  //Функции экспорта

  //Функции импорта

    //import - функция запуска импорта
      public function import(){
        if(isset($this->request->get['import_id']) && isset($this->request->get['key'])){
          if($this->request->get['key'] == base64_encode(($this->request->get['import_id']*2) . 'key')){
            $this->unixml->importStart(true);
          }
        }else{
          header("Location: /",1,302); exit();
        }
      }
    //import

  //Функции импорта

}
