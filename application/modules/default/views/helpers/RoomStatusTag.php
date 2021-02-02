<?php

class Zend_View_Helper_RoomStatusTag extends Zend_View_Helper_Abstract {

	function roomStatusTag($numer){
		switch ($numer) {
			case '1':
				return "dostepny";
			case '2':
				return "sprzedany";
			case '3':
				return "rezerwacja";
			case '4':
				return "wynajete";
            case '6':
				return "online";
		}
	}
}