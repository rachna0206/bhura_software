<?php
  include("header.php");

// State List
$stmt_state_list = $obj->con1->prepare("select DISTINCT(state_id) from tbl_industrial_estate where state_id='GUJARAT'");
$stmt_state_list->execute();
$state_result = $stmt_state_list->get_result();
$stmt_state_list->close();

// City List
$stmt_city = $obj->con1->prepare("select DISTINCT(city_id) from tbl_industrial_estate where state_id='GUJARAT' and city_id='SURAT'");
$stmt_city->execute();
$city_result = $stmt_city->get_result();
$stmt_city->close();

// Taluka List
$stmt_taluka = $obj->con1->prepare("select DISTINCT(taluka) from tbl_industrial_estate where state_id='GUJARAT' and city_id='SURAT'");
$stmt_taluka->execute();
$taluka_result = $stmt_taluka->get_result();
$stmt_taluka->close();

// insert data for select estate first
if(isset($_REQUEST['btnsubmit_est']))
{
  $industrial_estate_id = $_REQUEST['industrial_estate_id_est'];
  $plot_no = $_REQUEST['plot_no_est'];
  $road_no = $_REQUEST['road_no_est'];
  $floor = $_REQUEST['floor_est'];
  $rawdata_id = $_REQUEST['rawdata_id_est'];
  $plotting_pattern = $_REQUEST['plotting_pattern_est'];

  $str_arr = explode (",", $floor); 
  $plot_rawdata_id = $str_arr[0];
  $floor_no = $str_arr[1];
  
    
  $stmt_company_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` WHERE id=?");
  $stmt_company_list->bind_param("i",$rawdata_id);
  $stmt_company_list->execute();
  $company_result = $stmt_company_list->get_result()->fetch_assoc();
  $stmt_company_list->close();

  $stmt_plot_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` WHERE id=?");
  $stmt_plot_list->bind_param("i",$plot_rawdata_id);
  $stmt_plot_list->execute();
  $plot_result = $stmt_plot_list->get_result()->fetch_assoc();
  $stmt_plot_list->close();

  // get array of plot
  $row_data_plot=json_decode($plot_result["raw_data"]);
  $plot_array = $row_data_plot->plot_details;
  
  // add array of plot in company json
  $row_data_comp=json_decode($company_result["raw_data"]);
  $row_data_comp->bad_lead_reason = "";
  $row_data_comp->bad_lead_reason_remark = "";
  $row_data_comp->Image = "";
  $row_data_comp->Constitution = "";
  $row_data_comp->Status = "";
  $row_data_comp->plot_details = $plot_array;
  $json_object = json_encode($row_data_comp);

  $post_fields_comp = $row_data_comp->post_fields;
  $source = $post_fields_comp->source;
  $source_name = $post_fields_comp->Source_Name;
  $contact_person = $post_fields_comp->Contact_Name;
  $contact_no = $post_fields_comp->Mobile_No;
  $firm_name = $post_fields_comp->Firm_Name;
  $gst_no = $post_fields_comp->GST_No;
  $category = $post_fields_comp->Category;
  $segment = $post_fields_comp->Segment;
  $premise = $post_fields_comp->Premise;
  $state = $post_fields_comp->state;
  $city = $post_fields_comp->city;
  $taluka = $post_fields_comp->Taluka;
  $area = $post_fields_comp->Area;
  $industrial_estate = $post_fields_comp->IndustrialEstate;
  $inq_submit = "Submit";
  
  // get id of table pr_company_plot 
  if($plotting_pattern=="Series"){
    $stmt_company_plot = $obj->con1->prepare("SELECT pid, company_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=? ");
    $stmt_company_plot->bind_param("sii",$plot_no,$floor_no,$industrial_estate_id);
  }
  else if($plotting_pattern=="Road"){
    $stmt_company_plot = $obj->con1->prepare("SELECT pid, company_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=? and road_no=?");
    $stmt_company_plot->bind_param("siis",$plot_no,$floor_no,$industrial_estate_id,$road_no);
  }
  $stmt_company_plot->execute();
  $pr_company_plot = $stmt_company_plot->get_result()->fetch_assoc();
  $stmt_company_plot->close();

  try
  {
    // update json of company
    $stmt = $obj->con1->prepare("UPDATE `tbl_tdrawdata` set raw_data=? where id=?");
  	$stmt->bind_param("si",$json_object,$company_result['id']);
  	$Resp=$stmt->execute();
    $stmt->close();

    // delete blank json of plot
    $stmt_del = $obj->con1->prepare("DELETE from `tbl_tdrawdata` where id=?");
    $stmt_del->bind_param("i",$plot_rawdata_id);
    $Resp=$stmt_del->execute();
    $stmt_del->close();

    // insert into pr_company_details and pr_company_plots
    $stmt_pr_company_detail = $obj->con1->prepare("INSERT INTO `pr_company_details`(`source`, `source_name`, `contact_name`, `mobile_no`, `firm_name`, `gst_no`, `category`, `segment`, `premise`, `state`, `city`, `taluka`, `area`, `industrial_estate`, `inq_submit`, `industrial_estate_id`, `user_id`, `rawdata_id`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt_pr_company_detail->bind_param("sssssssssssssssiii",$source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$state,$city,$taluka,$area,$industrial_estate,$inq_submit,$industrial_estate_id,$user_id,$company_result['id']);
    $Resp=$stmt_pr_company_detail->execute();
    $last_insert_company_id = mysqli_insert_id($obj->con1);
    $stmt_pr_company_detail->close();
    
    $stmt_pr_company_plot = $obj->con1->prepare("UPDATE `pr_company_plots` SET `company_id`=? WHERE `pid`=?");
    $stmt_pr_company_plot->bind_param("ii",$last_insert_company_id,$pr_company_plot['pid']);
    $Resp=$stmt_pr_company_plot->execute();
    $stmt_pr_company_plot->close();

    if(!$Resp)
    {
      throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
    }
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
	  setcookie("msg", "data",time()+3600,"/");
    header("location:company_add_plot.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:company_add_plot.php");
  }
}

// insert data for select company first
if(isset($_REQUEST['btnsubmit_comp']))
{
  $industrial_estate_id = $_REQUEST['industrial_estate_id_comp'];
  $plot_no = $_REQUEST['plot_no_comp'];
  $road_no = $_REQUEST['road_no_comp'];
  $floor = $_REQUEST['floor_comp'];
  $rawdata_id = $_REQUEST['rawdata_id_comp'];
  $plotting_pattern = $_REQUEST['plotting_pattern_comp'];

  $str_arr = explode (",", $floor); 
  $plot_rawdata_id = $str_arr[0];
  $floor_no = $str_arr[1];
  $road_number = ($road_no=="")?NULL:$road_no;

  $stmt_estate = $obj->con1->prepare("SELECT * FROM tbl_industrial_estate i1 WHERE id=?");
  $stmt_estate->bind_param("i",$industrial_estate_id);
  $stmt_estate->execute();
  $estate_res = $stmt_estate->get_result()->fetch_assoc();
  $stmt_estate->close();
    
  $stmt_company_list = $obj->con1->prepare("SELECT * FROM `tbl_tdrawdata` WHERE id=?");
  $stmt_company_list->bind_param("i",$rawdata_id);
  $stmt_company_list->execute();
  $company_result = $stmt_company_list->get_result()->fetch_assoc();
  $stmt_company_list->close();

  // add array of plot in company json
  $row_data_comp=json_decode($company_result["raw_data"]);
  $post_fields = $row_data_comp->post_fields;
  $post_fields->state = $estate_res['state_id'];
  $post_fields->city = $estate_res['city_id'];
  $post_fields->Taluka = $estate_res['taluka'];
  $post_fields->Area = $estate_res['area_id'];
  $post_fields->IndustrialEstate = $estate_res['industrial_estate'];
  $row_data_comp->bad_lead_reason = "";
  $row_data_comp->bad_lead_reason_remark = "";
  $row_data_comp->Image = "";
  $row_data_comp->Constitution = "";
  $row_data_comp->Status = "";
  $row_data_comp->plot_details = Array(
          Array(
            "Plot_No" => $plot_no,
            "Floor" => $floor_no,
            "Road_No" => $road_no,
            "Plot_Status" => "",
            "Plot_Id" => "1",
          ));
  $json_object = json_encode($row_data_comp);

  $post_fields_comp = $row_data_comp->post_fields;
  $source = $post_fields_comp->source;
  $source_name = $post_fields_comp->Source_Name;
  $contact_person = $post_fields_comp->Contact_Name;
  $contact_no = $post_fields_comp->Mobile_No;
  $firm_name = $post_fields_comp->Firm_Name;
  $gst_no = $post_fields_comp->GST_No;
  $category = $post_fields_comp->Category;
  $segment = $post_fields_comp->Segment;
  $premise = $post_fields_comp->Premise;
  $inq_submit = "Submit";
  
  // get id of table pr_company_plot 
  if($plotting_pattern=="Series"){
    $stmt_company_plot = $obj->con1->prepare("SELECT pid, company_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=? ");
    $stmt_company_plot->bind_param("sii",$plot_no,$floor_no,$industrial_estate_id);
  }
  else if($plotting_pattern=="Road"){
    $stmt_company_plot = $obj->con1->prepare("SELECT pid, company_id FROM `pr_company_plots` WHERE plot_no=? and floor=? and industrial_estate_id=? and road_no=?");
    $stmt_company_plot->bind_param("siis",$plot_no,$floor_no,$industrial_estate_id,$road_no);
  }
  $stmt_company_plot->execute();
  $pr_company_plot = $stmt_company_plot->get_result()->fetch_assoc();
  $stmt_company_plot->close();

  try
  {
    // update json of company
    $stmt = $obj->con1->prepare("UPDATE `tbl_tdrawdata` set raw_data=? where id=?");
    $stmt->bind_param("si",$json_object,$company_result['id']);
    $Resp=$stmt->execute();
    $stmt->close();

    // delete blank json of plot
    $stmt_del = $obj->con1->prepare("DELETE from `tbl_tdrawdata` where id=?");
    $stmt_del->bind_param("i",$plot_rawdata_id);
    $Resp=$stmt_del->execute();
    $stmt_del->close();

    // insert into pr_company_details and pr_company_plots
    $stmt_pr_company_detail = $obj->con1->prepare("INSERT INTO `pr_company_details`(`source`, `source_name`, `contact_name`, `mobile_no`, `firm_name`, `gst_no`, `category`, `segment`, `premise`, `state`, `city`, `taluka`, `area`, `industrial_estate`, `inq_submit`, `industrial_estate_id`, `user_id`, `rawdata_id`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt_pr_company_detail->bind_param("sssssssssssssssiii",$source,$source_name,$contact_person,$contact_no,$firm_name,$gst_no,$category,$segment,$premise,$estate_res['state_id'],$estate_res['city_id'],$estate_res['taluka'],$estate_res['area_id'],$estate_res['industrial_estate'],$inq_submit,$industrial_estate_id,$user_id,$company_result['id']);
    $Resp=$stmt_pr_company_detail->execute();
    $last_insert_company_id = mysqli_insert_id($obj->con1);
    $stmt_pr_company_detail->close();
    
    $stmt_pr_company_plot = $obj->con1->prepare("UPDATE `pr_company_plots` SET `company_id`=? WHERE `pid`=?");
    $stmt_pr_company_plot->bind_param("ii",$last_insert_company_id,$pr_company_plot['pid']);
    $Resp=$stmt_pr_company_plot->execute();
    $stmt_pr_company_plot->close();

    if(!$Resp)
    {
      throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
    }
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  /*if($Resp)
  {
    setcookie("msg", "data",time()+3600,"/");
    header("location:company_add_plot.php");
  }
  else
  {
    setcookie("msg", "fail",time()+3600,"/");
    header("location:company_add_plot.php");
  }*/
}

?>

<h4 class="fw-bold py-3 mb-4">Add Plotting In Company</h4>

<?php 
if(isset($_COOKIE["msg"]) )
{

  if($_COOKIE['msg']=="data")
  {

  ?>
  <div class="alert alert-primary alert-dismissible" role="alert">
    Data added succesfully
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
    </button>
  </div>
  <script type="text/javascript">eraseCookie("msg")</script>
  <?php
  }
  if($_COOKIE['msg']=="update")
  {

  ?>
  <div class="alert alert-primary alert-dismissible" role="alert">
    Data updated succesfully
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
    </button>
  </div>
  <script type="text/javascript">eraseCookie("msg")</script>
  <?php
  }
  if($_COOKIE['msg']=="data_del")
  {

  ?>
  <div class="alert alert-primary alert-dismissible" role="alert">
    Data deleted succesfully
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
    </button>
  </div>
  <script type="text/javascript">eraseCookie("msg")</script>
  <?php
  }
  if($_COOKIE['msg']=="fail")
  {
  ?>

  <div class="alert alert-danger alert-dismissible" role="alert">
    An error occured! Try again.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
    </button>
  </div>
  <script type="text/javascript">eraseCookie("msg")</script>
  <?php
  }
}
  if(isset($_COOKIE["sql_error"]))
  {
    ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
      <?php echo urldecode($_COOKIE['sql_error'])?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
      </button>
    </div>

    <script type="text/javascript">eraseCookie("sql_error")</script>
    <?php
  }
?>


  <!-- Basic Layout -->
  <div class="row">
    <div class="col-xl">
      <div class="card mb-4">
        <!-- <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"></h5>
        </div> -->
        <div class="card-body">
          <form method="post" >
           
            <div class="mb-3">
              <label class="form-label" for="select_type">Plot Status</label>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="select_type" id="select_estate_first" value="select_estate_first" onchange="changeForm()" checked>
                <label class="form-check-label" for="inlineRadio1">Select Estate First</label>
              </div>
              <div class="form-check form-check-inline mt-3">
                <input class="form-check-input" type="radio" name="select_type" id="select_company_first" value="select_company_first" onchange="changeForm()">
                <label class="form-check-label" for="inlineRadio1">Select Company First</label>
              </div>
            </div>
              
            <!-- div for selecting estate first -->
            <div id="select_estate_first_div">
              <div class="mb-3">
                <label class="form-label" for="industrial_estate_id_est">Industrial Estate</label>
                <select name="industrial_estate_id_est" id="industrial_estate_id_est" class="form-control" onchange="getCompanyName(this.value)" required>
                    <option value="">Select Industrial Estate</option>
              <?php
                  $stmt_estate_list = $obj->con1->prepare("SELECT i1.* from (SELECT DISTINCT json_unquote(raw_data->'$.post_fields.Taluka') as taluka, json_unquote(raw_data->'$.post_fields.Area') as area, json_unquote(raw_data->'$.post_fields.IndustrialEstate') as ind_estate FROM tbl_tdrawdata WHERE JSON_CONTAINS_PATH(raw_data, 'one', '$.plot_details') = 0 and raw_data->'$.post_fields.IndustrialEstate'!='') tbl1, tbl_industrial_estate i1 where tbl1.taluka=i1.taluka and tbl1.area=i1.area_id and tbl1.ind_estate=i1.industrial_estate order by i1.area_id,i1.taluka,i1.industrial_estate");
                  $stmt_estate_list->execute();
                  $estate_result = $stmt_estate_list->get_result();
                  $stmt_estate_list->close();

                  while($estate = mysqli_fetch_array($estate_result)){ 
              ?>
                    <option value="<?php echo $estate['id'] ?>"><?php echo $estate['industrial_estate']." - ".$estate['taluka']." - ".$estate['area_id'] ?> </option>
              <?php } ?>
                  </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="rawdata_id_est">Company</label>
                <select name="rawdata_id_est" id="rawdata_id_est" onchange="get_companyStatus(this.value)" class="form-control" required>
                    <option value="">Select Company</option>
                  </select>
              </div>
              <div id="company_status_est" class="text-success"></div>

              <input type="hidden" class="form-control" name="plotting_pattern_est" id="plotting_pattern_est" required />
              <div id="estate_alert_div_est" class="text-danger"></div>

              <div id="plotting_div_est" hidden>
                <div class="row">
                  <div class="mb-3" id="road_list_div_est" hidden>
                    <label class="form-label" for="road_no_est">Road No.</label>
                    <select name="road_no_est" id="road_no_est" class="form-control" onchange="getRoadPlots_companyPlot(this.value,industrial_estate_id_est.value)">
                      <option value="">Select Road No.</option>
                    </select>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="plot_no_est">Plot No.</label>
                  <select name="plot_no_est" id="plot_no_est" onchange="getFloor_companyPlot(this.value,industrial_estate_id_est.value)" class="form-control" required>
                    <option value="">Select Plot No.</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="floor_est">Floor No.</label>
                  <select name="floor_est" id="floor_est" class="form-control" required>
                    <option value="0">Ground Floor</option>
                  </select>
                </div>
              </div>

              <button type="submit" name="btnsubmit_est" id="btnsubmit_est" class="btn btn-primary">Save</button>
              <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button>

            </div>


            <!-- div for selecting company first -->
            <div id="select_company_first_div" hidden>
              <div class="mb-3">
                <label class="form-label" for="rawdata_id_comp">Company</label>
                <select name="rawdata_id_comp" id="rawdata_id_comp" onchange="get_companyStatus(this.value)" class="form-control" required>
                    <option value="">Select Company</option>
                <?php 
                    $stmt_company_list = $obj->con1->prepare("SELECT id, json_unquote(raw_data->'$.post_fields.IndustrialEstate') as ind_estate, json_unquote(raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(raw_data->'$.post_fields.Contact_Name') as contact_name, json_unquote(raw_data->'$.post_fields.Mobile_No') as mobile_no FROM tbl_tdrawdata WHERE JSON_CONTAINS_PATH(raw_data, 'one', '$.plot_details') = 0 and raw_data->'$.post_fields.IndustrialEstate'=''");
                    $stmt_company_list->execute();
                    $company_result = $stmt_company_list->get_result();
                    $stmt_company_list->close();

                    while($company = mysqli_fetch_array($company_result)){ 
                ?>
                    <option value="<?php echo $company['id'] ?>"><?php echo $company['firm_name']." ( ".$company['contact_name']." - ".$company['mobile_no']." ) " ?></option>
                <?php } ?>
                  </select>
              </div>
              <div id="company_status_comp" class="text-success"></div>

              <div class="mb-3">
                <label class="form-label" for="state_comp">State</label>
                <select name="state_comp" id="state_comp" class="form-control" required>
          <?php while($state_list=mysqli_fetch_array($state_result)){ ?>
              <option value="<?php echo $state_list["state_id"] ?>" selected><?php echo $state_list["state_id"] ?></option>
          <?php } ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="city_comp">City</label>
                <select name="city_comp" id="city_comp" class="form-control" required>
          <?php while($city_list=mysqli_fetch_array($city_result)){ ?>
              <option value="<?php echo $city_list["city_id"] ?>" selected><?php echo $city_list["city_id"] ?></option>
          <?php } ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="taluka_comp">Taluka</label>
                <select name="taluka_comp" id="taluka_comp" onchange="areaList_tbl_indestate(this.value,city_comp.value,state_comp.value)" class="form-control" required>
                  <option value="">Select Taluka</option>
          <?php while($taluka_list=mysqli_fetch_array($taluka_result)){ ?>
              <option value="<?php echo $taluka_list["taluka"] ?>"><?php echo $taluka_list["taluka"] ?></option>
          <?php } ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="area_comp">Area</label>
                <select name="area_comp" id="area_comp" onchange="estateList_tbl_indestate(this.value,taluka_comp.value,city_comp.value,state_comp.value)" class="form-control" required>
                  <option value="">Select Area</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label" for="industrial_estate_id_comp">Industrial Estate</label>
                <select name="industrial_estate_id_comp" id="industrial_estate_id_comp" class="form-control" onchange="getPlot_companyPlot(this.value)" required>
                    <option value="">Select Industrial Estate</option>
                  </select>
              </div>

              <input type="hidden" class="form-control" name="plotting_pattern_comp" id="plotting_pattern_comp" required />
              <div id="estate_alert_div_comp" class="text-danger"></div>

              <div id="plotting_div_comp" hidden>
                <div class="row">
                  <div class="mb-3" id="road_list_div_comp" hidden>
                    <label class="form-label" for="road_no_comp">Road No.</label>
                    <select name="road_no_comp" id="road_no_comp" class="form-control" onchange="getRoadPlots_companyPlot(this.value,industrial_estate_id_comp.value)">
                      <option value="">Select Road No.</option>
                    </select>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="plot_no_comp">Plot No.</label>
                  <select name="plot_no_comp" id="plot_no_comp" onchange="getFloor_companyPlot(this.value,industrial_estate_id_comp.value)" class="form-control" required>
                    <option value="">Select Plot No.</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label" for="floor_comp">Floor No.</label>
                  <select name="floor_comp" id="floor_comp" class="form-control" required>
                    <option value="0">Ground Floor</option>
                  </select>
                </div>
              </div>
            
              <button type="submit" name="btnsubmit_comp" id="btnsubmit_comp" class="btn btn-primary">Save</button>
              <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button>

            </div>

            
            

          </form>
        </div>
      </div>
    </div>
    
  </div>


<!-- / Content -->
<script type="text/javascript">
  function changeForm() {
    if($('#select_company_first').is(':checked')) { 
      $('#select_company_first_div').removeAttr('hidden');
      $('#select_estate_first_div').attr('hidden',true);
      $("[id$='_comp']:not([id='road_no_comp'])").attr("required", true);
      $("[id$='_est']").removeAttr('required');
    }
    else if($('#select_estate_first').is(':checked')) { 
      $('#select_estate_first_div').removeAttr('hidden');
      $('#select_company_first_div').attr('hidden',true);
      $("[id$='_est']:not([id='road_no_est'])").attr("required", true);
      $("[id$='_comp']").removeAttr('required');
    }
  }

  function getCompanyName(ind_estate_id) {
    $.ajax({
      async: false,
      type: "POST",
      url: "ajaxdata.php?action=get_company_name",
      data: "ind_estate_id="+ind_estate_id,
      cache: false,
      success: function(result){
        $('#rawdata_id_est').html('');
        $('#rawdata_id_est').append(result);
        getPlot_companyPlot(ind_estate_id);
      }
    });
  }

  function get_companyStatus(company_id)  {
    if($('#select_company_first').is(':checked')) { 
      suffix = '_comp';
    }
    else if($('#select_estate_first').is(':checked')) { 
      suffix = '_est';
    }

    if(company_id!=""){
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=get_companyStatus",
        data: "company_id="+company_id,
        cache: false,
        success: function(result){
          $('#company_status'+suffix).html('');
          $('#company_status'+suffix).append(result);
        }
      });
    }
    else{
      $('#company_status'+suffix).html('');
    }
  }

  function getPlot_companyPlot(estate_id){
    if(estate_id!=""){
      if($('#select_company_first').is(':checked')) { 
        suffix = '_comp';
      }
      else if($('#select_estate_first').is(':checked')) { 
        suffix = '_est';
      }
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getPlot_companyPlot",
        data: "estate_id="+estate_id,
        cache: false,
        success: function(result){
          if(result=='false'){
            $('#estate_alert_div'+suffix).html('Please Enter Plotting First');
            document.getElementById('btnsubmit'+suffix).disabled = true;
            $('#plotting_div'+suffix).attr('hidden',true);
          }
          else{
            $('#estate_alert_div'+suffix).html('');
            document.getElementById('btnsubmit'+suffix).disabled = false;
            var data = result.split("@@@@@");
            plotting_pattern = data[1];
            $('#plotting_pattern'+suffix).val(plotting_pattern);
            $('#plotting_div'+suffix).attr('hidden',false);
            
            if(plotting_pattern=="Road"){
              $('#road_list_div'+suffix).removeAttr("hidden");
              $('#road_no'+suffix).html('');
              $('#road_no'+suffix).append(data[0]);
              $('#plot_no'+suffix).html('');
              $('#plot_no'+suffix).append('<option value="">Select Plot No.</option>');
              $('#floor'+suffix).html('');
              $('#floor'+suffix).append('<option value="0">Select Floor No.</option>');
            } 
            else if(plotting_pattern=="Series"){
              $('#road_list_div'+suffix).attr("hidden",true);
              $('#road_no'+suffix).val("");
              $('#plot_no'+suffix).html('');
              $('#plot_no'+suffix).append(data[0]);
              $('#floor'+suffix).html('');
              $('#floor'+suffix).append('<option value="">Select Floor No.</option>');
            }
          }
        }
      });
    }
  }

  function getRoadPlots_companyPlot(road_no,estate_id){
    if(road_no!=""){
      if($('#select_company_first').is(':checked')) { 
        suffix = '_comp';
      }
      else if($('#select_estate_first').is(':checked')) { 
        suffix = '_est';
      }
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getRoadPlots_companyPlot",
        data: "road_no="+road_no+"&estate_id="+estate_id,
        cache: false,
        success: function(result){
          $('#plot_no'+suffix).html('');
          $('#plot_no'+suffix).append(result);
          $('#floor'+suffix).html('');
          $('#floor'+suffix).append('<option value="">Select Floor No.</option>');
        }
      });
    }
    else{
      $('#floor'+suffix).html('');
      $('#floor'+suffix).append('<option value="">Select Floor No.</option>');
    }
  }

  function getFloor_companyPlot(plot_no,estate_id){
    if(plot_no!=""){
      if($('#select_company_first').is(':checked')) { 
        suffix = '_comp';
      }
      else if($('#select_estate_first').is(':checked')) { 
        suffix = '_est';
      }
      road_no = $('#road_no'+suffix).val();
      $.ajax({
        async: false,
        type: "POST",
        url: "ajaxdata.php?action=getFloor_companyPlot",
        data: "plot_no="+plot_no+"&estate_id="+estate_id+"&road_no="+road_no,
        cache: false,
        success: function(result){
          alert(result);
          $('#floor'+suffix).html('');
          $('#floor'+suffix).append(result);
        }
      });
    }
    else{
      $('#floor'+suffix).html('');
      $('#floor'+suffix).append('<option value="">Select Floor No.</option>');
    }
  }

  function areaList_tbl_indestate(taluka,city,state){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=areaList_tbl_indestate",
      data: "taluka="+taluka+"&city="+city+"&state_name="+state,
      cache: false,
      success: function(result){
        $('#area_comp').html('');
        $('#area_comp').append(result);
      }
    });
  }

  function estateList_tbl_indestate(area,taluka,city,state){
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=estateList_tbl_indestate",
      data: "area="+area+"&taluka="+taluka+"&city="+city+"&state_name="+state,
      cache: false,
      success: function(result){
        $('#industrial_estate_id_comp').html('');
        $('#industrial_estate_id_comp').append(result);
      }
    });
  }
</script>
<?php 
  include("footer.php");
?>