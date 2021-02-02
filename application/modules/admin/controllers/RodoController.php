<?php

class Admin_RodoController extends kCMS_Admin
{
		public function preDispatch() {
			$this->view->controlname = "Rodo";
		}
		
// Pokaz wszystkich klientow
		public function indexAction() {
			$db = Zend_Registry::get('db');
			$this->view->pagename = " - Lista klientów";
			
			$this->view->lista = $db->fetchAll($db->select()->from('rodo_klient')->order('id DESC'));
		}
		
// Ustawienia
		public function ustawieniaAction() {
			$db = Zend_Registry::get('db');
			$this->view->pagename = " - Główne ustawienia";
			
			$this->view->rodo = $db->fetchRow($db->select()->from('rodo_ustawienia')->where('id =?', 1));

			//Ustawienia główne strony
			if ($this->_request->getPost('submitUstawienia', false)) {
				$obowiazek = $this->_request->getPost('obowiazek');

				$data = array(
					'obowiazek' => $obowiazek,
				);
				$db->update('rodo_ustawienia', $data, 'id = 1');
				$this->_redirect('/admin/rodo/ustawienia/');
			}
		}
		
// Regułki klienta RODO
		public function pokazAction() {
			$db = Zend_Registry::get('db');

			$id = (int)$this->_request->getParam('id');
			$this->view->user = $db->fetchRow($db->select()->from('rodo_klient')->where('id =?', $id));

			$select = $db->select()
			->from(array('rgk' => 'rodo_regulki'), array('idregulka' => 'id', 'nazwa', 'statusregulka' => 'status'))
			->joinLeft(array('rg' => 'rodo_regulki_klient'), 'rg.id_regulka = rgk.id', array('idregulkaklient' => 'id', 'data_podpisania', 'statusklient' => 'status', 'termin', 'ip', 'id_klient'))
			->where('rg.id_klient = ?', $id)
			->where('rgk.status = ?', 1);
			$this->view->lista = $db->fetchAll($select);
			
			$this->view->archiwum = $db->fetchAll($db->select()->from('rodo_regulki_archiwum')->where('id_klient = ?', $id)->order('id DESC'));

		}
		
// Logi RODO
		public function logsAction() {
            $log = file_get_contents(BASE_PATH.'/rodo.log', FILE_USE_INCLUDE_PATH, null);
            $lines = explode("\n", $log);
            krsort($lines);
            $this->view->logcontent = implode("\n", array_slice($lines, 1));
		}

// Regułki RODO
		public function zgodyAction() {
			$db = Zend_Registry::get('db');
			$this->view->pagename = " - Lista regułek";
			$this->view->lista = $db->fetchAll($db->select()->from('rodo_regulki')->order('sort ASC'));
		}

// Dodaj regulke
		public function nowaRegulkaAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->pagename = " - Nowa regulka";

			$this->view->back = '<div class="back"><a href="/admin/rodo/zgody/">Wróć do listy</a></div>';

			$form = new Form_RegulkaForm();
			$this->view->form = $form;

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					$formData = $this->_request->getPost();
					unset($formData['submit']);
					$formData['data_utworzenia'] = strtotime(date("Y-m-d H:i:s"));

					//Sprawdzenie poprawnosci forma
					if ($form->isValid($formData)) {
			
						$db->insert('rodo_regulki', $formData);
						$this->_redirect('/admin/rodo/zgody/');

				} else {

					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}
		
// Edytuj regulke
		public function edytujRegulkaAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			
			$this->view->back = '<div class="back"><a href="/admin/rodo/zgody/">Wróć do listy</a></div>';
			
			$form = new Form_RegulkaForm();
			$this->view->form = $form;

			// Odczytanie id
			$id = (int)$this->getRequest()->getParam('id');
			$wpis = $db->fetchRow($db->select()->from('rodo_regulki')->where('id = ?', $id));
			$this->view->pagename = " - Edytuj: ".$wpis->nazwa;

			// Załadowanie do forma
			$array = json_decode(json_encode($wpis), true);
			$form->populate($array);

			//Akcja po wcisnieciu Submita
			if ($this->_request->getPost()) {

				$formData = $this->_request->getPost();
				unset($formData['submit']);
				$formData['data_edycji'] = strtotime(date("Y-m-d H:i:s"));

				//Sprawdzenie poprawnosci forma
				if ($form->isValid($formData)) {
				
					$db->update('rodo_regulki', $formData, 'id = '.$id);
					$this->_redirect('/admin/rodo/zgody/');
					
				} else {
									
					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}

			}
		}

// Usun regulke
		public function usunRegulkaAction() {
			$db = Zend_Registry::get('db');
			$id = (int)$this->_request->getParam('id');
			$db->delete('rodo_regulki', 'id = '.$id);
			$this->_redirect('/admin/rodo/zgody/');
		}
		
// Ustaw kolejność
		public function ustawAction() {
			$db = Zend_Registry::get('db');
			$updateRecordsArray = $_POST['recordsArray'];
			$listingCounter = 1;
			foreach ($updateRecordsArray as $recordIDValue) {
				$data = array('sort' => $listingCounter);
				$db->update('rodo_regulki', $data, 'id = '.$recordIDValue);
				$listingCounter = $listingCounter + 1;
				}
		}
}