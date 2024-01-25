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
        $count = count($file_data);

        header("Content-type: application/vnd.ms-word");  
        header("Content-Disposition: attachment;Filename=EmploymentData.doc");  
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
        <h1 style="font-family: calibri;text-align: center;font-size: 22px;"><u>Employment Data (Annexure – I)</u></h1>

        <table align="center" style="font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;">
                
                <tr>
                        <th style="border: 1px solid;">Sr. No.</th>
                        <th style="border: 1px solid;">Name & Address of Employee</th>
                        <th style="border: 1px solid;">Category of Employee (Supervisory/Skilled/ Technical/other)</th>
                        <th style="border: 1px solid;">Working Department & Shift details</th>
                        <th style="border: 1px solid;">EPF No. & Date</th>
                        <th style="border: 1px solid;">Pay roll wages P.M.</th>
                        <th style="border: 1px solid;">Male/Female</th>
                        <th style="border: 1px solid;">Since how many years living in Gujarat</th>
                        <th style="border: 1px solid;">Date of joining for current position</th>
                </tr>

                <tr>
                        <td style="border: 1px solid;">1</td>
                        <td style="border: 1px solid;">2</td>
                        <td style="border: 1px solid;">3</td>
                        <td style="border: 1px solid;">4</td>
                        <td style="border: 1px solid;">5</td>
                        <td style="border: 1px solid;">6</td>
                        <td style="border: 1px solid;">7</td>
                        <td style="border: 1px solid;">8</td>
                        <td style="border: 1px solid;">9</td>
                </tr>

        <?php for($i=0;$i<$count;$i++){ ?>
                <tr>
                        <td style="border: 1px solid;"><?php echo ($i+1) ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data[$i]->ename ?><br/><br/>ADDRESS: <br/><?php echo $file_data[$i]->address ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data[$i]->designation ?></td>
                        <td style="border: 1px solid;">----</td>
                        <td style="border: 1px solid;">----</td>
                        <td style="border: 1px solid;">----</td>
                        <td style="border: 1px solid;"><?php echo $file_data[$i]->gender ?></td>
                        <td style="border: 1px solid;"><?php echo $file_data[$i]->stay ?></td>
                        <td style="border: 1px solid;">11/09/2022</td>
                </tr>
        <?php } ?>

        </table><br/>
        
        
</body>
</html>