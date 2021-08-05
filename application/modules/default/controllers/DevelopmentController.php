<?php

class Default_DevelopmentController extends kCMS_Site
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
        $this->page_id = 10;
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

            $inwestycje = $db->fetchAll($db->select()->from('inwestycje')->order('sort ASC'));

            $array = array(
                'strona_nazwa' => $page->nazwa,
                'strona_h1' => $page->nazwa,
                'strona_tytul' => ' - '.$page->nazwa,
                'seo_tytul' => $page->meta_tytul,
                'seo_opis' => $page->meta_opis,
                'seo_slowa' => $page->meta_slowa,
                'strona_id' => $this->page_id,
                'strona' => $page,
                'inwestycje' => $inwestycje,
                'pageclass' => $this->page_class
            );
            $this->view->assign($array);

        }
    }
}