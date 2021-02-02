<?php
require_once 'Zend/Controller/Action.php';
abstract class kCMS_Admin extends Zend_Controller_Action {

    public function init() {

		$front = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();
		$this->view->paramsname = $request->getParams();
		$this->view->baseUrl = $this->baseUrl = $this->_request->getBaseUrl();
		$controller = $this->view->control = $request->getControllerName();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$db = Zend_Registry::get('db');

		$this->view->header = $db->fetchRow($db->select()->from('ustawienia'));
		
        function previewParser($string, $len) {
            $pattern_clear = array(
                '@(\[)(.*?)(\])@si',
                '@(\[/)(.*?)(\])@si'
            );

            $replace_clear = array(
                '',
                ''
            );

            $string = preg_replace($pattern_clear, $replace_clear, $string);
            if (strlen($string) > $len) {
                $result = substr($string, 0, $len) . '...';
            } else {
                $result = $string;
            }
            return $result;
        }
		
		function zlotowka($value) {
			
			if (preg_match("/zĹ‚/i", $value)) {
				$value = str_replace('zĹ‚', '', trim($value));
				$value = number_format($value , 0, ',', '.');
				$value = $value.' zł';
			} else {
				$value = $value;
			}
	
			return $value;
		}
		
		function zlotowka2($value) {
			
			if (preg_match("/zĹ‚/i", $value)) {
				$value = str_replace('zĹ‚', '', trim($value));
				$value = number_format($value , 0, ',', '.');
			} else {
				$value = number_format($value , 0, ',', '.');
				$value = $value;
			}
	
			return $value;
		}
		
        function status($status) {
            if($status == 1)
				{ $result = "<span class=\"online\"></span>"; }
			else
				{ $result = "<span class=\"offline\"></span>"; }
            return $result;
        }
		
		function zmiana($value) {
            $value = strtr($value, array('ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z', 'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'O', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z'));
            $value = str_replace(' ', '-', trim($value));
            $value = preg_replace('/[^a-zA-Z0-9\-_]/', '', (string) $value);
            $value = preg_replace('/[\-]+/', '-', $value);
            $value = stripslashes($value);
            return urlencode(strtolower($value));
		}
	
		function zmienobrazek($value) {
				$value = strtr($value, array('ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z', 'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'O', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z'));
				$value = preg_replace( "/[^a-z0-9\._]+/", "-", strtolower($value));
				$value = str_replace(' ', '-', trim($value));
				$value = str_replace('_', '-', trim($value));
				$value = preg_replace('/[\-]+/', '-', $value);
				$value = stripslashes($value);
				return $value;
			}

		function zmiennazwe($value) {
				$filename = strtolower($value);
				$exts = explode("[/\\.]", $filename);
				$n = count($exts)-1; 
				$exts = $exts[$n];
				return $exts;
		}
	
		function jaki_link($link,$baseUrl) {
			$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
			if(preg_match($reg_exUrl, $link, $url)) {
				return $link;
			} else {
				return $baseUrl.''.$link;
			}
		}
		
		function dostep($id) {
			$login = Zend_Auth::getInstance()->getIdentity();
			$inwestArray = explode(',', $login->inwestycje);
			if(!in_array($id, $inwestArray)){
	
				$_redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				$_redirector->gotoUrl('/admin/inwestycje/'); 
			}
		}
		
		function sitemap() {
			$db = Zend_Registry::get('db');

			$ustawienia = $db->fetchRow($db->select()->from('ustawienia'));
			
			$db = Zend_Registry::get('db');
			$select = $db->select()
			->from(array('s' => 'strony'), array('id', 'link', 'sort', 'typ', 'uri', 'data'))
			->order('s.sort ASC');
			$xmlsitemapsql = $db->fetchAll($select);

			$xmlfile = 'sitemap.xml';
		$xmlsitemap = '<?xml version="1.0" encoding="UTF-8"?>
		<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
			<url>
				<loc>'.$ustawienia->domena.'</loc>
				<changefreq>weekly</changefreq>
			</url>';
			foreach($xmlsitemapsql as $menu) {
				if($menu->typ <> 0) {

			$xmlsitemap .= '
			<url>
				<loc>'.jaki_link($menu->link, $ustawienia->domena).'</loc>
				<changefreq>weekly</changefreq>
			</url>
			';
				} else {
			$xmlsitemap .= '
			<url>
				<loc>'.$ustawienia->domena.$menu->uri.'.html</loc>
				<changefreq>weekly</changefreq>
				<lastmod>'.date('Y-m-d', strtotime(substr($menu->data, 0, 10))).'</lastmod>
			</url>
			';
				}
			}
		$xmlsitemap .= '
		</urlset>';
			file_put_contents(BASE_PATH . '/../'.$xmlfile, $xmlsitemap);
		}
	}
}

?>