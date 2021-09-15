<?php

class Default_KontaktController extends kCMS_Site
{

    private $page_id;
    private $validation;
    private $meta_title;
    private $meta_slowa;
    private $meta_opis;

    public function preDispatch() {
        $this->page_id = 1;
        $this->validation= 1;
    }

    public function indexAction() {
        $this->_helper->layout->setLayout('page');
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $pageModel = new Model_MenuModel();
        $page = $pageModel->getPageById($this->page_id);

        if(!$page) {
            errorPage();
        } else {
            $pageName = (isset($page->nazwa)) ? $page->nazwa : json_decode($page->json)->nazwa;
            $breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$pageName .'</b><meta itemprop="position" content="2" /></li>';

            if(isset($page->meta_tytul)) {
                $this->meta_title = $page->meta_tytul;
            } elseif (isset(json_decode($page->json)->meta_tytul)) {
                $this->meta_title = json_decode($page->json)->meta_tytul;
            }
            if(isset($page->meta_slowa)) {
                $this->meta_slowa = $page->meta_slowa;
            } elseif (isset(json_decode($page->json)->meta_slowa)) {
                $this->meta_slowa = json_decode($page->json)->meta_slowa;
            }
            if(isset($page->meta_opis)) {
                $this->meta_opis = $page->meta_opis;
            } elseif (isset(json_decode($page->json)->meta_opis)) {
                $this->meta_opis = json_decode($page->json)->meta_opis;
            }

            $array = array(
                'nobottom' => 1,
                'pageclass' => ' kontakt-page',
                'strona_id' => $this->page_id,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName,
                'seo_tytul' => $this->meta_title,
                'seo_opis' => $this->meta_opis,
                'seo_slowa' => $this->meta_slowa,
                'content' => (isset($page->tekst)) ? $page->tekst : json_decode($page->json)->tekst,
                'validation' => $this->validation,
                'breadcrumbs' => $breadcrumbs
            );
            $this->view->assign($array);

            if ($this->_request->isPost()) {

                $ip = $_SERVER['REMOTE_ADDR'];
                $adresip = $db->fetchRow($db->select()->from('blokowanie')->where('ip = ?', $ip));

                $grecaptcha = $this->_request->getPost('g-recaptcha-response');
                // unset($formData['g-recaptcha-response']);
                //if(getRecaptchaCheck($grecaptcha) === true){
                    if(!$adresip) {

                        $formData = $this->_request->getPost();
                        if($formData['imie'] && $formData['email']) {

                            $imie = $this->_request->getPost('imie');
                            $email = $this->_request->getPost('email');
                            $telefon = $this->_request->getPost('telefon');
                            $wiadomosc = $this->_request->getPost('wiadomosc');
                            $ip = $_SERVER['REMOTE_ADDR'];

                            $ustawienia = $db->fetchRow($db->select()->from('ustawienia'));

                            $emailarray = array(
                                'nazwa_strony' => $ustawienia->nazwa,
                                'imie' => $imie,
                                'email' => $email,
                                'telefon' => $telefon,
                                'wiadomosc' => $wiadomosc,
                                'ip' => $ip
                            );

                            $view = new Zend_View();
                            $view->setScriptPath( APPLICATION_PATH . '/modules/default/views/scripts/email/' );
                            $view->assign($emailarray);

                            $mail = new Zend_Mail('UTF-8');
                            $mail
                                ->setFrom($ustawienia->email, $imie)
                                ->addTo($ustawienia->email, 'Adres odbiorcy')
                                ->setReplyTo($email, $imie)
                                ->setSubject($ustawienia->domena.' - Zapytanie ze strony www - Kontakt');
                            $mail->setBodyHtml($view->render( 'kontakt.phtml'));
                            $mail->setBodyText($view->render( 'kontakt-txt.phtml'));

                            try {
                                $mail->send();
                            } catch (Zend_Exception $e) {
                                //echo $e->getMessage();
                                exit;
                            }

                            //Zapisz statystyki
                            $stat = array(
                                'akcja' => 1,
                                'miejsce' => 4,
                                'data' => date("d.m.Y - H:i:s"),
                                'timestamp' => date("d-m-Y"),
                                'godz' => date("H"),
                                'dzien' => date("d"),
                                'msc' => date("m"),
                                'rok' => date("Y"),
                                'tekst' => $wiadomosc,
                                'email' => $email,
                                'telefon' => $telefon,
                                'ip' => $_SERVER['REMOTE_ADDR']
                            );
                            $db->insert('statystyki', $stat);

                            //Zapisz klienta
                            $formData = $this->_request->getPost();
                            $checkbox = preg_grep("/zgoda_([0-9])/i", array_keys($formData));
                            $przegladarka = $_SERVER['HTTP_USER_AGENT'];
                            historylog($imie, $email, $ip, $przegladarka, $checkbox);

                            $this->view->message = 1;

                        } else {
                            $this->view->message = 2;
                        }
                    }
                //}
            }
        }
    }
}