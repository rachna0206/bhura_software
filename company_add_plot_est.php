<?php
  include("header.php");

?>

<h4 class="fw-bold py-3 mb-4">Add Plotting in Company (With Industrial Estate)</h4>

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

    <!-- grid -->

<!-- Modal -->
<!-- <div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCenterTitle">Estate Plotting</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div id="plotting_modal"></div>
    </div>
  </div>
</div> -->

<!-- /modal-->


    <!-- Basic Bootstrap Table -->
    <div class="card">
      <h5 class="card-header">Records (Estate)</h5>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>Taluka</th>
              <th>Area</th>
              <th>Industrial Estate</th>
              <th>Company Name</th>
              <th>Employee Name</th>
              <th>Factory Address</th>
              <th>Status</th>
              <th>Action</th>  
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php

              if(in_array($user_id, $admin)){
                $stmt_list = $obj->con1->prepare("SELECT i1.id as estate_id, tbl1.id as rawdata_id, tbl1.state, tbl1.city, tbl1.taluka, tbl1.area, tbl1.ind_estate, tbl1.firm_name, tbl1.factory_address from (SELECT id, json_unquote(raw_data->'$.post_fields.state') as state,json_unquote(raw_data->'$.post_fields.city') as city, json_unquote(raw_data->'$.post_fields.Taluka') as taluka, json_unquote(raw_data->'$.post_fields.Area') as area, json_unquote(raw_data->'$.post_fields.IndustrialEstate') as ind_estate, json_unquote(raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(raw_data->'$.post_fields.Factory_Address') as factory_address FROM tbl_tdrawdata WHERE JSON_CONTAINS_PATH(raw_data, 'one', '$.plot_details') = 0 and raw_data->'$.post_fields.IndustrialEstate'!='') tbl1, tbl_industrial_estate i1 where tbl1.taluka=i1.taluka and tbl1.area=i1.area_id and tbl1.ind_estate=i1.industrial_estate and tbl1.id not in (SELECT rawdata_id from pr_company_details)");
              }
              else{
                $stmt_list = $obj->con1->prepare("SELECT i1.id as estate_id, tbl1.id as rawdata_id, tbl1.state, tbl1.city, tbl1.taluka, tbl1.area, tbl1.ind_estate, tbl1.firm_name, tbl1.factory_address from (SELECT id, json_unquote(raw_data->'$.post_fields.state') as state,json_unquote(raw_data->'$.post_fields.city') as city, json_unquote(raw_data->'$.post_fields.Taluka') as taluka, json_unquote(raw_data->'$.post_fields.Area') as area, json_unquote(raw_data->'$.post_fields.IndustrialEstate') as ind_estate, json_unquote(raw_data->'$.post_fields.Firm_Name') as firm_name, json_unquote(raw_data->'$.post_fields.Factory_Address') as factory_address FROM tbl_tdrawdata WHERE JSON_CONTAINS_PATH(raw_data, 'one', '$.plot_details') = 0 and raw_data->'$.post_fields.IndustrialEstate'!='') tbl1, tbl_industrial_estate i1, assign_estate a1 where tbl1.taluka=i1.taluka and tbl1.area=i1.area_id and tbl1.ind_estate=i1.industrial_estate and a1.industrial_estate_id=i1.id and a1.employee_id=? and a1.start_dt<=curdate() and a1.end_dt>=curdate() and a1.action='company_entry' and tbl1.id not in (SELECT rawdata_id from pr_company_details)");
                $stmt_list->bind_param("i",$user_id);
              }
              $stmt_list->execute();
              $result = $stmt_list->get_result();
              $stmt_list->close();
              $i=1;

              while($data=mysqli_fetch_array($result))
              {
                $stmt_status = $obj->con1->prepare("SELECT stage FROM `tbl_tdrawassign` WHERE inq_id=? order by id desc LIMIT 1");
                $stmt_status->bind_param("i",$data['rawdata_id']);
                $stmt_status->execute();
                $result_status = $stmt_status->get_result()->fetch_assoc();
                $stmt_status->close();

                if($result_status["stage"]=='applicationstart' || $result_status["stage"]=='schemesstarted')
                {
                  $stmt_stage = $obj->con1->prepare("select a1.tatassign_id, a1.tatassign_status ,a1.tatassign_user_id,u1.name as emp_name from tbl_tdtatassign a1,tbl_users u1 where a1.tatassign_user_id=u1.id and  a1.tatassign_inq_id=?  order by a1.tatassign_id desc limit 1");
                  $stmt_stage->bind_param("i",$data["rawdata_id"]);
                  $stmt_stage->execute();
                  $stage_result = $stmt_stage->get_result()->fetch_assoc();
                  $stmt_stage->close();
                  $emp_name=$stage_result["emp_name"];
                }
                else
                {
                  $stmt_stage = $obj->con1->prepare("select r1.id, r1.stage ,r1.user_id,u1.name as emp_name from tbl_tdrawassign r1,tbl_users u1 where r1.user_id=u1.id and r1.inq_id=? order by r1.id desc limit 1; ");
                  $stmt_stage->bind_param("i",$data["rawdata_id"]);
                  $stmt_stage->execute();
                  $stage_result = $stmt_stage->get_result()->fetch_assoc();
                  $stmt_stage->close();
                  $emp_name=$stage_result["emp_name"];

                }

            ?>

            <tr>
              <td><?php echo $i?></td>
              <td><?php echo $data["taluka"] ?></td>
              <td><?php echo $data["area"] ?></td>
              <td><?php echo $data["ind_estate"] ?></td>
              <td><?php echo $data["firm_name"] ?></td>
              <td><?php echo $emp_name ?></td>
              <td><?php echo $data["factory_address"] ?></td>
              <td><?php if($result_status["stage"]=='lead'){
                        echo "Positive";
                  } else if($result_status["stage"]=='badlead'){
                        echo "Negative";
                  } else{
                        echo "Exisiting Client";
                  } ?></td>
              <td>
                <a href="javascript:editdata('<?php echo $data["estate_id"]?>','<?php echo $data["rawdata_id"]?>','<?php echo base64_encode($data["state"]) ?>','<?php echo base64_encode($data["city"]) ?>','<?php echo base64_encode($data["taluka"]) ?>','<?php echo base64_encode($data["area"]) ?>','<?php echo base64_encode($data["ind_estate"]) ?>','<?php echo base64_encode($data["firm_name"]) ?>','<?php echo $user_id ?>','<?php echo base64_encode($result_status["stage"]) ?>','<?php echo base64_encode($data["factory_address"]) ?>');"><i class="bx bx-edit-alt me-1"></i> </a>
              </td>
            </tr>
            <?php
                $i++;
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

  function editdata(estate_id,rawdata_id,state,city,taluka,area,ind_estate,firm_name,user_id,status,factory_address) {
    //cookie -> estate_id, rawdata_id
    createCookie('estateid_comp_addplot', estate_id);
    createCookie('rawdataid_comp_addplot', rawdata_id);
    console.log("add="+atob(factory_address));
   
    localStorage.setItem("factoryadd_est_addplot",atob(factory_address));
    createCookie('company_status', atob(status));
    createCookie('selecttype_comp_addplot', 'select_estate_first');
    window.open("company_add_plot.php", '_blank');
  }

</script>
<?php 
  include("footer.php");
?>