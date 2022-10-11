=== Plugin Name ===
Contributors: BCGov
Tags: cron, nginx
Requires at least: 3.0.1
Tested up to: 6.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple WP cron handler via external crontab and WP REST API.

== Installation ==

1. Upload zipped version via "Upload Plugin" tool.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. WP pods require a NGINX_PURGE_KEY env variable to trigger purges. This is set in the Helm chart for the WP deployment.
4. The crontab is also configured in the Helm chart. It activates two PHP files: (1) mailpoet-cron.php; and (2) wp-cron.php

== Changelog ==

= 1.1 =
* Include "Purge Cache" button for admin users on pages/posts.