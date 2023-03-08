<div id="foodRatings" class="well" >
	<h3>Food Ratings:  <span class="glyphicon glyphicon-glass"></span></h3>
	<div class="panel-group" id="accordion" style="overflow-y: scroll; height: 50vh;">
		<?php
		 $foodReview_check = "SELECT r.review_id,r.review_text, r.rating,f.name, re.restaurant_name
		 FROM review r
		 JOIN food f 
		 ON r.food_id = f.food_id
		 JOIN restaurant re
		 ON re.restaurant_id = f.restaurant_id;";
		 $result = $conn->query($foodReview_check);
		 if ($result->num_rows > 0) {
			 $firstReview = true;
			 while ($row = $result->fetch_assoc()){
		 ?>
		<div class="panel panel-default">
		   <div class="panel-heading">
			<h4 class="panel-title">
			<a data-toggle="collapse" data-parent="#accordion" href="#<?php echo $row["review_id"];?>">
				<?php echo $row["name"]; ?> 
				<span class=" w3-right w3-margin-right"><?php echo $row["rating"]; ?></span>
				<span class=" w3-right w3-margin-right"><?php echo " - ".$row["restaurant_name"]; ?></span>
			 </a>
			</h4>
			</div>
			<div id="<?php echo $row["review_id"]; ?>" class="panel-collapse collapse 
			<?php if($firstReview) {
				echo 'in';
				$firstReview = false;
			}
			?>">
			<div class="panel-body"><?php echo $row["review_text"]; ?></div>

		   </div>
		</div>
		  <?php
				}
			}
			else {
			echo "<p> 0 results</p>";
			}
			?>
	</div>
</div>