<?php
include("header.php");
//error_reporting(0);

$plot_status="";

$stmt_area_list = $obj->con1->prepare("select distinct(area_id) from tbl_industrial_estate");
$stmt_area_list->execute();
$area_result = $stmt_area_list->get_result();
$stmt_area_list->close();

$stmt_ind_estate_list = $obj->con1->prepare("select distinct(industrial_estate) from tbl_industrial_estate");
$stmt_ind_estate_list->execute();
$ind_estate_result = $stmt_ind_estate_list->get_result();
$stmt_ind_estate_list->close();

$stmt_ind_estate_name = $obj->con1->prepare("SELECT * FROM `tbl_industrial_estate` WHERE id=?");
$stmt_ind_estate_name->bind_param("i",$_COOKIE['report_estate_id']);
$stmt_ind_estate_name->execute();
$estate_result = $stmt_ind_estate_name->get_result()->fetch_assoc();
$stmt_ind_estate_name->close();

if(isset($_COOKIE['report_estate_id'])){
  $stmt_list = $obj->con1->prepare("SELECT p1.pid, c1.rawdata_id, r1.raw_data->>'$.post_fields.Firm_Name' as firm_name, r1.raw_data->>'$.post_fields.GST_No' as gst_no, i1.area_id, i1.city_id, i1.industrial_estate, p1.plot_no, p1.floor, p1.road_no, p1.plot_status, r1.raw_data->>'$.post_fields.Contact_Name' as contact_person, r1.raw_data->>'$.post_fields.Mobile_No' as contact_number, c1.status, c1.constitution, r1.raw_data->>'$.post_fields.Remarks' as remark, r1.raw_data->>'$.post_fields.Segment' as segment FROM `pr_company_plots` p1 JOIN `tbl_industrial_estate` i1 ON p1.industrial_estate_id=i1.id LEFT JOIN `pr_company_details` c1 ON p1.company_id=c1.cid LEFT JOIN `tbl_tdrawdata` r1 ON c1.rawdata_id=r1.id WHERE p1.industrial_estate_id='".$_COOKIE['report_estate_id']."' ORDER BY p1.industrial_estate_id, abs(p1.road_no), abs(p1.plot_no), p1.floor");
}
else{
  $stmt_list = $obj->con1->prepare("SELECT p1.pid, c1.rawdata_id, r1.raw_data->>'$.post_fields.Firm_Name' as firm_name, r1.raw_data->>'$.post_fields.GST_No' as gst_no, i1.area_id, i1.city_id, i1.industrial_estate, p1.plot_no, p1.floor, p1.road_no, p1.plot_status, r1.raw_data->>'$.post_fields.Contact_Name' as contact_person, r1.raw_data->>'$.post_fields.Mobile_No' as contact_number, c1.status, c1.constitution, r1.raw_data->>'$.post_fields.Remarks' as remark, r1.raw_data->>'$.post_fields.Segment' as segment FROM `pr_company_plots` p1 JOIN `tbl_industrial_estate` i1 ON p1.industrial_estate_id=i1.id LEFT JOIN `pr_company_details` c1 ON p1.company_id=c1.cid LEFT JOIN `tbl_tdrawdata` r1 ON c1.rawdata_id=r1.id ORDER BY p1.industrial_estate_id, abs(p1.road_no), abs(p1.plot_no), p1.floor");
}
$stmt_list->execute();
$result = $stmt_list->get_result();
$stmt_list->close();

// insert data
if(isset($_REQUEST['btnsubmit']))
{
  $firm_name=isset($_REQUEST['firm_name'])?$_REQUEST['firm_name']:"";
  $gst_no=isset($_REQUEST['gst_no'])?$_REQUEST['gst_no']:"";
  // $area=isset($_REQUEST['area'])?$_REQUEST['area']:"";
  // $ind_estate=isset($_REQUEST['industrial_estate'])?$_REQUEST['industrial_estate']:"";
  $status=isset($_REQUEST['status'])?$_REQUEST['status']:"";
  $plot_status=isset($_REQUEST['plot_status'])?$_REQUEST['plot_status']:"";
  $contact_person=isset($_REQUEST['contact_person'])?$_REQUEST['contact_person']:"";
  $contact_number=isset($_REQUEST['contact_number'])?$_REQUEST['contact_number']:"";
    
  $firm_name_str=($firm_name!="")?" and lower(r1.raw_data->'$.post_fields.Firm_Name') like '%".strtolower($firm_name)."%'":"";
  $gst_no_str=($gst_no!="")?"and r1.raw_data->'$.post_fields.GST_No' like '%".$gst_no."%'":"";
  // $area_str=($area!="")?"and lower(raw_data->'$.post_fields.Area') like '%".strtolower($area)."%'":"";
  // $ind_estate_str=($ind_estate!="")?"and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($ind_estate)."%'":"";
  $ind_estate_str=($_COOKIE['report_estate_id']!="")?"and p1.industrial_estate_id='".$_COOKIE['report_estate_id']."'":"";
  $status_str=($status!="")?"and c1.status='".$status."'":"";
  $plot_status_str=($plot_status!="")?"and p1.plot_status='".$plot_status."'":"";
  $contact_person_str=($contact_person!="")?" and lower(r1.raw_data->'$.post_fields.Contact_Name') like '%".strtolower($contact_person)."%'":"";
  $contact_number_str=($contact_number!="")?" and lower(r1.raw_data->'$.post_fields.Mobile_No') like '%".strtolower($contact_number)."%'":"";
  
  /*echo "SELECT p1.pid, c1.rawdata_id, r1.raw_data->>'$.post_fields.Firm_Name' as firm_name, r1.raw_data->>'$.post_fields.GST_No' as gst_no, i1.area_id, i1.city_id, i1.industrial_estate, p1.plot_no, p1.floor, p1.road_no, p1.plot_status, r1.raw_data->>'$.post_fields.Contact_Name' as contact_person, r1.raw_data->>'$.post_fields.Mobile_No' as contact_number, c1.status, c1.constitution, r1.raw_data->>'$.post_fields.Remarks' as remark, r1.raw_data->>'$.post_fields.Segment' as segment FROM `pr_company_plots` p1 JOIN `tbl_industrial_estate` i1 ON p1.industrial_estate_id=i1.id LEFT JOIN `pr_company_details` c1 ON p1.company_id=c1.cid LEFT JOIN `tbl_tdrawdata` r1 ON c1.rawdata_id=r1.id WHERE 1 ".$firm_name_str.$gst_no_str.$status_str.$plot_status_str.$ind_estate_str.$contact_person_str.$contact_number_str." ORDER BY p1.industrial_estate_id, abs(p1.plot_no)";*/

  $stmt_list = $obj->con1->prepare("SELECT p1.pid, c1.rawdata_id, r1.raw_data->>'$.post_fields.Firm_Name' as firm_name, r1.raw_data->>'$.post_fields.GST_No' as gst_no, i1.area_id, i1.city_id, i1.industrial_estate, p1.plot_no, p1.floor, p1.road_no, p1.plot_status, r1.raw_data->>'$.post_fields.Contact_Name' as contact_person, r1.raw_data->>'$.post_fields.Mobile_No' as contact_number, c1.status, c1.constitution, r1.raw_data->>'$.post_fields.Remarks' as remark, r1.raw_data->>'$.post_fields.Segment' as segment FROM `pr_company_plots` p1 JOIN `tbl_industrial_estate` i1 ON p1.industrial_estate_id=i1.id LEFT JOIN `pr_company_details` c1 ON p1.company_id=c1.cid LEFT JOIN `tbl_tdrawdata` r1 ON c1.rawdata_id=r1.id WHERE 1 ".$firm_name_str.$gst_no_str.$status_str.$plot_status_str.$ind_estate_str.$contact_person_str.$contact_number_str." ORDER BY p1.industrial_estate_id, abs(p1.road_no), abs(p1.plot_no), p1.floor");
  $stmt_list->execute();
  $result = $stmt_list->get_result();
  
  $stmt_list->close();

}
?>

<h4 class="fw-bold py-3">Company Plots Report</h4>
<dl class="row mt-2">
  <dd class="text-muted col-sm-2">Industrial Estate : </dd>
  <dt class="fw-bold col-sm-9"><?php echo $estate_result['industrial_estate'] ?></dt>

  <dd class="text-muted col-sm-2">Area : </dd>
  <dt class="fw-bold col-sm-9"><?php echo $estate_result['area_id'] ?></dt>

  <dd class="text-muted col-sm-2">Taluka : </dd>
  <dt class="fw-bold col-sm-9"><?php echo $estate_result['taluka'] ?></dt>
</dl>

<!-- Basic Layout -->
<div class="row">
  <div class="col-xl">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
          
      </div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data">
          <div class="row">
            
            <div class="mb-3 col-md-3">
              <label class="form-label" for="basic-default-fullname">Firm Name</label>
              <input type="text" class="form-control" name="firm_name" id="firm_name" value="<?php echo isset($_REQUEST['firm_name'])?$_REQUEST['firm_name']:""?>" />              
            </div>
            <div class="mb-3 col-md-3">
              <label class="form-label" for="basic-default-fullname">GST No.</label>
              <input type="text" class="form-control" name="gst_no" id="gst_no" value="<?php echo isset($_REQUEST['gst_no'])?$_REQUEST['gst_no']:""?>"/>
            </div>
            <div class="mb-3 col-md-3">
              <label class="form-label" for="basic-default-fullname">Contact Person</label>
              <input type="text" class="form-control" name="contact_person" id="contact_person" value="<?php echo isset($_REQUEST['contact_person'])?$_REQUEST['contact_person']:""?>" />              
            </div>
            <div class="mb-3 col-md-3">
              <label class="form-label" for="basic-default-fullname">Contact Number</label>
              <input type="text" class="form-control" name="contact_number" id="contact_number" value="<?php echo isset($_REQUEST['contact_number'])?$_REQUEST['contact_number']:""?>"/>
            </div>
            
            <!-- <div class="mb-3 col-md-3">
              <label class="form-label" for="basic-default-fullname">Area</label>
              <select name="area" id="area" class="form-control">
                <option value="">Select Area</option>
        <?php while($area_list=mysqli_fetch_array($area_result)){ ?>
            <option value="<?php echo $area_list["area_id"] ?>" <?php echo (isset($_REQUEST['area']) && $_REQUEST['area']==$area_list["area_id"])?"selected":""?>><?php echo $area_list["area_id"] ?></option>
        <?php } ?>
              </select>
            </div>

            <div class="mb-3 col-md-3">
              <label class="form-label" for="basic-default-fullname">Industrial Estate</label>
              <select name="industrial_estate" id="industrial_estate" class="form-control">
                <option value="">Select Industrial Estate</option>
        <?php while($ind_estate_list=mysqli_fetch_array($ind_estate_result)){ ?>
            <option value="<?php echo $ind_estate_list["industrial_estate"] ?>" <?php echo (isset($_REQUEST['industrial_estate']) && $_REQUEST['industrial_estate']==$ind_estate_list["industrial_estate"])?"selected":""?>><?php echo $ind_estate_list["industrial_estate"] ?></option>
        <?php } ?>
              </select>
            </div> -->
            
            <div class="mb-3">
              <label class="form-label d-block" for="basic-default-fullname">Status</label>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="status" id="existing_client" value="Existing Client" <?php echo (isset($_REQUEST['status']) && $_REQUEST['status']=="Existing Client")?"checked":""?>>
                <label class="form-check-label" for="inlineRadio1">Existing Client</label>
              </div>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="status" id="positive" value="Positive" <?php echo (isset($_REQUEST['status']) && $_REQUEST['status']=="Positive")?"checked":""?>>
                <label class="form-check-label" for="inlineRadio1">Positive</label>
              </div>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="status" id="negative" value="Negative" <?php echo (isset($_REQUEST['status']) && $_REQUEST['status']=="Negative")?"checked":""?>>
                <label class="form-check-label" for="inlineRadio1">Negative</label>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label d-block" for="basic-default-fullname">Plot Status</label>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="plot_status" id="open_plot" value="Open Plot" <?php echo (isset($_REQUEST['plot_status']) && $_REQUEST['plot_status']=="Open Plot")?"checked":""?>>
                <label class="form-check-label" for="inlineRadio1">Open Plot</label>
              </div>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="plot_status" id="under_construction" value="Under Construction" <?php echo (isset($_REQUEST['plot_status']) && $_REQUEST['plot_status']=="Under Construction")?"checked":""?>>
                <label class="form-check-label" for="inlineRadio1">Under Construction</label>
              </div>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="plot_status" id="constructed" value="Constructed" <?php echo (isset($_REQUEST['plot_status']) && $_REQUEST['plot_status']=="Constructed")?"checked":""?>>
                <label class="form-check-label" for="inlineRadio1">Constructed</label>
              </div>
            </div>
           
          </div>

          <button type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary">Submit</button>
        
          <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location='company_plot_report.php'">Cancel</button>

        </form>
      </div>
    </div>
  </div>
</div>

  <!-- Basic Bootstrap Table -->
    <div class="card">
      <div class="row ms-2 me-3">
        <div class="col-md-9"><h5 class="card-header">Company Plot Records</h5></div>
        <div class="col-md-2" style="margin:1%">
        <input type="button" class="btn btn-primary" name="btn_excel" value="View Full Table" onClick="javascript:companyGrid('<?php echo isset($_REQUEST['firm_name'])?$_REQUEST['firm_name']:"" ?>','<?php echo isset($_REQUEST['gst_no'])?$_REQUEST['gst_no']:"" ?>','<?php echo isset($_COOKIE['report_estate_id'])?$_COOKIE['report_estate_id']:""?>','<?php echo isset($_REQUEST['status'])?$_REQUEST['status']:""?>','<?php echo isset($_REQUEST['plot_status'])?$_REQUEST['plot_status']:""?>','<?php echo isset($_REQUEST['plot_status'])?$_REQUEST['contact_person']:""?>','<?php echo isset($_REQUEST['plot_status'])?$_REQUEST['contact_number']:""?>')" id="btn_excel">
        </div>
      </div>
     
      <div class="table-responsive text-nowrap">
        <table class="table" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>Firm Name</th>
              <th>GST No.</th>
              <th>Plot No.</th>
              <th>Floor No.</th>
              <th>Road No.</th>
              <th>Plot Status</th>
              <th>Contact Person</th>
              <th>Contact No.</th>
              <th>Status</th>
              <th>Constitution</th>
              <th>Remark</th>
              <th>Segment</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0" id="grid">
            <?php 
              $i=1;
              $c=0;

              $colour_array = array('secondary','success','danger','warning','info','dark');
              while($data=mysqli_fetch_array($result))
              {
                /*$row_data=json_decode($data["raw_data"]);
                $post_fields=$row_data->post_fields;*/
              
                if($data['rawdata_id']==0 || $data['rawdata_id']==null){
                    $table_colour = 'default';
                }
                else{
                  if($i==1){
                   // $old_name=$post_fields->Firm_Name;
                    $old_name=$data['rawdata_id'];
                    $table_colour = $colour_array[$c];
                    $c++;
                    if($c==count($colour_array)){
                      $c=0;
                    }
                  }
                  else{
                    // $new_name=$post_fields->Firm_Name;
                    $new_name=$data['rawdata_id'];
                    if($new_name!=$old_name){
                      $old_name=$new_name;
                      $table_colour = $colour_array[$c];
                      $c++;
                      if($c==count($colour_array)){
                        $c=0;
                      }
                    }
                  }  
                }
                
            ?>
            <tr class="table-<?php echo $table_colour?>">
              <td><?php echo $i ?></td>
              <td><?php echo $data['firm_name'] ?></td>
              <td><?php echo $data['gst_no'] ?></td>
              <td><?php echo $data['plot_no'] ?></td>
              <td><?php echo ($data['floor']=='0')?"Ground Floor":$data['floor'] ?></td>
              <td><?php echo $data['road_no'] ?></td>
              <td><?php echo $data['plot_status'] ?></td>
              <td><?php echo $data['contact_person'] ?></td>
              <td><?php echo $data['contact_number'] ?></td>
              <td><?php echo $data['status'] ?></td>
              <td><?php echo $data['constitution'] ?></td>
              <td><?php echo $data['remark'] ?></td>
              <td><?php echo $data['segment'] ?></td>
            </tr>
          <?php 
                $i++;
          } ?>
            
          </tbody>
        </table>
      </div>
    </div>

    <!--/ Basic Bootstrap Table -->

<script type="text/javascript">

  /*function companyGrid(firm_name,gst_no,area,industrial_estate,status,plot_status){
    const arr = [firm_name,gst_no,area,industrial_estate,status,plot_status];*/
  function companyGrid(firm_name,gst_no,industrial_estate,status,plot_status,contact_person,contact_number){
    const arr = [firm_name,gst_no,industrial_estate,status,plot_status,contact_person,contact_number];
    window.open('company_plot_grid.php', '_blank');
    document.cookie = "report_search="+arr;
  }

</script>

<?php 
  include("footer.php");
?>