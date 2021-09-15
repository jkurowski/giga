<?php
class kCMS_Language extends Zend_Controller_Plugin_Abstract {
    public function routeShutdown(Zend_Controller_Request_Abstract $request) {

        $langParam = $request->getParam('language');
        if($langParam) {

            $langModel = new Model_LanguageModel();
            $langDB = $langModel->fetchRow($langModel->select()->where('kod =?', $langParam));

            if($langDB) {
                $locale = new Zend_Locale($langParam);
                Zend_Registry::set('Zend_Locale', $locale);

                $dictionary = new Model_DictionaryModel();
                $dictionaryArray = $dictionary->fetchAll($dictionary->select()->where('lang =?', $langParam));

                $dictionaryEntry = array();
                foreach($dictionaryArray as $d){
                    $dictionaryEntry[$d->keyword] = $d->word;
                }

                $tr = new Zend_Translate('array', $dictionaryEntry);
                $tr->setLocale($locale);
                Zend_Form::setDefaultTranslator($tr);
                Zend_Registry::set('Zend_Translate', $tr);

            } else {
                $locale = new Zend_Locale('pl');
                Zend_Registry::set('Zend_Locale', $locale);

                $request->setModuleName('default');
                $request->setControllerName('error');
                $request->setActionName('error');
            }
        }
    }
}