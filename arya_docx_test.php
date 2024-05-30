<?php

include "db_connect.php";
$obj = new DB_connect();
require_once 'vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\IOFactory;

function fill_file($inq_id, $service_id, $stage_id, $file_id, $doc_file, $doc_type)
{
    $templateFileName = "pr_file_format/" . $doc_file;
    $outputFileName = $doc_file;
    $templateProcessor = new TemplateProcessor($templateFileName);
    if ($doc_type == "excel") {
        return excel_fill($inq_id, $service_id, $stage_id, $file_id, $doc_file);
    }
    $stmt_list = $GLOBALS['obj']->con1->prepare("SELECT * FROM `tbl_tdapplication` where inq_id=? order by id DESC LIMIT 1");
    $stmt_list->bind_param("i", $inq_id);
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();
    $stmt_files = $GLOBALS['obj']->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
    $stmt_files->bind_param("iiii", $service_id, $stage_id, $file_id, $inq_id);
    $stmt_files->execute();
    $result_files = $stmt_files->get_result();
    $stmt_files->close();
    if (mysqli_num_rows($result_files)>0) {
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
    if (mysqli_num_rows($result) != 0) {
        $result = mysqli_fetch_array($result);
        $row_data = json_decode($result["app_data"]);
        if (isset($row_data->contact_details)) {
            $contact_details = $row_data->contact_details;

            foreach ($contact_details as $key => $value) {
                if (!is_object($value) && !is_array($value)) {
                    $templateProcessor->setValue($key, $value);
                }
            }
        }
        if (isset($row_data->company_details)) {
            $company_details = $row_data->company_details;
            foreach ($company_details as $key => $value) {
                if (!is_object($value) && !is_array($value)) {
                    $templateProcessor->setValue($key, $value);
                }

            }
        }
        if (isset($row_data->bank_details)) {
            $bank_details = $row_data->bank_details;
            foreach ($bank_details as $key => $value) {
                if (!is_object($value) && !is_array($value)) {
                    $templateProcessor->setValue($key, $value);
                }

            }
        }



    }

    $templateProcessor->saveAs($outputFileName);
    $full_path = $outputFileName;
    return $full_path;


}
function excel_fill($inq_id, $service_id, $stage_id, $file_id, $doc_file)
{


    $stmt_list = $GLOBALS['obj']->con1->prepare("SELECT * FROM `tbl_tdapplication` where inq_id=? order by id DESC LIMIT 1");
    $stmt_list->bind_param("i", $inq_id);
    $stmt_list->execute();
    $result = $stmt_list->get_result();
    $stmt_list->close();


    $stmt_files = $GLOBALS['obj']->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
    $stmt_files->bind_param("iiii", $service_id, $stage_id, $file_id, $inq_id);
    $stmt_files->execute();
    $result_files = $stmt_files->get_result();
    $stmt_files->close();


    $templateFileName = "pr_file_format/" . $doc_file;
    $outputFileName = $doc_file;

    $template = IOFactory::load($templateFileName);
    $worksheet = $template->getActiveSheet();

    $file_data_status = false;
    $company_detail_status = false;
    $contact_detail_status = false;
    $bank_detail_status = false;

    if (mysqli_num_rows($result_files) > 0 || mysqli_num_rows($result) > 0) {

        if (mysqli_num_rows($result_files) > 0) {
            $res = mysqli_fetch_array($result_files);
            $file_data = json_decode($res['file_data'], true);
            $file_data_status = true;
        }

        if (mysqli_num_rows($result) > 0) {
            $result = mysqli_fetch_array($result);
            $row_data = json_decode($result["app_data"]);

            if (property_exists($row_data, 'contact_details')) {
                $contact_details = $row_data->contact_details;
                $contact_detail_status = true;
            }
            if (property_exists($row_data, 'company_details')) {
                $company_details = $row_data->company_details;
                $company_detail_status = true;
            }
            if (property_exists($row_data->application, 'bank_details')) {
                $bank_details = $row_data->application->bank_details[0];
                $bank_detail_status = true;
            }
        }

        for ($row = 1; $row <= $worksheet->getHighestRow(); $row++) {
            for ($col = 'A'; $col <= $worksheet->getHighestColumn(); $col++) {
                $cell = $worksheet->getCell($col . $row);
                $value = $cell->getValue();

                if ($file_data_status) {
                    $value = preg_replace_callback('/\${(.*?)}/', function ($matches) use ($file_data) {
                        $variable = $matches[1];
                        if (array_key_exists($variable, $file_data)) {
                            $replacement = $file_data[$variable];

                            if (is_array($replacement)) {
                                $replacement = array_map(function ($item) {
                                    return is_array($item) ? json_encode($item) : $item;
                                }, $replacement);
                                return implode("\n", $replacement);
                            } else if (is_numeric($replacement)) {
                                return number_format($replacement, 2);
                            } else if (strtotime($replacement)) {
                                return date('d/m/Y', strtotime($replacement));
                            } else {
                                return $replacement;
                            }
                        }

                        return $matches[0]; 
                    }, $value);
                }

                if ($contact_detail_status) {
                    $value = preg_replace_callback('/\${(.*?)}/', function ($matches) use ($contact_details) {
                        $variable = $matches[1];
                        if (property_exists($contact_details, $variable)) {

                            $replacement = $contact_details->$variable;

                            if (is_array($replacement)) {
                                // Handle arrays
                                $replacement = array_map(function ($item) {
                                    return is_array($item) ? json_encode($item) : $item;
                                }, $replacement);
                                return implode("\n", $replacement);
                            } else if (is_numeric($replacement)) {
                                return number_format($replacement, 2);
                            } else if (strtotime($replacement)) {
                                return date('d/m/Y', strtotime($replacement));
                            } else {
                                return $replacement;
                            }
                        }

                        return $matches[0]; 
                    }, $value);
                }

                if ($company_detail_status) {
                    $value = preg_replace_callback('/\${(.*?)}/', function ($matches) use ($company_details) {
                        $variable = $matches[1];
                        if (property_exists($company_details, $variable)) {
                            $replacement = $company_details->$variable;

                            if (is_array($replacement)) {
                                $replacement = array_map(function ($item) {
                                    return is_array($item) ? json_encode($item) : $item;
                                }, $replacement);
                                return implode("\n", $replacement);
                            } else if (is_numeric($replacement)) {
                                return number_format($replacement, 2);
                            } else if (strtotime($replacement)) {
                                return date('d/m/Y', strtotime($replacement));
                            } else {
                                return $replacement;
                            }
                        }

                        return $matches[0]; 
                    }, $value);
                }

                if ($bank_detail_status) {
                    $value = preg_replace_callback('/\${(.*?)}/', function ($matches) use ($bank_details) {
                        $variable = $matches[1];
                        if (property_exists($bank_details, $variable)) {
                            $replacement = $bank_details->$variable;

                            if (is_array($replacement)) {
                                // Handle arrays
                                $replacement = array_map(function ($item) {
                                    return is_array($item) ? json_encode($item) : $item;
                                }, $replacement);
                                return implode("\n", $replacement);
                            } else if (is_numeric($replacement)) {
                                return number_format($replacement, 2);
                            } else if (strtotime($replacement)) {
                                return date('d/m/Y', strtotime($replacement));
                            } else {
                                return $replacement;
                            }
                        }

                        return $matches[0]; 
                    }, $value);
                }

                $cell->setValue($value);
            }
        }
    }


    for ($row = 1; $row <= $worksheet->getHighestRow(); $row++) {
        for ($col = 'A'; $col <= $worksheet->getHighestColumn(); $col++) {
            $cell = $worksheet->getCell($col . $row);
            $value = $cell->getValue();
            $value = explode(' ', $value);

            for ($i = 0; $i < count($value); $i++) {

                if (substr($value[$i], 0, 2) == '${' && substr($value[$i], -1) == '}') {
                    $cell->setValue("");
                }

            }
        }
    }
    $writer = IOFactory::createWriter($template, 'Xlsx');
    $writer->save($outputFileName);
    return $outputFileName;

}
?>