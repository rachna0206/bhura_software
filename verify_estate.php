<?php
  include("header.php");

$user_id = $_SESSION["id"];

// Assigned Industrial Estate List
$stmt_estate_list = $obj->con1->prepare("SELECT a1.*,i1.industrial_estate, i1.taluka FROM assign_estate a1, tbl_industrial_estate i1 WHERE a1.industrial_estate_id=i1.id and employee_id=? and start_dt<=curdate() and end_dt>=curdate() and a1.action='company_entry'");
$stmt_estate_list->bind_param("i",$user_id);
$stmt_estate_list->execute();
$estate_result = $stmt_estate_list->get_result();
$stmt_estate_list->close();

// insert data
if(isset($_REQUEST['btnsubmit']))
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
    header("location:verify_estate.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:verify_estate.php");
  }
}

?>

<h4 class="fw-bold py-3 mb-4">Industrial Estate Master</h4>

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
            <h5 class="mb-0">Add Industrial Estate Details</h5> 
          </div>

          <div class="card-body">
            <form method="post" enctype="multipart/form-data">
              <input type="hidden" name="ttId" id="ttId">
              <input type="hidden" name="ind_estate_id" id="ind_estate_id">
        
                <div class="mb-3">
                  <label class="form-label" for="basic-default-company">Industrial Estate</label>
                  <select name="industrial_estate_id" id="industrial_estate_id" class="form-control" onchange="getStatus_Estate(this.value)" required>
                    <option value="">Select Industrial Estate</option>
              <?php while($estate = mysqli_fetch_array($estate_result)){ ?>
                    <option value="<?php echo $estate['industrial_estate_id'] ?>"><?php echo $estate['industrial_estate']." - ".$estate['taluka'] ?> </option>
              <?php } ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="basic-default-fullname"></label>
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

              <button type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary">Save</button>
          
              <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button>

            </form>
          </div>
        </div>
      </div>
      
    </div>
    <!-- grid -->
    <!--/ Basic Bootstrap Table -->

  <!-- / grid -->

  <!-- / Content -->

<script type="text/javascript">
  function getStatus_Estate(estate_id){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=getStatus_Estate",
      data: "estate_id="+estate_id,
      cache: false,
      success: function(result){
        if(result==""){
          $('#Verified').removeAttr("checked",false);  
          $('#Fake').removeAttr("checked",false);  
          $('#Duplicate').removeAttr("checked",false);  
        }
        else{
          $('#'+result).attr("checked","checked");
        }
      }
    });
  }
</script>
<?php 
  include("footer.php");
?>