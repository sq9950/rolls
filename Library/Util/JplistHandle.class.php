<?php
/**
 * Created by PhpStorm.
 * User: xuan
 * Date: 2015/6/12
 * Time: 14:36
 */

class JplistHandle{

    public $statuses;
    public $paginationStatuses;
    public $filterStatuses;
    public $filterEqStatuses;
    public $filterLeqStatuses;
    public $filterGeqStatuses;
    public $filterNotInStatuses;
    public $filterInStatuses;
    public $radioStatuses;
    public $query;
    public $jplsit_page;

    public function __construct($statuses){
        $this->jplsit_page = new \stdClass();
        $this->statuses = json_decode(urldecode($statuses));
    }

    public function getStatusesByType($type){

        $statusesList = array();
        foreach((array)$this->statuses as $key => $value){
            if($value->action == $type){

                array_push($statusesList, $value);
            }
        }
        switch($type){
            case 'paging' :
                $this->paginationStatuses = $statusesList;
                break;
            case 'filter':
                $this->filterStatuses = $statusesList;
                break;
            case 'filterEq':
                $this->filterEqStatuses = $statusesList;
                break;
            case 'filterLeq':
                $this->filterLeqStatuses = $statusesList;
                break;
            case 'filterGeq':
                $this->filterGeqStatuses = $statusesList;
                break;
            case 'filterIn':
                $this->filterInStatuses = $statusesList;
                break;
            case 'filterNotIn':
                $this->filterNotInStatuses = $statusesList;
                break;
            case 'radio':
                $this->radioStatuses = $statusesList;
                break;
            default:

                break;
        }
    }

    /**
     * get pagination query
     * @return {string}
     * status example
     * {
     *     "action": "paging",
     *     "name": "paging",
     *     "type": "placeholder",
     *     "data": {
     *         "number": "10",
     *         "currentPage": 0,
     *         "paging": null
     *     },
     *     "cookies": true
     * }
     */
    public function getPagingQuery(){

        $query = "";
        $currentPage = 0;
        $number = 0;

        if(count($this->paginationStatuses) > 0){

            $data = $this->paginationStatuses[0]->data;
            if(isset($data)){

                if(is_numeric($data->currentPage)){
                    $currentPage = intval($data->currentPage);
                }

                if(is_numeric($data->number)){
                    $number = intval($data->number);
                }

//                if($this->numberOfPages > $data->number){
                    $query = "LIMIT " . $currentPage * $number . ", " . $number;
//                }
            }
        }
        $this->jplsit_page->currentPage = $currentPage;
        $this->jplsit_page->pageSize = $number;
        $this->jplsit_page->offset = $this->jplsit_page->currentPage * $this->jplsit_page->pageSize;
        $this->query = $query;
        return $query;
    }

    function getWrapper($data = array(), $total = 0){

        $result['count'] = $total;
        $result['data'] = $data;

        return $result;
    }
}