<?php
  include("header.php");

  if(isset($_COOKIE['selecttype_comp_addplot'])){
    $selecttype_est = $_COOKIE['selecttype_comp_addplot'];
    if($selecttype_est=='select_estate_first'){
        $estateid_est = $_COOKIE['estateid_comp_addplot'];
        $rawdataid_est = $_COOKIE['rawdataid_comp_addplot'];
        
        $status_company = $_COOKIE['company_status'];

        $emp_name = $_COOKIE['empname_comp_addplot'];
        
    }
    else if($selecttype_est=='select_company_first'){
        $state_comp = $_COOKIE['state_comp_addplot'];
        $city_comp = $_COOKIE['city_comp_addplot'];
        $taluka_comp = $_COOKIE['taluka_comp_addplot'];
        $area_comp = $_COOKIE['area_comp_addplot'];
        echo $rawdataid_comp = $_COOKIE['rawdataid_comp_addplot'];
        $status_company = $_COOKIE['company_status'];
        $emp_name = $_COOKIE['empname_comp_addplot'];
    }
        
  }

// insert data for select estate first
if(isset($_REQUEST['btnsubmit_est']))
{
  $industrial_estate_id = $_REQUEST['industrial_estate_id_est'];
  $plot_no = $_REQUEST['plot_no_est'];
  $road_no = isset($_REQUEST['road_no_est'])?$_REQUEST['road_no_est']:"";
  $floor = $_REQUEST['floor_est'];
  $floor_no = $_REQUEST['floor_est'];
  $rawdata_id = $_REQUEST['rawdata_id_est'];
  $plotting_pattern = $_REQUEST['plotting_pattern_est'];
  $factory_address=$_REQUEST['factory_address_est'];

  $road_number = ($road_no=="")?NULL:$road_no;

  // get industrial est name
  $stmt_ind_est = $obj->con1->prepare("SELECT * FROM `tbl_industrial_estate` WHERE id=?");
  $stmt_ind_est->bind_param("i",$industrial_estate_id);
  $stmt_ind_est->execute();
  $ind_est_result = $stmt_ind_est->get_result()->fetch_assoc();
  $stmt_ind_est->close();

  // get id from tbl_tdrawdata of selected plot_no and floor_no
  $stmt_plot_search = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata WHERE raw_data->'$.plot_details[*].Plot_No' like '%".$plot_no."%' and raw_data->'$.plot_details[*].Road_No' like '%".$road_no."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($ind_est_result['industrial_estate'])."%' and lower(raw_data->'$.post_fields.Area') like '%".strtolower($ind_est_result['area_id'])."%' and lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($ind_est_result['taluka'])."%'");
  $stmt_plot_search->execute();
  $plot_search = $stmt_plot_search->get_result();
  $stmt_plot_search->close();

  $plot_rawdata_id = "";
  if(mysqli_num_rows($plot_search)>0){
    while($plot_search_result=mysqli_fetch_array($plot_search)){
      $row_data_plot_search=json_decode($plot_search_result["raw_data"]);
      if($row_data_plot_search->post_fields->IndustrialEstate==$ind_est_result['industrial_estate'] && $row_data_plot_search->post_fields->Taluka==$ind_est_result['taluka'] && $row_data_plot_search->post_fields->Area==$ind_est_result['area_id']){
        foreach ($row_data_plot_search->plot_details as $pd) {
          if($pd->Plot_No==$plot_no && $pd->Floor==$floor_no && $pd->Road_No==$road_no){
            $plot_rawdata_id=$plot_search_result["id"];
            break;
          }
        }
      }
    }
  }

  if($plot_rawdata_id==""){
    // New Floor is Added

    $plot_id='1';
    $stmt_company_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` WHERE id=?");
    $stmt_company_list->bind_param("i",$rawdata_id);
    $stmt_company_list->execute();
    $company_result = $stmt_company_list->get_result()->fetch_assoc();
    $stmt_company_list->close();
    
    // add array of plot in company json
    $row_data_comp=json_decode($company_result["raw_data"]);
   
    $row_data_comp->bad_lead_reason = "";
    $row_data_comp->bad_lead_reason_remark = "";
    $row_data_comp->Image = "";
    $row_data_comp->Constitution = "";
    $row_data_comp->Status = $status_company;
    $row_data_comp->post_fields->IndustrialEstate=$ind_est_result["industrial_estate"];
    $row_data_comp->post_fields->state=$ind_est_result["state_id"];
    $row_data_comp->post_fields->city=$ind_est_result["city_id"];
    $row_data_comp->post_fields->Taluka=$ind_est_result["taluka"];
    $row_data_comp->post_fields->Area=$ind_est_result["area_id"];
    $row_data_comp->post_fields->Factory_Address=$factory_address;
    $row_data_comp->plot_details = Array(
            Array(
              "Plot_No" => $plot_no,
              "Floor" => $floor_no,
              "Road_No" => $road_no,
              "Plot_Status" => "",
              "Plot_Id" => "1",
            ));
    $json_object = json_encode($row_data_comp);

    // get company details for pr_company_details
    $post_fields_comp = $row_data_comp->post_fields;
    $source = $post_fields_comp->source;
    $source_name = $post_fields_comp->Source_Name;
    $contact_person = $post_fields_comp->Contact_Name;
    $contact_no = $post_fields_comp->Mobile_No;
    $firm_name = $post_fields_comp->Firm_Name;
    $gst_no = $post_fields_comp->GST_No;
    $category = $post_fields_comp->Category;
    $segment = $post_fields_comp->Segment;
    $premise = $post_fields_comp->Premise;
    $state =$ind_est_result["state_id"];
    $city = $ind_est_result["city_id"];
    $taluka = $ind_est_result["taluka"];
    $area = $ind_est_result["area_id"];
    $industrial_estate = $ind_est_result["industrial_estate"];
    $inq_submit = "Submit";

    try {
      // update json of company
      $stmt = $obj->con1->prepare("UPDATE `tbl_tdrawdata` set raw_data=? where id=?");
      $stmt->bind_param("si",$json_object,$company_result['id']);
      $Resp=$stmt->execute();
      $stmt->close();

      // insert into pr_company_details and pr_company_plots
      $stmt_pr_company_detail = $obj->con1->prepare("INSERT INTO `pr_company_details`(`source`, `source_name`, `contact_name`, `mobile_no`, `firm_name`, `gst_no`, `category`, `segment`, `premise`, `state`, `city`, `taluka`, `area`, `industrial_estate`, `inq_submit`, `industrial_estate_id`, `user_id`, `rawdata_id`, `status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $stmt_pr_company_detail->bind_param("sssssssssssssssiiis",$source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$state,$city,$taluka,$area,$industrial_estate,$inq_submit,$industrial_estate_id,$user_id,$company_result['id'],$status_company);
      $Resp=$stmt_pr_company_detail->execute();
      $last_insert_company_id = mysqli_insert_id($obj->con1);
      $stmt_pr_company_detail->close();

      // insert in pr_company_plot
      $stmt_company_plot = $obj->con1->prepare("INSERT INTO `pr_company_plots`(`plot_no`, `floor`, `road_no`, `plot_id`, `industrial_estate_id`, `user_id`,`company_id`) VALUES (?,?,?,?,?,?,?)");
      $stmt_company_plot->bind_param("ssssiii",$plot_no,$floor_no,$road_no,$plot_id,$industrial_estate_id,$user_id,$last_insert_company_id);
      $Resp=$stmt_company_plot->execute();
      $stmt_company_plot->close();

      if(!$Resp)
      {
        throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
      }
    } 
    catch(\Exception  $e) {
      setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
    }
  }
  else{
    // Floor Already Exists
    $stmt_plot_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` WHERE id=?");
    $stmt_plot_list->bind_param("i",$plot_rawdata_id);
    $stmt_plot_list->execute();
    $plot_result = $stmt_plot_list->get_result()->fetch_assoc();
    $stmt_plot_list->close();

    // get array of plot
    $row_data_plot=json_decode($plot_result["raw_data"]);
    $plot_array = $row_data_plot->plot_details;

    $stmt_company_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` WHERE id=?");
    $stmt_company_list->bind_param("i",$rawdata_id);
    $stmt_company_list->execute();
    $company_result = $stmt_company_list->get_result()->fetch_assoc();
    $stmt_company_list->close();
    
    // add array of plot in company json
    $row_data_comp=json_decode($company_result["raw_data"]);
    $row_data_comp->bad_lead_reason = "";
    $row_data_comp->bad_lead_reason_remark = "";
    $row_data_comp->Image = "";
    $row_data_comp->Constitution = "";
    $row_data_comp->Status = $status_company;
    $row_data_comp->plot_details = $plot_array;
    $row_data_comp->post_fields->IndustrialEstate=$ind_est_result["industrial_estate"];
    $row_data_comp->post_fields->state=$ind_est_result["state_id"];
    $row_data_comp->post_fields->city=$ind_est_result["city_id"];
    $row_data_comp->post_fields->Taluka=$ind_est_result["taluka"];
    $row_data_comp->post_fields->Area=$ind_est_result["area_id"];
    $row_data_comp->post_fields->Factory_Address=$factory_address;
    $row_data_comp->plot_details = Array(
            Array(
              "Plot_No" => $plot_no,
              "Floor" => $floor_no,
              "Road_No" => $road_number,
              "Plot_Status" => "",
              "Plot_Id" => "1",
            ));
    $json_object = json_encode($row_data_comp);

    // get company details for pr_company_details
    $post_fields_comp = $row_data_comp->post_fields;
    $source = $post_fields_comp->source;
    $source_name = $post_fields_comp->Source_Name;
    $contact_person = $post_fields_comp->Contact_Name;
    $contact_no = $post_fields_comp->Mobile_No;
    $firm_name = $post_fields_comp->Firm_Name;
    $gst_no = $post_fields_comp->GST_No;
    $category = $post_fields_comp->Category;
    $segment = $post_fields_comp->Segment;
    $premise = $post_fields_comp->Premise;
    $state =$ind_est_result["state_id"];
    $city = $ind_est_result["city_id"];
    $taluka = $ind_est_result["taluka"];
    $area = $ind_est_result["area_id"];
    $industrial_estate = $ind_est_result["industrial_estate"];
    $inq_submit = "Submit";
    
    // get id of table pr_company_plot 
    if($plotting_pattern=="Series"){
      $stmt_company_plot = $obj->con1->prepare("SELECT pid, company_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=? ");
      $stmt_company_plot->bind_param("sii",$plot_no,$floor_no,$industrial_estate_id);
    }
    else if($plotting_pattern=="Road"){
      $stmt_company_plot = $obj->con1->prepare("SELECT pid, company_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=? and road_no=?");
      $stmt_company_plot->bind_param("siis",$plot_no,$floor_no,$industrial_estate_id,$road_no);
    }
    $stmt_company_plot->execute();
    $pr_company_plot = $stmt_company_plot->get_result()->fetch_assoc();
    $stmt_company_plot->close();

    try
    {
      // update json of company
      $stmt = $obj->con1->prepare("UPDATE `tbl_tdrawdata` set raw_data=? where id=?");
      $stmt->bind_param("si",$json_object,$company_result['id']);
      $Resp=$stmt->execute();
      $stmt->close();

      // delete blank json of plot
      $stmt_del = $obj->con1->prepare("DELETE from `tbl_tdrawdata` where id=?");
      $stmt_del->bind_param("i",$plot_rawdata_id);
      $Resp=$stmt_del->execute();
      $stmt_del->close();

      // insert into pr_company_details and pr_company_plots
      $stmt_pr_company_detail = $obj->con1->prepare("INSERT INTO `pr_company_details`(`source`, `source_name`, `contact_name`, `mobile_no`, `firm_name`, `gst_no`, `category`, `segment`, `premise`, `state`, `city`, `taluka`, `area`, `industrial_estate`, `inq_submit`, `industrial_estate_id`, `user_id`, `rawdata_id`, `status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $stmt_pr_company_detail->bind_param("sssssssssssssssiiis",$source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$state,$city,$taluka,$area,$industrial_estate,$inq_submit,$industrial_estate_id,$user_id,$company_result['id'],$status_company);
      $Resp=$stmt_pr_company_detail->execute();
      $last_insert_company_id = mysqli_insert_id($obj->con1);
      $stmt_pr_company_detail->close();
      
      $stmt_pr_company_plot = $obj->con1->prepare("UPDATE `pr_company_plots` SET `company_id`=? WHERE `pid`=?");
      $stmt_pr_company_plot->bind_param("ii",$last_insert_company_id,$pr_company_plot['pid']);
      $Resp=$stmt_pr_company_plot->execute();
      $stmt_pr_company_plot->close();

      if(!$Resp)
      {
        throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
      }
    } 
    catch(\Exception  $e) {
      setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
    }
  }

  //update tbl_tdcompany if not in lead/badlead
  if($status_company!='Positive' && $status_company!='Negative')
  {
    $stmt_comp_data = $obj->con1->prepare("SELECT * FROM `tbl_tdcompany` WHERE inq_id=?");
    $stmt_comp_data->bind_param("i",$rawdata_id);
    $stmt_comp_data->execute();
    $comp_result = $stmt_comp_data->get_result()->fetch_assoc();
    $stmt_comp_data->close();
    $comp_data=json_decode($comp_result["company_data"]);
    $comp_data->IndustrialEstate=$ind_est_result["industrial_estate"];
    $comp_data->Area=$ind_est_result["area_id"];
    $comp_data->Company_Address=$factory_address;
    $json_object_comp = json_encode($comp_data);

    //update tbl_tdcompany
    $stmt_tdcompany = $obj->con1->prepare("UPDATE `tbl_tdcompany` SET `company_data`=? WHERE `inq_id`=?");
    $stmt_tdcompany->bind_param("si",$json_object_comp,$rawdata_id);
    $Resp=$stmt_tdcompany->execute();
    $stmt_tdcompany->close();

    //tbl_tdapplication
    $stmt_app_data = $obj->con1->prepare("SELECT * FROM `tbl_tdapplication` WHERE inq_id=?");
    $stmt_app_data->bind_param("i",$rawdata_id);
    $stmt_app_data->execute();
    $app_result = $stmt_app_data->get_result()->fetch_assoc();
    $stmt_app_data->close();
    $app_data=json_decode($comp_result["app_data"]);
    $app_data->company_details->IndustrialEstate=$ind_est_result["industrial_estate"];
    $app_data->company_details->Area=$area_comp;
    $json_object_app = json_encode($app_data);

    //update tbl_tdapplication
    $stmt_tdcompany = $obj->con1->prepare("UPDATE `tbl_tdapplication` SET `app_data`=? WHERE `inq_id`=?");
    $stmt_tdcompany->bind_param("si",$json_object_app,$rawdata_id);
    $Resp=$stmt_tdcompany->execute();
    $stmt_tdcompany->close();


  }
  
  if($Resp)
  {
    if (isset($_COOKIE['selecttype_comp_addplot'])) {
      setcookie('selecttype_comp_addplot', '', time() - 3600, '/');
      setcookie('estateid_comp_addplot', '', time() - 3600, '/');
      setcookie('rawdataid_comp_addplot', '', time() - 3600, '/');
      setcookie('company_status', '', time() - 3600, '/');
    }

    setcookie("msg", "data",time()+3600,"/");
    header("location:company_add_plot.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:company_add_plot.php");
  }
}


// insert data for select company first
if(isset($_REQUEST['btnsubmit_comp']))
{
  $industrial_estate_id = $_REQUEST['industrial_estate_id_comp'];
  $plot_no = $_REQUEST['plot_no_comp'];
  $road_no = isset($_REQUEST['road_no_comp'])?$_REQUEST['road_no_comp']:"";
  $floor_no = $_REQUEST['floor_comp'];
  $rawdata_id = $_REQUEST['rawdata_id_comp'];
  $plotting_pattern = $_REQUEST['plotting_pattern_comp'];
  $area_comp=$_REQUEST['area_comp'];
  $factory_address_comp=$_REQUEST['factory_address_comp'];

  $road_number = ($road_no=="")?NULL:$road_no;
  $plot_id='1';
 
  $stmt_estate = $obj->con1->prepare("SELECT * FROM tbl_industrial_estate i1 WHERE id=?");
  $stmt_estate->bind_param("i",$industrial_estate_id);
  $stmt_estate->execute();
  $estate_res = $stmt_estate->get_result()->fetch_assoc();
  $stmt_estate->close();

  // get id from tbl_tdrawdata of selected plot_no and floor_no
  $stmt_plot_search = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata WHERE raw_data->'$.plot_details[*].Plot_No' like '%".$plot_no."%' and raw_data->'$.plot_details[*].Road_No' like '%".$road_no."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($estate_res['industrial_estate'])."%' and lower(raw_data->'$.post_fields.Area') like '%".strtolower($estate_res['area_id'])."%' and lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($estate_res['taluka'])."%'");
  $stmt_plot_search->execute();
  $plot_search = $stmt_plot_search->get_result();
  $stmt_plot_search->close();

  $plot_rawdata_id = "";
  if(mysqli_num_rows($plot_search)>0){
    while($plot_search_result=mysqli_fetch_array($plot_search)){
      $row_data_plot_search=json_decode($plot_search_result["raw_data"]);
      if($row_data_plot_search->post_fields->IndustrialEstate==$estate_res['industrial_estate'] && $row_data_plot_search->post_fields->Taluka==$estate_res['taluka'] && $row_data_plot_search->post_fields->Area==$estate_res['area_id']){
        foreach ($row_data_plot_search->plot_details as $pd) {
          if($pd->Plot_No==$plot_no && $pd->Floor==$floor_no && $pd->Road_No==$road_no){
            $plot_rawdata_id=$plot_search_result["id"];
            break;
          }
        }
      }
    }
  }

  if($plot_rawdata_id==""){
    // New Floor is Added

    $stmt_company_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` WHERE id=?");
    $stmt_company_list->bind_param("i",$rawdata_id);
    $stmt_company_list->execute();
    $company_result = $stmt_company_list->get_result()->fetch_assoc();
    $stmt_company_list->close();

    // add array of plot in company json
    $row_data_comp=json_decode($company_result["raw_data"]);
    $post_fields = $row_data_comp->post_fields;
    $post_fields->state = $estate_res['state_id'];
    $post_fields->city = $estate_res['city_id'];
    $post_fields->Taluka = $estate_res['taluka'];
    $post_fields->Area = $area_comp;
    $post_fields->IndustrialEstate = $estate_res['industrial_estate'];
    $post_fields->Factory_Address=$factory_address_comp;
    $row_data_comp->bad_lead_reason = "";
    $row_data_comp->bad_lead_reason_remark = "";
    $row_data_comp->Image = "";
    $row_data_comp->Constitution = "";
    $row_data_comp->Status = $status_company;
    $row_data_comp->plot_details = Array(
            Array(
              "Plot_No" => $plot_no,
              "Floor" => $floor_no,
              "Road_No" => $road_no,
              "Plot_Status" => "",
              "Plot_Id" => "1",
            ));
    $json_object = json_encode($row_data_comp);
  
    $post_fields_comp = $row_data_comp->post_fields;
    $source = $post_fields_comp->source;
    $source_name = $post_fields_comp->Source_Name;
    $contact_person = $post_fields_comp->Contact_Name;
    $contact_no = $post_fields_comp->Mobile_No;
    $firm_name = $post_fields_comp->Firm_Name;
    $gst_no = $post_fields_comp->GST_No;
    $category = $post_fields_comp->Category;
    $segment = $post_fields_comp->Segment;
    $premise = $post_fields_comp->Premise;
    $inq_submit = "Submit";

    try
    {
      // update json of company
     
      $stmt = $obj->con1->prepare("UPDATE `tbl_tdrawdata` set raw_data=? where id=?");
      $stmt->bind_param("si",$json_object,$company_result['id']);
      $Resp=$stmt->execute();
      $stmt->close();

      // insert into pr_company_details and pr_company_plots
      $stmt_pr_company_detail = $obj->con1->prepare("INSERT INTO `pr_company_details`(`source`, `source_name`, `contact_name`, `mobile_no`, `firm_name`, `gst_no`, `category`, `segment`, `premise`, `state`, `city`, `taluka`, `area`, `industrial_estate`, `inq_submit`, `industrial_estate_id`, `user_id`, `rawdata_id`, `status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $stmt_pr_company_detail->bind_param("sssssssssssssssiiis",$source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$estate_res['state_id'],$estate_res['city_id'],$estate_res['taluka'],$area_comp,$estate_res['industrial_estate'],$inq_submit,$industrial_estate_id,$user_id,$company_result['id'],$status_company);
      $Resp=$stmt_pr_company_detail->execute();
      $last_insert_company_id = mysqli_insert_id($obj->con1);
      $stmt_pr_company_detail->close();
      
      // insert in pr_company_plot
      $stmt_company_plot = $obj->con1->prepare("INSERT INTO `pr_company_plots`(`plot_no`, `floor`, `road_no`, `plot_id`, `industrial_estate_id`, `user_id`,`company_id`) VALUES (?,?,?,?,?,?,?)");
      $stmt_company_plot->bind_param("ssssiii",$plot_no,$floor_no,$road_number,$plot_id,$industrial_estate_id,$user_id,$last_insert_company_id);
      $Resp=$stmt_company_plot->execute();
      $stmt_company_plot->close();

      if(!$Resp)
      {
        throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
      }
    } 
    catch(\Exception  $e) {
      setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
    }
  }
  else{
    // Floor already exists 

    $stmt_company_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` WHERE id=?");
    $stmt_company_list->bind_param("i",$rawdata_id);
    $stmt_company_list->execute();
    $company_result = $stmt_company_list->get_result()->fetch_assoc();
    $stmt_company_list->close();

    // add array of plot in company json
    $row_data_comp=json_decode($company_result["raw_data"]);
    $post_fields = $row_data_comp->post_fields;
    $post_fields->state = $estate_res['state_id'];
    $post_fields->city = $estate_res['city_id'];
    $post_fields->Taluka = $estate_res['taluka'];
    $post_fields->Area = $estate_res['area_id'];
    $post_fields->IndustrialEstate = $estate_res['industrial_estate'];
    $post_fields->Factory_Address=$factory_address_comp;
    $row_data_comp->bad_lead_reason = "";
    $row_data_comp->bad_lead_reason_remark = "";
    $row_data_comp->Image = "";
    $row_data_comp->Constitution = "";
    $row_data_comp->Status = $status_company;
    $row_data_comp->plot_details = Array(
            Array(
              "Plot_No" => $plot_no,
              "Floor" => $floor_no,
              "Road_No" => $road_no,
              "Plot_Status" => "",
              "Plot_Id" => "1",
            ));
    $json_object = json_encode($row_data_comp);

    $post_fields_comp = $row_data_comp->post_fields;
    $source = $post_fields_comp->source;
    $source_name = $post_fields_comp->Source_Name;
    $contact_person = $post_fields_comp->Contact_Name;
    $contact_no = $post_fields_comp->Mobile_No;
    $firm_name = $post_fields_comp->Firm_Name;
    $gst_no = $post_fields_comp->GST_No;
    $category = $post_fields_comp->Category;
    $segment = $post_fields_comp->Segment;
    $premise = $post_fields_comp->Premise;
    $inq_submit = "Submit";
    
    // get id of table pr_company_plot 
    if($plotting_pattern=="Series"){
      $stmt_company_plot = $obj->con1->prepare("SELECT pid, company_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=? ");
      $stmt_company_plot->bind_param("sii",$plot_no,$floor_no,$industrial_estate_id);
    }
    else if($plotting_pattern=="Road"){
      $stmt_company_plot = $obj->con1->prepare("SELECT pid, company_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=? and road_no=?");
      $stmt_company_plot->bind_param("siis",$plot_no,$floor_no,$industrial_estate_id,$road_number);
    }
    $stmt_company_plot->execute();
    $pr_company_plot = $stmt_company_plot->get_result()->fetch_assoc();
    $stmt_company_plot->close();

    try
    {
      // update json of company
      $stmt = $obj->con1->prepare("UPDATE `tbl_tdrawdata` set raw_data=? where id=?");
      $stmt->bind_param("si",$json_object,$company_result['id']);
      $Resp=$stmt->execute();
      $stmt->close();

      // delete blank json of plot
      $stmt_del = $obj->con1->prepare("DELETE from `tbl_tdrawdata` where id=?");
      $stmt_del->bind_param("i",$plot_rawdata_id);
      $Resp=$stmt_del->execute();
      $stmt_del->close();

      // insert into pr_company_details and pr_company_plots
      $stmt_pr_company_detail = $obj->con1->prepare("INSERT INTO `pr_company_details`(`source`, `source_name`, `contact_name`, `mobile_no`, `firm_name`, `gst_no`, `category`, `segment`, `premise`, `state`, `city`, `taluka`, `area`, `industrial_estate`, `inq_submit`, `industrial_estate_id`, `user_id`, `rawdata_id`,`status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $stmt_pr_company_detail->bind_param("sssssssssssssssiiis",$source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$estate_res['state_id'],$estate_res['city_id'],$estate_res['taluka'],$estate_res['area_id'],$estate_res['industrial_estate'],$inq_submit,$industrial_estate_id,$user_id,$company_result['id'],$status_company);
      $Resp=$stmt_pr_company_detail->execute();
      $last_insert_company_id = mysqli_insert_id($obj->con1);
      $stmt_pr_company_detail->close();
      
      $stmt_pr_company_plot = $obj->con1->prepare("UPDATE `pr_company_plots` SET `company_id`=? WHERE `pid`=?");
      $stmt_pr_company_plot->bind_param("ii",$last_insert_company_id,$pr_company_plot['pid']);
      $Resp=$stmt_pr_company_plot->execute();
      $stmt_pr_company_plot->close();

      if(!$Resp)
      {
        throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
      }
    } 
    catch(\Exception  $e) {
      setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
    }
  }

  //update tbl_tdcompany,tbl_tdapplication if not in lead/badlead
  if($status_company!='Positive' && $status_company!='Negative')
  {
    //tbl_tdcompany
    $stmt_comp_data = $obj->con1->prepare("SELECT * FROM `tbl_tdcompany` WHERE inq_id=?");
    $stmt_comp_data->bind_param("i",$rawdata_id);
    $stmt_comp_data->execute();
    $comp_result = $stmt_comp_data->get_result()->fetch_assoc();
    $stmt_comp_data->close();
    $comp_data=json_decode($comp_result["company_data"]);
    $comp_data->IndustrialEstate=$estate_res["industrial_estate"];
    $comp_data->Area=$area_comp;
    $comp_data->Company_Address=$factory_address_comp;
    $json_object_comp = json_encode($comp_data);

    //update tbl_tdcompany
    $stmt_tdcompany = $obj->con1->prepare("UPDATE `tbl_tdcompany` SET `company_data`=? WHERE `inq_id`=?");
    $stmt_tdcompany->bind_param("si",$json_object_comp,$rawdata_id);
    $Resp=$stmt_tdcompany->execute();
    $stmt_tdcompany->close();

    //tbl_tdapplication
    $stmt_app_data = $obj->con1->prepare("SELECT * FROM `tbl_tdapplication` WHERE inq_id=?");
    $stmt_app_data->bind_param("i",$rawdata_id);
    $stmt_app_data->execute();
    $app_result = $stmt_app_data->get_result()->fetch_assoc();
    $stmt_comp_data->close();
    $app_data=json_decode($comp_result["app_data"]);
    $app_data->company_details->IndustrialEstate=$estate_res["industrial_estate"];
    $app_data->company_details->Area=$area_comp;
    $json_object_app = json_encode($app_data);

    //update tbl_tdapplication
    $stmt_tdcompany = $obj->con1->prepare("UPDATE `tbl_tdapplication` SET `app_data`=? WHERE `inq_id`=?");
    $stmt_tdcompany->bind_param("si",$json_object_app,$rawdata_id);
    $Resp=$stmt_tdcompany->execute();
    $stmt_tdcompany->close();


  }

  if($Resp)
  {
    if (isset($_COOKIE['selecttype_comp_addplot'])) {
      setcookie('selecttype_comp_addplot', '', time() - 3600, '/');
      setcookie('state_comp_addplot', '', time() - 3600, '/');
      setcookie('city_comp_addplot', '', time() - 3600, '/');
      setcookie('taluka_comp_addplot', '', time() - 3600, '/');
      setcookie('area_comp_addplot', '', time() - 3600, '/');
      setcookie('rawdataid_comp_addplot', '', time() - 3600, '/');
      setcookie('company_status', '', time() - 3600, '/');
    }

    setcookie("msg", "data",time()+3600,"/");
    header("location:company_add_plot.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:company_add_plot.php");
  }
}

?>

<h4 class="fw-bold py-3 mb-4">Add Plotting In Company</h4>

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
        <div class="card-body">
          <form method="post" >
           
            <!-- div for selecting estate first -->
            <div id="select_estate_first_div" <?php echo (isset($selecttype_est) && $selecttype_est=="select_estate_first")?"":"hidden"?>>
              <div class="mb-3">
                <label class="form-label" for="industrial_estate_id_est">Industrial Estate</label>
                  <select name="industrial_estate_id_est" id="industrial_estate_id_est" class="form-control"  <?php echo (isset($selecttype_est) && $selecttype_est=="select_estate_first")?"required":"" ?> onchange="getPlot_companyPlot_est(this.value)">
                    <option value="">Select Industrial Estate</option>
              <?php
                  $stmt_estate_list = $obj->con1->prepare("SELECT * from tbl_industrial_estate WHERE city_id='SURAT' order by state_id, city_id, taluka, area_id, industrial_estate");
                  $stmt_estate_list->execute();
                  $estate_result = $stmt_estate_list->get_result();
                  $stmt_estate_list->close();

                  while($estate = mysqli_fetch_array($estate_result)){ 
              ?>
                    <option value="<?php echo $estate['id'] ?>" <?php echo ($estateid_est==$estate['id'])?"selected":""?>>
                        <?php echo $estate['industrial_estate']." - ".$estate['taluka']." - ".$estate['area_id'] ?>
                    </option>
              <?php } ?>
                  </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="rawdata_id_est">Company</label>
                <input type="hidden" name="rawdata_id_est" id="rawdata_id_est" value="<?php echo $rawdataid_est ?>">
                <select name="est_comp_id" id="est_comp_id" onchange="get_companyStatus(this.value)" class="form-control" <?php echo (isset($selecttype_est) && $selecttype_est=="select_estate_first")?"required":"" ?> disabled >
                    <option value="">Select Company</option>
            <?php
                $stmt_firm_list = $obj->con1->prepare("SELECT r1.id, json_unquote(r1.raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(r1.raw_data->'$.post_fields.Contact_Name') as contact_name, json_unquote(r1.raw_data->'$.post_fields.Mobile_No') as mobile_no from tbl_tdrawdata r1, tbl_industrial_estate i1 where r1.raw_data->'$.post_fields.Taluka'=i1.taluka and r1.raw_data->'$.post_fields.Area'=i1.area_id and r1.raw_data->'$.post_fields.IndustrialEstate'=i1.industrial_estate and JSON_CONTAINS_PATH(raw_data, 'one', '$.plot_details') = 0 and raw_data->'$.post_fields.IndustrialEstate'!='' and i1.id=?");
                $stmt_firm_list->bind_param("i",$estateid_est);
                $stmt_firm_list->execute();
                $firm_result = $stmt_firm_list->get_result();
                $stmt_firm_list->close();

                while($firm_list = mysqli_fetch_array($firm_result)){
            ?>

                    <option value="<?php echo $firm_list["id"] ?>" <?php echo ($rawdataid_est==$firm_list["id"])?"selected":""?>>
                        <?php echo $firm_list["firm_name"].' ( '.$firm_list["contact_name"].' - '.$firm_list["mobile_no"].' ) ' ?>
                    </option>
            <?php } ?>
                  </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="status_comp">Status</label>
                <input type="text" name="company_status" class="form-control" value="<?php echo $status_company ?>" disabled>
              </div>


              <div class="mb-3">
                <?php 
                $stmt_emp = $obj->con1->prepare("SELECT max(a1.id),a1.inq_id, a1.user_id,u1.name FROM tbl_tdrawassign a1,tbl_users u1 where a1.user_id=u1.id and a1.inq_id=? GROUP BY inq_id");
                $stmt_emp->bind_param("i",$rawdataid_comp);
                $stmt_emp->execute();
                $emp_result = $stmt_emp->get_result()->fetch_assoc();
                $stmt_emp->close();
                ?>
                <label class="form-label" for="emp_comp">Employee Name</label>
                <input type="text" name="emp_comp" class="form-control" value="<?php echo $emp_name ?>" disabled>
              </div>
              <div class="mb-3">
                <label class="form-label" for="factory_address_est">Factory Address</label>
                <input type="text" name="factory_address_est" id="factory_address_est" class="form-control" value="" >
              </div>
              
              
              <div id="company_status_est" class="text-success"></div>
              <div id="plotting_div_est"></div>

              <button type="submit" name="btnsubmit_est" id="btnsubmit_est" class="btn btn-primary" <?php echo (isset($selecttype_est) && $selecttype_est=="select_estate_first")?"":"disabled" ?>>Save</button>
              <!-- <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button> -->
              <button name="btncancel" id="btncancel" class="btn btn-secondary" onclick="closeNewTabAndReturn()">Cancel</button>

            </div>


            <!-- div for selecting company first -->
            <div id="select_company_first_div" <?php echo (isset($selecttype_est) && $selecttype_est=="select_company_first")?"":"hidden"?>>
             
              <div class="mb-3">
                <label class="form-label" for="rawdata_id_comp">Company</label>
                <input type="hidden" name="rawdata_id_comp" value="<?php echo $rawdataid_comp?>">
                <?php 
                    $stmt_company_list = $obj->con1->prepare("SELECT id, json_unquote(raw_data->'$.post_fields.IndustrialEstate') as ind_estate, json_unquote(raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(raw_data->'$.post_fields.Contact_Name') as contact_name, json_unquote(raw_data->'$.post_fields.Mobile_No') as mobile_no FROM tbl_tdrawdata WHERE id=?");
                    $stmt_company_list->bind_param("i",$rawdataid_comp);
                    $stmt_company_list->execute();
                    $company_result = $stmt_company_list->get_result()->fetch_assoc();
                    $stmt_company_list->close();
                ?>
                <input type="text" name="rawdata_id_comp2" id="rawdata_id_comp2" class="form-control" value="<?php echo $company_result['firm_name']." ( ".$company_result['contact_name']." - ".$company_result['mobile_no']." ) " ?>" disabled>
              </div>

              <div id="company_status_comp" class="text-success"></div>
              <div class="mb-3">
                <label class="form-label" for="factory_address_comp">Factory Address</label>
                <input type="text" name="factory_address_comp" id="factory_address_comp" class="form-control" value="" >
              </div>
              <div class="mb-3">
                <label class="form-label" for="status_comp">Status</label>
                <input type="text" name="status_comp" class="form-control" value="<?php echo $status_company ?>" disabled>
              </div>

              <div class="mb-3">
                <label class="form-label" for="state_comp">State</label>
                <select name="state_comp" id="state_comp" class="form-control" onchange="cityList_tbl_indestate(this.value)" <?php echo (isset($selecttype_est) && $selecttype_est=="select_company_first")?"required":"" ?>>
                  <option value="">Select State</option>
          <?php 
            $stmt_state_list = $obj->con1->prepare("SELECT DISTINCT(state_id) from tbl_industrial_estate");
            $stmt_state_list->execute();
            $state_result = $stmt_state_list->get_result();
            $stmt_state_list->close();
            while($state_list=mysqli_fetch_array($state_result)){ 
          ?>
              <option value="<?php echo $state_list["state_id"] ?>" <?php echo ($state_comp==$state_list["state_id"])?"selected":""?>><?php echo $state_list["state_id"] ?></option>
          <?php } ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="city_comp">City</label>
                <select name="city_comp" id="city_comp" class="form-control" onchange="talukaList_tbl_indestate(state_comp.value,this.value)" <?php echo (isset($selecttype_est) && $selecttype_est=="select_company_first")?"required":"" ?>>
                  <option value="">Select City</option>
          <?php 
            if($state_comp!=""){
              $stmt_city = $obj->con1->prepare("SELECT DISTINCT(city_id) from tbl_industrial_estate where state_id=?");
              $stmt_city->bind_param("s",$state_comp);
              $stmt_city->execute();
              $city_result = $stmt_city->get_result();
              $stmt_city->close();
              while($city_list=mysqli_fetch_array($city_result)){ 
          ?>
              <option value="<?php echo $city_list["city_id"] ?>" <?php echo ($city_comp==$city_list["city_id"])?"selected":""?>><?php echo $city_list["city_id"] ?></option>
          <?php } } ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="taluka_comp">Taluka</label>
                <select name="taluka_comp" id="taluka_comp" onchange="areaList_tbl_indestate(state_comp.value,city_comp.value,this.value)" class="form-control" <?php echo (isset($selecttype_est) && $selecttype_est=="select_company_first")?"required":"" ?>>
                  <option value="">Select Taluka</option>
          <?php 
            if($state_comp!="" && $city_comp!=""){
              $stmt_taluka = $obj->con1->prepare("SELECT DISTINCT(taluka) from tbl_industrial_estate where state_id=? and city_id=?");
              $stmt_taluka->bind_param("ss",$state_comp,$city_comp);
              $stmt_taluka->execute();
              $taluka_result = $stmt_taluka->get_result();
              $stmt_taluka->close();
              while($taluka_list=mysqli_fetch_array($taluka_result)){ ?>
                <option value="<?php echo $taluka_list["taluka"] ?>" <?php echo ($taluka_comp==$taluka_list["taluka"])?"selected":""?>>
                    <?php echo $taluka_list["taluka"] ?>
                </option>
          <?php } } ?>
                </select>
              </div>
              
              <div class="mb-3">
                <label class="form-label" for="area_comp">Area</label>
                <select name="area_comp" id="area_comp" class="form-control" onchange="estateList_tbl_indestate(state_comp.value,city_comp.value,taluka_comp.value,this.value)" <?php echo (isset($selecttype_est) && $selecttype_est=="select_company_first")?"required":"" ?>>
                  <option value="">Select Area</option>
            <?php
              if($state_comp!="" && $city_comp!="" && $taluka_comp!=""){
                $stmt = $obj->con1->prepare("SELECT DISTINCT(area_id) from tbl_industrial_estate where state_id=? and city_id=? and taluka=?");
                $stmt->bind_param("sss",$state_comp,$city_comp,$taluka_comp);
                $stmt->execute();
                $res = $stmt->get_result();
                $stmt->close();

                while($area=mysqli_fetch_array($res))
                {
            ?>
                    <option value="<?php echo $area["area_id"] ?>" <?php echo ($area_comp==$area["area_id"])?"selected":""?>><?php echo $area["area_id"] ?></option>
            <?php } } ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="industrial_estate_id_comp">Industrial Estate</label>
                <select name="industrial_estate_id_comp" id="industrial_estate_id_comp" onchange="getPlot_companyPlot_comp(industrial_estate_id_comp.value)" class="form-control" <?php echo (isset($selecttype_est) && $selecttype_est=="select_company_first")?"required":"" ?>>
                    <option value="">Select Industrial Estate</option>
            <?php 
              if($state_comp!="" && $city_comp!="" && $taluka_comp!="" && $area_comp!=""){
                $stmt = $obj->con1->prepare("SELECT id,industrial_estate FROM tbl_industrial_estate where state_id=? and city_id=? and taluka=? and area_id=?");
                $stmt->bind_param("ssss",$state_comp,$city_comp,$taluka_comp,$area_comp);
                $stmt->execute();
                $res = $stmt->get_result();
                $stmt->close();

                while($estate=mysqli_fetch_array($res))
                {
            ?>
                    <option value="<?php echo $estate["id"] ?>"><?php echo $estate["industrial_estate"] ?></option>
            <?php } } ?>
                  </select>
              </div>

              <div class="mb-3">
                <?php 
                
                $stmt_emp = $obj->con1->prepare("SELECT max(a1.id),a1.inq_id, a1.user_id,u1.name FROM tbl_tdrawassign a1,tbl_users u1 where a1.user_id=u1.id and a1.inq_id=? GROUP BY inq_id");
                $stmt_emp->bind_param("i",$_COOKIE['rawdataid_comp_addplot']);
                $stmt_emp->execute();
                $emp_result = $stmt_emp->get_result()->fetch_assoc();
                $stmt_emp->close();
                ?>
                <label class="form-label" for="emp_est">Employee Name</label>
                <input type="text" name="emp_est" class="form-control" value="<?php echo $emp_name ?>" disabled>
              </div>
              
              <div id="plotting_div_comp"></div>
                          
              <button type="submit" name="btnsubmit_comp" id="btnsubmit_comp" class="btn btn-primary" <?php echo (isset($selecttype_est) && $selecttype_est=="select_company_first")?"":"disabled" ?>>Save</button>
              <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button>
              
            </div>

          </form>
        </div>
      </div>
    </div>
    
  </div>


<!-- / Content -->
<script type="text/javascript">

function closeNewTabAndReturn() {
  // page_name = readCookie("redirection_pagename")
  // Close the new tab
  window.close();
  // Focus back to the opener window (the previous tab)
  window.opener.focus();
  // Reload the opener window
  // window.location.href = page_name;
}

$( document ).ready(function() {
  
  if(readCookie("selecttype_comp_addplot")=="select_estate_first") { 
    var estate_id = readCookie("estateid_comp_addplot");
    getPlot_companyPlot_est(estate_id);
  }
  /*else if(readCookie("selecttype_comp_addplot")=="select_company_first") { 
    // suffix = '_comp';
  }*/
  
});

// set factory address
  if (localStorage.getItem("factoryadd_est_addplot") != null) {
   
  document.getElementById("factory_address_est").value = localStorage.getItem("factoryadd_est_addplot");
}
if (localStorage.getItem("factoryadd_comp_addplot") != null) {
 
  document.getElementById("factory_address_comp").value = localStorage.getItem("factoryadd_comp_addplot");
}
  
  function changeForm() {
    if($('#select_company_first').is(':checked')) { 
      $('#select_company_first_div').removeAttr('hidden');
      $('#select_estate_first_div').attr('hidden',true);
      $("[id$='_comp']:not([id='road_no_comp'])").attr("required", true);
      $("[id$='_est']").removeAttr('required');
    }
    else if($('#select_estate_first').is(':checked')) { 
      $('#select_estate_first_div').removeAttr('hidden');
      $('#select_company_first_div').attr('hidden',true);
      $("[id$='_est']:not([id='road_no_est'])").attr("required", true);
      $("[id$='_comp']").removeAttr('required');
    }
  }

  function getPlot_companyPlot_est(estate_id){
    if(estate_id!=""){
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getPlot_companyPlot_est",
        data: "estate_id="+estate_id,
        cache: false,
        success: function(result){
          var data = result.split("@@@@@");
          $('#plotting_div_est').html('');
          $('#plotting_div_est').html(data[0]);
          if(data[1]==true){
            document.getElementById('btnsubmit_est').disabled = false;
          }else{
            document.getElementById('btnsubmit_est').disabled = true;
          }
        }
      });
    }
  }

  function getPlot_companyPlot_comp(estate_id){
    if(estate_id!=""){
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getPlot_companyPlot_comp",
        data: "estate_id="+estate_id,
        cache: false,
        success: function(result){
          console.log(result);
          var data = result.split("@@@@@");
          $('#plotting_div_comp').html('');
          $('#plotting_div_comp').html(data[0]);
          if(data[1]==true){
            document.getElementById('btnsubmit_comp').disabled = false;
          }else{
            document.getElementById('btnsubmit_comp').disabled = true;
          }
        }
      });
    }
  }

  function getRoadPlots_companyPlot(road_no,estate_id){
    if(road_no!=""){
      if(readCookie("selecttype_comp_addplot")=="select_company_first") { 
        suffix = '_comp';
      }
      else if(readCookie("selecttype_comp_addplot")=="select_estate_first") { 
        suffix = '_est';
      }
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getRoadPlots_companyPlot",
        data: "road_no="+road_no+"&estate_id="+estate_id,
        cache: false,
        success: function(result){
          $('#plot_no'+suffix).html('');
          $('#plot_no'+suffix).append(result);
          $('#floor'+suffix).html('');
          $('#floor'+suffix).append('<option value="">Select Floor No.</option>');
        }
      });
    }
    else{
      $('#floor'+suffix).html('');
      $('#floor'+suffix).append('<option value="">Select Floor No.</option>');
    }
  }

  function getFloor_companyPlot(plot_no,estate_id){

    if(plot_no!=""){
      if(readCookie("selecttype_comp_addplot")=="select_company_first") { 
        suffix = '_comp';
      }
      else if(readCookie("selecttype_comp_addplot")=="select_estate_first") { 
        suffix = '_est';
      }
      road_no = $('#road_no'+suffix).val();
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getFloor_companyPlot",
        data: "plot_no="+plot_no+"&estate_id="+estate_id+"&road_no="+road_no,
        cache: false,
        success: function(result){
          $('#floor'+suffix).html('');
          $('#floor'+suffix).append(result);
        }
      });
    }
    else{
      $('#floor'+suffix).html('');
      $('#floor'+suffix).append('<option value="">Select Floor No.</option>');
    }
  }

  function cityList_tbl_indestate(state){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=cityList_tbl_indestate",
      data: "state="+state,
      cache: false,
      success: function(result){
        $('#city_comp').html('');
        $('#city_comp').append(result);
        
        $('#taluka_comp').html('');
        $('#taluka_comp').html('<option value="">Select Taluka</option>');
        $('#area_comp').html('');
        $('#area_comp').html('<option value="">Select Area</option>');
        $('#industrial_estate_id_comp').html('');
        $('#industrial_estate_id_comp').html('<option value="">Select Industrial Estate</option>');
        $('#plotting_div_comp').html('');
      }
    });
  }

  function talukaList_tbl_indestate(state,city){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=talukaList_tbl_indestate",
      data: "state="+state+"&city="+city,
      cache: false,
      success: function(result){
        $('#taluka_comp').html('');
        $('#taluka_comp').append(result);
        
        $('#area_comp').html('');
        $('#area_comp').html('<option value="">Select Area</option>');
        $('#industrial_estate_id_comp').html('');
        $('#industrial_estate_id_comp').html('<option value="">Select Industrial Estate</option>');
        $('#plotting_div_comp').html('');
      }
    });
  }

  function areaList_tbl_indestate(state,city,taluka){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=areaList_tbl_indestate",
      data: "state="+state+"&city="+city+"&taluka="+taluka,
      cache: false,
      success: function(result){
        $('#area_comp').html('');
        $('#area_comp').append(result);

        $('#industrial_estate_id_comp').html('');
        $('#industrial_estate_id_comp').html('<option value="">Select Industrial Estate</option>');
        $('#plotting_div_comp').html('');
      }
    });
  }

  function estateList_tbl_indestate(state,city,taluka,area){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=estateList_tbl_indestate",
      data: "state="+state+"&city="+city+"&taluka="+taluka+"&area="+area,
      cache: false,
      success: function(result){
        $('#industrial_estate_id_comp').html('');
        $('#industrial_estate_id_comp').append(result);

        $('#plotting_div_comp').html('');
      }
    });
  }
</script>
<?php 
  include("footer.php");
?>