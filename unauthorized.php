<html>
	<?php include 'partials/head.php';?>
	<body id="body">
		<?php include 'partials/header.php';?>
		<div class="main-container">
			<div class="row">
				<div class="col-sm-offset-3 col-sm-6 tab-content content-container">
<?php
$_SESSION = array();
session_destroy();

echo "No access <a href='login.php'>back to Login</a> ";
?>
				</div>
				<div class="col-sm-3"></div>
			</div>
		</div>
		<?php include 'partials/footer.php'?>
	</body>
</html>