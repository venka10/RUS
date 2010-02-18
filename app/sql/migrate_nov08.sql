ALTER TABLE `rus`.`projects` ADD archived INT(1) NOT NULL DEFAULT '0';
insert into site_maps ( controller, action ) values ('Projects','hide');
insert into site_maps ( controller, action ) values ('Projects','archived');
insert into site_maps ( controller, action ) values ('Projects','reactivate');
commit;
insert into app_roles_site_maps ( app_role_id, site_map_id)
select a.id, s.id
from app_roles a, site_maps s
where a.name='SuperAdmin'
and (s.controller='Projects' and s.action in ('hide','archived','reactivate'));
commit;

ALTER TABLE `rus`.`sites` ADD order_no INT(10) ;
update sites set order_no=id;
commit;
ALTER TABLE `rus`.`sites` CHANGE `order_no` order_no INT(10) NOT NULL;


insert into site_maps ( controller, action ) values ('Projects','order');
insert into app_roles_site_maps ( app_role_id, site_map_id)
select a.id, s.id
from app_roles a, site_maps s
where a.name in ('SuperAdmin','Writer','Regional Mgr')
and (s.controller='Projects' and s.action ='order');
commit;
