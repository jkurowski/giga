<?php

class Zend_View_Helper_FilesUrl extends Zend_View_Helper_Abstract {

    public function filesUrl() {
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
        $baseUrl = $request->getBaseUrl();

        return $baseUrl.'/files';
    }
}