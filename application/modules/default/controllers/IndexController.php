<?php
class Default_IndexController extends kCMS_Site
{

    private $inlineModel;

    public function preDispatch() {
        $this->inlineModel = new Model_InlineModel();
    }

    public function indexAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $offerModel = new Model_OfferModel();
        $offer = $offerModel->getAll(1);

        $slider = $db->fetchAll($db->select()->from('slider')->order('sort ASC'));
        $slider_ustawienia = $db->fetchRow($db->select()->from('slider_ustawienia'));

        $array = array(
            'inline' => $this->inlineModel->getInlineList(1),
            'offer' => $offer,
            'slider' => $slider,
            'slider_ustawienia' => $slider_ustawienia
        );
        $this->view->assign($array);
    }

    public function menuAction() {
        $this->_helper->layout->setLayout('page');
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        $tag = $this->getRequest()->getParam('tag');
        $page = $this->view->strona = $db->fetchRow($db->select()->from('strony')->where('uri = ?', $tag));

        if(!$page) {

            $front = Zend_Controller_Front::getInstance();
            $request = $front->getRequest();

            $request->setModuleName('default');
            $request->setControllerName('error');
            $request->setActionName('error');
            $this->getResponse()->setHttpResponseCode(404)->setRawHeader('HTTP/1.1 404 Not Found');
            $this->view->nofollow = 1;
            $this->view->strona_nazwa = 'Strona nie została znaleziona - błąd 404';
            $this->view->seo_tytul = 'Strona nie została znaleziona - błąd 404';
        } else {

            $master = explode("/", $page->uri);
            //echo $master[0];
            $this->getRequest()->setParam('parenttag', $master[0]);
            $this->getRequest()->setParam('sitetag', $master[0]);
            $this->getRequest()->setParam('siteid', $page->id);
            $this->getRequest()->setParam('uri', $page->uri);
            $this->getRequest()->setParam('tag', $page->tag);
            $this->view->parenttag = $master[0];

            $this->view->strona_nazwa = $page->nazwa;
            $this->view->strona_h1 = $page->nazwa;
            $this->view->strona_tytul = ' - '.$page->nazwa;
            $this->view->seo_tytul = $page->meta_tytul;
            $this->view->seo_opis = $page->meta_opis;
            $this->view->seo_slowa = $page->meta_slowa;

            $this->view->strona_id = $page->id;
            $this->view->pageclass =  'menu-page';

        }
    }
}