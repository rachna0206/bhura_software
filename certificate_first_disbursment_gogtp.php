<?php 

        // bank name , bank branch

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


        $stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
        $stmt_files->bind_param("iiii",$service_id,$stage_id,$file_id,$inq_id);
        $stmt_files->execute();
        $result_files = $stmt_files->get_result()->fetch_assoc();
        $stmt_files->close();

        $file_data=json_decode($result_files["file_data"]);                                             

        header("Content-type: application/vnd.ms-word");  
        header("Content-Disposition: attachment;Filename=CertificateRegardingFirstDisbursement.doc");  
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
        <title>APPRAISAL LETTER</title>
</head>
<body>
        <h1 style="font-family: calibri;text-align: center;font-size: 24px;"><u>Certificate regarding the Disbursement of the term loan</u></h1>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                This is to certify that <strong><u><?php echo $post_fields->Firm_Name ?></u></strong> at <strong><u><?php echo $post_fields->Factory_Address ?></u></strong>. has availed various credit facilities including Term Loan for a project of <strong><u><?php echo $file_data->project ?></u></strong>. We Have Sanctioned term loan of <strong><u>Rs. <?php echo number_format($file_data->sanctioned_term_loan,2) ?></u></strong> for project on <strong><u><?php echo date_format(new DateTime($file_data->project_date),"d/m/Y") ?></u></strong>. In that concern, we have disbursed term loan of <strong><u>Rs. <?php echo number_format($file_data->disbursed_term_loan,2) ?></u></strong>. for its project as underneath table, which is duly verified by us and details are as given below.
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                The detail of Term Loan is as under.
                <ol>
                        <li>Application received from unit on <strong><u><?php echo date_format(new DateTime($file_data->application_received_dt),"d/m/Y") ?>.</u></strong></li>
                        <li>Term Loan Sanction <strong><u>dated  <?php echo date_format(new DateTime($file_data->sanction_loan_dt),"d/m/Y") ?>.</u></strong></li>
                        <li>Disbursement was made on 1st Disbursement as on <strong><u>Dt. <?php echo date_format(new DateTime($file_data->first_disbursement_dt),"d/m/Y") ?> of Rs. <?php echo number_format($file_data->first_disbursement_price,2) ?>/-</u></strong></li>
                        <li>Total Disbursement of <strong><u>Rs. <?php echo number_format($file_data->total_disbursement_price,2) ?>/- up to Dt. <?php echo date_format(new DateTime($file_data->total_disbursement_dt),"d/m/Y") ?>.</u></strong></li>
                        <li>Disbursement was made against following Fixed Assets:</li>
        </p>

        <table align="center" style="font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;">
                <tr>
                        <th style="border: 1px solid;">Fixed Assets</th>
                        <th style="border: 1px solid;">Cost as per project report</th>
                        <th style="border: 1px solid;">Term loan sanctioned</th>
                        <th style="border: 1px solid;">Total Actual Investment against Term Loan</th>
                        <th style="border: 1px solid;">Total Disbursed Term Loan against actual investment.</th>
                </tr>
                        
                <tr>
                        <th style="border: 1px solid;">a. Land</th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->cost_land,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->sanctioned_term_land,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->total_investment_land,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->disbursed_term_land,2) ?></th>
                </tr>

                <tr>
                        <th style="border: 1px solid;">b.  Building & Shed</th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->cost_building,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->sanctioned_term_building,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->total_investment_building,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->disbursed_term_building,2) ?></th>
                </tr>

                <tr>
                        <th style="border: 1px solid;">c.  Plant & M/C</th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->cost_plant,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->sanctioned_term_plant,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->total_investment_plant,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->disbursed_term_plant,2) ?></th>
                </tr>

                <tr>
                        <th style="border: 1px solid;">d.  Electrification</th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->cost_electric,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->sanctioned_term_electric,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->total_investment_electric,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->disbursed_term_electric,2) ?></th>
                </tr>

                <tr>
                        <th style="border: 1px solid;">e. Tools and Equipment</th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->cost_tools,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->sanctioned_term_tools,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->total_investment_tools,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->disbursed_term_tools,2) ?></th>
                </tr>

                <tr>
                        <th style="border: 1px solid;">f. Accessories</th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->cost_accessories,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->sanctioned_term_accessories,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->total_investment_accessories,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->disbursed_term_accessories,2) ?></th>
                </tr>

                <tr>
                        <th style="border: 1px solid;">g. Utilities and Effluent Treatment plant</th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->cost_utilities,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->sanctioned_term_utilities,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->total_investment_utilities,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->disbursed_term_utilities,2) ?></th>
                </tr>

                <tr>
                        <th style="border: 1px solid;">h. Other investments</th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->cost_other,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->sanctioned_term_other,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->total_investment_other,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->disbursed_term_other,2) ?></th>
                </tr>

                <tr>
                        <th style="border: 1px solid;">Total</th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->cost_total,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->sanctioned_term_total,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->total_investment_total,2) ?></th>
                        <th style="border: 1px solid;"><?php echo number_format($file_data->disbursed_term_total,2) ?></th>
                </tr>
        </table><br/>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                <strong><u>TERM LOAN ACCOUNT NO. <?php echo $file_data->loan_account_no ?>.</u></strong>
        </p>

        <p style="font-family: calibri;text-align: left;font-size: 22px;">Signature:</p>

        <p style="font-family: calibri;text-align: right;font-size: 22px;">
                Name of Branch Manager:<br/>
                Chief Manager-RM(ME):<br/>
                Name of Bank: <strong>UNION BANK OF INDIA</strong><br/>
                Branch: PARLE POINT<br/>
                Email: <?php echo $file_data->branch_manager_email ?><br/>
        </p>

        <p style="font-family: calibri;text-align: left;font-size: 22px;">
                To,<br/>
                The General Manager / JCI (S/T)<br/>
                District Industrial Centre / Industries Commissionerate Lal<br/> 
                Bungalow Compound/ Udyog Bhavan<br/>
                District Name / Gandhinagar<br/>
        </p>
        
        
</body>
</html>