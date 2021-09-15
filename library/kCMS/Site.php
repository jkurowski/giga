<?php
require_once 'Zend/Controller/Action.php';
abstract class kCMS_Site extends Zend_Controller_Action {

    public function init() {
		$front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        try {
            $db = Zend_Registry::get('db');
        } catch (Zend_Exception $e) {

        }

        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $configApp = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'translated');
        if($configApp->app->translate) {
            $locale = Zend_Registry::get('Zend_Locale')->getLanguage();
            $this->canbetranslate = 1;
            Zend_Registry::set('canbetranslate', 1);
        } else {
            $locale = '';
            $this->canbetranslate = 0;
            Zend_Registry::set('canbetranslate', 0);
        }

        $mainmenu = new kCMS_MenuBuilder();
        Zend_Registry::set('mainmenu', $mainmenu);

        $header = $db->fetchRow($db->select()->from('ustawienia'));
        $langs = $db->fetchAll($db->select()->from('tlumaczenie')->order( 'id ASC' )->where('status =?', 1));
        $rodo = $db->fetchRow($db->select()->from('rodo_ustawienia')->where('id =?', 1));
        $rodo_rules = $db->fetchAll($db->select()->from('rodo_regulki')->order('sort ASC')->where('status = ?', 1));

        foreach($langs as $l) {
            if($l['kod'] == $locale) {
                $this->view->main_meta_tytul = $l['meta_tytul'];
                $this->view->main_meta_slowa = $l['meta_slowa'];
                $this->view->main_meta_opis = $l['meta_opis'];
                $this->view->obowiazek = $l['obowiazek'];
                $this->view->footer_about = $l['stopka'];
            }
        }

        $sitearray = array(
            'header' => $header,
            'langs' => $langs,
            'lang' => $locale,
            'rodo' => $rodo,
            'canbetranslate' => $this->canbetranslate,
            'rodo_rules' => $rodo_rules,
            'current_action' => $request->getActionName(),
            'current_controller' => $request->getControllerName()
        );
        $this->view->assign($sitearray);

		function zlotowka($value) {
			$value = str_replace('zĹ‚', 'zł', trim($value));
			return $value;
		}
		
		function pokoje($value) {
			if($value == 1){
				$text = ' - 1 pokój';
			}
			if($value > 1){
				$text = ' - '.$value.' pokoje';
			}
			if($value == 0){
				$text = '';
			}
			return $text;
		}
		
		function lamane($value) {
			$value = str_replace(';', '<br>', $value);
			return $value;
		}

		function zmiana($value) {
            $value = strtr($value, array('ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z', 'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'O', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z'));
            $value = str_replace(' ', '-', trim($value));
            $value = preg_replace('/[^a-zA-Z0-9\-_]/', '', (string) $value);
            $value = preg_replace('/[\-]+/', '-', $value);
            $value = stripslashes($value);
            return urlencode(strtolower($value));
		}

		function findKeyValue($array, $needle, $val, $found = false){
			foreach ($array as $key => $item){
				if(is_array($item)){
					$found = findKeyValue($item, $needle, $val, $found);
				}
				if( ! empty($key) && $key == $needle && strpos($item, $val) !== false){
					return true;
				}
			}

			return $found;
		}

		//******** Raportowanie bledow ********//
        function mylog($text, $level='i', $file='logs') {
            switch (strtolower($level)) {
                case 'e':
                case 'error':
                    $level='ERROR';
                    break;
                case 'i':
                case 'info':
                    $level='INFO';
                    break;
                case 'd':
                case 'debug':
                    $level='DEBUG';
                    break;
                default:
                    $level='INFO';
            }
            error_log(date("[Y-m-d H:i:s]")."\t[".$level."]\t[".$_SERVER['REQUEST_URI']."]\t".$text."\n", 3, $file);
        }
        //******** Raportowanie bledow ********//

		//******** RODO z -> ********//
		function historylog($nazwa, $mail, $ip, $przegladarka, $regulki) {
			$db = Zend_Registry::get('db');
			if($mail){
			    // Czy adres e-mail jest poprawny
                if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {

                    // Czy klient istnieje w bazie
                    $klientQuery = $db->select()
                        ->from('rodo_klient')
                        ->where('mail =?', $mail);
                    $klient = $db->fetchRow($klientQuery);

                    if($klient){
                        mylog($mail. ' - istnieje w bazie', 'i', 'rodo.log');
                        $historia = array(
                            'data_aktualizacji' => date("Y-m-d H:i:s"),
                            'ip' => $ip,
                            'host' => gethostbyaddr($ip),
                            'przegladarka' => $przegladarka,
                        );

                        try {
                            $n = $db->update('rodo_klient', $historia, 'id = '.$klient->id);
                            if (!empty($n)) {
                                mylog($mail.' - poprawna aktualizacja danych', 'i', 'rodo.log');
                            }
                        } catch (Zend_Exception $e) {
                            mylog($mail. ' - bład aktualizacji - '. $e->getMessage(), 'e', 'rodo.log');
                            exit;
                        }

                        foreach($regulki as $key => $number){
                            $getId = preg_replace('/[^0-9]/', '', $number);

                            $regulkaArchivQuery = $db->select()
                                ->from('rodo_regulki_klient')
                                ->where('id_regulka = ?', $getId)
                                ->where('id_klient = ?', $klient->id);
                            $regulkaArchiv = $db->fetchRow($regulkaArchivQuery);

                            $regulkaQuery = $db->select()
                                ->from('rodo_regulki')
                                ->where('id = ?', $getId);
                            $regulka = $db->fetchRow($regulkaQuery);

                            if(!$regulka){
                                mylog('Regulka o id '.$getId.' nie istnieje', 'e', 'rodo.log');
                                exit;
                            }

                            $dataRodo = array(
                                'id_regulka' => $getId,
                                'id_klient' => $klient->id,
                                'ip' => $ip,
                                'data_podpisania' => strtotime(date("Y-m-d H:i:s")),
                                'termin' => strtotime("+".$regulka->termin." months", strtotime(date("y-m-d"))),
                                'miesiace' => $regulka->termin,
                                'status' => 1
                            );

                            if($regulkaArchiv){

                                $arrayRegulka = json_decode(json_encode($regulkaArchiv),true);
                                unset($arrayRegulka['id']);
                                $arrayRegulka['data_anulowania'] = strtotime(date("Y-m-d H:i:s"));
                                $db->insert('rodo_regulki_archiwum', $arrayRegulka);

                                $where = array(
                                    'id_regulka = ?' => $getId,
                                    'id_klient = ?' => $klient->id
                                );
                                $db->update('rodo_regulki_klient', $dataRodo, $where);
                                mylog($mail.' - aktualizuje regulke id: '.$getId, 'i', 'rodo.log');
                            } else {

                                $db->insert('rodo_regulki_klient', $dataRodo);
                                mylog($mail.' - dodaje regulke id: '.$getId, 'i', 'rodo.log');
                            }
                        }

                        return true;
                    } else {
                        mylog($mail.' - nie istnieje w bazie', 'i', 'rodo.log');

                        $historia = array(
                            'nazwa' => $nazwa,
                            'mail' => $mail,
                            'ip' => $ip,
                            'host' => gethostbyaddr($ip),
                            'przegladarka' => $przegladarka,
                            'data_dodania' => date("Y-m-d H:i:s")
                        );

                        $db->insert('rodo_klient', $historia);
                        $lastId = $db->lastInsertId();
                        mylog( $mail.' - zapisany jako nowy klient', 'i', 'rodo.log');

                        $newklientQuery = $db->select()
                            ->from('rodo_klient')
                            ->where('id =?', $lastId);
                        $newklient = $db->fetchRow($newklientQuery);

                        if(!$newklient){
                            mylog('Klient o id '.$lastId.' nie istnieje', 'e', 'rodo.log');
                            exit;
                        }

                        foreach($regulki as $key => $number){
                            $getId = preg_replace('/[^0-9]/', '', $number);

                            $regulkaArchivQuery = $db->select()
                                ->from('rodo_regulki_klient')
                                ->where('id_regulka = ?', $getId)
                                ->where('id_klient = ?', $newklient->id);
                            $regulkaArchiv = $db->fetchRow($regulkaArchivQuery);

                            $regulkaQuery = $db->select()
                                ->from('rodo_regulki')
                                ->where('id = ?', $getId);
                            $regulka = $db->fetchRow($regulkaQuery);

                            $dataRodo = array(
                                'id_regulka' => $getId,
                                'id_klient' => $newklient->id,
                                'ip' => $ip,
                                'data_podpisania' => strtotime(date("Y-m-d H:i:s")),
                                'termin' => strtotime("+".$regulka->termin." months", strtotime(date("y-m-d"))),
                                'miesiace' => $regulka->termin,
                                'status' => 1
                            );

                            if($regulkaArchiv){

                                $arrayRegulka = json_decode(json_encode($regulkaArchiv),true);
                                unset($arrayRegulka['id']);
                                $arrayRegulka['data_anulowania'] = strtotime(date("Y-m-d H:i:s"));
                                $db->insert('rodo_regulki_archiwum', $arrayRegulka);

                                $where = array(
                                    'id_regulka = ?' => $getId,
                                    'id_klient = ?' => $newklient->id
                                );
                                $db->update('rodo_regulki_klient', $dataRodo, $where);
                                mylog($mail.' - aktualizuje regulke id: '.$getId, 'i', 'rodo.log');

                            } else {
                                $db->insert('rodo_regulki_klient', $dataRodo);
                                mylog($mail.' - dodaje regulke id: '.$getId, 'i', 'rodo.log');
                            }
                        }

                        return true;
                    }

                } else {
                    mylog('Zły adres e-mail: '.$mail, 'i', 'rodo.log');
                }
            } else {
                mylog('Brak adresu e-mail', 'i', 'rodo.log');
            }
		}
		//******** RODO ********//

        //******** 404 redirect ********//
        function errorPage()
        {
            $front = Zend_Controller_Front::getInstance()->getRequest();
            $response = Zend_Controller_Front::getInstance()->getResponse();

            $layout = Zend_Layout::getMvcInstance();
            $view = $layout->getView();
            $array = array(
                'seo_tytul' => "Strona nie została znaleziona - błąd 404",
                'strona_nazwa' => "Błąd 404",
                'nofollow' => 1,
            );
            $view ->assign($array);

            $front->setModuleName('default')->setControllerName('error')->setActionName('error');
            $response->setHttpResponseCode(404)->setRawHeader('HTTP/1.1 404 Not Found');
        }
        //******** 404 redirect ********//


		function galeria($input) {
			$db = Zend_Registry::get('db');
			$images = $db->fetchAll($db->select()->from('galeria_zdjecia')->order( 'sort ASC' )->where('id_gal =?', $input[2]));

            $request = Zend_Controller_Front::getInstance()->getRequest();
            $baseUrl = $request->getBaseUrl();

			if($input[1] == 'galeria') { 
				$html = '<div class="row justify-content-center">';
				foreach ($images as $value) {
					$html.= '<div class="col-6 col-sm-3 col-3-gallery"><div class="col-gallery-thumb"><a href="'.$baseUrl.'/files/galeria/big/'.$value->plik.'" class="swipebox" rel="gallery-1'.$input[2].'" title=""><img src="'.$baseUrl.'/files/galeria/thumbs/'.$value->plik.'"><div></div></a></div></div>';
				}
				$html.= '</div>';
			}
			if($input[1] == 'slider') { 
				$html= '<div class="row"><div class="col-12"><div class="sliderWrapper"><ul class="list-unstyled mb-0 clearfix">';
				foreach ($images as $value) {
					$html.= '<li><a href="'.$baseUrl.'/files/galeria/big/'.$value->plik.'" title="" class="swipebox" rel="gallery-2'.$input[2].'"><img src="'.$baseUrl.'/files/galeria/big/'.$value->plik.'" alt="" /></a></li>';
				}
				$html.= '</ul></div></div></div>';
			}
			if($input[1] == 'karuzela') { 
				$html= '<div class="carouselWrapper"><ul class="list-unstyled mb-0 clearfix">';
				foreach ($images as $value) {
					$html.= '<li><a href="'.$baseUrl.'/files/galeria/big/'.$value->plik.'" title="" class="swipebox" rel="gallery-3'.$input[2].'"><img src="'.$baseUrl.'/files/galeria/thumbs/'.$value->plik.'" alt="" /></a></li>';
				}
				$html.= '</ul></div>';
			}
			return($html);
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
		
		function miesiace( $time ){
			$miesiac = array( '', 'STY', 'LUT', 'MAR', 'KWI', 'MAJ', 'CZE', 'LIP', 'SIE', 'WRZ', 'PAŹ', 'LIS', 'GRU' );
			return $miesiac[$time];
		}
	
		function replace($input) {
			$input = preg_replace_callback('/\[galeria=(.*)](.*)\[\/galeria\]/', 'galeria', $input);
			$input = str_replace("</div></p>","</div>",$input);
			$input = str_replace("<p><div","<div",$input);
			return $input;
		}

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
                $result = mb_substr($string, 0, $len, "UTF-8") . ' ...';
            } else {
                $result = $string;
            }
            return $result;
        }
		
		//******** Google reCAPTCHA ********//
		
		function getOptions()
		{
			$front = Zend_Controller_Front::getInstance();
			$bootstrap = $front->getParam('bootstrap');
			if (null === $bootstrap) {
				throw new Exception('Unable to find bootstrap');
			}

			return $bootstrap->getOptions();
		}
		
		function getRecaptchaBody(){
			$config = getOptions();
			$key = $config['google']['recaptcha']['pagekey'];
			if($key){
				$url = "<script src='https://www.google.com/recaptcha/api.js?render=".$key."'></script>";
				return $url;
			} else {
				throw new Exception('Unable to find pagekey in application.ini');
			}
		}
		
		function getRecaptchaForm($action){
			$config = getOptions();
			$key = $config['google']['recaptcha']['pagekey'];
			if($key){
				$script = "<script>grecaptcha.ready(function(){grecaptcha.execute(\"".$key."\",{action:\"".$action."\"}).then(function(a){document.getElementById(\"g-recaptcha-response\").value=a})});</script>";
				$script .= "<input type=\"hidden\" id=\"g-recaptcha-response\" name=\"g-recaptcha-response\">";
				return $script;
			} else {
				throw new Exception('Unable to find pagekey in application.ini');
			}
		}
				
		function getRecaptchaCheck($response){
			$config = getOptions();
			$key = $config['google']['recaptcha']['secret'];
			if($key){
				if($response){
					$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$key.'&response='.$response);
					$responseData = json_decode($verifyResponse);

					if($responseData->success) {
						return true;
					}
				} else {
					throw new Exception('Could not get recaptcha response');
				}
			} else {
				throw new Exception('Unable to find pagekey in application.ini');
			}
		}
		
		//******** Google reCAPTCHA ********//

        //******** slug ********//
        function slug($value) {
            $value = strtr($value, array('ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z', 'ż' => 'z', 'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'O', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z'));
            $value = str_replace(' ', '-', trim($value));
            $value = preg_replace('/[^a-zA-Z0-9\-_]/', '', (string) $value);
            $value = preg_replace('/[\-]+/', '-', $value);
            $value = stripslashes($value);
            return urlencode(strtolower($value));
        }
        //******** slug ********//

        //******** image slug ********//
        function slugImg($title, $file) {
            $slug = slug($title);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            return $slug.'.'.$ext;
        }
        //******** image slug ********//
	}
}
?>