<!doctype html>
<html>
	<?php include 'partials/head.php';?>
	<body id="body">
		<?php include 'partials/header.php';?>
		<div class="main-container row">
			<h2 style="text-align:center;">Welcome to the member homepage!</h2><hr />
<?php 
	$page_roles = array('member');
	require_once 'checksession.php';
	require_once 'partials/sanitize.php';
	$_SESSION['validationError'] = '';
	$_SESSION['confirmation'] = '';
	
	function getTruncatedCCNumber($cardnumber){
        return str_replace(range(0,9), "*", substr($cardnumber, 0, -4)) .  substr($cardnumber, -4);
    }
	
	//Checking Connection
	$conn = new mysqli($servername, $username, $password, $dbname);

	if ($conn->connect_error){
		die("Connection failed: " . $conn->connection_error);
	}
	
	//Retrieve data from database
	$restau_namecheck = "SELECT restaurant_name, restaurant_id FROM restaurant";
	$restau_result = $conn->query($restau_namecheck);
	
	$optSelect = '';
	$disabled = "disabled";
	if(isset($_POST['Restaurant'])) {
		$optSelect = mysql_entities_fix_string($conn, $_POST['Restaurant']);
		$disabled = '';
	}
	
	$get_food_items = "SELECT food_id, name FROM food WHERE restaurant_id = $optSelect";
	$food_items = $conn->query($get_food_items);
	
	$user = '';
	if(isset($_SESSION['user'])){
		$user = $_SESSION['user'];
	}
	
	$member_id = '';
	if(isset($user)){
		if($user->role == 'Administrator'){
			$member_id = -1;
		} else {
			$get_member_info = "SELECT member_id FROM member WHERE user_account_id = $user->user_account_id;";
			$member_info = $conn->query($get_member_info);
			if(!$member_info) die($conn->error);
			$row = $member_info->fetch_array(MYSQLI_ASSOC);
			if(isset($row['member_id'])){
				$member_id = $row['member_id'];
			}
		}
	}
?>
			<div class="row">
				<div class="col-sm-4">
					<?php include 'Partials/food-ratings.php'; ?>
				</div>
				
				<div class="col-sm-4">
					<div id="newReview" class="well">
						<h3>New Review:</h3>
						<div class="list-group"style="min-height: 50vh;">
<?php
if(isset($_POST['save'])){
	$_SESSION['validationError'] = '';
	
	if(!isset($_POST["fooditem"])) $_POST["fooditem"] = '';
	if(!isset($_POST["rating"])) $_POST["rating"] = '';
	if(!isset($_POST["review"])) $_POST["review"] = '';

	if(isset($_POST["fooditem"]) && $_POST["fooditem"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include a food item <br/>";
	}
	
	if(isset($_POST["rating"]) && $_POST["rating"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include a rating <br/>";
	}
	
	if(isset($_POST["review"]) && $_POST["review"] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must include a review <br/>";
	}
	
	if(!isset($_SESSION['user']) || $_SESSION['user'] == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."You must log in to continue <br/>";
	}
	
	if(!isset($member_id) || $member_id == -1 || $member_id == null) 
	{
		$_SESSION['validationError'] = $_SESSION['validationError']."Only members are allowed to create food reviews. <br/>";
	}
	
	if ($_SESSION['validationError'] === ''){
		$review = mysql_entities_fix_string($conn, $_POST['review']);
		$rating = mysql_entities_fix_string($conn, $_POST['rating']);
		$date = date('Y-m-d H:i:s');
		$foodId = mysql_entities_fix_string($conn, $_POST['fooditem']);
			
		$query = "INSERT INTO review (review_text, rating, date, food_id, member_id) VALUES ('$review', '$rating', '$date', '$foodId', '$member_id')";
		
		$result = $conn->query($query);
		if(!$result){
			die($conn->error);
			echo "<script>alert('Error:  The record was not saved')</script>";
		} else {
			$_SESSION['confirmation'] = "The review was successfully created!";
			echo "<script>window.location = window.location.href;</script>";
		}
	}
}
?>
							<form method="post" id="myform" name="myform" class="form-horizontal">
								<div class="form-group">
									<label class="control-label col-sm-3" for="restaurant">Restaurant:</label>
									<div class="col-sm-9">
										<select name="Restaurant" id="Restaurant" onchange="myform.submit();" style="position:relative; top:10px;">
										<option value="" disabled selected>Select a restaurant</option>
<?php
if ($restau_result->num_rows > 0) {
	while ($row = $restau_result->fetch_assoc()){		
		echo "<option value='$row[restaurant_id]' ";
		if (isset($row['restaurant_id']) && $optSelect == $row['restaurant_id']) {
			echo "selected";
		}
		echo ">$row[restaurant_name]</option>";
	}
}
else {echo "<p> 0 results</p>";}
?>
										</select>
									</div>
								</div>
							</form>
							<form method="post" id="foodForm" class="form-horizontal">
								<div class="form-group">
									<label class="control-label col-sm-3" for="fooditem">Food Item:</label>
									<div class="col-sm-9">
										<select name="fooditem" id="fooditem" style="position:relative; top:10px;" <?php echo $disabled; ?>>
										<option value="" disabled selected>Select a food item</option>
											<?php
											if ($food_items->num_rows > 0) {
												while ($items = $food_items->fetch_assoc()){
													echo '<option value='.$items['food_id'].'>'.$items['name'].'</option>';		
												}
											}
											else {echo "<p> 0 results</p>";}

											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-sm-3" for="rating">Food Rating:</label>
									<div class="col-sm-9">
										<select name="rating" id="rating" style="position:relative; top:10px;" <?php echo $disabled; ?>>
										<option value="" disabled selected>Select a food rating</option>
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
											<?php
											if ($food_items->num_rows > 0) {
												while ($items = $food_items->fetch_assoc()){
													echo '<option value='.$items['name'].'>'.$items['name'].'</option>';		
												}
											}
											else {echo "<p> 0 results</p>";}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-sm-3" for="review">Review:</label>
									<div class="col-sm-9">
										<textarea class="form-control" rows="7" id="review" name="review" placeholder="Add Review" <?php echo $disabled; ?>></textarea>
									</div>
								</div>
								<div class="form-group" style="margin-left:0px;">
									<input type="submit" value="Save" name="save" class="btn btn-primary save-button" <?php if($_SESSION['validationError'] != ''){echo "disabled";} ?>></input><br />
									<span style='color:red;'><?php echo $_SESSION['validationError']; ?></span>
									<span style='color:#337ab7;'><?php echo $_SESSION['confirmation']; ?></span>
								</div>
							</form>
						</div>
					</div>
				</div>
				
			<?php
				if(isset($_POST['more-restaurants'])){
					$_SESSION['redirected_from'] = "member.php";
					echo "<script>window.location.href = 'restaurantlist.php';</script>";
				}
			
			?>
            <div class="col-sm-4">
				<div class="well">
					<h3>Restaurants <span class="glyphicon glyphicon-cutlery"></h3>
					  <div class="list-group"style="overflow-y: scroll; height: 50vh;">
						<?php
						//Retrieve data from database
						$restau_namecheck = "SELECT restaurant_name FROM restaurant";
						$result = $conn->query($restau_namecheck);

						if ($result->num_rows > 0) {
						while ($row = $result->fetch_assoc()){
						?>
						<a href="#" class="list-group-item"><?php echo $row["restaurant_name"]; ?></a>
						<?php
						}
						}
						else {
						echo "<p> 0 results</p>";
						}

						?>
						<form method="post" action="member.php">
							<br />
							<input type="submit" name= "more-restaurants" value="View More Restaurants"><br />
						</form>
					  </div>
				</div>
			</div>
				
			</div>
		</div>
		<?php include 'partials/footer.php'?>
		<?php $conn->close();?>
	</body>
</html>