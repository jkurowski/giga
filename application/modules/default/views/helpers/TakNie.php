<?php

class Zend_View_Helper_TakNie extends Zend_View_Helper_Abstract {

	function takNie($numer){
		switch ($numer) {
			case '1':
				return "tak";
			case '0':
				return "nie";
		}
	}
}