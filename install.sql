DROP TABLE IF EXISTS `caches`;
CREATE TABLE  `caches` (
  `cache_id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `value` longtext,
  PRIMARY KEY  (`cache_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `language`;
CREATE TABLE  `language` (
  `language_id` int(10) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `code` varchar(10) NOT NULL default '',
  `display_name` varchar(50) NOT NULL default '',
  `position` int(10) NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`language_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `language` VALUES  (1,'English','en','English',1,1);

DROP TABLE IF EXISTS `metadata`;
CREATE TABLE  `metadata` (
  `meta_id` bigint(15) NOT NULL auto_increment,
  `module` varchar(30) NOT NULL default '',
  `language_id` int(10) NOT NULL default '0',
  `id` int(10) NOT NULL default '0',
  `meta_key` varchar(255) NOT NULL default '',
  `meta_value` text,
  PRIMARY KEY  (`meta_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

INSERT INTO `metadata` VALUES  (1,'setting',0,0,'default_language_id','1'),
 (2,'setting',0,0,'session_timeout','30'),
 (3,'setting',0,0,'items_per_page','25'),
 (4,'setting',0,0,'padding','2'),
 (5,'setting',0,0,'cookie_enable','0'),
 (6,'setting',0,0,'seo_url','1');

DROP TABLE IF EXISTS `page`;
CREATE TABLE  `page` (
  `page_id` int(10) NOT NULL auto_increment,
  `parent_id` int(10) NOT NULL default '0',
  `type` varchar(30) NOT NULL default '',
  `languages` text,
  `position` int(10) NOT NULL default '0',
  `last_update` datetime NOT NULL,
  PRIMARY KEY  (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `session`;
CREATE TABLE  `session` (
  `session_id` varchar(32) NOT NULL default '',
  `user_id` int(10) NOT NULL default '0',
  `session_start` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_activity` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip_address` varchar(15) NOT NULL default '0.0.0.0',
  `application` varchar(250) NOT NULL default '',
  `remember` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `subpage`;
CREATE TABLE  `subpage` (
  `subpage_id` int(10) NOT NULL auto_increment,
  `page_id` int(10) NOT NULL default '0',
  `type` varchar(30) NOT NULL default '',
  `languages` text,
  `position` int(10) NOT NULL default '0',
  `last_update` datetime NOT NULL,
  PRIMARY KEY  (`subpage_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user`;
CREATE TABLE  `user` (
  `user_id` bigint(10) NOT NULL auto_increment,
  `type` varchar(30) NOT NULL default '',
  `first_name` varchar(100) NOT NULL default '',
  `last_name` varchar(100) NOT NULL default '',
  `email` varchar(150) NOT NULL default '',
  `email_alerts` tinyint(1) NOT NULL default 0,
  `language_id` int(10) NOT NULL default '0',
  `created` datetime NOT NULL default NOW(),
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `user` VALUES  (1,118,'superadmin','Keith','Rocheck','keithr@backlotimaging.com','866c893beb75aa530d93e4bed8305984','bfaa438b520713b08414790d6b354480',1, NOW());

DROP TABLE IF EXISTS `metadata_page`;
DROP VIEW IF EXISTS `metadata_page`;
CREATE VIEW `metadata_page` AS select `metadata`.`meta_id` AS `meta_id`,`metadata`.`language_id` AS `language_id`,`metadata`.`id` AS `id`,`metadata`.`meta_key` AS `meta_key`,`metadata`.`meta_value` AS `meta_value` from `metadata` where (`metadata`.`module` = _utf8'page');

DROP TABLE IF EXISTS `metadata_setting`;
DROP VIEW IF EXISTS `metadata_setting`;
CREATE VIEW `metadata_setting` AS select `metadata`.`meta_id` AS `meta_id`,`metadata`.`meta_key` AS `meta_key`,`metadata`.`meta_value` AS `meta_value` from `metadata` where (`metadata`.`module` = _utf8'setting');

DROP TABLE IF EXISTS `metadata_subpage`;
DROP VIEW IF EXISTS `metadata_subpage`;
CREATE VIEW `metadata_subpage` AS select `metadata`.`meta_id` AS `meta_id`,`metadata`.`language_id` AS `language_id`,`metadata`.`id` AS `id`,`metadata`.`meta_key` AS `meta_key`,`metadata`.`meta_value` AS `meta_value` from `metadata` where (`metadata`.`module` = _utf8'subpage');

DROP TABLE IF EXISTS `metadata_user`;
DROP VIEW IF EXISTS `metadata_user`;
CREATE VIEW `metadata_user` AS select `metadata`.`meta_id` AS `meta_id`,`metadata`.`id` AS `id`,`metadata`.`meta_key` AS `meta_key`,`metadata`.`meta_value` AS `meta_value` from `metadata` where (`metadata`.`module` = _utf8'user');

DROP TABLE IF EXISTS `logs`;
CREATE TABLE  `logs` (
  `log_id` int(10) NOT NULL AUTO_INCREMENT,
  `date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `type` varchar(255) NOT NULL default '',
  `total` int(10) NOT NULL default '0',
  `result` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `custom_field`;
CREATE TABLE `custom_field` (
  `field_gid` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `resource_subtype` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `enum_options` text,
  `last_update` datetime NOT NULL default NOW(),
  PRIMARY KEY (`field_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `portfolio`;
CREATE TABLE `portfolio` (
  `portfolio_gid` varchar(255) NOT NULL default '',
  `owner_gid` varchar(255) NOT NULL default '',
  `workspace_gid` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `due_on` date NOT NULL default '0000-00-00',
  `start_on` date NOT NULL default '0000-00-00',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `members` text,
  `custom_field_settings` text,
  `color` varchar(255) NOT NULL default '',
  `permalink_url` text,
  `last_update` datetime NOT NULL default NOW(),
  `projects` text,
  PRIMARY KEY (`portfolio_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `project_gid` varchar(255) NOT NULL default '',
  `owner_gid` varchar(255) NOT NULL default '',
  `workspace_gid` varchar(255) NOT NULL default '',
  `team_gid` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `current_status` text,
  `due_date` date NOT NULL default '0000-00-00',
  `start_on` date NOT NULL default '0000-00-00',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `archived` tinyint(1) NOT NULL default '0',
  `public` tinyint(1) NOT NULL default '0',
  `members` text,
  `followers` text,
  `custom_fields` text,
  `custom_field_settings` text,
  `color` varchar(255) NOT NULL default '',
  `html_notes` text,
  `layout` varchar(255) NOT NULL default '',
  `last_update` datetime NOT NULL default NOW(),
  `sections` text,
  `tasks` text,
  PRIMARY KEY (`project_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `section`;
CREATE TABLE `section` (
  `section_gid` varchar(255) NOT NULL default '',
  `project_gid` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_update` datetime NOT NULL default NOW(),
  `tasks` text,
  PRIMARY KEY (`section_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `tag_gid` varchar(255) NOT NULL default '',
  `workspace_gid` varchar(255) NOT NULL default '',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `followers` text,
  `name` varchar(255) NOT NULL default '',
  `color` varchar(255) NOT NULL default '',
  `last_update` datetime NOT NULL default NOW(),
  PRIMARY KEY (`tag_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `task_gid` varchar(255) NOT NULL default '',
  `parent_gid` varchar(255) NOT NULL default '',
  `workspace_gid` varchar(255) NOT NULL default '',
  `assignee_gid` varchar(255) NOT NULL default '',
  `resource_subtype` varchar(255) NOT NULL default '',
  `assignee_status` varchar(255) NOT NULL default '',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `completed` tinyint(1) NOT NULL default '0',
  `completed_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `custom_fields` text,
  `dependencies` text,
  `dependents` text,
  `due_on` date NOT NULL default '0000-00-00',
  `due_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `followers` text,
  `liked` tinyint(1) NOT NULL default '0',
  `likes` text,
  `modified_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(255) NOT NULL default '',
  `html_notes` text,
  `num_likes` int(10) NOT NULL default '0',
  `num_subtasks` int(10) NOT NULL DEFAULT '0',
  `subtasks` text,
  `projects` text,
  `start_on` date NOT NULL default '0000-00-00',
  `memberships` text,
  `tags` text,
  `last_update` datetime NOT NULL default NOW(),
  PRIMARY KEY (`task_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `team`;
CREATE TABLE `team` (
  `team_gid` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `html_description` text,
  `last_update` datetime NOT NULL default NOW(),
  PRIMARY KEY (`team_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `asana_user`;
CREATE TABLE `asana_user` (
  `user_gid` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `workspaces` text,
  `last_update` datetime NOT NULL default NOW(),
  PRIMARY KEY (`user_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `workspace`;
CREATE TABLE `workspace` (
  `workspace_gid` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `is_organization` tinyint(1) NOT NULL default '0',
  `last_update` datetime NOT NULL default NOW(),
  PRIMARY KEY (`workspace_gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
