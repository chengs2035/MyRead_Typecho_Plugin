<?php
//$options->pagemenuname='Statistics.php';
$currentpagemenuname=$options->pagemenuname;
?>
<ul class="typecho-option-tabs fix-tabs clearfix">
<li <?php if($currentpagemenuname=='manage.php') _e('class="current"')?> >
<a href="<?php $options->adminUrl('extending.php?panel=MyReader%2Fmanage.php'); ?>"><?php _e('我的阅读管理'); ?></a></li>

<li <?php if($currentpagemenuname=='readSetting.php') _e('class="current"')?> >
<a href="<?php $options->adminUrl('extending.php?panel=MyReader%2FreadSetting.php'); ?>"><?php _e('高级配置'); ?></a></li>

</ul>