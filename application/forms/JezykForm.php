<?php
class Form_JezykForm extends Zend_Form
{ 
    public function __construct($options = null)
    {
        $this->addElementPrefixPath('App', 'App/');
        parent::__construct($options);
        $this->setName('jezyk');
		$this->setAttrib('class', 'mainForm');

		$status = new Zend_Form_Element_Select('status');
        $status->setLabel('Status')
		->addMultiOption (1, '-- Aktywny --')
		->addMultiOption (2, '-- Nieaktywny --')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $nazwa = new Zend_Form_Element_Text('nazwa');
        $nazwa->setLabel('Nazwa języka')
		->setRequired(true)
		->setAttrib('size', 83)
		->addValidator('stringLength', false, array(1, 255))
		->setFilters(array('StripTags', 'StringTrim'))
		->addValidator('NotEmpty')
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $kod = new Zend_Form_Element_Text('kod');
        $kod->setLabel('Kod języka wg. ISO 639-1')
		->setRequired(true)
		->setAttrib('size', 83)
		->addValidator('stringLength', false, array(1, 3))
		->setFilters(array('StripTags', 'StringTrim'))
		->addValidator('NotEmpty')
		->setAttrib('class', 'validate[required]')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

		$di = new DirectoryIterator('../public/gfx/flags/');
		$flaga = new Zend_Form_Element_Select('flaga');
        $flaga->setLabel('Flaga')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));
		$db = Zend_Registry::get('db');
		foreach ($di as $file) {
			if( !$file->isDot() && !$file->isDir() )
			$flaga->addMultiOption($file->getFilename(), $file->getFilename());
		}

        $meta_slowa = new Zend_Form_Element_Text('meta_slowa');
        $meta_slowa->setLabel('Słowa kluczowe<br /><span style="font-size:11px;color:#A8A8A8">(Keywords)</span>')
		->setRequired(false)
		->setAttrib('size', 83)
		->setFilters(array('StripTags', 'StringTrim'))
		->addValidator('NotEmpty')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $meta_tytul = new Zend_Form_Element_Text('meta_tytul');
        $meta_tytul->setLabel('Tytuł strony<br /><span style="font-size:11px;color:#A8A8A8">(Title)</span>')
		->setRequired(false)
		->setAttrib('size', 83)
		->setFilters(array('StripTags', 'StringTrim'))
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $meta_opis = new Zend_Form_Element_Text('meta_opis');
        $meta_opis->setLabel('Opis strony<br /><span style="font-size:11px;color:#A8A8A8">(Description)</span>')
		->setRequired(false)
		->setAttrib('size', 123)
		->setFilters(array('StripTags', 'StringTrim'))
		->addValidator('NotEmpty')
		->setDecorators(array(
		'ViewHelper',
		'Errors',
		array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
		array('Label'),
		array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $stopka = new Zend_Form_Element_Textarea('stopka');
        $stopka->setLabel('Opis firmy w stopce')
            ->setRequired(true)
            ->setAttrib('rows', 14)
            ->setAttrib('cols', 100)
            ->setAttrib('class', 'minieditor')
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

		$this->setDecorators(array('FormElements',array('HtmlTag'),'Form',));
        $this->addElements(array(
			$status,
			$kod,
			$nazwa,
			$flaga,
			$meta_tytul,
			$meta_slowa,
			$meta_opis,
			$stopka,
			$submit
		));
    }
}