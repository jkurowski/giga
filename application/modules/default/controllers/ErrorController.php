<?php

class Default_ErrorController extends kCMS_Site
{

    public function errorAction()
    {
		$this->_helper->layout->setLayout('page');
		$this->getResponse()->setHttpResponseCode(404)->setRawHeader('HTTP/1.1 404 Not Found');
		$this->view->nofollow = 1;
		$this->view->strona_nazwa = 'Błąd 404';
		$this->view->strona_h1 = 'Błąd 404';
		$this->view->seo_tytul = 'Strona nie została znaleziona - błąd 404';
    }
	
	
	public function denyAction()
    {
		$this->_helper->layout->setLayout('page');
    }
}