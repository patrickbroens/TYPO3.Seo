#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	seo_browser_title VARCHAR(255) DEFAULT '' NOT NULL,
	seo_focus_keyword VARCHAR(150) DEFAULT '' NOT NULL
);

#
# Table structure for table 'pages_language_overlay'
#
CREATE TABLE pages_language_overlay (
	seo_browser_title VARCHAR(255) DEFAULT '' NOT NULL,
	seo_focus_keyword VARCHAR(150) DEFAULT '' NOT NULL
);