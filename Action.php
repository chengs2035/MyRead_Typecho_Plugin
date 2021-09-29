<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

require_once "DB/helper.php";

/**
 * 服务
 *
 * @copyright  Copyright (c) 2021
 * @license    GNU General Public License 2.0
 *
 */
class MyReader_Action extends Typecho_Widget implements Widget_Interface_Do
{
    private $db;
    private $options;
    private $pOption;
    private $myreadDB;
    /**
     * 构造一个对象
     */
    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);
        $this->db = Typecho_Db::get();
       // $this->options = self::getOptions();
        $this->pOption = Typecho_Widget::widget('Widget_Options')->Plugin('MyReader'); // 插件选项

         $this->myreadDB = MyreadDBHelper::create();
        // $this->tencentHelp = TencentHelp::create();
         $this->myreadDB->db=$this->db;
        // $this->tencentHelp->db=$this->db;
    }

     /**
     * 固定路由链接
     */
    public function action() {
       
       
        //必须管理员登录
        $this->widget('Widget_User')->pass('administrator');
        
        $this->on($this->request->is('add'))->add();
        $this->on($this->request->is('delbook'))->delbook();
        $this->on($this->request->is('updatebook'))->updatebook();
        $this->on($this->request->is('reloadbook'))->reloadbook();

        $this->response->goBack();
    }
    /**
     * 重载书籍信息
     */
    private function reloadbook(){
        //走api
        $bookISBN =$this->request->bookIsbn;
        
        $bookInfo = $this->myreadDB->getBookInfo($bookISBN)["book_isbn"];

        $bookinfoRet=self::__getBookRawData($bookISBN);
        $bookinfoRet->data->book_stats=$bookInfo->book_stats;
        $bookinfoRet->data->localPhotoUrl= 'https://i0.wp.com/' . str_replace(array('http://', 'https://'),'',$bookinfoRet->data->photoUrl);
       // $bookinfoRet->data->book_id=$bookinfo;


        //反更新到书籍上。
        $this->myreadDB->reloadbook($bookinfoRet->data);

        //刷新缓存
        self::updateBookCacheAndReturn(100,0,'1');

    }
    /**
     * 更新书籍状态
     */
    private function updatebook(){
        
        $bookinfo=$this->request->bookinfo;
        $state=$this->request->state;
        $this->myreadDB->updatebook($bookinfo,$state);
        //刷新缓存
        self::updateBookCacheAndReturn(100,0,'1');
    }
    /**
     * 添加文章
     */
    private function add(){
        //走api
        $bookinfo = json_decode($this->request->bookinfo);

        //判断isbn是否已存在
        if($this->myreadDB->getBookInfo($bookinfo->book_isbn)){
            reloadbook();
        }else{
            $bookinfoRet=self::__getBookRawData($bookinfo->book_isbn);
            $bookinfoRet->data->book_stats=$bookinfo->book_stats;
            $bookinfoRet->data->localPhotoUrl= 'https://i0.wp.com/' . str_replace(array('http://', 'https://'),'',$bookinfoRet->data->photoUrl);
            
            $this->myreadDB->addBook($bookinfoRet->data);//插入数据

        }
        
        //刷新缓存
        self::updateBookCacheAndReturn(100,0,'1');
        
    }
    private function delbook(){
        $bookinfo=$this->request->bookinfo;

        $this->myreadDB->deleteBookInfo($bookinfo);
         //刷新缓存
         self::updateBookCacheAndReturn(100,0,'1');
        
    }
    /**
     * 从接口获取书单数据
     *
     * @access  private
     * @param   string    $UserID     豆瓣ID
     * @return  array     返回 JSON 解码后的 array
     */
    private static function __getBookRawData($isbn)
    {
        $api='https://api.jike.xyz/situ/book/isbn/'.$isbn;
        return json_decode(self::curl_file_get_contents($api));
    }

    /**
     * 从本地读取缓存信息，若不存在则创建，若过期则更新。并返回格式化 JSON
     *
     * @access  public
     * @param   int       $PageSize           分页大小
     * @param   int       $From               开始位置
     * @param   int       $ValidTimeSpan      有效时间，Unix 时间戳
     * @return  json      返回格式化书单
     */
    public function updateBookCacheAndReturn($PageSize, $From, $status)
    {
       
        // 缓存无效或者过期，重新请求，重新写入
        $data_read = array();
        $data_reading = array();
        $data_wish = array();
        //查出所有的书籍
        $allBooks=$this->myreadDB->getAllBook();
        foreach($allBooks as $value){
        
            $item=array("img"=>$value['book_localPhotoUrl'],"title" => $value['book_name'],
            "rating" => $value['book_douban_score'],
            "author" => $value['book_author'],
            "link" => 'https://book.douban.com/subject/'.$value['book_douban_id'],
            "summary" => $value['book_description']);

            if ($value['book_stats'] == '1') {
                array_push($data_read, $item);
            } elseif ($value['book_stats'] == '2') {
                array_push($data_reading, $item);
            } elseif ($value['book_stats'] == '0') {
                array_push($data_wish, $item);
            }
        }


        $cache = array('time' => time(),
            'data' => array('read' => $data_read, 'reading' => $data_reading, 'wish' => $data_wish));

        // 如果 cache 全空，很可能没有获取到数据，时间戳置 1
        if (count($data_read) == 0 && count($data_reading) == 0 && count($data_wish) == 0) {
            $cache['time'] = 1;
        }
        file_put_contents(__DIR__ . '/cache/book.json', json_encode($cache));
    }
    public static function getOutBookList($PageSize, $From, $status){

        if (!file_exists(__DIR__ . '/cache/book.json')) {
            return -1;
        }

        $content = json_decode(file_get_contents(__DIR__ . '/cache/book.json'),true);

        $data = $content['data'][$status];
        $total = count($data);

        if ($From < 0 || $From > $total - 1) {
            echo json_encode(array());
        } else {
            $end = min($From + $PageSize, $total);
            $out = array();
            for ($index = $From; $index < $end; $index++) {
                array_push($out, $data[$index]);
            }
            return json_encode($out);
        }


        //return $content;
    }
    public function outlist(){

        $options = Helper::options()->plugin('MyReader');
        //$UserID = $options->ID;
        $PageSize = $options->PageSize ? $options->PageSize : 10;
        //$ValidTimeSpan = $options->ValidTimeSpan ? $options->ValidTimeSpan : 60 * 60 * 24;
        $From = 0;
        if (array_key_exists('from', $_GET)) {
            $From = $_GET['from'];
        }
        if ($_GET['type'] == 'book') {
            header("Content-type: application/json");
            $status = array_key_exists('status', $_GET) ? $_GET['status'] : 'read';
            
            echo MyReader_Action::getOutBookList($PageSize, $From, $status);

        } elseif ($_GET['type'] == 'singlebook') {
            header("Content-type: application/json");
            echo MyReader_Action::updateSingleCacheAndReturn($_GET['id'], 'book');
        }  else {
            echo json_encode(array());
        }

    }
    


    private static function curl_file_get_contents($_url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36');

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
