<?php
	include "header.php";

	$service_id = $_COOKIE['service_id'];

	$stmt_stage = $obj->con1->prepare("SELECT DISTINCT(s1.stage_name), a1.service_id, a1.stage_id from (select MAX(t2.tatassign_id) as assign_id from tbl_tdtatassign t1, tbl_tdtatassign t2 where t1.tatassign_id=t2.tatassign_id GROUP BY t2.tatassign_inq_id) as tbl1, tbl_tdtatassign a1, tbl_tdstages s1 where tbl1.assign_id=a1.tatassign_id and a1.stage_id=s1.stage_id and a1.tatassign_user_id=? and a1.service_id=?");
	$stmt_stage->bind_param("ii",$user_id,$service_id);
	$stmt_stage->execute();
	$stage_result = $stmt_stage->get_result();
	$stmt_stage->close();
?>

<div class="col-md mb-4 mb-md-0">
  <!-- <small class="text-light fw-semibold">Basic Accordion</small> -->
  <div class="accordion mt-3" id="accordionExample">
  	<?php while($stage = mysqli_fetch_array($stage_result)) { ?>
	    <div class="card accordion-item active">
	      <h2 class="accordion-header" id="headingOne">
	        <button type="button" class="accordion-button" data-bs-toggle="collapse" data-bs-target="#accordionOne" aria-expanded="true" aria-controls="accordionOne"><?php echo $stage['stage_name'] ?></button>
	      </h2>

	      <div id="accordionOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
	        <div class="accordion-body">
	          <!-- Basic Bootstrap Table -->
			    <div class="card">
			      <h5 class="card-header">Records</h5>
			      <div class="table-responsive text-nowrap">
			        <table class="table table-hover" id="table_id">
			          <thead>
			            <tr>
			              <th>Srno</th>
			              <th>Company Name</th>
			              <th></th>
			            </tr>
			          </thead>
			          <tbody class="table-border-bottom-0">
			            <?php
			              $stmt_list = $obj->con1->prepare("SELECT a1.stage_id, a1.tatassign_inq_id, a1.tatassign_user_id, r1.raw_data from (SELECT MAX(t2.tatassign_id) as assign_id from tbl_tdtatassign t1, tbl_tdtatassign t2 where t1.tatassign_id=t2.tatassign_id GROUP BY t2.tatassign_inq_id) as tbl1, tbl_tdtatassign a1, tbl_tdrawdata r1 where tbl1.assign_id=a1.tatassign_id and a1.tatassign_inq_id=r1.id and a1.tatassign_user_id=? and a1.stage_id=? and a1.service_id=?"); 
			              $stmt_list->bind_param("iii",$user_id,$stage['stage_id'],$stage['service_id']);
			              $stmt_list->execute();
			              $result = $stmt_list->get_result();
			              $stmt_list->close();
			              $i=1;

			              while($data=mysqli_fetch_array($result))
			              {
			              	$row_data=json_decode($data["raw_data"]);
                			$post_fields=$row_data->post_fields;
			            ?>
			            		
				            <tr>
				              <td><?php echo $i?></td>
				              <td><?php echo $post_fields->Firm_Name ?></td>
				              <td></td>
                    </tr>
                    
			            <?php
			                $i++;
			              }
			            ?>
			            
                    
			          </tbody>
			        </table>

			      </div>
			    </div>
			    <!--/ Basic Bootstrap Table -->
	        </div>
	      </div>
	    </div>
	<?php } ?>
    
  </div>
</div>


<script type="text/javascript">

</script>
<?php
	include "footer.php";
?>