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

        $pageModel = new Model_MenuModel();
        $page = $pageModel->getPageById($this->page_id);

        if(!$page) {
            errorPage();
        } else {
            $pageName = (isset($page->nazwa)) ? $page->nazwa : json_decode($page->json)->nazwa;
            $breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$pageName .'</b><meta itemprop="position" content="2" /></li>';

            //$inwestycje = $db->fetchAll($db->select()->from('inwestycje')->order('sort ASC'));

            $investmentModel = new Model_InvestmentModel();
            $inwestycje = $investmentModel->getAll();

            $array = array(
                'pageclass' => ' covid',
                'strona_id' => $this->page_id,
                'strona_h1' => $pageName,
                'strona_tytul' => ' - '.$pageName,
                'seo_tytul' => (isset($page->meta_tytul)) ? $page->meta_tytul : json_decode($page->json)->meta_tytul,
                'seo_opis' => (isset($page->meta_opis)) ? $page->meta_opis : json_decode($page->json)->meta_opis,
                'seo_slowa' => (isset($page->meta_slowa)) ? $page->meta_slowa : json_decode($page->json)->meta_slowa,
                'content' => (isset($page->tekst)) ? $page->tekst : json_decode($page->json)->tekst,
                'breadcrumbs' => $breadcrumbs,
                'inwestycje' => $inwestycje
            );
            $this->view->assign($array);

        }
    }
}