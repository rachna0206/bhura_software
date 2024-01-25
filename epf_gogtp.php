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
        header("Content-Disposition: attachment;Filename=EPF.doc");
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
        <p style="font-family: calibri;text-align: left;font-size: 24px;">
                <strong>પ્રતિ, <br/>
                જનરલ મેનેજરશ્રી,<br/>
                જિલ્લા ઉધોગ કેન્દ્ર, સુરત<br/></strong>
        </p>

        <p style="font-family: calibri;text-align: justify;font-size: 24px;">વિષય:      Assistance to Strengthen Specific Sector in The Textile Value Chain-2019 અંતર્ગત ઇ.પી.એફ. રજીસ્ટરની નકલ ના આપવા બાબત.</p>

        <p style="font-family: calibri;text-align: justify;font-size: 24px;">સંદર્ભ:    એકમનું નામ :- <strong><?php echo $post_fields->Firm_Name ?></strong></p>

        <p style="font-family: calibri;text-align: justify;font-size: 24px;">એકમનુ સરનામું:- <strong><?php echo $post_fields->Factory_Address ?></strong></p>

        <p style="font-family: calibri;text-align: justify;font-size: 24px;">&emsp;&emsp;&emsp;&emsp;&emsp;ઉપરોક્ત વિષય અન્વયે જણાવવાનું કે અમારા એકમમાં ૨૦ કરતા ઓછા સુપરવાઇઝર/ કારીગર હોય, પી.એફ. ઓફીસના નિયમ મુજબ અમારે ઇ.પી.એફ. રજિસ્ટર નિભાવવાનું રહેતુ નથી. જે ધ્યાને લઇ અમારી અરજીની આગળની કાર્યવાહી કરવા વિનંતી છે. </p>


        <p style="font-family: calibri;text-align: right;font-size: 24px;"><strong> આપનો વિશ્વાસુ</strong></p>
        
        
</body>
</html>