<?php

class Zend_View_Helper_GetInline extends Zend_View_Helper_Abstract {

	function getInline($array, $id, $element){
		foreach($array as $a){
			if($a->id == $id){
				$array = json_decode(json_encode($a), true);
				
				if($element == 'obrazek') {
					$front = Zend_Controller_Front::getInstance();
					$request = $front->getRequest();
					$baseUrl = $request->getBaseUrl();
					
					return $baseUrl.'/files/inline/'.$array[$element];
				} else {
					return $array[$element];
				}
			}
		}
	}
}