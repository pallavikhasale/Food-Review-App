<html>
	<?php include 'partials/head.php';?>
	<body id="body">
		<?php include 'partials/header.php';?>
		<div class="main-container">
			<div class="row">
				<div class="col-sm-offset-3 col-sm-6 tab-content content-container">
<?php
$page_roles = array('administrator', 'restaurant');
require_once 'checksession.php';
require_once 'Partials/states.php';
require_once 'partials/sanitize.php';

$_SESSION['validationError'] = '';

$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error) die($conn->connect_error);

$query = "SELECT * FROM subscription;";
$subscriptions = $conn->query($query);
if(!$subscriptions) die($conn->error);

$restaurant_id = '';
if (isset($_SESSION['restaurant_id'])) {
	$restaurant_id = $_SESSION['restaurant_id'];
	$query = "SELECT * FROM restaurant WHERE restaurant_id = '$restaurant_id;'";
	$result = $conn->query($query); 
	if(!$result) die($conn->error);
	$row = $result->fetch_array(MYSQLI_ASSOC);
}

$roleQuery = '';
if($user->role == 'administrator'){
	$roleQuery = "  SELECT ua.user_account_id,  ua.username
					FROM user_account ua
					JOIN restaurant r
					 ON r.user_account_id = ua.user_account_id
					WHERE r.restaurant_id = $restaurant_id
					UNION
					SELECT user_account_id,  username
					FROM user_account 
					WHERE role = 'restaurant'
					AND user_account_id NOT IN (
						SELECT user_account_id 
						FROM restaurant
					)";
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
			
		$query = "Update restaurant set restaurant_name = '$name', description = '$description', address1 = '$address1', address2 = '$address2', city = '$city', state = '$state', zipcode = '$zipcode', phone = '$phone', owner_first_name = '$ownerFirstName', owner_last_name = '$ownerLastName', email = '$email', restaurant_type = '$type', user_account_id = '$userId', subscription_id = '$subscription' WHERE restaurant_id = '$restaurant_id'";
		
		$result = $conn->query($query);
		if(!$result){
			die($conn->error);
			echo "<script>alert('Error:  The record was not saved')</script>";
		} else {
			echo "<script>alert('Success: The restaurant was updated successfully')</script>";
			if($user->role == 'administrator'){
				echo "<script>window.location.href = 'admin.php';</script>";
			} else {
				echo "<script>window.location.href = 'restaurant.php';</script>";
			}
		}
	}	
}

echo <<<_END
<form class="form-horizontal" method="post" action="update-restaurant.php">
	<h3>Add New Restaurant:</h3>
	<div class="form-group">
		<label class="control-label col-sm-4" for="user_account">Restaurant User Account:</label>
		<div class="col-sm-8">
			<select name="user_account">
			<option value="" disabled selected></option>
_END;
if ($users->num_rows > 1) {
	while ($u = $users->fetch_assoc()){
		if($u['user_account_id'] == $row['user_account_id']) $selected = "selected"; 
		else $selected = "";
		echo "<option value='$u[user_account_id]' $selected>$u[username]</option>";	
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
			<input type="name" class="form-control" name="name" value="$row[restaurant_name]" >
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="description">Description:</label>
		<div class="col-sm-8">
			<textarea class="form-control" rows="7" name="description" >$row[description]</textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="address1">Address:</label>
		<div class="col-sm-8">
			<input class="form-control" name="address1" value="$row[address1]">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="adddress2">Address2:</label>
		<div class="col-sm-8">
			<input class="form-control" name="address2" value="$row[address2]">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="city">City:</label>
		<div class="col-sm-8">
			<input class="form-control" name="city" value="$row[city]">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="state">State:</label>
		<div class="col-sm-8">
			<select name="state">
_END;
foreach ($us_state_abbrevs_names as $key=>$value){
	$selected = '';
	if ($key == $row['state']) $selected = 'selected';
	echo "<option value=$key $selected>$value</option>";		
}

echo <<<_END
			</select>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="zipcode">Zipcode:</label>
		<div class="col-sm-8">
			<input class="form-control" name="zipcode" value="$row[zipcode]">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="phone">Phone:</label>
		<div class="col-sm-8">
			<input class="form-control" name="phone" value="$row[phone]">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="ownerFirstName">Owner First Name:</label>
		<div class="col-sm-8">
			<input class="form-control" name="ownerFirstName" value="$row[owner_first_name]">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="ownerLastName">Owner Last Name:</label>
		<div class="col-sm-8">
			<input class="form-control" name="ownerLastName" value="$row[owner_last_name]">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="email">Email:</label>
		<div class="col-sm-8">
			<input class="form-control" name="email" value="$row[email]">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="type">Restaurant Type:</label>
		<div class="col-sm-8">
			<input class="form-control" name="type" value="$row[restaurant_type]">
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-4" for="subscription">Subscription:</label>
		<div class="col-sm-8">
			<select name="subscription">
			<option value="" disabled>Select a subscription type</option>
_END;
if ($subscriptions->num_rows > 0) {
	while ($subscription = $subscriptions->fetch_assoc()){
		$selected = '';
		if ($subscription['subscription_id'] == $row['subscription_id']) $selected = 'selected';
		echo "<option value=$subscription[subscription_id] $selected>$subscription[subscription_name]</option>";		
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
	</div>
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