<?php

class Default_AboutController extends kCMS_Site
{

    private $page_class;
    private $page_id;
    private $inlineModel;

    public function preDispatch() {
        $this->page_id = 7;
        $this->page_class = '';
        $this->inlineModel = new Model_InlineModel();
    }

    public function indexAction() {
        $this->_helper->layout->setLayout('page');
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $page = $db->fetchRow($db->select()->from('strony')->where('id = ?', $this->page_id));
        $opinie = $db->fetchAll($db->select()->from('opinie')->where('place_id = ?', 2)->order('sort ASC'));
        $slider = $db->fetchAll($db->select()->from('galeria_zdjecia')->order('sort ASC')->where('id_gal =?', 31));

        if(!$page) {
            errorPage();
        } else {

            $array = array(
                'inline' => $this->inlineModel->getInlineList(4),
                'strona_nazwa' => $page->nazwa,
                'strona_h1' => $page->nazwa,
                'strona_tytul' => ' - '.$page->nazwa,
                'seo_tytul' => $page->meta_tytul,
                'seo_opis' => $page->meta_opis,
                'seo_slowa' => $page->meta_slowa,
                'strona_id' => $this->page_id,
                'strona' => $page,
                'opinie' => $opinie,
                'slider' => $slider,
                'pageclass' => $this->page_class
            );
            $this->view->assign($array);

        }
    }
}