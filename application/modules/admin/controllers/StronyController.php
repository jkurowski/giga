<?php

class Admin_StronyController extends kCMS_Admin
{
    private $redirect;
    private $table;
    private $imgWidth;
    private $imgHeight;

    public function preDispatch() {
        $controlname = "Zarządzanie stronami";
        $this->imgWidth = 1920;
        $this->imgHeight = 300;
        $this->redirect = 'admin/strony';
        $this->table = 'strony';
        $back = '<div class="back"><a href="'.$this->view->baseUrl().'/admin/strony/">Wróć do listy stron</a></div>';
        $info = '<div class="info">Wymiary obrazka: '.$this->imgWidth.' szerokości / '.$this->imgHeight.' wysokości</div>';
        $array = array(
            'controlname' => $controlname,
            'back' => $back,
            'info' => $info,
        );
        $this->view->assign($array);
    }
// Pokaz wszystkie srony
    public function indexAction() {}

// Dodaj nową stronę
    public function nowaStronaAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        $this->view->pagename = " - Nowa strona";
        $this->view->tinymce = "1";

        $form = new Form_StronaForm();
        $this->view->form = $form;

        $form->removeElement('link');
        $form->removeElement('target');

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {

                unset($formData['MAX_FILE_SIZE']);
                unset($formData['obrazek']);
                unset($formData['submit']);

                $obrazek = $_FILES['obrazek']['name'];
                if($_FILES['obrazek']['size'] > 0) {
                    $plik = slugImg($formData['nazwa'], $obrazek);
                }
                $formData['tag'] = slug($formData['nazwa']);

                $parentQuery = $db->select()->from('strony')->where('id = ?', $formData['id_parent']);
                $parent = $db->fetchRow($parentQuery);
                $formData['tag_parent'] = $parent->tag;
                $formData['typ'] = 0; // Strona

                $db->insert('strony', $formData);
                $lastId = $db->lastInsertId();

                // Generowanie URI
                $menu = new kCMS_MenuBuilder();
                if($parent->link == '#'){
                    $uri = $formData['tag'];
                } else {
                    $uri = $menu->urigenerate($lastId);
                }

                $dataUri = array('uri' => $uri);
                $db->update('strony', $dataUri, 'id = '.$lastId);

                if($_FILES['obrazek']['size'] > 0) {
                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/header/'.$plik);
                    $upload = FILES_PATH.'/header/'.$plik;
                    chmod($upload, 0755);
                    require_once 'kCMS/Thumbs/ThumbLib.inc.php';
                    PhpThumbFactory::create($upload)
                        ->adaptiveResizeQuadrant($this->imgWidth, $this->imgHeight)
                        ->save($upload);
                    chmod($upload, 0755);
                    $dataImg = array('plik' => $plik);
                    $db->update('strony', $dataImg, 'id = '.$lastId);
                }

                $this->redirect('/admin/strony/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';

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
        $pageQuery = $db->select()->from('strony')->where('id = ?', $id);
        $page = $db->fetchRow($pageQuery);
        $this->view->pagename = " - Edytuj: ".$page->nazwa;

        $form = new Form_StronaForm();
        $this->view->form = $form;
        $form->removeElement('link');
        $form->removeElement('target');

        if($page->lock == 1) {
            $form->nazwa->setAttrib('readonly', 'true');
            $form->menu->setAttrib('disabled', 'disabled');
            $form->id_parent->setAttrib('disabled', 'disabled');
        }

        // Załadowanie do forma
        $array = json_decode(json_encode($page), true);
        if($array){
            $form->populate($array);
        }
        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {
                if($page->lock == 1) {
                    $formData['id_parent'] = $page->id_parent;
                    if($page->menu == 0) {
                        $formData['menu'] = 0;
                    }
                    if($page->menu == 1) {
                        $formData['menu'] = 1;
                    }
                    if($page->menu == 2) {
                        $formData['menu'] = 2;
                    }
                }

                unset($formData['MAX_FILE_SIZE']);
                unset($formData['obrazek']);
                unset($formData['submit']);

                $obrazek = $_FILES['obrazek']['name'];
                if($_FILES['obrazek']['size'] > 0) {
                    $plik = slugImg($formData['nazwa'], $obrazek);
                }
                $formData['tag'] = slug($formData['nazwa']);

                $parentQuery = $db->select()->from('strony')->where('id = ?', $formData['id_parent']);
                $parent = $db->fetchRow($parentQuery);
                $formData['tag_parent'] = $parent->tag;
                $formData['typ'] = 0; // Strona

                $db->update('strony', $formData, 'id = '.$id);

                // Generowanie URI
                $menu = new kCMS_MenuBuilder();
                if($parent->link == '#'){
                    $uri = $formData['tag'];
                } else {
                    $uri = $menu->urigenerate($id);
                }

                $dataUri = array('uri' => $uri);
                $db->update('strony', $dataUri, 'id = '.$id);

                if($page->lock <> 1) {
                    $menu->mapTree($id);
                }

                if($_FILES['obrazek']['size'] > 0) {
                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/header/'.$plik);
                    $upload = FILES_PATH.'/header/'.$plik;
                    chmod($upload, 0755);
                    require_once 'kCMS/Thumbs/ThumbLib.inc.php';
                    PhpThumbFactory::create($upload)
                        ->adaptiveResizeQuadrant($this->imgWidth, $this->imgHeight)
                        ->save($upload);
                    chmod($upload, 0755);
                    $dataImg = array('plik' => $plik);
                    $db->update('strony', $dataImg, 'id = '.$id);
                }

                $this->redirect('/admin/strony/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';

            }
        }
    }

// Dodaj nowy link
    public function nowyLinkAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        $this->view->pagename = " - Nowy adres URL";
        $this->view->linkform = 1;

        $form = new Form_StronaForm();
        $this->view->form = $form;

        $form->removeElement('tekst');

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {

                unset($formData['MAX_FILE_SIZE']);
                unset($formData['obrazek']);
                unset($formData['submit']);

                $obrazek = $_FILES['obrazek']['name'];
                if($_FILES['obrazek']['size'] > 0) {
                    $plik = slugImg($formData['nazwa'], $obrazek);
                }
                $formData['tag'] = slug($formData['nazwa']);

                $parentQuery = $db->select()->from('strony')->where('id = ?', $formData['id_parent']);
                $parent = $db->fetchRow($parentQuery);
                $formData['tag_parent'] = $parent->tag;
                $formData['typ'] = 3; // Link

                $db->insert('strony', $formData);
                $lastId = $db->lastInsertId();

                // Generowanie URI
                $menu = new kCMS_MenuBuilder();
                $uri = $menu->urigenerate($lastId);

                if($formData['link_target'] == '_self'){
                    $dataUri = array('uri' => $formData['link']);
                } else {
                    $dataUri = array('uri' => $uri);
                }

                $db->update('strony', $dataUri, 'id = '.$lastId);

                if($_FILES['obrazek']['size'] > 0) {
                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/header/'.$plik);
                    $upload = FILES_PATH.'/header/'.$plik;
                    chmod($upload, 0755);
                    require_once 'kCMS/Thumbs/ThumbLib.inc.php';
                    PhpThumbFactory::create($upload)
                        ->adaptiveResizeQuadrant($this->imgWidth, $this->imgHeight)
                        ->save($upload);
                    chmod($upload, 0755);
                    $dataImg = array('plik' => $plik);
                    $db->update('strony', $dataImg, 'id = '.$lastId);
                }

                $this->redirect('/admin/strony/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';

            }
        }
    }

// Edytuj link
    public function edytujlinkAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        // Odczytanie id
        $id = (int)$this->getRequest()->getParam('id');
        $pageQuery = $db->select()->from('strony')->where('id = ?', $id);
        $page = $db->fetchRow($pageQuery);
        $this->view->pagename = " - Edytuj: ".$page->nazwa;

        $form = new Form_StronaForm();
        $this->view->form = $form;
        $form->removeElement('tekst');

        // Zablokowana strona
        if($page->lock == 1) {
            $form->nazwa->setAttrib('readonly', 'true');
            $form->menu->setAttrib('disabled', 'disabled');
            $form->id_parent->setAttrib('disabled', 'disabled');
        }

        // Załadowanie do forma
        $array = json_decode(json_encode($page), true);
        if($array){
            $form->populate($array);
        }
        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            //Odczytanie wartosci z inputów
            $formData = $this->_request->getPost();

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {
                if($page->lock == 1) {
                    $formData['id_parent'] = $page->id_parent;
                    if($page->menu == 0) {
                        $formData['menu'] = 0;
                    }
                    if($page->menu == 1) {
                        $formData['menu'] = 1;
                    }
                    if($page->menu == 2) {
                        $formData['menu'] = 2;
                    }
                }

                unset($formData['MAX_FILE_SIZE']);
                unset($formData['obrazek']);
                unset($formData['submit']);

                $obrazek = $_FILES['obrazek']['name'];
                if($_FILES['obrazek']['size'] > 0) {
                    $plik = slugImg($formData['nazwa'], $obrazek);
                }
                $formData['tag'] = slug($formData['nazwa']);

                $parentQuery = $db->select()->from('strony')->where('id = ?', $formData['id_parent']);
                $parent = $db->fetchRow($parentQuery);
                $formData['tag_parent'] = $parent->tag;
                $formData['typ'] = 3; // Link

                $db->update('strony', $formData, 'id = '.$id);

                // Generowanie URI
                $menu = new kCMS_MenuBuilder();
                $uri = $menu->urigenerate($id);

                if($formData['link_target'] == '_self'){
                    $dataUri = array('uri' => $formData['link']);
                } else {
                    $dataUri = array('uri' => $uri);
                }

                $db->update('strony', $dataUri, 'id = '.$id);
                if ($page->lock <> 1 && $formData['link'] <> '#') {
                    $menu->mapTree($id);
                }

                if($_FILES['obrazek']['size'] > 0) {
                    move_uploaded_file($_FILES['obrazek']['tmp_name'], FILES_PATH.'/header/'.$plik);
                    $upload = FILES_PATH.'/header/'.$plik;
                    chmod($upload, 0755);
                    require_once 'kCMS/Thumbs/ThumbLib.inc.php';
                    PhpThumbFactory::create($upload)
                        ->adaptiveResizeQuadrant($this->imgWidth, $this->imgHeight)
                        ->save($upload);
                    chmod($upload, 0755);
                    $dataImg = array('plik' => $plik);
                    $db->update('strony', $dataImg, 'id = '.$id);
                }

                $this->redirect('/admin/strony/');

            } else {

                //Wyswietl bledy
                $this->view->message = '<div class="error">Formularz zawiera błędy</div>';

            }
        }
    }

// Edytuj języki
    public function tlumaczenieAction() {
        $db = Zend_Registry::get('db');
        $this->_helper->viewRenderer('form', null, true);
        $this->view->back = '<div class="back"><a href="/'.$this->redirect.'">Wróć do listy stron</a></div>';

        // Odczytanie id
        $id = (int)$this->getRequest()->getParam('id');
        $lang = $this->getRequest()->getParam('lang');
        if(!$id || !$lang){
            $this->_redirect($this->redirect);
        }

        $wpis = $db->fetchRow($db->select()->from($this->table)->where('id = ?', $id));
        $tlumaczenieQuery = $db->select()
            ->from('tlumaczenie_wpisy')
            ->where('module = ?', 'menu')
            ->where('id_wpis = ?', $id)
            ->where('lang = ?', $lang);
        $tlumaczenie = $db->fetchRow($tlumaczenieQuery);

        // Laduj form
        $form = new Form_StronaForm();
        $this->view->form = $form;

        if($tlumaczenie) {
            $array = json_decode($tlumaczenie->json, true);
            $form->populate($array);
        }

        $this->view->pagename = " - Edytuj tłumaczenie: ".$wpis->nazwa;

        if($wpis->typ == 3) {
            // Link
            $form->removeElement('tekst');
            $form->removeElement('menu');
            $form->removeElement('id_parent');
            $form->removeElement('target');
            $form->removeElement('link');
            $form->removeElement('obrazek');
        } else {
            // Strona tekstowa
            $form->removeElement('menu');
            $form->removeElement('id_parent');
            $form->removeElement('target');
            $form->removeElement('link');
            $form->removeElement('obrazek');
            $this->view->tinymce = "1";
        }

        //Akcja po wcisnieciu Submita
        if ($this->_request->getPost()) {

            $formData = $this->_request->getPost();

            //Sprawdzenie poprawnosci forma
            if ($form->isValid($formData)) {

                $translateModel = new Model_TranslateModel();
                $translateModel->saveTranslate($formData, 'menu', $wpis->id, $lang);
                $this->redirect($this->redirect);

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

        // Generowanie URI
        $menu = new kCMS_MenuBuilder();
        $menu->deletemapTree($id);

        $pageQuery = $db->select()->from('strony')->where('id = ?', $id);
        $page = $db->fetchRow($pageQuery);
        unlink(FILES_PATH."/header/".$page->plik);

        $where = $db->quoteInto('id = ?', $id);
        $db->delete('strony', $where);

        $where_tl = array('module = ?' => 'menu', 'id_wpis = ?' => $id);
        $db->delete('tlumaczenie_wpisy', $where_tl);

        $this->redirect('/admin/strony/');
    }

// Zablokuj strone
    public function lockAction() {
        $db = Zend_Registry::get('db');
        $id = (int)$this->_request->getParam('id');
        $pageQuery = $db->select()->from('strony')->where('id = ?', $id);
        $page = $db->fetchRow($pageQuery);

        ($page->lock == 1) ? $lock = 0 : $lock = 1;

        $data = array('lock' => $lock);
        $db->update('strony', $data, 'id = '.$id);
        $this->redirect('/admin/strony/');
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