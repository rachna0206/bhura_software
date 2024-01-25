<?php 

function checkCompany_rawassign($value)
{
  $stmt_comp = $GLOBALS['obj']->con1->prepare("SELECT COUNT(*) as cnt FROM `tbl_tdrawassign` WHERE inq_id=?");
  $stmt_comp->bind_param("i",$value);
  $stmt_comp->execute();
  $comp_result = $stmt_comp->get_result()->fetch_assoc();
  $stmt_comp->close();

  return $comp_result["cnt"];
}

function check_for_badlead($value)
{
  $stmt_badlead = $GLOBALS['obj']->con1->prepare("SELECT * FROM `tbl_tdrawassign` WHERE inq_id=? and stage='badlead' order by id desc limit 1");
  $stmt_badlead->bind_param("i",$value);
  $stmt_badlead->execute();
  $badlead_result = $stmt_badlead->get_result()->fetch_assoc();
  $stmt_badlead->close();

  if($badlead_result["stage"]=="badlead"){
    return 1;
  }
  else{
    return 0;
  }
}

function company_plot_insert($plot_no,$floor,$road_no,$plot_id,$industrial_estate_id,$user_id)
{
  $stmt_company_plot = $GLOBALS['obj']->con1->prepare("INSERT INTO `pr_company_plots`(`plot_no`, `floor`, `road_no`, `plot_id`, `industrial_estate_id`, `user_id`) VALUES (?,?,?,?,?,?)");
  $stmt_company_plot->bind_param("ssssii",$plot_no,$floor,$road_no,$plot_id,$industrial_estate_id,$user_id);
  $Resp=$stmt_company_plot->execute();
  $stmt_company_plot->close();
}

function tbl_tdrawdata_insert($json,$user_id)
{
  $stmt_rawdata = $GLOBALS['obj']->con1->prepare("INSERT INTO `tbl_tdrawdata`(`raw_data`,`userid`) VALUES (?,?)");
  $stmt_rawdata->bind_param("ss",$json,$user_id);
  $Resp=$stmt_rawdata->execute();
  $stmt_rawdata->close();
  return $Resp;
}

?>