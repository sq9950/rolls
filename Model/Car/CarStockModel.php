<?php
namespace Model\Car;
class CarStockModel extends \Model\ExtendModel {

    public function __construct(){
        $this->db_conf_name = 'cp';
        $this->table_name = 'car_stock';
        parent::__construct();
    }

}


