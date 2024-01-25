<?php
  include("header.php");

$cid = $_COOKIE['cid'];

$stmt_comp = $obj->con1->prepare("select raw_data from tbl_tdrawdata where id=?");
$stmt_comp->bind_param("i",$cid);
$stmt_comp->execute();
$comp_result = $stmt_comp->get_result();
$stmt_comp->close();
$comp = mysqli_fetch_array($comp_result);
$row_data=json_decode($comp["raw_data"]);
$post_fields=$row_data->post_fields;

// insert data
if(isset($_REQUEST['btnsubmit']))
{
  $constitution = $_REQUEST['constitution'];
  $contact_no = $_REQUEST['contact_no'];
  $contact_person = $_REQUEST['contact_person'];
  $segment = $_REQUEST['segment'];
  $status = $_REQUEST['status'];
  $remark = $_REQUEST['remark'];
  
  $stmt_slist = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
  $stmt_slist->bind_param("i",$cid);
  $stmt_slist->execute();
  $res = $stmt_slist->get_result();
  $stmt_slist->close();

  $data=mysqli_fetch_array($res);
  $row_data=json_decode($data["raw_data"]);
  $post_fields=$row_data->post_fields;

  $post_fields->Contact_Name = $contact_person;
  $post_fields->Mobile_No = $contact_no;
  $post_fields->Segment = $segment;
  $post_fields->Remarks = $remark;
  $row_data->Constitution = $constitution;
  $row_data->Status = $status;

  // if(isset($row_data->plot_details)){
    
  // }
    

  $json_object = json_encode($row_data);

  try
  {
  	$stmt = $obj->con1->prepare("update tbl_tdrawdata set raw_data=? where id=?");
  	$stmt->bind_param("si",$json_object,$cid);
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
    header("location:company_details.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:company_details.php");
  }
}

if(isset($_REQUEST['btnupdate']))
{
  $constitution = $_REQUEST['constitution'];
  $contact_no = $_REQUEST['contact_no'];
  $contact_person = $_REQUEST['contact_person'];
  $segment = $_REQUEST['segment'];
  $status = $_REQUEST['status'];
  $remark = $_REQUEST['remark'];
  $id=$_REQUEST['ttId'];

  $stmt_slist = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
  $stmt_slist->bind_param("i",$cid);
  $stmt_slist->execute();
  $res = $stmt_slist->get_result();
  $stmt_slist->close();

  $data=mysqli_fetch_array($res);
  $row_data=json_decode($data["raw_data"]);
  $post_fields=$row_data->post_fields;

  $post_fields->Contact_Name = $contact_person;
  $post_fields->Mobile_No = $contact_no;
  $post_fields->Segment = $segment;
  $post_fields->Remarks = $remark;
  $row_data->Constitution = $constitution;
  $row_data->Status = $status;

  $json_object = json_encode($row_data);

  try
  {
    $stmt = $obj->con1->prepare("update tbl_tdrawdata set raw_data=? where id=?");
    $stmt->bind_param("si",$json_object,$id);
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
      header("location:company_details.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
      header("location:company_details.php");
  }
}
?>

<h4 class="fw-bold py-3 mb-4"><?php echo $post_fields->Firm_Name ?>'s Details</h4>

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
                      <h5 class="mb-0">Add Details</h5>
                      
                    </div>
                    <div class="card-body">
                      <form method="post" enctype="multipart/form-data">
                       
                        <input type="hidden" name="ttId" id="ttId">
                          
                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Constitution</label>
                            <input type="text" class="form-control" name="constitution" id="constitution" required />
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Contact Person</label>
                            <input type="text" class="form-control" name="contact_person" id="contact_person" required />
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Contact Number</label>
                            <input type="text" class="form-control" name="contact_no" id="contact_no" required />
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Segment</label>
                            <input type="text" class="form-control" name="segment" id="segment" required />
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Status</label>
                            <div class="form-check form-check-inline mt-3">
                              <input class="form-check-input" type="radio" name="status" id="existing_company" value="Existing Company" required checked>
                              <label class="form-check-label" for="inlineRadio1">Existing Company</label>
                            </div>
                            <div class="form-check form-check-inline mt-3">
                              <input class="form-check-input" type="radio" name="status" id="negative" value="Negative" required>
                              <label class="form-check-label" for="inlineRadio1">Negative</label>
                            </div>
                            <div class="form-check form-check-inline mt-3">
                              <input class="form-check-input" type="radio" name="status" id="positive" value="Positive" required>
                              <label class="form-check-label" for="inlineRadio1">Positive</label>
                            </div>
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Remark</label>
                            <input type="text" class="form-control" name="remark" id="remark" required />
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
                <h5 class="card-header">Records</h5>
                <div class="table-responsive text-nowrap">
                  <table class="table table-hover" id="table_id">

                    <thead>
                      <tr>
                        <th>Srno</th>
                        <th>Firm Name</th>
                        <th>GST No.</th>
                        <th>Constitution</th>
                        <th>Contact Person</th>
                        <th>Contact Number</th>
                        <th>Segment</th>
                        <th>Status</th>
                        <th>Remark</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      <?php 
                        $stmt_list = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
                        $stmt_list->bind_param("i",$cid);
                        $stmt_list->execute();
                        $result = $stmt_list->get_result();
                        
                        $stmt_list->close();
                        $i=1;
                        while($data=mysqli_fetch_array($result))
                        {
                          $row_data=json_decode($data["raw_data"]);
                          $post_fields=$row_data->post_fields;
                          if(isset($row_data->Constitution)){
                      ?>
                      <tr>
                        <td><?php echo $i?></td>
                        <td><?php echo $post_fields->Firm_Name ?></td>
                        <td><?php echo $post_fields->GST_No ?></td>
                        <td><?php echo $row_data->Constitution ?></td>
                        <td><?php echo $post_fields->Contact_Name ?></td>
                        <td><?php echo $post_fields->Mobile_No ?></td>
                        <td><?php echo $post_fields->Segment ?></td>
                        <td><?php echo $row_data->Status ?></td>
                        <td><?php echo $post_fields->Remarks ?></td>
                        <td>
                        	<a href="javascript:editdata('<?php echo $data["id"]?>','<?php echo base64_encode($row_data->Constitution)?>','<?php echo base64_encode($post_fields->Contact_Name)?>','<?php echo base64_encode($post_fields->Mobile_No)?>','<?php echo base64_encode($post_fields->Segment)?>','<?php echo $row_data->Status?>','<?php echo base64_encode($post_fields->Remarks)?>');"><i class="bx bx-edit-alt me-1"></i> </a>
                          <a href="javascript:viewdata('<?php echo $data["id"]?>','<?php echo base64_encode($row_data->Constitution)?>','<?php echo base64_encode($post_fields->Contact_Name)?>','<?php echo base64_encode($post_fields->Mobile_No)?>','<?php echo base64_encode($post_fields->Segment)?>','<?php echo $row_data->Status?>','<?php echo base64_encode($post_fields->Remarks)?>');">View</a>
                        </td>
                      </tr>
                      <?php
                          $i++;
                        }
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
/*  function deletedata(id) {
      if(confirm("Are you sure to DELETE data?")) {
          var loc = "company_plot.php?flg=del&n_id=" + id;
          window.location = loc;
      }
  }*/
  function editdata(id,constitution,contact_person,contact_no,segment,status,remark) {
    $('#constitution').focus();   
   	$('#ttId').val(id);
		$('#constitution').val(atob(constitution));
    $('#contact_person').val(atob(contact_person));
    $('#contact_no').val(atob(contact_no));
    $('#segment').val(atob(segment));
    $('#remark').val(atob(remark));

    if(status=="Existing Company"){
     $('#existing_company').attr("checked","checked"); 
    }
    else if(status=="Positive"){
     $('#positive').attr("checked","checked"); 
    }
    else if(status=="Negative"){
     $('#negative').attr("checked","checked"); 
    }

		$('#btnsubmit').attr('hidden',true);
    $('#btnupdate').removeAttr('hidden');
		$('#btnsubmit').attr('disabled',true);
  }
  function viewdata(id,constitution,contact_person,contact_no,segment,status,remark) {
    $('#constitution').focus();   
    $('#ttId').val(id);
    $('#constitution').val(atob(constitution));
    $('#contact_person').val(atob(contact_person));
    $('#contact_no').val(atob(contact_no));
    $('#segment').val(atob(segment));
    $('#remark').val(atob(remark));

    if(status=="Existing Company"){
     $('#existing_company').attr("checked","checked"); 
    }
    else if(status=="Positive"){
     $('#positive').attr("checked","checked"); 
    }
    else if(status=="Negative"){
     $('#negative').attr("checked","checked"); 
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