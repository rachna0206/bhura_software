<?php
error_reporting(E_ALL);
	include("db_connect.php");
	$obj= new DB_connect();

	echo "select id,json_unquote(raw_data->'$.post_fields.IndustrialEstate') as IE ,length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0)) as estate_length, json_unquote(raw_data->'$.plot_details[0].Plot_No') as plot_no, raw_data, userid, raw_data_ts from tbl_tdrawdata where length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0))<3 and raw_data->'$.plot_details[0].Plot_No' IS NOT NULL and length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0))!=1  
ORDER BY `estate_length` DESC limit 10";
	$stmt_blank_ind_estate = $obj->con1->prepare("select id,json_unquote(raw_data->'$.post_fields.IndustrialEstate') as IE ,length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0)) as estate_length, json_unquote(raw_data->'$.plot_details[0].Plot_No') as plot_no, raw_data, userid, raw_data_ts from tbl_tdrawdata where length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0))<3 and raw_data->'$.plot_details[0].Plot_No' IS NOT NULL and length(ifnull(json_unquote(raw_data->'$.post_fields.IndustrialEstate'),0))!=1  
ORDER BY `estate_length` DESC limit 5,10");
	
	$stmt_blank_ind_estate->execute();
	$res_blank_estate = $stmt_blank_ind_estate->get_result();
	$stmt_blank_ind_estate->close();
	while($blank_estate=mysqli_fetch_array($res_blank_estate))
	{
		print_r($blank_estate);
		echo "<br/>".$blank_estate["userid"];
		echo "SELECT ind.id,ind.industrial_estate FROM pr_company_plots p1,tbl_industrial_estate ind where p1.industrial_estate_id=ind.id and  p1.user_id=$blank_estate['userid'] and p1.datetime=$blank_estate['raw_data_ts'] and  p1.plot_no=$blank_estate['plot_no']";

		/*$stmt_pr_company_plot = $obj->con1->prepare("SELECT * FROM `pr_company_plots` pr,tbl_industrial_estate ind where pr.industrial_estate_id=ind.id and pr.user_id=? and `pr.datetime`=? and  pr.plot_no=?");
		$stmt_pr_company_plot->bind_param("iii",$blank_estate["userid"],$blank_estate["raw_data_ts"],$blank_estate["plot_no"]);
		$stmt_pr_company_plot->execute();
		$res_company_plot = $stmt_pr_company_plot->get_result();
		$stmt_pr_company_plot->close();*/

	}

	//include("footer.php");
?>