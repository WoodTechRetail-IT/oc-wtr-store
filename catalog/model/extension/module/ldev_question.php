<?php

require_once(DIR_SYSTEM . 'library/ldevquestion.php');

 class ModelExtensionModuleLdevQuestion extends Model{

     public function __construct($registry)
     {
         parent::__construct($registry);
         $this->ldev_question = new LdevQuestion($registry);
         $this->version = $this->ldev_question->oc_version();

         if($this->version > 23){
             $this->setPref = 'module_';
         }else{
             $this->setPref = '';
         }


     }


     public function getQuestion($item_id, $relation = false){
         $sql = "SELECT qi.item_id, qi.selector_for_append, qi.container, qi.microdata, qi.view_type, qi.image_width, qi.image_height, qi.collapse_activate_1st, qi.hide_sublings, qi.marker, qi.heading_status, qi.display
            FROM ".DB_PREFIX."question_item qi
            LEFT JOIN ".DB_PREFIX."question_item_relations qir ON (qi.item_id = qir.item_id)
             WHERE qi.item_id=".(int)$item_id." AND qi.status = 1";
         if($relation){
             $sql .= " AND (";
             if(!is_array($relation)) {


                 if (isset(explode('product_id=', $relation)[1])) {
                     $sql .= " qi.all_product ='1' OR qir.query='" . $this->db->escape($relation) . "'";
                 } else {
                     $sql .= " qir.query ='" . $this->db->escape($relation) . "'";
                 }
             }else{
                 $sql .= "qi.all_product ='1' || ";
                 $i=0;
                 foreach ($relation as $query){
                     $sql .= "qir.query = '".$this->db->escape($query)."'";
                     if($i < count($relation)-1) { $sql .=' || ' ; }
                     $i++;
                 }


             }

             $sql .= ')';

         }

         $query = $this->db->query($sql);
         $data = [];
         if($query->num_rows){
             $question_info = $this->getQuestionItemDescriptions(
                 $query->row['item_id'],
                 $query->row['image_width'],
                 $query->row['image_height']
             );
             $data = [
                 'name' => $question_info['name'],
                 'about' => $question_info['about'],
                 'list' => $question_info['list'],
             ];

             foreach(array_keys($query->row) as $key){
                 if($query->row[$key]== 'none') continue;
                 if($key == 'display'){
                     $points = explode(',', $query->row[$key]);
                     $display = '';
                     foreach ($points as $point){
                         $display .= ' visible-'.$point;
                     }
                     $data['display'] = $display;
                 }else{
                     $data[$key] = $query->row[$key];
                 }
             }

         }

         return $data;
     }

     public function getQuestions($data = []){
         $sql = "SELECT DISTINCT qi.item_id FROM ".DB_PREFIX."question_item qi
            LEFT JOIN ".DB_PREFIX."question_item_description qid ON(qi.item_id = qid.item_id)
            LEFT JOIN ".DB_PREFIX."question_item_relations qir ON(qi.item_id = qir.item_id)";
         if($this->customer->isLogged()) {
             $sql .=  "LEFT JOIN " . DB_PREFIX . "question_item_to_customer_group qic ON(qi.item_id = qic.item_id)";
         }

         $sql .= "WHERE qid.language_id=".(int)$this->config->get('config_language_id')." AND qi.status = '1'";

         if (!empty($data['ldev_question_block_id'])) {
             $sql .= " AND qi.item_id = ".(int)$data['ldev_question_block_id'];
         }

         if(!empty($data['query'])){
             if(!is_array($data['query'])) {
                 if (isset(explode('product_id=', $data['query'])[1])) {
                     $sql .= " AND (qi.all_product ='1' OR qir.query='" . $this->db->escape($data['query']) . "')";
                 } else {
                     $sql .= " AND qir.query ='" . $this->db->escape($data['query']) . "'";
                 }
             }else{
                 $sql .= " AND qi.all_product ='1' || (";
                 $i=0;
                 foreach ($data['query'] as $query){
                     $sql .= "qir.query = '".$this->db->escape($query)."'";
                     if($i < count($data['query'])-1) { $sql .=' || ' ; }
                     $i++;
                 }

                 $sql .= ")";
             }
         }

         if (!empty($data['filter_name'])) {
             $sql .= " AND qid.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
         }

         if($this->customer->isLogged()){
             $sql .= " AND qi.logged = '1' AND qic.customer_group_id = ".(int)$this->config->get('config_customer_group_id');
         }else{
             $sql .= " AND qi.guest = '1'";
         }


         $sort_data = array(
             'qid.name',
             'qi.sort_order'
         );

         if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
             $sql .= " ORDER BY " . $data['sort'];
         } else {
             $sql .= " ORDER BY qid.name";
         }

         if (isset($data['order']) && ($data['order'] == 'DESC')) {
             $sql .= " DESC";
         } else {
             $sql .= " ASC";
         }

         if (isset($data['start']) || isset($data['limit'])) {
             if ($data['start'] < 0) {
                 $data['start'] = 0;
             }

             if ($data['limit'] < 1) {
                 $data['limit'] = 20;
             }

             $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
         }
         $query = $this->db->query($sql);
         $questions_data = [];
         foreach ($query->rows as $row){
            $questions_data[] = $this->getQuestion($row['item_id']);
         }

         return $questions_data;
     }

     public function getQuestionItemDescriptions($item_id, $image_w = 0, $image_h = 0) {
         $data = array(
             'name'            => '',
             'about'            => '',
             'list'      => [],
         );

         $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "question_item_description WHERE item_id = '" . (int)$item_id . "' AND language_id=".(int)$this->config->get('config_language_id')." ");

         foreach ($query->rows as $result) {

             if($image_w != 'none' && $image_h != 'none'){
                 $questions_data = $this->decodeQuestionDescription($result, $image_w, $image_h);
             }else{
                 $questions_data = $this->decodeQuestionDescription($result);
             }

             $data = array(
                 'name'            => $result['name'],
                 'about'            => $result['about'],
                 'list'      => $questions_data,
             );
         }

         return $data;
     }

     private function decodeQuestionDescription($result, $image_custom_w = 0,$image_custom_h = 0){
         $text = $result['text'];

         $this->load->model('tool/image');
         if($image_custom_w && $image_custom_h){
             $image_w = $image_custom_w;
             $image_h = $image_custom_h;
         }else{
             $image_w = $this->config->has($this->setPref.'ldev_question_image_width') ?  $this->config->get($this->setPref.'ldev_question_image_width') : 200;
             $image_h = $this->config->has($this->setPref.'ldev_question_image_height') ? $this->config->get($this->setPref.'ldev_question_image_height') : 200;
         }

         $questions_data = [];
         $questions = explode('[||]',$text);
         foreach ($questions as $question){
             if(!$text) break;
             if(!$question){continue;}
             $image_link = isset(explode('[~~]',$question)[2]) ? explode('[~~]',$question)[2] : '';
             $image = [
                 'thumb' => $image_link ? $this->model_tool_image->resize($image_link, $image_w,$image_h): '',
                 'link' => $image_link,
             ];

             $questions_data[] = [
                 'title' => isset(explode('[~~]',$question)[0]) ? explode('[~~]',$question)[0] : '',
                 'text' => isset(explode('[~~]',$question)[1]) ? explode('[~~]',$question)[1] : '',
                 'module' => isset(explode('[~~]',$question)[3]) ? explode('[~~]',$question)[3] : '',
                 'link' => isset(explode('[~~]',$question)[4]) ? explode('[~~]',$question)[4] : '',
                 'marker' => isset(explode('[~~]',$question)[5]) ? explode('[~~]',$question)[5] : '',
                 'image' => $image
             ];
         }
         return $questions_data;
     }

     public function getPriceByCategory($category_id, $func = 'MIN')
     {



         $sql = "SELECT 
                ".strtoupper($func)."(
                CASE WHEN ps.price IS NOT NULL AND (ps.date_start < NOW() OR ps.date_start = '0000-00-00') AND (ps.date_end > NOW() OR ps.date_end = '0000-00-00')  
                THEN ps.price 
                ELSE p.price
                END
                )
                 as price
                FROM ".DB_PREFIX."product p 
                LEFT JOIN ".DB_PREFIX."product_special ps ON (p.product_id = ps.product_id)
                LEFT JOIN ".DB_PREFIX."product_to_category p2c ON (p.product_id = p2c.product_id) 

                WHERE p2c.category_id = ".(int)$category_id." AND p.status = '1' AND p.date_available <= NOW()";


         $query = $this->db->query($sql);
         return $query->row['price'];
     }

     public function getPriceByManufacturer($manufacturer_id, $func = 'MIN')
     {



         $sql = "SELECT 
                ".strtoupper($func)."(
                CASE WHEN ps.price IS NOT NULL AND (ps.date_start < NOW() OR ps.date_start = '0000-00-00') AND (ps.date_end > NOW() OR ps.date_end = '0000-00-00')  
                THEN ps.price 
                ELSE p.price
                END
                )
                 as price
                FROM ".DB_PREFIX."product p 
                LEFT JOIN ".DB_PREFIX."product_special ps ON (p.product_id = ps.product_id)

                WHERE p.manufacturer_id = ".(int)$manufacturer_id." AND p.status = '1' AND p.date_available <= NOW()";

         $query = $this->db->query($sql);
         return $query->row['price'];
     }
 }