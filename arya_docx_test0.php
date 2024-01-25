<?php
error_reporting(E_ALL);
include "db_connect.php";


require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
 echo "hi";
function fill_file($inq_id, $service_id, $stage_id, $file_id, $doc_file)
{

$db = new DB_connect();
    echo "SELECT * FROM `tbl_tdapplication` where inq_id='".$inq_id."' order by id DESC LIMIT 1";
    $inq_id1="'.$inq_id.'";
    $stmt_list = $db->con1->prepare("SELECT * FROM `tbl_tdapplication` where inq_id='".$inq_id."' order by id DESC LIMIT 1");
   // $stmt_list->bind_param("i", $inq_id);
    $stmt_list->execute();
    $res_list = $stmt_list->get_result();
    $stmt_list->close();
   // print_r($res_list);
   /* echo "qry=".$qry="SELECT * FROM `tbl_tdapplication` where inq_id='2255' order by id DESC LIMIT 1";
    $res_qry=$obj->select($qry);


   
    print_r(mysqli_fetch_array($res_qry));*/

     //print_r($res_list);
    // echo "<br/>".$company_details->cname;
    // echo "<br/>".$company_details->Company_Address;

    echo "SELECT * FROM `pr_files_data` WHERE scheme_id=$service_id and stage_id=$stage_id and file_id=$file_id and inq_id=$inq_id order by id desc limit 1";
    $stmt_files = $db->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
    $stmt_files->bind_param("iiii", $service_id, $stage_id, $file_id, $inq_id);
    $stmt_files->execute();
    $result_files1 = $stmt_files->get_result();
    $stmt_files->close();
    $templateFileName = "pr_file_format/" . $doc_file;
    $outputFileName = $doc_file;
    //print_r($result_files);
    //echo "num rows=".mysqli_num_rows($result_files) ;
    $templateProcessor = new TemplateProcessor($templateFileName);

    if($res_files = mysqli_fetch_array($result_files1)){
       
        
       // print_r($res_files);
        
         $file_data = json_decode($res_files["file_data"]);
        foreach ($file_data as $key => $value) {
            if (is_array($value)) {
                $templateProcessor->cloneRowAndSetValues($key, $value);

            } else if (is_numeric($value)) {
                $templateProcessor->setValue($key, number_format($value, 2));
            } else if (is_string($value) && strtotime($value)) {
                $value = date('d/m/Y', strtotime($value));
                $templateProcessor->setValue($key, $value);
            } 
            else {
                $templateProcessor->setValue($key, $value);
            }

        }
    }
   // if (mysqli_num_rows($result) != 0) {
       //print_r(mysqli_fetch_array($res_qry));
        $res = $result1->fetch_assoc();

        echo "json".$row_data = json_decode($res["app_data"]);
       // $row_data = json_decode({"contact_details":{"fname":"CHANDUBHAI","mname":"BAVCHANDBHAI","lname":"KHATRANI","mobile":"8160579998","email":"","Designation":"Administrator","Aadhar":"818105903540","PAN":"ASRPK3514C","Date_of_Birth":"1985-04-05","addr1":"I-402, 4th Floor, Kaveri Hebitat, Opp: Madhuram Residency, Near Meghmalhar, V.T. Nagar Road, Sarthana, Surat. 395 006","state":"GUJARAT","city":"","Taluka":"","area":""},"application_company_id":"","company_details":{"ctype":"PROPRIETORSHIP","cname":"MIHAN ENTERPRISE","gstno":"24BXUPS9100B1ZE","gstdate":"2022-12-13","cpanno":"BXUPS9100B","cpandate":"","Company_Address":"Plot No 15, 4th Floor, Vrunda Embro Park, Opp: Shubham Industrial Estate, Saniya Hemad, Surat.","state":"GUJARAT","city":"SURAT","Taluka":"Chorasi","Area":"Saniya Hemad","IndustrialEstate":"VRUNDA EMBRO PARK","Category":"Micro","Segment":"Embroidery Machine (Stand Alone)","Premise":"Rental","teamdetails":[{"name":"ASHISH HASMUKHBHAI SATASIIYA","number":"","Shares":"100","aadhar":"799468306129","panno":"BXUPS9100B","dob":"1989-11-15"}]},"application":{"vertical":"2","services":[{"service_id":"1","service_action":"Eligible and Processing","service_remark":"Cap. 10% & Int. 6%"},{"service_id":"2","service_action":"Eligible but Not Processing","service_remark":"Policy not active"},{"service_id":"4","service_action":"Not Eligible","service_remark":"Rental Premises"},{"service_id":"5","service_action":"Not Eligible","service_remark":"Rental Premises"},{"service_id":"10","service_action":"Not Eligible","service_remark":"Only for SC & ST"},{"service_id":"11","service_action":"Eligible and Processing","service_remark":"Currently Inactive, to be applied if declared "}],"advance_payment":"Less Adv. & Full Cons. Charges","advance_payment_remark":"Cheque No 334456 recieved, Union Bank Of India","payment":{"advance_payment_amount":"5900","advance_payment_mode":"Cheque","advance_payment_other_details":"Cheque No UBI 334456, Union Bank Of India","advance_payment_remark":""}}});
       /* $contact_details = $row_data->contact_details;
        $company_details = $row_data->company_details;

        foreach ($contact_details as $key => $value) {
            if(!is_object($value) && !is_array($value)) {
                $templateProcessor->setValue($key, $value);
            }
        }

        foreach ($company_details as $key => $value) {
            if(!is_object($value) && !is_array($value)) {
                $templateProcessor->setValue($key, $value);
            }
        }*/
 //   }

   /* $templateProcessor->saveAs($outputFileName);
    $full_path = $outputFileName;
    return $full_path;*/

}

// function ($full_path) {
//     header('Content-Type: application/octet-stream');
//     header('Content-Disposition: attachment; filename=' . $full_path);
//     header('Content-Length: ' . filesize($full_path));
//     readfile($full_path);
//     unlink($full_path);
// }

    ?>