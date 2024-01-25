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
        header("Content-Disposition: attachment;Filename=CACertificate-Expansion.doc");  
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
        <title>CA CERTIFICATE EXPANSION</title>
</head>
<body>
        
        <h1 style="font-family: calibri;text-align: center;font-size: 24px;"><u>CHARTERED ENGINEER’S CERTIFICATE</u></h1>
        
        <p style="font-family: calibri;text-align: justify;font-size: 22px;"><strong>(For Expansion / Diversification / Forward Integration/ Backward Integration)</strong></p>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                This is to certify that M/s. <strong><u><?php echo $post_fields->Firm_Name ?></u></strong> located at <strong><u><?php echo $post_fields->Factory_Address ?></u></strong> for the manufacturing of <strong><u><?php echo $post_fields->Segment ?></u></strong> product/s. have acquired following fixed assets.
        </p>
        
        <table align="center" style="font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;">
                <tr>
                        <th style="border: 1px solid;" rowspan="2">No</th>
                        <th style="border: 1px solid;" rowspan="2">Break- up Fixed assets</th>
                        <th style="border: 1px solid;">Gross Fixed Capital Investment for Expansion/ Diversification/Forward Integration/Backward Integration as on the date of initiating expansion/ the date of initiating expansion Diversification/Forward Integration/Backward Integration</th>
                        <th style="border: 1px solid;">Gross Fixed Capital Investment for Expansion/ Diversification / Forward Integration/Backward Integration from the date of initiating expansion up to the date of commencing production/till Complete Project during the Diversification / Forward Integration/Backward Integration</th>
                        <th style="border: 1px solid;" rowspan="2">Total Investments as on date <?php echo date_format(new DateTime($file_data->total_investment_dt),"d/m/Y") ?></th>
                </tr>
                <tr>
                        <th style="border: 1px solid;"><?php echo date_format(new DateTime($file_data->ini_expansion_dt),"d/m/Y") ?></th>
                        <th style="border: 1px solid;"><?php echo date_format(new DateTime($file_data->from_expansion_dt),"d/m/Y") ?> to <?php echo date_format(new DateTime($file_data->to_initiating_dt),"d/m/Y") ?></th>
                </tr>

                <tr>
                        <td style="border: 1px solid;">1</td>
                        <td style="border: 1px solid;">Land</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->ini_investment_land,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->comm_investment_land,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->total_land,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;">2</td>
                        <td style="border: 1px solid;">Building</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->ini_investment_building,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->comm_investment_building,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->total_building,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;">3</td>
                        <td style="border: 1px solid;">Plant & Machinery</td>
                        <td style="border: 1px solid;"><strong><?php echo number_format($file_data->ini_investment_plant,2) ?></strong></td>
                        <td style="border: 1px solid;"><strong><?php echo number_format($file_data->comm_investment_plant,2) ?></strong></td>
                        <td style="border: 1px solid;"><strong><?php echo number_format($file_data->total_plant,2) ?></strong></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;">4</td>
                        <td style="border: 1px solid;">Utilities</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->ini_investment_utilities,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->comm_investment_utilities,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->total_utilities,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;">5</td>
                        <td style="border: 1px solid;">Tools & Equipment</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->ini_investment_tools,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->comm_investment_tools,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->total_tools,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;">6</td>
                        <td style="border: 1px solid;">Electrification</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->ini_investment_electric,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->comm_investment_electric,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->total_electric,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;">7</td>
                        <td style="border: 1px solid;">Other Assets (Required Manufacturing the end Product)</td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->ini_investment_assets,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->comm_investment_assets,2) ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->total_assets,2) ?></td>
                </tr>
                <tr>
                        <td style="border: 1px solid;"></td>
                        <td style="border: 1px solid;"><strong>Total</strong></td>
                        <td style="border: 1px solid;"><strong><?php echo number_format($file_data->total_ini_investment,2) ?></strong></td>
                        <td style="border: 1px solid;"><strong><?php echo number_format($file_data->total_comm_investment,2) ?></strong></td>
                        <td style="border: 1px solid;"><strong><?php echo number_format($file_data->final_total,2) ?></strong></td>
                </tr>
                
        </table>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                We have checked the book of accounts, invoices, balance sheet etc. of the enterprise and the information is verified and certified to be true. We certify that all the aforesaid items have been duly paid and no credit is raised.
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