<?php
ob_start(); 
include "header.php";
error_reporting(E_ALL);
$service_id = $_COOKIE['service_id'];
$scheme_name = $_REQUEST['scheme_name'];
$claim = $_REQUEST['claim'];

$stmt_stage = $obj->con1->prepare("SELECT ta.*, tapp.app_data FROM tbl_tdtatassign ta inner join tbl_tdtatclaim tc on ta.tatclaim_id = tc.tatassign_id and tc.claim_date_start<='".date('Y-m-d')."' inner join tbl_tdapplication tapp on tapp.inq_id = tc.tatassign_inq_id inner join tbl_service_master sm on sm.id = tc.service_id where ta.service_id='".$service_id."' and ta.tatassign_id in (select max(tatassign_id) from tbl_tdtatassign GROUP by tatclaim_id) and ta.tatclaim_id in (SELECT tatassign_id FROM tbl_tdtatclaim where tatassign_id in (select max(tatassign_id) from tbl_tdtatclaim where claim_date_start<='".date('Y-m-d')."' and claim_current='yes' and service_id='".$service_id."' group by service_id,tatassign_inq_id)) group by tc.tatassign_id having ta.tatassign_status='".$scheme_name."' and ta.tatassign_user_id = '".$user_id."' order by ta.tatassign_id");
$stmt_stage->execute();
$stage_result = $stmt_stage->get_result();
$stmt_stage->close();
$total_count = mysqli_num_rows($stage_result);

if(isset($_REQUEST['btn_ca_certi_newfirm']))
{
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];

  $acquired_assets_dt = $_REQUEST['acquired_assets_dt'];
  $manufacturing_prod = $_REQUEST['manufacturing_prod'];
  $commercial_date = $_REQUEST['commercial_date'];
  $first_invoice_date = $_REQUEST['first_invoice_date'];
  $invoice_value = $_REQUEST['invoice_value'];
  $land = $_REQUEST['land'];
  $building_shed = $_REQUEST['building_shed'];
  $plant_mc = $_REQUEST['plant_mc'];
  $electrification = $_REQUEST['electrification'];
  $tools_equipment = $_REQUEST['tools_equipment'];
  $accessories = $_REQUEST['accessories'];
  $utilities = $_REQUEST['utilities'];
  $investments = $_REQUEST['investments'];
  $capital = $_REQUEST['capital'];
  $premium = $_REQUEST['premium'];
  $term_loan = $_REQUEST['term_loan'];
  $capital_loan = $_REQUEST['capital_loan'];
  $internal_source = $_REQUEST['internal_source'];
  $others = $_REQUEST['others'];
  $status = 'Completed';

  $total_gross_capital = floatval($land) + floatval($building_shed) + floatval($plant_mc) + floatval($electrification) + floatval($tools_equipment) + floatval($accessories) + floatval($utilities) + floatval($investments);

  $total_amount_finance = floatval($capital) + floatval($premium) + floatval($term_loan) + floatval($capital_loan) + floatval($internal_source) + floatval($others);

  try
  {
    $cp = Array (
      "acquired_assets_dt" => $acquired_assets_dt,
      "manufacturing_prod" => $manufacturing_prod,
      "commercial_date" => $commercial_date,
      "first_invoice_date" => $first_invoice_date,
      "invoice_value" => $invoice_value,
      "land" => $land,
      "building_shed" => $building_shed,
      "plant_mc" => $plant_mc,
      "electrification" => $electrification,
      "tools_equipment" => $tools_equipment,
      "accessories" => $accessories,
      "utilities" => $utilities,
      "investments" => $investments,
      "total_gross_capital" => $total_gross_capital,
      "capital" => $capital,
      "premium" => $premium,
      "term_loan" => $term_loan,
      "capital_loan" => $capital_loan,
      "internal_source" => $internal_source,
      "others" => $others,
      "total_amount_finance" => $total_amount_finance
    );

    // Encode array to json
    $json = json_encode($cp);

    $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("iiiiss",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
   setcookie("msg", "data",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
 else
 {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
}

if(isset($_REQUEST['btn_update_ca_certi_newfirm']))
{
  $pr_file_data_id = $_REQUEST['pr_file_data_id'];
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];

  $acquired_assets_dt = $_REQUEST['acquired_assets_dt'];
  $manufacturing_prod = $_REQUEST['manufacturing_prod'];
  $commercial_date = $_REQUEST['commercial_date'];
  $first_invoice_date = $_REQUEST['first_invoice_date'];
  $invoice_value = $_REQUEST['invoice_value'];
  $land = $_REQUEST['land'];
  $building_shed = $_REQUEST['building_shed'];
  $plant_mc = $_REQUEST['plant_mc'];
  $electrification = $_REQUEST['electrification'];
  $tools_equipment = $_REQUEST['tools_equipment'];
  $accessories = $_REQUEST['accessories'];
  $utilities = $_REQUEST['utilities'];
  $investments = $_REQUEST['investments'];
  $capital = $_REQUEST['capital'];
  $premium = $_REQUEST['premium'];
  $term_loan = $_REQUEST['term_loan'];
  $capital_loan = $_REQUEST['capital_loan'];
  $internal_source = $_REQUEST['internal_source'];
  $others = $_REQUEST['others'];
  $status = 'Completed';


  $total_gross_capital = floatval($land) + floatval($building_shed) + floatval($plant_mc) + floatval($electrification) + floatval($tools_equipment) + floatval($accessories) + floatval($utilities) + floatval($investments);

  $total_amount_finance = floatval($capital) + floatval($premium) + floatval($term_loan) + floatval($capital_loan) + floatval($internal_source) + floatval($others);

  try
  {
    $stmt = $obj->con1->prepare("UPDATE pr_files_data SET file_data = JSON_SET(file_data, '$.acquired_assets_dt', ?, '$.manufacturing_prod' , ?, '$.commercial_date' , ?, '$.first_invoice_date' , ?, '$.invoice_value' , ?, '$.land' , ?, '$.building_shed' , ?, '$.plant_mc' , ?, '$.electrification' , ?, '$.tools_equipment' , ?, '$.accessories' , ?, '$.utilities' , ?, '$.investments' , ?, '$.total_gross_capital' , ?, '$.capital' , ?, '$.premium' , ?, '$.term_loan' , ?, '$.capital_loan' , ?, '$.internal_source' , ?, '$.others' , ?, '$.total_amount_finance' , ? ) WHERE id=?");
    $stmt->bind_param("sssssssssssssssssssssi",$acquired_assets_dt, $manufacturing_prod, $commercial_date, $first_invoice_date, $invoice_value, $land, $building_shed, $plant_mc, $electrification, $tools_equipment, $accessories, $utilities, $investments, $total_gross_capital, $capital, $premium, $term_loan, $capital_loan, $internal_source, $others, $total_amount_finance,$pr_file_data_id);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in updating! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
   setcookie("msg", "update",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
 else
 {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
}

if(isset($_REQUEST['btn_affidavit_gogtp']))
{
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];

  $mana_local_male = $_REQUEST['mana_local_male'];
  $mana_local_female = $_REQUEST['mana_local_female'];
  $mana_outside_male = $_REQUEST['mana_outside_male'];
  $mana_outside_female = $_REQUEST['mana_outside_female'];
  $worker_local_male = $_REQUEST['worker_local_male'];
  $worker_local_female = $_REQUEST['worker_local_female'];
  $worker_outside_male = $_REQUEST['worker_outside_male'];
  $worker_outside_female = $_REQUEST['worker_outside_female'];
  $status = 'Completed';

  $total_male_manager = intval($mana_local_male) + intval($mana_outside_male);
  $total_female_manager = intval($mana_local_female) + intval($mana_outside_female);
  $total_manager = intval($total_male_manager) + intval($total_female_manager);
  $total_male_worker = intval($worker_local_male) + intval($worker_outside_male);
  $total_female_worker = intval($worker_local_female) + intval($worker_outside_female);
  $total_worker = intval($total_male_worker) + intval($total_female_worker);

  $total_local_male = intval($mana_local_male) + intval($worker_local_male);
  $total_local_female = intval($mana_local_female) + intval($worker_local_female);
  $total_outside_male = intval($mana_outside_male) + intval($worker_outside_male);
  $total_outside_female = intval($mana_outside_female) + intval($worker_outside_female);
  $total_male = intval($total_male_manager) + intval($total_male_worker);
  $total_female = intval($total_female_manager) + intval($total_female_worker);
  $final_total = intval($total_male) + intval($total_female);

  $mana_percenatge = ((intval($mana_local_male)+intval($mana_local_female))/intval($total_manager))*100;
  $worker_percenatge = ((intval($worker_local_male)+intval($worker_local_female))/intval($total_worker))*100;

  try
  {
    $cp = Array (
      "mana_local_male" => $mana_local_male,
      "mana_local_female" => $mana_local_female,
      "mana_outside_male" => $mana_outside_male,
      "mana_outside_female" => $mana_outside_female,
      "mana_percenatge" => $mana_percenatge,
      "worker_local_male" => $worker_local_male,
      "worker_local_female" => $worker_local_female,
      "worker_outside_male" => $worker_outside_male,
      "worker_outside_female" => $worker_outside_female,
      "worker_percenatge" => $worker_percenatge,
      "total_male_manager" => $total_male_manager,
      "total_female_manager" => $total_female_manager,
      "total_manager" => $total_manager,
      "total_male_worker" => $total_male_worker,
      "total_female_worker" => $total_female_worker,
      "total_worker" => $total_worker,
      "total_local_male" => $total_local_male,
      "total_local_female" => $total_local_female,
      "total_outside_male" => $total_outside_male,
      "total_outside_female" => $total_outside_female,
      "total_male" => $total_male,
      "total_female" => $total_female,
      "final_total" => $final_total
    );

    // Encode array to json
    $json = json_encode($cp);

    $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("iiiiss",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
   setcookie("msg", "data",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
 else
 {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
}

if(isset($_REQUEST['btn_update_affidavit_gogtp']))
{
  $pr_file_data_id = $_REQUEST['pr_file_data_id'];
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];

  $mana_local_male = $_REQUEST['mana_local_male'];
  $mana_local_female = $_REQUEST['mana_local_female'];
  $mana_outside_male = $_REQUEST['mana_outside_male'];
  $mana_outside_female = $_REQUEST['mana_outside_female'];
  $worker_local_male = $_REQUEST['worker_local_male'];
  $worker_local_female = $_REQUEST['worker_local_female'];
  $worker_outside_male = $_REQUEST['worker_outside_male'];
  $worker_outside_female = $_REQUEST['worker_outside_female'];
  $status = 'Completed';

  $total_male_manager = intval($mana_local_male) + intval($mana_outside_male);
  $total_female_manager = intval($mana_local_female) + intval($mana_outside_female);
  $total_manager = intval($total_male_manager) + intval($total_female_manager);
  $total_male_worker = intval($worker_local_male) + intval($worker_outside_male);
  $total_female_worker = intval($worker_local_female) + intval($worker_outside_female);
  $total_worker = intval($total_male_worker) + intval($total_female_worker);

  $total_local_male = intval($mana_local_male) + intval($worker_local_male);
  $total_local_female = intval($mana_local_female) + intval($worker_local_female);
  $total_outside_male = intval($mana_outside_male) + intval($worker_outside_male);
  $total_outside_female = intval($mana_outside_female) + intval($worker_outside_female);
  $total_male = intval($total_male_manager) + intval($total_male_worker);
  $total_female = intval($total_female_manager) + intval($total_female_worker);
  $final_total = intval($total_male) + intval($total_female);

  $mana_percenatge = ((intval($mana_local_male)+intval($mana_local_female))/intval($total_manager))*100;
  $worker_percenatge = ((intval($worker_local_male)+intval($worker_local_female))/intval($total_worker))*100;

  try
  {
    $stmt = $obj->con1->prepare("UPDATE pr_files_data SET file_data = JSON_SET(file_data, '$.mana_local_male', ?, '$.mana_local_female', ?, '$.mana_outside_male', ?, '$.mana_outside_female', ?, '$.mana_percenatge', ?, '$.worker_local_male', ?, '$.worker_local_female', ?, '$.worker_outside_male', ?, '$.worker_outside_female', ?, '$.worker_percenatge', ?, '$.total_male_manager', ?, '$.total_female_manager', ?, '$.total_manager', ?, '$.total_male_worker', ?, '$.total_female_worker', ?, '$.total_worker', ?, '$.total_local_male', ?, '$.total_local_female', ?, '$.total_outside_male', ?, '$.total_outside_female', ?, '$.total_male', ?, '$.total_female', ?, '$.final_total', ?) WHERE id=?");
    $stmt->bind_param("sssssssssssssssssssssssi", $mana_local_male, $mana_local_female, $mana_outside_male, $mana_outside_female, $mana_percenatge, $worker_local_male, $worker_local_female, $worker_outside_male, $worker_outside_female, $worker_percenatge, $total_male_manager, $total_female_manager, $total_manager, $total_male_worker, $total_female_worker, $total_worker, $total_local_male, $total_local_female, $total_outside_male, $total_outside_female, $total_male, $total_female, $final_total, $pr_file_data_id);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in updating! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
   setcookie("msg", "update",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
 else
 {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
}

if(isset($_REQUEST['btn_ca_certi_expansion']))
{
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];
  $ini_expansion_dt = $_REQUEST['ini_expansion_dt'];
  $total_investment_dt = $_REQUEST['total_investment_dt'];
  $from_expansion_dt = $_REQUEST['from_expansion_dt'];
  $to_initiating_dt = $_REQUEST['to_initiating_dt'];
  $ini_investment_land = $_REQUEST['ini_investment_land'];
  $ini_investment_building = $_REQUEST['ini_investment_building'];
  $ini_investment_plant = $_REQUEST['ini_investment_plant'];
  $ini_investment_utilities = $_REQUEST['ini_investment_utilities'];
  $ini_investment_tools = $_REQUEST['ini_investment_tools'];
  $ini_investment_electric = $_REQUEST['ini_investment_electric'];
  $ini_investment_assets = $_REQUEST['ini_investment_assets'];
  $comm_investment_land = $_REQUEST['comm_investment_land'];
  $comm_investment_building = $_REQUEST['comm_investment_building'];
  $comm_investment_plant = $_REQUEST['comm_investment_plant'];
  $comm_investment_utilities = $_REQUEST['comm_investment_utilities'];
  $comm_investment_tools = $_REQUEST['comm_investment_tools'];
  $comm_investment_electric = $_REQUEST['comm_investment_electric'];
  $comm_investment_assets = $_REQUEST['comm_investment_assets'];
  $status = 'Completed';

  $total_land = intval($ini_investment_land) + intval($comm_investment_land);
  $total_building = intval($ini_investment_building) + intval($comm_investment_building);
  $total_plant = intval($ini_investment_plant) + intval($comm_investment_plant);
  $total_utilities = intval($ini_investment_utilities) + intval($comm_investment_utilities);
  $total_tools = intval($ini_investment_tools) + intval($comm_investment_tools);
  $total_electric = intval($ini_investment_electric) + intval($comm_investment_electric);
  $total_assets = intval($ini_investment_assets) + intval($comm_investment_assets);

  $total_ini_investment = intval($ini_investment_land) + intval($ini_investment_building) + intval($ini_investment_plant) + intval($ini_investment_utilities) + intval($ini_investment_tools) + intval($ini_investment_electric) + intval($ini_investment_assets);
  $total_comm_investment = intval($comm_investment_land) + intval($comm_investment_building) + intval($comm_investment_plant) + intval($comm_investment_utilities) + intval($comm_investment_tools) + intval($comm_investment_electric) + intval($comm_investment_assets);
  $final_total = intval($total_ini_investment) + intval($total_comm_investment);

  try
  {
    $cp = Array (
      "ini_expansion_dt" => $ini_expansion_dt,
      "total_investment_dt" => $total_investment_dt,
      "from_expansion_dt" => $from_expansion_dt,
      "to_initiating_dt" => $to_initiating_dt,
      "ini_investment_land" => $ini_investment_land,
      "ini_investment_building" => $ini_investment_building,
      "ini_investment_plant" => $ini_investment_plant,
      "ini_investment_utilities" => $ini_investment_utilities,
      "ini_investment_tools" => $ini_investment_tools,
      "ini_investment_electric" => $ini_investment_electric,
      "ini_investment_assets" => $ini_investment_assets,
      "comm_investment_land" => $comm_investment_land,
      "comm_investment_building" => $comm_investment_building,
      "comm_investment_plant" => $comm_investment_plant,
      "comm_investment_utilities" => $comm_investment_utilities,
      "comm_investment_tools" => $comm_investment_tools,
      "comm_investment_electric" => $comm_investment_electric,
      "comm_investment_assets" => $comm_investment_assets,
      "total_land" => $total_land,
      "total_building" => $total_building,
      "total_plant" => $total_plant,
      "total_utilities" => $total_utilities,
      "total_tools" => $total_tools,
      "total_electric" => $total_electric,
      "total_assets" => $total_assets,
      "total_ini_investment" => $total_ini_investment,
      "total_comm_investment" => $total_comm_investment,
      "final_total" => $final_total
    );

    // Encode array to json
    $json = json_encode($cp);

    $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("iiiiss",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
   setcookie("msg", "data",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
 else
 {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
}

if(isset($_REQUEST['btn_update_ca_certi_expansion']))
{
  $pr_file_data_id = $_REQUEST['pr_file_data_id'];
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];

  $ini_expansion_dt = $_REQUEST['ini_expansion_dt'];
  $total_investment_dt = $_REQUEST['total_investment_dt'];
  $from_expansion_dt = $_REQUEST['from_expansion_dt'];
  $to_initiating_dt = $_REQUEST['to_initiating_dt'];
  $ini_investment_land = $_REQUEST['ini_investment_land'];
  $ini_investment_building = $_REQUEST['ini_investment_building'];
  $ini_investment_plant = $_REQUEST['ini_investment_plant'];
  $ini_investment_utilities = $_REQUEST['ini_investment_utilities'];
  $ini_investment_tools = $_REQUEST['ini_investment_tools'];
  $ini_investment_electric = $_REQUEST['ini_investment_electric'];
  $ini_investment_assets = $_REQUEST['ini_investment_assets'];
  $comm_investment_land = $_REQUEST['comm_investment_land'];
  $comm_investment_building = $_REQUEST['comm_investment_building'];
  $comm_investment_plant = $_REQUEST['comm_investment_plant'];
  $comm_investment_utilities = $_REQUEST['comm_investment_utilities'];
  $comm_investment_tools = $_REQUEST['comm_investment_tools'];
  $comm_investment_electric = $_REQUEST['comm_investment_electric'];
  $comm_investment_assets = $_REQUEST['comm_investment_assets'];
  $status = 'Completed';

  $total_land = intval($ini_investment_land) + intval($comm_investment_land);
  $total_building = intval($ini_investment_building) + intval($comm_investment_building);
  $total_plant = intval($ini_investment_plant) + intval($comm_investment_plant);
  $total_utilities = intval($ini_investment_utilities) + intval($comm_investment_utilities);
  $total_tools = intval($ini_investment_tools) + intval($comm_investment_tools);
  $total_electric = intval($ini_investment_electric) + intval($comm_investment_electric);
  $total_assets = intval($ini_investment_assets) + intval($comm_investment_assets);

  $total_ini_investment = intval($ini_investment_land) + intval($ini_investment_building) + intval($ini_investment_plant) + intval($ini_investment_utilities) + intval($ini_investment_tools) + intval($ini_investment_electric) + intval($ini_investment_assets);
  $total_comm_investment = intval($comm_investment_land) + intval($comm_investment_building) + intval($comm_investment_plant) + intval($comm_investment_utilities) + intval($comm_investment_tools) + intval($comm_investment_electric) + intval($comm_investment_assets);
  $final_total = intval($total_ini_investment) + intval($total_comm_investment);

  try
  {
    $stmt = $obj->con1->prepare("UPDATE pr_files_data SET file_data = JSON_SET(file_data,  '$.ini_expansion_dt', ?, '$.total_investment_dt', ?, '$.from_expansion_dt', ?, '$.to_initiating_dt', ?, '$.ini_investment_land', ?, '$.ini_investment_building', ?, '$.ini_investment_plant', ?, '$.ini_investment_utilities', ?, '$.ini_investment_tools', ?, '$.ini_investment_electric', ?, '$.ini_investment_assets', ?, '$.comm_investment_land', ?, '$.comm_investment_building', ?, '$.comm_investment_plant', ?, '$.comm_investment_utilities', ?, '$.comm_investment_tools', ?, '$.comm_investment_electric', ?, '$.comm_investment_assets', ?, '$.total_land', ?, '$.total_building', ?, '$.total_plant', ?, '$.total_utilities', ?, '$.total_tools', ?, '$.total_electric', ?, '$.total_assets', ?, '$.total_ini_investment', ?, '$.total_comm_investment', ?, '$.final_total', ?) WHERE id=?");
    $stmt->bind_param("ssssssssssssssssssssssssssssi",$ini_expansion_dt, $total_investment_dt, $from_expansion_dt, $to_initiating_dt, $ini_investment_land, $ini_investment_building, $ini_investment_plant, $ini_investment_utilities, $ini_investment_tools, $ini_investment_electric, $ini_investment_assets, $comm_investment_land, $comm_investment_building, $comm_investment_plant, $comm_investment_utilities, $comm_investment_tools, $comm_investment_electric, $comm_investment_assets, $total_land, $total_building, $total_plant, $total_utilities, $total_tools, $total_electric, $total_assets, $total_ini_investment, $total_comm_investment, $final_total, $pr_file_data_id);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in updating! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
   setcookie("msg", "update",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
 else
 {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
}

if(isset($_REQUEST['btn_ce_certi_gogtp']))
{
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];
  $existing_gross_capital = $_REQUEST['existing_gross_capital'];
  $gross_capital = $_REQUEST['gross_capital'];
  $total_gross_capital = $_REQUEST['total_gross_capital'];
  $investment_increase_perc = $_REQUEST['investment_increase_perc'];
  $existing_capacity = $_REQUEST['existing_capacity'];
  $proposed_capacity = $_REQUEST['proposed_capacity'];
  $proposed_capacity_increase_perc = $_REQUEST['proposed_capacity_increase_perc'];
  $existing_capacity_second = $_REQUEST['existing_capacity_second'];
  $two_years_production_capacity = $_REQUEST['two_years_production_capacity'];
  $two_years_production_money = $_REQUEST['two_years_production_money'];
  $max_utilization_perc = $_REQUEST['max_utilization_perc'];
  $status = 'Completed';

  try
  {
    $cp = Array (
      "existing_gross_capital" => $existing_gross_capital,
      "gross_capital" => $gross_capital,
      "total_gross_capital" => $total_gross_capital,
      "investment_increase_perc" => $investment_increase_perc,
      "existing_capacity" => $existing_capacity,
      "proposed_capacity" => $proposed_capacity,
      "proposed_capacity_increase_perc" => $proposed_capacity_increase_perc,
      "existing_capacity_second" => $existing_capacity_second,
      "two_years_production_capacity" => $two_years_production_capacity,
      "two_years_production_money" => $two_years_production_money,
      "max_utilization_perc" => $max_utilization_perc
    );

    // Encode array to json
    $json = json_encode($cp);

    $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("iiiiss",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
   setcookie("msg", "data",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
 else
 {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
}

if(isset($_REQUEST['btn_update_ce_certi_gogtp']))
{
  $pr_file_data_id = $_REQUEST['pr_file_data_id'];
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];

  $existing_gross_capital = $_REQUEST['existing_gross_capital'];
  $gross_capital = $_REQUEST['gross_capital'];
  $total_gross_capital = $_REQUEST['total_gross_capital'];
  $investment_increase_perc = $_REQUEST['investment_increase_perc'];
  $existing_capacity = $_REQUEST['existing_capacity'];
  $proposed_capacity = $_REQUEST['proposed_capacity'];
  $proposed_capacity_increase_perc = $_REQUEST['proposed_capacity_increase_perc'];
  $existing_capacity_second = $_REQUEST['existing_capacity_second'];
  $two_years_production_capacity = $_REQUEST['two_years_production_capacity'];
  $two_years_production_money = $_REQUEST['two_years_production_money'];
  $max_utilization_perc = $_REQUEST['max_utilization_perc'];
  $status = 'Completed';

  try
  {
    $stmt = $obj->con1->prepare("UPDATE pr_files_data SET file_data = JSON_SET(file_data, '$.existing_gross_capital', ?, '$.gross_capital', ?, '$.total_gross_capital', ?, '$.investment_increase_perc', ?, '$.existing_capacity', ?, '$.proposed_capacity', ?, '$.proposed_capacity_increase_perc', ?, '$.existing_capacity_second', ?, '$.two_years_production_capacity', ?, '$.two_years_production_money', ?, '$.max_utilization_perc', ?) WHERE id=?");
    $stmt->bind_param("sssssssssssi",$existing_gross_capital, $gross_capital, $total_gross_capital, $investment_increase_perc, $existing_capacity, $proposed_capacity, $proposed_capacity_increase_perc, $existing_capacity_second, $two_years_production_capacity, $two_years_production_money, $max_utilization_perc, $pr_file_data_id);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in updating! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
   setcookie("msg", "update",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
 else
 {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
}

if(isset($_REQUEST['btn_certi_first_disbursement']))
{
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];
  $project = $_REQUEST['project'];
  $sanctioned_term_loan = $_REQUEST['sanctioned_term_loan'];
  $project_date = $_REQUEST['project_date'];
  $disbursed_term_loan = $_REQUEST['disbursed_term_loan'];
  $loan_account_no = $_REQUEST['loan_account_no'];
  $branch_manager_email = $_REQUEST['branch_manager_email'];
  $application_received_dt = $_REQUEST['application_received_dt'];
  $sanction_loan_dt = $_REQUEST['sanction_loan_dt'];
  $first_disbursement_dt = $_REQUEST['first_disbursement_dt'];
  $first_disbursement_price = $_REQUEST['first_disbursement_price'];
  $total_disbursement_price = $_REQUEST['total_disbursement_price'];
  $total_disbursement_dt = $_REQUEST['total_disbursement_dt'];
  $cost_land = $_REQUEST['cost_land'];
  $cost_building = $_REQUEST['cost_building'];
  $cost_plant = $_REQUEST['cost_plant'];
  $cost_electric = $_REQUEST['cost_electric'];
  $cost_tools = $_REQUEST['cost_tools'];
  $cost_accessories = $_REQUEST['cost_accessories'];
  $cost_utilities = $_REQUEST['cost_utilities'];
  $cost_other = $_REQUEST['cost_other'];
  $sanctioned_term_land = $_REQUEST['sanctioned_term_land'];
  $sanctioned_term_building = $_REQUEST['sanctioned_term_building'];
  $sanctioned_term_plant = $_REQUEST['sanctioned_term_plant'];
  $sanctioned_term_electric = $_REQUEST['sanctioned_term_electric'];
  $sanctioned_term_tools = $_REQUEST['sanctioned_term_tools'];
  $sanctioned_term_accessories = $_REQUEST['sanctioned_term_accessories'];
  $sanctioned_term_utilities = $_REQUEST['sanctioned_term_utilities'];
  $sanctioned_term_other = $_REQUEST['sanctioned_term_other'];
  $total_investment_land = $_REQUEST['total_investment_land'];
  $total_investment_building = $_REQUEST['total_investment_building'];
  $total_investment_plant = $_REQUEST['total_investment_plant'];
  $total_investment_electric = $_REQUEST['total_investment_electric'];
  $total_investment_tools = $_REQUEST['total_investment_tools'];
  $total_investment_accessories = $_REQUEST['total_investment_accessories'];
  $total_investment_utilities = $_REQUEST['total_investment_utilities'];
  $total_investment_other = $_REQUEST['total_investment_other'];
  $disbursed_term_land = $_REQUEST['disbursed_term_land'];
  $disbursed_term_building = $_REQUEST['disbursed_term_building'];
  $disbursed_term_plant = $_REQUEST['disbursed_term_plant'];
  $disbursed_term_electric = $_REQUEST['disbursed_term_electric'];
  $disbursed_term_tools = $_REQUEST['disbursed_term_tools'];
  $disbursed_term_accessories = $_REQUEST['disbursed_term_accessories'];
  $disbursed_term_utilities = $_REQUEST['disbursed_term_utilities'];
  $disbursed_term_other = $_REQUEST['disbursed_term_other'];
  $status = 'Completed';

  $cost_total = floatval($cost_land) + floatval($cost_building) + floatval($cost_plant) + floatval($cost_electric) + floatval($cost_tools) + floatval($cost_accessories) + floatval($cost_utilities) + floatval($cost_other);

  $sanctioned_term_total = floatval($sanctioned_term_land) + floatval($sanctioned_term_building) + floatval($sanctioned_term_plant) + floatval($sanctioned_term_electric) + floatval($sanctioned_term_tools) + floatval($sanctioned_term_accessories) + floatval($sanctioned_term_utilities) + floatval($sanctioned_term_other);

  $total_investment_total = floatval($total_investment_land) + floatval($total_investment_building) + floatval($total_investment_plant) + floatval($total_investment_electric) + floatval($total_investment_tools) + floatval($total_investment_accessories) + floatval($total_investment_utilities) + floatval($total_investment_other);

  $disbursed_term_total = floatval($disbursed_term_land) + floatval($disbursed_term_building) + floatval($disbursed_term_plant) + floatval($disbursed_term_electric) + floatval($disbursed_term_tools) + floatval($disbursed_term_accessories) + floatval($disbursed_term_utilities) + floatval($disbursed_term_other);

  try
  {
    $cp = Array (
      "project" => $project,
      "sanctioned_term_loan" => $sanctioned_term_loan,
      "project_date" => $project_date,
      "disbursed_term_loan" => $disbursed_term_loan,
      "loan_account_no" => $loan_account_no,
      "branch_manager_email" => $branch_manager_email,
      "application_received_dt" => $application_received_dt,
      "sanction_loan_dt" => $sanction_loan_dt,
      "first_disbursement_dt" => $first_disbursement_dt,
      "first_disbursement_price" => $first_disbursement_price,
      "total_disbursement_price" => $total_disbursement_price,
      "total_disbursement_dt" => $total_disbursement_dt,
      "cost_land" => $cost_land,
      "cost_building" => $cost_building,
      "cost_plant" => $cost_plant,
      "cost_electric" => $cost_electric,
      "cost_tools" => $cost_tools,
      "cost_accessories" => $cost_accessories,
      "cost_utilities" => $cost_utilities,
      "cost_other" => $cost_other,
      "sanctioned_term_land" => $sanctioned_term_land,
      "sanctioned_term_building" => $sanctioned_term_building,
      "sanctioned_term_plant" => $sanctioned_term_plant,
      "sanctioned_term_electric" => $sanctioned_term_electric,
      "sanctioned_term_tools" => $sanctioned_term_tools,
      "sanctioned_term_accessories" => $sanctioned_term_accessories,
      "sanctioned_term_utilities" => $sanctioned_term_utilities,
      "sanctioned_term_other" => $sanctioned_term_other,
      "total_investment_land" => $total_investment_land,
      "total_investment_building" => $total_investment_building,
      "total_investment_plant" => $total_investment_plant,
      "total_investment_electric" => $total_investment_electric,
      "total_investment_tools" => $total_investment_tools,
      "total_investment_accessories" => $total_investment_accessories,
      "total_investment_utilities" => $total_investment_utilities,
      "total_investment_other" => $total_investment_other,
      "disbursed_term_land" => $disbursed_term_land,
      "disbursed_term_building" => $disbursed_term_building,
      "disbursed_term_plant" => $disbursed_term_plant,
      "disbursed_term_electric" => $disbursed_term_electric,
      "disbursed_term_tools" => $disbursed_term_tools,
      "disbursed_term_accessories" => $disbursed_term_accessories,
      "disbursed_term_utilities" => $disbursed_term_utilities,
      "disbursed_term_other" => $disbursed_term_other,
      "cost_total" => $cost_total,
      "sanctioned_term_total" => $sanctioned_term_total,
      "total_investment_total" => $total_investment_total,
      "disbursed_term_total" => $disbursed_term_total,
    );

    // Encode array to json
    $json = json_encode($cp);

    $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("iiiiss",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
   setcookie("msg", "data",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
 else
 {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
}

if(isset($_REQUEST['btn_update_certi_first_disbursement']))
{
  $pr_file_data_id = $_REQUEST['pr_file_data_id'];
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];

  $project = $_REQUEST['project'];
  $sanctioned_term_loan = $_REQUEST['sanctioned_term_loan'];
  $project_date = $_REQUEST['project_date'];
  $disbursed_term_loan = $_REQUEST['disbursed_term_loan'];
  $loan_account_no = $_REQUEST['loan_account_no'];
  $branch_manager_email = $_REQUEST['branch_manager_email'];
  $application_received_dt = $_REQUEST['application_received_dt'];
  $sanction_loan_dt = $_REQUEST['sanction_loan_dt'];
  $first_disbursement_dt = $_REQUEST['first_disbursement_dt'];
  $first_disbursement_price = $_REQUEST['first_disbursement_price'];
  $total_disbursement_price = $_REQUEST['total_disbursement_price'];
  $total_disbursement_dt = $_REQUEST['total_disbursement_dt'];
  $cost_land = $_REQUEST['cost_land'];
  $cost_building = $_REQUEST['cost_building'];
  $cost_plant = $_REQUEST['cost_plant'];
  $cost_electric = $_REQUEST['cost_electric'];
  $cost_tools = $_REQUEST['cost_tools'];
  $cost_accessories = $_REQUEST['cost_accessories'];
  $cost_utilities = $_REQUEST['cost_utilities'];
  $cost_other = $_REQUEST['cost_other'];
  $sanctioned_term_land = $_REQUEST['sanctioned_term_land'];
  $sanctioned_term_building = $_REQUEST['sanctioned_term_building'];
  $sanctioned_term_plant = $_REQUEST['sanctioned_term_plant'];
  $sanctioned_term_electric = $_REQUEST['sanctioned_term_electric'];
  $sanctioned_term_tools = $_REQUEST['sanctioned_term_tools'];
  $sanctioned_term_accessories = $_REQUEST['sanctioned_term_accessories'];
  $sanctioned_term_utilities = $_REQUEST['sanctioned_term_utilities'];
  $sanctioned_term_other = $_REQUEST['sanctioned_term_other'];
  $total_investment_land = $_REQUEST['total_investment_land'];
  $total_investment_building = $_REQUEST['total_investment_building'];
  $total_investment_plant = $_REQUEST['total_investment_plant'];
  $total_investment_electric = $_REQUEST['total_investment_electric'];
  $total_investment_tools = $_REQUEST['total_investment_tools'];
  $total_investment_accessories = $_REQUEST['total_investment_accessories'];
  $total_investment_utilities = $_REQUEST['total_investment_utilities'];
  $total_investment_other = $_REQUEST['total_investment_other'];
  $disbursed_term_land = $_REQUEST['disbursed_term_land'];
  $disbursed_term_building = $_REQUEST['disbursed_term_building'];
  $disbursed_term_plant = $_REQUEST['disbursed_term_plant'];
  $disbursed_term_electric = $_REQUEST['disbursed_term_electric'];
  $disbursed_term_tools = $_REQUEST['disbursed_term_tools'];
  $disbursed_term_accessories = $_REQUEST['disbursed_term_accessories'];
  $disbursed_term_utilities = $_REQUEST['disbursed_term_utilities'];
  $disbursed_term_other = $_REQUEST['disbursed_term_other'];
  $status = 'Completed';

  $cost_total = floatval($cost_land) + floatval($cost_building) + floatval($cost_plant) + floatval($cost_electric) + floatval($cost_tools) + floatval($cost_accessories) + floatval($cost_utilities) + floatval($cost_other);

  $sanctioned_term_total = floatval($sanctioned_term_land) + floatval($sanctioned_term_building) + floatval($sanctioned_term_plant) + floatval($sanctioned_term_electric) + floatval($sanctioned_term_tools) + floatval($sanctioned_term_accessories) + floatval($sanctioned_term_utilities) + floatval($sanctioned_term_other);

  $total_investment_total = floatval($total_investment_land) + floatval($total_investment_building) + floatval($total_investment_plant) + floatval($total_investment_electric) + floatval($total_investment_tools) + floatval($total_investment_accessories) + floatval($total_investment_utilities) + floatval($total_investment_other);

  $disbursed_term_total = floatval($disbursed_term_land) + floatval($disbursed_term_building) + floatval($disbursed_term_plant) + floatval($disbursed_term_electric) + floatval($disbursed_term_tools) + floatval($disbursed_term_accessories) + floatval($disbursed_term_utilities) + floatval($disbursed_term_other);

  try
  {
    $stmt = $obj->con1->prepare("UPDATE pr_files_data SET file_data = JSON_SET(file_data, '$.project', ?, '$.sanctioned_term_loan', ?, '$.project_date', ?, '$.disbursed_term_loan', ?, '$.loan_account_no', ?, '$.branch_manager_email', ?, '$.application_received_dt', ?, '$.sanction_loan_dt', ?, '$.first_disbursement_dt', ?, '$.first_disbursement_price', ?, '$.total_disbursement_price', ?, '$.total_disbursement_dt', ?, '$.cost_land', ?, '$.cost_building', ?, '$.cost_plant', ?, '$.cost_electric', ?, '$.cost_tools', ?, '$.cost_accessories', ?, '$.cost_utilities', ?, '$.cost_other', ?, '$.sanctioned_term_land', ?, '$.sanctioned_term_building', ?, '$.sanctioned_term_plant', ?, '$.sanctioned_term_electric', ?, '$.sanctioned_term_tools', ?, '$.sanctioned_term_accessories', ?, '$.sanctioned_term_utilities', ?, '$.sanctioned_term_other', ?, '$.total_investment_land', ?, '$.total_investment_building', ?, '$.total_investment_plant', ?, '$.total_investment_electric', ?, '$.total_investment_tools', ?, '$.total_investment_accessories', ?, '$.total_investment_utilities', ?, '$.total_investment_other', ?, '$.disbursed_term_land', ?, '$.disbursed_term_building', ?, '$.disbursed_term_plant', ?, '$.disbursed_term_electric', ?, '$.disbursed_term_tools', ?, '$.disbursed_term_accessories', ?, '$.disbursed_term_utilities', ?, '$.disbursed_term_other', ?, '$.cost_total', ?, '$.sanctioned_term_total', ?, '$.total_investment_total', ?, '$.disbursed_term_total', ?) WHERE id=?");
    $stmt->bind_param("ssssssssssssssssssssssssssssssssssssssssssssssssi",$project, $sanctioned_term_loan, $project_date, $disbursed_term_loan, $loan_account_no, $branch_manager_email, $application_received_dt, $sanction_loan_dt, $first_disbursement_dt, $first_disbursement_price, $total_disbursement_price, $total_disbursement_dt, $cost_land, $cost_building, $cost_plant, $cost_electric, $cost_tools, $cost_accessories, $cost_utilities, $cost_other, $sanctioned_term_land, $sanctioned_term_building, $sanctioned_term_plant, $sanctioned_term_electric, $sanctioned_term_tools, $sanctioned_term_accessories, $sanctioned_term_utilities, $sanctioned_term_other, $total_investment_land, $total_investment_building, $total_investment_plant, $total_investment_electric, $total_investment_tools, $total_investment_accessories, $total_investment_utilities, $total_investment_other, $disbursed_term_land, $disbursed_term_building, $disbursed_term_plant, $disbursed_term_electric, $disbursed_term_tools, $disbursed_term_accessories, $disbursed_term_utilities, $disbursed_term_other, $cost_total, $sanctioned_term_total, $total_investment_total, $disbursed_term_total, $pr_file_data_id);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in updating! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
   setcookie("msg", "update",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
 else
 {
   setcookie("msg", "fail",time()+3600,"/");
   header("location:process_gogtp_pt.php");
 }
}

if(isset($_REQUEST['btn_employment_data_gogtp']))
{
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];
  $count = $_REQUEST['count'];
  $status = 'Completed';

  $data = array();
  $temp = array();
  $data["srno"] = array();

  for($i=1;$i<$count;$i++){
    $ename = $_REQUEST['ename'.$i];
    $address = $_REQUEST['address'.$i];
    $designation = $_REQUEST['designation'.$i];  
    $gender = $_REQUEST['gender'.$i];
    $stay = $_REQUEST['stay'.$i];
    $temp['ename'] = $ename;
    $temp['address'] = $address;
    $temp['designation'] = $designation;
    $temp['gender'] = $gender;
    $temp['stay'] = $stay;

    $temp = array_map('utf8_encode', $temp);
    array_push($data['srno'], $temp);
  }
  
  $json = json_encode($data);

  try
  {
    $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("iiiiss",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
    setcookie("msg", "data",time()+3600,"/");
    header("location:process_gogtp_pt.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:process_gogtp_pt.php");
  }
}

if(isset($_REQUEST['btn_update_employment_data_gogtp']))
{
  $pr_file_data_id = $_REQUEST['pr_file_data_id'];
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];
  $count = $_REQUEST['count'];

  $data = array();
  $temp = array();
  $data["srno"] = array();

  for($i=1;$i<$count;$i++){
    $ename = $_REQUEST['ename'.$i];
    $address = $_REQUEST['address'.$i];
    $designation = $_REQUEST['designation'.$i];  
    $gender = $_REQUEST['gender'.$i];
    $stay = $_REQUEST['stay'.$i];
    $temp['ename'] = $ename;
    $temp['address'] = $address;
    $temp['designation'] = $designation;
    $temp['gender'] = $gender;
    $temp['stay'] = $stay;

    $temp = array_map('utf8_encode', $temp);
    array_push($data, $temp);
  }
  
  $json = json_encode($data);

  try
  {
    $stmt = $obj->con1->prepare("UPDATE `pr_files_data` set `file_data`=? where `id`=?");
    $stmt->bind_param("si",$json,$pr_file_data_id);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in updating! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
    setcookie("msg", "update",time()+3600,"/");
    header("location:process_gogtp_pt.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:process_gogtp_pt.php");
  }
}

if(isset($_REQUEST['btn_annexure_pt1_new_unit']))
{
 $scheme_id = $_REQUEST['scheme_id'];
 $stage_id = $_REQUEST['stage_id'];
 $file_id = $_REQUEST['file_id'];
 $inq_id = $_REQUEST['inq_id'];
 $status = 'Completed';

 $company_letter_date = $_REQUEST['company_letter_date'];
 $provisional_sanction_letter_no = $_REQUEST['provisional_sanction_letter_no'];
 $provisional_sanction_letter_date = $_REQUEST['provisional_sanction_letter_date'];
 $customer_service_no = $_REQUEST['customer_service_no'];
 $meter_no = $_REQUEST['meter_no'];
 $date_of_production = $_REQUEST['date_of_production'];
 $contract_demand = $_REQUEST['contract_demand'];
 $date_of_power_release = $_REQUEST['date_of_power_release'];
 $tarrif_subsidy_period_from = $_REQUEST['tarrif_subsidy_period_from'];
 $tarrif_subsidy_period_to = $_REQUEST['tarrif_subsidy_period_to'];
 $monthsDifference= $_REQUEST['monthsDifference'];

 try
 {
  $cp = Array (
    "company_letter_date" => $company_letter_date,
    "provisional_sanction_letter_no" => $provisional_sanction_letter_no,
    "provisional_sanction_letter_date" => $provisional_sanction_letter_date,
    "customer_service_no" => $customer_service_no,
    "meter_no" => $meter_no,
    "date_of_production" => $date_of_production,
    "contract_demand" => $contract_demand,
    "date_of_power_release" => $date_of_power_release,
    "tarrif_subsidy_period_from" => $tarrif_subsidy_period_from,
    "tarrif_subsidy_period_to" => $tarrif_subsidy_period_to,
    "monthsDifference" =>$monthsDifference,
  );

  $temp = array();
  $cp["srno"] = array();

  for($i=0;$i<$monthsDifference;$i++){
    $month = $_REQUEST['month'.$i];
    $unit = $_REQUEST['unit'.$i];
    $remarks = $_REQUEST['remarks'.$i];  
    
    $temp['srno'] = ($i+1);
    $temp['month'] = $month;
    $temp['unit'] = $unit;
    $temp['remarks'] = $remarks;

    $temp = array_map('utf8_encode', $temp);
    array_push($cp['srno'], $temp);
  }

    // Encode array to json
  $json = json_encode($cp);

  $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
  $stmt->bind_param("iiiiss",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status);
  $Resp=$stmt->execute();

  if(!$Resp)
  {
    throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
  }
  $stmt->close();
} 
catch(\Exception  $e) {
  setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
}

if($Resp)
{
  setcookie("msg", "data",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
else
{
  setcookie("msg", "fail",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
}

if(isset($_REQUEST['btn_annexure_pt1_new_unit_update']))
{
  $pr_file_data_id = $_REQUEST['pr_file_data_id'];
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];
  
  $company_letter_date = $_REQUEST['company_letter_date'];
  $provisional_sanction_letter_no = $_REQUEST['provisional_sanction_letter_no'];
  $provisional_sanction_letter_date = $_REQUEST['provisional_sanction_letter_date'];
  $customer_service_no = $_REQUEST['customer_service_no'];
  $meter_no = $_REQUEST['meter_no'];
  $date_of_production = $_REQUEST['date_of_production'];
  $contract_demand = $_REQUEST['contract_demand'];
  $date_of_power_release = $_REQUEST['date_of_power_release'];
  $tarrif_subsidy_period_from = $_REQUEST['tarrif_subsidy_period_from'];
  $tarrif_subsidy_period_to = $_REQUEST['tarrif_subsidy_period_to'];
  $monthsDifference= $_REQUEST['monthsDifference'];


  try
  {
    $cp = Array (
      "company_letter_date" => $company_letter_date,
      "provisional_sanction_letter_no" => $provisional_sanction_letter_no,
      "provisional_sanction_letter_date" => $provisional_sanction_letter_date,
      "customer_service_no" => $customer_service_no,
      "meter_no" => $meter_no,
      "date_of_production" => $date_of_production,
      "contract_demand" => $contract_demand,
      "date_of_power_release" => $date_of_power_release,
      "tarrif_subsidy_period_from" => $tarrif_subsidy_period_from,
      "tarrif_subsidy_period_to" => $tarrif_subsidy_period_to,
      "monthsDifference" =>$monthsDifference,      
    );

    $temp = array();
    $cp["srno"] = array();

    for($i=0;$i<$monthsDifference;$i++){
      $month = $_REQUEST['month'.$i];
      $unit = $_REQUEST['unit'.$i];
      $remarks = $_REQUEST['remarks'.$i];  
      
      $temp['srno'] = ($i+1);
      $temp['month'] = $month;
      $temp['unit'] = $unit;
      $temp['remarks'] = $remarks;

      $temp = array_map('utf8_encode', $temp);
      array_push($cp['srno'], $temp);
    }

    // Encode array to json
    $json = json_encode($cp);

    $stmt = $obj->con1->prepare("UPDATE `pr_files_data` set `file_data`=? where `id`=?");
    $stmt->bind_param("si",$json,$pr_file_data_id);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
    setcookie("msg", "data",time()+3600,"/");
    header("location:process_gogtp_pt.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:process_gogtp_pt.php");
  }
}

if(isset($_REQUEST['btn_annexure_pt2_expansion']))
{
 $scheme_id = $_REQUEST['scheme_id'];
 $stage_id = $_REQUEST['stage_id'];
 $file_id = $_REQUEST['file_id'];
 $inq_id = $_REQUEST['inq_id'];
 $status = 'Completed';

 $company_letter_date = $_REQUEST['company_letter_date'];
 $provisional_sanction_letter_no = $_REQUEST['provisional_sanction_letter_no'];
 $provisional_sanction_letter_date = $_REQUEST['provisional_sanction_letter_date'];
 $old_customer_service_no = $_REQUEST['old_customer_service_no'];
 $new_customer_service_no = $_REQUEST['new_customer_service_no'];
 $main_meter_no = $_REQUEST['main_meter_no'];
 $date_of_production = $_REQUEST['date_of_production'];
 $contract_demand = $_REQUEST['contract_demand'];
 $date_of_power_release = $_REQUEST['date_of_power_release'];
 $date_of_integration = $_REQUEST['date_of_integration'];


 try
 {
  $cp = Array (
    "company_letter_date" => $company_letter_date,
    "provisional_sanction_letter_no" => $provisional_sanction_letter_no,
    "provisional_sanction_letter_date" => $provisional_sanction_letter_date,
    "old_customer_service_no" => $old_customer_service_no,
    "new_customer_service_no" => $new_customer_service_no,
    "main_meter_no" => $main_meter_no,
    "date_of_production" => $date_of_production,
    "contract_demand" => $contract_demand,
    "date_of_power_release" => $date_of_power_release,
    "date_of_integration" => $date_of_integration,
    
  );

  $temp = array();
  $cp["srno"] = array();
  $sum=0;

  for($i=0;$i<6;$i++){
    $month = $_REQUEST['month'.$i];
    $unit = $_REQUEST['unit'.$i];
    $remarks = $_REQUEST['remarks'.$i];  
    
    $sum+=$unit;

    $temp['srno'] = ($i+1);
    $temp['month'] = $month;
    $temp['unit'] = $unit;
    $temp['remarks'] = $remarks;

    $temp = array_map('utf8_encode', $temp);
    array_push($cp['srno'], $temp);
  }

  $average = $sum/6;

  $cp['average_units'] = round($average);

  // Encode array to json
  $json = json_encode($cp);

  $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
  $stmt->bind_param("iiiiss",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status);
  $Resp=$stmt->execute();

  if(!$Resp)
  {
    throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
  }
  $stmt->close();
} 
catch(\Exception  $e) {
  setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
}

if($Resp)
{
  setcookie("msg", "data",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
else
{
  setcookie("msg", "fail",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
}

if(isset($_REQUEST['btn_annexure_pt2_expansion_update']))
{
 $pr_file_data_id = $_REQUEST['pr_file_data_id'];
 $scheme_id = $_REQUEST['scheme_id'];
 $stage_id = $_REQUEST['stage_id'];
 $file_id = $_REQUEST['file_id'];
 $inq_id = $_REQUEST['inq_id'];
 $status = 'Completed';

 $company_letter_date = $_REQUEST['company_letter_date'];
 $provisional_sanction_letter_no = $_REQUEST['provisional_sanction_letter_no'];
 $provisional_sanction_letter_date = $_REQUEST['provisional_sanction_letter_date'];
 $old_customer_service_no = $_REQUEST['old_customer_service_no'];
 $new_customer_service_no = $_REQUEST['new_customer_service_no'];
 $main_meter_no = $_REQUEST['main_meter_no'];
 $date_of_production = $_REQUEST['date_of_production'];
 $contract_demand = $_REQUEST['contract_demand'];
 $date_of_power_release = $_REQUEST['date_of_power_release'];
 $date_of_integration = $_REQUEST['date_of_integration'];


 try
 {
  $cp = Array (
    "company_letter_date" => $company_letter_date,
    "provisional_sanction_letter_no" => $provisional_sanction_letter_no,
    "provisional_sanction_letter_date" => $provisional_sanction_letter_date,
    "old_customer_service_no" => $old_customer_service_no,
    "new_customer_service_no" => $new_customer_service_no,
    "main_meter_no" => $main_meter_no,
    "date_of_production" => $date_of_production,
    "contract_demand" => $contract_demand,
    "date_of_power_release" => $date_of_power_release,
    "date_of_integration" => $date_of_integration,
    
  );

  $temp = array();
  $cp["srno"] = array();
  $sum=0;

  for($i=0;$i<6;$i++){
    $month = $_REQUEST['month'.$i];
    $unit = $_REQUEST['unit'.$i];
    $remarks = $_REQUEST['remarks'.$i];  

    $sum+=$unit;
    
    $temp['srno'] = ($i+1);
    $temp['month'] = $month;
    $temp['unit'] = $unit;
    $temp['remarks'] = $remarks;

    $temp = array_map('utf8_encode', $temp);
    array_push($cp['srno'], $temp);
  }

  $average = $sum/6;

  $cp["average_units"] = floatval($average);

    // Encode array to json
  $json = json_encode($cp);
  $stmt = $obj->con1->prepare("UPDATE `pr_files_data` SET `scheme_id`= ?,`stage_id`=?,`file_id`=?,`inq_id`=?,`file_data`=?,`status`=? WHERE  `id`=?");
  $stmt->bind_param("iiiissi",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status,$pr_file_data_id);
  $Resp=$stmt->execute();

  if(!$Resp)
  {
    throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
  }
  $stmt->close();
} 
catch(\Exception  $e) {
  setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
}

if($Resp)
{
  setcookie("msg", "data",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
else
{
  setcookie("msg", "fail",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
}

if(isset($_REQUEST['btn_annexure_pt3_expansion']))
{
 $scheme_id = $_REQUEST['scheme_id'];
 $stage_id = $_REQUEST['stage_id'];
 $file_id = $_REQUEST['file_id'];
 $inq_id = $_REQUEST['inq_id'];
 $status = 'Completed';

 $company_letter_date = $_REQUEST['company_letter_date'];
 $provisional_sanction_letter_no = $_REQUEST['provisional_sanction_letter_no'];
 $provisional_sanction_letter_date = $_REQUEST['provisional_sanction_letter_date'];
 $old_customer_service_no = $_REQUEST['old_customer_service_no'];
 $new_customer_service_no = $_REQUEST['new_customer_service_no'];
 $sub_meter_no = $_REQUEST['sub_meter_no'];
 $date_of_production = $_REQUEST['date_of_production'];
 $contract_demand = $_REQUEST['contract_demand'];
 $date_of_power_release = $_REQUEST['date_of_power_release'];
 $date_of_integration = $_REQUEST['date_of_integration'];
 $tarrif_subsidy_period_from = $_REQUEST['tarrif_subsidy_period_from'];
 $tarrif_subsidy_period_to = $_REQUEST['tarrif_subsidy_period_to'];
 $monthsDifference = $_REQUEST['monthsDifference'];

 

 try
 {
  $cp = Array (
    "company_letter_date" => $company_letter_date,
    "provisional_sanction_letter_no" => $provisional_sanction_letter_no,
    "provisional_sanction_letter_date" => $provisional_sanction_letter_date,
    "old_customer_service_no" => $old_customer_service_no,
    "new_customer_service_no" => $new_customer_service_no,
    "sub_meter_no" => $sub_meter_no,
    "date_of_production" => $date_of_production,
    "contract_demand" => $contract_demand,
    "date_of_power_release" => $date_of_power_release,
    "date_of_integration" => $date_of_integration,
    "monthsDifference" => $monthsDifference,
    "tarrif_subsidy_period_from" => $tarrif_subsidy_period_from,
    "tarrif_subsidy_period_to" => $tarrif_subsidy_period_to
    
  );

  $temp = array();
  $cp["srno"] = array();
  
  for($i=0;$i<$monthsDifference;$i++){
    $month = $_REQUEST['month'.$i];
    $unit = $_REQUEST['unit'.$i];
    $remarks = $_REQUEST['remarks'.$i];  
    
    $temp['srno'] = ($i+1);
    $temp['month'] = $month;
    $temp['unit'] = $unit;
    $temp['remarks'] = $remarks;

    $temp = array_map('utf8_encode', $temp);
    array_push($cp['srno'], $temp);
  }

    // Encode array to json
  $json = json_encode($cp);
  
  $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
  $stmt->bind_param("iiiiss",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status);
  $Resp=$stmt->execute();

  if(!$Resp)
  {
    throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
  }
  $stmt->close();
} 
catch(\Exception  $e) {
  setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
}

if($Resp)
{
  setcookie("msg", "data",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
else
{
  setcookie("msg", "fail",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
}

if(isset($_REQUEST['btn_annexure_pt3_expansion_update']))
{  
  $pr_file_data_id = $_REQUEST['pr_file_data_id'];
 $scheme_id = $_REQUEST['scheme_id'];
 $stage_id = $_REQUEST['stage_id'];
 $file_id = $_REQUEST['file_id'];
 $inq_id = $_REQUEST['inq_id'];

 $company_letter_date = $_REQUEST['company_letter_date'];
 $provisional_sanction_letter_no = $_REQUEST['provisional_sanction_letter_no'];
 $provisional_sanction_letter_date = $_REQUEST['provisional_sanction_letter_date'];
 $old_customer_service_no = $_REQUEST['old_customer_service_no'];
 $new_customer_service_no = $_REQUEST['new_customer_service_no'];
 $sub_meter_no = $_REQUEST['sub_meter_no'];
 $date_of_production = $_REQUEST['date_of_production'];
 $contract_demand = $_REQUEST['contract_demand'];
 $date_of_power_release = $_REQUEST['date_of_power_release'];
 $date_of_integration = $_REQUEST['date_of_integration'];
 $tarrif_subsidy_period_from = $_REQUEST['tarrif_subsidy_period_from'];
 $tarrif_subsidy_period_to = $_REQUEST['tarrif_subsidy_period_to'];
 $monthsDifference = $_REQUEST['monthsDifference'];

 try
 {
  $cp = Array (
    "company_letter_date" => $company_letter_date,
    "provisional_sanction_letter_no" => $provisional_sanction_letter_no,
    "provisional_sanction_letter_date" => $provisional_sanction_letter_date,
    "old_customer_service_no" => $old_customer_service_no,
    "new_customer_service_no" => $new_customer_service_no,
    "sub_meter_no" => $sub_meter_no,
    "date_of_production" => $date_of_production,
    "contract_demand" => $contract_demand,
    "date_of_power_release" => $date_of_power_release,
    "date_of_integration" => $date_of_integration,
    "monthsDifference" => $monthsDifference,
    "tarrif_subsidy_period_from" =>$tarrif_subsidy_period_from,
    "tarrif_subsidy_period_to" =>$tarrif_subsidy_period_to,
  );

  $temp = array();
  $cp["srno"] = array();

  for($i=0;$i<$monthsDifference;$i++){
    $month = $_REQUEST['month'.$i];
    $unit = $_REQUEST['unit'.$i];
    $remarks = $_REQUEST['remarks'.$i];  
    
    $temp['srno'] = ($i+1);
    $temp['month'] = $month;
    $temp['unit'] = $unit;
    $temp['remarks'] = $remarks;

    $temp = array_map('utf8_encode', $temp);
    array_push($cp['srno'], $temp);
  }

    // Encode array to json
  $json = json_encode($cp);

  $stmt = $obj->con1->prepare("UPDATE `pr_files_data` SET `scheme_id`= ?,`stage_id`=?,`file_id`=?,`inq_id`=?,`file_data`=? WHERE  `id`=?");
  $stmt->bind_param("iiiissi",$scheme_id,$stage_id,$file_id,$inq_id,$json,$pr_file_data_id);
  $Resp=$stmt->execute();

  if(!$Resp)
  {
    throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
  }
  $stmt->close();
} 
catch(\Exception  $e) {
  setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
}

if($Resp)
{
  setcookie("msg", "data",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
else
{
  setcookie("msg", "fail",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
}

if(isset($_REQUEST['btn_annexure_pt4_expansion']))
{
 $scheme_id = $_REQUEST['scheme_id'];
 $stage_id = $_REQUEST['stage_id'];
 $file_id = $_REQUEST['file_id'];
 $inq_id = $_REQUEST['inq_id'];
 $status = 'Completed';

 $company_letter_date = $_REQUEST['company_letter_date'];
 $provisional_sanction_letter_no = $_REQUEST['provisional_sanction_letter_no'];
 $provisional_sanction_letter_date = $_REQUEST['provisional_sanction_letter_date'];
 $old_customer_service_no = $_REQUEST['old_customer_service_no'];
 $new_customer_service_no = $_REQUEST['new_customer_service_no'];
 $main_meter_no = $_REQUEST['main_meter_no'];
 $date_of_production = $_REQUEST['date_of_production'];
 $contract_demand = $_REQUEST['contract_demand'];
 $date_of_power_release = $_REQUEST['date_of_power_release'];
 $date_of_integration = $_REQUEST['date_of_integration'];
 $tarrif_subsidy_period_from = $_REQUEST['tarrif_subsidy_period_from'];
 $tarrif_subsidy_period_to = $_REQUEST['tarrif_subsidy_period_to'];
 $monthsDifference = $_REQUEST['monthsDifference'];

 

 try
 {
  $cp = Array (
    "company_letter_date" => $company_letter_date,
    "provisional_sanction_letter_no" => $provisional_sanction_letter_no,
    "provisional_sanction_letter_date" => $provisional_sanction_letter_date,
    "old_customer_service_no" => $old_customer_service_no,
    "new_customer_service_no" => $new_customer_service_no,
    "main_meter_no" => $main_meter_no,
    "date_of_production" => $date_of_production,
    "contract_demand" => $contract_demand,
    "date_of_power_release" => $date_of_power_release,
    "date_of_integration" => $date_of_integration,
    "monthsDifference" => $monthsDifference,
    "tarrif_subsidy_period_from" => $tarrif_subsidy_period_from,
    "tarrif_subsidy_period_to" => $tarrif_subsidy_period_to
    
  );

  $temp = array();
  $cp["srno"] = array();

  for($i=0;$i<$monthsDifference;$i++){
    $month = $_REQUEST['month'.$i];
    $unit = $_REQUEST['unit'.$i];
    $remarks = $_REQUEST['remarks'.$i];  
    
    $temp['srno'] = ($i+1);
    $temp['month'] = $month;
    $temp['unit'] = $unit;
    $temp['remarks'] = $remarks;

    $temp = array_map('utf8_encode', $temp);
    array_push($cp['srno'], $temp);
  }

    // Encode array to json
  $json = json_encode($cp);
  
  $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
  $stmt->bind_param("iiiiss",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status);
  $Resp=$stmt->execute();

  if(!$Resp)
  {
    throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
  }
  $stmt->close();
} 
catch(\Exception  $e) {
  setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
}

if($Resp)
{
  setcookie("msg", "data",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
else
{
  setcookie("msg", "fail",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
}

if(isset($_REQUEST['btn_annexure_pt4_expansion_update']))
{
    $pr_file_data_id = $_REQUEST['pr_file_data_id'];
 $scheme_id = $_REQUEST['scheme_id'];
 $stage_id = $_REQUEST['stage_id'];
 $file_id = $_REQUEST['file_id'];
 $inq_id = $_REQUEST['inq_id'];

 $company_letter_date = $_REQUEST['company_letter_date'];
 $provisional_sanction_letter_no = $_REQUEST['provisional_sanction_letter_no'];
 $provisional_sanction_letter_date = $_REQUEST['provisional_sanction_letter_date'];
 $old_customer_service_no = $_REQUEST['old_customer_service_no'];
 $new_customer_service_no = $_REQUEST['new_customer_service_no'];
 $main_meter_no = $_REQUEST['main_meter_no'];
 $date_of_production = $_REQUEST['date_of_production'];
 $contract_demand = $_REQUEST['contract_demand'];
 $date_of_power_release = $_REQUEST['date_of_power_release'];
 $date_of_integration = $_REQUEST['date_of_integration'];
 $tarrif_subsidy_period_from = $_REQUEST['tarrif_subsidy_period_from'];
 $tarrif_subsidy_period_to = $_REQUEST['tarrif_subsidy_period_to'];
 $monthsDifference = $_REQUEST['monthsDifference'];

 try
 {
  $cp = Array (
    "company_letter_date" => $company_letter_date,
    "provisional_sanction_letter_no" => $provisional_sanction_letter_no,
    "provisional_sanction_letter_date" => $provisional_sanction_letter_date,
    "old_customer_service_no" => $old_customer_service_no,
    "new_customer_service_no" => $new_customer_service_no,
    "main_meter_no" => $main_meter_no,
    "date_of_production" => $date_of_production,
    "contract_demand" => $contract_demand,
    "date_of_power_release" => $date_of_power_release,
    "date_of_integration" => $date_of_integration,
    "tarrif_subsidy_period_from" => $tarrif_subsidy_period_from,
    "tarrif_subsidy_period_to" => $tarrif_subsidy_period_to,
    "monthsDifference" => $monthsDifference,
    
  );

  $temp = array();
  $cp["srno"] = array();

  for($i=0;$i<$monthsDifference;$i++){
    $month = $_REQUEST['month'.$i];
    $unit = $_REQUEST['unit'.$i];
    $remarks = $_REQUEST['remarks'.$i];  
    
    $temp['srno'] = ($i+1);
    $temp['month'] = $month;
    $temp['unit'] = $unit;
    $temp['remarks'] = $remarks;

    $temp = array_map('utf8_encode', $temp);
    array_push($cp['srno'], $temp);
  }

    // Encode array to json
  $json = json_encode($cp);

  $stmt = $obj->con1->prepare("UPDATE `pr_files_data` SET `scheme_id`= ?,`stage_id`=?,`file_id`=?,`inq_id`=?,`file_data`=? WHERE  `id`=?");
  $stmt->bind_param("iiiissi",$scheme_id,$stage_id,$file_id,$inq_id,$json,$pr_file_data_id);
  $Resp=$stmt->execute();

  if(!$Resp)
  {
    throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
  }
  $stmt->close();
} 
catch(\Exception  $e) {
  setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
}

if($Resp)
{
  setcookie("msg", "data",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
else
{
  setcookie("msg", "fail",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
}

if(isset($_REQUEST['btn_annexure_pt5']))
{
  $scheme_id = $_REQUEST['scheme_id'];
  $stage_id = $_REQUEST['stage_id'];
  $file_id = $_REQUEST['file_id'];
  $inq_id = $_REQUEST['inq_id'];
  $status = 'Completed';

  $company_letter_date = $_REQUEST['company_letter_date'];
  $provisional_sanction_letter_no = $_REQUEST['provisional_sanction_letter_no'];
  $provisional_sanction_letter_date = $_REQUEST['provisional_sanction_letter_date'];
  $customer_service_no = $_REQUEST['customer_service_no'];
  $main_meter_no = $_REQUEST['main_meter_no'];
  $date_of_production = $_REQUEST['date_of_production'];
  $contract_demand = $_REQUEST['contract_demand'];
  $date_of_power_release = $_REQUEST['date_of_power_release'];
  $date_of_integration = $_REQUEST['date_of_integration'];
  $tarrif_subsidy_period_from = $_REQUEST['tarrif_subsidy_period_from'];
  $tarrif_subsidy_period_to = $_REQUEST['tarrif_subsidy_period_to'];
  $monthsDifference = $_REQUEST['monthsDifference'];
  $renewable_power_generation_facility = $_REQUEST['renewable_power_generation_facility'];
  $renewable_power_generation_capacity = $_REQUEST['renewable_power_generation_capacity'];

  

  try
  {
    $cp = Array (
      "company_letter_date" => $company_letter_date,
      "provisional_sanction_letter_no" => $provisional_sanction_letter_no,
      "provisional_sanction_letter_date" => $provisional_sanction_letter_date,
      "customer_service_no" => $customer_service_no,
      "main_meter_no" => $main_meter_no,
      "date_of_production" => $date_of_production,
      "contract_demand" => $contract_demand,
      "date_of_power_release" => $date_of_power_release,
      "date_of_integration" => $date_of_integration,
      "monthsDifference" => $monthsDifference,
      "tarrif_subsidy_period_from" => $tarrif_subsidy_period_from,
      "tarrif_subsidy_period_to" => $tarrif_subsidy_period_to,
      "renewable_power_generation_facility" => $renewable_power_generation_facility,
      "renewable_power_generation_capacity" => $renewable_power_generation_capacity,
      
    );

    $temp = array();
    $cp["srno"] = array();

    for($i=0;$i<$monthsDifference;$i++){
      $month = $_REQUEST['month'.$i];
      $unit = $_REQUEST['unit'.$i];
      $renewable_generation = $_REQUEST['renewable_generation'.$i];
      $remarks = $_REQUEST['remarks'.$i];  
      $balanced_generation = intval($unit)- intval($renewable_generation);
      
      $temp['srno'] = ($i+1);
      $temp['month'] = $month;
      $temp['unit'] = $unit;
      $temp['renewable_generation'] = $renewable_generation;
      $temp['remarks'] = $remarks;
      $temp['balanced_generation'] = $balanced_generation;

      $temp = array_map('utf8_encode', $temp);
      array_push($cp['srno'], $temp);
    }

    // Encode array to json
    echo $json = json_encode($cp);
    
    $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("iiiiss",$scheme_id,$stage_id,$file_id,$inq_id,$json,$status);
    $Resp=$stmt->execute();

    if(!$Resp)
    {
      throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
    setcookie("msg", "data",time()+3600,"/");
    header("location:process_gogtp_pt.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:process_gogtp_pt.php");
  }
}

if(isset($_REQUEST['btn_annexure_pt5_update']))
{
    $pr_file_data_id = $_REQUEST['pr_file_data_id'];
 $scheme_id = $_REQUEST['scheme_id'];
 $stage_id = $_REQUEST['stage_id'];
 $file_id = $_REQUEST['file_id'];
 $inq_id = $_REQUEST['inq_id'];

 $company_letter_date = $_REQUEST['company_letter_date'];
 $provisional_sanction_letter_no = $_REQUEST['provisional_sanction_letter_no'];
 $provisional_sanction_letter_date = $_REQUEST['provisional_sanction_letter_date'];
 $customer_service_no = $_REQUEST['customer_service_no'];
 $main_meter_no = $_REQUEST['main_meter_no'];
 $date_of_production = $_REQUEST['date_of_production'];
 $contract_demand = $_REQUEST['contract_demand'];
 $date_of_power_release = $_REQUEST['date_of_power_release'];
 $date_of_integration = $_REQUEST['date_of_integration'];
 $tarrif_subsidy_period_from = $_REQUEST['tarrif_subsidy_period_from'];
 $tarrif_subsidy_period_to = $_REQUEST['tarrif_subsidy_period_to'];
 $monthsDifference = $_REQUEST['monthsDifference'];
 $renewable_power_generation_facility = $_REQUEST['renewable_power_generation_facility'];
 $renewable_power_generation_capacity = $_REQUEST['renewable_power_generation_capacity'];

 try
 {
  $cp = Array (
    "company_letter_date" => $company_letter_date,
    "provisional_sanction_letter_no" => $provisional_sanction_letter_no,
    "provisional_sanction_letter_date" => $provisional_sanction_letter_date,
    "customer_service_no" => $customer_service_no,
    "main_meter_no" => $main_meter_no,
    "date_of_production" => $date_of_production,
    "contract_demand" => $contract_demand,
    "date_of_power_release" => $date_of_power_release,
    "date_of_integration" => $date_of_integration,
    "monthsDifference" => $monthsDifference,
    "tarrif_subsidy_period_from" => $tarrif_subsidy_period_from,
    "tarrif_subsidy_period_to" => $tarrif_subsidy_period_to,
    "renewable_power_generation_facility" => $renewable_power_generation_facility,
    "renewable_power_generation_capacity" => $renewable_power_generation_capacity,
    
  );

  $temp = array();
  $cp["srno"] = array();

  for($i=0;$i<$monthsDifference;$i++){
    $month = $_REQUEST['month'.$i];
    $unit = $_REQUEST['unit'.$i];
    $remarks = $_REQUEST['remarks'.$i]; 
    $renewable_generation = $_REQUEST['renewable_generation'.$i]; 
    $balanced_generation = intval($unit)- intval($renewable_generation); 
    
    $temp['srno'] = ($i+1);
    $temp['month'] = $month;
    $temp['unit'] = $unit;
    $temp['remarks'] = $remarks;
    $temp['renewable_generation'] = $renewable_generation;
    $temp['balanced_generation'] = $balanced_generation;

    $temp = array_map('utf8_encode', $temp);
    array_push($cp['srno'], $temp);
  }

    // Encode array to json
  $json = json_encode($cp);

  $stmt = $obj->con1->prepare("UPDATE `pr_files_data` SET `scheme_id`= ?,`stage_id`=?,`file_id`=?,`inq_id`=?,`file_data`=? WHERE  `id`=?");
  $stmt->bind_param("iiiissi",$scheme_id,$stage_id,$file_id,$inq_id,$json,$pr_file_data_id);
  $Resp=$stmt->execute();

  if(!$Resp)
  {
    throw new Exception("Problem in inserting! ". strtok($obj->con1-> error,  '('));
  }
  $stmt->close();
} 
catch(\Exception  $e) {
  setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
}

if($Resp)
{
  setcookie("msg", "data",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
else
{
  setcookie("msg", "fail",time()+3600,"/");
  header("location:process_gogtp_pt.php");
}
}

?>

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

<h4 class="fw-bold py-3 mb-4"><?php echo $scheme_name."(Total - ".$total_count.")"; ?></h4>

<div class="col-md mb-4 mb-md-0">
  <!-- <small class="text-light fw-semibold">Basic Accordion</small> -->
  <div class="accordion mt-3" id="accordionExample">
    <?php
    $j=0;
    while($data = mysqli_fetch_array($stage_result)) { 
            $app_data=json_decode($data["app_data"]);
            $company_details=$app_data->company_details;
            $contact_details=$app_data->contact_details;
     ?>
     <div class="card accordion-item">
       <h2 class="accordion-header" id="headingOne">
         <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#accordion<?php echo $j ?>" aria-expanded="false" aria-controls="accordion<?php echo $j ?>">Company Name : <?php echo $company_details->cname." ( ".$contact_details->mobile." )"; ?></button>
       </h2>

       <div id="accordion<?php echo $j ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
         <div class="accordion-body">


          <!-- nested accordion -->
          <div class="col-md mb-4 mb-md-0">
           <div class="accordion mt-3" id="accordionCompany">

          <?php
             $claim_str = ($claim==1)?" AND s1.stage_type='Claim'":" AND s1.stage_type!='Claim'";

             $stmt_list = $obj->con1->prepare("SELECT s1.* FROM (SELECT DISTINCT(stage_id) as stage_id FROM `pr_file_format` WHERE scheme_id=?) tbl, tbl_tdstages s1 WHERE tbl.stage_id=s1.stage_id".$claim_str); 
             $stmt_list->bind_param("i",$service_id);
             $stmt_list->execute();
             $result = $stmt_list->get_result();
             $stmt_list->close();
             $i=1;

             while($stage=mysqli_fetch_array($result))
             {
              ?>

              <div class="card shadow-none bg-transparent border border-info mb-3 accordion-item">
                <h2 class="accordion-header" id="headingOne">
                  <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#compAccordion<?php echo $i ?>" aria-expanded="false" aria-controls="compAccordion<?php echo $i ?>"><?php echo $stage["stage_name"] ?></button>
                </h2>

                <div id="compAccordion<?php echo $i ?>" class="accordion-collapse collapse" data-bs-parent="#accordionCompany">
                  <div class="accordion-body">

                   <div class="card">
                     <!-- <h5 class="card-header">Records</h5> -->
                     <div class="table-responsive text-nowrap">
                       <table class="table table-hover">
                         <thead>
                           <tr>
                             <th>File Name</th>
                             <th>Type</th>
                             <th></th>
                             <th><a href="javascript:download_zip('<?php echo $data['tatassign_inq_id'] ?>','<?php echo $stage['service_id'] ?>','<?php echo $stage['stage_id'] ?>','<?php echo $stage['stage_name'] ?>')" class="btn btn-primary" style="margin-right:15px; color: #fff;">Download Zip</a></th>
                             <th>Status</th>
                           </tr>
                         </thead>
                         <tbody class="table-border-bottom-0">

                           <?php
                           $stmt_file = $obj->con1->prepare("SELECT * FROM `pr_file_format` WHERE scheme_id=? and stage_id=?"); 
                           $stmt_file->bind_param("ii",$stage['service_id'],$stage['stage_id']);
                           $stmt_file->execute();
                           $file_result = $stmt_file->get_result();
                           $stmt_file->close();

                           while($files_res=mysqli_fetch_array($file_result))
                           {
                             $stmt_file_status = $obj->con1->prepare("SELECT status FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=?"); 
                             $stmt_file_status->bind_param("iiii",$stage['service_id'],$stage['stage_id'],$files_res['fid'],$data['tatassign_inq_id']);
                             $stmt_file_status->execute();
                             $file_status_result = $stmt_file_status->get_result();
                             $stmt_file_status->close();

                             $count = mysqli_num_rows($file_status_result);
                             $status_res = $file_status_result->fetch_assoc();
                             ?>



                             <tr>
                              <td id="<?php echo $files_res['page_name'] ?>" hidden><?php echo $files_res['fid'] ?></td>
                              <td><?php echo $files_res['file_name'] ?></td>
                              <td><?php echo $files_res['doc_type'] ?></td>
                              <td><?php if($files_res['get_data_type']=="retrieve" || $files_res['get_data_type']=="calculate"){ ?>
                               <a href="javascript:file_set_values('<?php echo $files_res['page_name'] ?>','<?php echo $data['tatassign_inq_id'] ?>','<?php echo $stage['service_id'] ?>','<?php echo $stage['stage_id'] ?>','<?php echo $files_res['fid'] ?>','<?php echo $count ?>','<?php echo $files_res['get_data_type'] ?>')">Fill Data</a>
                             <?php } ?>
                           </td>
                           <td><?php if($count>0 || $files_res['get_data_type']=="fetch"){ ?>
                            <a href="javascript:download_file('<?php echo $files_res['doc_file'] ?>','<?php echo $files_res['page_name'] ?>','<?php echo $data['tatassign_inq_id'] ?>','<?php echo $stage['service_id'] ?>','<?php echo $stage['stage_id'] ?>','<?php echo $files_res['fid'] ?>','<?php echo $count ?>','<?php echo $files_res['get_data_type'] ?>')">Download</a>
                            <?php } ?></td>
                            <td><?php
                            if($files_res['get_data_type']=="fetch"){
                              echo "Completed";
                            }
                            else{
                              if($count>0){ 
                               echo $status_res['status']; 
                             } else{ 
                               echo "Pending";
                             } }       
                           ?></td>
                         </tr>

                       <?php } ?>

                     </tbody>
                   </table>

                 </div>
               </div>                             

             </div>
           </div>
         </div>

      <?php
         $i++;
       }
      ?>

     </div>
   </div>

 </div>
</div>
</div>
<?php
$j++;
} 
?>

</div>
</div>


<!-- Modal -->
<div class="modal fade" id="modalCenter" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Fill Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post">
       <div id="modal_form_div"></div>
     </form>
   </div>
 </div>
</div>
<!-- /modal-->


<script type="text/javascript">

  function file_set_values(page_name,inq_id,service_id,stage_id,file_id,count,get_data_type) {
    if(get_data_type=="retrieve") { 
      $('#modalCenter').modal('toggle');
      $.ajax({
        async: true,
        type: "POST",
        url: "file_modals.php?action="+page_name,
        data: "scheme_id="+service_id+"&stage_id="+stage_id+"&file_id="+file_id+"&inq_id="+inq_id,
        cache: false,
        success: function(result){
          $('#modal_form_div').html('');
          $('#modal_form_div').html(result);
        }
      });
    }
    else if(get_data_type=="calculate") {
      if(page_name=="employment_data_gogtp"){
        affidavit_id = $('#affidavit2_gogtp').text();
        $('#modalCenter').modal('toggle');
        $.ajax({
          async: true,
          type: "POST",
          url: "file_modals.php?action="+page_name,
          data: "scheme_id="+service_id+"&stage_id="+stage_id+"&file_id="+file_id+"&inq_id="+inq_id+"&affidavit_id="+affidavit_id,
          cache: false,
          success: function(result){
            $('#modal_form_div').html('');
            $('#modal_form_div').html(result);
          }
        });
      }
    }
    else if(get_data_type=="fetch"){ }
  }


function download_file(doc_file,page_name,inq_id,service_id,stage_id,file_id,count,get_data_type) {
  window.location = "download_single_file.php?inq_id="+inq_id+"&service_id="+service_id+"&stage_id="+stage_id+"&file_id="+file_id+"&doc_file="+doc_file;
}

function download_zip(inq_id,service_id,stage_id,stage_name){
  window.location = "zip_download_sanction_office.php?inq_id=" + inq_id + "&service_id=" + service_id + "&stage_id=" + stage_id;
}

function generate_random_emp_list(scheme_id,stage_id,file_id,inq_id,edit) {
  $.ajax({
    async: true,
    type: "POST",
    url: "ajaxdata.php?action=generate_random_emp_list",
    data: "scheme_id="+scheme_id+"&stage_id="+stage_id+"&file_id="+file_id+"&inq_id="+inq_id,
    cache: false,
    success: function(result){
      if(edit){
        $('#emp_tbl_update_div').html('');
        $('#emp_tbl_update_div').html(result);
      }
      else{
        $('#emp_tbl_div').html('');
        $('#emp_tbl_div').html(result);
      }
    }
  });
}
function get_power_tariff_subsidy(start_dt,end_dt)
{
  if(start_dt!="" && end_dt!="")
  {
    $.ajax({
     async: true,
     type: "POST",
     url: "ajaxdata.php?action=get_power_tariff_subsidy",
     data: "start_dt="+start_dt+"&end_dt="+end_dt,
     cache: false,
     success: function(result){
      $('#month_year_div').html('');
      $('#month_year_div').html(result);

    }
  });
  }
}

function annexure_expansion_month_tbl(end_dt) {
  if(end_dt!="") { 
    $.ajax({
     async: true,
     type: "POST",
     url: "ajaxdata.php?action=annexure_expansion_month_tbl",
     data: "end_dt="+end_dt,
     cache: false,
     success: function(result){
       $('#month_year_annexure2_div').html('');
       $('#month_year_annexure2_div').html(result);

     }
   });
  }
}
function annexure3_expansion_month_tbl(start_dt,end_dt){
  if(start_dt!="" && end_dt!="")
  {
   $.ajax({
     async: true,
     type: "POST",
     url: "ajaxdata.php?action=annexure3_expansion_month_tbl",
     data: "start_dt="+start_dt+"&end_dt="+end_dt,
     cache: false,
     success: function(result){
      $('#month_year_annexure3_div').html('');
      $('#month_year_annexure3_div').html(result);

    }
  }); 
 }
}

function annexure4_expansion_month_tbl(start_dt,end_dt){
  if(start_dt!="" && end_dt!="")
  {
   $.ajax({
     async: true,
     type: "POST",
     url: "ajaxdata.php?action=annexure4_expansion_month_tbl",
     data: "start_dt="+start_dt+"&end_dt="+end_dt,
     cache: false,
     success: function(result){
      $('#month_year_annexure4_div').html('');
      $('#month_year_annexure4_div').html(result);

    }
  }); 
 }
}

function annexure5_month_tbl(start_dt,end_dt){
  if(start_dt!="" && end_dt!="")
  {
   $.ajax({
     async: true,
     type: "POST",
     url: "ajaxdata.php?action=month_year_annexure5_div",
     data: "start_dt="+start_dt+"&end_dt="+end_dt,
     cache: false,
     success: function(result){
      $('#month_year_annexure5_div').html('');
      $('#month_year_annexure5_div').html(result);

    }
  }); 
 }
}
</script>
<?php
include "footer.php";
?>