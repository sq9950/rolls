alter table car_lsls add column `to_airpot_day` int          NOT NULL DEFAULT 0  COMMENT '到港时间'    after `page_index`;
alter table car_lsls add column `to_store_day`  int          NOT NULL DEFAULT 0  COMMENT '入库时间'    after `to_airpot_day`;
alter table car_lsls add column `cfg_pdf`       varchar(255) NOT NULL DEFAULT '' COMMENT '配置pdf文件' after `to_store_day`;

CREATE TABLE `car_stock` (
  `id`            int NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name`          varchar(100) NOT NULL COMMENT '车名',
  `to_airpot_day` int NOT NULL COMMENT '到港时间',
  `to_store_day`  int NOT NULL COMMENT '入库时间',
  `cfg_pdf`       varchar(255) NOT NULL DEFAULT '' COMMENT '配置pdf文件',
  `display`       int NOT NULL DEFAULT 0 COMMENT '是否展示：0不展示 1展示',
  `remark`        varchar(255) DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '车辆库存表';

