<?php
include 'header.php';
include 'menu.php';
//先查询配置信息

$options->pagemenuname='readSetting.php';


?>
<style>
.description {font-size:12px;}
.container .addmargintop{margin-top:10px;}
.row .self-label{text-align:right;float:left;width:120px;margin-right:0; padding: 10px;}
.row .self-text{width:300px;}
.row .description{color:red}
</style>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <?php include 'pagemenu.php'; ?>

        <div class="container typecho-page-main">
           <div>开发中</div>
        </div>
    </div>
</div>
<?php
include 'copyright.php';
include 'common-js.php';
include 'footer.php';
?>
<script type="text/javascript">

</script>
