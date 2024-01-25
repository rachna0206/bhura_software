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


public function assignorLogin($userid, $password)
{           
    $qr = $this->con->prepare("select * from tbl_users where email=?");
    $qr->bind_param("s",$userid);
    $qr->execute();
    $result = $qr->get_result();
    $num=mysqli_num_rows($result);
    $row=mysqli_fetch_array($result);
    $hashed_pass=$row["password"];
    $qr->close();

    $verify = password_verify($password, $hashed_pass);

    if ($verify) {
        return $num>0;
    } 
}

public function assignor_data($userid)
{        
    $qr = $this->con->prepare("select id,name from tbl_users where email=?");
    $qr->bind_param("s",$userid);
    $qr->execute();
    $result = $qr->get_result()->fetch_assoc();
    $qr->close();
    return $result;
}


public function insert_device_type($device_type, $token, $cid)
    {
       
        $stmt = $this->con->prepare("INSERT INTO `tbl_user_devices`(`uid`, `token`, `type`) VALUES (?,?,?)");
        $stmt->bind_param("iss", $cid, $token, $device_type);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return 1;
        } else {
            return 0;
        }
    }

    public function logout($c_id, $token, $type)
    {
        
        $stmt = $this->con->prepare("DELETE FROM `tbl_user_devices` WHERE `uid`=? and `token`=? and `type`=?");

        $stmt->bind_param("iss", $c_id, $token, $type);
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

public function company_plot_insert($plot_no,$floor,$road_no,$plot_id,$industrial_estate_id,$user_id)
{
    $stmt_company_plot = $this->con->prepare("INSERT INTO `pr_company_plots`(`plot_no`, `floor`, `road_no`, `plot_id`, `industrial_estate_id`, `user_id`) VALUES (?,?,?,?,?,?)");
    $stmt_company_plot->bind_param("ssssii",$plot_no,$floor,$road_no,$plot_id,$industrial_estate_id,$user_id);
    $Resp=$stmt_company_plot->execute();
    $stmt_company_plot->close();
}

// add industrial estate
public function add_industrial_estate($state,$city,$taluka,$area,$industrial_estate,$description,$plotting_pattern,$location,$userid,$verify_status)
{
 
    $stmt = $this->con->prepare("INSERT INTO `tbl_industrial_estate`(`state_id`, `city_id`, `taluka`, `area_id`, `industrial_estate`,`description`) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssssss",$state,$city,$taluka,$area,$industrial_estate,$description);
    $Resp=$stmt->execute();
    $stmt->close();
    $insert_id = mysqli_insert_id($this->con);

    

    if($Resp){

        $res_estate_details=$this->pr_add_industrialestate_details($insert_id,$plotting_pattern,$location,$userid,$verify_status);


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

// get source type list
public function get_source_type_list()
{
    $stmt_list = $this->con->prepare("SELECT * FROM `tbl_sourcetype_master`"); 
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();
    return $result;
}

// get source type list
public function get_associate_list()
{
    $stmt_list = $this->con->prepare("SELECT * FROM `asso_segment_master`"); 
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();
    return $result;
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
public function get_plot_no($taluka,$ind_estate)
{
   $stmt_plot = $this->con->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($taluka)."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($ind_estate)."%'");
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
public function get_plot_floor($taluka,$industrial_estate,$plot_no,$road_no)
{
    $stmt_floor = $this->con->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($taluka)."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($industrial_estate)."%' and raw_data->'$.plot_details[*].Plot_No' like '%".$plot_no."%' and raw_data->'$.plot_details[*].Road_No' like '%".$road_no."%'");
    $stmt_floor->execute();
    $floor_res = $stmt_floor->get_result();
    $stmt_floor->close();
    return $floor_res;
}

//get road plot 
public function get_road_plot($taluka,$industrial_estate)
{
    $stmt_floor = $this->con->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($taluka)."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($industrial_estate)."%'");
    $stmt_floor->execute();
    $floor_res = $stmt_floor->get_result();
    $stmt_floor->close();
    return $floor_res;
}
//get company details
public function get_company_details($taluka,$industrial_estate,$plot_no,$floor_no)
{
    $stmt_floor = $this->con->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($estate_res['taluka'])."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($estate_res['industrial_estate'])."%' and raw_data->'$.plot_details[*].Plot_No' like '%".$plot_no."%' and raw_data->'$.plot_details[*].Floor' like '%".$floor_no."%'");
    $stmt_floor->execute();
    $floor_res = $stmt_floor->get_result();
    $stmt_floor->close();
    return $floor_res;
}
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