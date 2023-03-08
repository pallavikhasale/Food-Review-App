<html>
	<?php include 'partials/head.php';?>
	<body>
		<?php include 'partials/header.php';?>
		<div class="main-container row" >
			<div class="col-sm-4 col-sm-offset-4">
<?php	
$page_roles = array('member');
require_once 'checksession.php';
require_once 'partials/sanitize.php';

$_SESSION['validationError'] = '';

$conn = new mysqli($servername, $username, $password, $dbname);
	
if($conn->connect_error){
	die("Connection failed: " . $conn->connection_error);
}

if(isset($_POST['cancel'])){
	echo "<script>window.location.href = 'member.php';</script>";
}

$userId = $firstName = $lastName = $email = '';
if(isset($_SESSION['user'])){
	
	$userId = $user->user_account_id;
	$sql = "SELECT * FROM member WHERE user_account_id = '$userId';";

	$result = $conn->query($sql);
	if(!$result) die($conn->error);
	if ($result->num_rows === 1) {
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$firstName = $row['first_name'];
		$lastName = $row['last_name'];
		$email = $row['email'];
	} elseif ($result->num_rows === 0){
		echo "<script>window.location.href = 'add-member.php';</script>";
	}
}

echo <<<_END
				<form action="update-member.php" method="post">
					<h3>Member Details</h3><br />
					<label>First Name: </label>
					<input type="text" name="firstname" value="$firstName"><br />
					<label>Last Name: </label>
					<input type="text" name="lastname" value="$lastName"><br />
					<label>Email: </label>
					<input type="text" name="email" value="$email"><br />
					<input type="submit" value="Continue" name="continue" id="continue-button" class="btn btn-primary">
					<input type="submit" value="Cancel" name="cancel" class="btn btn-primary save-button"/><br />
					<span style='color:red;'><?php echo $_SESSION[validationError]; ?></span>
				</form>	
_END;
if(isset($_POST['continue']) && isset($_SESSION['user'])){
	$_SESSION['validationError'] = '';
	if(isset($_POST["firstname"]) && $_POST["firstname"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include a first name <br/>";
	}
	
	if(isset($_POST["lastname"]) && $_POST["lastname"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include a last name <br/>";
	}
	
	if(isset($_POST["email"]) && $_POST["email"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include an email <br/>";
	}
	
	if ($_SESSION['validationError'] === ''){
		$firstname = mysql_entities_fix_string($conn, $_POST['firstname']);
		$lastname = mysql_entities_fix_string($conn, $_POST['lastname']);
		$email = mysql_entities_fix_string($conn, $_POST['email']);
		
		$query = "UPDATE member SET first_name = '$firstname', last_name = '$lastname', email = '$email' WHERE user_account_id = $userId;";
		
		$result = $conn->query($query); 
		if(!$result) die($conn->error);

		echo "<script>window.location.href = 'member.php';</script>";
	}
}
?>
			</div>
		</div>	
		<?php include 'partials/footer.php'?>
		<?php $conn->close();?>
	</body>
</html>