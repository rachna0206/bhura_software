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


        $stmt_files = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
        $stmt_files->bind_param("iiii",$service_id,$stage_id,$file_id,$inq_id);
        $stmt_files->execute();
        $result_files = $stmt_files->get_result()->fetch_assoc();
        $stmt_files->close();

        $file_data=json_decode($result_files["file_data"]);
        
        header("Content-type: application/vnd.ms-word");  
        header("Content-Disposition: attachment;Filename=CACertificate-NewFirm.doc");  
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
        <title>CA CERTIFICATE NEW FIRM</title>
</head>
<body>
        
        <h1 style="font-family: calibri;text-align: center;font-size: 24px;"><u>CA Certificate</u></h1>
        
        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                We hereby certify that M/s. <strong><u><?php echo $post_fields->Firm_Name ?></u></strong> has acquired following fixed assets up to <strong><u><?php echo date_format(new DateTime($file_data->acquired_assets_dt),"d/m/Y") ?></u></strong> at their factory address <strong><u><?php echo $post_fields->Factory_Address ?></u></strong>. for <strong><u>MANUFACTURE OF <?php echo $file_data->manufacturing_prod ?></u></strong> and have commenced the commercial production on  <strong><u><?php echo date_format(new DateTime($file_data->commercial_date),"d/m/Y") ?></u></strong> and raised the first invoice on <strong><u><?php echo date_format(new DateTime($file_data->first_invoice_date),"d/m/Y") ?></u></strong> bearing on 1 dated <strong><u><?php echo date_format(new DateTime($file_data->first_invoice_date),"d/m/Y") ?></u></strong> for   a    total    invoice    value    of Rs. <strong><u><?php echo number_format($file_data->invoice_value,2) ?></u></strong>.
        </p>
        
        <table align="center" style="font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;">
                <tr>
                        <th style="border: 1px solid;">Sr. No.</th>
                        <th style="border: 1px solid;">Description of Assets</th>
                        <th style="border: 1px solid;">Gross Fixed Capital Assets</th>
                </tr>

                <tr>
                        <td style="border: 1px solid;">1<strong></strong></td>
                        <td style="border: 1px solid;">Land</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->land,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>2</strong></td>
                        <td style="border: 1px solid;">Building & Shed</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->building_shed,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>3</strong></td>
                        <td style="border: 1px solid;">Plant & M/C</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->plant_mc,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>4</strong></td>
                        <td style="border: 1px solid;">Electrification</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->electrification,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>5</strong></td>
                        <td style="border: 1px solid;">Tools and Equipment</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->tools_equipment,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>6</strong></td>
                        <td style="border: 1px solid;">Accessories</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->accessories,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>7</strong></td>
                        <td style="border: 1px solid;">Utilities and Effluent Treatment plant</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->utilities,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>8</strong></td>
                        <td style="border: 1px solid;">Other investments</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->investments,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;" colspan="2"><strong>Total</strong></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->total_gross_capital,2) ?></td>
                </tr>
        </table><br/><br/>

        <table align="center" style="font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;">
                <tr>
                        <th colspan="3">Details of Means of Finance</th>
                </tr>

                <tr>
                        <th style="border: 1px solid;">Sr. No.</th>
                        <th style="border: 1px solid;">Particulars</th>
                        <th style="border: 1px solid;">Total Amount</th>
                </tr>

                <tr>
                        <td style="border: 1px solid;"><strong>1</strong></td>
                        <td style="border: 1px solid;">a. Equity Share Capital</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->capital,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>2</strong></td>
                        <td style="border: 1px solid;">b. Equity Share Premium</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->premium,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>3</strong></td>
                        <td style="border: 1px solid;">c. Bank Term Loan</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->term_loan,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>4</strong></td>
                        <td style="border: 1px solid;">d. Working Capital Loan</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->capital_loan,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>5</strong></td>
                        <td style="border: 1px solid;">e. Internal Source of Fund</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->internal_source,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"><strong>6</strong></td>
                        <td style="border: 1px solid;">f. Others</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->others,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;" colspan="2"><strong>Total</strong></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->total_amount_finance,2) ?></td>
                </tr>
        </table>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                We have checked the books of accounts of the Enterprise and Invoice etc. and certify that the aforesaid information is verified to be true. We also certify that all the aforesaid items have been duly paid and no credits is raised there against in the books of the Enterprise, except those stated above.
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                Date : &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;For
        </p>
        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                Place : &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Signature,
        </p>
        <p style="font-family: calibri;text-align: right;font-size: 22px;">Membership No</p>
        <p style="font-family: calibri;text-align: right;font-size: 22px;">UDIN No</p>
        
</body>
</html>