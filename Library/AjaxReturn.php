<?php

class AjaxReturn{
	private $outputtype = 'JSON';
	private static $supporttype = array('JSON','XML','JSONP');
	
	public function __construct($type='JSON'){
		$this->setType($type);
	}
	public function output($data=array()){
		switch(strtoupper($this->outputtype)){
			case 'JSON':
				header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
			break;
			case 'XML':
				header('Content-Type:text/xml; charset=utf-8');
                exit($this->xml_encode($data));
			break;
			case 'JSONP':
				header('Content-Type:application/json; charset=utf-8');
                $handler  = strtolower('callback');  
                exit($handler.'('.json_encode($data).');');  
			break;
		}
	}
	
	public function ajaxReturn($data,$info,$status){
		$res = array(
			'data' => $data,
			'info' => $info,
			'status' => $status,
		);
		
		$this->output($res);
	}
	
	public function setType($type){
		if(in_array(strtoupper($type),self::$supporttype)) {
			$this->outputtype = strtoupper($type);
		}
	}
	
	/**
	 * XML编码
	 * @param mixed $data 数据
	 * @param string $encoding 数据编码
	 * @param string $root 根节点名
	 * @return string
	 */
	private function xml_encode($data, $encoding='utf-8', $root='Yundun') {
		$xml    = '<?xml version="1.0" encoding="' . $encoding . '"?>';
		$xml   .= '<' . $root . '>';
		$xml   .= $this->data_to_xml($data);
		$xml   .= '</' . $root . '>';
		return $xml;
	}

	/**
	 * 数据XML编码
	 * @param mixed $data 数据
	 * @return string
	 */
	private function data_to_xml($data) {
		$xml = '';
		foreach ($data as $key => $val) {
			is_numeric($key) && $key = "item id=\"$key\"";
			$xml    .=  "<$key>";
			$xml    .=  ( is_array($val) || is_object($val)) ? $this->data_to_xml($val) : $val;
			list($key, ) = explode(' ', $key);
			$xml    .=  "</$key>";
		}
		return $xml;
	}
	
	/**
	 * xml to array
	 *
	 * @param string $xml
	 * @return array
	 */
	
	function XML2Array ( $xml , $recursive = false )
	{
		if ( ! $recursive )
		{
			$array = simplexml_load_string ( $xml ) ;
		}
		else
		{
			$array = $xml ;
		}
		
		$newArray = array () ;
		$array = ( array ) $array ;
		foreach ( $array as $key => $value )
		{
			$value = ( array ) $value ;
			if ( isset ( $value [ 0 ] ) )
			{
				$newArray [ $key ] = trim ( $value [ 0 ] ) ;
			}
			else
			{
				$newArray [ $key ] = XML2Array ( $value , true ) ;
			}
		}
		return $newArray ;
	}
	 
	 
}