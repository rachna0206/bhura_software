<?php
include("arya_docx_test.php");

$inq_id = $_REQUEST['inq_id'];
$service_id = $_REQUEST['service_id'];
$stage_id = $_REQUEST['stage_id'];
$doc_file = $_REQUEST['doc_file'];
$file_id = $_REQUEST['file_id'];
$doc_type = $_REQUEST['doc_type'];


$full_path = fill_file($inq_id, $service_id, $stage_id, $file_id, $doc_file, $doc_type);

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . basename($full_path));
header('Content-Length: ' . filesize($full_path));
ob_clean();
flush();
readfile($full_path);
unlink($full_path);  
exit;
?>