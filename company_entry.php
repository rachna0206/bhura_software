<?php
  include("header.php");

$user_id = $_SESSION["id"];

// Assigned Industrial Estate List
$stmt_estate_list = $obj->con1->prepare("SELECT a1.*,i1.industrial_estate, i1.taluka FROM assign_estate a1, tbl_industrial_estate i1, pr_add_industrialestate_details p1 WHERE a1.industrial_estate_id=i1.id and i1.id=p1.industrial_estate_id and employee_id=? and start_dt<=curdate() and end_dt>=curdate() and a1.action='company_entry' and p1.status='Verified'");
$stmt_estate_list->bind_param("i",$user_id);
$stmt_estate_list->execute();
$estate_result = $stmt_estate_list->get_result();
$stmt_estate_list->close();

// Contitution List
$stmt_constitution_list = $obj->con1->prepare("SELECT * FROM `tbl_constitution_master`");
$stmt_constitution_list->execute();
$constitution_result = $stmt_constitution_list->get_result();
$stmt_constitution_list->close();

// Segment List
$stmt_segment_list = $obj->con1->prepare("SELECT * FROM `tbl_segment`");
$stmt_segment_list->execute();
$segment_result = $stmt_segment_list->get_result();
$stmt_segment_list->close();

// Source-Type List for Source
$stmt_source_list = $obj->con1->prepare("SELECT * FROM `tbl_sourcetype_master`");
$stmt_source_list->execute();
$source_result = $stmt_source_list->get_result();
$stmt_source_list->close();

// Associate-Type List for Source
$stmt_associate_list = $obj->con1->prepare("SELECT * FROM `asso_segment_master`");
$stmt_associate_list->execute();
$associate_result = $stmt_associate_list->get_result();
$stmt_associate_list->close();

// Badlead Reason List when Status=Negative
$stmt_badlead_reason = $obj->con1->prepare("SELECT * FROM `tbl_badlead_reasons` WHERE STATUS='active' ORDER BY reason_text");
$stmt_badlead_reason->execute();
$badlead_reason_result = $stmt_badlead_reason->get_result();
$stmt_badlead_reason->close();



// insert data
if(isset($_REQUEST['btnsubmit']))
{
  $plot_status = isset($_REQUEST['plot_status'])?$_REQUEST['plot_status']:"";
  $premise = $_REQUEST['premises'];
  $gst_no = $_REQUEST['gst_no'];
  $firm_name = $_REQUEST['firm_name'];
  $contact_no = $_REQUEST['contact_no'];
  $contact_person = $_REQUEST['contact_person'];
  $constitution = $_REQUEST['constitution'];
  $category = $_REQUEST['category'];
  $segment = $_REQUEST['segment'];
  $status = isset($_REQUEST['status'])?$_REQUEST['status']:"";
  if($status=='Negative'){
    $badlead_reason = $_REQUEST['badlead_reasons'];
    $badlead_type = "lead";
  }
  $source = $_REQUEST['source'];
  $source_name = $_REQUEST['source_name'];
  $remark = $_REQUEST['remark'];
  $img = $_FILES['img']['name'];
  $img_path = $_FILES['img']['tmp_name'];
  $old_img = $_REQUEST['himage'];
  $id = $_REQUEST['ttId'];
  $plot_id = $_REQUEST['plot_index'];
  $industrial_estate_id = $_REQUEST['industrial_estate_id'];
  $pr_company_plot_id = $_REQUEST['pr_company_plot_id'];
  $pr_company_detail_id = $_REQUEST['pr_company_detail_id'];
  
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

  //$state = $post_fields->State;

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
  $post_fields->loan_applied = $_COOKIE['loan_sanction'];
  $post_fields->Completion_Date = $_COOKIE['completion_date'];
  $post_fields->Existing_client_status = $_COOKIE['expansion_status'];
  $plot_details["$plot_index"]->Plot_Status = $plot_status;
  if($_COOKIE['expansion_status']=="positive for expansion")
  {
    $row_data->Status = "positive";
  }
  else if($_COOKIE['expansion_status']=="negative for expansion")
  {
    $row_data->Status = "negative";
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

  try
  {
    $stmt = $obj->con1->prepare("update tbl_tdrawdata set raw_data=?, userid=? where id=?");
    $stmt->bind_param("sii",$json_object,$user_id,$id);
    $Resp=$stmt->execute();
    
    // for pr_visit_count table
    // if the data is updated by an employee on different date then count+1
    if(mysqli_affected_rows($obj->con1)>0){

      $stmt_count_list = $obj->con1->prepare("SELECT `cid`, `count`, date(`datetime`) as datetime FROM `pr_visit_count` WHERE industrial_estate=? and area=? and taluka=? and company_id=? and employee_id=?");
      $stmt_count_list->bind_param("sssii",$post_fields->IndustrialEstate,$post_fields->Area,$post_fields->Taluka,$id,$user_id);
      $stmt_count_list->execute();
      $count_result = $stmt_count_list->get_result();
      $stmt_count_list->close();

      if(mysqli_num_rows($count_result)>0){
        $count = mysqli_fetch_array($count_result);
        if(strtotime($count['datetime'])!=strtotime(date("Y-m-d"))){
          $stmt_count = $obj->con1->prepare("UPDATE `pr_visit_count` set `count`=`count`+1 where `cid`=? and `employee_id`=?");
          $stmt_count->bind_param("ii",$count['cid'],$user_id);
          $Resp=$stmt_count->execute();
          $stmt_count->close();
          
          $stmt_visit_date = $obj->con1->prepare("INSERT INTO `pr_visit_dates`(`visit_count_id`) VALUES (?)");
          $stmt_visit_date->bind_param("i",$count['cid']);
          $Resp=$stmt_visit_date->execute();
          $stmt_visit_date->close();
        }
      }
      else{
        $count=1;
        $stmt_count = $obj->con1->prepare("INSERT INTO `pr_visit_count`(`industrial_estate`, `area`, `taluka`, `company_id`, `employee_id`, `count`) VALUES (?,?,?,?,?,?)");
        $stmt_count->bind_param("sssiii",$post_fields->IndustrialEstate,$post_fields->Area,$post_fields->Taluka,$id,$user_id,$count);
        $Resp=$stmt_count->execute();
        $last_insert_id = mysqli_insert_id($obj->con1);
        $stmt_count->close();
        
        $stmt_visit_date = $obj->con1->prepare("INSERT INTO `pr_visit_dates`(`visit_count_id`) VALUES (?)");
        $stmt_visit_date->bind_param("i",$last_insert_id);
        $Resp=$stmt_visit_date->execute();
        $stmt_visit_date->close();
      }


      // for insert into rawassign and followup table 
      $followup_source = "Auto";
      $followup_date = date("Y-m-d");
      $admin_userid = '1';

      if($status=="Positive" || $status=="Negative")
      {
        if(checkCompany_rawassign($id)){
          // insert into follow up
          $followup_text = "<p>".$_SESSION["username"]." has edited a lead data in system.</p>";
          
          $stmt_followup = $obj->con1->prepare("INSERT INTO `tbl_tdfollowup`(`user_id`, `inq_id`, `followup_text`, `followup_source`, `followup_date`) VALUES (?,?,?,?,?)");
          $stmt_followup->bind_param("iisss",$user_id,$id,$followup_text,$followup_source,$followup_date);
          $Resp=$stmt_followup->execute();
          $stmt_followup->close();
        }
        else{
          // insert into raw assign and follow up
          $raw_assign_status = "lead";
          $followup_text = "<p>".$_SESSION["username"]." has added a data in system.</p>";
          
          $stmt_status = $obj->con1->prepare("INSERT INTO `tbl_tdrawassign`(`inq_id`, `user_id`, `stage`) VALUES (?,?,?)");
          $stmt_status->bind_param("iis",$id,$admin_userid,$raw_assign_status);
          $Resp=$stmt_status->execute();
          $stmt_status->close();

          $stmt_followup = $obj->con1->prepare("INSERT INTO `tbl_tdfollowup`(`user_id`, `inq_id`, `followup_text`, `followup_source`, `followup_date`) VALUES (?,?,?,?,?)");
          $stmt_followup->bind_param("iisss",$user_id,$id,$followup_text,$followup_source,$followup_date);
          $Resp=$stmt_followup->execute();
          $stmt_followup->close();
        }

        if($status=='Negative'){
          if(check_for_badlead($id)==0){
            $badlead_raw_assign_status = "badlead";
            $badlead_followup_text = $_SESSION["username"]." has marked lead as BAD LEAD. <br />Reason: ".$badlead_reason." <br />Remark: ".$remark;

            $stmt_badlead = $obj->con1->prepare("INSERT INTO `tbl_tdbadleads`(`bad_lead_reason`, `bad_lead_reason_remark`, `inq_id`, `user_id`, `type`) VALUES (?,?,?,?,?)");
            $stmt_badlead->bind_param("sssis",$badlead_reason,$remark,$id,$user_id,$badlead_type);
            $Resp=$stmt_badlead->execute();
            $stmt_badlead->close();

            $stmt_status = $obj->con1->prepare("INSERT INTO `tbl_tdrawassign`(`inq_id`, `user_id`, `stage`) VALUES (?,?,?)");
            $stmt_status->bind_param("iis",$id,$admin_userid,$badlead_raw_assign_status);
            $Resp=$stmt_status->execute();
            $stmt_status->close();

            $stmt_followup = $obj->con1->prepare("INSERT INTO `tbl_tdfollowup`(`user_id`, `inq_id`, `followup_text`, `followup_source`, `followup_date`) VALUES (?,?,?,?,?)");
            $stmt_followup->bind_param("iisss",$user_id,$id,$badlead_followup_text,$followup_source,$followup_date);
            $Resp=$stmt_followup->execute();
            $stmt_followup->close();
          }  
        }
        else{
          if(check_for_badlead($id)==1){
            // insert into raw assign and follow up
            $raw_assign_status = "lead";
            
            $stmt_status = $obj->con1->prepare("INSERT INTO `tbl_tdrawassign`(`inq_id`, `user_id`, `stage`) VALUES (?,?,?)");
            $stmt_status->bind_param("iis",$id,$admin_userid,$raw_assign_status);
            $Resp=$stmt_status->execute();
            $stmt_status->close();
          }
        }  
      }
    }

    // insert into pr_company_detail and pr_company_plot table
    if($pr_company_detail_id==""){
      
      $stmt_pr_company_detail = $obj->con1->prepare("INSERT INTO `pr_company_details`(`source`, `source_name`, `contact_name`, `mobile_no`, `firm_name`, `gst_no`, `category`, `segment`, `premise`, `state`, `city`, `taluka`, `area`, `industrial_estate`, `remarks`, `inq_submit`, `image`, `constitution`, `status`, `industrial_estate_id`, `user_id`, `rawdata_id`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $stmt_pr_company_detail->bind_param("sssssssssssssssssssiii",$source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$post_fields->state,$post_fields->city,$post_fields->Taluka,$post_fields->Area,$post_fields->IndustrialEstate,$remark,$inq_submit,$PicFileName,$constitution,$status,$industrial_estate_id,$user_id,$id);
      $Resp=$stmt_pr_company_detail->execute();
      $last_insert_company_id = mysqli_insert_id($obj->con1);
      $stmt_pr_company_detail->close();
      

      $stmt_pr_company_plot = $obj->con1->prepare("UPDATE `pr_company_plots` SET `plot_status`=?, `company_id`=?, `user_id`=? WHERE `pid`=?");
      $stmt_pr_company_plot->bind_param("siii",$plot_status,$last_insert_company_id,$user_id,$pr_company_plot_id);
      $Resp=$stmt_pr_company_plot->execute();
      $stmt_pr_company_plot->close();
    }
    else{
      $stmt_pr_company_detail = $obj->con1->prepare("UPDATE `pr_company_details` SET `source`=?, `source_name`=?, `contact_name`=?, `mobile_no`=?, `firm_name`=?, `gst_no`=?, `category`=?, `segment`=?, `premise`=?, `remarks`=?, `inq_submit`=?, `image`=?, `constitution`=?, `status`=?, `user_id`=?, `rawdata_id`=? WHERE `cid`=?");
      $stmt_pr_company_detail->bind_param("ssssssssssssssiii",$source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$remark,$inq_submit,$PicFileName,$constitution,$status,$user_id,$id,$pr_company_detail_id);
      $Resp=$stmt_pr_company_detail->execute();
      $stmt_pr_company_detail->close();

      $stmt_pr_company_plot = $obj->con1->prepare("UPDATE `pr_company_plots` SET `plot_status`=?, `company_id`=?, `user_id`=? WHERE `pid`=?");
      $stmt_pr_company_plot->bind_param("siii",$plot_status,$pr_company_detail_id,$user_id,$pr_company_plot_id);
      $Resp=$stmt_pr_company_plot->execute();
      $stmt_pr_company_plot->close();
    }

    if(!$Resp)
    {
      throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
    }
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }
  $stmt->close();

  if($Resp)
  {
    setcookie("msg", "data",time()+3600,"/");
    header("location:company_entry.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:company_entry.php");
  }
}



// for floor modal
if(isset($_REQUEST['btn_modal_insert_floor']))
{
  $floor_confirmation = $_REQUEST['floor_confirmation'];
  $plot_no = $_REQUEST['plot_no_floormodal'];
  $road_no = $_REQUEST['road_no_floormodal'];
  $floor = $_REQUEST['floor_floormodal'];
  $plot_status = $_REQUEST['plot_status_floormodal'];
  $estate_id = $_REQUEST['estateid_floormodal'];
  $id=$_REQUEST['floormodal_ttId'];
  $arr_cookie = array();

  $pr_company_detail_id = $_REQUEST['pr_company_detail_id_floormodal'];
  $road_number = ($road_no=="")?NULL:$road_no;

  $stmt_estate = $obj->con1->prepare("SELECT * FROM tbl_industrial_estate WHERE id=?");
  $stmt_estate->bind_param("i",$estate_id);
  $stmt_estate->execute();
  $estate_result_floor = $stmt_estate->get_result()->fetch_assoc();
  $stmt_estate->close();
  
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

      //echo $json_object;

      $stmt = $obj->con1->prepare("update tbl_tdrawdata set raw_data=? where id=?");
      $stmt->bind_param("si", $json_object,$id);
      $Resp=$stmt->execute();
      $stmt->close();

      // for pr_visit_count table
      // if the data is updated by an employee on different date then count+1
      if(mysqli_affected_rows($obj->con1)>0){

        $stmt_count_list = $obj->con1->prepare("SELECT `cid`, `count`, date(`datetime`) as datetime FROM `pr_visit_count` WHERE industrial_estate=? and area=? and taluka=? and company_id=? and employee_id=?");
        $stmt_count_list->bind_param("sssii",$estate_result_floor['industrial_estate'],$estate_result_floor['area_id'],$estate_result_floor['taluka'],$id,$user_id);
        $stmt_count_list->execute();
        $count_result = $stmt_count_list->get_result();
        $stmt_count_list->close();

        if(mysqli_num_rows($count_result)>0){
          $count = mysqli_fetch_array($count_result);
          if(strtotime($count['datetime'])!=strtotime(date("Y-m-d"))){
            $stmt_count = $obj->con1->prepare("UPDATE `pr_visit_count` set `count`=`count`+1 where `cid`=? and `employee_id`=?");
            $stmt_count->bind_param("ii",$count['cid'],$user_id);
            $Resp=$stmt_count->execute();
            $stmt_count->close();
            
            $stmt_visit_date = $obj->con1->prepare("INSERT INTO `pr_visit_dates`(`visit_count_id`) VALUES (?)");
            $stmt_visit_date->bind_param("i",$count['cid']);
            $Resp=$stmt_visit_date->execute();
            $stmt_visit_date->close();
          }
        }
        else{
          $count=1;
          $stmt_count = $obj->con1->prepare("INSERT INTO `pr_visit_count`(`industrial_estate`, `area`, `taluka`, `company_id`, `employee_id`, `count`) VALUES (?,?,?,?,?,?)");
          $stmt_count->bind_param("sssiii",$post_fields->IndustrialEstate,$post_fields->Area,$post_fields->Taluka,$id,$user_id,$count);
          $Resp=$stmt_count->execute();
          $last_insert_id = mysqli_insert_id($obj->con1);
          $stmt_count->close();
          
          $stmt_visit_date = $obj->con1->prepare("INSERT INTO `pr_visit_dates`(`visit_count_id`) VALUES (?)");
          $stmt_visit_date->bind_param("i",$last_insert_id);
          $Resp=$stmt_visit_date->execute();
          $stmt_visit_date->close();
        }
      }

      $plot_id = $last_plot_id+1;
      
      // insert in pr_company_plot
      $stmt_company_plot = $obj->con1->prepare("INSERT INTO `pr_company_plots`(`plot_no`, `floor`, `road_no`, `plot_id`, `plot_status`, `company_id`, `industrial_estate_id`, `user_id`) VALUES (?,?,?,?,?,?,?,?)");
      $stmt_company_plot->bind_param("sssssiii",$plot_no,$floor,$road_number,$plot_id,$plot_status,$pr_company_detail_id,$estate_id,$user_id);
      $Resp=$stmt_company_plot->execute();
      $stmt_company_plot->close();
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
          "state" => $estate_result_floor["state_id"],
          "city" => $estate_result_floor["city_id"],
          "Taluka" => $estate_result_floor["taluka"],
          "Area" => $estate_result_floor["area_id"],
          "IndustrialEstate" => $estate_result_floor["industrial_estate"],
          "loan_applied" =>$post_fields->loan_applied,
          "Completion_Date" =>$post_fields->Completion_Date,
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
       
      // Display it
      //echo "$json";

      $stmt = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
      $stmt->bind_param("ss",$json,$user_id);
      $Resp=$stmt->execute();
      $insert_id = mysqli_insert_id($obj->con1);
      $stmt->close();
      
      $plot_id = '1';

      // insert in pr_company_details
      $stmt_company_detail = $obj->con1->prepare("INSERT INTO `pr_company_details`(`contact_name`, `mobile_no`, `state`, `city`, `taluka`, `area`, `industrial_estate`, `industrial_estate_id`, `user_id`, `rawdata_id`) VALUES (?,?,?,?,?,?,?,?,?,?)");
      $stmt_company_detail->bind_param("sssssssiii",$post_fields->Contact_Name,$post_fields->Mobile_No,$estate_result_floor["state_id"], $estate_result_floor["city_id"], $estate_result_floor["taluka"], $estate_result_floor["area_id"], $estate_result_floor["industrial_estate"],$estate_id,$user_id,$insert_id);
      $Resp=$stmt_company_detail->execute();
      $company_last_insert_id = mysqli_insert_id($obj->con1);
      $stmt_company_detail->close();

      // insert in pr_company_plot
      $stmt_company_plot = $obj->con1->prepare("INSERT INTO `pr_company_plots`(`plot_no`, `floor`, `road_no`, `plot_id`, `plot_status`, `company_id`, `industrial_estate_id`, `user_id`) VALUES (?,?,?,?,?,?,?,?)");
      $stmt_company_plot->bind_param("sssssiii",$plot_no,$floor,$road_number,$plot_id,$plot_status,$company_last_insert_id,$estate_id,$user_id);
      $Resp=$stmt_company_plot->execute();
      $stmt_company_plot->close();
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
          "state" => $estate_result_floor["state_id"],
          "city" => $estate_result_floor["city_id"],
          "Taluka" => $estate_result_floor["taluka"],
          "Area" => $estate_result_floor["area_id"],
          "IndustrialEstate" => $estate_result_floor["industrial_estate"],
          "loan_applied" =>$post_fields->loan_applied,
          "Completion_Date" =>$post_fields->Completion_Date,
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
       
      // Display it
      //echo "$json";

      $stmt = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
      $stmt->bind_param("ss",$json,$user_id);
      $Resp=$stmt->execute();
      $insert_id = mysqli_insert_id($obj->con1);
      $stmt->close();
      
      $plot_id = '1';

      // insert in pr_company_plot
      $stmt_company_plot = $obj->con1->prepare("INSERT INTO `pr_company_plots`(`plot_no`, `floor`, `road_no`, `plot_id`, `plot_status`, `industrial_estate_id`, `user_id`) VALUES (?,?,?,?,?,?,?)");
      $stmt_company_plot->bind_param("sssssii",$plot_no,$floor,$road_number,$plot_id,$plot_status,$estate_id,$user_id);
      $Resp=$stmt_company_plot->execute();
      $stmt_company_plot->close();
    }

    if(!$Resp)
    {
      throw new Exception("Problem in updating! ". strtok($obj->con1-> error,  '('));
    }
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
    if($floor_confirmation=='same_owner' || $floor_confirmation=='different_company'){
      setcookie("id_forentry", $insert_id, time()+3600,"/");
      setcookie("plotno_forentry", $plot_no, time()+3600,"/");
      setcookie("floorno_forentry", $floor, time()+3600,"/");
      setcookie("roadno_forentry", $road_no, time()+3600,"/");
      setcookie("estateid_forentry", $estate_id, time()+3600,"/");
    }

    setcookie("msg", "update",time()+3600,"/");
    header("location:company_entry.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:company_entry.php");
  }
}

// for plot modal
if(isset($_REQUEST['btn_modal_insert_plot']))
{ 
  $floor = $_REQUEST['floor_plotmodal'];
  $road_no = isset($_REQUEST['road_no_plotmodal'])?$_REQUEST['road_no_plotmodal']:"";
  $plot_status = $_REQUEST['plot_status_plotmodal'];
  $id = $_REQUEST['plotmodal_ttId'];
  $plot_confirmation = $_REQUEST['plot_confirmation'];
  $estate_id = $_REQUEST['estateid_plotmodal'];
  $plot_no = $_REQUEST['plot_no_plotmodal'];
  $plotting_pattern = $_REQUEST['plottingpattern_plotmodal'];
  $pr_company_detail_id = $_REQUEST['pr_company_detail_id_plotmodal'];

  $road_number = ($road_no=="")?NULL:$road_no;

  $stmt_estate = $obj->con1->prepare("SELECT * FROM tbl_industrial_estate WHERE id=?");
  $stmt_estate->bind_param("i",$estate_id);
  $stmt_estate->execute();
  $estate_result_plot = $stmt_estate->get_result()->fetch_assoc();
  $stmt_estate->close();

  $stmt_plot_search = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata WHERE raw_data->'$.plot_details[*].Plot_No' like '%".$plot_no."%' and raw_data->'$.plot_details[*].Road_No' like '%".$road_no."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($estate_result_plot['industrial_estate'])."%' and lower(raw_data->'$.post_fields.Area') like '%".strtolower($estate_result_plot['area_id'])."%' and lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($estate_result_plot['taluka'])."%'");
  $stmt_plot_search->execute();
  $plot_search = $stmt_plot_search->get_result();
  $stmt_plot_search->close();

  // get id of pr_company_plots table
  if($plotting_pattern=="Series"){
    $stmt_company_plot_search = $obj->con1->prepare("SELECT pid FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=?");
    $stmt_company_plot_search->bind_param("sii",$plot_no,$floor,$estate_id);
  }
  else if($plotting_pattern=="Road"){
    $stmt_company_plot_search = $obj->con1->prepare("SELECT pid FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=? and road_no=?");
    $stmt_company_plot_search->bind_param("siis",$plot_no,$floor,$estate_id,$road_no);
  }
  
  $stmt_company_plot_search->execute();
  $pr_company_plot_search = $stmt_company_plot_search->get_result();
  $stmt_company_plot_search->close();

  // if floor already created then update otherwise insert
  if(mysqli_num_rows($pr_company_plot_search)>0){
    $company_plot_res = $pr_company_plot_search->fetch_assoc();
    $pr_company_plot_id = $company_plot_res['pid'];
    $next_status = 'update';
  }
  else{
    $next_status = 'insert';
  }
  
  try
  {
    if($plot_confirmation=='same_as_ground'){  // Same Company As Ground
      $stmt_slist = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
      $stmt_slist->bind_param("i",$id);
      $stmt_slist->execute();
      $res = $stmt_slist->get_result();
      $stmt_slist->close();

      $data=mysqli_fetch_array($res);
      $row_data=json_decode($data["raw_data"]);
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

      $stmt_plot = $obj->con1->prepare("update tbl_tdrawdata set raw_data=? where id=?");
      $stmt_plot->bind_param("si",$json_object,$id);
      $Resp=$stmt_plot->execute();
      $stmt_plot->close();

      // for pr_visit_count table
      // if the data is updated by an employee on different date then count+1
      if(mysqli_affected_rows($obj->con1)>0){

        $stmt_count_list = $obj->con1->prepare("SELECT `cid`, `count`, date(`datetime`) as datetime FROM `pr_visit_count` WHERE industrial_estate=? and area=? and taluka=? and company_id=? and employee_id=?");
        $stmt_count_list->bind_param("sssii",$post_fields->IndustrialEstate,$post_fields->Area,$post_fields->Taluka,$id,$user_id);
        $stmt_count_list->execute();
        $count_result = $stmt_count_list->get_result();
        $stmt_count_list->close();

        if(mysqli_num_rows($count_result)>0){
          $count = mysqli_fetch_array($count_result);
          if(strtotime($count['datetime'])!=strtotime(date("Y-m-d"))){
            $stmt_count = $obj->con1->prepare("UPDATE `pr_visit_count` set `count`=`count`+1 where `cid`=? and `employee_id`=?");
            $stmt_count->bind_param("ii",$count['cid'],$user_id);
            $Resp=$stmt_count->execute();
            $stmt_count->close();
            
            $stmt_visit_date = $obj->con1->prepare("INSERT INTO `pr_visit_dates`(`visit_count_id`) VALUES (?)");
            $stmt_visit_date->bind_param("i",$count['cid']);
            $Resp=$stmt_visit_date->execute();
            $stmt_visit_date->close();
          }
        }
        else{
          $count=1;
          $stmt_count = $obj->con1->prepare("INSERT INTO `pr_visit_count`(`industrial_estate`, `area`, `taluka`, `company_id`, `employee_id`, `count`) VALUES (?,?,?,?,?,?)");
          $stmt_count->bind_param("sssiii",$post_fields->IndustrialEstate,$post_fields->Area,$post_fields->Taluka,$id,$user_id,$count);
          $Resp=$stmt_count->execute();
          $last_insert_id = mysqli_insert_id($obj->con1);
          $stmt_count->close();
          
          $stmt_visit_date = $obj->con1->prepare("INSERT INTO `pr_visit_dates`(`visit_count_id`) VALUES (?)");
          $stmt_visit_date->bind_param("i",$last_insert_id);
          $Resp=$stmt_visit_date->execute();
          $stmt_visit_date->close();
        }
      }

      $plot_id = $last_plot_id+1;

      // insert or update in pr_company_plot
      if($next_status=='update'){
        $stmt_company_plot = $obj->con1->prepare("UPDATE `pr_company_plots` SET `plot_status`=?, `plot_id`=?, `company_id`=?, `user_id`=? WHERE `pid`=?");
        $stmt_company_plot->bind_param("ssiii",$plot_status,$plot_id,$pr_company_detail_id,$user_id,$pr_company_plot_id);
        $Resp=$stmt_company_plot->execute();
        $stmt_company_plot->close();
      }
      else if($next_status=='insert'){
        $stmt_company_plot = $obj->con1->prepare("INSERT INTO `pr_company_plots`(`plot_no`, `floor`, `road_no`, `plot_status`, `plot_id`, `industrial_estate_id`, `company_id`, `user_id`) VALUES (?,?,?,?,?,?,?,?)");
        $stmt_company_plot->bind_param("ssssiiii",$plot_no,$floor,$road_number,$plot_status,$plot_id,$estate_id,$pr_company_detail_id,$user_id);
        $Resp=$stmt_company_plot->execute();
        $stmt_company_plot->close();
      }
        
    }
    else if($plot_confirmation=='same_owner'){   // Same Owner As Ground But Different Company
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
          "state" => $estate_result_plot['state_id'],
          "city" => $estate_result_plot['city_id'],
          "Taluka" => $estate_result_plot['taluka'],
          "Area" => $estate_result_plot['area_id'],
          "IndustrialEstate" => $estate_result_plot['industrial_estate'],
          "loan_applied" =>$post_fields->loan_applied,
          "Completion_Date" =>$post_fields->Completion_Date,
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
       
      $stmt = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
      $stmt->bind_param("ss",$json,$user_id);
      $Resp=$stmt->execute();
      $insert_id = mysqli_insert_id($obj->con1);
      $stmt->close();
      
      $plot_id = '1';

      // insert in pr_company_details
      $stmt_company_detail = $obj->con1->prepare("INSERT INTO `pr_company_details`(`contact_name`, `mobile_no`, `state`, `city`, `taluka`, `area`, `industrial_estate`, `industrial_estate_id`, `user_id`, `rawdata_id`) VALUES (?,?,?,?,?,?,?,?,?,?)");
      $stmt_company_detail->bind_param("sssssssiii",$post_fields->Contact_Name,$post_fields->Mobile_No,$estate_result_plot["state_id"], $estate_result_plot["city_id"], $estate_result_plot["taluka"], $estate_result_plot["area_id"], $estate_result_plot["industrial_estate"],$estate_id,$user_id,$insert_id);
      $Resp=$stmt_company_detail->execute();
      $company_last_insert_id = mysqli_insert_id($obj->con1);
      $stmt_company_detail->close();
      
      // insert or update in pr_company_plot
      if($next_status=='update'){
        $stmt_company_plot = $obj->con1->prepare("UPDATE `pr_company_plots` SET `plot_status`=?, `company_id`=?, `user_id`=? WHERE `pid`=?");
        $stmt_company_plot->bind_param("siii",$plot_status,$company_last_insert_id,$user_id,$pr_company_plot_id);
        $Resp=$stmt_company_plot->execute();
        $stmt_company_plot->close();
      }
      else if($next_status=='insert'){
        $stmt_company_plot = $obj->con1->prepare("INSERT INTO `pr_company_plots`(`plot_no`, `floor`, `road_no`, `plot_status`, `plot_id`, `industrial_estate_id`, `company_id`, `user_id`) VALUES (?,?,?,?,?,?,?,?)");
        $stmt_company_plot->bind_param("ssssiiii",$plot_no,$floor,$road_number,$plot_status,$plot_id,$estate_id,$company_last_insert_id,$user_id);
        $Resp=$stmt_company_plot->execute();
        $stmt_company_plot->close();
      }
    }


    // to get blank json data in tbl_tdrawdata and delete it
    if($next_status=='update'){
      if(mysqli_num_rows($plot_search)>0){
        $delete_id="";
        while($plot_search_res = mysqli_fetch_array($plot_search)){
          $row_data_search=json_decode($plot_search_res["raw_data"]);

          $post_fields = $row_data_search->post_fields;
          $plot_details = $row_data_search->plot_details;
          if($post_fields->IndustrialEstate==$estate_result_plot['industrial_estate'] && $post_fields->Area==$estate_result_plot['area_id'] && $post_fields->Taluka==$estate_result_plot['taluka']){
            foreach ($plot_details as $pd) {
              if($pd->Plot_No==$plot_no && $pd->Floor==$floor && $pd->Road_No==$road_no){
                $delete_id = $plot_search_res['id'];
                break;
              }
            }
          }
        }
        if($delete_id!=""){
          $stmt_del = $obj->con1->prepare("DELETE from tbl_tdrawdata where id=?");
          $stmt_del->bind_param("i",$delete_id);
          $Resp=$stmt_del->execute();   
          $stmt_del->close();   
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
    if($plot_confirmation=='same_owner'){
      setcookie("id_forentry", $insert_id, time()+3600,"/");
      setcookie("plotno_forentry", $plot_no, time()+3600,"/");
      setcookie("floorno_forentry", $floor, time()+3600,"/");
      setcookie("roadno_forentry", $road_no, time()+3600,"/");
      setcookie("estateid_forentry", $estate_id, time()+3600,"/");
    }

    setcookie("msg", "data",time()+3600,"/");
    header("location:company_entry.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:company_entry.php");
  }
}

// for additional plot modal
if(isset($_REQUEST['btn_modal_additional_plot']))
{
  $estate_id = $_REQUEST['estateid_additionalplot'];
  $plot_no = $_REQUEST['additional_plot'];
  $road_no = isset($_REQUEST['road_no_additionalplot'])?$_REQUEST['road_no_additionalplot']:"";
  $floor = '0';
  $road_number = ($road_no=="")?NULL:$road_no;
  $plot_id = '1';
  
  $stmt_estate = $obj->con1->prepare("SELECT * FROM tbl_industrial_estate WHERE id=?");
  $stmt_estate->bind_param("i",$estate_id);
  $stmt_estate->execute();
  $estate_result_plot = $stmt_estate->get_result()->fetch_assoc();
  $stmt_estate->close();

  try
  { 
    // As new plot is added it's entry is done in pr_estate_roadplot
    $stmt_plot_entry = $obj->con1->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `road_no`, `plot_start_no`, `user_id`) VALUES (?,?,?,?)");
    $stmt_plot_entry->bind_param("issi",$estate_id,$road_number,$plot_no,$user_id);
    $Resp=$stmt_plot_entry->execute();
    $stmt_plot_entry->close();

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
        "state" => $estate_result_plot['state_id'],
        "city" => $estate_result_plot['city_id'],
        "Taluka" => $estate_result_plot['taluka'],
        "Area" => $estate_result_plot['area_id'],
        "IndustrialEstate" => $estate_result_plot['industrial_estate'],
        "loan_applied" =>$post_fields->loan_applied,
        "Completion_Date" =>$post_fields->Completion_Date,
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
          "Plot_Status" => "",
          "Plot_Id" => "1",
        ),
      ) 
    );
     
    // Encode array to json
    $json = json_encode($cp);

    $stmt = $obj->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
    $stmt->bind_param("ss",$json,$user_id);
    $Resp=$stmt->execute();
    $insert_id = mysqli_insert_id($obj->con1);
    $stmt->close();
    
    //$plot_id = $last_plot_id+1;
    
    // insert into pr_company_plot
    $stmt_company_plot = $obj->con1->prepare("INSERT INTO `pr_company_plots`(`plot_no`, `floor`, `road_no`, `plot_id`, `industrial_estate_id`, `user_id`) VALUES (?,?,?,?,?,?)");
    $stmt_company_plot->bind_param("ssssii",$plot_no,$floor,$road_number,$plot_id,$estate_id,$user_id);
    $Resp=$stmt_company_plot->execute();
    $stmt_company_plot->close();

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
    setcookie("id_forentry", $insert_id, time()+3600,"/");
    setcookie("plotno_forentry", $plot_no, time()+3600,"/");
    setcookie("floorno_forentry", $floor, time()+3600,"/");
    setcookie("roadno_forentry", $road_no, time()+3600,"/");
    setcookie("estateid_forentry", $estate_id, time()+3600,"/");
    
    setcookie("msg", "data",time()+3600,"/");
    header("location:company_entry.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:company_entry.php");
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
          <input type="hidden" name="plot_index" id="plot_index">
          <input type="hidden" name="industrial_estate" id="industrial_estate">
          <input type="hidden" name="area" id="area">
          <input type="hidden" name="pr_company_detail_id" id="pr_company_detail_id">
          <input type="hidden" name="pr_company_plot_id" id="pr_company_plot_id">

            <div class="row">
              <label class="form-label" for="industrial_estate_id">Industrial Estate</label>
              <div class="col mb-3">
                <select name="industrial_estate_id" id="industrial_estate_id" class="form-control" onchange="getFilter(this.value)" required>
                  <option value="">Select Industrial Estate</option>
            <?php while($estate = mysqli_fetch_array($estate_result)){ ?>
                  <option value="<?php echo $estate['industrial_estate_id'] ?>"><?php echo $estate['industrial_estate']." - ".$estate['taluka'] ?> </option>
            <?php } ?>
                </select>
              </div>
              <div class="col mb-3">
                <div id="otherPlotModal" hidden>
                  <button type="button" onclick="additionalPlot(industrial_estate_id.value);" class="btn btn-primary"><i class='bx bxs-add-to-queue bx-sm'></i> &nbsp&nbsp Add Additional Plot</button>
                </div>
              </div>
            </div>

            <div class="row">
              <label class="form-label" for="filter">Select Filter</label>
              <div class="col mb-3">
                <select name="filter" id="filter" onchange="getPlot(industrial_estate_id.value,this.value)" class="form-control">
                  <option value="visit_pending">Visit Pending</option>
                  <option value="open_plot">Open Plot</option>
                  <option value="positive">Positive</option>
                  <option value="negative">Negative</option>
                  <option value="existing_client">Existing Client</option>
                </select>
              </div>
              <div class="col mb-3"></div>
            </div>

            <div class="row">
              <div class="col mb-3" id="road_list_div" hidden>
                <label class="form-label" for="road_no">Road No.</label>
                <select name="estate_road_no" id="road_no" class="form-control" onchange="getRoadPlots(this.value,industrial_estate_id.value,filter.value)">
                  <option value="">Select Road No.</option>
                </select>
              </div>
              <div class="col mb-3"></div>
            </div>

            <div class="row">
              <label class="form-label" for="plot_no">Plot No.</label>
              <div class="col mb-3">
                <select name="plot_no" id="plot_no" onchange="getFloor(this.value,industrial_estate_id.value,filter.value)" class="form-control" required>
                  <option value="">Select Plot No.</option>
                </select>
              </div>
              <div class="col mb-3">
                <div id="addPlotModal" hidden>
                  <button type="button" onclick="addPlot(ttId.value,industrial_estate_id.value,industrial_estate.value,area.value,plot_no.value,floor.value,road_no.value,pr_company_detail_id.value);" class="btn btn-primary"><i class='bx bxs-add-to-queue bx-sm'></i> &nbsp&nbsp Add Plot</button>
                </div>
              </div>
            </div>

            <div class="row">
              <label class="form-label" for="floor">Floor No.</label>
              <div class="col mb-3">
                <select name="floor" id="floor" class="form-control" onchange="get_companydetails(plot_no.value,industrial_estate_id.value)" required>
                  <option value="0">Ground Floor</option>
                </select>
              </div>
              <div class="col mb-3">
                <div id="addFloorModal" hidden>
                  <button type="button" onclick="addFloor(ttId.value,plot_no.value,road_no.value,area.value,industrial_estate.value,industrial_estate_id.value,floor.value,pr_company_detail_id.value);" class="btn btn-primary"><i class='bx bxs-add-to-queue bx-sm'></i> &nbsp&nbsp Add Floor</button>
                </div>
              </div>
            </div>

            <div id="company_details_div">
              <div class="mb-3">
                <label class="form-label" for="plot_status">Plot Status</label>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="plot_status" id="open_plot" value="Open Plot" >
                  <label class="form-check-label" for="inlineRadio1">Open Plot</label>
                </div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="plot_status" id="under_construction" value="Under Construction" >
                  <label class="form-check-label" for="inlineRadio1">Under Construction</label>
                </div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="plot_status" id="constructed" value="Constructed" >
                  <label class="form-check-label" for="inlineRadio1">Constructed</label>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label" for="premises">Premises</label>
                <select name="premises" id="premises" class="form-control" >
                  <option value="">Select Premises</option>
                  <option value="Sale Deed">Sale Deed</option>
                  <option value="Lease Deed">Lease Deed</option>
                  <option value="Register Rent Deed">Register Rent Deed</option>
                  <option value="Normal Rent Deed">Normal Rent Deed</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="gst_no">GST No.</label>
                <input type="text" class="form-control" name="gst_no" id="gst_no" onblur ="checkGST(this.value)" />
                <div id="gst_alert_div" class="text-danger"></div>
              </div>

              <div class="mb-3">
                <label class="form-label" for="firm_name">Firm Name</label>
                <input type="text" class="form-control" name="firm_name" id="firm_name" />
              </div>        

              <div class="mb-3">
                <label class="form-label" for="contact_person">Contact Person</label>
                <input type="text" class="form-control" name="contact_person" id="contact_person" />
              </div>

              <div class="mb-3">
                <label class="form-label" for="contact_no">Contact Number</label>
                <input type="text" class="form-control" name="contact_no" id="contact_no" />
              </div>

              <div class="mb-3">
                <label class="form-label" for="constitution">Constitution</label>
                <select name="constitution" id="constitution" class="form-control" >
                  <option value="">Select Constitution</option>
            <?php while($constitution_list=mysqli_fetch_array($constitution_result)){ ?>
                  <option value="<?php echo $constitution_list["constitution"] ?>"><?php echo $constitution_list["constitution"] ?></option>
            <?php } ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="category">Category</label>
                <select name="category" id="category" class="form-control" >
                  <option value="">Select Category</option>
                  <option value="Micro">Micro</option>
                  <option value="Small">Small</option>
                  <option value="Medium">Medium</option>
                  <option value="Large">Large</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="segment">Segment</label>
                <select name="segment" id="segment" class="form-control" >
                  <option value="">Select Segment</option>
            <?php while($segment_list=mysqli_fetch_array($segment_result)){ ?>
                    <option value="<?php echo $segment_list["segment"] ?>"><?php echo $segment_list["segment"] ?></option>
            <?php } ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="status">Status</label>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="status" id="existing_client" value="Existing Client" onclick="modal_open_ec()">
                  <label class="form-check-label" for="inlineRadio1">Existing Client</label>
                </div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="status" id="positive" value="Positive" onclick="modal_open_positive()">
                  <label class="form-check-label" for="inlineRadio1">Positive</label>
                </div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="status" id="negative" value="Negative" onclick="modal_open_negative()">
                  <label class="form-check-label" for="inlineRadio1">Negative</label>
                </div>
              </div>

              <div class="mb-3" id="badlead_reasons_div" hidden>
                <label class="form-label" for="badlead_reasons">Reasons</label>
                <select name="badlead_reasons" id="badlead_reasons" class="form-control">
                  <option value="">Select Reason</option>
            <?php while($reason_list=mysqli_fetch_array($badlead_reason_result)){ ?>
                  <option value="<?php echo $reason_list["reason_text"] ?>"><?php echo $reason_list["reason_text"] ?></option>
            <?php } ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="source">Source</label>
                <select name="source" id="source" class="form-control" onchange="getSourceName(this.value)">
                  <option value="">Select Source</option>
                    <optgroup label="Direct From Master">
            <?php while($source_list=mysqli_fetch_array($source_result)){ ?>
                      <option value="<?php echo $source_list["source_type"] ?>" data-tablename="tbl_sourcetype_master"><?php echo $source_list["source_type"] ?></option>
            <?php } ?>
                    </optgroup>
                    <optgroup label="Our Associates">
            <?php while($associate_list=mysqli_fetch_array($associate_result)){ ?>
                      <option value="<?php echo $associate_list["asso_segment_name"] ?>" data-tablename="asso_segment_master"><?php echo $associate_list["asso_segment_name"] ?></option>
            <?php } ?>
                    </optgroup>
                    <optgroup label="New System">
                      <option value="Lead Data" data-tablename="new_system">Lead Data</option>
                    </optgroup>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="source_name">Source Name</label>
                <select name="source_name" id="source_name" class="form-control" >
                  <option value="">Select Source Name</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="remark">Remark</label>
                <input type="text" class="form-control" name="remark" id="remark" />
              </div>

              <div class="mb-3">
                <label class="form-label" for="img">Image</label>
                <input type="file" class="form-control" onchange="readURL(this)" name="img" id="img" />
                <img src="" name="PreviewImage" id="PreviewImage" width="100" height="100" style="display:none;">
                <div id="imgdiv" style="color:red"></div>
                <input type="hidden" name="himage" id="himage" />
              </div>
            </div>
      
          <button type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary">Save</button>
          <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button>

        </form>
      </div>
    </div>
  </div>
</div>


<!-- Additional Plot Modal -->
<div class="modal fade" id="additionalPlotModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Add Additional Plot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div id="additionalPlot_div"></div>
    </div>
  </div>
</div>  


<!-- /modal-->

<!-- Plot Modal -->
<div class="modal fade" id="plotModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Add Plot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div id="addPlot_div"></div>
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
      <div id="addFloor_div"></div>
    </div>
  </div>
</div>
<!-- /modal-->

<!-- existing client Modal -->
<div class="modal fade" id="existing_clientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Existing Client Status </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="post">
        <div class="col mb-3">
          
          <div class="form-check form-check-inline mt-3">
            <input class="form-check-input" type="radio" name="expansion" id="positive_expansion" value="positive for expansion" >
            <label class="form-check-label" for="inlineRadio1">Positive for expansion</label>
          </div>
          <div class="form-check form-check-inline mt-3">
            <input class="form-check-input" type="radio" name="expansion" id="negative_expansion" value="negative for expansion" >
            <label class="form-check-label" for="inlineRadio1">Negative for expansion</label>
          </div>
         
        </div>
      </div>
      <div class="modal-footer">
          <input type="button" class="btn btn-primary" name="btn_submit_ec" id="btn_submit_ec" value="Save Changes" onclick="save_existing_status()" />
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>

       </form>
    </div>
  </div>
</div>
<!-- /modal-->

<!-- Positive Modal -->
<div class="modal fade" id="positive_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Positive Status </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="post">
        <label class="form-label" for="sanction">Loan Sanction</label>
        <div class="col mb-3">
          
          <div class="form-check form-check-inline mt-3">
            <input class="form-check-input" type="radio" name="sanction" id="apply" value="Want to Apply?" required >
            <label class="form-check-label" for="inlineRadio1">Want to Apply?</label>
          </div>
          <div class="form-check form-check-inline mt-3">
            <input class="form-check-input" type="radio" name="sanction" id="under_process" value="Loan Under Process" required>
            <label class="form-check-label" for="inlineRadio1">Loan Under Process</label>
          </div>
          <div class="form-check form-check-inline mt-3">
            <input class="form-check-input" type="radio" name="sanction" id="sanctioned" value="Sactioned Loan" required>
            <label class="form-check-label" for="inlineRadio1">Sanctioned</label>
          </div>
          
        </div>
        <div class="col mb-3">
          <label class="form-label" for="completion_date">Completion Date</label>
          <div class="form-check form-check-inline mt-3">
           
            <input type="date" class="form-control" name="completion_date" id="completion_date" max="9999-12-31"  required />
           
          </div>
          
          
        </div>
      </div>
      

        <div class="modal-footer">
          <input type="button" class="btn btn-primary" name="btn_submit_positive" id="btn_submit_positive" value="Save Changes" onclick="save_positive()" />
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
      </div>
    </div>
  </div>
</div>
<!-- /modal-->

           
<!-- / Content -->
<script type="text/javascript">

  $( document ).ready(function() {
    var id = readCookie("id_forentry");
    var plot_no = decodeURIComponent(readCookie("plotno_forentry"));
    var floor_no = readCookie("floorno_forentry");
    var road_no = decodeURIComponent(readCookie("roadno_forentry"));
    var estate_id = readCookie("estateid_forentry");
    var filter = "visit_pending";
    if(estate_id!=null){
      //getPlot(estate_id,filter);
      $('#filter').html('');
      $('#filter').append('<option value="">None</option>');
      if(road_no!="null"){
        //getRoadPlots(road_no,estate_id,filter);  
        $('#road_no').html('');
        $('#road_no').append('<option value="'+road_no+'">'+road_no+'</option>');
        $('#road_no').val(road_no);
        document.getElementById('#road_no').disabled = true;
      }
      $('#industrial_estate_id').val(estate_id);
      $('#plot_no').html('');
      $('#plot_no').append('<option value="'+plot_no+'">'+plot_no+'</option>');
      $('#floor').html('');
      
      if(floor_no=="0"){
        $('#floor').append('<option value="'+floor_no+'">Ground Floor</option>');
      }
      else{
        $('#floor').append('<option value="'+floor_no+'">'+floor_no+'</option>');
      }
      

      /*$('#plot_no').val(plot_no);
      getFloor(plot_no,estate_id,filter);
      $('#floor').val(floor_no);*/

      get_companydetails(plot_no,estate_id);

      document.getElementById('filter').readonly = true;
      document.getElementById('plot_no').readonly = true;
      document.getElementById('floor').readonly = true;  
      $('#otherPlotModal').removeAttr("hidden");
      $('#addFloorModal').removeAttr("hidden");
    }
    
    eraseCookie("id_forentry");
    eraseCookie("plotno_forentry");
    eraseCookie("floorno_forentry");
    eraseCookie("roadno_forentry");
    eraseCookie("estateid_forentry");
  });

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
        document.getElementById('btnsubmit').disabled = false;
      }
      else
      {
        $('#imgdiv').html("Please Select Image Only");
        document.getElementById('btnsubmit').disabled = true;
      }
    }
  }

  $('input[type=radio][name=status]').change(function() {
    if($('#negative').is(':checked')){
      $('#badlead_reasons_div').removeAttr("hidden");
    }
    else{
      $('#badlead_reasons_div').attr("hidden",true);
    }
  });

  function checkGST(gst_no)
  {
    var id=$('#ttId').val();
    $.ajax({
      async: false,
      type: "POST",
      url: "ajaxdata.php?action=checkGST",
      data: "gst_no="+gst_no+"&id="+id,
      cache: false,
      success: function(result){
        
        var data = result.split("@@@@@");
        if(data[0]>0)
        {
          $('#gst_alert_div').html('GST No. already exist!  You can add in Plot No. '+data[1]);
          document.getElementById('btnsubmit').disabled = true;
        }
        else
        {
          $('#gst_alert_div').html('');
          document.getElementById('btnsubmit').disabled = false;
        }
      }
    });
  }


  function getFilter(estate_id){

    if(estate_id!=""){
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getFilter",
        data: "estate_id="+estate_id,
        cache: false,
        success: function(result){
          
          //var data = result.split("@@@@@");          
          $('#filter').html('');
          //$('#filter').append(data[0]);
          $('#filter').append(result);
        }
      });
      /*$('#addPlotModal').attr("hidden",true);
      $('#addFloorModal').attr("hidden",true);
      $('#otherPlotModal').removeAttr("hidden");*/
      
      
    }
    else{
      window.location.reload();
    }
  }

  function getPlot(estate_id,filter){
    if(estate_id!=""){
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getPlot",
        data: "estate_id="+estate_id+"&filter="+filter,
        cache: false,
        success: function(result){
          
          var data = result.split("@@@@@");
          document.cookie = "plottingpattern_company="+data[1];
          if(data[1]=="Road"){
            $('#road_list_div').removeAttr("hidden");
            $('#road_no').html('');
            $('#road_no').append(data[0]);
            $('#plot_no').html('');
            $('#plot_no').append('<option value="">Select Plot No.</option>');
            $('#floor').html('');
            $('#floor').append('<option value="0">Ground Floor</option>');
          } 
          else if(data[1]=="Series"){
            $('#road_list_div').attr("hidden",true);
            $('#road_no').val("");
            $('#plot_no').html('');
            $('#plot_no').append(data[0]);
            $('#floor').html('');
            $('#floor').append('<option value="0">Ground Floor</option>');
          }
        }
      });
      $('#addPlotModal').attr("hidden",true);
      $('#addFloorModal').attr("hidden",true);
      $('#otherPlotModal').removeAttr("hidden");
      
      get_companydetails('',estate_id);
    }
    else{
      window.location.reload();
    }
  }

  function getRoadPlots(road_no,estate_id,filter){
    if(road_no!=""){
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getRoadPlots",
        data: "road_no="+road_no+"&estate_id="+estate_id+"&filter="+filter,
        cache: false,
        success: function(result){
          
          $('#plot_no').html('');
          $('#plot_no').append(result);
          $('#floor').html('');
          $('#floor').append('<option value="0">Ground Floor</option>');
        }
      });
    }
    else{
      $('#addPlotModal').attr("hidden",true);
      $('#addFloorModal').attr("hidden",true);
      $('#floor').html('');
      $('#floor').append('<option value="0">Ground Floor</option>');
    }
    get_companydetails('',estate_id);
  }

  function getFloor(plot_no,estate_id,filter){
    road_no = $('#road_no').val();
    if(plot_no!=""){
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getFloor_plot",
        data: "plot_no="+plot_no+"&estate_id="+estate_id+"&road_no="+road_no+"&filter="+filter,
        cache: false,
        success: function(result){
          
          $('#floor').html('');
          $('#floor').append(result);
        }
      });
      $('#addFloorModal').removeAttr("hidden");
    }
    else{
      $('#addPlotModal').attr("hidden",true);
      $('#addFloorModal').attr("hidden",true);
      $('#floor').html('');
      $('#floor').append('<option value="0">Ground Floor</option>');
    }
    get_companydetails(plot_no,estate_id);
  }

  function get_companydetails(plot_no,estate_id){
    road_no = $('#road_no').val();
    floor_no = $('#floor').val();

    $.ajax({
      async: false,
      type: "POST",
      url: "ajaxdata.php?action=get_companydetails",
      data: "plot_no="+plot_no+"&estate_id="+estate_id+"&floor_no="+floor_no+"&road_no="+road_no,
      cache: false,
      success: function(result){
        
        var res = $.parseJSON(result);
        
        $('#ttId').val(res['Id']);
        $('#plot_index').val(res['Plot_Id']);
        $('#industrial_estate').val(res['IndustrialEstate']);
        $('#area').val(res['Area']);

        $('#pr_company_detail_id').val(res['Company_detail_id']);
        if(res['Company_plot_id']==null){
          $('#pr_company_plot_id').val("");
        }
        else{
          $('#pr_company_plot_id').val(res['Company_plot_id']);
        }
        
        if(res['Plot_Status']=="Open Plot"){
          $('#open_plot').prop("checked","checked");
        }
        else if(res['Plot_Status']=="Under Construction"){
          $('#under_construction').prop("checked","checked"); 
        }
        else if(res['Plot_Status']=="Constructed"){
          $('#constructed').prop("checked","checked"); 
        }
        else{
          $('#open_plot').prop("checked",false); 
          $('#under_construction').prop("checked",false); 
          $('#constructed').prop("checked",false);  
        }

        $('#premises').val(res['Premise']);
        $('#gst_no').val(res['GST_No']);
        $('#firm_name').val(res['Firm_Name']);
        $('#contact_person').val(res['Contact_Name']);
        $('#contact_no').val(res['Mobile_No']);
        $('#constitution').val(res['Constitution']);
        $('#category').val(res['Category']);
        $('#segment').val(res['Segment']);

        if(res['Status']=="Existing Client"){
         $('#existing_client').prop("checked","checked");
         $('#badlead_reasons_div').attr("hidden",true); 
        }
        else if(res['Status']=="Positive"){
         $('#positive').prop("checked","checked"); 
         $('#badlead_reasons_div').attr("hidden",true);
        }
        else if(res['Status']=="Negative"){
         $('#negative').prop("checked","checked");
         $('#badlead_reasons').val(res['Reason']);
         $('#badlead_reasons_div').removeAttr("hidden");
        }
        else{
          $('#existing_client').prop("checked",false);
          $('#positive').prop("checked",false);
          $('#negative').prop("checked",false);
          $('#badlead_reasons_div').attr("hidden",true);
        }

        $('#source').val(res['source']);
        if(res['source']!=""){
          getSourceName(res['source']);
          setTimeout(function() {
            $('#source_name').val(res['Source_Name']);
          }, 1000);
        }
        
        $('#remark').val(res['Remarks']);

        $('#himage').val(res['Image']);
        $('#PreviewImage').show();
        if(res['Image']!=null && res['Image']!=""){
          $('#PreviewImage').attr('src','gst_image/'+res['Image']);
        }
        else{
          $('#PreviewImage').attr('src','gst_image/ns.jpg');
        }
        $('#img').removeAttr('required');

        // if contact name and contact person is filled then show add plot
        if(res['Contact_Name']!="" && res['Mobile_No']!=""){
          $('#addPlotModal').removeAttr("hidden");
        }
        else{
          $('#addPlotModal').attr("hidden",true);
        }

        // if all values filled then don't show all details
        //if(res['Id']!="" && res['Plot_Id']!="" && res['IndustrialEstate']!="" && res['Area']!="" && res['Plot_Status']!="" && res['Premise']!="" && res['GST_No']!="" && res['Firm_Name']!="" && res['Contact_Name']!="" && res['Mobile_No']!="" && res['Constitution']!="" && res['Category']!="" && res['Segment']!="" && res['Status']!="" && res['source']!="" && res['Source_Name']!="" && res['Remarks']!="" && res['Image']!=""){
        if(res['Company_plot_id']==null && res['Id']!=""){
            $('#company_details_div').attr("hidden",true);
            $('#btnsubmit').attr("hidden",true);
            $('#btnsubmit').attr("disabled",true);
        }
        else{
            $('#company_details_div').removeAttr("hidden");
            $('#btnsubmit').removeAttr("hidden");
            $('#btnsubmit').removeAttr("disabled");
        }
      }
    });
  }

  function additionalPlot(estate_id) {
    $('#additionalPlotModal').modal('toggle');
    $.ajax({
      async: false,
      type: "POST",
      url: "ajaxdata.php?action=additionalPlot_companyEntry",
      data: "estate_id="+estate_id,
      cache: false,
      success: function(result){
        
        $('#additionalPlot_div').html('');
        $('#additionalPlot_div').append(result);
      }
    });
  }

  function addPlot(id,estate_id,industrial_estate,area,plot_no,floor_no,road_no,pr_company_detail_id) 
  {
    $('#plotModal').modal('toggle');
    $.ajax({
      async: false,
      type: "POST",
      url: "ajaxdata.php?action=addPlot_companyEntry",
      data: "id="+id+"&estate_id="+estate_id+"&area="+area+"&industrial_estate="+industrial_estate+"&plot_no="+plot_no+"&floor_no="+floor_no+"&road_no="+road_no+"&pr_company_detail_id="+pr_company_detail_id,
      cache: false,
      success: function(result){
        
        $('#addPlot_div').html('');
        $('#addPlot_div').append(result);
      }
    });
  }

  function addFloor(id,plot_no,road_no,area,industrial_estate,estate_id,floor_no,pr_company_detail_id) {
    $.ajax({
      async: false,
      type: "POST",
      url: "ajaxdata.php?action=addFloor_companyEntry",
      data: "id="+id+"&plot_no="+plot_no+"&road_no="+road_no+"&estate_id="+estate_id+"&area="+area+"&industrial_estate="+industrial_estate+"&floor_no="+floor_no+"&pr_company_detail_id="+pr_company_detail_id,
      cache: false,
      success: function(result){
        
        $('#addFloor_div').html('');
        $('#addFloor_div').append(result);
        $('#floorModal').modal('toggle');
      }
    });
  }

  function get_plotno_plotmodal(road_no,estate_id) {
    $.ajax({
      async: false,
      type: "POST",
      url: "ajaxdata.php?action=get_plotno_plotmodal",
      data: "road_no="+road_no+"&estate_id="+estate_id,
      cache: false,
      success: function(result){
        
        $('#plot_no_plotmodal').html('');
        $('#plot_no_plotmodal').append(result);
        $('#floor_plotmodal').html('<option value="0">Ground Floor</option>');
      }
    });
  }

  function get_floorno(plot_no,estate_id){
    plotting_pattern = $('#plottingpattern_plotmodal').val();
    if(plotting_pattern=="Road"){
      road_no = $('#road_no_plotmodal').val();  
    }
    else{
      road = "";
    }

    $.ajax({
      async: false,
      type: "POST",
      url: "ajaxdata.php?action=get_floorno",
      data: "estate_id="+estate_id+"&plot_no="+plot_no+"&road_no="+road_no+"&plotting_pattern="+plotting_pattern,
      cache: false,
      success: function(result){
        $('#floor_plotmodal').html('');
        $('#floor_plotmodal').append(result);
      }
    });
  }

  function getSourceName(source){
    if(source!=""){
      table_name = $('#source').find(':selected').data('tablename');
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getSourceName",
        data: "source="+source+"&table_name="+table_name,
        cache: false,
        success: function(result){
          
          $('#source_name').html('');
          $('#source_name').append(result);
        }
      });  
    }
    else{
      $('#source_name').html('');
      $('#source_name').append('<option value="">Select Source Name</option>');
    }
  }

  function check_for_same_plot(additional_plot, estate_id, road_no="") {
    $.ajax({
      async: false,
      type: "POST",
      url: "ajaxdata.php?action=check_for_same_plot",
      data: "additional_plot="+additional_plot+"&estate_id="+estate_id+"&road_no="+road_no,
      cache: false,
      success: function(result){
        
        if(result==0){
          $('#additional_plot_alert_div').html('');
          document.getElementById('btn_modal_additional_plot').disabled = false;
          return true;
        }
        else{
          $('#additional_plot_alert_div').html('Plot No. already exist!');
          document.getElementById('btn_modal_additional_plot').disabled = true;
          return false;
        }
      }
    });
  }

  function enable_submit_button(){
    $('#additional_plot_alert_div').html('');
    document.getElementById('btn_modal_additional_plot').disabled = false;
  }
  function modal_open_ec()
  {
    $('#existing_clientModal').modal('toggle');
  }

  function modal_open_positive()
  {
    $('#positive_modal').modal('toggle');
  }
  function save_positive()
  {
    var loan_sanction=$("input[name='sanction']:checked").val();
    var completion_date=$('#completion_date').val();
    createCookie("loan_sanction",loan_sanction,1);
    createCookie("completion_date",completion_date,1);
    $('#positive_modal').modal('toggle');

  }
  function save_existing_status()
  {
    var ec_status=$("input[name='expansion']:checked").val();
    
    createCookie("expansion_status",ec_status,1);
    $('#existing_clientModal').modal('toggle');
  }
</script>
<?php 
  include("footer.php");
?>