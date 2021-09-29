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
class OUT_Action extends Typecho_Widget implements Widget_Interface_Do
{
   /**
     * 解析 URL，返回对应数据
     *
     * @access  public
     */
    public function action()
    {
        $options = Helper::options()->plugin('MyReader');
        $UserID = $options->ID;
        $PageSize = $options->PageSize ? $options->PageSize : 10;
        $ValidTimeSpan = $options->ValidTimeSpan ? $options->ValidTimeSpan : 60 * 60 * 24;
        $From = 0;
        if (array_key_exists('from', $_GET)) {
            $From = $_GET['from'];
        }
        if ($_GET['type'] == 'book') {
            header("Content-type: application/json");
            $status = array_key_exists('status', $_GET) ? $_GET['status'] : 'read';
            echo DoubanAPI::updateBookCacheAndReturn($UserID, $PageSize, $From, $ValidTimeSpan, $status);
        } elseif ($_GET['type'] == 'singlebook') {
            header("Content-type: application/json");
            echo DoubanAPI::updateSingleCacheAndReturn($_GET['id'], 'book', $ValidTimeSpan);
        }  else {
            echo json_encode(array());
        }
    }
}
