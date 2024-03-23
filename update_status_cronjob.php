<?php
include("db_connect.php");
$obj=new DB_Connect();

	$stmt = $obj->con1->prepare("SELECT r1.id, r1.inq_id, r1.stage, c1.status FROM (SELECT MAX(t2.id) as r_id FROM tbl_tdrawassign t1, tbl_tdrawassign t2 WHERE t1.id = t2.id GROUP BY t2.inq_id) as tbl1, tbl_tdrawassign r1, pr_company_details c1 WHERE tbl1.r_id = r1.id AND r1.inq_id = c1.rawdata_id AND (c1.status IS NULL OR NOT ((r1.stage = 'lead' AND c1.status = 'Positive') OR ((r1.stage IN ('badlead', 'revisedbadlead')) AND c1.status = 'Negative') OR (r1.stage NOT IN ('lead', 'badlead', 'revisedbadlead') AND c1.status = 'Existing Client')))");
	$stmt->execute();
	$res = $stmt->get_result();
	$stmt->close();

	if(mysqli_num_rows($res)>0){
		while($result=mysqli_fetch_array($res)){
			if($result['stage']=='lead'){
				$status = 'Positive';
			}
			else if($result['stage']=='badlead' || $result['stage']=='revisedbadlead'){
				$status = 'Negative';
			}
			else{
				$status = 'Existing Client';
			}

			$stmt_upd = $obj->con1->prepare("UPDATE pr_company_details SET status=? WHERE rawdata_id=?");
			$stmt_upd->bind_param("si",$status,$result['inq_id']);
			$res_upd = $stmt_upd->execute();
			$stmt_upd->close();
    }
  }
    
?>