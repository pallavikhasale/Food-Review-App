<html>
	<?php include 'partials/head.php';?>
	<body id="body">
		<?php include 'partials/header.php';?>
		<div class="main-container row">
			<div class="form-center col-lg-4 col-lg-offset-4">
<?php

$page_roles = array('administrator');
require_once 'checksession.php';
require_once 'partials/sanitize.php';

$_SESSION['validationError'] = '';
$updateUserId = $user_name = $role = $status = '';
$firstName = $lastName = $email = $A = $R = $M = '';
$active = $suspended = '';
$confirmation = '';

$conn = new mysqli($servername, $username, $password, $dbname);
	
if($conn->connect_error){
	die("Connection failed: " . $conn->connection_error);
}

if(isset($_POST['cancel'])){
	echo "<script>window.location.href = 'admin.php';</script>";
}

if(isset($_POST['continue']) && isset($_SESSION['update_user_id'])){
	$_SESSION['validationError'] = '';
	$updateUserId = $_SESSION['update_user_id'];
	if(isset($_POST["user_name"]) && $_POST["user_name"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include a user name <br/>";
	}
	
	if(isset($_POST["role"]) && $_POST["role"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include a role <br/>";
	}
	
	if(isset($_POST["status"]) && $_POST["status"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include a status <br/>";
	}
	
	if(isset($_POST["user_name"]) && $_POST["user_name"] != null) 
	{
		$user_name = mysql_entities_fix_string($conn, $_POST['user_name']);
		$query = "SELECT * FROM user_account WHERE username = '$user_name';";
		$result = $conn->query($query);
		if(!$result) die($conn->error);
		if ($result->num_rows === 1) {
			$row = $result->fetch_array(MYSQLI_ASSOC);
			if($row['user_account_id'] != $updateUserId){
				$_SESSION['validationError'] = 'This username has already been taken.  Please try again.';
			}
		}
	}
	
	if ($_SESSION['validationError'] === ''){
		$user_name = mysql_entities_fix_string($conn, $_POST['user_name']);
		$role = mysql_entities_fix_string($conn, $_POST['role']);
		$status = mysql_entities_fix_string($conn, $_POST['status']);
		
		$query = "SELECT * FROM user_account WHERE username = '$user_name';";
		$result = $conn->query($query);
		if(!$result) die($conn->error);
		if ($result->num_rows === 1) {	
			$row = $result->fetch_array(MYSQLI_ASSOC);
			if($row['user_account_id'] != $updateUserId){
				$_SESSION['validationError'] = 'This username has already been taken.  Please try again.';
				return;
			}
		}
		
		$query = "UPDATE user_account SET username = '$user_name', role = '$role', status = '$status' WHERE user_account_id = $updateUserId;";
		//$query = "UPDATE user_account SET first_name = '$firstname', last_name = '$lastname', email = '$email' WHERE user_account_id = $userId;";
		$result = $conn->query($query); 
		if(!$result) die($conn->error);
		
		$query = "SELECT * FROM member WHERE user_account_id = $updateUserId;";
		$result = $conn->query($query);
		if(!$result) die($conn->error);
		
		$firstname = mysql_entities_fix_string($conn, $_POST['firstname']);
		$lastname = mysql_entities_fix_string($conn, $_POST['lastname']);
		$email = mysql_entities_fix_string($conn, $_POST['email']);
			
		if ($result->num_rows === 1) {			
			$query = "UPDATE member SET first_name = '$firstname', last_name = '$lastname', email = '$email' WHERE user_account_id = $updateUserId;";
			$result = $conn->query($query); 
			if(!$result) die($conn->error);			
		} else if ($result->num_rows === 0 && $role == "member"){
			$query = "INSERT INTO member (first_name, last_name, email, user_account_id) VALUES ('$firstname', '$lastname', '$email', '$updateUserId')";
			$result = $conn->query($query); 
			if(!$result) die($conn->error);
		}
		$confirmation = 'The user was successfully updated';
	}
}

if(isset($_SESSION['update_user_id'])){
	
	$updateUserId = $_SESSION['update_user_id'];
	$sql = "SELECT ua.*, m.first_name, m.last_name, m.email 
			FROM user_account ua
			LEFT JOIN member m
			  ON m.user_account_id = ua.user_account_id
			WHERE ua.user_account_id = $updateUserId;";

	$result = $conn->query($sql);
	if(!$result) die($conn->error);
	if ($result->num_rows === 1) {
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$user_name = $row['username'];
		$status = $row['status'];
		if($status == "active") $active = "selected";
		if($status == "suspended") $suspended = "selected";
		$firstName = $row['first_name'];
		$lastName = $row['last_name'];
		$email = $row['email'];
		switch ($row['role']) {
			  case "administrator":
				$A = "checked";
				break;
			  case "restaurant":
				$R = "checked";
				break;
			  case "member":
				$M = "checked";
				$visibility = '';
				break;
			  default:
				return $_SESSION['validationError'] = 'Unable to determine role.';
			}
	} elseif ($result->num_rows === 0){
		$_SESSION['validationError'] = 'User was unable to be updated.';
	}
}

echo <<<_END

				<form action="update-user.php" method="post">
					<h3>User Info</h3><br />
					<label style="margin: 5px;">Username: </label>
					<input style="margin: 5px;" type="text" name="user_name" value="$user_name"><br />
					<div style="margin: 5px 25px;">
						<input type="radio" id="administrator" name="role" value="administrator" $A>
						<label for ="administrator">Administrator</label><br />
						<input type="radio" id="restaurant" name="role" value="restaurant" $R>
						<label for ="restaurant">Restaurant</label><br />
						<input type="radio" id="member" name="role" value="member" $M>
						<label for ="member">Member</label><br />
					</div>
					<label style="margin: 5px;">Status: </label>
					<select name="status">
						<option value='active' $active>Active</option>
						<option value='suspended' $suspended>Suspended</option>
					</select><br />
					<div style="padding-left:20px;" class="well">
						<h3>If the user has a role of "Member", then fill out the info below:</h3>
						<label>First Name: </label>
						<input type="text" name="firstname" value="$firstName"><br />
						<label>Last Name: </label>
						<input type="text" name="lastname" value="$lastName"><br />
						<label>Email: </label>
						<input type="text" name="email" value="$email"><br />
					</div>
					<input type="submit" value="Continue" name="continue" id="continue-button" class="btn btn-primary">
					<input type="submit" value="Cancel" name="cancel" class="btn btn-primary save-button"/><br />
					<span style='color:red;'>$_SESSION[validationError]</span>
					<span style='color:#337ab7;'>$confirmation</span>
				</form>	

_END;
?>
			</div>
		</div>
		<?php include 'partials/footer.php'?>
		<?php $conn->close();?>
	</body>
</html>