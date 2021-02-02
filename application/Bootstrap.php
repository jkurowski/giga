<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	 public function _initCoreSession()  
		{  
            $this->bootstrap('db');
            $db = $this->getResource('db');
            try {
                $db->getConnection();
            } catch( Exception $e ) {
                print_r('Błąd połączenia z bazą');
                http_response_code(404);

                echo '<pre>';
                print_r($e);
                echo '</pre>';

                die;
            }
            $this->bootstrap('session');
            try {
                Zend_Session::start();
            } catch (Zend_Session_Exception $e) {
            }
        }

    protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array(
        'namespace' => '',
        'basePath' => dirname(__FILE__),
        'resourceTypes' => array (
                'form' => array(
                    'path' => 'forms',
                    'namespace' => 'Form',
                )
            )

        ));
        return $autoloader;
    }

	protected function _initModules()
	{
		$this->bootstrap('frontcontroller');
		$frontController = Zend_Controller_Front::getInstance();
		$frontController->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
				'module' => 'default',
				'controller' => 'error',
				'action' => 'error'
		)));

		// Konfiguracja bazy danych
		$this->bootstrap('db');
        $db = $this->getResource('db');
        try {
            $db->getConnection();
        } catch( Exception $e ) {
            print_r('Błąd połączenia z bazą');
            http_response_code(404);
            die;
        }
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		$db->query("SET NAMES 'utf8'");
	    Zend_Registry::set('db', $db);

		$router = $frontController->getRouter();
		$admin = new Zend_Controller_Router_Route( 'admin/:controller/:action/*',
                    array(
                    'module'     => 'admin',
                    'controller' => 'index',
                    'action'     => 'index'
                    )
                );

        try {
            $route = new Zend_Config_Ini(APPLICATION_PATH . '/configs/route.ini', null);
        } catch (Zend_Config_Exception $e) {
        }
        $router->addConfig($route,'routes');
		$router->addRoute('admin', $admin);

		Zend_Layout::startMvc(array(
			'layoutPath' => APPLICATION_PATH . '/layouts/default/',
			'layout' => 'layout'
		));

		$autoloader = Zend_Loader_Autoloader::getInstance();
        try {
            $autoloader->registerNamespace('kCMS_');
        } catch (Zend_Loader_Exception $e) {
        }

        $helper= new kCMS_Controller_Helper_Acl();
		$helper->setRoles();
		$helper->setResources();
		$helper->setPrivilages();
		$helper->setAcl();
		$frontController->registerPlugin(new kCMS_Controller_Plugin_Acl());

        $router = new Zend_Controller_Router_Rewrite();
        $request = new Zend_Controller_Request_Http();
        $router->route($request);

		$layoutModulePlugin = new kCMS_LayoutPlugin();
		$layoutModulePlugin->registerModuleLayout('admin', APPLICATION_PATH . '/layouts/admin/');
		$layoutModulePlugin->registerModuleLayout('logowanie', APPLICATION_PATH . '/layouts/logowanie/');
		$frontController->registerPlugin($layoutModulePlugin);

		$frontController->throwExceptions(true);
	}
}