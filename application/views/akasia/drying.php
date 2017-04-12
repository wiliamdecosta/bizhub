<!-- breadcrumb -->

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="<?php base_url(); ?>">Home</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <a href="#">Drying</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>Drying Raw Material</span>
        </li>
    </ul>
</div>
<!-- end breadcrumb -->
<div class="space-4"></div>
<!--<div class="alert alert-info">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
    <strong>Info!</strong> Proses pengeringan hanya menimbang ulang berat Raw Material!
</div>-->
<div class="row">
    <div class="col-md-12">
        <table id="grid-table"></table>
        <div id="grid-pager"></div>
    </div>
</div>
<div class="space-4"></div>
<div class="row" id="detail_placeholder" style="display:none;">
    <div class="col-xs-12">
        <table id="grid-table-detail"></table>
        <div id="grid-pager-detail"></div>
    </div>
</div>

<?php $this->load->view('lov/lov_farmer.php'); ?>
<?php $this->load->view('lov/lov_raw_material.php'); ?>
<?php $this->load->view('lov/lov_plantation.php'); ?>

<script>

    function showLovFarmer(id, code) {
        modal_lov_farmer_show(id, code);
    }

    function clearLovFarmer() {
        $('#form_fm_id').val('');
        $('#form_fm_code').val('');
    }


    function showLovRawMaterial(id, code) {
        modal_lov_raw_material_show(id, code);
    }

    function clearLovRawMaterial() {
        $('#form_rm_id').val('');
        $('#form_rm_code').val('');
    }

    function showLovPlantation(id, code) {

        selRowId = $('#grid-table').jqGrid('getGridParam', 'selrow');
        // fm_id = $('#grid-table').jqGrid('getCell', selRowId, 'fm_id');
        if ($('#form_fm_id').val() == "") {
            swal({title: 'Attention', text: 'Please choose farmer', html: true, type: "info"});
            return;
        }
        modal_lov_plantation_show(id, code, $('#form_fm_id').val());
    }

    function clearLovPlantation() {
        $('#form_plt_id').val('');
        $('#form_plt_code').val('');
    }

    jQuery(function ($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        jQuery("#grid-table").jqGrid({
            url: '<?php echo WS_JQGRID . "agripro.drying_bizhub_controller/crud"; ?>',
            datatype: "json",
            mtype: "POST",
			postData :{is_drying:1},
            colModel: [
                {label: 'ID', name: 'in_biz_det_id', key: true, width: 5, sorttype: 'number', editable: true, hidden: true},
                {label: 'ID', name: 'in_packing_id',  width: 5, sorttype: 'number', editable: true, hidden: true},
                {label: 'ID', name: 'wh_id', width: 5, sorttype: 'number', editable: true, hidden: true},
                {
                    label: 'Packing Label', name: 'packing_batch_number', width: 250, align: "left", editable: true,
                    editoptions: {
                        size: 30,
                        maxlength: 32,
						readonly:"readonly"
                    },
                    editrules: {required: false}
                },
                {
                    label: 'product_id',
                    name: 'product_id',
                    width: 150,
                    align: "left",
                    editable: true,
                    hidden: true,
                    editrules: {required: false}
                },
                {
                    label: 'RM Name', name: 'product_code', width: 120, align: "left", editable: false
                },
				{
                    label: 'Bruto Qty (Kgs)', name: 'qty_bruto', width: 120, align: "left", editable: true,hidden:true,
                    editoptions: {
                        size: 10,
						readonly:"readonly"
                    },
                    editrules: {required: false}
                },
				{
                    label: 'Bruto Qty (Kgs)', name: 'qty_rescale', width: 120, align: "left", editable: true,hidden:false,
                    editoptions: {
                        size: 10,
						readonly:"readonly"
                    },
                    editrules: {required: false, edithidden:true}
                },
                {
                    label: 'Drying Qty (Kgs)', name: 'qty_netto_init', width: 120, align: "left", editable: true,
                    editoptions: {
                        size: 10,
                        maxlength: 4
                    },
                    editrules: {required: true}
                },
                {
                    label: 'Drying Qty (Kgs)', name: 'qty_netto', width: 120, align: "left", editable: true, hidden:true,
                    editoptions: {
                        size: 10,
                        maxlength: 4
                    },
                    editrules: {required: true}
                },
                {
                    label: 'Drying Date', name: 'in_biz_drying_date', width: 120, editable: true,
                    edittype: "text",
                    editrules: {required: true},
                    editoptions: {
                        // dataInit is the client-side event that fires upon initializing the toolbar search field for a column
                        // use it to place a third party control to customize the toolbar
                        dataInit: function (element) {
                            $(element).datepicker({
                                autoclose: true,
                                format: 'yyyy-mm-dd',
                                orientation: 'up',
                                todayHighlight: true
                            });
                        },
                        size: 25
                    }
                }
            ],
            height: '100%',
            width: '100%',
            autowidth: true,
            viewrecords: true,
            rowNum: 10,
            rowList: [10, 20, 50],
            rownumbers: true, // show row numbers
            rownumWidth: 35, // the width of the row numbers columns
            altRows: true,
            shrinkToFit: true,
            multiboxonly: true,
            onSelectRow: function (rowid) {

            },
            sortorder: '',
            pager: '#grid-pager',
            jsonReader: {
                root: 'rows',
                id: 'id',
                repeatitems: false
            },
            loadComplete: function (response) {
                if (response.success == false) {
                    swal({title: 'Attention', text: response.message, html: true, type: "warning"});
                }
            },
            //memanggil controller jqgrid yang ada di controller crud
            editurl: '<?php echo WS_JQGRID . "agripro.drying_bizhub_controller/crud"; ?>',
            caption: "Drying"

        });

        jQuery('#grid-table').jqGrid('navGrid', '#grid-pager',
            {   //navbar options
                edit: true,
                editicon: 'fa fa-pencil blue bigger-120',
                add: false,
                addicon: 'fa fa-plus-circle purple bigger-120',
                del: false,
                delicon: 'fa fa-trash-o red bigger-120',
                search: true,
                searchicon: 'fa fa-search orange bigger-120',
                refresh: true,
                afterRefresh: function () {
                    // some code here
                    jQuery("#detail_placeholder").hide();
                },

                refreshicon: 'fa fa-refresh green bigger-120',
                view: false,
                viewicon: 'fa fa-search-plus grey bigger-120'
            },

            {
                // options for the Edit Dialog
                closeAfterEdit: true,
                closeOnEscape: true,
                recreateForm: true,
                viewPagerButtons: false,
                serializeEditData: serializeJSON,
                width: 'auto',
                errorTextFormat: function (data) {
                    return 'Error: ' + data.responseText
                },
                beforeShowForm: function (e, form) {
                    var form = $(e[0]);
                    style_edit_form(form);
                    var bruto = $("#qty_rescale");
                    var netto = $("#qty_netto_init");
                    var netto2 = $("#qty_netto");
                    $("#sm_no_trans").prop("readonly", true);
                    bruto.prop("readonly", true);
                    netto.val(bruto.val());
                    netto2.val(bruto.val());

                },

                afterShowForm: function (form) {
                    form.closest('.ui-jqdialog').center();
                },
                beforeSubmit: function (response, postdata) {
                    var bruto = $("#qty_rescale").val();
                    var netto = $("#qty_netto_init").val();
                    if (netto > bruto) {
                        if (confirm('Netto greater then bruto, are you sure ?')) {
                            return [true, "", response.responseText];
                        } else {
                            return false;
                        }
                    } else {
                        return [true, "", response.responseText];
                    }

                },
                afterSubmit: function (response, postdata) {
                    var response = jQuery.parseJSON(response.responseText);
                    if (response.success == false) {
                        return [false, response.message, response.responseText];
                    }
                    return [true, "", response.responseText];
                }
            },
            {
                //new record form
                closeAfterAdd: true,
                clearAfterAdd: true,
                closeOnEscape: true,
                recreateForm: true,
                width: 'auto',
                errorTextFormat: function (data) {
                    return 'Error: ' + data.responseText
                },
                serializeEditData: serializeJSON,
                viewPagerButtons: false,
                beforeShowForm: function (e, form) {
                    var form = $(e[0]);
                    style_edit_form(form);
                    /*form.css({"height": 0.70 * screen.height + "px"});
                     form.css({"width": 0.60 * screen.width + "px"});*/

                    $("#sm_no_trans").prop("readonly", true);
                    setTimeout(function () {
                        clearLovFarmer();
                        clearLovPlantation();
                    }, 100);
                },
                afterShowForm: function (form) {
                    form.closest('.ui-jqdialog').center();
                },
                afterSubmit: function (response, postdata) {
                    var response = jQuery.parseJSON(response.responseText);
                    if (response.success == false) {
                        return [false, response.message, response.responseText];
                    }

                    $(".tinfo").html('<div class="ui-state-success">' + response.message + '</div>');
                    var tinfoel = $(".tinfo").show();
                    tinfoel.delay(3000).fadeOut();


                    return [true, "", response.responseText];
                }
            },
            {
                //delete record form
                serializeDelData: serializeJSON,
                recreateForm: true,
                beforeShowForm: function (e) {
                    var form = $(e[0]);
                    style_delete_form(form);

                },
                afterShowForm: function (form) {
                    form.closest('.ui-jqdialog').center();
                },
                onClick: function (e) {
                    //alert(1);
                },
                afterSubmit: function (response, postdata) {
                    var response = jQuery.parseJSON(response.responseText);
                    if (response.success == false) {
                        return [false, response.message, response.responseText];
                    }
                    return [true, "", response.responseText];
                }
            },
            {
                //search form
                closeAfterSearch: false,
                recreateForm: true,
                afterShowSearch: function (e) {
                    var form = $(e[0]);
                    style_search_form(form);
                    form.closest('.ui-jqdialog').center();
                },
                afterRedraw: function () {
                    style_search_filters($(this));
                }
            },
            {
                //view record form
                recreateForm: true,
                beforeShowForm: function (e) {
                    var form = $(e[0]);
                }
            }
        );


    });

    function responsive_jqgrid(grid_selector, pager_selector) {
        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(grid_selector).jqGrid('setGridWidth', $(".page-content").width());
        $(pager_selector).jqGrid('setGridWidth', parent_column.width());
    }

</script>