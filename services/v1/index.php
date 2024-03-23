<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
//including the required files
require_once '../include/DbOperation.php';
require '../libs/Slim/Slim.php';

date_default_timezone_set("Asia/Kolkata");
\Slim\Slim::registerAutoloader();

//require_once('../../PHPMailer_v5.1/class.phpmailer.php');

$app = new \Slim\Slim();


/* *
 * user login
 * Parameters: userid, password
 * Method: POST
 * 
 */

$app->post('/login', function () use ($app) {
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $email = $data_request->userid;
    $password = $data_request->password;
    $device_type = isset($data_request->device_type)?$data_request->device_type:"";
    $token = isset($data_request->device_token)?$data_request->device_token:"";
    $headers = apache_request_headers();
    $api_key = $headers['Authorization'];
    
    $db = new DbOperation();
    $data = array();
    
    $response = array();
    if (isset($headers['Authorization'])) 
    {
        $api_key = $headers['Authorization'];
    
        if ($db->assignorLogin($email, $password)) 
        {

            $assignor_data = $db->assignor_data($email);
        
            if($db->check_loggedin_user($assignor_data['id'])==0){
                $res = $db->insert_device_type($device_type, $token, $assignor_data['id'],$api_key);
                $response = array();
                foreach ($assignor_data as $key => $value) {
                    $response[$key]= $value;
                }
                $data["data"]=$response;
                $data["success"] = true;
                $data["message"]="Successfully Logged In!";
            }
            else{
                $data["data"]=null; 
                $data["success"] = false;
                $data["message"] = "User Already Logged In";       
            }
        }
        else
        {
            $data["data"]=null; 
            $data["success"] = false;
            $data["message"] = "Invalid username or password";
        }
    }
    else
    {
        $data["data"]=null; 
         $data["success"] = false;
        $data["message"] = "Device Id is misssing";
        
    }


    echoResponse(200, $data);
});

/* *
 * user logout
 * Parameters: uid, device_token,type
 * Method: POST
 * 
 */

$app->post('/logout','authenticateUser', function () use ($app) {
    verifyRequiredParams(array('data'));

    $data = array();

    $data_request = json_decode($app->request->post('data'));
    $uid = $data_request->uid;
    $device_token = $data_request->device_token;
    $type = $data_request->device_type;

    $db = new DbOperation();
    $res = $db->logout($uid);

    if ($res == 1) {

        $data["data"]=null; 
        $data["success"] = false;
        $data['message'] = "Logged out";
        echoResponse(201, $data);
    } else {

        $data["data"]=null; 
        $data['error_code'] = 1;
        $data['message'] = "Please try again";
        echoResponse(201, $data);
    }

});

/* *
 * assigned estates
 * Parameters: userid
 * Method: POST
 * 
 */

$app->post('/assigned_estates','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result=$db->assigned_estates($userid);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Result Found";
        $data['success'] = false;
    }

    echoResponse(200, $data);
});

/* *
 * add estate plotting (for old estates)
 * Parameters: 
 * Method: POST
 * 
 */

$app->post('/add_plotting_old_estates','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;
    $verify_status = $data_request->verify_status;
    $industrial_estate_id = $data_request->industrial_estate_id;

    if($verify_status=='Fake' || $verify_status=='Duplicate'){
        $result=$db->insert_estate_status($userid,$verify_status,$industrial_estate_id);
    }
    else{

        $plotting_pattern = $data_request->plotting_pattern;   
        $floor = "0";
        $plot_id = "1";     
        if($plotting_pattern=='Series')
        {
            $state = $data_request->state;
            $city = $data_request->city;        
            $taluka = $data_request->taluka;
            $area = $data_request->area;
            $industrial_estate = $data_request->industrial_estate;
            $location = $data_request->location;
            
            $from_plotno = $data_request->from_plotno;
            $to_plotno = $data_request->to_plotno;
            $additional_plot_cnt = $data_request->additional_plot_cnt;

            $result=$db->estate_plotting_series($userid,$verify_status,$industrial_estate_id,$plotting_pattern,$state,$city,$taluka,$area,$industrial_estate,$location,$from_plotno,$to_plotno);

            // image upload
            foreach ($_FILES["est_images"]['name'] as $key => $value)
              { 
                // rename for estate images       
                if($_FILES["est_images"]['name'][$key]!=""){
                  $PicSubImage = $_FILES["est_images"]["name"][$key];
                  if (file_exists("../../industrial_estate_image/" . $PicSubImage )) {
                    $i = 0;
                    $SubImageName = $PicSubImage;
                    $Arr = explode('.', $SubImageName);
                    $SubImageName = $Arr[0] . $i . "." . $Arr[1];
                    while (file_exists("../../industrial_estate_image/" . $SubImageName)) {
                        $i++;
                        $SubImageName = $Arr[0] . $i . "." . $Arr[1];
                    }
                  } else {
                    $SubImageName = $PicSubImage;
                  }
                  $SubImageTemp = $_FILES["est_images"]["tmp_name"][$key];
                 
                  // sub images qry
                  move_uploaded_file($SubImageTemp, "../../industrial_estate_image/".$SubImageName);
                }
               
                // add subimages
                $subImg=$db->pr_estate_subimages($industrial_estate_id,$SubImageName);
                
              }
            
            $road_number="";
            $resp_plot=$db->pr_estate_roadplot($industrial_estate_id,$road_number,$from_plotno,$to_plotno,$userid);

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
               
          
              $json = json_encode($cp);
              
              $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

              $road_no = NULL;
              $resp_company_plot = $db->company_plot_insert($p,$floor,$road_no,$plot_id,$industrial_estate_id,$userid);
              
            }

            // for additional plot in series wise
                if($additional_plot_cnt>0){
                    $additional_plot=$data_request->additional_plot;
                  for($c=0;$c<$additional_plot_cnt;$c++){
                    $additional_plotno = strtoupper($additional_plot[$c]->additional_plotno_road);

                    if($additional_plotno!="" || $additional_plotno!=null){
                    $road_number=NULL;
                    $to_plotno_road=NULL;
                    $resp_plot=$db->pr_estate_roadplot($industrial_estate_id,$road_number,$additional_plotno,$to_plotno_road,$userid);
                      

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
                                         
                    $json = json_encode($cp);
                    
                    $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                    $resp_company_plot = $db->company_plot_insert($additional_plotno,$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
                    
                      
                    }
                  }
                }

        }
        else if($plotting_pattern=='Road'){
            
            $state = $data_request->state;
            $city = $data_request->city;        
            $taluka = $data_request->taluka;
            $area = $data_request->area;
            $industrial_estate = $data_request->industrial_estate;
            $location = $data_request->location;
            $images = isset($data_request->images)?$data_request->images:"";
            $from_roadno = $data_request->from_roadno;
            $to_roadno = $data_request->to_roadno;
            $road_plotting =isset($data_request->road)?$data_request->road:"";
            $additional_road_plotting = $data_request->additional_road;
            $road_cnt=$data_request->road_count;

            $estate_roadplot=$data_request->estate_roadplot;
            

            $result=$db->estate_plotting_road($userid,$verify_status,$industrial_estate_id,$plotting_pattern,$state,$city,$taluka,$area,$industrial_estate,$location,$images,$from_roadno,$to_roadno,$road_plotting,$additional_road_plotting,$road_cnt);

            if($result)
            {
                // multiple estate images 
              foreach ($_FILES["est_images"]['name'] as $key => $value)
              { 
                // rename for estate images       
                if($_FILES["est_images"]['name'][$key]!=""){
                  $PicSubImage = $_FILES["est_images"]["name"][$key];
                  if (file_exists("../../industrial_estate_image/" . $PicSubImage )) {
                    $i = 0;
                    $SubImageName = $PicSubImage;
                    $Arr = explode('.', $SubImageName);
                    $SubImageName = $Arr[0] . $i . "." . $Arr[1];
                    while (file_exists("../../industrial_estate_image/" . $SubImageName)) {
                        $i++;
                        $SubImageName = $Arr[0] . $i . "." . $Arr[1];
                    }
                  } else {
                    $SubImageName = $PicSubImage;
                  }
                  $SubImageTemp = $_FILES["est_images"]["tmp_name"][$key];
                 
                  // sub images qry
                  move_uploaded_file($SubImageTemp, "../../industrial_estate_image/".$SubImageName);
                }
               
                // add subimages
                $subImg=$db->pr_estate_subimages($industrial_estate_id,$SubImageName);
                
              }

              for($i=0;$i<$road_cnt;$i++){

                  $road_number = $estate_roadplot[$i]->road_no;
                  $from_to_plots = $estate_roadplot[$i]->plots;
                  $road_plot_cnt = $estate_roadplot[$i]->road_plot_cnt;
                  $additional_plot=$estate_roadplot[$i]->additional_plot;

                  $from_to_plots_cnt = count($from_to_plots);

                  for($ft=0;$ft<$from_to_plots_cnt;$ft++){
                      $from_plotno_road = strtoupper($from_to_plots[$ft]->from_plot_no);
                      $to_plotno_road = strtoupper($from_to_plots[$ft]->to_plot_no);

                      $letters = "/^[A-Za-z]$/";
                      $suffix = "/^[0-9]+[^a-zA-Z0-9]*[a-zA-Z]$/";
                      $prefix = "/^[a-zA-Z][^a-zA-Z0-9]*[0-9]+$/";
                      $specialChars ="/[`!@#$%^&*()_\-+=\[\]{};':\\|,.<>\/?~ ]+/";
                      $re_for_alphabet = "/([a-zA-Z]+)/";
                      $re_for_digits = "/(\d+)/";

                      if($from_plotno_road!="" || $from_plotno_road!=null){
                    
                        $resp_plot=$db->pr_estate_roadplot($industrial_estate_id,$road_number,$from_plotno_road,$to_plotno_road,$userid);
                        
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
                             
                            $json = json_encode($cp);
                    
                            $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                            $resp_company_plot = $db->company_plot_insert($p,$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
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

                            $json = json_encode($cp);
                    
                            $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                            $resp_company_plot = $db->company_plot_insert(chr($p),$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
                          }
                        }
                        else if($type=="prefix") {

                          preg_match($re_for_digits,$from_plotno_road,$from_plot_number);
                          preg_match($re_for_alphabet,$from_plotno_road,$from_plot_alphabet);
                          preg_match($specialChars,$from_plotno_road,$from_plot_char);

                          preg_match($re_for_digits,$to_plotno_road,$to_plot_number);
                          preg_match($re_for_alphabet,$to_plotno_road,$to_plot_alphabet);
                          preg_match($specialChars,$to_plotno_road,$to_plot_char);

                          if($from_plot_char==[]){
                            $from_plot_char[0]="";  
                          }

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
                               
                                $json = json_encode($cp);
                    
                                $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                                $resp_company_plot = $db->company_plot_insert((strtoupper($from_plot_alphabet[0]).$from_plot_char[0].$p),$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
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
                               
                                $json = json_encode($cp);
                    
                                $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                                $resp_company_plot = $db->company_plot_insert((chr($p).$from_plot_char[0].$from_plot_number[0]),$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
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

                          if($from_plot_char==[]){
                            $from_plot_char[0]="";  
                          }

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
                               
                                $json = json_encode($cp);
                    
                                $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                                $resp_company_plot = $db->company_plot_insert(($p.$from_plot_char[0].strtoupper($from_plot_alphabet[0])),$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
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
                               
                                $json = json_encode($cp);
                    
                                $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                                $resp_company_plot = $db->company_plot_insert(($from_plot_number[0].$from_plot_char[0].chr($p)),$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
                            }
                          }
                        }
  
                      }
                  }

                      if($from_plotno_road!="" && $from_plotno_road!=null){
                        // for additional plot in road wise
                        if($road_plot_cnt>0){
                          for($c=0;$c<$road_plot_cnt;$c++){
                            $additional_plotno = strtoupper($additional_plot[$c]->additional_plotno_road);  
                            if($additional_plotno!="" || $additional_plotno!=null){
                                //$to_plot="";
                                $to_plot=NULL;
                                $resp_plot=$db->pr_estate_roadplot($industrial_estate_id,$road_number,$additional_plotno,$to_plot,$userid);
                              

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
                            
                              $json = json_encode($cp);
                               
                              
                              $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                              $resp_company_plot = $db->company_plot_insert($additional_plotno,$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
                              
                            }
                          }
                        }
                      }
              }

            }


        }
    }

    

    if($result>0)
    {
        $data['message'] = "Data added successfully";
        $data['success'] = true;
    }
    else
    {
        $data['message'] = "An error occurred";
        $data['success'] = false;
    }
    echoResponse(200, $data);
});


$app->post('/add_industrial_estates','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $data_request = json_decode($app->request->post('data'));
   // print_r($data_request);
    $userid = $data_request->userid;
    $verify_status = $data_request->verify_status;
    
    $state = $data_request->state;
    $city = $data_request->city;        
    $taluka = $data_request->taluka;
    $area = $data_request->area;
    $industrial_estate = $data_request->industrial_estate;
    $location = $data_request->location;
    $plotting_pattern = $data_request->plotting_pattern;   
    $floor = "0";
    $plot_id = "1";   
    $description='';
    $res_add_estate=$db->add_industrial_estate($state,$city,$taluka,$area,$industrial_estate,$description,$plotting_pattern,$location,$userid,$verify_status);
    if($res_add_estate>0)
    {
        $industrial_estate_id = $res_add_estate;
        // add multiple estate images
        foreach ($_FILES["est_images"]['name'] as $key => $value)
        { 
          // rename estate images       
          if($_FILES["est_images"]['name'][$key]!=""){
            $PicSubImage = $_FILES["est_images"]["name"][$key];
            if (file_exists("../../industrial_estate_image/" . $PicSubImage )) {
              $i = 0;
              $SubImageName = $PicSubImage;
              $Arr = explode('.', $SubImageName);
              $SubImageName = $Arr[0] . $i . "." . $Arr[1];
              while (file_exists("../../industrial_estate_image/" . $SubImageName)) {
                  $i++;
                  $SubImageName = $Arr[0] . $i . "." . $Arr[1];
              }
            } else {
              $SubImageName = $PicSubImage;
            }
            $SubImageTemp = $_FILES["est_images"]["tmp_name"][$key];
           
            
            move_uploaded_file($SubImageTemp, "../../industrial_estate_image/".$SubImageName);
          }
          
          $subImg=$db->pr_estate_subimages($res_add_estate,$SubImageName);

          
        }
        if($plotting_pattern=='Series')
        {
            $state = $data_request->state;
            $city = $data_request->city;        
            $taluka = $data_request->taluka;
            $area = $data_request->area;
            $industrial_estate = $data_request->industrial_estate;
            $location = $data_request->location;
            
            $from_plotno = $data_request->from_plotno;
            $to_plotno = $data_request->to_plotno;
            $additional_plot_cnt = $data_request->additional_plot_cnt;

            $result=$db->estate_plotting_series($userid,$verify_status,$industrial_estate_id,$plotting_pattern,$state,$city,$taluka,$area,$industrial_estate,$location,$from_plotno,$to_plotno);

            $road_number="";
            $resp_plot=$db->pr_estate_roadplot($industrial_estate_id,$road_number,$from_plotno,$to_plotno,$userid);


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
               
          
              $json = json_encode($cp);
              
              $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

              $road_no = NULL;
              $resp_company_plot = $db->company_plot_insert($p,$floor,$road_no,$plot_id,$industrial_estate_id,$userid);
              
            }

            // for additional plot in series wise
                if($additional_plot_cnt>0){
                    $additional_plot=$data_request->additional_plot;
                  for($c=0;$c<$additional_plot_cnt;$c++){
                    $additional_plotno = $additional_plot[$c]->additional_plotno_road;

                    if($additional_plotno!="" || $additional_plotno!=null){

                    //$road_number="";
                    //$to_plotno_road="";
                    $road_number=NULL;
                    $to_plotno_road=NULL;
                    $resp_plot=$db->pr_estate_roadplot($industrial_estate_id,$road_number,$additional_plotno,$to_plotno_road,$userid);
                      

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
                                         
                    $json = json_encode($cp);
                    
                    $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                    $resp_company_plot = $db->company_plot_insert($additional_plotno,$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
                    
                      
                    }
                  }
                }

        }
		else if($plotting_pattern=='Road'){
            
            $state = $data_request->state;
            $city = $data_request->city;        
            $taluka = $data_request->taluka;
            $area = $data_request->area;
            $industrial_estate = $data_request->industrial_estate;
            $location = $data_request->location;
            $images = isset($data_request->images)?$data_request->images:"";
            $from_roadno = $data_request->from_roadno;
            $to_roadno = $data_request->to_roadno;
            $road_plotting =isset($data_request->road)?$data_request->road:"";
            $additional_road_plotting = $data_request->additional_road;
            $road_cnt=$data_request->road_count;

            $estate_roadplot=$data_request->estate_roadplot;
            

            $result=$db->estate_plotting_road($userid,$verify_status,$industrial_estate_id,$plotting_pattern,$state,$city,$taluka,$area,$industrial_estate,$location,$images,$from_roadno,$to_roadno,$road_plotting,$additional_road_plotting,$road_cnt);

            if($result)
            {
              for($i=0;$i<$road_cnt;$i++){

                for($i=0;$i<$road_cnt;$i++){

                  $road_number = $estate_roadplot[$i]->road_no;
                  $from_to_plots = $estate_roadplot[$i]->plots;
                  $road_plot_cnt = $estate_roadplot[$i]->road_plot_cnt;
                  $additional_plot=$estate_roadplot[$i]->additional_plot;

                  $from_to_plots_cnt = count($from_to_plots);

                  for($ft=0;$ft<$from_to_plots_cnt;$ft++){
                      $from_plotno_road = strtoupper($from_to_plots[$ft]->from_plot_no);
                      $to_plotno_road = strtoupper($from_to_plots[$ft]->to_plot_no);

                      $letters = "/^[A-Za-z]$/";
                      $suffix = "/^[0-9]+[^a-zA-Z0-9]*[a-zA-Z]$/";
                      $prefix = "/^[a-zA-Z][^a-zA-Z0-9]*[0-9]+$/";
                      $specialChars ="/[`!@#$%^&*()_\-+=\[\]{};':\\|,.<>\/?~ ]+/";
                      $re_for_alphabet = "/([a-zA-Z]+)/";
                      $re_for_digits = "/(\d+)/";

                      if($from_plotno_road!="" || $from_plotno_road!=null){
                    
                        $resp_plot=$db->pr_estate_roadplot($industrial_estate_id,$road_number,$from_plotno_road,$to_plotno_road,$userid);
                        
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
                             
                            $json = json_encode($cp);
                    
                            $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                            $resp_company_plot = $db->company_plot_insert($p,$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
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

                            $json = json_encode($cp);
                    
                            $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                            $resp_company_plot = $db->company_plot_insert(chr($p),$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
                          }
                        }
                        else if($type=="prefix") {

                          preg_match($re_for_digits,$from_plotno_road,$from_plot_number);
                          preg_match($re_for_alphabet,$from_plotno_road,$from_plot_alphabet);
                          preg_match($specialChars,$from_plotno_road,$from_plot_char);

                          preg_match($re_for_digits,$to_plotno_road,$to_plot_number);
                          preg_match($re_for_alphabet,$to_plotno_road,$to_plot_alphabet);
                          preg_match($specialChars,$to_plotno_road,$to_plot_char);

                          if($from_plot_char==[]){
                            $from_plot_char[0]="";  
                          }

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
                               
                                $json = json_encode($cp);
                    
                                $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                                $resp_company_plot = $db->company_plot_insert((strtoupper($from_plot_alphabet[0]).$from_plot_char[0].$p),$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
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
                               
                                $json = json_encode($cp);
                    
                                $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                                $resp_company_plot = $db->company_plot_insert((chr($p).$from_plot_char[0].$from_plot_number[0]),$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
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

                          if($from_plot_char==[]){
                            $from_plot_char[0]="";  
                          }

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
                               
                                $json = json_encode($cp);
                    
                                $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                                $resp_company_plot = $db->company_plot_insert(($p.$from_plot_char[0].strtoupper($from_plot_alphabet[0])),$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
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
                               
                                $json = json_encode($cp);
                    
                                $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                                $resp_company_plot = $db->company_plot_insert(($from_plot_number[0].$from_plot_char[0].chr($p)),$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
                            }
                          }
                        }
  
                      }
                  }

                      if($from_plotno_road!="" && $from_plotno_road!=null){
                        // for additional plot in road wise
                        if($road_plot_cnt>0){
                          for($c=0;$c<$road_plot_cnt;$c++){
                            $additional_plotno = strtoupper($additional_plot[$c]->additional_plotno_road);  
                            if($additional_plotno!="" || $additional_plotno!=null){
                                //$to_plot="";
                                $to_plot=NULL;
                                $resp_plot=$db->pr_estate_roadplot($industrial_estate_id,$road_number,$additional_plotno,$to_plot,$userid);
                              

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
                            
                              $json = json_encode($cp);
                               
                              
                              $resp_tdrawdata=$db->tbl_tdrawdata($json,$userid);

                              $resp_company_plot = $db->company_plot_insert($additional_plotno,$floor,$road_number,$plot_id,$industrial_estate_id,$userid);
                              
                            }
                          }
                        }
                      }
                  }
                }

            }


        }
    }
    else
    {
        $data['message'] = "An error occurred! Data not inserted";
        $data['success'] = false;
    }
    

if($result>0)
    {
        $data['message'] = "Data added successfully";
        $data['success'] = true;
    }
    else
    {
        $data['message'] = "An error occurred";
        $data['success'] = false;
    }
    echoResponse(200, $data);
});


// get taluka list 
$app->post('/get_taluka_list','authenticateUser', function () use ($app) {
    
    //verifyRequiredParams(array('data'));
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_taluka_list();

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
 
    echoResponse(200, $data);
});

// get area list 
$app->post('/get_area_list','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $taluka = $data_request->taluka;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $state='GUJARAT';
    $city='SURAT';
    $result=$db->get_area_list($taluka,$state,$city);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
 
    echoResponse(200, $data);
});


//get Constitution list
$app->post('/get_constituion_list','authenticateUser', function () use ($app) {
    
    //verifyRequiredParams(array('data'));
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_constituion_list();

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }



    
    echoResponse(200, $data);
});

//get segment list
$app->post('/get_segment_list','authenticateUser', function () use ($app) {
    
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_segment_list();

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
   
    echoResponse(200, $data);
});


//get source type list
$app->post('/get_source_type_list','authenticateUser', function () use ($app) {
    
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $data["data"]["Direct From Master"] = array();
    $data["data"]["Our Associates"] = array();
    $data["data"]["New System"] = array();
    
    $result_master=$db->get_source_type_list();
    $result_associates=$db->get_associate_list();

    if(mysqli_num_rows($result_master)>0 || mysqli_num_rows($result_associates)>0){
        while ($row = $result_master->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
                $temp["reference"] = "source_master";
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data["data"]['Direct From Master'], $temp);
        }

        while ($row = $result_associates->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
                $temp["reference"] = "associate_master";
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data["data"]['Our Associates'], $temp);
        }
        $temp_new = array();
        $temp_new["name"] = "Lead Data";
        $temp_new["reference"] = "new_system";
        array_push($data["data"]['New System'], $temp_new);

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
  
    echoResponse(200, $data);
});

//get source name list
$app->post('/get_source_name_list','authenticateUser', function () use ($app) {
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $source_type = $data_request->source_type;
    $reference = $data_request->reference;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_source_name_list($source_type,$reference);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            if($row["source_name"]!=" - "){
                $temp = array();
                foreach ($row as $key => $value) {
                    $temp[$key] = $value;
                }
                $temp = array_map('utf8_encode', $temp);
                array_push($data['data'], $temp);
            }
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
  
    echoResponse(200, $data);
});

//get associate list
/*$app->post('/get_associate_list', function () use ($app) {
    
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_associate_list();

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
  
    echoResponse(200, $data);
});*/


//get reason list
$app->post('/get_reason_list','authenticateUser', function () use ($app) {
    
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_reason_list();

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
  
    echoResponse(200, $data);
});

/* *
 * assigned estates for company entry
 * Parameters: userid
 * Method: POST
 * 
 */

$app->post('/assigned_estates_for_company','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result=$db->assigned_estates_company($userid);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            $temp["industrial_estate_id"] = $row["industrial_estate_id"];
            $temp["industrial_estate"] = $row["industrial_estate"]." - ".$row["area_id"];
            $temp["taluka"] = $row["taluka"];
            // foreach ($row as $key => $value) {
            //     $temp[$key] = $value;
            // }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Result Found";
        $data['success'] = false;
    }

    echoResponse(200, $data);
});

// get Plot
$app->post('/get_plot','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $estate_id = $data_request->estate_id;
    $filter = $data_request->filter;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result_estate=$db->get_ind_estate($estate_id);
    $plotting_pattern = $result_estate['plotting_pattern'];
    if($plotting_pattern=="Road")
    {
        $res_road=$db->get_road_no($estate_id);
        if(mysqli_num_rows($res_road)>0){
            while ($row = $res_road->fetch_assoc()) {
                $temp = array();
                foreach ($row as $key => $value) {
                    $temp[$key] = $value;
                }
                $temp = array_map('utf8_encode', $temp);
                array_push($data['data'], $temp);
            }

            $data['message'] = "";
            $data['success'] = true;
        }
        else{
            $data['message'] = "No Data Found";
            $data['success'] = false;
        }

    }
    else if($plotting_pattern=="Series")
    {
        // (error handle) can have empty result set
        $res_plot=$db->get_plot_no($filter,$estate_id);
        if(!empty($res_plot))
        {
            $temp = array();
            while ($row = $res_plot->fetch_assoc()) {
                foreach($row as $key => $value){
                    $temp["plot_no"] = $value;
                }
                $temp = array_map('utf8_encode', $temp);
                array_push($data['data'], $temp);
            }
            
            $data['message'] = "";
            $data['success'] = true;
        }
        else
        {
            $data['message'] = "No Data Found";
            $data['success'] = false;
        }
    }
  
    echoResponse(200, $data);
});

// get floor
$app->post('/get_floor','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $estate_id = $data_request->estate_id;
    $plot_no=$data_request->plot_no;
    $road_no=isset($data_request->road_no)?$data_request->road_no:"";
    $filter=$data_request->filter;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result_estate=$db->get_ind_estate($estate_id);
    $plotting_pattern = $result_estate['plotting_pattern'];
    
    $res_floor=$db->get_plot_floor($plot_no,$road_no,$filter,$estate_id,$plotting_pattern);

    if(mysqli_num_rows($res_floor)>0)
    {
        $temp = array();
        while($row = mysqli_fetch_array($res_floor)){
            foreach($row as $key => $value){
                if($value=='0'){
                    $temp["floor_no"]="Ground Floor";
                }
                else{
                    $temp["floor_no"] = $value;
                }
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
        $data['message'] = "";
        $data['success'] = true;
    }
    else
    {
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
    
    echoResponse(200, $data);
});


// get road plots
$app->post('/get_road_plot','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $estate_id = $data_request->estate_id;
    $road_no=$data_request->road_no;
    $filter = $data_request->filter;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $res_plot=$db->get_road_plot($filter,$estate_id,$road_no);

    if(mysqli_num_rows($res_plot)>0)
    {
        // (error handle) can have empty result set
        $temp = array();
        while($row = mysqli_fetch_array($res_plot)){
            foreach($row as $key => $value){
                $temp["plot_no"] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
       
        $data['message'] = "";
        $data['success'] = true;
    }
    else
    {
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }

    echoResponse(200, $data);
});


// check gst number
$app->post('/check_gst','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $id = $data_request->id;
    $gst_no=$data_request->gst_no;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    if($gst_no!=""){
        $result=$db->check_gst($gst_no,$id);

        if(mysqli_num_rows($result)>0){
            
            $data_result = mysqli_fetch_array($result);
            $row_data=json_decode($data_result["raw_data"]);
            $plot_details=$row_data->plot_details; 
            if($plot_details[0]->Floor=='0'){
                $plot_no = $plot_details[0]->Plot_No.' ( Ground Floor ) ';  
            }
            else{
                $plot_no = $plot_details[0]->Plot_No.' ( ` No. - '.$plot_details[0]->Floor.' ) ';
            }

            $data['data'] = "";
            $data['message'] = "GST No. already exist!  You can add in Plot No. ".$plot_no;
            $data['success'] = false;
        }
        else{
            $data['data'] = "";
            $data['message'] = "";
            $data['success'] = true;
        }
    }
    else{
        $data['data'] = "";
        $data['message'] = "";
        $data['success'] = true;
    }

    echoResponse(200, $data);
});


// get company details
$app->post('/get_company_details','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $estate_id = $data_request->estate_id;
    $floor_no=$data_request->floor_no;
    $plot_no=$data_request->plot_no;
    $road_no=isset($data_request->road_no)?$data_request->road_no:"";

    $floor_no=($floor_no=="Ground Floor")?"0":$floor_no;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
  
    $result_estate=$db->get_ind_estate_data($estate_id);
   
    if($plot_no!="" && $floor_no!="")
    {
        // get plot details from pr_company_plots
        $res_pattern=$db->get_ind_estate($estate_id);

        $constitution = "";
        $image = "";
        $pr_comp_status = "";

        $res_company_plot=$db->get_pr_company_details($res_pattern['plotting_pattern'],$estate_id,$plot_no,$floor_no,$road_no);
        
        if($res_company_plot){
            $pr_comp_status = $res_company_plot["status"];

            if($res_company_plot["rawdata_id"]!="" || $res_company_plot["rawdata_id"]!=null){
                $plot=$db->get_rawdata($res_company_plot["rawdata_id"]);
                $row_data = json_decode($plot["raw_data"]);
                $post_fields = $row_data->post_fields;

                $reason = "";
                $status = "";

                // get company status (positive/negative/existing) from tbl_tdrawassign
                $rawassign_status = $db->get_tbl_tdrawassign($plot['id']);
                if(mysqli_num_rows($rawassign_status)>0){
                    $status_res = mysqli_fetch_array($rawassign_status);
                    if($status_res['stage']=="lead"){
                        $status = "Positive";   
                    }
                    else if($status_res['stage']=="badlead"){
                        $status = "Negative";
                    }
                    else{
                        $status = "Existing Client";
                    }
                }
                else{
                    $status = ($pr_comp_status!=null)?$pr_comp_status:"";
                }

                if($status=='Negative'){
                    $reason = $db->get_badlead_reason($plot['id']);
                }

                $details = Array (
                    "Id" => $plot["id"],
                    "Plot_Id" => $res_company_plot['plot_id'],
                    "Road_No" => $road_no,
                    "IndustrialEstate" => $post_fields->IndustrialEstate,
                    "Area" => $post_fields->Area,
                    "Plot_Status" => isset($res_company_plot['plot_status'])?$res_company_plot['plot_status']:"",
                    "Premise" => $post_fields->Premise,
                    "GST_No" => $post_fields->GST_No,
                    "Firm_Name" => $post_fields->Firm_Name,
                    "Contact_Name" => $post_fields->Contact_Name,
                    "Mobile_No" => $post_fields->Mobile_No,
                    "Constitution" => isset($res_company_plot['constitution'])?$res_company_plot['constitution']:"",
                    "Category" => $post_fields->Category,
                    "Segment" => $post_fields->Segment,
                    "Status" => $status,
                    "Reason" => $reason,
                    "source" => $post_fields->source,
                    "Source_Name" => $post_fields->Source_Name,
                    "Remarks" => $post_fields->Remarks,
                    "Image" => ($res_company_plot['image']=="")?"":"https://software.bhuraconsultancy.com/gst_image/".$res_company_plot['image'],
                    "Company_detail_id" => $res_company_plot["company_id"],
                    "Company_plot_id" => $res_company_plot["pid"],
                    "Loan_Sanction" => isset($post_fields->loan_applied)?$post_fields->loan_applied:"",
                    "Completion_Date" => isset($post_fields->Completion_Date)?date('d-m-Y',strtotime($post_fields->Completion_Date)):"",
                    "Existing_client_status" => $res_company_plot["existing_client_status"]
                );

                //if(($res_company_plot["pid"]!=null || $res_company_plot["pid"]!="") && $plot["id"]!=""){
                if($plot['id']!="" && $res_company_plot['plot_id']!="" && $post_fields->IndustrialEstate!="" && $post_fields->Area!="" && $res_company_plot['plot_status']!="" && $post_fields->Premise!="" && $post_fields->GST_No!="" && $post_fields->Firm_Name!="" && $post_fields->Contact_Name!="" && $post_fields->Mobile_No!="" && $res_company_plot['constitution']!="" && $post_fields->Category!="" && $post_fields->Segment!="" && $res_company_plot['status']!="" && $post_fields->source!="" && $post_fields->Source_Name!="" && $post_fields->Remarks!="" && $res_company_plot['image']!=""){
                    $data['message'] = "hide data";
                }
                else{
                    $data['message'] = "show data";
                }

                if($post_fields->Contact_Name!="" && $post_fields->Mobile_No!=""){
                    $data['message_plot'] = "show add plot";
                }
                else{
                    $data['message_plot'] = "hide add plot";
                }

                $data["data"]=$details;

                $data['success'] = true;
            }
            else{
                // get common values from json from table tbl_tdrawdata
                $res_company=$db->get_company_details($result_estate['taluka'],$result_estate['industrial_estate'],$result_estate['area_id'],$plot_no,$floor_no,$road_no);

                while($plot = mysqli_fetch_array($res_company)){
                    $row_data = json_decode($plot["raw_data"]);
                    $post_fields = $row_data->post_fields;

                    if($post_fields->IndustrialEstate==$result_estate['industrial_estate'] && $post_fields->Taluka==$result_estate['taluka'] && $post_fields->Area==$result_estate['area_id']){

                        $plot_details = $row_data->plot_details;

                        foreach ($plot_details as $pd) {
                            if($pd->Floor==$floor_no && $pd->Plot_No==$plot_no  && $pd->Road_No==$road_no){
                                
                                $reason = "";
                                $status = "";

                                // get company status (positive/negative/existing) from tbl_tdrawassign
                                $rawassign_status = $db->get_tbl_tdrawassign($plot['id']);
                                if(mysqli_num_rows($rawassign_status)>0){
                                    $status_res = mysqli_fetch_array($status_result);
                                    if($status_res['stage']=="lead"){
                                        $status = "Positive";   
                                    }
                                    else if($status_res['stage']=="badlead"){
                                        $status = "Negative";   
                                    }
                                    else{
                                        $status = "Existing Client";    
                                    }
                                }
                                else{
                                    $status = ($pr_comp_status!=null)?$pr_comp_status:"";
                                }

                                if($status=='Negative'){
                                    $reason = $db->get_badlead_reason($plot['id']);
                                }

                                $details = Array (
                                    "Id" => $plot["id"],
                                    "Plot_Id" => $res_company_plot['plot_id'],
                                    "Road_No" => $road_no,
                                    "IndustrialEstate" => $post_fields->IndustrialEstate,
                                    "Area" => $post_fields->Area,
                                    "Plot_Status" => isset($res_company_plot['plot_status'])?$res_company_plot['plot_status']:"",
                                    "Premise" => $post_fields->Premise,
                                    "GST_No" => $post_fields->GST_No,
                                    "Firm_Name" => $post_fields->Firm_Name,
                                    "Contact_Name" => $post_fields->Contact_Name,
                                    "Mobile_No" => $post_fields->Mobile_No,
                                    "Constitution" => isset($res_company_plot['constitution'])?$res_company_plot['constitution']:"",
                                    "Category" => $post_fields->Category,
                                    "Segment" => $post_fields->Segment,
                                    "Status" => isset($res_company_plot['status'])?$res_company_plot['status']:"",
                                    "Reason" => $reason,
                                    "source" => $post_fields->source,
                                    "Source_Name" => $post_fields->Source_Name,
                                    "Remarks" => $post_fields->Remarks,
                                    "Image" => ($res_company_plot['image']=="")?"":"https://software.bhuraconsultancy.com/gst_image/".$res_company_plot['image'],
                                    "Company_detail_id" => $res_company_plot["company_id"],
                                    "Company_plot_id" => $res_company_plot["pid"],
                                    "Loan_Sanction" => isset($post_fields->loan_applied)?$post_fields->loan_applied:"",
                                    "Completion_Date" => isset($post_fields->Completion_Date)?date('d-m-Y',strtotime($post_fields->Completion_Date)):"",
                                    "Existing_client_status" => $res_company_plot["existing_client_status"]
                                );
                                
                                //if(($res_company_plot["pid"]!=null || $res_company_plot["pid"]!="") && $plot["id"]!=""){
                                if($plot['id']!="" && $res_company_plot['plot_id']!="" && $post_fields->IndustrialEstate!="" && $post_fields->Area!="" && $res_company_plot['plot_status']!="" && $post_fields->Premise!="" && $post_fields->GST_No!="" && $post_fields->Firm_Name!="" && $post_fields->Contact_Name!="" && $post_fields->Mobile_No!="" && $res_company_plot['constitution']!="" && $post_fields->Category!="" && $post_fields->Segment!="" && $res_company_plot['status']!="" && $post_fields->source!="" && $post_fields->Source_Name!="" && $post_fields->Remarks!="" && $res_company_plot['image']!=""){
                                    $data['message'] = "hide data";
                                }
                                else{
                                    $data['message'] = "show data";
                                }

                                if($post_fields->Contact_Name!="" && $post_fields->Mobile_No!=""){
                                    $data['message_plot'] = "show add plot";
                                }
                                else{
                                    $data['message_plot'] = "hide add plot";
                                }

                                $data["data"]=$details;

                                $data['success'] = true;
                            }
                        }
                    }
                }
            }
         }
        else{
            //$data["message"]="No Result Found";
            $data['message'] = "hide data";
            $data['message_plot'] = "hide add plot";
            $data['success'] = false;
        }
    }
    
    echoResponse(200, $data);
});


//verify plot no
$app->post('/verify_plot_no','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    
    $from_plot_no=$data_request->from_plot_no;
    $to_plot_no=$data_request->to_plot_no;

    $data = array();
    $data["data"] = array();
    
    $letters = "/^[A-Za-z]$/";
    $suffix = "/^[0-9]+[^a-zA-Z0-9]*[a-zA-Z]$/";
    $prefix = "/^[a-zA-Z][^a-zA-Z0-9]*[0-9]+$/";
    $specialChars ="/[`!@#$%^&*()_\-+=\[\]{};':\\|,.<>\/?~ ]+/";
    $re_for_alphabet = "/([a-zA-Z]+)/";
    $re_for_digits = "/(\d+)/";

    if(is_numeric($from_plot_no) && is_numeric($to_plot_no)){
        $type = "numeric";  
        if($from_plot_no>$to_plot_no){
            $data['message'] = "Invalid Input";
            $data['success'] = false;
        }
        else{
            $data['message'] = "Valid Input";
            $data['success'] = true;
        }
    }
    else if(preg_match($letters,$from_plot_no) && preg_match($letters,$to_plot_no)){
        $type = "alphabet";
      
        $from_plot_no = strtoupper($from_plot_no);
        $to_plot_no = strtoupper($to_plot_no);

        if($from_plot_no>$to_plot_no){
            $data['message'] = "Invalid Input";
            $data['success'] = false;
        }
        else{
            $data['message'] = "Valid Input";
            $data['success'] = true;
        }
    }
    else if(preg_match($prefix,$from_plot_no) && preg_match($prefix,$to_plot_no)){
        
        preg_match($re_for_digits,$from_plot_no,$from_plot_number);
        preg_match($re_for_alphabet,$from_plot_no,$from_plot_alphabet);
        preg_match($specialChars,$from_plot_no,$from_plot_char);

        preg_match($re_for_digits,$to_plot_no,$to_plot_number);
        preg_match($re_for_alphabet,$to_plot_no,$to_plot_alphabet);
        preg_match($specialChars,$to_plot_no,$to_plot_char);

        if($from_plot_char==null){
            $from_plot_char=[''];
        }
        if($to_plot_char==null){
            $to_plot_char=[''];
        }

        if(strtoupper($from_plot_alphabet[0])==strtoupper($to_plot_alphabet[0]) && $from_plot_char[0]==$to_plot_char[0] && $to_plot_number>=$from_plot_number){
            $type = "prefix_number";
            $data['message'] = "Valid Input";
            $data['success'] = true;
        }
        else if(strtoupper($to_plot_alphabet[0])>=strtoupper($from_plot_alphabet[0]) && $from_plot_char[0]==$to_plot_char[0] && $to_plot_number==$from_plot_number){
            $type = "prefix_alphabet";
            $data['message'] = "Valid Input";
            $data['success'] = true;
        }
        else{
            $data['message'] = "Invalid Input";
            $data['success'] = false;
        }
    }
    else if(preg_match($suffix,$from_plot_no) && preg_match($suffix,$to_plot_no)){
      
        preg_match($re_for_digits,$from_plot_no,$from_plot_number);
        preg_match($re_for_alphabet,$from_plot_no,$from_plot_alphabet);
        preg_match($specialChars,$from_plot_no,$from_plot_char);

        preg_match($re_for_digits,$to_plot_no,$to_plot_number);
        preg_match($re_for_alphabet,$to_plot_no,$to_plot_alphabet);
        preg_match($specialChars,$to_plot_no,$to_plot_char);

        if($from_plot_char==null){
            $from_plot_char=[''];
        }
        if($to_plot_char==null){
            $to_plot_char=[''];
        }

        if(strtoupper($from_plot_alphabet[0])==strtoupper($to_plot_alphabet[0]) && $from_plot_char[0]==$to_plot_char[0] && $to_plot_number>=$from_plot_number){
            $type = "suffix_number";      
            $data['message'] = "Valid Input";
            $data['success'] = true;
        }
        else if(strtoupper($to_plot_alphabet[0])>=strtoupper($from_plot_alphabet[0]) && $from_plot_char[0]==$to_plot_char[0] && $to_plot_number==$from_plot_number){
            $type = "suffix_alphabet";
            $data['message'] = "Valid Input";
            $data['success'] = true;
        }
        else{
            $data['message'] = "Invalid Input";
            $data['success'] = false;
        }
    }
    else{
        $data['message'] = "Invalid Input";
        $data['success'] = false;
    }

    echoResponse(200, $data);
});


//verify road no
$app->post('/verify_road_no','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    
    $from_road_no=$data_request->from_road_no;
    $to_road_no=$data_request->to_road_no;

    $data = array();
    $data["data"] = array();
    
    $letters = "/^[A-Za-z]$/";
    $suffix = "/^[0-9]+[^a-zA-Z0-9]*[a-zA-Z]$/";
    $prefix = "/^[a-zA-Z][^a-zA-Z0-9]*[0-9]+$/";
    $specialChars ="/[`!@#$%^&*()_\-+=\[\]{};':\\|,.<>\/?~ ]+/";
    $re_for_alphabet = "/([a-zA-Z]+)/";
    $re_for_digits = "/(\d+)/";

    if(is_numeric($from_road_no) && is_numeric($to_road_no)){
        $type = "numeric";  
        if($from_road_no>$to_road_no){
            $data['message'] = "Invalid Input";
            $data['success'] = false;
        }
        else{
            $data['message'] = "Valid Input";
            $data['success'] = true;
            for ($i=$from_road_no; $i<=$to_road_no; $i++) {
                array_push($data['data'], strval($i));
            }
        }
    }
    else if(preg_match($letters,$from_road_no) && preg_match($letters,$to_road_no)){
        $type = "alphabet";
      
        $from_road_no = strtoupper($from_road_no);
        $to_road_no = strtoupper($to_road_no);

        if($from_road_no>$to_road_no){
            $data['message'] = "Invalid Input";
            $data['success'] = false;
        }
        else{
            $data['message'] = "Valid Input";
            $data['success'] = true;
            for ($i=ord($from_road_no); $i<=ord($to_road_no); $i++) {
                array_push($data['data'], chr($i));
            }
        }
    }
    else if(preg_match($prefix,$from_road_no) && preg_match($prefix,$to_road_no)){
        
        preg_match($re_for_digits,$from_road_no,$from_road_number);
        preg_match($re_for_alphabet,$from_road_no,$from_road_alphabet);
        preg_match($specialChars,$from_road_no,$from_road_char);

        preg_match($re_for_digits,$to_road_no,$to_road_number);
        preg_match($re_for_alphabet,$to_road_no,$to_road_alphabet);
        preg_match($specialChars,$to_road_no,$to_road_char);

        if($from_road_char==null){
            $from_road_char=[''];
        }
        if($to_road_char==null){
            $to_road_char=[''];
        }

        if(strtoupper($from_road_alphabet[0])==strtoupper($to_road_alphabet[0]) && $from_road_char[0]==$to_road_char[0] && $to_road_number>=$from_road_number){
            $type = "prefix_number";
            $data['message'] = "Valid Input";
            $data['success'] = true;
            for ($i=$from_road_number[0]; $i<=$to_road_number[0]; $i++) {
                array_push($data['data'], strtoupper($from_road_alphabet[0]).$from_road_char[0].$i);
            }
        }
        else if(strtoupper($to_road_alphabet[0])>=strtoupper($from_road_alphabet[0]) && $from_road_char[0]==$to_road_char[0] && $to_road_number==$from_road_number){
            $type = "prefix_alphabet";
            $data['message'] = "Valid Input";
            $data['success'] = true;
            for ($i=ord(strtoupper($from_road_alphabet[0])); $i<=ord(strtoupper($to_road_alphabet[0])); $i++) {
                array_push($data['data'], chr($i).$from_road_char[0].$from_road_number[0]);
            }
        }
        else{
            $data['message'] = "Invalid Input";
            $data['success'] = false;
        }
    }
    else if(preg_match($suffix,$from_road_no) && preg_match($suffix,$to_road_no)){
      
        preg_match($re_for_digits,$from_road_no,$from_road_number);
        preg_match($re_for_alphabet,$from_road_no,$from_road_alphabet);
        preg_match($specialChars,$from_road_no,$from_road_char);

        preg_match($re_for_digits,$to_road_no,$to_road_number);
        preg_match($re_for_alphabet,$to_road_no,$to_road_alphabet);
        preg_match($specialChars,$to_road_no,$to_road_char);

        if($from_road_char==null){
            $from_road_char=[''];
        }
        if($to_road_char==null){
            $to_road_char=[''];
        }

        if(strtoupper($from_road_alphabet[0])==strtoupper($to_road_alphabet[0]) && $from_road_char[0]==$to_road_char[0] && $to_road_number>=$from_road_number){
            $type = "suffix_number";      
            $data['message'] = "Valid Input";
            $data['success'] = true;
            for ($i=$from_road_number[0]; $i<=$to_road_number[0]; $i++) {
                array_push($data['data'], $i.$from_road_char[0].strtoupper($from_road_alphabet[0]));
            }
        }
        else if(strtoupper($to_road_alphabet[0])>=strtoupper($from_road_alphabet[0]) && $from_road_char[0]==$to_road_char[0] && $to_road_number==$from_road_number){
            $type = "suffix_alphabet";
            $data['message'] = "Valid Input";
            $data['success'] = true;
            for ($i=ord(strtoupper($from_road_alphabet[0])); $i<=ord(strtoupper($to_road_alphabet[0])); $i++) {
                array_push($data['data'], $from_road_number[0].$from_road_char[0].chr($i));
            }
        }
        else{
            $data['message'] = "Invalid Input";
            $data['success'] = false;
        }
    }
    else{
        $data['message'] = "Invalid Input";
        $data['success'] = false;
    }

    echoResponse(200, $data);
});


// insert company
$app->post('/insert_company','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $industrial_estate_id = $data_request->industrial_estate_id;
    $plot_status = $data_request->plot_status;
    $premise = $data_request->premise;
    $gst_no = $data_request->gst_no;
    $firm_name = $data_request->firm_name;
    $contact_person = $data_request->contact_person;
    $contact_no = $data_request->contact_number;
    $constitution = $data_request->constitution;
    $category = $data_request->category;
    $segment = $data_request->segment;
    $status = $data_request->status;
    $badlead_reason = $data_request->badlead_reason;
    $source = $data_request->source;
    $source_name = $data_request->source_name;
    $remark = $data_request->remark;
    $id = $data_request->id;
    $plot_id = $data_request->plot_id;
    $pr_company_plot_id = $data_request->company_plot_id;
    $pr_company_detail_id = $data_request->company_detail_id;
    $user_id = $data_request->user_id;
    $location = isset($data_request->location)?$data_request->location:NULL;
    $existing_expansion_status = isset($data_request->existing_expansion_status)?$data_request->existing_expansion_status:"";
    $loan_sanction = isset($data_request->loan_sanction)?$data_request->loan_sanction:"";
    $completion_date = isset($data_request->completion_date)?$data_request->completion_date:"";
    $filter = isset($data_request->filter)?$data_request->filter:"";
    $badlead_type = "lead";
    $PicFileName="";

    $update_location = false;

    $completion_date = date("Y-m-d", strtotime($completion_date));

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $res_username=$db->get_username($user_id);

    $res_rawdata=$db->get_rawdata($id);

    $row_data=json_decode($res_rawdata["raw_data"]);
    $post_fields=$row_data->post_fields;
    $plot_details=$row_data->plot_details;


    $old_img = $row_data->Image;
    
    if(isset($_FILES["company_img"]["name"])!=""){
        $update_location = true;
        if($old_img!="" && file_exists("../../gst_image/".$old_img)){
            unlink("../../gst_image/".$old_img); 
        }
        
        //rename file for gst image
        $img = $img_path = $_FILES["company_img"]["name"];
        $img_path = $_FILES["company_img"]["tmp_name"];
          if(file_exists("../../gst_image/" . $img)) {
            $i = 0;
            $PicFileName = $_FILES["company_img"]["name"];
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("../../gst_image/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
         } 
         else {
            $PicFileName = $_FILES["company_img"]["name"];
          }
        move_uploaded_file($img_path,"../../gst_image/".$PicFileName);
    }
    else{
        $update_location = false;
        $PicFileName=$old_img;
    }


    $i=0;
    foreach($plot_details as $pd){
    if($pd->Plot_Id==$plot_id){
        $plot_index=$i;
        break;
    }
    $i++;
    }

    $post_fields->Premise = $premise;
    $post_fields->GST_No = $gst_no;
    $post_fields->Firm_Name = $firm_name;
    $post_fields->Contact_Name = $contact_person;
    $post_fields->Mobile_No = $contact_no;
    $row_data->Constitution = $constitution;
    $post_fields->Category = $category;
    $post_fields->Segment = $segment;
    
    $post_fields->source = $source;
    $post_fields->Source_Name = $source_name;
    $row_data->Image = $PicFileName;
    $post_fields->Remarks = $remark;
    $post_fields->loan_applied = $loan_sanction;
    $post_fields->Completion_Date = $completion_date;
    $post_fields->Existing_client_status = $existing_expansion_status;
    $plot_details["$plot_index"]->Plot_Status = $plot_status;

    if($existing_expansion_status=="positive for expansion")
    {
        $row_data->Status = "Positive";
    }
    else if($existing_expansion_status=="negative for expansion")
    {
        $row_data->Status = "Negative";
    }
    else
    {
        $row_data->Status = $status;
    }

    if($status=='Negative'){
        $row_data->bad_lead_reason = $badlead_reason;
    }
    else{
        $row_data->bad_lead_reason = ""; 
    }

    $json_object = json_encode($row_data);

    $result=$db->update_tbl_tdrawdata($json_object,$user_id,$id,$filter,$pr_company_detail_id);

    if($result==-1){
        $data['message'] = "An error occurred";
        $data['success'] = false;
    }
    else{
        if($completion_date!=""){
            $db->insert_tdrawdata_cdates($id,$user_id,$completion_date);
        }

        if($result>0){
            $result_visit_count=$db->pr_visit_count($post_fields->IndustrialEstate,$post_fields->Area,$post_fields->Taluka,$id,$user_id,$result);

            $followup_source = "Auto";
            $followup_date = date("Y-m-d");
            $admin_userid = '1';

            if($plot_status!="" && $gst_no!="" && $firm_name!="" && $contact_person!="" && $contact_no!="" && $status!="" && $source!="" && $source_name!="" && $remark!="" && $PicFileName!=""){
                if($status=="Positive" || $status=="Negative"){
                    if($db->checkCompany_rawassign($id)){
                        // insert into follow up
                        $followup_text = "<p>".$res_username['name']." has edited a lead data in system.</p>";
                        
                        $result_followup = $db->insert_followup($user_id,$id,$followup_text,$followup_source,$followup_date);
                    }
                    else{
                        // insert into raw assign and follow up
                        $raw_assign_status = "lead";
                        $followup_text = "<p>".$res_username['name']." has added a data in system.</p>";
                        
                        $result_rawassign = $db->insert_rawassign($id,$admin_userid,$raw_assign_status);

                        $result_followup = $db->insert_followup($user_id,$id,$followup_text,$followup_source,$followup_date);
                    }

                    if($status=='Negative'){
                        if($db->check_for_badlead($id)==0){
                          $badlead_raw_assign_status = "badlead";
                          $badlead_followup_text = $res_username['name']." has marked lead as BAD LEAD. <br />Reason: ".$badlead_reason." <br />Remark: ".$remark;

                          $result_followup = $db->insert_badleads($badlead_reason,$remark,$id,$user_id,$badlead_type);

                          $result_rawassign = $db->insert_rawassign($id,$admin_userid,$badlead_raw_assign_status);

                          $result_followup = $db->insert_followup($user_id,$id,$badlead_followup_text,$followup_source,$followup_date);
                        }  
                    }
                    else{
                        if($db->check_for_badlead($id)==1){
                          // insert into raw assign and follow up
                          $raw_assign_status = "lead";
                          
                          $result_rawassign = $db->insert_rawassign($id,$admin_userid,$raw_assign_status);
                        }
                    }
                } 
            } 
        }

        // insert into pr_company_detail and pr_company_plot table
        $inq_submit = "Submit";
        
        $result_rawassign = $db->insert_pr_company_detail($source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$post_fields->state,$post_fields->city,$post_fields->Taluka,$post_fields->Area,$post_fields->IndustrialEstate,$remark,$inq_submit,$PicFileName,$constitution,$status,$industrial_estate_id,$user_id,$id,$plot_status,$pr_company_plot_id,$pr_company_detail_id,$location,$existing_expansion_status,$update_location);
        

        if($result_rawassign>0)
        {
            $data['message'] = "Data added successfully";
            $data['success'] = true;
        }
        else
        {
            $data['data'] = "";
            $data['message'] = "An error occurred";
            $data['success'] = false;
        }
    }

    
    echoResponse(200, $data);
});


// check additional plot number
$app->post('/check_additional_plot','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $additional_plot_no = $data_request->additional_plot_no;
    $road_no=isset($data_request->road_no)?$data_request->road_no:"";
    $estate_id=$data_request->estate_id;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result=$db->check_additional_plot($additional_plot_no,$road_no,$estate_id);
    
    if(mysqli_num_rows($result)>0){
        $data['data'] = "";
        $data['message'] = "Plot No. already exist!";
        $data['success'] = false;
    }
    else{
        $data['data'] = "";
        $data['message'] = "";
        $data['success'] = true;
    }

    echoResponse(200, $data);
});

// add additional plot
$app->post('/add_additional_plot','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $additional_plot_no = strtoupper($data_request->additional_plot_no);
    $road_no=($data_request->road_no)?$data_request->road_no:"";
    $estate_id=$data_request->estate_id;
    $user_id=$data_request->user_id;
    $floor = '0';
    $road_number = ($road_no=="")?NULL:$road_no;
    $plot_id = '1';
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result=$db->check_additional_plot($additional_plot_no,$road_no,$estate_id);
    
    if(mysqli_num_rows($result)>0){
        $data['data'] = "";
        $data['message'] = "Plot No. already exist!";
        $data['success'] = false;
    }
    else{
        $result_estate=$db->get_ind_estate_data($estate_id);
    
        $to_plotno = NULL;
        $resp_plot=$db->pr_estate_roadplot($estate_id,$road_number,$additional_plot_no,$to_plotno,$user_id);

        if($resp_plot>0){
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
                "state" => $result_estate['state_id'],
                "city" => $result_estate['city_id'],
                "Taluka" => $result_estate['taluka'],
                "Area" => $result_estate['area_id'],
                "IndustrialEstate" => $result_estate['industrial_estate'],
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
                  "Plot_No" => $additional_plot_no,
                  "Floor" => $floor,
                  "Road_No" => $road_no,
                  "Plot_Status" => "",
                  "Plot_Id" => "1",
                ),
              ) 
            );
             
            // Encode array to json
            $json = json_encode($cp);

            $resp_tdrawdata_id=$db->insert_tbl_tdrawdata($json,$user_id);

            // insert into pr_company_plot
            $resp_company_plot=$db->company_plot_insert($additional_plot_no,$floor,$road_number,$plot_id,$estate_id,$user_id);
            
            $result_estate=$db->get_ind_estate($estate_id);
            
            $res_company_plot=$db->get_pr_company_details($result_estate['plotting_pattern'],$estate_id,$additional_plot_no,$floor,$road_no);

            // dropdowns
            $data["data"]["Refill Data"] = array();
            $data["data"]["Industrial Estate"] = array();
            $data["data"]["Plot No"] = array();
            $data["data"]["Floor No"] = array();
            $data["data"]["Filter"] = array();

            // industrial estate
            $resp_estate=$db->get_ind_estate_data($estate_id);
            $temp_est = array();
            $temp_est['industrial_estate_id'] = $resp_estate['id'];
            $temp_est['industrial_estate'] = $resp_estate['industrial_estate'];
            $temp_est['taluka'] = $resp_estate['taluka'];
            $temp_est['area_id'] = $resp_estate['area_id'];
            $temp_est = array_map('utf8_encode', $temp_est);
            array_push($data['data']['Industrial Estate'], $temp_est);
        
            $refill_data = array();

            if($road_no!=""){
                $data["data"]["Road No"] = array();
                $refill_data['road_no'] = $road_no;

                // road no
                $temp_road = array();
                $temp_road['road_no'] = $road_no;
                $temp_road = array_map('utf8_encode', $temp_road);
                array_push($data['data']['Road No'], $temp_road);
            }
        
            // plot no
            $temp_plot = array();
            $temp_plot["plot_no"] = $additional_plot_no;
            $temp_plot = array_map('utf8_encode', $temp_plot);
            array_push($data['data']['Plot No'], $temp_plot);

            // floor no
            $temp_floor = array();
            $temp_floor["floor_no"] = ($floor=="0")?"Ground Floor":$floor;
            $temp_floor = array_map('utf8_encode', $temp_floor);
            array_push($data['data']['Floor No'], $temp_floor);
        
            // floor no
            $temp_filter = array();
            $temp_filter["filter"] = 'None';
            $temp_filter = array_map('utf8_encode', $temp_filter);
            array_push($data['data']['Filter'], $temp_filter);

            $refill_data['estate_id'] = $estate_id;
            $refill_data['filter'] = 'None';
            $refill_data['plot_no'] = $additional_plot_no;
            $refill_data['floor'] = ($floor=="0")?"Ground Floor":$floor;
            $refill_data['Id'] = $resp_tdrawdata_id;
            $refill_data['Plot_Id'] = $res_company_plot['plot_id'];
            $refill_data['Company_detail_id'] = $res_company_plot['company_id'];
            $refill_data['Company_plot_id'] = $res_company_plot['pid'];
            
            $refill_data = array_map('utf8_encode', $refill_data);
            array_push($data['data']['Refill Data'], $refill_data);

        }
        else
        {
            $data['message'] = "An error occurred! Data not inserted";
            $data['success'] = false;
        }
        
        if($resp_company_plot)
        {
            $data['message'] = "Data added successfully";
            $data['success'] = true;
        }
        else
        {
            $data['data'] = "";
            $data['message'] = "An error occurred";
            $data['success'] = false;
        }
    }

    echoResponse(200, $data);
});


// get floor list for add floor modal
$app->post('/get_floor_floormodal','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $plot_no = $data_request->plot_no;
    $road_no=isset($data_request->road_no)?$data_request->road_no:"";
    $estate_id=$data_request->estate_id;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result_estate=$db->get_ind_estate($estate_id);
    $plotting_pattern = $result_estate['plotting_pattern'];

    if(count($result_estate)>0){
        $result=$db->get_floor_floormodal($plot_no,$road_no,$estate_id,$plotting_pattern);
    
        while($row = mysqli_fetch_array($result)){
            $data['data'][] = $row['floor'];
        }

        $data['message'] = "";
        $data['success'] = true;    
    }
    else{
        $data['message'] = "An error occurred";
        $data['success'] = false;
    }

    echoResponse(200, $data);
});


// add floor of floor modal
$app->post('/add_floor','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $plot_no = $data_request->plot_no;
    $road_no=$data_request->road_no;
    $floor=$data_request->floor;
    $floor=($floor=="Ground Floor")?"0":$floor;
    $plot_status=$data_request->plot_status;
    $floor_confirmation=$data_request->floor_confirmation;
    $estate_id=$data_request->estate_id;
    $id=$data_request->id;
    $pr_company_detail_id=$data_request->company_detail_id;
    $user_id=$data_request->user_id;
    $road_number = ($road_no=="")?NULL:$road_no;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result_estate=$db->get_ind_estate_data($estate_id);

    if($floor_confirmation=='Same Company'){  // Same Company As Ground

        $res_rawdata=$db->get_rawdata($id);
        
        $row_data=json_decode($res_rawdata["raw_data"]);
        $post_fields = $row_data->post_fields;

        if(isset($row_data->plot_details)){
            $plot_details = $row_data->plot_details;
            $arr_count = count($plot_details);
            $last_plot_id = $plot_details[$arr_count-1]->Plot_Id;

            $plot_id = $last_plot_id+1;

            $new_plot_detail=Array(
              "Plot_No" => $plot_no,
              "Floor" => $floor,
              "Road_No" => $road_no,
              "Plot_Status" => $plot_status,
              "Plot_Id" => $plot_id,
            );  
            array_push($row_data->plot_details, $new_plot_detail);
        }
        else{
            $plot_id = "1";

            $row_data->plot_details=Array(
              Array(
                "Plot_No" => $plot_no,
                "Floor" => $floor,
                "Road_No" => $road_no,
                "Plot_Status" => $plot_status,
                "Plot_Id" => $plot_id,
              ),
            );  
        }

        $json_object = json_encode($row_data);

        $filter_samecomp = 'same_company';
        $result=$db->update_tbl_tdrawdata($json_object,$user_id,$id,$filter_samecomp);
      
        // for pr_visit_count table
        // if the data is updated by an employee on different date then count+1

        if($result==-1){
            $data['message'] = "An error occurred";
            $data['success'] = false;
        }
        else{
            if($result>0){
                $result_visit_count=$db->pr_visit_count($result_estate['industrial_estate'],$result_estate['area_id'],$result_estate['taluka'],$id,$user_id,$result);
            }
        }

      $resp_company_plot = $db->company_plot_insert($plot_no,$floor,$road_number,$plot_id,$estate_id,$user_id,$plot_status,$pr_company_detail_id);
    }
    else if($floor_confirmation=='Same Owner But Different Company'){   // Same Owner As Ground But Different Company
      
      $res_rawdata=$db->get_rawdata($id);

      $row_data=json_decode($res_rawdata["raw_data"]);
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
          "state" => $result_estate["state_id"],
          "city" => $result_estate["city_id"],
          "Taluka" => $result_estate["taluka"],
          "Area" => $result_estate["area_id"],
          "IndustrialEstate" => $result_estate["industrial_estate"],
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

      $insertid_tdrawdata=$db->insert_tbl_tdrawdata($json,$user_id);

      $plot_id = '1';

      // insert in pr_company_details
      $resp_company_detail_id = $db->company_details_insert($post_fields->Contact_Name,$post_fields->Mobile_No,$result_estate["state_id"], $result_estate["city_id"], $result_estate["taluka"], $result_estate["area_id"], $result_estate["industrial_estate"],$estate_id,$user_id,$insertid_tdrawdata);

      // insert in pr_company_plot
      $resp_company_plot = $db->company_plot_insert($plot_no,$floor,$road_number,$plot_id,$estate_id,$user_id,$plot_status,$resp_company_detail_id);

    }
    else if($floor_confirmation=='Different Company'){  // Different Company and Different Owner than Ground
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
          "state" => $result_estate["state_id"],
          "city" => $result_estate["city_id"],
          "Taluka" => $result_estate["taluka"],
          "Area" => $result_estate["area_id"],
          "IndustrialEstate" => $result_estate["industrial_estate"],
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

      $insertid_tdrawdata=$db->insert_tbl_tdrawdata($json,$user_id);
      
      $plot_id = '1';

      // insert in pr_company_plot
      $resp_company_plot = $db->company_plot_insert($plot_no,$floor,$road_number,$plot_id,$estate_id,$user_id,$plot_status);
    }
    else
    {
        $data['data'] = "";
        $data['message'] = "An error occurred! Data not inserted";
        $data['success'] = false;
    }

    if($floor_confirmation=='Same Owner But Different Company' || $floor_confirmation=='Different Company'){
        $result_estate=$db->get_ind_estate($estate_id);

        $res_company_plot=$db->get_pr_company_details($result_estate['plotting_pattern'],$estate_id,$plot_no,$floor,$road_no);

        // dropdowns
        $data["data"]["Refill Data"] = array();
        $data["data"]["Industrial Estate"] = array();
        $data["data"]["Plot No"] = array();
        $data["data"]["Floor No"] = array();
        $data["data"]["Filter"] = array();

        // industrial estate
        $resp_estate=$db->get_ind_estate_data($estate_id);
        $temp_est = array();
        $temp_est['industrial_estate_id'] = $resp_estate['id'];
        $temp_est['industrial_estate'] = $resp_estate['industrial_estate'];
        $temp_est['taluka'] = $resp_estate['taluka'];
        $temp_est['area_id'] = $resp_estate['area_id'];
        $temp_est = array_map('utf8_encode', $temp_est);
        array_push($data['data']['Industrial Estate'], $temp_est);
    
        $refill_data = array();

        if($road_no!=""){
            $data["data"]["Road No"] = array();
            $refill_data['road_no'] = $road_no;

            // road no
            $temp_road = array();
            $temp_road['road_no'] = $road_no;
            $temp_road = array_map('utf8_encode', $temp_road);
            array_push($data['data']['Road No'], $temp_road);
        }
    
        // plot no
        $temp_plot = array();
        $temp_plot["plot_no"] = $plot_no;
        $temp_plot = array_map('utf8_encode', $temp_plot);
        array_push($data['data']['Plot No'], $temp_plot);

        // floor no
        $temp_floor = array();
        $temp_floor["floor_no"] = ($floor=="0")?"Ground Floor":$floor;
        $temp_floor = array_map('utf8_encode', $temp_floor);
        array_push($data['data']['Floor No'], $temp_floor);
    
        // floor no
        $temp_filter = array();
        $temp_filter["filter"] = 'None';
        $temp_filter = array_map('utf8_encode', $temp_filter);
        array_push($data['data']['Filter'], $temp_filter);

        $refill_data['estate_id'] = $estate_id;
        $refill_data['filter'] = 'None';
        $refill_data['plot_no'] = $plot_no;
        $refill_data['floor'] = ($floor=="0")?"Ground Floor":$floor;
        $refill_data['plot_status'] = $plot_status;
        if($floor_confirmation=='Same Owner But Different Company'){
            $refill_data['contact_person'] = $post_fields->Contact_Name;
            $refill_data['contact_no'] = $post_fields->Mobile_No;
        }
        $refill_data['Id'] = $insertid_tdrawdata;
        $refill_data['Plot_Id'] = $res_company_plot['plot_id'];
        $refill_data['Company_detail_id'] = $res_company_plot['company_id'];
        $refill_data['Company_plot_id'] = $res_company_plot['pid'];

        $refill_data = array_map('utf8_encode', $refill_data);
        array_push($data['data']['Refill Data'], $refill_data);

    }

    if($resp_company_plot){
        $data['message'] = "Data added successfully";
        $data['success'] = true;
    }
    else{
        $data['data'] = "";
        $data['message'] = "An error occurred! Data not inserted";
        $data['success'] = false;   
    }

    echoResponse(200, $data);
});

// get plot list for add plot modal
$app->post('/get_plot_plotmodal','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $old_road_no=isset($data_request->old_road_no)?$data_request->old_road_no:"";
    $old_plot_no = $data_request->old_plot_no;
    $road_no=isset($data_request->road_no)?$data_request->road_no:"";
    $estate_id=$data_request->estate_id;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result_estate=$db->get_ind_estate($estate_id);
    $plotting_pattern = $result_estate['plotting_pattern'];

    $result=$db->get_plot_plotmodal($old_road_no,$old_plot_no,$road_no,$plotting_pattern,$estate_id);
      
    if(mysqli_num_rows($result)>0){
        $temp = array();
        while ($row = $result->fetch_assoc()) {
            foreach($row as $key => $value){
                $temp["plot_no"] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Result Found";
        $data['success'] = true;
    }

    echoResponse(200, $data);
});

// get floor list for add plot modal
$app->post('/get_floor_plotmodal','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $plot_no = $data_request->plot_no;
    $road_no=isset($data_request->road_no)?$data_request->road_no:"";
    $estate_id=$data_request->estate_id;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result_estate=$db->get_ind_estate($estate_id);
    $plotting_pattern = $result_estate['plotting_pattern'];

    $result=$db->get_floor_plotmodal($plot_no,$road_no,$plotting_pattern,$estate_id);
      
    if(mysqli_num_rows($result)>0){
        while($row = mysqli_fetch_array($result)){
            if($row['floor']=='0'){
                $data['data'][] = "Ground Floor";
            }
            else{
                $data['data'][] = $row['floor'];
            }
        }
        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Result Found";
        $data['success'] = true;
    }

    echoResponse(200, $data);
});

// add plot of add plot modal
$app->post('/add_plot','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $plot_no = $data_request->plot_no;
    $road_no=$data_request->road_no;
    $floor=$data_request->floor;
    $floor=($floor=="Ground Floor")?"0":$floor;
    $plot_status=$data_request->plot_status;
    $plot_confirmation=$data_request->plot_confirmation;
    $estate_id=$data_request->estate_id;
    $id=$data_request->id;
    $pr_company_detail_id=$data_request->company_detail_id;
    $user_id=$data_request->user_id;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result_estate=$db->get_ind_estate_data($estate_id);

    $result_plot_search=$db->get_plot_floor_old($result_estate['taluka'],$result_estate['industrial_estate'],$result_estate['area_id'],$plot_no,$road_no);

    $res_pattern=$db->get_ind_estate($estate_id);

    $result_company_plot=$db->get_pr_company_plot($res_pattern['plotting_pattern'],$estate_id,$plot_no,$floor,$road_no);

    // if floor already created then update otherwise insert
    if(mysqli_num_rows($result_company_plot)){
        $res_company_plot = $result_company_plot->fetch_assoc();
        $pr_company_plot_id = $res_company_plot['pid'];
        $next_status = 'update';
    }
    else{
        $next_status = 'insert';
    }

    $res_rawdata=$db->get_rawdata($id);

    if($plot_confirmation=='Same Company'){  // Same Company As Ground
        // update plot_details section of already existing company
        $row_data=json_decode($res_rawdata["raw_data"]);
        $post_fields = $row_data->post_fields;

        if(isset($row_data->plot_details)){
            $plot_details = $row_data->plot_details;
            $arr_count = count($plot_details);
            $last_plot_id = $plot_details[$arr_count-1]->Plot_Id;

            $plot_id = $last_plot_id+1;

            $new_plot_detail=Array(
              "Plot_No" => $plot_no,
              "Floor" => $floor,
              "Road_No" => $road_no,
              "Plot_Status" => $plot_status,
              "Plot_Id" => $plot_id,
            );  
            array_push($row_data->plot_details, $new_plot_detail);
        }
        else{
            $plot_id = "1";

            $row_data->plot_details=Array(
              Array(
                "Plot_No" => $plot_no,
                "Floor" => $floor,
                "Road_No" => $road_no,
                "Plot_Status" => $plot_status,
                "Plot_Id" => $plot_id,
              ),
            );  
        }
        
        $json_object = json_encode($row_data);

        $filter_samecomp = 'same_company';
        $result=$db->update_tbl_tdrawdata($json_object,$user_id,$id,$filter_samecomp);

        if($result==-1){
            $data['message'] = "An error occurred";
            $data['success'] = false;
        }
        else{
            if($result>0){
            // for pr_visit_count table
            $result_visit_count=$db->pr_visit_count($post_fields->IndustrialEstate,$post_fields->Area,$post_fields->Taluka,$id,$user_id,$result);

            // to get blank json data in tbl_tdrawdata and delete it
            if($next_status=='update'){
                if(mysqli_num_rows($result_plot_search)>0){
                  $delete_id="";
                  while($plot_search_res = mysqli_fetch_array($result_plot_search)){
                    $row_data_search=json_decode($plot_search_res["raw_data"]);

                    $plot_details = $row_data_search->plot_details;
                    foreach ($plot_details as $pd) {
                      if($pd->Plot_No==$plot_no && $pd->Floor==$floor){
                        $delete_id = $plot_search_res['id'];
                        break;
                      }
                    }
                  }
                  if($delete_id!=""){
                    $result_del=$db->delete_tbl_tdrawdata($delete_id);
                  }
                }
            }
          }
        }

        // insert or update in pr_company_plot
        if($next_status=='update'){
            $result_company_plot=$db->company_plot_update($plot_status,$plot_id,$pr_company_detail_id,$pr_company_plot_id,$user_id);
        }
        else{
            $result_company_plot=$db->company_plot_insert($plot_no,$floor,$road_no,$plot_id,$estate_id,$user_id,$plot_status,$pr_company_detail_id);
        }
    }
    else if($plot_confirmation=='Same Owner But Different Company'){   // Same Owner As Ground But Different Company

        $row_data=json_decode($res_rawdata["raw_data"]);
        $post_fields = $row_data->post_fields;
        $mobile_no = $post_fields->Mobile_No;
        $contact_name = $post_fields->Contact_Name;

        if($next_status=='update'){
            // if floor already exists then only update Contact_Name, Mobile_No and Plot_Status
            if(mysqli_num_rows($result_plot_search)>0){
              $result_rawdata_id="";
              while($plot_search_res = mysqli_fetch_array($result_plot_search)){
                $row_data_search=json_decode($plot_search_res["raw_data"]);

                $post_fields = $row_data_search->post_fields;
                $plot_details = $row_data_search->plot_details;
                if($post_fields->IndustrialEstate==$result_estate['industrial_estate'] && $post_fields->Area==$result_estate['area_id'] && $post_fields->Taluka==$result_estate['taluka']){
                  foreach ($plot_details as $pd) {
                    if($pd->Plot_No==$plot_no && $pd->Floor==$floor && $pd->Road_No==$road_no){
                      $result_rawdata_id = $plot_search_res['id'];
                      break;
                    }
                  }
                }
              }
            }
            $res = $db->update_tbl_tdrawdata_contact($contact_name,$mobile_no,$plot_status,$user_id,$result_rawdata_id);
        }
        else if($next_status=='insert'){
        // if floor does not exist then create new json

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
                "state" => $result_estate['state_id'],
                "city" => $result_estate['city_id'],
                "Taluka" => $result_estate['taluka'],
                "Area" => $result_estate['area_id'],
                "IndustrialEstate" => $result_estate['industrial_estate'],
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
           
            $result_rawdata_id=$db->insert_tbl_tdrawdata($json,$user_id,$id);
        }
      
        $plot_id = '1';

        // insert in pr_company_details
        $result_companydetail_id=$db->company_details_insert($post_fields->Contact_Name,$post_fields->Mobile_No,$result_estate["state_id"], $result_estate["city_id"], $result_estate["taluka"], $result_estate["area_id"], $result_estate["industrial_estate"],$estate_id,$user_id,$result_rawdata_id);

        // insert or update in pr_company_plot
        if($next_status=='update'){
            $result_company_plot=$db->company_plot_update($plot_status,$plot_id,$result_companydetail_id,$pr_company_plot_id,$user_id);
        }
        else if($next_status=='insert'){
            $result_company_plot=$db->company_plot_insert($plot_no,$floor,$road_no,$plot_id,$estate_id,$user_id,$plot_status,$result_companydetail_id);
        }

        $result_estate=$db->get_ind_estate($estate_id);
        $res_company_plot=$db->get_pr_company_details($result_estate['plotting_pattern'],$estate_id,$plot_no,$floor,$road_no);


        // dropdowns
        $data["data"]["Refill Data"] = array();
        $data["data"]["Industrial Estate"] = array();
        $data["data"]["Plot No"] = array();
        $data["data"]["Floor No"] = array();
        $data["data"]["Filter"] = array();

        // industrial estate
        $resp_estate=$db->get_ind_estate_data($estate_id);
        $temp_est = array();
        $temp_est['industrial_estate_id'] = $resp_estate['id'];
        $temp_est['industrial_estate'] = $resp_estate['industrial_estate'];
        $temp_est['taluka'] = $resp_estate['taluka'];
        $temp_est['area_id'] = $resp_estate['area_id'];
        $temp_est = array_map('utf8_encode', $temp_est);
        array_push($data['data']['Industrial Estate'], $temp_est);
    
        $refill_data = array();

        if($road_no!=""){
            $data["data"]["Road No"] = array();
            $refill_data['road_no'] = $road_no;

            // road no
            $temp_road = array();
            $temp_road['road_no'] = $road_no;
            $temp_road = array_map('utf8_encode', $temp_road);
            array_push($data['data']['Road No'], $temp_road);
        }
    
        // plot no
        $temp_plot = array();
        $temp_plot["plot_no"] = $plot_no;
        $temp_plot = array_map('utf8_encode', $temp_plot);
        array_push($data['data']['Plot No'], $temp_plot);

        // floor no
        $temp_floor = array();
        $temp_floor["floor_no"] = ($floor=="0")?"Ground Floor":$floor;
        $temp_floor = array_map('utf8_encode', $temp_floor);
        array_push($data['data']['Floor No'], $temp_floor);
    
        // floor no
        $temp_filter = array();
        $temp_filter["filter"] = 'None';
        $temp_filter = array_map('utf8_encode', $temp_filter);
        array_push($data['data']['Filter'], $temp_filter);

        $refill_data['estate_id'] = $estate_id;
        $refill_data['filter'] = 'None';
        $refill_data['plot_no'] = $plot_no;
        $refill_data['floor'] = ($floor=="0")?"Ground Floor":$floor;
        $refill_data['plot_status'] = $plot_status;
        $refill_data['contact_person'] = $post_fields->Contact_Name;
        $refill_data['contact_no'] = $post_fields->Mobile_No;
        $refill_data['Id'] = $result_rawdata_id;
        $refill_data['Plot_Id'] = $res_company_plot['plot_id'];
        $refill_data['Company_detail_id'] = $res_company_plot['company_id'];
        $refill_data['Company_plot_id'] = $res_company_plot['pid'];

        $refill_data = array_map('utf8_encode', $refill_data);
        array_push($data['data']['Refill Data'], $refill_data);

    }

    if($result_company_plot){
        $data['message'] = "Data added successfully";
        $data['success'] = true;
    }
    else{
        $data['data'] = "";
        $data['message'] = "An error occurred! Data not inserted";
        $data['success'] = false;   
    }

    echoResponse(200, $data);
});

//get filter list
$app->post('/get_filter','authenticateUser', function () use ($app) {

    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $emp_id = $data_request->emp_id;
    $estate_id=$data_request->estate_id;
    
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_filter($estate_id,$emp_id);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp['filter'] = ucwords(str_replace("_"," ",$value)); 
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $temp['filter'] = 'No Filter';
        $temp = array_map('utf8_encode', $temp);
        array_push($data['data'], $temp);

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
  
    echoResponse(200, $data);
});

// lead company list
$app->post('/lead_company_list','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result=$db->lead_company_list($userid);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Result Found";
        $data['success'] = false;
    }

    echoResponse(200, $data);
});

// follow ups list
$app->post('/followups_list','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $inq_id = $data_request->inqid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $temp_data["temp"] = array();

    $result=$db->followups_list($inq_id);

    if(mysqli_num_rows($result)>0){

        while ($row = $result->fetch_assoc()) {
            $temp = array_map('utf8_encode', $row);
            $temp_data['temp'][] = $temp;
        }

        $organizedData = [];

        foreach ($temp_data['temp'] as $entry) {
            $date = $entry['followup_date'] ?? '';
            $organizedData[$date][] = $entry;
        }

        $data['data'] = [];

        foreach ($organizedData as $date => $entries) {
            $data['data'][] = [
                'followup_date' => date('d-m-Y',strtotime($date)),
                'entries' => $entries,
            ];
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Result Found";
        $data['success'] = false;
    }

    echoResponse(200, $data);
});

// add follow up
$app->post('/add_followup','authenticateUser', function () use ($app) {
    
    //{"followup_text" : "", "inqid" : "", "source" : "", "next_action" : "", "userid" : "", "next_datetime" : "", "summary" : "", "reason" : "", "remark" : ""}

    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $followup_text=$data_request->followup_text;
    $inq_id=$data_request->inqid;
    $source=$data_request->source;
    $next_action=$data_request->next_action;
    $user_id=$data_request->userid;

    $next_datetime=isset($data_request->next_datetime)?$data_request->next_datetime:"";
    $summary=isset($data_request->summary)?$data_request->summary:"";
    $reason=isset($data_request->reason)?$data_request->reason:"";
    $remark=isset($data_request->remark)?$data_request->remark:"";
    
    $followup_date = date("Y-m-d");
    $admin_userid = "1";
    
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $res_username=$db->get_username($user_id);    

    if(strtolower($next_action)=="next follow up on phone" || strtolower($next_action)=="next follow up by meeting"){
        $resp = $db->insert_followup($user_id,$inq_id,$followup_text,$source,$followup_date);
        $db->insert_reminder($inq_id,$user_id,$next_datetime,$source,$summary,$next_action);
    }
    else if(strtolower($next_action)=="just an update"){
        $resp = $db->insert_followup($user_id,$inq_id,$followup_text,$source,$followup_date);
    }
    else if(strtolower($next_action)=="process application"){
        $followup_text_application = $res_username['name']." has started Application.";
        $source_application = "Auto";
        $stage_application = "applicationstart";

        $resp = $db->insert_followup($user_id,$inq_id,$followup_text,$source,$followup_date);
        $db->insert_followup($user_id,$inq_id,$followup_text_application,$source_application,$followup_date);
        $db->insert_rawassign($inq_id,$user_id,$stage_application);
    }
    else if(strtolower($next_action)=="bad lead (discard)"){
        $followup_text_badlead = $res_username['name']." has marked lead as BAD LEAD. <br />Reason: ".$reason." <br />Remark: ".$remark;
        $source_badlead = "Auto";
        $stage_badlead = "badlead";
        $badlead_type = "lead";

        $resp = $db->insert_followup($user_id,$inq_id,$followup_text,$source,$followup_date);
        $db->insert_followup($user_id,$inq_id,$followup_text_badlead,$source_badlead,$followup_date);
        $db->insert_rawassign($inq_id,$admin_userid,$stage_badlead);
        $db->insert_badleads($reason,$remark,$inq_id,$user_id,$badlead_type);
    }
    
    if($resp>0)
    {
        $data['message'] = "Data added successfully";
        $data['success'] = true;
    }
    else
    {
        $data['data'] = "";
        $data['message'] = "An error occurred";
        $data['success'] = false;
    }

    echoResponse(200, $data);
});

// mark follow up as done
$app->post('/mark_as_done','authenticateUser', function () use ($app) {
    
    //{"followup_text" : "", "inqid" : "", "mark_id" : "", "source" : "", "next_action" : "", "userid" : "", "next_datetime" : "", "summary" : "", "reason" : "", "remark" : ""}


    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $followup_text=$data_request->followup_text;
    $inq_id=$data_request->inqid;
    $mark_id=$data_request->mark_id;
    $source=$data_request->source;
    $next_action=$data_request->next_action;
    $user_id=$data_request->userid;

    $next_datetime=isset($data_request->next_datetime)?$data_request->next_datetime:"";
    $summary=isset($data_request->summary)?$data_request->summary:"";
    $reason=isset($data_request->reason)?$data_request->reason:"";
    $remark=isset($data_request->remark)?$data_request->remark:"";
    
    $followup_date = date("Y-m-d");
    $admin_userid = "1";
    $done_status = "done";
    $followup_done_date = date("Y-m-d H:i:s");
    
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $res_username=$db->get_username($user_id);

    $res_reminder=$db->update_reminder($done_status,$followup_done_date,$mark_id);

    if($res_reminder>0){
        if(strtolower($next_action)=="next follow up on phone" || strtolower($next_action)=="next follow up by meeting"){
            $resp = $db->insert_followup($user_id,$inq_id,$followup_text,$source,$followup_date);
            $db->insert_reminder($inq_id,$user_id,$next_datetime,$source,$summary,$next_action);
        }
        else if(strtolower($next_action)=="just an update"){
            $resp = $db->insert_followup($user_id,$inq_id,$followup_text,$source,$followup_date);
        }
        else if(strtolower($next_action)=="process application"){
            $followup_text_application = $res_username['name']." has started Application.";
            $source_application = "Auto";
            $stage_application = "applicationstart";

            $resp = $db->insert_followup($user_id,$inq_id,$followup_text,$source,$followup_date);
            $db->insert_followup($user_id,$inq_id,$followup_text_application,$source_application,$followup_date);
            $db->insert_rawassign($inq_id,$user_id,$stage_application);
        }
        else if(strtolower($next_action)=="bad lead (discard)"){
            $followup_text_badlead = $res_username['name']." has marked lead as BAD LEAD. <br />Reason: ".$reason." <br />Remark: ".$remark;
            $source_badlead = "Auto";
            $stage_badlead = "badlead";
            $badlead_type = "lead";

            $resp = $db->insert_followup($user_id,$inq_id,$followup_text,$source,$followup_date);
            $db->insert_followup($user_id,$inq_id,$followup_text_badlead,$source_badlead,$followup_date);
            $db->insert_rawassign($inq_id,$admin_userid,$stage_badlead);
            $db->insert_badleads($reason,$remark,$inq_id,$user_id,$badlead_type);
        }
        
        if($resp>0)
        {
            $data['message'] = "Data added successfully";
            $data['success'] = true;
        }
        else
        {
            $data['data'] = "";
            $data['message'] = "An error occurred";
            $data['success'] = false;
        }
    }
    else{
        $data['data'] = "";
        $data['message'] = "An error occurred";
        $data['success'] = false;
    }
    

    echoResponse(200, $data);
});


// get department list 
$app->post('/get_department_list','authenticateUser', function () use ($app) {
    
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_department_list();

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
 
    echoResponse(200, $data);
});

// get department designation list
$app->post('/get_department_designation','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $department = $data_request->department;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_department_designation($department);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
 
    echoResponse(200, $data);
});

// get department user list
$app->post('/get_department_user','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $department = $data_request->department;
    $designation = $data_request->designation;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_department_user($department,$designation);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
 
    echoResponse(200, $data);
});

// assign lead
$app->post('/get_assign_history','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $inq_id = $data_request->inq_id;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_assign_history($inq_id);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
 
    echoResponse(200, $data);
});

// assign lead to user (check user ids and data)
$app->post('/assign_lead','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $loggedin_user_id=$data_request->loggedin_user;
    $selected_user_id=$data_request->selected_user;
    $inq_id=$data_request->inq_id;
    
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $res_logged_username=$db->get_username($loggedin_user_id);
    $res_selected_username=$db->get_username($selected_user_id);

    $raw_assign_status = "lead";
    $followup_text = "<p>".$res_logged_username['name']." has forwarded to ".$res_selected_username['name'].".</p>";
    $followup_source = "Auto";

    $followup_date = date("Y-m-d");
    
    $result_rawassign = $db->insert_rawassign($inq_id,$selected_user_id,$raw_assign_status);

    $result_followup = $db->insert_followup($loggedin_user_id,$inq_id,$followup_text,$followup_source,$followup_date);

    if($result_followup>0)
    {
        $data['message'] = "Data added successfully";
        $data['success'] = true;
    }
    else
    {
        $data['data'] = "";
        $data['message'] = "An error occurred";
        $data['success'] = false;
    }

    echoResponse(200, $data);
});

// lead company list
$app->post('/lead_list','authenticateUser', function () use ($app) {
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $data["data"]["new"] = array();
    $data["data"]["lead"] = array();
    $data["data"]["overdue"] = array();
    $data["data"]["today"] = array();
    $data["data"]["tomorrow"] = array();

    $res_username=$db->get_username($userid);

    $result_new=$db->new_section_company_list($userid);
    $result_remaining=$db->remaining_lead_company_list($userid);
    $result_overdue=$db->overdue_section_company_list($userid);
    $result_today=$db->today_section_company_list($userid);
    $result_tomorrow=$db->tomorrow_section_company_list($userid);

    // if display=true -> show all radio buttons
    // if display=false -> show only 'Just An Update' radio button

    // new section
    if(mysqli_num_rows($result_new)>0){
        while ($row = $result_new->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp['assign'] = ($res_username["role"]=="assignor/verifier")?"true":"false";
            $temp['radiobutton_display'] = "true";
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['new'], $temp);
        }
    }

    // remaining lead section
    if(mysqli_num_rows($result_remaining)>0){
        while ($row = $result_remaining->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp['assign'] = ($res_username["role"]=="assignor/verifier")?"true":"false";
            $temp['radiobutton_display'] = "true";
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['lead'], $temp);
        }
    }

    // overdue section
    if(mysqli_num_rows($result_overdue)>0){
        while ($row = $result_overdue->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['overdue'], $temp);
        }
    }

    // today section
    if(mysqli_num_rows($result_today)>0){
        while ($row = $result_today->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['today'], $temp);
        }
    }

    // tomorrow section
    if(mysqli_num_rows($result_tomorrow)>0){
        while ($row = $result_tomorrow->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['tomorrow'], $temp);
        }
    }

    // $data['assign'] = ($res_username["role"]=="assignor/verifier")?true:false;
    $data['success'] = true;

    echoResponse(200, $data);
});

// new section lead company list
$app->post('/new_lead_list','authenticateUser', function () use ($app) {
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $data["data"]["new"] = array();
    
    $res_username=$db->get_username($userid);

    $result_new=$db->new_section_company_list($userid);
    
    // if display=true -> show all radio buttons
    // if display=false -> show only 'Just An Update' radio button

    // new section
    if(mysqli_num_rows($result_new)>0){
        while ($row = $result_new->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp['assign'] = ($res_username["role"]=="assignor/verifier")?"true":"false";
            $temp['radiobutton_display'] = "true";
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['new'], $temp);
        }
    }

    $data['success'] = true;

    echoResponse(200, $data);
});

// remaining section lead company list
$app->post('/remaining_lead_list','authenticateUser', function () use ($app) {
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $data["data"]["lead"] = array();

    $res_username=$db->get_username($userid);

    $result_remaining=$db->remaining_lead_company_list($userid);

    // if display=true -> show all radio buttons
    // if display=false -> show only 'Just An Update' radio button

    // remaining lead section
    if(mysqli_num_rows($result_remaining)>0){
        while ($row = $result_remaining->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp['assign'] = ($res_username["role"]=="assignor/verifier")?"true":"false";
            $temp['radiobutton_display'] = "true";
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['lead'], $temp);
        }
    }

    $data['success'] = true;

    echoResponse(200, $data);
});

// overdue section lead company list
$app->post('/overdue_lead_list','authenticateUser', function () use ($app) {
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $data["data"]["overdue"] = array();

    $res_username=$db->get_username($userid);

    $result_overdue=$db->overdue_section_company_list($userid);

    // if display=true -> show all radio buttons
    // if display=false -> show only 'Just An Update' radio button

    // overdue section
    if(mysqli_num_rows($result_overdue)>0){
        while ($row = $result_overdue->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['overdue'], $temp);
        }
    }

    $data['success'] = true;

    echoResponse(200, $data);
});

// today section lead company list
$app->post('/today_lead_list','authenticateUser', function () use ($app) {
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $data["data"]["today"] = array();

    $res_username=$db->get_username($userid);

    $result_today=$db->today_section_company_list($userid);

    // if display=true -> show all radio buttons
    // if display=false -> show only 'Just An Update' radio button

    // today section
    if(mysqli_num_rows($result_today)>0){
        while ($row = $result_today->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['today'], $temp);
        }
    }

    $data['success'] = true;

    echoResponse(200, $data);
});

// tomorrow section lead company list
$app->post('/tomorrow_lead_list','authenticateUser', function () use ($app) {
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $data["data"]["tomorrow"] = array();

    $res_username=$db->get_username($userid);

    $result_tomorrow=$db->tomorrow_section_company_list($userid);

    // if display=true -> show all radio buttons
    // if display=false -> show only 'Just An Update' radio button

    // tomorrow section
    if(mysqli_num_rows($result_tomorrow)>0){
        while ($row = $result_tomorrow->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['tomorrow'], $temp);
        }
    }

    $data['success'] = true;

    echoResponse(200, $data);
});

// involved section lead company list
$app->post('/involved_lead_list','authenticateUser', function () use ($app) {
    verifyRequiredParams(array('data'));
    
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $data["data"]["involved"] = array();

    $res_username=$db->get_username($userid);

    $result_involved=$db->involved_section_company_list($userid);

    // if display=true -> show all radio buttons
    // if display=false -> show only 'Just An Update' radio button

    // involved section
    if(mysqli_num_rows($result_involved)>0){
        while ($row = $result_involved->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp['assign'] = "false";
            $temp['radiobutton_display'] = "false";
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['involved'], $temp);
        }
    }

    $data['success'] = true;

    echoResponse(200, $data);
});

// get today's lead count
$app->post('/get_lead_count','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    
    $result=$db->get_lead_count($userid);

    $data['count'] = $result;
    $data['success'] = true;
 
    echoResponse(200, $data);
});

// get all counts of lead section
$app->post('/get_all_section_count','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $userid = $data_request->userid;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_all_section_count($userid);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }
    }

    $data['success'] = true;
 
    echoResponse(200, $data);
});

/*
// get state list 
$app->post('/get_state_list','authenticateUser', function () use ($app) {
    
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_state_list();

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
 
    echoResponse(200, $data);
});

// get city list 
$app->post('/get_city_list','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $state = $data_request->state;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_city_list($state);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp['city'] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
 
    echoResponse(200, $data);
});

// get designation list 
$app->post('/get_designation_list','authenticateUser', function () use ($app) {
    
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_designation_list();

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
 
    echoResponse(200, $data);
});

// get vertical list
$app->post('/get_vertical_list','authenticateUser', function () use ($app) {
    
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_vertical_list();

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
 
    echoResponse(200, $data);
});

// get service name list 
$app->post('/get_service_name_list','authenticateUser', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $vertical_id = $data_request->vertical_id;

    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result=$db->get_service_name_list($vertical_id);

    if(mysqli_num_rows($result)>0){
        while ($row = $result->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data'], $temp);
        }

        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['message'] = "No Data Found";
        $data['success'] = false;
    }
 
    echoResponse(200, $data);
});
*/

function echoResponse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json');
    echo json_encode($response);
}



function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = $_REQUEST;

    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }

    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["error_code"] = 99;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}

function authenticateUser(\Slim\Route $route)
{
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
 
    if (isset($headers['Authorization'])) {
        $db = new DbOperation();
        $api_key = $headers['Authorization'];
        if (!$db->isValidUser($api_key)) {
            $response["success"] = false;
            $response["message"] = "Access Denied. Invalid Device Id";
            echoResponse(401, $response);
            $app->stop();
        }
    } else {
        $response["success"] = false;
        $response["message"] = "Api key is misssing";
        echoResponse(400, $response);
        $app->stop();
    }
}



// fcm notificaton for android
function send_notification_android($data, $reg_ids_android, $title, $body)
{

    $url = 'https://fcm.googleapis.com/fcm/send';
   
   $api_key = 'AAAA2n2PB4A:APA91bEb_4LGpFCH3xTmzG763VWpuV02DGrMmunv1e-bza06vBLdIZgcHaqYu_f7P8a-druZ7buh6b1-OzcLGCP1Yc0bywdVb93dlKQ-BmOgZCVSD135Itw9UKSuNy6rWGqyWr7Q9eLX';
    
    
    $msg = array(
        'title' => $title,
        'body' => $body,
        'icon' => 'myicon',
        'sound' => 'custom_notification.mp3',
        'data' => $data
    );

    $fields = array(
        'registration_ids' => $reg_ids_android,
        'data' => $data,

    );
//print_r($fields);
    $headers = array(
        'Content-Type:application/json',
        'Authorization:key=' . $api_key
    );

    // echo json_encode($fields);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    if ($result === FALSE) {
        //die('FCM Send Error: ' . curl_error($ch));
        $resp=0;
    }
    else{
        $resp=$result;
    }
    curl_close($ch);

    //  echo $result;
    return $resp;
}

// fcm notification code
function send_notification_ios($data, $reg_ids, $title, $body,$send_to)
{
    //$reg_ids[0]="esR5GsVCeEBljF0hszij-k:APA91bEq7A2QCl6Rrt8-__t7OlUemcQOIy_KRe0Zm6h50b8ffZcciHDdnT8f9poGAiW6gcqywi438TWt_aOLN0yk7YKgbOakkvrmTlvVUEtr98aiz69BsgoACxfHXztRmFx-0HprNxLy";
    $url = 'https://fcm.googleapis.com/fcm/send';
    if($send_to=="user")
    {
        $api_key = 'AAAAECnANz8:APA91bGYp0sVe-8WMW7EJt6SHsaHXplVfZb0jniq8kSuw62aruDgcfLkH_-lTSQR2tFu_NSexF7L9tl05c1N1LxcLbrry2q_vE8gv5k4_xXM8GQj32EJDPbJm-FeO532GPO2wp-9sg6K';
    }
    else
    {
        $api_key = 'AAAAECnANz8:APA91bGYp0sVe-8WMW7EJt6SHsaHXplVfZb0jniq8kSuw62aruDgcfLkH_-lTSQR2tFu_NSexF7L9tl05c1N1LxcLbrry2q_vE8gv5k4_xXM8GQj32EJDPbJm-FeO532GPO2wp-9sg6K';
    }
    $msg = array(
        'title' => $title,
        'body' => $body,
        'icon' => 'myicon',
        'sound' => 'custom_notification.mp3',
        'data' => $data
    );
    $fields = array(
        'registration_ids' => $reg_ids,
        'notification' => $msg
    );
//print_r($fields);
    $headers = array(
        'Content-Type:application/json',
        'Authorization:key=' . $api_key
    );

    // echo json_encode($fields);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    if ($result === FALSE) {
    //    die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);

    //  echo $result;
    return $result;
}


$app->run();
