<?php
/*
    modaltytul
    modaleditor
    modaleditortext
    modallink
    modallinkbutton
    obrazek
    obrazek_alt
*/

class Zend_View_Helper_GetInlineBtn extends Zend_View_Helper_Abstract {

    function getInlineBtn($id, $img_width, $img_height, $fields){

        $user = Zend_Auth::getInstance()->getIdentity();
        if ($user && $user->role == "admin") {
            return '<div class="container"><div class="row"><div class="col-12 text-center col-inline-nav"><button type="button" class="btn btn-primary btn-modal btn-sm" data-toggle="modal" data-target="#inlineModal" data-inline="'.$id.'" data-hideinput="'.$fields.'" data-method="update" data-imgwidth="'.$img_width.'" data-imgheight="'.$img_height.'">Edytuj <i class="lar la-edit"></i></button></div></div></div>';
        }

    }

}