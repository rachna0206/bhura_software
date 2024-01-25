<?php

include "db_connect.php";
$obj=new DB_connect();

$inq_id = $_REQUEST['inq_id'];
$service_id = $_REQUEST['service_id'];
$stage_id = $_REQUEST['stage_id'];


// tbl_tdrawdata -> company data
$stmt_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` where id=?");
$stmt_list->bind_param("i",$inq_id);
$stmt_list->execute();
$result = $stmt_list->get_result()->fetch_assoc();
$stmt_list->close();
$row_data=json_decode($result["raw_data"]);
$post_fields=$row_data->post_fields;

// pr_file_format -> all files at this stage
$stmt_file_list = $obj->con1->prepare("SELECT fid, page_name FROM `pr_file_format` where stage_id=? and scheme_id=?");
$stmt_file_list->bind_param("ii",$stage_id,$service_id);
$stmt_file_list->execute();
$result_file_list = $stmt_file_list->get_result();
$stmt_file_list->close();

while($res_files=mysqli_fetch_array($result_file_list)) {
        if($res_files["page_name"]=='affidavit2_gogtp'){
                $file_id_affidavit = $res_files["fid"];
        }
        else if($res_files["page_name"]=='certificate_first_disbursment_gogtp'){
                $file_id_disbursement = $res_files["fid"];
        }
        else if($res_files["page_name"]=='ca_certificate_expansion_gogtp'){
                $file_id_ca_expansion = $res_files["fid"];
        }
        else if($res_files["page_name"]=='ca_certificate_newfirm_gogtp'){
                $file_id_ca_newfirm = $res_files["fid"];
        }
        else if($res_files["page_name"]=='ce_certificate_gogtp'){
                $file_id_ce = $res_files["fid"];
        }
        else if($res_files["page_name"]=='employment_data_gogtp'){
                $file_id_employment = $res_files["fid"];
        }
}


// affidavit - file 1
$stmt_files_affidavit = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
$stmt_files_affidavit->bind_param("iiii",$service_id,$stage_id,$file_id_affidavit,$inq_id);
$stmt_files_affidavit->execute();
$result_files_affidavit = $stmt_files_affidavit->get_result();
$stmt_files_affidavit->close();
if(mysqli_num_rows($result_files_affidavit)>0){
        $downl_affidavit=true;
        $res_files_affidavit = mysqli_fetch_array($result_files_affidavit);
        $file_data_affidavit=json_decode($res_files_affidavit["file_data"]);        
} else{
        $downl_affidavit=false;
}


// certificate regarding first disbursement - file 2
$stmt_files_disbursement = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
$stmt_files_disbursement->bind_param("iiii",$service_id,$stage_id,$file_id_disbursement,$inq_id);
$stmt_files_disbursement->execute();
$result_files_disbursement = $stmt_files_disbursement->get_result();
$stmt_files_disbursement->close();
if(mysqli_num_rows($result_files_disbursement)>0){
        $downl_disbursement=true;
        $res_files_disbursement = mysqli_fetch_array($result_files_disbursement);
        $file_data_disbursement=json_decode($res_files_disbursement["file_data"]);        
} else{
        $downl_disbursement=false;
}


// employment data - file 3
$stmt_files_employment = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
$stmt_files_employment->bind_param("iiii",$service_id,$stage_id,$file_id_employment,$inq_id);
$stmt_files_employment->execute();
$result_files_employment = $stmt_files_employment->get_result();
$stmt_files_employment->close();
if(mysqli_num_rows($result_files_employment)>0){
        $downl_employment=true;
        $res_files_employment = mysqli_fetch_array($result_files_employment);
        $file_data_employment=json_decode($res_files_employment["file_data"]);
        $count = count($file_data_employment);
} else{
        $downl_employment=false;
}


// ca certificate expansion - file 5
$stmt_files_ca_expansion = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
$stmt_files_ca_expansion->bind_param("iiii",$service_id,$stage_id,$file_id_ca_expansion,$inq_id);
$stmt_files_ca_expansion->execute();
$result_files_ca_expansion = $stmt_files_ca_expansion->get_result();
$stmt_files_ca_expansion->close();
if(mysqli_num_rows($result_files_ca_expansion)>0){
        $downl_ca_expansion=true;
        $res_files_ca_expansion = mysqli_fetch_array($result_files_ca_expansion);
        $file_data_ca_expansion=json_decode($res_files_ca_expansion["file_data"]);        
} else{
        $downl_ca_expansion=false;
}


// ca certificate new firm - file 6
$stmt_files_ca_newfirm = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
$stmt_files_ca_newfirm->bind_param("iiii",$service_id,$stage_id,$file_id_ca_newfirm,$inq_id);
$stmt_files_ca_newfirm->execute();
$result_files_ca_newfirm = $stmt_files_ca_newfirm->get_result();
$stmt_files_ca_newfirm->close();
if(mysqli_num_rows($result_files_ca_newfirm)>0){
        $downl_ca_newfirm=true;
        $res_files_ca_newfirm = mysqli_fetch_array($result_files_ca_newfirm);
        $file_data_ca_newfirm=json_decode($res_files_ca_newfirm["file_data"]);        
} else{
        $downl_ca_newfirm=false;
}


// ce certificate - file 7
$stmt_files_ce = $obj->con1->prepare("SELECT * FROM `pr_files_data` WHERE scheme_id=? and stage_id=? and file_id=? and inq_id=? order by id desc limit 1");
$stmt_files_ce->bind_param("iiii",$service_id,$stage_id,$file_id_ce,$inq_id);
$stmt_files_ce->execute();
$result_files_ce = $stmt_files_ce->get_result();
$stmt_files_ce->close();
if(mysqli_num_rows($result_files_ce)>0){
        $downl_ce=true;
        $res_files_ce = mysqli_fetch_array($result_files_ce);
        $file_data_ce=json_decode($res_files_ce["file_data"]);        
} else{
        $downl_ce=false;
}




// Create a temporary directory
$tempDir = sys_get_temp_dir() . '/export_' . uniqid();
mkdir($tempDir);

if($downl_affidavit){
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

                <table align='center' style='font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;'>
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
                                <td style='border: 1px solid;'>".$file_data_affidavit->mana_local_male."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->mana_local_female."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->mana_outside_male."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->mana_outside_female."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_male_manager."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_female_manager."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_manager."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_affidavit->mana_percenatge,2)."%</td>
                        </tr>

                        <tr>
                                <td style='border: 1px solid;'><strong>2</strong></td>
                                <td style='border: 1px solid;'>Workers</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->worker_local_male."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->worker_local_female."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->worker_outside_male."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->worker_outside_female."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_male_worker."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_female_worker."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_worker."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_affidavit->worker_percenatge,2)."%</td>
                        </tr>

                        <tr>
                                <td style='border: 1px solid;' colspan='2'><strong>Total</strong></td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_local_male."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_local_female."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_outside_male."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_outside_female."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_male."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->total_female."</td>
                                <td style='border: 1px solid;'>".$file_data_affidavit->final_total."</td>
                                <td style='border: 1px solid;'></td>
                        </tr>
                </table><br/>

                <p style='font-family: calibri;text-align: right;font-size: 22px;'>
                        (Name/Designation/Sign)<br/> Unit Stamp
                </p>
                
                
        </body>
        </html>";

        $file1Path = $tempDir . '/2AffidavitScheme.docx';
        file_put_contents($file1Path, $file1Content);
}


if($downl_disbursement){
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
                <title>APPRAISAL LETTER</title>
        </head>
        <body>
                <h1 style='font-family: calibri;text-align: center;font-size: 24px;'><u>Certificate regarding the Disbursement of the term loan</u></h1>

                <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
                        This is to certify that <strong><u>".$post_fields->Firm_Name."</u></strong> at <strong><u>".$post_fields->Factory_Address."</u></strong>. has availed various credit facilities including Term Loan for a project of <strong><u>".$file_data_disbursement->project."</u></strong>. We Have Sanctioned term loan of <strong><u>Rs. ".number_format($file_data_disbursement->sanctioned_term_loan,2)."</u></strong> for project on <strong><u>".date_format(new DateTime($file_data_disbursement->project_date),"d/m/Y")."</u></strong>. In that concern, we have disbursed term loan of <strong><u>Rs. ".number_format($file_data_disbursement->disbursed_term_loan,2)."</u></strong>. for its project as underneath table, which is duly verified by us and details are as given below.
                </p>

                <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
                        The detail of Term Loan is as under.
                        <ol>
                                <li>Application received from unit on <strong><u>".date_format(new DateTime($file_data_disbursement->application_received_dt),"d/m/Y").".</u></strong></li>
                                <li>Term Loan Sanction <strong><u>dated  ".date_format(new DateTime($file_data_disbursement->sanction_loan_dt),"d/m/Y").".</u></strong></li>
                                <li>Disbursement was made on 1st Disbursement as on <strong><u>Dt. ".date_format(new DateTime($file_data_disbursement->first_disbursement_dt),"d/m/Y")." of Rs. ".number_format($file_data_disbursement->first_disbursement_price,2)."/-</u></strong></li>
                                <li>Total Disbursement of <strong><u>Rs. ".number_format($file_data_disbursement->total_disbursement_price,2)."/- up to Dt. ".date_format(new DateTime($file_data_disbursement->total_disbursement_dt),'d/m/Y').".</u></strong></li>
                                <li>Disbursement was made against following Fixed Assets:</li>
                </p>

                <table align='center' style='font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;'>
                        <tr>
                                <th style='border: 1px solid;'>Fixed Assets</th>
                                <th style='border: 1px solid;'>Cost as per project report</th>
                                <th style='border: 1px solid;'>Term loan sanctioned</th>
                                <th style='border: 1px solid;'>Total Actual Investment against Term Loan</th>
                                <th style='border: 1px solid;'>Total Disbursed Term Loan against actual investment.</th>
                        </tr>
                                
                        <tr>
                                <th style='border: 1px solid;'>a. Land</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->cost_land,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->sanctioned_term_land,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->total_investment_land,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->disbursed_term_land,2)."</th>
                        </tr>

                        <tr>
                                <th style='border: 1px solid;'>b.  Building & Shed</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->cost_building,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->sanctioned_term_building,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->total_investment_building,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->disbursed_term_building,2)."</th>
                        </tr>

                        <tr>
                                <th style='border: 1px solid;'>c.  Plant & M/C</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->cost_plant,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->sanctioned_term_plant,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->total_investment_plant,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->disbursed_term_plant,2)."</th>
                        </tr>

                        <tr>
                                <th style='border: 1px solid;'>d.  Electrification</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->cost_electric,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->sanctioned_term_electric,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->total_investment_electric,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->disbursed_term_electric,2)."</th>
                        </tr>

                        <tr>
                                <th style='border: 1px solid;'>e. Tools and Equipment</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->cost_tools,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->sanctioned_term_tools,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->total_investment_tools,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->disbursed_term_tools,2)."</th>
                        </tr>

                        <tr>
                                <th style='border: 1px solid;'>f. Accessories</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->cost_accessories,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->sanctioned_term_accessories,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->total_investment_accessories,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->disbursed_term_accessories,2)."</th>
                        </tr>

                        <tr>
                                <th style='border: 1px solid;'>g. Utilities and Effluent Treatment plant</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->cost_utilities,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->sanctioned_term_utilities,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->total_investment_utilities,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->disbursed_term_utilities,2)."</th>
                        </tr>

                        <tr>
                                <th style='border: 1px solid;'>h. Other investments</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->cost_other,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->sanctioned_term_other,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->total_investment_other,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->disbursed_term_other,2)."</th>
                        </tr>

                        <tr>
                                <th style='border: 1px solid;'>Total</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->cost_total,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->sanctioned_term_total,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->total_investment_total,2)."</th>
                                <th style='border: 1px solid;'>".number_format($file_data_disbursement->disbursed_term_total,2)."</th>
                        </tr>
                </table><br/>

                <p style='font-family: calibri;text-align: justify;font-size: 22px;'>
                        <strong><u>TERM LOAN ACCOUNT NO. ".$file_data_disbursement->loan_account_no.".</u></strong>
                </p>

                <p style='font-family: calibri;text-align: left;font-size: 22px;'>Signature:</p>

                <p style='font-family: calibri;text-align: right;font-size: 22px;'>
                        Name of Branch Manager:<br/>
                        Chief Manager-RM(ME):<br/>
                        Name of Bank: <strong>UNION BANK OF INDIA</strong><br/>
                        Branch: PARLE POINT<br/>
                        Email: ".$file_data_disbursement->branch_manager_email."<br/>
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

        $file2Path = $tempDir . '/CertificateRegardingFirstDisbursement.docx';
        file_put_contents($file2Path, $file2Content);
}


if($downl_disbursement){
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
                <h1 style='font-family: calibri;text-align: center;font-size: 22px;'><u>Employment Data (Annexure – I)</u></h1>

                <table align='center' style='font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;'>
                        
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
                        </tr>";

                for($i=0;$i<$count;$i++){
                $file3Content.= "<tr>
                                <td style='border: 1px solid;'>".($i+1)."</td>
                                <td style='border: 1px solid;'>".$file_data_employment[$i]->ename."<br/><br/>ADDRESS: <br/>".$file_data_employment[$i]->address."</td>
                                <td style='border: 1px solid;'>".$file_data_employment[$i]->designation."</td>
                                <td style='border: 1px solid;'>----</td>
                                <td style='border: 1px solid;'>----</td>
                                <td style='border: 1px solid;'>----</td>
                                <td style='border: 1px solid;'>".$file_data_employment[$i]->gender."</td>
                                <td style='border: 1px solid;'>".$file_data_employment[$i]->stay."</td>
                                <td style='border: 1px solid;'>11/09/2022</td>
                        </tr>";
                }

                $file3Content.= "</table><br/>
                
        </body>
        </html>";

        $file3Path = $tempDir . '/EmploymentData.docx';
        file_put_contents($file3Path, $file3Content);
}


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
        <title>APPRAISAL LETTER</title>
</head>
<body>
        <p style='font-family: calibri;text-align: left;font-size: 16px;'>
                DIC/SRT/Textile Policy-2019<br/>
                District Industries Center,<br/>
                C- Block, 2nd Floor,<br/>
                Bahumali Bhavan,<br/>
                Nanpura, Surat<br/>
        </p>

        <h1 style='font-family: calibri;text-align: center;font-size: 22px;'>SUB: Regarding Bank Appraisal</h1>
        
        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>Respected Sir,</p>

        <p style='font-family: calibri;text-align: justify;font-size: 16px;'>
                This is to inform you that our firm (<strong>".$post_fields->Firm_Name."</strong>) have not term loan above 5.00 cr. Therefore we don’t have a Bank Appraisal Report.
        </p><br/><br/>

        <p style='font-family: calibri;text-align: right;font-size: 16px;'>Thanks and Regards</p>
        
        
</body>
</html>";

$file4Path = $tempDir . '/AppraisalLetter.docx';
file_put_contents($file4Path, $file4Content);



if($downl_ca_expansion){
        // File 5
        $file5Content = "<!doctype html>
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
                
                <table align='center' style='font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;'>
                        <tr>
                                <th style='border: 1px solid;' rowspan='2'>No</th>
                                <th style='border: 1px solid;' rowspan='2'>Break- up Fixed assets</th>
                                <th style='border: 1px solid;'>Gross Fixed Capital Investment for Expansion/ Diversification/Forward Integration/Backward Integration as on the date of initiating expansion/ the date of initiating expansion Diversification/Forward Integration/Backward Integration</th>
                                <th style='border: 1px solid;'>Gross Fixed Capital Investment for Expansion/ Diversification / Forward Integration/Backward Integration from the date of initiating expansion up to the date of commencing production/till Complete Project during the Diversification / Forward Integration/Backward Integration</th>
                                <th style='border: 1px solid;' rowspan='2'>Total Investments as on date ".date_format(new DateTime($file_data_ca_expansion->total_investment_dt),'d/m/Y')."</th>
                        </tr>
                        <tr>
                                <th style='border: 1px solid;'>".date_format(new DateTime($file_data_ca_expansion->ini_expansion_dt),'d/m/Y')."</th>
                                <th style='border: 1px solid;'>".date_format(new DateTime($file_data_ca_expansion->from_expansion_dt),'d/m/Y')." to ".date_format(new DateTime($file_data_ca_expansion->to_initiating_dt),'d/m/Y')."</th>
                        </tr>
                                
                        <tr>
                                <td style='border: 1px solid;'>1</td>
                                <td style='border: 1px solid;'>Land</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->ini_investment_land,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->comm_investment_land,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->total_land,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'>2</td>
                                <td style='border: 1px solid;'>Building</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->ini_investment_building,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->comm_investment_building,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->total_building,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'>3</td>
                                <td style='border: 1px solid;'>Plant & Machinery</td>
                                <td style='border: 1px solid;'><strong>".number_format($file_data_ca_expansion->ini_investment_plant,2)."</strong></td>
                                <td style='border: 1px solid;'><strong>".number_format($file_data_ca_expansion->comm_investment_plant,2)."</strong></td>
                                <td style='border: 1px solid;'><strong>".number_format($file_data_ca_expansion->total_plant,2)."</strong></td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'>4</td>
                                <td style='border: 1px solid;'>Utilities</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->ini_investment_utilities,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->comm_investment_utilities,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->total_utilities,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'>5</td>
                                <td style='border: 1px solid;'>Tools & Equipment</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->ini_investment_tools,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->comm_investment_tools,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->total_tools,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'>6</td>
                                <td style='border: 1px solid;'>Electrification</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->ini_investment_electric,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->comm_investment_electric,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->total_electric,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'>7</td>
                                <td style='border: 1px solid;'>Other Assets (Required Manufacturing the end Product)</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->ini_investment_assets,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->comm_investment_assets,2)."</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_expansion->total_assets,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'></td>
                                <td style='border: 1px solid;'><strong>Total</strong></td>
                                <td style='border: 1px solid;'><strong>".number_format($file_data_ca_expansion->total_ini_investment,2)."</strong></td>
                                <td style='border: 1px solid;'><strong>".number_format($file_data_ca_expansion->total_comm_investment,2)."</strong></td>
                                <td style='border: 1px solid;'><strong>".number_format($file_data_ca_expansion->final_total,2)."</strong></td>
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

        $file5Path = $tempDir . '/CACertificate-Expansion.docx';
        file_put_contents($file5Path, $file5Content);
}


if($downl_ca_newfirm){
        // File 6
        $file6Content = "<!doctype html>
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
                        We hereby certify that M/s. <strong><u>".$post_fields->Firm_Name."</u></strong> has acquired following fixed assets up to <strong><u>".date_format(new DateTime($file_data_ca_newfirm->acquired_assets_dt),"d/m/Y")."</u></strong> at their factory address <strong><u>".$post_fields->Factory_Address."</u></strong>. for <strong><u>MANUFACTURE OF ".$file_data_ca_newfirm->manufacturing_prod."</u></strong> and have commenced the commercial production on  <strong><u>".date_format(new DateTime($file_data_ca_newfirm->commercial_date),"d/m/Y")."</u></strong> and raised the first invoice on <strong><u>".date_format(new DateTime($file_data_ca_newfirm->first_invoice_date),"d/m/Y")."</u></strong> bearing on 1 dated <strong><u>".date_format(new DateTime($file_data_ca_newfirm->first_invoice_date),"d/m/Y")."</u></strong> for   a    total    invoice    value    of Rs. <strong><u>".number_format($file_data_ca_newfirm->invoice_value,2)."</u></strong>.
                </p>
                
                <table align='center' style='font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;'>
                        <tr>
                                <th style='border: 1px solid;'>Sr. No.</th>
                                <th style='border: 1px solid;'>Description of Assets</th>
                                <th style='border: 1px solid;'>Gross Fixed Capital Assets</th>
                        </tr>

                        <tr>
                                <td style='border: 1px solid;'>1<strong></strong></td>
                                <td style='border: 1px solid;'>Land</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->land,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>2</strong></td>
                                <td style='border: 1px solid;'>Building & Shed</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->building_shed,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>3</strong></td>
                                <td style='border: 1px solid;'>Plant & M/C</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->plant_mc,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>4</strong></td>
                                <td style='border: 1px solid;'>Electrification</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->electrification,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>5</strong></td>
                                <td style='border: 1px solid;'>Tools and Equipment</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->tools_equipment,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>6</strong></td>
                                <td style='border: 1px solid;'>Accessories</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->accessories,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>7</strong></td>
                                <td style='border: 1px solid;'>Utilities and Effluent Treatment plant</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->utilities,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>8</strong></td>
                                <td style='border: 1px solid;'>Other investments</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->investments,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;' colspan='2'><strong>Total</strong></td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->total_gross_capital,2)."</td>
                        </tr>
                </table><br/><br/>

                <table align='center' style='font-family: calibri;text-align: center;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 50%;'>
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
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->capital,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>2</strong></td>
                                <td style='border: 1px solid;'>b. Equity Share Premium</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->premium,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>3</strong></td>
                                <td style='border: 1px solid;'>c. Bank Term Loan</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->term_loan,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>4</strong></td>
                                <td style='border: 1px solid;'>d. Working Capital Loan</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->capital_loan,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>5</strong></td>
                                <td style='border: 1px solid;'>e. Internal Source of Fund</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->internal_source,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;'><strong>6</strong></td>
                                <td style='border: 1px solid;'>f. Others</td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->others,2)."</td>
                        </tr>
                        <tr>
                                <td style='border: 1px solid;' colspan='2'><strong>Total</strong></td>
                                <td style='border: 1px solid;'>".number_format($file_data_ca_newfirm->total_amount_finance,2)."</td>
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

        $file6Path = $tempDir . '/CACertificate-NewFirm.docx';
        file_put_contents($file6Path, $file6Content);
}



if($downl_ce){
        // File 7
        $file7Content = "<!doctype html>
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

                <p style='font-family: calibri;text-align: justify;font-size: 22px;'>I have visited the manufacturing facility of the unit and hereby opined that the applicant <strong><u>M/s. ".$post_fields->Firm_Name."</u></strong> fulfills all the provisions concerned to Expansion or Forward / Backward integration mentioned under the Government Resolution No. <u>TEX-102018-3327-CH</u>, dated <u>10/01/2019</u> for “Scheme for assistance to strengthen specific sector in the textile value chain”.</p>
                
                <table align='center' style='font-family: calibri;font-size: 16px; border-collapse: collapse; border: 1px solid; width: 80%;'>
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
                                        Rs. ".$file_data_ce->existing_gross_capital."<br/><br/>
                                        Rs. ".$file_data_ce->gross_capital."<br/><br/>
                                        Rs. ".$file_data_ce->total_gross_capital."<br/><br/>
                                        ".$file_data_ce->investment_increase_perc."% (should be 25% or more as mentioned in the condition no. 4.6 and 4.7 of GR)
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
                                        ".$file_data_ce->existing_capacity."  MTR (Per Year)<br/><br/>
                                        ".$file_data_ce->proposed_capacity." MTR (Per Year)<br/><br/>
                                        ".$file_data_ce->proposed_capacity_increase_perc."% (should be 25% or more as mentioned in the condition no. 4.6 and 4.7 of GR)
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
                                        ".$file_data_ce->existing_capacity_second." MTR<br/><br/>
                                        ".$file_data_ce->two_years_production_capacity." MTR (MAR-2022)<br/><br/>
                                        ".$file_data_ce->two_years_production_money." MTR (MAR-2021)<br/><br/>
                                        ".$file_data_ce->max_utilization_perc."%(MAR-2022) (should be 75% or more as mentioned in the condition no. 4.6 and 4.7 of GR)
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

        $file7Path = $tempDir . '/CECertificate.docx';
        file_put_contents($file7Path, $file7Content);
}


// File 8
$file8Content = "<!doctype html>
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

$file8Path = $tempDir . '/EPF.docx';
file_put_contents($file8Path, $file8Content);


// Create the zip archive
$zipPath = sys_get_temp_dir() . '/export.zip';
$zip = new ZipArchive();
$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
if($downl_affidavit){ $zip->addFile($file1Path, '2AffidavitScheme.docx'); }
if($downl_disbursement){ $zip->addFile($file2Path, 'CertificateRegardingFirstDisbursement.docx'); }
if($downl_employment){ $zip->addFile($file3Path, 'EmploymentData.docx'); }
$zip->addFile($file4Path, 'AppraisalLetter.docx');
if($downl_ca_expansion){ $zip->addFile($file5Path, 'CACertificate-Expansion.docx'); }
if($downl_ca_newfirm){ $zip->addFile($file6Path, 'CACertificate-NewFirm.docx'); }
if($downl_ce){ $zip->addFile($file7Path, 'CECertificate.docx'); }
$zip->addFile($file8Path, 'EPF.docx');
$zip->close();

/*echo $file1Content;
echo $file2Content;
echo $file3Content;
echo $file4Content;
echo $file5Content;
echo $file6Content;
echo $file7Content;
echo $file8Content;*/

// Set the appropriate headers for the download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="export_'.$post_fields->Firm_Name.'.zip"');
header('Content-Length: ' . filesize($zipPath));

// Send the zip file to the user
readfile($zipPath);

// Clean up - remove temporary directory and zip file
if($downl_affidavit){ unlink($file1Path); }
if($downl_disbursement){ unlink($file2Path); }
if($downl_employment){ unlink($file3Path); }
unlink($file4Path);
if($downl_ca_expansion){ unlink($file5Path); }
if($downl_ca_newfirm){ unlink($file6Path); }
if($downl_ce){ unlink($file7Path); }
unlink($file8Path);
rmdir($tempDir);
unlink($zipPath);
?>