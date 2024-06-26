<?php
include("header.php");
//error_reporting(0);

$service_id = $_COOKIE['service_id'];

$stmt_service = $obj->con1->prepare("SELECT DISTINCT(sm.service) as service_name FROM `tbl_tdtatassign` ta inner join tbl_tdtatclaim tc on ta.tatclaim_id = tc.tatassign_id inner join tbl_service_master sm on sm.id = tc.service_id where ta.service_id='".$service_id."'");
$stmt_service->execute();
$result_service = $stmt_service->get_result()->fetch_assoc();
$stmt_service->close();

$stmt = $obj->con1->prepare("SELECT sm.service,ta.tatassign_status,sum(case when ta.tatassign_user_id='".$user_id."' then 1 else 0 end) AS OnHand, CASE WHEN ta.tatassign_status LIKE '%claim%' THEN 1 ELSE 0 END AS contains_claim FROM `tbl_tdtatassign` ta inner join tbl_tdtatclaim tc on ta.tatclaim_id = tc.tatassign_id and tc.claim_date_start<='".date('Y-m-d')."' and tc.claim_current='yes' inner join tbl_service_master sm on sm.id = tc.service_id where ta.service_id='".$service_id."' and ta.tatassign_id in (select max(tatassign_id) from tbl_tdtatassign GROUP by tatclaim_id) and ta.tatassign_status LIKE '%Process%' GROUP by ta.tatassign_status HAVING OnHand!=0");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

?>

<h4 class="fw-bold py-3 mb-4"><?php echo $result_service["service_name"] ?></h4>

  <!-- Basic Bootstrap Table -->
    <div class="card">
      <div class="col-md-9"><h5 class="card-header"><?php echo $result_service["service_name"] ?></h5></div>
      <div class="table-responsive text-nowrap">
        <table class="table" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>Scheme Name</th>
              <th>On Hand</th>
              <th></th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0" id="grid">
            <?php 
                $i=1;
              while($data=mysqli_fetch_array($result))
              {
            ?>
            <tr>
              <td><?php echo $i ?></td>
              <td><?php echo $data['tatassign_status'] ?></td>
              <td><?php echo $data['OnHand'] ?></td>
              <td><a href="javascript:view_data('<?php echo $result_service["service_name"] ?>','<?php echo $data["tatassign_status"] ?>','<?php echo $data["contains_claim"] ?>');"><i class="bx bx-show me-1"></i> </a></td>
            </tr>  
          <?php 
                $i++;
          } ?>
          </tbody>
        </table>
      </div>
    </div>

    <!--/ Basic Bootstrap Table -->

<script type="text/javascript">

    function view_data(service_name, scheme_name, contains_claim) {
        if(service_name=="GOGTP IR"){
          window.location="process_gogtp_ir.php?scheme_name="+scheme_name+"&claim="+contains_claim;
        } else if(service_name=="GOGTP PT"){
          window.location="process_gogtp_pt.php?scheme_name="+scheme_name+"&claim="+contains_claim;
        }
      }

</script>

<?php 
  include("footer.php");
?>