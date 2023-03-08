<!doctype html>
<html>
	<?php include 'partials/head.php';?>
	<body id="body">
		<?php include 'partials/header.php';?>
		<div class="jumbotron text-center">
			<br><br>
			<h1> Good Food & Good Health</h1> 
			<p>Food Review Simplified</p> 
		</div>
		<div class="row">
			<div class="col-sm-offset-3 col-sm-6" style="position:relative;left:30px;">
				<form class="example reset-this" action="#">
					<input type="text" placeholder="Search.." name="search" style="margin-top:0px;">
					<button type="submit" class="btn btn-primary"><i id="search-button" class="fa fa-search"></i></button>
				</form>
			</div>
		</div>
		<div id="portfolio" class="container-fluid text-center bg-grey">
			<h2>Customers Reviews</h2>
			<div id="myCarousel" class="carousel slide text-center" data-ride="carousel">
				<!-- Indicators -->
				<ol class="carousel-indicators">
					<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
					<li data-target="#myCarousel" data-slide-to="1"></li>
					<li data-target="#myCarousel" data-slide-to="2"></li>
				</ol>

				<!-- Wrapper for slides -->
				<div class="carousel-inner" role="listbox">
					<div class="item active">
						<h4>The food was excellent and so was the service.  I had the mushroom risotto with scallops which was awesome.<br><span>Michael Roe</span></h4>
					</div>
					<div class="item">
						<h4>"We enjoyed the Eggs Benedict served on homemade focaccia bread and hot coffee."<br><span>John Doe</span></h4>
					</div>
					<div class="item">
						<h4>"Best Chicken Tikka Masala I've had!"<br><span>Chandler Bing</span></h4>
					</div>
				</div>

				<!-- Left and right controls -->
				<a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
					<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
					<span class="sr-only">Previous</span>
				</a>
				<a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
					<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
				</a>
			</div>
		</div>
		<?php include 'partials/footer.php'?>
	</body>
</html>