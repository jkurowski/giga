<?php

class Default_MisjaController extends kCMS_Site
{

    private $page_class;
    private $page_id;
    private $inlineModel;
    private $meta_title;
    private $meta_slowa;
    private $meta_opis;

    public function preDispatch() {
        $this->page_id = 9;
        $this->page_class = 'misja';
        $this->inlineModel = new Model_InlineModel();
    }

    public function indexAction() {
        $this->_helper->layout->setLayout('page');
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $pageModel = new Model_MenuModel();
        $page = $pageModel->getPageById($this->page_id);

        //$opinie = $db->fetchAll($db->select()->from('opinie')->where('place_id = ?', 1)->order('sort ASC'));

        if(!$page) {
            errorPage();
        } else {

            $reviewModel = new Model_ReviewModel();
            $opinie = $reviewModel->getAll(1);

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
                'inline' => $this->inlineModel->getInlineList(3),
                'strona_nazwa' => $pageName,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName,
                'seo_tytul' => $this->meta_title,
                'seo_opis' => $this->meta_opis,
                'seo_slowa' => $this->meta_slowa,
                'strona_id' => $this->page_id,
                'strona' => $page,
                'opinie' => $opinie,
                'menutag' => 'misja-i-wartosci',
                'pageclass' => $this->page_class,
                'breadcrumbs' => $breadcrumbs
            );
            $this->view->assign($array);

        }
    }
}