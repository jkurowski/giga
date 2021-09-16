<?php

class Default_NewsController extends kCMS_Site
{
    private $meta_title;
    private $meta_slowa;
    private $meta_opis;

    public function preDispatch() {
        $this->page_id = 5;
        $this->page_class = 'news-page';
        $this->news_count_pre_page = 6;

    }

    public function indexAction() {
        $this->_helper->layout->setLayout('page');
        try {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_Db::FETCH_OBJ);
        } catch (Zend_Exception $e) {
        }

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
        } else {

            $pageName = (isset($page->nazwa)) ? $page->nazwa : json_decode($page->json)->nazwa;
            $breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$pageName .'</b><meta itemprop="position" content="2" /></li>';

//				$result = $db->select()
//				->from(array('n' => 'news'), array('id', 'plik', 'data', 'status', 'wprowadzenie', 'tytul', 'tag'))
//				->where('n.status =?', 1)
//				->order('n.data DESC');


            $newsModel = new Model_NewsModel();
            $result = $newsModel->getAll();

            $pageNo = $this->_getParam('strona', 1);
            $paginator = Zend_Paginator::factory($result);
            $paginator->setItemCountPerPage($this->news_count_pre_page);
            $paginator->setCurrentPageNumber($pageNo);

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
                'pageclass' => $this->page_class,
                'strona_id' => $this->page_id,
                'strona_nazwa' => $pageName,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName,
                'seo_tytul' => $this->meta_title,
                'seo_opis' => $this->meta_opis,
                'seo_slowa' => $this->meta_slowa,
                'breadcrumbs' => $breadcrumbs,
                'menutag' => 'aktualnosci',
                'news' => $paginator,
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

        $tag = $this->getRequest()->getParam('newstag');
//
//			$result = $db->select()
//			->from('news')
//			->where('status =?', 1)
//			->where('tag = ?', $tag);
//			$news = $db->fetchRow($result);

        $newsModel = new Model_NewsModel();
        $news = $newsModel->get($tag);

        if(!$news) {
            errorPage();
            $array = array(
                'seo_tytul' => "Strona nie została znaleziona - błąd 404",
                'strona_nazwa' => "Błąd 404",
                'nofollow' => 1,
            );
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

            if(isset($news->tytul)) {
                $this->news_tytul = $news->tytul;
            } elseif (isset(json_decode($news->json)->tytul)) {
                $this->news_tytul = json_decode($news->json)->tytul;
            }

            $pageName = (isset($page->nazwa)) ? $page->nazwa : json_decode($page->json)->nazwa;
            $locale = Zend_Registry::get('Zend_Locale')->getLanguage();
            $breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="/'.$locale.'/aktualnosci/"><span itemprop="name">'.$pageName.'</span></a><meta itemprop="position" content="2" /></li><li class="sep"></li><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$this->news_tytul.'</b><meta itemprop="position" content="3" /></li>';

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
                'pageclass' => $this->page_class,
                'strona_nazwa' => $pageName,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName.' - '.$this->news_tytul,
                'seo_tytul' => $this->meta_title,
                'seo_opis' => $this->meta_opis,
                'seo_slowa' => $this->meta_slowa,
                'share' => 1,
                'share_tytul' => $this->news_tytul,
                'menutag' => 'aktualnosci',
                'share_desc' => (isset($news->wprowadzenie)) ? $news->wprowadzenie : json_decode($news->json)->wprowadzenie,
                'share_image' => 'http://giga.4dl-dev.pl/files/news/share/'.$news->plik,
                'share_url' => 'http://giga.4dl-dev.pl/'.$page->tag.'/'.$locale.'/'.(isset($news->tag)) ? $news->tag : json_decode($news->json)->tag.'/',
                'breadcrumbs' => $breadcrumbs,
                'news' => $news
            );


        }
        $this->view->assign($array);
    }
}