<?php
  include("header.php");

// insert data
if(isset($_REQUEST['btnsubmit']))
{
  $emp_name = $_REQUEST['emp_name'];
  $gender = $_REQUEST['gender'];
  $dob = $_REQUEST['dob'];
  $address = $_REQUEST['address'];
  $native = $_REQUEST['native'];
  $designation = $_REQUEST['designation'];
  $guj_stay = $_REQUEST['guj_stay'];
  
  try
  {
  	$stmt = $obj->con1->prepare("INSERT INTO `pr_random_emp_list`(`ename`, `gender`, `dob`, `address`, `native`, `designation`, `guj_stay`) VALUES (?,?,?,?,?,?,?)");
  	$stmt->bind_param("sssssss",$emp_name,$gender,$dob,$address,$native,$designation,$guj_stay);
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
    header("location:employee_master.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:employee_master.php");
  }
}

if(isset($_REQUEST['btnupdate']))
{
  $emp_name = $_REQUEST['emp_name'];
  $gender = $_REQUEST['gender'];
  $dob = $_REQUEST['dob'];
  $address = $_REQUEST['address'];
  $native = $_REQUEST['native'];
  $designation = $_REQUEST['designation'];
  $guj_stay = $_REQUEST['guj_stay'];
  $id=$_REQUEST['ttId'];

  try
  {
    $stmt = $obj->con1->prepare("UPDATE `pr_random_emp_list` set `ename`=?, `gender`=?, `dob`=?, `address`=?, `native`=?, `designation`=?, `guj_stay`=? where `eid`=?");
  	$stmt->bind_param("sssssssi", $emp_name,$gender,$dob,$address,$native,$designation,$guj_stay,$id);
  	$Resp=$stmt->execute();
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
      header("location:employee_master.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
      header("location:employee_master.php");
  }
}

// delete data
if(isset($_REQUEST["flg"]) && $_REQUEST["flg"]=="del")
{
  try
  {
    $stmt_del = $obj->con1->prepare("DELETE from pr_random_emp_list where eid='".$_REQUEST["n_id"]."'");
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
    header("location:employee_master.php");
  }
  else
  {
	setcookie("msg", "fail",time()+3600,"/");
    header("location:employee_master.php");
  }
}

?>

<h4 class="fw-bold py-3 mb-4">Employee Master</h4>

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


  <!-- Basic Layout -->
  <div class="row">
    <div class="col-xl">
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Add Employee</h5>
          
        </div>
        <div class="card-body">
          <form method="post" >
           
            <input type="hidden" name="ttId" id="ttId">
            <div class="row g-2">
              <div class="col mb-3">
                <label class="form-label" for="basic-default-fullname">Employee Name</label>
                <input type="text" class="form-control" name="emp_name" id="emp_name" required />
              </div>
              <div class="col mb-3">
                <div><label class="form-label" for="basic-default-fullname">Gender</label></div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="gender" id="male" value="Male">
                  <label class="form-check-label" for="inlineRadio1">Male</label>
                </div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="gender" id="female" value="Female">
                  <label class="form-check-label" for="inlineRadio1">Female</label>
                </div>
              </div>
            </div>

            <div class="row g-2">
              <div class="col mb-3">
                <label class="form-label" for="basic-default-fullname">Date of Birth</label>
                <input type="date" class="form-control" name="dob" id="dob" required />
              </div>
              <div class="col mb-3">
                <label class="form-label" for="basic-default-fullname">Native</label>
                <input type="text" class="form-control" name="native" id="native" required />
              </div>
              
            </div>

            <div class="row g-2">
              <div class="col mb-3">
                <label class="form-label" for="basic-default-fullname">Designation</label>
                <input type="text" class="form-control" name="designation" id="designation" required />
              </div>
              <div class="col mb-3">
                <label class="form-label" for="basic-default-fullname">Gujarat Stay</label>
                <input type="date" class="form-control" name="guj_stay" id="guj_stay" required />
              </div>
            </div>
            
            <div class="col mb-3">
              <label class="form-label" for="basic-default-fullname">Address</label>
              <textarea class="form-control" name="address" id="address"></textarea>
              <!-- <input type="text" class="form-control" name="address" id="address" required /> -->
            </div>
            
        
            <button type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary">Save</button>
        
            <button type="submit" name="btnupdate" id="btnupdate" class="btn btn-primary " hidden>Update</button>
        
            <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button>

          </form>
        </div>
      </div>
    </div>
    
  </div>


<!-- grid -->

<!-- Basic Bootstrap Table -->
  <div class="card">
    <h5 class="card-header">Employee Records</h5>
    <div class="table-responsive text-nowrap">
      <table class="table" id="table_id">

        <thead>
          <tr>
            <th>Srno</th>
            <th>Name</th>
            <th>Date of Birth</th>
            <th>Native</th>
            <th>Designation</th>
            <th>Gujarat Stay</th>
            <th>Gender</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <?php 
            $stmt_list = $obj->con1->prepare("SELECT * from pr_random_emp_list order by eid desc");
            $stmt_list->execute();
            $result = $stmt_list->get_result();
            
            $stmt_list->close();
            $i=1;
            while($emp=mysqli_fetch_array($result))
            {
              ?>

          <tr>
            <td><?php echo $i?></td>
            <td><?php echo $emp["ename"]?></td>
            <td><?php echo date('d/m/Y',strtotime($emp["dob"]))?></td>
            <td><?php echo $emp["native"]?></td>
            <td><?php echo $emp["designation"]?></td>
            <!-- <td><?php echo $emp["guj_stay"]?></td> -->
            <td><?php echo date('d/m/Y',strtotime($emp["guj_stay"]))?></td>
            <td><?php echo $emp["gender"]?></td>
            <td>
            	<a href="javascript:editdata('<?php echo $emp["eid"]?>','<?php echo base64_encode($emp["ename"])?>','<?php echo base64_encode($emp["gender"])?>','<?php echo base64_encode($emp["dob"])?>','<?php echo base64_encode($emp["address"])?>','<?php echo base64_encode($emp["native"])?>','<?php echo base64_encode($emp["designation"])?>','<?php echo base64_encode($emp["guj_stay"])?>');"><i class="bx bx-edit-alt me-1"></i> </a>
              <a href="javascript:deletedata('<?php echo $emp["eid"]?>');"><i class="bx bx-trash me-1"></i> </a>
            	<a href="javascript:viewdata('<?php echo $emp["eid"]?>','<?php echo base64_encode($emp["ename"])?>','<?php echo base64_encode($emp["gender"])?>','<?php echo base64_encode($emp["dob"])?>','<?php echo base64_encode($emp["address"])?>','<?php echo base64_encode($emp["native"])?>','<?php echo base64_encode($emp["designation"])?>','<?php echo base64_encode($emp["guj_stay"])?>');">View</a>
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

<!-- / Content -->
<script type="text/javascript">
  function deletedata(id) {

      if(confirm("Are you sure to DELETE data?")) {
          var loc = "employee_master.php?flg=del&n_id=" + id;
          window.location = loc;
      }
  }
  function editdata(id,name,gender,dob,address,native,desig,guj_stay) {
      $('#emp_name').focus();   
     	$('#ttId').val(id);
			$('#emp_name').val(atob(name));
      $('#dob').val(atob(dob));
      $('#native').val(atob(native));
      $('#designation').val(atob(desig));
      $('#guj_stay').val(atob(guj_stay));
      $('#address').val(atob(address));
      if(atob(gender)=='Male'){
        $('#male').attr('checked',true);
      }
      else if(atob(gender)=='Female'){
        $('#female').attr('checked',true);
      }
      
			$('#btnsubmit').attr('hidden',true);
      $('#btnupdate').removeAttr('hidden');
			$('#btnsubmit').attr('disabled',true);
  }
  function viewdata(id,name,gender,dob,address,native,desig,guj_stay) {
      $('#emp_name').focus();   
      $('#ttId').val(id);
      $('#emp_name').val(atob(name));
      $('#dob').val(atob(dob));
      $('#native').val(atob(native));
      $('#designation').val(atob(desig));
      $('#guj_stay').val(atob(guj_stay));
      $('#address').val(atob(address));
      if(atob(gender)=='Male'){
        $('#male').attr('checked',true);
      }
      else if(atob(gender)=='Female'){
        $('#female').attr('checked',true);
      }
			
			$('#btnsubmit').attr('hidden',true);
      $('#btnupdate').attr('hidden',true);
			$('#btnsubmit').attr('disabled',true);
			$('#btnupdate').attr('disabled',true);

  }
</script>
<?php 
  include("footer.php");
?>