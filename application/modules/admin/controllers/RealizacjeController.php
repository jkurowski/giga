<?php
require_once 'kCMS/Thumbs/ThumbLib.inc.php';
class Admin_RealizacjeController extends kCMS_Admin
{
    public function preDispatch() {
        $this->view->controlname = "Realizacje";
    }

// Pokaz wszystkie galerie
    public function indexAction() {
        $db = Zend_Registry::get('db');
        $this->view->lista = $db->fetchAll($db->select()->from('realizacje')->order('sort ASC'));
    }

// Dodaj galerie
    public function nowaAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        $this->view->pagename = " - Nowa realizacja";
        $this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/realizacje/">Wróć do listy</a></div>';

        $form = new Form_RealizacjaForm();
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

                $db->insert('realizacje', $formData);
                $lastId = $db->lastInsertId();

                if($_FILES['obrazek']['size'] > 0) {
                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/realizacje/'.$plik);
                    $upfile = FILES_PATH.'/realizacje/'.$plik;
                    chmod($upfile, 0755);

                    PhpThumbFactory::create($upfile)
                        ->adaptiveResizeQuadrant(590, 590)
                        ->save($upfile);
                    chmod($upfile, 0755);

                    $db->update('realizacje', array('obrazek' => $plik), 'id = ' . $lastId);
                }

                $this->_redirect('/admin/realizacje/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
                $form->populate($formData);

            }
        }
    }

// Edytuj galerie
    public function edytujAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);

        $this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/realizacje/">Wróć do listy galerii</a></div>';

        $form = new Form_RealizacjaForm();
        $this->view->form = $form;
        $this->view->tinymce = "1";

        // Odczytanie id
        $id = (int)$this->_request->getParam('id');
        $entry = $db->fetchRow($db->select()->from('realizacje')->where('id = ?', $id));

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

                $db->update('realizacje', $formData, 'id = '.$id);

                if($_FILES['obrazek']['size'] > 0) {
                    unlink(FILES_PATH."/realizacje/".$entry->obrazek);

                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/realizacje/'.$plik);
                    $upfile = FILES_PATH.'/realizacje/'.$plik;
                    chmod($upfile, 0755);

                    PhpThumbFactory::create($upfile)
                        ->adaptiveResizeQuadrant(590, 590)
                        ->save($upfile);
                    chmod($upfile, 0755);

                    $db->update('realizacje', array('obrazek' => $plik), 'id = ' . $id);
                }

                $this->_redirect('/admin/realizacje/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
                $form->populate($formData);

            }
        }
    }

// Usun galerie
    public function usunAction() {
        $db = Zend_Registry::get('db');
        $id = (int)$this->_request->getParam('id');

        $katalog = $db->fetchRow($db->select()->from('realizacje')->where('id = ?',$id));
        unlink(FILES_PATH."/realizacje/".$katalog->obrazek);

        $where = $db->quoteInto('id = ?', $id);
        $db->delete('realizacje', $where);
        $this->_redirect('/admin/realizacje/');
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