<?php
  include("header.php");

$cid = $_COOKIE['cid'];
$industrial_estate_id = $_COOKIE["estate_id"];
$stmt_comp = $obj->con1->prepare("select raw_data from tbl_tdrawdata where id=?");
$stmt_comp->bind_param("i",$cid);
$stmt_comp->execute();
$comp_result = $stmt_comp->get_result();
$stmt_comp->close();
$comp = mysqli_fetch_array($comp_result);
$row_data=json_decode($comp["raw_data"]);
$post_fields=$row_data->post_fields;

$stmt_plot = $obj->con1->prepare("SELECT * FROM `pr_add_industrialestate_details` WHERE industrial_estate_id=?");
$stmt_plot->bind_param("i",$industrial_estate_id);
$stmt_plot->execute();
$plot_result = $stmt_plot->get_result();
$stmt_plot->close();
$plotting_pattern = mysqli_fetch_array($plot_result);

$stmt_plot_list = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata WHERE lower(raw_data->'$.post_fields.Taluka') like '%".strtolower($post_fields->Taluka)."%' and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($post_fields->IndustrialEstate)."%' and raw_data->'$.post_fields.GST_No' = ''");
$stmt_plot_list->execute();
$plot_list_result = $stmt_plot_list->get_result();
$stmt_plot_list->close();

// insert data
if(isset($_REQUEST['btnsubmit']))
{
  if($_REQUEST['op']=="op"){
    $plot_no = $_REQUEST['other_plot_no'];
  } else{
    $plot_no = $_REQUEST['plot_no'];
  }
  $floor = $_REQUEST['floor'];
  $road_no = $_REQUEST['road_no'];
  $plot_status = $_REQUEST['plot_status'];

  $stmt_slist = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
  $stmt_slist->bind_param("i",$cid);
  $stmt_slist->execute();
  $res = $stmt_slist->get_result();
  $stmt_slist->close();

  $data=mysqli_fetch_array($res);
  $row_data=json_decode($data["raw_data"]);

  if(isset($row_data->plot_details)){
    $plot_details = $row_data->plot_details;
    $arr_count = count($plot_details);
    $last_plot_id = $plot_details[$arr_count-1]->Plot_Id;

    $new_plot_detail=Array(
      "Plot_No" => $plot_no,
      "Floor" => $floor,
      "Road_No" => $road_no,
      "Plot_Status" => $plot_status,
      "Plot_Id" => $last_plot_id+1,
    );  
    array_push($row_data->plot_details, $new_plot_detail);
  }
  else{
    $row_data->plot_details=Array(
      Array(
        "Plot_No" => $plot_no,
        "Floor" => $floor,
        "Road_No" => $road_no,
        "Plot_Status" => $plot_status,
        "Plot_Id" => "1",
      ),
    );  
  }
    
  $json_object = json_encode($row_data);

  $stmt_plot_search = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata WHERE raw_data->'$.plot_details[*].Plot_No' like '%".$plot_no."%' and raw_data->'$.plot_details[*].Road_No' like '%".$road_no."%' and raw_data->'$.post_fields.IndustrialEstate' like '%".$post_fields->IndustrialEstate."%' and raw_data->'$.post_fields.Area' like '%".$post_fields->Area."%'");
  $stmt_plot_search->execute();
  $plot_search = $stmt_plot_search->get_result();
  $stmt_plot_search->close();

  try
  {
    $stmt_plot = $obj->con1->prepare("update tbl_tdrawdata set raw_data=? where id=?");
    $stmt_plot->bind_param("si",$json_object,$cid);
    $Resp=$stmt_plot->execute();

    if(mysqli_num_rows($plot_search)>0){
      $plot_search_res = mysqli_fetch_array($plot_search);
      $stmt_del = $obj->con1->prepare("delete from tbl_tdrawdata where id=?");
      $stmt_del->bind_param("i",$plot_search_res['id']);
      $Resp=$stmt_del->execute();    
    }

    if(!$Resp)
    {
      throw new Exception("Problem in adding! ". strtok($obj->con1-> error,  '('));
    }
    $stmt_plot->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
	  setcookie("msg", "data",time()+3600,"/");
    header("location:plot_details.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:plot_details.php");
  }
}

if(isset($_REQUEST['btnupdate']))
{
  $plot_no = $_REQUEST['plot_no_upd'];
  if($_REQUEST['floor_upd']=='Ground Floor'){
    $floor = '0';
  }
  else{
    $floor = $_REQUEST['floor_upd'];
  }
  $road_no = $_REQUEST['road_no_upd'];
  $plot_status = $_REQUEST['plot_status'];
  $id = $_REQUEST['ttId'];
  $plot_id = $_REQUEST['plot_id'];

  $stmt_slist = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
  $stmt_slist->bind_param("i",$cid);
  $stmt_slist->execute();
  $res = $stmt_slist->get_result();
  $stmt_slist->close();

  $data=mysqli_fetch_array($res);
  $row_data=json_decode($data["raw_data"]);

  $plot_details=$row_data->plot_details;

  $i=0;
  foreach($plot_details as $pd){
    if($pd->Plot_Id==$plot_id){
      $plot_index=$i;
      break;
    }
    $i++;
  }
  
  $plot_details[$plot_index]->Plot_No = $plot_no;
  $plot_details[$plot_index]->Floor = $floor;
  $plot_details[$plot_index]->Road_No = $road_no;
  $plot_details[$plot_index]->Plot_Status = $plot_status;

  $json_object = json_encode($row_data);
  
  try
  {
    $stmt = $obj->con1->prepare("update tbl_tdrawdata set raw_data=? where id=?");
    $stmt->bind_param("si", $json_object,$id);
    $Resp=$stmt->execute();
    if(!$Resp)
    {
      throw new Exception("Problem in updating! ". strtok($obj->con1-> error,  '('));
    }
    $stmt->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
	  setcookie("msg", "update",time()+3600,"/");
      header("location:plot_details.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
      header("location:plot_details.php");
  }
}

// delete data
if(isset($_REQUEST["flg"]) && $_REQUEST["flg"]=="del")
{
  $id = $_REQUEST['n_id'];
  $plot_id = $_REQUEST['plot_id'];

  $stmt_slist = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
  $stmt_slist->bind_param("i",$id);
  $stmt_slist->execute();
  $res = $stmt_slist->get_result();
  $stmt_slist->close();

  $data=mysqli_fetch_array($res);
  $row_data=json_decode($data["raw_data"]);
  
  $plot_details=$row_data->plot_details;

  $i=0;
  foreach($plot_details as $pd){
    if($pd->Plot_Id==$plot_id){
      $plot_index=$i;
      break;
    }
    $i++;
  }
  
  array_splice($row_data->plot_details, $plot_index,1);

  $json_object = json_encode($row_data);

  try
  {
    $stmt_del = $obj->con1->prepare("update tbl_tdrawdata set raw_data=? where id=?");
    $stmt_del->bind_param("si", $json_object,$id);
  	$Resp=$stmt_del->execute();
    if(!$Resp)
    {
      throw new Exception("Problem in deleting! ". strtok($obj->con1-> error,  '('));
    }
    $stmt_del->close();
  } 
  catch(\Exception  $e) {
    setcookie("sql_error", urlencode($e->getMessage()),time()+3600,"/");
  }

  if($Resp)
  {
  	setcookie("msg", "data_del",time()+3600,"/");
    header("location:plot_details.php");
  }
  else
  {
	  setcookie("msg", "fail",time()+3600,"/");
    header("location:plot_details.php");
  }
} 

?>

<h4 class="fw-bold py-3 mb-4"><?php echo $post_fields->Firm_Name ?>'s Plot Details</h4>

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
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add Plot Details</h5>
            
          </div>
          <div class="card-body">
            <form method="post" enctype="multipart/form-data">
             
              <input type="hidden" name="ttId" id="ttId">
              <input type="hidden" name="plot_id" id="plot_id">
                
              <div id="plot_insert">
              <div class="mb-3" id="plot_div">
                <label class="form-label" for="basic-default-fullname">Plot No.</label>
                <select name="plot_no" id="plot_no" class="form-control" required>
                  <option value="">Select Plot No.</option>
            <?php while($plot = mysqli_fetch_array($plot_list_result)){  
                    $raw_data=json_decode($plot["raw_data"]);
                    $plot_details=$raw_data->plot_details;
                    asort($plot_details);

                    foreach ($plot_details as $pd) {
                      if($pd->Floor == '0'){
            ?>
                  <option value="<?php echo $pd->Plot_No ?>"><?php echo $pd->Plot_No ?></option>
            <?php } } } ?>
                </select>
              </div>

              <div>
                <input type="checkbox" name="op" id="op" value="op" onclick="otherPlot()" /> Other Additional Plot
              </div>

              <div class="mb-3" id="other_plot_div" hidden>
                <label class="form-label" for="basic-default-fullname">Plot No.</label>
                <input type="text" class="form-control" name="other_plot_no" id="other_plot_no" />
              </div>

              <div class="mb-3">
                <label class="form-label" for="basic-default-fullname">Floor No.</label>
                <select name="floor" id="floor" class="form-control" required>
                  <option value="">Select Floor No.</option>
                  <option value="0">Ground Floor</option>
          <?php for($i=1;$i<=10;$i++){ ?>
                  <option value="<?php echo $i ?>"><?php echo $i ?></option>
          <?php } ?>
                </select>
              </div>

          <?php if($plotting_pattern['plotting_pattern']=='Road'){ ?>
              <div class="mb-3">
                <label class="form-label" for="basic-default-fullname">Road No.</label>
                <input type="text" class="form-control" name="road_no" id="road_no" />
              </div>
          <?php } ?>

          </div>

            <div id="plot_update" hidden>
              <div class="mb-3">
                <label class="form-label" for="basic-default-fullname">Plot No.</label>
                <input type="text" class="form-control" name="plot_no_upd" id="plot_no_upd" readonly />
              </div>

              <div class="mb-3">
                <label class="form-label" for="basic-default-fullname">Floor No.</label>
                <input type="text" class="form-control" name="floor_upd" id="floor_upd" readonly />
              </div>

          <?php if($plotting_pattern['plotting_pattern']=='Road'){ ?>
              <div class="mb-3">
                <label class="form-label" for="basic-default-fullname">Road No.</label>
                <input type="text" class="form-control" name="road_no_upd" id="road_no_upd" readonly />
              </div>
          <?php } ?>

          </div>

              <div class="mb-3">
                <label class="form-label" for="basic-default-fullname">Plot Status</label>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="plot_status" id="open_plot" value="Open Plot" required checked>
                  <label class="form-check-label" for="inlineRadio1">Open Plot</label>
                </div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="plot_status" id="under_construction" value="Under Construction" required>
                  <label class="form-check-label" for="inlineRadio1">Under Construction</label>
                </div>
                <div class="form-check form-check-inline mt-3">
                  <input class="form-check-input" type="radio" name="plot_status" id="constructed" value="Constructed" required>
                  <label class="form-check-label" for="inlineRadio1">Constructed</label>
                </div>
              </div>

              <button type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary">Save</button>
          
	            <button type="submit" name="btnupdate" id="btnupdate" class="btn btn-primary " hidden>Update</button>
          
              <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary" onclick="window.location.reload()">Cancel</button>

            </form>
          </div>
        </div>
      </div>      
    </div>

    <!-- grid -->

    <!-- Basic Bootstrap Table -->
    <div class="card">
      <h5 class="card-header">Records</h5>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>Firm Name</th>
              <th>Plot No</th>
              <th>Floor</th>
              <th>Road No</th>
              <th>Plot Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php 
              $stmt_list = $obj->con1->prepare("select * from tbl_tdrawdata where id=?");
              $stmt_list->bind_param("i",$cid);
              $stmt_list->execute();
              $result = $stmt_list->get_result();
              
              $stmt_list->close();
              $i=1;
              while($data=mysqli_fetch_array($result))
              {
                $row_data=json_decode($data["raw_data"]);
                $post_fields=$row_data->post_fields;
                if(isset($row_data->plot_details)){
                  //$j=0;
                  $plot_details=$row_data->plot_details;  
                  asort($plot_details);

                foreach ($plot_details as $pd) {
            ?>

            <tr>
              <td><?php echo $i?></td>
              <td><?php echo $post_fields->Firm_Name ?></td>
              <td><?php echo $pd->Plot_No ?></td>
              <td><?php if($pd->Floor=='0'){ echo 'Ground Floor'; } else{ echo $pd->Floor; } ?></td>
              <td><?php if($pd->Road_No==null){ echo '-';} else{ echo $pd->Road_No; } ?></td>
              <td><?php echo $pd->Plot_Status ?></td>
              <td>
              	<a href="javascript:editdata('<?php echo $data["id"]?>','<?php echo $pd->Plot_Id ?>','<?php echo base64_encode($pd->Plot_No)?>','<?php echo base64_encode($pd->Floor)?>','<?php echo base64_encode($pd->Road_No)?>','<?php echo $pd->Plot_Status ?>');"><i class="bx bx-edit-alt me-1"></i> </a>
                <a href="javascript:deletedata('<?php echo $data["id"]?>','<?php echo $pd->Plot_Id ?>');"><i class="bx bx-trash me-1"></i> </a>
                <a href="javascript:viewdata('<?php echo $data["id"]?>','<?php echo $pd->Plot_Id ?>','<?php echo base64_encode($pd->Plot_No)?>','<?php echo base64_encode($pd->Floor)?>','<?php echo base64_encode($pd->Road_No)?>','<?php echo $pd->Plot_Status ?>');">View</a>
              </td>
            </tr>
            <?php
                    $i++;
                    //$j++;
                  }
                }
              }
            ?>
            
          </tbody>
        </table>
      </div>
    </div>
    <!--/ Basic Bootstrap Table -->

    <!-- / grid -->

    <!-- / Content -->
<script type="text/javascript">

  // window.addEventListener('beforeunload', function (event)
  // {
  //   //history.back();
  //   window.href = "company_plot_new.php";
  // }

/*   function get_roadno(plot_no,taluka,ind_estate){
    //onchange="get_roadno(this.value,'<?php echo $post_fields->Taluka ?>','<?php echo $post_fields->IndustrialEstate ?>')"
    $.ajax({
      async: true,
      type: "POST",
      url: "ajaxdata.php?action=get_roadno",
      data: "taluka="+taluka+"&ind_estate="+ind_estate+"&plot_no="+plot_no,
      cache: false,
      success: function(result){
        //$('#industrial_estate').html('');
        //$('#industrial_estate').append(result);
        $('#road_no').val(result);
        
      }
    });
  } */ 

  function otherPlot(){
    if($('#op').is(':checked')){
      $('#other_plot_div').removeAttr("hidden");
      $('#plot_div').attr("hidden",true);
      $('#other_plot_no').attr("required",true);
      $('#plot_no').removeAttr("required");
    }
    else{
      $('#other_plot_div').attr("hidden",true);
      $('#plot_div').removeAttr("hidden"); 
      $('#plot_no').attr("required",true);
      $('#other_plot_no').removeAttr("required");
    }
  } 

  function deletedata(id,plot_id) {
    if(confirm("Are you sure to DELETE data?")) {
        var loc = "plot_details.php?flg=del&n_id=" + id + "&plot_id=" + plot_id;
        window.location = loc;
    }
  }
  function editdata(id,plot_id,plot_no,floor,road_no,plot_status) {    
    $('#plot_no').focus();   
   	$('#ttId').val(id);
    $('#plot_id').val(plot_id);
		$('#plot_no_upd').val(atob(plot_no));
    if(atob(floor)=='0'){
      $('#floor_upd').val('Ground Floor');
    }
    else{
      $('#floor_upd').val(atob(floor));
    }
    
    $('#road_no_upd').val(atob(road_no));

    $('#plot_no').removeAttr('required');
    $('#floor').removeAttr('required');
    $('#road_no').removeAttr('required');
    $('#plot_update').removeAttr('hidden');
    $('#plot_insert').attr('hidden',true);

    if(plot_status=="Open Plot"){
     $('#open_plot').attr("checked","checked"); 
    }
    else if(plot_status=="Under Construction"){
     $('#under_construction').attr("checked","checked"); 
    }
    else if(plot_status=="Constructed"){
     $('#constructed').attr("checked","checked"); 
    }

		$('#btnsubmit').attr('hidden',true);
    $('#btnupdate').removeAttr('hidden');
		$('#btnsubmit').attr('disabled',true);
  }
  function viewdata(id,plot_id,plot_no,floor,road_no,plot_status) {
    $('#plot_no').focus();   
    $('#ttId').val(id);
    $('#plot_id').val(plot_id);
    $('#plot_no_upd').val(atob(plot_no));
    if(atob(floor)=='0'){
      $('#floor_upd').val('Ground Floor');
    }
    else{
      $('#floor_upd').val(atob(floor));
    }
    
    $('#road_no_upd').val(atob(road_no));

    $('#plot_no').removeAttr('required');
    $('#floor').removeAttr('required');
    $('#road_no').removeAttr('required');
    $('#plot_update').removeAttr('hidden');
    $('#plot_insert').attr('hidden',true);

    if(plot_status=="Open Plot"){
     $('#open_plot').attr("checked","checked"); 
    }
    else if(plot_status=="Under Construction"){
     $('#under_construction').attr("checked","checked"); 
    }
    else if(plot_status=="Constructed"){
     $('#constructed').attr("checked","checked"); 
    }
			
		$('#btnsubmit').attr('hidden',true);
    $('#btnupdate').attr('hidden',true);
		$('#btnsubmit').attr('disabled',true);
		$('#btnupdate').attr('disabled',true);
  }
</script>
<?php 
  include("footer.php");
?>