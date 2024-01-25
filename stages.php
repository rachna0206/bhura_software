<?php
include("header.php");

$st_id = $_SESSION["id"];

// Scheme List
$stmt_scheme_list = $obj->con1->prepare("SELECT `sid`, `scheme_name` FROM `pr_scheme`");
$stmt_scheme_list->execute();
$scheme_result = $stmt_scheme_list->get_result();
$stmt_scheme_list->close();

// Insert data
if (isset($_REQUEST['btnsubmit'])) {
    $st_id = $_REQUEST['st_id'];
    $scheme_id = $_REQUEST['scheme_id'];
    $stage_name = $_REQUEST['stage_name'];
    $status = $_REQUEST['status'];

    try {
        $stmt_stages_insert = $obj->con1->prepare("INSERT INTO `pr_stages`(`scheme_id`, `stage_name`, `status`) VALUES (?, ?, ?)");
        $stmt_stages_insert->bind_param("iss", $scheme_id, $stage_name, $status);
        $Resp = $stmt_stages_insert->execute();
        $stmt_stages_insert->close();

        if (!$Resp) {
            throw new Exception("Problem in adding: " . $obj->con1->error);
        }
    } catch (Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data", time() + 3600, "/");
        header("location:stages.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:stages.php");
    }
}

//update data
if(isset($_REQUEST['btnupdate']))
{
    $st_id = $_REQUEST['st_id'];
    $scheme_id = $_REQUEST['scheme_id'];
    $stage_name = $_REQUEST['stage_name'];
    $status = $_REQUEST['status']; 
  try
  {
    $stmt = $obj->con1->prepare("UPDATE `pr_stages` SET `scheme_id`=?,`stage_name`=?,`status`=? WHERE `st_id`=?");
    $stmt->bind_param("issi", $scheme_id,$stage_name,$status,$st_id);
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
      header("location:stages.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
      header("location:stages.php");
  }
}

// delete data
if (isset($_REQUEST['btndelete'])) {
    try {
        $x = $_REQUEST['stage_id'];
        $stmt_del = $obj->con1->prepare("DELETE FROM `pr_stages` WHERE `st_id`=?");
        $stmt_del->bind_param("i", $x);
        $Resp = $stmt_del->execute();
        $stmt_del->close();

        if (!$Resp) {
            throw new Exception("Error in deleting the record: " . $obj->con1->error);
        }
    } catch (Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data_del", time() + 3600, "/");
        header("location: stages.php");
    }
}


?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Stages</title>
</head>
<h4 class="fw-bold py-3 mb-4">Stage Master</h4>
    <!-- Basic Layout -->
    <div class="row">
      <div class="col-xl">
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add Stage</h5> 
          </div>
          <div class="card-body">
            <form method="post" enctype="multipart/form-data">   
              <input type="hidden" name="st_id" id="st_id">

               <div class="mb-3">
                  <label class="form-label" for="basic-default-fullname">Scheme</label>
                  <select name="scheme_id" id="scheme_id" class="form-control" required>
                   <option value="">Select Scheme</option> 
            <?php 
            while(
            	$scheme_list=mysqli_fetch_array($scheme_result)
            )
            { 
            	?>
                <option value="<?php echo $scheme_list["sid"] ?>"><?php echo $scheme_list["scheme_name"]?></option>
            <?php
             } 
             ?>
                  </select>
                </div> 

                <div class="mb-3">
                  <label class="form-label" for="basic-default-fullname">Stage Name</label>
                  <input type="text" class="form-control" name="stage_name" id="stage_name" required />
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
<!-- Basic Bootstrap Table -->
    <!-- Delete Modal -->
<div class="modal fade" id="backDropModal" data-bs-backdrop="static" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post">
      <div class="modal-header">
        <h5 class="modal-title" id="backDropModalTitle">Delete Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col mb-3">
            <label for="nameBackdrop" class="form-label" id="label_del"></label>
            <input type="hidden" name="stage_id" id="stage_id">
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

    <div class="card">
      <h5 class="card-header">Stage Data</h5>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>Scheme Name</th>
              <th>Stage Name</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php 
             // Scheme list
              $stmt_stages = $obj->con1->prepare("SELECT pr_stages.st_id, pr_stages.scheme_id, pr_stages.stage_name, pr_stages.status, pr_scheme.scheme_name FROM `pr_stages`, `pr_scheme` WHERE pr_scheme.sid = pr_stages.scheme_id ORDER BY pr_stages.st_id DESC");
              $stmt_stages->execute();
              $stages_result = $stmt_stages->get_result();
              $stmt_stages->close();
              $i=1;
              while($data=mysqli_fetch_array($stages_result))
              {
            ?>
            <tr>
              <td><?php echo $i?></td>
              <td><?php echo $data["scheme_name"] ?></td>
              <td><?php echo $data["stage_name"] ?></td>
              <td><?php echo $data["status"] ?></td>
              <td>
                <a href="javascript:editdata('<?php echo $data["st_id"]?>','<?php echo $data["scheme_id"]?>','<?php echo $data["stage_name"]?>','<?php echo $data["status"]?>');"><i class="bx bx-edit-alt me-1"></i> </a>
                <a href="javascript:deletedata('<?php echo $data["st_id"]?>');"><i class="bx bx-trash me-1"></i> </a>
                <a href="javascript:viewdata('<?php echo $data["st_id"]?>','<?php echo $data["scheme_id"]?>','<?php echo $data["stage_name"]?>','<?php echo $data["status"]?>');">View</a> 
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
</html>
<script type="text/javascript">
function editdata(st_id, scheme_id, stage_name, status) {

      $('#scheme_id').focus();
      $('#st_id').val(st_id);
      $('#scheme_id').val(scheme_id);
      $('#stage_name').val(stage_name);
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

  function viewdata(st_id, scheme_id, stage_name, status) {
  
      $('#st_id').val(st_id);
      $('#scheme_id').val(scheme_id);
      $('#stage_name').val(stage_name);
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

 function deletedata(st_id) {
    $('#backDropModal').modal('toggle');
    $('#stage_id').val(st_id);
    $('#label_del').html('Are you sure you want to DELETE record ?');
  }  
</script>
<?php
include ("footer.php");
?>