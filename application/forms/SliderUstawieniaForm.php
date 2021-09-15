<?php
class Form_SliderUstawieniaForm extends Zend_Form
{
    public function __construct($options = null)
    {
        $this->addElementPrefixPath('App', 'App/');
        parent::__construct($options);
        $this->setName('sliderustawienia');
        $this->setAttrib('class', 'mainForm');

        $slider_efekt = new Zend_Form_Element_Select('slider_efekt');
        $slider_efekt->setLabel('Efekt')
            ->addMultiOption('random','random')
            ->addMultiOption('simpleFade','simpleFade')
            ->addMultiOption('curtainTopLeft','curtainTopLeft')
            ->addMultiOption('curtainTopRight','curtainTopRight')
            ->addMultiOption('curtainBottomLeft','curtainBottomLeft')
            ->addMultiOption('curtainBottomRight','curtainBottomRight')
            ->addMultiOption('curtainSliceLeft','curtainSliceLeft')
            ->addMultiOption('curtainSliceRight','curtainSliceRight')
            ->addMultiOption('blindCurtainTopLeft','blindCurtainTopLeft')
            ->addMultiOption('blindCurtainTopRight','blindCurtainTopRight')
            ->addMultiOption('blindCurtainBottomLeft','blindCurtainBottomLeft')
            ->addMultiOption('blindCurtainBottomRight','blindCurtainBottomRight')
            ->addMultiOption('blindCurtainSliceBottom','blindCurtainSliceBottom')
            ->addMultiOption('blindCurtainSliceTop','blindCurtainSliceTop')
            ->addMultiOption('stampede','stampede')
            ->addMultiOption('mosaic','mosaic')
            ->addMultiOption('mosaicReverse','mosaicReverse')
            ->addMultiOption('mosaicRandom','mosaicRandom')
            ->addMultiOption('mosaicSpiral','mosaicSpiral')
            ->addMultiOption('mosaicSpiralReverse','mosaicSpiralReverse')
            ->addMultiOption('topLeftBottomRight','topLeftBottomRight')
            ->addMultiOption('bottomRightTopLeft','bottomRightTopLeft')
            ->addMultiOption('bottomLeftTopRight','bottomLeftTopRight')
            ->addMultiOption('bottomLeftTopRight','bottomLeftTopRight')
            ->addMultiOption('scrollLeft','scrollLeft')
            ->addMultiOption('scrollRight','scrollRight')
            ->addMultiOption('scrollHorz','scrollHorz')
            ->addMultiOption('scrollBottom','scrollBottom')
            ->addMultiOption('scrollTop','scrollTop')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
                array('Label'),
                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $slider_auto = new Zend_Form_Element_Select('slider_auto');
        $slider_auto->setLabel('Tryb automatyczny')
            ->addMultiOption('true','Tak')
            ->addMultiOption('false','Nie')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
                array('Label'),
                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $slider_pause = new Zend_Form_Element_Select('slider_pause');
        $slider_pause->setLabel('Zatrzymaj po najechaniu')
            ->addMultiOption('true','Tak')
            ->addMultiOption('false','Nie')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
                array('Label'),
                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $slider_nav = new Zend_Form_Element_Select('slider_nav');
        $slider_nav->setLabel('Pokaż strzałki')
            ->addMultiOption('true','Tak')
            ->addMultiOption('false','Nie')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
                array('Label'),
                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $slider_pager = new Zend_Form_Element_Select('slider_pager');
        $slider_pager->setLabel('Pokaż kulki')
            ->addMultiOption('true','Tak')
            ->addMultiOption('false','Nie')
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRight')),
                array('Label'),
                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'formRow'))));

        $slider_speed = new Zend_Form_Element_Text('slider_speed');
        $slider_speed->setLabel('Szybkość przenikania obrazków<br /><span style="font-size:11px;color:#A8A8A8">1000 = 1s</span>')
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

        $slider_timeout = new Zend_Form_Element_Text('slider_timeout');
        $slider_timeout->setLabel('Czas wyświetlania obrazka<br /><span style="font-size:11px;color:#A8A8A8">1000 = 1s</span>')
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

        $this->setDecorators(array('FormElements',array('HtmlTag'),'Form',));
        $this->addElements(array($slider_auto, $slider_pause, $slider_nav, $slider_speed, $slider_timeout, $submit));

    }
}