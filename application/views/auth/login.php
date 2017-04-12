<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8"/>
    <title>TOS</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>

    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet"
          type="text/css"/>
    <link href="<?php echo base_url(); ?>assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="<?php echo base_url(); ?>assets/global/plugins/simple-line-icons/simple-line-icons.min.css"
          rel="stylesheet" type="text/css"/>
    <link href="<?php echo base_url(); ?>assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="<?php echo base_url(); ?>assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css"
          rel="stylesheet" type="text/css"/>

    <link href="<?php echo base_url(); ?>assets/global/plugins/select2/css/select2.min.css" rel="stylesheet"
          type="text/css"/>
    <link href="<?php echo base_url(); ?>assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet"
          type="text/css"/>

    <link href="<?php echo base_url(); ?>assets/global/css/components.min.css" rel="stylesheet" id="style_components"
          type="text/css"/>
    <link href="<?php echo base_url(); ?>assets/global/css/plugins.min.css" rel="stylesheet" type="text/css"/>

    <link href="<?php echo base_url(); ?>assets/pages/css/login-5.css" rel="stylesheet" type="text/css"/>

    <link rel="shortcut icon" href="<?php echo base_url(); ?>favicon.png"/>
</head>


<body class=" login">
<!-- BEGIN : LOGIN PAGE 5-2 -->
<div class="user-login-5">
    <div class="row bs-reset">
        <div class="col-md-6 login-container bs-reset">
            <img class="login-logo login-6" src="<?php echo base_url(); ?>assets/image/telkom.png" width="300px" height=""/>

            <div class="login-content">
                <h4> TOS - Telkom Indonesia </h4>

                <form class="login-form" name="login_form" method="post" action="<?php echo $login_url;?>">
                    <?php if($this->session->flashdata('error_message') != ""): ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button>
                            <strong><?php echo $this->session->flashdata('error_message'); ?></strong>
                        </div>
                    <?php endif; ?>
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    <div class="row">
                        <div class="col-xs-6">
                            <input class="form-control form-control-solid placeholder-no-fix form-group" type="text"
                                   autocomplete="off" placeholder="Username" name="username" required="" />
                        </div>
                        <div class="col-xs-6">
                            <input class="form-control form-control-solid placeholder-no-fix form-group" type="password"
                                   autocomplete="off" placeholder="Password" name="password" required="" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <button class="btn blue btn-block" type="submit">Login</button>
                        </div>
                    </div>
                </form>
                <!-- END FORGOT PASSWORD FORM -->
            </div>

            <div class="login-footer">
                <div class="row bs-reset">
                    <div class="col-xs-5 bs-reset">
                    </div>
                    <div class="col-xs-7 bs-reset">
                        <div class="login-copyright text-right">
                            <p>Copyright &copy; Telkom Indonesia. 2017</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 bs-reset">
            <div class="login-bg"></div>
        </div>
    </div>
</div>
<!-- END : LOGIN PAGE 5-2 -->
<!--[if lt IE 9]>
<script src="<?php echo base_url();?>assets/global/plugins/respond.min.js"></script>
<script src="<?php echo base_url();?>assets/global/plugins/excanvas.min.js"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="<?php echo base_url(); ?>assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/bootstrap/js/bootstrap.min.js"
        type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
<script
    src="<?php echo base_url(); ?>assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js"
    type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js"
        type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"
        type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="<?php echo base_url(); ?>assets/global/plugins/jquery-validation/js/jquery.validate.min.js"
        type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/jquery-validation/js/additional-methods.min.js"
        type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/select2/js/select2.full.min.js"
        type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/backstretch/jquery.backstretch.min.js"
        type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="<?php echo base_url(); ?>assets/global/scripts/app.min.js" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="<?php echo base_url(); ?>assets/pages/scripts/login-5.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/jquery.blockUI.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<!-- END THEME LAYOUT SCRIPTS -->
</body>


<script type="text/javascript">
    $(document).ajaxStart(function () {
        $(document).ajaxStart($.blockUI({
            message:  'Loading...',
            css: {
                border: 'none',
                padding: '5px',
                backgroundColor: '#000',
                '-webkit-border-radius': '10px',
                '-moz-border-radius': '10px',
                opacity: .6,
                color: '#fff'
            }

        })).ajaxStop($.unblockUI);
    });

    $('.login-bg').backstretch([
            "<?php echo base_url();?>assets/pages/img/login/gmp.jpg",
            "<?php echo base_url();?>assets/pages/img/login/bg2.jpg",
            "<?php echo base_url();?>assets/pages/img/login/bg3.jpg"
        ], {
            fade: 1000,
            duration: 8000
        }
    );

    $(document).ready(function () {
        // Ajax setup csrf token.
        var csfrData = {};
        csfrData['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo
        $this->security->get_csrf_hash(); ?>';
        $.ajaxSetup({
            data: csfrData,
            cache: false
        });
    })
</script>
</html>