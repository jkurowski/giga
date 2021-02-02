<?php

class Default_KontaktController extends kCMS_Site
{
    /**
     * @var string
     */
    private $page_class;
    /**
     * @var int
     */
    private $validation;
    private $page_id;

    public function preDispatch() {
			$this->page_id = 1;
			$this->page_class = ' contact-page';
			$this->validation = 1;
		}

	
	   public function indexAction() {
            $this->_helper->layout->setLayout('page');
			$db = Zend_Registry::get('db');
			$db->setFetchMode(Zend_Db::FETCH_OBJ);
			
			$page = $this->view->strona = $db->fetchRow($db->select()->from('strony')->where('id = ?', $this->page_id));

			if(!$page) {
                $request = Zend_Controller_Front::getInstance()->getRequest();
				$request->setModuleName('default');
				$request->setControllerName('error');
				$request->setActionName('error');
				$this->getResponse()->setHttpResponseCode(404)->setRawHeader('HTTP/1.1 404 Not Found');
				$this->view->nofollow = 1;
				$this->view->strona_nazwa = 'Błąd 404';
				$this->view->seo_tytul = 'Strona nie została znaleziona - błąd 404';
			} else {

				$this->view->strona_nazwa = $page->nazwa;
				$this->view->strona_h1 = $page->nazwa;
				$this->view->strona_tytul = ' - '.$page->nazwa;
				$this->view->seo_tytul = $page->meta_tytul;
				$this->view->seo_opis = $page->meta_opis;
				$this->view->seo_slowa = $page->meta_slowa;
				
				$this->view->strona_id = $this->page_id;
				$this->view->validation = $this->validation;
				$this->view->pageclass = $this->page_class;
				$this->view->nobottom = 1;

				if ($this->_request->isPost()) {

					$ip = $_SERVER['REMOTE_ADDR'];
					$adresip = $db->fetchRow($db->select()->from('blokowanie')->where('ip = ?', $ip));
					
					$grecaptcha = $this->_request->getPost('g-recaptcha-response');
					// unset($formData['g-recaptcha-response']);
					// if(getRecaptchaCheck($grecaptcha) === true){
						if($adresip){  } else {
							
							$formData = $this->_request->getPost();
							if($formData['imie'] && $formData['email'] && $formData['telefon']) {
								
								$imie = $this->_request->getPost('imie');
								$email = $this->_request->getPost('email');
								$telefon = $this->_request->getPost('telefon');
								$wiadomosc = $this->_request->getPost('wiadomosc');
								$useremail = $this->_request->getPost('useremail');
								$ip = $_SERVER['REMOTE_ADDR'];
								$datadodania = date("d.m.Y - H:i:s");

								$ustawienia = $db->fetchRow($db->select()->from('ustawienia'));

								if(!$useremail) {
									$mail = new Zend_Mail('UTF-8');
									$mail
									->setFrom($ustawienia->email, 'Zapytanie ze strony www')
									->addTo($ustawienia->email, 'Adres odbiorcy')
									->setReplyTo($email, $imie)
									->setSubject($ustawienia->domena.' - Zapytanie ze strony www - Kontakt')
									->setBodyHTML('
									<div style="width:550px;border:1px solid #ececec;padding:0 20px;margin:0 auto;font-family:Arial;font-size:14px;line-height:27px">
									<p style="text-align:center">'.$ustawienia->nazwa.'</p>
									<p><b>Wiadomość wysłana: '. $datadodania .'</b></p>
									<hr style="border:0;border-bottom:1px solid #ececec" />
									<p><b>Imię i nazwisko:</b> '.$imie.'<br />
									<b>E-mail:</b> '. $email .'<br />
									<b>Telefon:</b> '. $telefon .'<br />
									<b>IP:</b> '. $ip .'<br /></p>
									<hr style="border:0;border-bottom:1px solid #ececec" />
									<p style="margin-top:0">'. $wiadomosc .'</p>
									</div>')
									->setBodyText($ustawienia->nazwa.'
									Wiadomość wysłana: '. $datadodania .'
									Imię i nazwisko: '.$imie.'
									E-mail: '. $email .'
									Telefon: '. $telefon .'
									IP: '. $ip .'

									'. $wiadomosc);
									
									try {
										$mail->send();
									} catch (Zend_Exception $e) {
										echo $e->getMessage();
										exit;
									} 
								}

								$stat = array(
									'akcja' => 1,
									'miejsce' => 4,
									'data' => date("d.m.Y - H:i:s"),
									'timestamp' => date("d-m-Y"),
									'godz' => date("H"),
									'dzien' => date("d"),
									'msc' => date("m"),
									'rok' => date("Y"),
									'tekst' => $wiadomosc,
									'email' => $email,
									'telefon' => $telefon,
									'ip' => $_SERVER['REMOTE_ADDR']
								);
								$db->insert('statystyki', $stat);
						
								$formData = $this->_request->getPost();
								$checkbox = preg_grep("/zgoda_([0-9])/i", array_keys($formData));
								$przegladarka = $_SERVER['HTTP_USER_AGENT'];
								historylog($imie, $email, $ip, $przegladarka, $checkbox);

								$this->view->message = 1;
								
							} else {
								$this->view->message = 2; 
							}
						}
					// }
				}
			}
	   }
}