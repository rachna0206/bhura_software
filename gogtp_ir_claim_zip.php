<?php

include "db_connect.php";
$obj=new DB_connect();

$inq_id = $_REQUEST['inq_id'];
$service_id = $_REQUEST['service_id'];
$stage_id = $_REQUEST['stage_id'];

$stmt_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` where id=?");
$stmt_list->bind_param("i",$inq_id);
$stmt_list->execute();
$result = $stmt_list->get_result()->fetch_assoc();
$stmt_list->close();

$row_data=json_decode($result["raw_data"]);
$post_fields=$row_data->post_fields;


// Create a temporary directory
$tempDir = sys_get_temp_dir() . '/export_' . uniqid();
mkdir($tempDir);

/*(employment_data_gogtp.php)
(annexure2_gogtp.php)
(epf_gogtp.php)
(forwarding_claim_gogtp.php)*/


// File 1
$file1Content = "<!doctype html>
<!DOCTYPE html>
<html>
<head>
        <meta charset='utf-8'>
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
        <h1 style='font-family: calibri;text-align: center;font-size: 22px;'><u>Employment Data (Annexure – I)</u></h1>

        <table style='font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;'>
                
                <tr>
                        <th style='border: 1px solid;'>Sr. No.</th>
                        <th style='border: 1px solid;'>Name & Address of Employee</th>
                        <th style='border: 1px solid;'>Category of Employee (Supervisory/Skilled/ Technical/other)</th>
                        <th style='border: 1px solid;'>Working Department & Shift details</th>
                        <th style='border: 1px solid;'>EPF No. & Date</th>
                        <th style='border: 1px solid;'>Pay roll wages P.M.</th>
                        <th style='border: 1px solid;'>Male/Female</th>
                        <th style='border: 1px solid;'>Since how many years living in Gujarat</th>
                        <th style='border: 1px solid;'>Date of joining for current position</th>
                </tr>

                <tr>
                        <td style='border: 1px solid;'>1</td>
                        <td style='border: 1px solid;'>2</td>
                        <td style='border: 1px solid;'>3</td>
                        <td style='border: 1px solid;'>4</td>
                        <td style='border: 1px solid;'>5</td>
                        <td style='border: 1px solid;'>6</td>
                        <td style='border: 1px solid;'>7</td>
                        <td style='border: 1px solid;'>8</td>
                        <td style='border: 1px solid;'>9</td>
                </tr>

                <tr>
                        <td style='border: 1px solid;'>1</td>
                        <td style='border: 1px solid;'>KATARIYA MAHESH MAGANBHAI<br/><br/>ADDRESS: <br/>158, NAVO HALPATI WAS, NEAR KHATRI TEMPLE, DINDOLI,SURAT CITY,SURAT-394210</td>
                        <td style='border: 1px solid;'>Supervisor</td>
                        <td style='border: 1px solid;'>----</td>
                        <td style='border: 1px solid;'>----</td>
                        <td style='border: 1px solid;'>----</td>
                        <td style='border: 1px solid;'>Male</td>
                        <td style='border: 1px solid;'>11</td>
                        <td style='border: 1px solid;'>11/09/2022</td>
                </tr>

        </table><br/>
        
        
</body>
</html>";

$file1Path = $tempDir . '/EmploymentData.docx';
file_put_contents($file1Path, $file1Content);


// File 2
$file2Content = "<!doctype html>
<!DOCTYPE html>
<html>
<head>
        <meta charset='utf-8'>
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
        <p style='font-family: calibri;text-align: left;font-size: 16px;'>
                NAME OF BANK:- <strong><u>CANARA BANK</u></strong><br/>
                FULL ADDRESS:- <strong><u>RATAN KUTIR BUILDING, SALABATPURA, MAIN ROAD, SURAT-395003</u></strong><br/>
                IFSC NO.: <strong><u>CNRB0017170</u></strong><br/>
                ACCOUNT NUMBER OF ENTERPRISE <strong><u>120000271678</u></strong> FOR RTGS. <br/>
        </p>

        <h1 style='font-family: calibri;text-align: center;font-size: 22px;'>ANNEXURE-” II”</h1>

        <h2 style='font-family: calibri;text-align: center;font-size: 22px;'>CERTIFICATE FROM FINANCIAL INSTITUTION/BANK</h2>

        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>
                &emsp;&emsp;&emsp;&emsp;This is to certify that <strong><u>".$post_fields->Firm_Name."</u></strong> has been sanctioned <strong><u>Rs. 3,50,00,000.00/-</u></strong> term loan on new plant & machinery for the project AT <strong><u>".$post_fields->Factory_Address."</u></strong> At the rate of <strong><u>8.25%</u></strong> interest for the project. The first disbursement date is <strong><u>18/02/2022.</u></strong>
        </p>

        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>
                &emsp;&emsp;&emsp;&emsp;The Unit has been disbursed a total of <strong><u>Rs. 3,42,76,437.00/-</u> UP TO <u>26/09/2022.</u></strong> The first installment of the term loan of <strong><u>Rs.17,60,542.00/-</u></strong> was disbursed on date <strong><u>18/02/2022</u></strong> and their term loan account number is <strong><u>170003825630.</u></strong>  <br/><br/>
                The unit had made repayment for a period date – <strong><u>18/02/2022 TO 31/12/2022 ?></u></strong> as under. <br/><br/>
        </p>

        <p style='font-family: calibri;text-align: left;font-size: 16px;'>
                Against term loan &emsp;&emsp;&emsp;&emsp; <strong><u>Rs.  11,03,165.15/- </u></strong><br/>
                As interest &emsp;&emsp;&emsp;&emsp; <strong><u>Rs.  24,26,707.35/-</u></strong><br/>
                Total &emsp;&emsp;&emsp;&emsp; <strong><u>Rs.  35,29,872.50/-</u></strong><br/><br/>
        </p>


        <p style='font-family: calibri;text-align: left;font-size: 16px;'>
                The interest subsidy @ 6 on the amount disbursed for the period from: <strong><u>18/02/2022 TO 31/12/2022</u></strong> is <strong><u>RS. 15,97,477.00/-</u></strong><br/><br/>

                1) This is certifying that penal interest or other charges are not included in the said claim and the enterprise pays regular installments and interest to the bank.<br/>
                2) The enterprise is not a defaulter of the bank. (ii the case of a defaulter, please give defaulter period details) <br/>
        </p>


<p style='font-family: calibri;text-align: left;font-size: 16px;'>
Place: SURAT  &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;  BANK MANAGER SIGN & STAMP<br/>
Date:                                                                             

        </p>

</body>
</html>";

$file2Path = $tempDir . '/Annexure-II.docx';
file_put_contents($file2Path, $file2Content);


// File 3
$file3Content = "<!doctype html>
<!DOCTYPE html>
<html>
<head>
        <meta charset='utf-8'>
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
        <p style='font-family: calibri;text-align: left;font-size: 24px;'>
                <strong>પ્રતિ, <br/>
                જનરલ મેનેજરશ્રી,<br/>
                જિલ્લા ઉધોગ કેન્દ્ર, સુરત<br/></strong>
        </p>

        <p style='font-family: calibri;text-align: justify;font-size: 24px;'>વિષય:      Assistance to Strengthen Specific Sector in The Textile Value Chain-2019 અંતર્ગત ઇ.પી.એફ. રજીસ્ટરની નકલ ના આપવા બાબત.</p>

        <p style='font-family: calibri;text-align: justify;font-size: 24px;'>સંદર્ભ:    એકમનું નામ :- <strong>".$post_fields->Firm_Name."</strong></p>

        <p style='font-family: calibri;text-align: justify;font-size: 24px;'>એકમનુ સરનામું:- <strong>".$post_fields->Factory_Address."</strong></p>

        <p style='font-family: calibri;text-align: justify;font-size: 24px;'>&emsp;&emsp;&emsp;&emsp;&emsp;ઉપરોક્ત વિષય અન્વયે જણાવવાનું કે અમારા એકમમાં ૨૦ કરતા ઓછા સુપરવાઇઝર/ કારીગર હોય, પી.એફ. ઓફીસના નિયમ મુજબ અમારે ઇ.પી.એફ. રજિસ્ટર નિભાવવાનું રહેતુ નથી. જે ધ્યાને લઇ અમારી અરજીની આગળની કાર્યવાહી કરવા વિનંતી છે. </p>


        <p style='font-family: calibri;text-align: right;font-size: 24px;'><strong> આપનો વિશ્વાસુ</strong></p>
        
        
</body>
</html>";

$file3Path = $tempDir . '/EPF.docx';
file_put_contents($file3Path, $file3Content);



// File 4
$file4Content = "<!doctype html>
<!DOCTYPE html>
<html>
<head>
        <meta charset='utf-8'>
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
        <p style='font-family: calibri;text-align: right;font-size: 16px;'>Date:04/05/2023</p>

        <p style='font-family: calibri;text-align: left;font-size: 16px;'>
                To,<br/>
                The General Manager,<br/>
                District Industries Centre,<br/>
                C-Block, 2ND Floor,<br/>
                Nanpura, Surat.
        </p>

        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>
                <strong><u>Sub: - 1TH & 4TH Quarterly Application for interest subsidy & power tariff under GOGTP- 2019.</u></strong>
        </p>

        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>Respected Sir/Madam,</p>

        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>
                <strong>
                        Application no.:- 2431243<br/>
                        Quarter Period: - 14/04/2022 TO 31/03/2023<br/>
                        Loan amount: - 3,50,00,000.00
                </strong>
        </p>

        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>&emsp;&emsp;&emsp;&emsp;&emsp;
                With reference to the above subject & details, herewith we are submitting a claim for interest reimbursement & power tariff subsidy under <strong>(GOGTP-2019)</strong>. Kindly find the necessary document for your ready reference and do the needful at your end as soon as possible.
        </p>

        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>&emsp;&emsp;&emsp;&emsp;&emsp;
                Kindly let us know if any information or document is required from our side.
        </p>

        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>Thanking You,</p>

        <p style='font-family: calibri;text-align: justify;font-size: 16px;'><strong><u>Enclosed document: -</u></strong></p>

        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>
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
</html>";

$file4Path = $tempDir . '/ForwardingClaim.docx';
file_put_contents($file4Path, $file4Content);


// Create the zip archive
$zipPath = sys_get_temp_dir() . '/export.zip';
$zip = new ZipArchive();
$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addFile($file1Path, 'EmploymentData.doc');
$zip->addFile($file2Path, 'Annexure-II.doc');
$zip->addFile($file3Path, 'EPF.doc');
$zip->addFile($file4Path, 'ForwardingClaim.doc');
$zip->close();

// Set the appropriate headers for the download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="export.zip"');
header('Content-Length: ' . filesize($zipPath));

// Send the zip file to the user
readfile($zipPath);

// Clean up - remove temporary directory and zip file
unlink($file1Path);
unlink($file2Path);
unlink($file3Path);
unlink($file4Path);
rmdir($tempDir);
unlink($zipPath);
?>
