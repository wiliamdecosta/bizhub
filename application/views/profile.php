<!-- breadcrumb -->
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <span>My Profile</span>
        </li>
    </ul>
</div>
<!-- end breadcrumb -->
<div class="space-4"></div>
<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">Form Profile</div>
            </div>

            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <form method="post" action="" class="form-horizontal" id="form-profile">
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="id" value="<?php echo $this->session->userdata('user_id'); ?>">
                    <div class="form-body">

                        <div class="form-group">
                            <label class="col-md-3 control-label" for="username">Username</label>
                            <div class="col-md-4">
                                <input type="text" name="username" readonly="" class="form-control" value="<?php  echo $this->session->userdata('user_name'); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label" for="email">Email</label>
                            <div class="col-md-4">
                                <input type="email" name="email" class="form-control required" value="<?php  echo $this->session->userdata('user_email'); ?>">
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-md-3 control-label" for="password">New Password</label>
                            <div class="col-md-4">
                                <input type="password" class="form-control" name="password" value="">
                                <i class="orange">Min.4 Characters</i>
                            </div>

                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label" for="password_confirmation">Confirm Password</label>
                            <div class="col-md-4">
                                <input type="password" class="form-control" name="password_confirmation" value="">
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                <input type="submit" name="submit" value="Save Changes" class="btn btn-danger">
                            </div>
                        </div>
                    </div>
                </form>
                <!-- END FORM-->
            </div>
        </div>
    </div>
</div>

<script>

$("#form-profile").on('submit', (function (e) {

    e.preventDefault();

    var data = $(this).serializeArray();
    $.ajax({
        url: "<?php echo WS_JQGRID."administration.users_controller/updateProfile"; ?>",
        type: "POST",
        data: data,
        dataType: "json",
        success: function (data) {
            if (data.success == true) {
                swal("Sukses",data.message,"success");
                loadContentWithParams('profile',{});
            } else {
                swal("Perhatian",data.message,"warning");
            }
        },
        error: function (xhr, status, error) {
            swal({title: "Error!", text: xhr.responseText, html: true, type: "error"});
        }
    });

    return false;
}));

</script>