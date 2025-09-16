<?php

class GetQuery{
	public $table;
	
	public function __construct($table){
		$this->table= $table;
	}
	
	public function search($cursor){
		//Trae todos lo campos de la tabla pasada por el contructor
		$queryUser= "SELECT * FROM {$this->table}";
		return mysqli_query($cursor, $queryUser);		
	}
}