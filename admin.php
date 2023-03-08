<!doctype html>
<html>
	<?php include 'partials/head.php';?>
	<body id="body">
		<?php include 'partials/header.php';?>
		<div class="main-container row">
			<h2 style="text-align:center;">Welcome to the administrator homepage!</h2><hr />
			<?php
				$page_roles = array('administrator');
				require_once 'checksession.php';
				require_once 'partials/sanitize.php';
				
				$validationError = '';
				$validationError_updateRestaurant = '';
				$validationError_deleteRestaurant = '';
				$validationError_updateUser = '';
				$validationError_deleteUser = '';
				$validationError_generateTransactionReport = '';
				$_SESSION['redirected_from'] = "admin.php";
				
				//Checking Connection
				$conn = new mysqli($servername, $username, $password, $dbname);
			
				if ($conn->connect_error){
					die("Connection failed: " . $conn->connection_error);
				}
				
				if(isset($_POST['update-restaurant'])){
					if(isset($_POST['restaurant_id']) && $_POST['restaurant_id'] != null) {
						$_SESSION['restaurant_id'] = mysql_entities_fix_string($conn, $_POST['restaurant_id']);
						$_SESSION['redirected_from'] = "admin.php";
						echo "<script>window.location.href = 'update-restaurant.php';</script>";
					}
					else $validationError_updateRestaurant = "<br /><span style='color:red;'>You must select a restaurant to continue.</span>";
				}
				
				if(isset($_POST['delete-restaurant'])){
					if(isset($_POST['restaurant_id']) && $_POST['restaurant_id'] != null) {
						$restaurantId = mysql_entities_fix_string($conn, $_POST['restaurant_id']);
						$query = "DELETE FROM restaurant WHERE restaurant_id = $restaurantId;";
						$result = $conn->query($query); 
						if(!$result) die($conn->error);
					}
					else $validationError_deleteRestaurant = "<br /><span style='color:red;'>You must select a restaurant to continue.</span>";
				}
				
				if(isset($_POST['update-user'])){
					if(isset($_POST['user_id']) && $_POST['user_id'] != null) {
						$_SESSION['update_user_id'] = mysql_entities_fix_string($conn, $_POST['user_id']);
						$_SESSION['redirected_from'] = "admin.php";
						echo "<script>window.location.href = 'update-user.php';</script>";
					}
					else $validationError_updateUser = "<br /><span style='color:red;'>You must select a user to continue.</span>";
				}
				
				if(isset($_POST['delete-user'])){
					if(isset($_POST['user_id']) && $_POST['user_id'] != null) {
						$_SESSION['update_user_id'] = mysql_entities_fix_string($conn, $_POST['user_id']);
						$query = "DELETE FROM user_account WHERE user_account_id = $_SESSION[update_user_id];";
						$result = $conn->query($query); 
						if(!$result) die($conn->error);
						$query = "DELETE FROM member WHERE user_account_id = $_SESSION[update_user_id];";
						$result = $conn->query($query); 
						if(!$result) die($conn->error);
					}
					else $validationError_deleteUser = "<br /><span style='color:red;'>You must select a user to continue.</span>";
				}
				
				if(isset($_POST['generateTransactionReport'])){
					$validationError_generateTransactionReport = '';
					
					if(!isset($_POST["restaurant_id"])) $_POST["restaurant_id"] = '';
					if(!isset($_POST["service_begin_date"])) $_POST["service_begin_date"] = '';
					if(!isset($_POST["service_end_date"])) $_POST["service_end_date"] = '';
					
					if(isset($_POST["restaurant_id"]) && $_POST["restaurant_id"] == null) 
					{
						$validationError_generateTransactionReport = $validationError_generateTransactionReport."You must select a restaurant to continue<br/>";
					}
					
					if(isset($_POST["service_begin_date"]) && $_POST["service_begin_date"] == null) 
					{
						$validationError_generateTransactionReport = $validationError_generateTransactionReport."You must select a start date.<br/>";
					}
					
					if(isset($_POST["service_end_date"]) && $_POST["service_end_date"] == null) 
					{
						$validationError_generateTransactionReport = $validationError_generateTransactionReport."You must select an end date<br/>";
					}
					
					if ($validationError_generateTransactionReport === ''){
						$restaurantId = mysql_entities_fix_string($conn, $_POST['restaurant_id']);
						$service_begin_date = mysql_entities_fix_string($conn, $_POST['service_begin_date']);
						$service_end_date = mysql_entities_fix_string($conn, $_POST['service_end_date']);
						
						$query = "SELECT t.*
								  FROM restaurant r
								  JOIN restaurant_payment_method rpm
								  ON rpm.restaurant_id = r.restaurant_id
								  JOIN transaction t
								  ON t.restaurant_payment_method_id = rpm.restaurant_payment_method_id
								  WHERE r.restaurant_id = $restaurantId;";
						$result = $conn->query($query); 
						if(!$result) die($conn->error);
						$transactions = array();
						while ($row = $result->fetch_assoc()){
							$transactions[] = $row;
						}
						if (count($transactions) > 0) {
							$timestamp = date("m-d-Y_h-i-sa");
							$fileName = "transactionReport_id$restaurantId"."_".$timestamp;
							$fileLoc = "TransactionReports/$fileName";
							$myfile = fopen($fileLoc.".txt", "wr") or die("Unable to open file!");
							$txt = "File Name: $fileName<br /><hr />";
							
							foreach($transactions as $t){
								$txt = $txt."<br />";
								$txt = $txt."Transaction Date: ".$t['transaction_date']."<br />";
								$txt = $txt."Transaction Type: ".$t['transaction_type']."<br />";
								$txt = $txt."Service Begin Date: ".$t['service_begin_date']."<br />";
								$txt = $txt."Service End Date: ".$t['service_end_date']."<br />";
								$txt = $txt."Transaction Amount: $".$t['transaction_amount']."<br />";
								$txt = $txt."<br /><hr />";
							}
							fwrite($myfile, $txt);
							fclose($myfile);
							
							rename($fileLoc.".txt", $fileLoc.".html");
							
							$confirmation_generateTransactionReport = "Report Generated Successfully.";									
						} else {
							$validationError_generateTransactionReport = "No transactions were found.";
						}
						
					}
				}

				//Retrieve data from database
				$restau_namecheck = "SELECT * FROM restaurant";
				$result = $conn->query($restau_namecheck);
				$restaurants = array();
				while ($row = $result->fetch_assoc()){
					$restaurants[] = $row;
				}
				
				//Retrieve data from database
				$user_namecheck = "SELECT * FROM user_account";
				$result = $conn->query($user_namecheck);
				$users = array();
				while ($row = $result->fetch_assoc()){
					$users[] = $row;
				}
				
			
				$validationError_newFoodItem = '';

				if(isset($_POST['new-food']))//check variable set or not
				{
					if(!isset($_POST["restaurant_id"])) $_POST["restaurant_id"] = '';
					if(!isset($_POST["name"])) $_POST["name"] = '';
					if(!isset($_POST["description"])) $_POST["description"] = '';
					if(!isset($_POST["type"])) $_POST["type"] = '';
					if(!isset($_POST["price"])) $_POST["price"] = '';
					
					if(isset($_POST["restaurant_id"]) && $_POST["restaurant_id"] == null) 
					{
						$validationError_newFoodItem = $validationError_newFoodItem."You must select a restaurant to continue<br/>";
					}
					
					if(isset($_POST["name"]) && $_POST["name"] == null) 
					{
						$validationError_newFoodItem = $validationError_newFoodItem."You must include a food item name<br/>";
					}
					
					if(isset($_POST["description"]) && $_POST["description"] == null) 
					{
						$validationError_newFoodItem = $validationError_newFoodItem."You must include a food item description<br/>";
					}
					
					if(isset($_POST["type"]) && $_POST["type"] == null) 
					{
						$validationError_newFoodItem = $validationError_newFoodItem."You must include a food item type<br/>";
					}
					
					if(isset($_POST["price"]) && $_POST["price"] == null) 
					{
						$validationError_newFoodItem = $validationError_newFoodItem."You must include a food item price<br/>";
					}
					
					if ($validationError_newFoodItem === ''){
						$restaurantId = mysql_entities_fix_string($conn, $_POST['restaurant_id']);
						$name = mysql_entities_fix_string($conn, $_POST['name']);
						$description = mysql_entities_fix_string($conn, $_POST['description']);
						$type = mysql_entities_fix_string($conn, $_POST['type']);
						$price = mysql_entities_fix_string($conn, $_POST['price']);

						// sql query for inserting data into database
						mysqli_query($conn, "INSERT INTO food (name, description, type, price, restaurant_id) VALUES ('$name', '$description', '$type', '$price', '$restaurantId')");

						$confirmation_newFoodItem = "Food Item Added Successfully.";
					}
				}
 
            ?>
			<div class="col-sm-4">
				<div class="well">
					<h3>Update User</h3>
					<form action="admin.php" method="post">
						<select name="user_id" id="UserId">
							<option value='' selected>Select a user</option>
							<?php
							if (count($users) > 0) {
								foreach($users as $u){
									echo "<option value='$u[user_account_id]'>$u[username]</option>";
								}
							}
							else {echo "<p> 0 results</p>";}
							?>
						</select><br />
						<input type="submit" value="Update User Info" name="update-user" class="btn btn-primary save-button" />
						<?php echo $validationError_updateUser;?>
					</form>
				</div>
				<div style="margin-top:50px;" class="well">
					<h3>Delete User</h3>
					<form action="admin.php" method="post">
						<select name="user_id" id="UserId">
							<option value='' selected>Select a user</option>
							<?php
							if (count($users) > 0) {
								foreach($users as $u){
									echo "<option value='$u[user_account_id]'>$u[username]</option>";
								}
							}
							else {echo "<p> 0 results</p>";}
							?>
						</select><br />
						<input type="submit" value="Delete User" name="delete-user" class="btn btn-primary save-button" />
						<?php echo $validationError_deleteUser;?>
					</form>
				</div>
				<div style="margin-top:50px;" class="well">
					<h3>Change Password</h3>
					<form action="changePassword.php" method="post">
						<input type="submit" value="Change Password" class="btn btn-primary save-button"/>
					</form>
				</div>
			</div>
			<div class="col-sm-4">
				<div id="addFoodItem" class="well" >
				<h3>Add Food Item <span class="glyphicon glyphicon-pencil"></span></h3>
				  <form action="" method="post">
					<div class="row">
				      <div class="col-25">
				        <label for="restaurant">Restaurants</label>
				      </div>
				    </div>
					<div class="col-75">
				      <select id="restaurant" name="restaurant_id">
						<option value=''>Choose a restaurant</option>
				      <?php
				        //Checking Connection
						$conn = new mysqli($servername, $username, $password, $dbname);
					
						if ($conn->connect_error){
							die("Connection failed: " . $conn->connection_error);
						}

						//Retrieve data from database
						/* $restau_namecheck = "SELECT food.food_id, restaurant.restaurant_name
                         FROM food
                         inner join restaurant on food.restaurant_id = restaurant.restaurant_id"; */
						$restau_namecheck = "SELECT * FROM restaurant";
						$result = $conn->query($restau_namecheck);
						$restaurants = array();
						while ($row = $result->fetch_assoc()){
							$restaurants[] = $row;
						}
						if (count($restaurants) > 0) {
							foreach($restaurants as $r){
								echo "<option value='$r[restaurant_id]'>$r[restaurant_name]</option>";
							}
						}
						else {echo "<p> 0 results</p>";}
						?>
				        </select>
				      </div>
				      <div class="col-25">
				        <label for="name">Food Item</label>
				      </div>
				      <div class="col-75">
				        <input type="text" id="name" name="name" placeholder="Enter a food item...">
				      </div>
				      <div class="col-25">
				        <label for="subject">Description</label>
				      </div>
				      <div class="col-75">
				        <textarea type="text" id="description" name="description" placeholder="Write something about food item..." style="height:200px"></textarea> 
				      </div>
					  <div class="col-25">
				        <label for="type">Food Type</label>
				      </div>
				      <div class="col-75">
				        <input type="text" id="type" name="type" placeholder="Enter a food type...">
				      </div>
					  <div class="col-25">
				        <label for="price">Food Price    <span style="font-size:.9em;font-weight:100;">*format 7.99</span></label>
				      </div>
				      <div class="col-75">
				        <input type="text" id="price" name="price" placeholder="Enter a price...">
				      </div>
				      <input type="submit" name= "new-food" value="Submit"><br />
					  <span style='color:red;'><?php echo $validationError_newFoodItem;?></span>
						<span style='color:#337ab7;'><?php if(isset($confirmation_newFoodItem)){ echo $confirmation_newFoodItem; } ?></span>
				    </div>
				  </form>
			</div>
			<div class="col-sm-4">
				<div id="adminFunctions">
					<div class="well">
						<h3>Transaction Report</h3>
						<form action="admin.php" method="post">
							<select name="restaurant_id" id="Restaurant">
								<option value='' selected>Select a restaurant</option>
								<?php
								if (count($restaurants) > 0) {
									foreach($restaurants as $r){
										echo "<option value='$r[restaurant_id]'>$r[restaurant_name]</option>";
									}
								}
								else {echo "<p> 0 results</p>";}
								?>
							</select>
							<label for="Transaction">Start Date:</label>
							<input type="date" id="Transaction" name="service_begin_date">
							<label for="Transaction">End Date:</label>
							<input type="date" id="Transaction" name="service_end_date"><br><br />
							<input type="submit" name="generateTransactionReport" value="Generate Transaction Report"><br />
							<span style='color:red;'><?php echo $validationError_generateTransactionReport;?></span>
							<span style='color:#337ab7;'><?php if(isset($confirmation_generateTransactionReport)){ echo $confirmation_generateTransactionReport; } ?></span>
						</form>
					</div>
				</div>
				<div class="well">
					<h3>Add New Restaurant</h3>
					<form action="add-restaurant.php" method="post">
						<input type="submit" value="Add Restaurant" name="add-restaurant" class="btn btn-primary save-button"/>
					</form>
				</div>
			
				<div class="well">
					<h3>Update Restaurant</h3>
					<form action="admin.php" method="post">
						<select name="restaurant_id" id="Restaurant">
							<option value='' selected>Select a restaurant</option>
							<?php
							if (count($restaurants) > 0) {
								foreach($restaurants as $r){
									echo "<option value='$r[restaurant_id]'>$r[restaurant_name]</option>";
								}
							}
							else {echo "<p> 0 results</p>";}
							?>
						</select><br />
						<input type="submit" value="Update Restaurant Info" name="update-restaurant" class="btn btn-primary save-button" />
						<?php echo $validationError_updateRestaurant;?>
					</form>
				</div>
				
				<div class="well">
					<h3>Delete Restaurant</h3>
					<form action="admin.php" method="post">
						<select name="restaurant_id" id="Restaurant">
							<option value='' selected>Select a restaurant</option>
							<?php
							if (count($restaurants) > 0) {
								foreach($restaurants as $r){
									echo "<option value='$r[restaurant_id]'>$r[restaurant_name]</option>";
								}
							}
							else {echo "<p> 0 results</p>";}
							?>
						</select><br />
						<input type="submit" value="Delete Restaurant" name="delete-restaurant" class="btn btn-primary save-button" />
						<?php echo $validationError_deleteRestaurant;?>
						<span style='color:#337ab7;'><?php if(isset($confirmation)){ echo $confirmation; } ?></span>
					</form>
				</div>
			</div>
			
		</div>
		<div class="main-container row">
			<div class="col-sm-4">
				<?php include 'Partials/food-ratings.php'; ?>
			</div>
		    
			<?php
				if(isset($_POST['more-restaurants'])){
					$_SESSION['redirected_from'] = "admin.php";
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
						<form method="post" action="admin.php">
							<br />
							<input type="submit" name= "more-restaurants" value="View More Restaurants"><br />
						</form>
					  </div>
				</div>
			</div>
		</div>
		<?php include 'partials/footer.php'?>
		<?php $conn->close();?>
	</body>
</html>