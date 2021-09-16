<?php
class Model_NewsModel  extends Zend_Db_Table_Abstract
{
    protected $_name = 'news';
    protected $_module = 'news';
    protected $_db_table;
    protected $_locale;
    private $canbetranslate;

    public function __construct()
    {
        try {
            $this->_db_table = Zend_Registry::get('db');
            $this->_db_table->setFetchMode(Zend_Db::FETCH_OBJ);
        } catch (Zend_Exception $e) {
        }
        try {
            $this->canbetranslate = Zend_Registry::get('canbetranslate');
            if($this->canbetranslate) {
                $this->_locale = Zend_Registry::get('Zend_Locale')->getLanguage();
            } else {
                $this->_locale = 'pl';
            }
        } catch (Zend_Exception $e) {
        }
    }

    /**
     * Pokaz wybrana strone
     * @param int $id
     * @return Object
     */
    public function getAll()
    {
        if($this->_locale == 'pl') {
            $query = $this->_db_table->select()
                ->from($this->_name, array('id', 'plik', 'data', 'status', 'wprowadzenie', 'tytul', 'tag'))
                ->where('status =?', 1)
                ->order('data DESC');
            return $this->_db_table->fetchAll($query);
        } else {
            $translatedQuery = $this->_db_table->select()
                ->from(array('t' => 'tlumaczenie_wpisy'))
                ->join(array('tl' => $this->_name), 't.id_wpis = tl.id', array(
                    'status',
                    'plik',
                    'tag',
                    'data'
                ))
                ->where('module = ?', $this->_module)
                ->where('lang = ?', $this->_locale)
                ->where('status =?', 1)
                ->order('data DESC');
            return $this->_db_table->fetchAll($translatedQuery);
        }
    }

    public function get($tag)
    {
        if($this->_locale == 'pl') {
            $query = $this->_db_table->select()
                ->from($this->_name, array('id', 'plik', 'data', 'status', 'wprowadzenie', 'tytul', 'tag', 'tekst'))
                ->where('tag = ?', $tag);
            return $this->_db_table->fetchRow($query);
        } else {
            $translatedQuery = $this->_db_table->select()
                ->from(array('t' => 'tlumaczenie_wpisy'))
                ->join(array('tl' => $this->_name), 't.id_wpis = tl.id', array(
                    'status',
                    'plik',
                    'tag',
                    'data'
                ))
                ->where('tag = ?', $tag)
                ->where('module = ?', $this->_module)
                ->where('lang = ?', $this->_locale);
            return $this->_db_table->fetchRow($translatedQuery);
        }
    }
}