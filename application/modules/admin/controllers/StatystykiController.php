<?php
class Admin_StatystykiController extends kCMS_Admin
{
    public function preDispatch() {
        $this->view->controlname = "Statystyki";
    }

    public function indexAction() {
        $db = Zend_Registry::get('db');

        $this->view->data_start = $data_start = $this->getRequest()->getParam('data_start');
        $this->view->data_end = $data_end = $this->getRequest()->getParam('data_end');

        $time_end  = strtotime($data_end);
        $day_end   = date('d', $time_end);
        $month_end = date('m', $time_end);
        $year_end  = date('Y', $time_end);

        $time_start  = strtotime($data_start);
        $day_start   = date('d', $time_start);
        $month_start = date('m', $time_start);
        $year_start  = date('Y', $time_start);

        $query = $db->select()
            ->from('statystyki')
            ->where('miejsce = ?', 4);

        if($data_start && $data_end){
            $query->where("dzien >= ?", $day_start)->where("msc >= ?", $month_start)->where("rok >= ?", $year_start);
            $query->where("dzien <= ?", $day_end)->where("msc <= ?", $month_end)->where("rok <= ?", $year_end);
        }
        $query->where('akcja = ?', 1);
        $query->orWhere('miejsce = ?', 3);

        if($data_start && $data_end){
            $query->where("dzien >= ?", $day_start)->where("msc >= ?", $month_start)->where("rok >= ?", $year_start);
            $query->where("dzien <= ?", $day_end)->where("msc <= ?", $month_end)->where("rok <= ?", $year_end);
        }
        $query->where('akcja = ?', 1);
        $query->orWhere('miejsce = ?', 5);

        if($data_start && $data_end){
            $query->where("dzien >= ?", $day_start)->where("msc >= ?", $month_start)->where("rok >= ?", $year_start);
            $query->where("dzien <= ?", $day_end)->where("msc <= ?", $month_end)->where("rok <= ?", $year_end);
        }
        $query->where('akcja = ?', 1);
        $query->order('id DESC');

        $this->view->lista = $db->fetchAll($query);
    }

    public function maileAction()
    {
        $db = Zend_Registry::get('db');
        $email_list = $db->fetchAll($db->select()->from('statystyki')->where('email IS NOT NULL'));

        $csv_output = '';
        foreach ($email_list as $item) {
            $csv_output .= $item->imie.','.$item->telefon.','.$item->email . "\r\n";
        }
        $filename = "newsletter_" . date("Y-m-d_H-i", time());
        header("Content-type: text/csv");
        header("Content-disposition: filename=" . $filename . ".csv");
        print $csv_output;
        exit;

    }

    public function usunWiadomoscAction() {
        $db = Zend_Registry::get('db');

        $id = (int)$this->_request->getParam('id');
        $where = $db->quoteInto('id = ?', $id);
        $db->delete('statystyki', $where);

        $this->_redirect('/admin/statystyki/');
    }
}