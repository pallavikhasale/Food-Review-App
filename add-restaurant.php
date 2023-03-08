<html>
	<?php include 'partials/head.php';?>
	<body id="body">
		<?php include 'partials/header.php';?>
		<div class="main-container">
			<div class="row">
				<div class="col-sm-offset-3 col-sm-6 tab-content content-container">
<?php
$page_roles = array('administrator');
require_once 'checksession.php';
require_once  'Partials/states.php';
require_once 'partials/sanitize.php';

$_SESSION['validationError'] = '';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error){
	die("Connection failed: ".$conn->connection_error);
}

$query = "SELECT * FROM subscription;";
$result = $conn->query($query);
if(!$result) die($conn->error);

$roleQuery = '';
if($user->role == 'administrator'){
	$roleQuery = "SELECT * 
			FROM user_account 
			WHERE role = 'restaurant'
			AND user_account_id NOT IN (
				SELECT user_account_id 
				FROM restaurant
			)
			;";
} else {
	$roleQuery = "SELECT * 
			FROM user_account 
			WHERE user_account_id = '$user->user_account_id';";
}

$users = $conn->query($roleQuery);
if(!$users) die($conn->error);

if(isset($_POST['cancel'])){
	if($user->role == 'administrator'){
		echo "<script>window.location.href = 'admin.php';</script>";
	} else {
		echo "<script>window.location.href = 'restaurant.php';</script>";
	}
}

if(isset($_POST['save'])){
	$_SESSION['validationError'] = '';
	
	if(!isset($_POST["name"])) $_POST["name"] = '';
	if(!isset($_POST["description"])) $_POST["description"] = '';
	if(!isset($_POST["address1"])) $_POST["address1"] = '';
	if(!isset($_POST["address2"])) $_POST["address2"] = '';
	if(!isset($_POST["city"])) $_POST["city"] = '';
	if(!isset($_POST["state"])) $_POST["state"] = '';
	if(!isset($_POST["zipcode"])) $_POST["zipcode"] = '';
	if(!isset($_POST["phone"])) $_POST["phone"] = '';
	if(!isset($_POST["ownerFirstName"])) $_POST["ownerFirstName"] = '';
	if(!isset($_POST["ownerLastName"])) $_POST["ownerLastName"] = '';
	if(!isset($_POST["email"])) $_POST["email"] = '';
	if(!isset($_POST["type"])) $_POST["type"] = '';
	if(!isset($_POST["user_account"])) $_POST["user_account"] = '';
	if(!isset($_POST["subscription"])) $_POST["subscription"] = '';

	if(isset($_POST["name"]) && $_POST["name"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include a restaurant name <br/>";
	}
	
	if(isset($_POST['subscription']) && $_POST['subscription'] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must select a subscription <br/>";
	}
	
	if(isset($_POST['user_account']) && $_POST['user_account'] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must select a user to manage this restaurant <br/>";
	}
	
	if(!isset($_SESSION['user']) || $_SESSION['user'] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must log in to continue <br/>";
	}

	if ($_SESSION['validationError'] === ''){
		$name = mysql_entities_fix_string($conn, $_POST['name']);
		$description = mysql_entities_fix_string($conn, $_POST['description']);
		$address1 = mysql_entities_fix_string($conn, $_POST['address1']);
		$address2 = mysql_entities_fix_string($conn, $_POST['address2']);
		$city = mysql_entities_fix_string($conn, $_POST['city']);
		$state = mysql_entities_fix_string($conn, $_POST['state']);
		$zipcode = mysql_entities_fix_string($conn, $_POST['zipcode']);
		$phone = mysql_entities_fix_string($conn, $_POST['phone']);
		$ownerFirstName = mysql_entities_fix_string($conn, $_POST['ownerFirstName']);
		$ownerLastName = mysql_entities_fix_string($conn, $_POST['ownerLastName']);
		$email = mysql_entities_fix_string($conn, $_POST['email']);
		$type = mysql_entities_fix_string($conn, $_POST['type']);
		$userId = mysql_entities_fix_string($conn, $_POST['user_account']);
		$subscription = mysql_entities_fix_string($conn, $_POST['subscription']);
			
		$query = "INSERT INTO restaurant (restaurant_name, description, address1, address2, city, state, zipcode, phone, owner_first_name, owner_last_name, email, restaurant_type, user_account_id, subscription_id) VALUES ('$name', '$description', '$address1', '$address2', '$city', '$state', '$zipcode', '$phone','$ownerFirstName', '$ownerLastName', '$email', '$type', $userId, $subscription)";
		
		$result = $conn->query($query);
		if(!$result){
			die($conn->error);
			echo "<script>alert('Error:  The record was not saved')</script>";
		} else {
			echo "<script>alert('Success: The restaurant was added successfully')</script>";
			echo "<script>window.location.href = 'admin.php';</script>";
		}
	}
}
echo <<<_END
<form class="form-horizontal" method="post" action="add-restaurant.php">
	<h3>Add New Restaurant:</h3>
	<div class="form-group">
		<label class="control-label col-sm-4" for="user_account">Restaurant User Account:</label>
		<div class="col-sm-8">
			<select name="user_account">
			<option value="" disabled selected></option>
_END;
if ($users->num_rows > 1) {
	while ($u = $users->fetch_assoc()){
		echo "<option value='$u[user_account_id]'>$u[username]</option>";	
	}
} elseif ($users->num_rows == 1) {
	while ($u = $users->fetch_assoc()){
		echo "<option value='$u[user_account_id]' selected>$u[username]</option>";	
	}
} else {
	echo "<option value='' selected>no available users</option>";	
}
echo <<<_END
			</select>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="name">Restaurant Name:</label>
		<div class="col-sm-8">
			<input type="name" class="form-control" name="name">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="description">Description:</label>
		<div class="col-sm-8">
			<textarea class="form-control" rows="7" name="description"></textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="address1">Address:</label>
		<div class="col-sm-8">
			<input class="form-control" name="address1">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="adddress2">Address2:</label>
		<div class="col-sm-8">
			<input class="form-control" name="address2">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="city">City:</label>
		<div class="col-sm-8">
			<input class="form-control" name="city">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="state">State:</label>
		<div class="col-sm-8">
			<select name="state">
			<option value="" disabled selected></option>
_END;
foreach ($us_state_abbrevs_names as $key=>$value){
	echo "<option value=$key>$value</option>";		
}
echo <<<_END
			</select>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="zipcode">Zipcode:</label>
		<div class="col-sm-8">
			<input class="form-control" name="zipcode">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="phone">Phone:</label>
		<div class="col-sm-8">
			<input class="form-control" name="phone">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="ownerFirstName">Owner First Name:</label>
		<div class="col-sm-8">
			<input class="form-control" name="ownerFirstName">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="ownerLastName">Owner Last Name:</label>
		<div class="col-sm-8">
			<input class="form-control" name="ownerLastName">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="email">Email:</label>
		<div class="col-sm-8">
			<input class="form-control" name="email">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="type">Restaurant Type:</label>
		<div class="col-sm-8">
			<input class="form-control" name="type">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="subscription">Subscription:</label>
		<div class="col-sm-8">
			<select name="subscription">
			<option value="" disabled selected></option>
_END;
if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()){
		echo "<option value=$row[subscription_id]>$row[subscription_name]</option>";		
	}
}
else {echo "<p> 0 results</p>";}
echo <<<_END
			</select>
		</div>
	</div>
	<div class="form-group">
		<input type="submit" value="Save" name="save" class="btn btn-primary save-button"/>
		<input type="submit" value="Cancel" name="cancel" class="btn btn-primary save-button"/>
	</div><br />
	<span style='color:red;'>$_SESSION[validationError]</span>
</form>
_END;
?>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<?php include 'partials/footer.php'?>
		<?php $conn->close();?>
	</body>
</html>