<?php
class Model_TranslateModel  extends Zend_Db_Table_Abstract
{
    protected $_name = 'tlumaczenie_wpisy';

    /**
     * Zapisz tlumaczenie
     * @param string $module
     * @param int $id
     * @param string $lang
     * @return Zend_Db_Table_Row_Abstract|null
     */
    public function getTranslate(string $module, int $id, string $lang)
    {
        return $this->fetchRow($this->select()
            ->where('module = ?', $module)
            ->where('id_wpis = ?', $id)
            ->where('lang = ?', $lang)
        );
    }

    /**
     * Zapisz tlumaczenie
     * @param array $formData
     * @param string $module
     * @param int $id
     * @param string $lang
     * @return bool
     */
    public function saveTranslate(array $formData, string $module, int $id, string $lang)
    {

        $tlumaczenie = $this->getTranslate($module, $id, $lang);

        unset($formData['submit']);
        $formDataJson = json_encode(array_filter($formData, 'strlen'));

        $array = array(
            'lang' => $lang,
            'module' => $module,
            'id_wpis' => $id,
            'json' => $formDataJson
        );

        if($tlumaczenie) {
            $this->delete('id = '.$tlumaczenie->id);
        }

        $this->insert($array);

        return true;
    }
}