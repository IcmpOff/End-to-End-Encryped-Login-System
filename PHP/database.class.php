<?php
class database{
	public $db;
	private $db_host = "localhost";
	private $db_user = "root";
	private $db_password = "";
	private $db_name = "license_login";
	public function connect(){
		try{
			$this->db = new PDO("mysql:host=".$this->db_host.";dbname=".$this->db_name, $this->db_user, $this->db_password);
		}
		catch (PDOException $e) {
			die("DataBase Error<br>".$e->getMessage());
		} 
		catch (Exception $e) {
			die("General Error<br>".$e->getMessage());
		}
	}
	public function select($what,$table, $specifier = NULL,$val = NULL, $type = NULL){
		if(!$type){
			$q = $this->db->prepare("SELECT $what FROM $table WHERE $specifier = :s");
			$q->execute(array("s"=>$val));
			return $q->fetchall();
		}else{
			$q = $this->db->prepare("SELECT $what FROM $table");
			$q->execute(array("s"=>$val));
			return $q->fetchall();
		}
	}
	public function insert_query($where, $col){
		$vals = NULL;
		$column = NULL;
		foreach ($col as $key => $val){
			if($column){
				$column .= " , `".$key."`";
			}else{
				$column .= "`".$key."`";
			}
			if($vals){
				$vals .= ",:".$key;
			}else{
				$vals .= ":".$key;
			}
		}
		$q = $this->db->prepare("INSERT INTO $where ($column) VALUES ($vals) ");
		return $q->execute($col);
	}

	public function update($where, $col, $specifier, $spec){
		$vals = NULL;
		$column = NULL;
		foreach ($col as $key => $val){
			if($column){
				$column .= " , `".$key."` = :".$key;
			}else{
				$column .= "`".$key."` = :".$key;
			}

		}
		$q = $this->db->prepare("UPDATE $where SET $column WHERE $specifier = :spec ");
		$col['spec'] = $spec;
		$bool = $q->execute($col);
		if(!$bool){
			$error = $q->errorInfo();
			 return print_r($error);
		}
		else{
			return true;
		}
	}
	static function sanitize($val){
		return htmlspecialchars($val);
	}
	public function generate_random_string($length) {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return strtoupper($randomString);
	}
}