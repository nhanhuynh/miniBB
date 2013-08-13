CREATE TABLE minibbtable_banned (
id int(10) NOT NULL auto_increment,
banip varchar(15) NOT NULL default '',
banreason varchar(255) NOT NULL DEFAULT '',
PRIMARY KEY(id)
) ENGINE=MYISAM;

CREATE TABLE minibbtable_forums (
forum_id int(10) NOT NULL auto_increment,
forum_name varchar(150) NOT NULL default '',
forum_desc text NOT NULL,
forum_order int(10) NOT NULL default '0',
forum_icon varchar(255) NOT NULL default 'default.gif',
topics_count int(10) NOT NULL default '0',
posts_count int(10) NOT NULL default '0',
forum_group varchar(150) not null default '',
PRIMARY KEY(forum_id)
) ENGINE=MYISAM;

CREATE TABLE minibbtable_posts (
post_id int(10) NOT NULL auto_increment,
forum_id int(10) NOT NULL default '1',
topic_id int(10) NOT NULL default '1',
poster_id int(10) NOT NULL default '0',
poster_name varchar(255) NOT NULL default 'Anonymous',
post_text text NOT NULL,
post_time datetime NOT NULL default '0000-00-00 00:00:00',
poster_ip varchar(15) NOT NULL default '',
post_status tinyint(1) NOT NULL default '0',
PRIMARY KEY(post_id),
KEY forum_id (forum_id),
KEY topic_id (topic_id),
KEY poster_id (poster_id),
KEY poster_ip (poster_ip)
) ENGINE=MYISAM;

CREATE TABLE minibbtable_send_mails (
id int(11) NOT NULL auto_increment,
user_id int(11) NOT NULL default '1',
topic_id int(11) NOT NULL default '0',
active int(1) UNSIGNED DEFAULT '1' NOT NULL,
email_code varchar(10),
PRIMARY KEY(id),
KEY topic_id (topic_id),
KEY user_id (user_id)
) ENGINE=MYISAM;

CREATE TABLE minibbtable_topics (
topic_id int(10) NOT NULL auto_increment,
topic_title varchar(255) NOT NULL default '',
topic_poster int(10) NOT NULL default '0',
topic_poster_name varchar(255) NOT NULL default 'Anonymous',
topic_time datetime NOT NULL default '0000-00-00 00:00:00',
topic_views int(10) default '0' not null,
forum_id int(10) NOT NULL default '1',
topic_status tinyint(1) NOT NULL default '0',
topic_last_post_id int(10) NOT NULL default '1',
posts_count int(10) NOT NULL default '0',
sticky int(1) NOT NULL default '0',
topic_last_post_time datetime not null default '0000-00-00 00:00:00',
topic_last_poster VARCHAR(255) default '' NOT NULL,
PRIMARY KEY(topic_id),
KEY forum_id (forum_id),
KEY topic_last_post_id (topic_last_post_id),
KEY sticky (sticky),
KEY posts_count (posts_count),
KEY topic_last_post_time (topic_last_post_time),
KEY topic_views (topic_views)
) ENGINE=MYISAM;

CREATE TABLE minibbtable_users (
user_id int(10) NOT NULL auto_increment,
username varchar(255) NOT NULL default '',
user_regdate datetime NOT NULL default '0000-00-00 00:00:00',
user_password varchar(32) NOT NULL default '',
user_email varchar(50) NOT NULL default '',
user_icq varchar(50) NOT NULL default '',
user_website varchar(100) NOT NULL default '',
user_occ varchar(100) NOT NULL default '',
user_from varchar(100) NOT NULL default '',
user_interest varchar(150) NOT NULL default '',
user_viewemail tinyint(1) NOT NULL default '0',
user_sorttopics tinyint(1) NOT NULL default '0',
user_newpwdkey varchar(32) NOT NULL default '',
user_newpasswd varchar(32) NOT NULL default '',
language char(3) NOT NULL default '',
activity int(1) NOT NULL default '1',
num_topics INT(10) UNSIGNED DEFAULT '0' NOT NULL,
num_posts INT(10) UNSIGNED DEFAULT '0' NOT NULL,
user_custom1 varchar(255) NOT NULL default '',
user_custom2 varchar(255) NOT NULL default '',
user_custom3 varchar(255) NOT NULL default '',
PRIMARY KEY(user_id),
KEY username (username)
) ENGINE=MYISAM;
