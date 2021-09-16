<?php

class Admin_NewsController extends kCMS_Admin
{
		
		public function preDispatch() {
			$this->view->controlname = "Aktualności";
			
			$this->duzawysokosc = '602';
			$this->duzaszerokosc = '1170';
			$this->malawysokosc = '360';
			$this->malaszerokosc = '700';

		}
// Pokaz wszystkie wpisy
		public function indexAction() {
			$db = Zend_Registry::get('db');
			$this->view->news = $db->fetchAll($db->select()->from('news')->order('data DESC'));
		}
// Dodaj nowy wpis
		public function nowyAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->pagename = " - Nowy wpis";
			$this->view->tinymce = "1";
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/news/">Wróć do listy wpisów</a></div>';
			$this->view->info = '<div class="info">Wymiary obrazka: szerokość <b>'.$this->duzaszerokosc.'px</b> / wysokość <b>'.$this->duzawysokosc.'px</b></div>';

			$form = new Form_NewsForm();
			$this->view->form = $form;
			
			$form->getElement('meta_opis')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_slowa')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_tytul')->getDecorator('label')->setOption('escape', false);
			
			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					//Odczytanie wartosci z inputów 
					$status = $this->_request->getPost('status');
					$tytul = $this->_request->getPost('tytul');
					$meta_tytul = $this->_request->getPost('meta_tytul');
					$meta_slowa = $this->_request->getPost('meta_slowa');
					$meta_opis = $this->_request->getPost('meta_opis');
					$wprowadzenie = $this->_request->getPost('wprowadzenie');
					$tekst = $this->_request->getPost('tekst');
					$tag = zmiana($tytul);
					$datadodania = $this->_request->getPost('data');
					$obrazek = $_FILES['obrazek']['name'];
					$plik = zmiana($tytul).'.'.zmiennazwe($obrazek);
					$formData = $this->_request->getPost();
					
					$pieces = explode("-", $datadodania);
					
					$wpis = $db->fetchRow($db->select()->from('news')->where('tag = ?', $tag));
					if($wpis){
						
						$this->view->error = '<div class="blad">Artykuł o takiej nazwie już istnieje</div>';
						
					} else {
						//Sprawdzenie poprawnosci forma
						if ($form->isValid($formData)) {
				
							$data = array(
								'data' => $datadodania,
								'tytul' => $tytul,
								'wprowadzenie' => $wprowadzenie,
								'tekst' => $tekst,
								'meta_slowa' => $meta_slowa,
								'meta_opis' => $meta_opis,
								'meta_tytul' => $meta_tytul,
								'tag' => $tag,
								'status' => $status
							);
							
							$db->insert('news', $data);
							$lastId = $db->lastInsertId();
				
						//Pomyslnie
						if ($obrazek) {

							move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/news/'.$plik);
							$upfile = FILES_PATH.'/news/'.$plik;
							$thumbs = FILES_PATH.'/news/thumbs/'.$plik;
							$share = FILES_PATH.'/news/share/'.$plik;
							chmod($upfile, 0755);
							require_once 'kCMS/Thumbs/ThumbLib.inc.php';

							$options = array('jpegQuality' => 80);
							$thumb = PhpThumbFactory::create($upfile, $options)->adaptiveResizeQuadrant($this->malaszerokosc, $this->malawysokosc)->save($thumbs);
							$thumb = PhpThumbFactory::create($upfile, $options)->adaptiveResizeQuadrant(600,315)->save($share);
							$thumb = PhpThumbFactory::create($upfile, $options)->adaptiveResizeQuadrant($this->duzaszerokosc, $this->duzawysokosc)->save($upfile);

							$dataImg = array('plik' => $plik);
							$db->update('news', $dataImg, 'id = '.$lastId);
							
						}

						$this->_redirect('/admin/news/');

					} else {

						//Wyswietl bledy	
						$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
						$form->populate($formData);

					}
				}
			}
		}
// Edytuj wpis
		public function edytujAction() {
			$db = Zend_Registry::get('db');
			$this->_helper->viewRenderer('form', null, true);
			$this->view->tinymce = "1";
			$this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/news/">Wróć do listy wpisów</a></div>';
			$this->view->info = '<div class="info">Wymiary obrazka: szerokość <b>'.$this->duzaszerokosc.'px</b> / wysokość <b>'.$this->duzawysokosc.'px</b></div>';
			
			$form = new Form_NewsForm();
			$this->view->form = $form;
			
			$form->getElement('meta_opis')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_slowa')->getDecorator('label')->setOption('escape', false);
			$form->getElement('meta_tytul')->getDecorator('label')->setOption('escape', false);
			
			// Polskie tlumaczenie errorów
			$polish = kCMS_Polish::getPolishTranslation();
			$translate = new Zend_Translate('array', $polish, 'pl');
			$form->setTranslator($translate);

			// Odczytanie id
			$id = (int)$this->getRequest()->getParam('id');
			$wpis = $db->fetchRow($db->select()->from('news')->where('id = ?', $id));
			$this->view->pagename = " - Edytuj wpis: ".$wpis->tytul;

			// Załadowanie do forma
			$form->status->setvalue($wpis->status);
			$form->tytul->setvalue($wpis->tytul);
			
			$form->meta_tytul->setvalue($wpis->meta_tytul);
			$form->meta_slowa->setvalue($wpis->meta_slowa);
			$form->meta_opis->setvalue($wpis->meta_opis);
			$form->wprowadzenie->setvalue($wpis->wprowadzenie);
			$form->tekst->setvalue($wpis->tekst);
			$form->data->setvalue($wpis->data);
			$form->obrazek->setvalue($wpis->plik);

				//Akcja po wcisnieciu Submita
				if ($this->_request->getPost()) {

					//Odczytanie wartosci z inputów $tytul, $data, $obrazek, $wprowadzenie, $tekst
					$status = $this->_request->getPost('status');
					$tytul = $this->_request->getPost('tytul');
					$meta_tytul = $this->_request->getPost('meta_tytul');
					$meta_slowa = $this->_request->getPost('meta_slowa');
					$meta_opis = $this->_request->getPost('meta_opis');
					$wprowadzenie = $this->_request->getPost('wprowadzenie');
					$tekst = $this->_request->getPost('tekst');
					$tag = zmiana($tytul);
					$datadodania = $this->_request->getPost('data');
					$obrazek = $_FILES['obrazek']['name'];
					$plik = zmiana($tytul).'.'.zmiennazwe($obrazek);
					$formData = $this->_request->getPost();

					if($tag == $wpis->tag){
						
						//Sprawdzenie poprawnosci forma
						if ($form->isValid($formData)) {
						
							$data = array(
								'data' => $datadodania,
								'tytul' => $tytul,
								'wprowadzenie' => $wprowadzenie,
								'tekst' => $tekst,
								'meta_slowa' => $meta_slowa,
								'meta_opis' => $meta_opis,
								'meta_tytul' => $meta_tytul,
								'tag' => $tag,
								'status' => $status
							);
							
							$db->update('news', $data, 'id = '.$id);

							if ($obrazek) {
								//Usuwanie starych zdjęć
								unlink(FILES_PATH."/news/".$wpis->plik);
								unlink(FILES_PATH."/news/thumbs/".$wpis->plik);
								unlink(FILES_PATH."/news/share/".$wpis->plik);

								move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/news/'.$plik);
								$upfile = FILES_PATH.'/news/'.$plik;
								$thumbs = FILES_PATH.'/news/thumbs/'.$plik;
								$share = FILES_PATH.'/news/share/'.$plik;
								chmod($upfile, 0755);
								require_once 'kCMS/Thumbs/ThumbLib.inc.php';

								$options = array('jpegQuality' => 80);
								$thumb = PhpThumbFactory::create($upfile, $options)->adaptiveResizeQuadrant($this->malaszerokosc, $this->malawysokosc)->save($thumbs);
								$thumb = PhpThumbFactory::create($upfile, $options)->adaptiveResizeQuadrant(600,315)->save($share);
								$thumb = PhpThumbFactory::create($upfile, $options)->adaptiveResizeQuadrant($this->duzaszerokosc, $this->duzawysokosc)->save($upfile);
										
								$dataImg = array('plik' => $plik);
								$db->update('news', $dataImg, 'id = '.$id);
								
							}
							$this->redirect('/admin/news/');

						} else {
											
							//Wyswietl bledy	
							$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
							$form->populate($formData);

						}
						
						//echo 'to ten sam tag i ten sam post';
						
					} else {
						
						//tag jest inny
						//sprawdz czy ten istnieje w innym poscie
						$czyjest = $db->fetchRow($db->select()->from('news')->where('tag = ?', $tag));
						
						if($czyjest && $czyjest->id <> $id) {
							
							$this->view->error = '<div class="blad">Artykuł o takiej nazwie już istnieje</div>';
							
						} else {
							
							//echo 'to ten sam tag i nowy post';
							
							//Sprawdzenie poprawnosci forma
							if ($form->isValid($formData)) {
							
								$data = array(
									'data' => $datadodania,
									'wprowadzenie' => $wprowadzenie,
									'tekst' => $tekst,
									'meta_slowa' => $meta_slowa,
									'meta_opis' => $meta_opis,
									'meta_tytul' => $meta_tytul,
									'tytul' => $tytul,
									'tag' => $tag,
									'status' => $status
								);
								
								$db->update('news', $data, 'id = '.$id);

								if ($obrazek) {
									//Usuwanie starych zdjęć
									unlink(FILES_PATH."/news/".$wpis->plik);
									unlink(FILES_PATH."/news/thumbs/".$wpis->plik);
									unlink(FILES_PATH."/news/share/".$wpis->plik);

									move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/news/'.$plik);
									$upfile = FILES_PATH.'/news/'.$plik;
									$thumbs = FILES_PATH.'/news/thumbs/'.$plik;
									$share = FILES_PATH.'/news/share/'.$plik;
									chmod($upfile, 0755);
									require_once 'kCMS/Thumbs/ThumbLib.inc.php';

									$options = array('jpegQuality' => 80);
									$thumb = PhpThumbFactory::create($upfile, $options)->adaptiveResizeQuadrant($this->malaszerokosc, $this->malawysokosc)->save($thumbs);
									$thumb = PhpThumbFactory::create($upfile, $options)->adaptiveResizeQuadrant(600,315)->save($share);
									$thumb = PhpThumbFactory::create($upfile, $options)->adaptiveResizeQuadrant($this->duzaszerokosc, $this->duzawysokosc)->save($upfile);
											
									$dataImg = array('plik' => $plik);
									$db->update('news', $dataImg, 'id = '.$id);
									
								}
								$this->_redirect('/admin/news/');

							} else {
												
								//Wyswietl bledy	
								$this->view->message = '<div class="error">Formularz zawiera błędy</div>';
								$form->populate($formData);

							}
				
						}
				}
			}
		}

// Edytuj języki
    public function tlumaczenieAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);

        // Odczytanie id
        $id = (int)$this->getRequest()->getParam('id');
        $lang = $this->getRequest()->getParam('lang');
        if(!$id || !$lang){
            $this->redirect('/admin/news/');
        }
        $wpis = $db->fetchRow($db->select()->from('news')->where('id = ?', $id));

        $tlumaczenieQuery = $db->select()
            ->from('tlumaczenie_wpisy')
            ->where('module = ?', 'news')
            ->where('id_wpis = ?', $id)
            ->where('lang = ?', $lang);
        $tlumaczenie = $db->fetchRow($tlumaczenieQuery);

        // Laduj form
        $form = new Form_NewsForm();
        $this->view->tinymce = "1";
        $form->removeElement('obrazek');
        $form->removeElement('status');
        $form->removeElement('data');

        $array = array(
            'form' => $form,
            'back' => '<div class="back"><a href="'.$this->view->baseUrl().'/admin/news/">Wróć do listy</a></div>',
            'pagename' => ' - Edytuj tłumaczenie: '.$wpis->tytul
        );
        $this->view->assign($array);

        if($tlumaczenie) {
            $array = json_decode($tlumaczenie->json, true);
            $form->populate($array);
        }

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            $formData = $this->_request->getPost();

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {

                $translateModel = new Model_TranslateModel();
                $translateModel->saveTranslate($formData, 'news', $wpis->id, $lang);
                $this->redirect('/admin/news/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
                $form->populate($formData);

            }
        }
    }

// Usun wpis
		public function usunAction() {
			$db = Zend_Registry::get('db');
			$id = (int)$this->_request->getParam('id');
			$wpis = $db->fetchRow($db->select()->from('news')->where('id = ?', $id));
			
			unlink(FILES_PATH."/news/".$wpis->plik);
			unlink(FILES_PATH."/news/thumbs/".$wpis->plik);
			unlink(FILES_PATH."/news/share/".$wpis->plik);
			
			$db->delete('news', 'id = '.$id);
			$this->redirect('/admin/news/');
		}
// Usun obrazek do newsa
		public function usunobrazekAction() {
			$db = Zend_Registry::get('db');
			$id = (int)$this->_request->getParam('id');
			$wpis = $db->fetchRow($db->select()->from('news')->where('id = ?', $id));
			
			unlink(FILES_PATH."/news/".$wpis->plik);
			unlink(FILES_PATH."/news/thumbs/".$wpis->plik);

			$data = array('plik' => '');
			$db->update('news', $data, 'id = '.$id);

			$this->redirect('/admin/news/');
		}
}