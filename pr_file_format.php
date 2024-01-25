<?php
include "header.php";


$user_id = $_SESSION["id"];

$stmt_scheme_list = $obj->con1->prepare("SELECT * FROM `tbl_service_master` WHERE status='active'");
$stmt_scheme_list->execute();
$scheme_result = $stmt_scheme_list->get_result();
$stmt_scheme_list->close();


if (isset($_REQUEST['btnsubmit'])) {
    $scheme_id = $_REQUEST['scheme_id'];
    $stage = $_REQUEST['stage'];
    $name = $_REQUEST['f_name'];
    $doc_type = $_REQUEST['doc_type'];
    $p_path = $_FILES['doc_file']['tmp_name'];
    $doc_file = $_REQUEST['doc_file']['name'];
    $get_data_type = $_REQUEST['get_data_type'];
    $page_name = $_REQUEST['page_name'];

    if ($_FILES["doc_file"]["name"] != "") {
        if (file_exists("pr_file_format/" . $doc_file)) {
            $i = 0;
            $PicFileName = $_FILES["doc_file"]["name"];
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("pr_file_format/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $_FILES["doc_file"]["name"];
        }
    }

    try {
        $stmt = $obj->con1->prepare("INSERT INTO pr_file_format(scheme_id, stage_id, file_name, doc_type, doc_file, get_data_type, page_name) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("iisssss", $scheme_id,$stage, $name, $doc_type, $PicFileName, $get_data_type, $page_name);
        $Resp = $stmt->execute();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }
    $stmt->close();

    if ($Resp) {
        move_uploaded_file($p_path, "pr_file_format/" . $PicFileName);
        setcookie("msg", "data", time() + 3600, "/");
        header("location:pr_file_format.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:pr_file_format.php");
    }
}
if (isset($_REQUEST['btnupdate'])) {
    $id = $_REQUEST['ttId'];
    $scheme_id = $_REQUEST['scheme_id'];
    $stage = $_REQUEST['stage'];
    $name = $_REQUEST['f_name'];
    $rpp = $_REQUEST['hdoc_file'];
    $srcpp = $_FILES['doc_file']['tmp_name'];
    $doc_type = $_REQUEST['doc_type'];
    $doc_file = $_FILES['doc_file']['name'];
    $get_data_type = $_REQUEST['get_data_type'];
    $page_name = $_REQUEST['page_name'];

    if ($doc_file != "") {
        unlink("pr_file_format/" . $rpp);
        move_uploaded_file($srcpp, "pr_file_format/" . $doc_file);
    } else {
        $doc_file = $rpp;
    }


    try {
        $stmt = $obj->con1->prepare("UPDATE pr_file_format SET scheme_id=?, stage_id=?, file_name=?, doc_type=?, doc_file=?, get_data_type=?, page_name=? where fid=? ");
        $stmt->bind_param("iisssssi", $scheme_id, $stage, $name, $doc_type, $doc_file, $get_data_type, $page_name, $id);
        $Resp = $stmt->execute();
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data", time() + 3600, "/");
        header("location:pr_file_format.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:pr_file_format.php");
    }
}
if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
    $file = $_REQUEST["file"];
    $stmt_del = $obj->con1->prepare("delete from pr_file_format where fid='" . $_REQUEST["n_id"] . "'");
    $Resp = $stmt_del->execute();
    $stmt_del->close();

    if ($Resp) {
        if (file_exists("pr_file_format/" . $file)) {
            unlink("pr_file_format/" . $file);
        }
        header("location:pr_file_format.php?msg=data_del");
    } else {
        header("location:pr_file_format.php?msg=fail");
    }
}

if (isset($_REQUEST["msg"])) {

    if ($_REQUEST['msg'] == "data") {

        ?>
        <div class="alert alert-primary alert-dismissible" role="alert">
            Data added succesfully
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            </button>
        </div>
        <?php
    }
    if ($_REQUEST['msg'] == "update") {

        ?>
        <div class="alert alert-primary alert-dismissible" role="alert">
            Data updated succesfully
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            </button>
        </div>
        <?php
    }
    if ($_REQUEST['msg'] == "data_del") {

        ?>
        <div class="alert alert-primary alert-dismissible" role="alert">
            Data deleted succesfully
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            </button>
        </div>
        <?php
    }
    if ($_REQUEST['msg'] == "fail") {
        ?>

        <div class="alert alert-danger alert-dismissible" role="alert">
            An error occured! Try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            </button>
        </div>
        <?php
    }
}


?>


<div class="row">
    <div class="col-xl">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">File Format</h5>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="ttId" id="ttId">
                    <div class="mb-3">
                        <label class="form-label" for="basic-default-fullname">Scheme</label>
                        <select name="scheme_id" id="scheme_id" class="form-control" onchange="get_stage(this.value)" required>
                            <option value="" selected>Select Scheme</option>
                            <?php while ($scheme_list = mysqli_fetch_array($scheme_result)) { ?>
                                <option value="<?php echo $scheme_list["id"] ?>"><?php echo $scheme_list["service"] ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="basic-default-fullname">Stage</label>
                        <select name="stage" id="stage" class="form-control" required>
                            <option value="">Select Stage</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="basic-default-company">File Name</label>
                        <input type="text" class="form-control" name="f_name" id="f_name" required />
                    </div>

                    <div class="mb-3">
                      <label class="form-label" for="basic-default-fullname">Documnet type</label>
                      <div class="form-check form-check-inline mt-3">
                        <input class="form-check-input" type="radio" name="doc_type" id="word" value="word" required>
                        <label class="form-check-label" for="inlineRadio1">Word</label>
                      </div>
                      <div class="form-check form-check-inline mt-3">
                        <input class="form-check-input" type="radio" name="doc_type" id="excel" value="excel" required>
                        <label class="form-check-label" for="inlineRadio1">Excel</label>
                      </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="basic-default-fullname">Document File</label>
                        <input type="file" class="form-control" onchange="readURL(this)" name="doc_file" id="doc_file"
                            required />
                            <span  id="check" ></span>
                        <a href="" name="download_file" id="download_file" download style="display:none;">Download</a>
                        <input type="hidden" name="hdoc_file" id="hdoc_file" />
                    </div>

                    <div class="mb-3">
                      <label class="form-label" for="basic-default-fullname">Data</label>
                      <div class="form-check form-check-inline mt-3">
                        <input class="form-check-input" type="radio" name="get_data_type" id="fetch" value="fetch" required>
                        <label class="form-check-label" for="inlineRadio1">Fetch</label>
                      </div>
                      <div class="form-check form-check-inline mt-3">
                        <input class="form-check-input" type="radio" name="get_data_type" id="retrieve" value="retrieve" required>
                        <label class="form-check-label" for="inlineRadio1">Retrieve</label>
                      </div>
                      <div class="form-check form-check-inline mt-3">
                        <input class="form-check-input" type="radio" name="get_data_type" id="calculate" value="calculate" required>
                        <label class="form-check-label" for="inlineRadio1">Calculate</label>
                      </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="basic-default-company">Page Name</label>
                        <input type="text" class="form-control" name="page_name" id="page_name" required />
                    </div>

                    <button type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary">Submit</button>
                    <button type="submit" name="btnupdate" id="btnupdate" class="btn btn-primary "
                        hidden>Update</button>
                    <button type="reset" name="btncancel" id="btncancel" class="btn btn-secondary"
                        onclick="window.location.reload()"> Cancel</button>
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
                    <th>Service</th>
                    <th>Stage</th>
                    <th>File Name</th>
                    <th>Document Type</th>
                    <th>Type</th>
                    <th>Page Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                <?php

                $stmt_list = $obj->con1->prepare("SELECT f1.*, s1.service, st1.stage_name from pr_file_format f1, tbl_service_master s1, tbl_tdstages st1 where f1.scheme_id=s1.id and f1.stage_id=st1.stage_id order by f1.fid desc");
                $stmt_list->execute();
                $result = $stmt_list->get_result();
                $stmt_list->close();
                $i = 1;

                while ($data = mysqli_fetch_array($result)) {
                    ?>

                    <tr>
                        <td><?php echo $i ?></td>
                        <td><?php echo $data["service"] ?></td>
                        <td><?php echo $data["stage_name"] ?></td>
                        <td><?php echo $data["file_name"] ?></td>
                        <td><?php echo $data["doc_type"] ?></td>
                        <td><?php echo $data["get_data_type"] ?></td>
                        <td><?php echo $data["page_name"] ?></td>
                        <td>
                            <a href="javascript:editdata('<?php echo $data["fid"] ?>','<?php echo $data["scheme_id"] ?>','<?php echo $data["stage_id"] ?>','<?php echo $data["file_name"] ?>','<?php echo $data["doc_type"] ?>','<?php echo $data["doc_file"] ?>','<?php echo $data["get_data_type"] ?>','<?php echo $data["page_name"] ?>');">
                                <i class="bx bx-edit-alt me-1"></i> </a>

                            <a href="javascript:viewdata('<?php echo $data["scheme_id"] ?>','<?php echo $data["stage_id"] ?>','<?php echo $data["file_name"] ?>','<?php echo $data["doc_type"] ?>','<?php echo $data["doc_file"] ?>','<?php echo $data["get_data_type"] ?>','<?php echo $data["page_name"] ?>');">
                                View</a>

                            <a href="javascript:deletedata('<?php echo $data["fid"] ?>','<?php echo $data["doc_file"] ?>')">Delete</a>
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

<script type="text/javascript">

    function get_stage(scheme_id) {
        $.ajax({
          async: false,
          type: "POST",
          url: "ajaxdata.php?action=get_stage",
          data: "scheme_id="+scheme_id,
          cache: false,
          success: function(result){
            $('#stage').html(result);
            }
        });
    }

    function readURL(input) {
        if (input.files && input.files[0]) {
            var filename = input.files.item(0).name;

            var reader = new FileReader();
            var extn = filename.split(".");

            if(extn[1].toLowerCase() == "xlsx" || extn[1].toLowerCase() == "xls" || extn[1].toLowerCase() == "docx" || extn[1].toLowerCase() == "doc"){
                // reader.onload = function (e) {
                //     $('#PreviewImageP').attr('src', e.target.result);
                //     document.getElementById("PreviewImageP").style.display = "block";
                // };

                reader.readAsDataURL(input.files[0]);
                $('#check').html("");
                document.getElementById('btnsubmit').disabled = false;
            }
            else {
                $('#check').html("Please Select proper document type");
                document.getElementById('btnsubmit').disabled = true;
            }
        }
    }

    function deletedata(id, doc_file) {
        if (confirm("Are you sure to DELETE data?")) {
            var loc = "pr_file_format.php?flg=del&n_id=" + id + "&file=" + doc_file;
            window.location = loc;
        }
    }
    function editdata(id, scheme_id, stage, f_name, doc_type, doc_file, get_data_type, page_name) {
        $('#ttId').val(id);
        $('#scheme_id').val(scheme_id);
        get_stage(scheme_id);
        $('#stage').val(stage);
        $('#f_name').val(f_name);
        $('#hdoc_file').val(doc_file);
        $('#doc_file').removeAttr("required");
        $('#download_file').show();
        $('#download_file').attr('href','pr_file_format/'+doc_file);
        
        // $('#download_file').attr("src",doc_file);
        if(doc_type=="word")
        {
            $('#word').prop("checked","checked"); 
        }
        else if(doc_type=="excel")
        {
            $('#excel').prop("checked","checked");  
        }

        if(get_data_type=="fetch")
        {
            $('#fetch').prop("checked","checked"); 
        }
        else if(get_data_type=="retrieve")
        {
            $('#retrieve').prop("checked","checked");  
        }
        else if(get_data_type=="calculate")
        {
            $('#calculate').prop("checked","checked");  
        }

        $('#page_name').val(page_name);

        $('#btnsubmit').attr('hidden', true);
        $('#btnupdate').removeAttr('hidden');
       
    }
    function viewdata(scheme_id, stage, f_name, doc_type, doc_file, get_data_type, page_name) {
        $('#scheme_id').val(scheme_id);
        get_stage(scheme_id);
        $('#stage').val(stage);
        $('#f_name').val(f_name);
        $('#download_file').show();
        $('#download_file').attr('href','pr_file_format/'+doc_file);
        /*$('#download_file').show();
        $('#download_file').attr("src",doc_file);*/
        if(doc_type=="word")
        {
            $('#word').prop("checked","checked"); 
        }
        else if(doc_type=="excel")
        {
            $('#excel').prop("checked","checked");  
        }

        if(get_data_type=="fetch")
        {
            $('#fetch').prop("checked","checked"); 
        }
        else if(get_data_type=="retrieve")
        {
            $('#retrieve').prop("checked","checked");  
        }
        else if(get_data_type=="calculate")
        {
            $('#calculate').prop("checked","checked");  
        }

        $('#page_name').val(page_name);

        $('#btnsubmit').attr('hidden', true);
        $('#btnsubmit').attr('disabled', true);
        $('#btnupdate').attr('hidden', true);
        
    }
</script>

<?php
include "footer.php";
?>
