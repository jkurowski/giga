<?php

class Zend_View_Helper_RoomWindow extends Zend_View_Helper_Abstract {

	function roomWindow($numer){
		switch ($numer) {
			case '1':
				return "północ";
			case '2':
				return "południe";
			case '3':
				return "wschód";
			case '4':
				return "zachód";
			case '5':
				return "północny-wschód";
			case '6':
				return "północny-zachód";
			case '7':
				return "południowy-wschód";
			case '8':
				return "południowy-zachód";
		}
	}
}