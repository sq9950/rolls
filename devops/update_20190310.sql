alter table car_lsls add column `page_index` varchar(255) NOT NULL COMMENT '首页' after `summary`;
update car_lsls set page_index='phantom-swb' where id=1;
update car_lsls set page_index='cullinan' where id=2;
update car_lsls set page_index='ghost-overview' where id=3;
update car_lsls set page_index='dawn-overview' where id=4;
update car_lsls set page_index='wraith' where id=5;
update car_lsls set page_index='dawn-black-badge' where id=6;

