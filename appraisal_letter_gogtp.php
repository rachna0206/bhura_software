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

        header("Content-type: application/vnd.ms-word");  
        header("Content-Disposition: attachment;Filename=AppraisalLetter.doc");  
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
        <p style="font-family: calibri;text-align: left;font-size: 16px;">
                DIC/SRT/Textile Policy-2019<br/>
                District Industries Center,<br/>
                C- Block, 2nd Floor,<br/>
                Bahumali Bhavan,<br/>
                Nanpura, Surat<br/>
        </p>

        <h1 style="font-family: calibri;text-align: center;font-size: 22px;">SUB: Regarding Bank Appraisal</h1>
        
        <p style="font-family: calibri;text-align: justify;font-size: 16px;">Respected Sir,</p>

        <p style="font-family: calibri;text-align: justify;font-size: 16px;">
                This is to inform you that our firm (<strong><?php echo $post_fields->Firm_Name ?></strong>) have not term loan above 5.00 cr. Therefore we don’t have a Bank Appraisal Report.
        </p><br/><br/>

        <p style="font-family: calibri;text-align: right;font-size: 16px;">Thanks and Regards</p>
        
        
</body>
</html>