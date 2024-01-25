<?php

include "arya_docx_test.php";

$inq_id = $_REQUEST['inq_id'];
$service_id = $_REQUEST['service_id'];
$stage_id = $_REQUEST['stage_id'];


// tbl_tdrawdata -> company data
$stmt_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` where id=?");
$stmt_list->bind_param("i", $inq_id);
$stmt_list->execute();
$result = $stmt_list->get_result()->fetch_assoc();
$stmt_list->close();
$row_data = json_decode($result["raw_data"]);
$post_fields = $row_data->post_fields;

// pr_file_format -> all files at this stage
$stmt_file_list = $obj->con1->prepare("SELECT fid, doc_file FROM `pr_file_format` where stage_id=? and scheme_id=? and get_data_type='fetch'");
$stmt_file_list->bind_param("ii", $stage_id, $service_id);
$stmt_file_list->execute();
$result_file_list = $stmt_file_list->get_result();
$stmt_file_list->close();

$list = array();
$temp = array();
while ($res_files = mysqli_fetch_array($result_file_list)) {

    $temp["file_id"] = $res_files["fid"];
    $temp["doc_file"] = $res_files["doc_file"];
    array_push($list, $temp);
}
$stmt_files_completed = $obj->con1->prepare("SELECT distinct(d1.file_id), f1.doc_file FROM `pr_files_data` d1, `pr_file_format` f1 WHERE d1.scheme_id=? and d1.stage_id=? and d1.inq_id=? and d1.status='Completed' and d1.file_id=f1.fid");
$stmt_files_completed->bind_param("iii", $service_id, $stage_id, $inq_id);
$stmt_files_completed->execute();
$result_files_completed = $stmt_files_completed->get_result();
$stmt_files_completed->close();

while ($res_files_comp = mysqli_fetch_array($result_files_completed)) {
    $temp["file_id"] = $res_files_comp["file_id"];
    $temp["doc_file"] = $res_files_comp["doc_file"];
    array_push($list, $temp);
}

$new_file = array();

$zipPath = sys_get_temp_dir() . '/export.zip';
$zip = new ZipArchive();
$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
for($i=0;$i<sizeof($list);$i++){
    $temp = fill_file($inq_id, $service_id, $stage_id, $list[$i]["file_id"],$list[$i]["doc_file"]);
    $zip->addFile($temp, $list[$i]["doc_file"]);
    
}
$zip->close();

// Set the appropriate headers for the download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="export_' . $post_fields->Firm_Name . '.zip"');
header('Content-Length: ' . filesize($zipPath));

// Send the zip file to the user
readfile($zipPath);

// Clean up - remove temporary directory and zip file
unlink($zipPath);
unlink($temp);

?>