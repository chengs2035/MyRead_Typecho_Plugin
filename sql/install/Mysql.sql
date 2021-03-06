CREATE TABLE IF NOT EXISTS `{prefix}bookinfo`(
  `book_id` int(10) NOT NULL AUTO_INCREMENT,
  `book_name` varchar(300) NOT NULL COMMENT '书名',
  `book_subname` varchar(300) NULL COMMENT 'subname',
  `book_author` varchar(300) NOT NULL COMMENT '作者',
  `book_translator` varchar(300) NULL COMMENT '译者',
  `book_publishing` varchar(300)  NULL COMMENT '出版社',
  `book_published` varchar(300)  NULL COMMENT '出版时间',
  `book_designed` varchar(300)  NULL COMMENT '装帧',
  `book_isbn` varchar(300) NOT NULL COMMENT 'ISBN',
  `book_douban_id` varchar(300)  NULL COMMENT '豆瓣ID',
  `book_douban_score` varchar(20)  NULL COMMENT '豆瓣评分',
  `book_brand` varchar(300)  NULL COMMENT '',
  `book_weight` varchar(300)  NULL COMMENT '',
  `book_size` varchar(300)  NULL COMMENT '',
  `book_pages` varchar(300)  NULL COMMENT '页数',
  `book_photoUrl` varchar(300)  NULL COMMENT '封面图片',
  `book_localPhotoUrl` varchar(300)  NULL COMMENT '本地图片',
  `book_price` varchar(300)  NULL COMMENT '价格',
  `book_createTime` varchar(300)  NULL COMMENT '创建时间',
  `book_uptime` varchar(300)  NULL COMMENT '更新时间',
  `book_authorIntro` varchar(3000)  NULL COMMENT '作者简介',
  `book_description` varchar(3000)  NULL COMMENT '书籍简介',
  `insrt_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '插入时间',
  `update_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `book_stats` tinyint NOT NULL COMMENT '阅读状态：0，未读，1，已读，2，在读',
  PRIMARY KEY (`book_id`)
)DEFAULT CHARSET = { charset } AUTO_INCREMENT = 1 COMMENT='我的图书信息';