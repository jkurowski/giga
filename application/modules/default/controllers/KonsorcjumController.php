<?php

class Default_KonsorcjumController extends kCMS_Site
{
    private $page_class;
    private $page_id;
    private $inlineModel;
    private $meta_title;
    private $meta_slowa;
    private $meta_opis;

    public function preDispatch() {
        $this->page_id = 6;
        $this->page_class = 'konsorcjum';
        $this->inlineModel = new Model_InlineModel();
    }

    public function indexAction() {
        $this->_helper->layout->setLayout('page');
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $page = $db->fetchRow($db->select()->from('strony')->where('id = ?', $this->page_id));

        if(!$page) {
            errorPage();
        } else {

            $array = array(
                'inline' => $this->inlineModel->getInlineList(2),
                'strona_nazwa' => 'GIGA Construction',
                'strona_h1' => 'GIGA Construction',
                'strona_tytul' => ' - GIGA Construction',
                'seo_tytul' => $page->meta_tytul,
                'seo_opis' => $page->meta_opis,
                'seo_slowa' => $page->meta_slowa,
                'strona_id' => $this->page_id,
                'strona' => $page,
                'menutag' => 'oferta',
                'pageclass' => $this->page_class
            );
            $this->view->assign($array);

        }
    }
}