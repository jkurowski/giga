<?php

class Admin_BlokowanieController extends kCMS_Admin
{
		public function preDispatch() {
			$this->view->controlname = "Zablokowane adresy IP";
		}
		
// Pokaz wszystkie panele
		public function indexAction() {
			$db = Zend_Registry::get('db');
			$this->view->lista = $db->fetchAll($db->select()->from('blokowanie')->order('data ASC'));
		}
// Dodaj nowy panel
		function nowyAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->pagename = " - Dodaj adres";
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/blokowanie/">Wróć do listy</a></div>';

			$form = new Form_IpForm();
			$this->view->form = $form;

			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					//Odczytanie wartosci z inputów $tytul, $subtytul, $link, $obrazek
					$ip = $this->_request->getPost('ip');
					$datadodania = date("d-m-Y H:s");
					$formData = $this->_request->getPost();

					//Sprawdzenie poprawnosci forma
					if ($form->isValid($formData)) {

						$data = array(
							'ip' => $ip,
							'data' => $datadodania
							
						);
						
						$db->insert('blokowanie', $data);
						$this->_redirect('/admin/blokowanie/');
				} else {
						
					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}
// Edytuj panel
		function edytujAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);

			// Odczytanie id
			$id = (int)$this->_request->getParam('id');
			$boksy = $db->fetchRow($db->select()->from('blokowanie')->where('id = ?', $id));
			
			$this->view->pagename = " - Edytuj wpis: ".$boksy->ip;
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/blokowanie/">Wróć do listy</a></div>';
			
			$form = new Form_IpForm();
			$this->view->form = $form;

			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);

			// Załadowanie do forma $nazwa, $link, $obrazek
			$form->ip->setvalue($boksy->ip);

			if ($this->_request->isPost()) {

				//Odczytanie wartosci z inputów $nazwa, $link, $obrazek
				$ip = $this->_request->getPost('ip');
				$datadodania = date("d-m-Y H:s");
				$formData = $this->_request->getPost();


				//Sprawdzenie poprawnosci forma
				if ($form->isValid($formData)) {
				
					$data = array(
						'ip' => $ip,
						'data' => $datadodania
						
					);
					
					$db->update('blokowanie', $data, 'id = '.$id);
					$this->_redirect('/admin/blokowanie/');
					
				} else {

					//Wyswietl bledy    
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}

// Usuń panel
		function usunAction() {
			$db = Zend_Registry::get('db');
			$id = (int)$this->_request->getParam('id');
			$where = $db->quoteInto('id = ?', $id);
			$db->delete('blokowanie', $where);
			$this->_redirect('/admin/blokowanie/');
		}

}