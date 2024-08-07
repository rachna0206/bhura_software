<?php
require 'vendor/autoload.php';
include ("db_connect.php");
$obj = new DB_Connect();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function fetchTableData($query,$obj) {
    $result = $obj->con1->query($query);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}

function generateExcel($data, $filename = 'export.xlsx') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Sheet1');

    if (empty($data)) {
        return;
    }

    // Add header row
    $headers = array_keys($data[0]);
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }

    // Add data rows
    $rowNum = 2;
    foreach ($data as $row) {
        $col = 'A';
        foreach ($row as $cell) {
            $sheet->setCellValue($col . $rowNum, $cell);
            $col++;
        }
        $rowNum++;
    }

    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'. $filename .'"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
}

// Example usage:
$query = isset($_POST['query']) ? $_POST['query'] : null;

if ($query) {
    $data = fetchTableData($query,$obj);
    generateExcel($data);
}
?>