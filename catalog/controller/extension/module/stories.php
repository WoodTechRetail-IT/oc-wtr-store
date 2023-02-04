<?php
/**
 * @package OpencartStories for Opencart 2.3
 * @version 1.1
 * @author smartcoder
 * @copyright https://smart-coder.ru
 */

class ControllerExtensionModuleStories extends Controller {
	public function index($stories) {
        if (isset($stories['status']) && (int)$stories['status'] === 1) {
            $this->load->language('extension/module/stories');

            //  ZUCK
            $this->document->addStyle('catalog/view/javascript/stories/zuck/zuck.min.css');
            $this->document->addStyle('catalog/view/javascript/stories/zuck/snapgram.css');
            $this->document->addScript('catalog/view/javascript/stories/zuck/zuck.min.js');
            $this->document->addScript('catalog/view/javascript/stories/zuck/script.js');

            $data['heading_title'] = $this->language->get('heading_title');

            $data['stories'] = $stories;

            $data['height'] = isset($stories['height']) ? $stories['height'] : 300;

            return $this->load->view('extension/module/stories', $data);
        }



	}
}