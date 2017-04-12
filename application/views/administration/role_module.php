<!-- breadcrumb -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="<?php base_url();?>">Home</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>Role Module</span>
        </li>
    </ul>
</div>
<!-- end breadcrumb -->
<div class="space-4"></div>
<div class="row">
    <div class="col-xs-12">
        <div class="tabbable">
            <ul class="nav nav-tabs">
                <li class="">
                    <a href="javascript:;" data-toggle="tab" aria-expanded="true" id="tab-1">
                        <i class="blue"></i>
                        <strong> Role </strong>
                    </a>
                </li>
                <li class="active">
                    <a href="javascript:;" data-toggle="tab" aria-expanded="true" id="tab-2">
                        <i class="blue"></i>
                        <strong> Module </strong>
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content no-border">
            <div class="row">
                <div class="col-md-7">
                    <table id="grid-table"></table>
                    <div id="grid-pager"></div>
                </div>
                <div class="col-md-5">
                    <div class="portlet red box menu-panel">
                        <div class="portlet-title">
                            <div class="caption">
                                Menu <?php echo $this->input->post('role_name');?>
                            </div>
                            <div class="tools">
                                <a class="collapse" href="javascript:;" data-original-title="" title=""> </a>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div id="info" class="text-center blue">
                                <span> Click a module to view role's menu </span>
                            </div>
                            <button id="btn-save" class="btn btn-danger btn-block" style="display:none;"> Save Changes </button>
                            <div class="space-4"></div>
                            <div id="tree-menu">
                                <!-- tree menu here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$("#tab-1").on("click", function(event) {
    event.stopPropagation();
    loadContentWithParams("administration.roles", {});
});
</script>

<script>

    jQuery(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        jQuery("#grid-table").jqGrid({
            url: '<?php echo WS_JQGRID."administration.role_module_controller/crud"; ?>',
            postData: { role_id : <?php echo $this->input->post('role_id'); ?>},
            datatype: "json",
            mtype: "POST",
            colModel: [
                {label: 'ID', name: 'id', key: true, width: 5, sorttype: 'number', editable: true, hidden: true},
                {label: 'Role ID', name: 'role_id', width: 5, sorttype: 'number', editable: true, hidden: true},
                {label: 'Module', name: 'module_id', width: 250, align: "left", editable: true, hidden:true,
                    editrules: {edithidden: true, required:true},
                    edittype: 'select',
                    editoptions: {
                        dataUrl: "<?php echo WS_JQGRID.'administration.role_module_controller/html_select_options_modules'; ?>",
                        dataInit: function(elem) {
                            $(elem).width(250);  // set the width which you need
                        },
                        postData : {
                            role_id : function() {
                                return <?php echo $this->input->post('role_id'); ?>;
                            },
                            module_id : function(){
                                var selRowId =  $("#grid-table").jqGrid ('getGridParam', 'selrow');
                                var module_id = $("#grid-table").jqGrid('getCell', selRowId, 'module_id');

                                return module_id;
                            }
                        },
                        buildSelect: function (data) {
                            try {
                                var response = $.parseJSON(data);
                                if(response.success == false) {
                                    swal({title: 'Attention', text: response.message, html: true, type: "warning"});
                                    return "";
                                }
                            }catch(err) {
                                return data;
                            }
                        }
                    }
                },
                {label: 'Module Name', name: 'module_name', width: 120, align: "left", editable: false}
            ],
            height: '100%',
            autowidth: true,
            viewrecords: true,
            rowNum: 10,
            rowList: [10,20,50],
            rownumbers: true, // show row numbers
            rownumWidth: 35, // the width of the row numbers columns
            altRows: true,
            shrinkToFit: true,
            multiboxonly: true,
            onSelectRow: function (rowid) {
                //do something
                var module_id = $('#grid-table').jqGrid('getCell', rowid, 'module_id');

                if (module_id != null) {
                    $('#info').hide();
                    $("#btn-save").hide();
                    reloadTreeMenu( <?php echo $this->input->post('role_id'); ?>, module_id );
                    $("#tree-menu").jqxTree("refresh");

                    $("#btn-save").show();
                }
            },
            sortorder:'',
            pager: '#grid-pager',
            jsonReader: {
                root: 'rows',
                id: 'id',
                repeatitems: false
            },
            loadComplete: function (response) {
                if(response.success == false) {
                    swal({title: 'Attention', text: response.message, html: true, type: "warning"});
                }
            },
            //memanggil controller jqgrid yang ada di controller crud
            editurl: '<?php echo WS_JQGRID."administration.role_module_controller/crud"; ?>',
            caption: '<?php echo "Module ".$this->input->post('role_name'); ?>'

        });

        jQuery('#grid-table').jqGrid('navGrid', '#grid-pager',
            {   //navbar options
                edit: true,
                editicon: 'fa fa-pencil blue bigger-120',
                add: true,
                addicon: 'fa fa-plus-circle purple bigger-120',
                del: true,
                delicon: 'fa fa-trash-o red bigger-120',
                search: false,
                searchicon: 'fa fa-search orange bigger-120',
                refresh: true,
                afterRefresh: function () {
                    // some code here
                    $("#info").show();
                    $("#btn-save").hide();
                    $("#tree-menu").jqxTree("clear");
                },

                refreshicon: 'fa fa-refresh green bigger-120',
                view: false,
                viewicon: 'fa fa-search-plus grey bigger-120'
            },

            {
                // options for the Edit Dialog
                closeAfterEdit: true,
                closeOnEscape:true,
                recreateForm: true,
                serializeEditData: serializeJSON,
                width: 'auto',
                errorTextFormat: function (data) {
                    return 'Error: ' + data.responseText
                },
                beforeShowForm: function (e, form) {
                    var form = $(e[0]);
                    style_edit_form(form);

                },
                afterShowForm: function(form) {
                    form.closest('.ui-jqdialog').center();
                },
                afterSubmit:function(response,postdata) {
                    var response = jQuery.parseJSON(response.responseText);
                    if(response.success == false) {
                        return [false,response.message,response.responseText];
                    }
                    return [true,"",response.responseText];
                }
            },
            {
                editData: {
                    role_id: function() {
                        return <?php echo $this->input->post('role_id'); ?>;
                    }
                },
                //new record form
                closeAfterAdd: true,
                clearAfterAdd : true,
                closeOnEscape:true,
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
                },
                afterShowForm: function(form) {
                    form.closest('.ui-jqdialog').center();
                },
                afterSubmit:function(response,postdata) {
                    var response = jQuery.parseJSON(response.responseText);
                    if(response.success == false) {
                        return [false,response.message,response.responseText];
                    }

                    $(".tinfo").html('<div class="ui-state-success">' + response.message + '</div>');
                    var tinfoel = $(".tinfo").show();
                    tinfoel.delay(3000).fadeOut();


                    return [true,"",response.responseText];
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
                afterShowForm: function(form) {
                    form.closest('.ui-jqdialog').center();
                },
                onClick: function (e) {
                    //alert(1);
                },
                afterSubmit:function(response,postdata) {
                    var response = jQuery.parseJSON(response.responseText);
                    if(response.success == false) {
                        return [false,response.message,response.responseText];
                    }

                    $("#info").show();
                    $("#btn-save").hide();
                    $("#tree-menu").jqxTree("clear");

                    return [true,"",response.responseText];
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
        $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() );
        $(pager_selector).jqGrid( 'setGridWidth', parent_column.width() );
    }

</script>

<script>

    function reloadTreeMenu(r_id, mod_id) {

        var source =
        {
            datatype: "json",
            datafields: [
                { name: 'id' },
                { name: 'parentid' },
                { name: 'text' },
                { name: 'value' },
                { name: 'checked' },
                { name: 'icon' }
            ],
            id: 'id',
            data: {role_id: r_id, module_id : mod_id},
            url: '<?php echo WS_JQGRID."administration.role_menu_controller/getTreeJson"; ?>',
            async: false
        };

        $("#tree-menu").jqxTree("clear");

        var dataAdapter = new $.jqx.dataAdapter(source);
        dataAdapter.dataBind();
        var records = dataAdapter.getRecordsHierarchy('id', 'parentid', 'items', [{ name: 'text', map: 'label'}]);
        var tree = $("#tree-menu").jqxTree({
            source: records,
            checkboxes: true
        });
    }

    $(function($) {
        $('#btn-save').on('click', function() {

            var itemsChecked = $('#tree-menu').jqxTree('getCheckedItems');
            var itemsUnchecked = $('#tree-menu').jqxTree('getUncheckedItems');

            var strChecked = [];
            var strUnchecked = [];

            for (var c = 0; c < itemsChecked.length; c++) {
                strChecked.push(itemsChecked[c].value);
            }

            for (var u = 0; u < itemsUnchecked.length; u++) {
                strUnchecked.push(itemsUnchecked[u].value);
            }

            var selRowId =  $("#grid-table").jqGrid ('getGridParam', 'selrow');
            var role_id = $("#grid-table").jqGrid('getCell', selRowId, 'role_id');
            var module_id = $("#grid-table").jqGrid('getCell', selRowId, 'module_id');

            $.ajax({
                type: 'POST',
                datatype: "json",
                url: '<?php echo WS_JQGRID."administration.role_menu_controller/create"; ?>',
                data: { role_id: role_id, items_checked: strChecked.toString(), items_unchecked: strUnchecked.toString()},
                success: function(response) {
                    if(response.success == false) {
                        swal({title: 'Attention', text: response.message, html: true, type: "warning"});
                    }else {
                        swal({title: 'Success', text: response.message, html: true, type: "success"});
                    }
                }
            });

        });

        reloadTreeMenu(null, null);
    });
</script>