<?php $this->load->view('home/header.php'); ?>
<style>
    img.img-app {
        width:100%;
    }
</style>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <?php
                $ci = & get_instance();
                $ci->load->model('administration/modules');
                $tModules = $ci->modules;

                $modules = $tModules->getHomeModules($this->session->userdata('user_id'));
        ?>

        <div class="rows">
        <?php foreach($modules as $module): ?>
            <div class="col-xs-6 col-md-3">
                <div class="portlet box blue-hoki">
                    <div class="portlet-title">
                        <div class="caption">
                            <?php echo $module['module_name']; ?>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <a href="<?php echo base_url().'panel?module_id='.$module['module_id'];?>">
                            <img class="img-app" src="<?php echo $module['module_icon']; ?>">
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

    </div>
</div>
<?php $this->load->view('home/footer.php'); ?>