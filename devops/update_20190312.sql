alter table car_lsls add column `to_airpot_day` int          NOT NULL DEFAULT 0  COMMENT '到港时间'    after `page_index`;
alter table car_lsls add column `to_store_day`  int          NOT NULL DEFAULT 0  COMMENT '入库时间'    after `to_airpot_day`;
alter table car_lsls add column `cfg_pdf`       varchar(255) NOT NULL DEFAULT '' COMMENT '配置pdf文件' after `to_store_day`;

