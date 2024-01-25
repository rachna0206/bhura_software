<?php
  include("header.php");

$user_id = $_SESSION["id"];

// insert data
if(isset($_REQUEST['btn_modal_update']))
{
  $start_date = $_REQUEST['start_date'];
  $end_date = $_REQUEST['end_date'];
  $action = 'company_entry';
  $user_id = $_SESSION["id"];
  $estate_id = explode(",",$_REQUEST['industrial_estate']);

  try
  {
    foreach($_REQUEST['e'] as $emp_id){
      foreach($estate_id as $industrial_estate_id){
        $stmt = $obj->con1->prepare("INSERT INTO `assign_estate`(`employee_id`, `industrial_estate_id`, `start_dt`, `end_dt`, `user_id`, `action`) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("iissis",$emp_id,$industrial_estate_id,$start_date,$end_date,$user_id,$action);
        $Resp=$stmt->execute();

        foreach($_REQUEST['filter'] as $filter){
          // insert into pr_emp_estate
          $stmt_est = $obj->con1->prepare("INSERT INTO `pr_emp_estate`(`employee_id`, `industrial_estate_id`, `assign_estate_status`) VALUES (?,?,?)");
          $stmt_est->bind_param("iis",$emp_id,$industrial_estate_id,$filter);
          $Resp_est=$stmt_est->execute();
        }
      }
    }

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
    header("location:unassigned_estate_company.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:unassigned_estate_company.php");
  }
}

// update status
if(isset($_REQUEST['btn_modal_update_status']))
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
    header("location:unassigned_estate_company.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:unassigned_estate_company.php");
  }
}
?>

<h4 class="fw-bold py-3 mb-4">Unassigned Estate Master (For Company)</h4>

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


<!-- Assign Modal -->
<div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Assign Estate For Company</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div id="assign_estate_modal"></div>
    </div>
  </div>
</div>

<!-- /modal-->

<!-- View Modal -->
<div class="modal fade" id="viewModalCenter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Industrial Estate Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div id="view_modal"></div>
    </div>
  </div>
</div>

<!-- /modal-->

<!-- Status Modal -->
<div class="modal fade" id="statusModalCenter" tabindex="-1" aria-hidden="true">
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
            <input type="submit" class="btn btn-primary" name="btn_modal_update_status" id="btn_modal_update_status" value="Save Changes">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- /modal-->

<?php if(in_array($user_id, $admin)){ ?> 
    <!-- grid -->

    <!-- Basic Bootstrap Table -->
    <div class="card">
      <h5 class="card-header">Records</h5>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="table_id">
          <thead>
            <tr>
              <th></th>
              <th>Srno</th>
              <th>State</th>
              <th>City</th>
              <th>Taluka</th>
              <th>Area</th>
              <th>Industrial Estate</th>
              
              <th>Plotting Pattern</th>
              <th>Total Plots</th>
              <th>Added By</th>
              <th>Date Time</th>
              <th>Action</th>  
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php 

              //select i1.state_id, i1.city_id, i1.taluka, i1.area_id, i1.industrial_estate, d1.plotting_pattern, d1.status, ttl.industrial_estate_id, sum(ttl.sum1) as total from (( SELECT sum(IfNULL(plot_end_no,1)) as sum1,industrial_estate_id  from pr_estate_roadplot where plot_end_no is null GROUP BY industrial_estate_id) union all (select sum((plot_end_no-plot_start_no)+1) as sum1,industrial_estate_id from pr_estate_roadplot where plot_end_no is not null GROUP BY industrial_estate_id )) ttl, pr_add_industrialestate_details d1, tbl_industrial_estate i1 where d1.industrial_estate_id=i1.id and ttl.industrial_estate_id=i1.id and (d1.status is null or d1.status not in ('Fake','Duplicate')) and d1.industrial_estate_id not in (SELECT industrial_estate_id FROM `assign_estate` WHERE action='company_entry') group by industrial_estate_id order by d1.id desc

              
              //SELECT i1.state_id, i1.city_id, i1.taluka, i1.area_id, i1.industrial_estate, d1.plotting_pattern, d1.status, i1.id as industrial_estate_id from pr_add_industrialestate_details d1, tbl_industrial_estate i1 where d1.industrial_estate_id=i1.id and (d1.status is null or d1.status not in ('Fake','Duplicate')) and d1.industrial_estate_id not in (SELECT industrial_estate_id FROM `assign_estate` WHERE action='company_entry') group by industrial_estate_id order by d1.id desc

              $stmt_list = $obj->con1->prepare("SELECT i1.state_id, i1.city_id, i1.taluka, i1.area_id, i1.industrial_estate, d1.plotting_pattern, d1.status, i1.id as industrial_estate_id, count(p1.plot_no) as total,u1.name as user_name,d1.datetime from pr_add_industrialestate_details d1, tbl_industrial_estate i1, pr_company_plots p1,tbl_users u1 where d1.industrial_estate_id=i1.id and i1.id=p1.industrial_estate_id and d1.user_id=u1.id and (d1.status is null or d1.status not in ('Fake','Duplicate')) and d1.industrial_estate_id not in (SELECT industrial_estate_id FROM `assign_estate` WHERE action='company_entry') group by industrial_estate_id order by d1.id desc;");
              $stmt_list->execute();
              $result = $stmt_list->get_result();
              $stmt_list->close();
              $i=1;

              while($data=mysqli_fetch_array($result))
              {

                /*$stmt_sum = $obj->con1->prepare("SELECT count(plot_no) as total from pr_company_plots where floor='0' and industrial_estate_id=?");
                $stmt_sum->bind_param("i",$data['industrial_estate_id']);
                $stmt_sum->execute();
                $result_sum = $stmt_sum->get_result()->fetch_assoc();
                $stmt_sum->close();*/
            ?>

            <tr>
              <td><input type="checkbox" id="estate_id_<?php echo $data["industrial_estate_id"] ?>" onclick="show_assign(this.value)" name="estate_id" value="<?php echo $data["industrial_estate_id"] ?>" class="call-checkbox"/></td>
              <td><?php echo $i?></td>
              <td><?php echo $data["state_id"] ?></td>
              <td><?php echo $data["city_id"] ?></td>
              <td><?php echo $data["taluka"] ?></td>
              <td><?php echo $data["area_id"] ?></td>
              <td><?php echo $data["industrial_estate"] ?></td>
              
              <td><?php echo $data["plotting_pattern"] ?></td>
              <td><?php echo $data["total"] ?></td>
              <td><?php echo $data["user_name"] ?></td>
              <td><?php echo date("d-m-Y h:i A", strtotime($data["datetime"])) ?></td>
              <td>
                <a href="javascript:editdata('<?php echo $data["industrial_estate_id"]?>','<?php echo $data["state_id"]?>','<?php echo $data["city_id"]?>','<?php echo $data["taluka"]?>','<?php echo $data["area_id"]?>','<?php echo $data["industrial_estate"]?>','<?php echo $data["status"] ?>');"><i class="bx bx-edit-alt me-1"></i> </a>

                <a href="javascript:viewdata('<?php echo $data["industrial_estate_id"]?>','<?php echo $data["plotting_pattern"]?>','<?php echo $data["state_id"]?>','<?php echo $data["city_id"]?>','<?php echo $data["taluka"]?>','<?php echo $data["area_id"]?>','<?php echo $data["industrial_estate"]?>');">View</a> 

                <a href="javascript:assign_estate();" id="assign_<?php echo $data["industrial_estate_id"] ?>" hidden>Assign</a>
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

  <!-- / Content -->

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
  
  
  $(document).ready(function() {
    $('.js-example-basic-multiple').select2();
  });
  
  function show_assign(estate_id){
    
    if($('#estate_id_'+estate_id).is(':checked')){
      $('#assign_'+estate_id).removeAttr("hidden");
    }
    else{
      $('#assign_'+estate_id).attr("hidden",true);
    }
  } 
  
  function assign_estate(){

    var estate_array = [];

    //datatable has to be initialized to a variable
    var myTable = $('#table_id').dataTable();

    //checkboxes should have a general class to traverse
    var rowcollection = myTable.$(".call-checkbox:checked", {"page": "all"});

    //Now loop through all the selected checkboxes to perform desired actions
    rowcollection.each(function(index,elem){
        //You have access to the current iterating row
      estate_array.push($(elem).val());
    });

    $('#modalCenter').modal('toggle');
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=assign_estate_forcompany",
      data: "ind_estate_id="+estate_array,
      cache: false,
      success: function(result){
        $('#assign_estate_modal').html('');
        $('#assign_estate_modal').html(result);

        $('#emp_list').css("width","100%");
        $('.js-example-basic-multiple').select2({
          dropdownParent: $('#modalCenter')
        });
      }
    });
  }

  function editdata(id,state,city,taluka,area,industrial_estate,status) {
    $('#statusModalCenter').modal('toggle');
    $('#industrial_estate_id').val(id);
    $('#industrial_estate').html("Industrial Estate : "+industrial_estate);
    $('#taluka').html("Taluka : "+taluka);
    if(status==""){
      $('#Verified').removeAttr("checked",false);  
      $('#Fake').removeAttr("checked",false);  
      $('#Duplicate').removeAttr("checked",false);  
    }
    else{
      $('#'+status).attr("checked","checked");
    }
  } 

  function viewdata(ind_estate_id,plotting_pattern,state,city,taluka,area,ind_estate) {
    $('#viewModalCenter').modal('toggle');
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=view_estate_details",
      data: "ind_estate_id="+ind_estate_id,
      cache: false,
      success: function(result){
        $('#view_modal').html('');
        $('#view_modal').html(result);
   
        $('#btn_modal_update').attr('hidden',true);
        $('#btn_modal_update').attr('disabled',true);

        $('#state_modal').val(state);
        $('#city_modal').val(city);
        $('#area_modal').val(area);
        $('#taluka_modal').val(taluka);
        $('#industrial_estate_modal').val(ind_estate);

        $('#state_label').html("State : "+state);
        $('#city_label').html("City : "+city);
        $('#area_label').html("Area : "+area);
        $('#taluka_label').html("Taluka : "+taluka);
        $('#industrial_estate_label').html("Industrial Estate : "+ind_estate);

        $('#emp_list').css("width","100%");
        $('.js-example-basic-multiple').select2({
          dropdownParent: $('#modalCenter')
        });
      }
    });
  }

</script>

<?php 
  include("footer.php");
?>