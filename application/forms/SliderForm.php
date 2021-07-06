<?php
class Form_SliderForm extends Zend_Form
{ 
    public function __construct($options = null)
    {
        $this->addElementPrefixPath('App', 'App/');
        parent::__construct($options);
        $this->setName('nowypanel');
		$this->setAttrib('enctype', 'multipart/form-data');
		$this->setAttrib('class', 'mainForm');
		
		$opacity = new Zend_Form_Element_Select('opacity');
        $opacity->setLabel('Wyciemnienie')
		->addMultiOption('1','1')
		->addMultiOption('0.9','0.9')
		->addMultiOption('0.8','0.8')
		->addMultiOption('0.7','0.7')
		->addMultiOption('0.6','0.6')
		->addMultiOption('0.5','0.5')
		->addMultiOption('0.4','0.4')
		->addMultiOption('0.3','0.3')
		->addMultiOption('0.2','0.2')
		->addMultiOption('0.1','0.1')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));
		
        $tytul = new Zend_Form_Element_Text('tytul');
        $tytul->setLabel('Tytuł')
		->setRequired(true)
		->setAttrib('size', 33)
		->setFilters(array('StripTags', 'StringTrim'))
		->setAttrib('class', 'validate[required, maxSize[110]]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $link = new Zend_Form_Element_Text('link');
        $link->setLabel('Link')
		->setRequired(true)
		->setAttrib('size', 33)
		->addValidator('stringLength', false, array(1, 255))
		->setFilters(array('StripTags', 'StringTrim'))
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));
		
        $link_tytul = new Zend_Form_Element_Text('link_tytul');
        $link_tytul->setLabel('Nazwa przycisku')
		->setRequired(true)
		->setAttrib('size', 33)
		->addValidator('stringLength', false, array(1, 255))
		->setFilters(array('StripTags', 'StringTrim'))
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));
		
        $link2 = new Zend_Form_Element_Text('link2');
        $link2->setLabel('Link 2')
		->setRequired(false)
		->setAttrib('size', 33)
		->addValidator('stringLength', false, array(1, 255))
		->setFilters(array('StripTags', 'StringTrim'))
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));
		
        $link_tytul2 = new Zend_Form_Element_Text('link_tytul2');
        $link_tytul2->setLabel('Nazwa przycisku 2')
		->setRequired(false)
		->setAttrib('size', 33)
		->addValidator('stringLength', false, array(1, 255))
		->setFilters(array('StripTags', 'StringTrim'))
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));
		
        $tekst = new Zend_Form_Element_Text('tekst');
        $tekst->setLabel('Tekst')
		->setRequired(true)
		->setAttrib('size', 103)
		->setFilters(array('StripTags', 'StringTrim'))
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

		$obrazek = new Zend_Form_Element_File('obrazek');
		$obrazek->setLabel('Plik')
		->setRequired(false)
		->addValidator('NotEmpty')
		->addValidator('Extension', false, 'jpg, png, jpeg, bmp, gif')
		->addValidator('Size', false, 9402400)
		->setDecorators(array(
		'File',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

	    $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel ('Zapisz panel')
		->setAttrib('class', 'greyishBtn')
		->setDecorators(array(
		'ViewHelper',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formSubmit'))));

		// Polskie tlumaczenie errorów
		$polish = kCMS_Polish::getPolishTranslation();
		$translate = new Zend_Translate('array', $polish, 'pl');
		$this->setTranslator($translate);

		$this->setDecorators(array('FormElements',array('HtmlTag'),'Form',));
        $this->addElements(array(
			$opacity,
			$tytul,
			$tekst,
			$link,
			$link_tytul,
//			$link2,
//			$link_tytul2,
			$obrazek,
			$submit
		));
    }
}