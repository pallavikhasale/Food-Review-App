<?php
require_once 'Partials/user.php';

if(!isset($_SESSION['user'])){
	echo "<script>window.location.href = 'login.php';</script>";
	exit();
}else{
	
	$user = $_SESSION['user'];
	$role = $user->role;
	
	if($user->status == "suspended") {
		echo "<script>alert('Error:  Your account has been suspended.  Please contact an administrator for further help.')</script>";
		echo "<script>window.location.href = 'homepage.php';</script>";
	}
	
	$found=0;
	foreach ($page_roles as $prole){
		if($role==$prole){
			$found=1;
		}
	}
	
	if(!$found){
		echo "<script>window.location.href = 'unauthorized.php';</script>";
		exit();
	}
}
?>