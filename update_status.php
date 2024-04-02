<?php
include 'header.php';

if(isset($_REQUEST['btnupdate'])){
	echo "yes";

	$msg1=$msg2=$msg3="";

	$stmt = $obj->con1->prepare("SELECT r1.id, r1.inq_id, r1.stage, c1.status FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdrawassign t1, tbl_tdrawassign t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdrawassign r1, pr_company_details c1 WHERE tbl1.r_id = r1.id AND r1.inq_id = c1.rawdata_id AND (c1.status IS NULL OR NOT ((r1.stage = 'lead' AND c1.status = 'Positive') OR ((r1.stage IN ('badlead', 'revisedbadlead')) AND c1.status = 'Negative') OR (r1.stage NOT IN ('lead', 'badlead', 'revisedbadlead') AND c1.status = 'Existing Client')))");
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();

	if(mysqli_num_rows($res)>0){
		while($result=mysqli_fetch_array($res)){
			if($result['stage']=='lead'){
				$status = 'Positive';
			}
			else if($result['stage']=='badlead' || $result['stage']=='revisedbadlead'){
				$status = 'Negative';
			}
			else{
				$status = 'Existing Client';
			}

			$stmt_upd = $obj->con1->prepare("UPDATE pr_company_details SET status=? WHERE rawdata_id=?");
			$stmt_upd->bind_param("si",$status,$result['inq_id']);
			$res_upd = $stmt_upd->execute();
			$stmt_upd->close();

			if($res_upd){
				$msg1.= '<div style="font-family:serif;font-size:18px;Padding:0px 0 0 0;margin:10px 0px 0px 0px;">Date Updated Successfully</div>';
			}
			else
		    {
		      $msg2.= '<div style="font-family:serif;font-size:18px;color:rgb(214, 13, 42);Padding:0px 0 0 0;margin:10px 0px 0px 0px;">Error In Updating Data</div>';
		    }
		}
	}
	else{
		$msg3.= '<div style="font-family:serif;font-size:18px;color:rgb(214, 13, 42);Padding:0px 0 0 0;margin:10px 0px 0px 0px;">No Data Found</div>';
	}

	$msges=$msg1.$msg2.$msg3.$msg4;
    
    setcookie("excelmsg", $msges,time()+3600,"/");
     
    header("location:update_status.php");
}

?>

<?php
  if(isset($_COOKIE["excelmsg"]))
  {
?>
  <div class="alert alert-primary alert-dismissible" role="alert">
      <?php echo $_COOKIE['excelmsg']?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
      </button>
    </div>

    <script type="text/javascript">eraseCookie("excelmsg")</script>

<?php
  }
?>

<?php if(in_array($user_id, $admin)){ ?> 
    <!-- grid -->

    <!-- Basic Bootstrap Table -->
    <div class="card">
    	<form method="post">
      <!-- <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Records</h5>
      </div> -->
      	<div class="row ms-2 me-2">
            <div class="col-md-8"><h5 class="card-header">Records</h5></div>
			<div class="col-md" style="margin:1%">
                <input type="submit" name="btnupdate" id="btnupdate" class="btn btn-primary" value="Update">
            </div>
        </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>Inq. ID</th>
              <th>Firm Name</th>
              <th>Status</th>
              <th>App Status</th>
              <th>Contact No.</th>
              <th>Industrial Estate</th>
              <th>Taluka</th>
              <th>Area</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php 
      
              $stmt_list = $obj->con1->prepare("SELECT r1.id, r1.inq_id, JSON_UNQUOTE(d1.raw_data->'$.post_fields.Firm_Name') as firm_name, JSON_UNQUOTE(d1.raw_data->'$.post_fields.Mobile_No') as mobile_no, JSON_UNQUOTE(d1.raw_data->'$.post_fields.IndustrialEstate') as industrial_estate, JSON_UNQUOTE(d1.raw_data->'$.post_fields.Area') as area, JSON_UNQUOTE(d1.raw_data->'$.post_fields.Taluka') as taluka, r1.stage, c1.status FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdrawassign t1, tbl_tdrawassign t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdrawassign r1, pr_company_details c1, tbl_tdrawdata d1 WHERE tbl1.r_id = r1.id AND r1.inq_id = c1.rawdata_id AND r1.inq_id=d1.id AND (c1.status IS NULL OR NOT ((r1.stage = 'lead' AND c1.status = 'Positive') OR ((r1.stage IN ('badlead', 'revisedbadlead')) AND c1.status = 'Negative') OR (r1.stage NOT IN ('lead', 'badlead', 'revisedbadlead') AND c1.status = 'Existing Client')));");
              $stmt_list->execute();
              $result = $stmt_list->get_result();
              $stmt_list->close();
              $i=1;

              while($data=mysqli_fetch_array($result))
              {
            ?>

            <tr>
              <td><?php echo $i?></td>
              <td><?php echo $data["inq_id"] ?></td>
              <td><?php echo $data["firm_name"] ?></td>
              <td><?php echo $data["stage"] ?></td>
              <td><?php echo $data["status"] ?></td>
              <td><?php echo $data["mobile_no"] ?></td>
              <td><?php echo $data["industrial_estate"] ?></td>
              <td><?php echo $data["area"] ?></td>
              <td><?php echo $data["taluka"] ?></td>
            </tr>
            <?php
                $i++;
              }
            ?>
            
          </tbody>
        </table>
      </div>
      </form>
    </div>
    <!--/ Basic Bootstrap Table -->

  <!-- / grid -->

<?php } ?>

<?php
	include 'footer.php';
?>