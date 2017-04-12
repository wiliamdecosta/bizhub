<!-- breadcrumb -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="<?php base_url();?>">Home</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <a href="#">Tracking</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>Stock Summary Bizhub</span>
        </li>
    </ul>
</div>

<div class="space-4"></div>
<h3>Stock Summary Bizhub</h3>

<div class="row">
    <div class="col-md-10">
        <table class="table table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Code</th>
                    <th>Raw Material (Kg)</th>
                    <th>In Progress (Kg)</th>
                    <th>Packing (Kg)</th>
                </tr>
            </thead>
            <tbody id="tbody-summary">

            </tbody>
        </table>
    </div>
</div>

<script>

    $(function() {

        $.ajax({
            url: '<?php echo WS_JQGRID."agripro.stock_summary_controller/getFullSummaryBizhub"; ?>',
            type: "POST",
            success: function (response) {
                $( "#tbody-summary" ).html( response );
            },
            error: function (xhr, status, error) {
                swal({title: "Error!", text: xhr.responseText, html: true, type: "error"});
            }
        });

    });

</script>