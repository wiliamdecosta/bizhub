<!-- BEGIN SIDEBAR MENU -->
<ul class="page-sidebar-menu  page-header-fixed " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px">
    <!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
    <li class="sidebar-toggler-wrapper hide">
        <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
        <div class="sidebar-toggler">
            <span></span>
        </div>
        <!-- END SIDEBAR TOGGLER BUTTON -->
    </li>
    <li class="sidebar-search-wrapper">
        <!-- BEGIN RESPONSIVE QUICK SEARCH FORM -->
        <!-- DOC: Apply "sidebar-search-bordered" class the below search form to have bordered search box -->
        <!-- DOC: Apply "sidebar-search-bordered sidebar-search-solid" class the below search form to have bordered & solid search box -->
        <div class="sidebar-search">
            <a class="remove" href="javascript:;">
                <i class="icon-close"></i>
            </a>
            <div class="input-group">
                <input type="text" placeholder="Filter Menu..." class="form-control" id="search-menu">
                <span class="input-group-btn">
                    <a class="btn submit" href="javascript:;">
                        <i class="icon-magnifier"></i>
                    </a>
                </span>
            </div>
        </div>
        <!-- END RESPONSIVE QUICK SEARCH FORM -->
    </li>
    <?php
        $ci = & get_instance();
        $ci->load->model('administration/menus');
        $table = $ci->menus;

        echo $ci->menus->htmlMenuSideBar( $ci->input->get('module_id') );
    ?>

</ul>
<!-- END SIDEBAR MENU -->