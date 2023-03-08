<!doctype html>
<html>
	<?php include 'partials/head.php';?>
	<body id="body">
	<?php include 'partials/header.php';?>
	<hr>
	<div class="row middle-box" align="center">
	<hr>
	<?php 
	$page_roles = array('member', 'administrator');
	require_once 'checksession.php';

	$user = '';
	if(isset($_SESSION['user'])){
		$user = $_SESSION['user'];	
	}

	$member_id = '-1';
	if(isset($user)){
		$get_member_info = "SELECT member_id FROM member WHERE user_account_id = $user->user_account_id;";
		$member_info = $conn->query($get_member_info);
		if(!$member_info) die($conn->error);
		$row = $member_info->fetch_array(MYSQLI_ASSOC);
		if(isset($row['member_id'])){
			$member_id = $row['member_id'];
		}
	}
	
	if(isset($_POST['follow'])){
		$restaurant_id  = $_POST['follow'];
		$sql = "INSERT INTO follow (restaurant_id, member_id) VALUES ('$restaurant_id', '$member_id');";
		$result = $conn->query($sql);
		if(!$result) die($conn->error);
	}
	
	if(isset($_POST['unfollow'])){
		$restaurant_id  = $_POST['unfollow'];
		$sql = "DELETE FROM follow WHERE restaurant_id = '$restaurant_id' AND member_id = '$member_id';";
		$result = $conn->query($sql);
		if(!$result) die($conn->error);
	}

	$follow_query = "SELECT * FROM follow WHERE member_id = $member_id;";
	$result = $conn->query($follow_query);
	if(!$result) die($conn->error);

	$followedRestaurants = array();
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()){
			array_push($followedRestaurants, $row['restaurant_id']);
		}
	}

	$getRestaurants = "SELECT restaurant_id, restaurant_name, description FROM restaurant";
	$result = $conn->query($getRestaurants);

	if ($result->num_rows > 0 ) {
		while ($restaurant = $result->fetch_assoc()){

	?>
	<div class="col-sm-4">
		<div class="card" style="width:400px">
    		<img class="picture-img-top" src="content/Restaurant5.jpeg" alt="restaurant picture" style="width:80%">
    		<div class="restaurantlist-body">
      		<h4 class="restaurantlist-title" ><?php echo $restaurant["restaurant_name"]; ?></h4>
      		<p class="restaurantlist-text"><?php echo $restaurant["description"]; ?></p>
      		<form method="post">
			<?php
			if($user->role == 'member'){
				if(in_array($restaurant['restaurant_id'], $followedRestaurants)){
					echo "<button id='$restaurant[restaurant_id]' value='$restaurant[restaurant_id]' name='unfollow'>Unfollow</button>";
				} else {
					echo "<button id='$restaurant[restaurant_id]' value='$restaurant[restaurant_id]' name='follow'>Follow</button>";
				}
			}
			?>
      		<p></p>
      		</form>
   	       </div>
 		</div>
	</div>
	<?php 
		}
	} else {
		echo "<p> 0 results</p>";
	}
    ?>
	</div>
		<?php include 'partials/footer.php'?>
		<?php $conn->close();?>
	</body>
</html>