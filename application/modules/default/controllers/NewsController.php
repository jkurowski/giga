<?php

class Default_NewsController extends kCMS_Site
{

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

			$page = $this->view->strona = $db->fetchRow($db->select()->from('strony')->where('id = ?', $this->page_id));

			if(!$page) {
                errorPage();
                $array = array(
                    'seo_tytul' => "Strona nie została znaleziona - błąd 404",
                    'strona_nazwa' => "Błąd 404",
                    'nofollow' => 1,
                );
                $this->view->assign($array);
			} else {

				$breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$page->nazwa.'</b><meta itemprop="position" content="2" /></li>';

				$result = $db->select()
				->from(array('n' => 'news'), array('id', 'plik', 'data', 'status', 'wprowadzenie', 'tytul', 'tag'))
				->where('n.status =?', 1)
				->order('n.data DESC');
				
				$pageNo = $this->_getParam('strona', 1);
                $paginator = Zend_Paginator::factory($result);
                $paginator->setItemCountPerPage($this->news_count_pre_page);
                $paginator->setCurrentPageNumber($pageNo);

                $array = array(
                    'nobottom' => 1,
                    'pageclass' => $this->page_class,
                    'strona_id' => $this->page_id,
                    'strona_nazwa' => $page->nazwa,
                    'strona_h1' => $page->nazwa,
                    'strona_tytul' => ' - '.$page->nazwa,
                    'seo_tytul' => $page->meta_tytul,
                    'seo_opis' => $page->meta_opis,
                    'seo_slowa' => $page->meta_slowa,
                    'breadcrumbs' => $breadcrumbs,
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
			 
			$result = $db->select()
			->from('news')
			->where('status =?', 1)
			->where('tag = ?', $tag);
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
			
				$page = $db->fetchRow($db->select()->from('strony')->where('id = ?', $this->page_id));
                if(!$page) {
                    errorPage();
                    $array = array(
                        'seo_tytul' => "Strona nie została znaleziona - błąd 404",
                        'strona_nazwa' => "Błąd 404",
                        'nofollow' => 1,
                    );
                    $this->view->assign($array);
                }

				$breadcrumbs = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="'.$this->baseUrl.'/'.$page->tag.'/"><span itemprop="name">'.$page->nazwa.'</span></a><meta itemprop="position" content="2" /></li><li class="sep"></li><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="item">'.$news->tytul.'</b><meta itemprop="position" content="3" /></li>';

                $array = array(
                    'pageclass' => $this->page_class,
                    'strona_nazwa' => $page->nazwa,
                    'strona_h1' => $page->nazwa,
                    'strona_tytul' => ' - '.$page->nazwa.' - '.$news->tytul,
                    'seo_tytul' => $page->meta_tytul,
                    'seo_opis' => $page->meta_opis,
                    'seo_slowa' => $page->meta_slowa,
                    'share' => 1,
                    'share_tytul' => $news->tytul,
                    'share_desc' => $news->wprowadzenie,
                    'share_image' => 'http://testy.4dl.pl/realinvestdev/files/news/share/'.$news->plik,
                    'share_url' => 'http://testy.4dl.pl/realinvestdev/pl/'.$page->tag.'/'.$news->tag.'/',
                    'breadcrumbs' => $breadcrumbs,
                    'news' => $news
                );
                $this->view->assign($array);


				
			}
	   }
}