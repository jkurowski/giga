<?php

class Zend_View_Helper_RoomStatus extends Zend_View_Helper_Abstract {

	public function roomStatus($numer){
		switch ($numer) {
			case '1':
				return "Dostępne";
			case '2':
				return "Sprzedane";
			case '3':
				return "Rezerwacja";
			case '4':
				return "Wynajęte";
            case '6':
				return "Rezerwacja On-line";
		}
	}
}