<?php
  include("header.php");

$user_id = $_SESSION["id"];

$stmt_state_list = $obj->con1->prepare("select DISTINCT(state_id) from tbl_industrial_estate");
$stmt_state_list->execute();
$state_result = $stmt_state_list->get_result();
$stmt_state_list->close();

$stmt_admin = $obj->con1->prepare("SELECT * FROM `tbl_users` WHERE role='superadmin'");
$stmt_admin->execute();
$admin_result = $stmt_admin->get_result();
$stmt_admin->close();
$admin = array();
while($row = mysqli_fetch_array($admin_result)){
  $admin[] = $row['id'];
}

$stmt_emp = $obj->con1->prepare("SELECT * FROM `assign_estate` WHERE employee_id=? and start_dt<=curdate() and end_dt>=curdate()");
$stmt_emp->bind_param("i",$user_id);
$stmt_emp->execute();
$emp_result = $stmt_emp->get_result();
$stmt_emp->close();

// insert data
if(isset($_REQUEST['btnsubmit']))
{
  $firm_name = $_REQUEST['firm_name'];
  $gst_no = $_REQUEST['gst_no'];
  $img = $_FILES['img']['name'];
  $img_path = $_FILES['img']['tmp_name'];
  $state = $_REQUEST['state'];
  $city = $_REQUEST['city'];
  $taluka = $_REQUEST['taluka'];
  $area = $_REQUEST['area'];
  $industrial_estate = $_REQUEST['industrial_estate'];
  
  //rename file for id proof
  if ($_FILES["img"]["name"] != "")
  {
    if(file_exists("gst_image/" . $img)) {
      $i = 0;
      $PicFileName = $_FILES["img"]["name"];
      $Arr1 = explode('.', $PicFileName);

      $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
      while (file_exists("gst_image/" . $PicFileName)) {
          $i++;
          $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
      }
   } 
   else {
      $PicFileName = $_FILES["img"]["name"];
    }
  }

  $cp = Array (
      "post_fields" => Array (
          "source" => "Open",
          "Source_Name" => "Direct Contact by us",
          "Contact_Name" => "",
          "Mobile_No" => "",
          "Email" => "",
          "Designation_In_Firm" => "Owner",
          "Firm_Name" => $firm_name,
          "GST_No" => $gst_no,
          "Type_of_Company" => "",
          "Category" => "Micro",
          "Segment" => "",
          "Premise" => "",
          "Factory_Address" => "",
          "state" => $state,
          "city" => $city,
          "Taluka" => $taluka,
          "Area" => $area,
          "IndustrialEstate" => $industrial_estate,
          "loan_applied" => "",
          "new_loan_when" => "",
          "new_loan_from_whom" => "",
          "Under_Process_Bank" => "",
          "Under_Process_Branch" => "",
          "Under_Process_Date" => "",
          "ROI" => "",
          "Colletral" => "",
          "Consultant_Details_Name" => "",
          "Consultant_Details_Number" => "",
          "Sanctioned_Bank" => "",
          "Bank_Branch" => "",
          "DOS" => "",
          "TL_Amount" => "",
          "saction_Consultant_Details_Name" => "",
          "saction_Consultant_Details_Number" => "",
          "category_type" => "",
          "Remarks" => ""
        ),
        "inq_submit" => "Submit",
        "Image" => $PicFileName,
        "Constitution" => "",
        "Status" => ""
      );
   
  // Encode array to json
  $json = json_encode($cp);
   
  // Display it
  echo "$json";

  try
  {
    $stmt = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
    $stmt->bind_param("ss",$json,$user_id);
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
    move_uploaded_file($img_path,"gst_image/".$PicFileName);
	  setcookie("msg", "data",time()+3600,"/");
    header("location:company_plot.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:company_plot.php");
  }
}

if(isset($_REQUEST['btnupdate']))
{
  $firm_name = $_REQUEST['firm_name'];
  $gst_no = $_REQUEST['gst_no'];
  $img = $_FILES['img']['name'];
  $img_path = $_FILES['img']['tmp_name'];
  $himage = $_REQUEST['himage'];
  $state = $_REQUEST['state'];
  $city = $_REQUEST['city'];
  $taluka = $_REQUEST['taluka'];
  $area = $_REQUEST['area'];
  $industrial_estate = $_REQUEST['industrial_estate'];
  $id=$_REQUEST['ttId'];

  //rename file for image
  if ($_FILES["img"]["name"] != "")
  {
    if(file_exists("gst_image/" . $img)) {
      $i = 0;
      $PicFileName = $_FILES["img"]["name"];
      $Arr1 = explode('.', $PicFileName);

      $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
      while (file_exists("gst_image/" . $PicFileName)) {
        $i++;
        $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
      }
    } 
    else {
      $PicFileName = $_FILES["img"]["name"];
    }
    unlink("gst_image/".$himage);  
    move_uploaded_file($img_path,"gst_image/".$PicFileName);  
  }
  else
  {
    $PicFileName=$himage;
  }
  
  $stmt_slist = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
  $stmt_slist->bind_param("i",$id);
  $stmt_slist->execute();
  $res = $stmt_slist->get_result();
  $stmt_slist->close();

  $data=mysqli_fetch_array($res);
  $row_data=json_decode($data["raw_data"]);
  $post_fields=$row_data->post_fields;

  $post_fields->Firm_Name = $firm_name;
  $post_fields->GST_No = $gst_no;
  $post_fields->city = $city;
  $post_fields->Area = $area;
  $post_fields->state = $state;
  $post_fields->Taluka = $taluka;
  $post_fields->IndustrialEstate = $industrial_estate;
  $row_data->Image = $PicFileName;

  $json_object = json_encode($row_data);

  try
  {
    $stmt = $obj->con1->prepare("update tbl_tdrawdata set raw_data=? where id=?");
    $stmt->bind_param("si", $json_object,$id);
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
    header("location:company_plot.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:company_plot.php");
  }
}

// delete data
if(isset($_REQUEST["flg"]) && $_REQUEST["flg"]=="del")
{
  $img = $_REQUEST['img'];
  try
  {
    $stmt_del = $obj->con1->prepare("delete from tbl_tdrawdata where id='".$_REQUEST["n_id"]."'");
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
    unlink("gst_image/".$img); 
  	setcookie("msg", "data_del",time()+3600,"/");
    header("location:company_plot.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:company_plot.php");
  }
}

?>

<h4 class="fw-bold py-3 mb-4">Company Plots Master</h4>

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
                      <h5 class="mb-0">Add Company</h5>
                      
                    </div>
                    <div class="card-body">
                      <form method="post" enctype="multipart/form-data">                       
                        <input type="hidden" name="ttId" id="ttId">
                  <?php
                    if(in_array($user_id, $admin)){
                  ?>
                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">State</label>
                            <select name="state" id="state" onchange="cityList(this.value)" class="form-control" required>
                              <option value="">Select State</option>
                      <?php    
                          while($state_list=mysqli_fetch_array($state_result)){
                      ?>
                          <option value="<?php echo $state_list["state_id"] ?>"><?php echo $state_list["state_id"] ?></option>
                      <?php
                        }
                      ?>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-company">City</label>
                            <select name="city" id="city" onchange="talukaList(this.value,state.value)" class="form-control" required>
                              <option value="">Select City</option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-company">Taluka</label>
                            <select name="taluka" id="taluka" onchange="areaList(this.value,city.value,state.value)" class="form-control" required>
                              <option value="">Select Taluka</option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-company">Area</label>
                            <select name="area" id="area" class="form-control" onchange="indEstateList(this.value,taluka.value,city.value,state.value)" required>
                              <option value="">Select Area</option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-company">Industrial Estate</label>
                            <select name="industrial_estate" id="industrial_estate" class="form-control" required>
                              <option value="">Select Industrial Estate</option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Firm Name</label>
                            <input type="text" class="form-control" name="firm_name" id="firm_name" required />
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">GST No.</label>
                            <input type="text" class="form-control" name="gst_no" id="gst_no" onkeyup ="checkGST(this.value)" required />
                            <div id="gst_alert_div" class="text-danger"></div>
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Image</label>
                            <input type="file" class="form-control" onchange="readURL(this,'PreviewImage')" name="img" id="img" required />
                            <img src="" name="PreviewImage" id="PreviewImage" width="100" height="100" style="display:none;">
                            <div id="imgdiv" style="color:red"></div>
                            <input type="hidden" name="himage" id="himage" />
                          </div>

                        <button type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary">Save</button>
                    
                        <button type="submit" name="btnupdate" id="btnupdate" class="btn btn-primary " hidden>Update</button>
                    
                        <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button>

                  <?php
                    } else {
                      if(mysqli_num_rows($emp_result)>0){
                  ?>
                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Industrial Estate</label>
                            <select name="industrial_estate" id="industrial_estate" onchange="getEstateDetail(this.value,<?php echo $user_id ?>)" class="form-control" required>

                              <option value="">Select Industrial Estate</option>
                      <?php
                        while($emp = mysqli_fetch_array($emp_result)){
                      ?>
                              <option value="<?php echo $emp['industrial_estate'] ?>"><?php echo $emp['industrial_estate'] ?></option>   
                      <?php } ?>
                            </select>
                          </div>

                          <div id="estateDetail"></div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Firm Name</label>
                            <input type="text" class="form-control" name="firm_name" id="firm_name" required />
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">GST No.</label>
                            <input type="text" class="form-control" name="gst_no" id="gst_no" onkeyup ="checkGST(this.value)" required />
                            <div id="gst_alert_div" class="text-danger"></div>
                          </div>

                          <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">Image</label>
                            <input type="file" class="form-control" onchange="readURL(this,'PreviewImage')" name="img" id="img" required />
                            <img src="" name="PreviewImage" id="PreviewImage" width="100" height="100" style="display:none;">
                            <div id="imgdiv" style="color:red"></div>
                            <input type="hidden" name="himage" id="himage" />
                          </div>

                        <button type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary">Save</button>
                    
                        <button type="submit" name="btnupdate" id="btnupdate" class="btn btn-primary " hidden>Update</button>
                    
                        <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button>

                  <?php
                      }
                      else{
                  ?>
                      <label class="form-label" for="basic-default-fullname" style="color:red;">No Estate Assigned</label>
                  <?php
                      }
                    }
                  ?>
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
                        <th>Area</th>
                        <th>City</th>
                        <th>Actions</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      <?php 

                        if(in_array($user_id, $admin)){
                          $stmt_list = $obj->con1->prepare("select * from tbl_tdrawdata order by id desc");
                        }
                        else{
                          $stmt_list = $obj->con1->prepare("select r1.* from tbl_tdrawdata r1, assign_estate a1 where r1.userid=? and r1.userid=a1.employee_id and start_dt<=raw_data_ts and end_dt>=raw_data_ts order by r1.id desc"); 
                          $stmt_list->bind_param("i",$user_id);
                        }
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
                        <td><?php echo $post_fields->GST_No ?></td>
                        <td><?php echo $post_fields->Area ?></td>
                        <td><?php echo $post_fields->city ?></td>
                        <td>
                      <?php
                        if(in_array($user_id, $admin)){
                      ?>
                          <a href="javascript:editdata_admin('<?php echo $data["id"]?>','<?php echo base64_encode($post_fields->Firm_Name)?>','<?php echo base64_encode($post_fields->GST_No)?>','<?php if(isset($row_data->Image)){ echo base64_encode($row_data->Image); } ?>','<?php echo $post_fields->state?>','<?php echo $post_fields->city?>','<?php echo $post_fields->Taluka?>','<?php echo $post_fields->Area?>','<?php echo $post_fields->IndustrialEstate?>');"><i class="bx bx-edit-alt me-1"></i> </a>
                          <a href="javascript:deletedata('<?php echo $data["id"]?>','<?php if(isset($row_data->Image)){ echo base64_encode($row_data->Image); } ?>');"><i class="bx bx-trash me-1"></i> </a>
                          <a href="javascript:viewdata_admin('<?php echo $data["id"]?>','<?php echo base64_encode($post_fields->Firm_Name)?>','<?php echo base64_encode($post_fields->GST_No)?>','<?php if(isset($row_data->Image)){ echo base64_encode($row_data->Image); } ?>','<?php echo $post_fields->state?>','<?php echo $post_fields->city?>','<?php echo $post_fields->Taluka?>','<?php echo $post_fields->Area?>','<?php echo $post_fields->IndustrialEstate?>');">View</a> 
                      <?php
                        } else {
                      ?>
                          <a href="javascript:editdata('<?php echo $data["id"]?>','<?php echo base64_encode($post_fields->Firm_Name)?>','<?php echo base64_encode($post_fields->GST_No)?>','<?php if(isset($row_data->Image)){ echo base64_encode($row_data->Image); } ?>','<?php echo $post_fields->state?>','<?php echo $post_fields->city?>','<?php echo $post_fields->Taluka?>','<?php echo $post_fields->Area?>','<?php echo $post_fields->IndustrialEstate?>','<?php echo $user_id?>');"><i class="bx bx-edit-alt me-1"></i> </a>
                          <a href="javascript:deletedata('<?php echo $data["id"]?>','<?php if(isset($row_data->Image)){ echo base64_encode($row_data->Image); } ?>');"><i class="bx bx-trash me-1"></i> </a>
                          <a href="javascript:viewdata('<?php echo $data["id"]?>','<?php echo base64_encode($post_fields->Firm_Name)?>','<?php echo base64_encode($post_fields->GST_No)?>','<?php if(isset($row_data->Image)){ echo base64_encode($row_data->Image); } ?>','<?php echo $post_fields->state?>','<?php echo $post_fields->city?>','<?php echo $post_fields->Taluka?>','<?php echo $post_fields->Area?>','<?php echo $post_fields->IndustrialEstate?>','<?php echo $user_id?>');">View</a> 
                      <?php
                        }
                      ?>
                        </td>
                        <td>
                          <a href="javascript:addDetails('<?php echo $data["id"]?>');" style="color:blue">Add Details</a>&nbsp;&nbsp;
                          <a href="javascript:addPlotDetails('<?php echo $data["id"]?>');" style="color:green">Add Plot Details</a>
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

  function addDetails(id) {
      document.cookie = "cid="+id;
      var loc = "company_details.php";
      window.location = loc;
  }

  function addPlotDetails(id) {
      document.cookie = "cid="+id;
      var loc = "plot_details.php";
      window.location = loc;
  }

  function getEstateDetail(ind_estate,emp_id){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=getEstateDetail",
      data: "ind_estate="+ind_estate+"&emp_id="+emp_id,
      cache: false,
      success: function(result){
        $('#estateDetail').html('');
        $('#estateDetail').append(result);
      }
    });
  }

  function checkGST(gst_no)
  {
    var id=$('#ttId').val();
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=checkGST",
      data: "gst_no="+gst_no+"&id="+id,
      cache: false,
      success: function(result){
        if(result>0)
        {
          $('#gst_alert_div').html('GST No. already exist!');
          document.getElementById('btnsubmit').disabled = true;
          document.getElementById('btnupdate').disabled = true;
        }
        else
        {
          $('#gst_alert_div').html('');
          document.getElementById('btnsubmit').disabled = false;
          document.getElementById('btnupdate').disabled = false;
        }
      }
    });
  }

  function cityList(state){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=cityList",
      data: "state_name="+state,
      cache: false,
      success: function(result){
        $('#city').html('');
        $('#city').append(result);
      }
    });
  }

  function talukaList(city,state){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=talukaList",
      data: "city="+city+"&state_name="+state,
      cache: false,
      success: function(result){
        $('#taluka').html('');
        $('#taluka').append(result);
      }
    });
  }

  function areaList(taluka,city,state){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=areaList",
      data: "taluka="+taluka+"&city="+city+"&state_name="+state,
      cache: false,
      success: function(result){
        $('#area').html('');
        $('#area').append(result);
      }
    });
  }

  function indEstateList(area,taluka,city,state){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=indEstateList",
      data: "area="+area+"&taluka="+taluka+"&city="+city+"&state_name="+state,
      cache: false,
      success: function(result){
        $('#industrial_estate').html('');
        $('#industrial_estate').append(result);
      }
    });
  }

  function readURL(input,preview) {
    if (input.files && input.files[0]) {
      var filename=input.files.item(0).name;

      var reader = new FileReader();
      var extn=filename.split(".");

       if(extn[1].toLowerCase()=="jpg" || extn[1].toLowerCase()=="jpeg" || extn[1].toLowerCase()=="png" || extn[1].toLowerCase()=="bmp") {
        reader.onload = function (e) {
            $('#'+preview).attr('src', e.target.result);
              document.getElementById(preview).style.display = "block";
        };

        reader.readAsDataURL(input.files[0]);
        $('#imgdiv').html("");
        document.getElementById('btnsubmit').disabled = false;
      }
      else
      {
          $('#imgdiv').html("Please Select Image Only");
          document.getElementById('btnsubmit').disabled = true;
      }
    }
  }

  function deletedata(id,img) {

      if(confirm("Are you sure to DELETE data?")) {
          var loc = "company_plot.php?flg=del&n_id="+id+"&img="+atob(img);
          window.location = loc;
      }
  }
  function editdata(id,firm_name,gst_no,img,state,city,taluka,area,ind_estate,emp_id) {
      $('#firm_name').focus();   
     	$('#ttId').val(id);
			$('#firm_name').val(atob(firm_name));
      $('#gst_no').val(atob(gst_no));
      $('#himage').val(atob(img));
      $('#PreviewImage').show();
      $('#PreviewImage').attr('src','gst_image/'+atob(img));
      $('#img').removeAttr('required');
      $('#industrial_estate').val(ind_estate);
      getEstateDetail(ind_estate,emp_id);
    
			$('#btnsubmit').attr('hidden',true);
      $('#btnupdate').removeAttr('hidden');
			$('#btnsubmit').attr('disabled',true);
  }
  function viewdata(id,firm_name,gst_no,img,state,city,taluka,area,ind_estate,emp_id) {
      $('#firm_name').focus();   
      $('#ttId').val(id);
      $('#firm_name').val(atob(firm_name));
      $('#gst_no').val(atob(gst_no));
      $('#himage').val(atob(img));
      $('#PreviewImage').show();
      $('#PreviewImage').attr('src','gst_image/'+atob(img));
      $('#img').removeAttr('required');
      $('#industrial_estate').val(ind_estate);
      getEstateDetail(ind_estate,emp_id);

			$('#btnsubmit').attr('hidden',true);
      $('#btnupdate').attr('hidden',true);
			$('#btnsubmit').attr('disabled',true);
			$('#btnupdate').attr('disabled',true);
  }
  function editdata_admin(id,firm_name,gst_no,img,state,city,taluka,area,ind_estate) {
      $('#state').focus();   
      $('#ttId').val(id);
      $('#firm_name').val(atob(firm_name));
      $('#gst_no').val(atob(gst_no));
      $('#himage').val(atob(img));
      $('#PreviewImage').show();
      $('#PreviewImage').attr('src','gst_image/'+atob(img));
      $('#img').removeAttr('required');
      
      $('#state').val(state);
      cityList(state);
      talukaList(city,state);
      areaList(taluka,city,state);
      indEstateList(area,taluka,city,state);

      setTimeout(function() {
          $('#city').val(city);
          $('#area').val(area);
          $('#taluka').val(taluka);
          $('#industrial_estate').val(ind_estate);
      }, 500);  
    
      $('#btnsubmit').attr('hidden',true);
      $('#btnupdate').removeAttr('hidden');
      $('#btnsubmit').attr('disabled',true);
  }
  function viewdata_admin(id,firm_name,gst_no,img,state,city,taluka,area,ind_estate) {
      $('#firm_name').focus();   
      $('#ttId').val(id);
      $('#firm_name').val(atob(firm_name));
      $('#gst_no').val(atob(gst_no));
      $('#himage').val(atob(img));
      $('#PreviewImage').show();
      $('#PreviewImage').attr('src','gst_image/'+atob(img));
      $('#img').removeAttr('required');
      
      $('#state').val(state);
      cityList(state);
      talukaList(city,state);
      areaList(taluka,city,state);
      indEstateList(area,taluka,city,state);

      setTimeout(function() {
          $('#city').val(city);
          $('#area').val(area);
          $('#taluka').val(taluka);
          $('#industrial_estate').val(ind_estate);
      }, 500);  

      $('#btnsubmit').attr('hidden',true);
      $('#btnupdate').attr('hidden',true);
      $('#btnsubmit').attr('disabled',true);
      $('#btnupdate').attr('disabled',true);
  }
</script>
<?php 
  include("footer.php");
?>