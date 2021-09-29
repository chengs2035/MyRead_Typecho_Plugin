<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
define('DoubanBoard_Plugin_VERSION', '1.0');
/**
 * MyReader我的阅读展示
 *
 * @package MyReader
 * @author  小码农
 * @version 1.0.0
 * @link https://www.djc8.cn
 */
class MyReader_Plugin implements Typecho_Plugin_Interface
{
    
    const MyRead_Action="MyReader_Action";
    
    //路由列表
    const routes = array
    (
        //路由名，路由地址，class名,class中的方法
        array("/myread/book/list","/myread/book/list",self::MyRead_Action,"list"),
        array("/myread/out/book/list","/myread/out/book/list",self::MyRead_Action,"outlist")
    );

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
         // 检查是否存在对应扩展
         if (!extension_loaded('openssl')) {
            throw new Typecho_Plugin_Exception('启用失败，PHP 需启用 OpenSSL 扩展。');
        }
        if (!extension_loaded('curl')) {
            throw new Typecho_Plugin_Exception('启用失败，PHP 需启用 CURL 扩展。');
        }
        Helper::addAction('myreadaction',self::MyRead_Action);

        //Helper::addRoute("route_DoubanBoard","/DoubanBoard","DoubanBoard_Action",'action');

        foreach (self::routes as $route){
            Helper::addRoute($route[0],$route[1],$route[2],$route[3]);
        }
        Helper::addPanel(3, 'MyReader/manage.php', '我的阅读', '我的阅读管理', 'administrator');
        Helper::addPanel(3, 'MyReader/readSetting.php', '我的阅读', '高级配置', 'administrator',true);
        
        Typecho_Plugin::factory('Widget_Archive')->footer = array('MyReader_Plugin', 'footer');
        self::addTable();
    }
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){

        foreach (self::routes as $route){
            Helper::removeRoute($route[0]);
        }
        Helper::removePanel(3, 'MyReader/manage.php');
        Helper::removePanel(3,'MyReader/readSetting.php');
        Helper::removeAction('myreadaction');
    }

     /**
     * @throws Typecho_Db_Exception
     */
    private static function addTable()
    {
        $db = Typecho_Db::get();

        $sql = self::getSql($db, 'install');

        $db->query($sql);
    }
    /**
     * @param $db
     * @param string $path
     * @return string|string[]
     */
    private static function getSql($db, $path = 'install')
    {
        $adapter = $db->getAdapterName();
        $prefix = $db->getPrefix();
        $dbConfig = Typecho_Db::get()->getConfig()[0];
        $charset = $dbConfig->charset;
        if ($adapter === 'Pdo_Mysql' || $adapter === 'Mysql' || $adapter === 'Mysqli') {
            $sqlTemplate = file_get_contents(__DIR__ . '/sql/' . $path . '/Mysql.sql');
        }
        if (empty($sqlTemplate)) throw new \Exception('暂不支持你的数据库');

        $sql = str_replace('{prefix}', $prefix, $sqlTemplate);
        $sql = str_replace('{ charset }', $charset, $sql);
        return $sql;
    }



    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        //$serviceTitle = new Typecho_Widget_Helper_Layout('div', array('class=' => 'typecho-page-title'));
        //$serviceTitle->html('<h2>我的阅读</h2><h3>详细配置请在管理->我的阅读中进行配置</h3>');
        //$form->addItem($serviceTitle);
        echo '<b>使用方式</b><br>
        已读书单列表：&lt;div data-status=&quot;read&quot; class=&quot;douban-book-list doubanboard-list&quot;&gt;&lt;/div&gt;<br>
        在读书单列表：&lt;div data-status=&quot;reading&quot; class=&quot;douban-book-list doubanboard-list&quot;&gt;&lt;/div&gt;<br>
        想读书单列表：&lt;div data-status=&quot;wish&quot; class=&quot;douban-book-list doubanboard-list&quot;&gt;&lt;/div&gt;<br>
        ';
        $PageSize = new Typecho_Widget_Helper_Form_Element_Text('PageSize', NULL, '12', _t('每次加载的数量'), _t('填写每次加载的数量，不填默认为 10。注意：豆瓣限制最多取得 100 条数据。'));
        $form->addInput($PageSize);
        $ValidTimeSpan = new Typecho_Widget_Helper_Form_Element_Text('ValidTimeSpan', NULL, '86400', _t('缓存过期时间'), _t('填写缓存过期时间，单位秒。默认 24 小时。'));
        $form->addInput($ValidTimeSpan);
        $loadJQ= new Typecho_Widget_Helper_Form_Element_Checkbox('loadJQ',  array('jq'=>_t('配置是否引入 JQuery：勾选则引入不勾选则不引入<br>')),array('jq'), _t('基本设置'));
        $form->addInput($loadJQ);

        $isDrop = new Typecho_Widget_Helper_Form_Element_Radio('isDrop', array('0' => '删除', '1' => '不删除'), '1', '彻底卸载(<b style="color:red">请慎重选择</b>)', '请选择是否在禁用插件时，删除数据表');
        $form->addInput($isDrop);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}


    /**
     * 在底部输出所需 JS
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function footer()
    {
        
        echo '<link rel="stylesheet" href="';
        Helper::options()->pluginUrl('MyReader/assets/DoubanBoard.09.css');
        echo '?v='.DoubanBoard_Plugin_VERSION.'" />';
        echo '<script>var DoubanPageSize='.Helper::options()->plugin('MyReader')->PageSize.'</script>';
        
        if (!empty(Helper::options()->plugin('MyReader')->loadJQ) && in_array('jq', Helper::options()->plugin('MyReader')->loadJQ))
        {
            echo '<script src="';
            Helper::options()->pluginUrl('MyReader/assets/jquery.min.js');
            echo '"></script>';
        }
        echo '<script>var DoubanAPI = "';
        Helper::options()->index('/myread/out/book/list');
        echo '"</script>';

        echo '<script type="text/javascript" src="';
        Helper::options()->pluginUrl('MyReader/assets/DoubanBoard.07.js');
        echo '?v='.DoubanBoard_Plugin_VERSION.'"></script>';
    }
}
