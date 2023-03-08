<?php
require_once 'credentials.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die($conn->connect_error);

class User{
	
	public $user_account_id;
	public $username;
	public $role;
	public $status;
	
	function __construct($username){
		global $conn;
				
		$this->username = $username;
		
		$query="select * from user_account where username='$username' ";
		
		$result = $conn->query($query);
		if(!$result) die($conn->error);
		
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$this->user_account_id = $row['user_account_id'];
		$this->role = $row['role'];
		$this->status = $row['status'];
	}
}
?>