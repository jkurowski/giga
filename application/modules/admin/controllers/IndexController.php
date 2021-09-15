<?php

class Admin_IndexController extends kCMS_Admin
{
		
		public function preDispatch() {
			$this->redirect('/admin/ustawienia/');
	
		}
}