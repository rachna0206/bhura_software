<?php 

        // propritor/partner , constitution

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
        header("Content-Disposition: attachment;Filename=2AffidavitScheme.doc");  
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
        <title>Affidavit</title>
</head>
<body>
        
        <p style="font-family: calibri;text-align: center;font-size: 22px;">
                <strong>AFFIDAVIT FOR TEXTILE / TECHNICAL TEXTILE UNIT</strong>
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                I\We  <strong><u>MANJIT T JAJOO</u></strong>  <s>Proprietor</s> /<strong>Partner(s)</strong> <s>/ Authorize Person / Managing Director(s)</s> of <strong><u><?php echo $post_fields->Firm_Name ?></u></strong> Address  <strong><u><?php echo $post_fields->Factory_Address ?></u></strong>  hereby solemnly affirmed and declared on oath that our unit is situated at above mentioned address.
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                I/We undertake to comply with the terms, conditions, particulars and parameters of the Resolution No. TEX/102018/3327/CH, dated 10.01.2019 and Amendment there to.
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 22px;">
                I/We availing benefit under the scheme will observe the following conditions. If I/We violate any of these conditions, it shall be liable to pay back the assistance received under the scheme failing which the same will be recovered as arrears of land revenue.
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;">
                <ol type="A">
                        <li>The unit will observe pollution control measures as prescribed by GPCB or other competent authority.</li>
                        <li>The unit will remain in production for at least 10 years from the commencement of commercial production.</li>
                        <li>I/We will furnish the information regarding the production, employment, etc. year wise or whenever asked by the Government.</li>
                        <li>I/We am/are not receiving any subsidy from any other scheme of State Government or from any other State Government agency for the same purpose.</li>
                        <li>I/We am/are not getting any benefits under GBIFR scheme.</li>
                        <li>No Government dues are outstanding and No Court case is pending against State Government and/or State Government Corporations against company.</li>
                        <li>I/We am/are state that the machineries installed in factory premises are new.</li>
                        <li>I/We am/are to state that this is the first application under the Scheme for assistance to Strengthen Specific Sectors in the Textile Value Chain - 2019 during operative period of the scheme.</li>
                        <li>Hereby solemnly affirmed declare that the unit employees local persons to the extent of 85% of all employees and 60% of managerial and supervisory staff as per the employment policy of Government of Gujarat. The details of the direct employment, only the employees registered under Employees' Provident Fund scheme thereof are as under:-</li>
                </ol>
        </p>

        <table align="center" style="font-family: calibri;text-align: center;font-size: 16px;  border-collapse: collapse; border: 1px solid; width: 50%;">
                <tr>
                        <th style="border: 1px solid;">Sr. No.</th>
                        <th style="border: 1px solid;">Particulars</th>
                        <th style="border: 1px solid;" colspan="2">Local Employee</th>
                        <th style="border: 1px solid;" colspan="2">Outside of Gujarat</th>
                        <th style="border: 1px solid;" colspan="2">Total</th>
                        <th style="border: 1px solid;">Total</th>
                        <th style="border: 1px solid;">% age of Local Employee</th>
                </tr>
                        
                <tr>
                        <th style="border: 1px solid;"></th>
                        <th style="border: 1px solid;"></th>
                        <th style="border: 1px solid;">Male</th>
                        <th style="border: 1px solid;">Female</th>
                        <th style="border: 1px solid;">Male</th>
                        <th style="border: 1px solid;">Female</th>
                        <th style="border: 1px solid;">Male</th>
                        <th style="border: 1px solid;">Female</th>
                        <th style="border: 1px solid;"></th>
                        <th style="border: 1px solid;"></th>
                </tr>

                <tr>
                        <td style="border: 1px solid;"><strong>1</strong></td>
                        <td style="border: 1px solid;">Manager/Supervisor</td>
                        <td style="border: 1px solid;"><?php echo $file_data->mana_local_male ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->mana_local_female ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->mana_outside_male ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->mana_outside_female ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_male_manager ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_female_manager ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_manager ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->mana_percenatge,2) ?>%</td>
                </tr>

                <tr>
                        <td style="border: 1px solid;"><strong>2</strong></td>
                        <td style="border: 1px solid;">Workers</td>
                        <td style="border: 1px solid;"><?php echo $file_data->worker_local_male ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->worker_local_female ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->worker_outside_male ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->worker_outside_female ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_male_worker ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_female_worker ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_worker ?></td>
                        <td style="border: 1px solid;"><?php echo number_format($file_data->worker_percenatge,2) ?>%</td>
                </tr>

                <tr>
                        <td style="border: 1px solid;" colspan="2"><strong>Total</strong></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_local_male ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_local_female ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_outside_male ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_outside_female ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_male ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->total_female ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data->final_total ?></td>
                        <td style="border: 1px solid;"></td>
                </tr>
        </table><br/>

        <p style="font-family: calibri;text-align: right;font-size: 22px;">
                (Name/Designation/Sign)<br/> Unit Stamp
        </p>
        
        
</body>
</html>