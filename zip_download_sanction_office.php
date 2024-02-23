<?php

// include "arya_docx_test.php";

// $inq_id = $_REQUEST['inq_id'];
// $service_id = $_REQUEST['service_id'];
// $stage_id = $_REQUEST['stage_id'];


// // tbl_tdrawdata -> company data
// $stmt_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` where id=?");
// $stmt_list->bind_param("i", $inq_id);
// $stmt_list->execute();
// $result = $stmt_list->get_result()->fetch_assoc();
// $stmt_list->close();
// $row_data = json_decode($result["raw_data"]);
// $post_fields = $row_data->post_fields;

// // pr_file_format -> all files at this stage
// $stmt_file_list = $obj->con1->prepare("SELECT fid, doc_file FROM `pr_file_format` where stage_id=? and scheme_id=? and get_data_type='fetch'");
// $stmt_file_list->bind_param("ii", $stage_id, $service_id);
// $stmt_file_list->execute();
// $result_file_list = $stmt_file_list->get_result();
// $stmt_file_list->close();

// $list = array();
// $temp = array();
// while ($res_files = mysqli_fetch_array($result_file_list)) {

//     $temp["file_id"] = $res_files["fid"];
//     $temp["doc_file"] = $res_files["doc_file"];
//     array_push($list, $temp);
// }
// $stmt_files_completed = $obj->con1->prepare("SELECT distinct(d1.file_id), f1.doc_file FROM `pr_files_data` d1, `pr_file_format` f1 WHERE d1.scheme_id=? and d1.stage_id=? and d1.inq_id=? and d1.status='Completed' and d1.file_id=f1.fid");
// $stmt_files_completed->bind_param("iii", $service_id, $stage_id, $inq_id);
// $stmt_files_completed->execute();
// $result_files_completed = $stmt_files_completed->get_result();
// $stmt_files_completed->close();

// while ($res_files_comp = mysqli_fetch_array($result_files_completed)) {
//     $temp["file_id"] = $res_files_comp["file_id"];
//     $temp["doc_file"] = $res_files_comp["doc_file"];
//     array_push($list, $temp);
// }

// $new_file = array();

// $zipPath = sys_get_temp_dir() . '/export.zip';
// $zip = new ZipArchive();
// $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
// for($i=0;$i<sizeof($list);$i++){
//     $temp1 = fill_file($inq_id, $service_id, $stage_id, $list[$i]["file_id"],$list[$i]["doc_file"]);
//     $zip->addFile($temp1, $list[$i]["doc_file"]);
    
// }
// $zip->close();

// // Set the appropriate headers for the download
// header('Content-Type: application/zip');
// header('Content-Disposition: attachment; filename="export_' . $post_fields->Firm_Name . '.zip"');
// header('Content-Length: ' . filesize($zipPath));

// // Send the zip file to the user
// readfile($zipPath);

// // Clean up - remove temporary directory and zip file
// // unlink($zipPath);
// // unlink($temp1);




include "arya_docx_test.php";

$inq_id = $_REQUEST['inq_id'];
$service_id = $_REQUEST['service_id'];
$stage_id = $_REQUEST['stage_id'];
//  tbl_tdrawdata -> company data
$stmt_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` where id=?");
$stmt_list->bind_param("i", $inq_id);
$stmt_list->execute();
$result = $stmt_list->get_result()->fetch_assoc();
$stmt_list->close();
$row_data = json_decode($result["raw_data"]);
$post_fields = $row_data->post_fields;

// pr_file_format -> all files at this stage
$stmt_file_list = $obj->con1->prepare("SELECT fid, doc_file,doc_type FROM `pr_file_format` where stage_id=? and scheme_id=? and get_data_type='fetch'");
$stmt_file_list->bind_param("ii", $stage_id, $service_id);
$stmt_file_list->execute();
$result_file_list = $stmt_file_list->get_result();
$stmt_file_list->close();

// ... (Other code remains unchanged)

$list = array();
$temp = array();
while ($res_files = mysqli_fetch_array($result_file_list)) {

    $temp["file_id"] = $res_files["fid"];
    $temp["doc_file"] = $res_files["doc_file"];
    $temp["doc_type"] = $res_files["doc_type"];
    array_push($list, $temp);
}
$stmt_files_completed = $obj->con1->prepare("SELECT distinct(d1.file_id), f1.doc_file,f1.doc_type FROM `pr_files_data` d1, `pr_file_format` f1 WHERE d1.scheme_id=? and d1.stage_id=? and d1.inq_id=? and d1.status='Completed' and d1.file_id=f1.fid");
$stmt_files_completed->bind_param("iii", $service_id, $stage_id, $inq_id);
$stmt_files_completed->execute();
$result_files_completed = $stmt_files_completed->get_result();
$stmt_files_completed->close();

while ($res_files_comp = mysqli_fetch_array($result_files_completed)) {
    $temp["file_id"] = $res_files_comp["file_id"];
    $temp["doc_file"] = $res_files_comp["doc_file"];
    $temp["doc_type"] = $res_files_comp["doc_type"];
    array_push($list, $temp);
}

$new_file = array();

$zipPath = 'temp\\export.zip';
$zip = new ZipArchive();
// Check for the result of the zip file creation and handle errors
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    // Handle error, possibly log it and inform the user
    exit("Cannot open <$zipPath>\n");
}

$files_to_delete = array(); // Array to keep track of files to delete later

for($i=0;$i<count($list);$i++){
    $temp = fill_file($inq_id, $service_id, $stage_id, $list[$i]["file_id"],$list[$i]["doc_file"],$list[$i]["doc_type"]);
    if (file_exists($temp)) { // Check if the file exists before adding to the zip
        $zip->addFile($temp, $list[$i]["doc_file"]);
        $files_to_delete[] = $temp; // Add the temp file to the list for deletion
    } else {
        // Handle error for file not existing
    }
}
$zip->close();

// Set the appropriate headers for the download
// header('Content-Type: application\zip');

// Set the appropriate headers for the download
header('Content-Type: application\zip');//1st try
// header('Content-Type: application/octet-stream');//2nd try
// header('Content-Type: application/x-zip-compressed');//3rd try
// header('Content-Type: multipart/x-zip');//4th try
header('Content-Disposition: attachment; filename="export_' . urlencode($post_fields->Firm_Name) . '.zip"'); // urlencode to handle special characters
header('Content-Length: ' . filesize($zipPath));
header('Content-Disposition: attachment; filename="export_' . urlencode($post_fields->Firm_Name) . '.zip"'); // urlencode to handle special characters
header('Content-Length: ' . filesize($zipPath));

// Send the zip file to the user
readfile($zipPath);

// Clean up - remove temporary files and zip file
foreach ($files_to_delete as $file) {
    unlink($file); // Delete the individual temp files
}
unlink($zipPath); // Delete the zip file after sending it

?>