<?php
  include("header.php");

  $sid = $_SESSION["id"];

  // insert data
  if (isset($_REQUEST['btnsubmit'])) {
    $scheme_name = $_REQUEST['scheme_name'];
    $status = $_REQUEST['status'];
    try {
      $stmt_scheme_insert = $obj->con1->prepare("INSERT INTO `pr_scheme`(`scheme_name`, `status`) VALUES (?, ?)");
      $stmt_scheme_insert->bind_param("ss", $scheme_name, $status);
      $Resp = $stmt_scheme_insert->execute();
      $stmt_scheme_insert->close();
      if (!$Resp) {
        throw new Exception("Problem in adding! " . strtok($obj->con1->error, '('));
      }
    } catch (\Exception $e) {
      setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
      setcookie("msg", "data", time() + 3600, "/");
      header("location:scheme.php");
    } else {
      setcookie("msg", "fail", time() + 3600, "/");
      header("location:scheme.php");
    }
  }

  // delete data
if(isset($_REQUEST["btndelete"])) 
{
  try
  {
    $x = $_REQUEST["s_id"];
    $stmt_del = $obj->con1->prepare("DELETE FROM `pr_scheme` WHERE sid=?");
    $stmt_del->bind_param("i", $x);
    $Resp=$stmt_del->execute();
    if(!$Resp)
    {
      if(strtok($obj->con1->error,  ':')=="Cannot delete or update a parent row")
      {
        throw new Exception("Scheme is already in use!");
      }
    }
    $stmt_del->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
   setcookie("msg", "data_del",time()+3600,"/");
     header("location:scheme.php");
  }
}

//update data
if(isset($_REQUEST['btnupdate']))
{
  $sid = $_REQUEST['sid'];
  $scheme_name = $_REQUEST['scheme_name'];
  $status = $_REQUEST['status'];  
  try
  {
    $stmt = $obj->con1->prepare("UPDATE `pr_scheme` SET `scheme_name`=?,`status`=? WHERE `sid`=?");
    $stmt->bind_param("ssi", $scheme_name,$status,$sid);
    $Resp=$stmt->execute();
    if(!$Resp)
    {
      throw new Exception("Problem in updating! ". strtok($obj->con1->error,  '('));
    }
    $stmt->close();
  } 
  catch(Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
    setcookie("msg", "update",time()+3600,"/");
      header("location:scheme.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
      header("location:scheme.php");
  }
}

?>

<h4 class="fw-bold py-3 mb-4">Scheme Master</h4>

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
            <h5 class="mb-0">Add Scheme</h5> 
          </div>
          <div class="card-body">
            <form method="post" enctype="multipart/form-data">   
              <input type="hidden" name="sid" id="sid">
                <div class="mb-3">
                  <label class="form-label" for="basic-default-fullname">Scheme Name</label>
                  <input type="text" class="form-control" name="scheme_name" id="scheme_name" required />
                </div>

                <div class="mb-3">
                  <label class="form-label" for="basic-default-fullname">Status</label>
                  <div class="form-check form-check-inline mt-3">
                    <input class="form-check-input" type="radio" name="status" id="enable" value="enable" required>
                    <label class="form-check-label" for="inlineRadio1">Enable</label>
                  </div>
                  <div class="form-check form-check-inline mt-3">
                    <input class="form-check-input" type="radio" name="status" id="disable" value="disable" required>
                    <label class="form-check-label" for="inlineRadio1">Disable</label>
                  </div>
                </div>
              <button type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary">Save</button>
              <button type="submit" name="btnupdate" id="btnupdate" class="btn btn-primary " hidden>Update</button>
              <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()" hidden>Cancel</button>
            </form>
          </div>
        </div>
      </div>
    </div>

  <!-- / Content -->
     <!-- grid -->
     <!-- Delete Modal -->
<div class="modal fade" id="backDropModal" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="backDropModalTitle">Delete Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col mb-3">
            <label for="nameBackdrop" class="form-label" id="label_del"></label>
            <input type="hidden" name="s_id" id="s_id">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="btndelete" class="btn btn-primary">Delete</button>
      </div>
    </form>
  </div>
</div>

    <!-- Basic Bootstrap Table -->
    <div class="card">
      <h5 class="card-header">Schemes Data</h5>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>Scheme Name</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php 
             // Scheme list
              $stmt_scheme = $obj->con1->prepare("SELECT `sid`, `scheme_name`, `status` FROM `pr_scheme`");
              $stmt_scheme->execute();
              $scheme_result = $stmt_scheme->get_result();
              $stmt_scheme->close();

              $i=1;

              while($data=mysqli_fetch_array($scheme_result))
              {
            ?>

            <tr>
              <td><?php echo $i?></td>
              <td><?php echo $data["scheme_name"] ?></td>
              <td><?php echo $data["status"] ?></td>
              <td>
                <a href="javascript:editdata('<?php echo $data["sid"]?>','<?php echo $data["scheme_name"]?>','<?php echo $data["status"]?>');"><i class="bx bx-edit-alt me-1"></i> </a>
                <a href="javascript:deletedata('<?php echo $data["sid"]?>');"><i class="bx bx-trash me-1"></i> </a>
                <a href="javascript:viewdata('<?php echo $data["sid"]?>','<?php echo $data["scheme_name"]?>','<?php echo $data["status"]?>');">View</a> 
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
  <script src="assets/js/ui-popover.js"></script>

<script type="text/javascript">

  function editdata(sid, scheme_name, status) {
      
      $('#scheme_name').focus();
      $('#sid').val(sid);
      $('#scheme_name').val(scheme_name);
      if(status=="enable")
      {
        $('#enable').prop("checked","checked"); 
      }
      else if(status=="disable")
      {
        $('#disable').prop("checked","checked");  
      }      
      
      $('#btnsubmit').attr('hidden',true);
      $('#btnupdate').removeAttr('hidden');
      $('#btnsubmit').attr('disabled',true);
      $('#btnupdate').removeAttr('disabled',false);
      $('#btncancel').removeAttr('hidden');

  }

  function deletedata(sid) {
    $('#backDropModal').modal('toggle');
    $('#s_id').val(sid);
    $('#label_del').html('Are you sure you want to DELETE record ?');
  }

  function viewdata(sid, scheme_name, status) {

      $('#sid').val(sid);
      $('#scheme_name').val(scheme_name);
      if(status=="enable")
      {
        $('#enable').prop("checked","checked"); 
      }
      else if(status=="disable")
      {
        $('#disable').prop("checked","checked");  
      }
      $('#btnsubmit').attr('hidden',true);
      $('#btnupdate').attr('hidden',true);
      $('#btnsubmit').attr('disabled',true);
      $('#btnupdate').attr('disabled',true);
      $('#btncancel').removeAttr('hidden');

  }

</script>
<?php 
  include("footer.php");
?>