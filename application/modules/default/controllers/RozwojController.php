<?php

class Default_RozwojController extends kCMS_Site
{
    private $page_class;
    private $page_id;
    private $meta_title;
    private $meta_slowa;
    private $meta_opis;
    private $wpis_nazwa;

    public function preDispatch() {
        $this->page_id = 8;
        $this->page_class = 'rozwoj';
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
            $boxModel = new Model_BoxModel();
            $boksy = $boxModel->getAll();

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
                'strona_nazwa' => $pageName,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName,
                'seo_tytul' => $this->meta_title,
                'seo_opis' => $this->meta_opis,
                'seo_slowa' => $this->meta_slowa,
                'strona_id' => $this->page_id,
                'strona' => $page,
                'content' => (isset($page->tekst)) ? $page->tekst : json_decode($page->json)->tekst,
                'menutag' => 'o-nas',
                'boksy' => $boksy,
                'pageclass' => $this->page_class,
                'breadcrumbs' => $breadcrumbs
            );
            $this->view->assign($array);

        }
    }

    public function showAction() {
        $this->_helper->layout->setLayout('page');
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $pageModel = new Model_MenuModel();
        $page = $pageModel->getPageById($this->page_id);

        if(!$page) {
            errorPage();
        } else {

            $slug = $this->_request->getParam('slug');
            $boxModel = new Model_BoxModel();
            $boksy = $boxModel->getAll();
            $wpis = $boxModel->getBySlug($slug);
            $locale = Zend_Registry::get('Zend_Locale')->getLanguage();

            $pageName = (isset($page->nazwa)) ? $page->nazwa : json_decode($page->json)->nazwa;
            if(isset($wpis->nazwa)) {
                $this->wpis_nazwa = $wpis->nazwa;
            } elseif (isset(json_decode($wpis->json)->nazwa)) {
                $this->wpis_nazwa = json_decode($wpis->json)->nazwa;
            }

            $breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="/'.$locale.'/zrownowazony-rozwoj/"><span itemprop="name">'.$pageName.'</span></a><meta itemprop="position" content="2" /></li><li class="sep"></li><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$this->wpis_nazwa.'</b><meta itemprop="position" content="3" /></li>';

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
                'strona_nazwa' => $pageName,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName.' - '.$this->wpis_nazwa,
                'seo_tytul' => $this->meta_title,
                'seo_opis' => $this->meta_opis,
                'seo_slowa' => $this->meta_slowa,
                'strona_id' => $this->page_id,
                'strona' => $page,
                'wpis' => $wpis,
                'boksy' => $boksy,
                'pageclass' => $this->page_class,
                'breadcrumbs' => $breadcrumbs
            );
            $this->view->assign($array);

        }
    }
}