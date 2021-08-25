<?php
require_once 'kCMS/Thumbs/ThumbLib.inc.php';
class Admin_OpinieController extends kCMS_Admin
{
    public function preDispatch() {
        $this->view->controlname = "Opinie";
    }

// Pokaz wszystkie galerie
    public function indexAction() {
        $db = Zend_Registry::get('db');
        $this->view->katalog = $db->fetchAll($db->select()->from('opinie')->order('sort ASC'));
    }

// Dodaj
    public function nowaAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        $this->view->pagename = " - Nowy wpis";
        $this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/opinie/">Wróć do listy</a></div>';
        $this->view->info = '<div class="info">Wymiary obrazka: szerokość <b>150 px</b> / wysokość <b>150 px</b></div>';

        $form = new Form_OpiniaForm();
        $this->view->form = $form;

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();
            unset($formData['MAX_FILE_SIZE']);
            unset($formData['obrazek']);
            unset($formData['submit']);

            $obrazek = $_FILES['obrazek']['name'];
            if($_FILES['obrazek']['size'] > 0) {
                $plik = date('mdhis').'-'.slugImg($formData['imie'], $obrazek);
            }

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {

                $db->insert('opinie', $formData);
                $lastId = $db->lastInsertId();

                if($_FILES['obrazek']['size'] > 0) {
                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/opinie/'.$plik);
                    $upfile = FILES_PATH.'/opinie/'.$plik;
                    chmod($upfile, 0755);

                    PhpThumbFactory::create($upfile)
                        ->adaptiveResizeQuadrant(180, 180)
                        ->save($upfile);
                    chmod($upfile, 0755);

                    $db->update('opinie', array('plik' => $plik), 'id = ' . $lastId);
                }

                $this->_redirect('/admin/opinie/');

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

        $this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/opinie/">Wróć do listy</a></div>';
        $this->view->info = '<div class="info">Wymiary obrazka: szerokość <b>150 px</b> / wysokość <b>150 px</b></div>';

        $form = new Form_OpiniaForm();
        $this->view->form = $form;

        // Odczytanie id
        $id = (int)$this->_request->getParam('id');
        $entry = $db->fetchRow($db->select()->from('opinie')->where('id = ?', $id));

        $this->view->pagename = " - Edytuj: ".$entry->imie;

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
                $plik = date('mdhis').'-'.slugImg($formData['imie'], $obrazek);
            }

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {

                $db->update('opinie', $formData, 'id = '.$id);

                if($_FILES['obrazek']['size'] > 0) {
                    unlink(FILES_PATH."/opinie/".$entry->plik);

                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/opinie/'.$plik);
                    $upfile = FILES_PATH.'/opinie/'.$plik;
                    chmod($upfile, 0755);

                    PhpThumbFactory::create($upfile)
                        ->adaptiveResizeQuadrant(180, 180)
                        ->save($upfile);
                    chmod($upfile, 0755);

                    $db->update('opinie', array('plik' => $plik), 'id = ' . $id);
                }

                $this->_redirect('/admin/opinie/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';
                $form->populate($formData);

            }
        }
    }

// Usun
    public function usunAction() {
        $db = Zend_Registry::get('db');
        $id = (int)$this->_request->getParam('id');

        $katalog = $db->fetchRow($db->select()->from('opinie')->where('id = ?',$id));
        unlink(FILES_PATH."/opinie/".$katalog->plik);

        $where = $db->quoteInto('id = ?', $id);
        $db->delete('opinie', $where);
        $this->_redirect('/admin/opinie/');
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