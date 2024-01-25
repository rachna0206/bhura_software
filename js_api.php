<?php
include("header.php");

?>

<div class="row">
	<div class="col-lg-12" id="data">
		

	</div>

</div>
<script type="text/javascript">
$.ajax({
    type: "POST",
    url: "https://fakestoreapi.com/products/1",
    data: "",
    success: function (result) {          
        
          
        $('#data').html();
        
    }
});
</script>
<?php
include ("footer.php");
?>