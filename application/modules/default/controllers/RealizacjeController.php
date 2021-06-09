<?php

class Default_RealizacjeController extends kCMS_Site
{
    /**
     * @var string
     */
    private $page_class;
    /**
     * @var int
     */
    private $page_id;

    public function preDispatch() {
        $this->page_id = 2;
        $this->page_class = ' realizacje-page';
    }

    public function indexAction() {
        $this->_helper->layout->setLayout('page');
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $page = $db->fetchRow($db->select()->from('strony')->where('id = ?', $this->page_id));

        if(!$page) {
            errorPage();
        } else {

            $tag = $this->getRequest()->getParam('category');

            $query = $db->select()->from('realizacje')->order('sort ASC');

            if($tag) {
                $query->where('category =?', $tag);
            }

            $realizacje = $db->fetchAll($query);

            $array = array(
                'strona_nazwa' => $page->nazwa,
                'strona_h1' => $page->nazwa,
                'strona_tytul' => ' - '.$page->nazwa,
                'seo_tytul' => $page->meta_tytul,
                'seo_opis' => $page->meta_opis,
                'seo_slowa' => $page->meta_slowa,
                'strona_id' => $this->page_id,
                'strona' => $page,
                'realizacje' => $realizacje,
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

        $result = $db->select()
            ->from('realizacje')
            ->where('slug = ?', $tag);
        $news = $db->fetchRow($result);

        if(!$news) {
            errorPage();
            $array = array(
                'seo_tytul' => "Strona nie została znaleziona - błąd 404",
                'strona_nazwa' => "Błąd 404",
                'nofollow' => 1,
            );
            $this->view->assign($array);
        } else {

            $page = $db->fetchRow($db->select()->from('strony')->where('id = ?', 2));
            if(!$page) {
                errorPage();
                $array = array(
                    'seo_tytul' => "Strona nie została znaleziona - błąd 404",
                    'strona_nazwa' => "Błąd 404",
                    'nofollow' => 1,
                );
                $this->view->assign($array);
            }

            $breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="'.$this->baseUrl.'/'.$page->tag.'/"><span itemprop="name">'.$page->nazwa.'</span></a><meta itemprop="position" content="2" /></li><li class="sep"></li><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$news->nazwa.'</b><meta itemprop="position" content="3" /></li>';

            $array = array(
                'pageclass' => $this->page_class,
                'strona_nazwa' => $page->nazwa,
                'strona_h1' => $page->nazwa,
                'strona_tytul' => ' - '.$page->nazwa.' - '.$news->nazwa,
                'seo_tytul' => $page->meta_tytul,
                'seo_opis' => $page->meta_opis,
                'seo_slowa' => $page->meta_slowa,
                'breadcrumbs' => $breadcrumbs,
                'news' => $news
            );
            $this->view->assign($array);
        }
    }
}