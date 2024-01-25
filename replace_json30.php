<?php
error_reporting(E_ALL);
	include("db_connect.php");
	$obj= new DB_connect();

	echo "select id,json_unquote(raw_data->'$.post_fields.IndustrialEstate') as IE ,length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0)) as estate_length, json_unquote(raw_data->'$.plot_details[0].Plot_No') as plot_no, raw_data, userid, raw_data_ts from tbl_tdrawdata where length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0))<3 and raw_data->'$.plot_details[0].Plot_No' IS NOT NULL and length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0))!=1  ORDER BY `estate_length` DESC limit 2500";
	$stmt_blank_ind_estate = $obj->con1->prepare("select id,json_unquote(raw_data->'$.post_fields.IndustrialEstate') as IE ,length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0)) as estate_length, json_unquote(raw_data->'$.plot_details[0].Plot_No') as plot_no, raw_data, userid, raw_data_ts from tbl_tdrawdata where length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0))<3 and raw_data->'$.plot_details[0].Plot_No' IS NOT NULL and length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0))!=1  
ORDER BY `estate_length` DESC limit 2500");
	
	$stmt_blank_ind_estate->execute();
	$res_blank_estate = $stmt_blank_ind_estate->get_result();
	$stmt_blank_ind_estate->close();
	while($blank_estate=mysqli_fetch_array($res_blank_estate))
	{
		
		//echo "SELECT ind.id,ind.industrial_estate FROM pr_company_plots p1,tbl_industrial_estate ind where p1.industrial_estate_id=ind.id and  p1.user_id='".$blank_estate['userid']."' and p1.datetime='".$blank_estate["raw_data_ts"]."' and  p1.plot_no='".$blank_estate["plot_no"]."'";

		$stmt_pr_company_plot = $obj->con1->prepare("SELECT * FROM `pr_company_plots` pr,tbl_industrial_estate ind where pr.industrial_estate_id=ind.id and pr.user_id=? and pr.datetime=? and  pr.plot_no=?");
		$stmt_pr_company_plot->bind_param("iss",$blank_estate["userid"],$blank_estate["raw_data_ts"],$blank_estate["plot_no"]);
		$stmt_pr_company_plot->execute();
		$res=$stmt_pr_company_plot->get_result();
		$stmt_pr_company_plot->close();
		$res_company_plot = mysqli_fetch_array($res);
		//print_r($res_company_plot);
		
		$row_data=json_decode($blank_estate["raw_data"]);
		$post_fields=$row_data->post_fields;
		$post_fields->IndustrialEstate = $res_company_plot["industrial_estate"];
		$json_object = json_encode($row_data);

  try
  {
  	//echo "<br/>update tbl_tdrawdata set raw_data='".$json_object."' where id='".$blank_estate["id"]."'";
    $stmt = $obj->con1->prepare("update tbl_tdrawdata set raw_data=? where id=?");
    $stmt->bind_param("si", $json_object,$blank_estate["id"]);
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

 

}
/* if($Resp)
  {
	  setcookie("msg", "update",time()+3600,"/");
    header("location:replace_json1.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:replace_json1.php");
  }*/

//include("footer.php");
?>