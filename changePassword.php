<html>
	<?php include 'partials/head.php';?>
	<body>
		<?php include 'partials/header.php';?>
		<div class="main-container row" >
<?php	

$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error) die($conn->connect_error);

$_SESSION['validationError'] = '';
$redirect = 'login.php';

if(isset($_SESSION['redirected_from'])){
	$redirect = $_SESSION['redirected_from'];
}

if(isset($_POST['cancel'])){
	echo "<script>window.location.href = '$redirect';</script>";
}

if(isset($_POST['continue'])) {
	
	if(isset($_POST["username"]) && $_POST["username"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include a username<br />";
	}
	
	if(isset($_POST["password1"]) && $_POST["password1"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include a password<br />";
	}
	
	if(isset($_POST["password2"]) && $_POST["password2"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must confirm your password<br />";
	}
	
	$tmp_username = mysql_entities_fix_string($conn, $_POST['username']);
	$tmp_password1 = mysql_entities_fix_string($conn, $_POST['password1']);
	$tmp_password2 = mysql_entities_fix_string($conn, $_POST['password2']);
	
	if($tmp_password1 != $tmp_password2){
		$_SESSION['validationError'] = "Your passwords do not match.  Try again.";
	}
	
	$usernameCheck = "SELECT * FROM user_account WHERE username = '$tmp_username';";
	$result = $conn->query($usernameCheck);
	if(!$result) die($conn->error);
	
	if ($result->num_rows != 1) {
		$_SESSION['validationError'] = "This username does not exist.  Please try again.";
	}

	if ($_SESSION['validationError'] === ''){
		$hashed_password = password_hash($tmp_password1, PASSWORD_DEFAULT);
		$query = "UPDATE user_account SET password = '$hashed_password' WHERE username = '$tmp_username';";
		$result = $conn->query($query);
		if(!$result) die($conn->error);
		echo "<script>window.location.href = '$redirect';</script>";
	}
}

function mysql_entities_fix_string($conn, $string){
	return htmlentities(mysql_fix_string($conn, $string));	
}

function mysql_fix_string($conn, $string){
	$string = stripslashes($string);
	return $conn->real_escape_string($string);
}

?>
			<div class="col-sm-4 col-sm-offset-4">
				<form action="changePassword.php" method="post">
					<h3>Change Password</h3><br />
					<label>Username: </label>
					<input type="text" name="username"><br />
					<label>New Password: </label>
					<input type="password" name="password1"><br />
					<label>Confirm Password: </label>
					<input type="password" name="password2"><br />
					<input type="submit" value="Continue" name="continue" class="btn btn-primary">
					<input type="submit" value="Cancel" name="cancel" class="btn btn-primary"/><br />
					<span style='color:red;'><?php echo $_SESSION['validationError']; ?></span>
				</form>	
			</div>
		</div>	
		<?php include 'partials/footer.php'?>
		<?php $conn->close();?>
	</body>
</html>