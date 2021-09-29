<?php
include 'header.php';
include 'menu.php';
$stat = Typecho_Widget::widget('Widget_Stat');

$options->pagemenuname='manage.php';

$query =$options->adminUrl.'extending.php?panel=MyReader%2Fmanage.php&page={page}';

$queryReq=isset($_REQUEST['bookStats'])?$_REQUEST['bookStats']:'';

if($queryReq!=''){
    $query=$query.'&bookStats='.$_REQUEST['bookStats'];
}


//配置依次填入表单中。只查一行
$querycount=$db->select('count(1) as count')->from('table.bookinfo');
if($queryReq!=''){
    $querycount->where('table.bookinfo.book_stats=?',$queryReq);
}

$links = $db->fetchAll($querycount);
 
$res=array();
$res["count"]=$links[0]['count']; 

//计算分页
$pageSize = 20;
$currentPage = isset($_REQUEST['page']) ? ($_REQUEST['page'] + 0) : 1;


$queryT=$db->select()->from('table.bookinfo')
->page($currentPage, $pageSize)
//->order('table.TwitterTimeline.is_delete', Typecho_Db::SORT_ASC)
->order('table.bookinfo.insrt_dt', Typecho_Db::SORT_DESC);

if($queryReq!=''){
    $queryT->where('table.bookinfo.book_stats=?',$queryReq);
}

$current = $db->fetchAll($queryT);



$nav = new Typecho_Widget_Helper_PageNavigator_Box($res["count"],$currentPage, $pageSize, $query);

?>
<style>
.container .addmargintop{margin-top:10px;}
.row .self-label{text-align:right;float:left;margin-right:0; padding: 10px;}
.row .self-text{width:300px;}

table, th, td {font: 12px Arial,Helvetica,sans-serif,'宋体';margin: 0;padding: 0}

.even {height: 80px;}
.triangle-topleft {
    width: 0;
    height: 0;
    font-size: xx-small;
    border-top: 45px solid #000;
    border-right: 45px solid transparent;
    position: absolute;
    top: 0;
    left: 0;
}
.word {
    text-align: center;
    margin: auto;
    position: absolute;
    display: inline-block;
    width: 45px;
    left: 0;
    top: -40px;
    color: #FFF;
    transform-origin: bottom center;
    transform: rotate(-45deg);
    font-size: 12px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}

.unread{
    border-top: 45px solid #9d9d9d;
}
.reading{
    border-top: 45px solid #FF3131;
}
.read{
    border-top: 45px solid #00b570;
}


.modal-window {
  position: fixed;
  background-color:rgb(0 0 0 / 25%);
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 999;
  visibility: hidden;
  opacity: 0;
  pointer-events: none;
  transition: all 0.3s;
}
.modal-window:target {
  visibility: visible;
  opacity: 1;
  pointer-events: auto;
}
.modal-window > div {
  width: 500px;
  position: absolute;
  top: 50%;
  left: 50%;
  -webkit-transform: translate(-50%, -50%);
          transform: translate(-50%, -50%);
  padding: 2em;
  background: #ffffff;

}
.modal-window header {
  font-weight: bold;
}
.modal-window h1 {
  font-size: 150%;
  margin: 0 0 15px;
}

.modal-close {
  color: #aaa;
  line-height: 50px;
  font-size: 80%;
  position: absolute;
  right: 0;
  text-align: center;
  top: 0;
  width: 70px;
  text-decoration: none;
}
.modal-close:hover {
  color: black;
}
</style>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <?php include 'pagemenu.php'; ?>
        <div class="container typecho-page-main">
            <div class="row addmargintop">
                <div class="col-mb-8">
                   [<a href="#open-modal">添加</a>]|
                   [<a href="<?php _e($options->adminUrl.'extending.php?panel=MyReader%2Fmanage.php&bookStats=0'); ?>">未读</a>]|
                   [<a href="<?php _e($options->adminUrl.'extending.php?panel=MyReader%2Fmanage.php&bookStats=1'); ?>">已读</a>]|
                   [<a href="<?php _e($options->adminUrl.'extending.php?panel=MyReader%2Fmanage.php&bookStats=2'); ?>">想读</a>]|
                   [<a href="<?php _e($options->adminUrl.'extending.php?panel=MyReader%2Fmanage.php'); ?>">所有</a>]
                </div>
                <div class="col-mb-2">
                    <label class="typecho-label self-label">所有书籍：<?php echo $res["count"];?></label>
                </div>
            </div>
            
        </div>
        <div class="container typecho-page-main">
            <form method="post" name="manage_posts" class="operate-form">
                <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup>
                                <col width="4%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                            </colgroup>
                            <thead>
                            <tr>
                                <th><?php _e('状态'); ?></th>
                                <th><?php _e('书名'); ?></th>
                                <th><?php _e('作者'); ?></th>
                                <th><?php _e('isbn'); ?></th>
                                <th><?php _e('出版社'); ?></th>
                                <th><?php _e('出版时间'); ?></th>
                                <th><?php _e('价格');?></th>
                                <th><?php _e('操作'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($current as $line): ?>
                            
                                <tr class="even " id="<?php _e($line['book_id']); ?>">
                                    <td style="position: relative;padding-left: 40px;">
                                        <?php 
                                                $statusClass='';
                                                if($line['book_stats']=='0')$statusClass='unread';
                                                else if($line['book_stats']=='1') $statusClass='read';
                                                else if($line['book_stats']=='2')$statusClass='reading';
                                                else $statusClass='';
                                        ?>
                                        <div class="triangle-topleft <?php _e($statusClass);?>">
                                        <div class="word ">
                                            <?php 
                                                if($line['book_stats']=='0')_e('未读');
                                                else if($line['book_stats']=='1') _e('已读');
                                                else if($line['book_stats']=='2')_e('在读');
                                                else _e('异常');
                                            ?>
                                        </div>
                                        </div>
                                       
                                    </td>
                                    <td>
                                        <?php _e($line['book_name']); ?>
                                    </td>
                                   
                                    <td>
                                        <?php _e($line['book_author']); ?>
                                    </td>
                                    <td>
                                        <?php _e($line['book_isbn']); ?>
                                    </td>
                                    <td>
                                        <?php _e($line['book_publishing']); ?>
                                    </td>
                                    <td>
                                        <?php _e($line['book_published']); ?>
                                    </td>
                                    <td>
                                        <?php _e($line['book_price']); ?>
                                    </td>
                                    <td>
                                        <a title="改为未读状态" href="<?php $options->index('/action/myreadaction?updatebook');_e('&state=0&bookinfo='.$line['book_id']); ?>" class="operate-ret" >未</a>
                                        <a title="改为已读状态" href="<?php $options->index('/action/myreadaction?updatebook');_e('&state=1&bookinfo='.$line['book_id']); ?>" class="operate-ret" >已</a>
                                        <a title="改为在读状态" href="<?php $options->index('/action/myreadaction?updatebook');_e('&state=2&bookinfo='.$line['book_id']); ?>" class="operate-ret" >在</a>
                                        <a title="从接口更新本书信息" href="<?php $options->index('/action/myreadaction?reloadbook');_e('&bookIsbn='.$line['book_isbn']); ?>" class="operate-ret" >更</a>
                                        <a title="删除这本书" href="<?php $options->index('/action/myreadaction?delbook');_e('&bookinfo='.$line['book_id']); ?>" class="operate-ret">删</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>
                    <div class="typecho-pager">
                    <div class="typecho-pager-content">
                        <ul>
                             <?php $nav->render('&laquo;', '&raquo;');?>
                        </ul>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
</div>
<div id="open-modal" class="modal-window">
  <div>
    <a href="#" title="Close" class="modal-close">Close</a>
    <h1>新增</h1>
    <div class="row typecho-page-main" role="form">
        <div class="col-mb-12 col-tb-8 col-tb-offset-2">
            <form>
            <ul class="typecho-option" id="typecho-option-item-isbn-1">
                <li>
                <label class="typecho-label" for="isbn">
                isbn</label>
                <input id="isbn" name="isbn" type="text" class="w-100 mono" value="" />
                <p class="description">
                书籍的ISBN编码.</p>
                </li>
            </ul>
            <ul class="typecho-option" id="typecho-option-item-book_stats-6">
                <li>
                <label class="typecho-label" for="book_stats">
                状态</label>
                <select name="book_stats" id="book_stats">
                <option value="0">
                未读</option>
                <option value="1">
                已读</option>
                <option value="2">
                在读
                </select>
                </li>
            </ul>
            <a href="#" onclick="addNewBook()" title="Add" class="modal-add">新增</a>
            </form>
        </div>
    </div>
    </div>
</div>


<?php
include 'copyright.php';
include 'common-js.php';

?>
<script type="text/javascript">
    function addNewBook(){
        if($("#isbn").val()==''){
            alert('请输入ISBN');
            return;
        }
        $bookinfo={};

        $book={};
        //发送给后台
        $book.book_isbn=$("#isbn").val();
        $book.book_stats=$("#book_stats").val();
        $bookinfo.bookinfo=JSON.stringify($book);
        $.ajax({ url: "<?php $options->index('/action/myreadaction?add');?>", context: document.body,data:$bookinfo,type:"post", success: function(){
            
            location.reload();
            
        }});
    }

</script>
<?php
include 'footer.php';
?>
