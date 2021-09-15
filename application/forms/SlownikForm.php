<?php
class Form_SlownikForm extends Zend_Form
{ 
    public function __construct($options = null)
    {
        $this->addElementPrefixPath('App', 'App/');
        parent::__construct($options);
        $this->setName('slownik');
		$this->setAttrib('class', 'mainForm');

		$keyword = new Zend_Form_Element_Select('keyword');
        $keyword->setLabel('Tag')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));
		$db = Zend_Registry::get('db');
		$tag = $db->fetchAll($db->select()->from('tlumaczenie_tag')->order( 'tag ASC' ));
		foreach ($tag as $listItem) {
			$keyword->addMultiOption($listItem->tag, ' '.$listItem->tag.'');
		}

        $tlumaczenie = new Zend_Form_Element_Text('tlumaczenie');
        $tlumaczenie->setLabel('Tłumaczenie')
		->setRequired(true)
		->setAttrib('size', 83)
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

	    $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel ('Zapisz')
		->setAttrib('class', 'greyishBtn')
		->setDecorators(array(
		'ViewHelper',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formSubmit'))));

		$this->setDecorators(array('FormElements',array('HtmlTag'),'Form',));
        $this->addElements(array($keyword, $tlumaczenie, $submit));
    }
}