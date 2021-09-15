<?php

class Default_RealizacjeController extends kCMS_Site
{
    private $page_class;
    private $page_id;
    private $meta_title;
    private $meta_slowa;
    private $meta_opis;

    public function preDispatch() {
        $this->page_id = 2;
        $this->page_class = ' realizacje-page';
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

            $tag = $this->getRequest()->getParam('category');
//
//            $query = $db->select()->from('realizacje')->order('sort ASC');
//
//            if($tag) {
//                $query->where('category =?', $tag);
//            }
//            $query->where('status =?', 1);
//            $realizacje = $db->fetchAll($query);
//

            $portfolioModel = new Model_PortfolioModel();
            $realizacje = $portfolioModel->getAll($tag);

            $array = array(
                'strona_nazwa' => $pageName,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName,
                'seo_tytul' => $this->meta_title,
                'seo_opis' => $this->meta_opis,
                'seo_slowa' => $this->meta_slowa,
                'strona_id' => $this->page_id,
                'strona' => $page,
                'realizacje' => $realizacje,
                'menutag' => 'realizacje',
                'breadcrumbs' => $breadcrumbs,
                'pageclass' => $this->page_class
            );
            $this->view->assign($array);

        }
    }


    public function showAction() {
        $this->_helper->layout->setLayout('page');
        try {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_Db::FETCH_OBJ);
        } catch (Zend_Exception $e) {
        }

        $tag = $this->getRequest()->getParam('slug');

//        $result = $db->select()
//            ->from('realizacje')
//            ->where('slug = ?', $tag);
//        $news = $db->fetchRow($result);

        $portfolioModel = new Model_PortfolioModel();
        $news = $portfolioModel->get($tag);

        if(!$news) {
            errorPage();
            $array = array(
                'seo_tytul' => "Strona nie została znaleziona - błąd 404",
                'strona_nazwa' => "Błąd 404",
                'nofollow' => 1,
            );
            $this->view->assign($array);
        } else {

            $pageModel = new Model_MenuModel();
            $page = $pageModel->getPageById($this->page_id);

            if(!$page) {
                errorPage();
                $array = array(
                    'seo_tytul' => "Strona nie została znaleziona - błąd 404",
                    'strona_nazwa' => "Błąd 404",
                    'nofollow' => 1,
                );
                $this->view->assign($array);
            }

            $locale = Zend_Registry::get('Zend_Locale')->getLanguage();

            if($locale <> 'pl') {
                $lang = $locale;
                $news_title = json_decode($news->json)->nazwa;
            } else {
                $lang = 'pl';
                $news_title = $news->nazwa;
            }

            $pageName = (isset($page->nazwa)) ? $page->nazwa : json_decode($page->json)->nazwa;
            $breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="/'.$lang.'/realizacje/"><span itemprop="name">'.$pageName.'</span></a><meta itemprop="position" content="2" /></li><li class="sep"></li><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$news_title.'</b><meta itemprop="position" content="3" /></li>';

            $array = array(
                'pageclass' => $this->page_class,
                'strona_nazwa' => $pageName,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName.' - '.$news_title,
                'seo_tytul' => $this->meta_title,
                'seo_opis' => $this->meta_opis,
                'seo_slowa' => $this->meta_slowa,
                'breadcrumbs' => $breadcrumbs,
                'menutag' => 'realizacje',
                'news' => $news,
                'strona' => $page
            );
            $this->view->assign($array);
        }
    }
}