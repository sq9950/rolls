<?php
namespace Model\Car;
class CarLslsModel extends \Model\ExtendModel {

    public function __construct(){
        $this->db_conf_name = 'cp';
        $this->table_name = 'car_lsls';
        parent::__construct();
    }

}


