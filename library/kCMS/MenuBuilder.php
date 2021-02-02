<?php
// Klasa zarzadzania stronami w kCMS - wersja z jezykami
class kCMS_MenuBuilder {
    var $items = array();
    var $html = array();
	var $query;

	function __construct() {
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

		$this->lang = $request->getParam('jezyk');
		$controller = $request->getModuleName();
		
		if($controller <> 'admin') {
			$db = Zend_Registry::get('db');
			$db->setFetchMode(Zend_Db::FETCH_ASSOC);
			$select = $db->select()
			->from(array('s' => 'strony'), array('id', 'id_parent', 'tag_parent', 'nazwa', 'link', 'sort', 'typ', 'tag', 'menu', 'data', 'lock', 'tekst', 'meta_tytul', 'uri', 'link_target'))
			->order('s.sort ASC');
			$query = $db->fetchAll($select);
			$this->query = $query;
		}
	} 

    function get_menu_items() {
        return $this->query;
    }

	function active($menu_tag, $site_tag) {
		if ($menu_tag == $site_tag) {
			return ' class="active"';
		}
	}
	
    function type_link($link,$baseUrl) {
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		if(preg_match($reg_exUrl, $link, $url)) {
			return array('link' => $link);
  		} else {
		
			if($link[0] == '#'){
				return array('link' => '#');
			} else {
				return array('link' => $baseUrl.'/'.$link);
			}
		}
    }
	
	// Menu na stronie
    function get_menu_html() {
		$query = $this->get_menu_items();
		$html = '';
		$root_id = 0;

        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
        $this->tag = $request->getParam('tag');
        $this->parenttag = $request->getParam('sitetag');
        $parentTag = $request->getParam('parenttag');
		$this->baseUrl = $request->getBaseUrl();

		if($this->parenttag) {$tag = $this->parenttag; } else {$tag = $this->tag;}
		
		//$html .= '<ul class="none mainmenu">';
        //$html .= '<li><a href="'.$this->baseUrl.'/">Strona główna</a></li>';
		foreach ( $query as $item )
			$children[$item['id_parent']][] = $item;
			$loop = !empty($children[$root_id]);

                $parent = $root_id;
                $parent_stack = array();

                while ( $loop && ( ( $option = each( $children[$parent] ) ) || ( $parent > $root_id ) ) )
                {
                        if ( $option === false )
                        {
                                $parent = array_pop( $parent_stack );
                                $html .= str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 ) . '</ul>';
                                $html .= str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 - 1 ) . '</li>';
                        }
                        elseif ( !empty( $children[$option['value']['id']] ) )
                        {
                                $tab = str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 - 1 );

								if ($option['value']['menu'] == 1 || $option['value']['menu'] == 3) {
									if ($option['value']['typ'] == 0) {

										$html .= sprintf(
												'%1$s<li%2$s><a href="%3$s/" class="gotsub">%4$s</a>',
												$tab,   // %1$s = tabulation
												$this->active($option['value']['tag'], $tag),   // %2$s = active position
												$this->baseUrl.'/'.$option['value']['uri'],   // %2$s = link (URL)
												$option['value']['nazwa']   // %3$s = title
										);
									} else {

										if($option['value']['link_target']) {$target = ' target="'.$option['value']['link_target'].'"';}else{$target='';}
										$link = $this->type_link($option['value']['uri'],$this->baseUrl);
										
										$html .= sprintf(
												'%1$s<li%2$s><a href="%3$s"%4$s class="gotsub">%5$s</a>',
												$tab,   // %1$s = tabulation
												$this->active($option['value']['tag'], $tag),   // %2$s = active position
												$link['link'],   // %3$s = link (URL)
												$target,   // %4$s = link (TARGET)
												$option['value']['nazwa']   // %5$s = title
										);
									}
								}
								
								
                                $html .= $tab . "\t" . '<ul class="submenu list-unstyled">';
                                array_push( $parent_stack, $option['value']['id_parent'] );
                                $parent = $option['value']['id'];
                        }
                        else
							
								if ($option['value']['menu'] == 1 || $option['value']['menu'] == 3) {
									if ($option['value']['typ'] == 0) {

										$html .= sprintf(
												'%1$s<li%2$s><a href="%3$s/">%4$s</a></li>',
												str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 - 1 ),   // %1$s = tabulation
												$this->active($option['value']['tag'], $tag),   // %2$s = active position
												$this->baseUrl.'/'.$option['value']['uri'],   // %2$s = link (URL)
												$option['value']['nazwa']   // %3$s = title
										);
									} else {

										if($option['value']['link_target']) {$target = ' target="'.$option['value']['link_target'].'"';}else{$target='';}
										$link = $this->type_link($option['value']['uri'],$this->baseUrl);

										$html .= sprintf(
												'%1$s<li%2$s><a href="%3$s"%4$s>%5$s</a></li>',
												str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 - 1 ),   // %1$s = tabulation
												$this->active($option['value']['tag'], $tag),   // %2$s = active position
												$link['link'],   // %3$s = link (URL)
												$target,   // %4$s = link (TARGET)
												$option['value']['nazwa']   // %5$s = title
										);
									}
								}
		
                }
			//$html .= '</ul>';
			return $html;	
	}
	
	// Sprawdzenie typu strony
    function what_type($check) {
		if($check == '3'){
			return 'Link';
		}
		if($check == '0'){
			return 'Strona';
		}
    }
	
	// Menu stron w adminie
	function get_adminmenu_html() {
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_ASSOC);
		$select = $db->select()
		->from(array('s' => 'strony'), array('id', 'id_parent', 'nazwa', 'link', 'sort', 'typ', 'tag', 'menu', 'tag_parent', 'data', 'lock'))
		->order('s.sort ASC');
		$lamka = $db->fetchAll($select);
	
		$query = $lamka;
		$html = '';
		$parent = 0;
		$parent_stack = array();
		$children = array();

		$front = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();
		$this->tag = $request->getParam('tag');
		$this->parenttag = $request->getParam('sitetag');
		$this->baseUrl = $request->getBaseUrl();

		foreach ( $query as $item )
		$children[$item['id_parent']][] = $item;
		while ( ( $option = each( $children[$parent] ) ) || ( $parent > 0 ) ) {
			if ( !empty( $option ) ) {
				if ( !empty( $children[$option['value']['id']] ) ) {
					if($option['value']['typ'] == 0 ) {
						$edycja = '<a href="'.$this->baseUrl.'/admin/strony/edytuj/id/'.$option['value']['id'].'/" class="actionBtn tip btnEdit"  title="Edytuj stronę" data-placement="top-right"></a>'; 
					} else { 
						$edycja = '<a href="'.$this->baseUrl.'/admin/strony/edytuj_link/id/'.$option['value']['id'].'/" class="actionBtn tip btnEdit"  title="Edytuj link" data-placement="top-right"></a>';
					}

					if($option['value']['lock'] == 0 ) {
						$usun = ' <a href="'.$this->baseUrl.'/admin/strony/usun/id/'.$option['value']['id'].'/" class="actionBtn tip btnDelete confirm" title="Usuń stronę" data-placement="top-right"></a>';
					} else {
						$usun = ' <a href="#" class="actionBtn tip btnLock" title="Zablokowana" data-placement="top-right"></a>';
					}

					if($option['value']['menu'] == 0) {
						$status = "<span class=\"offline tip\" title=\"Pozycja ukryta w menu\"></span>";
					} else {
						$status = "<span class=\"online tip\" title=\"Pozycja widoczna w menu\"></span>";
					}

					if(count($parent_stack) > 0) {$parentstyle = ' class="parent"';}else{$parentstyle = '';}

					$html .= '<tr id="recordsArray_'.$option['value']['id'].'">';
					$html .= '<td style="padding-left:'.(count($parent_stack) * 12 + 12).'px"'.$parentstyle.'>'.$option['value']['nazwa'].'</td>';
					$html .= '<td class="center">'.$option['value']['sort'].'</td>';
					$html .= '<td>'.$this->what_type($option['value']['typ']).'</td>';
					$html .= '<td>'.$status.'</td>';
					$html .= '<td>'.$option['value']['data'].'</td>';
					$html .= '<td class="right">'.$edycja.$usun.'</td>';
					$html .= '</tr>';

					array_push( $parent_stack, $parent );
					$parent = $option['value']['id'];

				} else {

					if($option['value']['typ'] == 0 ) {
						$edycja2 = '<a href="'.$this->baseUrl.'/admin/strony/edytuj/id/'.$option['value']['id'].'/" class="actionBtn tip btnEdit"  title="Edytuj stronę" data-placement="top-right"></a>'; 
					} else { 
						$edycja2 = '<a href="'.$this->baseUrl.'/admin/strony/edytuj_link/id/'.$option['value']['id'].'/" class="actionBtn tip btnEdit"  title="Edytuj link" data-placement="top-right"></a>';
					}

					if($option['value']['lock'] == 0 ) {
						$usun2 = ' <a href="'.$this->baseUrl.'/admin/strony/usun/id/'.$option['value']['id'].'/" class="actionBtn tip btnDelete confirm" title="Usuń stronę" data-placement="top-right"></a>';
					} else {
						$usun2 = ' <a href="#" class="actionBtn tip btnLock" title="Zablokowana" data-placement="top-right"></a>';
					}

					if($option['value']['menu'] == 0) {
						$status2 = "<span class=\"offline tip\" title=\"Pozycja ukryta w menu\"></span>";
					} else {
						$status2 = "<span class=\"online tip\" title=\"Pozycja widoczna w menu\"></span>";
					}

					if(count($parent_stack) > 0) {$childstyle = ' class="child"';}else{$childstyle = '';}
					$html .= '<tr id="recordsArray_'.$option['value']['id'].'">';
					$html .= '<td'.$childstyle.' style="padding-left:'.(count($parent_stack) * 12 + 12).'px">'.$option['value']['nazwa'].'</td>';
					$html .= '<td class="center">'.$option['value']['sort'].'</td>';
					$html .= '<td>'.$this->what_type($option['value']['typ']).'</td>';
					$html .= '<td>'.$status2.'</td>';
					$html .= '<td>'.$option['value']['data'].'</td>';
					$html .= '<td class="right">'.$edycja2.$usun2.'</td>';
					$html .= '</tr>';
				}
			} else {
				$parent = array_pop( $parent_stack );
			}
		}
		return $html;	
	}

	// Boczne menu
	function sidemenu() {
		$query = $this->get_menu_items();
		$html = '';
		
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
        $tag = $request->getParam('tag');
        $parentTag = $request->getParam('parenttag');
		$this->baseUrl = $request->getBaseUrl();

		if($parentTag) {$parentTag = $parentTag;} else {$parentTag = $tag;}
		
		foreach ($query as $value) {
			if($value['tag'] == $parentTag) { $firstTag = $value['tag']; $firstNazwa = $value['nazwa']; $root_id = $value['id']; $firstLink = $value['link'];}
			if($value['tag'] == $tag && !$parentTag) { $root_id = $value['id'];} //Dla obecnej pierwszej strony wszystkie dzieci
		}
		
		foreach ( $query as $item )
			$children[$item['id_parent']][] = $item;
			$loop = !empty($children[$root_id]);

                $parent = $root_id;
                $parent_stack = array();

				if($loop) {
					if($firstLink <> '#') {
					$html .= '<li'. $this->active($tag , $firstTag).'><a href="'.$this->baseUrl.'/'.$firstTag. '/">'. $firstNazwa.'</a></li>';
					}
				}
				
                while ( $loop && ( ( $option = each( $children[$parent] ) ) || ( $parent > $root_id ) ) )
                {
                        if ( $option === false )
                        {
                                $parent = array_pop( $parent_stack );
                                $html .= str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 ) . '</ul>';
                                $html .= str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 - 1 ) . '</li>';
                        }
                        elseif ( !empty( $children[$option['value']['id']] ) )
                        {
                                $tab = str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 - 1 );

								if ($option['value']['menu'] <> 3) {
									if ($option['value']['typ'] == 0) {
									
										$html .= sprintf(
												'%1$s<li%2$s><a href="%3$s/">%4$s</a>',
												$tab,   // %1$s = tabulation
												$this->active($option['value']['tag'], $tag),   // %2$s = active position
												$this->baseUrl.'/'.$option['value']['uri'],   // %2$s = link (URL)
												$option['value']['nazwa']   // %3$s = title
										);
									} else {

										if($option['value']['link_target']) {$target = ' target="'.$option['value']['link_target'].'"';}else{$target='';}
										$link = $this->type_link($option['value']['uri'],$this->baseUrl);
										
										$html .= sprintf(
												'%1$s<li%2$s><a href="%3$s"%4$s>%5$s</a></li>',
												str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 - 1 ),   // %1$s = tabulation
												$this->active($option['value']['tag'], $tag),   // %2$s = active position
												$link['link'],   // %3$s = link (URL)
												$target,   // %4$s = link (TARGET)
												$option['value']['nazwa']   // %5$s = title
										);
									}
								}
								
								
                                $html .= $tab . "\t" . '<ul class="sidesubmenu cleanul">';
                                array_push( $parent_stack, $option['value']['id_parent'] );
                                $parent = $option['value']['id'];
                        }
                        else
							
								if ($option['value']['menu'] <> 3) {
									if ($option['value']['typ'] == 0) {
									
										$html .= sprintf(
												'%1$s<li%2$s><a href="%3$s/">%4$s</a></li>',
												str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 - 1 ),   // %1$s = tabulation
												$this->active($option['value']['tag'], $tag),   // %2$s = active position
												$this->baseUrl.'/'.$option['value']['uri'],   // %2$s = link (URL)
												$option['value']['nazwa']   // %3$s = title
										);
									} else {

										if($option['value']['link_target']) {$target = ' target="'.$option['value']['link_target'].'"';}else{$target='';}
										$link = $this->type_link($option['value']['uri'],$this->baseUrl);
										
										$html .= sprintf(
												'%1$s<li%2$s><a href="%3$s"%4$s>%5$s</a></li>',
												str_repeat( "\t", ( count( $parent_stack ) + 1 ) * 2 - 1 ),   // %1$s = tabulation
												$this->active($option['value']['tag'], $tag),   // %2$s = active position
												$link['link'],   // %3$s = link (URL)
												$target,   // %4$s = link (TARGET)
												$option['value']['nazwa']   // %5$s = title
										);
									}
								}
		
                }

			return $html;	
    }

	// Generowanie URI do bazy
	function urigenerate($id){
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_Db::FETCH_ASSOC);
		$select = $db->select()
		->from(array('s' => 'strony'), array('id', 'id_parent', 'nazwa', 'link', 'sort', 'typ', 'tag', 'menu', 'tag_parent', 'data', 'lock'))
		->order('s.sort ASC');
		$data = $db->fetchAll($select);
		
		$crumbs = Array();
		$c = count($data);

		do {
			$found = false;
			for($i = 0; $i<$c; ++$i){
				if($data[$i]['id'] == $id){

					if($data[$i]['typ'] == 0){
						$url = $data[$i]['tag'];
						array_unshift($crumbs, empty($crumbs)?($data[$i]['tag']):($url));
					}  else {
						$url = $data[$i]['link'];
						array_unshift($crumbs, empty($crumbs)?($data[$i]['link']):($url));
					}
					$id = $data[$i]['id_parent'];
					$found = true;
					break;
				}
			}
		} while ($id != 0 AND $found);        
		return implode('/', $crumbs);
	}
	
	// Chlebek, rogalik itd
	function breadcrumbs($id){
			$data = $this->get_menu_items();
			$crumbs = Array();
			$c = count($data);

			$front = Zend_Controller_Front::getInstance();
			$request = $front->getRequest();
			$this->baseUrl = $request->getBaseUrl();

			//<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item"><span itemprop="name"></span></a><meta itemprop="position" content="1" /></li>
			
			do {
			$found = false;
					for($i = 0; $i<$c; ++$i){
							if($data[$i]['id'] == $id){

								if($data[$i]['typ'] == 0){
									$url = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="'.$this->baseUrl.'/'.$data[$i]['uri'].'/"><span itemprop="name">'.$data[$i]['nazwa'].'</span></a><meta itemprop="position" content="'.$i.'" /></li>';
									}  else {
									$url = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="'.$data[$i]['uri'].'"><span itemprop="name">'.$data[$i]['nazwa'].'</span></a><meta itemprop="position" content="'.$i.'" /></li>';
									}
                                array_unshift($crumbs, empty($crumbs)?                                       
								'<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><b itemprop="name">'.($data[$i]['nazwa']).'</b><meta itemprop="position" content="'.$i.'" /></li>':                                        
								($url));
								$id = $data[$i]['id_parent'];
                                $found = true;
                                break;
							}
					}
			} while ($id != 0 AND $found);        
			return implode('<li class="sep"></li>', $crumbs);
	}

	//Przy zmianie nazwy generowanie linkow dla dzieci
	function mapTree($root_id)
	{
			$db = Zend_Registry::get('db');
			$db->setFetchMode(Zend_Db::FETCH_ASSOC);
			$select = $db->select()
			->from(array('s' => 'strony'), array('id', 'id_parent', 'nazwa', 'typ', 'tag', 'link'))
			->order('s.sort ASC');
			$pro = $db->fetchAll($select);
		   
			foreach ( $pro as $item )
			$children[$item['id_parent']][] = $item;
			$loop = !empty( $children[$root_id] );
			$parent = $root_id;
			$parent_stack = array();

			while ( $loop && ( ( $option = each( $children[$parent] ) ) || ( $parent > $root_id ) ) )
			{
					if ( $option === false )
					{
							$parent = array_pop( $parent_stack );
					}
					elseif ( !empty( $children[$option['value']['id']] ) )
					{

							$id_element = $option['value']['id'];
							$id_up = $option['value']['id'];
							$crumbs = Array();
							$c = count($pro);

							do {
								$found = false;
								for($i = 0; $i<$c; ++$i){
									if($pro[$i]['id'] == $id_element){

										if($pro[$i]['typ'] == 0){
											$url = $pro[$i]['tag'];
											array_unshift($crumbs, empty($crumbs)?($pro[$i]['tag']):($url));
										}  else {
											$url = $pro[$i]['link'];
											array_unshift($crumbs, empty($crumbs)?($pro[$i]['link']):($url));
										}
										$id_element = $pro[$i]['id_parent'];
										$found = true;
										break;
									}
								}
							} while ($id_element != 0 AND $found);        
							$uri = implode('/', $crumbs);
		
							$dataUri = array('uri' => $uri);
							$db->update('strony', $dataUri, 'id = '.$id_up);

							array_push( $parent_stack, $option['value']['id_parent'] );
							$parent = $option['value']['id'];
					}
					else {

							$id_element = $option['value']['id'];
							$id_up = $option['value']['id'];
							$crumbs = Array();
							$c = count($pro);

							do {
								$found = false;
								for($i = 0; $i<$c; ++$i){
									if($pro[$i]['id'] == $id_element){

										if($pro[$i]['typ'] == 0){
											$url = $pro[$i]['tag'];
											array_unshift($crumbs, empty($crumbs)?($pro[$i]['tag']):($url));
										}  else {
											$url = $pro[$i]['link'];
											array_unshift($crumbs, empty($crumbs)?($pro[$i]['link']):($url));
										}
										$id_element = $pro[$i]['id_parent'];
										$found = true;
										break;
									}
								}
							} while ($id_element != 0 AND $found);        
							$uri = implode('/', $crumbs);
		
							$dataUri = array('uri' => $uri);
							$db->update('strony', $dataUri, 'id = '.$id_up);

					}
			}
	}

	// Jak usuwasz rodzicow to usun tez dzieci
	function deletemapTree($root_id)
	{
			$db = Zend_Registry::get('db');
			$db->setFetchMode(Zend_Db::FETCH_ASSOC);
			$select = $db->select()
			->from(array('s' => 'strony'), array('id', 'id_parent', 'nazwa', 'typ', 'tag', 'link'))
			->order('s.sort ASC');
			$pro = $db->fetchAll($select);
		   
			foreach ( $pro as $item )
			$children[$item['id_parent']][] = $item;
			$loop = !empty( $children[$root_id] );
			$parent = $root_id;
			$parent_stack = array();

			while ( $loop && ( ( $option = each( $children[$parent] ) ) || ( $parent > $root_id ) ) )
			{
					if ( $option === false )
					{
							$parent = array_pop( $parent_stack );
					}
					elseif ( !empty( $children[$option['value']['id']] ) )
					{

							$id_element = $option['value']['id'];
			
							$where = $db->quoteInto('id = ?', $id_element);
							$db->delete('strony', $where);
							
							$tlumaczenie = $db->quoteInto('id_page = ?', $id_element);
							$db->delete('strony_tlumaczenie', $tlumaczenie);

							$strony = $db->fetchRow($db->select()->from('strony')->where('id = ?', $id_element));
							unlink(FILES_PATH."/background/".$strony->plik);
	
							array_push( $parent_stack, $option['value']['id_parent'] );
							$parent = $option['value']['id'];
					}
					else {

							$id_element = $option['value']['id'];
			
							$where = $db->quoteInto('id = ?', $id_element);
							$db->delete('strony', $where);
							
							$tlumaczenie = $db->quoteInto('id_page = ?', $id_element);
							$db->delete('strony_tlumaczenie', $tlumaczenie);

							$strony = $db->fetchRow($db->select()->from('strony')->where('id = ?', $id_element));
							unlink(FILES_PATH."/background/".$strony->plik);
	
					}
			}
	}

}