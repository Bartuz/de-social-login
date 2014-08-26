=== DE Social Login ===
Contributors: surinder83singh@gmail.com, sunildhanda
Donate link: http://developerextensions.com
Tags: Wordpress Social Login, Wordpress Login, Social Login, login with facebook, login with twitter, login with linkedin, login with google, login with yahoo, login with openid, facebook, twitter, yahoo, linkedin, google, open id
Requires at least: 3.0
Tested up to: 3.9.2
Stable tag: 1.0.2
License: GPLv2 or later

A Simple wordpress plugin which enable the user to login in wordress site with Google/Facebook/Twitter/LinkedIn/Yahoo/OpenId accounts with one click.

== Description ==

A Simple wordpress plugin which enable the user to login in wordress site with Google/Facebook/Twitter/LinkedIn/Yahoo/OpenId accounts with one click.

Features:-
1. Easy to manage.
2. It offers the user to login with social ids i.e Facebook, Twitter, Google etc.
3. You can enable/disable according to you requirement out of Facebook, Twitter, Google, Yahoo, LinkedIn and OpenId
4. Yor can change the order by dragging up and down.
5. Supportable to custom template.
For further help contact us at [http://devx.in](http://devx.in) [http://sunilkumardhanda.me](http://sunilkumardhanda.me)

== Installation ==

1. Upload the DE Social Login plugin to your plugin directory wp-content/plugins/. 
2. Activate it via the 'Plugins' menu in WordPress.
3. Fill the required info under Setting->DE Social Login.

You can also installed directly from the main WordPress Plugin page.

1. Go to the Plugins => Add New page.
2. Enter 'de-social-login' in the textbox and click the 'Search Plugins' button.
3. In the list of relevant Plugins click the 'Install' link for Simple Sitemap on the right hand side of the page.
4. Click the 'Install Now' button on the popup page.
5. Click 'Activate Plugin' to finish installation.
6. Goto settins/DE Social Login menu in WordPress.
7. Fill the required info and manage it.


== Changelog ==
= 1.0.2 =
Shortcode added to apply the login form in pages, posts or widgets.
Use <code>[WPDE_LOGIN_FORM]</code> to apply it.

Minnor bug fixes.

= 1.0.1 =
Minnor bug fixes.

= 1.0 =
Login with google Migrated to Google+ Sign-In

= 0.1.7 =
Functionality added to stop and start force registration for google only.
On enable of Force Register option under general settings, plugin will register the user if not exists. Default only already existing users can login.

= 0.1.6 =
Bug fix, which is created by myself during previous bug fix.

= 0.1.5 =
Bug fix for 5.4 lower version of php
<code>Parse error: syntax error, unexpected T_FUNCTION in /home/content/.../wordpress/wp-content/plugins/de-social-login/linkedin/linkedin_3.2.0.class.php on line 635</code>

= 0.1.4 =
Minor bug fixes

= 0.1.3 =
Logout url added.

= 0.1.2 =
Supportable to custom template. Use the code below to add in your custome template:
<code>if(function_exists('wp_de_render_login_form')){wp_de_render_login_form();}</code>

= 0.1.1 =
Plugin name updated in readme

= 0.1.0 =
Launch DE Social Login

== Screenshots ==
1. DE Social Login Settings
2. Order Settings