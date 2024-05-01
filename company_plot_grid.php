<?php
  session_start();

  if(!isset($_SESSION["userlogin"]) )
  {
      header("location:index.php");
  }
?>

<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
<html
  lang="en"
  class="light-style"
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

    <title>Dashboard | Demo</title>

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
    <!-- <link rel="stylesheet" type="text/css" href="assets/vendor/DataTables/datatables.css"> -->

    <!-- Helpers -->
    <script src="assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="assets/js/config.js"></script>
    <script src="assets/vendor/libs/jquery/jquery.js"></script>



    <style>
            .container {
                position: relative;
            }
            .container__menu {
                /* Absolute position */
                position: absolute;

                /* Reset */
                list-style-type: none;
                margin: 0;
                padding: 0;

                /* Misc */
                background-color: #f7fafc;
                border: 1px solid #cbd5e0;
                border-radius: 0.25rem;
                padding: 0.5rem;
            }
            .container__menu--hidden {
                display: none;
            }
            .table {
                border: 1px solid #ccc;
                border-collapse: collapse;
            }
            .table th,
            .table td {
                border: 1px solid #ccc;
            }
            .table th,
            .table td {
                padding: 0.5rem;
            }
            .table th {
                user-select: none;
            }
            .draggable_col {
                cursor: move;
                user-select: none;
            }
            .placeholder_col {
                background-color: #edf2f7;
                border: 2px dashed #cbd5e0;
            }
            .clone-list_col {
                border-left: 1px solid #ccc;
                border-top: 1px solid #ccc;
                display: flex;
            }
            .clone-table_col {
                border-collapse: collapse;
                border: none;
            }
            .clone-table_col th,
            .clone-table_col td {
                border: 1px solid #ccc;
                border-left: none;
                border-top: none;
                padding: 0.5rem;
            }
            .dragging_col {
                background: #fff;
                border-left: 1px solid #ccc;
                border-top: 1px solid #ccc;
                z-index: 999;
            }
            .draggable_row {
                cursor: move;
                user-select: none;
            }
            .placeholder_row {
                background-color: #edf2f7;
                border: 2px dashed #cbd5e0;
            }
            .clone-list_row {
                border-top: 1px solid #ccc;
            }
            .clone-table_row {
                border-collapse: collapse;
                border: none;
            }
            .clone-table_row th,
            .clone-table_row td {
                border: 1px solid #ccc;
                border-top: none;
                padding: 0.5rem;
            }
            .dragging_row {
                background: #fff;
                border-top: 1px solid #ccc;
                z-index: 999;
            }
            
        </style>



  </head>

  <body>
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">

    <h4 class="fw-bold p-4">Company Plot Records</h4>



<?php

include "db_connect.php";
$obj=new DB_connect();

$report_search = $_COOKIE['report_search'];
$search_values= explode(',',$report_search);

/*$firm_name=isset($search_values[0])?$search_values[0]:"";
$gst_no=isset($search_values[1])?$search_values[1]:"";
$area=isset($search_values[2])?$search_values[2]:"";
$ind_estate=isset($search_values[3])?$search_values[3]:"";
$status=isset($search_values[4])?$search_values[4]:"";
$plot_status=isset($search_values[5])?$search_values[5]:"";*/
  
/*$firm_name_str=($firm_name!="")?" and lower(raw_data->'$.post_fields.Firm_Name') like '%".strtolower($firm_name)."%'":"";
$gst_no_str=($gst_no!="")?"and raw_data->'$.post_fields.GST_No' like '%".$gst_no."%'":"";
$area_str=($area!="")?"and lower(raw_data->'$.post_fields.Area') like '%".strtolower($area)."%'":"";
$ind_estate_str=($ind_estate!="")?"and lower(raw_data->'$.post_fields.IndustrialEstate') like '%".strtolower($ind_estate)."%'":"";
$status_str=($status!="")?"and raw_data->'$.Status' like '%".$status."%'":"";
$plot_status_str=($plot_status!="")?"and raw_data->'$.plot_details[*].Plot_Status' like '%".$plot_status."%'":"";*/

$firm_name=isset($search_values[0])?$search_values[0]:"";
$gst_no=isset($search_values[1])?$search_values[1]:"";
$ind_estate=isset($search_values[2])?$search_values[2]:"";
$status=isset($search_values[3])?$search_values[3]:"";
$plot_status=isset($search_values[4])?$search_values[4]:"";
$contact_person=isset($search_values[5])?$search_values[5]:"";
$contact_number=isset($search_values[6])?$search_values[6]:"";

$firm_name_str=($firm_name!="")?" and lower(r1.raw_data->'$.post_fields.Firm_Name') like '%".strtolower($firm_name)."%'":"";
$gst_no_str=($gst_no!="")?"and r1.raw_data->'$.post_fields.GST_No' like '%".$gst_no."%'":"";
$ind_estate_str=($_COOKIE['report_estate_id']!="")?"and p1.industrial_estate_id='".$_COOKIE['report_estate_id']."'":"";
$status_str=($status!="")?"and c1.status='".$status."'":"";
$plot_status_str=($plot_status!="")?"and p1.plot_status='".$plot_status."'":"";
$contact_person_str=($contact_person!="")?" and lower(r1.raw_data->'$.post_fields.Contact_Name') like '%".strtolower($contact_person)."%'":"";
$contact_number_str=($contact_number!="")?" and lower(r1.raw_data->'$.post_fields.Mobile_No') like '%".strtolower($contact_number)."%'":"";

// $stmt_list = $obj->con1->prepare("SELECT * FROM tbl_tdrawdata WHERE 1 ".$firm_name_str.$gst_no_str.$area_str.$status_str.$plot_status_str.$ind_estate_str." order by id desc");
$stmt_list = $obj->con1->prepare("SELECT p1.pid, c1.rawdata_id, r1.raw_data->>'$.post_fields.Firm_Name' as firm_name, r1.raw_data->>'$.post_fields.GST_No' as gst_no, i1.area_id, i1.city_id, i1.industrial_estate, p1.plot_no, p1.floor, p1.road_no, p1.plot_status, r1.raw_data->>'$.post_fields.Contact_Name' as contact_person, r1.raw_data->>'$.post_fields.Mobile_No' as contact_number, c1.status, c1.constitution, r1.raw_data->>'$.post_fields.Remarks' as remark, r1.raw_data->>'$.post_fields.Segment' as segment FROM `pr_company_plots` p1 JOIN `tbl_industrial_estate` i1 ON p1.industrial_estate_id=i1.id LEFT JOIN `pr_company_details` c1 ON p1.company_id=c1.cid LEFT JOIN `tbl_tdrawdata` r1 ON c1.rawdata_id=r1.id WHERE 1 ".$firm_name_str.$gst_no_str.$status_str.$plot_status_str.$ind_estate_str." ORDER BY p1.industrial_estate_id, abs(p1.road_no), abs(p1.plot_no), p1.floor");
$stmt_list->execute();
$result = $stmt_list->get_result();
$stmt_list->close();

$stmt_ind_estate_name = $obj->con1->prepare("SELECT * FROM `tbl_industrial_estate` WHERE id=?");
$stmt_ind_estate_name->bind_param("i",$ind_estate);
$stmt_ind_estate_name->execute();
$estate_result = $stmt_ind_estate_name->get_result()->fetch_assoc();
$stmt_ind_estate_name->close();

?>

<!-- Basic Bootstrap Table -->
<div class="container">
    <dl class="row mt-2">
      <dd class="text-muted col-sm-2">Industrial Estate : </dd>
      <dt class="fw-bold col-sm-9"><?php echo $estate_result['industrial_estate'] ?></dt>

      <dd class="text-muted col-sm-2">Area : </dd>
      <dt class="fw-bold col-sm-9"><?php echo $estate_result['area_id'] ?></dt>

      <dd class="text-muted col-sm-2">Taluka : </dd>
      <dt class="fw-bold col-sm-9"><?php echo $estate_result['taluka'] ?></dt>

        <?php if($firm_name!=""){ ?>
            <dd class="text-muted col-sm-2">Firm Name : </dd>
            <dt class="fw-bold col-sm-9"><?php echo $firm_name ?></dt>
        <?php } if($gst_no!=""){ ?>
            <dd class="text-muted col-sm-2">GST NO. : </dd>
            <dt class="fw-bold col-sm-9"><?php echo $gst_no ?></dt>
        <?php } if($status!=""){ ?>
            <dd class="text-muted col-sm-2">Status : </dd>
            <dt class="fw-bold col-sm-9"><?php echo $status ?></dt>
        <?php } if($plot_status!=""){ ?>
            <dd class="text-muted col-sm-2">Plot Status : </dd>
            <dt class="fw-bold col-sm-9"><?php echo $plot_status ?></dt>
        <?php } ?>
    </dl>    
</div>

  <div class="card">
   
    <div class="table-responsive text-nowrap">
      <table class="table" id="dragdrop_id">
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
              <td><?php echo $data['area_id']." - ".$data['city_id'] ?></td>
              <td><?php echo $data['industrial_estate'] ?></td>
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
              }
        ?>
          </tr>
          
        </tbody>
      </table>
      <ul id="menu" class="container__menu container__menu--hidden"></ul>
    </div>
  </div>

  <!--/ Basic Bootstrap Table -->


        </div>

        <script>
		/*$(document).ready( function () {
			$('#dragdrop_id').DataTable();     
		} );*/
            document.addEventListener('DOMContentLoaded', function () {
                const table = document.getElementById('dragdrop_id');

                let draggingEle_col;
                let draggingColumnIndex_col;
                let placeholder_col;
                let list_col;
                let isDraggingStarted_col = false;

                let draggingEle_row;
                let draggingRowIndex_row;
                let placeholder_row;
                let list_row;
                let isDraggingStarted_row = false;


                const menu = document.getElementById('menu');
                //const table = document.getElementById('table');
                const headers = [].slice.call(table.querySelectorAll('th'));
                const cells = [].slice.call(table.querySelectorAll('th, td'));
                const numColumns = headers.length;
                
                // The current position of mouse relative to the dragging element
                let x = 0;
                let y = 0;

                // Swap two nodes
                const swap_col = function (nodeA, nodeB) {
                    const parentA = nodeA.parentNode;
                    const siblingA = nodeA.nextSibling === nodeB ? nodeA : nodeA.nextSibling;

                    // Move `nodeA` to before the `nodeB`
                    nodeB.parentNode.insertBefore(nodeA, nodeB);

                    // Move `nodeB` to before the sibling of `nodeA`
                    parentA.insertBefore(nodeB, siblingA);
                };

                // Swap two nodes
                const swap_row = function (nodeA, nodeB) {
                    const parentA = nodeA.parentNode;
                    const siblingA = nodeA.nextSibling === nodeB ? nodeA : nodeA.nextSibling;

                    // Move `nodeA` to before the `nodeB`
                    nodeB.parentNode.insertBefore(nodeA, nodeB);

                    // Move `nodeB` to before the sibling of `nodeA`
                    parentA.insertBefore(nodeB, siblingA);
                };

                // Check if `nodeA` is on the left of `nodeB`
                const isOnLeft = function (nodeA, nodeB) {
                    // Get the bounding rectangle of nodes
                    const rectA = nodeA.getBoundingClientRect();
                    const rectB = nodeB.getBoundingClientRect();

                    return rectA.left + rectA.width / 2 < rectB.left + rectB.width / 2;
                };

                // Check if `nodeA` is above `nodeB`
                const isAbove_row = function (nodeA, nodeB) {
                    // Get the bounding rectangle of nodes
                    const rectA = nodeA.getBoundingClientRect();
                    const rectB = nodeB.getBoundingClientRect();

                    return rectA.top + rectA.height / 2 < rectB.top + rectB.height / 2;
                };

                const cloneTable_col = function () {
                    const rect = table.getBoundingClientRect();

                    list_col = document.createElement('div');
                    list_col.classList.add('clone-list_col');
                    list_col.style.position = 'absolute';
                    list_col.style.left = `${rect.left}px`;
                    list_col.style.top = `${rect.top}px`;
                    table.parentNode.insertBefore(list_col, table);

                    // Hide the original table
                    table.style.visibility = 'hidden';

                    // Get all cells
                    const originalCells = [].slice.call(table.querySelectorAll('tbody td'));

                    const originalHeaderCells = [].slice.call(table.querySelectorAll('th'));
                    const numColumns = originalHeaderCells.length;

                    // Loop through the header cells
                    originalHeaderCells.forEach(function (headerCell, headerIndex) {
                        const width = parseInt(window.getComputedStyle(headerCell).width);

                        // Create a new table from given row
                        const item = document.createElement('div');
                        item.classList.add('draggable_col');

                        const newTable = document.createElement('table');
                        newTable.setAttribute('class', 'clone-table_col');
                        newTable.style.width = `${width}px`;

                        // Header
                        const th = headerCell.cloneNode(true);
                        let newRow = document.createElement('tr');
                        newRow.appendChild(th);
                        newTable.appendChild(newRow);

                        const cells = originalCells.filter(function (c, idx) {
                            return (idx - headerIndex) % numColumns === 0;
                        });
                        cells.forEach(function (cell) {
                            const newCell = cell.cloneNode(true);
                            newCell.style.width = `${width}px`;
                            newRow = document.createElement('tr');
                            newRow.appendChild(newCell);
                            newTable.appendChild(newRow);
                        });

                        item.appendChild(newTable);
                        list_col.appendChild(item);
                    });
                };

                const cloneTable_row = function () {
                    const rect = table.getBoundingClientRect();
                    const width = parseInt(window.getComputedStyle(table).width);

                    list_row = document.createElement('div');
                    list_row.classList.add('clone-list_row');
                    list_row.style.position = 'absolute';
                    list_row.style.left = `${rect.left}px`;
                    list_row.style.top = `${rect.top}px`;
                    table.parentNode.insertBefore(list_row, table);

                    // Hide the original table
                    table.style.visibility = 'hidden';

                    table.querySelectorAll('tr').forEach(function (row) {
                        // Create a new table from given row
                        const item = document.createElement('div');
                        item.classList.add('draggable_row');

                        const newTable = document.createElement('table');
                        newTable.setAttribute('class', 'clone-table_row');
                        newTable.style.width = `${width}px`;

                        const newRow = document.createElement('tr');
                        const cells = [].slice.call(row.children);
                        cells.forEach(function (cell) {
                            const newCell = cell.cloneNode(true);
                            newCell.style.width = `${parseInt(window.getComputedStyle(cell).width)}px`;
                            newRow.appendChild(newCell);
                        });

                        newTable.appendChild(newRow);
                        item.appendChild(newTable);
                        list_row.appendChild(item);
                    });
                };

                const mouseDownHandler_col = function (e) {
                    draggingColumnIndex_col = [].slice.call(table.querySelectorAll('th')).indexOf(e.target);

                    // Determine the mouse position
                    x = e.clientX - e.target.offsetLeft;
                    y = e.clientY - e.target.offsetTop;

                    // Attach the listeners to `document`
                    document.addEventListener('mousemove', mouseMoveHandler_col);
                    document.addEventListener('mouseup', mouseUpHandler_col);
                };

                const mouseDownHandler_row = function (e) {
                    // Get the original row
                    const originalRow = e.target.parentNode;
                    draggingRowIndex_row = [].slice.call(table.querySelectorAll('tr')).indexOf(originalRow);

                    // Determine the mouse position
                    x = e.clientX;
                    y = e.clientY;

                    // Attach the listeners to `document`
                    document.addEventListener('mousemove', mouseMoveHandler_row);
                    document.addEventListener('mouseup', mouseUpHandler_row);
                };

                const mouseMoveHandler_col = function (e) {
                    if (!isDraggingStarted_col) {
                        isDraggingStarted_col = true;

                        cloneTable_col();

                        draggingEle_col = [].slice.call(list_col.children)[draggingColumnIndex_col];
                        draggingEle_col.classList.add('dragging_col');

                        // Let the placeholder take the height of dragging element
                        // So the next element won't move to the left or right
                        // to fill the dragging element space
                        placeholder_col = document.createElement('div');
                        placeholder_col.classList.add('placeholder_col');
                        draggingEle_col.parentNode.insertBefore(placeholder_col, draggingEle_col.nextSibling);
                        placeholder_col.style.width = `${draggingEle_col.offsetWidth}px`;
                    }

                    // Set position for dragging element
                    draggingEle_col.style.position = 'absolute';
                    draggingEle_col.style.top = `${draggingEle_col.offsetTop + e.clientY - y}px`;
                    draggingEle_col.style.left = `${draggingEle_col.offsetLeft + e.clientX - x}px`;

                    // Reassign the position of mouse
                    x = e.clientX;
                    y = e.clientY;

                    // The current order
                    // prevEle
                    // draggingEle
                    // placeholder
                    // nextEle
                    const prevEle = draggingEle_col.previousElementSibling;
                    const nextEle = placeholder_col.nextElementSibling;

                    // // The dragging element is above the previous element
                    // // User moves the dragging element to the left
                    if (prevEle && isOnLeft(draggingEle_col, prevEle)) {
                        // The current order    -> The new order
                        // prevEle              -> placeholder
                        // draggingEle          -> draggingEle
                        // placeholder          -> prevEle
                        swap_col(placeholder_col, draggingEle_col);
                        swap_col(placeholder_col, prevEle);
                        return;
                    }

                    // The dragging element is below the next element
                    // User moves the dragging element to the bottom
                    if (nextEle && isOnLeft(nextEle, draggingEle_col)) {
                        // The current order    -> The new order
                        // draggingEle          -> nextEle
                        // placeholder          -> placeholder
                        // nextEle              -> draggingEle
                        swap_col(nextEle, placeholder_col);
                        swap_col(nextEle, draggingEle_col);
                    }
                };

                const mouseMoveHandler_row = function (e) {
                    if (!isDraggingStarted_row) {
                        isDraggingStarted_row = true;

                        cloneTable_row();

                        draggingEle_row = [].slice.call(list_row.children)[draggingRowIndex_row];
                        draggingEle_row.classList.add('dragging_row');

                        // Let the placeholder take the height of dragging element
                        // So the next element won't move up
                        placeholder_row = document.createElement('div');
                        placeholder_row.classList.add('placeholder_row');
                        draggingEle_row.parentNode.insertBefore(placeholder_row, draggingEle_row.nextSibling);
                        placeholder_row.style.height = `${draggingEle_row.offsetHeight}px`;
                    }

                    // Set position for dragging element
                    draggingEle_row.style.position = 'absolute';
                    draggingEle_row.style.top = `${draggingEle_row.offsetTop + e.clientY - y}px`;
                    draggingEle_row.style.left = `${draggingEle_row.offsetLeft + e.clientX - x}px`;

                    // Reassign the position of mouse
                    x = e.clientX;
                    y = e.clientY;

                    // The current order
                    // prevEle
                    // draggingEle
                    // placeholder
                    // nextEle
                    const prevEle = draggingEle_row.previousElementSibling;
                    const nextEle = placeholder_row.nextElementSibling;

                    // The dragging element is above the previous element
                    // User moves the dragging element to the top
                    // We don't allow to drop above the header
                    // (which doesn't have `previousElementSibling`)
                    if (prevEle && prevEle.previousElementSibling && isAbove_row(draggingEle_row, prevEle)) {
                        // The current order    -> The new order
                        // prevEle              -> placeholder
                        // draggingEle          -> draggingEle
                        // placeholder          -> prevEle
                        swap_row(placeholder_row, draggingEle_row);
                        swap_row(placeholder_row, prevEle);
                        return;
                    }

                    // The dragging element is below the next element
                    // User moves the dragging element to the bottom
                    if (nextEle && isAbove_row(nextEle, draggingEle_row)) {
                        // The current order    -> The new order
                        // draggingEle          -> nextEle
                        // placeholder          -> placeholder
                        // nextEle              -> draggingEle
                        swap_row(nextEle, placeholder_row);
                        swap_row(nextEle, draggingEle_row);
                    }
                };

                const mouseUpHandler_col = function () {
                    // // Remove the placeholder
                    placeholder_col && placeholder_col.parentNode.removeChild(placeholder_col);

                    draggingEle_col.classList.remove('dragging_col');
                    draggingEle_col.style.removeProperty('top');
                    draggingEle_col.style.removeProperty('left');
                    draggingEle_col.style.removeProperty('position');

                    // Get the end index
                    const endColumnIndex = [].slice.call(list_col.children).indexOf(draggingEle_col);

                    isDraggingStarted_col = false;

                    // Remove the `list` element
                    list_col.parentNode.removeChild(list_col);

                    // Move the dragged column to `endColumnIndex`
                    table.querySelectorAll('tr').forEach(function (row) {
                        const cells = [].slice.call(row.querySelectorAll('th, td'));
                        draggingColumnIndex_col > endColumnIndex
                            ? cells[endColumnIndex].parentNode.insertBefore(
                                  cells[draggingColumnIndex_col],
                                  cells[endColumnIndex]
                              )
                            : cells[endColumnIndex].parentNode.insertBefore(
                                  cells[draggingColumnIndex_col],
                                  cells[endColumnIndex].nextSibling
                              );
                    });

                    // Bring back the table
                    table.style.removeProperty('visibility');

                    // Remove the handlers of `mousemove` and `mouseup`
                    document.removeEventListener('mousemove', mouseMoveHandler_col);
                    document.removeEventListener('mouseup', mouseUpHandler_col);
                };

                const mouseUpHandler_row = function () {
                    // Remove the placeholder
                    placeholder_row && placeholder_row.parentNode.removeChild(placeholder_row);

                    draggingEle_row.classList.remove('dragging_row');
                    draggingEle_row.style.removeProperty('top');
                    draggingEle_row.style.removeProperty('left');
                    draggingEle_row.style.removeProperty('position');

                    // Get the end index
                    const endRowIndex = [].slice.call(list_row.children).indexOf(draggingEle_row);

                    isDraggingStarted_row = false;

                    // Remove the `list` element
                    list_row.parentNode.removeChild(list_row);

                    // Move the dragged row to `endRowIndex`
                    let rows = [].slice.call(table.querySelectorAll('tr'));
                    draggingRowIndex_row > endRowIndex
                        ? rows[endRowIndex].parentNode.insertBefore(rows[draggingRowIndex_row], rows[endRowIndex])
                        : rows[endRowIndex].parentNode.insertBefore(
                              rows[draggingRowIndex_row],
                              rows[endRowIndex].nextSibling
                          );

                    // Bring back the table
                    table.style.removeProperty('visibility');

                    // Remove the handlers of `mousemove` and `mouseup`
                    document.removeEventListener('mousemove', mouseMoveHandler_row);
                    document.removeEventListener('mouseup', mouseUpHandler_row);
                };







                const tbody = table.querySelector('tbody');
                tbody.addEventListener('contextmenu', function (e) {
                    e.preventDefault();

                    const rect = tbody.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    menu.style.top = `${y}px`;
                    menu.style.left = `${x}px`;
                    menu.classList.toggle('container__menu--hidden');

                    document.addEventListener('click', documentClickHandler);
                });

                // Hide the menu when clicking outside of it
                const documentClickHandler = function (e) {
                    const isClickedOutside = !menu.contains(e.target);
                    if (isClickedOutside) {
                        menu.classList.add('container__menu--hidden');
                        document.removeEventListener('click', documentClickHandler);
                    }
                };

                const showColumn = function (index) {
                    cells
                        .filter(function (cell) {
                            return cell.getAttribute('data-column-index') === `${index}`;
                        })
                        .forEach(function (cell) {
                            cell.style.display = '';
                            cell.setAttribute('data-shown', 'true');
                        });

                    menu.querySelectorAll(`[type="checkbox"][disabled]`).forEach(function (checkbox) {
                        checkbox.removeAttribute('disabled');
                    });
                };

                const hideColumn = function (index) {
                    cells
                        .filter(function (cell) {
                            return cell.getAttribute('data-column-index') === `${index}`;
                        })
                        .forEach(function (cell) {
                            cell.style.display = 'none';
                            cell.setAttribute('data-shown', 'false');
                        });
                    // How many columns are hidden
                    const numHiddenCols = headers.filter(function (th) {
                        return th.getAttribute('data-shown') === 'false';
                    }).length;
                    if (numHiddenCols === numColumns - 1) {
                        // There's only one column which isn't hidden yet
                        // We don't allow user to hide it
                        const shownColumnIndex = tbody
                            .querySelector('[data-shown="true"]')
                            .getAttribute('data-column-index');

                        const checkbox = menu.querySelector(
                            `[type="checkbox"][data-column-index="${shownColumnIndex}"]`
                        );
                        checkbox.setAttribute('disabled', 'true');
                    }
                };

                cells.forEach(function (cell, index) {
                    cell.setAttribute('data-column-index', index % numColumns);
                    cell.setAttribute('data-shown', 'true');
                });

                headers.forEach(function (th, index) {
                    // Build the menu item
                    const li = document.createElement('li');
                    const label = document.createElement('label');
                    const checkbox = document.createElement('input');
                    checkbox.setAttribute('type', 'checkbox');
                    checkbox.setAttribute('checked', 'true');
                    checkbox.setAttribute('data-column-index', index);
                    checkbox.style.marginRight = '.25rem';

                    const text = document.createTextNode(th.textContent);

                    label.appendChild(checkbox);
                    label.appendChild(text);
                    label.style.display = 'flex';
                    label.style.alignItems = 'center';
                    li.appendChild(label);
                    menu.appendChild(li);

                    // Handle the event
                    checkbox.addEventListener('change', function (e) {
                        e.target.checked ? showColumn(index) : hideColumn(index);
                        menu.classList.add('container__menu--hidden');
                    });
                });



                table.querySelectorAll('th').forEach(function (headerCell) {
                    headerCell.classList.add('draggable_col');
                    headerCell.addEventListener('mousedown', mouseDownHandler_col);
                });

                table.querySelectorAll('tr').forEach(function (row, index) {
                    // Ignore the header
                    // We don't want user to change the order of header
                    if (index === 0) {
                        return;
                    }

                    const firstCell = row.firstElementChild;
                    firstCell.classList.add('draggable_row');
                    firstCell.addEventListener('mousedown', mouseDownHandler_row);
                });
            });
        </script>


    <!-- / Content -->
  </div>

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>


    <!-- Page JS -->
    <script src="assets/js/dashboards-analytics.js"></script>

    <!-- data tables-->
    <!-- <script type="text/javascript" charset="utf8" src="assets/vendor/DataTables/datatables.js"></script> -->

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>

  </body>
</html>
