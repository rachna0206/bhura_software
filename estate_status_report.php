<?php
  include("header.php");

$user_id = $_SESSION["id"];


// update estate status and insert data for assign estate for plotting
if(isset($_REQUEST['btn_modal_update']))
{
  echo "<br/>Estate Id = ".$estate_id = $_REQUEST['industrial_estate_id'];
  echo "<br/>Verify Status = ".$verify_status = $_REQUEST['verify_status'];
  echo "<br/>Insert Type = ".$insert_type = $_REQUEST['insert_type'];
  
  try
  {
    if($verify_status=='Fake' || $verify_status=='Duplicate' || $insert_type=='only_status'){
      $stmt = $obj->con1->prepare("UPDATE `pr_add_industrialestate_details` SET `status`=? WHERE `industrial_estate_id`=?");
      $stmt->bind_param("si",$verify_status,$estate_id);
      $Resp=$stmt->execute();
      $stmt->close();
    }
    else if($insert_type=='assign_estate'){
      $start_date = $_REQUEST['start_date'];
      $end_date = $_REQUEST['end_date'];
      $action = 'estate_plotting';
      $user_id = $_SESSION["id"];

      $stmt_detail = $obj->con1->prepare("UPDATE `pr_add_industrialestate_details` set `status`=? where `industrial_estate_id`=?");
      $stmt_detail->bind_param("si",$verify_status,$estate_id);
      $Resp=$stmt_detail->execute();
      $stmt_detail->close();

      foreach($_REQUEST['e'] as $emp_id){
          $stmt = $obj->con1->prepare("INSERT INTO `assign_estate`(`employee_id`, `industrial_estate_id`, `start_dt`, `end_dt`, `user_id`, `action`) VALUES (?,?,?,?,?,?)");
          $stmt->bind_param("iissis",$emp_id,$estate_id,$start_date,$end_date,$user_id,$action);
          $Resp=$stmt->execute();
      }
    }

    if(!$Resp)
    {
      throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
    }
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
/*
// update data
if(isset($_REQUEST['btn_modal_update']))
{
  $industrial_estate_id = $_REQUEST['industrial_estate_id'];
  $verify_status = $_REQUEST['verify_status'];
  $insert_type = $_REQUEST['insert_type'];
  
  try
  {
    if($verify_status=='Fake' || $verify_status=='Duplicate' || $insert_type=='only_status'){
      $stmt = $obj->con1->prepare("UPDATE `pr_add_industrialestate_details` SET `status`=? WHERE `industrial_estate_id`=?");
      $stmt->bind_param("si",$verify_status,$industrial_estate_id);
      $Resp=$stmt->execute();
      $stmt->close();
    }
    else if($insert_type=='plotting_and_status'){
      $state = $_REQUEST['state'];
      $city = $_REQUEST['city'];
      $taluka = $_REQUEST['taluka'];
      $ind_estate_id = $_REQUEST['ind_estate_id'];
      $industrial_estate = $_REQUEST['industrial_estate'];
      $area = $_REQUEST['area'];
      $plotting_pattern = $_REQUEST['plotting_pattern'];

      if($_REQUEST['plotting_pattern']=='Series'){
        $from_plotno = $_REQUEST['from_plotno'];
        $to_plotno = $_REQUEST['to_plotno'];  
        $series_plot_cnt = $_REQUEST['series_plot_cnt'];
      }
      else if($_REQUEST['plotting_pattern']=='Road'){
        $num_of_roads = $_REQUEST['road_cnt'];
        $num_of_additional_roads = $_REQUEST['additional_road_cnt'];
        $from_roadno = $_REQUEST['from_roadno'];
        $to_roadno = $_REQUEST['to_roadno'];  
      }

      $stmt_detail = $obj->con1->prepare("UPDATE `pr_add_industrialestate_details` set  `plotting_pattern`=?, `user_id`=?, `status`=? where `industrial_estate_id`=?");
      $stmt_detail->bind_param("sisi",$plotting_pattern,$user_id,$verify_status,$ind_estate_id);
      $Resp=$stmt_detail->execute();
      $stmt_detail->close();

      // multiple estate images 
      foreach ($_FILES["img"]['name'] as $key => $value)
      { 
        // rename for estate images       
        if($_FILES["img"]['name'][$key]!=""){
          $PicSubImage = $_FILES["img"]["name"][$key];
          if (file_exists("industrial_estate_image/" . $PicSubImage )) {
            $i = 0;
            $SubImageName = $PicSubImage;
            $Arr = explode('.', $SubImageName);
            $SubImageName = $Arr[0] . $i . "." . $Arr[1];
            while (file_exists("industrial_estate_image/" . $SubImageName)) {
                $i++;
                $SubImageName = $Arr[0] . $i . "." . $Arr[1];
            }
          } else {
            $SubImageName = $PicSubImage;
          }
          $SubImageTemp = $_FILES["img"]["tmp_name"][$key];
         
          // sub images qry
          move_uploaded_file($SubImageTemp, "industrial_estate_image/".$SubImageName);
        }
        
        $stmt_image = $obj->con1->prepare("INSERT INTO `pr_estate_subimages`(`industrial_estate_id`, `image`) VALUES (?,?)");
        $stmt_image->bind_param("ss",$ind_estate_id,$SubImageName);
        $Resp=$stmt_image->execute();
        $stmt_image->close();
      }

      // if plotting pattern = Series
      if($plotting_pattern=='Series'){
        $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `plot_start_no`, `plot_end_no`, `user_id`) VALUES (?,?,?,?)");
        $stmt_plot->bind_param("issi",$ind_estate_id,$from_plotno,$to_plotno,$user_id);
        $Resp=$stmt_plot->execute();
        $stmt_plot->close();

        for($p=$from_plotno;$p<=$to_plotno;$p++){
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
            "Image" => "",
            "Constitution" => "",
            "Status" => "",
            "plot_details" => Array(
              Array(
              "Plot_No" => $p,
              "Floor" => "0",
              "Road_No" => "",
              "Plot_Status" => "",
              "Plot_Id" => "1",
              ),
            ) 
          );
           
          // Encode array to json
          $json = json_encode($cp);
           
          // Display it
          //echo "$json";

          $stmt_rawdata = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
          $stmt_rawdata->bind_param("ss",$json,$user_id);
          $Resp=$stmt_rawdata->execute();
          $stmt_rawdata->close();
        }

        // for additional plot in series wise
        if($series_plot_cnt>0){
          for($c=0;$c<$series_plot_cnt;$c++){
            $additional_plotno = $_REQUEST['additional_plotno'.$c];

            if($additional_plotno!="" || $additional_plotno!=null){
              $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `plot_start_no`, `user_id`) VALUES (?,?,?)");
              $stmt_plot->bind_param("isi",$ind_estate_id,$additional_plotno,$user_id);
              $Resp=$stmt_plot->execute();
              $stmt_plot->close();

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
                "Image" => "",
                "Constitution" => "",
                "Status" => "",
                "plot_details" => Array(
                  Array(
                  "Plot_No" => $additional_plotno,
                  "Floor" => "0",
                  "Road_No" => "",
                  "Plot_Status" => "",
                  "Plot_Id" => "1",
                  ),
                ) 
              );
               
              // Encode array to json
              $json = json_encode($cp);
               
              // Display it
              //echo "$json";

              $stmt_rawdata = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
              $stmt_rawdata->bind_param("ss",$json,$user_id);
              $Resp=$stmt_rawdata->execute();
              $stmt_rawdata->close();
            }
          }
        }
      }

      // if plotting pattern = Road
      else if($plotting_pattern=='Road'){
        for($i=0;$i<=$num_of_roads;$i++){

          $road_number = $_REQUEST['road_no'.$i];
          $from_plotno_road = $_REQUEST['from_plotno_road'.$i];
          $to_plotno_road = $_REQUEST['to_plotno_road'.$i];
          $road_plot_cnt = $_REQUEST['road_plot_cnt'.$i];

          if($from_plotno_road!="" || $from_plotno_road!=null){
          
            $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `road_no`, `plot_start_no`, `plot_end_no`, `user_id`) VALUES (?,?,?,?,?)");
            $stmt_plot->bind_param("isssi",$ind_estate_id,$road_number,$from_plotno_road,$to_plotno_road,$user_id);
            $Resp=$stmt_plot->execute();
            $stmt_plot->close();

            for($p=$from_plotno_road;$p<=$to_plotno_road;$p++){
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
                "Image" => "",
                "Constitution" => "",
                "Status" => "",
                "plot_details" => Array(
                  Array(
                  "Plot_No" => $p,
                  "Floor" => "0",
                  "Road_No" => $road_number,
                  "Plot_Status" => "",
                  "Plot_Id" => "1",
                  ),
                ) 
              );
               
              // Encode array to json
              $json = json_encode($cp);
               
              // Display it
              //echo "$json";

              $stmt_rawdata = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
              $stmt_rawdata->bind_param("ss",$json,$user_id);
              $Resp=$stmt_rawdata->execute();
              $stmt_rawdata->close();
            }

            // for additional plot in road wise
            if($road_plot_cnt>0){
              for($c=0;$c<$road_plot_cnt;$c++){
                $additional_plotno = $_REQUEST['additional_plotno_road'.$i.'_'.$c];  
                if($additional_plotno!="" || $additional_plotno!=null){

                  $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `road_no`, `plot_start_no`, `user_id`) VALUES (?,?,?,?)");
                  $stmt_plot->bind_param("issi",$ind_estate_id,$road_number,$additional_plotno,$user_id);
                  $Resp=$stmt_plot->execute();
                  $stmt_plot->close();

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
                    "Image" => "",
                    "Constitution" => "",
                    "Status" => "",
                    "plot_details" => Array(
                      Array(
                        "Plot_No" => $additional_plotno,
                        "Floor" => "0",
                        "Road_No" => $road_number,
                        "Plot_Status" => "",
                        "Plot_Id" => "1",
                      ),
                    ) 
                  );
                   
                  // Encode array to json
                  $json = json_encode($cp);
                   
                  // Display it
                  //echo "<br/>"."$json";

                  $stmt_rawdata = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
                  $stmt_rawdata->bind_param("ss",$json,$user_id);
                  $Resp=$stmt_rawdata->execute();
                  $stmt_rawdata->close();
                }
              }
            }
          }
        }

        // for additional road
        if($num_of_additional_roads>0){
          for($r=0;$r<$num_of_additional_roads;$r++){
            
            $road_number = $_REQUEST['additional_road_no'.$r];
            $from_plotno_road = $_REQUEST['additional_from_plotno_road'.$r];
            $to_plotno_road = $_REQUEST['additional_to_plotno_road'.$r];
            $road_plot_cnt = $_REQUEST['additional_road_plot_cnt'.$r];

            if($from_plotno_road!="" || $from_plotno_road!=null){
            
              $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `road_no`, `plot_start_no`, `plot_end_no`, `user_id`) VALUES (?,?,?,?,?)");
              $stmt_plot->bind_param("isssi",$ind_estate_id,$road_number,$from_plotno_road,$to_plotno_road,$user_id);
              $Resp=$stmt_plot->execute();
              $stmt_plot->close();

              for($p=$from_plotno_road;$p<=$to_plotno_road;$p++){
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
                  "Image" => "",
                  "Constitution" => "",
                  "Status" => "",
                  "plot_details" => Array(
                    Array(
                    "Plot_No" => $p,
                    "Floor" => "0",
                    "Road_No" => $road_number,
                    "Plot_Status" => "",
                    "Plot_Id" => "1",
                    ),
                  ) 
                );
                 
                // Encode array to json
                $json = json_encode($cp);
                 
                // Display it
                //echo "<br/>"."$json";

                $stmt_rawdata = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
                $stmt_rawdata->bind_param("ss",$json,$user_id);
                $Resp=$stmt_rawdata->execute();
                $stmt_rawdata->close();
              }

              // for additional plot in road wise
              if($road_plot_cnt>0){
                for($c=0;$c<$road_plot_cnt;$c++){
                  $additional_plotno = $_REQUEST['additional_plotno_new_road'.$r.'_'.$c];  
                  if($additional_plotno!="" || $additional_plotno!=null){

                    $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `road_no`, `plot_start_no`, `user_id`) VALUES (?,?,?,?)");
                    $stmt_plot->bind_param("issi",$ind_estate_id,$road_number,$additional_plotno,$user_id);
                    $Resp=$stmt_plot->execute();
                    $stmt_plot->close();

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
                      "Image" => "",
                      "Constitution" => "",
                      "Status" => "",
                      "plot_details" => Array(
                        Array(
                          "Plot_No" => $additional_plotno,
                          "Floor" => "0",
                          "Road_No" => $road_number,
                          "Plot_Status" => "",
                          "Plot_Id" => "1",
                        ),
                      ) 
                    );
                     
                    // Encode array to json
                    $json = json_encode($cp);
                     
                    // Display it
                    //echo "<br/>"."$json";

                    $stmt_rawdata = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
                    $stmt_rawdata->bind_param("ss",$json,$user_id);
                    $Resp=$stmt_rawdata->execute();
                    $stmt_rawdata->close();
                  }
                }
              }
            }
          }
        }
      }
    }
      
    
    if(!$Resp)
    {
      throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
    }
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
*/
?>

<h4 class="fw-bold py-3 mb-4">Estate Plots Report</h4>

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

        <form action="generate_report_excel.php" method="post" style="display:inline;">
            <input type="hidden" name="query" value="SELECT i1.*, d1.status,u1.name as user_name,d1.datetime FROM `tbl_industrial_estate` i1, `pr_add_industrialestate_details` d1,tbl_users u1 where i1.id=d1.industrial_estate_id and d1.user_id=u1.id and d1.status in ('Fake','Duplicate')">
            <button type="submit" class="btn btn-primary">Download Excel</button>
        </form>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>State</th>
              <th>City</th>
              <th>Taluka</th>
              <th>Area</th>
              <th>Industrial Estate</th>
              <th>Status</th>
              <th>Added By</th>
              <th>Date Time</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php 
      
              $stmt_list = $obj->con1->prepare("SELECT i1.*, d1.status,u1.name as user_name,d1.datetime FROM `tbl_industrial_estate` i1, `pr_add_industrialestate_details` d1,tbl_users u1 where i1.id=d1.industrial_estate_id and d1.user_id=u1.id and d1.status in ('Fake','Duplicate')");
              $stmt_list->execute();
              $result = $stmt_list->get_result();
              $stmt_list->close();
              $i=1;

              while($data=mysqli_fetch_array($result))
              {
            ?>

            <tr>
              <td><?php echo $i?></td>
              <td><?php echo $data["state_id"] ?></td>
              <td><?php echo $data["city_id"] ?></td>
              <td><?php echo $data["taluka"] ?></td>
              <td><?php echo $data["area_id"] ?></td>
              <td><?php echo $data["industrial_estate"] ?></td>
              <td><?php echo $data["status"] ?></td>
              <td><?php echo $data["user_name"] ?></td>
              <td><?php echo date("d-m-Y h:i A", strtotime($data["datetime"])) ?></td>
              <td>
                <a href="javascript:editdata('<?php echo $data["id"]?>','<?php echo $data["state_id"]?>','<?php echo $data["city_id"]?>','<?php echo $data["taluka"]?>','<?php echo $data["area_id"]?>','<?php echo $data["industrial_estate"]?>','<?php echo $data["status"] ?>');"><i class="bx bx-edit-alt me-1"></i> </a>
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
        <form method="post" enctype="multipart/form-data"><div class="modal-body" >
          <div class="row">
            <input type="hidden" class="form-control" name="industrial_estate_id" id="industrial_estate_id" />

            <div class="mb-3">
              <label class="form-label" for="basic-default-fullname" id="industrial_estate"></label><br/>
              <label class="form-label" for="basic-default-fullname" id="taluka"></label>
            </div>

            <div class="mb-3">
              <label class="form-label" for="basic-default-fullname">Status</label><br/>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="verify_status" id="Verified" value="Verified" onchange="getOtherOptions()" required>
                <label class="form-check-label" for="inlineRadio1">Verified</label>
              </div>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="verify_status" id="Fake" value="Fake" onchange="getOtherOptions()" required>
                <label class="form-check-label" for="inlineRadio1">Fake</label>
              </div>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="verify_status" id="Duplicate" value="Duplicate" onchange="getOtherOptions()" required>
                <label class="form-check-label" for="inlineRadio1">Duplicate</label>
              </div>
            </div>
          </div>
          <div id="newModal_div"></div>
        </div>
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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- / Content -->
<script type="text/javascript">

  $(document).ready(function() {
    $('.js-example-basic-multiple').select2();
  });

  function getOtherOptions(){
    if($('#Verified').is(':checked')){
      ind_estate_id = $('#industrial_estate_id').val();
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getPlotStatus",
        data: "estate_id="+ind_estate_id,
        cache: false,
        success: function(result){
          $('#newModal_div').html('');
          $('#newModal_div').append(result);

          $('#emp_list').css("width","100%");
          $('.js-example-basic-multiple').select2({
            dropdownParent: $('#modalCenter')
          });
        }
      });
    }
    else{
      $('#newModal_div').html('');
    }
  }

  function editdata(id,state,city,taluka,area,industrial_estate,status) {
    $('#modalCenter').modal('toggle');
    $('#industrial_estate_id').val(id);
    $('#industrial_estate').html("Industrial Estate : "+industrial_estate);
    $('#taluka').html("Taluka : "+taluka);
    $('input[type=radio][name=verify_status]').prop("checked",false);
    $('#'+status).prop("checked","checked");
    getOtherOptions();
  }  


  // for plotting
  function estate_withnoplotting(taluka,city,state,user_id){
    $('#area_list').html('');
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=estate_withnoplotting",
      data: "taluka="+taluka+"&city="+city+"&state_name="+state+"&user_id="+user_id,
      cache: false,
      success: function(result){
        $('#ind_estate_id').html('');
        $('#ind_estate_id').append(result);
      }
    });
  }
  
  function getArea(ind_estate){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=getAreaList",
      data: "ind_estate_id="+ind_estate,
      cache: false,
      success: function(result){
        $('#area_list').html('');
        $('#area_list').append(result);
      }
    });
  }

  function getplotform(){
    if($('#series_wise').is(':checked')){
      $('#series_div').removeAttr("hidden");
      $('#road_div').attr("hidden",true);

      $('#from_plotno').attr("required",true);
      $('#to_plotno').attr("required",true);
      $("[id^='additional_plotno']").attr("required",true);

      $("[id^='road_no']").removeAttr("required");
      $("[id^='from_roadno']").removeAttr("required");
      $("[id^='to_roadno']").removeAttr("required");
      $("[id^='from_plotno_road']").removeAttr("required");
      $("[id^='to_plotno_road']").removeAttr("required");
      $("[id^='additional_plotno_road']").removeAttr("required");
    }
    else if($('#road_wise').is(':checked')){
      $('#road_div').removeAttr("hidden");
      $('#series_div').attr("hidden",true);

      $("[id^='road_no']").attr("required",true);
      $("[id^='from_roadno']").attr("required",true);
      $("[id^='to_roadno']").attr("required",true);
      $("[id^='from_plotno_road']").attr("required",true);
      $("[id^='to_plotno_road']").attr("required",true);
      $("[id^='additional_plotno_road']").attr("required",true);

      $('#from_plotno').removeAttr("required");
      $('#to_plotno').removeAttr("required");
      $("[id^='additional_plotno']").removeAttr("required");
    }
  } 

  function readURL(input) {
    $('#preview_image_div').html("");
    var filesAmount = input.files.length;
    for (i = 0; i < filesAmount; i++) {
      if (input.files && input.files[i]) {

        var filename=input.files.item(i).name;
        var reader = new FileReader();
        var extn=filename.split(".");

         if(extn[1].toLowerCase()=="jpg" || extn[1].toLowerCase()=="jpeg" || extn[1].toLowerCase()=="png" || extn[1].toLowerCase()=="bmp") {
          reader.onload = function (e) {
            $('#preview_image_div').append('<img src="'+e.target.result+'" name="PreviewImage'+i+'" id="PreviewImage'+i+'" width="100" height="100" style="display:inline-block; margin:2%;">');
          };

          reader.readAsDataURL(input.files[i]);
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
  }

  // for road wise 
  function get_plot_adding_options()
  {
    $('#road_plots_div').html("");

    var from_roadno=$('#from_roadno').val();
    var to_roadno=$('#to_roadno').val();

    if(from_roadno!="" && to_roadno!=""){

      $('#road_plots_div').append('<div><a href="javascript:get_additional_road()" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i>  Add Additional Road No.</a><br/></div><br/><div id="additional_road_div"></div>');
      
      var count=0;

      for (let i=parseInt(from_roadno); i<=parseInt(to_roadno); i++) {
        var road_count=$('#road_cnt').val();
            
        $('#road_plots_div').append('<div id="road_plot_div_'+count+'"><div><label class="form-label" for="basic-default-fullname">Add Plot : </label></br><label class="form-label" for="basic-default-company">Road No.</label><input type="text" name="road_no'+count+'" id="road_no'+count+'" value="'+i+'" class="form-control" readonly required></div><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">From (Plot No.)</label><input type="text" class="form-control" pattern="^[0-9]*$" name="from_plotno_road'+count+'" id="from_plotno_road'+count+'" required /></div><div class="col mb-3"><label class="form-label" for="basic-default-fullname">To (Plot No.)</label><input type="text" class="form-control" pattern="^[0-9]*$" name="to_plotno_road'+count+'" id="to_plotno_road'+count+'" required /></div> <a href="javascript:additional_plot_road('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i> Add Additional Plot</a></br> <input type="hidden" name="road_plot_cnt'+count+'" id="road_plot_cnt'+count+'" value="0"/></div><div id="additional_road_plots_div'+count+'"></div><hr></div>');

        $('#road_cnt').val(parseInt(count));

        count++;
      }
    }
  }

  // for additional road no 
  function get_additional_road(){
   var additional_road_count=$('#additional_road_cnt').val();
        
    $('#additional_road_div').append('<div id="additional_road_plot_div_'+additional_road_count+'"><div><label class="form-label" for="basic-default-fullname">Add Plot : </label><a href="javascript:remove_field(\'additional_road_plot_div_'+additional_road_count+'\')" class="text-right"><i class="bx bxs-message-square-minus bx-sm"></i></a></br><label class="form-label" for="basic-default-company">Select Road No.</label><input type="text" name="additional_road_no'+additional_road_count+'" id="additional_road_no'+additional_road_count+'" class="form-control" required></div><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">From (Plot No.)</label><input type="text" class="form-control" pattern="^[0-9]*$" name="additional_from_plotno_road'+additional_road_count+'" id="additional_from_plotno_road'+additional_road_count+'" required /></div><div class="col mb-3"><label class="form-label" for="basic-default-fullname">To (Plot No.)</label><input type="text" class="form-control" pattern="^[0-9]*$" name="additional_to_plotno_road'+additional_road_count+'" id="additional_to_plotno_road'+additional_road_count+'" required /></div> <a href="javascript:additional_plot_new_road('+additional_road_count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i> Add Additional Plot</a></br> <input type="hidden" name="additional_road_plot_cnt'+additional_road_count+'" id="additional_road_plot_cnt'+additional_road_count+'" value="0"/></div><div id="additional_extra_road_plots_div'+additional_road_count+'"></div><hr></div>');
   
    $('#additional_road_cnt').val(parseInt(additional_road_count)+1);

    //.remove() to remove div
  }
  function remove_field(div)
  {
    $('#'+div).remove();
  }

  // for series wise additional plot
  function additional_plot_series()
  {
    var plot_count=$('#series_plot_cnt').val();

    $('#additional_series_plots_div').append('<div id="additional_series_plot_div_'+plot_count+'"><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">Plot No.</label><a href="javascript:remove_additional_plot_series(\'additional_series_plot_div_'+plot_count+'\')" class="text-right"><i class="bx bxs-message-square-minus bx-sm"></i></a></br><input type="text" class="form-control" name="additional_plotno'+plot_count+'" id="additional_plotno'+plot_count+'" /></div><div class="col mb-3"></div></div></div>');
   
    $('#series_plot_cnt').val(parseInt(plot_count)+1);

    //.remove() to remove div
  }
  function remove_additional_plot_series(div)
  {
    $('#'+div).remove();
  }

  // for road wise additional plot
  function additional_plot_road(x)
  {
    var road_count=x;
    var plot_count=$('#road_plot_cnt'+road_count).val();
        
    $('#additional_road_plots_div'+road_count).append('<div id="additional_road_plot_div'+road_count+'_'+plot_count+'"><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">Plot No.</label><a href="javascript:remove_additional_plot_road(\'additional_road_plot_div'+road_count+'_'+plot_count+'\')" class="text-right"><i class="bx bxs-message-square-minus bx-sm"></i></a></br><input type="text" class="form-control" name="additional_plotno_road'+road_count+'_'+plot_count+'" id="additional_plotno_road'+road_count+'_'+plot_count+'" /></div><div class="col mb-3"></div></div></div>');
   
    $('#road_plot_cnt'+road_count).val(parseInt(plot_count)+1);

    //.remove() to remove div
  }
  function remove_additional_plot_road(div)
  {
    $('#'+div).remove();
  }

  // for additional road - additional plot
  function additional_plot_new_road(x)
  {
    var additional_road_count=x;
    var plot_count=$('#additional_road_plot_cnt'+additional_road_count).val();
        
    $('#additional_extra_road_plots_div'+additional_road_count).append('<div id="additional_road_plot_div'+additional_road_count+'_'+plot_count+'"><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">Plot No.</label><a href="javascript:remove_additional_plot_new_road(\'additional_road_plot_div'+additional_road_count+'_'+plot_count+'\')" class="text-right"><i class="bx bxs-message-square-minus bx-sm"></i></a></br><input type="text" class="form-control" name="additional_plotno_new_road'+additional_road_count+'_'+plot_count+'" id="additional_plotno_new_road'+additional_road_count+'_'+plot_count+'" /></div><div class="col mb-3"></div></div></div>');
   
    $('#additional_road_plot_cnt'+additional_road_count).val(parseInt(plot_count)+1);

    //.remove() to remove div
  }
  function remove_additional_plot_new_road(div)
  {
    $('#'+div).remove();
  }

</script>

<?php 
  include("footer.php");
?>