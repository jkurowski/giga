<?php

class Zend_View_Helper_GfxUrl extends Zend_View_Helper_Abstract {

    public function gfxUrl() {
		$front = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();
		$baseUrl = $request->getBaseUrl();
			
        $url = $baseUrl.'/public/gfx/template';
        return $url;
    }
}