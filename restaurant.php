<!doctype html>
<html>
	<?php include 'partials/head.php';?>
	<body id="body">
		<?php include 'partials/header.php';?>
		<div class="main-container row">
		<h2 style="text-align:center;">Welcome to the restaurant homepage!</h2><hr />
<?php
	$page_roles = array('restaurant');
	require_once 'checksession.php';
	require_once 'partials/sanitize.php';
	
	$query = "SELECT * FROM restaurant WHERE user_account_id = '$user->user_account_id';";
	$result = $conn->query($query);
	if($result->num_rows < 1) {
		echo "<script>alert('Error:  You do not have a restaurant associated with your account.  Please contact an administrator for further help.')</script>";
		echo "<script>window.location.href = 'homepage.php';</script>";
	} elseif ($result->num_rows > 1){
		echo "<script>alert('Error:  Somehow your user account is associated with more than one restaurant.  Please contact an administrator for further help.')</script>";
		echo "<script>window.location.href = 'homepage.php';</script>";
	}
	$usersRestaurant = $result->fetch_array(MYSQLI_ASSOC);
	
	if(isset($_POST['ccupdate'])){
		$_SESSION['validationError'] = '';
		
		if(!isset($_POST["paymentmethodid"])) $_POST["paymentmethodid"] = '';
		if(!isset($_POST["cardholderfirstname"])) $_POST["cardholderfirstname"] = '';
		if(!isset($_POST["cardholderlastname"])) $_POST["cardholderlastname"] = '';
		if(!isset($_POST["cardnumber"])) $_POST["cardnumber"] = '';
		if(!isset($_POST["cvv"])) $_POST["cvv"] = '';
		if(!isset($_POST["expdate"])) $_POST["expdate"] = '';
		if(!isset($_POST["zipcode"])) $_POST["zipcode"] = '';

		if(isset($_POST["cardnumber"]) && $_POST["cardnumber"] == null) 
		{
			$_SESSION['validationError'] = $_SESSION['validationError']."You must include a credit card number <br/>";
		}
		
		if(isset($_POST['cvv']) && $_POST['cvv'] == null) 
		{
			$_SESSION['validationError'] = $_SESSION['validationError']."You must include a cvv code <br/>";
		}
		
		if(!isset($_SESSION['user']) || $_SESSION['user'] == null) 
		{
			$_SESSION['validationError'] = $_SESSION['validationError']."You must log in to continue <br/>";
		}
		
		if ($_SESSION['validationError'] === ''){
			$paymentmethodid = mysql_entities_fix_string($conn, $_POST['paymentmethodid']);
			$cardholderfirstname = mysql_entities_fix_string($conn, $_POST['cardholderfirstname']);
			$cardholderlastname = mysql_entities_fix_string($conn, $_POST['cardholderlastname']);
			$cardnumber = mysql_entities_fix_string($conn, $_POST['cardnumber']);
			$cvv = mysql_entities_fix_string($conn, $_POST['cvv']);
			$zipcode = mysql_entities_fix_string($conn, $_POST['zipcode']);
			$expdate = date('Y-m-d', strtotime(mysql_entities_fix_string($conn, $_POST['expdate'])));
			
			if(!isset($_SESSION['existingPaymentMethod']) 
				|| $_SESSION['existingPaymentMethod'] == null
				|| $_SESSION['existingPaymentMethod'] == false){
				$query = "INSERT INTO payment_method (credit_card_number, cvv_code, expiration_date, first_name, last_name, zipcode) VALUES ('$cardnumber', '$cvv', '$expdate', '$cardholderfirstname', '$cardholderlastname', '$zipcode');";
				$result = $conn->query($query);
				
				$query = "SELECT LAST_INSERT_ID();";
				$result = $conn->query($query);
				$newpayment = $result->fetch_array(MYSQLI_ASSOC);
				$paymentmethodid = $newpayment['LAST_INSERT_ID()'];
				
				$query = "INSERT INTO restaurant_payment_method (payment_method_id, restaurant_id) VALUES ($paymentmethodid, $usersRestaurant[restaurant_id]);";
			} else {
				$query = "Update payment_method set credit_card_number = '$cardnumber', cvv_code = '$cvv', expiration_date = '$expdate', first_name = '$cardholderfirstname', last_name = '$cardholderlastname', zipcode = '$zipcode' WHERE payment_method_id = '$paymentmethodid'";
			}
			
			$result = $conn->query($query);
			if(!$result){
				die($conn->error);
				echo "<script>alert('Error:  The record was not saved')</script>";
			} else {
				echo "<script>alert('Success: The payment method was added successfully')</script>";
				if($user->role == 'administrator'){
					echo "<script>window.location.href = 'admin.php';</script>";
				} else {
					echo "<script>window.location.href = 'restaurant.php';</script>";
				}
			}
		}
	}
?>
			<div class="row">
				<div class="col-sm-4">
					<?php include 'Partials/food-ratings.php'; ?>				
				</div>
				<div class="col-sm-4">
					<div id="paymentSettings" class="well">
					<?php
						$cardholderfirstname = '';
						$cardholderlastname = '';
						$cardnumber = '';
						$cvv = '';
						$zipcode = '';
						$expdate = '';
					
						function getTruncatedCCNumber($cardnumber){
							return str_replace(range(0,9), "*", substr($cardnumber, 0, -4)).substr($cardnumber, -4);
						}
						
						$query = "SELECT pm.*
								  FROM payment_method pm
								  JOIN restaurant_payment_method rpm
								  ON rpm.payment_method_id = pm.payment_method_id
								  WHERE rpm.restaurant_id = $usersRestaurant[restaurant_id];";
						$result = $conn->query($query); 
						if(!$result) die($conn->error);
						if($result->num_rows == 1) {
							$_SESSION['existingPaymentMethod'] = true;
							$row = $result->fetch_array(MYSQLI_ASSOC);
							$paymentmethodid = $row['payment_method_id'];
							$cardholderfirstname = $row['first_name'];
							$cardholderlastname = $row['last_name'];
							$cardnumber = $row['credit_card_number'];
							$cvv = $row['cvv_code'];
							$zipcode = $row['zipcode'];	
							$expdate = date('m/d/Y', strtotime($row['expiration_date']));	
						} else {
							$_SESSION['existingPaymentMethod'] = false;
						}							
					?>
						<form class="form-horizontal" action="restaurant.php" method="post">
							<h3>Payment Information:</h3>
							<div class="form-group">
								<label class="control-label col-sm-3" for="cardholderfirstname">First Name:</label>
								<div class="col-sm-9">
									<input class="form-control" name="cardholderfirstname"
									value="<?php if(isset($cardholderfirstname)){ echo $cardholderfirstname; }  ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3" for="cardholderlastname">Last Name:</label>
								<div class="col-sm-9">
									<input class="form-control" name="cardholderlastname"
									value="<?php if(isset($cardholderlastname)){ echo $cardholderlastname; }  ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3" for="cardnumber">Credit Card Number:</label>
								<div class="col-sm-9">
									<input class="form-control" name="cardnumber" onfocus="this.value=''"
										value="<?php if(isset($cardnumber)){ echo getTruncatedCCNumber($cardnumber); }  ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3" for="cvv">CVV:</label>
								<div class="col-sm-9">
									<input class="form-control" name="cvv"
										value="<?php if(isset($cvv)){ echo $cvv; }  ?>">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3" for="expdate">Expiration Date:</label>
								<div class="col-sm-9">
									<input class="form-control" name="expdate"
										value="<?php if(isset($expdate)){ echo $expdate; }  ?>">
									* format mm/dd/yyyy
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3" for="zipcode">Zipcode:</label>
								<div class="col-sm-9">
									<input class="form-control" name="zipcode"
										value="<?php if(isset($zipcode)){ echo $zipcode; }  ?>">
								</div>
							</div>
							<!--<div class="form-group">
								<div class="col-sm-offset-4 col-sm-8">
									<a href="transactionReport.php">View Transaction Report</a>
								</div>
							</div>-->
							<div class="form-group" style="margin-left:0px;">
								<input type='hidden' name='paymentmethodid' value="<?php if(isset($paymentmethodid)){ echo $paymentmethodid; }  ?>">
								<input type="submit" value="Save" name="ccupdate" class="btn btn-primary save-button" /><br />
								<span style='color:red;'><?php if(isset($_SESSION['validationError'])){ echo $_SESSION['validationError']; }  ?></span>
							</div>
						</form>
					</div>
					<div id="viewTransactionReport" class="well">
						<?php				
							$urValidationError = '';
							$_SESSION['report_path'] = '';
							$fileId = 'id'.$usersRestaurant['restaurant_id'];
								
							if(isset($_POST['viewReport'])){
								if(isset($_POST['report_path']) && $_POST['report_path'] != null) {
									$_SESSION['report_path'] = mysql_entities_fix_string($conn, $_POST['report_path']);
									echo "<script type='text/javascript'>
											$(document).ready(function(){
											$('#transactionReportModal').modal('show');
											});
											</script>";
								}
								else $urValidationError = "<br /><span style='color:red;'>You must select a restaurant to continue.</span>";
							}

							//Retrieve data from database
							$path    = 'TransactionReports';
							$files = array_diff(scandir($path, 1), array('.', '..'));
							
						?>
						<h3>Transaction Reports: <span class="glyphicon glyphicon-pencil"></span></h3>
						<form action="restaurant.php" method="post">
							<select name="report_path" id="Restaurant" >
								<option value=''>Choose a transaction report</option>
								<?php
									foreach($files as $file){
										if(strpos($file, $fileId) !== false){
											echo "<option value='$path/$file'>$file</option>";
										}
									}
								?>
							</select><br />
							<input type="submit" value="View Transaction Report" name="viewReport" class="btn btn-primary save-button" data-toggle="modal" data-target="#transactionReportModal" />
							<?php echo $urValidationError;?>
						</form>
						<?php include 'Partials/transaction-report.php'?>
					</div>
					<div id="paymentSettings" class="well">
					<?php
					if(isset($_POST['paysubscription'])){
						$validationError_paySubscription = '';
						
						$query = "SELECT pm.*, r.subscription_id, s.price
								  FROM payment_method pm
								  JOIN restaurant_payment_method rpm
								  ON rpm.payment_method_id = pm.payment_method_id
								  JOIN restaurant r
								  ON r.restaurant_id = rpm.restaurant_id
								  JOIN subscription s
								  ON s.subscription_id = r.subscription_id
								  WHERE rpm.restaurant_id = $usersRestaurant[restaurant_id];";
						$result = $conn->query($query); 
						if(!$result) die($conn->error);
						if($result->num_rows == 1) {
							$_SESSION['existingPaymentMethod'] = true;
							$row = $result->fetch_array(MYSQLI_ASSOC);
							$transaction_date = date("Y-m-d", strtotime("Today"));
							$transaction_type = 'payment';
							$service_begin_date = date("Y-m-01", strtotime("Today"));
							$service_end_date = date("Y-m-t", strtotime("Today"));
							$transaction_amount = $row['price'];
							$restaurant_payment_method_id = $row['payment_method_id'];
														
							if ($validationError_paySubscription === ''){
								$query = "INSERT INTO transaction (transaction_date, transaction_type, service_begin_date, service_end_date, transaction_amount, restaurant_payment_method_id) VALUES ('$transaction_date', '$transaction_type', '$service_begin_date', '$service_end_date', $transaction_amount, $restaurant_payment_method_id)";
								$result = $conn->query($query); 
								if(!$result) die($conn->error);
								$confirmation = "The subscription was successfully paid. View the updated transaction report for further details.";
							}
						} else {
							$_SESSION['existingPaymentMethod'] = false;
							$validationError_paySubscription = "You don't have a payment method on file.<br/>";
						}
					}						
					?>
						<form action="restaurant.php" method="post">
							<h3>Pay Subscription:</h3>
							<div class="form-group">
								<input type='hidden' name='paymentmethodid' value="<?php if(isset($paymentmethodid)){ echo $paymentmethodid; }  ?>">
								<input type="submit" value="Make Payment" name="paysubscription" class="btn btn-primary save-button"/><br />
								<span style='color:red;'><?php if(isset($validationError_paySubscription)){ echo $validationError_paySubscription; }  ?></span>
								<span style='color:#337ab7;'><?php if(isset($confirmation)){ echo $confirmation; } ?></span>
							</div>
						</form>
					</div>
				</div>
				<div class="col-sm-4">
					<div id="update_restaurant_info" class="well">
						<?php				
							$urValidationError = '';
							
							if(!isset($_SESSION['restaurant_id'])) $_SESSION['restaurant_id'] = '';
								
							if(isset($_POST['update-restaurant'])){
								if(isset($_POST['restaurant_id']) && $_POST['restaurant_id'] != null) {
									$_SESSION['restaurant_id'] = mysql_entities_fix_string($conn, $_POST['restaurant_id']);
									$_SESSION['redirected_from'] = "restaurant.php";
									echo "<script>window.location.href = 'update-restaurant.php';</script>";
								}
								else $urValidationError = "<br /><span style='color:red;'>You must select a restaurant to continue.</span>";
							}

							//Checking Connection
							$conn = new mysqli($servername, $username, $password, $dbname);
						
							if ($conn->connect_error){
								die("Connection failed: " . $conn->connection_error);
							}

							//Retrieve data from database
							$restau_namecheck = "SELECT * FROM restaurant where user_account_id = '$user->user_account_id';";
							$result = $conn->query($restau_namecheck);
							$restaurant = $result->fetch_array(MYSQLI_ASSOC);
						?>
						<h3>Update Restaurant Information <span class="glyphicon glyphicon-pencil"></span></h3>
						<form action="restaurant.php" method="post">
							<select name="restaurant_id" id="Restaurant" >
								<?php
									echo "<option value='$restaurant[restaurant_id]' selected>$restaurant[restaurant_name]</option>";
								?>
							</select><br /><br />
							<input type="submit" value="Update Restaurant Info" name="update-restaurant" class="btn btn-primary save-button" />
							<?php echo $urValidationError;?>
						</form>
					</div>
					<div id="followers" class="well">
<?PHP
if (isset($restaurant['restaurant_id'])){
	
	$query = "DELETE
			  FROM follow
			  WHERE restaurant_id = $restaurant[restaurant_id]
			  AND member_id = -1;";
	$result = $conn->query($query);
	

	$today = strtotime("Today");
	$low = strtotime("-1 years", $today);
	$randomNumberOfFollowers = mt_rand(300,1000);
	for($i=0; $i<$randomNumberOfFollowers; $i++){
		$query = "INSERT INTO follow
			(member_id, restaurant_id, created_date)
			VALUES (-1, $restaurant[restaurant_id], '".
			date("Y-m-d", mt_rand($low,$today))
			."');";
		$result = $conn->query($query);
		if(!$result) die($conn->error);
	}
	
	$query = "SELECT COUNT(*) 
			  FROM follow 
			  WHERE restaurant_id = $restaurant[restaurant_id]
			  GROUP BY MONTH(created_date);";
	$result = $conn->query($query);
	
	$rows=$result->num_rows;
	$monthlyFollowers = [];
	$runningCount = 0;
	for($j=0; $j<$rows; $j++) {
		$result->data_seek($j);
		$row=$result->fetch_array(MYSQLI_NUM);
		$runningCount += $row[0];
		array_push($monthlyFollowers, $runningCount);
	}
}
?>
						<form action="restaurant.php" method="post">
							<h3>Followers: 
							<span class="badge">
								<?php echo $runningCount;?>
							</span></h3>
							<canvas id="myChart"></canvas>
							<input type="submit" value="Refresh Mock Followers" name="mockfollowers" class="btn btn-primary save-button"/><br />
						</form>
<script>
var xValues = ["Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec","Jan","Feb",];
//var yValues = [10,13,25,54,78,102,98,119,178,196,242,277];

var yValues = <?php echo json_encode($monthlyFollowers); ?>;
var max = Math.ceil(<?php echo json_encode($runningCount); ?>/100)*100;

new Chart("myChart", {
  type: "line",
  data: {
    labels: xValues,
    datasets: [{
      fill: false,
      lineTension: 0,
      backgroundColor: "gray",
      borderColor: "powderblue",
      data: yValues
    }]
  },
  options: {
    legend: {display: false},
    scales: {
      yAxes: [{ticks: {min: 0, max:max}}],
    }
  }
});
</script>
					</div>
				</div>
			</div>
		</div>
		<?php include 'partials/footer.php'?>
		<?php $conn->close();?>
	</body>
</html>