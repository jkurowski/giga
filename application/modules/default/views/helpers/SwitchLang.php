<?php

class Zend_View_Helper_SwitchLang extends Zend_View_Helper_Abstract {

    public function switchLang($lang) {
        $frontController = Zend_Controller_Front::getInstance();
        $router = $frontController->getRouter();
        $request = $frontController->getRequest();
        $name = $router->getCurrentRouteName();
        $params = $request->getParams();
        $reset = TRUE;

        if ($name != 'default') {
            if (isset($params['language'])) {
                $params['language'] = $lang;
            }
        }

        return $this->view->url($params, $name, $reset);
    }

}