<?php
  include("header.php");

$user_id = $_SESSION["id"];

?>

<h4 class="fw-bold py-3 mb-4">Estate Plots Report</h4>

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

<?php if(in_array($user_id, $admin)){ ?> 
    <!-- grid -->

    <!-- Basic Bootstrap Table -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Records</h5>
        <div class="mb-3"><a href="add_industrial_estate.php">Add Estate</a></div>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover" id="table_id">
          <thead>
            <tr>
              <th>Srno</th>
              <th>State</th>
              <th>City</th>
              <th>Taluka</th>
              <th>Area</th>
              <th>Industrial Estate</th>
              <th>Plotting Pattern</th>
              <th>Total Plots</th>
              <th></th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            <?php 
              
              //SELECT i1.state_id, i1.city_id, i1.taluka, i1.area_id, i1.industrial_estate, d1.plotting_pattern, ttl.industrial_estate_id, sum(ttl.sum1) as total from (( SELECT sum(IfNULL(plot_end_no,1)) as sum1,industrial_estate_id  from pr_estate_roadplot where plot_end_no is null GROUP BY industrial_estate_id) union all (select sum((plot_end_no-plot_start_no)+1) as sum1,industrial_estate_id from pr_estate_roadplot where plot_end_no is not null GROUP BY industrial_estate_id )) ttl, pr_add_industrialestate_details d1, tbl_industrial_estate i1 where d1.industrial_estate_id=i1.id and ttl.industrial_estate_id=i1.id group by industrial_estate_id order by d1.id desc

              $stmt_list = $obj->con1->prepare("SELECT i1.state_id, i1.city_id, i1.taluka, i1.area_id, i1.industrial_estate, d1.plotting_pattern, i1.id as industrial_estate_id from pr_add_industrialestate_details d1, tbl_industrial_estate i1 where d1.industrial_estate_id=i1.id group by industrial_estate_id order by d1.id desc");
              $stmt_list->execute();
              $result = $stmt_list->get_result();
              $stmt_list->close();
              $i=1;

              while($data=mysqli_fetch_array($result))
              {
                $stmt_plot_list = $obj->con1->prepare("SELECT concat(plot_start_no,' To ',plot_end_no) as plot, road_no FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is not null UNION ALL SELECT plot_start_no as plot, road_no FROM `pr_estate_roadplot` WHERE industrial_estate_id=? and plot_end_no is null");
                $stmt_plot_list->bind_param("ii",$data["industrial_estate_id"],$data["industrial_estate_id"]);
                $stmt_plot_list->execute();
                $plot_result = $stmt_plot_list->get_result();
                $stmt_plot_list->close();

                $stmt_sum = $obj->con1->prepare("SELECT count(plot_no) as total from pr_company_plots where floor='0' and industrial_estate_id=?");
                $stmt_sum->bind_param("i",$data['industrial_estate_id']);
                $stmt_sum->execute();
                $result_sum = $stmt_sum->get_result()->fetch_assoc();
                $stmt_sum->close();
                
            ?>

            <tr>
              <td><?php echo $i?></td>
              <td><?php echo $data["state_id"] ?></td>
              <td><?php echo $data["city_id"] ?></td>
              <td><?php echo $data["taluka"] ?></td>
              <td><?php echo $data["area_id"] ?></td>
              <td><?php echo $data["industrial_estate"] ?></td>
              <td><?php echo $data["plotting_pattern"] ?></td>
              <td><?php echo $result_sum["total"] ?></td>
              <td>
                <i class="bx bx-info-circle bx-sm" data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true" 
                title="<span>
              <?php
                if($data["plotting_pattern"]=='Road'){
                  echo "Road No -> Plots <br/>";
                }else{ echo "Plots <br/>"; } 
                while($plot_list=mysqli_fetch_array($plot_result)){ 
                  if($data["plotting_pattern"]=='Road'){
                    echo $plot_list["road_no"]." -> ".$plot_list["plot"]."<br/>";
                  }
                  else{
                    echo $plot_list["plot"]."<br/>";
                  }
                }
               ?>
                </span>"></i>
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

<?php } ?>

  <!-- / Content -->

<?php 
  include("footer.php");
?>