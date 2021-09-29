<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
class MyreadDBHelper{

	public $connection;
	public $db;

    public $options;

    public static function create(){
		static $instance ;
		if (!$instance){ 
		    $instance = new MyreadDBHelper();
		}
		return $instance;
	}
    /**
     * 更新书籍所有信息
     */
    public function reloadbook($bookinfo){
        $links = array(
            'book_name' => $bookinfo -> name,
            'book_subname' => $bookinfo -> subname,
            'book_author' => $bookinfo -> author,
            'book_translator' => $bookinfo -> translator,
            'book_publishing' => $bookinfo -> publishing,
            'book_published' => $bookinfo -> published,
            'book_designed' => $bookinfo -> designed,
            'book_douban_id' => $bookinfo -> douban,
            'book_douban_score' => $bookinfo -> doubanScore,
            'book_brand' => $bookinfo -> brand,
            'book_weight' => $bookinfo -> weight,
            'book_size' => $bookinfo -> size,
            'book_pages' => $bookinfo -> pages,
            'book_photoUrl' => $bookinfo -> photoUrl,
            'book_localPhotoUrl' => $bookinfo -> localPhotoUrl,
            'book_price' => $bookinfo -> price,
            'book_createTime' => $bookinfo -> createTime,
            'book_uptime' => $bookinfo -> uptime,
            'book_authorIntro' => $bookinfo -> authorIntro,
            'book_description' => $bookinfo -> description
		);
        $this->db->query($this->db->update('table.bookinfo')->rows($links)->where('book_isbn = ?',$bookinfo->book_isbn));
    }
    
    public function getAllBook(){
		$value = $this->db->fetchAll($this->db->select()
            ->from('table.bookinfo')->order('table.bookinfo.book_id',Typecho_Db::SORT_DESC));
		return $value;
    }
    public function getBookInfo($book_isbn){
		$value = $this->db->fetchRow($this->db->select()
            ->from('table.bookinfo')->where('table.bookinfo.book_isbn=?',$book_isbn));

		return $value;
    }

    public function addBook($bookinfo){
        
        $links = array(
            'book_name' => $bookinfo -> name,
            'book_subname' => $bookinfo -> subname,
            'book_author' => $bookinfo -> author,
            'book_translator' => $bookinfo -> translator,
            'book_publishing' => $bookinfo -> publishing,
            'book_published' => $bookinfo -> published,
            'book_designed' => $bookinfo -> designed,
            'book_isbn' => $bookinfo -> code,
            'book_douban_id' => $bookinfo -> douban,
            'book_douban_score' => $bookinfo -> doubanScore,
            'book_brand' => $bookinfo -> brand,
            'book_weight' => $bookinfo -> weight,
            'book_size' => $bookinfo -> size,
            'book_pages' => $bookinfo -> pages,
            'book_photoUrl' => $bookinfo -> photoUrl,
            'book_localPhotoUrl' => $bookinfo -> localPhotoUrl,
            'book_price' => $bookinfo -> price,
            'book_createTime' => $bookinfo -> createTime,
            'book_uptime' => $bookinfo -> uptime,
            'book_authorIntro' => $bookinfo -> authorIntro,
            'book_description' => $bookinfo -> description,
            'book_stats' => $bookinfo -> book_stats
		);
		$insertId = $this->db->query($this->db->insert('table.bookinfo')->rows($links));
    }
    /**
     *  删除书籍信息
     */
    public function deleteBookInfo($bookid){
        $this->db->query($this->db->delete('table.bookinfo')->where('book_id = ?', $bookid));
    }
    /**
     * 更新书籍状态
     */
    public function updatebook($bookinfo,$state){
        $query=$this->db->update('table.bookinfo')->rows(array('book_stats' => $state))->where('book_id=?',$bookinfo);
        $this->db->query($query);
    }

}