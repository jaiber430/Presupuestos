<?php
namespace presupuestos\model;
use presupuestos\model\MainModel;
use PDO;

class UserModel extends MainModel{
	private $email;
	
	public function __construct($email){
		$this->email= $email;
	}
	
	public function findByEmail() {
		$query= "SELECT * 
			FROM user 
			WHERE email= :email";
			
		$params= ["email"=>$this->email];	
		$stmt= $this->executeQuery($query, $params);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
}