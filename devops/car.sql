CREATE TABLE `car_lsls` (
  `id`      int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name_en` varchar(100) NOT NULL COMMENT '英文名',
  `name_zh` varchar(100) NOT NULL COMMENT '中文名',
  `image`   varchar(255) NOT NULL COMMENT '左侧车型图',
  `summary` varchar(255) NOT NULL COMMENT '描述',
  `status`  int NOT NULL DEFAULT 0 COMMENT '状态：0停用  1启用',
  `remark`  varchar(255) DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into car_lsls(`name_en`,`name_zh`,`image`,`summary`,`status`) values
('phantom',            '幻影',        '/content/dam/rollsroyce-website/MenuModelThumbnails/JPEGS/RR_Phantom_Model_Selector_Belladonna_3.jpg.rr.1198.LOW.jpg', '移动的艺术宫殿',  '1'),
('cullinan',           '库里南',      '/content/dam/rollsroyce-website/MenuModelThumbnails/JPEGS/RR_Cullinan-Model_Selector_02.jpg.rr.1198.LOW.jpg',       '悠然天地间',    '1'),
('ghost-overview',     '古思特',      '/content/dam/rollsroyce-website/MenuModelThumbnails/JPEGS/RR_Model_Selector_Ghost_SWB_Light.jpg.rr.1198.LOW.jpg',   '轻松简约之美',  '1'),
('dawn-overview',      '曜影',        '/content/dam/rollsroyce-website/MenuModelThumbnails/JPEGS/RR_Model_Selector_Dawn_Red_Light.jpg.rr.1198.LOW.jpg',    '美妙绝伦',      '1'),
('introducing-wraith', '魅影',        '/content/dam/rollsroyce-website/MenuModelThumbnails/JPEGS/Off-Canvas_Model_selector_v3-1_HR3.jpg.rr.1198.LOW.jpg',  '不羁动力、优雅格调、非凡魅力', '1'),
('black-badge',        'BLACK BADGE', '/content/dam/rollsroyce-website/MenuModelThumbnails/JPEGS/BB_Model_Selector_SideMenu_Wraith.jpg.rr.1198.LOW.jpg',   '勇闯暗夜：魅影，曜影，古思特', '1');
