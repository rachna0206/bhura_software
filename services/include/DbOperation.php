<?php
date_default_timezone_set("Asia/Kolkata");
class DbOperation
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect();
        $this->con = $db->connect();
    }


//Checking the user is valid or not by api key

public function isValidUser($api_key) {
    
    $stmt = $this->con->prepare("SELECT id,uid from tbl_user_devices WHERE device_id = ?");
    $stmt->bind_param("s", $api_key);
    $stmt->execute();
    $stmt->store_result();
    $num_rows = $stmt->num_rows;
    $stmt->close();
    return $num_rows > 0;
}

public function assignorLogin($userid, $password)
{           
    $qr = $this->con->prepare("select * from tbl_users where email=? and role!='superadmin'");
    $qr->bind_param("s",$userid);
    $qr->execute();
    $result = $qr->get_result();
    $num=mysqli_num_rows($result);
    
    if($num>0)
    {
        $row=mysqli_fetch_array($result);
        $hashed_pass=$row["password"];
        $qr->close();

        $verify = password_verify($password, $hashed_pass);

        if ($verify) {
            return $num>0;
        } 
    }
    else
    {
        return 0;
    }
}

public function assignor_data($userid)
{        
    $qr = $this->con->prepare("select id,name,role from tbl_users where email=?");
    $qr->bind_param("s",$userid);
    $qr->execute();
    $result = $qr->get_result()->fetch_assoc();
    $qr->close();
    return $result;
}

public function get_username($userid)
{
    $qr = $this->con->prepare("select name, role from tbl_users where id=?");
    $qr->bind_param("i",$userid);
    $qr->execute();
    $result = $qr->get_result()->fetch_assoc();
    $qr->close();
    //$name = $result['name'];
    return $result;
}


public function insert_device_type($device_type, $token, $cid,$api_key)
    {
       
        $stmt = $this->con->prepare("INSERT INTO `tbl_user_devices`(`uid`, `token`,`device_id`, `type`) VALUES (?,?,?,?)");
        $stmt->bind_param("isss", $cid, $token,$api_key, $device_type);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return 1;
        } else {
            return 0;
        }
    }

    public function check_loggedin_user($user_id)
    {
        //SELECT * FROM `tbl_user_devices` WHERE uid=31
        $stmt = $this->con->prepare("SELECT count(*) as count FROM `tbl_user_devices` WHERE uid=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['count'];
    }

    public function logout($c_id)
    {
        
        //$stmt = $this->con->prepare("DELETE FROM `tbl_user_devices` WHERE `uid`=? and `token`=? and `type`=?"); (update by nidhi)
        $stmt = $this->con->prepare("DELETE FROM `tbl_user_devices` WHERE `uid`=?");
        $stmt->bind_param("i", $c_id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return 1;
        } else {
            return 0;
        }
    }


public function assigned_estates($userid)
{
    $stmt_list = $this->con->prepare("SELECT * from ((SELECT i1.* from tbl_industrial_estate i1, assign_estate a1 where a1.industrial_estate_id=i1.id and employee_id=? and start_dt<=curdate() and end_dt>=curdate() and state_id='GUJARAT' and city_id='SURAT' and action='estate_plotting' and i1.id in (SELECT industrial_estate_id FROM `pr_add_industrialestate_details` where status='Verified' and plotting_pattern is null)) union (SELECT i1.* from tbl_industrial_estate i1, assign_estate a1 where a1.industrial_estate_id=i1.id and employee_id=? and start_dt<=curdate() and end_dt>=curdate() and state_id='GUJARAT' and city_id='SURAT' and action='estate_plotting' and i1.id not in (SELECT industrial_estate_id FROM `pr_add_industrialestate_details`))) as result"); 
    $stmt_list->bind_param("ii",$userid,$userid);
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();
    return $result;
}

public function insert_estate_status($userid,$verify_status,$industrial_estate_id)
{
    $stmt_detail = $this->con->prepare("INSERT INTO `pr_add_industrialestate_details`(`industrial_estate_id`, `user_id`,`status`) VALUES (?,?,?)");
    $stmt_detail->bind_param("iis",$industrial_estate_id,$userid,$verify_status);
    $result=$stmt_detail->execute();
    $stmt_detail->close();

    if ($result) {
        return 1;
    } else {
        return 0;
    }
}

public function estate_plotting_series($userid,$verify_status,$industrial_estate_id,$plotting_pattern,$state,$city,$taluka,$area,$industrial_estate,$location,$from_plotno,$to_plotno)
{
    $stmt_detail = $this->con->prepare("INSERT INTO `pr_add_industrialestate_details`(`industrial_estate_id`, `plotting_pattern`, `location`, `user_id`,`status`) VALUES (?,?,?,?,?)");
    $stmt_detail->bind_param("issis",$industrial_estate_id,$plotting_pattern,$location,$userid,$verify_status);
    $result=$stmt_detail->execute();
    $stmt_detail->close();

    if($result){

        return 1;
    }
    else {
        return 0;
    }
}


public function estate_plotting_road($userid,$verify_status,$industrial_estate_id,$plotting_pattern,$state,$city,$taluka,$area,$industrial_estate,$location,$images,$from_roadno,$to_roadno,$road_plotting,$additional_road_plotting,$road_cnt)
{
 
    $stmt_detail = $this->con->prepare("INSERT INTO `pr_add_industrialestate_details`(`industrial_estate_id`, `plotting_pattern`, `location`, `user_id`,`status`) VALUES (?,?,?,?,?)");
    $stmt_detail->bind_param("issis",$industrial_estate_id,$plotting_pattern,$location,$userid,$verify_status);
    $result=$stmt_detail->execute();
    $stmt_detail->close();

    if($result){


        return 1;
    }
    else{
        return 0;
    }
}


public function pr_estate_subimages($ind_estate_id,$SubImageName)
{
    $stmt_image = $this->con->prepare("INSERT INTO `pr_estate_subimages`(`industrial_estate_id`, `image`) VALUES (?,?)");
    $stmt_image->bind_param("ss",$ind_estate_id,$SubImageName);
    $Resp=$stmt_image->execute();
    $stmt_image->close();

    if ($Resp) {
        return 1;
    } else {
        return 0;
    }
}
public function pr_estate_roadplot($ind_estate_id,$road_number,$from_plotno_road,$to_plotno_road,$user_id)
{
    $stmt_plot = $this->con->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `road_no`, `plot_start_no`, `plot_end_no`, `user_id`) VALUES (?,?,?,?,?)");
    $stmt_plot->bind_param("isssi",$ind_estate_id,$road_number,$from_plotno_road,$to_plotno_road,$user_id);
    $Resp=$stmt_plot->execute();
    $stmt_plot->close();

    if ($Resp) {
        return 1;
    } else {
        return 0;
    }
}

public function tbl_tdrawdata($json,$user_id)
{
    $stmt_rawdata = $this->con->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
    $stmt_rawdata->bind_param("ss",$json,$user_id);
    $Resp=$stmt_rawdata->execute();
    $stmt_rawdata->close();

    if ($Resp) {
        return 1;
    } else {
        return 0;
    }
}

public function insert_tbl_tdrawdata($json,$user_id)
{
    $stmt_rawdata = $this->con->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
    $stmt_rawdata->bind_param("ss",$json,$user_id);
    $Resp=$stmt_rawdata->execute();
    $insert_id = mysqli_insert_id($this->con);
    $stmt_rawdata->close();

    return $insert_id;
}

public function company_plot_insert($plot_no,$floor,$road_no,$plot_id,$industrial_estate_id,$user_id,$plot_status=NULL,$pr_company_detail_id=NULL)
{
    $stmt_company_plot = $this->con->prepare("INSERT INTO `pr_company_plots`(`plot_no`, `floor`, `road_no`, `plot_id`, `industrial_estate_id`, `user_id`,`plot_status`,`company_id`) VALUES (?,?,?,?,?,?,?,?)");
    $stmt_company_plot->bind_param("ssssiisi",$plot_no,$floor,$road_no,$plot_id,$industrial_estate_id,$user_id,$plot_status,$pr_company_detail_id);
    $Resp=$stmt_company_plot->execute();
    $stmt_company_plot->close();
    return $Resp;
}

public function company_plot_update($plot_status,$plot_id,$pr_company_detail_id,$pr_company_plot_id,$user_id)
{
    $stmt_company_plot = $this->con->prepare("UPDATE `pr_company_plots` SET `plot_status`=?, `plot_id`=?, `company_id`=?, `user_id`=? WHERE `pid`=?");
    $stmt_company_plot->bind_param("ssiii",$plot_status,$plot_id,$pr_company_detail_id,$user_id,$pr_company_plot_id);
    $Resp=$stmt_company_plot->execute();
    $stmt_company_plot->close();
    return $Resp;
}

public function company_details_insert($contact_name,$mobile_no,$state,$city,$taluka,$area,$industrial_estate,$estate_id,$user_id,$insert_id)
{
    $stmt_company_detail = $this->con->prepare("INSERT INTO `pr_company_details`(`contact_name`, `mobile_no`, `state`, `city`, `taluka`, `area`, `industrial_estate`, `industrial_estate_id`, `user_id`, `rawdata_id`) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt_company_detail->bind_param("sssssssiii",$contact_name,$mobile_no,$state,$city,$taluka,$area,$industrial_estate,$estate_id,$user_id,$insert_id);
    $Resp=$stmt_company_detail->execute();
    $company_last_insert_id = mysqli_insert_id($this->con);
    $stmt_company_detail->close();
    
    return $company_last_insert_id;
}

// add industrial estate
public function add_industrial_estate($state,$city,$taluka,$area,$industrial_estate,$description,$plotting_pattern,$location,$userid,$verify_status)
{
 
    $stmt = $this->con->prepare("INSERT INTO `tbl_industrial_estate`(`state_id`, `city_id`, `taluka`, `area_id`, `industrial_estate`,`description`) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssssss",$state,$city,$taluka,$area,$industrial_estate,$description);
    $Resp=$stmt->execute();
    $insert_id = mysqli_insert_id($this->con);
    $stmt->close();
    
    if($Resp){

        //$res_estate_details=$this->pr_add_industrialestate_details($insert_id,$plotting_pattern,$location,$userid,$verify_status);

        return $insert_id;
    }
    else{
        return 0;
    }
}
private function pr_add_industrialestate_details($ind_estate_id,$plotting_pattern,$location,$user_id,$verify_status)
{
    $stmt_detail = $this->con->prepare("INSERT INTO `pr_add_industrialestate_details`(`industrial_estate_id`, `plotting_pattern`, `location`, `user_id`,`status`) VALUES (?,?,?,?,?)");
    $stmt_detail->bind_param("issis",$ind_estate_id,$plotting_pattern,$location,$user_id,$verify_status);
    $Resp=$stmt_detail->execute();
    $stmt_detail->close();
    if($Resp)
    {   
        return 1;
    }
    else{
        return 0;
    }

    

}

// get taluka list
public function get_taluka_list()
{
    $stmt_taluka = $this->con->prepare("select DISTINCT(subdistrict) from all_taluka where state='GUJARAT' and district='SURAT'");
    $stmt_taluka->execute();
    $taluka_result = $stmt_taluka->get_result();
    $stmt_taluka->close();
    return $taluka_result;
}

// get constitution list
public function get_area_list($taluka,$state,$city)
{
    $stmt = $this->con->prepare("select DISTINCT(village_name) from all_taluka where state=? and district=? and subdistrict=?");
    $stmt->bind_param("sss",$state,$city,$taluka);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();
    return $res;
}

// get constitution list
public function get_constituion_list()
{
    $stmt_list = $this->con->prepare("SELECT * FROM `tbl_constitution_master`"); 
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();
    return $result;
}

// get segment list
public function get_segment_list()
{
    $stmt_list = $this->con->prepare("SELECT * FROM `tbl_segment`"); 
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();
    return $result;
}

// get Source-Type List for Source
public function get_source_type_list()
{
    $stmt_list = $this->con->prepare("SELECT source_type as name FROM `tbl_sourcetype_master`"); 
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();
    return $result;
}

// get Associate-Type List for Source
public function get_associate_list()
{
    $stmt_list = $this->con->prepare("SELECT asso_segment_name as name FROM `asso_segment_master`"); 
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();
    return $result;
}

// get source name list
public function get_source_name_list($source_type,$reference)
{   
    if($reference=='source_master'){
        $stmt_sourcename_list = $this->con->prepare("SELECT s1.name as source_name FROM tbl_sourcing_master s1, tbl_sourcetype_master t1 WHERE s1.source_type_id=t1.id and t1.source_type=?");
        $stmt_sourcename_list->bind_param("s",$source_type);
    }
    else if($reference=='associate_master'){
        $stmt_sourcename_list = $this->con->prepare("SELECT concat(json_unquote(raw_data->'$.post_fields.Firm_Name'),' - ',json_unquote(raw_data->'$.post_fields.Contact_Name')) as source_name FROM `tbl_tdassodata` WHERE lower(raw_data->'$.post_fields.Segment_Name') like '%".strtolower($source_type)."%'");
    }
    else if($reference=='new_system'){
        $stmt_sourcename_list = $this->con->prepare("SELECT concat(json_unquote(raw_data->'$.post_fields.Firm_Name'),' - ',json_unquote(raw_data->'$.post_fields.Contact_Name')) as source_name FROM `tbl_tdrawdata`");
    }
    $stmt_sourcename_list->execute();
    $sourcename_result = $stmt_sourcename_list->get_result();
    $stmt_sourcename_list->close();

    return $sourcename_result;
}

// get reason type list
public function get_reason_list()
{
    $stmt_list = $this->con->prepare("SELECT * FROM `tbl_badlead_reasons` WHERE STATUS='active' ORDER BY reason_text"); 
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();
    return $result;
}

// get assigned estates for company
public function assigned_estates_company($userid)
{
    $stmt_estate_list = $this->con->prepare("SELECT a1.industrial_estate_id,i1.industrial_estate, i1.taluka, i1.area_id FROM assign_estate a1, tbl_industrial_estate i1, pr_add_industrialestate_details p1 WHERE a1.industrial_estate_id=i1.id and i1.id=p1.industrial_estate_id and employee_id=? and start_dt<=curdate() and end_dt>=curdate() and a1.action='company_entry' and p1.status='Verified';");
    $stmt_estate_list->bind_param("i",$userid);
    $stmt_estate_list->execute();
    $estate_result = $stmt_estate_list->get_result();
    $stmt_estate_list->close();
    return $estate_result;
}

//get industrial estate
public function get_ind_estate($estate_id)
{
    $stmt_estate = $this->con->prepare("SELECT i1.*,a1.plotting_pattern FROM tbl_industrial_estate i1 , pr_add_industrialestate_details a1 where i1.id=a1.industrial_estate_id and i1.id=?");
    $stmt_estate->bind_param("i",$estate_id);
    $stmt_estate->execute();
    $estate_res = $stmt_estate->get_result()->fetch_assoc();
    $stmt_estate->close();
    return $estate_res;
}

//get road no
public function get_road_no($estate_id)
{
    $stmt_road = $this->con->prepare("SELECT DISTINCT(road_no) FROM `pr_estate_roadplot` WHERE industrial_estate_id=? order by abs(road_no)");
    $stmt_road->bind_param("i",$estate_id);
    $stmt_road->execute();
    $road_res = $stmt_road->get_result();
    $stmt_road->close();
    return $road_res;
}

//get plot no
public function get_plot_no($filter,$estate_id)
{
    if($filter=="Visit Pending"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 on p1.company_id=d1.cid where p1.industrial_estate_id=? AND ((d1.image IS NULL) OR (d1.image='')) order by abs(plot_no)");
    }
    else if($filter=="Open Plot"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 ON p1.company_id=d1.cid WHERE p1.industrial_estate_id=? and plot_status='Open Plot' AND ((d1.image IS NOT NULL) AND (d1.image!='')) order by abs(plot_no)");
    } 
    else if($filter=="Positive"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Positive' and p1.industrial_estate_id=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
    } 
    else if($filter=="Negative"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Negative' and p1.industrial_estate_id=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
    } 
    else if($filter=="Existing Client"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Existing Client' and p1.industrial_estate_id=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
    }
    else if($filter=="No Filter"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and (c1.status NOT IN ('Positive','Negative','Existing Client') OR c1.status IS null) and p1.plot_status!='Open Plot' and ((c1.image IS NOT NULL) AND (c1.image!='')) and p1.industrial_estate_id=? order by abs(p1.plot_no)");
    }
        
    $stmt_plot->bind_param("i",$estate_id);    
    $stmt_plot->execute();
    $plot_res = $stmt_plot->get_result();
    $stmt_plot->close();

    return $plot_res;
}

public function get_plot_no_old($taluka,$ind_estate,$area)
{
    $stmt_plot = $this->con->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($taluka)."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($ind_estate)."%' and lower(raw_data->'$.post_fields.Area') like '%".strtolower($area)."%'");
    $stmt_plot->execute();
    $plot_res = $stmt_plot->get_result();
    $stmt_plot->close();
    return $plot_res;
}


//get industrial estate data from id
public function get_ind_estate_data($estate_id)
{
    $stmt_estate = $this->con->prepare("SELECT * FROM tbl_industrial_estate i1 WHERE id=?");
    $stmt_estate->bind_param("i",$estate_id);
    $stmt_estate->execute();
    $estate_res = $stmt_estate->get_result()->fetch_assoc();
    $stmt_estate->close();
    return $estate_res;
}

//get plot floor
public function get_plot_floor($plot_no,$road_no,$filter,$estate_id,$plotting_pattern)
{
    if($plotting_pattern=='Series'){
        if($filter=="Visit Pending"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 on p1.company_id=d1.cid where p1.industrial_estate_id=? AND p1.plot_no=? AND ((d1.image IS NULL) OR (d1.image='')) order by p1.floor");
        }
        else if($filter=="Open Plot"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 ON p1.company_id=d1.cid WHERE p1.industrial_estate_id=? AND p1.plot_no=? AND plot_status='Open Plot' AND ((d1.image IS NOT NULL) AND (d1.image!='')) order by p1.floor");
        } 
        else if($filter=="Positive"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Positive' and p1.industrial_estate_id=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
        } 
        else if($filter=="Negative"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Negative' and p1.industrial_estate_id=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
        } 
        else if($filter=="Existing Client"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Existing Client' and p1.industrial_estate_id=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
        }
        else if($filter=="No Filter"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and (c1.status NOT IN ('Positive','Negative','Existing Client') OR c1.status IS null) and p1.plot_status!='Open Plot' and ((c1.image IS NOT NULL) AND (c1.image!='')) and p1.industrial_estate_id=? AND p1.plot_no=? order by p1.floor");
        } 
        $stmt_floor->bind_param("is",$estate_id,$plot_no);
    }
    else if($plotting_pattern=='Road'){
        if($filter=="Visit Pending"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 on p1.company_id=d1.cid where p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? AND ((d1.image IS NULL) OR (d1.image='')) order by p1.floor");
        }
        else if($filter=="Open Plot"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 ON p1.company_id=d1.cid WHERE p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? AND plot_status='Open Plot' AND ((d1.image IS NOT NULL) AND (d1.image!='')) order by p1.floor");
        } 
        else if($filter=="Positive"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Positive' and p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
        } 
        else if($filter=="Negative"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Negative' and p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
        } 
        else if($filter=="Existing Client"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Existing Client' and p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
        }
        else if($filter=="No Filter"){
            $stmt_floor = $this->con->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and (c1.status NOT IN ('Positive','Negative','Existing Client') OR c1.status IS null) and p1.plot_status!='Open Plot' and ((c1.image IS NOT NULL) AND (c1.image!='')) and p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? order by p1.floor");
        } 
        $stmt_floor->bind_param("iss",$estate_id,$road_no,$plot_no);
    }

    $stmt_floor->execute();
    $floor_res = $stmt_floor->get_result();
    $stmt_floor->close();
        
    return $floor_res;
}

public function get_plot_floor_old($taluka,$industrial_estate,$area,$plot_no,$road_no)
{
    $stmt_floor = $this->con->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($taluka)."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($industrial_estate)."%' and lower(raw_data->'$.post_fields.Area') like '%".strtolower($area)."%' and raw_data->'$.plot_details[*].Plot_No' like '%".$plot_no."%' and raw_data->'$.plot_details[*].Road_No' like '%".$road_no."%'");
    $stmt_floor->execute();
    $floor_res = $stmt_floor->get_result();
    $stmt_floor->close();
    return $floor_res;
}

//get road plot 
public function get_road_plot($filter,$estate_id,$road_no)
{
    if($filter=="Visit Pending"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 on p1.company_id=d1.cid where p1.industrial_estate_id=? AND p1.road_no=? AND ((d1.image IS NULL) OR (d1.image='')) order by abs(plot_no)");
    }
    else if($filter=="Open Plot"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 ON p1.company_id=d1.cid WHERE p1.industrial_estate_id=? AND p1.road_no=? and plot_status='Open Plot' AND ((d1.image IS NOT NULL) AND (d1.image!='')) order by abs(plot_no)");
    } 
    else if($filter=="Positive"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Positive' and p1.industrial_estate_id=? AND p1.road_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
    } 
    else if($filter=="Negative"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Negative' and p1.industrial_estate_id=? AND p1.road_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
    } 
    else if($filter=="Existing Client"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Existing Client' and p1.industrial_estate_id=? AND p1.road_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
    }
    else if($filter=="No Filter"){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and (c1.status NOT IN ('Positive','Negative','Existing Client') OR c1.status IS null) and p1.plot_status!='Open Plot' and ((c1.image IS NOT NULL) AND (c1.image!='')) and p1.industrial_estate_id=? AND p1.road_no=? order by abs(p1.plot_no)");
    } 

    $stmt_plot->bind_param("is",$estate_id,$road_no);
    $stmt_plot->execute();
    $plot_res = $stmt_plot->get_result();
    $stmt_plot->close();

    return $plot_res;
}

public function get_road_plot_old($taluka,$industrial_estate,$area)
{
    $stmt_floor = $this->con->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($taluka)."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($industrial_estate)."%' and lower(raw_data->'$.post_fields.Area') like '%".strtolower($area)."%'");
    $stmt_floor->execute();
    $floor_res = $stmt_floor->get_result();
    $stmt_floor->close();
    return $floor_res;
}

//get company details
public function get_company_details($taluka,$industrial_estate,$area,$plot_no,$floor_no,$road_no)
{
    $stmt_floor = $this->con->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($taluka)."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($industrial_estate)."%' and lower(raw_data->'$.post_fields.Area') like '%".strtolower($area)."%' and raw_data->'$.plot_details[*].Plot_No' like '%".$plot_no."%' and raw_data->'$.plot_details[*].Road_No' like '%".$road_no."%' and raw_data->'$.plot_details[*].Floor' like '%".$floor_no."%'");
    $stmt_floor->execute();
    $floor_res = $stmt_floor->get_result();
    $stmt_floor->close();
    return $floor_res;
}

// check gst no.
public function check_gst($gst_no,$id)
{
    if($id!=""){
        $stmt_gst = $this->con->prepare("select * from tbl_tdrawdata where raw_data->'$.post_fields.GST_No'=? and id!=?");
        $stmt_gst->bind_param("si",$gst_no,$id);
    }
    else{   
        $stmt_gst = $this->con->prepare("select * from tbl_tdrawdata where raw_data->'$.post_fields.GST_No'=?");
        $stmt_gst->bind_param("s",$gst_no);
    }

    $stmt_gst->execute();
    $res = $stmt_gst->get_result();
    $stmt_gst->close();
    return $res;
}

// get values from pr_company_plot
public function get_pr_company_plot($plotting_pattern,$estate_id,$plot_no,$floor_no,$road_no)
{
    if($plotting_pattern=="Series"){
        $stmt_company_plot = $this->con->prepare("SELECT pid, company_id, plot_no, floor, road_no, plot_status, plot_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=?");
        $stmt_company_plot->bind_param("sii",$plot_no,$floor_no,$estate_id);
    }
    else if($plotting_pattern=="Road"){
        $stmt_company_plot = $this->con->prepare("SELECT pid, company_id, plot_no, floor, road_no, plot_status, plot_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=? and road_no=?");
        $stmt_company_plot->bind_param("siis",$plot_no,$floor_no,$estate_id,$road_no);
    }
    $stmt_company_plot->execute();
    $pr_company_plot = $stmt_company_plot->get_result();
    $stmt_company_plot->close();

    return $pr_company_plot;
}

public function get_pr_company_details($plotting_pattern,$estate_id,$plot_no,$floor_no,$road_no){

    if($plotting_pattern=="Series"){
        $stmt_company_plot = $this->con->prepare("SELECT p1.pid, p1.company_id, p1.plot_no, p1.floor, p1.road_no, p1.plot_status, p1.plot_id, c1.image, c1.constitution, c1.status, c1.rawdata_id, c1.existing_client_status FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` c1 on p1.company_id=c1.cid WHERE p1.plot_no=? and p1.floor=? and p1.industrial_estate_id=?");
        $stmt_company_plot->bind_param("sii",$plot_no,$floor_no,$estate_id);
    }
    else if($plotting_pattern=="Road"){
        $stmt_company_plot = $this->con->prepare("SELECT p1.pid, p1.company_id, p1.plot_no, p1.floor, p1.road_no, p1.plot_status, p1.plot_id, c1.image, c1.constitution, c1.status, c1.rawdata_id, c1.existing_client_status FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` c1 on p1.company_id=c1.cid WHERE p1.plot_no=? and p1.floor=? and p1.industrial_estate_id=? and p1.road_no=?");
        $stmt_company_plot->bind_param("siis",$plot_no,$floor_no,$estate_id,$road_no);
    }
    $stmt_company_plot->execute();
    $pr_company_details = $stmt_company_plot->get_result()->fetch_assoc();
    $stmt_company_plot->close();

    return $pr_company_details;
}

public function get_tbl_tdrawassign($rawdata_id){
    $stmt_status = $this->con->prepare("SELECT stage FROM `tbl_tdrawassign` WHERE inq_id=? order by id desc LIMIT 1");
    $stmt_status->bind_param("i",$rawdata_id);
    $stmt_status->execute();
    $status_result = $stmt_status->get_result();
    $stmt_status->close();
    
    return $status_result;
}

// get json from tbl_tdrawdata
public function get_rawdata($id)
{
    $stmt_slist = $this->con->prepare("select * from tbl_tdrawdata where id=?");
    $stmt_slist->bind_param("i",$id);
    $stmt_slist->execute();
    $res = $stmt_slist->get_result()->fetch_assoc();
    $stmt_slist->close();
    return $res;
}

// update table tbl_tdrawdata
public function update_tbl_tdrawdata($json,$user_id,$id,$filter,$pr_company_detail_id)
{
    if($pr_company_detail_id=="" || $pr_company_detail_id==null || $pr_company_detail_id=="null"){
      $todays_date = date("Y-m-d H:i:s");
      $stmt = $this->con->prepare("update tbl_tdrawdata set raw_data=?, userid=?, raw_data_ts=? where id=?");
      $stmt->bind_param("sisi",$json,$user_id,$todays_date,$id);
    }
    else{
      $stmt = $this->con->prepare("update tbl_tdrawdata set raw_data=?, userid=? where id=?");
      $stmt->bind_param("sii",$json,$user_id,$id);
    }
    $Resp=$stmt->execute();
    $num_rows_aff = mysqli_affected_rows($this->con);
    $stmt->close();
    
    if ($Resp) {
        return $num_rows_aff;
    } else {
        return -1;
    }
}

// delete from table tbl_tdrawdata
public function delete_tbl_tdrawdata($delete_id)
{
    $stmt_del = $this->con->prepare("delete from tbl_tdrawdata where id=?");
    $stmt_del->bind_param("i",$delete_id);
    $Resp=$stmt_del->execute();   
    $stmt_del->close();
    return $Resp;
}

// update table tbl_tdrawdata
public function update_tbl_tdrawdata_contact($contact_name,$mobile_no,$plot_status,$user_id,$id)
{
    $stmt = $this->con->prepare("UPDATE `tbl_tdrawdata` SET raw_data=JSON_SET(raw_data,'$.post_fields.Contact_Name','".$contact_name."','$.post_fields.Mobile_No','".$mobile_no."','$.plot_details[0].Plot_Status','".$plot_status."'), userid='".$user_id."' WHERE id='".$id."'");
    $Resp=$stmt->execute();
    $num_rows_aff = mysqli_affected_rows($this->con);
    $stmt->close();
    
    if ($Resp) {
        return $num_rows_aff;
    } else {
        return -1;
    }
}

// for visit count
public function pr_visit_count($industrial_estate,$area,$taluka,$id,$user_id,$num_affected_rows)
{
    if($num_affected_rows>0){

        $stmt_count_list = $this->con->prepare("SELECT `cid`, `count`, date(`datetime`) as datetime FROM `pr_visit_count` WHERE industrial_estate=? and area=? and taluka=? and company_id=? and employee_id=?");
        $stmt_count_list->bind_param("sssii",$industrial_estate,$area,$taluka,$id,$user_id);
        $stmt_count_list->execute();
        $count_result = $stmt_count_list->get_result();
        $stmt_count_list->close();

        if(mysqli_num_rows($count_result)>0){
            $count = mysqli_fetch_array($count_result);
            if(strtotime($count['datetime'])!=strtotime(date("Y-m-d"))){
              $stmt_count = $this->con->prepare("UPDATE `pr_visit_count` set `count`=`count`+1 where `cid`=? and `employee_id`=?");
              $stmt_count->bind_param("ii",$count['cid'],$user_id);
              $Resp=$stmt_count->execute();
              $stmt_count->close();
              
              $stmt_visit_date = $this->con->prepare("INSERT INTO `pr_visit_dates`(`visit_count_id`) VALUES (?)");
              $stmt_visit_date->bind_param("i",$count['cid']);
              $Resp=$stmt_visit_date->execute();
              $stmt_visit_date->close();
            }
        }
        else{
            $count=1;
            $stmt_count = $this->con->prepare("INSERT INTO `pr_visit_count`(`industrial_estate`, `area`, `taluka`, `company_id`, `employee_id`, `count`) VALUES (?,?,?,?,?,?)");
            $stmt_count->bind_param("sssiii",$industrial_estate,$area,$taluka,$id,$user_id,$count);
            $Resp=$stmt_count->execute();
            $last_insert_id = mysqli_insert_id($this->con);
            $stmt_count->close();
            
            $stmt_visit_date = $this->con->prepare("INSERT INTO `pr_visit_dates`(`visit_count_id`) VALUES (?)");
            $stmt_visit_date->bind_param("i",$last_insert_id);
            $Resp=$stmt_visit_date->execute();
            $stmt_visit_date->close();
        }
    }
}

// check for entry in tbl_tdrawassign
public function checkCompany_rawassign($value)
{
  $stmt_comp = $this->con->prepare("SELECT COUNT(*) as cnt FROM `tbl_tdrawassign` WHERE inq_id=?");
  $stmt_comp->bind_param("i",$value);
  $stmt_comp->execute();
  $comp_result = $stmt_comp->get_result()->fetch_assoc();
  $stmt_comp->close();

  return $comp_result["cnt"];
}

// check for badlead in tbl_tdrawassign
public function check_for_badlead($value)
{
  $stmt_badlead = $this->con->prepare("SELECT * FROM `tbl_tdrawassign` WHERE inq_id=? and stage='badlead' order by id desc limit 1");
  $stmt_badlead->bind_param("i",$value);
  $stmt_badlead->execute();
  $badlead_result = $stmt_badlead->get_result(); //->fetch_assoc()
  $stmt_badlead->close();

  if(mysqli_num_rows($badlead_result)>0){
    $res = mysqli_fetch_array($badlead_result);
      if($res["stage"]=="badlead"){
        return 1;
      }
      else{
        return 0;
      }
  }
}

// insert into tbl_tdfollowup
public function insert_followup($user_id,$id,$followup_text,$followup_source,$followup_date)
{
    $stmt_followup = $this->con->prepare("INSERT INTO `tbl_tdfollowup`(`user_id`, `inq_id`, `followup_text`, `followup_source`, `followup_date`) VALUES (?,?,?,?,?)");
    $stmt_followup->bind_param("iisss",$user_id,$id,$followup_text,$followup_source,$followup_date);
    $Resp=$stmt_followup->execute();
    $stmt_followup->close();

    return $Resp;
}

// insert into tbl_tdrawassign
public function insert_rawassign($id,$admin_userid,$raw_assign_status)
{
    // echo "INSERT INTO `tbl_tdrawassign`(`inq_id`, `user_id`, `stage`) VALUES ('".$id."','".$admin_userid."','".$raw_assign_status."')";
    $stmt_status = $this->con->prepare("INSERT INTO `tbl_tdrawassign`(`inq_id`, `user_id`, `stage`) VALUES (?,?,?)");
    $stmt_status->bind_param("iis",$id,$admin_userid,$raw_assign_status);
    $Resp=$stmt_status->execute();
    $stmt_status->close();
}

//insert into tbl_tdbadleads
public function insert_badleads($badlead_reason,$remark,$id,$user_id,$badlead_type)
{
    $stmt_badlead = $this->con->prepare("INSERT INTO `tbl_tdbadleads`(`bad_lead_reason`, `bad_lead_reason_remark`, `inq_id`, `user_id`, `type`) VALUES (?,?,?,?,?)");
    $stmt_badlead->bind_param("sssis",$badlead_reason,$remark,$id,$user_id,$badlead_type);
    $Resp=$stmt_badlead->execute();
    $stmt_badlead->close();

    return $Resp;
}

public function get_badlead_reason($inq_id)
{
    $stmt_reason = $this->con->prepare("SELECT bad_lead_reason FROM `tbl_tdbadleads` WHERE inq_id=?");
    $stmt_reason->bind_param("i",$inq_id);
    $stmt_reason->execute();
    $reason_result = $stmt_reason->get_result()->fetch_assoc();
    $stmt_reason->close();
    $reason = $reason_result['bad_lead_reason'];
    return $reason;
}

// insert into pr_company_details and pr_company_plots
public function insert_pr_company_detail($source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$state,$city,$taluka,$area,$industrial_estate,$remark,$inq_submit,$PicFileName,$constitution,$status,$industrial_estate_id,$user_id,$id,$plot_status,$pr_company_plot_id,$pr_company_detail_id,$location,$existing_expansion_status,$update_location)
{
    if($pr_company_detail_id=="" || $pr_company_detail_id==null || $pr_company_detail_id=="null"){
          
      $stmt_pr_company_detail = $this->con->prepare("INSERT INTO `pr_company_details`(`source`, `source_name`, `contact_name`, `mobile_no`, `firm_name`, `gst_no`, `category`, `segment`, `premise`, `state`, `city`, `taluka`, `area`, `industrial_estate`, `remarks`, `inq_submit`, `image`, `constitution`, `status`, `industrial_estate_id`, `user_id`, `rawdata_id`,`existing_client_status`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
      $stmt_pr_company_detail->bind_param("sssssssssssssssssssiiis",$source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$state,$city,$taluka,$area,$industrial_estate,$remark,$inq_submit,$PicFileName,$constitution,$status,$industrial_estate_id,$user_id,$id,$existing_expansion_status);
      $Resp=$stmt_pr_company_detail->execute();
      $last_insert_company_id = mysqli_insert_id($this->con);
      $stmt_pr_company_detail->close();
      
      if($update_location==true){
        $stmt_pr_company_plot = $this->con->prepare("UPDATE `pr_company_plots` SET `plot_status`=?, `company_id`=?, `user_id`=?, `location`=? WHERE `pid`=?");
        $stmt_pr_company_plot->bind_param("siisi",$plot_status,$last_insert_company_id,$user_id,$location,$pr_company_plot_id);
      }
      else{
        $stmt_pr_company_plot = $this->con->prepare("UPDATE `pr_company_plots` SET `plot_status`=?, `company_id`=?, `user_id`=? WHERE `pid`=?");
        $stmt_pr_company_plot->bind_param("siii",$plot_status,$last_insert_company_id,$user_id,$pr_company_plot_id);
      }
      $Resp=$stmt_pr_company_plot->execute();
      $stmt_pr_company_plot->close();
    }
    else{

      $stmt_pr_company_detail = $this->con->prepare("UPDATE `pr_company_details` SET `source`=?, `source_name`=?, `contact_name`=?, `mobile_no`=?, `firm_name`=?, `gst_no`=?, `category`=?, `segment`=?, `premise`=?, `remarks`=?, `inq_submit`=?, `image`=?, `constitution`=?, `status`=?, `user_id`=?, `rawdata_id`=?, `existing_client_status`=? WHERE `cid`=?");
      $stmt_pr_company_detail->bind_param("ssssssssssssssiisi",$source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$remark,$inq_submit,$PicFileName,$constitution,$status,$user_id,$id,$existing_expansion_status,$pr_company_detail_id);
      $Resp=$stmt_pr_company_detail->execute();
      $stmt_pr_company_detail->close();

      if($update_location==true){
        $stmt_pr_company_plot = $this->con->prepare("UPDATE `pr_company_plots` SET `plot_status`=?, `company_id`=?, `user_id`=?, `location`=? WHERE `pid`=?");
        $stmt_pr_company_plot->bind_param("siisi",$plot_status,$pr_company_detail_id,$user_id,$location,$pr_company_plot_id);
      }
      else{
        $stmt_pr_company_plot = $this->con->prepare("UPDATE `pr_company_plots` SET `plot_status`=?, `company_id`=?, `user_id`=? WHERE `pid`=?");
        $stmt_pr_company_plot->bind_param("siii",$plot_status,$pr_company_detail_id,$user_id,$pr_company_plot_id);
      }
      $Resp=$stmt_pr_company_plot->execute();
      $stmt_pr_company_plot->close();
    }
    return $Resp;
}

// check additional plot number
public function check_additional_plot($additional_plot,$road_no,$estate_id)
{
    if($road_no!=""){
        $stmt_list = $this->con->prepare("SELECT * FROM `pr_company_plots` WHERE plot_no=? and road_no=? and industrial_estate_id=?");
        $stmt_list->bind_param("ssi",$additional_plot,$road_no,$estate_id);
    }
    else{
        $stmt_list = $this->con->prepare("SELECT * FROM `pr_company_plots` WHERE plot_no=? and industrial_estate_id=?");
        $stmt_list->bind_param("si",$additional_plot,$estate_id);
    }
        
    $stmt_list->execute();
    $plot_res = $stmt_list->get_result();
    $stmt_list->close();

    return $plot_res;
}

// get floor for add floor modal
public function get_floor_floormodal($plot_no,$road_no,$estate_id,$plotting_pattern)
{
    if($plotting_pattern=='Series'){
        $stmt = $this->con->prepare("SELECT all_numbers.floor FROM ( SELECT 0 AS floor UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 ) AS all_numbers LEFT JOIN ( SELECT DISTINCT floor FROM pr_company_plots WHERE industrial_estate_id='".$estate_id."' AND plot_no='".$plot_no."' ) AS plots ON all_numbers.floor = plots.floor WHERE plots.floor IS NULL");
    }
    else if($plotting_pattern=='Road'){
        $stmt = $this->con->prepare("SELECT all_numbers.floor FROM ( SELECT 0 AS floor UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 ) AS all_numbers LEFT JOIN ( SELECT DISTINCT floor FROM pr_company_plots WHERE industrial_estate_id='".$estate_id."' AND plot_no='".$plot_no."' and road_no='".$road_no."' ) AS plots ON all_numbers.floor = plots.floor WHERE plots.floor IS NULL");
    }

    $stmt->execute();
    $data = $stmt->get_result();
    $stmt->close();

    return $data;
}

// get plot for add plot modal
public function get_plot_plotmodal($old_road_no,$old_plot_no,$road_no,$plotting_pattern,$estate_id)
{
    // to all plots - plot selected
    if($plotting_pattern=='Series'){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(plot_no) FROM `pr_company_plots` WHERE industrial_estate_id='".$estate_id."' and plot_no!='".$old_plot_no."' order by abs(plot_no)");
    }
    else if($plotting_pattern=='Road'){
        $stmt_plot = $this->con->prepare("SELECT DISTINCT(plot_no) FROM `pr_company_plots` WHERE industrial_estate_id='".$estate_id."' AND road_no='".$road_no."' AND (road_no!='".$old_road_no."' OR plot_no!='".$old_plot_no."') ORDER BY ABS(plot_no)");
    }
    
    $stmt_plot->execute();
    $plot_res = $stmt_plot->get_result();
    $stmt_plot->close();        
    
    return $plot_res;
}

// get floor for add plot modal
public function get_floor_plotmodal($plot_no,$road_no,$plotting_pattern,$estate_id)
{
    // to get floors whose company details is blank + other floors left
    if($plotting_pattern=='Series'){
        $stmt_floor = $this->con->prepare("SELECT all_numbers.number as floor FROM ( SELECT 0 AS number UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 ) AS all_numbers LEFT JOIN ( SELECT floor FROM pr_company_plots WHERE industrial_estate_id='".$estate_id."' AND plot_no='".$plot_no."' and company_id is NOT null ) AS existing_numbers ON all_numbers.number = existing_numbers.floor WHERE existing_numbers.floor IS NULL order by abs(number)");
    }
    else if($plotting_pattern=='Road'){
        $stmt_floor = $this->con->prepare("SELECT all_numbers.number as floor FROM ( SELECT 0 AS number UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 ) AS all_numbers LEFT JOIN ( SELECT floor FROM pr_company_plots WHERE industrial_estate_id='".$estate_id."' AND plot_no='".$plot_no."' and road_no='".$road_no."' and company_id is NOT null ) AS existing_numbers ON all_numbers.number = existing_numbers.floor WHERE existing_numbers.floor IS NULL order by abs(number)");
    }
    
    $stmt_floor->execute();
    $floor_res = $stmt_floor->get_result();
    $stmt_floor->close();        
    
    return $floor_res;
}


public function industrial_estate_company_list($user_id)
{
    $result=$this->assigned_estates_company($user_id);

    if(mysqli_num_rows($result)>0){
        return $result;
    }
    else{
        return 0;
    }
}

public function roadno_for_roadwise($estate_id)
{
    $res_road=$this->get_road_no($estate_id);
    if(mysqli_num_rows($res_road)>0){
        return $res_road;
    }
}

public function plotno_for_roadwise($estate_id,$road_no)
{
    $plot_array = array();
    $result_estate=$this->get_ind_estate($estate_id);
    $res_plot=$this->get_road_plot_old($result_estate['taluka'],$result_estate['industrial_estate'],$result_estate['area_id']);

    if(mysqli_num_rows($res_plot)>0)
    {
        while($plot = mysqli_fetch_array($res_plot))
        {
            $raw_data=json_decode($plot["raw_data"]);
            $post_fields=$raw_data->post_fields;
            if(isset($raw_data->plot_details)){
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
        }
        
        sort($plot_array);
        return $plot_array;
    }
}

public function plotno_for_serieswise($estate_id)
{
    $result_estate=$this->get_ind_estate($estate_id);
    $res_plot=$this->get_plot_no_old($result_estate['taluka'],$result_estate['industrial_estate'],$result_estate['area_id']);
    if(mysqli_num_rows($res_plot)>0)
    {
        while($plot = mysqli_fetch_array($res_plot))
        {
            $raw_data=json_decode($plot["raw_data"]);
            $post_fields=$raw_data->post_fields;
            if(isset($raw_data->plot_details)){
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
        }

        sort($plot_array);
        return $plot_array;
    }
}

public function floorno_list($estate_id,$road_no,$plot_no)
{
    $result_estate=$this->get_ind_estate_data($estate_id);
    $res_plot=$this->get_plot_floor_old($result_estate['taluka'],$result_estate['industrial_estate'],$result_estate['area_id'],$plot_no,$road_no);

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
        return $floor_array;
    }
}

// get get_filter list
public function get_filter($estate_id,$emp_id)
{
    $stmt_filter = $this->con->prepare("SELECT assign_estate_status FROM `pr_emp_estate` where industrial_estate_id=? and employee_id=?");
    $stmt_filter->bind_param("ii",$estate_id,$emp_id);
    $stmt_filter->execute();
    $stmt_filter_result = $stmt_filter->get_result();
    $stmt_filter->close();
    return $stmt_filter_result;
}

// get lead company list
public function lead_company_list($userid)
{
    $stmt_company_list = $this->con->prepare("SELECT r1.inq_id, json_unquote(c1.raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(c1.raw_data->'$.post_fields.Contact_Name') as contact_name, json_unquote(c1.raw_data->'$.post_fields.Mobile_No') as contact_no, json_unquote(c1.raw_data->'$.post_fields.Area') as area, ifnull((SELECT CASE WHEN DATE(reminder_dt)=CURDATE() THEN 'yes' ELSE 'no' END status FROM `tbl_tdreminder` where inq_id=r1.inq_id order by id desc limit 1),'no') as status from (select MAX(t2.id) as r_id from tbl_tdrawassign t1, tbl_tdrawassign t2 where t1.id=t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdrawassign r1, tbl_tdrawdata c1 where tbl1.r_id=r1.id and r1.inq_id=c1.id and r1.stage='lead' and r1.user_id=?");
    $stmt_company_list->bind_param("i",$userid);
    $stmt_company_list->execute();
    $company_result = $stmt_company_list->get_result();
    $stmt_company_list->close();
    return $company_result;
}

// get follow ups list for company
public function followups_list($inq_id)
{
    $stmt_followup_list = $this->con->prepare("SELECT f1.id, u1.name, f1.followup_text, f1.followup_source as source, f1.followup_date, date_format(f1.tdfollowup_ts,'%h:%i %p') as followup_time FROM tbl_tdfollowup f1, tbl_users u1 WHERE f1.user_id=u1.id and inq_id=? order by f1.tdfollowup_ts DESC;");
    $stmt_followup_list->bind_param("i",$inq_id);
    $stmt_followup_list->execute();
    $followup_result = $stmt_followup_list->get_result();
    $stmt_followup_list->close();
    return $followup_result;
}

// insert into tbl_tdreminder
public function insert_reminder($inq_id,$user_id,$reminder_dt,$reminder_text,$reminder_summary,$reminder_source)
{   
    $stmt_status = $this->con->prepare("INSERT INTO `tbl_tdreminder`(`inq_id`, `user_id`, `reminder_dt`, `reminder_text`, `reminder_summary`, `reminder_source`) VALUES (?,?,?,?,?,?)");
    $stmt_status->bind_param("iissss",$inq_id,$user_id,$reminder_dt,$reminder_text,$reminder_summary,$reminder_source);
    $Resp=$stmt_status->execute();
    $stmt_status->close();
}

// update tbl_tdreminder
public function update_reminder($reminder_status,$done_date,$id)
{   
    $stmt_status = $this->con->prepare("UPDATE `tbl_tdreminder` set `reminder_status`=?, `followup_done_date`=? WHERE id=?");
    $stmt_status->bind_param("ssi",$reminder_status,$done_date,$id);
    $Resp=$stmt_status->execute();
    $stmt_status->close();

    return $Resp;
}

// insert into tbl_tdrawdata_cdates
public function insert_tdrawdata_cdates($inq_id,$user_id,$completion_date)
{
    $stmt = $this->con->prepare("SELECT * FROM `tbl_tdrawdata_cdates` where inq_id=? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i",$inq_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $insert_flag = false;

    if(mysqli_num_rows($result)>0){
        $date_res = $result->fetch_assoc();
        if($date_res["cdate"]!=$completion_date){
            $insert_flag=true;
        }
    }
    else{
        $insert_flag=true;
    }

    if($insert_flag==true){
        $stmt_completion_dt = $this->con->prepare("INSERT INTO `tbl_tdrawdata_cdates`(`inq_id`, `user_id`, `cdate`) VALUES (?,?,?)");
        $stmt_completion_dt->bind_param("iis",$inq_id,$user_id,$completion_date);
        $Resp=$stmt_completion_dt->execute();
        $stmt_completion_dt->close();
    }
}

// get department list
public function get_department_list()
{
    $stmt_department = $this->con->prepare("SELECT * FROM `tbl_department_master`");
    $stmt_department->execute();
    $department_result = $stmt_department->get_result();
    $stmt_department->close();
    return $department_result;
}

// get department designation list
public function get_department_designation($designation_id)
{
    $stmt_designation = $this->con->prepare("SELECT id, designation FROM `tbl_department_designation` WHERE dept_id=?");
    $stmt_designation->bind_param("i",$designation_id);
    $stmt_designation->execute();
    $designation_result = $stmt_designation->get_result();
    $stmt_designation->close();
    return $designation_result;
}

// get department designation list
public function get_department_user($department_id,$designation_id)
{
    $stmt_user = $this->con->prepare("SELECT id, name FROM `tbl_users` WHERE department=? and designation=?");
    $stmt_user->bind_param("ii",$department_id,$designation_id);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result();
    $stmt_user->close();
    return $user_result;
}

// get assign history of lead
public function get_assign_history($inq_id)
{
    $stmt_history = $this->con->prepare("SELECT r1.inq_id, CONCAT(u1.name,'(',r1.stage,') ', r1.ts) as assign_data FROM `tbl_tdrawassign` r1, `tbl_users` u1 WHERE r1.user_id = u1.id AND r1.inq_id=?");
    $stmt_history->bind_param("i",$inq_id);
    $stmt_history->execute();
    $history_result = $stmt_history->get_result();
    $stmt_history->close();
    return $history_result;
}

// get remaining lead company list
public function remaining_lead_company_list($userid)
{
    $stmt_company_list = $this->con->prepare("SELECT r1.inq_id, json_unquote(c1.raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(c1.raw_data->'$.post_fields.Contact_Name') as contact_name, json_unquote(c1.raw_data->'$.post_fields.Mobile_No') as contact_no, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN 'Subsidy (Loan - Sactioned)' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN 'Loan - Want to Apply' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN 'Subsidy (Loan - Under Process)' ELSE 'Yet to decide' END AS vertical, us1.name AS current_user_name, (SELECT u1.name from tbl_tdrawassign ra1, tbl_users u1 WHERE ra1.user_id=u1.id and user_id!='".$userid."' and ra1.inq_id=r1.inq_id order by ra1.id desc LIMIT 1) as forwarded_by, date_format(c1.raw_data_ts,'%d-%m-%Y') as received_on, COALESCE((SELECT date_format(cdate,'%d-%m-%Y') FROM tbl_tdrawdata_cdates WHERE inq_id = r1.inq_id ORDER BY id DESC LIMIT 1), '01-01-1970') as completion_date, json_unquote(c1.raw_data->'$.post_fields.Area') as area, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.TL_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount') + json_unquote(c1.raw_data->'$.post_fields.CC_Loan_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount_In_Process'), ''), 0) ELSE 0 END AS loan_amount FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdrawassign t1, tbl_tdrawassign t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdrawassign r1, tbl_tdrawdata c1,
        tbl_users us1 WHERE tbl1.r_id = r1.id AND r1.inq_id = c1.id AND r1.user_id=us1.id AND r1.stage = 'lead' AND r1.user_id = '".$userid."' AND r1.inq_id IN (SELECT r1.inq_id FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdfollowup t1, tbl_tdfollowup t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdfollowup r1 WHERE tbl1.r_id = r1.id AND r1.user_id='".$userid."') AND r1.inq_id NOT IN (SELECT re1.inq_id FROM tbl_tdreminder re1 WHERE re1.user_id='".$userid."' and re1.reminder_status='pending' and followup_type='application-followup' order by re1.reminder_dt);");
    $stmt_company_list->execute();
    $company_result = $stmt_company_list->get_result();
    $stmt_company_list->close();
    return $company_result;
}

// get new section company list
public function new_section_company_list($userid)
{
    $stmt_company_list = $this->con->prepare("SELECT r1.inq_id, json_unquote(c1.raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(c1.raw_data->'$.post_fields.Contact_Name') as contact_name, json_unquote(c1.raw_data->'$.post_fields.Mobile_No') as contact_no, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN 'Subsidy (Loan - Sactioned)' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN 'Loan - Want to Apply' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN 'Subsidy (Loan - Under Process)' ELSE 'Yet to decide' END AS vertical, us1.name AS current_user_name, (SELECT u1.name from tbl_tdrawassign ra1, tbl_users u1 WHERE ra1.user_id=u1.id and user_id!='".$userid."' and ra1.inq_id=r1.inq_id order by ra1.id desc LIMIT 1) as forwarded_by, date_format(c1.raw_data_ts,'%d-%m-%Y') as received_on, COALESCE((SELECT date_format(cdate,'%d-%m-%Y') FROM tbl_tdrawdata_cdates WHERE inq_id = r1.inq_id ORDER BY id DESC LIMIT 1), '01-01-1970') as completion_date, json_unquote(c1.raw_data->'$.post_fields.Area') as area, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.TL_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount') + json_unquote(c1.raw_data->'$.post_fields.CC_Loan_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount_In_Process'), ''), 0) ELSE 0 END AS loan_amount FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdrawassign t1, tbl_tdrawassign t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdrawassign r1, tbl_tdrawdata c1, tbl_users us1 WHERE tbl1.r_id = r1.id AND r1.inq_id = c1.id AND r1.user_id=us1.id AND r1.stage = 'lead' AND r1.user_id = '".$userid."' AND r1.inq_id NOT IN (SELECT r1.inq_id FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdfollowup t1, tbl_tdfollowup t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdfollowup r1 WHERE tbl1.r_id = r1.id AND r1.user_id='".$userid."')");
    $stmt_company_list->execute();
    $company_result = $stmt_company_list->get_result();
    $stmt_company_list->close();
    return $company_result;
}

// get today's section company list
public function today_section_company_list($userid)
{
    $stmt_company_list = $this->con->prepare("SELECT c1.id as inq_id, re1.id as mark_id, re1.reminder_summary as follow_up, re1.reminder_source, date_format(re1.reminder_dt, '%d-%m-%Y %h:%i %p') as reminder_dt, CASE WHEN DATEDIFF(re1.reminder_dt, CURDATE()) < 0 THEN CONCAT('You are ', DATEDIFF(CURDATE(), re1.reminder_dt), ' days(s) late.') ELSE NULL END AS date_difference, json_unquote(c1.raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(c1.raw_data->'$.post_fields.Contact_Name') as contact_name, json_unquote(c1.raw_data->'$.post_fields.Mobile_No') as contact_no, json_unquote(c1.raw_data->'$.post_fields.Area') as area, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN 'Subsidy (Loan - Sactioned)' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN 'Loan - Want to Apply' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN 'Subsidy (Loan - Under Process)' ELSE 'Yet to decide' END AS vertical, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.TL_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount') + json_unquote(c1.raw_data->'$.post_fields.CC_Loan_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount_In_Process'), ''), 0) ELSE 0 END AS loan_amount, COALESCE((SELECT date_format(cdate, '%d-%m-%Y') FROM tbl_tdrawdata_cdates WHERE inq_id = c1.id ORDER BY id DESC LIMIT 1), '01-01-1970') as completion_date, CASE WHEN r1.inq_id IS NOT NULL THEN 'true' ELSE 'false' END AS radiobutton_display FROM tbl_tdreminder re1 JOIN tbl_tdrawdata c1 ON re1.inq_id = c1.id LEFT JOIN (SELECT r1.inq_id FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdrawassign t1, tbl_tdrawassign t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1 JOIN tbl_tdrawassign r1 ON tbl1.r_id = r1.id AND r1.stage = 'lead' AND r1.user_id = '".$userid."') r1 ON re1.inq_id = r1.inq_id WHERE re1.user_id = '".$userid."' AND re1.reminder_status = 'pending' AND date_format(re1.reminder_dt, '%Y-%m-%d') <= CURDATE() AND re1.followup_type = 'application-followup' ORDER BY re1.reminder_dt");
    $stmt_company_list->execute();
    $company_result = $stmt_company_list->get_result();
    $stmt_company_list->close();
    return $company_result;
}

// get tomorrow's section company list
public function tomorrow_section_company_list($userid)
{
    $stmt_company_list = $this->con->prepare("SELECT c1.id as inq_id, re1.id as mark_id, re1.reminder_summary as follow_up, re1.reminder_source, date_format(re1.reminder_dt,'%d-%m-%Y %h:%i %p') as reminder_dt, json_unquote(c1.raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(c1.raw_data->'$.post_fields.Contact_Name') as contact_name, json_unquote(c1.raw_data->'$.post_fields.Mobile_No') as contact_no, json_unquote(c1.raw_data->'$.post_fields.Area') as area, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN 'Subsidy (Loan - Sactioned)' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN 'Loan - Want to Apply' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN 'Subsidy (Loan - Under Process)' ELSE 'Yet to decide' END AS vertical, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.TL_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount') + json_unquote(c1.raw_data->'$.post_fields.CC_Loan_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount_In_Process'), ''), 0) ELSE 0 END AS loan_amount, COALESCE((SELECT date_format(cdate,'%d-%m-%Y') FROM tbl_tdrawdata_cdates WHERE inq_id = c1.id ORDER BY id DESC LIMIT 1), '01-01-1970') as completion_date, CASE WHEN r1.inq_id IS NOT NULL THEN 'true' ELSE 'false' END AS radiobutton_display FROM tbl_tdreminder re1 JOIN tbl_tdrawdata c1 ON re1.inq_id = c1.id LEFT JOIN (SELECT r1.inq_id FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdrawassign t1, tbl_tdrawassign t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1 JOIN tbl_tdrawassign r1 ON tbl1.r_id = r1.id AND r1.stage = 'lead' AND r1.user_id = '".$userid."') r1 ON re1.inq_id = r1.inq_id WHERE re1.user_id='".$userid."' and re1.reminder_status='pending' and date_format(re1.reminder_dt,'%Y-%m-%d')>CURDATE() and re1.inq_id=c1.id and followup_type='application-followup' order by re1.reminder_dt");
    $stmt_company_list->execute();
    $company_result = $stmt_company_list->get_result();
    $stmt_company_list->close();
    return $company_result;
}

// get overdue's section company list
public function overdue_section_company_list($userid)
{
    $stmt_company_list = $this->con->prepare("SELECT r1.inq_id, json_unquote(c1.raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(c1.raw_data->'$.post_fields.Contact_Name') as contact_name, json_unquote(c1.raw_data->'$.post_fields.Mobile_No') as contact_no, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN 'Subsidy (Loan - Sactioned)' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN 'Loan - Want to Apply' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN 'Subsidy (Loan - Under Process)' ELSE 'Yet to decide' END AS vertical, us1.name AS current_user_name, (SELECT u1.name from tbl_tdrawassign ra1, tbl_users u1 WHERE ra1.user_id=u1.id and user_id!=us1.id and ra1.inq_id=r1.inq_id order by ra1.id desc LIMIT 1) as forwarded_by, date_format(c1.raw_data_ts,'%d-%m-%Y') as received_on, COALESCE((SELECT date_format(cdate,'%d-%m-%Y') FROM tbl_tdrawdata_cdates WHERE inq_id = r1.inq_id ORDER BY id DESC LIMIT 1), '01-01-1970') as completion_date, json_unquote(c1.raw_data->'$.post_fields.Area') as area, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.TL_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount') + json_unquote(c1.raw_data->'$.post_fields.CC_Loan_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount_In_Process'), ''), 0) ELSE 0 END AS loan_amount, CASE WHEN us1.id='".$userid."' THEN 'true' ELSE 'false' END as assign, CASE WHEN us1.id='".$userid."' THEN 'true' ELSE 'false' END as radiobutton_display FROM (SELECT DISTINCT(r1.inq_id) AS inq_id FROM tbl_tdrawassign r1, tbl_tdrawdata_cdates cd1 WHERE r1.inq_id = cd1.inq_id AND cd1.cdate < CURDATE() AND r1.user_id='".$userid."') ctbl1, (SELECT MAX(t2.id) AS r_id, t2.inq_id FROM tbl_tdrawassign t1 JOIN tbl_tdrawassign t2 ON t1.id = t2.id GROUP BY t2.inq_id) ltbl1, tbl_tdrawassign r1, tbl_tdrawdata c1, tbl_users us1 WHERE r1.id=ltbl1.r_id AND ctbl1.inq_id=r1.inq_id AND r1.inq_id = c1.id AND r1.user_id=us1.id AND r1.stage='lead'");
    $stmt_company_list->execute();
    $company_result = $stmt_company_list->get_result();
    $stmt_company_list->close();
    return $company_result;
}

// get involved's section company list
public function involved_section_company_list($userid)
{
    $stmt_company_list = $this->con->prepare("SELECT r1.inq_id, json_unquote(c1.raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(c1.raw_data->'$.post_fields.Contact_Name') as contact_name, json_unquote(c1.raw_data->'$.post_fields.Mobile_No') as contact_no, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN 'Subsidy (Loan - Sactioned)' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN 'Loan - Want to Apply' WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN 'Subsidy (Loan - Under Process)' ELSE 'Yet to decide' END AS vertical, us1.name AS current_user_name, (SELECT u1.name from tbl_tdrawassign ra1, tbl_users u1 WHERE ra1.user_id=u1.id and user_id!=us1.id and ra1.inq_id=r1.inq_id order by ra1.id desc LIMIT 1) as forwarded_by, date_format(c1.raw_data_ts,'%d-%m-%Y') as received_on, COALESCE((SELECT date_format(cdate,'%d-%m-%Y') FROM tbl_tdrawdata_cdates WHERE inq_id = r1.inq_id ORDER BY id DESC LIMIT 1), '01-01-1970') as completion_date, json_unquote(c1.raw_data->'$.post_fields.Area') as area, CASE WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Sactioned Loan' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.TL_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Want to Apply?' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount') + json_unquote(c1.raw_data->'$.post_fields.CC_Loan_Amount'), ''), 0) WHEN json_unquote(c1.raw_data->'$.post_fields.loan_applied') = 'Loan Under Process' THEN COALESCE(NULLIF(json_unquote(c1.raw_data->'$.post_fields.Term_Loan_Amount_In_Process'), ''), 0) ELSE 0 END AS loan_amount FROM (SELECT DISTINCT(r1.inq_id) AS inq_id FROM tbl_tdrawassign r1 WHERE r1.user_id='".$userid."') ctbl1, (SELECT MAX(t2.id) AS r_id, t2.inq_id FROM tbl_tdrawassign t1 JOIN tbl_tdrawassign t2 ON t1.id = t2.id GROUP BY t2.inq_id) ltbl1, tbl_tdrawassign r1, tbl_tdrawdata c1, tbl_users us1 WHERE r1.id=ltbl1.r_id 
        AND ctbl1.inq_id=r1.inq_id AND r1.inq_id = c1.id AND r1.user_id=us1.id AND r1.stage='lead' AND r1.user_id!='".$userid."'");
    $stmt_company_list->execute();
    $company_result = $stmt_company_list->get_result();
    $stmt_company_list->close();
    return $company_result;
}

// get today's lead count
public function get_lead_count($userid)
{
    $stmt_count = $this->con->prepare("SELECT count(*) as today_count FROM tbl_tdreminder re1, tbl_tdrawdata c1 WHERE re1.user_id=? and re1.reminder_status='pending' and date_format(re1.reminder_dt,'%Y-%m-%d')<=CURDATE() and re1.inq_id=c1.id and followup_type='application-followup' order by re1.reminder_dt");
    $stmt_count->bind_param("i",$userid);
    $stmt_count->execute();
    $count_result = $stmt_count->get_result()->fetch_assoc();
    $stmt_count->close();
    $todays_count = $count_result["today_count"];
    return $todays_count;
}

public function get_all_section_count($userid)
{
    $stmt_count = $this->con->prepare("SELECT (SELECT COUNT(*) as remaining_count FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdrawassign t1, tbl_tdrawassign t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdrawassign r1 WHERE tbl1.r_id = r1.id AND r1.stage = 'lead' AND r1.user_id = '".$userid."' AND r1.inq_id IN (SELECT r1.inq_id FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdfollowup t1, tbl_tdfollowup t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdfollowup r1 WHERE tbl1.r_id = r1.id AND r1.user_id='".$userid."') AND r1.inq_id NOT IN (SELECT re1.inq_id FROM tbl_tdreminder re1 WHERE re1.user_id='".$userid."' and re1.reminder_status='pending' and followup_type='application-followup' order by re1.reminder_dt)) as remaining_count, (SELECT COUNT(*) as new_count FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdrawassign t1, tbl_tdrawassign t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdrawassign r1 WHERE tbl1.r_id = r1.id AND r1.stage = 'lead' AND r1.user_id = '".$userid."' AND r1.inq_id NOT IN (SELECT r1.inq_id FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdfollowup t1, tbl_tdfollowup t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdfollowup r1 WHERE tbl1.r_id = r1.id AND r1.user_id='".$userid."')) as new_count, (SELECT COUNT(*) as today_count FROM tbl_tdreminder re1 LEFT JOIN (SELECT r1.inq_id FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdrawassign t1, tbl_tdrawassign t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1 JOIN tbl_tdrawassign r1 ON tbl1.r_id = r1.id AND r1.stage = 'lead' AND r1.user_id = '".$userid."') r1 ON re1.inq_id = r1.inq_id WHERE re1.user_id = '".$userid."' AND re1.reminder_status = 'pending' AND date_format(re1.reminder_dt, '%Y-%m-%d') <= CURDATE() AND re1.followup_type = 'application-followup' ORDER BY re1.reminder_dt) as today_count, (SELECT COUNT(*) as tomorrow_count FROM tbl_tdreminder re1 LEFT JOIN (SELECT r1.inq_id FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdrawassign t1, tbl_tdrawassign t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1 JOIN tbl_tdrawassign r1 ON tbl1.r_id = r1.id AND r1.stage = 'lead' AND r1.user_id = '".$userid."') r1 ON re1.inq_id = r1.inq_id WHERE re1.user_id='".$userid."' and re1.reminder_status='pending' and date_format(re1.reminder_dt,'%Y-%m-%d')>CURDATE() and followup_type='application-followup' order by re1.reminder_dt) as tomorrow_count, (select count(*) AS overdue_count FROM (SELECT DISTINCT(r1.inq_id) AS inq_id FROM tbl_tdrawassign r1, tbl_tdrawdata_cdates cd1 WHERE r1.inq_id = cd1.inq_id AND cd1.cdate < CURDATE() AND r1.user_id='".$userid."') c1, (SELECT MAX(t2.id) AS r_id, t2.inq_id FROM tbl_tdrawassign t1 JOIN tbl_tdrawassign t2 ON t1.id = t2.id GROUP BY t2.inq_id) l1, tbl_tdrawassign r1 WHERE r1.id=l1.r_id AND c1.inq_id=r1.inq_id AND r1.stage='lead') as overdue_count, (SELECT COUNT(*) as involved_count FROM (SELECT DISTINCT(r1.inq_id) AS inq_id FROM tbl_tdrawassign r1 WHERE r1.user_id='".$userid."') c1, (SELECT MAX(t2.id) AS r_id, t2.inq_id FROM tbl_tdrawassign t1 JOIN tbl_tdrawassign t2 ON t1.id = t2.id GROUP BY t2.inq_id) l1, tbl_tdrawassign r1 WHERE r1.id=l1.r_id AND c1.inq_id=r1.inq_id AND r1.stage='lead' AND r1.user_id!='".$userid."') as involved_count");
    $stmt_count->execute();
    $count_result = $stmt_count->get_result();
    $stmt_count->close();
    return $count_result;
}


/*
// get state list
public function get_state_list()
{
    $stmt_state = $this->con->prepare("SELECT DISTINCT(state) from `all_taluka`");
    $stmt_state->execute();
    $state_result = $stmt_state->get_result();
    $stmt_state->close();
    return $state_result;
}

// get city list
public function get_city_list($state)
{
    $stmt_city = $this->con->prepare("SELECT DISTINCT(district) from `all_taluka` where state=?");
    $stmt_city->bind_param("s",$state);
    $stmt_city->execute();
    $city_result = $stmt_city->get_result();
    $stmt_city->close();
    return $city_result;
}

// get designation list
public function get_designation_list()
{
    $stmt_designation = $this->con->prepare("SELECT designation_name FROM `designation_master`");
    $stmt_designation->execute();
    $designation_result = $stmt_designation->get_result();
    $stmt_designation->close();
    return $designation_result;
}

// get vertical list
public function get_vertical_list()
{
    $stmt_vertical = $this->con->prepare("SELECT id,service_type FROM `tbl_service_type` WHERE status='active'");
    $stmt_vertical->execute();
    $vertical_result = $stmt_vertical->get_result();
    $stmt_vertical->close();
    return $vertical_result;
}

// get service names list
public function get_service_name_list($vertical)
{
    $stmt_service = $this->con->prepare("SELECT id,service FROM `tbl_service_master` WHERE service_type=? and status='active'");
    $stmt_service->bind_param("i",$vertical);
    $stmt_service->execute();
    $service_result = $stmt_service->get_result();
    $stmt_service->close();
    return $service_result;
}

*/
//7239
// supplier details (machinery) => SELECT json_unquote(raw_data->'$.post_fields.Firm_Name') as supplier_details FROM `tbl_tdassodata` where lower(raw_data->'$.post_fields.Segment_Name') like '%machine supplier%' order by id desc;

/*public function pr_estate_roadplot($ind_estate_id,$road_number,$additional_plotno,$user_id)
{
    $stmt_plot = $this->con->prepare("INSERT INTO `pr_estate_roadplot`(`industrial_estate_id`, `road_no`, `plot_start_no`, `user_id`) VALUES (?,?,?,?)");
    $stmt_plot->bind_param("issi",$ind_estate_id,$road_number,$additional_plotno,$user_id);
    $Resp=$stmt_plot->execute();
    $stmt_plot->close();

    if ($Resp) {
        return 1;
    } else {
        return 0;
    }
}*/
}
?>