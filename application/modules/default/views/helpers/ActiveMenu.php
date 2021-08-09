<?php

class Zend_View_Helper_ActiveMenu extends Zend_View_Helper_Abstract {

    public function activeMenu($item, $slug) {
        if($item == $slug){
            return ' active';
        }
    }
}