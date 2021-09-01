<?php
header("Content-Type: application/json");
include "authentication.class.php";
$authentication = new authentication;
if(isset($_POST['TYPE'])){
	switch(strtoupper($_POST['TYPE'])){
		case "LOGIN":
			echo $authentication->login($_POST['LICENSE_KEY']);
		break;
		case "REGISTER":
			echo $authentication->register($_POST['USERNAME'], $_POST['EMAIL_ADDRESS']);
		break;
	}
}