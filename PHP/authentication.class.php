<?php
session_start();
ob_start();
include "database.class.php";
class authentication extends database{
    public $SECRET_KEY = null;
	public function __construct(){
		$this->connect();
		$this->SECRET_KEY = "Z2hvc3Rpc2Fob3Jl";//your_secret_key
	}
	public function encrypt_data($string){
        return base64_encode(openssl_encrypt($string, 'aes-256-cbc',  substr(hash('sha256', base64_decode($this->SECRET_KEY), true), 0, 32), OPENSSL_RAW_DATA, chr(0x01).chr(0x02).chr(0x03).chr(0x04).chr(0x05).chr(0x06).chr(0x07).chr(0x08).chr(0x09).chr(0x0A).chr(0x0B).chr(0x0C).chr(0x0D).chr(0x0E).chr(0x0F).chr(0x01)));
    }
    public function decrypt_data($string){
        return openssl_decrypt(base64_decode($string), 'aes-256-cbc', substr(hash('sha256', base64_decode($this->SECRET_KEY), true), 0, 32), OPENSSL_RAW_DATA, chr(0x01).chr(0x02).chr(0x03).chr(0x04).chr(0x05).chr(0x06).chr(0x07).chr(0x08).chr(0x09).chr(0x0A).chr(0x0B).chr(0x0C).chr(0x0D).chr(0x0E).chr(0x0F).chr(0x01));
    }
	public function login($license_key){
		if(empty($license_key)){
			$return->AUTHENTICATION->STATUS = "ERROR";
			$return->AUTHENTICATION->MESSAGE = "Please Fill Out All The Fields!";
		}
		else{
			$query = $this->db->prepare("SELECT * FROM `users` WHERE `license_key` = :license_key"); 
			$query->execute(array("license_key"=>self::sanitize($this->decrypt_data($license_key))));
			$result = $query->fetch(PDO::FETCH_ASSOC);
			if($result){
				$return->AUTHENTICATION->STATUS = "SUCCESS";
				$return->AUTHENTICATION->MESSAGE = "You Have Successfully Logged In!";
				$return->USER_INFORMATION->USERNAME = $result['username'];
				$return->USER_INFORMATION->EMAIL_ADDRESS = $result['email_address'];
				$return->USER_INFORMATION->LICENSE_KEY = $result['license_key'];
				$return->USER_INFORMATION->LATEST_IP_ADDRESS = $result['latest_ip_address'];
				$this->insert_query(
					"login_logs",
					array(
						"user_id"=>$result['id'],
						"ip_address"=>(isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'])
					)
				);
			}
			else{
				$return->AUTHENTICATION->STATUS = "ERROR";
				$return->AUTHENTICATION->MESSAGE = "That License Key Is Invalid!";
			}
		}
		return $this->encrypt_data(json_encode($return));
	}
	public function register($username, $email){
		if(empty($username) || empty($email)){
			$return->AUTHENTICATION->STATUS = "ERROR";
			$return->AUTHENTICATION->MESSAGE = "Please Fill Out All The Fields!";
		}
		else{
			$query = $this->db->prepare("SELECT * FROM `users` WHERE `username` = :username"); 
			$query->execute(array("username"=>self::sanitize($this->decrypt_data($username))));
			$result = $query->fetch(PDO::FETCH_ASSOC);
			if($result){
				$return->AUTHENTICATION->STATUS = "ERROR";
				$return->AUTHENTICATION->MESSAGE = "That Username Already Exists!";
			}
			else{
				$second_query = $this->db->prepare("SELECT * FROM `users` WHERE `email_address` = :email_address"); 
				$second_query->execute(array("email_address"=>self::sanitize($this->decrypt_data($email))));
				$second_result = $second_query->fetch(PDO::FETCH_ASSOC);
				if($second_result){
					$return->AUTHENTICATION->STATUS = "ERROR";
					$return->AUTHENTICATION->MESSAGE = "That Email Address Already Exists!";
				}
				else{
					$license_key = sprintf("%s-%s-%s-%s-%s", $this->generate_random_string(5), $this->generate_random_string(5), $this->generate_random_string(5), $this->generate_random_string(5), $this->generate_random_string(5));
					$this->insert_query(
						"users",
						array(
							"username"=>self::sanitize($this->decrypt_data($username)),
							"email_address"=>self::sanitize($this->decrypt_data($email)),
							"license_key"=>self::sanitize($license_key),
							"latest_ip_address"=>(isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'])
						)
					);
					$return->AUTHENTICATION->STATUS = "SUCCESS";
					$return->AUTHENTICATION->MESSAGE = "You Have Successfully Registered!, Your License Key Is {$license_key}";
				}
			}
		}
		return $this->encrypt_data(json_encode($return));
	}
}