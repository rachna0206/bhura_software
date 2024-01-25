<?php
include("header.php");

$date=isset($_COOKIE["dash_date"])?$_COOKIE['dash_date']:date('Y-m-d');
setcookie("selected_date", $date,time()+3600,"/");

$user_id = $_SESSION["id"];

$stmt_username = $obj->con1->prepare("SELECT * FROM `tbl_users` WHERE id=?");
$stmt_username->bind_param("i",$user_id);
$stmt_username->execute();
$username_result = $stmt_username->get_result()->fetch_assoc();
$stmt_username->close();
?>

<div class="row">
	<div class="col-lg-12 mb-4 order-0">
		<div class="card">
			<div class="d-flex align-items-end row">
			  <div class="col-sm-7">
			    <div class="card-body">
			      <h5 class="card-title text-primary">Welcome <?php echo ucwords(strtolower($username_result['name'])) ?></h5>
			      <!-- <p class="mb-4">
			        You have done <span class="fw-bold">72%</span> more sales today. Check your new badge in
			        your profile.
			      </p> -->

			      <!-- <a href="javascript:;" class="btn btn-sm btn-outline-primary">View Badges</a> -->
			    </div>
			  </div>
			  <div class="col-sm-5 text-center text-sm-left">
			    <div class="card-body pb-0 px-0 px-md-4">
			      <img
			        src="assets/img/illustrations/man-with-laptop-light.png"
			        height="140"
			        alt="View Badge User"
			        data-app-dark-img="illustrations/man-with-laptop-dark.png"
			        data-app-light-img="illustrations/man-with-laptop-light.png"
			      />
			    </div>
			  </div>
			</div>
		</div>
	</div>
</div>
 




<?php 
include("footer.php");
?>