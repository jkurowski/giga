<?php
require_once 'kCMS/Thumbs/ThumbLib.inc.php';
class Admin_OfertaController extends kCMS_Admin
{
    public function preDispatch() {
        $this->view->controlname = "Oferta";
    }

// Pokaz wszystkie galerie
    public function indexAction() {
        $db = Zend_Registry::get('db');
        $this->view->katalog = $db->fetchAll($db->select()->from('oferta')->order('sort ASC'));
    }

// Dodaj
    public function nowaAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        $this->view->pagename = " - Nowy wpis";
        $this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/oferta/">Wróć do listy</a></div>';

        $form = new Form_OfertaForm();
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
                $plik = date('mdhis').'-'.slugImg($formData['nazwa'], $obrazek);
            }

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {

                $db->insert('oferta', $formData);
                $lastId = $db->lastInsertId();

                if($_FILES['obrazek']['size'] > 0) {
                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/oferta/'.$plik);
                    $upfile = FILES_PATH.'/oferta/'.$plik;
                    chmod($upfile, 0755);

                    PhpThumbFactory::create($upfile)
                        ->adaptiveResizeQuadrant(850, 500)
                        ->save($upfile);
                    chmod($upfile, 0755);

                    $db->update('oferta', array('plik' => $plik), 'id = ' . $lastId);
                }

                $this->_redirect('/admin/oferta/');

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

        $this->view->back = '<div class="back"><a href="'.$this->view->baseUrl.'/admin/oferta/">Wróć do listy</a></div>';

        $form = new Form_OfertaForm();
        $this->view->form = $form;

        // Odczytanie id
        $id = (int)$this->_request->getParam('id');
        $entry = $db->fetchRow($db->select()->from('oferta')->where('id = ?', $id));

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

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {

                $db->update('oferta', $formData, 'id = '.$id);

                if($_FILES['obrazek']['size'] > 0) {
                    unlink(FILES_PATH."/oferta/".$entry->plik);

                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/oferta/'.$plik);
                    $upfile = FILES_PATH.'/oferta/'.$plik;
                    chmod($upfile, 0755);

                    PhpThumbFactory::create($upfile)
                        ->adaptiveResizeQuadrant(850, 500)
                        ->save($upfile);
                    chmod($upfile, 0755);

                    $db->update('oferta', array('plik' => $plik), 'id = ' . $id);
                }

                $this->_redirect('/admin/oferta/');

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

        $katalog = $db->fetchRow($db->select()->from('oferta')->where('id = ?',$id));
        unlink(FILES_PATH."/oferta/".$katalog->plik);

        $where = $db->quoteInto('id = ?', $id);
        $db->delete('oferta', $where);
        $this->_redirect('/admin/oferta/');
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