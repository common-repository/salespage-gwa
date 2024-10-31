<?php
/*
Template Name: GWA_SALESPAGE
*/

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>[GWA] SalesPage Plugin for Wordpress default template.</title>
</head>
<body style="font-family:Tahoma,Verdana,Arial,Sans-serif"><CENTER><b>
<font color="#00f" face="Tahoma,Verdana,Sans-serif">
<!-- START COMMENT

Display info from your Wordpress DB. Access wp-option values as follows.
See Wordpress Codex (plugins website) for ll values available & how-to access.

COMMENT END -->

<?php echo get_bloginfo("name");?>

</font></b><br><br>
<div style="border: dashed 3px #f00;width:350px;min-height:150px;">

<!-- START COMMENT 

Use the php tag immediately below to insert your payment button and all html.

COMMENT END -->

<?php echo sp_gwa_form_display(); ?>

</div><br><a href="http://ogblog.biz">

<!-- START COMMENT

Link to an image in plugin /salespage-gwa/index_files using img/php tag below.
Store custom images in the dir & only replace image filename as required.

COMMENT END -->

<img src="<?php echo WP_PLUGIN_URL.'/salespage-gwa/index_files/';?>sp_button.gif" border="0" />

</a></CENTER></body></html>