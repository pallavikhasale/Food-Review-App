<!doctype html>
<html>
	<?php include 'partials/head.php';?>
	<body>
		<?php include 'partials/header.php';?>
		<div class="main-container row" >
			<div class="form-center col-lg-4 col-lg-offset-2">
<?php
$_SESSION['validationError'] = '';

require_once 'partials/sanitize.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error){
	die("Connection failed: " . $conn->connection_error);
}
	
if(isset($_POST['submit'])) {
	$_SESSION['validationError'] = '';
	
	if(!isset($_POST["username"])) $_POST["username"] = '';
	if(!isset($_POST["password"])) $_POST["password"] = '';
	if(!isset($_POST["role"])) $_POST["role"] = '';
	
	if(isset($_POST["username"]) && $_POST["username"] == null) 
	{
		$_SESSION['validationError'] = "You must include a username";
	}
	
	if(isset($_POST["password"]) && $_POST["password"] == null) 
	{
		$_SESSION['validationError'] = "You must include a password";
	}
	
	if(isset($_POST["role"]) && $_POST["role"] == null) 
	{
		$_SESSION['validationError'] = "You must include a role";
	}
	
	if ($_SESSION['validationError'] === ''){
		$usernameCheck = "SELECT * FROM user_account WHERE username = '".mysql_entities_fix_string($conn, $_POST["username"])."';";
		$result = $conn->query($usernameCheck);
		if(!$result) die($conn->error);
		
		if ($result->num_rows > 0) {
			$_SESSION['validationError'] = "This username has already been taken.  Please try again.";
		} else {
			$hashed_password = password_hash(mysql_entities_fix_string($conn, $_POST["password"]), PASSWORD_DEFAULT);
			$sql = "INSERT INTO user_account (username, password, role, status)
			VALUES ('".$_POST["username"]."', '".$hashed_password."', '".mysql_entities_fix_string($conn, $_POST["role"])."', 'active')";
			if ($conn->query($sql) === TRUE) {
				$sql = "SELECT * FROM user_account WHERE username = '".mysql_entities_fix_string($conn, $_POST["username"])."';";
				$newUser = $conn->query($sql);
				if(!$newUser) die($conn->error);
				$row = $newUser->fetch_array(MYSQLI_ASSOC); 
				if($row["role"] === "member") {
					$_SESSION['user_account_id'] = $row['user_account_id'];
					redirect('add-member.php');
				} else {
					redirect('login.php');
				}
			} else {
			  echo "Error: " . $sql . "<br>" . $conn->error;
			}
		}
	}
}

function redirect($url){
	ob_start();
	echo "<script>window.location.href='$url';</script>";
	ob_end_flush();
	die();
}
?>
				<form action="signup.php" method="post">
					<h3>Sign Up</h3><br />
					<label style="margin: 5px;">Username: </label>
					<input style="margin: 5px;" type="text" name="username"><br />
					<label style="margin: 5px;">Password: </label>
					<input style="margin: 5px;" type="password" name="password"><br />
					<div style="margin: 5px 25px;">
						<input type="radio" id="administrator" name="role" value="administrator">
						<label for ="administrator">Administrator</label><br />
						<input type="radio" id="restaurant" name="role" value="restaurant">
						<label for ="restaurant">Restaurant</label><br />
						<input type="radio" id="member" name="role" value="member">
						<label for ="member">Member</label><br />
					</div>
					<input style="margin: 5px;" type="submit" value="Create Account" name="submit" class="btn btn-primary"><br />
					<span style='color:red;'><?php echo $_SESSION['validationError']; ?></span>
				</form>	
			</div>
			<div class="col-lg-6">
				<img src="Content/signUp1.jpeg" height="400" width="400" style="padding-top:28px;">
			</div>
		</div>
		<?php include 'partials/footer.php'?>
		<?php $conn->close();?>
	</body>
</html>