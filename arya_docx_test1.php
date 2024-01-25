<?php

include "db_connect.php";

$obj = new DB_connect();
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

function fill_file($inq_id, $service_id, $stage_id, $file_id, $doc_file)
{
    $db = new DB_connect();
    echo "SELECT * FROM `tbl_tdapplication` where inq_id='".$inq_id."' order by id DESC LIMIT 1";
    $stmt_list = $db->con1->prepare("SELECT * FROM `tbl_tdapplication` where inq_id=".$inq_id." order by id DESC LIMIT 1");
    //$stmt_list->bind_param("i", $inq_id);
    $stmt_list->execute();
    $result = $stmt_list->get_result()->fetch_assoc();
    $stmt_list->close();

    $row_data = json_decode($result["app_data"]);
    $contact_details = $row_data->contact_details;
    $company_details = $row_data->company_details;

    // print_r($row_data);
    // echo "<br/>".$company_details->cname;
    // echo "<br/>".$company_details->Company_Address;

    $stmt_files = $db->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
    $stmt_files->bind_param("iiii", $service_id, $stage_id, $file_id, $inq_id);
    $stmt_files->execute();
    $result_files = $stmt_files->get_result();
    $stmt_files->close();
    $templateFileName = "pr_file_format/" . $doc_file;
    $outputFileName = $doc_file;

    $templateProcessor = new TemplateProcessor($templateFileName);
    if (mysqli_num_rows($result_files) != 0) {
        $res_files = $result_files->fetch_assoc();
        $file_data = json_decode($res_files["file_data"]);
        foreach ($file_data as $key => $value) {
            if (is_array($value)) {
                $templateProcessor->cloneRowAndSetValues($key, $value);

            } else if (is_numeric($value)) {
                $templateProcessor->setValue($key, number_format($value, 2));
            } else if (is_string($value) && strtotime($value)) {
                $value = date('d/m/Y', strtotime($value));
                $templateProcessor->setValue($key, $value);
            } else {
                $templateProcessor->setValue($key, $value);
            }

        }
    }
    foreach ($contact_details as $key => $value) {
        if(!is_object($value) && !is_array($value)) {
            $templateProcessor->setValue($key, $value);
        }
    }

    foreach ($company_details as $key => $value) {
        if(!is_object($value) && !is_array($value)) {
            $templateProcessor->setValue($key, $value);
        }
        
    }

    $templateProcessor->saveAs($outputFileName);
    $full_path = $outputFileName;
    return $full_path;

}

function ($full_path) {
  /*  header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $full_path);
    header('Content-Length: ' . filesize($full_path));
    readfile($full_path);
    unlink($full_path);*/
}

    ?>