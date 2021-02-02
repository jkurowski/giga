<?php

class Zend_View_Helper_CssUrl extends Zend_View_Helper_Abstract {

    public function cssUrl() {
		$front = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();
		$baseUrl = $request->getBaseUrl();
			
        $url = $baseUrl.'/public/styles/template';
        return $url;
    }
}