<?php

/*function arya_docx_test($inq_id, $service_id, $stage_id, $file_id, $page_name){
	
}

function downoad_zip($inq_id, $service_id, $stage_id, $file_id)
{
	// Create a temporary directory
	$tempDir = sys_get_temp_dir() . '/export_' . uniqid();
	mkdir($tempDir);

	$file1Content = affidavit2_gogtp($inq_id, $service_id, $stage_id, $file_id);
	$file1Path = $tempDir . '/2AffidavitScheme.docx';
	file_put_contents($file1Path, $file1Content);

	$file2Content = ca_certificate_newfirm_gogtp($inq_id, $service_id, $stage_id, $file_id);
	$file2Path = $tempDir . '/CACertificate-NewFirm.docx';
	file_put_contents($file2Path, $file2Content);

	$file3Content = ca_certificate_expansion_gogtp($inq_id, $service_id, $stage_id, $file_id);
	$file3Path = $tempDir . '/CACertificate-Expansion.docx';
	file_put_contents($file3Path, $file3Content);

	$file4Content = ce_certificate_gogtp($inq_id, $service_id, $stage_id, $file_id);
	$file4Path = $tempDir . '/CECertificate.docx';
	file_put_contents($file4Path, $file4Content);

	$file5Content = certificate_first_disbursement_gogtp($inq_id, $service_id, $stage_id, $file_id);
	$file5Path = $tempDir . '/CertificateRegardingFirstDisbursement.docx';
	file_put_contents($file5Path, $file5Content);

	$file6Content = epf_gogtp($inq_id, $service_id, $stage_id, $file_id);
	$file6Path = $tempDir . '/EPF.docx';
	file_put_contents($file6Path, $file6Content);

	// Create the zip archive
	$zipPath = sys_get_temp_dir() . '/export.zip';
	$zip = new ZipArchive();
	$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
	$zip->addFile($file1Path, '2AffidavitScheme.doc');
	$zip->addFile($file2Path, 'CACertificate-NewFirm.doc');
	$zip->addFile($file3Path, 'CACertificate-Expansion.doc');
	$zip->addFile($file4Path, 'CECertificate.doc');
	$zip->addFile($file5Path, 'CertificateRegardingFirstDisbursement.doc');
	$zip->addFile($file6Path, 'EPF.doc');
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
}
*/
function affidavit2_gogtp($inq_id, $service_id, $stage_id, $file_id)
{
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

    $f = "<!doctype html>
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
	        <title>Affidavit</title>
	</head>
	<body>
	        
	        <p style='font-family: calibri;text-align: center;font-size: 22px;'>
	                <strong>AFFIDAVIT FOR TEXTILE / TECHNICAL TEXTILE UNIT</strong>
	        </p>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                I\We  <strong><u>MANJIT T JAJOO</u></strong>  <s>Proprietor</s> /<strong>Partner(s)</strong> <s>/ Authorize Person / Managing Director(s)</s> of <strong><u>".$post_fields->Firm_Name."</u></strong> Address  <strong><u>".$post_fields->Factory_Address."</u></strong>  hereby solemnly affirmed and declared on oath that our unit is situated at above mentioned address.
	        </p>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                I/We undertake to comply with the terms, conditions, particulars and parameters of the Resolution No. TEX/102018/3327/CH, dated 10.01.2019 and Amendment there to.
	        </p>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                I/We availing benefit under the scheme will observe the following conditions. If I/We violate any of these conditions, it shall be liable to pay back the assistance received under the scheme failing which the same will be recovered as arrears of land revenue.
	        </p>

	        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>
	                <ol type='A'>
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

	        <table style='font-family: calibri;text-align: center;font-size: 22px; border-collapse: collapse; border: 1px solid; width: 50%;'>
	                <tr>
	                        <th style='border: 1px solid;'>Sr. No.</th>
	                        <th style='border: 1px solid;'>Particulars</th>
	                        <th style='border: 1px solid;' colspan='2'>Local Employee</th>
	                        <th style='border: 1px solid;' colspan='2'>Outside of Gujarat</th>
	                        <th style='border: 1px solid;' colspan='2'>Total</th>
	                        <th style='border: 1px solid;'>Total</th>
	                        <th style='border: 1px solid;'>% age of Local Employee</th>
	                </tr>
	                        
	                <tr>
	                        <th style='border: 1px solid;'></th>
	                        <th style='border: 1px solid;'></th>
	                        <th style='border: 1px solid;'>Male</th>
	                        <th style='border: 1px solid;'>Female</th>
	                        <th style='border: 1px solid;'>Male</th>
	                        <th style='border: 1px solid;'>Female</th>
	                        <th style='border: 1px solid;'>Male</th>
	                        <th style='border: 1px solid;'>Female</th>
	                        <th style='border: 1px solid;'></th>
	                        <th style='border: 1px solid;'></th>
	                </tr>

	                <tr>
	                        <td style='border: 1px solid;'><strong>1</strong></td>
	                        <td style='border: 1px solid;'>Manager/Supervisor</td>
	                        <td style='border: 1px solid;'>".$file_data->mana_local_male."</td>
	                        <td style='border: 1px solid;'>".$file_data->mana_local_female."</td>
	                        <td style='border: 1px solid;'>".$file_data->mana_outside_male."</td>
	                        <td style='border: 1px solid;'>".$file_data->mana_outside_female."</td>
	                        <td style='border: 1px solid;'>".$file_data->total_male_manager."</td>
	                        <td style='border: 1px solid;'>".$file_data->total_female_manager."</td>
	                        <td style='border: 1px solid;'>".$file_data->total_manager."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->mana_percenatge,2)."%</td>
	                </tr>

	                <tr>
	                        <td style='border: 1px solid;'><strong>2</strong></td>
	                        <td style='border: 1px solid;'>Workers</td>
	                        <td style='border: 1px solid;'>".$file_data->worker_local_male."</td>
	                        <td style='border: 1px solid;'>".$file_data->worker_local_female."</td>
	                        <td style='border: 1px solid;'>".$file_data->worker_outside_male."</td>
	                        <td style='border: 1px solid;'>".$file_data->worker_outside_female."</td>
	                        <td style='border: 1px solid;'>".$file_data->total_male_worker."</td>
	                        <td style='border: 1px solid;'>".$file_data->total_female_worker."</td>
	                        <td style='border: 1px solid;'>".$file_data->total_worker."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->worker_percenatge,2)."%</td>
	                </tr>

	                <tr>
	                        <td style='border: 1px solid;' colspan='2'><strong>Total</strong></td>
	                        <td style='border: 1px solid;'>".$file_data->total_local_male."</td>
	                        <td style='border: 1px solid;'>".$file_data->total_local_female."</td>
	                        <td style='border: 1px solid;'>".$file_data->total_outside_male."</td>
	                        <td style='border: 1px solid;'>".$file_data->total_outside_female."</td>
	                        <td style='border: 1px solid;'>".$file_data->total_male."</td>
	                        <td style='border: 1px solid;'>".$file_data->total_female."</td>
	                        <td style='border: 1px solid;'>".$file_data->final_total."</td>
	                        <td style='border: 1px solid;'></td>
	                </tr>
	        </table><br/>

	        <p style='font-family: calibri;text-align: right;font-size: 22px;'>
	                (Name/Designation/Sign)<br/> Unit Stamp
	        </p>
	        
	        
	</body>
	</html>";

	return $f;
}

function ca_certificate_newfirm_gogtp($inq_id, $service_id, $stage_id, $file_id)
{
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

    $f = "<!doctype html>
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
	        <title>CA CERTIFICATE NEW FIRM</title>
	</head>
	<body>
	        
	        <h1 style='font-family: calibri;text-align: center;font-size: 24px;'><u>CA Certificate</u></h1>
	        
	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                We hereby certify that M/s. <strong><u>".$post_fields->Firm_Name."</u></strong> has acquired following fixed assets up to <strong><u>".date_format(new DateTime($file_data->acquired_assets_dt),'d/m/Y')."</u></strong> at their factory address <strong><u>".$post_fields->Factory_Address."</u></strong>. for <strong><u>MANUFACTURE OF ".$file_data->manufacturing_prod."</u></strong> and have commenced the commercial production on  <strong><u>".date_format(new DateTime($file_data->commercial_date),'d/m/Y')."</u></strong> and raised the first invoice on <strong><u>".date_format(new DateTime($file_data->first_invoice_date),'d/m/Y')."</u></strong> bearing on 1 dated <strong><u>".date_format(new DateTime($file_data->first_invoice_date),'d/m/Y')."</u></strong> for   a    total    invoice    value    of Rs. <strong><u>".number_format($file_data->invoice_value,2)."</u></strong>.
	        </p>
	        
	        <table style='font-family: calibri;text-align: center;font-size: 22px; border-collapse: collapse; border: 1px solid; width: 50%;'>
	                <tr>
	                        <th style='border: 1px solid;'>Sr. No.</th>
	                        <th style='border: 1px solid;'>Description of Assets</th>
	                        <th style='border: 1px solid;'>Gross Fixed Capital Assets</th>
	                </tr>

	                <tr>
	                        <td style='border: 1px solid;'>1<strong></strong></td>
	                        <td style='border: 1px solid;'>Land</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->land,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>2</strong></td>
	                        <td style='border: 1px solid;'>Building & Shed</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->building_shed,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>3</strong></td>
	                        <td style='border: 1px solid;'>Plant & M/C</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->plant_mc,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>4</strong></td>
	                        <td style='border: 1px solid;'>Electrification</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->electrification,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>5</strong></td>
	                        <td style='border: 1px solid;'>Tools and Equipment</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->tools_equipment,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>6</strong></td>
	                        <td style='border: 1px solid;'>Accessories</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->accessories,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>7</strong></td>
	                        <td style='border: 1px solid;'>Utilities and Effluent Treatment plant</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->utilities,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>8</strong></td>
	                        <td style='border: 1px solid;'>Other investments</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->investments,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;' colspan='2'><strong>Total</strong></td>
	                        <td style='border: 1px solid;'>".number_format($file_data->total_gross_capital,2)."</td>
	                </tr>
	        </table><br/><br/>

	        <table style='font-family: calibri;text-align: center;font-size: 22px; border-collapse: collapse; border: 1px solid; width: 50%;'>
	                <tr>
	                        <th colspan='3'>Details of Means of Finance</th>
	                </tr>

	                <tr>
	                        <th style='border: 1px solid;'>Sr. No.</th>
	                        <th style='border: 1px solid;'>Particulars</th>
	                        <th style='border: 1px solid;'>Total Amount</th>
	                </tr>

	                <tr>
	                        <td style='border: 1px solid;'><strong>1</strong></td>
	                        <td style='border: 1px solid;'>a. Equity Share Capital</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->capital,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>2</strong></td>
	                        <td style='border: 1px solid;'>b. Equity Share Premium</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->premium,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>3</strong></td>
	                        <td style='border: 1px solid;'>c. Bank Term Loan</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->term_loan,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>4</strong></td>
	                        <td style='border: 1px solid;'>d. Working Capital Loan</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->capital_loan,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>5</strong></td>
	                        <td style='border: 1px solid;'>e. Internal Source of Fund</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->internal_source,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'><strong>6</strong></td>
	                        <td style='border: 1px solid;'>f. Others</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->others,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;' colspan='2'><strong>Total</strong></td>
	                        <td style='border: 1px solid;'>".number_format($file_data->total_amount_finance,2)."</td>
	                </tr>
	        </table>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                We have checked the books of accounts of the Enterprise and Invoice etc. and certify that the aforesaid information is verified to be true. We also certify that all the aforesaid items have been duly paid and no credits is raised there against in the books of the Enterprise, except those stated above.
	        </p>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                Date : &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;For
	        </p>
	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                Place : &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Signature,
	        </p>
	        <p style='font-family: calibri;text-align: right;font-size: 22px;'>Membership No</p>
	        <p style='font-family: calibri;text-align: right;font-size: 22px;'>UDIN No</p>
	        
	</body>
	</html>";

	return $f;
}

function ca_certificate_expansion_gogtp($inq_id, $service_id, $stage_id, $file_id)
{
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

    $f = "<!doctype html>
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
	        <title>CA CERTIFICATE EXPANSION</title>
	</head>
	<body>
	        
	        <h1 style='font-family: calibri;text-align: center;font-size: 24px;'><u>CHARTERED ENGINEER’S CERTIFICATE</u></h1>
	        
	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'><strong>(For Expansion / Diversification / Forward Integration/ Backward Integration)</strong></p>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                This is to certify that M/s. <strong><u>".$post_fields->Firm_Name."</u></strong> located at <strong><u>".$post_fields->Factory_Address."</u></strong> for the manufacturing of <strong><u>".$post_fields->Segment."</u></strong> product/s. have acquired following fixed assets.
	        </p>
	        
	        <table style='font-family: calibri;text-align: center;font-size: 22px; border-collapse: collapse; border: 1px solid; width: 50%;'>
	                <tr>
	                        <th style='border: 1px solid;' rowspan='2'>No</th>
	                        <th style='border: 1px solid;' rowspan='2'>Break- up Fixed assets</th>
	                        <th style='border: 1px solid;'>Gross Fixed Capital Investment for Expansion/ Diversification/Forward Integration/Backward Integration as on the date of initiating expansion/ the date of initiating expansion Diversification/Forward Integration/Backward Integration</th>
	                        <th style='border: 1px solid;'>Gross Fixed Capital Investment for Expansion/ Diversification / Forward Integration/Backward Integration from the date of initiating expansion up to the date of commencing production/till Complete Project during the Diversification / Forward Integration/Backward Integration</th>
	                        <th style='border: 1px solid;' rowspan='2'>Total Investments as on date 14/08/2022</th>
	                </tr>
	                <tr>
	                        <th style='border: 1px solid;'>19/06/2022</th>
	                        <th style='border: 1px solid;'>20/06/2022 to 14/08/2022</th>
	                </tr>
	                        
	                <tr>
	                        <td style='border: 1px solid;'>1</td>
	                        <td style='border: 1px solid;'>Land</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->ini_investment_land,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->comm_investment_land,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->total_land,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'>2</td>
	                        <td style='border: 1px solid;'>Building</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->ini_investment_building,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->comm_investment_building,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->total_building,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'>3</td>
	                        <td style='border: 1px solid;'>Plant & Machinery</td>
	                        <td style='border: 1px solid;'><strong>".number_format($file_data->ini_investment_plant,2)."</strong></td>
	                        <td style='border: 1px solid;'><strong>".number_format($file_data->comm_investment_plant,2)."</strong></td>
	                        <td style='border: 1px solid;'><strong>".number_format($file_data->total_plant,2)."</strong></td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'>4</td>
	                        <td style='border: 1px solid;'>Utilities</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->ini_investment_utilities,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->comm_investment_utilities,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->total_utilities,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'>5</td>
	                        <td style='border: 1px solid;'>Tools & Equipment</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->ini_investment_tools,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->comm_investment_tools,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->total_tools,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'>6</td>
	                        <td style='border: 1px solid;'>Electrification</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->ini_investment_electric,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->comm_investment_electric,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->total_electric,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'>7</td>
	                        <td style='border: 1px solid;'>Other Assets (Required Manufacturing the end Product)</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->ini_investment_assets,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->comm_investment_assets,2)."</td>
	                        <td style='border: 1px solid;'>".number_format($file_data->total_assets,2)."</td>
	                </tr>
	                <tr>
	                        <td style='border: 1px solid;'></td>
	                        <td style='border: 1px solid;'><strong>Total</strong></td>
	                        <td style='border: 1px solid;'><strong>".number_format($file_data->total_ini_investment,2)."</strong></td>
	                        <td style='border: 1px solid;'><strong>".number_format($file_data->total_comm_investment,2)."</strong></td>
	                        <td style='border: 1px solid;'><strong>".number_format($file_data->final_total,2)."</strong></td>
	                </tr>
	                
	        </table>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                We have checked the book of accounts, invoices, balance sheet etc. of the enterprise and the information is verified and certified to be true. We certify that all the aforesaid items have been duly paid and no credit is raised.
	        </p>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                Date : &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;For
	        </p>
	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                Place : &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Signature,
	        </p>
	        <p style='font-family: calibri;text-align: right;font-size: 22px;'>Membership No</p>
	        <p style='font-family: calibri;text-align: right;font-size: 22px;'>UDIN No</p>
	        
	</body>
	</html>";

	return $f;
}

function ce_certificate_gogtp($inq_id, $service_id, $stage_id, $file_id)
{
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

    $f = "<!doctype html>
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
	        <title>CE CERTIFICATE</title>
	</head>
	<body>
	        
	        <h1 style='font-family: calibri;text-align: center;font-size: 24px;'><u>CHARTERED ENGINEER’S CERTIFICATE</u></h1>
	        
	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>This is to certify that, <strong><u>M/s. ".$post_fields->Firm_Name."</u></strong> is located at <strong><u>".$post_fields->Factory_Address."</u></strong> and engaged in <strong>".$post_fields->Segment."</strong>.</p>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>I have visited the manufacturing facility of the unit and hereby opined that the applicant <strong><u>M/s. '.$post_fields->Firm_Name.'</u></strong> fulfills all the provisions concerned to Expansion or Forward / Backward integration mentioned under the Government Resolution No. <u>TEX-102018-3327-CH</u>, dated <u>10/01/2019</u> for “Scheme for assistance to strengthen specific sector in the textile value chain”.</p>
	        
	        <table style='font-family: calibri;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 80%;'>
	                <tr>
	                        <td style='border: 1px solid; text-align: center;'>1.</td>
	                        <td style='border: 1px solid; text-align: justify;'>
	                                <strong><u>For Expansion or Forward / Backward Integration</u></strong>
	                                <ul>
	                                        <li>Existing Gross Fixed Capital Investment before the initiation of Expansion or Forward/Backward Integration</li>
	                                        <li>Gross Fixed Capital Investment installed during Expansion or Forward/Backward Integration period</li>
	                                        <li>Total Gross Fixed capital Investment after the completion of Expansion or Forward/Backward Integration period</li>
	                                        <li>Investment in Gross Fixed Capital Investment increased in percentage during Expansion or Forward/Backward Integration period</li>
	                                </ul>
	                        </td>
	                        <td style='border: 1px solid; text-align: justify;'>
	                                <br/><br/>:<br/><br/>:<br/><br/>:<br/><br/>:<br/><br/>
	                        </td>
	                        <td style='border: 1px solid; text-align: justify;'><br/><br/>
	                                Rs. ".$file_data->existing_gross_capital."<br/><br/>
	                                Rs. ".$file_data->gross_capital."<br/><br/>
	                                Rs. ".$file_data->total_gross_capital."<br/><br/>
	                                ".$file_data->investment_increase_perc."% (should be 25% or more as mentioned in the condition no. 4.6 and 4.7 of GR)
	                        </td>
	                </tr>
	                
	                <tr>
	                        <td style='border: 1px solid;'>2.</td>
	                        <td style='border: 1px solid;'>
	                                <strong><u>For Expansion or Forward / Backward Integration</u></strong>
	                                <ul>
	                                        <li>Existing Installed Capacity of the product before the initiation of Expansion or Forward/Backward Integration</li>
	                                        <li>Proposed Installed Capacity of the product during Expansion or Forward/Backward Integration period</li>
	                                        <li>Increased in percentage of the Proposed Installed Capacity during Expansion or Forward/Backward Integration period</li>
	                        </td>
	                        <td style='border: 1px solid;'>
	                                <br/><br/>:<br/><br/>:<br/><br/>:<br/><br/>
	                        </td>
	                        <td style='border: 1px solid;'>
	                                Should be in the opted quantity<br/><br/>
	                                ".$file_data->existing_capacity."  MTR (Per Year)<br/><br/>
	                                ".$file_data->proposed_capacity." MTR (Per Year)<br/><br/>
	                                ".$file_data->proposed_capacity_increase_perc."% (should be 25% or more as mentioned in the condition no. 4.6 and 4.7 of GR)
	                        </td>
	                </tr>
	                
	                <tr>
	                        <td style='border: 1px solid;'>3.</td>
	                        <td style='border: 1px solid;'>
	                                <strong><u>For Expansion or Forward/Backward Integration</u></strong>
	                                <ul>
	                                        <li>Existing Installed Capacity of the product before the initiation of Expansion or Forward/Backward Integration</li>
	                                        <li>Production made in Immediately preceding two financial year from date of initiation of Expansion or Forward/Backward Integration</li>
	                                        <li>Production made in Immediately preceding two financial year from date of initiation of Expansion or Forward/Backward Integration</li>
	                                        <li>Maximum utilization of existing installed capacity reached in percentage in the immediately preceding two financial year from date of initiation of Expansion or Forward/Backward Integration</li>
	                                </ul>
	                        </td>
	                        <td style='border: 1px solid;'>
	                                <br/><br/>:<br/><br/>:<br/><br/>:<br/><br/>:<br/><br/><br/>
	                        </td>
	                        <td style='border: 1px solid;'>
	                                Should be in the opted quantity<br/><br/>
	                                ".$file_data->existing_capacity_second." MTR<br/><br/>
	                                ".$file_data->two_years_production_capacity." MTR (MAR-2022)<br/><br/>
	                                ".$file_data->two_years_production_money." MTR (MAR-2021)<br/><br/>
	                                ".$file_data->max_utilization_perc."%(MAR-2022) (should be 75% or more as mentioned in the condition no. 4.6 and 4.7 of GR)
	                        </td>
	                </tr>
	        </table>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                <ol>
	                        <li>All the details provided in the certificate are as per the details given by the firm for the Plant and Machinery and other relevant documents.</li>
	                        <li>This certificate issued base on the physical checking of the plant and machinery on the above mentioned date of visit in a working premises; if party change / alter / shift / sell out the machines after this date; we are not responsible for such kind of acts of the party.</li>
	                        <li>This certificate issued without any prejudice and the best of my knowledge and experience.</li>
	                </ol>
	        </p>


	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                <strong>Note:</strong> This certificate is issued on the request of the party <strong>M/s <u>".$post_fields->Firm_Name."</u></strong> and on the basis of documents and information provided by them.
	        </p>
	        
	</body>
	</html>";

	return $f;
}

function certificate_first_disbursement_gogtp($inq_id, $service_id, $stage_id, $file_id)
{
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

    $f = "<!doctype html>
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
	        <h1 style='font-family: calibri;text-align: center;font-size: 24px;'><u>Certificate regarding the Disbursement of the term loan</u></h1>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                This is to certify that <strong><u>".$post_fields->Firm_Name."</u></strong> at <strong><u>".$post_fields->Factory_Address."</u></strong>. has availed various credit facilities including Term Loan for a project of <strong><u>".$file_data->project."</u></strong>. We Have Sanctioned term loan of <strong><u>Rs. ".number_format($file_data->sanctioned_term_loan,2)."</u></strong> for project on <strong><u>".date_format(new DateTime($file_data->project_date),"d/m/Y")."</u></strong>. In that concern, we have disbursed term loan of <strong><u>Rs. ".number_format($file_data->disbursed_term_loan,2)."</u></strong>. for its project as underneath table, which is duly verified by us and details are as given below.
	        </p>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                The detail of Term Loan is as under.
	                <ol>
	                        <li>Application received from unit on <strong><u>".date_format(new DateTime($file_data->application_received_dt),"d/m/Y").".</u></strong></li>
	                        <li>Term Loan Sanction <strong><u>dated  ".date_format(new DateTime($file_data->sanction_loan_dt),"d/m/Y").".</u></strong></li>
	                        <li>Disbursement was made on 1st Disbursement as on <strong><u>Dt. ".date_format(new DateTime($file_data->first_disbursement_dt),"d/m/Y")." of Rs. ".number_format($file_data->first_disbursement_price,2)."/-</u></strong></li>
	                        <li>Total Disbursement of <strong><u>Rs. ".number_format($file_data->total_disbursement_price,2)."/- up to Dt. ".date_format(new DateTime($file_data->total_disbursement_dt),"d/m/Y").".</u></strong></li>
	                        <li>Disbursement was made against following Fixed Assets:</li>
	        </p>

	        <table style='font-family: calibri;text-align: center;font-size: 22px; border-collapse: collapse; border: 1px solid; width: 50%;'>
	                <tr>
	                        <th style='border: 1px solid;'>Fixed Assets</th>
	                        <th style='border: 1px solid;'>Cost as per project report</th>
	                        <th style='border: 1px solid;'>Term loan sanctioned</th>
	                        <th style='border: 1px solid;'>Total Actual Investment against Term Loan</th>
	                        <th style='border: 1px solid;'>Total Disbursed Term Loan against actual investment.</th>
	                </tr>
	                        
	                <tr>
	                        <th style='border: 1px solid;'>a. Land</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->cost_land,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->sanctioned_term_land,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->total_investment_land,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->disbursed_term_land,2)."</th>
	                </tr>

	                <tr>
	                        <th style='border: 1px solid;'>b.  Building & Shed</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->cost_building,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->sanctioned_term_building,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->total_investment_building,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->disbursed_term_building,2)."</th>
	                </tr>

	                <tr>
	                        <th style='border: 1px solid;'>c.  Plant & M/C</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->cost_plant,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->sanctioned_term_plant,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->total_investment_plant,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->disbursed_term_plant,2)."</th>
	                </tr>

	                <tr>
	                        <th style='border: 1px solid;'>d.  Electrification</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->cost_electric,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->sanctioned_term_electric,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->total_investment_electric,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->disbursed_term_electric,2)."</th>
	                </tr>

	                <tr>
	                        <th style='border: 1px solid;'>e. Tools and Equipment</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->cost_tools,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->sanctioned_term_tools,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->total_investment_tools,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->disbursed_term_tools,2)."</th>
	                </tr>

	                <tr>
	                        <th style='border: 1px solid;'>f. Accessories</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->cost_accessories,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->sanctioned_term_accessories,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->total_investment_accessories,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->disbursed_term_accessories,2)."</th>
	                </tr>

	                <tr>
	                        <th style='border: 1px solid;'>g. Utilities and Effluent Treatment plant</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->cost_utilities,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->sanctioned_term_utilities,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->total_investment_utilities,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->disbursed_term_utilities,2)."</th>
	                </tr>

	                <tr>
	                        <th style='border: 1px solid;'>h. Other investments</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->cost_other,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->sanctioned_term_other,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->total_investment_other,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->disbursed_term_other,2)."</th>
	                </tr>

	                <tr>
	                        <th style='border: 1px solid;'>Total</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->cost_total,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->sanctioned_term_total,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->total_investment_total,2)."</th>
	                        <th style='border: 1px solid;'>".number_format($file_data->disbursed_term_total,2)."</th>
	                </tr>
	        </table><br/>

	        <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
	                <strong><u>TERM LOAN ACCOUNT NO. ".$file_data->loan_account_no.".</u></strong>
	        </p>

	        <p style='font-family: calibri;text-align: left;font-size: 22px;'>Signature:</p>

	        <p style='font-family: calibri;text-align: right;font-size: 22px;'>
	                Name of Branch Manager:<br/>
	                Chief Manager-RM(ME):<br/>
	                Name of Bank: <strong>UNION BANK OF INDIA</strong><br/>
	                Branch: PARLE POINT<br/>
	                Email: ".$file_data->branch_manager_email."<br/>
	        </p>

	        <p style='font-family: calibri;text-align: left;font-size: 22px;'>
	                To,<br/>
	                The General Manager / JCI (S/T)<br/>
	                District Industrial Centre / Industries Commissionerate Lal<br/> 
	                Bungalow Compound/ Udyog Bhavan<br/>
	                District Name / Gandhinagar<br/>
	        </p>
	        
	        
	</body>
	</html>";
}

function epf_gogtp($inq_id, $service_id, $stage_id, $file_id)
{
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

    $f = "<!doctype html>
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

	return $f;
}

?>