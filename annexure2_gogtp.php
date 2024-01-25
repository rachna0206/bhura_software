<?php 

        include "db_connect.php";
        $obj=new DB_connect();

        $inq_id = $_REQUEST['inq_id'];
        $service_id = $_REQUEST['service_id'];
        $stage_id = $_REQUEST['stage_id'];
        $file_id = $_REQUEST['file_id'];

        $stmt_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` where id=?");
        $stmt_list->bind_param("i",$inq_id);
        $stmt_list->execute();
        $result = $stmt_list->get_result()->fetch_assoc();
        $stmt_list->close();

        $row_data=json_decode($result["raw_data"]);
        $post_fields=$row_data->post_fields;

        /*$cp = Array(
                "FirmName" => $post_fields->Firm_Name,
                "Address" => $post_fields->Factory_Address
        );
        $json = json_encode($cp);

        $complete_status = "Completed";

        try{
                $stmt = $obj->con1->prepare("INSERT INTO `pr_files_data`(`scheme_id`, `stage_id`, `file_id`, `inq_id`, `file_data`, `status`) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param("iiiiss", $service_id, $stage_id, $file_id, $inq_id, $json, $complete_status);
                $Resp = $stmt->execute();
                $stmt->close();        
        }
        catch(\Exception $e){
                exit();
        }*/

        header("Content-type: application/vnd.ms-word");  
        header("Content-Disposition: attachment;Filename=Annexure-II.doc");  
        header("Pragma: no-cache");  
        header("Expires: 0");
?> 

<!doctype html>
<!DOCTYPE html>
<html>
<head>
        <meta charset="utf-8">
        <xml>
<w:WordDocument>
<w:View>Print
<w:Zoom>100
<w:DoNotOptimizeForBrowser/>
</w:WordDocument>
</xml>
<style>
        @page {
            size: A4;
            margin: 25mm 17.5mm; /* Margins: 2.5 cm top, 1.75 cm left/right */
        }
        body {
            font-family: calibri;
        }
    </style>
        <title>ANNEXURE II</title>
</head>
<body>
        <p style="font-family: calibri;text-align: left;font-size: 16px;">
                NAME OF BANK:- <strong><u>CANARA BANK</u></strong><br/>
                FULL ADDRESS:- <strong><u>RATAN KUTIR BUILDING, SALABATPURA, MAIN ROAD, SURAT-395003</u></strong><br/>
                IFSC NO.: <strong><u>CNRB0017170</u></strong><br/>
                ACCOUNT NUMBER OF ENTERPRISE <strong><u>120000271678</u></strong> FOR RTGS. <br/>
        </p>

        <h1 style="font-family: calibri;text-align: center;font-size: 22px;">ANNEXURE-” II”</h1>

        <h2 style="font-family: calibri;text-align: center;font-size: 22px;">CERTIFICATE FROM FINANCIAL INSTITUTION/BANK</h2>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;">
                &emsp;&emsp;&emsp;&emsp;This is to certify that <strong><u><?php echo $post_fields->Firm_Name ?></u></strong> has been sanctioned <strong><u>Rs. 3,50,00,000.00/-</u></strong> term loan on new plant & machinery for the project AT <strong><u><?php echo $post_fields->Factory_Address ?></u></strong> At the rate of <strong><u>8.25%</u></strong> interest for the project. The first disbursement date is <strong><u>18/02/2022.</u></strong>
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;">
                &emsp;&emsp;&emsp;&emsp;The Unit has been disbursed a total of <strong><u>Rs. 3,42,76,437.00/-</u> UP TO <u>26/09/2022.</u></strong> The first installment of the term loan of <strong><u>Rs.17,60,542.00/-</u></strong> was disbursed on date <strong><u>18/02/2022</u></strong> and their term loan account number is <strong><u>170003825630.</u></strong>  <br/><br/>
                The unit had made repayment for a period date – <strong><u>18/02/2022 TO 31/12/2022 ?></u></strong> as under. <br/><br/>
        </p>

        <p style="font-family: calibri;text-align: left;font-size: 16px;">
                Against term loan &emsp;&emsp;&emsp;&emsp; <strong><u>Rs.  11,03,165.15/- </u></strong><br/>
                As interest &emsp;&emsp;&emsp;&emsp; <strong><u>Rs.  24,26,707.35/-</u></strong><br/>
                Total &emsp;&emsp;&emsp;&emsp; <strong><u>Rs.  35,29,872.50/-</u></strong><br/><br/>
        </p>


        <p style="font-family: calibri;text-align: left;font-size: 16px;">
                The interest subsidy @ 6% on the amount disbursed for the period from: <strong><u>18/02/2022 TO 31/12/2022</u></strong> is <strong><u>RS. 15,97,477.00/-</u></strong><br/><br/>

                1) This is certifying that penal interest or other charges are not included in the said claim and the enterprise pays regular installments and interest to the bank.<br/>
                2) The enterprise is not a defaulter of the bank. (ii the case of a defaulter, please give defaulter period details) <br/>
        </p>


<p style="font-family: calibri;text-align: left;font-size: 16px;">
Place: SURAT  &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;  BANK MANAGER SIGN & STAMP<br/>
Date:                                                                             

        </p>

</body>
</html>