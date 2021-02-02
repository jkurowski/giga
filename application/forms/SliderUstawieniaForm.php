<?php
class Form_SliderUstawieniaForm extends Zend_Form 
{
    public function __construct($options = null)
    {
        $this->addElementPrefixPath('App', 'App/');
        parent::__construct($options);
        $this->setName('sliderustawienia');
		$this->setAttrib('class', 'mainForm');

		$effect = new Zend_Form_Element_Select('effect');
        $effect->setLabel('Efekt')
        ->addMultiOption('slideInRight', 'slideInRight')
        ->addMultiOption('sliceDown', 'sliceDown')
        ->addMultiOption('sliceDownLeft', 'sliceDownLeft')
        ->addMultiOption('sliceUp', 'sliceUp')
        ->addMultiOption('sliceUpLeft', 'sliceUpLeft')
        ->addMultiOption('sliceUpDown', 'sliceUpDown')
        ->addMultiOption('sliceUpDownLeft', 'sliceUpDownLeft')
        ->addMultiOption('fold', 'fold')
        ->addMultiOption('fade', 'fade')
        ->addMultiOption('slideInRight', 'slideInRight')
        ->addMultiOption('slideInLeft', 'slideInLeft')
        ->addMultiOption('boxRain', 'boxRain')
        ->addMultiOption('boxRainReverse', 'boxRainReverse')
        ->addMultiOption('boxRainGrow', 'boxRainGrow')
        ->addMultiOption('boxRainGrowReverse', 'boxRainGrowReverse')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

		$pausehover = new Zend_Form_Element_Select('pausehover');
        $pausehover->setLabel('Zatrzymaj po najechaniu')
		->addMultiOption('true','Tak')
		->addMultiOption('false','Nie')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $speed = new Zend_Form_Element_Text('speed');
        $speed->setLabel('Szybkość przenikania obrazków<br /><span style="font-size:11px;color:#A8A8A8">1000 = 1s</span>')
		->setRequired(true)
		->setAttrib('size', 33)
		->addValidator('stringLength', false, array(1, 255))
		->setFilters(array('StripTags', 'StringTrim'))
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
        array('Label', array('escape' => false)),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $pausetime = new Zend_Form_Element_Text('pausetime');
        $pausetime->setLabel('Czas wyświetlania obrazka<br /><span style="font-size:11px;color:#A8A8A8">1000 = 1s</span>')
		->setRequired(true)
		->setAttrib('size', 33)
		->addValidator('stringLength', false, array(1, 255))
		->setFilters(array('StripTags', 'StringTrim'))
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
        array('Label', array('escape' => false)),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $pausetime = new Zend_Form_Element_Text('pausetime');
        $pausetime->setLabel('Czas wyświetlania obrazka<br /><span style="font-size:11px;color:#A8A8A8">1000 = 1s</span>')
		->setRequired(true)
		->setAttrib('size', 33)
		->addValidator('stringLength', false, array(1, 255))
		->setFilters(array('StripTags', 'StringTrim'))
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
        array('Label', array('escape' => false)),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $slices = new Zend_Form_Element_Text('slices');
        $slices->setLabel('Ilość plastrów<br /><span style="font-size:11px;color:#A8A8A8">Na ile pionowe pasków ma być podzielony obrazek</span>')
		->setRequired(true)
		->setAttrib('size', 33)
		->addValidator('stringLength', false, array(1, 255))
		->setFilters(array('StripTags', 'StringTrim'))
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
        array('Label', array('escape' => false)),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $cols = new Zend_Form_Element_Text('cols');
        $cols->setLabel('Ilość kolumn<br /><span style="font-size:11px;color:#A8A8A8">Na ile kolumn ma być podzielony obrazek</span>')
		->setRequired(true)
		->setAttrib('size', 33)
		->addValidator('stringLength', false, array(1, 255))
		->setFilters(array('StripTags', 'StringTrim'))
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
        array('Label', array('escape' => false)),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $rows = new Zend_Form_Element_Text('rows');
        $rows->setLabel('Ilość rzędów<br /><span style="font-size:11px;color:#A8A8A8">Na ile rzędów ma być podzielony obrazek</span>')
		->setRequired(true)
		->setAttrib('size', 33)
		->addValidator('stringLength', false, array(1, 255))
		->setFilters(array('StripTags', 'StringTrim'))
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
        array('Label', array('escape' => false)),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

	    $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel ('Zapisz ustawienia')
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
            $effect,
            $pausehover,
            $speed,
            $pausetime,
            $slices,
            $cols,
            $rows,
            $submit
        ));

    }
}