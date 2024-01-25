<?php
  include("header.php");

  $user_id = $_SESSION["id"];


// delete data
if(isset($_REQUEST["flg"]) && $_REQUEST["flg"]=="del")
{
  try
  {
    $stmt_del = $obj->con1->prepare("DELETE FROM `tbl_user_devices` WHERE `uid`='".$_REQUEST["n_id"]."'");
    $Resp=$stmt_del->execute();
    if(!$Resp)
    {
      throw new Exception("Problem in deleting! ". strtok($obj->con1-> error,  '('));
    }
    $stmt_del->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
    setcookie("msg", "data_del",time()+3600,"/");
    header("location:logged_users.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:logged_users.php");
  }
}






if(isset($_REQUEST['btn_modal_update']))
{
  $state = $_REQUEST['state_modal'];
  $city = $_REQUEST['city_modal'];
  $taluka = $_REQUEST['taluka_modal'];
  $area = $_REQUEST['area_modal'];
  $industrial_estate = $_REQUEST['industrial_estate_modal'];
  $industrial_estate_id = $_REQUEST['ind_estate_id'];
  $start_date = $_REQUEST['start_date_modal'];
  $end_date = $_REQUEST['end_date_modal'];
  $action = 'company_entry';
  $id=$_REQUEST['ttId'];
  
  try
  {

    $stmt_del = $obj->con1->prepare("DELETE from assign_estate where industrial_estate_id=? and action='company_entry'");
    $stmt_del->bind_param("i", $industrial_estate_id);
    $Resp=$stmt_del->execute();

    foreach($_REQUEST['e'] as $emp_id){
      $stmt = $obj->con1->prepare("INSERT INTO `assign_estate`(`employee_id`, `industrial_estate_id`, `start_dt`, `end_dt`, `user_id`, `action`) VALUES (?,?,?,?,?,?)");
      $stmt->bind_param("iissis",$emp_id,$industrial_estate_id,$start_date,$end_date,$user_id,$action);
      $Resp=$stmt->execute();
    }

    if(!$Resp)
    {
      throw new Exception("Problem in updating! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
	  setcookie("msg", "update",time()+3600,"/");
    header("location:assign_estate.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:assign_estate.php");
  }
}



?>

<h4 class="fw-bold py-3 mb-4">Logged In User List</h4>

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

   <!-- grid -->

    <!-- Basic Bootstrap Table -->
    <div class="card">
      <h5 class="card-header">Records</h5>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>User Name</th>
              <th>E-mail</th>
              <th>Phone No.</th>
              <th>Role</th>
              <th></th>
             
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php 
              $stmt_list = $obj->con1->prepare("SELECT t1.* FROM tbl_users t1,tbl_user_devices t2 where t2.uid=t1.id");
              $stmt_list->execute();
              $result = $stmt_list->get_result();
              $stmt_list->close();

              $i=1;

              while($data=mysqli_fetch_array($result))
              {
            ?>

            <tr>
              <td><?php echo $i?></td>
              <td><?php echo $data["name"] ?></td>
              <td><?php echo $data["email"] ?></td>
              <td><?php echo $data["phone_no"] ?></td>
              <td><?php echo $data["role"] ?></td>
              <td><a href="javascript:deletedata('<?php echo $data["id"]?>');">Forced Logout</a></td>
              
              
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

    <!-- Modal -->
    <div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalCenterTitle">Assign Estate Update Page</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div id="assign_estate_modal"></div>
        </div>
      </div>
    </div>

    <!-- /modal-->

    <!-- / Content -->

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">

  $(document).ready(function() {
    $('.js-example-basic-multiple').select2();
  });
  
  function deletedata(id) {
    if(confirm("Are you sure to Log Out User?")) {
        var loc = "logged_users.php?flg=del&n_id="+id;
        window.location = loc;
    }
  }

  function editdata(id,emp_id,state,city,taluka,area,ind_estate,ind_estate_id,start_dt,end_dt) {
    $('#modalCenter').modal('toggle');
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=assign_estate_modal",
      data: "id="+id+"&ind_estate_id="+ind_estate_id+"&pg=company_entry",
      cache: false,
      success: function(result){
        $('#assign_estate_modal').html('');
        $('#assign_estate_modal').html(result);
   
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

        $('#emp_list').prop("readonly",false);
        $('#start_date_modal').prop("readonly",false);
        $('#end_date_modal').prop("readonly",false);
      }
    });
  }
  function viewdata(id,emp_id,state,city,taluka,area,ind_estate,ind_estate_id,start_dt,end_dt) {
    $('#modalCenter').modal('toggle');
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=assign_estate_modal",
      data: "id="+id+"&ind_estate_id="+ind_estate_id+"&pg=company_entry",
      cache: false,
      success: function(result){
        $('#assign_estate_modal').html('');
        $('#assign_estate_modal').html(result);
   
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

        $('#emp_list').prop("readonly",true);
        $('#start_date_modal').prop("readonly",true);
        $('#end_date_modal').prop("readonly",true);
      }
    });
  }
</script>

<?php 
  include("footer.php");
?>