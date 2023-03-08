<html>
	<?php include 'partials/head.php';?>
	<body>
		<?php include 'partials/header.php';?>
		<div class="main-container row" >
<?php	

$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error) die($conn->connect_error);

$userExists = '';

if(isset($_POST['login'])) {$userExists = post();}

function post(){
	
	if(isset($_POST["username"]) && $_POST["username"] == null) 
	{
		return "You must include a username";
	}
	
	if(isset($_POST["password"]) && $_POST["password"] == null) 
	{
		return "You must include a password";
	}
	
	global $conn;

	$tmp_username = mysql_entities_fix_string($conn, $_POST['username']);
	$tmp_password = mysql_entities_fix_string($conn, $_POST['password']);

	$sql = "SELECT * FROM user_account WHERE username = '$tmp_username';";

	$result = $conn->query($sql);
	if(!$result) die($conn->error);

	if ($result->num_rows === 1) {
		$row = $result->fetch_assoc();
		if(password_verify($tmp_password, $row['password'])){
			
			$user = new User($tmp_username);
			$_SESSION['user'] = $user;
				
			switch ($user->role) {
			  case "administrator":
				redirect("admin.php");
				break;
			  case "restaurant":
				redirect("restaurant.php");
				break;
			  case "member":
				redirect("add-member.php");
				break;
			  default:
				return "<span style='color:red;'>Login Failed.  Please try again.</span>";
			}
			redirect("homepage.php");
		} else {
			return "<span style='color:red;'>Login Failed.  Please try again.</span>";
		}
	} else {
	  return "<span style='color:red;'>Login Failed.  Please try again.</span>";
	}
	$conn->close();
}

function redirect($url){
	ob_start();
	echo "<script>window.location.href='$url';</script>";
	ob_end_flush();
	die();
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
				<form action="login.php" method="post">
					<h3>Login Page</h3><br />
					<label>Username: </label>
					<input type="text" name="username"><br />
					<label>Password: </label>
					<input type="password" name="password"><br />
					<input type="submit" value="Login" name="login" id="login-button" class="btn btn-primary">
					<a href="changePassword.php" style="margin-left:100px;">Forgot password</a><br />
					<span style='color:red;'><?php echo $userExists; ?></span>
				</form>	
			</div>
		</div>	
		<?php include 'partials/footer.php'?>	
	</body>
</html>