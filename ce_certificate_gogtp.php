<?php 

        // years in table

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
        header("Content-Disposition: attachment;Filename=CECertificate.doc");  
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
        <title>CE CERTIFICATE</title>
</head>
<body>
        
        <h1 style="font-family: calibri;text-align: center;font-size: 24px;"><u>CHARTERED ENGINEER’S CERTIFICATE</u></h1>
        
        <p style="font-family: calibri;text-align: justify;font-size: 22px;">This is to certify that, <strong><u>M/s. <?php echo $post_fields->Firm_Name ?></u></strong> is located at <strong><u><?php echo $post_fields->Factory_Address ?></u></strong> and engaged in <strong><?php echo $post_fields->Segment ?></strong>.</p>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">I have visited the manufacturing facility of the unit and hereby opined that the applicant <strong><u>M/s. <?php echo $post_fields->Firm_Name ?></u></strong> fulfills all the provisions concerned to Expansion or Forward / Backward integration mentioned under the Government Resolution No. <u>TEX-102018-3327-CH</u>, dated <u>10/01/2019</u> for “Scheme for assistance to strengthen specific sector in the textile value chain”.</p>
        
        <table align="center" style="font-family: calibri;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 80%;">
                <tr>
                        <td style="border: 1px solid; text-align: center;">1.</td>
                        <td style="border: 1px solid; text-align: justify;">
                                <strong><u>For Expansion or Forward / Backward Integration</u></strong>
                                <ul>
                                        <li>Existing Gross Fixed Capital Investment before the initiation of Expansion or Forward/Backward Integration</li>
                                        <li>Gross Fixed Capital Investment installed during Expansion or Forward/Backward Integration period</li>
                                        <li>Total Gross Fixed capital Investment after the completion of Expansion or Forward/Backward Integration period</li>
                                        <li>Investment in Gross Fixed Capital Investment increased in percentage during Expansion or Forward/Backward Integration period</li>
                                </ul>
                        </td>
                        <td style="border: 1px solid; text-align: justify;">
                                <br/><br/>:<br/><br/>:<br/><br/>:<br/><br/>:<br/><br/>
                        </td>
                        <td style="border: 1px solid; text-align: justify;"><br/><br/>
                                Rs. <?php echo $file_data->existing_gross_capital ?><br/><br/>
                                Rs. <?php echo $file_data->gross_capital ?><br/><br/>
                                Rs. <?php echo $file_data->total_gross_capital ?><br/><br/>
                                <?php echo $file_data->investment_increase_perc ?>% (should be 25% or more as mentioned in the condition no. 4.6 and 4.7 of GR)
                        </td>
                </tr>
                
                <tr>
                        <td style="border: 1px solid;">2.</td>
                        <td style="border: 1px solid;">
                                <strong><u>For Expansion or Forward / Backward Integration</u></strong>
                                <ul>
                                        <li>Existing Installed Capacity of the product before the initiation of Expansion or Forward/Backward Integration</li>
                                        <li>Proposed Installed Capacity of the product during Expansion or Forward/Backward Integration period</li>
                                        <li>Increased in percentage of the Proposed Installed Capacity during Expansion or Forward/Backward Integration period</li>
                        </td>
                        <td style="border: 1px solid;">
                                <br/><br/>:<br/><br/>:<br/><br/>:<br/><br/>
                        </td>
                        <td style="border: 1px solid;">
                                Should be in the opted quantity<br/><br/>
                                <?php echo $file_data->existing_capacity ?>  MTR (Per Year)<br/><br/>
                                <?php echo $file_data->proposed_capacity ?> MTR (Per Year)<br/><br/>
                                <?php echo $file_data->proposed_capacity_increase_perc ?>% (should be 25% or more as mentioned in the condition no. 4.6 and 4.7 of GR)
                        </td>
                </tr>
                
                <tr>
                        <td style="border: 1px solid;">3.</td>
                        <td style="border: 1px solid;">
                                <strong><u>For Expansion or Forward/Backward Integration</u></strong>
                                <ul>
                                        <li>Existing Installed Capacity of the product before the initiation of Expansion or Forward/Backward Integration</li>
                                        <li>Production made in Immediately preceding two financial year from date of initiation of Expansion or Forward/Backward Integration</li>
                                        <li>Production made in Immediately preceding two financial year from date of initiation of Expansion or Forward/Backward Integration</li>
                                        <li>Maximum utilization of existing installed capacity reached in percentage in the immediately preceding two financial year from date of initiation of Expansion or Forward/Backward Integration</li>
                                </ul>
                        </td>
                        <td style="border: 1px solid;">
                                <br/><br/>:<br/><br/>:<br/><br/>:<br/><br/>:<br/><br/><br/>
                        </td>
                        <td style="border: 1px solid;">
                                Should be in the opted quantity<br/><br/>
                                <?php echo $file_data->existing_capacity_second ?> MTR<br/><br/>
                                <?php echo $file_data->two_years_production_capacity ?> MTR (MAR-2022)<br/><br/>
                                <?php echo $file_data->two_years_production_money ?> MTR (MAR-2021)<br/><br/>
                                <?php echo $file_data->max_utilization_perc ?>%(MAR-2022) (should be 75% or more as mentioned in the condition no. 4.6 and 4.7 of GR)
                        </td>
                </tr>
        </table>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                <ol>
                        <li>All the details provided in the certificate are as per the details given by the firm for the Plant and Machinery and other relevant documents.</li>
                        <li>This certificate issued base on the physical checking of the plant and machinery on the above mentioned date of visit in a working premises; if party change / alter / shift / sell out the machines after this date; we are not responsible for such kind of acts of the party.</li>
                        <li>This certificate issued without any prejudice and the best of my knowledge and experience.</li>
                </ol>
        </p>


        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                <strong>Note:</strong> This certificate is issued on the request of the party <strong>M/s <u><?php echo $post_fields->Firm_Name ?></u></strong> and on the basis of documents and information provided by them.
        </p>
        
</body>
</html>