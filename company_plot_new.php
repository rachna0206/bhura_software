<?php
  include("header.php");

$user_id = $_SESSION["id"];
$industrial_estate_id = $_COOKIE["estate_id"];

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

$stmt_estate = $obj->con1->prepare("SELECT * FROM `tbl_industrial_estate` WHERE id=?");
$stmt_estate->bind_param("i",$industrial_estate_id);
$stmt_estate->execute();
$estate_result = $stmt_estate->get_result()->fetch_assoc();
$stmt_estate->close();
$industrial_estate = $estate_result["industrial_estate"];
$area = $estate_result["area_id"];

$stmt_plot = $obj->con1->prepare("SELECT * FROM `pr_add_industrialestate_details` WHERE industrial_estate_id=?");
$stmt_plot->bind_param("i",$industrial_estate_id);
$stmt_plot->execute();
$plot_result = $stmt_plot->get_result()->fetch_assoc();
$stmt_plot->close();
$plotting_pattern = $plot_result["plotting_pattern"];
//$plotting_pattern = mysqli_fetch_array($plot_result);

// for company and plot modal
if(isset($_REQUEST['btn_modal_update']))
{
  $firm_name = $_REQUEST['firm_name'];
  $gst_no = $_REQUEST['gst_no'];
  $img = $_FILES['img']['name'];
  $img_path = $_FILES['img']['tmp_name'];
  $old_img = $_REQUEST['himage'];
  $constitution = $_REQUEST['constitution'];
  $contact_no = $_REQUEST['contact_no'];
  $contact_person = $_REQUEST['contact_person'];
  $category = $_REQUEST['category'];
  $segment = $_REQUEST['segment'];
  $status = $_REQUEST['status'];
  $remark = $_REQUEST['remark'];
  $plot_status = $_REQUEST['plot_status'];
  $id = $_REQUEST['ttId'];
  $plot_index = $_REQUEST['plot_index'];
  //$plot_id = $_REQUEST['plot_index'];

  if($img!=""){
    unlink("gst_image/".$old_img);  
    //rename file for gst image
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
    move_uploaded_file($img_path,"gst_image/".$PicFileName);
  }
  else{
    $PicFileName=$old_img;
  }
  
  $stmt_slist = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
  $stmt_slist->bind_param("i",$id);
  $stmt_slist->execute();
  $res = $stmt_slist->get_result();
  $stmt_slist->close();

  $data=mysqli_fetch_array($res);
  $row_data=json_decode($data["raw_data"]);
  $post_fields=$row_data->post_fields;
  $plot_details=$row_data->plot_details;

  $post_fields->Firm_Name = $firm_name;
  $post_fields->GST_No = $gst_no;
  $row_data->Image = $PicFileName;
  $row_data->Constitution = $constitution;
  $post_fields->Contact_Name = $contact_person;
  $post_fields->Mobile_No = $contact_no;
  $post_fields->Category = $category;
  $post_fields->Segment = $segment;
  $row_data->Status = $status;
  $post_fields->Remarks = $remark;
  $plot_details["$plot_index"]->Plot_Status = $plot_status;

  $json_object = json_encode($row_data);

  try
  {
    $stmt = $obj->con1->prepare("update tbl_tdrawdata set raw_data=?, userid=? where id=?");
    $stmt->bind_param("sii",$json_object,$user_id,$id);
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
    header("location:company_plot_new.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:company_plot_new.php");
  }
}

// for floor modal
if(isset($_REQUEST['btn_modal_insert_floor']))
{
  $floor_confirmation = $_REQUEST['floor_confirmation'];
  $plot_no = $_REQUEST['plot_no'];
  $road_no = $_REQUEST['road_no'];
  $floor = $_REQUEST['floor'];
  $plot_status = $_REQUEST['plot_status'];
  $id=$_REQUEST['floormodal_ttId'];
  $arr_cookie = array();
 
  try
  {
    if($floor_confirmation=='same_as_ground'){  // Same Company As Ground
      $stmt_slist = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
      $stmt_slist->bind_param("i",$id);
      $stmt_slist->execute();
      $res = $stmt_slist->get_result();
      $stmt_slist->close();

      $data=mysqli_fetch_array($res);
      $row_data=json_decode($data["raw_data"]);
      $plot_details = $row_data->plot_details;
      $arr_count = count($plot_details);
      $last_plot_id = $plot_details[$arr_count-1]->Plot_Id;

      $new_plot_detail=Array(
        "Plot_No" => $plot_no,
        "Floor" => $floor,
        "Road_No" => $road_no,
        "Plot_Status" => $plot_status,
        "Plot_Id" => $last_plot_id+1,
      );  
      array_push($row_data->plot_details, $new_plot_detail);

      $json_object = json_encode($row_data);

      //echo $json_object;

      $stmt = $obj->con1->prepare("update tbl_tdrawdata set raw_data=? where id=?");
      $stmt->bind_param("si", $json_object,$id);
      $Resp=$stmt->execute();
    }
    else if($floor_confirmation=='same_owner'){   // Same Owner As Ground But Different Company
      $stmt_slist = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
      $stmt_slist->bind_param("i",$id);
      $stmt_slist->execute();
      $res = $stmt_slist->get_result();
      $stmt_slist->close();

      $data=mysqli_fetch_array($res);
      $row_data=json_decode($data["raw_data"]);
      $post_fields = $row_data->post_fields;

      $cp = Array (
          "post_fields" => Array (
          "source" => "",
          "Source_Name" => "",
          "Contact_Name" => $post_fields->Contact_Name,
          "Mobile_No" => $post_fields->Mobile_No,
          "Email" => "",
          "Designation_In_Firm" => "",
          "Firm_Name" => "",
          "GST_No" => "",
          "Type_of_Company" => "",
          "Category" => "",
          "Segment" => "",
          "Premise" => "",
          "Factory_Address" => "",
          "state" => $estate_result["state_id"],
          "city" => $estate_result["city_id"],
          "Taluka" => $estate_result["taluka"],
          "Area" => $estate_result["area_id"],
          "IndustrialEstate" => $estate_result["industrial_estate"],
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
        "Image" => "",
        "Constitution" => "",
        "Status" => "",
        "plot_details" => Array(
          Array(
            "Plot_No" => $plot_no,
            "Floor" => $floor,
            "Road_No" => $road_no,
            "Plot_Status" => $plot_status,
            "Plot_Id" => "1",
          ),
        ) 
      );
       
      // Encode array to json
      $json = json_encode($cp);
       
      // Display it
      //echo "$json";

      $stmt = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
      $stmt->bind_param("ss",$json,$user_id);
      $Resp=$stmt->execute();
      $insert_id = mysqli_insert_id($obj->con1);
    }
    else if($floor_confirmation=='different_company'){  // Different Company and Different Owner than Ground
      $cp = Array (
          "post_fields" => Array (
          "source" => "",
          "Source_Name" => "",
          "Contact_Name" => "",
          "Mobile_No" => "",
          "Email" => "",
          "Designation_In_Firm" => "",
          "Firm_Name" => "",
          "GST_No" => "",
          "Type_of_Company" => "",
          "Category" => "",
          "Segment" => "",
          "Premise" => "",
          "Factory_Address" => "",
          "state" => $estate_result["state_id"],
          "city" => $estate_result["city_id"],
          "Taluka" => $estate_result["taluka"],
          "Area" => $estate_result["area_id"],
          "IndustrialEstate" => $estate_result["industrial_estate"],
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
        "Image" => "",
        "Constitution" => "",
        "Status" => "",
        "plot_details" => Array(
          Array(
            "Plot_No" => $plot_no,
            "Floor" => $floor,
            "Road_No" => $road_no,
            "Plot_Status" => $plot_status,
            "Plot_Id" => "1",
          ),
        ) 
      );
       
      // Encode array to json
      $json = json_encode($cp);
       
      // Display it
      //echo "$json";

      $stmt = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
      $stmt->bind_param("ss",$json,$user_id);
      $Resp=$stmt->execute();
      $insert_id = mysqli_insert_id($obj->con1);
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
    if($floor_confirmation=='same_owner' || $floor_confirmation=='different_company'){
      setcookie("id_foredit", $insert_id, time()+3600,"/");
      setcookie("plotid_foredit", '1', time()+3600,"/");
      setcookie("plotstatus_foredit", $plot_status, time()+3600,"/");
      setcookie("pattern_foredit", $plotting_pattern, time()+3600,"/");
    }

	  setcookie("msg", "update",time()+3600,"/");
    header("location:company_plot_new.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:company_plot_new.php");
  }
}

// for search
if(isset($_REQUEST['btnsearch']))
{
  $search_by = $_REQUEST['search'];
  $search_value = isset($_REQUEST['search_textbox'])?$_REQUEST['search_textbox']:"";

  if($search_value!=""){
    if($search_by=='gst_no_search'){
      $search_str=($search_value!="")?"and raw_data->'$.post_fields.GST_No' like '%".$search_value."%'":"";
    }
    else if($search_by=='plot_no_search'){
      $search_str=($search_value!="")?"and raw_data->'$.plot_details[*].Plot_No' like '%".$search_value."%'":"";    
    }

    $stmt_list = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Area') like '%".strtolower($area)."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($industrial_estate)."%' ".$search_str); 
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();
  }
}
?>

<h4 class="fw-bold py-3 mb-4">Company Plots Master (Estate - <?php echo $estate_result["industrial_estate"] ?>)</h4>

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
      <!--    <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"></h5>
          </div>  -->
          <div class="card-body">
            <form method="post">
            <div class="row">
              <div class="col mb-3">
                <label class="form-label" for="basic-default-fullname">State : <?php echo $estate_result["state_id"] ?></label>
              </div>
              <div class="col mb-3">
                <label class="form-label" for="basic-default-company">City : <?php echo $estate_result["city_id"] ?></label>
              </div>
            </div>

            <div class="row">
              <div class="col mb-3">
                <label class="form-label" for="basic-default-company">Taluka : <?php echo $estate_result["taluka"] ?></label>
              </div>
              <div class="col mb-3">
                <label class="form-label" for="basic-default-company">Area : <?php echo $estate_result["area_id"] ?></label>
              </div>
            </div>

            <div class="mb-3">
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="search" id="plot_no_search" value="plot_no_search" required checked <?php echo (isset($_REQUEST['search']) && $_REQUEST['search']=="plot_no_search")?"checked":""?>>
                <label class="form-check-label" for="inlineRadio1">Plot No.</label>
              </div>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="search" id="gst_no_search" value="gst_no_search" required <?php echo (isset($_REQUEST['search']) && $_REQUEST['search']=="gst_no_search")?"checked":""?>>
                <label class="form-check-label" for="inlineRadio1">GST No.</label>
              </div>
              <div class="form-check form-check-inline mt-3">
                <input type="text" class="form-control" name="search_textbox" id="search_textbox" value="<?php echo isset($_REQUEST['search_textbox'])?$_REQUEST['search_textbox']:""?>"/>
              </div>
            </div>

            <button type="submit" name="btnsearch" id="btnsearch" class="btn btn-primary">Search</button>
        
            <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location='company_plot_new.php'">Cancel</button>

            </form>
          </div>
        </div>
      </div>       
    </div>
   
    <!-- Company Plot Modal -->
    <div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalCenterTitle">Company's Plot Page</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="post" enctype="multipart/form-data">
            <div id="company_detail_modal"></div>
        </form>
        </div>
      </div>
    </div>

    <!-- /modal-->

    <!-- Floor Modal -->
    <div class="modal fade" id="floorModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalCenterTitle">Add Floor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div >
            <form method="post" enctype="multipart/form-data">
            <div class="modal-body" ><div class="row">
              <input type="hidden" name="floormodal_ttId" id="floormodal_ttId">
              <input type="hidden" name="plot_no" id="plot_no">
              <input type="hidden" name="road_no" id="road_no">
                
              <div class="mb-3">
                <label class="form-label" for="basic-default-fullname">Plot No. : </label>
                <label class="form-label" for="basic-default-fullname" id="plotno_floormodal"></label>
              </div>

          <?php if($plotting_pattern=='Road'){ ?>
              <div class="mb-3">
                <label class="form-label" for="basic-default-fullname">Road No. : </label>
                <label class="form-label" for="basic-default-fullname" id="roadno_floormodal"></label>
              </div>
          <?php } ?>

              <div class="mb-3">
                <label class="form-label" for="basic-default-fullname">Floor No.</label>
                <select name="floor" id="floor" class="form-control" required>
                  <option value="">Select Floor No.</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="basic-default-fullname">Status</label>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="plot_status" id="open_plot" value="Open Plot" required checked>
                  <label class="form-check-label" for="inlineRadio1">Open Plot</label>
                </div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="plot_status" id="under_construction" value="Under Construction" required>
                  <label class="form-check-label" for="inlineRadio1">Under Construction</label>
                </div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="plot_status" id="constructed" value="Constructed" required>
                  <label class="form-check-label" for="inlineRadio1">Constructed</label>
                </div>
              </div>
              <hr>
              <div class="mb-3">
                <label class="form-label" for="basic-default-fullname"></label>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="floor_confirmation" id="same_as_ground" value="same_as_ground" required checked>
                  <label class="form-check-label" for="inlineRadio1">Same Company</label>
                </div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="floor_confirmation" id="same_owner" value="same_owner" required>
                  <label class="form-check-label" for="inlineRadio1">Same Owner But Different Company</label>
                </div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="floor_confirmation" id="different_company" value="different_company" required>
                  <label class="form-check-label" for="inlineRadio1">Different Company</label>
                </div>
              </div>
            </div></div>
            <div class="modal-footer">
              <input type="submit" class="btn btn-primary" name="btn_modal_insert_floor" id="btn_modal_insert_floor" value="Save Changes">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- /modal-->

    <!-- grid -->

    <!-- Basic Bootstrap Table -->
    <div class="card">
      <h5 class="card-header">Records</h5>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="table_id">
          <thead>
            <tr>
              <th hidden>Sr. No.</th>
              <th>Actions</th>
              <th>Plot No.</th>
              <th>Floor No.</th>
              <th>Plot Status</th>
              <th>Firm Name</th>
              <th>GST No.</th>
              <th>Contact Name</th>
              <th>Contact Number</th>
              <th>Segment</th>
              <th>Category</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php

              if(isset($_REQUEST['btnsearch']) && $_REQUEST['search_textbox']!=null){

                $i=1;
                //$j=0;

                while($data=mysqli_fetch_array($result))
                {
                  $row_data=json_decode($data["raw_data"]);
                  $post_fields=$row_data->post_fields;

                /*  if($i==1){
                    $old_id = $data["id"];
                    $j=0;
                  } 
                  else{
                    $new_id = $data["id"];
                    if($new_id!=$old_id){
                      $old_id=$new_id;
                      $j=0;
                    }
                  } */

                  if(isset($row_data->plot_details)){
                    $plot_details=$row_data->plot_details;
                    asort($plot_details);
                    foreach ($plot_details as $pd) {
            ?>

            <tr>
              <td hidden><?php echo $i ?></td>
              <td>
                <a href="javascript:editdata('<?php echo $data["id"]?>','<?php echo $pd->Plot_Id ?>','<?php echo base64_encode($row_data->Image) ?>','<?php echo $row_data->Status?>','<?php echo base64_encode($post_fields->Category)?>','<?php echo $pd->Plot_Status ?>','<?php echo $plotting_pattern ?>');" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="right" data-bs-html="true" title="<span>Edit</span>"><i class="bx bx-edit-alt me-1"></i> </a>

                <a href="javascript:viewdata('<?php echo $data["id"]?>','<?php echo $pd->Plot_Id ?>','<?php echo base64_encode($row_data->Image) ?>','<?php echo $row_data->Status?>','<?php echo base64_encode($post_fields->Category)?>','<?php echo $pd->Plot_Status ?>','<?php echo base64_encode($plotting_pattern) ?>');" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="right" data-bs-html="true" title="<span>View</span>"><i class="bx bx-show me-1"></i></a> 

            <?php if($pd->Floor=='0'){ ?>
                <a href="javascript:addPlotDetails('<?php echo $data["id"]?>');" style="color:green">Add Plot</a>
                <a href="javascript:addFloor('<?php echo $data["id"]?>','<?php echo base64_encode($pd->Plot_No)?>','<?php echo base64_encode($pd->Road_No)?>','<?php echo base64_encode($estate_result["area_id"])?>','<?php echo base64_encode($estate_result["industrial_estate"])?>');" style="color:blue;">Add Floor</a>
            <?php } ?>
              </td>

              <td><?php if(isset($pd->Plot_No)){ echo $pd->Plot_No; } ?></td>
              <td><?php if(isset($pd->Floor)){ if($pd->Floor=='0'){ echo 'Ground Floor'; } else{ echo $pd->Floor; } } ?></td>
              <td><?php if(isset($pd->Plot_Status)){ echo $pd->Plot_Status; } ?></td>
              <td><?php echo $post_fields->Firm_Name ?></td>
              <td><?php echo $post_fields->GST_No ?></td>
              <td><?php echo $post_fields->Contact_Name ?></td>
              <td><?php echo $post_fields->Mobile_No ?></td>
              <td><?php echo $post_fields->Segment ?></td>
              <td><?php echo $post_fields->Category ?></td>
              <td><?php echo $row_data->Status ?></td>              
            </tr>
            <?php
                    $i++;
                    //$j++;
                  } }
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

/*  document.addEventListener( "DOMContentLoaded", function(){
    /*let cookies = document.cookie;
    console.log(cookies);
    id = readCookie("id_foredit");
    plot_id = readCookie("plotid_foredit");
    plot_status = readCookie("plotstatus_foredit");
    plotting_pattern = readCookie("pattern_foredit");
    blank = '';
    if(id!=null){
      editdata(id,plot_id,blank,blank,blank,plot_status,plotting_pattern);  
    }
    
    
  });*/

  $( document ).ready(function() {
    let cookies = document.cookie;
    console.log(cookies);
    id = readCookie("id_foredit");
    plot_id = readCookie("plotid_foredit");
    plot_status = readCookie("plotstatus_foredit");
    plotting_pattern = readCookie("pattern_foredit");
    if(id!=null){
      editdata(id,plot_id,'','','',plot_status,plotting_pattern);  
    }
    eraseCookie("id_foredit");
    eraseCookie("plotid_foredit");
    eraseCookie("plotstatus_foredit");
    eraseCookie("pattern_foredit");
    console.log("done");
  });

  if ( window.history.replaceState ) {
    window.history.replaceState( null, null, window.location.href );
  }
  
  function addPlotDetails(id) {
    document.cookie = "cid="+id+"; path=/";
    var loc = "plot_details.php";
    window.location = loc;
  }

  function addFloor(id,plot_no,road_no,area,industrial_estate) {
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=getFloors",
      data: "plot_no="+atob(plot_no)+"&area="+atob(area)+"&industrial_estate="+atob(industrial_estate),
      cache: false,
      success: function(result){
        $('#floor').html('');
        $('#floor').append(result);
      }
    });
    $('#floorModal').modal('toggle');
    $('#floormodal_ttId').val(id);
    $('#plot_no').val(atob(plot_no));
    $('#road_no').val(atob(road_no));
    $('#plotno_floormodal').html(atob(plot_no));
    $('#roadno_floormodal').html(atob(road_no));
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
        var data = result.split("@@@@@");
        if(data[0]>0)
        {
          $('#gst_alert_div').html('GST No. already exist!  You can add in Plot No. '+data[1]);
          document.getElementById('btn_modal_update').disabled = true;
        }
        else
        {
          $('#gst_alert_div').html('');
          document.getElementById('btn_modal_update').disabled = false;
        }
      }
    });
  }

  function readURL(input) {
    if (input.files && input.files[0]) {
      var filename=input.files.item(0).name;

      var reader = new FileReader();
      var extn=filename.split(".");

      if(extn[1].toLowerCase()=="jpg" || extn[1].toLowerCase()=="jpeg" || extn[1].toLowerCase()=="png" || extn[1].toLowerCase()=="bmp") {

        reader.onload = function (e) {
            $('#PreviewImage').attr('src', e.target.result);
            document.getElementById('PreviewImage').style.display = "block";
        };

        reader.readAsDataURL(input.files[0]);
        $('#imgdiv').html("");
        document.getElementById('btn_modal_update').disabled = false;
      }
      else
      {
          $('#imgdiv').html("Please Select Image Only");
          document.getElementById('btn_modal_update').disabled = true;
      }
    }
  }

  function editdata(id,j,img,status,category,plot_status,plotting_pattern) {
    //alert('yes');
    $('#modalCenter').modal('toggle');
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=update_company_plots",
      data: "id="+id+"&index="+j+"&plotting_pattern="+plotting_pattern,
      cache: false,
      success: function(result){
        //alert(result);
        var data = result.split("@@@@@");

        $('#company_detail_modal').html('');
        $('#company_detail_modal').html(data[0]);

        $('#plot_detail_modal').html('');
        $('#plot_detail_modal').html(data[1]);
   
        $('#himage').val(atob(img));
        $('#PreviewImage').show();
        $('#PreviewImage').attr('src','gst_image/'+atob(img));
        $('#img').removeAttr('required');

        $('#category').val(atob(category));

        if(status=="Existing Company"){
         $('#existing_company').attr("checked","checked"); 
        }
        else if(status=="Positive"){
         $('#positive').attr("checked","checked"); 
        }
        else if(status=="Negative"){
         $('#negative').attr("checked","checked"); 
        }

        if(plot_status=="Open Plot"){
         $('#open_plot').attr("checked","checked"); 
        }
        else if(plot_status=="Under Construction"){
         $('#under_construction').attr("checked","checked"); 
        }
        else if(plot_status=="Constructed"){
         $('#constructed').attr("checked","checked"); 
        }
      }
    });
  }
  function viewdata(id,j,img,status,category,plot_status,plotting_pattern) {
    $('#modalCenter').modal('toggle');
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=update_company_plots",
      data: "id="+id+"&index="+j+"&plotting_pattern="+atob(plotting_pattern),
      cache: false,
      success: function(result){
        var data = result.split("@@@@@");

        $('#company_detail_modal').html('');
        $('#company_detail_modal').html(data[0]);

        $('#plot_detail_modal').html('');
        $('#plot_detail_modal').html(data[1]);
   
        $('#himage').val(atob(img));
        $('#PreviewImage').show();
        $('#PreviewImage').attr('src','gst_image/'+atob(img));
        $('#img').removeAttr('required');

        $('#category').val(atob(category));

        if(status=="Existing Company"){
         $('#existing_company').attr("checked","checked"); 
        }
        else if(status=="Positive"){
         $('#positive').attr("checked","checked"); 
        }
        else if(status=="Negative"){
         $('#negative').attr("checked","checked"); 
        }

        if(plot_status=="Open Plot"){
         $('#open_plot').attr("checked","checked"); 
        }
        else if(plot_status=="Under Construction"){
         $('#under_construction').attr("checked","checked"); 
        }
        else if(plot_status=="Constructed"){
         $('#constructed').attr("checked","checked"); 
        }

        $('#btn_modal_update').attr('hidden',true);
        $('#btn_modal_update').attr('disabled',true);
      }
    });
  }
</script>
<?php 
  include("footer.php");
?>