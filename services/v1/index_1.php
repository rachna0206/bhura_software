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
    
    $db = new DbOperation();
    $data = array();
    
    $response = array();
   
    if ($db->assignorLogin($email, $password)) 
    {

        $assignor_data = $db->assignor_data($email);
        $res = $db->insert_device_type($device_type, $token, $assignor_data['id']);
        $response = array();
        foreach ($assignor_data as $key => $value) {
            $response[$key]= $value;
        }
        $data["data"]=$response;
        $data["success"] = true;
        $data["message"]="Successfully Logged In!";
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
    $res = $db->logout($uid, $device_token, $type);

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

$app->post('/add_plotting_old_estates', function () use ($app) {
    
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
                  $from_plotno_road = $estate_roadplot[$i]->from_plot_no;
                  $to_plotno_road = $estate_roadplot[$i]->to_plot_no;
                  $road_plot_cnt = $estate_roadplot[$i]->road_plot_cnt;
                  $additional_plot=$estate_roadplot[$i]->additional_plot;
                  if($from_plotno_road!="" || $from_plotno_road!=null){
                
                    $resp_plot=$db->pr_estate_roadplot($industrial_estate_id,$road_number,$from_plotno_road,$to_plotno_road,$userid);
                    

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

                    // for additional plot in road wise
                    if($road_plot_cnt>0){
                      for($c=0;$c<$road_plot_cnt;$c++){
                        $additional_plotno = $additional_plot[$c]->additional_plotno_road;  
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
                  $from_plotno_road = $estate_roadplot[$i]->from_plot_no;
                  $to_plotno_road = $estate_roadplot[$i]->to_plot_no;
                  $road_plot_cnt = $estate_roadplot[$i]->road_plot_cnt;
                  $additional_plot=$estate_roadplot[$i]->additional_plot;
                  if($from_plotno_road!="" || $from_plotno_road!=null){
                
                    $resp_plot=$db->pr_estate_roadplot($industrial_estate_id,$road_number,$from_plotno_road,$to_plotno_road,$userid);
                    

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

                    // for additional plot in road wise
                    if($road_plot_cnt>0){
                      for($c=0;$c<$road_plot_cnt;$c++){
                        $additional_plotno = $additional_plot[$c]->additional_plotno_road;  
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
    
    $result=$db->get_source_type_list();

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

//get associate list
$app->post('/get_associate_list', function () use ($app) {
    
  
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
});


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
                    if($pd->Plot_No==$plot_no)
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
                //$html.='<option value="'.$floor_no.'">Ground Floor</option>';
                $temp["floor_no"]="Ground Floor";
            }
            else{
               // $html.='<option value="'.$floor_no.'">'.$floor_no.'</option>';
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


// get company details
$app->post('/get_company_details', function () use ($app) {
    
    verifyRequiredParams(array('data'));
    $data_request = json_decode($app->request->post('data'));
    $estate_id = $data_request->estate_id;
    $floor_no=$data_request->floor_no;
    $plot_no=$data_request->plot_no;
  
    $db = new DbOperation();
    $data = array();
    $data["data"] = array();
  
    $result_estate=$db->get_ind_estate_data($estate_id);
   
    if($plot_no!="" && $floor_no!="")
    {
        $res_company=$db->get_company_details($result_estate['taluka'],$result_estate['industrial_estate'],$plot_no,$floor_no);
        while($plot = mysqli_fetch_array($company_details)){
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
                        );
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
            );
    }

   
    $data["data"]=$details;
    $data['message'] = "";
    $data['success'] = true;
    
    
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
