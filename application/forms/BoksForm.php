<?php
class Form_BoksForm extends Zend_Form
{
    public function __construct($options = null)
    {
        $this->addElementPrefixPath('App', 'App/');
        parent::__construct($options);
        $this->setName('boks');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setAttrib('class', 'mainForm');

        $nazwa = new Zend_Form_Element_Text('nazwa');
        $nazwa->setLabel('Tytuł')
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
        $obrazek->setLabel('Obrazek')
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

        $wprowadzenie = new Zend_Form_Element_Textarea('wprowadzenie');
        $wprowadzenie->setLabel('Wprowadzenie')
            ->setRequired(true)
            ->setAttrib('rows', 10)
            ->setAttrib('cols', 100)
            ->addValidator('NotEmpty')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fullformRowtext')),
                array('Label'), array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fullformRow'))));

        $tekst = new Zend_Form_Element_Textarea('tekst');
        $tekst->setLabel('Treść')
            ->setRequired(true)
            ->setAttrib('rows', 27)
            ->setAttrib('cols', 100)
            ->setAttrib('class', 'editor')
            ->addValidator('NotEmpty')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fullformRowtext')),
                array('Label'), array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fullformRow'))));

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
        $this->addElements(array($nazwa, $obrazek, $wprowadzenie, $tekst, $submit));
    }
}