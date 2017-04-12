<!--[if lt IE 9]>
<script src="<?php echo base_url(); ?>assets/global/plugins/respond.min.js"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/excanvas.min.js"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="<?php echo base_url(); ?>assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/jquery.blockUI.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="<?php echo base_url(); ?>assets/global/scripts/app.js" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="<?php echo base_url(); ?>assets/layouts/layout/scripts/layout.min.js" type="text/javascript"></script>
<!-- END THEME LAYOUT SCRIPTS -->

<script src="<?php echo base_url(); ?>assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<!-- begin jqgrid -->
<script src="<?php echo base_url(); ?>assets/jqgrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/jqgrid/src/jquery.jqGrid.js" type="text/javascript"></script>

<!-- begin swal -->
<script src="<?php echo base_url(); ?>assets/swal/sweetalert.min.js"></script>
<!-- end swal -->

<script src="<?php echo base_url(); ?>assets/bootgrid/jquery.bootgrid.min.js"></script>

<script src="<?php echo base_url(); ?>assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/global/plugins/jquery-validation/js/additional-methods.min.js" type="text/javascript"></script>

<script src="<?php echo base_url(); ?>assets/js/jqgrid.function.js"></script>

<script src="<?php echo base_url(); ?>jqwidgets/jqxcore.js"></script>
<script src="<?php echo base_url(); ?>jqwidgets/jqxbuttons.js"></script>
<script src="<?php echo base_url(); ?>jqwidgets/jqxscrollbar.js"></script>
<script src="<?php echo base_url(); ?>jqwidgets/jqxpanel.js"></script>
<script src="<?php echo base_url(); ?>jqwidgets/jqxtree.js"></script>
<script src="<?php echo base_url(); ?>jqwidgets/jqxcheckbox.js"></script>
<script src="<?php echo base_url(); ?>jqwidgets/jqxdata.js"></script>

<script>
    function PopupCenter(url, title, w, h) {
        // Fixes dual-screen position                         Most browsers      Firefox
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;
        var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        // Puts focus on the newWindow
        if (window.focus) {
            newWindow.focus();
        }
    }
</script>

<script type="text/javascript">
    $(document).ready(function () {
        // Ajax setup csrf token.
        var csfrData = {};
        csfrData['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
        $.ajaxSetup({
            data: csfrData,
            cache: false
        });
   });

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

    function loadContentWithParams(id, params) {
        $.ajax({
            url: "<?php echo base_url().'home/load_content/'; ?>" + id,
            type: "POST",
            data: params,
            success: function (data) {
                $( "#main-content" ).html( data );
            },
            error: function (xhr, status, error) {
                swal({title: "Error!", text: xhr.responseText, html: true, type: "error"});
            }
        });
        return;
    }

    $(".nav-item").on('click', function(){
        var nav = $(this).attr('data-source');

        if(!nav){

        }else{
            var menu_id = $(this).attr('menu-id');
            $(".nav-item").removeClass("active");

            $(this).addClass("active");
            $(this).parent("ul").parent("li").addClass("active");

            loadContentWithParams(nav,{menu_id:menu_id});
        }

    });


    $("#my-profile").click(function(event){
        event.stopPropagation();
        $(".nav-item").removeClass("active");
        loadContentWithParams('profile',{});
    });

    $.jgrid.defaults.responsive = true;
    $.jgrid.defaults.styleUI = 'Bootstrap';
    jQuery.fn.center = function () {

        if(this.width() > $(window).width()) {
            this.css("width", $(window).width()-40);
        }
        this.css("top",($(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
        this.css("left",( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");

        return this;
    }


    $('#search-menu').keyup(function() {
        var filter = $(this).val();
        if( filter.length == 0 ) {
            $("li.nav-item").show();
            $("ul.sub-menu").hide();
            $('.active').parent('ul.sub-menu').show();
            return;
        }

        if( filter.length < 2 ) return;
        var regex = new RegExp(filter,"i");
        $("li.nav-item").each(function() {
            if($(this).text().search(regex) < 0) {
                $(this).hide();
            }
            else {
                $(this).parent().show();
                $(this).show();
            }
        });
    });



</script>
