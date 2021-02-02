<?php

class Admin_StronyController extends kCMS_Admin
{
		public function preDispatch() {
			$this->view->controlname = "Zarządzanie stronami";
		}
// Pokaz wszystkie srony
		public function indexAction() {
			$db = Zend_Registry::get('db');
			$this->view->strony = $db->fetchAll($db->select()->from('strony')->order('sort ASC')->order('nazwa ASC'));
		}
// Dodaj nową stronę
		public function nowaStronaAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->pagename = " - Nowa strona";
			$this->view->tinymce = "1";
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/strony/">Wróć do listy stron</a></div>';
			$this->view->info = '<div class="info">Wymiary obrazka: 1920px szerokości / 350px wysokości</div>';

			$form = new Form_StronaForm();
			$this->view->form = $form;
			
			$form->getElement('meta_slowa')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_tytul')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_opis')->getDecorator('label')->setOption('escape', false);
			$form->removeElement('link');
			$form->removeElement('target');
			//$form->removeElement('menu');
			//$form->removeElement('id_parent');
			//$form->removeElement('meta_slowa');
			//$form->removeElement('meta_tytul');
			//$form->removeElement('meta_opis');
			//$form->removeElement('tekst');

			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					//Odczytanie wartosci z inputów
					$menu = $this->_request->getPost('menu');
					$id_parent = $this->_request->getPost('id_parent');
					$nazwa = $this->_request->getPost('nazwa');
					$tag = zmiana($nazwa);
					$tekst = $this->_request->getPost('tekst');
					$meta_tytul = $this->_request->getPost('meta_tytul');
					$meta_opis = $this->_request->getPost('meta_opis');
					$meta_slowa = $this->_request->getPost('meta_slowa');
					$datadodania = date("d.m.Y - H:i:s");
					
					$obrazek = $_FILES['obrazek']['name'];
					$plik = zmiana($nazwa).'.'.zmiennazwe($obrazek);
					
					$formData = $this->_request->getPost();

					//Sprawdzenie poprawnosci forma
					if ($form->isValid($formData)) {
					
					$parenttag = $db->fetchRow($db->select()->from('strony')->where('id = ?', $id_parent));
					$parenttag = $parenttag->tag;
					
					//Pomyslnie
					$data = array(
						'menu' => $menu,
						'id_parent' => $id_parent,
						'nazwa' => $nazwa,
						'tekst' => $tekst,
						'typ' => 0,
						'tag' => $tag,
						'tag_parent' => $parenttag,
						'meta_slowa' => $meta_slowa,
						'meta_opis' => $meta_opis,
						'meta_tytul' => $meta_tytul,
						'data' => $datadodania);
						
					$db->insert('strony', $data);
					$lastId = $db->lastInsertId();
					
					// Generowanie URI
					$menu = new kCMS_MenuBuilder();
					$uri = $menu->urigenerate($lastId);
					
					$dataUri = array('uri' => $uri);
					$db->update('strony', $dataUri, 'id = '.$lastId);
					
					if($obrazek) {
						move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/header/'.$plik);
						$pc = FILES_PATH.'/header/'.$plik;
						chmod($pc, 0755);
						require_once 'kCMS/Thumbs/ThumbLib.inc.php';
						$thumb = PhpThumbFactory::create($pc)->adaptiveResizeQuadrant(1920, 130)->save($pc);
						
						$dataImg = array('plik' => $plik);
						
						$db->update('strony', $dataImg, 'id = '.$lastId);
					}
					
					$this->_redirect('/admin/strony/');

				} else {
						
					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}
// Edytuj stronę
		public function edytujAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->tinymce = "1";

			// Odczytanie id
			$id = (int)$this->getRequest()->getParam('id');
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/strony/">Wróć do listy stron</a></div>';
			$this->view->info = '<div class="info">Wymiary obrazka: 1920px szerokości / 350px wysokości</div>';
			
			$strony = $db->fetchRow($db->select()->from('strony')->where('id = ?', $id));
			$this->view->pagename = " - Edytuj stronę: ".$strony->nazwa;

			$form = new Form_StronaForm();
			$this->view->form = $form;
			
			$form->getElement('meta_slowa')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_tytul')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_opis')->getDecorator('label')->setOption('escape', false);
			$form->removeElement('link');
			$form->removeElement('target');
			//$form->removeElement('menu');
			//$form->removeElement('id_parent');
			//$form->removeElement('meta_slowa');
			//$form->removeElement('meta_tytul');
			//$form->removeElement('meta_opis');
			//$form->removeElement('tekst');

			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);
			
			if($strony->lock == 1) {
				$form->nazwa->setAttrib('readonly', 'true');
				$form->menu->setAttrib('disabled', 'disabled');
				$form->id_parent->setAttrib('disabled', 'disabled');
			}

			// Załadowanie do forma
			$form->menu->setvalue($strony->menu);
			$form->id_parent->setvalue($strony->id_parent);
			$form->nazwa->setvalue($strony->nazwa);
			// $form->obrazek->setvalue($strony->plik);
			$form->tekst->setvalue($strony->tekst);
			$form->meta_slowa->setvalue($strony->meta_slowa);
			$form->meta_opis->setvalue($strony->meta_opis);
			$form->meta_tytul->setvalue($strony->meta_tytul);

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					//Odczytanie wartosci z inputów
					$menu = $this->_request->getPost('menu');
					$id_parent = $this->_request->getPost('id_parent');
					$nazwa = $this->_request->getPost('nazwa');
					$tag = zmiana($nazwa);
					$tekst = $this->_request->getPost('tekst');
					$meta_tytul = $this->_request->getPost('meta_tytul');
					$meta_opis = $this->_request->getPost('meta_opis');
					$meta_slowa = $this->_request->getPost('meta_slowa');
					$datadodania = date("d.m.Y - H:i:s");
					
					$obrazek = $_FILES['obrazek']['name'];
					$plik = zmiana($nazwa).'.'.zmiennazwe($obrazek);
					
					$formData = $this->_request->getPost();

					//Sprawdzenie poprawnosci forma
					if ($form->isValid($formData)) {
					
					if($strony->lock == 1) {
						$id_parent = $strony->id_parent;
						if($strony->menu == 0) {$menu = 0;}
						if($strony->menu == 1) {$menu = 1;}
						if($strony->menu == 2) {$menu = 2;}
					} else {
						$id_parent = $this->_request->getPost('id_parent');
						$menu = $menu;
					}
					
					$parenttag = $db->fetchRow($db->select()->from('strony')->where('id = ?', $id_parent));
					$parenttag = $parenttag->tag;
					
					//Pomyslnie
					$data = array(
						'menu' => $menu,
						'id_parent' => $id_parent,
						'nazwa' => $nazwa,
						'tekst' => $tekst,
						'typ' => 0,
						'tag' => $tag,
						'tag_parent' => $parenttag,
						'meta_slowa' => $meta_slowa,
						'meta_opis' => $meta_opis,
						'meta_tytul' => $meta_tytul,
						'data' => $datadodania);
						
					$db->update('strony', $data, 'id = '.$id);
					
					// Generowanie URI
					$menu = new kCMS_MenuBuilder();
					$uri = $menu->urigenerate($id);
					$menu->mapTree($id);
					
					$dataUri = array('uri' => $uri);
					$db->update('strony', $dataUri, 'id = '.$id);
					
					if($obrazek) {
						unlink(FILES_PATH."/header/".$strony->plik);
						move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/header/'.$plik);
						$pc = FILES_PATH.'/header/'.$plik;
						chmod($pc, 0755);
						require_once 'kCMS/Thumbs/ThumbLib.inc.php';
						$thumb = PhpThumbFactory::create($pc)->adaptiveResizeQuadrant(1920, 130)->save($pc);
						
						$dataImg = array('plik' => $plik);
						
						$db->update('strony', $dataImg, 'id = '.$id);
					}
					
					$this->_redirect('/admin/strony/');

				} else {
						
					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}

// Dodaj nowy link
		public function nowyLinkAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->pagename = " - Dodaj link";
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/strony/">Wróć do listy stron</a></div>';
			$this->view->info = '<div class="info">Wymiary obrazka: 1920px szerokości / 350px wysokości</div>';
			
			$form = new Form_StronaForm();
			$this->view->form = $form;
			
			$this->view->linkform = 1;
			
			$form->getElement('meta_slowa')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_tytul')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_opis')->getDecorator('label')->setOption('escape', false);
			//$form->removeElement('link');
			//$form->removeElement('menu');
			//$form->removeElement('id_parent');
			// $form->removeElement('meta_slowa');
			// $form->removeElement('meta_tytul');
			// $form->removeElement('meta_opis');
			$form->removeElement('tekst');
			
			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					//Odczytanie wartosci z inputów
					$menu = $this->_request->getPost('menu');
					$id_parent = $this->_request->getPost('id_parent');
					$nazwa = $this->_request->getPost('nazwa');
					$target = $this->_request->getPost('target');
					$tag = zmiana($nazwa);
					//$tekst = $this->_request->getPost('tekst');
					$link = $this->_request->getPost('link');
					//$meta_tytul = $this->_request->getPost('meta_tytul');
					//$meta_opis = $this->_request->getPost('meta_opis');
					//$meta_slowa = $this->_request->getPost('meta_slowa');
					$datadodania = date("d.m.Y - H:i:s");
										
					$obrazek = $_FILES['obrazek']['name'];
					$plik = zmiana($nazwa).'.'.zmiennazwe($obrazek);
					$formData = $this->_request->getPost();
					
					//Sprawdzenie poprawnosci forma
					if ($form->isValid($formData)) {
					
					$parenttag = $db->fetchRow($db->select()->from('strony')->where('id = ?', $id_parent));
					$parenttag = $parenttag->tag;
					
					//Pomyslnie
					$data = array(
						'menu' => $menu,
						'id_parent' => $id_parent,
						'nazwa' => $nazwa,
						//'tekst' => $tekst,
						'link' => $link,
						'link_target' => $target,
						'typ' => 3,
						'tag' => $tag,
						'tag_parent' => $parenttag,
						'meta_slowa' => $meta_slowa,
						'meta_opis' => $meta_opis,
						'meta_tytul' => $meta_tytul,
						'data' => $datadodania);
						
					$db->insert('strony', $data);
					$lastId = $db->lastInsertId();
					
					// Generowanie URI
					$menu = new kCMS_MenuBuilder();
					$uri = $menu->urigenerate($lastId);
					
					if($target == '_self'){
						$dataUri = array('uri' => $link);
					} else {
						$dataUri = array('uri' => $uri);
					}

					$db->update('strony', $dataUri, 'id = '.$lastId);
					
					if($obrazek) {
						move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/header/'.$plik);
						$pc = FILES_PATH.'/header/'.$plik;
						chmod($pc, 0755);
						require_once 'kCMS/Thumbs/ThumbLib.inc.php';
						$thumb = PhpThumbFactory::create($pc)->adaptiveResizeQuadrant(1920, 130)->save($pc);
						
						$dataImg = array('plik' => $plik);
						
						$db->update('strony', $dataImg, 'id = '.$lastId);
					}
					$this->_redirect('/admin/strony/');

				} else {
						
					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}

// Edytuj link
		public function edytujlinkAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);

			// Odczytanie id
			$id = (int)$this->getRequest()->getParam('id');
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/strony/">Wróć do listy stron</a></div>';
			$this->view->info = '<div class="info">Wymiary obrazka: 1920px szerokości / 350px wysokości</div>';
			
			$strony = $db->fetchRow($db->select()->from('strony')->where('id = ?', $id));
			$this->view->pagename = " - Edytuj link: ".$strony->nazwa;

			$form = new Form_StronaForm();
			$this->view->form = $form;
			
			$form->getElement('meta_slowa')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_tytul')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_opis')->getDecorator('label')->setOption('escape', false);
			//$form->removeElement('link');
			//$form->removeElement('menu');
			//$form->removeElement('id_parent');
			// $form->removeElement('meta_slowa');
			// $form->removeElement('meta_tytul');
			// $form->removeElement('meta_opis');
			$form->removeElement('tekst');
			
			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);

			// Zablokowana strona
			if($strony->lock == 1) {
				$form->nazwa->setAttrib('readonly', 'true');
				$form->menu->setAttrib('disabled', 'disabled');
				$form->id_parent->setAttrib('disabled', 'disabled');
			}
			
			// Załadowanie do forma
			$form->menu->setvalue($strony->menu);
			$form->id_parent->setvalue($strony->id_parent);
			$form->nazwa->setvalue($strony->nazwa);
			//$form->tekst->setvalue($strony->tekst);
			// $form->obrazek->setvalue($strony->plik);
			$form->link->setvalue($strony->link);
			$form->target->setvalue($strony->link_target);
			$form->meta_slowa->setvalue($strony->meta_slowa);
			$form->meta_opis->setvalue($strony->meta_opis);
			$form->meta_tytul->setvalue($strony->meta_tytul);

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

			
					//Odczytanie wartosci z inputów
					$menu = $this->_request->getPost('menu');
					$id_parent = $this->_request->getPost('id_parent');
					$nazwa = $this->_request->getPost('nazwa');
					$target = $this->_request->getPost('target');
					$tag = zmiana($nazwa);
					$tekst = $this->_request->getPost('tekst');
					$link = $this->_request->getPost('link');
					$meta_tytul = $this->_request->getPost('meta_tytul');
					$meta_opis = $this->_request->getPost('meta_opis');
					$meta_slowa = $this->_request->getPost('meta_slowa');
					$datadodania = date("d.m.Y - H:i:s");
					
					$obrazek = $_FILES['obrazek']['name'];
					$plik = zmiana($nazwa).'.'.zmiennazwe($obrazek);
					
					$formData = $this->_request->getPost();
					
					//Sprawdzenie poprawnosci forma
					if ($form->isValid($formData)) {

					if($strony->lock == 1) {
						$id_parent = $strony->id_parent;
						if($strony->menu == 0) {$menu = 0;}
						if($strony->menu == 1) {$menu = 1;}
						if($strony->menu == 2) {$menu = 2;}
					} else {
						$id_parent = $this->_request->getPost('id_parent');
						$menu = $menu;
					}
					
					$parenttag = $db->fetchRow($db->select()->from('strony')->where('id = ?', $id_parent));
					$parenttag = $parenttag->tag;
					
					//Pomyslnie
					$data = array(
						'menu' => $menu,
						'id_parent' => $id_parent,
						'nazwa' => $nazwa,
						//'tekst' => $tekst,
						'link' => $link,
						'link_target' => $target,
						'typ' => 3,
						'tag' => $tag,
						'tag_parent' => $parenttag,
						'meta_slowa' => $meta_slowa,
						'meta_opis' => $meta_opis,
						'meta_tytul' => $meta_tytul,
						'data' => $datadodania);

					$where['id = ?'] = $id;
					$db->update('strony', $data, $where);
					
					// Generowanie URI
					$menu = new kCMS_MenuBuilder();
					$uri = $menu->urigenerate($id);
					$menu->mapTree($id);
					
					if($target == '_self'){
						$dataUri = array('uri' => $link);
					} else {
						$dataUri = array('uri' => $uri);
					}

					$db->update('strony', $dataUri, 'id = '.$id);
					
					if($obrazek) {
						unlink(FILES_PATH."/header/".$strony->plik);
						move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/header/'.$plik);
						$pc = FILES_PATH.'/header/'.$plik;
						chmod($pc, 0755);
						require_once 'kCMS/Thumbs/ThumbLib.inc.php';
						$thumb = PhpThumbFactory::create($pc)->adaptiveResizeQuadrant(1920, 130)->save($pc);
						
						$dataImg = array('plik' => $plik);
						
						$db->update('strony', $dataImg, 'id = '.$id);
					}
					
					$this->_redirect('/admin/strony/');

				} else {
						
					//Wyswietl bledy	
					$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
					$form->populate($formData);

				}
			}
		}
// Usun wpis do stron
		public function usunAction() {
			$db = Zend_Registry::get('db');
			$id = (int)$this->_request->getParam('id');
			
			$strony = $db->fetchRow($db->select()->from('strony')->where('id = ?', $id));
			unlink(FILES_PATH."/header/".$strony->plik);
			
			$where = $db->quoteInto('id = ?', $id);
			$db->delete('strony', $where);
			
			$this->_redirect('/admin/strony/');
		}
// Zablokuj strone
		public function lockAction() {
			$db = Zend_Registry::get('db');
			$id = (int)$this->_request->getParam('id');
			$strona = $db->fetchRow($db->select()->from('strony')->where('id = ?', $id));
			
			if($strona->lock == 1) { $lock = 0; } else { $lock = 1; }
			
			$data = array('lock' => $lock);
			$db->update('strony', $data, 'id = '.$id);
			$this->_redirect('/admin/strony/');
		}
      
// Ustaw kolejność
		public function ustawAction() {
			$db = Zend_Registry::get('db');
			$updateRecordsArray = $_POST['recordsArray'];
			$listingCounter = 1;
			foreach ($updateRecordsArray as $recordIDValue) {
				$data = array('sort' => $listingCounter);
				$db->update('strony', $data, 'id = '.$recordIDValue);
				$listingCounter = $listingCounter + 1;
				}
		}
}