<?php

class Default_CovidController extends kCMS_Site
{
    private $page_id;
    private $meta_title;
    private $meta_slowa;
    private $meta_opis;

    public function preDispatch() {
        $this->page_id = 6;
    }

    public function indexAction() {
        $this->_helper->layout->setLayout('page');

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
                'pageclass' => ' covid',
                'strona_id' => $this->page_id,
                'strona_nazwa' => $pageName,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName,
                'seo_tytul' => $this->meta_title,
                'seo_opis' => $this->meta_opis,
                'seo_slowa' => $this->meta_slowa,
                'content' => (isset($page->tekst)) ? $page->tekst : json_decode($page->json)->tekst,
                'breadcrumbs' => $breadcrumbs
            );
            $this->view->assign($array);

        }
    }
}