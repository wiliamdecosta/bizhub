<!-- breadcrumb -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="<?php base_url();?>">Home</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>Role</span>
        </li>
    </ul>
</div>
<!-- end breadcrumb -->
<div class="space-4"></div>
<div class="row">
    <div class="col-xs-12">
        <div class="tabbable">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="javascript:;" data-toggle="tab" aria-expanded="true" id="tab-1">
                        <i class="blue"></i>
                        <strong> Role </strong>
                    </a>
                </li>
                <li class="">
                    <a href="javascript:;" data-toggle="tab" aria-expanded="true" id="tab-2">
                        <i class="blue"></i>
                        <strong> Module </strong>
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content no-border">
            <div class="row">
                <div class="col-md-12">
                    <table id="grid-table"></table>
                    <div id="grid-pager"></div>
                </div>
            </div>
            <div class="space-4"></div>
            <hr>
            <div class="row" id="detail_placeholder" style="display:none;">
                <div class="col-xs-12 col-md-3">
                    <div class="form-group">
                        <button class="btn btn-danger btn-block" id="btn-save">
                            Save Changes
                        </button>
                    </div>
                </div>
                <div class="col-xs-12">
                    <table id="grid-table-detail"></table>
                    <div id="grid-pager-detail"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$("#tab-2").on("click", function(event) {

    event.stopPropagation();
    var grid = $('#grid-table');
    role_id = grid.jqGrid ('getGridParam', 'selrow');
    role_name = grid.jqGrid ('getCell', role_id, 'role_name');

    if(role_id == null) {
        swal('Informasi','Silahkan pilih salah satu module','info');
        return false;
    }

    loadContentWithParams("administration.role_module", {
        role_id: role_id,
        role_name : role_name
    });
});
</script>
<script>
    function savePermission() {
        var grid = $("#grid-table-detail");
        var dataGrid = grid.jqGrid('getRowData');

        var selRowId =  $("#grid-table").jqGrid ('getGridParam', 'selrow');
        var role_id = $("#grid-table").jqGrid('getCell', selRowId, 'role_id');
        var role_name = $("#grid-table").jqGrid('getCell', selRowId, 'role_name');

        var items = new Array();
        if( dataGrid.length > 0) {
            for(var row = 0; row < dataGrid.length; row++) {
                items[row] = {};
                items[row].id = dataGrid[row].role_permissions_id;
                items[row].val = $('input[name="permission'+dataGrid[row].role_permissions_id+'"]:checked').val();
            }

            var ajaxOptions = {
                url: '<?php echo WS_JQGRID."administration.permission_role_controller/crud"; ?>',
                type: "POST",
                dataType: "json",
                data: { oper:'add', 'role_id':role_id, items: JSON.stringify(items) },
                success: function (data) {
                    if(data.success == true) {
                        swal('Success',data.message,'success');
                        grid.trigger("reloadGrid");
                    }else {
                        swal('Attention',data.message,'warning');
                    }
                },
                error: function (xhr, status, error) {
                    swal({title: "Error!", text: xhr.responseText, html: true, type: "error"});
                }
            };

            $.ajax({
                beforeSend: function( xhr ) {
                    swal({
                        title: "Confirmation",
                        text: 'Are You sure to update data permission for <strong class="text-blue">'+ role_name +'</strong> role ?',
                        type: "info",
                        showCancelButton: true,
                        showLoaderOnConfirm: true,
                        confirmButtonText: "Yes, Do It",
                        confirmButtonColor: "#00a65a",
                        cancelButtonText: "Cancel",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                        html: true
                    },
                    function(isConfirm){
                        if(isConfirm) {
                            $.ajax(ajaxOptions);
                            return true;
                        }else {
                            return false;
                        }
                    });
                }
            });
        }
    }

    $('#btn-save').on('click', function() {
        savePermission();
    });

</script>
<script>
    jQuery(function($) {
        var grid_selector = "#grid-table";
        var pager_selector = "#grid-pager";

        jQuery("#grid-table").jqGrid({
            url: '<?php echo WS_JQGRID."administration.roles_controller/crud"; ?>',
            datatype: "json",
            mtype: "POST",
            colModel: [
                {label: 'ID', name: 'role_id', key: true, width: 5, sorttype: 'number', editable: true, hidden: true},
                {label: 'Role Name',name: 'role_name',width: 150, align: "left",editable: true,
                    editoptions: {
                        size: 30,
                        maxlength:32,
                    },
                    editrules: {required: true}
                },
                {label: 'Description',name: 'description',width: 200, align: "left",editable: true,
                    edittype:'textarea',
                    editoptions: {
                        rows: 2,
                        cols:50
                    }
                },
                {label: 'Status Aktif',name: 'is_active',width: 120, align: "left",editable: true, edittype: 'select', hidden:true,
                    editrules: {edithidden: true, required: false},
                    editoptions: {
                    value: "Y:AKTIF;N:TIDAK AKTIF",
                    dataInit: function(elem) {
                        $(elem).width(150);  // set the width which you need
                    }
                }},
                {label: 'Status Aktif', name: 'status_active', width: 120, align: "left", editable: false}
            ],
            height: '100%',
            autowidth: true,
            viewrecords: true,
            rowNum: 5,
            rowList: [5, 10, 20],
            rownumbers: true, // show row numbers
            rownumWidth: 35, // the width of the row numbers columns
            altRows: true,
            shrinkToFit: true,
            multiboxonly: true,
            onSelectRow: function (rowid) {
                /*do something when selected*/
				var celValue = $('#grid-table').jqGrid('getCell', rowid, 'role_id');
                var celCode = $('#grid-table').jqGrid('getCell', rowid, 'role_name');

                var grid_detail = $("#grid-table-detail");
                if (rowid != null) {
                    grid_detail.jqGrid('setGridParam', {
                        url: '<?php echo WS_JQGRID."administration.permission_role_controller/crud"; ?>',
                        postData: {role_id: rowid}
                    });
                    var strCaption = 'Permission Role :: ' + celCode;
                    grid_detail.jqGrid('setCaption', strCaption);
                    $("#grid-table-detail").trigger("reloadGrid");
                    $("#detail_placeholder").show();

                    responsive_jqgrid('#grid-table-detail', '#grid-pager-detail');
                }

            },
            sortorder:'',
            pager: '#grid-pager' ,
            jsoncruder: {
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
            editurl: '<?php echo WS_JQGRID."administration.roles_controller/crud"; ?>',
            caption: "Roles"

        });

        jQuery('#grid-table').jqGrid('navGrid', '#grid-pager',
            {   //navbar options
                edit: true,
                editicon: 'fa fa-pencil blue bigger-120',
                add: true,
                addicon: 'fa fa-plus-circle purple bigger-120',
                del: true,
                delicon: 'fa fa-trash-o red bigger-120',
                search: true,
                searchicon: 'fa fa-search orange bigger-120',
                refresh: true,
                afterRefresh: function () {
                    // some code here
                    jQuery("#detailsPlaceholder").hide();
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
                //new record form
                closeAfterAdd: false,
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

	/**
     * ---------------------------------------------------------------------
     * |  jqgrid table detail
     * ---------------------------------------------------------------------
     */
    $("#grid-table-detail").jqGrid({
        url: '<?php echo WS_JQGRID."administration.permission_role_controller/crud"; ?>',
        datatype: "json",
        mtype: "POST",
        colModel: [
            {label: 'ID', key:true, name: 'role_permissions_id', width: 5, sorttype: 'number', editable: true, hidden: true},
            {label: 'Permission ID',  name: 'permission_id', width: 5, sorttype: 'number',editable: true, hidden: true},
            {label: 'Role ID', name: 'role_id', width: 5, sorttype: 'number', editable: true, hidden: true},
            {label: 'Yes', name: 'status_permission', width: 40,  sortable:false, search:false, align: "left", align:"center", editable: false,
                formatter: function(cellvalue, options, rowObject) {
                    var  theID = rowObject['role_permissions_id'];
                    var checked = '';
                    if (cellvalue == 'Yes')
                        checked = ' checked="checked"';

                    return '<input type="radio" class="jqgrid-radio" name="permission'+theID+'" value="Yes"'+checked+'> <span class="green">Yes</span>';
                }
            },
            {label: 'No', name: 'status_permission', width: 40,  sortable:false, search:false, align: "left", align:"center", editable: false,
                formatter: function(cellvalue, options, rowObject) {
                    var  theID = rowObject['role_permissions_id'];
                    var checked = '';
                    if (cellvalue == 'No')
                        checked = ' checked="checked"';

                    return '<input type="radio" class="jqgrid-radio" name="permission'+theID+'" value="No"'+checked+'> <span class="red">No</span>';
                }
            },
            {label: 'Action', name: 'permission_name', width: 200, align: "left", editable: false},
            {label: 'Display Name', name: 'permission_display_name', width: 200, align: "left", editable: false},

        ],
        height: '100%',
        width:500,
        autowidth: true,
        viewrecords: true,
        rowNum: 10,
        rowList: [10,20,50],
        rownumbers: true, // show row numbers
        rownumWidth: 35, // the width of the row numbers columns
        altRows: true,
        shrinkToFit: true,
        onSelectRow: function (rowid) {
            /*do something when selected*/
        },
        sortorder:'',
        pager: '#grid-pager-detail',
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
        editurl: '<?php echo WS_JQGRID."administration.permission_role_controller/crud"; ?>',
        caption: "Role Permissions"

    });

    $('#grid-table-detail').jqGrid('navGrid', '#grid-pager-detail',
        {   //navbar options
            edit: false,
            editicon: 'fa fa-pencil blue bigger-110',
            add: false,
            addicon: 'fa fa-plus-circle purple bigger-110',
            del: false,
            delicon: 'fa fa-trash-o red bigger-110',
            search: true,
            searchicon: 'fa fa-search orange bigger-110',
            refresh: true,
            afterRefresh: function () {
                // some code here
            },

            refreshicon: 'fa fa-refresh green bigger-110',
            view: false,
            viewicon: 'fa fa-search-plus grey bigger-110'
        },

        {
            editData: {
                role_id: function() {
                    var selRowId =  $("#grid-table").jqGrid ('getGridParam', 'selrow');
                    var role_id = $("#grid-table").jqGrid('getCell', selRowId, 'role_id');
                    return role_id;
                }
            },
            // options for the Edit Dialog
            serializeEditData: serializeJSON,
            closeAfterEdit: true,
            closeOnEscape:true,
            recreateForm: true,
            viewPagerButtons: true,
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
                var response = $.parseJSON(response.responseText);
                if(response.success == false) {
                    return [false,response.message,response.responseText];
                }
                return [true,"",response.responseText];
            }

        },
        {
            //new record form
            serializeEditData: serializeJSON,
            closeAfterAdd: true,
            clearAfterAdd : true,
            closeOnEscape:true,
            recreateForm: true,
            width: 'auto',
            errorTextFormat: function (data) {
                return 'Error: ' + data.responseText
            },
            viewPagerButtons: false,
            beforeShowForm: function (e, form) {
                var form = $(e[0]);
                style_edit_form(form);
            },
            afterShowForm: function(form) {
                form.closest('.ui-jqdialog').center();
            },
            afterSubmit:function(response,postdata) {
                var response = $.parseJSON(response.responseText);
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
                var response = $.parseJSON(response.responseText);
                if(response.success == false) {
                    return [false,response.message,response.responseText];
                }
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

    function responsive_jqgrid(grid_selector, pager_selector) {

        var parent_column = $(grid_selector).closest('[class*="col-"]');
        $(grid_selector).jqGrid( 'setGridWidth', $(".page-content").width() );
        $(pager_selector).jqGrid( 'setGridWidth', parent_column.width() );

    }

</script>