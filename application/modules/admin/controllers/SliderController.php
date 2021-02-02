<?php

class Admin_SliderController extends kCMS_Admin
{
		public function preDispatch() {
			$this->view->controlname = "Slider";
			$this->sliderszerokosc = 1920;
			$this->sliderwysokosc = 1080;
		}
		
// Pokaz wszystkie panele
		public function indexAction() {
			$db = Zend_Registry::get('db');
			$this->view->lista = $db->fetchAll($db->select()->from('slider')->order('sort ASC'));
		}
// Dodaj nowy panel
		function nowyAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->pagename = " - Dodaj panel";
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/slider/">Wróć do listy paneli</a></div>';
			$this->view->info = '<div class="info">Obrazek o wymiarach: szerokość <b>'.$this->sliderszerokosc.'</b>px / wysokość <b>'.$this->sliderwysokosc.'</b>px</div>';

			$form = new Form_SliderForm();
			$this->view->form = $form;

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					//Odczytanie wartosci z inputów

					$formData = $this->_request->getPost();
					unset($formData['MAX_FILE_SIZE']);
					unset($formData['obrazek']);
					unset($formData['submit']);
					$obrazek = $_FILES['obrazek']['name'];
					$plik = zmiana($formData['tytul']).'.'.zmiennazwe($obrazek);
					
					//Sprawdzenie poprawnosci forma
					if ($form->isValid($formData)) {

						$db->insert('slider', $formData);
						$lastId = $db->lastInsertId();
					
						if ($obrazek) {
							move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/slider/'.$plik);
							$pc = FILES_PATH.'/slider/'.$plik;
							$thumbs = FILES_PATH.'/slider/thumbs/'.$plik;
							chmod($pc, 0755);
							require_once 'kCMS/Thumbs/ThumbLib.inc.php';
							
							$options = array('jpegQuality' => 90);
							$thumb = PhpThumbFactory::create($pc, $options)->resize(159, 159)->save($thumbs);
							$thumb = PhpThumbFactory::create($pc, $options)->adaptiveResizeQuadrant($this->sliderszerokosc, $this->sliderwysokosc)->save($pc);
							chmod($thumbs, 0755);
							$dataImg = array('plik' => $plik);
							$db->update('slider', $dataImg, 'id = ' . $lastId);
						}

					$this->_redirect('/admin/slider/');
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
			$slider = $db->fetchRow($db->select()->from('slider')->where('id = ?', $id));
			
			$this->view->pagename = " - Edytuj panel: ".$slider->tytul;
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/slider/">Wróć do listy paneli</a></div>';
			$this->view->info = '<div class="info">Obrazek o wymiarach: szerokość <b>'.$this->sliderszerokosc.'</b>px / wysokość <b>'.$this->sliderwysokosc.'</b>px</div>';
			
			$form = new Form_SliderForm();
			$this->view->form = $form;

			// Załadowanie do forma
			$array = json_decode(json_encode($slider), true);
			$form->populate($array);

			if ($this->_request->isPost()) {

				//Odczytanie wartosci z inputów
				$formData = $this->_request->getPost();
				unset($formData['MAX_FILE_SIZE']);
				unset($formData['obrazek']);
				unset($formData['submit']);
				$obrazek = $_FILES['obrazek']['name'];
				$plik = zmiana($formData['tytul']).'.'.zmiennazwe($obrazek);
				
				//Sprawdzenie poprawnosci forma
				if ($form->isValid($formData)) {

					$db->update('slider', $formData, 'id = '.$id);

					if ($obrazek) {
							unlink(FILES_PATH."/slider/".$slider->plik);
							unlink(FILES_PATH."/slider/thumbs/".$slider->plik);
							
							move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/slider/'.$plik);
							$pc = FILES_PATH.'/slider/'.$plik;
							$thumbs = FILES_PATH.'/slider/thumbs/'.$plik;
							
							chmod($pc, 0755);
							require_once 'kCMS/Thumbs/ThumbLib.inc.php';
							
							$options = array('jpegQuality' => 90);

							$thumb = PhpThumbFactory::create($pc, $options)->adaptiveResizeQuadrant($this->sliderszerokosc, $this->sliderwysokosc)->save($pc);
							$thumb = PhpThumbFactory::create($pc, $options)->resize(159, 159)->save($thumbs);
							chmod($thumbs, 0755);
							
							$dataImg = array('plik' => $plik);
							$db->update('slider', $dataImg, 'id = ' . $id);
						}

						$this->_redirect('/admin/slider/');
				} else {

					//Wyswietl bledy    
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
				}
			}
		}

// Usuń panel
		function usunAction() {
			$db = Zend_Registry::get('db');
			// Odczytanie id obrazka
			$id = (int)$this->_request->getParam('id');
			$slider = $db->fetchRow($db->select()->from('slider')->where('id = ?', $id));
							
			unlink(FILES_PATH."/slider/".$slider->plik);
			unlink(FILES_PATH."/slider/thumbs/".$slider->plik);

			$where = $db->quoteInto('id = ?', $id);
			$db->delete('slider', $where);

			$this->_redirect('/admin/slider/');
		}
// Ustaw kolejność
		public function ustawAction() {
			$db = Zend_Registry::get('db');
			$updateRecordsArray = $_POST['recordsArray'];
			$listingCounter = 1;
			foreach ($updateRecordsArray as $recordIDValue) {
				$data = array('sort' => $listingCounter);
				$db->update('slider', $data, 'id = '.$recordIDValue);
				$listingCounter = $listingCounter + 1;
				}
		}
// Usun kilka paneli
		public function kilkaAction() {
			$db = Zend_Registry::get('db');
			$checkbox = $_POST[checkbox];
			for($i=0;$i<count($_POST[checkbox]);$i++){
				$id = $checkbox[$i];
				$where = $db->quoteInto('id = ?', $id);
				$slider = $db->fetchRow($db->select()->from('slider')->where('id = ?', $id));
				
				unlink(FILES_PATH."/slider/".$slider->plik);
				unlink(FILES_PATH."/slider/thumbs/".$slider->plik);
							
				$db->delete('slider', $where);
			}
			$this->_redirect('/admin/slider/');
	}
	
// Ustawienia slidera
	public function ustawieniaAction() {
			$db = Zend_Registry::get('db');

			$form = new Form_SliderUstawieniaForm();
			$this->view->form = $form;
            $ustawienia = $db->fetchRow($db->select()->from('slider_ustawienia')->where('id = ?', 1));

            // Załadowanie do forma
            $array = json_decode(json_encode($ustawienia), true);
            if($array){
                $form->populate($array);
            }

			//Akcja po wcisnieciu Submita
			if ($this->_request->getPost()) {

                $formData = $this->_request->getPost();
                unset($formData['submit']);

				//Sprawdzenie poprawnosci forma
				if ($form->isValid($formData)) {

                    $db->update('slider_ustawienia', $formData, 'id = 1');
                    $this->_redirect('/admin/slider/ustawienia/');

				}

			} else {
					
				//Wyswietl bledy	
				$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
			}
		}
}