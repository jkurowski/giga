<?php
class Model_InlineModel  extends Zend_Db_Table_Abstract
{
    protected $_name = 'inline';
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
     * Pobierz tlumaczenie inline
     * @param int $id
     * @return Object
     */
    public function get(int $id)
    {
        $inlineQuery = $this->_db_table->select()
            ->from('inline')
            ->where('id_item = ?', $id);
        return $this->_db_table->fetchRow($inlineQuery);
    }

    /**
     * Pobierz tlumaczenie inline
     * @param int $id
     * @return Object
     */
    public function getInlineItem(int $id)
    {
        $inlineQuery = $this->_db_table->select()
            ->from('inline')
            ->where('id_item = ?', $id)
            ->where('lang =?', $this->_locale);
        return $this->_db_table->fetchRow($inlineQuery);
    }

    /**
     * Pobierz tlumaczenia inline
     * @param int $id
     * @return Object
     */
    public function getInlineList(int $id)
    {
        $inlineQuery = $this->_db_table->select()
            ->from('inline')
            ->where('id_place =?', $id)
            ->where('lang = ?', $this->_locale);
        return $this->_db_table->fetchAll($inlineQuery);
    }

    /**
     * Dodaj tlumaczenie inline
     * @param array $data
     */
    public function save(array $data)
    {
        $this->_db_table->insert('inline', $data);
    }

    /**
     * Aktualizuj tlumaczenie inline
     * @param int $id
     * @param array $data
     */
    public function updateInline(int $id, array $data)
    {
        $where = array(
            'id_item = ?' => $id,
            'lang = ?' => $this->_locale
        );
        $this->_db_table->update('inline', $data, $where);
    }
}