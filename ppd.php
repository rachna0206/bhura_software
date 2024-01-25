<?php
  include("header.php");
?>

<h4 class="fw-bold py-3 mb-4">Power & Production Detail</h4>

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
                  <!--    <h5 class="mb-0">Add State</h5>
                      
                    </div>  -->
                    <div class="card-body">
                      <form method="post" >
                       
                        <input type="hidden" name="ttId" id="ttId">
                        
                        <div class="row g-2">
                          <div class="col mb-3">
                            <label class="form-label" for="basic-default-fullname">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="start_date" required />
                          </div>
                          <div class="col mb-3">
                            <label class="form-label" for="basic-default-fullname">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="end_date" required />
                          </div>
                        </div>

                        <hr/>
                        <label class="form-label" for="basic-default-fullname">Production Quantity</label>

                        <div class="row g-2">
                          <div class="col mb-3">
                            <label class="form-label" for="basic-default-fullname">Start Quantity</label>
                            <input type="text" class="form-control" name="start_quantity" id="start_quantity" required />
                          </div>
                          <div class="col mb-3">
                            <label class="form-label" for="basic-default-fullname">End Quantity</label>
                            <input type="text" class="form-control" name="end_quantity" id="end_quantity" required />
                          </div>
                        </div>

                        <hr/>
                        <label class="form-label" for="basic-default-fullname">Price</label>

                        <div class="row g-2">
                          <div class="col mb-3">
                            <label class="form-label" for="basic-default-fullname">Start Price</label>
                            <input type="text" class="form-control" name="start_price" id="start_price" required />
                          </div>
                          <div class="col mb-3">
                            <label class="form-label" for="basic-default-fullname">End Price</label>
                            <input type="text" class="form-control" name="end_price" id="end_price" required />
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
                <div class="row ms-2 me-9">
                  <div class="col-md-6"><h5 class="card-header">PPD Sheet</h5></div>
                <?php 
                    if(isset($_REQUEST['btnsubmit']))
                    {
                ?>
                  <div class="col-md-2" style="margin:1%">  
                    <input type="button" class="btn btn-primary" name="btn_excel" value="Export to Excel"  onClick="window.location.href='calculation_sheet_excel.php'" id="btn_excel">
                  </div>
                <?php } ?>
                </div>
                <div class="table-responsive text-nowrap">
                  <table class="table" id="table_id">

                    <thead>
                      <tr>
                        <th>Srno</th>
                        <th>Month-Year</th>
                        <th>Production Quantity (PCS)</th>
                        <th>Production Value (Rs.)</th>
                        <th>Sales Quantity (PCS)</th>
                        <th>Sales Value (RS)</th>
                        <th>Power Consumption (unit)</th>
                        <th>Power Consumption (In Rs.)</th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                      <?php 

                      if(isset($_REQUEST['btnsubmit']))
                      {//create table ppd (pid int primary key auto_increment, start_date date, end_date date, start_prod_quan int, end_prod_quan int, start_price int, end_price int)
                        $stmt_truncate = $obj->con1->prepare("truncate table ppd");
                        $stmt_truncate->execute();
                        $stmt_truncate->close();

                        $start_date = $_REQUEST['start_date'];
                        $end_date = $_REQUEST['end_date'];
                        $start_quantity = $_REQUEST['start_quantity'];
                        $end_quantity = $_REQUEST['end_quantity'];
                        $start_price = $_REQUEST['start_price'];
                        $end_price = $_REQUEST['end_price'];

                        $start_month = date('m', strtotime($start_date));
                        $end_month = date('m', strtotime($end_date));
                        $time_period = $end_month - $start_month;
                        $i=1;
                        for($j=0;$j<$time_period;$j++)
                        {
                          $month_year = date('M-Y', strtotime('last day of +'.$j.' month', strtotime($start_date)));
                          $rand_quantity = rand($start_quantity,$end_quantity);
                          $rand_price = rand($start_price,$end_price);

                          $production_value = $rand_quantity*$rand_price;

                          $rand_increment = rand(2,7);
                          $sales_price = $rand_price + $rand_increment;

                          $sales_value = $rand_quantity*$sales_price;

                          $stmt = $obj->con1->prepare("INSERT INTO `ppd`(`start_date`, `end_date`, `month_year`, `prod_quantity`, `prod_value`, `sales_quantity`, `sales_value`) VALUES (?,?,?,?,?,?,?)");
                          $stmt->bind_param("sssssss",$start_date,$end_date,$month_year,$rand_quantity,$production_value,$rand_quantity,$sales_value);
                          $Resp=$stmt->execute();
                          $stmt->close();
                        ?>

                      <tr>
                        <td><?php echo $i?></td>
                        <td><?php echo $month_year ?></td>
                        <td><?php echo $rand_quantity ?></td>
                        <td><?php echo $production_value ?></td>
                        <td><?php echo $rand_quantity ?></td>
                        <td><?php echo $sales_value ?></td>
                        <td></td>
                        <td></td>
                      </tr>
                      <?php
                          $i++;
                        }
                      ?>
                      
                    </tbody>
                    
                    <?php

                      }
                    ?>
                  </table>
                    
                </div>
              </div>
              <!--/ Basic Bootstrap Table -->


           <!-- / grid -->

            <!-- / Content -->
<?php 
  include("footer.php");
?>