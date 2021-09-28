<?php
class Model_PortfolioModel  extends Zend_Db_Table_Abstract
{
    protected $_name = 'realizacje';
    protected $_module = 'portfolio';
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
    public function getAll(string $tag = null)
    {
        if($this->_locale == 'pl') {
            $query = $this->_db_table->select()
                ->from($this->_name)
                ->where('status =?', 1)
                ->order('sort ASC');

            if($tag) {
                $query->where('category =?', $tag);
            }

            return $this->_db_table->fetchAll($query);
        } else {
            $translatedQuery = $this->_db_table->select()
                ->from(array('t' => 'tlumaczenie_wpisy'))
                ->join(array('tl' => $this->_name), 't.id_wpis = tl.id', array(
                    'obrazek',
                    'slug',
                    'status'
                ))
                ->where('status =?', 1)
                ->where('module = ?', $this->_module)
                ->where('lang = ?', $this->_locale)
                ->order('sort ASC');

            if($tag) {
                $translatedQuery->where('category =?', $tag);
            }

            return $this->_db_table->fetchAll($translatedQuery);
        }
    }

    public function get($tag)
    {
        if($this->_locale == 'pl') {
            $query = $this->_db_table->select()
                ->from($this->_name)
                ->where('slug = ?', $tag);
            return $this->_db_table->fetchRow($query);
        } else {
            $translatedQuery = $this->_db_table->select()
                ->from(array('t' => 'tlumaczenie_wpisy'))
                ->join(array('tl' => $this->_name), 't.id_wpis = tl.id', array(
                    'obrazek',
                    'slug'
                ))
                ->where('slug = ?', $tag)
                ->where('module = ?', $this->_module)
                ->where('lang = ?', $this->_locale)
                ->order('sort ASC');
            return $this->_db_table->fetchRow($translatedQuery);
        }
    }
}