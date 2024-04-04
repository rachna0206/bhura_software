<?php
  include("header.php");

$user_id = $_SESSION["id"];

// update data
if(isset($_REQUEST['btn_modal_update']))
{
  $industrial_estate_id = $_REQUEST['industrial_estate_id'];
  $verify_status = $_REQUEST['verify_status'];
  
  try
  {
    $stmt = $obj->con1->prepare("UPDATE `pr_add_industrialestate_details` SET `status`=? WHERE `industrial_estate_id`=?");
    $stmt->bind_param("si",$verify_status,$industrial_estate_id);
    $Resp=$stmt->execute();
    
    if(!$Resp)
    {
      throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
    setcookie("msg", "data",time()+3600,"/");
    header("location:estate_status_report.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:estate_status_report.php");
  }
}

?>

<h4 class="fw-bold py-3 mb-4">Visit Count Report</h4>

<?php 
if(isset($_COOKIE["msg"]) )
{

  if($_COOKIE['msg']=="data")
  {

  ?>
  <div class="alert alert-primary alert-dismissible" role="alert">
    Data added succesfully
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
    </button>
  </div>
  <script type="text/javascript">eraseCookie("msg")</script>
  <?php
  }
  if($_COOKIE['msg']=="update")
  {

  ?>
  <div class="alert alert-primary alert-dismissible" role="alert">
    Data updated succesfully
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
    </button>
  </div>
  <script type="text/javascript">eraseCookie("msg")</script>
  <?php
  }
  if($_COOKIE['msg']=="data_del")
  {

  ?>
  <div class="alert alert-primary alert-dismissible" role="alert">
    Data deleted succesfully
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
    </button>
  </div>
  <script type="text/javascript">eraseCookie("msg")</script>
  <?php
  }
  if($_COOKIE['msg']=="fail")
  {
  ?>

  <div class="alert alert-danger alert-dismissible" role="alert">
    An error occured! Try again.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
    </button>
  </div>
  <script type="text/javascript">eraseCookie("msg")</script>
  <?php
  }
}
  if(isset($_COOKIE["sql_error"]))
  {
    ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
      <?php echo urldecode($_COOKIE['sql_error'])?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
      </button>
    </div>

    <script type="text/javascript">eraseCookie("sql_error")</script>
    <?php
  }
?>

<?php if(in_array($user_id, $admin)){ ?> 
    <!-- grid -->

    <!-- Basic Bootstrap Table -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Records</h5>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>Taluka</th>
              <th>Area</th>
              <th>Industrial Estate</th>
              <th>Company Name</th>
              <th>GST No</th>
              <th>Total Count</th>
              <th></th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php 
      
              // $stmt_list = $obj->con1->prepare("SELECT cid, industrial_estate, area, taluka, company_id, sum(count) as total_count FROM pr_visit_count group by company_id");
              $stmt_list = $obj->con1->prepare("SELECT c.cid, c.industrial_estate, c.area, c.taluka, c.company_id, SUM(c.count) AS total_count, JSON_UNQUOTE(t.raw_data->'$.post_fields.Firm_Name') AS firm_name, JSON_UNQUOTE(t.raw_data->'$.post_fields.GST_No') AS gst_no FROM pr_visit_count c LEFT JOIN tbl_tdrawdata t ON c.company_id = t.id GROUP BY c.company_id");
              $stmt_list->execute();
              $result = $stmt_list->get_result();
              $stmt_list->close();
              $i=1;

              while($data=mysqli_fetch_array($result))
              {

                $stmt_employee_list = $obj->con1->prepare("SELECT c1.employee_id, u1.name, c1.count, GROUP_CONCAT(date_format(d1.datetime,'%d-%m-%y')) as visit_dates FROM pr_visit_count c1, pr_visit_dates d1, tbl_users u1 WHERE c1.employee_id=u1.id and d1.visit_count_id=c1.cid and c1.company_id=? group by c1.employee_id");
                $stmt_employee_list->bind_param("i",$data['company_id']);
                $stmt_employee_list->execute();
                $result_employee_list = $stmt_employee_list->get_result();
                $stmt_employee_list->close();
            ?>

            <tr>
              <td><?php echo $i?></td>
              <td><?php echo $data["taluka"] ?></td>
              <td><?php echo $data["area"] ?></td>
              <td><?php echo $data["industrial_estate"] ?></td>
              <td><?php echo $data["firm_name"] ?></td>
              <td><?php echo $data["gst_no"] ?></td>
              <td><?php echo $data["total_count"] ?></td>
              <td>
                <i class="bx bx-info-circle bx-sm" data-bs-toggle="tooltip" data-bs-offset="0,2" data-bs-placement="top" data-bs-html="true" 
                title="
              <?php
                while($employee_list=mysqli_fetch_array($result_employee_list)){
                  echo $employee_list['name']."  -  ".$employee_list['count']." ( ".$employee_list['visit_dates']." )<br/>";
                } 
              ?>
               "></i>
              </td>
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

  <!-- / grid -->

<?php } ?>

<!-- Modal -->
<div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Update Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div>
        <form  method="post"><div class="modal-body" >
          <div class="row">
            <input type="hidden" class="form-control" name="industrial_estate_id" id="industrial_estate_id" />

            <div class="mb-3">
              <label class="form-label" for="basic-default-fullname" id="industrial_estate"></label><br/>
              <label class="form-label" for="basic-default-fullname" id="taluka"></label>
            </div>

            <div class="mb-3">
              <label class="form-label" for="basic-default-fullname">Status</label><br/>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="verify_status" id="Verified" value="Verified" required>
                <label class="form-check-label" for="inlineRadio1">Verified</label>
              </div>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="verify_status" id="Fake" value="Fake" required>
                <label class="form-check-label" for="inlineRadio1">Fake</label>
              </div>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="verify_status" id="Duplicate" value="Duplicate" required>
                <label class="form-check-label" for="inlineRadio1">Duplicate</label>
              </div>
            </div>
          </div></div>
          <div class="modal-footer">
            <input type="submit" class="btn btn-primary" name="btn_modal_update" id="btn_modal_update" value="Save Changes">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- /modal-->

  <!-- / Content -->
<script type="text/javascript">

  function editdata(id,state,city,taluka,area,industrial_estate,status) {
    $('#modalCenter').modal('toggle');
    $('#industrial_estate_id').val(id);
    $('#industrial_estate').html("Industrial Estate : "+industrial_estate);
    $('#taluka').html("Taluka : "+taluka);
    $('#'+status).attr("checked","checked");
  }  

</script>

<?php 
  include("footer.php");
?>