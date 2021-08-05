<?php
class Form_OpiniaForm extends Zend_Form
{
    public function __construct($options = null)
    {
        $this->addElementPrefixPath('App', 'App/');
        parent::__construct($options);
        $this->setName('boks');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setAttrib('class', 'mainForm');

        $place_id = new Zend_Form_Element_Select('place_id');
        $place_id->setLabel('Pozycja')
            ->addMultiOption (1, 'Misja i wartości')
            ->addMultiOption (2, 'O nas')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
                array('Label'),
                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $imie = new Zend_Form_Element_Text('imie');
        $imie->setLabel('Imię')
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

        $stanowisko = new Zend_Form_Element_Text('stanowisko');
        $stanowisko->setLabel('Stanowisko')
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

        $dodatkowe = new Zend_Form_Element_Text('dodatkowe');
        $dodatkowe->setLabel('Dodatkowy tekst')
            ->setRequired(false)
            ->setAttrib('size', 103)
            ->setFilters(array('StripTags', 'StringTrim'))
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
                array('Label'),
                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $obrazek = new Zend_Form_Element_File('obrazek');
        $obrazek->setLabel('Zdjęcie')
            ->setRequired(false)
            ->addValidator('NotEmpty')
            ->addValidator('Extension', false, 'jpg, png, jpeg, bmp, gif')
            ->addValidator('Size', false, 2702400)
            ->setDecorators(array(
                'File',
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

        // Polskie tlumaczenie errorów
        $polish = kCMS_Polish::getPolishTranslation();
        $translate = new Zend_Translate('array', $polish, 'pl');
        $this->setTranslator($translate);

        $this->setDecorators(array('FormElements',array('HtmlTag'),'Form',));
        $this->addElements(
            array(
                $place_id,
                $imie,
                $stanowisko,
                $dodatkowe,
                $tekst,
                $obrazek,
                $submit
            )
        );
    }
}