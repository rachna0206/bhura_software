<?php
  include("header.php");

$user_id = $_SESSION["id"];

// insert data for assign estate for plotting
if(isset($_REQUEST['btn_modal_update']))
{
  $start_date = $_REQUEST['start_date'];
  $end_date = $_REQUEST['end_date'];
  $action = 'estate_plotting';
  $user_id = $_SESSION["id"];
  $estate_id = explode(",",$_REQUEST['industrial_estate']);
  
  try
  {
    foreach($_REQUEST['e'] as $emp_id){
      foreach($estate_id as $industrial_estate_id){
        $stmt = $obj->con1->prepare("INSERT INTO `assign_estate`(`employee_id`, `industrial_estate_id`, `start_dt`, `end_dt`, `user_id`, `action`) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("iissis",$emp_id,$industrial_estate_id,$start_date,$end_date,$user_id,$action);
        $Resp=$stmt->execute();
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
    header("location:unassigned_estate_plotting.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:unassigned_estate_plotting.php");
  }
}

// insert data for estate plotting
if(isset($_REQUEST['btnupdate']))
{
  $state = $_REQUEST['state'];
  $city = $_REQUEST['city'];
  $taluka = $_REQUEST['taluka'];
  $ind_estate_id = $_REQUEST['ind_estate_id'];
  $industrial_estate = $_REQUEST['industrial_estate'];
  $area = $_REQUEST['area'];
  $plotting_pattern = $_REQUEST['plotting_pattern'];
  $verify_status = isset($_REQUEST['verify_status'])?$_REQUEST['verify_status']:"";

  $floor_no = "0";
  $plot_id = "1";

  try
  {

    if($verify_status=='Fake' || $verify_status=='Duplicate'){
      $stmt_detail = $obj->con1->prepare("INSERT INTO `pr_add_industrialestate_details`(`industrial_estate_id`, `user_id`,`status`) VALUES (?,?,?)");
      $stmt_detail->bind_param("iis",$ind_estate_id,$user_id,$verify_status);
      $Resp=$stmt_detail->execute();
      $stmt_detail->close();
    }
    else{
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

      $stmt_detail = $obj->con1->prepare("INSERT INTO `pr_add_industrialestate_details`(`industrial_estate_id`, `plotting_pattern`,  `user_id`, `status`) VALUES (?,?,?,?)");
      $stmt_detail->bind_param("isis",$ind_estate_id,$plotting_pattern,$user_id,$verify_status);
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
          /*$cp = Array (
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
          );*/

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
              "Completion_Date" => "",
              "Term_Loan_Amount" => "",
              "CC_Loan_Amount" => "",
              "Under_Process_Bank" => "",
              "Under_Process_Branch" => "",
              "Term_Loan_Amount_In_Process" => "",
              "Under_Process_Date" => "",
              "ROI" => "",
              "Colletral" => "",
              "Consultant" => "",
              "Sanctioned_Bank" => "",
              "Bank_Branch" => "",
              "DOS" => "",
              "TL_Amount" => "",
              "Sactioned_Loan_Consultant" => "",
              "category_type" => "",
              "Remarks" => ""
            ),
            "inq_submit" => "Submit",
            "bad_lead_reason" => "",
            "bad_lead_reason_remark" => "",
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

          $Resp = tbl_tdrawdata_insert($json,$user_id);

          $road_number = NULL;

          company_plot_insert($p,$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
        }

        // for additional plot in series wise
        if($series_plot_cnt>0){
          for($c=0;$c<$series_plot_cnt;$c++){
            $additional_plotno = strtoupper($_REQUEST['additional_plotno'.$c]);

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
                  "Completion_Date" => "",
                  "Term_Loan_Amount" => "",
                  "CC_Loan_Amount" => "",
                  "Under_Process_Bank" => "",
                  "Under_Process_Branch" => "",
                  "Term_Loan_Amount_In_Process" => "",
                  "Under_Process_Date" => "",
                  "ROI" => "",
                  "Colletral" => "",
                  "Consultant" => "",
                  "Sanctioned_Bank" => "",
                  "Bank_Branch" => "",
                  "DOS" => "",
                  "TL_Amount" => "",
                  "Sactioned_Loan_Consultant" => "",
                  "category_type" => "",
                  "Remarks" => ""
                ),
                "inq_submit" => "Submit",
                "bad_lead_reason" => "",
                "bad_lead_reason_remark" => "",
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

              $Resp = tbl_tdrawdata_insert($json,$user_id);

              $road_number = NULL;

              company_plot_insert($additional_plotno,$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
            }
          }
        }
      }

      // if plotting pattern = Road
      else if($plotting_pattern=='Road'){
        for($i=0;$i<=$num_of_roads;$i++){

          $road_number = $_REQUEST['road_no'.$i];
          $road_plot_cnt = $_REQUEST['road_plot_cnt'.$i];
          $from_to_plot_cnt = $_REQUEST['from_to_plot_cnt'.$i];

          for($ft=0;$ft<$from_to_plot_cnt;$ft++){
            $from_plotno_road = $_REQUEST['from_plotno_road'.$i.'_'.$ft];
            $to_plotno_road = $_REQUEST['to_plotno_road'.$i.'_'.$ft];

            $letters = "/^[A-Za-z]$/";
            $suffix = "/^[0-9]+[^a-zA-Z0-9]*[a-zA-Z]$/";
            $prefix = "/^[a-zA-Z][^a-zA-Z0-9]*[0-9]+$/";
            $specialChars ="/[`!@#$%^&*()_\-+=\[\]{};':\\|,.<>\/?~ ]+/";
            $re_for_alphabet = "/([a-zA-Z]+)/";
            $re_for_digits = "/(\d+)/";

            if($from_plotno_road!="" || $from_plotno_road!=null){

              $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `road_no`, `plot_start_no`, `plot_end_no`, `user_id`) VALUES (?,?,?,?,?)");
              $stmt_plot->bind_param("isssi",$ind_estate_id,$road_number,strtoupper($from_plotno_road),strtoupper($to_plotno_road),$user_id);
              $Resp=$stmt_plot->execute();
              $stmt_plot->close();
              
              if(is_numeric($from_plotno_road) && is_numeric($to_plotno_road)){
                $type = "numeric";  
              }
              else if(preg_match($letters,$from_plotno_road) && preg_match($letters,$to_plotno_road)){
                $type = "alphabet";
              }
              else if(preg_match($prefix,$from_plotno_road) && preg_match($prefix,$to_plotno_road)){
                $type = "prefix";
              }
              else if(preg_match($suffix,$from_plotno_road) && preg_match($suffix,$to_plotno_road)){
                $type = "suffix";
              }


              if($type=="numeric"){
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
                      "Completion_Date" => "",
                      "Term_Loan_Amount" => "",
                      "CC_Loan_Amount" => "",
                      "Under_Process_Bank" => "",
                      "Under_Process_Branch" => "",
                      "Term_Loan_Amount_In_Process" => "",
                      "Under_Process_Date" => "",
                      "ROI" => "",
                      "Colletral" => "",
                      "Consultant" => "",
                      "Sanctioned_Bank" => "",
                      "Bank_Branch" => "",
                      "DOS" => "",
                      "TL_Amount" => "",
                      "Sactioned_Loan_Consultant" => "",
                      "category_type" => "",
                      "Remarks" => ""
                    ),
                    "inq_submit" => "Submit",
                    "bad_lead_reason" => "",
                    "bad_lead_reason_remark" => "",
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

                  $Resp = tbl_tdrawdata_insert($json,$user_id);
                  
                  company_plot_insert($p,$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                }
              }
              else if($type=="alphabet") {
                $from_plot_upper = strtoupper($from_plotno_road);
                $to_plot_upper = strtoupper($to_plotno_road);
                $from_plot_ascii = ord($from_plot_upper);
                $to_plot_ascii = ord($to_plot_upper);
                for($p=$from_plot_ascii;$p<=$to_plot_ascii;$p++){
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
                      "Completion_Date" => "",
                      "Term_Loan_Amount" => "",
                      "CC_Loan_Amount" => "",
                      "Under_Process_Bank" => "",
                      "Under_Process_Branch" => "",
                      "Term_Loan_Amount_In_Process" => "",
                      "Under_Process_Date" => "",
                      "ROI" => "",
                      "Colletral" => "",
                      "Consultant" => "",
                      "Sanctioned_Bank" => "",
                      "Bank_Branch" => "",
                      "DOS" => "",
                      "TL_Amount" => "",
                      "Sactioned_Loan_Consultant" => "",
                      "category_type" => "",
                      "Remarks" => ""
                    ),
                    "inq_submit" => "Submit",
                    "bad_lead_reason" => "",
                    "bad_lead_reason_remark" => "",
                    "Image" => "",
                    "Constitution" => "",
                    "Status" => "",
                    "plot_details" => Array(
                      Array(
                      "Plot_No" => chr($p),
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

                  $Resp = tbl_tdrawdata_insert($json,$user_id);
                  
                  company_plot_insert(chr($p),$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                }
              }
              else if($type=="prefix") {

                preg_match($re_for_digits,$from_plotno_road,$from_plot_number);
                preg_match($re_for_alphabet,$from_plotno_road,$from_plot_alphabet);
                preg_match($specialChars,$from_plotno_road,$from_plot_char);

                preg_match($re_for_digits,$to_plotno_road,$to_plot_number);
                preg_match($re_for_alphabet,$to_plotno_road,$to_plot_alphabet);
                preg_match($specialChars,$to_plotno_road,$to_plot_char);

                if($to_plot_number[0]>=$from_plot_number[0] && $to_plot_alphabet[0]==$from_plot_alphabet[0]){
                  // number increment
                  for($p=$from_plot_number[0];$p<=$to_plot_number[0];$p++){
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
                        "Completion_Date" => "",
                        "Term_Loan_Amount" => "",
                        "CC_Loan_Amount" => "",
                        "Under_Process_Bank" => "",
                        "Under_Process_Branch" => "",
                        "Term_Loan_Amount_In_Process" => "",
                        "Under_Process_Date" => "",
                        "ROI" => "",
                        "Colletral" => "",
                        "Consultant" => "",
                        "Sanctioned_Bank" => "",
                        "Bank_Branch" => "",
                        "DOS" => "",
                        "TL_Amount" => "",
                        "Sactioned_Loan_Consultant" => "",
                        "category_type" => "",
                        "Remarks" => ""
                      ),
                      "inq_submit" => "Submit",
                      "bad_lead_reason" => "",
                      "bad_lead_reason_remark" => "",
                      "Image" => "",
                      "Constitution" => "",
                      "Status" => "",
                      "plot_details" => Array(
                        Array(
                        "Plot_No" => strtoupper($from_plot_alphabet[0]).$from_plot_char[0].$p,
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

                    $Resp = tbl_tdrawdata_insert($json,$user_id);
                    
                    company_plot_insert((strtoupper($from_plot_alphabet[0]).$from_plot_char[0].$p),$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                  }
                }
                else if($to_plot_number[0]==$from_plot_number[0] && strtoupper($to_plot_alphabet[0])>=strtoupper($from_plot_alphabet[0])){
                  // alphabet increment
                  for($p=ord(strtoupper($from_plot_alphabet[0]));$p<=ord(strtoupper($to_plot_alphabet[0]));$p++){
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
                        "Completion_Date" => "",
                        "Term_Loan_Amount" => "",
                        "CC_Loan_Amount" => "",
                        "Under_Process_Bank" => "",
                        "Under_Process_Branch" => "",
                        "Term_Loan_Amount_In_Process" => "",
                        "Under_Process_Date" => "",
                        "ROI" => "",
                        "Colletral" => "",
                        "Consultant" => "",
                        "Sanctioned_Bank" => "",
                        "Bank_Branch" => "",
                        "DOS" => "",
                        "TL_Amount" => "",
                        "Sactioned_Loan_Consultant" => "",
                        "category_type" => "",
                        "Remarks" => ""
                      ),
                      "inq_submit" => "Submit",
                      "bad_lead_reason" => "",
                      "bad_lead_reason_remark" => "",
                      "Image" => "",
                      "Constitution" => "",
                      "Status" => "",
                      "plot_details" => Array(
                        Array(
                        "Plot_No" => chr($p).$from_plot_char[0].$from_plot_number[0],
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

                    $Resp = tbl_tdrawdata_insert($json,$user_id);
                    
                    company_plot_insert((chr($p).$from_plot_char[0].$from_plot_number[0]),$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                  }
                }
              }
              else if($type=="suffix") {
                preg_match($re_for_digits,$from_plotno_road,$from_plot_number);
                preg_match($re_for_alphabet,$from_plotno_road,$from_plot_alphabet);
                preg_match($specialChars,$from_plotno_road,$from_plot_char);

                preg_match($re_for_digits,$to_plotno_road,$to_plot_number);
                preg_match($re_for_alphabet,$to_plotno_road,$to_plot_alphabet);
                preg_match($specialChars,$to_plotno_road,$to_plot_char);

                if($to_plot_number[0]>=$from_plot_number[0] && $to_plot_alphabet[0]==$from_plot_alphabet[0]){
                  // number increment
                  for($p=$from_plot_number[0];$p<=$to_plot_number[0];$p++){
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
                        "Completion_Date" => "",
                        "Term_Loan_Amount" => "",
                        "CC_Loan_Amount" => "",
                        "Under_Process_Bank" => "",
                        "Under_Process_Branch" => "",
                        "Term_Loan_Amount_In_Process" => "",
                        "Under_Process_Date" => "",
                        "ROI" => "",
                        "Colletral" => "",
                        "Consultant" => "",
                        "Sanctioned_Bank" => "",
                        "Bank_Branch" => "",
                        "DOS" => "",
                        "TL_Amount" => "",
                        "Sactioned_Loan_Consultant" => "",
                        "category_type" => "",
                        "Remarks" => ""
                      ),
                      "inq_submit" => "Submit",
                      "bad_lead_reason" => "",
                      "bad_lead_reason_remark" => "",
                      "Image" => "",
                      "Constitution" => "",
                      "Status" => "",
                      "plot_details" => Array(
                        Array(
                        "Plot_No" => $p.$from_plot_char[0].strtoupper($from_plot_alphabet[0]),
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

                    $Resp = tbl_tdrawdata_insert($json,$user_id);
                    
                    company_plot_insert(($p.$from_plot_char[0]).strtoupper($from_plot_alphabet[0]),$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                  }
                }
                else if($to_plot_number[0]==$from_plot_number[0] && strtoupper($to_plot_alphabet[0])>=strtoupper($from_plot_alphabet[0])){
                  // alphabet increment
                  for($p=ord(strtoupper($from_plot_alphabet[0]));$p<=ord(strtoupper($to_plot_alphabet[0]));$p++){
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
                        "Completion_Date" => "",
                        "Term_Loan_Amount" => "",
                        "CC_Loan_Amount" => "",
                        "Under_Process_Bank" => "",
                        "Under_Process_Branch" => "",
                        "Term_Loan_Amount_In_Process" => "",
                        "Under_Process_Date" => "",
                        "ROI" => "",
                        "Colletral" => "",
                        "Consultant" => "",
                        "Sanctioned_Bank" => "",
                        "Bank_Branch" => "",
                        "DOS" => "",
                        "TL_Amount" => "",
                        "Sactioned_Loan_Consultant" => "",
                        "category_type" => "",
                        "Remarks" => ""
                      ),
                      "inq_submit" => "Submit",
                      "bad_lead_reason" => "",
                      "bad_lead_reason_remark" => "",
                      "Image" => "",
                      "Constitution" => "",
                      "Status" => "",
                      "plot_details" => Array(
                        Array(
                        "Plot_No" => $from_plot_number[0].$from_plot_char[0].chr($p),
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

                    $Resp = tbl_tdrawdata_insert($json,$user_id);
                    
                    company_plot_insert(($from_plot_number[0].$from_plot_char[0].chr($p)),$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                  }
                }
              }
            }
          }
          
            
          if($from_plotno_road!="" && $from_plotno_road!=null){
            // for additional plot in road wise
            if($road_plot_cnt>0){
              for($c=0;$c<$road_plot_cnt;$c++){
                $additional_plotno = strtoupper($_REQUEST['additional_plotno_road'.$i.'_'.$c]);
                if($additional_plotno!="" && $additional_plotno!=null){

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
                      "Completion_Date" => "",
                      "Term_Loan_Amount" => "",
                      "CC_Loan_Amount" => "",
                      "Under_Process_Bank" => "",
                      "Under_Process_Branch" => "",
                      "Term_Loan_Amount_In_Process" => "",
                      "Under_Process_Date" => "",
                      "ROI" => "",
                      "Colletral" => "",
                      "Consultant" => "",
                      "Sanctioned_Bank" => "",
                      "Bank_Branch" => "",
                      "DOS" => "",
                      "TL_Amount" => "",
                      "Sactioned_Loan_Consultant" => "",
                      "category_type" => "",
                      "Remarks" => ""
                    ),
                    "inq_submit" => "Submit",
                    "bad_lead_reason" => "",
                    "bad_lead_reason_remark" => "",
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

                  $Resp = tbl_tdrawdata_insert($json,$user_id);
                  
                  company_plot_insert($additional_plotno,$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                }
              }
            }
          }
        }

        // for additional road
        if($num_of_additional_roads>0){
          for($r=0;$r<$num_of_additional_roads;$r++){
            
            $road_number = strtoupper($_REQUEST['additional_road_no'.$r]);
            $road_plot_cnt = $_REQUEST['additional_road_plot_cnt'.$r];
            $from_to_plot_cnt = $_REQUEST['from_to_plot_cnt_foradditional'.$r];

            for($ft=0;$ft<$from_to_plot_cnt;$ft++){
              $from_plotno_road = strtoupper($_REQUEST['additional_from_plotno_road'.$r.'_'.$ft]);
              $to_plotno_road = strtoupper($_REQUEST['additional_to_plotno_road'.$r.'_'.$ft]);

              $letters = "/^[A-Za-z]$/";
              $suffix = "/^[0-9]+[^a-zA-Z0-9]*[a-zA-Z]$/";
              $prefix = "/^[a-zA-Z][^a-zA-Z0-9]*[0-9]+$/";
              $specialChars ="/[`!@#$%^&*()_\-+=\[\]{};':\\|,.<>\/?~ ]+/";
              $re_for_alphabet = "/([a-zA-Z]+)/";
              $re_for_digits = "/(\d+)/";

              if($from_plotno_road!="" && $from_plotno_road!=null){

                $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `road_no`, `plot_start_no`, `plot_end_no`, `user_id`) VALUES (?,?,?,?,?)");
                $stmt_plot->bind_param("isssi",$ind_estate_id,$road_number,$from_plotno_road,$to_plotno_road,$user_id);
                $Resp=$stmt_plot->execute();
                $stmt_plot->close();

                if(is_numeric($from_plotno_road) && is_numeric($to_plotno_road)){
                  $type = "numeric";
                }
                else if(preg_match($letters,$from_plotno_road) && preg_match($letters,$to_plotno_road)){
                  $type = "alphabet";
                }
                else if(preg_match($prefix,$from_plotno_road) && preg_match($prefix,$to_plotno_road)){
                  $type = "prefix";
                }
                else if(preg_match($suffix,$from_plotno_road) && preg_match($suffix,$to_plotno_road)){
                  $type = "suffix";
                }


                if($type=="numeric"){
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
                        "Completion_Date" => "",
                        "Term_Loan_Amount" => "",
                        "CC_Loan_Amount" => "",
                        "Under_Process_Bank" => "",
                        "Under_Process_Branch" => "",
                        "Term_Loan_Amount_In_Process" => "",
                        "Under_Process_Date" => "",
                        "ROI" => "",
                        "Colletral" => "",
                        "Consultant" => "",
                        "Sanctioned_Bank" => "",
                        "Bank_Branch" => "",
                        "DOS" => "",
                        "TL_Amount" => "",
                        "Sactioned_Loan_Consultant" => "",
                        "category_type" => "",
                        "Remarks" => ""
                      ),
                      "inq_submit" => "Submit",
                      "bad_lead_reason" => "",
                      "bad_lead_reason_remark" => "",
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

                    $Resp = tbl_tdrawdata_insert($json,$user_id);
                    
                    company_plot_insert($p,$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                  }
                }
                else if($type=="alphabet") {
                  $from_plot_upper = strtoupper($from_plotno_road);
                  $to_plot_upper = strtoupper($to_plotno_road);
                  $from_plot_ascii = ord($from_plot_upper);
                  $to_plot_ascii = ord($to_plot_upper);
                  for($p=$from_plot_ascii;$p<=$to_plot_ascii;$p++){
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
                        "Completion_Date" => "",
                        "Term_Loan_Amount" => "",
                        "CC_Loan_Amount" => "",
                        "Under_Process_Bank" => "",
                        "Under_Process_Branch" => "",
                        "Term_Loan_Amount_In_Process" => "",
                        "Under_Process_Date" => "",
                        "ROI" => "",
                        "Colletral" => "",
                        "Consultant" => "",
                        "Sanctioned_Bank" => "",
                        "Bank_Branch" => "",
                        "DOS" => "",
                        "TL_Amount" => "",
                        "Sactioned_Loan_Consultant" => "",
                        "category_type" => "",
                        "Remarks" => ""
                      ),
                      "inq_submit" => "Submit",
                      "bad_lead_reason" => "",
                      "bad_lead_reason_remark" => "",
                      "Image" => "",
                      "Constitution" => "",
                      "Status" => "",
                      "plot_details" => Array(
                        Array(
                        "Plot_No" => chr($p),
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

                    $Resp = tbl_tdrawdata_insert($json,$user_id);
                    
                    company_plot_insert(chr($p),$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                  }
                }
                else if($type=="prefix") {

                  preg_match($re_for_digits,$from_plotno_road,$from_plot_number);
                  preg_match($re_for_alphabet,$from_plotno_road,$from_plot_alphabet);
                  preg_match($specialChars,$from_plotno_road,$from_plot_char);

                  preg_match($re_for_digits,$to_plotno_road,$to_plot_number);
                  preg_match($re_for_alphabet,$to_plotno_road,$to_plot_alphabet);
                  preg_match($specialChars,$to_plotno_road,$to_plot_char);

                  if($to_plot_number[0]>=$from_plot_number[0] && $to_plot_alphabet[0]==$from_plot_alphabet[0]){
                    // number increment
                    for($p=$from_plot_number[0];$p<=$to_plot_number[0];$p++){
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
                          "Completion_Date" => "",
                          "Term_Loan_Amount" => "",
                          "CC_Loan_Amount" => "",
                          "Under_Process_Bank" => "",
                          "Under_Process_Branch" => "",
                          "Term_Loan_Amount_In_Process" => "",
                          "Under_Process_Date" => "",
                          "ROI" => "",
                          "Colletral" => "",
                          "Consultant" => "",
                          "Sanctioned_Bank" => "",
                          "Bank_Branch" => "",
                          "DOS" => "",
                          "TL_Amount" => "",
                          "Sactioned_Loan_Consultant" => "",
                          "category_type" => "",
                          "Remarks" => ""
                        ),
                        "inq_submit" => "Submit",
                        "bad_lead_reason" => "",
                        "bad_lead_reason_remark" => "",
                        "Image" => "",
                        "Constitution" => "",
                        "Status" => "",
                        "plot_details" => Array(
                          Array(
                          "Plot_No" => strtoupper($from_plot_alphabet[0]).$from_plot_char[0].$p,
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

                      $Resp = tbl_tdrawdata_insert($json,$user_id);
                      
                      company_plot_insert((strtoupper($from_plot_alphabet[0]).$from_plot_char[0].$p),$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                    }
                  }
                  else if($to_plot_number[0]==$from_plot_number[0] && strtoupper($to_plot_alphabet[0])>=strtoupper($from_plot_alphabet[0])){
                    // alphabet increment
                    for($p=ord(strtoupper($from_plot_alphabet[0]));$p<=ord(strtoupper($to_plot_alphabet[0]));$p++){
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
                          "Completion_Date" => "",
                          "Term_Loan_Amount" => "",
                          "CC_Loan_Amount" => "",
                          "Under_Process_Bank" => "",
                          "Under_Process_Branch" => "",
                          "Term_Loan_Amount_In_Process" => "",
                          "Under_Process_Date" => "",
                          "ROI" => "",
                          "Colletral" => "",
                          "Consultant" => "",
                          "Sanctioned_Bank" => "",
                          "Bank_Branch" => "",
                          "DOS" => "",
                          "TL_Amount" => "",
                          "Sactioned_Loan_Consultant" => "",
                          "category_type" => "",
                          "Remarks" => ""
                        ),
                        "inq_submit" => "Submit",
                        "bad_lead_reason" => "",
                        "bad_lead_reason_remark" => "",
                        "Image" => "",
                        "Constitution" => "",
                        "Status" => "",
                        "plot_details" => Array(
                          Array(
                          "Plot_No" => chr($p).$from_plot_char[0].$from_plot_number[0],
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

                      $Resp = tbl_tdrawdata_insert($json,$user_id);
                      
                      company_plot_insert((chr($p).$from_plot_char[0].$from_plot_number[0]),$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                    }
                  }
                }
                else if($type=="suffix") {
                  preg_match($re_for_digits,$from_plotno_road,$from_plot_number);
                  preg_match($re_for_alphabet,$from_plotno_road,$from_plot_alphabet);
                  preg_match($specialChars,$from_plotno_road,$from_plot_char);

                  preg_match($re_for_digits,$to_plotno_road,$to_plot_number);
                  preg_match($re_for_alphabet,$to_plotno_road,$to_plot_alphabet);
                  preg_match($specialChars,$to_plotno_road,$to_plot_char);

                  if($to_plot_number[0]>=$from_plot_number[0] && $to_plot_alphabet[0]==$from_plot_alphabet[0]){
                    // number increment
                    for($p=$from_plot_number[0];$p<=$to_plot_number[0];$p++){
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
                          "Completion_Date" => "",
                          "Term_Loan_Amount" => "",
                          "CC_Loan_Amount" => "",
                          "Under_Process_Bank" => "",
                          "Under_Process_Branch" => "",
                          "Term_Loan_Amount_In_Process" => "",
                          "Under_Process_Date" => "",
                          "ROI" => "",
                          "Colletral" => "",
                          "Consultant" => "",
                          "Sanctioned_Bank" => "",
                          "Bank_Branch" => "",
                          "DOS" => "",
                          "TL_Amount" => "",
                          "Sactioned_Loan_Consultant" => "",
                          "category_type" => "",
                          "Remarks" => ""
                        ),
                        "inq_submit" => "Submit",
                        "bad_lead_reason" => "",
                        "bad_lead_reason_remark" => "",
                        "Image" => "",
                        "Constitution" => "",
                        "Status" => "",
                        "plot_details" => Array(
                          Array(
                          "Plot_No" => $p.$from_plot_char[0].strtoupper($from_plot_alphabet[0]),
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

                      $Resp = tbl_tdrawdata_insert($json,$user_id);
                      
                      company_plot_insert(($p.$from_plot_char[0]).strtoupper($from_plot_alphabet[0]),$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                    }
                  }
                  else if($to_plot_number[0]==$from_plot_number[0] && strtoupper($to_plot_alphabet[0])>=strtoupper($from_plot_alphabet[0])){
                    // alphabet increment
                    for($p=ord(strtoupper($from_plot_alphabet[0]));$p<=ord(strtoupper($to_plot_alphabet[0]));$p++){
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
                          "Completion_Date" => "",
                          "Term_Loan_Amount" => "",
                          "CC_Loan_Amount" => "",
                          "Under_Process_Bank" => "",
                          "Under_Process_Branch" => "",
                          "Term_Loan_Amount_In_Process" => "",
                          "Under_Process_Date" => "",
                          "ROI" => "",
                          "Colletral" => "",
                          "Consultant" => "",
                          "Sanctioned_Bank" => "",
                          "Bank_Branch" => "",
                          "DOS" => "",
                          "TL_Amount" => "",
                          "Sactioned_Loan_Consultant" => "",
                          "category_type" => "",
                          "Remarks" => ""
                        ),
                        "inq_submit" => "Submit",
                        "bad_lead_reason" => "",
                        "bad_lead_reason_remark" => "",
                        "Image" => "",
                        "Constitution" => "",
                        "Status" => "",
                        "plot_details" => Array(
                          Array(
                          "Plot_No" => $from_plot_number[0].$from_plot_char[0].chr($p),
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

                      $Resp = tbl_tdrawdata_insert($json,$user_id);
                      
                      company_plot_insert(($from_plot_number[0].$from_plot_char[0].chr($p)),$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
                    }
                  }
                }
              }
            }

            if($from_plotno_road!="" && $from_plotno_road!=null){
              // for additional plot in road wise
              if($road_plot_cnt>0){
                for($c=0;$c<$road_plot_cnt;$c++){
                  /*$additional_plotno = strtoupper($_REQUEST['additional_plotno_new_road'.$r.'_'.$c]);
                  if($additional_plotno!="" && $additional_plotno!=null){*/
                  if(isset($_REQUEST['additional_plotno_new_road'.$r.'_'.$c]))
                  {
                      $additional_plotno = strtoupper($_REQUEST['additional_plotno_new_road'.$r.'_'.$c]);
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
                        "Completion_Date" => "",
                        "Term_Loan_Amount" => "",
                        "CC_Loan_Amount" => "",
                        "Under_Process_Bank" => "",
                        "Under_Process_Branch" => "",
                        "Term_Loan_Amount_In_Process" => "",
                        "Under_Process_Date" => "",
                        "ROI" => "",
                        "Colletral" => "",
                        "Consultant" => "",
                        "Sanctioned_Bank" => "",
                        "Bank_Branch" => "",
                        "DOS" => "",
                        "TL_Amount" => "",
                        "Sactioned_Loan_Consultant" => "",
                        "category_type" => "",
                        "Remarks" => ""
                      ),
                      "inq_submit" => "Submit",
                      "bad_lead_reason" => "",
                      "bad_lead_reason_remark" => "",
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

                    $Resp = tbl_tdrawdata_insert($json,$user_id);
                    
                    company_plot_insert($additional_plotno,$floor_no,$road_number,$plot_id,$ind_estate_id,$user_id);
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
    header("location:unassigned_estate_plotting.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:unassigned_estate_plotting.php");
  }
}
?>

<h4 class="fw-bold py-3 mb-4">Unassigned Estate Master (For Plotting)</h4>

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


<!-- Modal For Assign Estate For Plotting -->
<div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Assign Estate For Plotting</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div id="assign_estate_modal"></div>
    </div>
  </div>
</div>

<!-- /modal-->

<!-- Modal For Estate Plotting -->
<div class="modal fade" id="plotting_modalCenter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Estate Plotting</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div id="plotting_modal"></div>
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
              <th>Action</th>  
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php 
              // estates whose plotting is remaining and is not assigned to anyone for plotting
              $stmt_list = $obj->con1->prepare("SELECT * FROM `tbl_industrial_estate` WHERE state_id='GUJARAT' and city_id='SURAT' and id not in (SELECT industrial_estate_id FROM `pr_add_industrialestate_details`) and id not in (SELECT industrial_estate_id FROM `assign_estate` WHERE action='estate_plotting')");
              $stmt_list->execute();
              $result = $stmt_list->get_result();
              $stmt_list->close();
              $i=1;

              while($data=mysqli_fetch_array($result))
              {
            ?>

            <tr>
              <td><input type="checkbox" id="estate_id_<?php echo $data["id"] ?>" onclick="show_assign(this.value)" name="estate_id" value="<?php echo $data["id"] ?>" class="call-checkbox"/></td>
              <td><?php echo $i?></td>
              <td><?php echo $data["state_id"] ?></td>
              <td><?php echo $data["city_id"] ?></td>
              <td><?php echo $data["taluka"] ?></td>
              <td><?php echo $data["area_id"] ?></td>
              <td><?php echo $data["industrial_estate"] ?></td>
              <td>
                <a href="javascript:editdata('<?php echo $data["id"]?>','<?php echo base64_encode($data["state_id"]) ?>','<?php echo base64_encode($data["city_id"]) ?>','<?php echo base64_encode($data["taluka"]) ?>','<?php echo base64_encode($data["area_id"]) ?>','<?php echo base64_encode($data["industrial_estate"]) ?>','<?php echo $user_id ?>');"><i class="bx bx-edit-alt me-1"></i> </a>
                <a href="javascript:assign_estate();" id="assign_<?php echo $data["id"] ?>" hidden>Assign</a>
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
  
// for assign estate for plotting
  $(document).ready(function() {
    $('.js-example-basic-multiple').select2();
  });

  function change_for_required(){
    if($('#Fake').is(':checked') || $('#Duplicate').is(':checked')){
      $('#series_div').attr("hidden",true);
      $('#road_div').attr("hidden",true);

      $('#series_wise').attr("required",false);
      $('#road_wise').attr("required",false);
      $('#img').attr("required",false);
      $('#from_plotno').attr("required",false);
      $('#to_plotno').attr("required",false);
      $("[id^='additional_plotno']").attr("required",false);

      $("[id^='from_roadno']").attr("required",false);
      $("[id^='to_roadno']").attr("required",false);
      $("[id^='road_no']").attr("required",false);
      $("[id^='from_plotno_road']").attr("required",false);
      $("[id^='to_plotno_road']").attr("required",false);
      $("[id^='additional_plotno_road']").attr("required",false);
      $("[id^='additional_road_no']").attr("required",false);
      $("[id^='additional_from_plotno_road']").attr("required",false);
      $("[id^='additional_to_plotno_road']").attr("required",false);
      $("[id^='additional_plotno_new_road']").attr("required",false);
    }
    else{
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
  }

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
        url: "ajaxdata.php?action=assign_estate_forplotting",
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


// for estate plotting
  function editdata(id,state,city,taluka,area,ind_estate,user_id) {    

    $('#plotting_modalCenter').modal('toggle');
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=add_plotting_oldestate",
      data: "estate_id="+id,
      cache: false,
      success: function(result){
        $('#plotting_modal').html('');
        $('#plotting_modal').html(result);
      }
    });
  }

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
          document.getElementById('btnupdate').disabled = false;
        }
        else
        {
          $('#imgdiv').html("Please Select Image Only");
          document.getElementById('btnupdate').disabled = true;
        }
      }
    }
  }

  // for road wise 
  function get_plot_adding_options()
  {
    $('#road_alert_div').html('');
    document.getElementById('btnupdate').disabled = false;
    $('#road_plots_div').html("");
    var from_roadno=$('#from_roadno').val();
    var to_roadno=$('#to_roadno').val();
    if(from_roadno!="" && to_roadno!=""){
    
      var letters = /^[A-Za-z]$/;
      var suffix = /^[0-9]+[^a-zA-Z0-9]*[a-zA-Z]$/;
      var prefix = /^[a-zA-Z][^a-zA-Z0-9]*[0-9]+$/;
      let specialChars =/[`!@#$%^&*()_\-+=\[\]{};':"\\|,.<>\/?~ ]+/;
      let re_for_alphabet = /([a-zA-Z]+)/;
      let re_for_digits = /(\d+)/;

      let from_road_number = "";
      let from_road_alphabet = "";
      let to_road_number = "";
      let to_road_alphabet = "";
      let from_road_char = "";
      let to_road_char = "";
      let from_road_ascii = "";
      let to_road_ascii = "";

      if($.isNumeric(from_roadno) && $.isNumeric(to_roadno)){
        type = "numeric";
        if(parseInt(from_roadno)>parseInt(to_roadno)){
          $('#road_alert_div').html('Invalid Input!');
          document.getElementById('btnupdate').disabled = true;
          return false;
        }
      }
      else if(from_roadno.match(letters) && to_roadno.match(letters))
      {
        type = "alphabet";
        from_roadno = from_roadno.toUpperCase();
        to_roadno = to_roadno.toUpperCase();
        from_road_ascii = from_roadno.charCodeAt(0);
        to_road_ascii = to_roadno.charCodeAt(0);
        
        if(from_road_ascii>to_road_ascii){
          $('#road_alert_div').html('Invalid Input!');
          document.getElementById('btnupdate').disabled = true;
          return false;
        }
      }
      else if(from_roadno.match(suffix) && to_roadno.match(suffix))
      {
        from_road_number = from_roadno.match(re_for_digits);
        from_road_alphabet = from_roadno.match(re_for_alphabet);
        from_road_char = (from_roadno.match(specialChars)==null)?['']:from_roadno.match(specialChars);

        to_road_number = to_roadno.match(re_for_digits);
        to_road_alphabet = to_roadno.match(re_for_alphabet);
        to_road_char = (to_roadno.match(specialChars)==null)?['']:to_roadno.match(specialChars);

        let pattern = new RegExp(from_road_alphabet, 'gi');
        let result = pattern.test(to_road_alphabet);

        if(result && from_road_char[0]==to_road_char[0] && parseInt(to_road_number)>=parseInt(from_road_number)){
          type = "suffix_number";
        }
        else if(parseInt(to_road_number)==parseInt(from_road_number) && to_road_alphabet[0].charCodeAt(0)>=from_road_alphabet[0].charCodeAt(0) && from_road_char[0]==to_road_char[0]){
          from_road_alphabet = from_road_alphabet[0].toUpperCase();
          to_road_alphabet = to_road_alphabet[0].toUpperCase();
          from_road_ascii = from_road_alphabet.charCodeAt(0);
          to_road_ascii = to_road_alphabet.charCodeAt(0);
          type = "suffix_alphabet";
        }
        else{
          $('#road_alert_div').html('Invalid Input!');
          document.getElementById('btnupdate').disabled = true;
          return false;
        }
      }
      else if(from_roadno.match(prefix) && to_roadno.match(prefix))
      {
        from_road_number = from_roadno.match(re_for_digits);
        from_road_alphabet = from_roadno.match(re_for_alphabet);
        from_road_char = (from_roadno.match(specialChars)==null)?['']:from_roadno.match(specialChars);

        to_road_number = to_roadno.match(re_for_digits);
        to_road_alphabet = to_roadno.match(re_for_alphabet);
        to_road_char = (to_roadno.match(specialChars)==null)?['']:to_roadno.match(specialChars);
        
        let pattern = new RegExp(from_road_alphabet, 'gi');
        let result = pattern.test(to_road_alphabet);
        
        if(result && from_road_char[0]==to_road_char[0] && parseInt(to_road_number)>=parseInt(from_road_number)){
          type = "prefix_number";
        }
        else if(parseInt(to_road_number)==parseInt(from_road_number) && to_road_alphabet[0].charCodeAt(0)>=from_road_alphabet[0].charCodeAt(0) && from_road_char[0]==to_road_char[0]){
          from_road_alphabet = from_road_alphabet[0].toUpperCase();
          to_road_alphabet = to_road_alphabet[0].toUpperCase();
          from_road_ascii = from_road_alphabet.charCodeAt(0);
          to_road_ascii = to_road_alphabet.charCodeAt(0);
          type = "prefix_alphabet";
        }
        else{
          $('#road_alert_div').html('Invalid Input!');
          document.getElementById('btnupdate').disabled = true;
          return false;
        }
      }
      else{
        $('#road_alert_div').html('Invalid Input!');
        document.getElementById('btnupdate').disabled = true;
        return false;
      }


      $('#road_plots_div').append('<div><a href="javascript:get_additional_road()" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i>  Add Additional Road No.</a><br/></div><br/><div id="additional_road_div"></div>');
      
      var count=0;

      switch(type){
        case "numeric" : 
          for (let i=parseInt(from_roadno); i<=parseInt(to_roadno); i++) {
            
            var road_count=$('#road_cnt').val();
            $('#road_plots_div').append('<div id="road_plot_div_'+count+'"><div><label class="form-label" for="basic-default-fullname">Add Plot : </label></br><label class="form-label" for="basic-default-company">Road No.</label><input type="text" name="road_no'+count+'" id="road_no'+count+'" value="'+i+'" class="form-control" readonly required></div><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">From (Plot No.)</label><input type="text" class="form-control" name="from_plotno_road'+count+'_0" id="from_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div class="col mb-3"><label class="form-label" for="basic-default-fullname">To (Plot No.)</label><a href="javascript:from_to_plot('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i></a><input type="hidden" name="from_to_plot_cnt'+count+'" id="from_to_plot_cnt'+count+'" value="1"/><input type="text" class="form-control" name="to_plotno_road'+count+'_0" id="to_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div id="plot_alert_div'+count+'_0" style="color:red"></div><div id="from_to_plots_div'+count+'"></div></div><a href="javascript:additional_plot_road('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i> Add Additional Plot</a></br> <input type="hidden" name="road_plot_cnt'+count+'" id="road_plot_cnt'+count+'" value="0"/></div><div id="additional_road_plots_div'+count+'"></div><hr></div>');

            $('#road_cnt').val(parseInt(count));

            count++;
          }
          break;   
        case "prefix_number":
          for (let i=parseInt(from_road_number); i<=parseInt(to_road_number); i++) {
            
            var road_count=$('#road_cnt').val();
            $('#road_plots_div').append('<div id="road_plot_div_'+count+'"><div><label class="form-label" for="basic-default-fullname">Add Plot : </label></br><label class="form-label" for="basic-default-company">Road No.</label><input type="text" name="road_no'+count+'" id="road_no'+count+'" value="'+from_road_alphabet[0].toUpperCase()+from_road_char[0]+i+'" class="form-control" readonly required></div><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">From (Plot No.)</label><input type="text" class="form-control" name="from_plotno_road'+count+'_0" id="from_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div class="col mb-3"><label class="form-label" for="basic-default-fullname">To (Plot No.)</label><a href="javascript:from_to_plot('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i></a><input type="hidden" name="from_to_plot_cnt'+count+'" id="from_to_plot_cnt'+count+'" value="1"/><input type="text" class="form-control" name="to_plotno_road'+count+'_0" id="to_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div id="plot_alert_div'+count+'_0" style="color:red"></div><div id="from_to_plots_div'+count+'"></div></div><a href="javascript:additional_plot_road('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i> Add Additional Plot</a></br> <input type="hidden" name="road_plot_cnt'+count+'" id="road_plot_cnt'+count+'" value="0"/></div><div id="additional_road_plots_div'+count+'"></div><hr></div>');

            $('#road_cnt').val(parseInt(count));

            count++;
          }
          break;
        case "prefix_alphabet" : 
          for (let i=parseInt(from_road_ascii); i<=parseInt(to_road_ascii); i++) {
            
            var road_count=$('#road_cnt').val();
            $('#road_plots_div').append('<div id="road_plot_div_'+count+'"><div><label class="form-label" for="basic-default-fullname">Add Plot : </label></br><label class="form-label" for="basic-default-company">Road No.</label><input type="text" name="road_no'+count+'" id="road_no'+count+'" value="'+String.fromCharCode(i)+from_road_char[0]+from_road_number[0]+'" class="form-control" readonly required></div><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">From (Plot No.)</label><input type="text" class="form-control" name="from_plotno_road'+count+'_0" id="from_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div class="col mb-3"><label class="form-label" for="basic-default-fullname">To (Plot No.)</label><a href="javascript:from_to_plot('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i></a><input type="hidden" name="from_to_plot_cnt'+count+'" id="from_to_plot_cnt'+count+'" value="1"/><input type="text" class="form-control" name="to_plotno_road'+count+'_0" id="to_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div id="plot_alert_div'+count+'_0" style="color:red"></div><div id="from_to_plots_div'+count+'"></div></div><a href="javascript:additional_plot_road('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i> Add Additional Plot</a></br> <input type="hidden" name="road_plot_cnt'+count+'" id="road_plot_cnt'+count+'" value="0"/></div><div id="additional_road_plots_div'+count+'"></div><hr></div>');

            $('#road_cnt').val(parseInt(count));

            count++;
          }
          break;
        case "suffix_number" :
          for (let i=parseInt(from_road_number); i<=parseInt(to_road_number); i++) {
            
            var road_count=$('#road_cnt').val();
            $('#road_plots_div').append('<div id="road_plot_div_'+count+'"><div><label class="form-label" for="basic-default-fullname">Add Plot : </label></br><label class="form-label" for="basic-default-company">Road No.</label><input type="text" name="road_no'+count+'" id="road_no'+count+'" value="'+i+from_road_char[0]+from_road_alphabet[0].toUpperCase()+'" class="form-control" readonly required></div><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">From (Plot No.)</label><input type="text" class="form-control" name="from_plotno_road'+count+'_0" id="from_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div class="col mb-3"><label class="form-label" for="basic-default-fullname">To (Plot No.)</label><a href="javascript:from_to_plot('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i></a><input type="hidden" name="from_to_plot_cnt'+count+'" id="from_to_plot_cnt'+count+'" value="1"/><input type="text" class="form-control" name="to_plotno_road'+count+'_0" id="to_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div id="plot_alert_div'+count+'_0" style="color:red"></div><div id="from_to_plots_div'+count+'"></div></div><a href="javascript:additional_plot_road('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i> Add Additional Plot</a></br> <input type="hidden" name="road_plot_cnt'+count+'" id="road_plot_cnt'+count+'" value="0"/></div><div id="additional_road_plots_div'+count+'"></div><hr></div>');

            $('#road_cnt').val(parseInt(count));

            count++;
          }
          break;
        case "suffix_alphabet" :
          for (let i=parseInt(from_road_ascii); i<=parseInt(to_road_ascii); i++) {
            
            var road_count=$('#road_cnt').val();
            $('#road_plots_div').append('<div id="road_plot_div_'+count+'"><div><label class="form-label" for="basic-default-fullname">Add Plot : </label></br><label class="form-label" for="basic-default-company">Road No.</label><input type="text" name="road_no'+count+'" id="road_no'+count+'" value="'+from_road_number[0]+from_road_char[0]+String.fromCharCode(i)+'" class="form-control" readonly required></div><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">From (Plot No.)</label><input type="text" class="form-control" name="from_plotno_road'+count+'_0" id="from_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div class="col mb-3"><label class="form-label" for="basic-default-fullname">To (Plot No.)</label><a href="javascript:from_to_plot('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i></a><input type="hidden" name="from_to_plot_cnt'+count+'" id="from_to_plot_cnt'+count+'" value="1"/><input type="text" class="form-control" name="to_plotno_road'+count+'_0" id="to_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div id="plot_alert_div'+count+'_0" style="color:red"></div><div id="from_to_plots_div'+count+'"></div></div><a href="javascript:additional_plot_road('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i> Add Additional Plot</a></br> <input type="hidden" name="road_plot_cnt'+count+'" id="road_plot_cnt'+count+'" value="0"/></div><div id="additional_road_plots_div'+count+'"></div><hr></div>');

            $('#road_cnt').val(parseInt(count));

            count++;
          }
          break;
        case "alphabet" :
          for (let i=parseInt(from_road_ascii); i<=parseInt(to_road_ascii); i++) {
            
            var road_count=$('#road_cnt').val();
            $('#road_plots_div').append('<div id="road_plot_div_'+count+'"><div><label class="form-label" for="basic-default-fullname">Add Plot : </label></br><label class="form-label" for="basic-default-company">Road No.</label><input type="text" name="road_no'+count+'" id="road_no'+count+'" value="'+String.fromCharCode(i)+'" class="form-control" readonly required></div><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">From (Plot No.)</label><input type="text" class="form-control" name="from_plotno_road'+count+'_0" id="from_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div class="col mb-3"><label class="form-label" for="basic-default-fullname">To (Plot No.)</label><a href="javascript:from_to_plot('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i></a><input type="hidden" name="from_to_plot_cnt'+count+'" id="from_to_plot_cnt'+count+'" value="1"/><input type="text" class="form-control" name="to_plotno_road'+count+'_0" id="to_plotno_road'+count+'_0" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+count+'_0\')" required /></div><div id="plot_alert_div'+count+'_0" style="color:red"></div><div id="from_to_plots_div'+count+'"></div></div><a href="javascript:additional_plot_road('+count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i> Add Additional Plot</a></br> <input type="hidden" name="road_plot_cnt'+count+'" id="road_plot_cnt'+count+'" value="0"/></div><div id="additional_road_plots_div'+count+'"></div><hr></div>');

            $('#road_cnt').val(parseInt(count));

            count++;
          }
          break;  
      }
      return true;
    }
  }

  function check_input(from_plot,to_plot,div,x){
    $('#'+div+x).html('');
    document.getElementById('btnupdate').disabled = false;

    from_plotno = $('#'+from_plot+x).val();
    to_plotno = $('#'+to_plot+x).val();
    
    if(from_plotno!="" && to_plotno!=""){
      var letters = /^[A-Za-z]$/;
      var suffix = /^[0-9]+[^a-zA-Z0-9]*[a-zA-Z]$/;
      var prefix = /^[a-zA-Z][^a-zA-Z0-9]*[0-9]+$/;
      let specialChars =/[`!@#$%^&*()_\-+=\[\]{};':"\\|,.<>\/?~ ]+/;
      let re_for_alphabet = /([a-zA-Z]+)/;
      let re_for_digits = /(\d+)/;

      if($.isNumeric(from_plotno) && $.isNumeric(to_plotno)){
        type = "numeric";
        if(parseInt(from_plotno)>parseInt(to_plotno)){
          $('#'+div+x).html('Invalid Input!');
          document.getElementById('btnupdate').disabled = true;
          return false;
        }
      }
      else if(from_plotno.match(letters) && to_plotno.match(letters))
      {
        type = "alphabet";
        from_plotno = from_plotno.toUpperCase();
        to_plotno = to_plotno.toUpperCase();
        from_plot_ascii = from_plotno.charCodeAt(0);
        to_plot_ascii = to_plotno.charCodeAt(0);
        
        if(from_plot_ascii>to_plot_ascii){
          $('#'+div+x).html('Invalid Input!');
          document.getElementById('btnupdate').disabled = true;
          return false;
        }
      }
      else if(from_plotno.match(suffix) && to_plotno.match(suffix))
      {
        from_plot_number = from_plotno.match(re_for_digits);
        from_plot_alphabet = from_plotno.match(re_for_alphabet);
        from_plot_char = (from_plotno.match(specialChars)==null)?['']:from_plotno.match(specialChars);

        to_plot_number = to_plotno.match(re_for_digits);
        to_plot_alphabet = to_plotno.match(re_for_alphabet);
        to_plot_char = (to_plotno.match(specialChars)==null)?['']:to_plotno.match(specialChars);
        
        let pattern = new RegExp(from_plot_alphabet, 'gi');
        let result = pattern.test(to_plot_alphabet);

        if(result && from_plot_char[0]==to_plot_char[0] && parseInt(to_plot_number)>=parseInt(from_plot_number)){
          type = "suffix_number"; 
        }
        else if(parseInt(to_plot_number)==parseInt(from_plot_number) && to_plot_alphabet[0].charCodeAt(0)>=from_plot_alphabet[0].charCodeAt(0) && from_plot_char[0]==to_plot_char[0]){
          type = "suffix_alphabet"; 
        }
        else{
          $('#'+div+x).html('Invalid Input!');
          document.getElementById('btnupdate').disabled = true;
          return false;
        }
      }
      else if(from_plotno.match(prefix) && to_plotno.match(prefix))
      {
        from_plot_number = from_plotno.match(re_for_digits);
        from_plot_alphabet = from_plotno.match(re_for_alphabet);
        from_plot_char = (from_plotno.match(specialChars)==null)?['']:from_plotno.match(specialChars);

        to_plot_number = to_plotno.match(re_for_digits);
        to_plot_alphabet = to_plotno.match(re_for_alphabet);
        to_plot_char = (to_plotno.match(specialChars)==null)?['']:to_plotno.match(specialChars);
        
        let pattern = new RegExp(from_plot_alphabet, 'gi');
        let result = pattern.test(to_plot_alphabet);
        if(result && from_plot_char[0]==to_plot_char[0] && parseInt(to_plot_number)>=parseInt(from_plot_number)){
          type = "prefix_number"; 
        }
        else if(parseInt(to_plot_number)==parseInt(from_plot_number) && to_plot_alphabet[0].charCodeAt(0)>=from_plot_alphabet[0].charCodeAt(0) && from_plot_char[0]==to_plot_char[0]){
          type = "prefix_alphabet"; 
        }
        else{
          $('#'+div+x).html('Invalid Input!');
          document.getElementById('btnupdate').disabled = true;
          return false;
        }
      }
      else{
        $('#'+div+x).html('Invalid Input!');
        document.getElementById('btnupdate').disabled = true;
        return false;
      }
    }
  }

  // for additional road no 
  function get_additional_road(){
   var additional_road_count=$('#additional_road_cnt').val();
        
    $('#additional_road_div').append('<div id="additional_road_plot_div_'+additional_road_count+'"><div><label class="form-label" for="basic-default-fullname">Add Plot : </label><a href="javascript:remove_field(\'additional_road_plot_div_'+additional_road_count+'\')" class="text-right"><i class="bx bxs-message-square-minus bx-sm"></i></a></br><label class="form-label" for="basic-default-company">Road No.</label><input type="text" name="additional_road_no'+additional_road_count+'" id="additional_road_no'+additional_road_count+'" class="form-control" required></div><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">From (Plot No.)</label><input type="text" class="form-control" name="additional_from_plotno_road'+additional_road_count+'_0" id="additional_from_plotno_road'+additional_road_count+'_0" onblur="check_input(\'additional_from_plotno_road\',\'additional_to_plotno_road\',\'additional_plot_alert_div\',\''+additional_road_count+'_0\')" required /></div><div class="col mb-3"><label class="form-label" for="basic-default-fullname">To (Plot No.)</label><a href="javascript:from_to_plot_foradditional('+additional_road_count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i></a><input type="hidden" name="from_to_plot_cnt_foradditional'+additional_road_count+'" id="from_to_plot_cnt_foradditional'+additional_road_count+'" value="1"/><input type="text" class="form-control" name="additional_to_plotno_road'+additional_road_count+'_0" id="additional_to_plotno_road'+additional_road_count+'_0" onblur="check_input(\'additional_from_plotno_road\',\'additional_to_plotno_road\',\'additional_plot_alert_div\',\''+additional_road_count+'_0\')" required /></div><div id="additional_plot_alert_div'+additional_road_count+'_0" style="color:red"></div><div id="from_to_plots_div_foradditional'+additional_road_count+'"></div> <a href="javascript:additional_plot_new_road('+additional_road_count+')" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i> Add Additional Plot</a></br> <input type="hidden" name="additional_road_plot_cnt'+additional_road_count+'" id="additional_road_plot_cnt'+additional_road_count+'" value="0"/></div><div id="additional_extra_road_plots_div'+additional_road_count+'"></div><hr></div>');
   
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

  // for road wise - from and to plotting
  function from_to_plot(x)
  {
    var road_count=x;
    var plot_count=$('#from_to_plot_cnt'+road_count).val();
        
    $('#from_to_plots_div'+road_count).append('<div id="from_to_plot_div'+road_count+'_'+plot_count+'"><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">From (Plot No.)</label><input type="text" class="form-control" name="from_plotno_road'+road_count+'_'+plot_count+'" id="from_plotno_road'+road_count+'_'+plot_count+'" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+road_count+'_'+plot_count+'\')" required /></div><div class="col mb-3"><label class="form-label" for="basic-default-fullname">To (Plot No.)</label><a href="javascript:remove_from_to_plot(\'from_to_plot_div'+road_count+'_'+plot_count+'\')" class="text-right"><i class="bx bxs-message-square-minus bx-sm"></i></a></br><input type="text" class="form-control" name="to_plotno_road'+road_count+'_'+plot_count+'" id="to_plotno_road'+road_count+'_'+plot_count+'" onblur="check_input(\'from_plotno_road\',\'to_plotno_road\',\'plot_alert_div\',\''+road_count+'_'+plot_count+'\')" required /></div><div id="plot_alert_div'+road_count+'_'+plot_count+'" style="color:red"></div></div></div>');

    $('#from_to_plot_cnt'+road_count).val(parseInt(plot_count)+1);

    //.remove() to remove div
  }
  function remove_from_to_plot(div)
  {
    $('#'+div).remove();
  }

  // for road wise - from and to plotting (for additional road)
  function from_to_plot_foradditional(x)
  {
    var road_count=x;
    var plot_count=$('#from_to_plot_cnt_foradditional'+road_count).val();
        
    $('#from_to_plots_div_foradditional'+road_count).append('<div id="from_to_plot_div_foradditional'+road_count+'_'+plot_count+'"><div class="row"><div class="col mb-3"><label class="form-label" for="basic-default-fullname">From (Plot No.)</label><input type="text" class="form-control" name="additional_from_plotno_road'+road_count+'_'+plot_count+'" id="additional_from_plotno_road'+road_count+'_'+plot_count+'" onblur="check_input(\'additional_from_plotno_road\',\'additional_to_plotno_road\',\'additional_plot_alert_div\',\''+road_count+'_'+plot_count+'\')" required /></div><div class="col mb-3"><label class="form-label" for="basic-default-fullname">To (Plot No.)</label><a href="javascript:remove_from_to_plot_foradditional(\'from_to_plot_div_foradditional'+road_count+'_'+plot_count+'\')" class="text-right"><i class="bx bxs-message-square-minus bx-sm"></i></a></br><input type="text" class="form-control" name="additional_to_plotno_road'+road_count+'_'+plot_count+'" id="additional_to_plotno_road'+road_count+'_'+plot_count+'" onblur="check_input(\'additional_from_plotno_road\',\'additional_to_plotno_road\',\'additional_plot_alert_div\',\''+road_count+'_'+plot_count+'\')" required /></div><div id="additional_plot_alert_div'+road_count+'_'+plot_count+'" style="color:red"></div></div></div>');

    $('#from_to_plot_cnt_foradditional'+road_count).val(parseInt(plot_count)+1);

    //.remove() to remove div
  }
  function remove_from_to_plot_foradditional(div)
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