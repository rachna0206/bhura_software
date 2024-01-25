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

$stmt_list = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata order by id desc");
$stmt_list->execute();
$result = $stmt_list->get_result();
$stmt_list->close();

// insert data
if(isset($_REQUEST['btnsubmit']))
{
  $firm_name=isset($_REQUEST['firm_name'])?$_REQUEST['firm_name']:"";
  $gst_no=isset($_REQUEST['gst_no'])?$_REQUEST['gst_no']:"";
  $area=isset($_REQUEST['area'])?$_REQUEST['area']:"";
  $ind_estate=isset($_REQUEST['industrial_estate'])?$_REQUEST['industrial_estate']:"";
  $status=isset($_REQUEST['status'])?$_REQUEST['status']:"";
  $plot_status=isset($_REQUEST['plot_status'])?$_REQUEST['plot_status']:"";
  
  $firm_name_str=($firm_name!="")?" and lower(raw_data->'$.post_fields.Firm_Name') like '%".strtolower($firm_name)."%'":"";
  $gst_no_str=($gst_no!="")?"and raw_data->'$.post_fields.GST_No' like '%".$gst_no."%'":"";
  $area_str=($area!="")?"and lower(raw_data->'$.post_fields.Area') like '%".strtolower($area)."%'":"";
  $ind_estate_str=($ind_estate!="")?"and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($ind_estate)."%'":"";
  $status_str=($status!="")?"and raw_data->'$.Status' like '%".$status."%'":"";
  $plot_status_str=($plot_status!="")?"and raw_data->'$.plot_details[*].Plot_Status' like '%".$plot_status."%'":"";
  
  $stmt_list = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata WHERE 1 ".$firm_name_str.$gst_no_str.$area_str.$status_str.$plot_status_str.$ind_estate_str." order by id desc");
  $stmt_list->execute();
  $result = $stmt_list->get_result();
  
  $stmt_list->close();

}
?>

<h4 class="fw-bold py-3 mb-4">Company Plots Report</h4>

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
            </div>
            
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
        <input type="button" class="btn btn-primary" name="btn_excel" value="View Full Table" onClick="javascript:companyGrid('<?php echo isset($_REQUEST['firm_name'])?$_REQUEST['firm_name']:"" ?>','<?php echo isset($_REQUEST['gst_no'])?$_REQUEST['gst_no']:"" ?>','<?php echo isset($_REQUEST['area'])?$_REQUEST['area']:""?>','<?php echo isset($_REQUEST['indusrail_estate'])?$_REQUEST['industrial_estate']:""?>','<?php echo isset($_REQUEST['status'])?$_REQUEST['status']:""?>','<?php echo isset($_REQUEST['plot_status'])?$_REQUEST['plot_status']:""?>')" id="btn_excel">
        </div>
      </div>
     
      <div class="table-responsive text-nowrap">
        <table class="table" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>Firm Name</th>
              <th>GST No.</th>
              <th>Area</th>
              <th>Industrial Estate</th>
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

              $colour_array = array('default','secondary','success','danger','warning','info','dark');
              while($data=mysqli_fetch_array($result))
              {
                $row_data=json_decode($data["raw_data"]);
                $post_fields=$row_data->post_fields;
              
                if($i==1){
                  $old_name=$post_fields->Firm_Name;
                  $table_colour = $colour_array[$c];
                  $c++;
                  if($c==count($colour_array)){
                    $c=0;
                  }
                }
                else{
                  $new_name=$post_fields->Firm_Name;
                  if($new_name!=$old_name){
                    $old_name=$new_name;
                    $table_colour = $colour_array[$c];
                    $c++;
                    if($c==count($colour_array)){
                      $c=0;
                    }
                  }else{}
                }

                if($plot_status!=""){
                  if(isset($row_data->plot_details)){
                    $plot_details=$row_data->plot_details;
                    asort($plot_details);
                    
                    foreach ($plot_details as $pd) {
                      if($plot_status==$pd->Plot_Status){
            ?>

            <tr class="table-<?php echo $table_colour?>">
              <td><?php echo $i?></td>
              <td><?php echo $post_fields->Firm_Name ?></td>
              <td><?php echo $post_fields->GST_No ?></td>
              <td><?php echo $post_fields->Area." - ".$post_fields->city ?></td>
              <td><?php echo $post_fields->IndustrialEstate ?></td>
              <td><?php if(isset($pd->Plot_No)){ echo $pd->Plot_No; } ?></td>
              <td><?php if(isset($pd->Floor)){ if($pd->Floor=='0'){ echo 'Ground Floor'; } else{ echo $pd->Floor; } } ?></td>
              <td><?php if(isset($pd->Road_No)){ echo $pd->Road_No; } ?></td>
              <td><?php if(isset($pd->Plot_Status)){ echo $pd->Plot_Status; } ?></td>
              <td><?php echo $post_fields->Contact_Name ?></td>
              <td><?php echo $post_fields->Mobile_No ?></td>
              <td><?php if(isset($row_data->Status)){ echo $row_data->Status; } ?></td>
              <td><?php if(isset($row_data->Constitution)){ echo $row_data->Constitution; } ?></td>
              <td><?php echo $post_fields->Remarks ?></td>
              <td><?php echo $post_fields->Segment ?></td>
          <?php 
                $i++;
              } }
                  }
                }

                else{ 
                if(isset($row_data->plot_details)){
                  $plot_details=$row_data->plot_details;  
                  asort($plot_details);

                  foreach ($plot_details as $pd) {
            ?>

            <tr class="table-<?php echo $table_colour?>">
              <td><?php echo $i?></td>
              <td><?php echo $post_fields->Firm_Name ?></td>
              <td><?php echo $post_fields->GST_No ?></td>
              <td><?php echo $post_fields->Area." - ".$post_fields->city ?></td>
              <td><?php echo $post_fields->IndustrialEstate ?></td>
              <td><?php if(isset($pd->Plot_No)){ echo $pd->Plot_No; } ?></td>
              <td><?php if(isset($pd->Floor)){ if($pd->Floor=='0'){ echo 'Ground Floor'; } else{ echo $pd->Floor; } } ?></td>
              <td><?php if(isset($pd->Road_No)){ echo $pd->Road_No; } ?></td>
              <td><?php if(isset($pd->Plot_Status)){ echo $pd->Plot_Status; } ?></td>
              <td><?php echo $post_fields->Contact_Name ?></td>
              <td><?php echo $post_fields->Mobile_No ?></td>
              <td><?php if(isset($row_data->Status)){ echo $row_data->Status; } ?></td>
              <td><?php if(isset($row_data->Constitution)){ echo $row_data->Constitution; } ?></td>
              <td><?php echo $post_fields->Remarks ?></td>
              <td><?php echo $post_fields->Segment ?></td> 
          <?php 
                $i++;
              }
            }
            else{
          ?>
          <tr class="table-<?php echo $table_colour?>">
            <td><?php echo $i?></td>
              <td><?php echo $post_fields->Firm_Name ?></td>
              <td><?php echo $post_fields->GST_No ?></td>
              <td><?php echo $post_fields->Area." - ".$post_fields->city ?></td>
              <td><?php echo $post_fields->IndustrialEstate ?></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td><?php echo $post_fields->Contact_Name ?></td>
              <td><?php echo $post_fields->Mobile_No ?></td>
              <td><?php if(isset($row_data->Status)){ echo $row_data->Status; } ?></td>
              <td><?php if(isset($row_data->Constitution)){ echo $row_data->Constitution; } ?></td>
              <td><?php echo $post_fields->Remarks ?></td>
              <td><?php echo $post_fields->Segment ?></td> 
          <?php
              $i++;
            }
          }
          } ?>
            </tr>
            
          </tbody>
        </table>
      </div>
    </div>

    <!--/ Basic Bootstrap Table -->

<script type="text/javascript">

  function companyGrid(firm_name,gst_no,area,industrial_estate,status,plot_status){
    const arr = [firm_name,gst_no,area,industrial_estate,status,plot_status];
    window.open('company_plot_grid.php', '_blank');
    document.cookie = "report_search="+arr;
  }

</script>

<?php 
	include("footer.php");
?>