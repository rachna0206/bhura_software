<?php
session_start();
date_default_timezone_set("Asia/Kolkata");
//error_reporting(E_ALL);
include("db_connect.php");
$obj=new DB_Connect();
if(isset($_REQUEST['action']))
{
	// unassigned_estate_plotting, add_industrial_estate_old
	if($_REQUEST['action']=="getAreaList")
	{	
		$html="";
		$estate_id=explode(",",$_REQUEST["ind_estate_id"]);

		for($j=0;$j<count($estate_id);$j++)
		{
			$stmt_elist = $obj->con1->prepare("SELECT area_id,industrial_estate FROM `tbl_industrial_estate` WHERE id=?");
			$stmt_elist->bind_param("i",$estate_id[$j]);
			$stmt_elist->execute();
			$estate_res = $stmt_elist->get_result();
			$stmt_elist->close();
			
			if(mysqli_num_rows($estate_res)>0){
				while($estate=mysqli_fetch_array($estate_res)){
					$html.='<div class="col-md-4"><label class="form-label" for="basic-default-fullname">'.$estate["industrial_estate"].' - '.$estate["area_id"].'</label></div>';
				}
			}
		}	
		echo $html;
	}	

	// company_entry
	if($_REQUEST['action']=="checkGST")
	{	
		$html="";
		$plot_no="";

		$gst_no=$_REQUEST["gst_no"];
		$id=$_REQUEST['id'];
		if($gst_no!=""){
			if($id!=""){
				$stmt_gst = $obj->con1->prepare("select * from tbl_tdrawdata where raw_data->'$.post_fields.GST_No'=? and id!=?");
				$stmt_gst->bind_param("si",$gst_no,$id);
			}
			else{	
				$stmt_gst = $obj->con1->prepare("select * from tbl_tdrawdata where raw_data->'$.post_fields.GST_No'=?");
				$stmt_gst->bind_param("s",$gst_no);
			}	
		}
		
		$stmt_gst->execute();
		$res = $stmt_gst->get_result();
		$stmt_gst->close();

		if(mysqli_num_rows($res)>0){
			$html=1;

			$data = mysqli_fetch_array($res);
			$row_data=json_decode($data["raw_data"]);
			$plot_details=$row_data->plot_details; 
			if($plot_details[0]->Floor=='0'){
				$plot_no = $plot_details[0]->Plot_No.' ( Ground Floor ) ';	
			}
			else{
				$plot_no = $plot_details[0]->Plot_No.' ( Floor No. - '.$plot_details[0]->Floor.' ) ';
			}
			
		}
		else{
			$html=0;
		}
		echo $html."@@@@@".$plot_no;
	}

	// assign_estate_plotting
	if($_REQUEST['action']=="assign_estate_modal")
	{
		$id = $_REQUEST["id"];
		$ind_estate_id = $_REQUEST["ind_estate_id"];
		$action_type = $_REQUEST["pg"];
		
		$html="";

		$stmt = $obj->con1->prepare("select a1.*, u1.name from assign_estate a1, tbl_users u1 where a1.employee_id=u1.id and a1.id=?");
		$stmt->bind_param("i",$id);
		$stmt->execute();
		$data = $stmt->get_result()->fetch_assoc();
		$stmt->close();

		$stmt_emp_list = $obj->con1->prepare("SELECT * FROM tbl_users where (LOWER(role)='assignor/verifier' or LOWER(role)='worker')");
		$stmt_emp_list->execute();
		$emp_result = $stmt_emp_list->get_result();
		$stmt_emp_list->close();

		$stmt_pr_estate = $obj->con1->prepare("SELECT id,assign_estate_status FROM pr_emp_estate where industrial_estate_id=?");
		$stmt_pr_estate->bind_param("i",$ind_estate_id);
		$stmt_pr_estate->execute();
		$estate_result = $stmt_pr_estate->get_result();
		$stmt_pr_estate->close();
		$assigned_filter=array();
		//$filter_array=array("visit_pending","open_plot","positive","negative","existing_client");
		while($row = mysqli_fetch_array($estate_result)){
			$assigned_filter[] = $row['assign_estate_status'];
		}
		

		$stmt_assigned_emp_list = $obj->con1->prepare("SELECT a1.employee_id from assign_estate a1 where action=? and a1.industrial_estate_id=? order by a1.id desc");
		$stmt_assigned_emp_list->bind_param("si",$action_type,$ind_estate_id);
		$stmt_assigned_emp_list->execute();
		$assigned_emp_result = $stmt_assigned_emp_list->get_result();
		$stmt_assigned_emp_list->close();
		$assigned_emp = array();
		while($row = mysqli_fetch_array($assigned_emp_result)){
			$assigned_emp[] = $row['employee_id'];
		}
		//echo "qs=".in_array("visit_pending",$assigned_filter);

		$html='<form  method="post"><div class="modal-body" ><div class="row">
		<input type="hidden" name="ttId" id="ttId" value="'.$data['id'].'">
		<input type="hidden" name="ind_estate_id" id="ind_estate_id" value="'.$ind_estate_id.'">

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname" id="state_label"></label>
		<input type="hidden" class="form-control" name="state_modal" id="state_modal" value="'.date("d-m-Y").'"/>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-company" id="city_label"></label>
		<input type="hidden" class="form-control" name="city_modal" id="city_modal"/>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-company" id="taluka_label"></label>
		<input type="hidden" class="form-control" name="taluka_modal" id="taluka_modal"/>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-company" id="area_label"></label>
		<input type="hidden" class="form-control" name="area_modal" id="area_modal"/>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-company" id="industrial_estate_label"></label>
		<input type="hidden" class="form-control" name="industrial_estate_modal" id="industrial_estate_modal"/>
		</div>

		<div class="mb-3" >
		<label class="form-label" for="basic-default-fullname">Employees</label>
		<select name="e[]" id="emp_list" class="form-control js-example-basic-multiple" multiple="multiple" required>';
		while($emp=mysqli_fetch_array($emp_result)){
			
			$html.='<option value="'.$emp["id"].'" '.(in_array($emp["id"],$assigned_emp)?"selected":"").'>'.$emp["name"].'</option>';
		}
		$html.='</select>
		</div>';

		if($action_type=='company_entry'){

		$html.='<div class="mb-3" >
		<label class="form-label" for="basic-default-fullname">Filter Selection</label>
		<select name="filter[]" id="filter_selection" class="form-control js-example-basic-multiple" multiple="multiple" required>
		<option value="visit_pending" '.(in_array("visit_pending",$assigned_filter)?"selected":"").'>Visit Pending</option>
		<option value="open_plot" '.(in_array("open_plot",$assigned_filter)?"selected":"").'>Open Plot</option>
		<option value="positive" '.(in_array("positive",$assigned_filter)?"selected":"").'>Positive</option>
		<option value="negative" '.(in_array("negative",$assigned_filter)?"selected":"").'>Negative</option>
		<option value="existing_client" '.(in_array("existing_client",$assigned_filter)?"selected":"").'>Existing Client</option>';

		$html.='</select>
		</div>';
	}


		$html.='<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Start Date</label>
		<input type="date" class="form-control" name="start_date_modal" id="start_date_modal" max="9999-12-31" value="'.$data['start_dt'].'" required />
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">End Date</label>
		<input type="date" class="form-control" name="end_date_modal" id="end_date_modal" max="9999-12-31" value="'.$data['end_dt'].'" required />
		</div>';

		if($action_type=='company_entry'){

			$stmt_sum = $obj->con1->prepare("SELECT count(plot_no) as total from pr_company_plots where floor='0' and industrial_estate_id=?");
			$stmt_sum->bind_param("i",$ind_estate_id);
			$stmt_sum->execute();
			$result_sum = $stmt_sum->get_result()->fetch_assoc();
			$stmt_sum->close();

			$stmt_list = $obj->con1->prepare("SELECT i1.state_id, i1.city_id, i1.taluka, i1.area_id, i1.industrial_estate, d1.plotting_pattern, d1.user_id, u1.name, ifnull(d1.status,'-') as status from pr_add_industrialestate_details d1, tbl_industrial_estate i1, tbl_users u1 where d1.industrial_estate_id=i1.id and u1.id=d1.user_id and i1.id=? group by industrial_estate_id");
			$stmt_list->bind_param("i",$ind_estate_id);
			$stmt_list->execute();
			$result = $stmt_list->get_result()->fetch_assoc();
			$stmt_list->close();

			$stmt_image = $obj->con1->prepare("SELECT image FROM `pr_estate_subimages` WHERE industrial_estate_id=?");
			$stmt_image->bind_param("i",$ind_estate_id);
			$stmt_image->execute();
			$image_result = $stmt_image->get_result();
			$stmt_image->close();

			if($result["plotting_pattern"]=='Road'){
				$stmt_plot_list = $obj->con1->prepare("SELECT b.road_no, b.plot, ifnull(a.additional_plot,'-') as additional_plot from (SELECT GROUP_CONCAT(plot_start_no) as additional_plot, road_no FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is null group by road_no) a right outer join (SELECT concat(plot_start_no,' To ',plot_end_no) as plot, road_no FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is not null) b on (a.road_no = b.road_no) order by abs(b.road_no)");
			}
			else if($result["plotting_pattern"]=='Series'){
				$stmt_plot_list = $obj->con1->prepare("SELECT b.plot, ifnull(a.additional_plot,'-') as additional_plot from (SELECT GROUP_CONCAT(plot_start_no) as additional_plot FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is null) a, (SELECT concat(plot_start_no,' To ',plot_end_no) as plot FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is not null) b");
			}

			$stmt_plot_list->bind_param("ii",$ind_estate_id,$ind_estate_id);
			$stmt_plot_list->execute();
			$plot_result = $stmt_plot_list->get_result();
			$stmt_plot_list->close();

			$html.='<div class="row">
			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Plotting Pattern</label>
			<input type="text" class="form-control" name="plotting_pattern" id="plotting_pattern" value="'.$result["plotting_pattern"].'" readonly/>
			</div>

			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Total Plots</label>
			<input type="text" class="form-control" name="total_plots" id="total_plots" value="'.$result_sum["total"].'" readonly/>
			</div>
			</div>

			<div class="row">
			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Status</label>
			<input type="text" class="form-control" name="status" id="status" value="'.$result["status"].'" readonly/>
			</div>
			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Added By</label>
			<input type="text" class="form-control" name="added_by" id="added_by" value="'.ucwords(strtolower($result["name"])).'" readonly/>
			</div>
			</div>

			<div class="mb-3">
			<div class="card">
			<div class="card-body">
			<div class="table-responsive text-nowrap">
			<table class="table table-bordered">
			<thead>';
			if($result["plotting_pattern"]=='Road'){
				$html.= '<tr>
				<th>Road No.</th>
				<th>Plots</th>
				<th>Additional Plots</th>
				</tr>';
			}
			else{ 
				$html.= '<tr>
				<th>Plots</th>
				<th>Additional Plots</th>
				</tr>'; 
			} 
			$html.='</thead>
			<tbody>';

			$old_roadno = "";
			$new_roadno = "";

			while($plot_list=mysqli_fetch_array($plot_result)){ 
				if($result["plotting_pattern"]=='Road'){
					$new_roadno = $plot_list["road_no"];
					$html.='<tr>
					<td>'.$plot_list["road_no"].'</td>
					<td>'.$plot_list["plot"].'</td>';
					if($old_roadno==$new_roadno){
						$html.='<td>-</td>	';
					}
					else{
						$html.='<td>'.$plot_list["additional_plot"].'</td>';	
					}

					$html.='</tr>';
					$old_roadno = $new_roadno;
				}
				else{
					$html.='<tr>
					<td>'.$plot_list["plot"].'</td>
					<td>'.$plot_list["additional_plot"].'</td>
					</tr>';
				}
			}
			$html.='</tbody>
			</table>
			</div>
			</div>
			</div>
			</div>

			<div class="mb-3">
			<label class="form-label" for="basic-default-fullname">Image</label>';
			while($image_list=mysqli_fetch_array($image_result)){  
				$html.='<img src="industrial_estate_image/'.$image_list["image"].'" name="img" id="img" width="100" height="100" style="display:block;"><br/>';
			}
			$html.='</div>';
		}


		$html.='<div class="modal-footer">
		<input type="submit" class="btn btn-primary" name="btn_modal_update" id="btn_modal_update" value="Save Changes">
		<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
		Close
		</button>
		</form>
		</div>';
		echo $html;
	}

	// add_industrial_estate
	if($_REQUEST['action']=="areaList_ade")
	{	
		$html="";
		$taluka=$_REQUEST['taluka'];
		$city=$_REQUEST['city'];
		$state_name=$_REQUEST['state_name'];
		
		$stmt = $obj->con1->prepare("select DISTINCT(village_name) from all_taluka where state=? and district=? and subdistrict=?");
		$stmt->bind_param("sss",$state_name,$city,$taluka);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();

		$html='<option value="">Select Area</option>';
		while($area=mysqli_fetch_array($res))
		{
			$html.='<option value="'.$area["village_name"].'">'.$area["village_name"].'</option>';
		}
		echo $html;
	}

	// add_industrial_estate
	if($_REQUEST['action']=="checkIndEstate")
	{	
		$html="";
		$ind_estate=$_REQUEST["ind_estate"];
		$area=$_REQUEST["area"];
		$taluka=$_REQUEST["taluka"];
		$city=$_REQUEST["city"];
		$state=$_REQUEST["state"];
		$id=$_REQUEST['id'];

		if($ind_estate!=""){
			$stmt_like = $obj->con1->prepare("select * from tbl_industrial_estate where industrial_estate=? and area_id=? and taluka=? and city_id=? and state_id=?");
			$stmt_like->bind_param("sssss",$ind_estate,$area,$taluka,$city,$state);
			$stmt_like->execute();
			$like_res = $stmt_like->get_result();
			$stmt_like->close();

			if(mysqli_num_rows($like_res)>0){
				$html=1;
				echo $html.'@@@@@no';
			}
			else{
				$stmt_soundex = $obj->con1->prepare("select GROUP_CONCAT(industrial_estate) as industrial_estate from tbl_industrial_estate where soundex(industrial_estate)=soundex(?) and area_id=? and taluka=? and city_id=? and state_id=? group by taluka");
				$stmt_soundex->bind_param("sssss",$ind_estate,$area,$taluka,$city,$state);
				$stmt_soundex->execute();
				$soundex_res = $stmt_soundex->get_result();
				$stmt_soundex->close();

				if(mysqli_num_rows($soundex_res)>0){
					$list="";

					while($estate_list=mysqli_fetch_array($soundex_res))
					{
						$list.=$estate_list['industrial_estate'];
					}
					$html=1;
					echo $html.'@@@@@'.$list;
				}
				else{
					$html=0;
					echo $html.'@@@@@no';
				}
			}
		}
	}

	// company_entry
	if($_REQUEST['action']=="get_floorno")
	{	
		$html="";
		$floor_dropdown="";
		$estate_id=$_REQUEST['estate_id'];
		$plot_no=$_REQUEST['plot_no'];
		$road_no=$_REQUEST['road_no'];
		$plotting_pattern=$_REQUEST['plotting_pattern'];
		
		// to get floors whose company details is blank + other floors left
		if($plotting_pattern=='Series'){
			$stmt_floor = $obj->con1->prepare("SELECT all_numbers.number FROM ( SELECT 0 AS number UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 ) AS all_numbers LEFT JOIN ( SELECT floor FROM pr_company_plots WHERE industrial_estate_id='".$estate_id."' AND plot_no='".$plot_no."' and company_id is NOT null ) AS existing_numbers ON all_numbers.number = existing_numbers.floor WHERE existing_numbers.floor IS NULL order by abs(number)");
		}
		else if($plotting_pattern=='Road'){
			$stmt_floor = $obj->con1->prepare("SELECT all_numbers.number FROM ( SELECT 0 AS number UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 ) AS all_numbers LEFT JOIN ( SELECT floor FROM pr_company_plots WHERE industrial_estate_id='".$estate_id."' AND plot_no='".$plot_no."' and road_no='".$road_no."' and company_id is NOT null ) AS existing_numbers ON all_numbers.number = existing_numbers.floor WHERE existing_numbers.floor IS NULL order by abs(number)");
		}
		
		$stmt_floor->execute();
		$floor_res = $stmt_floor->get_result();
		$stmt_floor->close();

		if(mysqli_num_rows($floor_res)>0){
			while($floor=mysqli_fetch_array($floor_res)){
				if($floor["number"]=='0'){
					$floor_dropdown.='<option value="'.$floor["number"].'">Ground Floor</option>';
				}
				else{
					$floor_dropdown.='<option value="'.$floor["number"].'">'.$floor["number"].'</option>';
				}
			}	
		}
		else{
			$floor_dropdown.='<option value="">Select Floor No.</option>';
		}

		echo $floor_dropdown;
	}

	// unassigned_estate_plotting, add_industrial_estate_old
	if($_REQUEST['action']=="estate_withnoplotting")
	{	
		$html="";
		$taluka=$_REQUEST['taluka'];
		$city=$_REQUEST['city'];
		$state_name=$_REQUEST['state_name'];
		$user_id=$_REQUEST['user_id'];
		
		$stmt_admin = $obj->con1->prepare("SELECT * FROM `tbl_users` WHERE role='superadmin'");
		$stmt_admin->execute();
		$admin_result = $stmt_admin->get_result();
		$stmt_admin->close();
		$admin = array();
		while($row = mysqli_fetch_array($admin_result)){
			$admin[] = $row['id'];
		}

		if(in_array($user_id, $admin)){
			$stmt = $obj->con1->prepare("SELECT DISTINCT(industrial_estate),id from tbl_industrial_estate where state_id=? and city_id=? and taluka=? and id not in (SELECT industrial_estate_id FROM `pr_add_industrialestate_details`)");
			$stmt->bind_param("sss",$state_name,$city,$taluka);
		}
		else{
			$stmt = $obj->con1->prepare("SELECT DISTINCT(i1.industrial_estate),i1.id FROM assign_estate a1, tbl_industrial_estate i1 WHERE a1.industrial_estate_id=i1.id and employee_id=? and start_dt<=curdate() and end_dt>=curdate() and i1.state_id=? and i1.city_id=? and i1.taluka=? and action='estate_plotting' and i1.id not in (SELECT industrial_estate_id FROM `pr_add_industrialestate_details`)");
			$stmt->bind_param("isss",$user_id,$state_name,$city,$taluka);
		}
		
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();

		$html='<option value="">Select Industrial Estate</option>';
		while($indEstate=mysqli_fetch_array($res))
		{
			$html.='<option value="'.$indEstate["id"].'">'.$indEstate["industrial_estate"].'</option>';
		}
		echo $html;
	}

	// unassigned_estate_plotting
	if($_REQUEST['action']=="assign_estate_forplotting")
	{	
		$html="";
		$estate_id=explode(",",$_REQUEST["ind_estate_id"]);

		$stmt_emp_list = $obj->con1->prepare("SELECT * FROM tbl_users where (LOWER(role)='assignor/verifier' or LOWER(role)='worker')");
		$stmt_emp_list->execute();
		$emp_result = $stmt_emp_list->get_result();
		$stmt_emp_list->close();
		
		$html='<form  method="post"><div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="industrial_estate" id="industrial_estate" value="'.$_REQUEST['ind_estate_id'].'"/>

		<div class="mb-3" >
		<label class="form-label" for="basic-default-fullname"><strong>Industrial Estates : </strong></label><br/>';
		for($j=0;$j<count($estate_id);$j++){
			$stmt_estate = $obj->con1->prepare("SELECT * FROM tbl_industrial_estate where id=?");
			$stmt_estate->bind_param("i",$estate_id[$j]);
			$stmt_estate->execute();
			$estate_result = $stmt_estate->get_result()->fetch_assoc();
			$stmt_estate->close();
			$html.='<i><label class="form-label" for="basic-default-fullname">'.($j+1).') '.$estate_result["industrial_estate"].' - '.$estate_result["taluka"].'</label></i><br/>';
		}
		$html.='</div>

		<div class="mb-3" >
		<label class="form-label" for="basic-default-fullname">Employees</label>
		<select name="e[]" id="emp_list" class="form-control js-example-basic-multiple" multiple="multiple" required>';
		while($emp=mysqli_fetch_array($emp_result)){
			$html.='<option value="'.$emp["id"].'">'.$emp["name"].'</option>';
		}
		$html.='</select>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Start Date</label>
		<input type="date" class="form-control" name="start_date" id="start_date" value="'.date("Y-m-d").'" max="9999-12-31" required />
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">End Date</label>
		<input type="date" class="form-control" name="end_date" id="end_date" max="9999-12-31" required />
		</div></div>';

		$html.='<div class="modal-footer">
		<input type="submit" class="btn btn-primary" name="btn_modal_update" id="btn_modal_update" value="Save Changes">
		<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
		Close
		</button>
		</form>
		</div></div>';

		echo $html;
	}

	// unassigned_estate_company
	if($_REQUEST['action']=="assign_estate_forcompany")
	{	
		$html="";
		$estate_id=explode(",",$_REQUEST["ind_estate_id"]);

		$stmt_emp_list = $obj->con1->prepare("SELECT * FROM tbl_users where (LOWER(role)='assignor/verifier' or LOWER(role)='worker')");
		$stmt_emp_list->execute();
		$emp_result = $stmt_emp_list->get_result();
		$stmt_emp_list->close();
		
		$html='<form  method="post"><div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="industrial_estate" id="industrial_estate" value="'.$_REQUEST['ind_estate_id'].'"/>

		<div class="mb-3" >
		<label class="form-label" for="basic-default-fullname">'.count($estate_id).'  estates selected</label>
		</div>

		<div class="mb-3" >
		<label class="form-label" for="basic-default-fullname">Industrial Estates : </label><br/>';
		for($j=0;$j<count($estate_id);$j++){
			$stmt_estate = $obj->con1->prepare("SELECT * FROM tbl_industrial_estate where id=?");
			$stmt_estate->bind_param("i",$estate_id[$j]);
			$stmt_estate->execute();
			$estate_result = $stmt_estate->get_result()->fetch_assoc();
			$stmt_estate->close();
			$html.='<label class="form-label" for="basic-default-fullname">'.$estate_result["industrial_estate"].' - '.$estate_result["taluka"].'</label><br/>';
		}
		$html.='</div>

		<div class="mb-3" >
		<label class="form-label" for="basic-default-fullname">Employees</label>
		<select name="e[]" id="emp_list" class="form-control js-example-basic-multiple" multiple="multiple" required>';
		while($emp=mysqli_fetch_array($emp_result)){
			$html.='<option value="'.$emp["id"].'">'.$emp["name"].'</option>';
		}
		$html.='</select>
		</div>

		<div class="mb-3" >
		<label class="form-label" for="basic-default-fullname">Filter Selection</label>
		<select name="filter[]" id="filter_selection" class="form-control js-example-basic-multiple" multiple="multiple" required>
		<option value="visit_pending">Visit Pending</option>
		<option value="open_plot">Open Plot</option>
		<option value="positive">Positive</option>
		<option value="negative">Negative</option>
		<option value="existing_client">Existing Client</option>';

		$html.='</select>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Start Date</label>
		<input type="date" class="form-control" name="start_date" id="start_date" value="'.date("Y-m-d").'" max="9999-12-31" required />
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">End Date</label>
		<input type="date" class="form-control" name="end_date" id="end_date" max="9999-12-31" required />
		</div></div>';

		$html.='<div class="modal-footer">
		<input type="submit" class="btn btn-primary" name="btn_modal_update" id="btn_modal_update" value="Save Changes">
		<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
		Close
		</button>
		</form>
		</div></div>';

		echo $html;
	}

	// unassigned_estate_plotting, add_industrial_estate_old
	if($_REQUEST['action']=="add_plotting_oldestate")
	{	
		$html="";
		$estate_id=$_REQUEST["estate_id"];

		$stmt_estate = $obj->con1->prepare("SELECT * FROM tbl_industrial_estate where id=?");
		$stmt_estate->bind_param("i",$estate_id);
		$stmt_estate->execute();
		$estate_result = $stmt_estate->get_result()->fetch_assoc();
		$stmt_estate->close();
		
		$html='<form  method="post" enctype="multipart/form-data"><div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="ind_estate_id" id="ind_estate_id" value="'.$estate_id.'"/>

		<div class="row">
		<div class="col mb-3" >
		<label class="form-label" for="basic-default-fullname">State : '.$estate_result["state_id"].'</label>
		<input type="hidden" class="form-control" name="state" id="state" value="'.$estate_result["state_id"].'"/>
		</div>
		<div class="col mb-3" >
		<label class="form-label" for="basic-default-fullname">City : '.$estate_result["city_id"].'</label>
		<input type="hidden" class="form-control" name="city" id="city" value="'.$estate_result["city_id"].'"/>
		</div>
		</div>
		<div class="row">
		<div class="col mb-3" >
		<label class="form-label" for="basic-default-fullname">Taluka : '.$estate_result["taluka"].'</label>
		<input type="hidden" class="form-control" name="taluka" id="taluka" value="'.$estate_result["taluka"].'"/>
		</div>
		<div class="col mb-3" >
		<label class="form-label" for="basic-default-fullname">Area : '.$estate_result["area_id"].'</label>
		<input type="hidden" class="form-control" name="area" id="area" value="'.$estate_result["area_id"].'"/>
		</div>
		</div>
		<div class="mb-3" >
		<label class="form-label" for="basic-default-fullname">Industrial Estate : '.$estate_result["industrial_estate"].'</label>
		<input type="hidden" class="form-control" name="industrial_estate" id="industrial_estate" value="'.$estate_result["industrial_estate"].'"/>
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
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Status</label>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="verify_status" id="Verified" onchange="change_for_required()" value="Verified">
		<label class="form-check-label" for="inlineRadio1">Verified</label>
		</div>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="verify_status" id="Fake" onchange="change_for_required()" value="Fake">
		<label class="form-check-label" for="inlineRadio1">Fake</label>
		</div>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="verify_status" id="Duplicate" onchange="change_for_required()" value="Duplicate">
		<label class="form-check-label" for="inlineRadio1">Duplicate</label>
		</div>
		</div>
		</div>';

		$html.='<div class="modal-footer">
		<input type="submit" class="btn btn-primary" name="btnupdate" id="btnupdate" value="Save Changes">
		<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
		Close
		</button>
		</form>
		</div></div>';

		echo $html;
	}

	// company_entry
	if($_REQUEST['action']=="getFilter")
	{	
	  	$html="";
	  	$estate_id=$_REQUEST['estate_id'];

		// fetch assigned estate filter
	  	$stmt_filter = $obj->con1->prepare("SELECT * FROM `pr_emp_estate` where industrial_estate_id=? and employee_id=?");
	  	$stmt_filter->bind_param("ii",$estate_id,$_SESSION["id"]);
	  	$stmt_filter->execute();
	  	$stmt_filter_result = $stmt_filter->get_result();
	  	$stmt_filter->close();
	  	$html='<option value="">Select Filter</option>';
	  	while($filter = mysqli_fetch_array($stmt_filter_result)){
	  		$html.='<option value="'.$filter["assign_estate_status"].'">'.ucfirst(str_replace("_"," ",$filter["assign_estate_status"])).'</option>';	
	  	}
	  	$html.='<option value="no_filter">No Filter</option>';

	  	echo $html;
	}

  	if($_REQUEST['action']=="getPlot")
  	{	
	  	$html="";
	  	$estate_id=$_REQUEST['estate_id'];
	  	$filter=$_REQUEST['filter'];

	  	$stmt_estate = $obj->con1->prepare("SELECT plotting_pattern FROM pr_add_industrialestate_details where industrial_estate_id=?");
	  	$stmt_estate->bind_param("i",$estate_id);
	  	$stmt_estate->execute();
	  	$estate_res = $stmt_estate->get_result()->fetch_assoc();
	  	$stmt_estate->close();
	  	$plotting_pattern = $estate_res['plotting_pattern']; 

	  	if($plotting_pattern=="Road"){
	  		$stmt_road = $obj->con1->prepare("SELECT DISTINCT(road_no) FROM `pr_estate_roadplot` WHERE industrial_estate_id=? order by abs(road_no)");
	  		$stmt_road->bind_param("i",$estate_id);
	  		$stmt_road->execute();
	  		$road_res = $stmt_road->get_result();
	  		$stmt_road->close();

	  		$html='<option value="">Select Road No.</option>';
	  		while($road = mysqli_fetch_array($road_res)){
	  			$html.='<option value="'.$road["road_no"].'">'.$road["road_no"].'</option>';	
	  		}
	  	}
	  	else if($plotting_pattern=="Series"){

	  		if($filter=="visit_pending"){
	  			$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 on p1.company_id=d1.cid where p1.industrial_estate_id=? AND ((d1.image IS NULL) OR (d1.image='')) order by abs(plot_no)");
	  		}
			else if($filter=="open_plot"){
				$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 ON p1.company_id=d1.cid WHERE p1.industrial_estate_id=? and plot_status='Open Plot' AND ((d1.image IS NOT NULL) AND (d1.image!='')) order by abs(plot_no)");
			}
			else if($filter=="positive"){
				$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Positive' and p1.industrial_estate_id=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
			}
			else if($filter=="negative"){
				$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Negative' and p1.industrial_estate_id=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
			}
			else if($filter=="existing_client"){
				$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Existing Client' and p1.industrial_estate_id=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
			}
			else if($filter=="no_filter"){
				$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and (c1.status NOT IN ('Positive','Negative','Existing Client') OR c1.status IS null) and p1.plot_status!='Open Plot' and ((c1.image IS NOT NULL) AND (c1.image!='')) and p1.industrial_estate_id=? order by abs(p1.plot_no)");
			}

			$stmt_plot->bind_param("i",$estate_id);
			$stmt_plot->execute();
			$plot_res = $stmt_plot->get_result();
			$stmt_plot->close();

			$html='<option value="">Select Plot No.</option>';
			while($plot = mysqli_fetch_array($plot_res)){
				$html.='<option value="'.$plot['plot_no'].'">'.$plot['plot_no'].'</option>';	
			}
		}

		echo $html.'@@@@@'.$plotting_pattern;
	}

	// company_entry
	if($_REQUEST['action']=="getRoadPlots")
	{	
		$html="";
		$estate_id=$_REQUEST['estate_id'];
		$road_no=$_REQUEST['road_no'];
		$filter=$_REQUEST['filter'];

		if($filter=="visit_pending"){
			$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 on p1.company_id=d1.cid where p1.industrial_estate_id=? AND p1.road_no=? AND ((d1.image IS NULL) OR (d1.image='')) order by abs(plot_no)");
		}
		else if($filter=="open_plot"){
			$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 ON p1.company_id=d1.cid WHERE p1.industrial_estate_id=? AND p1.road_no=? and plot_status='Open Plot' AND ((d1.image IS NOT NULL) AND (d1.image!='')) order by abs(plot_no)");
		}
		else if($filter=="positive"){
			$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Positive' and p1.industrial_estate_id=? AND p1.road_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
		}
		else if($filter=="negative"){
			$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Negative' and p1.industrial_estate_id=? AND p1.road_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
		}
		else if($filter=="existing_client"){
			$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Existing Client' and p1.industrial_estate_id=? AND p1.road_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by abs(p1.plot_no)");
		}
		else if($filter=="no_filter"){
			$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(p1.plot_no) FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and (c1.status NOT IN ('Positive','Negative','Existing Client') OR c1.status IS null) and p1.plot_status!='Open Plot' and ((c1.image IS NOT NULL) AND (c1.image!='')) and p1.industrial_estate_id=? AND p1.road_no=? order by abs(p1.plot_no)");
		}

		$stmt_plot->bind_param("is",$estate_id,$road_no);
		$stmt_plot->execute();
		$plot_res = $stmt_plot->get_result();
		$stmt_plot->close();

		$html='<option value="">Select Plot No.</option>';
		while($plot = mysqli_fetch_array($plot_res)){
			$html.='<option value="'.$plot['plot_no'].'">'.$plot['plot_no'].'</option>';
		}
		
		echo $html;
	}

	// company_entry
	if($_REQUEST['action']=="getFloor_plot")
	{	
		$html="";
		$estate_id=$_REQUEST['estate_id'];
		$plot_no=$_REQUEST['plot_no'];
		$road_no=$_REQUEST['road_no'];
		$filter=$_REQUEST['filter'];
		$floor_array = array();

		$stmt_estate = $obj->con1->prepare("SELECT plotting_pattern FROM pr_add_industrialestate_details where industrial_estate_id=?");
	  	$stmt_estate->bind_param("i",$estate_id);
	  	$stmt_estate->execute();
	  	$estate_res = $stmt_estate->get_result()->fetch_assoc();
	  	$stmt_estate->close();
	  	$plotting_pattern = $estate_res['plotting_pattern'];

		if($plotting_pattern=='Series'){
			if($filter=="visit_pending"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 on p1.company_id=d1.cid where p1.industrial_estate_id=? AND p1.plot_no=? AND ((d1.image IS NULL) OR (d1.image='')) order by p1.floor");
			}
			else if($filter=="open_plot"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 ON p1.company_id=d1.cid WHERE p1.industrial_estate_id=? AND p1.plot_no=? AND plot_status='Open Plot' AND ((d1.image IS NOT NULL) AND (d1.image!='')) order by p1.floor");	
			}
			else if($filter=="positive"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Positive' and p1.industrial_estate_id=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
			}
			else if($filter=="negative"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Negative' and p1.industrial_estate_id=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
			}
			else if($filter=="existing_client"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Existing Client' and p1.industrial_estate_id=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
			}
			else if($filter=="no_filter"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and (c1.status NOT IN ('Positive','Negative','Existing Client') OR c1.status IS null) and p1.plot_status!='Open Plot' and ((c1.image IS NOT NULL) AND (c1.image!='')) and p1.industrial_estate_id=? AND p1.plot_no=? order by p1.floor");
			}
			
			$stmt_floor->bind_param("is",$estate_id,$plot_no);
		}
		else if($plotting_pattern=='Road'){
			if($filter=="visit_pending"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 on p1.company_id=d1.cid where p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? AND ((d1.image IS NULL) OR (d1.image='')) order by p1.floor");
			}
			else if($filter=="open_plot"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` d1 ON p1.company_id=d1.cid WHERE p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? AND plot_status='Open Plot' AND ((d1.image IS NOT NULL) AND (d1.image!='')) order by p1.floor");
			}
			else if($filter=="positive"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Positive' and p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
			}
			else if($filter=="negative"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Negative' and p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
			}
			else if($filter=="existing_client"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and c1.status='Existing Client' and p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? AND ((c1.image IS NOT NULL) AND (c1.image!='')) order by p1.floor");
			}
			else if($filter=="no_filter"){
				$stmt_floor = $obj->con1->prepare("SELECT p1.floor FROM pr_company_plots p1, pr_company_details c1 WHERE p1.company_id=c1.cid and (c1.status NOT IN ('Positive','Negative','Existing Client') OR c1.status IS null) and p1.plot_status!='Open Plot' and ((c1.image IS NOT NULL) AND (c1.image!='')) and p1.industrial_estate_id=? AND p1.road_no=? AND p1.plot_no=? order by p1.floor");
			}
			$stmt_floor->bind_param("iss",$estate_id,$road_no,$plot_no);
		}
		$stmt_floor->execute();
		$floor_res = $stmt_floor->get_result();
		$stmt_floor->close();

		while($floor=mysqli_fetch_array($floor_res)){
			$floor_array[] = $floor['floor'];
			if($floor['floor']=='0'){
				$html.='<option value="'.$floor['floor'].'">Ground Floor</option>';
			}
			else{
				$html.='<option value="'.$floor['floor'].'">'.$floor['floor'].'</option>';
			}
		}

    	echo $html;
	}

	// company_entry
	if($_REQUEST['action']=="get_companydetails")
	{	
		$html="";
		$estate_id=$_REQUEST['estate_id'];
		$plot_no=$_REQUEST['plot_no'];
		$floor_no=$_REQUEST['floor_no'];
		$road_no=$_REQUEST['road_no'];
		$plotting_pattern = $_COOKIE['plottingpattern_company'];

		$stmt_estate = $obj->con1->prepare("SELECT * FROM tbl_industrial_estate i1 WHERE id=?");
		$stmt_estate->bind_param("i",$estate_id);
		$stmt_estate->execute();
		$estate_res = $stmt_estate->get_result()->fetch_assoc();
		$stmt_estate->close();

		if($plot_no!="" && $floor_no!=""){

			$constitution = "";
			$image = "";
			$pr_comp_status = "";

			if($plotting_pattern=="Series"){
				$stmt_company_plot = $obj->con1->prepare("SELECT p1.pid, p1.company_id, p1.plot_no, p1.floor, p1.road_no, p1.plot_status, p1.plot_id, c1.image, c1.constitution, c1.status, c1.rawdata_id, c1.existing_client_status FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` c1 on p1.company_id=c1.cid WHERE p1.plot_no=? and p1.floor=? and p1.industrial_estate_id=?");
				$stmt_company_plot->bind_param("sii",$plot_no,$floor_no,$estate_id);
			}
			else if($plotting_pattern=="Road"){
				$stmt_company_plot = $obj->con1->prepare("SELECT p1.pid, p1.company_id, p1.plot_no, p1.floor, p1.road_no, p1.plot_status, p1.plot_id, c1.image, c1.constitution, c1.status, c1.rawdata_id, c1.existing_client_status FROM `pr_company_plots` p1 LEFT JOIN `pr_company_details` c1 on p1.company_id=c1.cid WHERE p1.plot_no=? and p1.floor=? and p1.industrial_estate_id=? and p1.road_no=?");
				$stmt_company_plot->bind_param("siis",$plot_no,$floor_no,$estate_id,$road_no);
			}
			$stmt_company_plot->execute();
			$pr_company_plot = $stmt_company_plot->get_result()->fetch_assoc();
			$stmt_company_plot->close();
			$pr_comp_status = $pr_company_plot["status"];

			if($pr_company_plot["rawdata_id"]!="" || $pr_company_plot["rawdata_id"]!=null){
				// get common values from json from table tbl_tdrawdata
				$stmt = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata WHERE id=?");
				$stmt->bind_param("i",$pr_company_plot["rawdata_id"]);
				$stmt->execute();
				$company_details = $stmt->get_result();
				$stmt->close();	

				while($plot = mysqli_fetch_array($company_details)){
					$row_data = json_decode($plot["raw_data"]);
					$post_fields = $row_data->post_fields;

					if($post_fields->IndustrialEstate==$estate_res['industrial_estate'] && $post_fields->Taluka==$estate_res['taluka'] && $post_fields->Area==$estate_res['area_id']){
								
						$reason = "";
						$status = "";

						// get company status (positive/negative/existing) from tbl_tdrawassign
						$stmt_status = $obj->con1->prepare("SELECT stage FROM `tbl_tdrawassign` WHERE inq_id=? order by id desc LIMIT 1");
						$stmt_status->bind_param("i",$plot['id']);
						$stmt_status->execute();
						$status_result = $stmt_status->get_result();
						$stmt_status->close();
						if(mysqli_num_rows($status_result)>0){
							$status_res = mysqli_fetch_array($status_result);
							if($status_res['stage']=="lead"){
								$status = "Positive";	
							}
							else if($status_res['stage']=="badlead" || $status_res['stage']=="revisedbadlead"){
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
							$stmt_reason = $obj->con1->prepare("SELECT bad_lead_reason FROM `tbl_tdbadleads` WHERE inq_id=?");
							$stmt_reason->bind_param("i",$plot['id']);
							$stmt_reason->execute();
							$reason_result = $stmt_reason->get_result()->fetch_assoc();
							$stmt_reason->close();
							$reason = $reason_result['bad_lead_reason'];
						}

						$details = Array (
							"Id" => $plot["id"],
							"Plot_Id" => $pr_company_plot["plot_id"],
							"Road_No" => $pr_company_plot["road_no"],
							"IndustrialEstate" => $post_fields->IndustrialEstate,
							"Area" => $post_fields->Area,
							"Plot_Status" => isset($pr_company_plot["plot_status"])?$pr_company_plot["plot_status"]:"",
							"Premise" => $post_fields->Premise,
							"GST_No" => $post_fields->GST_No,
							"Firm_Name" => $post_fields->Firm_Name,
							"Contact_Name" => $post_fields->Contact_Name,
							"Mobile_No" => $post_fields->Mobile_No,
							"Constitution" => isset($pr_company_plot["constitution"])?$pr_company_plot["constitution"]:"",
							"Category" => $post_fields->Category,
							"Segment" => $post_fields->Segment,
							"Status" => isset($pr_company_plot["status"])?$pr_company_plot["status"]:"",
							"Reason" => $reason,
							"source" => $post_fields->source,
							"Source_Name" => $post_fields->Source_Name,
							"Remarks" => $post_fields->Remarks,
							"Image" => isset($pr_company_plot["image"])?$pr_company_plot["image"]:"",
							"Company_detail_id" => $pr_company_plot["company_id"],
							"Company_plot_id" => $pr_company_plot["pid"],
                            "Loan_Sanction" => isset($post_fields->loan_applied)?$post_fields->loan_applied:"",
                            "Completion_Date" => isset($post_fields->Completion_Date)?$post_fields->Completion_Date:"",
                            "Existing_client_status" => ($pr_company_plot["existing_client_status"]==NULL)?"":$pr_company_plot["existing_client_status"]
						);
						break;
					}
				}
			}
			else{
				// get common values from json from table tbl_tdrawdata
				$stmt = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($estate_res['taluka'])."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($estate_res['industrial_estate'])."%' and lower(raw_data->'$.post_fields.Area') like '%".strtolower($estate_res['area_id'])."%' and raw_data->'$.plot_details[*].Plot_No' like '%".$plot_no."%' and raw_data->'$.plot_details[*].Road_No' like '%".$road_no."%' and raw_data->'$.plot_details[*].Floor' like '%".$floor_no."%'");
				$stmt->execute();
				$company_details = $stmt->get_result();
				$stmt->close();	

				while($plot = mysqli_fetch_array($company_details)){
					$row_data = json_decode($plot["raw_data"]);
					$post_fields = $row_data->post_fields;

					if($post_fields->IndustrialEstate==$estate_res['industrial_estate'] && $post_fields->Taluka==$estate_res['taluka'] && $post_fields->Area==$estate_res['area_id']){

						$plot_details = $row_data->plot_details;

						foreach ($plot_details as $pd) {
							if($pd->Floor==$floor_no && $pd->Plot_No==$plot_no  && $pd->Road_No==$road_no){
								
								$reason = "";
								$status = "";

								// get company status (positive/negative/existing) from tbl_tdrawassign
								$stmt_status = $obj->con1->prepare("SELECT stage FROM `tbl_tdrawassign` WHERE inq_id=? order by id desc LIMIT 1");
								$stmt_status->bind_param("i",$plot['id']);
								$stmt_status->execute();
								$status_result = $stmt_status->get_result();
								$stmt_status->close();
								if(mysqli_num_rows($status_result)>0){
									$status_res = mysqli_fetch_array($status_result);
									if($status_res['stage']=="lead"){
										$status = "Positive";	
									}
									else if($status_res['stage']=="badlead" || $status_res['stage']=="revisedbadlead"){
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
									$stmt_reason = $obj->con1->prepare("SELECT bad_lead_reason FROM `tbl_tdbadleads` WHERE inq_id=?");
									$stmt_reason->bind_param("i",$plot['id']);
									$stmt_reason->execute();
									$reason_result = $stmt_reason->get_result()->fetch_assoc();
									$stmt_reason->close();
									$reason = $reason_result['bad_lead_reason'];
								}

								$details = Array (
									"Id" => $plot["id"],
									"Plot_Id" => $pr_company_plot["plot_id"],
									"Road_No" => $pr_company_plot["road_no"],
									"IndustrialEstate" => $post_fields->IndustrialEstate,
									"Area" => $post_fields->Area,
									"Plot_Status" => isset($pr_company_plot["plot_status"])?$pr_company_plot["plot_status"]:"",
									"Premise" => $post_fields->Premise,
									"GST_No" => $post_fields->GST_No,
									"Firm_Name" => $post_fields->Firm_Name,
									"Contact_Name" => $post_fields->Contact_Name,
									"Mobile_No" => $post_fields->Mobile_No,
									"Constitution" => isset($pr_company_plot["constitution"])?$pr_company_plot["constitution"]:"",
									"Category" => $post_fields->Category,
									"Segment" => $post_fields->Segment,
									"Status" => isset($pr_company_plot["status"])?$pr_company_plot["status"]:"",
									"Reason" => $reason,
									"source" => $post_fields->source,
									"Source_Name" => $post_fields->Source_Name,
									"Remarks" => $post_fields->Remarks,
									"Image" => isset($pr_company_plot["image"])?$pr_company_plot["image"]:"",
									"Company_detail_id" => $pr_company_plot["company_id"],
									"Company_plot_id" => $pr_company_plot["pid"],
	                                "Loan_Sanction" => isset($post_fields->loan_applied)?$post_fields->loan_applied:"",
	                                "Completion_Date" => isset($post_fields->Completion_Date)?$post_fields->Completion_Date:"",
	                                "Existing_client_status" => ($pr_company_plot["existing_client_status"]==NULL)?"":$pr_company_plot["existing_client_status"]
								);
								
								break;
							}
						}
					}
				}
			}
			/*

			// get common values from json from table tbl_tdrawdata
			$stmt = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($estate_res['taluka'])."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($estate_res['industrial_estate'])."%' and lower(raw_data->'$.post_fields.Area') like '%".strtolower($estate_res['area_id'])."%' and raw_data->'$.plot_details[*].Plot_No' like '%".$plot_no."%' and raw_data->'$.plot_details[*].Road_No' like '%".$road_no."%' and raw_data->'$.plot_details[*].Floor' like '%".$floor_no."%'");
			$stmt->execute();
			$company_details = $stmt->get_result();
			$stmt->close();

			// get plot details from pr_company_plots
			if($plotting_pattern=="Series"){
				$stmt_company_plot = $obj->con1->prepare("SELECT pid, company_id, plot_no, floor, road_no, plot_status, plot_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=?");
				$stmt_company_plot->bind_param("sii",$plot_no,$floor_no,$estate_id);
			}	
			else if($plotting_pattern=="Road"){
				$stmt_company_plot = $obj->con1->prepare("SELECT pid, company_id, plot_no, floor, road_no, plot_status, plot_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=?	and road_no=?");
				$stmt_company_plot->bind_param("siis",$plot_no,$floor_no,$estate_id,$road_no);
			}

			$stmt_company_plot->execute();
			$pr_company_plot = $stmt_company_plot->get_result()->fetch_assoc();
			$stmt_company_plot->close();


			$constitution = "";
			$image = "";
			$pr_comp_status = "";

			// get image and contitution from pr_company_details
			if($pr_company_plot["company_id"]!="" || $pr_company_plot["company_id"]!=null){
				$stmt_company_details = $obj->con1->prepare("SELECT image, constitution,status FROM `pr_company_details` where cid=?");
				$stmt_company_details->bind_param("i",$pr_company_plot["company_id"]);
				$stmt_company_details->execute();
				$pr_company_details = $stmt_company_details->get_result()->fetch_assoc();
				$stmt_company_details->close();
				$constitution = $pr_company_details["constitution"];
				$image = $pr_company_details["image"];
				$pr_comp_status = $pr_company_details["status"];
			}


			while($plot = mysqli_fetch_array($company_details)){
				$row_data = json_decode($plot["raw_data"]);
				$post_fields = $row_data->post_fields;

				if($post_fields->IndustrialEstate==$estate_res['industrial_estate'] && $post_fields->Taluka==$estate_res['taluka'] && $post_fields->Area==$estate_res['area_id']){

					$plot_details = $row_data->plot_details;

					foreach ($plot_details as $pd) {
						if($pd->Floor==$floor_no && $pd->Plot_No==$plot_no  && $pd->Road_No==$road_no){
							
							$reason = "";
							$status = "";

							// get company status (positive/negative/existing) from tbl_tdrawassign
							$stmt_status = $obj->con1->prepare("SELECT stage FROM `tbl_tdrawassign` WHERE inq_id=? order by id desc LIMIT 1");
							$stmt_status->bind_param("i",$plot['id']);
							$stmt_status->execute();
							$status_result = $stmt_status->get_result();
							$stmt_status->close();
							if(mysqli_num_rows($status_result)>0){
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
								$stmt_reason = $obj->con1->prepare("SELECT bad_lead_reason FROM `tbl_tdbadleads` WHERE inq_id=?");
								$stmt_reason->bind_param("i",$plot['id']);
								$stmt_reason->execute();
								$reason_result = $stmt_reason->get_result()->fetch_assoc();
								$stmt_reason->close();
								$reason = $reason_result['bad_lead_reason'];
							}

							$details = Array (
								"Id" => $plot["id"],
								"Plot_Id" => $pr_company_plot["plot_id"],
								"Road_No" => $pr_company_plot["road_no"],
								"IndustrialEstate" => $post_fields->IndustrialEstate,
								"Area" => $post_fields->Area,
								"Plot_Status" => $pr_company_plot["plot_status"],
								"Premise" => $post_fields->Premise,
								"GST_No" => $post_fields->GST_No,
								"Firm_Name" => $post_fields->Firm_Name,
								"Contact_Name" => $post_fields->Contact_Name,
								"Mobile_No" => $post_fields->Mobile_No,
								"Constitution" => $constitution,
								"Category" => $post_fields->Category,
								"Segment" => $post_fields->Segment,
								"Status" => $status,
								"Reason" => $reason,
								"source" => $post_fields->source,
								"Source_Name" => $post_fields->Source_Name,
								"Remarks" => $post_fields->Remarks,
								"Image" => $image,
								"Company_detail_id" => $pr_company_plot["company_id"],
								"Company_plot_id" => $pr_company_plot["pid"],
                                "Loan_Sanction" => isset($post_fields->loan_applied)?$post_fields->loan_applied:"",
                                "Completion_Date" => isset($post_fields->Completion_Date)?$post_fields->Completion_Date:"",
                                "Existing_client_status" => isset($post_fields->Existing_client_status)?$post_fields->Existing_client_status:""
							);
							break;
						}
					}
				}
			}*/
		}
		else{
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
				"Loan_Sanction" => "",
                "Completion_Date" => "",
                "Existing_client_status" => ""
			);
		}

		$d = json_encode($details);
		echo $d;
	}

	// company_entry
	if($_REQUEST['action']=="additionalPlot_companyEntry")
	{	
		$html="";
		$estate_id=$_REQUEST['estate_id'];
		$plotting_pattern = $_COOKIE['plottingpattern_company'];

		$stmt_road_list = $obj->con1->prepare("SELECT DISTINCT(road_no) FROM `pr_estate_roadplot` WHERE industrial_estate_id=? order by abs(road_no)");
		$stmt_road_list->bind_param("i",$estate_id);
		$stmt_road_list->execute();
		$road_list_result = $stmt_road_list->get_result();
		$stmt_road_list->close();

		$html = '<div><form method="post" enctype="multipart/form-data">
		<div class="modal-body" ><div class="row">
		<input type="hidden" name="estateid_additionalplot" id="estateid_additionalplot" value="'.$estate_id.'">

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Plot No.</label>
		<input type="text" class="form-control" name="additional_plot" id="additional_plot" onkeyup="enable_submit_button()"/>
		<div id="additional_plot_alert_div" class="text-danger"></div>
		</div>';

		if($plotting_pattern=='Road'){ 
			$html.='<div class="mb-3">
			<label class="form-label" for="basic-default-fullname">Road No.</label>
			<select name="road_no_additionalplot" id="road_no_additionalplot" class="form-control" onchange="enable_submit_button()">
			<option value="">Select Road No.</option>';
			while($road = mysqli_fetch_array($road_list_result)){
				$html.='<option value="'.$road['road_no'].'">'.$road['road_no'].'</option>';
			}
			$html.='</select>

			</div>';
		} 
		$html.='</div></div>
		<div class="modal-footer">
		<input type="submit" class="btn btn-primary" name="btn_modal_additional_plot" id="btn_modal_additional_plot" value="Save Changes" ';
		if($plotting_pattern=="Road"){
			$html.='onclick="return check_for_same_plot(additional_plot.value,'.$estate_id.',road_no_additionalplot.value)"';
		} else{
			$html.='onclick="return check_for_same_plot(additional_plot.value,'.$estate_id.')"';
		}
		$html.='>
		<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>
		</form>
		</div>';

		echo $html;
	}

	//company_entry
	if($_REQUEST['action']=="check_for_same_plot")
	{	
		$html="";
		$estate_id = $_REQUEST['estate_id'];
		$road_no = $_REQUEST['road_no'];
		$additional_plot = $_REQUEST['additional_plot'];

		if($road_no!=""){
			$stmt_list = $obj->con1->prepare("SELECT * FROM `pr_company_plots` WHERE plot_no=? and road_no=? and industrial_estate_id=?");
			$stmt_list->bind_param("ssi",$additional_plot,$road_no,$estate_id);
		}
		else{
			$stmt_list = $obj->con1->prepare("SELECT * FROM `pr_company_plots` WHERE plot_no=? and industrial_estate_id=?");
			$stmt_list->bind_param("si",$additional_plot,$estate_id);
		}

		$stmt_list->execute();
		$plot_res = $stmt_list->get_result();
		$stmt_list->close();

		if(mysqli_num_rows($plot_res)>0){
			echo 1;
		}
		else{
			echo 0;
		}
	}

	// company_entry
	if($_REQUEST['action']=="addPlot_companyEntry")
	{	
		$html="";
		$id = $_REQUEST['id'];
		$estate_id = $_REQUEST['estate_id'];
		$industrial_estate=$_REQUEST['industrial_estate'];
		$area=$_REQUEST['area'];
		$plotting_pattern = $_COOKIE['plottingpattern_company'];
		$plot_no=$_REQUEST['plot_no'];
		$floor_no=$_REQUEST['floor_no'];
		$road_no=$_REQUEST['road_no'];
		$pr_company_detail_id = $_REQUEST['pr_company_detail_id'];

		$plot_array = array();

		if($plotting_pattern=="Road"){
			
			$stmt_road = $obj->con1->prepare("SELECT DISTINCT(road_no) FROM `pr_estate_roadplot` WHERE industrial_estate_id=? order by abs(road_no)");
			$stmt_road->bind_param("i",$estate_id);
			$stmt_road->execute();
			$road_res = $stmt_road->get_result();
			$stmt_road->close();
		}
		else if($plotting_pattern=="Series"){
			
			// to all plots - plot selected
			//$stmt_plot_list=$obj->con1->prepare("SELECT DISTINCT(plot_no) FROM `pr_company_plots` WHERE industrial_estate_id='".$estate_id."' and company_id IS NULL and plot_no!='".$plot_no."' ");
			$stmt_plot_list=$obj->con1->prepare("SELECT DISTINCT(plot_no) FROM `pr_company_plots` WHERE industrial_estate_id='".$estate_id."' and plot_no!='".$plot_no."' order by abs(plot_no)");
			$stmt_plot_list->execute();
			$plot_list_result = $stmt_plot_list->get_result();
			$stmt_plot_list->close();

			while($plot = mysqli_fetch_array($plot_list_result)){
				$plot_array[] = $plot["plot_no"];
			}
		}

		$html = '<div ><form method="post" enctype="multipart/form-data">
		<div class="modal-body" ><div class="row">
		<input type="hidden" name="plotmodal_ttId" id="plotmodal_ttId" value="'.$id.'">
		<input type="hidden" name="estateid_plotmodal" id="estateid_plotmodal" value="'.$estate_id.'">
		<input type="hidden" name="industrialestate_plotmodal" id="industrialestate_plotmodal" value="'.$industrial_estate.'">
		<input type="hidden" name="area_plotmodal" id="area_plotmodal" value="'.$area.'">
		<input type="hidden" name="plottingpattern_plotmodal" id="plottingpattern_plotmodal" value="'.$plotting_pattern.'">
		<input type="hidden" name="pr_company_detail_id_plotmodal" id="pr_company_detail_id_plotmodal" value="'.$pr_company_detail_id.'">

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Plot No. : '.$plot_no.'</label>
		</div>';

		if($floor_no=='0'){
			$html.='<div class="col mb-3">
			<label class="form-label" for="basic-default-fullname">Floor No. : Ground Floor</label>
			</div>';
		} else{
			$html.='<div class="col mb-3">
			<label class="form-label" for="basic-default-fullname">Floor No. : '.$floor_no.'</label>
			</div>';
		}

		if($plotting_pattern=='Road'){
			$html.='<div class="col mb-3">
			<label class="form-label" for="basic-default-fullname">Road No. : '.$road_no.'</label>
			</div></div>

			<div class="mb-3">
			<label class="form-label" for="basic-default-fullname">Road No.</label>
			<select name="road_no_plotmodal" id="road_no_plotmodal" class="form-control" onchange="get_plotno_plotmodal(this.value,estateid_plotmodal.value,\''.$plot_no.'\',\''.$road_no.'\')">
			<option value="">Select Road No.</option>';
			while($road = mysqli_fetch_array($road_res)){
				$html.='<option value="'.$road["road_no"].'">'.$road["road_no"].'</option>';
			}
			$html.='</select>
			</div>';
		}

		$html.='<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Plot No.</label>
		<select name="plot_no_plotmodal" id="plot_no_plotmodal" class="form-control" onchange="get_floorno(this.value,estateid_plotmodal.value)" required>
		<option value="">Select Plot No.</option>';
		foreach($plot_array as $plot_no){
			$html.='<option value="'.$plot_no.'">'.$plot_no.'</option>';	
		}
		$html.='</select>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Floor No.</label>
		<select name="floor_plotmodal" id="floor_plotmodal" class="form-control" required>
		<option value="0">Ground Floor</option>
		</select>
		</div>';

		$html.='<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Plot Status</label>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="plot_status_plotmodal" id="open_plot_plotmodal" value="Open Plot" required checked>
		<label class="form-check-label" for="inlineRadio1">Open Plot</label>
		</div>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="plot_status_plotmodal" id="under_construction_plotmodal" value="Under Construction" required>
		<label class="form-check-label" for="inlineRadio1">Under Construction</label>
		</div>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="plot_status_plotmodal" id="constructed_plotmodal" value="Constructed" required>
		<label class="form-check-label" for="inlineRadio1">Constructed</label>
		</div>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname"></label>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="plot_confirmation" id="same_as_ground_plotmodal" value="same_as_ground" required checked>
		<label class="form-check-label" for="inlineRadio1">Same Company</label>
		</div>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="plot_confirmation" id="same_owner_plotmodal" value="same_owner" required>
		<label class="form-check-label" for="inlineRadio1">Same Owner But Different Company</label>
		</div>
		</div>
		</div></div>
		<div class="modal-footer">
		<input type="submit" class="btn btn-primary" name="btn_modal_insert_plot" id="btn_modal_insert_plot" value="Save Changes">
		<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>
		</form>
		</div>';

		echo $html;
	}

	// company_entry
	if($_REQUEST['action']=="addFloor_companyEntry")
	{	
		$html = "";
		$id = $_REQUEST['id'];
		$plot_no = $_REQUEST['plot_no'];
		$road_no = $_REQUEST['road_no'];
		$floorno = $_REQUEST['floor_no'];
		$estate_id = $_REQUEST['estate_id'];
		$area = $_REQUEST['area'];
		$industrial_estate = $_REQUEST['industrial_estate'];
		$plotting_pattern = $_COOKIE['plottingpattern_company'];
		$pr_company_detail_id = $_REQUEST['pr_company_detail_id'];

		if($plotting_pattern=='Series'){
			$stmt = $obj->con1->prepare("SELECT all_numbers.floor FROM ( SELECT 0 AS floor UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 ) AS all_numbers LEFT JOIN ( SELECT DISTINCT floor FROM pr_company_plots WHERE industrial_estate_id='".$estate_id."' AND plot_no='".$plot_no."' ) AS plots ON all_numbers.floor = plots.floor WHERE plots.floor IS NULL");
		}
		else if($plotting_pattern=='Road'){
			$stmt = $obj->con1->prepare("SELECT all_numbers.floor FROM ( SELECT 0 AS floor UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 ) AS all_numbers LEFT JOIN ( SELECT DISTINCT floor FROM pr_company_plots WHERE industrial_estate_id='".$estate_id."' AND plot_no='".$plot_no."' and road_no='".$road_no."' ) AS plots ON all_numbers.floor = plots.floor WHERE plots.floor IS NULL");
		}

		$stmt->execute();
		$data = $stmt->get_result();
		$stmt->close();

		$html = '<div><form method="post" enctype="multipart/form-data">
		<div class="modal-body"><div class="row">
		<input type="hidden" name="floormodal_ttId" id="floormodal_ttId" value="'.$id.'">
		<input type="hidden" name="plot_no_floormodal" id="plot_no_floormodal" value="'.$plot_no.'">
		<input type="hidden" name="selected_floor_floormodal" id="selected_floor_floormodal" value="'.$floorno.'">
		<input type="hidden" name="road_no_floormodal" id="road_no_floormodal" value="'.$road_no.'">
		<input type="hidden" name="estateid_floormodal" id="estateid_floormodal" value="'.$estate_id.'">
		<input type="hidden" name="pr_company_detail_id_floormodal" id="pr_company_detail_id_floormodal" value="'.$pr_company_detail_id.'">
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Plot No. : '.$plot_no.'</label>
		</div>';

		if($floorno=='0'){
			$html.='<div class="col mb-3">
			<label class="form-label" for="basic-default-fullname">Floor No. : Ground Floor</label>
			</div>';
		} else{
			$html.='<div class="col mb-3">
			<label class="form-label" for="basic-default-fullname">Floor No. : '.$floorno.'</label>
			</div>';
		}


		if($plotting_pattern=='Road'){
			$html.='<div class="col mb-3">
			<label class="form-label" for="basic-default-fullname">Road No. : '.$road_no.'</label>
			</div>';
		}

		$html.='</div><div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Floor No.</label>
		<select name="floor_floormodal" id="floor_floormodal" class="form-control" required>
		<option value="">Select Floor No.</option>';
		while($floor=mysqli_fetch_array($data)){
			if($floor['floor']=='0'){
				$html.='<option value="'.$floor['floor'].'">Ground Floor</option>';
			}
			else{
				$html.='<option value="'.$floor['floor'].'">'.$floor['floor'].'</option>';
			}
		}
		$html.='</select>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Status</label>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="plot_status_floormodal" id="open_plot_modal" value="Open Plot" required checked>
		<label class="form-check-label" for="inlineRadio1">Open Plot</label>
		</div>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="plot_status_floormodal" id="under_construction_modal" value="Under Construction" required>
		<label class="form-check-label" for="inlineRadio1">Under Construction</label>
		</div>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="plot_status_floormodal" id="constructed_modal" value="Constructed" required>
		<label class="form-check-label" for="inlineRadio1">Constructed</label>
		</div>
		</div>
		<hr>
		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname"></label>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="floor_confirmation" id="same_as_ground_floor" value="same_as_ground" required checked>
		<label class="form-check-label" for="inlineRadio1">Same Company</label>
		</div>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="floor_confirmation" id="same_owner_floor" value="same_owner" required>
		<label class="form-check-label" for="inlineRadio1">Same Owner But Different Company</label>
		</div>
		<div class="form-check form-check-inline mt-3">
		<input class="form-check-input" type="radio" name="floor_confirmation" id="different_company_floor" value="different_company" required>
		<label class="form-check-label" for="inlineRadio1">Different Company</label>
		</div>
		</div>
		</div></div>
		<div class="modal-footer">
		<input type="submit" class="btn btn-primary" name="btn_modal_insert_floor" id="btn_modal_insert_floor" value="Save Changes">
		<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>
		</form>
		</div>';

		echo $html;
	}

	//company_entry
	if($_REQUEST['action']=="get_plotno_plotmodal")
	{	
		$html="";
		$road_no=$_REQUEST['road_no'];
		$estate_id=$_REQUEST['estate_id'];
		$not_road_no=$_REQUEST['not_road_no'];
		$not_plot_no=$_REQUEST['not_plot_no'];

		// to all plots - plot selected
		$stmt_plot_list=$obj->con1->prepare("SELECT DISTINCT(plot_no) FROM `pr_company_plots` WHERE industrial_estate_id='".$estate_id."' AND road_no='".$road_no."' AND (road_no!='".$not_road_no."' OR plot_no!='".$not_plot_no."') ORDER BY ABS(plot_no)");
		$stmt_plot_list->execute();
		$plot_list_result = $stmt_plot_list->get_result();
		$stmt_plot_list->close();

		$html='<option value="">Select Plot No.</option>';
		while($plot = mysqli_fetch_array($plot_list_result)){
			$html.='<option value="'.$plot["plot_no"].'">'.$plot["plot_no"].'</option>';
		}

		echo $html;
	}

	// company_entry
	if($_REQUEST['action']=="getSourceName")
	{	
		$html="";
		$source = $_REQUEST['source'];
		$table_name = $_REQUEST['table_name'];

		if($table_name=='tbl_sourcetype_master'){
			$stmt_sourcename_list = $obj->con1->prepare("SELECT s1.name FROM tbl_sourcing_master s1, tbl_sourcetype_master t1 WHERE s1.source_type_id=t1.id and t1.source_type=?");
			$stmt_sourcename_list->bind_param("s",$source);
			$stmt_sourcename_list->execute();
			$sourcename_result = $stmt_sourcename_list->get_result();
			$stmt_sourcename_list->close();

			$html='<option value="">Select Source Name</option>';
			while($sourcename_list=mysqli_fetch_array($sourcename_result)){
				$html.='<option value="'.$sourcename_list["name"].'">'.$sourcename_list["name"].'</option>';
			}
		}
		else if($table_name=='asso_segment_master'){
			$stmt_sourcename_list = $obj->con1->prepare("SELECT concat(json_unquote(raw_data->'$.post_fields.Firm_Name'),' - ',json_unquote(raw_data->'$.post_fields.Contact_Name')) as firm_contact FROM `tbl_tdassodata` WHERE lower(raw_data->'$.post_fields.Segment_Name') like '%".strtolower($source)."%'");
			$stmt_sourcename_list->execute();
			$sourcename_result = $stmt_sourcename_list->get_result();
			$stmt_sourcename_list->close();

			$html='<option value="">Select Source Name</option>';
			while($sourcename_list=mysqli_fetch_array($sourcename_result)){
				$html.='<option value="'.$sourcename_list["firm_contact"].'">'.$sourcename_list["firm_contact"].'</option>';
			}
		}
		else if($table_name=='new_system'){
			$stmt_sourcename_list = $obj->con1->prepare("SELECT concat(json_unquote(raw_data->'$.post_fields.Firm_Name'),' - ',json_unquote(raw_data->'$.post_fields.Contact_Name')) as firm_contact FROM `tbl_tdrawdata`");
			$stmt_sourcename_list->execute();
			$sourcename_result = $stmt_sourcename_list->get_result();
			$stmt_sourcename_list->close();

			$html='<option value="">Select Source Name</option>';
			while($sourcename_list=mysqli_fetch_array($sourcename_result)){
				if($sourcename_list["firm_contact"]!=' - '){
					$html.='<option value="'.$sourcename_list["firm_contact"].'">'.$sourcename_list["firm_contact"].'</option>';
				}
			}
		}

		echo $html;
	}

	// unassigned_estate_company
	if($_REQUEST['action']=="view_estate_details")
	{	
		$html="";
		$ind_estate_id=$_REQUEST['ind_estate_id'];

		$stmt_sum = $obj->con1->prepare("SELECT count(plot_no) as total from pr_company_plots where floor='0' and industrial_estate_id=?");
		$stmt_sum->bind_param("i",$ind_estate_id);
		$stmt_sum->execute();
		$result_sum = $stmt_sum->get_result()->fetch_assoc();
		$stmt_sum->close();

		$stmt_list = $obj->con1->prepare("SELECT i1.state_id, i1.city_id, i1.taluka, i1.area_id, i1.industrial_estate, d1.plotting_pattern, d1.user_id, u1.name, ifnull(d1.status,'-') as status from pr_add_industrialestate_details d1, tbl_industrial_estate i1, tbl_users u1 where d1.industrial_estate_id=i1.id and u1.id=d1.user_id and i1.id=? group by industrial_estate_id");
		$stmt_list->bind_param("i",$ind_estate_id);
		$stmt_list->execute();
		$result = $stmt_list->get_result()->fetch_assoc();
		$stmt_list->close();

		$stmt_image = $obj->con1->prepare("SELECT image FROM `pr_estate_subimages` WHERE industrial_estate_id=?");
		$stmt_image->bind_param("i",$ind_estate_id);
		$stmt_image->execute();
		$image_result = $stmt_image->get_result();
		$stmt_image->close();

		if($result["plotting_pattern"]=='Road'){
			$stmt_plot_list = $obj->con1->prepare("SELECT b.road_no, b.plot, ifnull(a.additional_plot,'-') as additional_plot from (SELECT GROUP_CONCAT(plot_start_no) as additional_plot, road_no FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is null group by road_no) a right outer join (SELECT concat(plot_start_no,' To ',plot_end_no) as plot, road_no FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is not null) b on (a.road_no = b.road_no) order by abs(b.road_no)");
		}
		else if($result["plotting_pattern"]=='Series'){
			$stmt_plot_list = $obj->con1->prepare("SELECT b.plot, ifnull(a.additional_plot,'-') as additional_plot from (SELECT GROUP_CONCAT(plot_start_no) as additional_plot FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is null) a, (SELECT concat(plot_start_no,' To ',plot_end_no) as plot FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is not null) b");
		}

		$stmt_plot_list->bind_param("ii",$ind_estate_id,$ind_estate_id);
		$stmt_plot_list->execute();
		$plot_result = $stmt_plot_list->get_result();
		$stmt_plot_list->close();


		$html='<form  method="post"><div class="modal-body" ><div class="row">

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">State</label>
		<input type="text" class="form-control" name="state" id="state" value="'.$result["state_id"].'" readonly/>
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-company">City</label>
		<input type="text" class="form-control" name="city" id="city" value="'.$result["city_id"].'" readonly/>
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-company">Taluka</label>
		<input type="text" class="form-control" name="taluka" id="taluka" value="'.$result["taluka"].'" readonly/>
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-company">Area</label>
		<input type="text" class="form-control" name="area" id="area" value="'.$result["area_id"].'" readonly/>
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-company">Industrial Estate</label>
		<input type="text" class="form-control" name="ind_estate" id="ind_estate" value="'.$result["industrial_estate"].'" readonly/>
		</div>
		<div class="col mb-3"></div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-company">Plotting Pattern</label>
		<input type="text" class="form-control" name="plotting_pattern" id="plotting_pattern" value="'.$result["plotting_pattern"].'" readonly/>
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-company">Total Plots</label>
		<input type="text" class="form-control" name="total_plots" id="total_plots" value="'.$result_sum["total"].'" readonly/>
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-company">Status</label>
		<input type="text" class="form-control" name="status" id="status" value="'.$result["status"].'" readonly/>
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-company">Added By</label>
		<input type="text" class="form-control" name="added_by" id="added_by" value="'.ucwords(strtolower($result["name"])).'" readonly/>
		</div>
		</div>

		<div class="mb-3">
		<div class="card">
		<div class="card-body">
		<div class="table-responsive text-nowrap">
		<table class="table table-bordered">
		<thead>';
		if($result["plotting_pattern"]=='Road'){
			$html.= '<tr>
			<th>Road No.</th>
			<th>Plots</th>
			<th>Additional Plots</th>
			</tr>';
		}
		else{ 
			$html.= '<tr>
			<th>Plots</th>
			<th>Additional Plots</th>
			</tr>'; 
		} 
		$html.='</thead>
		<tbody>';

		$old_roadno = "";
		$new_roadno = "";
		while($plot_list=mysqli_fetch_array($plot_result)){ 

			if($result["plotting_pattern"]=='Road'){
				$new_roadno = $plot_list["road_no"];
				$html.='<tr>
				<td>'.$plot_list["road_no"].'</td>
				<td>'.$plot_list["plot"].'</td>';
				if($old_roadno==$new_roadno){
					$html.='<td>-</td>	';
				}
				else{
					$html.='<td>'.$plot_list["additional_plot"].'</td>';	
				}

				$html.='</tr>';
				$old_roadno = $new_roadno;
			}
			else{
				$html.='<tr>
				<td>'.$plot_list["plot"].'</td>
				<td>'.$plot_list["additional_plot"].'</td>
				</tr>';
			}
		}
		$html.='</tbody>
		</table>
		</div>
		</div>
		</div>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Image</label>';
		while($image_list=mysqli_fetch_array($image_result)){  
			$html.='<img src="industrial_estate_image/'.$image_list["image"].'" name="img" id="img" width="100" height="100" style="display:block;"><br/>';
		}
		$html.='</div>';     

		$html.='<div class="modal-footer">
		<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
		Close
		</button>
		</form>
		</div>';

		echo $html;
	}
	// estate_status_report
		if($_REQUEST['action']=="getPlotStatus")
	{	
		$html="";
		$estate_id=$_REQUEST['estate_id'];

		// check if plotting is done or not
		$stmt = $obj->con1->prepare("SELECT plotting_pattern FROM `pr_add_industrialestate_details` WHERE industrial_estate_id=? and plotting_pattern is not null");
		$stmt->bind_param("i",$estate_id);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();

		if(mysqli_num_rows($res)>0){
			// check if estate is assigned or unassigned
			$stmt_assign = $obj->con1->prepare("SELECT * from assign_estate where industrial_estate_id=? and action='company_entry'");
			$stmt_assign->bind_param("i",$estate_id);
			$stmt_assign->execute();
			$res_assign = $stmt_assign->get_result();
			$stmt_assign->close();

			if(mysqli_num_rows($res_assign)>0){
				// assigned -> show details of plot and assigned employees
				$stmt_assigned_emp_list = $obj->con1->prepare("SELECT a1.start_dt, a1.end_dt, GROUP_CONCAT(u1.name) as emp_names from assign_estate a1, tbl_users u1 where a1.employee_id=u1.id and action='company_entry' and a1.industrial_estate_id=? order by a1.id desc");
				$stmt_assigned_emp_list->bind_param("i",$estate_id);
				$stmt_assigned_emp_list->execute();
				$assigned_emp_result = $stmt_assigned_emp_list->get_result()->fetch_assoc();
				$stmt_assigned_emp_list->close();
				$assigned_emp = array();

				$html.='<div class="mb-3" >
				<label class="form-label" for="basic-default-fullname">Placed in Section -> <strong>Assigned Estate (For Company)</strong></label>
				</div>

				<div class="mb-3" >
				<label class="form-label" for="basic-default-fullname">Employees</label>
				<input type="text" class="form-control" name="emp_list" id="emp_list" value="'.$assigned_emp_result["emp_names"].'" readonly />
				</div>

				<div class="mb-3">
				<label class="form-label" for="basic-default-fullname">Start Date</label>
				<input type="date" class="form-control" name="start_date_modal" id="start_date_modal" max="9999-12-31" value="'.$assigned_emp_result["start_dt"].'" readonly />
				</div>

				<div class="mb-3">
				<label class="form-label" for="basic-default-fullname">End Date</label>
				<input type="date" class="form-control" name="end_date_modal" id="end_date_modal" max="9999-12-31" value="'.$assigned_emp_result["end_dt"].'" readonly />
				</div>';
			}
			else{ 
				// unassigned -> show details of plot only
				$html.='<div class="mb-3" >
				<label class="form-label" for="basic-default-fullname">Placed in Section -> <strong>Unassigned Estate (For Company)</strong></label>
				</div>';
			}
			$stmt_list = $obj->con1->prepare("SELECT i1.state_id, i1.city_id, i1.taluka, i1.area_id, i1.industrial_estate, d1.plotting_pattern, ifnull(d1.status,'-') as status, i1.id as industrial_estate_id from pr_add_industrialestate_details d1, tbl_industrial_estate i1 where d1.industrial_estate_id=i1.id and i1.id=? group by industrial_estate_id");
			$stmt_list->bind_param("i",$estate_id);
			$stmt_list->execute();
			$result = $stmt_list->get_result()->fetch_assoc();
			$stmt_list->close();

			$stmt_sum = $obj->con1->prepare("SELECT count(plot_no) as total from pr_company_plots where floor='0' and industrial_estate_id=?");
			$stmt_sum->bind_param("i",$estate_id);
			$stmt_sum->execute();
			$result_sum = $stmt_sum->get_result()->fetch_assoc();
			$stmt_sum->close();

			$stmt_image = $obj->con1->prepare("SELECT image FROM `pr_estate_subimages` WHERE industrial_estate_id=?");
			$stmt_image->bind_param("i",$estate_id);
			$stmt_image->execute();
			$image_result = $stmt_image->get_result();
			$stmt_image->close();

			if($result["plotting_pattern"]=='Road'){
				$stmt_plot_list = $obj->con1->prepare("SELECT b.road_no, b.plot, ifnull(a.additional_plot,'-') as additional_plot from (SELECT GROUP_CONCAT(plot_start_no) as additional_plot, road_no FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is null group by road_no) a right outer join (SELECT concat(plot_start_no,' To ',plot_end_no) as plot, road_no FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is not null) b on (a.road_no = b.road_no) order by abs(b.road_no)");
			}
			else if($result["plotting_pattern"]=='Series'){
				$stmt_plot_list = $obj->con1->prepare("SELECT b.plot, ifnull(a.additional_plot,'-') as additional_plot from (SELECT GROUP_CONCAT(plot_start_no) as additional_plot FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is null) a, (SELECT concat(plot_start_no,' To ',plot_end_no) as plot FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is not null) b");
			}

			$stmt_plot_list->bind_param("ii",$estate_id,$estate_id);
			$stmt_plot_list->execute();
			$plot_result = $stmt_plot_list->get_result();
			$stmt_plot_list->close();

			$html.='<div class="row">
			<div class="row">
			<div class="col mb-3">
			<input type="hidden" class="form-control" name="insert_type" id="insert_type" value="only_status"/>
			<label class="form-label" for="basic-default-fullname">State</label>
			<input type="text" class="form-control" name="state" id="state" value="'.$result["state_id"].'" readonly/>
			</div>

			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">City</label>
			<input type="text" class="form-control" name="city" id="city" value="'.$result["city_id"].'" readonly/>
			</div>
			</div>

			<div class="row">
			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Taluka</label>
			<input type="text" class="form-control" name="taluka" id="taluka" value="'.$result["taluka"].'" readonly/>
			</div>

			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Area</label>
			<input type="text" class="form-control" name="area" id="area" value="'.$result["area_id"].'" readonly/>
			</div>
			</div>

			<div class="row">
			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Industrial Estate</label>
			<input type="text" class="form-control" name="ind_estate" id="ind_estate" value="'.$result["industrial_estate"].'" readonly/>
			</div>
			<div class="col mb-3"></div>
			</div>

			<div class="row">
			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Plotting Pattern</label>
			<input type="text" class="form-control" name="plotting_pattern" id="plotting_pattern" value="'.$result["plotting_pattern"].'" readonly/>
			</div>

			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Total Plots</label>
			<input type="text" class="form-control" name="total_plots" id="total_plots" value="'.$result_sum["total"].'" readonly/>
			</div>
			</div>

			<div class="row">
			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Status</label>
			<input type="text" class="form-control" name="status" id="status" value="'.$result["status"].'" readonly/>
			</div>
			<div class="col mb-3"></div>
			</div>

			<div class="mb-3">
			<div class="card">
			<div class="card-body">
			<div class="table-responsive text-nowrap">
			<table class="table table-bordered">
			<thead>';
			if($result["plotting_pattern"]=='Road'){
				$html.='<tr>
				<th>Road No.</th>
				<th>Plots</th>
				<th>Additional Plots</th>
				</tr>';
			}
			else{ 
				$html.='<tr>
				<th>Plots</th>
				<th>Additional Plots</th>
				</tr>'; 
			} 
			$html.='</thead>
			<tbody>';

			$old_roadno = "";
			$new_roadno = "";

			while($plot_list=mysqli_fetch_array($plot_result)){
				if($result["plotting_pattern"]=='Road'){
					$new_roadno = $plot_list["road_no"];
					$html.='<tr>
					<td>'.$plot_list["road_no"].'</td>
					<td>'.$plot_list["plot"].'</td>';
					if($old_roadno==$new_roadno){
						$html.='<td>-</td>	';
					}
					else{
						$html.='<td>'.$plot_list["additional_plot"].'</td>';	
					}

					$html.='</tr>';
					$old_roadno = $new_roadno;
				}
				else{
					$html.='<tr>
					<td>'.$plot_list["plot"].'</td>
					<td>'.$plot_list["additional_plot"].'</td>
					</tr>';
				}
			}
			$html.='</tbody>
			</table>
			</div>
			</div>
			</div>
			</div>

			<div class="mb-3">
			<label class="form-label" for="basic-default-fullname">Image</label>';
			while($image_list=mysqli_fetch_array($image_result)){  
				$html.='<img src="industrial_estate_image/'.$image_list["image"].'" name="img" id="img" width="100" height="100" style="display:block;"><br/>';
			}
			$html.='</div>'; 
		}
		else{
			// option for assign estate
			$stmt_emp_list = $obj->con1->prepare("SELECT * FROM tbl_users where (LOWER(role)='assignor/verifier' or LOWER(role)='worker')");
			$stmt_emp_list->execute();
			$emp_result = $stmt_emp_list->get_result();
			$stmt_emp_list->close();
			
			$html='<div class="mb-3" >
				<label class="form-label" for="basic-default-fullname">Assign Estate (For Plotting) :-</label>
				</div>

			<input type="hidden" class="form-control" name="industrial_estate_id" id="industrial_estate_id" value="'.$estate_id.'"/>
			<input type="hidden" class="form-control" name="insert_type" id="insert_type" value="assign_estate"/>

			<div class="mb-3" >
			<label class="form-label" for="basic-default-fullname">Employees</label>
			<select name="e[]" id="emp_list" class="form-control js-example-basic-multiple" multiple="multiple" required>';
			while($emp=mysqli_fetch_array($emp_result)){
				$html.='<option value="'.$emp["id"].'">'.$emp["name"].'</option>';
			}
			$html.='</select>
			</div>

			<div class="mb-3">
			<label class="form-label" for="basic-default-fullname">Start Date</label>
			<input type="date" class="form-control" name="start_date" id="start_date" value="'.date("Y-m-d").'" max="9999-12-31" required />
			</div>

			<div class="mb-3">
			<label class="form-label" for="basic-default-fullname">End Date</label>
			<input type="date" class="form-control" name="end_date" id="end_date" max="9999-12-31" required />';


			/*
			// option for plotting
			$html="plotting pending";
			$stmt_estate = $obj->con1->prepare("SELECT * FROM tbl_industrial_estate where id=?");
			$stmt_estate->bind_param("i",$estate_id);
			$stmt_estate->execute();
			$estate_result = $stmt_estate->get_result()->fetch_assoc();
			$stmt_estate->close();

			$html='<input type="hidden" class="form-control" name="ind_estate_id" id="ind_estate_id" value="'.$estate_id.'"/>
			<input type="hidden" class="form-control" name="insert_type" id="insert_type" value="plotting_and_status"/>

			<div class="row">
			<div class="col mb-3" >
			<label class="form-label" for="basic-default-fullname">State : '.$estate_result["state_id"].'</label>
			<input type="hidden" class="form-control" name="state" id="state" value="'.$estate_result["state_id"].'"/>
			</div>
			<div class="col mb-3" >
			<label class="form-label" for="basic-default-fullname">City : '.$estate_result["city_id"].'</label>
			<input type="hidden" class="form-control" name="city" id="city" value="'.$estate_result["city_id"].'"/>
			</div>
			</div>
			<div class="row">
			<div class="col mb-3" >
			<label class="form-label" for="basic-default-fullname">Taluka : '.$estate_result["taluka"].'</label>
			<input type="hidden" class="form-control" name="taluka" id="taluka" value="'.$estate_result["taluka"].'"/>
			</div>
			<div class="col mb-3" >
			<label class="form-label" for="basic-default-fullname">Area : '.$estate_result["area_id"].'</label>
			<input type="hidden" class="form-control" name="area" id="area" value="'.$estate_result["area_id"].'"/>
			</div>
			</div>
			<div class="mb-3" >
			<label class="form-label" for="basic-default-fullname">Industrial Estate : '.$estate_result["industrial_estate"].'</label>
			<input type="hidden" class="form-control" name="industrial_estate" id="industrial_estate" value="'.$estate_result["industrial_estate"].'"/>
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
			<input type="text" class="form-control" pattern="^[0-9]*$" name="from_roadno" id="from_roadno" onblur="get_plot_adding_options()" required />
			</div>  
			<div class="col mb-3">
			<label class="form-label" for="basic-default-fullname">To (Road No.)</label>
			<input type="text" class="form-control" pattern="^[0-9]*$" name="to_roadno" id="to_roadno" onblur="get_plot_adding_options()" required />
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
			</div>'; */
		}

		echo $html;
	}


	// estate_status_report (not in use) (to get plotting option if selected verified)
/*	if($_REQUEST['action']=="getPlotStatus")
	{	
		$html="";
		$estate_id=$_REQUEST['estate_id'];

		// check if plotting is done or not
		$stmt = $obj->con1->prepare("SELECT plotting_pattern FROM `pr_add_industrialestate_details` WHERE industrial_estate_id=? and plotting_pattern is not null");
		$stmt->bind_param("i",$estate_id);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();

		if(mysqli_num_rows($res)>0){
			// check if estate is assigned or unassigned
			$stmt_assign = $obj->con1->prepare("SELECT * from assign_estate where industrial_estate_id=? and action='company_entry'");
			$stmt_assign->bind_param("i",$estate_id);
			$stmt_assign->execute();
			$res_assign = $stmt_assign->get_result();
			$stmt_assign->close();

			if(mysqli_num_rows($res_assign)>0){
				// assigned -> show details of plot and assigned employees
				$stmt_assigned_emp_list = $obj->con1->prepare("SELECT a1.start_dt, a1.end_dt, GROUP_CONCAT(u1.name) as emp_names from assign_estate a1, tbl_users u1 where a1.employee_id=u1.id and action='company_entry' and a1.industrial_estate_id=? order by a1.id desc");
				$stmt_assigned_emp_list->bind_param("i",$estate_id);
				$stmt_assigned_emp_list->execute();
				$assigned_emp_result = $stmt_assigned_emp_list->get_result()->fetch_assoc();
				$stmt_assigned_emp_list->close();
				$assigned_emp = array();

				$html.='<div class="mb-3" >
				<label class="form-label" for="basic-default-fullname">Placed in Section -> <strong>Assigned Estate (For Company)</strong></label>
				</div>

				<div class="mb-3" >
				<label class="form-label" for="basic-default-fullname">Employees</label>
				<input type="text" class="form-control" name="emp_list" id="emp_list" value="'.$assigned_emp_result["emp_names"].'" readonly />
				</div>

				<div class="mb-3">
				<label class="form-label" for="basic-default-fullname">Start Date</label>
				<input type="date" class="form-control" name="start_date_modal" id="start_date_modal" max="9999-12-31" value="'.$assigned_emp_result["start_dt"].'" readonly />
				</div>

				<div class="mb-3">
				<label class="form-label" for="basic-default-fullname">End Date</label>
				<input type="date" class="form-control" name="end_date_modal" id="end_date_modal" max="9999-12-31" value="'.$assigned_emp_result["end_dt"].'" readonly />
				</div>';
			}
			else{ 
				// unassigned -> show details of plot only
				$html.='<div class="mb-3" >
				<label class="form-label" for="basic-default-fullname">Placed in Section -> <strong>Unassigned Estate (For Company)</strong></label>
				</div>';
			}
			$stmt_list = $obj->con1->prepare("SELECT i1.state_id, i1.city_id, i1.taluka, i1.area_id, i1.industrial_estate, d1.plotting_pattern, ifnull(d1.status,'-') as status, i1.id as industrial_estate_id from pr_add_industrialestate_details d1, tbl_industrial_estate i1 where d1.industrial_estate_id=i1.id and i1.id=? group by industrial_estate_id");
			$stmt_list->bind_param("i",$estate_id);
			$stmt_list->execute();
			$result = $stmt_list->get_result()->fetch_assoc();
			$stmt_list->close();

			$stmt_sum = $obj->con1->prepare("SELECT count(plot_no) as total from pr_company_plots where floor='0' and industrial_estate_id=?");
			$stmt_sum->bind_param("i",$estate_id);
			$stmt_sum->execute();
			$result_sum = $stmt_sum->get_result()->fetch_assoc();
			$stmt_sum->close();

			$stmt_image = $obj->con1->prepare("SELECT image FROM `pr_estate_subimages` WHERE industrial_estate_id=?");
			$stmt_image->bind_param("i",$estate_id);
			$stmt_image->execute();
			$image_result = $stmt_image->get_result();
			$stmt_image->close();

			if($result["plotting_pattern"]=='Road'){
				$stmt_plot_list = $obj->con1->prepare("SELECT b.road_no, b.plot, ifnull(a.additional_plot,'-') as additional_plot from (SELECT GROUP_CONCAT(plot_start_no) as additional_plot, road_no FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is null group by road_no) a right outer join (SELECT concat(plot_start_no,' To ',plot_end_no) as plot, road_no FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is not null) b on (a.road_no = b.road_no) order by abs(b.road_no)");
			}
			else if($result["plotting_pattern"]=='Series'){
				$stmt_plot_list = $obj->con1->prepare("SELECT b.plot, ifnull(a.additional_plot,'-') as additional_plot from (SELECT GROUP_CONCAT(plot_start_no) as additional_plot FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is null) a, (SELECT concat(plot_start_no,' To ',plot_end_no) as plot FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is not null) b");
			}

			$stmt_plot_list->bind_param("ii",$estate_id,$estate_id);
			$stmt_plot_list->execute();
			$plot_result = $stmt_plot_list->get_result();
			$stmt_plot_list->close();

			$html.='<div class="row">
			<div class="row">
			<div class="col mb-3">
			<input type="hidden" class="form-control" name="insert_type" id="insert_type" value="only_status"/>
			<label class="form-label" for="basic-default-fullname">State</label>
			<input type="text" class="form-control" name="state" id="state" value="'.$result["state_id"].'" readonly/>
			</div>

			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">City</label>
			<input type="text" class="form-control" name="city" id="city" value="'.$result["city_id"].'" readonly/>
			</div>
			</div>

			<div class="row">
			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Taluka</label>
			<input type="text" class="form-control" name="taluka" id="taluka" value="'.$result["taluka"].'" readonly/>
			</div>

			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Area</label>
			<input type="text" class="form-control" name="area" id="area" value="'.$result["area_id"].'" readonly/>
			</div>
			</div>

			<div class="row">
			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Industrial Estate</label>
			<input type="text" class="form-control" name="ind_estate" id="ind_estate" value="'.$result["industrial_estate"].'" readonly/>
			</div>
			<div class="col mb-3"></div>
			</div>

			<div class="row">
			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Plotting Pattern</label>
			<input type="text" class="form-control" name="plotting_pattern" id="plotting_pattern" value="'.$result["plotting_pattern"].'" readonly/>
			</div>

			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Total Plots</label>
			<input type="text" class="form-control" name="total_plots" id="total_plots" value="'.$result_sum["total"].'" readonly/>
			</div>
			</div>

			<div class="row">
			<div class="col mb-3">
			<label class="form-label" for="basic-default-company">Status</label>
			<input type="text" class="form-control" name="status" id="status" value="'.$result["status"].'" readonly/>
			</div>
			<div class="col mb-3"></div>
			</div>

			<div class="mb-3">
			<div class="card">
			<div class="card-body">
			<div class="table-responsive text-nowrap">
			<table class="table table-bordered">
			<thead>';
			if($result["plotting_pattern"]=='Road'){
				$html.='<tr>
				<th>Road No.</th>
				<th>Plots</th>
				<th>Additional Plots</th>
				</tr>';
			}
			else{ 
				$html.='<tr>
				<th>Plots</th>
				<th>Additional Plots</th>
				</tr>'; 
			} 
			$html.='</thead>
			<tbody>';

			$old_roadno = "";
			$new_roadno = "";

			while($plot_list=mysqli_fetch_array($plot_result)){
				if($result["plotting_pattern"]=='Road'){
					$new_roadno = $plot_list["road_no"];
					$html.='<tr>
					<td>'.$plot_list["road_no"].'</td>
					<td>'.$plot_list["plot"].'</td>';
					if($old_roadno==$new_roadno){
						$html.='<td>-</td>	';
					}
					else{
						$html.='<td>'.$plot_list["additional_plot"].'</td>';	
					}

					$html.='</tr>';
					$old_roadno = $new_roadno;
				}
				else{
					$html.='<tr>
					<td>'.$plot_list["plot"].'</td>
					<td>'.$plot_list["additional_plot"].'</td>
					</tr>';
				}
			}
			$html.='</tbody>
			</table>
			</div>
			</div>
			</div>
			</div>

			<div class="mb-3">
			<label class="form-label" for="basic-default-fullname">Image</label>';
			while($image_list=mysqli_fetch_array($image_result)){  
				$html.='<img src="industrial_estate_image/'.$image_list["image"].'" name="img" id="img" width="100" height="100" style="display:block;"><br/>';
			}
			$html.='</div>'; 
		}
		else{
			// option for plotting
			$html="plotting pending";
			$stmt_estate = $obj->con1->prepare("SELECT * FROM tbl_industrial_estate where id=?");
			$stmt_estate->bind_param("i",$estate_id);
			$stmt_estate->execute();
			$estate_result = $stmt_estate->get_result()->fetch_assoc();
			$stmt_estate->close();

			$html='<input type="hidden" class="form-control" name="ind_estate_id" id="ind_estate_id" value="'.$estate_id.'"/>
			<input type="hidden" class="form-control" name="insert_type" id="insert_type" value="plotting_and_status"/>

			<div class="row">
			<div class="col mb-3" >
			<label class="form-label" for="basic-default-fullname">State : '.$estate_result["state_id"].'</label>
			<input type="hidden" class="form-control" name="state" id="state" value="'.$estate_result["state_id"].'"/>
			</div>
			<div class="col mb-3" >
			<label class="form-label" for="basic-default-fullname">City : '.$estate_result["city_id"].'</label>
			<input type="hidden" class="form-control" name="city" id="city" value="'.$estate_result["city_id"].'"/>
			</div>
			</div>
			<div class="row">
			<div class="col mb-3" >
			<label class="form-label" for="basic-default-fullname">Taluka : '.$estate_result["taluka"].'</label>
			<input type="hidden" class="form-control" name="taluka" id="taluka" value="'.$estate_result["taluka"].'"/>
			</div>
			<div class="col mb-3" >
			<label class="form-label" for="basic-default-fullname">Area : '.$estate_result["area_id"].'</label>
			<input type="hidden" class="form-control" name="area" id="area" value="'.$estate_result["area_id"].'"/>
			</div>
			</div>
			<div class="mb-3" >
			<label class="form-label" for="basic-default-fullname">Industrial Estate : '.$estate_result["industrial_estate"].'</label>
			<input type="hidden" class="form-control" name="industrial_estate" id="industrial_estate" value="'.$estate_result["industrial_estate"].'"/>
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
			<input type="text" class="form-control" pattern="^[0-9]*$" name="from_roadno" id="from_roadno" onblur="get_plot_adding_options()" required />
			</div>  
			<div class="col mb-3">
			<label class="form-label" for="basic-default-fullname">To (Road No.)</label>
			<input type="text" class="form-control" pattern="^[0-9]*$" name="to_roadno" id="to_roadno" onblur="get_plot_adding_options()" required />
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
			</div>';
		}

		echo $html;
	}*/

	if($_REQUEST["action"]=="estate_locations")
	{
		$stmt_estate = $obj->con1->prepare("SELECT d1.id,e1.industrial_estate,d1.location FROM `pr_add_industrialestate_details` d1,tbl_industrial_estate e1 where d1.industrial_estate_id=e1.id and  d1.location!='' and d1.location!='null,null'");
		$stmt_estate->execute();
		$estate_result = $stmt_estate->get_result();
		$stmt_estate->close();
		$data=array();

		while($ind_estate=mysqli_fetch_array($estate_result))
		{	

			$temp=array();
			$location=explode(",",$ind_estate["location"]);
			array_push($temp,$ind_estate["industrial_estate"]);
			array_push($temp,$location[0]);
			array_push($temp,$location[1]);
			array_push($data,$temp);

		}
		echo json_encode($data);
	}

	// pr_file_format
	if($_REQUEST['action']=="get_stage")
	{	
		$html="";
		$service_id=$_REQUEST["scheme_id"];

		$stmt_stage_list = $obj->con1->prepare("SELECT * FROM `tbl_tdstages` WHERE service_id=?");
		$stmt_stage_list->bind_param("i",$service_id);
		$stmt_stage_list->execute();
		$stage_result = $stmt_stage_list->get_result();
		$stmt_stage_list->close();

		$html='<option value="">Select Stage</option>';
		while($stage_list = mysqli_fetch_array($stage_result)){
			$html.='<option value="'.$stage_list["stage_id"].'">'.$stage_list["stage_name"].'</option>';
		}

		echo $html;
	}

	// company_add_plot
	if($_REQUEST['action']=="get_company_name")
	{	
		$html="";
		$ind_estate_id=$_REQUEST["ind_estate_id"];

		$stmt_firm_list = $obj->con1->prepare("SELECT r1.id, json_unquote(r1.raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(r1.raw_data->'$.post_fields.Contact_Name') as contact_name, json_unquote(r1.raw_data->'$.post_fields.Mobile_No') as mobile_no from tbl_tdrawdata r1, tbl_industrial_estate i1 where r1.raw_data->'$.post_fields.Taluka'=i1.taluka and r1.raw_data->'$.post_fields.Area'=i1.area_id and r1.raw_data->'$.post_fields.IndustrialEstate'=i1.industrial_estate and JSON_CONTAINS_PATH(raw_data, 'one', '$.plot_details') = 0 and raw_data->'$.post_fields.IndustrialEstate'!='' and i1.id=?");
		$stmt_firm_list->bind_param("i",$ind_estate_id);
		$stmt_firm_list->execute();
		$firm_result = $stmt_firm_list->get_result();
		$stmt_firm_list->close();

		$html='<option value="">Select Firm</option>';
		while($firm_list = mysqli_fetch_array($firm_result)){
			$html.='<option value="'.$firm_list["id"].'">'.$firm_list["firm_name"].' ( '.$firm_list["contact_name"].' - '.$firm_list["mobile_no"].' )</option>';
		}

		echo $html;	
	}

	// company_add_plot
	if($_REQUEST['action']=="getPlot_companyPlot_est")
	{	
		$html="";
		$estate_id=$_REQUEST['estate_id'];

		$stmt_estate = $obj->con1->prepare("SELECT a1.plotting_pattern, a1.status FROM pr_add_industrialestate_details a1 where a1.industrial_estate_id=?");
		$stmt_estate->bind_param("i",$estate_id);
		$stmt_estate->execute();
		$estate_result = $stmt_estate->get_result();
		$stmt_estate->close();

		$submit_status = true;

		if(mysqli_num_rows($estate_result)>0){
            $estate_res = mysqli_fetch_array($estate_result);
            if($estate_res['status']=='Verified'){
              $plotting_pattern = $estate_res['plotting_pattern'];
		$html.='<div class="row">
                  <div class="mb-3" id="road_list_div_est" '.(($plotting_pattern=="Road")?"":"hidden").'>
                    <label class="form-label" for="road_no_est">Road No.</label>
                    <select name="road_no_est" id="road_no_est" class="form-control" onchange="getRoadPlots_companyPlot(this.value,industrial_estate_id_est.value)" required>
                      <option value="">Select Road No.</option>';
            
            if($plotting_pattern=="Road"){
                $stmt_road = $obj->con1->prepare("SELECT DISTINCT(road_no) FROM `pr_estate_roadplot` WHERE industrial_estate_id=? order by abs(road_no)");
                $stmt_road->bind_param("i",$estate_id);
                $stmt_road->execute();
                $road_res = $stmt_road->get_result();
                $stmt_road->close();

                while($road = mysqli_fetch_array($road_res)){
                    $html.='<option value="'.$road["road_no"].'">'.$road["road_no"].'</option>';    
            } }
                    $html.='</select>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="plot_no_est">Plot No.</label>
                  <select name="plot_no_est" id="plot_no_est" onchange="getFloor_companyPlot(this.value,industrial_estate_id_est.value)" class="form-control" required>
                    <option value="">Select Plot No.</option>';
                if($plotting_pattern=="Series"){
                    /*$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(plot_no) FROM `pr_company_plots` WHERE industrial_estate_id=? and company_id IS NULL order by abs(plot_no)");*/
                    $stmt_plot = $obj->con1->prepare("SELECT DISTINCT(plot_no) FROM `pr_company_plots` WHERE industrial_estate_id=? order by abs(plot_no)");
                    $stmt_plot->bind_param("i",$estate_id);
                    $stmt_plot->execute();
                    $plot_res = $stmt_plot->get_result();
                    $stmt_plot->close();
                    while($plot = mysqli_fetch_array($plot_res)){
                    $html.='<option value="'.$plot['plot_no'].'">'.$plot['plot_no'].'</option>';
            } }
                  $html.='</select>
                </div>

                <input type="hidden" class="form-control" name="plotting_pattern_est" id="plotting_pattern_est" value="'.$plotting_pattern.'"/>

                <div class="mb-3">
                  <label class="form-label" for="floor_est">Floor No.</label>
                  <select name="floor_est" id="floor_est" class="form-control" required>
                    <option value="">Select Floor</option>
                  </select>
                </div>';


        } else{
        	$submit_status = false;
                $html.='<div id="estate_alert_div_est" class="text-danger">Industrial Estate is '.$estate_res["status"].'</div><br/>';
        } } else{
        	$submit_status = false;
                $html.='<div id="estate_alert_div_est" class="text-danger">Please Enter Plotting First</div><br/>';
        }

        echo $html."@@@@@".$submit_status;
	}

	// company_add_plot
	if($_REQUEST['action']=="getPlot_companyPlot_comp")
	{	
		$html="";
		$estate_id=$_REQUEST['estate_id'];

		$submit_status = true;

		$stmt_estate = $obj->con1->prepare("SELECT a1.plotting_pattern, a1.status FROM pr_add_industrialestate_details a1 where a1.industrial_estate_id=?");
		$stmt_estate->bind_param("i",$estate_id);
		$stmt_estate->execute();
		$estate_result = $stmt_estate->get_result();
		$stmt_estate->close();

		if(mysqli_num_rows($estate_result)>0){
            $estate_res = mysqli_fetch_array($estate_result);
            if($estate_res['status']=='Verified'){
              $plotting_pattern = $estate_res['plotting_pattern'];
		$html.='<div class="row">
                  <div class="mb-3" id="road_list_div_comp" '.($plotting_pattern=="Road")?"":"hidden".'>
                    <label class="form-label" for="road_no_comp">Road No.</label>
                    <select name="road_no_comp" id="road_no_comp" class="form-control" onchange="getRoadPlots_companyPlot(this.value,industrial_estate_id_comp.value)" required>
                      <option value="">Select Road No.</option>';
            
            if($plotting_pattern=="Road"){
                $stmt_road = $obj->con1->prepare("SELECT DISTINCT(road_no) FROM `pr_estate_roadplot` WHERE industrial_estate_id=? order by abs(road_no)");
                $stmt_road->bind_param("i",$estate_id);
                $stmt_road->execute();
                $road_res = $stmt_road->get_result();
                $stmt_road->close();

                while($road = mysqli_fetch_array($road_res)){
                    $html.='<option value="'.$road["road_no"].'">'.$road["road_no"].'</option>';    
            } }
                    $html.='</select>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="plot_no_comp">Plot No.</label>
                  <select name="plot_no_comp" id="plot_no_comp" onchange="getFloor_companyPlot(this.value,industrial_estate_id_comp.value)" class="form-control" required>
                    <option value="">Select Plot No.</option>';
                if($plotting_pattern=="Series"){
                    $stmt_plot = $obj->con1->prepare("SELECT DISTINCT(plot_no) FROM `pr_company_plots` WHERE industrial_estate_id=? and company_id IS NULL order by abs(plot_no)");
                    $stmt_plot->bind_param("i",$estate_id);
                    $stmt_plot->execute();
                    $plot_res = $stmt_plot->get_result();
                    $stmt_plot->close();
                    while($plot = mysqli_fetch_array($plot_res)){
                    $html.='<option value="'.$plot['plot_no'].'">'.$plot['plot_no'].'</option>';
            } }
                  $html.='</select>
                </div>

                <input type="hidden" class="form-control" name="plotting_pattern_comp" id="plotting_pattern_comp" value="'.$plotting_pattern.'"/>

                <div class="mb-3">
                  <label class="form-label" for="floor_comp">Floor No.</label>
                  <select name="floor_comp" id="floor_comp" class="form-control" required>
                    <option value="">Select Floor</option>
                  </select>
                </div>';


        } else{
        	$submit_status=false;
                $html.='<div id="estate_alert_div_comp" class="text-danger">Industrial Estate is '.$estate_res["status"].'</div><br/>';
        } } else{
        	$submit_status=false;
                $html.='<div id="estate_alert_div_comp" class="text-danger">Please Enter Plotting First</div><br/>';
        }

        echo $html."@@@@@".$submit_status;
	}

	// company_add_plot
	if($_REQUEST['action']=="getRoadPlots_companyPlot")
	{	
		$html="";
		$estate_id=$_REQUEST['estate_id'];
		$road_no=$_REQUEST['road_no'];
		$plot_array = array();

		// $stmt_plot = $obj->con1->prepare("SELECT DISTINCT(plot_no) FROM `pr_company_plots` WHERE industrial_estate_id=? and road_no=? and company_id IS NULL order by abs(plot_no)");
		$stmt_plot = $obj->con1->prepare("SELECT DISTINCT(plot_no) FROM `pr_company_plots` WHERE industrial_estate_id=? and road_no=? order by abs(plot_no)");
        $stmt_plot->bind_param("is",$estate_id,$road_no);
        $stmt_plot->execute();
        $plot_res = $stmt_plot->get_result();
        $stmt_plot->close();

        $html='<option value="">Select Plot No.</option>';
        while($plot = mysqli_fetch_array($plot_res)){
        	$html.='<option value="'.$plot['plot_no'].'">'.$plot['plot_no'].'</option>';
        }

		echo $html;
	}

	// company_add_plot
	if($_REQUEST['action']=="getFloor_companyPlot")
	{	
  		$html="";
		$estate_id=$_REQUEST['estate_id'];
		$plot_no=$_REQUEST['plot_no'];
		$road_no=$_REQUEST['road_no'];
		$floor_array = array();
		$id ="";

		echo "SELECT a1.plotting_pattern FROM pr_add_industrialestate_details a1 where a1.industrial_estate_id=".$estate_id;
		$stmt_estate = $obj->con1->prepare("SELECT a1.plotting_pattern FROM pr_add_industrialestate_details a1 where a1.industrial_estate_id=?");
		$stmt_estate->bind_param("i",$estate_id);
		$stmt_estate->execute();
		$estate_result = $stmt_estate->get_result()->fetch_assoc();
		$stmt_estate->close();
		
		// to get floors whose company details is blank + other floors left
		if($estate_result['plotting_pattern']=='Series'){
			$stmt_floor = $obj->con1->prepare("SELECT all_numbers.number FROM ( SELECT 0 AS number UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 ) AS all_numbers LEFT JOIN ( SELECT floor FROM pr_company_plots WHERE industrial_estate_id='".$estate_id."' AND plot_no='".$plot_no."' and company_id is NOT null ) AS existing_numbers ON all_numbers.number = existing_numbers.floor WHERE existing_numbers.floor IS NULL order by abs(number)");
		}
		else if($estate_result['plotting_pattern']=='Road'){
			$stmt_floor = $obj->con1->prepare("SELECT all_numbers.number FROM ( SELECT 0 AS number UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 ) AS all_numbers LEFT JOIN ( SELECT floor FROM pr_company_plots WHERE industrial_estate_id='".$estate_id."' AND plot_no='".$plot_no."' and road_no='".$road_no."' and company_id is NOT null ) AS existing_numbers ON all_numbers.number = existing_numbers.floor WHERE existing_numbers.floor IS NULL order by abs(number)");
		}
		
		$stmt_floor->execute();
		$floor_res = $stmt_floor->get_result();
		$stmt_floor->close();

		if(mysqli_num_rows($floor_res)>0){
			while($floor=mysqli_fetch_array($floor_res)){
				if($floor["number"]=='0'){
					$html.='<option value="'.$floor["number"].'">Ground Floor</option>';
				}
				else{
					$html.='<option value="'.$floor["number"].'">'.$floor["number"].'</option>';
				}
			}	
		}
		else{
			$html.='<option value="">Select Floor No.</option>';
		}

		echo $html;
  	}

	// company_add_estate
	if($_REQUEST['action']=="cityList_tbl_indestate")
	{	
		$html="";
		$state=$_REQUEST['state'];

		$stmt = $obj->con1->prepare("SELECT DISTINCT(city_id) from tbl_industrial_estate where state_id=?");
		$stmt->bind_param("s",$state);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();

		$html='<option value="">Select City</option>';
		while($city=mysqli_fetch_array($res))
		{
			$html.='<option value="'.$city["city_id"].'">'.$city["city_id"].'</option>';
		}
		echo $html;
	}

	// company_add_estate
	if($_REQUEST['action']=="talukaList_tbl_indestate")
	{	
		$html="";
		$state=$_REQUEST['state'];
		$city=$_REQUEST['city'];

		$stmt = $obj->con1->prepare("SELECT DISTINCT(taluka) from tbl_industrial_estate where state_id=? and city_id=?");
		$stmt->bind_param("ss",$state,$city);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();

		$html='<option value="">Select Taluka</option>';
		while($taluka=mysqli_fetch_array($res))
		{
			$html.='<option value="'.$taluka["taluka"].'">'.$taluka["taluka"].'</option>';
		}
		echo $html;
	}

	// company_add_estate
	if($_REQUEST['action']=="areaList_tbl_indestate")
	{	
		$html="";
		$state=$_REQUEST['state'];
		$city=$_REQUEST['city'];
		$taluka=$_REQUEST['taluka'];

		$stmt = $obj->con1->prepare("SELECT DISTINCT(area_id) from tbl_industrial_estate where state_id=? and city_id=? and taluka=?");
		$stmt->bind_param("sss",$state,$city,$taluka);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();

		$html='<option value="">Select Area</option>';
		while($area=mysqli_fetch_array($res))
		{
			$html.='<option value="'.$area["area_id"].'">'.$area["area_id"].'</option>';
		}
		echo $html;
	}

	// company_add_estate
	if($_REQUEST['action']=="estateList_tbl_indestate")
	{	
		$html="";
		$state=$_REQUEST['state'];
		$city=$_REQUEST['city'];
		$taluka=$_REQUEST['taluka'];
		$area=$_REQUEST['area'];

		echo "SELECT id,industrial_estate from tbl_industrial_estate where state_id='".$state."' and city_id='".$city."' and taluka='".$taluka."' and area_id='".$area."'";
		$stmt = $obj->con1->prepare("SELECT id,industrial_estate from tbl_industrial_estate where state_id=? and city_id=? and taluka=? and area_id=?");
		$stmt->bind_param("ssss",$state,$city,$taluka,$area);
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();

		$html='<option value="">Select Industrial Estate</option>';
		while($estate=mysqli_fetch_array($res))
		{
			$html.='<option value="'.$estate["id"].'">'.$estate["industrial_estate"].'</option>';
		}
		echo $html;
	}

	// company_add_plot_est
	if($_REQUEST['action']=="company_plot_est_grid")
	{	
		$html="";
		$state=$_REQUEST['state'];
		$city=$_REQUEST['city'];
		$taluka=$_REQUEST['taluka'];
		$area=$_REQUEST['area'];
		$ind_estate=$_REQUEST['ind_estate'];
		$estate_id=$_REQUEST['estate_id'];
		$user_id=$_REQUEST['user_id'];

		$stmt = $obj->con1->prepare("SELECT r1.id, r1.raw_data->>'$.post_fields.Firm_Name' as firm_name, r1.raw_data->>'$.post_fields.Factory_Address' as factory_address, r1.raw_data->>'$.post_fields.Mobile_No' as mobile_no, (select stage END from tbl_tdrawassign where inq_id=r1.id order by id desc LIMIT 1) stage, (select CASE WHEN stage='lead' THEN 'Positive' WHEN stage='badlead' THEN 'Negative' ELSE 'Existing Client' END from tbl_tdrawassign where inq_id=r1.id order by id desc LIMIT 1) stage1 from tbl_tdrawdata r1 where r1.raw_data->'$.post_fields.Taluka'='".$taluka."' and r1.raw_data->'$.post_fields.Area'='".$area."' and r1.raw_data->'$.post_fields.IndustrialEstate'='".$ind_estate."' and JSON_CONTAINS_PATH(raw_data, 'one', '$.plot_details') = 0 and raw_data->'$.post_fields.IndustrialEstate'!='' and r1.id not in (SELECT rawdata_id from pr_company_details)");
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();

		$html='<div class="row">
					<div class="col mb-3"><label class="form-label">Taluka : '.$taluka.'</label></div>
                	<div class="col mb-3"><label class="form-label">Area : '.$area.'</label></div>
                	<div class="col mb-3"><label class="form-label">Industrial Estate : '.$ind_estate.'</label></div>
            </div>
			<div class="card">
      			<h5 class="card-header">Records</h5>
      			<div class="table-responsive text-nowrap">
        			<table class="table table-hover" id="table_modal_id">
			          <thead>
			            <tr>
			              <th>Srno</th>
			              <th>Company Name</th>
			              <th>Employee Name</th>
			              <th>Factory Address</th>
			              <th>Status</th>
			              <th>Action</th>
			            </tr>
			          </thead>
			          <tbody class="table-border-bottom-0">';
			          $i=1;
		while($data=mysqli_fetch_array($res))
	    {
	        if($data["stage"]=='applicationstart' || $data["stage"]=='schemesstarted')
	        {
	          $stmt_stage = $obj->con1->prepare("select a1.tatassign_id, a1.tatassign_status ,a1.tatassign_user_id,u1.name as emp_name from tbl_tdtatassign a1,tbl_users u1 where a1.tatassign_user_id=u1.id and  a1.tatassign_inq_id=?  order by a1.tatassign_id desc limit 1");
	          $stmt_stage->bind_param("i",$data["id"]);
	          $stmt_stage->execute();
	          $stage_result = $stmt_stage->get_result()->fetch_assoc();
	          $stmt_stage->close();
	          $emp_name=$stage_result["emp_name"];
	        }
	        else
	        {
	          $stmt_stage = $obj->con1->prepare("select r1.id, r1.stage ,r1.user_id,u1.name as emp_name from tbl_tdrawassign r1,tbl_users u1 where r1.user_id=u1.id and r1.inq_id=? order by r1.id desc limit 1; ");
	          $stmt_stage->bind_param("i",$data["id"]);
	          $stmt_stage->execute();
	          $stage_result = $stmt_stage->get_result()->fetch_assoc();
	          $stmt_stage->close();
	          $emp_name=$stage_result["emp_name"];

	        }
				$html.='<tr>
		              <td>'.$i.'</td>
		              <td>'.$data["firm_name"].'</td>
		              <td>'.$emp_name.'</td>
		              <td>'.$data["factory_address"].'</td>
		              <td>';
		                  $html.=$data["stage1"];
		            $html.='</td>
		              <td>
		                	<a href="javascript:editdata(\''.$estate_id.'\',\''.$data["id"].'\',\''.base64_encode($state) .'\',\''.base64_encode($city) .'\',\''.base64_encode($taluka) .'\',\''.base64_encode($area) .'\',\''.base64_encode($ind_estate) .'\',\''.base64_encode($data["firm_name"]) .'\',\''.$user_id .'\',\''.base64_encode($data["stage1"]) .'\',\''.base64_encode($data["factory_address"]) .'\',\''.base64_encode($emp_name) .'\');"><i class="bx bx-edit-alt me-1"></i> </a>
		              </td>
		            </tr>';
		                $i++;
		              }
		            
		          $html.='</tbody>
		        </table>
		      </div>
		    </div>';
		echo $html;
	}

	// company_add_plot_comp
	if($_REQUEST['action']=="company_plot_comp_grid")
	{	
		$html="";
		$state=$_REQUEST['state'];
		$city=$_REQUEST['city'];
		$taluka=$_REQUEST['taluka'];
		$area=$_REQUEST['area'];
		$user_id=$_REQUEST['user_id'];
		$stmt = $obj->con1->prepare("SELECT r1.id, r1.raw_data->>'$.post_fields.Firm_Name' as firm_name, r1.raw_data->>'$.post_fields.Factory_Address' as factory_address, r1.raw_data->>'$.post_fields.Mobile_No' as mobile_no, (select stage END from tbl_tdrawassign where inq_id=r1.id order by id desc LIMIT 1) stage, (select CASE WHEN stage='lead' THEN 'Positive' WHEN stage='badlead' THEN 'Negative' ELSE 'Existing Client' END from tbl_tdrawassign where inq_id=r1.id order by id desc LIMIT 1) stage1 from tbl_tdrawdata r1 where r1.raw_data->'$.post_fields.city'='".$city."' and r1.raw_data->'$.post_fields.Taluka'='".$taluka."' and r1.raw_data->'$.post_fields.Area'='".$area."' and JSON_CONTAINS_PATH(raw_data, 'one', '$.plot_details') = 0 and raw_data->'$.post_fields.IndustrialEstate'='' and id not in (SELECT rawdata_id from pr_company_details)");
		$stmt->execute();
		$res = $stmt->get_result();
		$stmt->close();

		$html='<div class="row">
				<div class="col mb-3"><label class="form-label">City : '.$city.'</label></div>
				<div class="col mb-3"><label class="form-label">Taluka : '.$taluka.'</label></div>
            	<div class="col mb-3"><label class="form-label">Area : '.$area.'</label></div>
            </div>
            <div class="card">
      			<h5 class="card-header">Records</h5>
      			<div class="table-responsive text-nowrap">
        			<table class="table" id="table_modal_id">
			          <thead>
			            <tr>
			              <th>Srno</th>
			              <th>Company Name</th>
			              <th>Employee Name</th>
			              <th>Factory Address</th>
			              <th>Status</th>
			              <th>Action</th>
			            </tr>
			          </thead>
			          <tbody class="table-border-bottom-0">';
			          $i=1;
		while($data=mysqli_fetch_array($res))
	    {
	        if($data["stage"]=='applicationstart' || $data["stage"]=='schemesstarted')
	        {
	          $stmt_stage = $obj->con1->prepare("select a1.tatassign_id, a1.tatassign_status ,a1.tatassign_user_id,u1.name as emp_name from tbl_tdtatassign a1,tbl_users u1 where a1.tatassign_user_id=u1.id and  a1.tatassign_inq_id=?  order by a1.tatassign_id desc limit 1");
	          $stmt_stage->bind_param("i",$data["id"]);
	          $stmt_stage->execute();
	          $stage_result = $stmt_stage->get_result()->fetch_assoc();
	          $stmt_stage->close();
	          $emp_name=$stage_result["emp_name"];
	        }
	        else
	        {
	          $stmt_stage = $obj->con1->prepare("select r1.id, r1.stage ,r1.user_id,u1.name as emp_name from tbl_tdrawassign r1,tbl_users u1 where r1.user_id=u1.id and r1.inq_id=? order by r1.id desc limit 1; ");
	          $stmt_stage->bind_param("i",$data["id"]);
	          $stmt_stage->execute();
	          $stage_result = $stmt_stage->get_result()->fetch_assoc();
	          $stmt_stage->close();
	          $emp_name=$stage_result["emp_name"];

	        }
				$html.='<tr>
		              <td>'.$i.'</td>
		              <td>'.$data["firm_name"].'</td>
		              <td>'.$emp_name.'</td>
		              <td>'.$data["factory_address"].'</td>
		              <td>';
		                  $html.=$data["stage1"];
		            $html.='</td>
		              <td>
		                <a href="javascript:editdata(\''.$data["id"].'\',\''.base64_encode($state) .'\',\''.base64_encode($city) .'\',\''.base64_encode($taluka) .'\',\''.base64_encode($area) .'\',\''.base64_encode($data["firm_name"]) .'\',\''.$user_id .'\',\''.base64_encode($data["stage1"]) .'\',\''.base64_encode($data["factory_address"]) .'\',\''.base64_encode($emp_name) .'\');"><i class="bx bx-edit-alt me-1"></i> </a>	
		              </td>
		            </tr>';
		                $i++;
		              }
		              
		            
		          $html.='</tbody>
		        </table>
		      </div>
		    </div>';
		echo $html;
	}

	// process_gogtp_ir
	if($_REQUEST['action']=="generate_random_emp_list")
	{	
		$html="";
		$scheme_id=$_REQUEST["scheme_id"];
		$stage_id=$_REQUEST["stage_id"];
		$file_id=$_REQUEST["file_id"];
		$inq_id=$_REQUEST["inq_id"];

		$stmt_affidavit = $obj->con1->prepare("SELECT json_unquote(file_data->'$.total_male_manager') as total_male_manager, json_unquote(file_data->'$.total_female_manager') as total_female_manager, json_unquote(file_data->'$.total_male_worker') as total_male_worker, json_unquote(file_data->'$.total_female_worker') as total_female_worker FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_affidavit->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_affidavit->execute();
		$result_affidavit = $stmt_affidavit->get_result()->fetch_assoc();
		$stmt_affidavit->close();

		$stmt_emp_list = $obj->con1->prepare("(SELECT *, TIMESTAMPDIFF(YEAR, guj_stay, CURDATE()) as stay FROM `pr_random_emp_list` where gender='Male' and designation='Manager' order by rand() limit ?) union all (SELECT *, TIMESTAMPDIFF(YEAR, guj_stay, CURDATE()) as stay FROM `pr_random_emp_list` where gender='Female' and designation='Manager' order by rand() limit ?) union all (SELECT *, TIMESTAMPDIFF(YEAR, guj_stay, CURDATE()) as stay FROM `pr_random_emp_list` where gender='Male' and designation='Worker' order by rand() limit ?) union all (SELECT *, TIMESTAMPDIFF(YEAR, guj_stay, CURDATE()) as stay FROM `pr_random_emp_list` where gender='Female' and designation='Worker' order by rand() limit ?)");
		$stmt_emp_list->bind_param("iiii",$result_affidavit['total_male_manager'],$result_affidavit['total_female_manager'],$result_affidavit['total_male_worker'],$result_affidavit['total_female_worker']);
		$stmt_emp_list->execute();
		$emp_result = $stmt_emp_list->get_result();
		$stmt_emp_list->close();

		$i=1;
		$html='<div class="table-responsive text-nowrap">
		<table class="table table-hover" id="table_id">
		<thead>
		<tr>
		<th>Sr. No.</th>
		<th>Name</th>
		<th>Address</th>
		<th>Designation</th>
		<th>Gender</th>
		<th>Gujarat Stay</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">';
		while($emp_list = mysqli_fetch_array($emp_result)){
			$html.='<tr>
			<td>'.$i.'</td>
			<td name="ename'.$i.'"><input type="hidden" name="ename'.$i.'" id="ename'.$i.'" value="'.$emp_list["ename"].'">'.$emp_list["ename"].'</td>
			<td name="address'.$i.'"><input type="hidden" name="address'.$i.'" id="address'.$i.'" value="'.$emp_list["address"].'">'.$emp_list["address"].'</td>
			<td name="designation'.$i.'"><input type="hidden" name="designation'.$i.'" id="designation'.$i.'" value="'.$emp_list["designation"].'">'.$emp_list["designation"].'</td>
			<td name="gender'.$i.'"><input type="hidden" name="gender'.$i.'" id="gender'.$i.'" value="'.$emp_list["gender"].'">'.$emp_list["gender"].'</td>
			<td name="stay'.$i.'"><input type="hidden" name="stay'.$i.'" id="stay'.$i.'" value="'.$emp_list["stay"].'">'.$emp_list["stay"].'</td>
			</tr>';
			$i++;
		}
		$html.='</tbody>
		</table>
		</div>
		<input type="hidden" name="count" id="count" value="'.$i.'">';

		echo $html;
	}

	// process_gogtp_pt
	if($_REQUEST['action']=="annexure_expansion_month_tbl"){
		$html="";
		$end_dt = $_REQUEST['end_dt'];

		$html='<div class="table-responsive text-nowrap">
		<table class="table table-hover" id="table_id">
		<thead>
		<tr>
		<th>Sr. No.</th>
		<th>Month-Year</th>
		<th>Unit Consumed(KWH)</th>
		<th>Remarks if any</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">';
		// for 6 months 
		for($j=5,$i=1;$j>=0;$j--,$i++){
			$end = date('Y-m-d',strtotime('first day of -'.$j.' month',strtotime($end_dt)));


			$html.='<tr>
			<td>'.$i.'</td>
			<td name="month'.$j.'"><input type="hidden" name="month'.$j.'" id="month'.$j.'" value="'.date('M-Y',strtotime($end)).'">'.date('M-Y',strtotime($end)).'</td>
			<td name="unit'.$j.'"><input type="text" class="form-control" name="unit'.$j.'" id="unit'.$j.'" required value=""></td>
			<td name="remarks'.$j.'"><input type="text" class="form-control" name="remarks'.$j.'" id="remarks'.$j.'"></td>
			</tr>';
		}
		$html.='</tbody>
		</table>
		</div>';

		echo $html;

	}
		
	// process_gogtp_pt
	if ($_REQUEST['action'] == "get_power_tariff_subsidy") {
		$html = "";
		$start_date = $_REQUEST['start_dt'];
		$end_date = $_REQUEST['end_dt'];
		$months=get_months($start_date,$end_date);
		$no_of_months=sizeof(get_months($start_date,$end_date));

		$html .= '<input type="hidden" name="monthsDifference" id="monthsDifference" value="'.$no_of_months.'">
		<div class="table-responsive text-nowrap">
		<table class="table table-hover" id="table_id">
		<thead>
		<tr>
		<th>Sr. No.</th>
		<th>Month-Year</th>
		<th>Unit Consumed(KWH)</th>
		<th>Remarks if any</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">';


		for ($j = 0,$i=1; $j<$no_of_months; $j++,$i++) {

			$html .= '<tr>
			<td>' . $i . '</td>
			<td name="month' . $j . '">
			<input type="hidden" name="month' . $j . '" id="month' . $j . '" value="' . $months[$j] . '" class="form-control">
			' . $months[$j] . '
			</td>
			<td name="unit' . $j . '"><input type="text" name="unit' . $j . '" id="unit' . $j . '" class="form-control" required></td>
			<td name="remarks' . $j . '"><input type="text" name="remarks' . $j . '" class="form-control" id="remarks' . $j . '"></td>
			</tr>';

				//$start->modify('+1 month');
		}

		$html .= '</tbody>
		</table>
		</div>';

		echo $html;
	}

	if ($_REQUEST['action'] == "annexure3_expansion_month_tbl") {
		$html = "";
		$start_date = $_REQUEST['start_dt'];
		$end_date = $_REQUEST['end_dt'];
		$months=get_months($start_date,$end_date);
		$no_of_months=sizeof(get_months($start_date,$end_date));

		$html .= '<input type="hidden" name="monthsDifference" id="monthsDifference" value="'.$no_of_months.'">
		<div class="table-responsive text-nowrap">
		<table class="table table-hover" id="table_id">
		<thead>
		<tr>
		<th>Sr. No.</th>
		<th>Month-Year</th>
		<th>Unit Consumed(KWH)</th>
		<th>Remarks if any</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">';


		for ($j = 0,$i=1; $j<$no_of_months; $j++,$i++) {

			//	$currentMonth = $start->format('M-Y');

			$html .= '<tr>
			<td>' . $i . '</td>
			<td name="month' . $j . '">
			<input type="hidden" name="month' . $j . '" id="month' . $j . '" value="' . $months[$j] . '" class="form-control">
			' . $months[$j] . '
			</td>
			<td name="unit' . $j . '"><input type="text" name="unit' . $j . '" id="unit' . $j . '" class="form-control" required></td>
			<td name="remarks' . $j . '"><input type="text" name="remarks' . $j . '" class="form-control" id="remarks' . $j . '"></td>
			</tr>';

				//$start->modify('+1 month');
		}

		$html .= '</tbody>
		</table>
		</div>';

		echo $html;
	}

	if ($_REQUEST['action'] == "annexure4_expansion_month_tbl") {
		$html = "";
		$start_date = $_REQUEST['start_dt'];
		$end_date = $_REQUEST['end_dt'];
		$months=get_months($start_date,$end_date);
		$no_of_months=sizeof(get_months($start_date,$end_date));

		$html .= '<input type="hidden" name="monthsDifference" id="monthsDifference" value="'.$no_of_months.'">
		<div class="table-responsive text-nowrap">
		<table class="table table-hover" id="table_id">
		<thead>
		<tr>
		<th>Sr. No.</th>
		<th>Month-Year</th>
		<th>Unit Consumed(KWH)</th>
		<th>Remarks if any</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">';


		for ($j = 0,$i=1; $j<$no_of_months; $j++,$i++) {

			//	$currentMonth = $start->format('M-Y');

			$html .= '<tr>
			<td>' . $i . '</td>
			<td name="month' . $j . '">
			<input type="hidden" name="month' . $j . '" id="month' . $j . '" value="' . $months[$j] . '" class="form-control">
			' . $months[$j] . '
			</td>
			<td name="unit' . $j . '"><input type="text" name="unit' . $j . '" id="unit' . $j . '" class="form-control" required></td>
			<td name="remarks' . $j . '"><input type="text" name="remarks' . $j . '" class="form-control" id="remarks' . $j . '"></td>
			</tr>';

				//$start->modify('+1 month');
		}

		$html .= '</tbody>
		</table>
		</div>';

		echo $html;
	}

	if ($_REQUEST['action'] == "month_year_annexure5_div") {
		$html = "";
		$start_date = $_REQUEST['start_dt'];
		$end_date = $_REQUEST['end_dt'];
		$months=get_months($start_date,$end_date);
		$no_of_months=sizeof(get_months($start_date,$end_date));

		$html .= '<input type="hidden" name="monthsDifference" id="monthsDifference" value="'.$no_of_months.'">
		<div class="table-responsive text-nowrap">
		<table class="table table-hover" id="table_id">
		<thead>
		<tr>
		<th>Sr. No.</th>
		<th>Month-Year</th>
		<th>Unit Consumed(KWH)</th>
		<th>Renewable Generation (Kwh)</th>
		<th>Remarks if any</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">';


		for ($j = 0,$i=1; $j<$no_of_months; $j++,$i++) {

			//	$currentMonth = $start->format('M-Y');

			$html .= '<tr>
			<td>' . $i . '</td>
			<td name="month' . $j . '">
			<input type="hidden" name="month' . $j . '" id="month' . $j . '" value="' . $months[$j] . '" class="form-control">
			' . $months[$j] . '
			</td>
			<td name="unit' . $j . '"><input type="text" name="unit' . $j . '" id="unit' . $j . '" class="form-control" required></td>
			<td name="renewable_generation' . $j . '"><input type="text" name="renewable_generation' . $j . '" id="renewable_generation' . $j . '" class="form-control" required></td>
			<td name="remarks' . $j . '"><input type="text" name="remarks' . $j . '" class="form-control" id="remarks' . $j . '"></td>
			</tr>';

				//$start->modify('+1 month');
		}

		$html .= '</tbody>
		</table>
		</div>';

		echo $html;
	}

	if($_REQUEST['action']=="excel_tbl"){
		$html = "";
		$start_date = $_REQUEST['start_dt'];
		$end_date = $_REQUEST['end_dt'];
		$precent_of_interest_amt = $_REQUEST['precent_of_interest_amt'];
		$months=get_months($start_date,$end_date);
		$no_of_months=sizeof(get_months($start_date,$end_date));
		$precent_of_interest_amt = $_REQUEST['precent_of_interest_amt'];
		$html .= '<input type="hidden" name="monthsDifference" id="monthsDifference" value="'.$no_of_months.'">
		<div class="table-responsive text-nowrap">
		<table class="table table-hover" id="table_id">
		<thead>
		<tr>
		<th>Sr. No.</th>
		<th>Date</th>
		<th>Days</th>
		<th>Capital amt</th>
		<th>ROI</th>
		<th>Int. Amt</th>
		<th>'.$precent_of_interest_amt.'%Int. Amt</th>
		<th>Int. pen.</th>
		<th>Int. regular</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">';

			 // precent of interest= (interest_amt/roi)*precent 

		for ($j = 0,$i=1; $j<$no_of_months; $j++,$i++) {

			$start_date = date('d-m-Y',strtotime($start_date));
			$end_of_month_date = date('d-m-Y',strtotime ( '+1 month -1 day' , strtotime ( $start_date ) )) ;
			$diff = strtotime($end_of_month_date) - strtotime($start_date);
			$date_diff= round($diff / (60 * 60 * 24));
			++$date_diff;
			// $precent_of_interest[$j] = ($interest_amt[$j]/$rate_of_interest[$j])*$precent_of_interest_amt;
			$html.='<tr>
			<td>'.$i.'</td>
			<td name="month'.$j.'" class="col-md-3" style="text-align:center"><input  type="text" id="start_date'.$j.'" name="start_date'.$j.'" value="'.$start_date.'" readonly class="form-control "/>TO<input type="text" id="end_of_month_date'.$j.'" name="end_of_month_date'.$j.'" value="'.$end_of_month_date.'" readonly class="form-control "/></td>
			<td name = "days_difference'.$j.'" class="col-md-1"><input type="text" id="no_of_days'.$j.'" name="no_of_days'.$j.'" value="'.$date_diff.'" readonly class="form-control "/></td>
			<td name="capital_amt'.$j.'"><input type="text" name="capital_amt'.$j.'" class="form-control" id="capital_amt'.$j.'"></td>
			<td name="rate_of_interest'.$j.'" class="col-md-2"><input type="text" name="rate_of_interest'.$j.'" class="form-control" id="rate_of_interest'.$j.'" onblur="total_interest_amt(this.value,\''.$precent_of_interest_amt.'\',interest_amt'.$j.'.value,'.$j.')"></td>

			<td name="interest_amt'.$j.'" class="col-md-3"><input type="text" name="interest_amt'.$j.'" class="form-control" id="interest_amt'.$j.'"onblur="total_interest_amt(rate_of_interest'.$j.'.value,\''.$precent_of_interest_amt.'\',this.value,'.$j.')"></td>

			<td name="percent_of_interest'.$j.'"><input type="text" id="percent_of_interest'.$j.'" name="percent_of_interest'.$j.'" readonly class="form-control "/></td>

			<td name="interest_pending'.$j.'"> <input class="form-check-input" type="checkbox" id="interest_pending'.$j.'" name="interest_pending'.$j.'" value="yes"></td>

			<td name="interest_regular'.$j.'"> <input class="form-check-input" type="checkbox" id="interest_regular'.$j.'" name="interest_regular'.$j.'" value="yes"></td>
			</tr>';
			$start_date= date('Y-m-d',strtotime ( '+1 day' , strtotime ( $end_of_month_date ) )) ;
			;
		}
		$html.='</tbody>
		</table>
		</div>';

		echo $html;
	}
}

function get_months($date1, $date2) {
	$flag =false;
	if($date1!==null && $date2!==null)
	{
		$time1  = strtotime($date1);
		$time2  = strtotime($date2);
		$my     = date('mY', $time2);

		$months = array(date('M-Y', $time1));
		while($time1 < $time2) {
			$flag= true;
			$time1 = strtotime(date('Y-m-d', $time1).' +1 month');
			if(date('mY', $time1) != $my && ($time1 < $time2) )
				$months[] = date('M-Y', $time1);
		}

		if($flag)
		{  									
			$months[] = date('M-Y', $time2);
		}  									
		return $months;
	}
}

?>