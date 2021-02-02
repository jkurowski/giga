<?php

class Zend_View_Helper_JsUrl extends Zend_View_Helper_Abstract {

    public function jsUrl() {
		$front = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();
		$baseUrl = $request->getBaseUrl();
			
        $url = $baseUrl.'/public/js';
        return $url;
    }
}