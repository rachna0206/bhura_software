<?php
session_start();
date_default_timezone_set("Asia/Kolkata");
//error_reporting(E_ALL);
include("db_connect.php");
$obj=new DB_Connect();
if(isset($_REQUEST['action']))
{
	// process_gogtp_ir
	if($_REQUEST['action']=="ca_certificate_newfirm_gogtp")
	{	
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();

		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
		}
		else{
			$edit = false;
		}

		
		$html = '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}
		$html.='<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Acquired following fixed assets up to date</label>
		<input type="date" class="form-control" name="acquired_assets_dt" id="acquired_assets_dt" max="9999-12-31" value="'.($edit?$file_data->acquired_assets_dt:"").'" required />
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Manufacturing Product</label>
		<input type="text" class="form-control" name="manufacturing_prod" id="manufacturing_prod" value="'.($edit?$file_data->manufacturing_prod:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Commercial Production Date</label>
		<input type="date" class="form-control" name="commercial_date" id="commercial_date" max="9999-12-31" value="'.($edit?$file_data->commercial_date:"").'" required />
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">First Invoice Date</label>
		<input type="date" class="form-control" name="first_invoice_date" id="first_invoice_date" max="9999-12-31" value="'.($edit?$file_data->first_invoice_date:"").'" required />
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Total Invoice Value</label>
		<input type="text" class="form-control" name="invoice_value" id="invoice_value" value="'.($edit?$file_data->invoice_value:"").'" required />
		</div>
		</div>

		<div class="table-responsive text-nowrap">
		<table class="table table-hover" id="table_id">
		<thead>
		<tr>
		<th>Srno</th>
		<th>Description of Assets</th>
		<th>Gross Fixed Capital Assets (Rs. In Lakh)</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">

		<tr>
		<td>1</td>
		<td>Land</td>
		<td><input type="text" class="form-control" name="land" id="land" value="'.($edit?$file_data->land:"").'" required /></td>
		</tr>

		<tr>
		<td>2</td>
		<td>Building & Shed</td>
		<td><input type="text" class="form-control" name="building_shed" id="building_shed" value="'.($edit?$file_data->building_shed:"").'" required /></td>
		</tr>

		<tr>
		<td>3</td>
		<td>Plant & M/C</td>
		<td><input type="text" class="form-control" name="plant_mc" id="plant_mc" value="'.($edit?$file_data->plant_mc:"").'" required /></td>
		</tr>

		<tr>
		<td>4</td>
		<td>Electrification</td>
		<td><input type="text" class="form-control" name="electrification" id="electrification" value="'.($edit?$file_data->electrification:"").'" required /></td>
		</tr>

		<tr>
		<td>5</td>
		<td>Tools and Equipment</td>
		<td><input type="text" class="form-control" name="tools_equipment" id="tools_equipment" value="'.($edit?$file_data->accessories:"").'" required /></td>
		</tr>

		<tr>
		<td>6</td>
		<td>Accessories</td>
		<td><input type="text" class="form-control" name="accessories" id="accessories" value="'.($edit?$file_data->accessories:"").'" required /></td>
		</tr>

		<tr>
		<td>7</td>
		<td>Utilities and Effluent Treatment plant</td>
		<td><input type="text" class="form-control" name="utilities" id="utilities" value="'.($edit?$file_data->utilities:"").'" required /></td>
		</tr>

		<tr>
		<td>8</td>
		<td>Other investments</td>
		<td><input type="text" class="form-control" name="investments" id="investments" value="'.($edit?$file_data->investments:"").'" required /></td>
		</tr>

		</tbody>
		</table>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Details of Means of Finance</label>
		</div>
		<div class="table-responsive text-nowrap">
		<table class="table table-hover" id="table_id">
		<thead>
		<tr>
		<th>Srno</th>
		<th>Particulars</th>
		<th>Total Amount (Rs. In Lakh)</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">

		<tr>
		<td>1</td>
		<td>a. Equity Share Capital/ Promoter’s Contribution</td>
		<td><input type="text" class="form-control" name="capital" id="capital" value="'.($edit?$file_data->capital:"").'" required /></td>
		</tr>

		<tr>
		<td>2</td>
		<td>b. Equity Share Premium</td>
		<td><input type="text" class="form-control" name="premium" id="premium" value="'.($edit?$file_data->premium:"").'" required /></td>
		</tr>

		<tr>
		<td>3</td>
		<td>c. Bank Term Loan</td>
		<td><input type="text" class="form-control" name="term_loan" id="term_loan" value="'.($edit?$file_data->term_loan:"").'" required /></td>
		</tr>

		<tr>
		<td>4</td>
		<td>d. Working Capital Loan</td>
		<td><input type="text" class="form-control" name="capital_loan" id="capital_loan" value="'.($edit?$file_data->capital_loan:"").'" required /></td>
		</tr>

		<tr>
		<td>5</td>
		<td>e. Internal Source of Fund</td>
		<td><input type="text" class="form-control" name="internal_source" id="internal_source" value="'.($edit?$file_data->internal_source:"").'" required /></td>
		</tr>

		<tr>
		<td>6</td>
		<td>f. Others</td>
		<td><input type="text" class="form-control" name="others" id="others" value="'.($edit?$file_data->others:"").'" required /></td>
		</tr>

		</tbody>
		</table>
		</div>

		</div></div>';

		$html.= '<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_update_ca_certi_newfirm" id="btn_update_ca_certi_newfirm" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_ca_certi_newfirm" id="btn_ca_certi_newfirm" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}

	// process_gogtp_ir
	if($_REQUEST['action']=="affidavit2_gogtp")
	{	
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();

		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
		}
		else{
			$edit = false;
		}
		
		$html = '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}
		$html.='<div class="table-responsive text-nowrap">
		<table class="table table-bordered table-hover" id="table_id">
		<thead>
		<tr>
		<th>Sr. No.</th>
		<th>Particulars</th>
		<th colspan="2">Local Employee</th>
		<th colspan="2">Outside of Gujarat</th>
		</tr>

		<tr>
		<th></th>
		<th></th>
		<th>Male</th>
		<th>Female</th>
		<th>Male</th>
		<th>Female</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">

		<tr>
		<td>1</td>
		<td>Manager/Supervisor</td>
		<td><input type="text" class="form-control" name="mana_local_male" id="mana_local_male" value="'.($edit?$file_data->mana_local_male:"").'" required /></td>
		<td><input type="text" class="form-control" name="mana_local_female" id="mana_local_female" value="'.($edit?$file_data->mana_local_female:"").'" required /></td>
		<td><input type="text" class="form-control" name="mana_outside_male" id="mana_outside_male" value="'.($edit?$file_data->mana_outside_male:"").'" required /></td>
		<td><input type="text" class="form-control" name="mana_outside_female" id="mana_outside_female" value="'.($edit?$file_data->mana_outside_female:"").'" required /></td>
		</tr>

		<tr>
		<td>2</td>
		<td>Workers</td>
		<td><input type="text" class="form-control" name="worker_local_male" id="worker_local_male" value="'.($edit?$file_data->worker_local_male:"").'" required /></td>
		<td><input type="text" class="form-control" name="worker_local_female" id="worker_local_female" value="'.($edit?$file_data->worker_local_female:"").'" required /></td>
		<td><input type="text" class="form-control" name="worker_outside_male" id="worker_outside_male" value="'.($edit?$file_data->worker_outside_male:"").'" required /></td>
		<td><input type="text" class="form-control" name="worker_outside_female" id="worker_outside_female" value="'.($edit?$file_data->worker_outside_female:"").'" required /></td>
		</tr>

		</tbody>
		</table>
		</div>

		</div></div>';

		$html.= '<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_update_affidavit_gogtp" id="btn_update_affidavit_gogtp" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_affidavit_gogtp" id="btn_affidavit_gogtp" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}

	// process_gogtp_ir
	if($_REQUEST['action']=="ca_certificate_expansion_gogtp")
	{	
		$html="";
		
		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();

		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
		}
		else{
			$edit = false;
		}

		$html = '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}

		$html.='<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of Initiating Expansion</label>
		<input type="date" class="form-control" name="ini_expansion_dt" id="ini_expansion_dt" max="9999-12-31" value="'.($edit?$file_data->ini_expansion_dt:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Total Investments as on date</label>
		<input type="date" class="form-control" name="total_investment_dt" id="total_investment_dt" max="9999-12-31" value="'.($edit?$file_data->total_investment_dt:"").'" required />
		</div>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Date of Initiating Expansion to the date of commencing production</label>
		</div>
		<div class="row">
		<div class="col mb-3">
		<input type="date" class="form-control" name="from_expansion_dt" id="from_expansion_dt" max="9999-12-31" value="'.($edit?$file_data->from_expansion_dt:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">to</label>
		</div>
		<div class="col mb-3">
		<input type="date" class="form-control" name="to_initiating_dt" id="to_initiating_dt" max="9999-12-31" value="'.($edit?$file_data->to_initiating_dt:"").'" required />
		</div>
		</div>

		<div class="table-responsive text-nowrap">
		<table class="table table-bordered" id="table_id">
		<thead>
		<tr>
		<th>Sr. No.</th>
		<th>Break- up Fixed assets</th>
		<th>Gross Fixed Capital Investment <br/>for Expansion/ Diversification/Forward <br/>Integration/Backward Integration as <br/>on the date of initiating <br/>expansion/ the date of initiating <br/>expansion Diversification/Forward <br/>Integration/Backward Integration</th>
		<th>Gross Fixed Capital Investment <br/>for Expansion/ Diversification / Forward <br/>Integration/Backward Integration from <br/>the date of initiating expansion <br/>up to the date of <br/>commencing production/till Complete <br/>Project during the Diversification / <br/>Forward Integration/Backward Integration</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">

		<tr>
		<td>1</td>
		<td>Land</td>
		<td><input type="text" class="form-control" name="ini_investment_land" id="ini_investment_land" value="'.($edit?$file_data->ini_investment_land:"").'" required /></td>
		<td><input type="text" class="form-control" name="comm_investment_land" id="comm_investment_land" value="'.($edit?$file_data->comm_investment_land:"").'" required /></td>
		</tr>
		<tr>
		<td>2</td>
		<td>Building</td>
		<td><input type="text" class="form-control" name="ini_investment_building" id="ini_investment_building" value="'.($edit?$file_data->ini_investment_building:"").'" required /></td>
		<td><input type="text" class="form-control" name="comm_investment_building" id="comm_investment_building" value="'.($edit?$file_data->comm_investment_building:"").'" required /></td>
		</tr>
		<tr>
		<td>3</td>
		<td>Plant & Machinery</td>
		<td><input type="text" class="form-control" name="ini_investment_plant" id="ini_investment_plant" value="'.($edit?$file_data->ini_investment_plant:"").'" required /></td>
		<td><input type="text" class="form-control" name="comm_investment_plant" id="comm_investment_plant" value="'.($edit?$file_data->comm_investment_plant:"").'" required /></td>
		</tr>
		<tr>
		<td>4</td>
		<td>Utilities</td>
		<td><input type="text" class="form-control" name="ini_investment_utilities" id="ini_investment_utilities" value="'.($edit?$file_data->ini_investment_utilities:"").'" required /></td>
		<td><input type="text" class="form-control" name="comm_investment_utilities" id="comm_investment_utilities" value="'.($edit?$file_data->comm_investment_utilities:"").'" required /></td>
		</tr>
		<tr>
		<td>5</td>
		<td>Tools & Equipment</td>
		<td><input type="text" class="form-control" name="ini_investment_tools" id="ini_investment_tools" value="'.($edit?$file_data->ini_investment_tools:"").'" required /></td>
		<td><input type="text" class="form-control" name="comm_investment_tools" id="comm_investment_tools" value="'.($edit?$file_data->comm_investment_tools:"").'" required /></td>
		</tr>
		<tr>
		<td>6</td>
		<td>Electrification</td>
		<td><input type="text" class="form-control" name="ini_investment_electric" id="ini_investment_electric" value="'.($edit?$file_data->ini_investment_electric:"").'" required /></td>
		<td><input type="text" class="form-control" name="comm_investment_electric" id="comm_investment_electric" value="'.($edit?$file_data->comm_investment_electric:"").'" required /></td>
		</tr>
		<tr>
		<td>7</td>
		<td>Other Assets (Required <br/>Manufacturing the end Product)</td>
		<td><input type="text" class="form-control" name="ini_investment_assets" id="ini_investment_assets" value="'.($edit?$file_data->ini_investment_assets:"").'" required /></td>
		<td><input type="text" class="form-control" name="comm_investment_assets" id="comm_investment_assets" value="'.($edit?$file_data->comm_investment_assets:"").'" required /></td>
		</tr>
		</tbody>
		</table>
		</div>

		</div></div>';

		$html.= '<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_update_ca_certi_expansion" id="btn_update_ca_certi_expansion" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_ca_certi_expansion" id="btn_ca_certi_expansion" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}

	// process_gogtp_ir
	if($_REQUEST['action']=="ce_certificate_gogtp")
	{	
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();

		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
		}
		else{
			$edit = false;
		}
		
		$html = '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}
		$html.='<h5>1.</h5>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Existing Gross Fixed Capital Investment before the initiation of Expansion or Forward/Backward Integration</label>
		</div>
		<div class="col mb-3">
		<input type="text" class="form-control" name="existing_gross_capital" id="existing_gross_capital" value="'.($edit?$file_data->existing_gross_capital:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Gross Fixed Capital Investment installed during Expansion or Forward/Backward Integration period</label>
		</div>
		<div class="col mb-3">
		<input type="text" class="form-control" name="gross_capital" id="gross_capital" value="'.($edit?$file_data->gross_capital:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Total Gross Fixed capital Investment after the completion of Expansion or Forward/Backward Integration period</label>
		</div>
		<div class="col mb-3">
		<input type="text" class="form-control" name="total_gross_capital" id="total_gross_capital" value="'.($edit?$file_data->total_gross_capital:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Investment in Gross Fixed Capital Investment increased in percentage during Expansion or Forward/Backward Integration period</label>
		</div>
		<div class="col mb-3">
		<input type="text" class="form-control" name="investment_increase_perc" id="investment_increase_perc" value="'.($edit?$file_data->investment_increase_perc:"").'" required />
		</div>
		</div>

		<h5>2.</h5>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Existing Installed Capacity of the product before the initiation of Expansion or Forward/Backward Integration</label>
		</div>
		<div class="col mb-3">
		<input type="text" class="form-control" name="existing_capacity" id="existing_capacity" value="'.($edit?$file_data->existing_capacity:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Proposed Installed Capacity of the product during Expansion or Forward/Backward Integration period</label>
		</div>
		<div class="col mb-3">
		<input type="text" class="form-control" name="proposed_capacity" id="proposed_capacity" value="'.($edit?$file_data->proposed_capacity:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Increased in percentage of the Proposed Installed Capacity during Expansion or Forward/Backward Integration period</label>
		</div>
		<div class="col mb-3">
		<input type="text" class="form-control" name="proposed_capacity_increase_perc" id="proposed_capacity_increase_perc" value="'.($edit?$file_data->proposed_capacity_increase_perc:"").'" required />
		</div>
		</div>

		<h5>3.</h5>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Existing Installed Capacity of the product before the initiation of Expansion or Forward/Backward Integration</label>
		</div>
		<div class="col mb-3">
		<input type="text" class="form-control" name="existing_capacity_second" id="existing_capacity_second" value="'.($edit?$file_data->existing_capacity_second:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Production made in Immediately preceding two financial year from date of initiation of Expansion or Forward/Backward Integration</label>
		</div>
		<div class="col mb-3">
		<input type="text" class="form-control" name="two_years_production_capacity" id="two_years_production_capacity" value="'.($edit?$file_data->two_years_production_capacity:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Production made in Immediately preceding two financial year from date of initiation of Expansion or Forward/Backward Integration</label>
		</div>
		<div class="col mb-3">
		<input type="text" class="form-control" name="two_years_production_money" id="two_years_production_money" value="'.($edit?$file_data->two_years_production_money:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Maximum utilization of existing installed capacity reached in percentage in the immediately preceding two financial year from date of initiation of Expansion or Forward/Backward Integration</label>
		</div>
		<div class="col mb-3">
		<input type="text" class="form-control" name="max_utilization_perc" id="max_utilization_perc" value="'.($edit?$file_data->max_utilization_perc:"").'" required />
		</div>
		</div>

		</div></div>';

		$html.= '<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_update_ce_certi_gogtp" id="btn_update_ce_certi_gogtp" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_ce_certi_gogtp" id="btn_ce_certi_gogtp" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}

	// process_gogtp_ir
	if($_REQUEST['action']=="certificate_first_disbursment_gogtp")
	{	
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();

		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
		}
		else{
			$edit = false;
		}
		
		$html = '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}
		$html.='<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Project</label>
		<input type="text" class="form-control" name="project" id="project" value="'.($edit?$file_data->project:"").'" required />
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Sanctioned Term Loan</label>
		<input type="text" class="form-control" name="sanctioned_term_loan" id="sanctioned_term_loan" value="'.($edit?$file_data->sanctioned_term_loan:"").'" required />
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Project Date</label>
		<input type="date" class="form-control" name="project_date" id="project_date" max="9999-12-31" value="'.($edit?$file_data->project_date:"").'" required />
		</div>
		</div>


		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Disbursed Term Loan</label>
		<input type="text" class="form-control" name="disbursed_term_loan" id="disbursed_term_loan" value="'.($edit?$file_data->disbursed_term_loan:"").'" required />
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Term Loan Account No.</label>
		<input type="text" class="form-control" name="loan_account_no" id="loan_account_no" value="'.($edit?$file_data->loan_account_no:"").'" required />
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Branch Manager Email</label>
		<input type="text" class="form-control" name="branch_manager_email" id="branch_manager_email" value="'.($edit?$file_data->branch_manager_email:"").'" required />
		</div>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Details of Term Loan : </label>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">1. Application received from unit on</label>
		<input type="date" class="form-control" name="application_received_dt" id="application_received_dt" max="9999-12-31" value="'.($edit?$file_data->application_received_dt:"").'" required />
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">2. Term Loan Sanction date</label>
		<input type="date" class="form-control" name="sanction_loan_dt" id="sanction_loan_dt" max="9999-12-31" value="'.($edit?$file_data->sanction_loan_dt:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">3. Disbursement was made on 1st Disbursement as on Dt.</label>
		<input type="date" class="form-control" name="first_disbursement_dt" id="first_disbursement_dt" max="9999-12-31" value="'.($edit?$file_data->first_disbursement_dt:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">of Rs.</label>
		<input type="text" class="form-control" name="first_disbursement_price" id="first_disbursement_price" value="'.($edit?$file_data->first_disbursement_price:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">4. Total Disbursement of Rs.</label>
		<input type="text" class="form-control" name="total_disbursement_price" id="total_disbursement_price" value="'.($edit?$file_data->total_disbursement_price:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">up to Dt.</label>
		<input type="date" class="form-control" name="total_disbursement_dt" id="total_disbursement_dt" value="'.($edit?$file_data->total_disbursement_dt:"").'" max="9999-12-31" required />
		</div>
		</div>

		<div class="mb-3">
		<label class="form-label" for="basic-default-fullname">Disbursement was made against following Fixed Assets : </label>
		</div>

		<div class="table-responsive text-nowrap">
		<table class="table table-hover" id="table_id">
		<thead>
		<tr>
		<th>Fixed Assets</th>
		<th>Cost as per<br/> project report</th>
		<th>Term loan<br/>sanctioned</th>
		<th>Total Actual<br/>Investment against<br/>Term Loan</th>
		<th>Total Disbursed<br/>Term Loan against<br/>actual investment</th>
		</tr>
		</thead>
		<tbody class="table-border-bottom-0">

		<tr>
		<td>a. Land</td>
		<td><input type="text" class="form-control" name="cost_land" id="cost_land" value="'.($edit?$file_data->cost_land:"").'" required /></td>
		<td><input type="text" class="form-control" name="sanctioned_term_land" id="sanctioned_term_land" value="'.($edit?$file_data->sanctioned_term_land:"").'" required /></td>
		<td><input type="text" class="form-control" name="total_investment_land" id="total_investment_land" value="'.($edit?$file_data->total_investment_land:"").'" required /></td>
		<td><input type="text" class="form-control" name="disbursed_term_land" id="disbursed_term_land" value="'.($edit?$file_data->disbursed_term_land:"").'" required /></td>
		</tr>

		<tr>
		<td>b. Building & Shed</td>
		<td><input type="text" class="form-control" name="cost_building" id="cost_building" value="'.($edit?$file_data->cost_building:"").'" required /></td>
		<td><input type="text" class="form-control" name="sanctioned_term_building" id="sanctioned_term_building" value="'.($edit?$file_data->sanctioned_term_building:"").'" required /></td>
		<td><input type="text" class="form-control" name="total_investment_building" id="total_investment_building" value="'.($edit?$file_data->total_investment_building:"").'" required /></td>
		<td><input type="text" class="form-control" name="disbursed_term_building" id="disbursed_term_building" value="'.($edit?$file_data->disbursed_term_building:"").'" required /></td>
		</tr>

		<tr>
		<td>c. Plant & M/C</td>
		<td><input type="text" class="form-control" name="cost_plant" id="cost_plant" value="'.($edit?$file_data->cost_plant:"").'" required /></td>
		<td><input type="text" class="form-control" name="sanctioned_term_plant" id="sanctioned_term_plant" value="'.($edit?$file_data->sanctioned_term_plant:"").'" required /></td>
		<td><input type="text" class="form-control" name="total_investment_plant" id="total_investment_plant" value="'.($edit?$file_data->total_investment_plant:"").'" required /></td>
		<td><input type="text" class="form-control" name="disbursed_term_plant" id="disbursed_term_plant" value="'.($edit?$file_data->disbursed_term_plant:"").'" required /></td>
		</tr>

		<tr>
		<td>d. Electrification</td>
		<td><input type="text" class="form-control" name="cost_electric" id="cost_electric" value="'.($edit?$file_data->cost_electric:"").'" required /></td>
		<td><input type="text" class="form-control" name="sanctioned_term_electric" id="sanctioned_term_electric" value="'.($edit?$file_data->sanctioned_term_electric:"").'" required /></td>
		<td><input type="text" class="form-control" name="total_investment_electric" id="total_investment_electric" value="'.($edit?$file_data->total_investment_electric:"").'" required /></td>
		<td><input type="text" class="form-control" name="disbursed_term_electric" id="disbursed_term_electric" value="'.($edit?$file_data->disbursed_term_electric:"").'" required /></td>
		</tr>

		<tr>
		<td>e. Tools and Equipment</td>
		<td><input type="text" class="form-control" name="cost_tools" id="cost_tools" value="'.($edit?$file_data->cost_tools:"").'" required /></td>
		<td><input type="text" class="form-control" name="sanctioned_term_tools" id="sanctioned_term_tools" value="'.($edit?$file_data->sanctioned_term_tools:"").'" required /></td>
		<td><input type="text" class="form-control" name="total_investment_tools" id="total_investment_tools" value="'.($edit?$file_data->total_investment_tools:"").'" required /></td>
		<td><input type="text" class="form-control" name="disbursed_term_tools" id="disbursed_term_tools" value="'.($edit?$file_data->disbursed_term_tools:"").'" required /></td>
		</tr>

		<tr>
		<td>f. Accessories</td>
		<td><input type="text" class="form-control" name="cost_accessories" id="cost_accessories" value="'.($edit?$file_data->cost_accessories:"").'" required /></td>
		<td><input type="text" class="form-control" name="sanctioned_term_accessories" id="sanctioned_term_accessories" value="'.($edit?$file_data->sanctioned_term_accessories:"").'" required /></td>
		<td><input type="text" class="form-control" name="total_investment_accessories" id="total_investment_accessories" value="'.($edit?$file_data->total_investment_accessories:"").'" required /></td>
		<td><input type="text" class="form-control" name="disbursed_term_accessories" id="disbursed_term_accessories" value="'.($edit?$file_data->disbursed_term_accessories:"").'" required /></td>
		</tr>

		<tr>
		<td>g. Utilities and Effluent Treatment Plant</td>
		<td><input type="text" class="form-control" name="cost_utilities" id="cost_utilities" value="'.($edit?$file_data->cost_utilities:"").'" required /></td>
		<td><input type="text" class="form-control" name="sanctioned_term_utilities" id="sanctioned_term_utilities" value="'.($edit?$file_data->sanctioned_term_utilities:"").'" required /></td>
		<td><input type="text" class="form-control" name="total_investment_utilities" id="total_investment_utilities" value="'.($edit?$file_data->total_investment_utilities:"").'" required /></td>
		<td><input type="text" class="form-control" name="disbursed_term_utilities" id="disbursed_term_utilities" value="'.($edit?$file_data->disbursed_term_utilities:"").'" required /></td>
		</tr>

		<tr>
		<td>h. Other investments</td>
		<td><input type="text" class="form-control" name="cost_other" id="cost_other" value="'.($edit?$file_data->cost_other:"").'" required /></td>
		<td><input type="text" class="form-control" name="sanctioned_term_other" id="sanctioned_term_other" value="'.($edit?$file_data->sanctioned_term_other:"").'" required /></td>
		<td><input type="text" class="form-control" name="total_investment_other" id="total_investment_other" value="'.($edit?$file_data->total_investment_other:"").'" required /></td>
		<td><input type="text" class="form-control" name="disbursed_term_other" id="disbursed_term_other" value="'.($edit?$file_data->disbursed_term_other:"").'" required /></td>
		</tr>
		</tbody>
		</table>
		</div>

		</div></div>';

		$html.= '<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_update_certi_first_disbursement" id="btn_update_certi_first_disbursement" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_certi_first_disbursement" id="btn_certi_first_disbursement" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}

	// process_gogtp_ir
	if($_REQUEST['action']=="employment_data_gogtp")
	{	
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];
		$affidavit_id = $_REQUEST['affidavit_id'];

		//print("SELECT * FROM `pr_files_data` WHERE scheme_id=".$scheme_id." and stage_id=".$stage_id." and file_id=".$affidavit_id." and inq_id=".$inq_id." order by id desc limit 1");
		
		$stmt_affidavit = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_affidavit->bind_param("iiii",$scheme_id,$stage_id,$affidavit_id,$inq_id);
		$stmt_affidavit->execute();
		$result_affidavit = $stmt_affidavit->get_result();
		$stmt_affidavit->close();

		if(mysqli_num_rows($result_affidavit)>0){

			$res_affidavit = mysqli_fetch_array($result_affidavit);
			$file_data_affidavit=json_decode($res_affidavit["file_data"]);

			$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
			$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
			$stmt_files->execute();
			$result_files = $stmt_files->get_result();
			$stmt_files->close();

			if(mysqli_num_rows($result_files)>0){
				$edit = true;
				$res = mysqli_fetch_array($result_files);
				$file_data=json_decode($res["file_data"]);
				$data=$file_data->srno;
			}
			else{
				$edit = false;
			}

			$html = '<div class="modal-body" ><div class="row">
			<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
			<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
			<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
			<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
			if($edit){
				$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
			}
			$html.='<div class="row">
			<div class="col mb-3"></div>
			<div class="col mb-3">
			<a href="javascript:generate_random_emp_list('.$scheme_id.','.$stage_id.','.$affidavit_id.','.$inq_id.','.$edit.')" class="btn btn-primary" style="margin-right:15px; color: #fff;">Generate List</a>
			</div>
			<div class="col mb-3"></div>
			</div>';
			if($edit){
				$html.='<div id="emp_tbl_update_div">
				<div class="table-responsive text-nowrap">
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
				$count = count($data);
				for($i=0;$i<$count;$i++){
					$html.='<tr>
					<td>'.($i+1).'</td>
					<td name="ename'.$i.'"><input type="hidden" name="ename'.($i+1).'" id="ename'.($i+1).'" value="'.$data[$i]->ename.'">'.$data[$i]->ename.'</td>
					<td name="address'.($i+1).'"><input type="hidden" name="address'.($i+1).'" id="address'.($i+1).'" value="'.$data[$i]->address.'">'.$data[$i]->address.'</td>
					<td name="designation'.($i+1).'"><input type="hidden" name="designation'.($i+1).'" id="designation'.($i+1).'" value="'.$data[$i]->designation.'">'.$data[$i]->designation.'</td>
					<td name="gender'.($i+1).'"><input type="hidden" name="gender'.($i+1).'" id="gender'.($i+1).'" value="'.$data[$i]->gender.'">'.$data[$i]->gender.'</td>
					<td name="stay'.($i+1).'"><input type="hidden" name="stay'.($i+1).'" id="stay'.($i+1).'" value="'.$data[$i]->stay.'">'.$data[$i]->stay.'</td>
					</tr>';
				}
				$html.='</tbody>
				</table>
				</div>
				<input type="hidden" name="count" id="count" value="'.$i.'">
				</div>';
			} else{
				$html.='<div id="emp_tbl_div"></div>';
			}
			$html.='
			</div></div>';

			$html.= '<div class="modal-footer">';
			if($edit){
				$html.='<input type="submit" class="btn btn-primary" name="btn_update_employment_data_gogtp" id="btn_update_employment_data_gogtp" value="Update Changes">';
			} else{
				$html.='<input type="submit" class="btn btn-primary" name="btn_employment_data_gogtp" id="btn_employment_data_gogtp" value="Save Changes">';
			}
			$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
			</div>';
		}
		else{
			$html = '<div class="modal-body" ><div class="row">
			<label class="form-label" for="basic-default-fullname">Please Fill Details Of File Affidavit Scheme</label>
			</div></div>
			<div class="modal-footer">
			<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
			</div>';
		}

		echo $html;
	}
	// process_gogtp_ir
	if($_REQUEST['action']=="annexure2_gogtp")
	{	
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();

		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
		}
		else{
			$edit = false;
		}

		
		$html = '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}
		$html.='<div class="row mb-3">
		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Account number of Enterprise</label>
		<input type="text" class="form-control" name="enterprise_account_no_rtgs" id="enterprise_account_no_rtgs"  value="'.($edit?$file_data->enterprise_account_no_rtgs:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Rate of interest</label>
		<input type="text" class="form-control" name="interest_rate_for_project" id="interest_rate_for_project" value="'.($edit?$file_data->interest_rate_for_project:"").'" required />
		</div>
		</div>
		<div class="row mb-3">

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">First disbursement  date</label>
		<input type="date" class="form-control" name="first_disbursement_date" id="first_disbursement_date" max="9999-12-31" value="'.($edit?$file_data->first_disbursement_date:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Till date</label>
		<input type="date" class="form-control" name="till_date" id="till_date" max="9999-12-31" value="'.($edit?$file_data->till_date:"").'" required />
		</div>
		</div>
		<div class="row mb-3">

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">First Installment of the Loan </label>
		<input type="text" class="form-control" name="first_installment_of_loan" id="first_installment_of_loan" value="'.($edit?$file_data->first_installment_of_loan:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">disbursed on</label>
		<input type="date" class="form-control" name="disbursed_on_date" id="disbursed_on_date" max="9999-12-31" value="'.($edit?$file_data->disbursed_on_date:"").'" required />
		</div>
		</div>
		<div class="row mb-3">
		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Loan Account Number</label>
		<input type="text" class="form-control" name="loan_acc_number" id="loan_acc_number" value="'.($edit?$file_data->loan_acc_number:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Disbursed AMOUNT</label>
		<input type="text" class="form-control" name="disbursed_amount_by_unit" id="disbursed_amount_by_unit" value="'.($edit?$file_data->disbursed_amount_by_unit:"").'" required />
		</div>
		</div>
		<div class="row mb-3">

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">From </label>
		<input type="date" class="form-control" name="start_date_of_loan" id="start_date_of_loan" max="9999-12-31" value="'.($edit?$file_data->start_date_of_loan:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">TO </label>
		<input type="date" class="form-control" name="end_date_of_loan" id="end_date_of_loan" max="9999-12-31" value="'.($edit?$file_data->end_date_of_loan:"").'" required />
		</div>
		</div>
		<div class="row mb-3">

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Against term loan</label>
		<input type="text" class="form-control" name="against_term_loan" id="against_term_loan" value="'.($edit?$file_data->against_term_loan:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">as interest</label>
		<input type="text" class="form-control" name="as_interest" id="as_interest" value="'.($edit?$file_data->as_interest:"").'" required />
		</div>
		</div>
		<div class="row mb-3">
		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Total</label>
		<input type="text" class="form-control" name="total_amount" id="total_amount" value="'.($edit?$file_data->total_amount:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">subsidy amount</label>
		<input type="text" class="form-control" name="subsidy_amount_num" id="subsidy_amount_num" value="'.($edit?$file_data->subsidy_amount_num:"").'" required />
		</div>
		</div>';

		$html.= '<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure2" id="btn_annexure2" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure2" id="btn_annexure2" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}

	if($_REQUEST['action']=="gogtp_ir_calculation")
	{	
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();

		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
		}
		else{
			$edit = false;
		}

		$html= '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}
		
		$html.='<div class="row mb-3">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Start date of interest</label>
		<input type="date" class="form-control" name="period_of_interest_from" id="period_of_interest_from" max="9999-12-31" value="'.($edit?$file_data->period_of_interest_from:"").'" onblur="excel_tbl(this.value,period_of_interest_to.value,percent_of_interest_amt.value)" required />
		</div>

		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">End date of interest</label>
		<input type="date" class="form-control" name="period_of_interest_to" id="period_of_interest_to" max="9999-12-31" value="'.($edit?$file_data->period_of_interest_to:"").'" onblur="excel_tbl(period_of_interest_from.value,this.value,percent_of_interest_amt.value)" required />
		</div>
		</div>
		<div class="row mb-3">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Percentage of interest amount</label>
		<input type="text" class="form-control" name="percent_of_interest_amt" id="percent_of_interest_amt" value="'.($edit?$file_data->percent_of_interest_amt:"").'" onblur="excel_tbl(period_of_interest_from.value,period_of_interest_to.value,this.value)" required />
		</div>
		<div class="col mb-3">
		</div>
		</div>
		<div id="excel_month_tbl_div">';
		if($edit){
			$no_of_months=$file_data->monthsDifference;
			$precent_of_interest_amt = $file_data->percent_of_interest_amt;
			$html.='<input type="hidden" name="monthsDifference" id="monthsDifference" value="'.$no_of_months.'">
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
			<th>Int. Reg.</th>
			</tr>
			</thead>
			<tbody class="table-border-bottom-0">';
			$monthsDifference = $file_data->monthsDifference;
			for($j=0;$j<$monthsDifference;$j++){
				$srno = 'srno'.$j;
				$start_date = 'start_date'.$j;
				$end_of_month_date = 'end_of_month_date'.$j;
				$no_of_days = 'no_of_days'.$j;
				$capital_amt = 'capital_amt'.$j;
				$rate_of_interest = 'rate_of_interest'.$j;
				$interest_amt = 'interest_amt'.$j;
				$percent_of_interest = 'percent_of_interest'.$j;
				$interest_pending = 'interest_pending'.$j;
				$interest_regular = 'interest_regular'.$j;
				$html.='<tr>
				<td>'.$file_data->$srno.'</td>
				<td name="month'.$j.'" class="col-md-3" style="text-align:center"><input  type="text" id="start_date'.$j.'" name="start_date'.$j.'" value="'.$file_data->$start_date.'" readonly class="form-control "/>TO<input type="text" id="end_of_month_date'.$j.'" name="end_of_month_date'.$j.'" value="'.$file_data->$end_of_month_date.'" readonly class="form-control "/></td>
				<td name = "days_difference'.$j.'" class="col-md-2"><input type="text" id="no_of_days'.$j.'" name="no_of_days'.$j.'" value="'.$file_data->$no_of_days.'" readonly class="form-control "/></td>
				<td name="capital_amt'.$j.'"><input type="text" name="capital_amt'.$j.'" class="form-control" id="capital_amt'.$j.'" value="'.$file_data->$capital_amt.'"></td>
				<td name="rate_of_interest'.$j.'" class="col-md-2"><input type="text" name="rate_of_interest'.$j.'" class="form-control" id="rate_of_interest'.$j.'"  value="'.$file_data->$rate_of_interest.'" onblur="total_interest_amt(this.value,\''.$file_data->percent_of_interest_amt.'\',interest_amt'.$j.'.value,'.$j.')"></td>

				<td name="interest_amt'.$j.'" class="col-md-3"><input type="text" name="interest_amt'.$j.'" class="form-control" id="interest_amt'.$j.'"  value="'.$file_data->$interest_amt.'" onblur="total_interest_amt(rate_of_interest'.$j.'.value,\''.$file_data->percent_of_interest_amt.'\',this.value,'.$j.')"></td>

				<td name="percent_of_interest'.$j.'"><input type="text" id="percent_of_interest'.$j.'" name="percent_of_interest'.$j.'"  value="'.$file_data->$percent_of_interest.'" readonly class="form-control "/></td>

				<td name="interest_pending'.$j.'"> <input class="form-check-input" type="checkbox" id="interest_pending'.$j.'" '.(($file_data->$interest_pending=="yes")?"checked":"").' name="interest_pending'.$j.'" value="yes"></td>

				<td name="interest_regular'.$j.'"> <input class="form-check-input" type="checkbox" id="interest_regular'.$j.'" '.(($file_data->$interest_regular=="yes")?"checked":"").'  name="interest_regular'.$j.'" value="yes"></td>
				</tr>';
			}
			$html.='</tbody>
			</table>
			</div>';
		}
		$html.='
		</div>

		<div class="row mb-3">
		<div class="col md-6">
		<label class="form-label" for="basic-default-fullname">Disbursed Amount</label>
		<input type="text" class="form-control" name="disbursed_amount" id="disbursed_amount" value="'.($edit?$file_data->disbursed_amount:"").'" required />
		</div>
		
		<div class="col md-6">
		<label class="form-label" for="basic-default-fullname">No. Of Installment</label>
		<input type="text" class="form-control" name="no_of_installment" id="no_of_installment" value="'.($edit?$file_data->no_of_installment:"").'" required />
		</div>
		</div>
		
		<div class="row mb-3">
		<div class="col md-6">
		<label class="form-label" for="basic-default-fullname">Installment Amount</label>
		<input type="text" class="form-control" name="installment_amount" id="installment_amount" value="'.($edit?$file_data->installment_amount:"").'" required />
		</div>

		<div class="col md-6">
		<label class="form-label" for="basic-default-fullname">No. Of installment commenced</label>
		<input type="text" class="form-control" name="no_of_installment_commenced" id="no_of_installment_commenced" value="'.($edit?$file_data->no_of_installment_commenced:"").'" required />
		</div>
		</div>
		<div class="row mb-3">
		<div class="col md-6">
		<label class="form-label" for="basic-default-fullname">Rate Of Interest</label>
		<input type="text" class="form-control" name="rate_of_interest" id="rate_of_interest" value="'.($edit?$file_data->rate_of_interest:"").'" required />
		</div>
		<div class="col md-6">
		</div>
		</div>
		';
		
		$html.= '<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_gogtp_ir_calculation_update" id="btn_gogtp_ir_calculation_update" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_gogtp_ir_calculation" id="btn_gogtp_ir_calculation" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}

	if($_REQUEST['action']=="annexure_pt1_new_unit")
	{	
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();
		$edit=false;
		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
			$data=$file_data->srno;
		}

		
		$html = '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}
		$html.='<div class="row mb-3">
		<div class="col mb-6">

		<label class="form-label" for="basic-default-fullname">Date of Company letter</label>
		<input type="date" class="form-control" name="company_letter_date" id="company_letter_date"  value="'.($edit?$file_data->company_letter_date:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Provisional sanction Letter No.</label>
		<input type="text" class="form-control" name="provisional_sanction_letter_no" id="provisional_sanction_letter_no" value="'.($edit?$file_data->provisional_sanction_letter_no:"").'" required />
		</div>
		</div>
		<div class="row mb-3">

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Provisional sanction Letter Date</label>
		<input type="date" class="form-control" name="provisional_sanction_letter_date" id="provisional_sanction_letter_date" max="9999-12-31" value="'.($edit?$file_data->provisional_sanction_letter_date:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Customer Service No.</label>
		<input type="text" class="form-control" name="customer_service_no" id="customer_service_no" value="'.($edit?$file_data->customer_service_no:"").'" required />
		</div>
		</div>
		<div class="row mb-3">

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Meter No.</label>
		<input type="text" class="form-control" name="meter_no" id="meter_no" value="'.($edit?$file_data->meter_no:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Date of Commencement of commercial Production</label>
		<input type="date" class="form-control" name="date_of_production" id="date_of_production" max="9999-12-31" value="'.($edit?$file_data->date_of_production:"").'" required />
		</div>
		</div>
		<div class="row mb-3">
		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Contract Demand</label>
		<div class="input-group input-group-merge">
		<input type="text" class="form-control" name="contract_demand" id="contract_demand" value="'.($edit?$file_data->contract_demand:"").'" required />
		<span class="input-group-text" id="basic-addon33">KVA/KW</span>
		</div>		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Date of Power Release</label>
		<input type="date" class="form-control" name="date_of_power_release" id="date_of_power_release" max="9999-12-31" value="'.($edit?$file_data->date_of_power_release:"").'" required />
		</div>
		</div>
		<div class="row mb-3">

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Power Tariff Subsidy (From)</label>
		<input type="date" class="form-control" name="tarrif_subsidy_period_from" id="tarrif_subsidy_period_from" max="9999-12-31" value="'.($edit?$file_data->tarrif_subsidy_period_from:"").'" onblur="get_power_tariff_subsidy(this.value,tarrif_subsidy_period_to.value)" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Tarrif Subsidy Period(To)</label>
		<input type="date" class="form-control" name="tarrif_subsidy_period_to" id="tarrif_subsidy_period_to" max="9999-12-31" value="'.($edit?$file_data->tarrif_subsidy_period_to:"").'" onblur="get_power_tariff_subsidy(tarrif_subsidy_period_from.value,this.value)" required />
		</div>
		</div>
		<div id="month_year_div">';

		if($edit){
			$html.='<div class="table-responsive text-nowrap">
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
			$count = count($data);
			for ($j = 0; $j<$count; $j++) {
				$html.='<tr>
				<td>'.($j+1).'</td>
				<td name="month'.$j.'">
				<input type="hidden" name="month'.$j.'" id="month'.$j.'" value="'.$data[$j]->month.'" class="form-control">
				'.$data[$j]->month.'
				</td>
				<td name="unit'.$j.'"><input type="text" name="unit'.$j.'" id="unit'.$j.'" value="'.$data[$j]->unit.'" class="form-control" required></td>
				<td name="remarks'.$j.'"><input type="text" name="remarks'.$j.'" value="'.$data[$j]->remarks.'" class="form-control" id="remarks'.$j.'"></td>
				</tr>';
			}
			$html.='</tbody>
			</table>
			</div>';
		}

		$html.='</div>';

		$html.= '<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure_pt1_new_unit_update" id="btn_annexure_pt1_new_unit_update" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure_pt1_new_unit" id="btn_annexure_pt1_new_unit" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}

	if($_REQUEST['action']=="annexure_pt_2_expansion")
	{	
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();
                //json decode and check subsidy period 
		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
			$data=$file_data->srno;
		}
		else{
			$edit = false;
		}

		
		$html = '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}
		$html.='<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of Company letter</label>
		<input type="date" class="form-control" name="company_letter_date" id="company_letter_date"  value="'.($edit?$file_data->company_letter_date:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Provisional sanction Letter No.</label>
		<input type="text" class="form-control" name="provisional_sanction_letter_no" id="provisional_sanction_letter_no" value="'.($edit?$file_data->provisional_sanction_letter_no:"").'" required />
		</div>
		</div>
		
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Provisional sanction Letter Date</label>
		<input type="date" class="form-control" name="provisional_sanction_letter_date" id="provisional_sanction_letter_date" max="9999-12-31" value="'.($edit?$file_data->provisional_sanction_letter_date:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Main Meter No.</label>
		<input type="text" class="form-control" name="main_meter_no" id="main_meter_no" value="'.($edit?$file_data->main_meter_no:"").'" required />
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Old Customer Service No.</label>
		<input type="text" class="form-control" name="old_customer_service_no" id="old_customer_service_no" value="'.($edit?$file_data->old_customer_service_no:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">New Customer Service No.</label>
		<input type="text" class="form-control" name="new_customer_service_no" id="new_customer_service_no" value="'.($edit?$file_data->new_customer_service_no:"").'" required />
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of Commencement of commercial Production</label>
		<input type="date" class="form-control" name="date_of_production" id="date_of_production" max="9999-12-31" value="'.($edit?$file_data->date_of_production:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Contract Demand</label>
		<div class="input-group input-group-merge">
		<input type="text" class="form-control" name="contract_demand" id="contract_demand" value="'.($edit?$file_data->contract_demand:"").'" required />
		<span class="input-group-text" id="basic-addon33">KVA/KW</span>
		</div>		
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of Power Release</label>
		<input type="date" class="form-control" name="date_of_power_release" id="date_of_power_release" max="9999-12-31" value="'.($edit?$file_data->date_of_power_release:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of initiation of Forward / Backward Integration</label>
		<input type="date" class="form-control" name="date_of_integration" id="date_of_integration" max="9999-12-31" value="'.($edit?$file_data->date_of_integration:"").'" onblur="annexure_expansion_month_tbl(this.value)" required />
		</div>
		</div>

		<div id="month_year_annexure2_div">';
		if($edit){
			$html.='<div class="table-responsive text-nowrap">
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
			
			$count = count($data);
			for($j=0;$j<$count;$j++){
				
				$html.='<tr>
				<td>'.($j+1).'</td>
				<td name="month'.$j.'"><input type="hidden" name="month'.$j.'" id="month'.$j.'" value="'.$data[$j]->month.'">'.$data[$j]->month.'</td>
				<td name="unit'.$j.'"><input type="text" class="form-control" name="unit'.$j.'" id="unit'.$j.'" required value="'.$data[$j]->unit.'"></td>
				<td name="remarks'.$j.'"><input type="text" class="form-control" name="remarks'.$j.'" id="remarks'.$j.'" value="'.$data[$j]->remarks.'"></td>
				</tr>';
			} 
			$html.='</tbody>
			</table>
			</div>';
		}
		$html.='</div>';

		$html.= '<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure_pt2_expansion_update" id="btn_annexure_pt2_expansion_update" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure_pt2_expansion" id="btn_annexure_pt2_expansion" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}

	if($_REQUEST['action']=="annexure_pt_3_expansion")
	{	
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();

		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
			$data=$file_data->srno;
		}
		else{
			$edit = false;
		}

		
		$html = '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}
		$html.='<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of Company letter</label>
		<input type="date" class="form-control" name="company_letter_date" id="company_letter_date"  value="'.($edit?$file_data->company_letter_date:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Provisional sanction Letter No.</label>
		<input type="text" class="form-control" name="provisional_sanction_letter_no" id="provisional_sanction_letter_no" value="'.($edit?$file_data->provisional_sanction_letter_no:"").'" required />
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Provisional sanction Letter Date</label>
		<input type="date" class="form-control" name="provisional_sanction_letter_date" id="provisional_sanction_letter_date" max="9999-12-31" value="'.($edit?$file_data->provisional_sanction_letter_date:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Sub Meter No.</label>
		<input type="text" class="form-control" name="sub_meter_no" id="sub_meter_no" value="'.($edit?$file_data->sub_meter_no:"").'" required />
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Old Customer Service No.</label>
		<input type="text" class="form-control" name="old_customer_service_no" id="old_customer_service_no" value="'.($edit?$file_data->old_customer_service_no:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">New Customer Service No.</label>
		<input type="text" class="form-control" name="new_customer_service_no" id="new_customer_service_no" value="'.($edit?$file_data->new_customer_service_no:"").'" required />
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of Commencement of commercial Production</label>
		<input type="date" class="form-control" name="date_of_production" id="date_of_production" max="9999-12-31" value="'.($edit?$file_data->date_of_production:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Contract Demand</label>
		<div class="input-group input-group-merge">
		<input type="text" class="form-control" name="contract_demand" id="contract_demand" value="'.($edit?$file_data->contract_demand:"").'" required />
		<span class="input-group-text" id="basic-addon33">KVA/KW</span>
		</div>		
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of Power Release</label>
		<input type="date" class="form-control" name="date_of_power_release" id="date_of_power_release" max="9999-12-31" value="'.($edit?$file_data->date_of_power_release:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of initiation of Forward / Backward Integration</label>
		<input type="date" class="form-control" name="date_of_integration" id="date_of_integration" max="9999-12-31" value="'.($edit?$file_data->date_of_integration:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Tarrif Subsidy Period(From)</label>
		<input type="date" class="form-control" name="tarrif_subsidy_period_from" id="tarrif_subsidy_period_from" max="9999-12-31" value="'.($edit?$file_data->tarrif_subsidy_period_from:"").'" onblur="annexure3_expansion_month_tbl(this.value,tarrif_subsidy_period_to.value)" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Tarrif Subsidy Period(To)</label>
		<input type="date" class="form-control" name="tarrif_subsidy_period_to" id="tarrif_subsidy_period_to" max="9999-12-31" value="'.($edit?$file_data->tarrif_subsidy_period_to:"").'" onblur="annexure3_expansion_month_tbl(tarrif_subsidy_period_from.value,this.value)" required />
		</div>
		</div>
		<div id="month_year_annexure3_div">';
		if($edit){
			
			$html.='<div class="table-responsive text-nowrap">
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
			
			$count = count($data);
			for ($j = 0; $j<$count; $j++) {
				$html.='<tr>
				<td>'.($j+1).'</td>
				<td name="month'.$j.'">
				<input type="hidden" name="month'.$j.'" id="month'.$j.'" value="'.$data[$j]->month.'" class="form-control">
				'.$data[$j]->month.'
				</td>
				<td name="unit'.$j.'"><input type="text" name="unit'.$j.'" id="unit'.$j.'" value="'.$data[$j]->unit.'" class="form-control" required></td>
				<td name="remarks'.$j.'"><input type="text" name="remarks'.$j.'" value="'.$data[$j]->remarks.'" class="form-control" id="remarks'.$j.'"></td>
				</tr>';
			}
			$html.='</tbody>
			</table>
			</div>';
		}
		$html.= '</div>';

		$html.='<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure_pt3_expansion_update" id="btn_annexure_pt3_expansion_update" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure_pt3_expansion" id="btn_annexure_pt3_expansion" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}
	if($_REQUEST['action']=="annexure_pt_4_expansion")
	{
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();

		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
			$data=$file_data->srno;
		}
		else{
			$edit = false;
		}

		
		$html = '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}
		$html.='<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of Company letter</label>
		<input type="date" class="form-control" name="company_letter_date" id="company_letter_date"  value="'.($edit?$file_data->company_letter_date:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Provisional sanction Letter No.</label>
		<input type="text" class="form-control" name="provisional_sanction_letter_no" id="provisional_sanction_letter_no" value="'.($edit?$file_data->provisional_sanction_letter_no:"").'" required />
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Provisional sanction Letter Date</label>
		<input type="date" class="form-control" name="provisional_sanction_letter_date" id="provisional_sanction_letter_date" max="9999-12-31" value="'.($edit?$file_data->provisional_sanction_letter_date:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Main Meter No.</label>
		<input type="text" class="form-control" name="main_meter_no" id="main_meter_no" value="'.($edit?$file_data->main_meter_no:"").'" required />
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Old Customer Service No.</label>
		<input type="text" class="form-control" name="old_customer_service_no" id="old_customer_service_no" value="'.($edit?$file_data->old_customer_service_no:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">New Customer Service No.</label>
		<input type="text" class="form-control" name="new_customer_service_no" id="new_customer_service_no" value="'.($edit?$file_data->new_customer_service_no:"").'" required />
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of Commencement of commercial Production</label>
		<input type="date" class="form-control" name="date_of_production" id="date_of_production" max="9999-12-31" value="'.($edit?$file_data->date_of_production:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Contract Demand</label>
		<div class="input-group input-group-merge">
		<input type="text" class="form-control" name="contract_demand" id="contract_demand" value="'.($edit?$file_data->contract_demand:"").'" required />
		<span class="input-group-text" id="basic-addon33">KVA/KW</span>
		</div>		
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of Power Release</label>
		<input type="date" class="form-control" name="date_of_power_release" id="date_of_power_release" max="9999-12-31" value="'.($edit?$file_data->date_of_power_release:"").'" required />
		</div>
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Date of initiation of Forward / Backward Integration</label>
		<input type="date" class="form-control" name="date_of_integration" id="date_of_integration" max="9999-12-31" value="'.($edit?$file_data->date_of_integration:"").'" required />
		</div>
		</div>

		<div class="row">
		<div class="col mb-3">
		<label class="form-label" for="basic-default-fullname">Tarrif Subsidy Period(From)</label>
		<input type="date" class="form-control" name="tarrif_subsidy_period_from" id="tarrif_subsidy_period_from" max="9999-12-31" value="'.($edit?$file_data->tarrif_subsidy_period_from:"").'" onchange="annexure4_expansion_month_tbl(this.value,tarrif_subsidy_period_to.value)" required />
		</div>
		<div class="col md-6">
		<label class="form-label" for="basic-default-fullname">Tarrif Subsidy Period(To)</label>
		<input type="date" class="form-control" name="tarrif_subsidy_period_to" id="tarrif_subsidy_period_to" max="9999-12-31" value="'.($edit?$file_data->tarrif_subsidy_period_to:"").'" onchange="annexure4_expansion_month_tbl(tarrif_subsidy_period_from.value,this.value)" required />
		</div>
		</div>
		<div id="month_year_annexure4_div">';
		if($edit){
			$html.='<div class="table-responsive text-nowrap">
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
			$count = count($data);
			for ($j = 0; $j<$count; $j++) {
				$html.='<tr>
				<td>'.($j+1).'</td>
				<td name="month'.$j.'">
				<input type="hidden" name="month'.$j.'" id="month'.$j.'" value="'.$data[$j]->month.'" class="form-control">
				'.$data[$j]->month.'
				</td>
				<td name="unit'.$j.'"><input type="text" name="unit'.$j.'" id="unit'.$j.'" value="'.$data[$j]->unit.'" class="form-control" required></td>
				<td name="remarks'.$j.'"><input type="text" name="remarks'.$j.'" value="'.$data[$j]->remarks.'" class="form-control" id="remarks'.$j.'"></td>
				</tr>';
			}
			$html.='</tbody>
			</table>
			</div>';
		}
		$html.= '</div>';

		$html.='<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure_pt4_expansion_update" id="btn_annexure_pt4_expansion_update" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure_pt4_expansion" id="btn_annexure_pt4_expansion" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}

	if($_REQUEST['action']=="annexure_pt_5")
	{
		$html="";

		$scheme_id = $_REQUEST['scheme_id'];
		$stage_id = $_REQUEST['stage_id'];
		$file_id = $_REQUEST['file_id'];
		$inq_id = $_REQUEST['inq_id'];

		$stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
		$stmt_files->bind_param("iiii",$scheme_id,$stage_id,$file_id,$inq_id);
		$stmt_files->execute();
		$result_files = $stmt_files->get_result();
		$stmt_files->close();

		if(mysqli_num_rows($result_files)>0){
			$edit = true;
			$res = mysqli_fetch_array($result_files);
			$file_data=json_decode($res["file_data"]);
			$data=$file_data->srno;
		}
		else{
			$edit = false;
		}

		
		$html = '<div class="modal-body" ><div class="row">
		<input type="hidden" class="form-control" name="scheme_id" id="scheme_id" value="'.$scheme_id.'"/>
		<input type="hidden" class="form-control" name="stage_id" id="stage_id" value="'.$stage_id.'"/>
		<input type="hidden" class="form-control" name="file_id" id="file_id" value="'.$file_id.'"/>
		<input type="hidden" class="form-control" name="inq_id" id="inq_id" value="'.$inq_id.'"/>';
		if($edit){
			$html.='<input type="hidden" class="form-control" name="pr_file_data_id" id="pr_file_data_id" value="'.$res["id"].'"/>';
		}
		$html.='<div class="row mb-3">
		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Date of Company letter</label>
		<input type="date" class="form-control" name="company_letter_date" id="company_letter_date"  value="'.($edit?$file_data->company_letter_date:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Provisional sanction Letter No.</label>
		<input type="text" class="form-control" name="provisional_sanction_letter_no" id="provisional_sanction_letter_no" value="'.($edit?$file_data->provisional_sanction_letter_no:"").'" required />
		</div>
		</div>
		<div class="row mb-3">

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Provisional sanction Letter Date</label>
		<input type="date" class="form-control" name="provisional_sanction_letter_date" id="provisional_sanction_letter_date" max="9999-12-31" value="'.($edit?$file_data->provisional_sanction_letter_date:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Customer Service No.</label>
		<input type="text" class="form-control" name="customer_service_no" id="customer_service_no" value="'.($edit?$file_data->customer_service_no:"").'" required />
		</div>
		</div>
		<div class="row mb-3">

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Main Meter No.</label>
		<input type="text" class="form-control" name="main_meter_no" id="main_meter_no" value="'.($edit?$file_data->main_meter_no:"").'" required />
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Date of Commencement of commercial Production</label>
		<input type="date" class="form-control" name="date_of_production" id="date_of_production" max="9999-12-31" value="'.($edit?$file_data->date_of_production:"").'" required />
		</div>
		</div>
		<div class="row mb-3">
		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Contract Demand</label>		
		<div class="input-group input-group-merge">
		<input type="text" class="form-control" name="contract_demand" id="contract_demand" value="'.($edit?$file_data->contract_demand:"").'" required />
		<span class="input-group-text" id="basic-addon33">KVA/KW</span>
		</div>
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Date of Power Release</label>
		<input type="date" class="form-control" name="date_of_power_release" id="date_of_power_release" max="9999-12-31" value="'.($edit?$file_data->date_of_power_release:"").'" required />
		</div>
		</div>
		<div class="row">
		<div class="col mb-3">


		<label class="form-label" for="basic-default-fullname">Date of initiation of Forward / Backward Integration</label>
		<input type="date" class="form-control" name="date_of_integration" id="date_of_integration" max="9999-12-31" value="'.($edit?$file_data->date_of_integration:"").'"/>
		</div>

		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Renewable Power Generation Facility</label>
		<div class="input-group input-group-merge">
		<input type="text" class="form-control" name="renewable_power_generation_facility" id="renewable_power_generation_facility" value="'.($edit?$file_data->renewable_power_generation_facility:"").'" required />
		<span class="input-group-text" id="basic-addon33">Wind / Solar / etc</span>
		</div>
		</div>
		</div>

		<div class="row mb-3">
		<div class="col mb-6">
		<label class="form-label" for="basic-default-fullname">Renewable Power Generation capicity</label>
		<div class="input-group input-group-merge">
		<input type="text" class="form-control" name="renewable_power_generation_capacity" id="renewable_power_generation_capacity" value="'.($edit?$file_data->renewable_power_generation_capacity:"").'" required />
		<span class="input-group-text" id="basic-addon33">KVA/KW</span>
		</div>
		</div>
		<div class="col mb-6"></div> 
		</div>

		<div class="row md-3">
		<div class="col md-6">
		<label class="form-label" for="basic-default-fullname">Tarrif Subsidy Period(From)</label>
		<input type="date" class="form-control" name="tarrif_subsidy_period_from" id="tarrif_subsidy_period_from" max="9999-12-31" value="'.($edit?$file_data->tarrif_subsidy_period_from:"").'" onchange="annexure5_month_tbl(this.value,tarrif_subsidy_period_to.value)" required />
		</div>
		<div class="col md-6">
		<label class="form-label" for="basic-default-fullname">Tarrif Subsidy Period(To)</label>
		<input type="date" class="form-control" name="tarrif_subsidy_period_to" id="tarrif_subsidy_period_to" max="9999-12-31" value="'.($edit?$file_data->tarrif_subsidy_period_to:"").'" onchange="annexure5_month_tbl(tarrif_subsidy_period_from.value,this.value)" required />
		</div>
		</div>
		<div id="month_year_annexure5_div">';
		if($edit){
			$html.='<div class="table-responsive text-nowrap">
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
			$count = count($data);
			for ($j = 0; $j<$count; $j++) {
				$html.='<tr>
				<td>'.($j+1).'</td>
				<td name="month'.$j.'">
				<input type="hidden" name="month'.$j.'" id="month'.$j.'" value="'.$data[$j]->month.'" class="form-control">
				'.$data[$j]->month.'
				</td>
				<td name="unit'.$j.'"><input type="text" name="unit'.$j.'" id="unit'.$j.'" value="'.$data[$j]->unit.'" class="form-control" required></td>
				<td name="remarks'.$j.'"><input type="text" name="remarks'.$j.'" value="'.$data[$j]->remarks.'" class="form-control" id="remarks'.$j.'"></td>
				</tr>';
			}
			$html.='</tbody>
			</table>
			</div>';
			
		}
		$html.= '</div>';

		$html.='<div class="modal-footer">';
		if($edit){
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure_pt5_update" id="btn_annexure_pt5_update" value="Update Changes">';
		} else{
			$html.='<input type="submit" class="btn btn-primary" name="btn_annexure_pt5" id="btn_annexure_pt5" value="Save Changes">';
		}
		$html.='<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
		</div>';

		echo $html;
	}
}

?>