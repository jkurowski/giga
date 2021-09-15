<?php
require_once 'kCMS/Thumbs/ThumbLib.inc.php';
class Admin_BoksyController extends kCMS_Admin
{
    public function preDispatch() {
        $this->view->controlname = "Boksy";
    }

// Pokaz wszystkie galerie
    public function indexAction() {
        $db = Zend_Registry::get('db');
        $this->view->katalog = $db->fetchAll($db->select()->from('boksy')->order('sort ASC'));
    }

// Dodaj
    public function nowaAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        $this->view->pagename = " - Nowy boks";
        $this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/boksy/">Wróć do listy</a></div>';

        $form = new Form_BoksForm();
        $this->view->form = $form;
        $this->view->tinymce = "1";

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();
            unset($formData['MAX_FILE_SIZE']);
            unset($formData['obrazek']);
            unset($formData['submit']);

            $obrazek = $_FILES['obrazek']['name'];
            if($_FILES['obrazek']['size'] > 0) {
                $plik = date('mdhis').'-'.slugImg($formData['nazwa'], $obrazek);
            }

            $formData['slug'] = slug($formData['nazwa']);

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {

                $db->insert('boksy', $formData);
                $lastId = $db->lastInsertId();

                if($_FILES['obrazek']['size'] > 0) {
                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/boksy/'.$plik);
                    $upfile = FILES_PATH.'/boksy/'.$plik;
                    chmod($upfile, 0755);

                    PhpThumbFactory::create($upfile)
                        ->adaptiveResizeQuadrant(350, 400)
                        ->save($upfile);
                    chmod($upfile, 0755);

                    $db->update('boksy', array('obrazek' => $plik), 'id = ' . $lastId);
                }

                $this->_redirect('/admin/boksy/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
                $form->populate($formData);

            }
        }
    }

// Edytuj
    public function edytujAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);

        $this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/boksy/">Wróć do listy</a></div>';

        $form = new Form_BoksForm();
        $this->view->form = $form;
        $this->view->tinymce = "1";

        // Odczytanie id
        $id = (int)$this->_request->getParam('id');
        $entry = $db->fetchRow($db->select()->from('boksy')->where('id = ?', $id));

        $this->view->pagename = " - Edytuj: ".$entry->nazwa;

        // Załadowanie do forma
        $array = json_decode(json_encode($entry), true);
        if($array){
            $form->populate($array);
        }

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();
            unset($formData['MAX_FILE_SIZE']);
            unset($formData['obrazek']);
            unset($formData['submit']);

            $obrazek = $_FILES['obrazek']['name'];
            if($_FILES['obrazek']['size'] > 0) {
                $plik = date('mdhis').'-'.slugImg($formData['nazwa'], $obrazek);
            }

            $formData['slug'] = slug($formData['nazwa']);

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {

                $db->update('boksy', $formData, 'id = '.$id);

                if($_FILES['obrazek']['size'] > 0) {
                    unlink(FILES_PATH."/boksy/".$entry->obrazek);

                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/boksy/'.$plik);
                    $upfile = FILES_PATH.'/boksy/'.$plik;
                    chmod($upfile, 0755);

                    PhpThumbFactory::create($upfile)
                        ->adaptiveResizeQuadrant(350, 400)
                        ->save($upfile);
                    chmod($upfile, 0755);

                    $db->update('boksy', array('obrazek' => $plik), 'id = ' . $id);
                }

                $this->_redirect('/admin/boksy/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
                $form->populate($formData);

            }
        }
    }

// Usun
    public function usunKatalogAction() {
        $db = Zend_Registry::get('db');
        $id = (int)$this->_request->getParam('id');

        $katalog = $db->fetchRow($db->select()->from('boksy')->where('id = ?',$id));
        unlink(FILES_PATH."/boksy/".$katalog->obrazek);

        $where = $db->quoteInto('id = ?', $id);
        $db->delete('boksy', $where);
        $this->_redirect('/admin/boksy/');
    }

// Edytuj języki
    public function tlumaczenieAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);

        // Odczytanie id
        $id = (int)$this->getRequest()->getParam('id');
        $lang = $this->getRequest()->getParam('lang');
        if(!$id || !$lang){
            $this->redirect('/admin/boksy/');
        }
        $wpis = $db->fetchRow($db->select()->from('boksy')->where('id = ?', $id));

        $tlumaczenieQuery = $db->select()
            ->from('tlumaczenie_wpisy')
            ->where('module = ?', 'boxes')
            ->where('id_wpis = ?', $id)
            ->where('lang = ?', $lang);
        $tlumaczenie = $db->fetchRow($tlumaczenieQuery);

        // Laduj form
        $form = new Form_BoksForm();
        $form->removeElement('obrazek');
        $this->view->tinymce = "1";

        $array = array(
            'form' => $form,
            'back' => '<div class="back"><a href="'.$this->view->baseUrl().'/admin/boksy/">Wróć do listy</a></div>',
            'pagename' => ' - Edytuj tłumaczenie: '.$wpis->nazwa
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
                $translateModel->saveTranslate($formData, 'boxes', $wpis->id, $lang);
                $this->redirect('/admin/boksy/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
                $form->populate($formData);

            }
        }
    }

// Ustaw kolejność
    public function ustawAction() {
        $db = Zend_Registry::get('db');
        $tabela = $this->_request->getParam('co');
        $updateRecordsArray = $_POST['recordsArray'];
        $listingCounter = 1;
        foreach ($updateRecordsArray as $recordIDValue) {
            $data = array('sort' => $listingCounter);
            $db->update($tabela, $data, 'id = '.$recordIDValue);
            $listingCounter = $listingCounter + 1;
        }
    }
}