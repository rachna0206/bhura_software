<?php
  include("header.php");

$user_id = $_SESSION["id"];

// State List
$stmt_state_list = $obj->con1->prepare("select DISTINCT(state) from all_taluka where state='GUJARAT'");
$stmt_state_list->execute();
$state_result = $stmt_state_list->get_result();
$stmt_state_list->close();

// City List
$stmt_city = $obj->con1->prepare("select DISTINCT(district) from all_taluka where state='GUJARAT' and district='SURAT'");
$stmt_city->execute();
$city_result = $stmt_city->get_result();
$stmt_city->close();

// Taluka List
$stmt_taluka = $obj->con1->prepare("select DISTINCT(subdistrict) from all_taluka where state='GUJARAT' and district='SURAT'");
$stmt_taluka->execute();
$taluka_result = $stmt_taluka->get_result();
$stmt_taluka->close();

// insert data
if(isset($_REQUEST['btnsubmit']))
{
  $state = $_REQUEST['state'];
  $city = $_REQUEST['city'];
  $taluka = $_REQUEST['taluka'];
  $area = $_REQUEST['area'];
  $industrial_estate = $_REQUEST['industrial_estate'];
  $plotting_pattern = $_REQUEST['plotting_pattern'];
  $verify_status = "Verified";

  $floor_no = "0";
  $plot_id = "1";

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

  $description='';

  try
  {
    $stmt = $obj->con1->prepare("INSERT INTO `tbl_industrial_estate`(`state_id`, `city_id`, `taluka`, `area_id`, `industrial_estate`,`description`) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssssss",$state,$city,$taluka,$area,$industrial_estate,$description);
    $Resp=$stmt->execute();
    $stmt->close();
    $insert_id = mysqli_insert_id($obj->con1);

    $stmt_detail = $obj->con1->prepare("INSERT INTO `pr_add_industrialestate_details`(`industrial_estate_id`, `plotting_pattern`, `location`, `user_id`,`status`) VALUES (?,?,?,?,?)");
    $stmt_detail->bind_param("issis",$insert_id,$plotting_pattern,$location,$user_id,$verify_status);
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
      $stmt_image->bind_param("ss",$insert_id,$SubImageName);
      $Resp=$stmt_image->execute();
      $stmt_image->close();
    }

    // if plotting pattern = Series
    if($plotting_pattern=='Series'){
      $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `plot_start_no`, `plot_end_no`, `user_id`) VALUES (?,?,?,?)");
      $stmt_plot->bind_param("issi",$insert_id,$from_plotno,$to_plotno,$user_id);
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

        company_plot_insert($p,$floor_no,$road_number,$plot_id,$insert_id,$user_id);
      }

      // for additional plot in series wise
      if($series_plot_cnt>0){
        for($c=0;$c<$series_plot_cnt;$c++){
          $additional_plotno = strtoupper($_REQUEST['additional_plotno'.$c]);

          if($additional_plotno!="" && $additional_plotno!=null){
            
            $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `plot_start_no`, `user_id`) VALUES (?,?,?)");
            $stmt_plot->bind_param("isi",$insert_id,$additional_plotno,$user_id);
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

            company_plot_insert($additional_plotno,$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
          $from_plotno_road = strtoupper($_REQUEST['from_plotno_road'.$i.'_'.$ft]);
          $to_plotno_road = strtoupper($_REQUEST['to_plotno_road'.$i.'_'.$ft]);

          $letters = "/^[A-Za-z]$/";
          $suffix = "/^[0-9]+[^a-zA-Z0-9]*[a-zA-Z]$/";
          $prefix = "/^[a-zA-Z][^a-zA-Z0-9]*[0-9]+$/";
          $specialChars ="/[`!@#$%^&*()_\-+=\[\]{};':\\|,.<>\/?~ ]+/";
          $re_for_alphabet = "/([a-zA-Z]+)/";
          $re_for_digits = "/(\d+)/";

          if($from_plotno_road!="" && $from_plotno_road!=null){

            $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `road_no`, `plot_start_no`, `plot_end_no`, `user_id`) VALUES (?,?,?,?,?)");
            $stmt_plot->bind_param("isssi",$insert_id,$road_number,$from_plotno_road,$to_plotno_road,$user_id);
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
                
                company_plot_insert($p,$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
                
                company_plot_insert(chr($p),$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
                  
                  company_plot_insert((strtoupper($from_plot_alphabet[0]).$from_plot_char[0].$p),$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
                  
                  company_plot_insert((chr($p).$from_plot_char[0].$from_plot_number[0]),$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
                  
                  company_plot_insert(($p.$from_plot_char[0]).strtoupper($from_plot_alphabet[0]),$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
                  
                  company_plot_insert(($from_plot_number[0].$from_plot_char[0].chr($p)),$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
                $stmt_plot->bind_param("issi",$insert_id,$road_number,$additional_plotno,$user_id);
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
                
                company_plot_insert($additional_plotno,$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
              $stmt_plot->bind_param("isssi",$insert_id,$road_number,$from_plotno_road,$to_plotno_road,$user_id);
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
                  
                  company_plot_insert($p,$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
                  
                  company_plot_insert(chr($p),$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
                    
                    company_plot_insert((strtoupper($from_plot_alphabet[0]).$from_plot_char[0].$p),$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
                    
                    company_plot_insert((chr($p).$from_plot_char[0].$from_plot_number[0]),$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
                    
                    company_plot_insert(($p.$from_plot_char[0]).strtoupper($from_plot_alphabet[0]),$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
                    
                    company_plot_insert(($from_plot_number[0].$from_plot_char[0].chr($p)),$floor_no,$road_number,$plot_id,$insert_id,$user_id);
                  }
                }
              }
            }
          }

          if($from_plotno_road!="" && $from_plotno_road!=null){
            // for additional plot in road wise
            if($road_plot_cnt>0){
              for($c=0;$c<$road_plot_cnt;$c++){
                $additional_plotno = strtoupper($_REQUEST['additional_plotno_new_road'.$r.'_'.$c]);
                if($additional_plotno!="" && $additional_plotno!=null){

                  $stmt_plot = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `road_no`, `plot_start_no`, `user_id`) VALUES (?,?,?,?)");
                  $stmt_plot->bind_param("issi",$insert_id,$road_number,$additional_plotno,$user_id);
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
                  
                  company_plot_insert($additional_plotno,$floor_no,$road_number,$plot_id,$insert_id,$user_id);
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
    header("location:add_industrial_estate.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:add_industrial_estate.php");
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

          <?php if(in_array($user_id, $admin)){ ?> 
            <div class="mb-3"><a href="estate_plotting_report.php">View</a></div>
          <?php } ?>

          </div>
          <div class="card-body">
            <form method="post" enctype="multipart/form-data">                       
              <input type="hidden" name="ttId" id="ttId">
              <input type="hidden" name="ind_estate_id" id="ind_estate_id">
        
                <div class="mb-3">
                  <label class="form-label" for="basic-default-fullname">State</label>
                  <select name="state" id="state" class="form-control" required>
                <!--    <option value="">Select State</option>  -->
            <?php while($state_list=mysqli_fetch_array($state_result)){ ?>
                <option value="<?php echo $state_list["state"] ?>" selected><?php echo $state_list["state"] ?></option>
            <?php } ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="basic-default-company">City</label>
                  <select name="city" id="city" class="form-control" required>
                    <option value="">Select City</option>
            <?php while($city_list=mysqli_fetch_array($city_result)){ ?>
                <option value="<?php echo $city_list["district"] ?>" selected><?php echo $city_list["district"] ?></option>
            <?php } ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="basic-default-company">Taluka</label>
                  <select name="taluka" id="taluka" onchange="areaList_ade(this.value,city.value,state.value)" class="form-control" required>
                    <option value="">Select Taluka</option>
            <?php while($taluka_list=mysqli_fetch_array($taluka_result)){ ?>
                <option value="<?php echo $taluka_list["subdistrict"] ?>"><?php echo $taluka_list["subdistrict"] ?></option>
            <?php } ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="basic-default-company">Area</label>
                  <select name="area" id="area" class="form-control" required>
                    <option value="">Select Area</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="basic-default-company">Industrial Estate</label>
                  <input type="text" class="form-control" name="industrial_estate" id="industrial_estate" onblur ="checkIndEstate(this.value,area.value,taluka.value,city.value,state.value)" required />
                  <div id="indestate_alert_div" class="text-danger"></div>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="basic-default-fullname">Plotting Pattern</label>
                  <div class="form-check form-check-inline mt-3">
                    <input class="form-check-input" type="radio" name="plotting_pattern" id="series_wise" value="Series" onclick="getplotform()" required>
                    <label class="form-check-label" for="inlineRadio1">Series Wise</label>
                  </div>
                  <div class="form-check form-check-inline mt-3">
                    <input class="form-check-input" type="radio" name="plotting_pattern" id="road_wise" value="Road" onclick="getplotform()" required>
                    <label class="form-check-label" for="inlineRadio1">Road Wise</label>
                  </div>
                </div>

                <div id="series_div" hidden>
                  <div class="row">
                    <div class="col mb-3">
                      <label class="form-label" for="basic-default-fullname">From (Plot No.)</label>
                      <input type="text" class="form-control" pattern="^[0-9]*$" name="from_plotno" id="from_plotno" required />
                    </div>
                    <div class="col mb-3">
                      <label class="form-label" for="basic-default-fullname">To (Plot No.)</label>
                      <input type="text" class="form-control" name="to_plotno" id="to_plotno" pattern="^[0-9]*$" required />
                    </div>
                  </div>
                  <a href="javascript:additional_plot_series(this.value)" class="text-right"><i class="bx bxs-add-to-queue bx-sm"></i> Add Additional Plot</a></br></br>
                  <input type="hidden" name="series_plot_cnt" id="series_plot_cnt" value="0"/>
                  <div id="additional_series_plots_div"></div>  
                </div>
                
                <div id="road_div" hidden>
                  <div class="row">
                    <div class=" col mb-3">
                      <label class="form-label" for="basic-default-fullname">From (Road No.)</label>
                      <input type="text" class="form-control" name="from_roadno" id="from_roadno" onblur="return get_plot_adding_options()" required />
                    </div>  
                    <div class="col mb-3">
                      <label class="form-label" for="basic-default-fullname">To (Road No.)</label>
                      <input type="text" class="form-control" name="to_roadno" id="to_roadno" onblur="return get_plot_adding_options()" required />
                    </div>
                    <div id="road_alert_div" style="color:red"></div>
                  </div>

                  <input type="hidden" name="road_cnt" id="road_cnt" value="1"/>
                  <input type="hidden" name="additional_road_cnt" id="additional_road_cnt" value="0"/>
                  <div id="road_plots_div"></div>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="basic-default-fullname">Image</label>
                  <input type="file" class="form-control" onchange="readURL(this)" name="img[]" id="img" multiple required />
                  <div id="preview_image_div"></div>
                  <div id="imgdiv" style="color:red"></div>
              <!--    <input type="hidden" name="himage" id="himage" /> -->
                </div>

              <button type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary">Save</button>
          
              <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button>

            </form>
          </div>
        </div>
      </div>
      
    </div>
  
<!-- Next Modal -->
<div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">New Modal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" enctype="multipart/form-data"><div class="modal-body" >
          <div class="row">
            <div class="mb-3" >
              <label class="form-label" for="basic-default-fullname">Industrial Estate : </label>
              <label class="form-label" for="basic-default-fullname" id="estate_list_modal"></label>
            </div>
            
            <div class="mb-3" >
              <label class="form-label" for="basic-default-fullname">The above Industrial Estate exists<br/> Do you still want to continue ?</label>
            </div>
      
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="allow_submit()" data-bs-dismiss="modal">Yes</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- /modal-->

  <!-- / Content -->
  <script src="assets/js/ui-popover.js"></script>

<script type="text/javascript">
  
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

  function checkIndEstate(ind_estate,area,taluka,city,state)
  {
    if(ind_estate!=""){
      var id=$('#ttId').val();
      $.ajax({
        async: true,
        type: "POST",
        url: "ajaxdata.php?action=checkIndEstate",
        data: "ind_estate="+ind_estate+"&area="+area+"&taluka="+taluka+"&city="+city+"&state="+state+"&id="+id,
        cache: false,
        success: function(result){
          var data = result.split("@@@@@");
          if(data[0]>0)
          {
            $('#indestate_alert_div').html('Industrial Estate already exist!');
            document.getElementById('btnsubmit').disabled = true;
            if(data[1]!='no'){
              $('#modalCenter').modal('toggle');
              $('#estate_list_modal').html('');
              $('#estate_list_modal').html(data[1]);
            }
          }
          else
          {
            $('#indestate_alert_div').html('');
            document.getElementById('btnsubmit').disabled = false;
            //document.getElementById('btnupdate').disabled = false;
          }
        }
      });
    }
  }

  function allow_submit(){
    $('#indestate_alert_div').html('');
    document.getElementById('btnsubmit').disabled = false;
  }

  function areaList_ade(taluka,city,state){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=areaList_ade",
      data: "taluka="+taluka+"&city="+city+"&state_name="+state,
      cache: false,
      success: function(result){
        $('#area').html('');
        $('#area').append(result);
      }
    });
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
          document.getElementById('btnsubmit').disabled = false;
        }
        else
        {
          $('#imgdiv').html("Please Select Image Only");
          document.getElementById('btnsubmit').disabled = true;
        }
      }
    }
  }

  // for road wise 
  function get_plot_adding_options()
  {
    $('#road_alert_div').html('');
    document.getElementById('btnsubmit').disabled = false;
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
          document.getElementById('btnsubmit').disabled = true;
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
          document.getElementById('btnsubmit').disabled = true;
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
          document.getElementById('btnsubmit').disabled = true;
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
          document.getElementById('btnsubmit').disabled = true;
          return false;
        }
      }
      else{
        $('#road_alert_div').html('Invalid Input!');
        document.getElementById('btnsubmit').disabled = true;
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
    document.getElementById('btnsubmit').disabled = false;

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
          document.getElementById('btnsubmit').disabled = true;
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
          document.getElementById('btnsubmit').disabled = true;
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
          document.getElementById('btnsubmit').disabled = true;
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
          document.getElementById('btnsubmit').disabled = true;
          return false;
        }
      }
      else{
        $('#'+div+x).html('Invalid Input!');
        document.getElementById('btnsubmit').disabled = true;
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