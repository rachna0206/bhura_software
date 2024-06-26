<?php 
//ob_start();
include ("db_connect.php");
include ("func.php");
$obj=new DB_connect();
date_default_timezone_set("Asia/Kolkata");
//error_reporting(E_ALL);

session_start();


if(!isset($_SESSION["userlogin"]) )
{
    header("location:index.php");
}

$stmt_admin = $obj->con1->prepare("SELECT * FROM `tbl_users` WHERE role='superadmin'");
$stmt_admin->execute();
$admin_result = $stmt_admin->get_result();
$stmt_admin->close();
$admin = array();
while($row = mysqli_fetch_array($admin_result)){
  $admin[] = $row['id'];
}

$user_id = $_SESSION["id"];

$stmt_username = $obj->con1->prepare("SELECT * FROM `tbl_users` WHERE id=?");
$stmt_username->bind_param("i",$user_id);
$stmt_username->execute();
$username_result = $stmt_username->get_result()->fetch_assoc();
$stmt_username->close();

$stmt_emp = $obj->con1->prepare("SELECT DISTINCT(i1.taluka) FROM assign_estate a1, tbl_industrial_estate i1 WHERE a1.industrial_estate_id=i1.id and employee_id=? and start_dt<=curdate() and end_dt>=curdate() and action='company_entry'");
$stmt_emp->bind_param("i",$user_id);
$stmt_emp->execute();
$emp_result = $stmt_emp->get_result();
$stmt_emp->close();

$stmt_scheme = $obj->con1->prepare("SELECT * FROM `tbl_service_master` WHERE service IN ('GOGTP IR', 'GOGTP PT')");
$stmt_scheme->bind_param("i",$user_id);
$stmt_scheme->execute();
$scheme_result = $stmt_scheme->get_result();
$stmt_scheme->close();

$adminmenu=array("company_plot_report.php","assign_estate.php","unassigned_estate_company.php","assign_estate_plotting.php","unassigned_estate_plotting.php","add_industrial_estate.php","add_industrial_estate_old.php","company_entry.php","estate_plotting_report.php","estate_status_report.php","visit_count_report.php","scheme.php","stages.php","pr_file_format.php","logged_users","employee_master.php","company_add_plot.php","company_add_plot_est.php","company_add_plot_com.php","update_status.php","logged_users.php","estate_list.php");

$processmenu=array("process_gogtp_ir.php","process_gogtp_pt.php","process.php","process_scheme_list.php");
?>

<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <meta name="date" content="2023-06-09" scheme="YYYY-MM-DD"/>

    <title>Dashboard | Bhura Consultancy</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- data tables -->
    <link rel="stylesheet" type="text/css" href="assets/vendor/DataTables/datatables.css">
     
    <!-- Row Group CSS -->
    <!-- <link rel="stylesheet" href="assets/vendor/datatables-rowgroup-bs5/rowgroup.bootstrap5.css"> -->
    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="assets/js/config.js"></script>
    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script type="text/javascript">
        function createCookie(name, value, days) {
          var expires;
          if (days) {
              var date = new Date();
              date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
              expires = "; expires=" + date.toGMTString();
          } else {
              expires = "";
          }
          document.cookie = (name) + "=" + String(value) + expires + ";path=/ ";

      }

      function readCookie(name) {
          var nameEQ = (name) + "=";
          var ca = document.cookie.split(';');
          for (var i = 0; i < ca.length; i++) {
              var c = ca[i];
              while (c.charAt(0) === ' ') c = c.substring(1, c.length);
              if (c.indexOf(nameEQ) === 0) return (c.substring(nameEQ.length, c.length));
          }
          return null;
      }

      function eraseCookie(name) {
          createCookie(name, "", -1);
      }

      function process_pages(service_name, service_id) {
        createCookie("service_id",service_id);
        window.location="process_scheme_list.php";
      }
    </script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="#" class="app-brand-link">
              <span class="app-brand-text demo menu-text fw-bolder ms-0">Bhura App</span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">
            <!-- Dashboard -->
            <li class="menu-item active">
              <a href="home.php" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
              </a>
            </li>


            <!-- Forms & Tables -->
            <!-- <li class="menu-header small text-uppercase"><span class="menu-header-text">Masters</span></li> -->
            <!-- Forms -->
            

            <li class="menu-item <?php echo in_array(basename($_SERVER["PHP_SELF"]),$adminmenu)?"active open":"" ?> ">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-detail"></i>
                <div data-i18n="Form Elements">Admin Controls</div>
              </a>

              <ul class="menu-sub">

                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="add_industrial_estate.php"?"active":"" ?>">
                  <a href="add_industrial_estate.php" class="menu-link">
                  <div data-i18n="course">Add Industrial Estate</div>
                  </a>
                </li>

              <?php if(in_array($user_id, $admin)){ ?>
                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="unassigned_estate_plotting.php"?"active":"" ?>">
                  <a href="unassigned_estate_plotting.php" class="menu-link">
                  <div data-i18n="course">Unassigned Estate (For Plotting)</div>
                  </a>
                </li>

                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="assign_estate_plotting.php"?"active":"" ?>">
                  <a href="assign_estate_plotting.php" class="menu-link">
                  <div data-i18n="course">Assigned Estate (For Plotting)</div>
                  </a>
                </li>

                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="unassigned_estate_company.php"?"active":"" ?>">
                  <a href="unassigned_estate_company.php" class="menu-link">
                  <div data-i18n="course">Unassigned Estate (For Company)</div>
                  </a>
                </li>

                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="assign_estate.php"?"active":"" ?>">
                  <a href="assign_estate.php" class="menu-link">
                  <div data-i18n="course">Assigned Estate (For Company)</div>
                  </a>
                </li>

                <!-- <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="company_plot_report.php"?"active":"" ?>">
                  <a href="company_plot_report.php" class="menu-link">
                  <div data-i18n="course">Company Plot Report</div>
                  </a>
                </li> -->
                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="estate_list.php"?"active":"" ?>">
                  <a href="estate_list.php" class="menu-link">
                  <div data-i18n="course">Company Plot Report</div>
                  </a>
                </li>

                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="estate_status_report.php"?"active":"" ?>">
                  <a href="estate_status_report.php" class="menu-link">
                  <div data-i18n="course">Estate Status Report</div>
                  </a>
                </li>

                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="visit_count_report.php"?"active":"" ?>">
                  <a href="visit_count_report.php" class="menu-link">
                  <div data-i18n="course">Visit Report</div>
                  </a>
                </li>
                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="logged_users.php"?"active":"" ?>">
                  <a href="logged_users.php" class="menu-link">
                  <div data-i18n="course">View Logged-in Users</div>
                  </a>
                </li>
                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="update_status.php"?"active":"" ?>">
                  <a href="update_status.php" class="menu-link">
                  <div data-i18n="course">Update Status</div>
                  </a>
                </li>
                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="pr_file_format.php"?"active":"" ?>">
                  <a href="pr_file_format.php" class="menu-link">
                  <div data-i18n="course">File Format</div>
                  </a>
                </li>

              <?php } ?>

              <?php if(!in_array($user_id, $admin)){ ?>

                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="add_industrial_estate_old.php"?"active":"" ?>">
                  <a href="add_industrial_estate_old.php" class="menu-link">
                  <div data-i18n="course">Estate Detail Pending</div>
                  </a>
                </li>

              <?php if(mysqli_num_rows($emp_result)>0){ ?>
                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="company_entry.php"?"active":"" ?>">
                  <a href="company_entry.php" class="menu-link">
                  <div data-i18n="course">Add Company</div>
                  </a>
                </li>
              <?php } ?>

                <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])==""?"active":"" ?>">
                  <a href="employee_master.php" class="menu-link">
                  <div data-i18n="course">Employee Master</div>
                  </a>
                </li>

              <?php } ?>

              <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="company_add_plot_est.php"?"active":"" ?>">
                <a href="company_add_plot_est.php" class="menu-link">
                <div data-i18n="course">Add Plotting In Company (With Estate)</div>
                </a>
              </li>

              <li class="menu-item <?php echo basename($_SERVER["PHP_SELF"])=="company_add_plot_com.php"?"active":"" ?>">
                <a href="company_add_plot_com.php" class="menu-link">
                <div data-i18n="course">Add Plotting In Company (Without Estate)</div>
                </a>
              </li>

              </ul>
            </li>

            <?php if(!in_array($user_id, $admin)){ ?>

            <li class="menu-item <?php echo in_array(basename($_SERVER["PHP_SELF"]),$processmenu)?"active open":"" ?> ">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-detail"></i>
                <div data-i18n="Form Elements">Process</div>
              </a>

              <ul class="menu-sub">
                <?php while($scheme=mysqli_fetch_array($scheme_result)){
                    $isActive = (basename($_SERVER["PHP_SELF"]) == "process_gogtp_ir.php" && $scheme['service'] == "GOGTP IR") || (basename($_SERVER["PHP_SELF"]) == "process_gogtp_pt.php" && $scheme['service'] == "GOGTP PT") || (basename($_SERVER["PHP_SELF"]) == "process.php" && !in_array($scheme['service'], ["GOGTP IR", "GOGTP PT"])); 
                ?>
                  <li class="menu-item <?php echo $isActive ? "active" : "" ?>">
                    <a href="javascript:process_pages('<?php echo $scheme['service'] ?>','<?php echo $scheme['id'] ?>');" class="menu-link">
                    <div data-i18n="course"><?php echo $scheme['service'] ?></div>
                    </a>
                  </li>
                <?php } ?>
              </ul>

            </li>

            <?php } ?>
           
          </ul>
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->

          <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              

              <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- Place this tag where you want the button to render. -->
                

              
                <!-- <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar">
                     <i class="bx bx-bell"></i>
                     <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20" id="noti_count"></span>
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end" id="notification_list">
                  </ul>
                </li> -->
                <!-- Notification -->
          <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" >
              <i class="bx bx-bell bx-sm"></i>
              <span class="badge bg-danger rounded-pill badge-notifications" id="noti_count"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end py-0">
              <li class="dropdown-menu-header border-bottom" id="notif_header" style="display:none">
                <div class="dropdown-header d-flex align-items-center py-3">
                  <h5 class="text-body mb-0 me-auto">Notification</h5>
                  <a href="javascript:mark_read_all()" class="dropdown-notifications-all text-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark all as read">Read All</a>
                </div>
              </li>
              <li class="dropdown-notifications-list scrollable-container">
                <ul class="list-group list-group-flush" id="notification_list">
                 
                </ul>
              </li>
              
            </ul>
          </li>
          <!--/ Notification -->
             
                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <img src="assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="assets/img/avatars/1.png" alt="" class="w-px-40 h-auto rounded-circle">
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <span class="fw-semibold d-block"><?php echo ucwords(strtolower($_SESSION["username"])) ?></span>
                            <small class="text-muted"><?php echo ucwords(strtolower($username_result["role"])) ?></small>
                          </div>
                        </div>
                      </a>
                    </li>
                   
                    
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                  <!--  <li>
                      <a class="dropdown-item" href="changePassword.php">
                        <i class="bx bx-lock me-2"></i>
                        <span class="align-middle">Change Password</span>
                      </a>
                    </li> 
                    <li>
                      <div class="dropdown-divider"></div>
                    </li> -->

                    <li>
                      <a class="dropdown-item" href="logout.php">
                        <i class="bx bx-power-off me-2"></i>
                        <span class="align-middle">Log Out</span>
                      </a>
                    </li>
                  </ul>
                </li>
              <!-- / User -->
              </ul>
            </div>
          </nav>
          <div id="sound"></div>
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->

            <div class="container-xxl flex-grow-1 container-p-y">
