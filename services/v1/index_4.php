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
        
    
   
    

    echoResponse(200, $data);
});

/* *
 * user logout
 * Parameters: uid, device_token,type
 * Method: POST
 * 
 */

$app->post('/logout', function () use ($app) {
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

$app->post('/assigned_estates', function () use ($app) {
    
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
   // print_r($data_request);
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
                  /*$from_plotno_road = $estate_roadplot[$i]->from_plot_no;
                  $to_plotno_road = $estate_roadplot[$i]->to_plot_no;*/
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


$app->post('/add_industrial_estates', function () use ($app) {
    
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
                  /*$from_plotno_road = $estate_roadplot[$i]->from_plot_no;
                  $to_plotno_road = $estate_roadplot[$i]->to_plot_no;*/
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

//get Constitution list
$app->post('/get_constituion_list', function () use ($app) {
    
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
$app->post('/get_segment_list', function () use ($app) {
    
  
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
$app->post('/get_source_type_list', function () use ($app) {
    
  
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
$app->post('/get_source_name_list', function () use ($app) {
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
$app->post('/get_reason_list', function () use ($app) {
    
  
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

$app->post('/assigned_estates_for_company', function () use ($app) {
    
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

// get Plot
$app->post('/get_plot', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $estate_id = $data_request->estate_id;
  
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
        $plot_array = array();
        $res_plot=$db->get_plot_no($result_estate['taluka'],$result_estate['industrial_estate']);
        if(mysqli_num_rows($res_plot)>0)
        {
            while($plot = mysqli_fetch_array($res_plot))
            {
                $raw_data=json_decode($plot["raw_data"]);
                $post_fields=$raw_data->post_fields;
                $plot_details=$raw_data->plot_details;
                asort($plot_details);
                if($post_fields->IndustrialEstate==$result_estate["industrial_estate"] && $post_fields->Taluka==$result_estate["taluka"])
                {
                    foreach ($plot_details as $pd) 
                    {
                        if($pd->Floor == '0')
                        {
                          $plot_array[] = $pd->Plot_No;
                        } 
                    } 
                }
            }

            sort($plot_array);
           
            $temp = array();
            foreach($plot_array as $key =>$value){
                 $temp["plot_no"] = $value;
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
$app->post('/get_floor', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $estate_id = $data_request->estate_id;
    $plot_no=$data_request->plot_no;
    $road_no=$data_request->road_no;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    
    $result_estate=$db->get_ind_estate_data($estate_id);
    
    $res_plot=$db->get_plot_floor($result_estate['taluka'],$result_estate['industrial_estate'],$plot_no,$road_no);

    if(mysqli_num_rows($res_plot)>0)
    {
        while($floor=mysqli_fetch_array($res_plot))
        {
            $row_data=json_decode($floor["raw_data"]);
            $post_fields = $row_data->post_fields;
            if($post_fields->Taluka==$result_estate['taluka'] && $post_fields->IndustrialEstate==$result_estate['industrial_estate'])
            {
                $plot_details=$row_data->plot_details;
                foreach ($plot_details as $pd) 
                {
                    if($pd->Plot_No==$plot_no && $pd->Road_No==$road_no)
                    {
                        $floor_array[] = $pd->Floor;
                    }
                }
            }
        }

        sort($floor_array);
        $temp = array();
        foreach($floor_array as $key =>$value){
            if($value=='0'){
                $temp["floor_no"]="Ground Floor";
            }
            else{
                $temp["floor_no"] = $value;
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
$app->post('/get_road_plot', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $estate_id = $data_request->estate_id;
    
    $road_no=$data_request->road_no;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
    $plot_array = array();
    $result_estate=$db->get_ind_estate($estate_id);
   
    
    $res_plot=$db->get_road_plot($result_estate['taluka'],$result_estate['industrial_estate']);

    if(mysqli_num_rows($res_plot)>0)
    {
        while($plot = mysqli_fetch_array($res_plot))
        {
            $raw_data=json_decode($plot["raw_data"]);
            $post_fields=$raw_data->post_fields;
            $plot_details=$raw_data->plot_details;
            asort($plot_details);
            if($post_fields->IndustrialEstate==$result_estate["industrial_estate"] && $post_fields->Taluka==$result_estate["taluka"])
            {
                foreach ($plot_details as $pd)
                {
                    if($pd->Floor == '0' && $pd->Road_No == $road_no)
                    {
                            $plot_array[] = $pd->Plot_No;
                    } 
                } 
            }
        }

        
        sort($plot_array);
        $temp = array();
        foreach($plot_array as $key =>$value){
        
            $temp["plot_no"] = $value;
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
$app->post('/check_gst', function () use ($app) {
    
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
                $plot_no = $plot_details[0]->Plot_No.' ( Floor No. - '.$plot_details[0]->Floor.' ) ';
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
$app->post('/get_company_details', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $estate_id = $data_request->estate_id;
    $floor_no=$data_request->floor_no;
    $plot_no=$data_request->plot_no;
    $road_no=$data_request->road_no;

    $floor_no=($floor_no=="Ground Floor")?"0":$floor_no;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
  
    $result_estate=$db->get_ind_estate_data($estate_id);
   
    if($plot_no!="" && $floor_no!="")
    {
        $res_company=$db->get_company_details($result_estate['taluka'],$result_estate['industrial_estate'],$plot_no,$floor_no,$road_no);

        $res_pattern=$db->get_ind_estate($estate_id);

        $res_company_plot=$db->get_pr_company_plot($res_pattern['plotting_pattern'],$estate_id,$plot_no,$floor_no,$road_no);

        while($plot = mysqli_fetch_array($res_company)){
                $row_data = json_decode($plot["raw_data"]);
            $post_fields = $row_data->post_fields;

            if($post_fields->IndustrialEstate==$result_estate['industrial_estate'] && $post_fields->Taluka==$result_estate['taluka']){

                $plot_details = $row_data->plot_details;

                foreach ($plot_details as $pd) {
                    if($pd->Floor==$floor_no && $pd->Plot_No==$plot_no){
                        $plot_id = $pd->Plot_Id;
                        $plot_status = $pd->Plot_Status;
                        $road_no = $pd->Road_No;

                        $reason = "";
                        if($row_data->Status=='Negative'){
                            $stmt_reason = $obj->con1->prepare("SELECT bad_lead_reason FROM `tbl_tdbadleads` WHERE inq_id=?");
                            $stmt_reason->bind_param("i",$plot['id']);
                                $stmt_reason->execute();
                                $reason_result = $stmt_reason->get_result()->fetch_assoc();
                                $stmt_reason->close();
                                $reason = $reason_result['bad_lead_reason'];
                        }

                        if($plot['id']!="" && $plot_id!="" && $post_fields->IndustrialEstate!="" && $post_fields->Area!="" && $plot_status!="" && $post_fields->Premise!="" && $post_fields->GST_No!="" && $post_fields->Firm_Name!="" && $post_fields->Contact_Name!="" && $post_fields->Mobile_No!="" && $row_data->Constitution!="" && $post_fields->Category!="" && $post_fields->Segment!="" && $row_data->Status!="" && $post_fields->source!="" && $post_fields->Source_Name!="" && $post_fields->Remarks!="" && $row_data->Image!=""){
                            $details="hide";
                        }
                        else{
                            $details = Array (
                                    "Id" => $plot["id"],
                                    "Plot_Id" => $plot_id,
                                    "Road_No" => $road_no,
                                    "IndustrialEstate" => $post_fields->IndustrialEstate,
                                    "Area" => $post_fields->Area,
                                    "Plot_Status" => $plot_status,
                                    "Premise" => $post_fields->Premise,
                                    "GST_No" => $post_fields->GST_No,
                                    "Firm_Name" => $post_fields->Firm_Name,
                                    "Contact_Name" => $post_fields->Contact_Name,
                                    "Mobile_No" => $post_fields->Mobile_No,
                                    "Constitution" => $row_data->Constitution,
                                    "Category" => $post_fields->Category,
                                    "Segment" => $post_fields->Segment,
                                    "Status" => $row_data->Status,
                                    "Reason" => $reason,
                                    "source" => $post_fields->source,
                                    "Source_Name" => $post_fields->Source_Name,
                                    "Remarks" => $post_fields->Remarks,
                                    "Image" => $row_data->Image,
                                    "Company_detail_id" => $res_company_plot["company_id"],
                                    "Company_plot_id" => $res_company_plot["pid"]
                            );
                        }

                        
                        break;
                    }
                }
            }
        }
    }
    else
    {
        $details = Array (
                "Id" => "",
                "Plot_Id" => "",
                "Road_No" => "",
                "IndustrialEstate" => "",
                "Area" => "",
                "Plot_Status" => "",
                    "Premise" => "",
                    "GST_No" => "",
                    "Firm_Name" => "",
                    "Contact_Name" => "",
                "Mobile_No" => "",
                "Constitution" => "",
                "Category" => "",
                "Segment" => "",
                "Status" => "",
                "source" => "",
                "Source_Name" => "",
                "Remarks" => "",
                "Image" => "",
                "Company_detail_id" => "",
                "Company_plot_id" => "",
            );
    }

    if($post_fields->Contact_Name!="" && $post_fields->Mobile_No!=""){
        $data['message'] = "show add plot";
    }
    else{
        $data['message'] = "";
    }
    $data["data"]=$details;
    
    $data['success'] = true;
    
    
    echoResponse(200, $data);
});


//verify plot no
$app->post('/verify_plot_no', function () use ($app) {
    
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
$app->post('/verify_road_no', function () use ($app) {
    
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
$app->post('/insert_company', function () use ($app) {
    
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
    $badlead_type = "lead";
    $PicFileName="";

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
        unlink("../../gst_image/".$old_img);  
        //rename file for gst image
        $img = $img_path = $_FILES["company_img"]["name"];
        $img_path = $_FILES["company_img"]["tmp_name"];
          if(file_exists("gst_image/" . $img)) {
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
    $row_data->Status = $status;
    $post_fields->source = $source;
    $post_fields->Source_Name = $source_name;
    $row_data->Image = $PicFileName;
    $post_fields->Remarks = $remark;
    $plot_details["$plot_index"]->Plot_Status = $plot_status;
    if($status=='Negative'){
        $row_data->bad_lead_reason = $badlead_reason;
    }
    else{
        $row_data->bad_lead_reason = ""; 
    }

    $json_object = json_encode($row_data);

    $result=$db->update_tbl_tdrawdata($json_object,$user_id,$id);

    if($result==-1){
        $data['message'] = "An error occurred";
        $data['success'] = false;
    }
    else{
        if($result>0){
            $result_visit_count=$db->pr_visit_count($post_fields->IndustrialEstate,$post_fields->Area,$post_fields->Taluka,$id,$user_id,$result);

            $followup_source = "Auto";
            $followup_date = date("Y-m-d");
            $admin_userid = '1';

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

        // insert into pr_company_detail and pr_company_plot table
        $inq_submit = "Submit";
        
        $result_rawassign = $db->insert_pr_company_detail($source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$post_fields->state,$post_fields->city,$post_fields->Taluka,$post_fields->Area,$post_fields->IndustrialEstate,$remark,$inq_submit,$PicFileName,$constitution,$status,$industrial_estate_id,$user_id,$id,$plot_status,$pr_company_plot_id,$pr_company_detail_id);
        

        $data['message'] = "Data added successfully";
        $data['success'] = true;
    }

    
    echoResponse(200, $data);
});


// check additional plot number
$app->post('/check_additional_plot', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $additional_plot_no = $data_request->additional_plot_no;
    $road_no=$data_request->road_no;
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
$app->post('/add_additional_plot', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $additional_plot_no = strtoupper($data_request->additional_plot_no);
    $road_no=$data_request->road_no;
    $estate_id=$data_request->estate_id;
    $user_id=$data_request->user_id;
    $floor = '0';
    $road_number = ($road_no=="")?NULL:$road_no;
    $plot_id = '1';
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

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

        $resp_tdrawdata=$db->tbl_tdrawdata($json,$user_id);

        // insert into pr_company_plot
        $resp_company_plot=$db->company_plot_insert($additional_plot_no,$floor,$road_number,$plot_id,$estate_id,$user_id);



        // dropdowns
        $data["data"]["Refill Data"] = array();
        $data["data"]["Industrial Estate"] = array();
        $data["data"]["Plot No"] = array();
        $data["data"]["Floor No"] = array();

        // estate
        $resp_estate=$db->industrial_estate_company_list($user_id);
        while ($row = $resp_estate->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['Industrial Estate'], $temp);
        }
        
        $refill_data = array();

        if($road_no!=""){
            $data["data"]["Road No"] = array();
            $refill_data['road_no'] = $road_no;

            // road no
            $resp_road=$db->roadno_for_roadwise($estate_id);
            while ($row = $resp_road->fetch_assoc()) {
                $temp = array();
                foreach ($row as $key => $value) {
                    $temp[$key] = $value;
                }
                $temp = array_map('utf8_encode', $temp);
                array_push($data['data']['Road No'], $temp);
            }

            // plot no
            $resp_plot=$db->plotno_for_roadwise($estate_id,$road_no);
            $temp = array();
            foreach($resp_plot as $key =>$value){
                $temp["plot_no"] = $value;
                $temp = array_map('utf8_encode', $temp);
                array_push($data['data']['Plot No'], $temp);
            }
        }
        else{
            // plot no
            $resp_plot=$db->plotno_for_serieswise($estate_id);
            $temp = array();
            foreach($resp_plot as $key =>$value){
                 $temp["plot_no"] = $value;
                 $temp = array_map('utf8_encode', $temp);
                array_push($data['data']['Plot No'], $temp);
            }
            
        }

        // floor no
        $resp_floor=$db->floorno_list($estate_id,$road_no,$additional_plot_no);
        $temp = array();
        foreach($resp_floor as $key =>$value){
            if($value=='0'){
                $temp["floor_no"]="Ground Floor";
            }
            else{
                $temp["floor_no"] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['Floor No'], $temp);
        }

        $refill_data['estate_id'] = $estate_id;
        $refill_data['plot_no'] = $additional_plot_no;
        $refill_data['floor'] = $floor;
        $refill_data = array_map('utf8_encode', $refill_data);
        array_push($data['data']['Refill Data'], $refill_data);

    }
    else
    {
        $data['message'] = "An error occurred! Data not inserted";
        $data['success'] = false;
    }
    
    if($resp_company_plot>0)
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


// get floor list for add floor modal
$app->post('/get_floor_floormodal', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $plot_no = $data_request->plot_no;
    $road_no=$data_request->road_no;
    $estate_id=$data_request->estate_id;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result_estate=$db->get_ind_estate_data($estate_id);

    $result=$db->get_floor_floormodal($result_estate['industrial_estate'],$result_estate['area_id'],$plot_no,$road_no);
    
    foreach ($result as $floor_no){
        $data['data'][] = $floor_no;
    }
    
    $data['message'] = "";
    $data['success'] = true;

    echoResponse(200, $data);
});


// add floor of floor modal
$app->post('/add_floor', function () use ($app) {
    
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

        $result=$db->update_tbl_tdrawdata($json_object,$user_id,$id);
      
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

      $plot_id = $last_plot_id+1;

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

      $result=$db->tbl_tdrawdata($json,$user_id,$id);
      
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
        // dropdowns
        $data["data"]["Refill Data"] = array();
        $data["data"]["Industrial Estate"] = array();
        $data["data"]["Plot No"] = array();
        $data["data"]["Floor No"] = array();

        // estate
        $resp_estate=$db->industrial_estate_company_list($user_id);
        while ($row = $resp_estate->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['Industrial Estate'], $temp);
        }
        
        $refill_data = array();

        if($road_no!=""){
            $data["data"]["Road No"] = array();
            $refill_data['road_no'] = $road_no;

            // road no
            $resp_road=$db->roadno_for_roadwise($estate_id);
            while ($row = $resp_road->fetch_assoc()) {
                $temp = array();
                foreach ($row as $key => $value) {
                    $temp[$key] = $value;
                }
                $temp = array_map('utf8_encode', $temp);
                array_push($data['data']['Road No'], $temp);
            }

            // plot no
            $resp_plot=$db->plotno_for_roadwise($estate_id,$road_no);
            $temp = array();
            foreach($resp_plot as $key =>$value){
                $temp["plot_no"] = $value;
                $temp = array_map('utf8_encode', $temp);
                array_push($data['data']['Plot No'], $temp);
            }
        }
        else{
            // plot no
            $resp_plot=$db->plotno_for_serieswise($estate_id);
            $temp = array();
            foreach($resp_plot as $key =>$value){
                 $temp["plot_no"] = $value;
                 $temp = array_map('utf8_encode', $temp);
                array_push($data['data']['Plot No'], $temp);
            }
            
        }

        // floor no
        $resp_floor=$db->floorno_list($estate_id,$road_no,$plot_no);
        $temp = array();
        foreach($resp_floor as $key =>$value){
            if($value=='0'){
                $temp["floor_no"]="Ground Floor";
            }
            else{
                $temp["floor_no"] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['Floor No'], $temp);
        }

        $refill_data['estate_id'] = $estate_id;
        $refill_data['plot_no'] = $plot_no;
        $refill_data['floor'] = $floor;
        $refill_data['plot_status'] = $plot_status;

        if($floor_confirmation=='Same Owner But Different Company'){
            $refill_data['contact_person'] = $post_fields->Contact_Name;
            $refill_data['contact_no'] = $post_fields->Mobile_No;
        }

        $refill_data = array_map('utf8_encode', $refill_data);
        array_push($data['data']['Refill Data'], $refill_data);
    }

    if($resp_company_plot>0){
        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['data'] = "";
        $data['message'] = "An error occurred! Data not inserted";
        $data['success'] = false;   
    }

    echoResponse(200, $data);
});

// get floor list for add plot modal
$app->post('/get_floor_plotmodal', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $plot_no = $data_request->plot_no;
    $road_no=$data_request->road_no;
    $estate_id=$data_request->estate_id;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();

    $result_estate=$db->get_ind_estate_data($estate_id);

    $result=$db->get_floor_plotmodal($result_estate['industrial_estate'],$result_estate['area_id'],$plot_no,$road_no);
      
    if(count($result)>0){
        foreach($result as $floor_no){
            if($floor_no=='0'){
                $data['data'][] = "Ground Floor";
            }
            else{
                $data['data'][] = $floor_no;
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
$app->post('/add_plot', function () use ($app) {
    
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

    $result_plot_search=$db->get_plot_floor($result_estate['taluka'],$result_estate['industrial_estate'],$plot_no,$road_no);

    $res_pattern=$db->get_ind_estate($estate_id);

    $res_company_plot=$db->get_pr_company_plot($res_pattern['plotting_pattern'],$estate_id,$plot_no,$floor,$road_no);

    $pr_company_plot_id = $res_company_plot['pid'];

    if($plot_confirmation=='Same Company'){  // Same Company As Ground
      
        $res_rawdata=$db->get_rawdata($id);

        $row_data=json_decode($res_rawdata["raw_data"]);
        $post_fields = $row_data->post_fields;

        if(isset($row_data->plot_details)){
            $plot_details = $row_data->plot_details;
            $arr_count = count($plot_details);
            $last_plot_id = $plot_details[$arr_count-1]->Plot_Id;

            $new_plot_detail=Array(
              "Plot_No" => $plot_no,
              "Floor" => $floor,
              "Road_No" => $road_no,
              "Plot_Status" => $plot_status,
              "Plot_Id" => ($last_plot_id+1),
            );  
            array_push($row_data->plot_details, $new_plot_detail);
        }
        else{
            $row_data->plot_details=Array(
              Array(
                "Plot_No" => $plot_no,
                "Floor" => $floor,
                "Road_No" => $road_no,
                "Plot_Status" => $plot_status,
                "Plot_Id" => "1",
              ),
            );  
        }
        
        $json_object = json_encode($row_data);

        $result=$db->update_tbl_tdrawdata($json_object,$user_id,$id);

        if($result==-1){
            $data['message'] = "An error occurred";
            $data['success'] = false;
        }
        else{
            if($result>0){
            // for pr_visit_count table
            $result_visit_count=$db->pr_visit_count($post_fields->IndustrialEstate,$post_fields->Area,$post_fields->Taluka,$id,$user_id,$result);
          }
        }

      $plot_id = $last_plot_id+1;

      $result_company_plot=$db->company_plot_update($plot_status,$plot_id,$pr_company_detail_id,$pr_company_plot_id,$user_id);
    }
    else if($plot_confirmation=='Same Owner But Different Company'){   // Same Owner As Ground But Different Company
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
      
        $plot_id = '1';

        // insert in pr_company_details
        $result_companydetail_id=$db->company_details_insert($post_fields->Contact_Name,$post_fields->Mobile_No,$result_estate["state_id"], $result_estate["city_id"], $result_estate["taluka"], $result_estate["area_id"], $result_estate["industrial_estate"],$estate_id,$user_id,$result_rawdata_id);

        // insert in pr_company_plot 
        $result_company_plot=$db->company_plot_update($plot_status,$plot_id,$pr_company_detail_id,$pr_company_plot_id,$user_id);


        // dropdowns
        $data["data"]["Refill Data"] = array();
        $data["data"]["Industrial Estate"] = array();
        $data["data"]["Plot No"] = array();
        $data["data"]["Floor No"] = array();

        // estate
        $resp_estate=$db->industrial_estate_company_list($user_id);
        while ($row = $resp_estate->fetch_assoc()) {
            $temp = array();
            foreach ($row as $key => $value) {
                $temp[$key] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['Industrial Estate'], $temp);
        }
        
        $refill_data = array();

        if($road_no!=""){
            $data["data"]["Road No"] = array();
            $refill_data['road_no'] = $road_no;

            // road no
            $resp_road=$db->roadno_for_roadwise($estate_id);
            while ($row = $resp_road->fetch_assoc()) {
                $temp = array();
                foreach ($row as $key => $value) {
                    $temp[$key] = $value;
                }
                $temp = array_map('utf8_encode', $temp);
                array_push($data['data']['Road No'], $temp);
            }

            // plot no
            $resp_plot=$db->plotno_for_roadwise($estate_id,$road_no);
            $temp = array();
            foreach($resp_plot as $key =>$value){
                $temp["plot_no"] = $value;
                $temp = array_map('utf8_encode', $temp);
                array_push($data['data']['Plot No'], $temp);
            }
        }
        else{
            // plot no
            $resp_plot=$db->plotno_for_serieswise($estate_id);
            $temp = array();
            foreach($resp_plot as $key =>$value){
                 $temp["plot_no"] = $value;
                 $temp = array_map('utf8_encode', $temp);
                array_push($data['data']['Plot No'], $temp);
            }
            
        }

        // floor no
        $resp_floor=$db->floorno_list($estate_id,$road_no,$plot_no);
        $temp = array();
        foreach($resp_floor as $key =>$value){
            if($value=='0'){
                $temp["floor_no"]="Ground Floor";
            }
            else{
                $temp["floor_no"] = $value;
            }
            $temp = array_map('utf8_encode', $temp);
            array_push($data['data']['Floor No'], $temp);
        }

        $refill_data['estate_id'] = $estate_id;
        $refill_data['plot_no'] = $plot_no;
        $refill_data['floor'] = $floor;
        $refill_data['plot_status'] = $plot_status;
        $refill_data['contact_person'] = $post_fields->Contact_Name;
        $refill_data['contact_no'] = $post_fields->Mobile_No;
        
        $refill_data = array_map('utf8_encode', $refill_data);
        array_push($data['data']['Refill Data'], $refill_data);
    }

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

    if($result_company_plot>0){
        $data['message'] = "";
        $data['success'] = true;
    }
    else{
        $data['data'] = "";
        $data['message'] = "An error occurred! Data not inserted";
        $data['success'] = false;   
    }

    echoResponse(200, $data);
});





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
            $response["message"] = "Access Denied. Invalid Api key";
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
