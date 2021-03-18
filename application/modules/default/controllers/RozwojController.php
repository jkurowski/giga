<?php

class Default_RozwojController extends kCMS_Site
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
        $this->page_id = 8;
        $this->page_class = '';
    }

    public function indexAction() {
        $this->_helper->layout->setLayout('page');
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $page = $db->fetchRow($db->select()->from('strony')->where('id = ?', $this->page_id));

        if(!$page) {
            errorPage();
        } else {
            $boksy = $db->fetchAll($db->select()->from('boksy')->order('sort ASC'));

            $array = array(
                'strona_nazwa' => $page->nazwa,
                'strona_h1' => $page->nazwa,
                'strona_tytul' => ' - '.$page->nazwa,
                'seo_tytul' => $page->meta_tytul,
                'seo_opis' => $page->meta_opis,
                'seo_slowa' => $page->meta_slowa,
                'strona_id' => $this->page_id,
                'strona' => $page,
                'boksy' => $boksy,
                'pageclass' => $this->page_class
            );
            $this->view->assign($array);

        }
    }

    public function showAction() {
        $this->_helper->layout->setLayout('page');
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $page = $db->fetchRow($db->select()->from('strony')->where('id = ?', $this->page_id));

        if(!$page) {
            errorPage();
        } else {

            $slug = $this->_request->getParam('slug');
            $wpis = $db->fetchRow($db->select()->from('boksy')->where('slug = ?', $slug));
            $boksy = $db->fetchAll($db->select()->from('boksy')->order('sort ASC'));

            $breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="'.$this->baseUrl.'/'.$page->tag.'/"><span itemprop="name">'.$page->nazwa.'</span></a><meta itemprop="position" content="2" /></li><li class="sep"></li><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$wpis->nazwa.'</b><meta itemprop="position" content="3" /></li>';

            $array = array(
                'strona_nazwa' => $page->nazwa,
                'strona_h1' => 'Kapitał ludzki',
                'strona_tytul' => ' - '.$page->nazwa,
                'seo_tytul' => $page->meta_tytul,
                'seo_opis' => $page->meta_opis,
                'seo_slowa' => $page->meta_slowa,
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