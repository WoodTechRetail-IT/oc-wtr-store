<?php

class ModelExtensionTotalFilterit extends Model {
    public function getTotal($total) {
        if (method_exists($this->load, 'library') || get_class($this->load) == 'agooLoader') {
            $this->load->library('simple/filterit');
        } 

        $filterit = new Simple\Filterit($this->registry);
        
        $address = !empty($this->session->data['shipping_address']) ? $this->session->data['shipping_address'] : array();

        $filterit->getTotals($total, $address);
    }
}
