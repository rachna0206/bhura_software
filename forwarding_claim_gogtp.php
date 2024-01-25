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
        header("Content-Disposition: attachment;Filename=ForwardingClaim.doc"); 
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
        <title>FORWARDING CLAIM</title>
</head>
<body>
        <p style="font-family: calibri;text-align: right;font-size: 16px;">Date:04/05/2023</p>

        <p style="font-family: calibri;text-align: left;font-size: 16px;">
                To,<br/>
                The General Manager,<br/>
                District Industries Centre,<br/>
                C-Block, 2ND Floor,<br/>
                Nanpura, Surat.
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;">
                <strong><u>Sub: - 1TH & 4TH Quarterly Application for interest subsidy & power tariff under GOGTP- 2019.</u></strong>
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;">Respected Sir/Madam,</p>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;">
                <strong>
                        Application no.:- 2431243<br/>
                        Quarter Period: - 14/04/2022 TO 31/03/2023<br/>
                        Loan amount: - 3,50,00,000.00
                </strong>
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;">&emsp;&emsp;&emsp;&emsp;&emsp;
                With reference to the above subject & details, herewith we are submitting a claim for interest reimbursement & power tariff subsidy under <strong>(GOGTP-2019)</strong>. Kindly find the necessary document for your ready reference and do the needful at your end as soon as possible.
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;">&emsp;&emsp;&emsp;&emsp;&emsp;
                Kindly let us know if any information or document is required from our side.
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;">Thanking You,</p>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;"><strong><u>Enclosed document: -</u></strong></p>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;">
                <ol>
                        <li>Application</li>
                        <li>Power Tariff Eligibility Certificate</li>
                        <li>Annexure PT-1 &  Annexure PT-5</li>
                        <li>Electricity Consumption bills for the Claim Period</li>
                        <li>First Motive power bill</li>
                        <li>Valid Insurance policy for Plant & Machinery</li>
                        <li>GPCB Copy</li>
                        <li>First Sale Bill</li>
                        <li>cancel cheque</li>
                        <li>Details of Gross Fixed Capital Investment (List of Machinery)</li>
                        <li>Receipts of electricity bills for the claim</li>
                </ol>
        </p>
        
</body>
</html>