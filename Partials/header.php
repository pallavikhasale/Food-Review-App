<?php
require  'Partials/user.php';
require  'Partials/credentials.php';

session_start();

if (isset($_POST['logout']))
{ 
	$_SESSION = array();
	session_destroy();
	ob_start();
	header('Location: homepage.php');
	ob_end_flush();
	die();
}
?>
<div class="row" id="headerContainer">
	<div class="col-sm-1">
		<img src="Content/EatTreat.png" height="150" width="150"></img>
	</div>
	<div class="col-sm-2">
		<h1 id="CompanyName">EatTreat</h1>
	</div>
	<div class="col-sm-7">
		<div class="collapse navbar-collapse" id="myNavbar">
			<ul class="nav navbar-nav navbar-center">
				<li><a href="homepage.php">Home</a></li>
				<li><a href="#siteMapModal" data-toggle="modal" data-target="#siteMapModal">Site Map</a></li>
				<li><a href="#useCaseModal" data-toggle="modal" data-target="#useCaseModal">Use Cases</a></li>
				<li><a href="#proposalModal" data-toggle="modal" data-target="#proposalModal">Project Proposal</a></li>				
				<li><a href="#demoModal" data-toggle="modal" data-target="#demoModal">View Demo</a></li>	
			</ul>
		</div>
	</div>
	<div class="col-sm-2">
		<h1 id="Authentication"></h1>
		<ul class="nav navbar-nav navbar-left">
				<?php 
				if (isset($_SESSION['user'])){
					$user = $_SESSION['user'];
					echo '<div>';
					if($user->role == 'member'){
						echo '<a href="update-member.php"><img src="Content/user_icon.png" height="50" width="50"></a>';
						echo "<a href='update-member.php' style='color:#333'>$user->username</a>";
					} else {
						echo '<img src="Content/user_icon.png" height="50" width="50">';
						echo $user->username; 
					}
					echo '<form method="post" style="margin-left:20px; display:inline;">';
					echo '<input type="submit" value="Log Out" name="logout">';
					echo '</form>';
					echo '</div>';
				} else {
					echo '<a href="update-member.php"><img src="Content/user_icon.png" height="50" width="50"></a>';
					echo '<li><a href="login.php">Login</a></li>';
					echo '<li><a href="signup.php">Sign up</a></li>';
				}
				?>
		</ul>
	</div>
	<?php include 'Partials/sitemap.html'?>
	<?php include 'Partials/usecases.html'?>
	<?php include 'Partials/proposal.html'?>
	<?php include 'Partials/demo.html'?>
</div>