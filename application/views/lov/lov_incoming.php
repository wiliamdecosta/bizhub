<div id="modal_lov_incoming" class="modal fade" tabindex="-1" style="overflow-y: scroll;">
    <div class="modal-dialog" style="width:900px;">
        <div class="modal-content">
            <!-- modal title -->
            <div class="modal-header no-padding">
                <div class="table-header">
                    <span class="form-add-edit-title">Stock Material</span>
                </div>
            </div>
            <input type="hidden" id="modal_lov_smd_id_val" value="" />
            <input type="hidden" id="modal_lov_smd_trx_code_val" value="" />
            <input type="hidden" id="modal_lov_farmer_val" value="" />
            <input type="hidden" id="modal_lov_smd_qty_val" value="" />

            <!-- modal body -->
            <div class="modal-body">
                <div>
                  <button type="button" class="btn btn-sm btn-success" id="modal_lov_incoming_btn_blank">
                    <span class="fa fa-pencil-square-o" aria-hidden="true"></span> BLANK
                  </button>
                </div>
                <table id="modal_lov_incoming_detail_grid_selection" class="table table-striped table-bordered table-hover">
                <thead>
                  <tr>
                     <th data-column-id="in_biz_det_id" data-sortable="false" data-visible="false" data-width="300">ID SMD</th>
                     <th data-header-align="center" data-align="center" data-formatter="opt-edit" data-sortable="false" data-width="100">Options</th>
                     <th data-column-id="packing_batch_number" data-width="300">Packing Label</th>
                     <th data-column-id="product_code" data-width="150">Product Name</th>
                     <th data-column-id="qty_netto" data-width="100">Qty(Kg)</th>
                  </tr>
                </thead>
                </table>
            </div>

            <!-- modal footer -->
            <div class="modal-footer no-margin-top">
                <div class="bootstrap-dialog-footer">
                    <div class="bootstrap-dialog-footer-buttons">
                        <button class="btn btn-danger btn-xs radius-4" data-dismiss="modal">
                            <i class="fa fa-times"></i>
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.end modal -->

<script>

    jQuery(function($) {
        $("#modal_lov_incoming_btn_blank").on('click', function() {
            $("#"+ $("#modal_lov_smd_id_val").val()).val("");
            $("#"+ $("#modal_lov_smd_trx_code_val").val()).val("");
            $("#modal_lov_incoming").modal("toggle");
        });
    });

    function modal_lov_incoming_show(id, trx_code, qty,production_product_id,parent_id,farmer) {

        modal_lov_incoming_set_field_value(id, trx_code,qty,farmer);
        $("#modal_lov_incoming").modal({backdrop: 'static'});
        modal_lov_incoming_prepare_table(parent_id,production_product_id);
    }


    function modal_lov_incoming_set_field_value(the_id_field, the_code_field,qty,farmer) {
         $("#modal_lov_smd_id_val").val(the_id_field);
         $("#modal_lov_smd_trx_code_val").val(the_code_field);
         $("#modal_lov_farmer_val").val(farmer);
         $("#modal_lov_smd_qty_val").val(qty);

    }

    function modal_lov_incoming_set_value(the_id_val, the_code_val,the_qty,fm_name) {
         $("#"+ $("#modal_lov_smd_id_val").val()).val(the_id_val);
         $("#"+ $("#modal_lov_smd_trx_code_val").val()).val(the_code_val);
         $("#"+ $("#modal_lov_farmer_val").val()).val(fm_name);
         $("#"+ $("#modal_lov_smd_qty_val").val()).val(the_qty);
         //Custom
         $("#in_source_qty").val(the_qty);
         $("#modal_lov_incoming").modal("toggle");

         $("#"+ $("#modal_lov_smd_id_val").val()).change();
         $("#"+ $("#modal_lov_smd_trx_code_val").val()).change();
    }

    function modal_lov_incoming_prepare_table(parent_id,product_id) {
        $("#modal_lov_incoming_detail_grid_selection").bootgrid("destroy");
        $("#modal_lov_incoming_detail_grid_selection").bootgrid({
             formatters: {
                "opt-edit" : function(col, row) {
                    return '<a href="javascript:;" title="Set Value" onclick="modal_lov_incoming_set_value(\''+ row.in_biz_det_id +'\', \''+ row.packing_batch_number +'\',\''+ row.qty_netto +'\',\''+ row.product_code +'\')" class="blue"><i class="fa fa-pencil-square-o bigger-130"></i></a>';
                }
             },
             rowCount:[5,10],
             ajax: true,
             requestHandler:function(request) {
                if(request.sort) {
                    var sortby = Object.keys(request.sort)[0];
                    request.dir = request.sort[sortby];

                    delete request.sort;
                    request.sort = sortby;
                }
                return request;
             },
             responseHandler:function (response) {
                if(response.success == false) {
                    swal({title: 'Attention', text: response.message, html: true, type: "warning"});
                }
                return response;
             },
            post: {
                parent_id: parent_id,
                product_id : product_id
            },
             url: '<?php echo WS_BOOTGRID."agripro.production_bizhub_controller/readLovINC"; ?>',
             selection: true,
             sorting:true
        });

        $('.bootgrid-header span.glyphicon-search').removeClass('glyphicon-search')
        .html('<i class="fa fa-search"></i>');
    }

</script>