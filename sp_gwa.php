<?php
/*
Plugin Name: [GWA] SalesPage
Plugin URI: http://Code4Cookies.com
Description: Add Product Sales with a Paypal Express Checkout or a Paypal Subscription Button to any weblog page. Simply set the private link to your product for automated Paypal IPN purchase fulfillment. 
Version: 1.3
Author: G.J.P
Author URI: http://www.getwebactive.com
*/
/*
1.1.1
- tinymce added to product decription
- shortcode [gwa-salespage] for display

1.2.1
- translated version with default 'en' locale

1.3
- tinymce updated/fixed for WP3.x
- added subscription payment option
- added wp-template example file
*/

/*
    Copyright 2008-2011  G.J.P  (email : cookies@getwebactive.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
tag : <?php if(function_exists('sp_gwa_form_display')) echo sp_gwa_form_display(); ?>
*/

function ar_gwa_plugin_init() {
			$currentLocale = get_locale();
			if(!empty($currentLocale)) {
				$moFile = dirname(__FILE__) . "/gwasalespage-" . $currentLocale . ".mo";
				if(@file_exists($moFile) && is_readable($moFile)) 
        load_plugin_textdomain('gwasalespage',false,dirname( plugin_basename( __FILE__ ) ));
#        load_plugin_textdomain('salespage', $moFile);
#				echo $moFile;
			}
}

$bloginfo_url = get_bloginfo('url');

if(is_array($_REQUEST['sp_gwa_id'])){

sp_gwa_delete_record($_REQUEST['sp_gwa_id']);

} else if(isset($_REQUEST['sp_gwa_newclient'])) {
sp_gwa_add_record();
}

function sp_gwa_add_record() {

global $wpdb;
if(isset($_REQUEST['sp_gwa_camt']) && $_REQUEST['sp_gwa_camt']!='')
if(isset($_REQUEST['sp_gwa_ctxn']) && $_REQUEST['sp_gwa_ctxn']!='')
$wpdb->query("INSERT INTO ".$wpdb->prefix."sp_gwa_sales (`name`, `pp_client`, `amount`, `txn_id`, `pp`, `sale_date`, `pickup`) VALUES ('{$_REQUEST['sp_gwa_cname']}', '{$_REQUEST['sp_gwa_cpp']}', '{$_REQUEST['sp_gwa_camt']}', '{$_REQUEST['sp_gwa_ctxn']}', '{$_REQUEST['sp_gwa_cpp1']}', '{$_REQUEST['sp_gwa_cdate']}', 0)");
  }


function sp_gwa_delete_record($sp_gwa_id) {
global $wpdb;
  foreach($sp_gwa_id as $id) {
    $wpdb->query("DELETE FROM ".$wpdb->prefix."sp_gwa_sales WHERE id='".$id."'");
  }
}

class ar_gwa_i18n {
  var $values = array();
  var $glocale; 

function ar_gwa_i18n() {
  if(WPLANG=='')
    $this->glocale = 'en_US';
  else
    $this->glocale = WPLANG;
  if(file_exists(dirname(__FILE__).'/sp_gwa_'.$this->glocale.'.txt')) {
    $tfile = file(dirname(__FILE__).'/sp_gwa_'.$this->glocale.'.txt');
    foreach ($tfile as $tline) {
     $parv = preg_split("/,/",$tline);
     $parv[1] = preg_replace("/\\n$/",'',$parv[1]);
     $this->values[$parv[0]] = $parv[1];
    }
   }  
  }
  function gwa_i18n($tag) {
   return $this->values[$tag]; 
  }
}

$trx = new ar_gwa_i18n;

  if($_REQUEST['sp_gwa_tagf']) {
    update_option('sp_gwa_tagf',$_REQUEST['sp_gwa_tagf']);    
  }

if((get_option('sp_gwa_tagf'))!=1){
$sp_gwa_tag = sprintf("<p style='margin:0px;padding:0px;font-size:8pt;line-height:10pt;'><a style='font-family:Verdana,Arial,Sans-serif;font-size:7pt;text-decoration:none;font-weight:normal;' href='http://Code4Cookies.com' target='_blank'>%s <u>Code4Cookies.com</u></a></p>",$trx->gwa_i18n('courtesy'));
}

if(!function_exists('sp_gwa_ipn_response')) { 
function sp_gwa_ipn_response() {
global $wpdb,$bloginfo_url;
$trx = new ar_gwa_i18n;
$wpdb->show_errors();
$pp = new paypal_class;
if(isset($_REQUEST['txn_id'])) {
  if($pp->validate_ipn()) {
  if($pp->ipn_data['txn_type']=='subscr_payment' || $pp->ipn_data['txn_type']=='web_accept') {
  $prod_detail = 'PP_Product: '.$pp->ipn_data['item_name']."\n".
  'PP_Buyer: '.$pp->ipn_data['payer_email']."\n".
  'PP_Buyer ID: '.$pp->ipn_data['payer_id']."\n".
  'PP_Payment Type: '.$pp->ipn_data['txn_type']."\n".
  'PP_Amount: $'.$pp->ipn_data['mc_gross']." ".$pp->ipn_data['mc_currency']."\n"
  .$trx->gwa_i18n('label1').$pp->ipn_data['txn_id']
  ."\n".$trx->gwa_i18n('label2').": $bloginfo_url?page_id=".get_option('sp_gwa_page_select')."&sp_gwa_ppr=1";
  $wpdb->query("INSERT INTO ".$wpdb->prefix."sp_gwa_sales (id,name,pp_client,amount,txn_id,pp) VALUES (NULL,'{$pp->ipn_data['item_name']}','{$pp->ipn_data['payer_email']}','{$pp->ipn_data['mc_gross']}','{$pp->ipn_data['txn_id']}','{$pp->ipn_data['payer_id']}')");
mail($pp->ipn_data['payer_email'], sprintf("%s %s",$trx->gwa_i18n('label3'),get_bloginfo('name')),get_option('sp_gwa_confirm_email')."\n\n---------------------\n".$prod_detail,"From: ".get_bloginfo('email')." <".get_bloginfo('email').">\n\n");
mail(get_option('sp_gwa_paypal_email'), '(COPY) '.sprintf("%s %s",$trx->gwa_i18n('label3'),get_bloginfo('name')),get_option('sp_gwa_confirm_email')."\n\n---------------------\n".$prod_detail,"From: ".get_bloginfo('email')." <".get_bloginfo('email').">\n\n");
} else die('Not Validated - Void'); } else die('Not Validated - Void'); } else if($_REQUEST['sp_gwa_ppr']=='1') {
query_posts($query_string."&page_id=".get_option('sp_gwa_page_select'));
$url = get_bloginfo('url');
function sp_gwa_form_display() {
global $bloginfo_url,$sp_gwa_tag,$trx;
$zap = $trx->gwa_i18n('label4');
$zap .= "<form action='$bloginfo_url/index.php?sp_gwa_ppr=2&page_id=".get_option('sp_gwa_page_select')."' method='GET' id='leads-filter'>";
$zap .= $trx->gwa_i18n('label5'); 
$zap .= "<br /><br /><input type='text' name='ppr_id' value='' size='33'>&nbsp;<input type='submit' name='sp_gwa_submit' value='".$trx->gwa_i18n('label6')."' class='button'></form><br />$sp_gwa_tag</CENTER>";
return $zap;
}} else if(isset($_REQUEST['ppr_id'])) {
  if(isset($_REQUEST['ppr_id'])) {
  $url = get_option('sp_gwa_product_link');
  $ex = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."sp_gwa_sales WHERE txn_id = '".$wpdb->escape($_REQUEST['ppr_id'])."'");
  if($ex>0) {
  if(!$url) $url = $bloginfo_url;
  $rr = $wpdb->query("UPDATE ".$wpdb->prefix."sp_gwa_sales SET pickup=pickup+1 WHERE id = '$ex'");
  header("Location: $url\n\n");
  exit;
  } else {
function sp_gwa_form_display() {
global $bloginfo_url,$sp_gwa_tag,$trx;
$zap = $trx->gwa_i18n('label4')."<form action='$bloginfo_url/index.php?sp_gwa_ppr=2&page_id=".get_option('sp_gwa_page_select')."' method='GET' id='leads-filter'>";
$zap .= $trx->gwa_i18n('label5');
$zap .= "<br /><br /><input type='text' name='ppr_id' value='' size='33'>&nbsp;<input type='submit' name='sp_gwa_submit' value='".$trx->gwa_i18n('label6')."' class='button'></form><p style='margin:0px;padding:0px;font-size:8pt;line-height:8pt;'>$sp_gwa_tag</p></CENTER>";
return $zap;
}}}} else {
function sp_gwa_form_display() {
global $bloginfo_url,$sp_gwa_tag,$trx;
$sp_gwa_paypal_email = get_option('sp_gwa_paypal_email');
$sp_gwa_paypal_curr = get_option('sp_gwa_paypal_curr');
$sp_gwa_paypal_url = get_option('sp_gwa_paypal_url');
$sp_gwa_paypal_type = get_option('sp_gwa_paypal_type');

$sp_gwa_paypal_show = get_option('sp_gwa_paypal_show');

$sp_gwa_page_select = get_option('sp_gwa_page_select');
$sp_gwa_product_name = stripslashes(get_option('sp_gwa_product_name'));
$sp_gwa_product_price = get_option('sp_gwa_product_price');
$sp_gwa_product_desc = stripslashes(get_option('sp_gwa_product_desc'));
$zap = "<CENTER>";
 if(get_option('sp_gwa_paypal_url')==1)
 $sp_gwa_paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
 else  if(get_option('sp_gwa_paypal_url')==2)
 $sp_gwa_paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
 if(is_page(get_option('sp_gwa_page_select')) || is_page_template('sp_gwa_tpl.php')){
 if($sp_gwa_paypal_show==1)
 $zap .="<div id='paypalForm' style='padding:5px;width:300px;text-align:left;'><p align='left' style='margin-top:0px;'><strong>$sp_gwa_product_name</strong><br />".__("Price",'gwasalespage').": <strong>\$$sp_gwa_product_price&nbsp;$sp_gwa_paypal_curr</strong>$sp_gwa_product_desc</p>";
$zap .= "<form action='$sp_gwa_paypal_url' method='post' target='_self'>";
if($sp_gwa_paypal_type == 2) {
$zap .= '<input class=" " name="cmd" value="_xclick-subscriptions" type="hidden">
<input class=" " name="modify" value="1" type="hidden">
<input class=" " name="bn" value="PP-SubscriptionsBF" type="hidden">
<input class=" " name="src" value="1" type="hidden">
<input class=" " name="sra" value="1" type="hidden">
<input class=" " name="item_name" value="'.$sp_gwa_product_name.'" type="hidden">
<input class=" " name="a3" value="'.$sp_gwa_product_price.'" type="hidden">
<input class=" " name="item_number" value="1" type="hidden">
<input class=" " name="t3" value="M" type="hidden">
<input class=" " name="p3" value="1" type="hidden">';
} else {
$zap .= "<input type='hidden' name='cmd' value='_xclick' />
<input type='hidden' name='item_name' value='$sp_gwa_product_name' />
<input type='hidden' name='quantity' value='1' />
<input type='hidden' name='amount' value='$sp_gwa_product_price' />
<input type='hidden' name='item_number' value='1' />";
}
$zap .="<input type='hidden' name='business' value='$sp_gwa_paypal_email' />
<input type='hidden' name='currency_code' value='$sp_gwa_paypal_curr' />
<input type='hidden' name='custom' value='1' />
<input type='hidden' name='no_shipping' value='1' />
<input type='hidden' name='no_note' value='0' />
<input type='hidden' name='return' value='".get_bloginfo('url')."?sp_gwa_ppr=1&page_id=".$sp_gwa_page_select."' />
<input type='hidden' name='notify_url' value='".get_bloginfo('url')."' />
<input type='hidden' name='cancel_return' value='".get_bloginfo('url')."?page_id=".$sp_gwa_page_select."' />
<div style='text-align:center;'><input type='image' src='wp-content/plugins/salespage-gwa/paypal_buynow.gif' name='submit' alt='".$trx->gwa_i18n('label7')."' /></div></form>";
 if($sp_gwa_paypal_show==1)
  $zap .="</div>";
  $zap .="$sp_gwa_tag</CENTER>";
return $zap;
}}}}}

if(!function_exists('sp_gwa_install')) { 
function sp_gwa_install() {
global $wpdb;
   $table_name = $wpdb->prefix . "sp_gwa_sales";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
    $sql = "CREATE TABLE `" . $table_name . "` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `pp_client` varchar(255) NOT NULL,
  `amount` decimal(6,2) NOT NULL,
  `txn_id` varchar(55) NOT NULL,
  `pp` varchar(255) NOT NULL,
  `sale_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `pickup` tinyint(1) NOT NULL,
    PRIMARY KEY  (`id`)) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
   $results = $wpdb->query( $sql );
  }
  update_option('sp_gwa_paypal_url','1');
  update_option('sp_gwa_paypal_show','1');
  update_option('sp_gwa_paypal_type','1');
  update_option('sp_gwa_paypal_curr','USD');
  update_option('sp_gwa_product_link',get_bloginfo('url')."/wp-content/plugins/salespage-gwa/dl_files");
}}

if(!function_exists('modify_sp_gwa_menu')) { 
function modify_sp_gwa_menu() {
add_options_page('sp_gwa','[GWA] SalesPage','manage_options',__FILE__,'admin_sp_gwa_options');
add_submenu_page('tools.php', '[GWA] SalesPage Products', '[GWA] SalesPage', 7, __FILE__, 'admin_sp_gwa_products');
}}

if(!function_exists('admin_sp_gwa_products')) { 
function admin_sp_gwa_products() {
global $wpdb;
$res = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."sp_gwa_sales WHERE 1");
if($res) {
if(get_option('sp_gwa_paypal_email')=='your-paypal-receiver email')
echo '<div id="update-nag">You have <u>not</u> completed the setup of the [GWA] SalesPage Plugin. You must do this before it will operate correctly.</div>';
?>
<h2>Manage [GWA] SalesPage Sales Records</h2>
<form action="" method="post" id="leads-filter">
<table class="widefat">
	<thead><tr>
	<th scope="col" class="check-column"><input type="checkbox" onclick="checkAll(document.getElementById('leads-filter'));" /></th>
	<th scope="col"><?php _e("List",'gwasalespage'); ?></th>
	<th scope="col"><?php _e("Product",'gwasalespage'); ?></th>
	<th scope="col"><?php _e("Client",'gwasalespage'); ?></th>
	<th scope="col"><?php _e("Amount",'gwasalespage'); ?></th>
	<th scope="col">TXN_ID</th>
	<th scope="col">PP_UID</th>
	<th scope="col"><?php _e("DATE",'gwasalespage'); ?></th>
	</tr>	</thead>
	<tbody>
	<tr id='post-1' class='alternate author-self status-publish' valign="top">
<?php
foreach($res as $row)
{	
echo '<tr><th scope="row" class="check-column"><input type="checkbox" name="sp_gwa_id[]" value="'.$row->id.'" /></th>'
				.'<td><abbr title="'.$row->id.'">'.$row->id.'</abbr></td>'
				.'<td><abbr title="'.$row->name.'">'.$row->name.'</abbr></td>'
				.'<td><strong><a class="row-title" href="mailto:'.$row->pp_client.'" title="Email Lead">'.$row->pp_client.'</a></strong></td>'
				.'<td>'.$row->amount.'</td>'
				.'<td>'.$row->txn_id.'</td>'
				.'<td>'.$row->pp.'</td>'
				.'<td>'.$row->sale_date.'</td></tr>';
}
echo '<tr><th scope="row" class="check-column"><input type="checkbox" name="sp_gwa_id[]" value="'.$row->id.'" disabled /></th>'
				.'<td></td>'
				.'<td><input type="text" name="sp_gwa_cname" id="sp_gwa_cname"><br />product name</td>'
				.'<td><input type="text" name="sp_gwa_cpp" id="sp_gwa_cpp"><br />paypal client email</td>'
				.'<td><input type="text" name="sp_gwa_camt" id="sp_gwa_camt" size="4"></td>'
				.'<td><input type="text" name="sp_gwa_ctxn" id="sp_gwa_ctxn" size="4"></td>'
				.'<td><input type="text" name="sp_gwa_cpp1" id="sp_gwa_cpp1" size="4"></td>'
				.'<td><input type="text" name="sp_gwa_cdate" id="sp_gwa_cdate"><br />0000-00-00 00:00:00</td></tr></table>';
echo '<p><input type="submit" class="button" value="'.__("Delete Checked",'gwasalespage').'">&nbsp;<input name="sp_gwa_newclient" type="submit" class="button" value="'.__("Add New Client - Sale",'gwasalespage').'">';
	echo '</form></div></div>';
} else {
if(get_option('sp_gwa_paypal_email')=='your-paypal-receiver email')
echo '<div id="update-nag">You have <u>not</u> completed the setup of the [GWA] SalesPage Plugin. You must do this before it will operate correctly.</div>';?>
<h2>Manage [GWA] SalesPage Sales Records</h2>
<form action="" method="post" id="leads-filter">
<table class="widefat">
	<thead>	<tr>
	<th scope="col" class="check-column"><input type="checkbox" onclick="checkAll(document.getElementById('leads-filter'));" /></th>
	<th scope="col"><?php _e("List",'gwasalespage'); ?></th>
	<th scope="col"><?php _e("Product",'gwasalespage'); ?></th>
	<th scope="col"><?php _e("Client",'gwasalespage'); ?></th>
	<th scope="col"><?php _e("Amount",'gwasalespage'); ?></th>
	<th scope="col">TXN_ID</th>
	<th scope="col">PP_UID</th>
	<th scope="col"><?php _e("DATE",'gwasalespage'); ?></th>
	</tr>
	</thead>
	<tbody>
	<tr id='post-1' class='alternate author-self status-publish' valign="top">
<?php
		echo '<th scope="row" class="check-column"><input type="checkbox" name="sp_gwa_id[]" value="'.$row->id.'" /></th>'
				.'<td colspan="7" align="center"><h2>'.__("No Records",'gwasalespage').'</h2></td></tr>';
		echo '<tr><th scope="row" class="check-column"><input type="checkbox" name="sp_gwa_id[]" value="'.$row->id.'" disabled /></th>'
				.'<td></td>'
				.'<td><input type="text" name="sp_gwa_cname" id="sp_gwa_cname"><br />product name</td>'
				.'<td><input type="text" name="sp_gwa_cpp" id="sp_gwa_cpp"><br />paypal client email</td>'
				.'<td><input type="text" name="sp_gwa_camt" id="sp_gwa_camt" size="4"></td>'
				.'<td><input type="text" name="sp_gwa_ctxn" id="sp_gwa_ctxn" size="4"></td>'
				.'<td><input type="text" name="sp_gwa_cpp1" id="sp_gwa_cpp1" size="4"></td>'
				.'<td><input type="text" name="sp_gwa_cdate" id="sp_gwa_cdate"><br />0000-00-00 00:00:00</td></tr></table>';
	echo '<p>&nbsp;<input name="sp_gwa_newclient" type="submit" class="button" value="'.__("Add New Client - Sale",'gwasalespage').'"></p>';
	echo '</form></div></div>';
}}}

if(!function_exists('set_sp_gwa_options')) { 
function set_sp_gwa_options()
{
add_option('sp_gwa_paypal_email','your-paypal-receiver email');
add_option('sp_gwa_product_name','Default product name');
add_option('sp_gwa_product_price','1.00');
add_option('sp_gwa_product_desc','<p>Default product/form display.</p>');
add_option('sp_gwa_paypal_curr','USD');
add_option('sp_gwa_product_link','http://');
add_option('sp_gwa_confirm_email','Here are your payment details.');
add_option('sp_gwa_paypal_url','https://www.paypal.com/cgi-bin/webscr');
}}

if(!function_exists('unset_sp_gwa_options')) { 
function unset_sp_gwa_options()
{
delete_option('sp_gwa_paypal_email');
delete_option('sp_gwa_page_select');
delete_option('sp_gwa_product_name');
delete_option('sp_gwa_product_price');
delete_option('sp_gwa_product_desc');
delete_option('sp_gwa_paypal_curr');
delete_option('sp_gwa_product_link');
delete_option('sp_gwa_confirm_email');
delete_option('sp_gwa_paypal_url');
delete_option('sp_gwa_paypal_type');
delete_option('sp_gwa_paypal_show');
delete_option('sp_gwa_tagf');
}}

if(!function_exists('admin_sp_gwa_options')) { 
function admin_sp_gwa_options() {

if($_REQUEST['submit'])
update_sp_gwa_options();
if(get_option('sp_gwa_paypal_email')=='your-paypal-receiver email')
echo '<div id="update-nag">You have <u>not</u> completed the setup of the [GWA] SalesPage Plugin. You must do this before it will operate correctly.</div>';
?><div class="wrap"><h2>[GWA] SalesPage Settings</h2><?php
print_sp_gwa_options_form();
?></div><?php
}}

if(!function_exists('admin_sp_gwa_sp_gwa')) { 
function admin_sp_gwa_sp_gwa() {
if(get_option('sp_gwa_paypal_email')=='your-paypal-receiver email')
echo '<div id="update-nag">You have <u>not</u> completed the setup of the [GWA] SalesPage Plugin. You must do this before it will operate correctly.</div>';
?><div class="wrap"><h2>[GWA] SalesPage Manager</h2><?php

if($_REQUEST['submit'])
update_sp_gwa_sp_gwa();
print_sp_gwa_form();
?></div><?php
}}

if(!function_exists('update_sp_gwa_sp_gwa')) { 
function update_sp_gwa_sp_gwa() {
  $ok = false;
  if(Count($_REQUEST['sp_gwa']>0)) {
  // Update All Affiliate Links
  }
  if($ok) {
  ?><div id="message" class="updated fade">
  <p>[GWA] SalesPage form options saved.</p>
  </div>
  <?php
  } else {
  ?><div id="message" class="error fade">
  <p>ERROR! [GWA] SalesPage form options NOT saved.</p>
  </div>
  <?php  
  }
}}

if(!function_exists('update_sp_gwa_options')) { 
function update_sp_gwa_options() {
  $ok = false;
  if($_REQUEST['sp_gwa_paypal_email']) {
    update_option('sp_gwa_paypal_email',$_REQUEST['sp_gwa_paypal_email']);    
    $ok = true;
  }
  if($_REQUEST['sp_gwa_page_select']) {
    update_option('sp_gwa_page_select',$_REQUEST['sp_gwa_page_select']);    
    $ok = true;
  }
  if($_REQUEST['sp_gwa_product_name']) {
    update_option('sp_gwa_product_name',$_REQUEST['sp_gwa_product_name']);    
    $ok = true;
  }
  if($_REQUEST['sp_gwa_product_price']) {
    update_option('sp_gwa_product_price',$_REQUEST['sp_gwa_product_price']);    
    $ok = true;
  }
  if($_REQUEST['sp_gwa_product_desc']) {
    update_option('sp_gwa_product_desc',$_REQUEST['sp_gwa_product_desc']);    
    $ok = true;
  }
  if($_REQUEST['sp_gwa_product_link']) {
    update_option('sp_gwa_product_link',$_REQUEST['sp_gwa_product_link']);    
    $ok = true;
  }
  if($_REQUEST['sp_gwa_paypal_curr']) {
    update_option('sp_gwa_paypal_curr',$_REQUEST['sp_gwa_paypal_curr']);    
    $ok = true;
  }
  if($_REQUEST['sp_gwa_confirm_email']) {
    update_option('sp_gwa_confirm_email',$_REQUEST['sp_gwa_confirm_email']);    
    $ok = true;
  }
  if($_REQUEST['sp_gwa_paypal_url']) {
    update_option('sp_gwa_paypal_url',$_REQUEST['sp_gwa_paypal_url']);    
    $ok = true;
  }
  if($_REQUEST['sp_gwa_paypal_type']) {
    update_option('sp_gwa_paypal_type',$_REQUEST['sp_gwa_paypal_type']);    
    $ok = true;
  }
  if($_REQUEST['sp_gwa_paypal_show']) {
    update_option('sp_gwa_paypal_show',$_REQUEST['sp_gwa_paypal_show']);    
    $ok = true;
  }
  if($_REQUEST['sp_gwa_tagf']) {
    update_option('sp_gwa_tagf',$_REQUEST['sp_gwa_tagf']);    
    $ok = true;
  }
  if(!$_REQUEST['sp_gwa_page_select']) {
  ?><div id="message" class="error fade">
  <p><strong style="color:#f00">ERROR! Options NOT saved!</strong> You must SELECT A PAGE on which to display your plug-in results.</p>
  </div>
  <?php    
  } else {
  if($ok) {
  ?><div id="message" class="updated fade">
  <p>Options saved.</p>
  </div>
  <?php
  } else {
  ?><div id="message" class="error fade">
  <p>ERROR! Options NOT saved.</p>
  </div>
  <?php  
  }
 }
}}

if(!function_exists('sp_gwa_short_code')) {
  function sp_gwa_short_code($atts=NULL) {
    if(function_exists('sp_gwa_form_display')) return sp_gwa_form_display();
  }
}

if(!function_exists('print_sp_gwa_options_form')) { 
function print_sp_gwa_options_form() {
global $wpdb;
$curr = array('USD','CDN','AUD','GBP','CZK','DKK','EUR','HKD','HUF','ILS','JPY','MXN','NZD','NOK','PLN','SGD','SEK','CHF');
$sp_gwa_paypal_email = get_option('sp_gwa_paypal_email');
$sp_gwa_paypal_curr = get_option('sp_gwa_paypal_curr');
$sp_gwa_page_select = get_option('sp_gwa_page_select');
$sp_gwa_product_name = $wpdb->escape(stripslashes(get_option('sp_gwa_product_name')));
$sp_gwa_product_price = get_option('sp_gwa_product_price');
$sp_gwa_product_desc = $wpdb->escape(stripslashes(get_option('sp_gwa_product_desc')));
$sp_gwa_product_link = get_option('sp_gwa_product_link');
$sp_gwa_confirm_email = $wpdb->escape(stripslashes(get_option('sp_gwa_confirm_email')));
$sp_gwa_paypal_url = get_option('sp_gwa_paypal_url');
$sp_gwa_paypal_type = get_option('sp_gwa_paypal_type');
$sp_gwa_paypal_show = get_option('sp_gwa_paypal_show');

if (function_exists('wp_tiny_mce')) { wp_tiny_mce( false ,
array("editor_selector" => "editorContent","plugins" => "spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template","theme_advanced_buttons1" => "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect","theme_advanced_buttons2" => "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor","theme_advanced_buttons3" => "hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen,|,spellchecker","extended_valid_elements" => "form[*],div[*],style[*],p[*],table[*],td[*]","skin" => "o2k7","skin_variant" => "silver",));
}
?>
<script type="text/javascript">
<!--
function toggleEditor(id) {
	if (!tinyMCE.getInstanceById(id))
		tinyMCE.execCommand('mceAddControl', false, id);
	else
		tinyMCE.execCommand('mceRemoveControl', false, id);
}
-->
</script>
<style>.form-table p { font-size:11pt;}</style><form method="POST"><table class="form-table"><tr><td><div style="position:relative;float:left;"><p>
<?php 
_e("Enter your Paypal email address and the direct link to your product (http: or ftp:) and you are ready to begin accepting payments on your Weblog.",'gwasalespage');
if((get_option('sp_gwa_tagf'))!=1) { ?>&nbsp;
<a href="?page=salespage-gwa/sp_gwa.php&sp_gwa_tagf=1"><?php _e("Click here",'gwasalespage'); ?></a> <?php _e("to remove plugin branding.",'gwasalespage');
} else { ?>&nbsp;<a href="?page=salespage-gwa/sp_gwa.php&sp_gwa_tagf=2"><?php _e("Click here",'gwasalespage'); ?></a> <?php _e("to support the author of this plugin and display plugin branding.",'gwasalespage'); $spgwalink='<br /><br /><a href="http://wordpressemailsoftware.com" target="_blank" title="'.__("Will open in a new window.",'gwasalespage').'"><img src="http://freeautoresponder.biz/images/links/new_banner_blue_wp.jpg" width="468" height="60" border="0" alt="'.__("Will open in a new window.",'gwasalespage').'"></a>';
$spgwalink2='<br /><a href="http://ogblog.biz" target="_blank" title="'.__("Will open in a new window.",'gwasalespage').'"><img src="http://ogblog.biz/wp-content/themes/newone/images/sp_sidebar_big.gif" border="0" alt="'.__("Will open in a new window.",'gwasalespage').'"></a><br />';
} 
echo "&nbsp;".__("You may assign your Paypal Checkout Button to any page using the drop-down box below. You can have Express Purchase or Subscription Payment transaction buttons.",'gwasalespage');
echo $spgwalink;
echo "&nbsp;".__("<p>Use this Wordpress Shortcode within your posts and pages to display your form: <strong>[gwa-salespage]</strong></p><p>This is the php tag you can insert directly in your template (or sidebar?)<br>",'gwasalespage'); 
?>
<code>
&lt;?php if(function_exists('sp_gwa_form_display')) echo sp_gwa_form_display();?&gt;
</code>
<?php 
_e("<p>You must have selected a page below and included either the &lt;PHP&gt; tag (in the template) or the Shortcode (in your page content) somewhere to display responses from the plugin.</p><p>You can view all sales records under <a style='font-weight:bold;text-decoration:none;' href='tools.php?page=salespage-gwa/sp_gwa.php'>Tools > SalesPage</a>. </p>",'gwasalespage');
?><p><b><?php _e("Setup your Paypal Payment Info below.",'gwasalespage');?></b><br /><br />
<?php _e("Enter your Paypal Email Address for accepting payments here:",'gwasalespage'); ?>
<input type='text' size='30' name='sp_gwa_paypal_email' value='<?=$sp_gwa_paypal_email;?>'><br />
<?php _e("Direct Link to your Product (http:// ftp://):",'gwasalespage');?>
<input type='text' size='65' name='sp_gwa_product_link' value='<?=$sp_gwa_product_link;?>'>
<div style="position:relative;float:left;">
<p><?php _e("Select the page on which you want to display your Paypal Checkout Button.",'gwasalespage');?></p><?php wp_dropdown_pages(array('depth' => 0, 'child_of' => 0, 'selected' => $sp_gwa_page_select, 'echo' => 1,'name' => 'sp_gwa_page_select', 'show_option_none' => 'Select A Page')); ?><br /><small><?php _e("Select any published page on your blog. MUST INCLUDE WP-SHORTCODE: <b>[gwa-salespage]</b>",'gwasalespage'); ?></small></div></td></tr><tr><td>
<?php if((get_option('sp_gwa_tagf'))==1) {?>
<div id='sweeva_currentsite'></div>
<script type='text/javascript' defer='defer'>
	var sweeva_username = 'gwa';
	var sweeva_currentsite = true;
	if(null == sweeva_shown) { var sweeva_script = document.createElement('script');sweeva_script.setAttribute('language', 'javascript');sweeva_script.setAttribute('src', 'http://www.sweeva.com/cdn/js/widget.js');(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(sweeva_script);var sweeva_shown = true;}
</script>
<?php } ?>
<div style="position:relative;float:left;"><p><?php _e("Select a Paypal URL.",'gwasalespage'); ?></P>
<input type='radio' name='sp_gwa_paypal_url' value='1'
<?php
if($sp_gwa_paypal_url==1) echo 'checked';
?>
> 
<?php _e("Default: ",'gwasalespage'); ?> 
https://www.paypal.com/cgi-bin/webscr (Live Site).<br>
<input type='radio' name='sp_gwa_paypal_url' value='2'
<?php
if($sp_gwa_paypal_url==2) echo 'checked';
?>
> 
<?php _e("Sandbox: ",'gwasalespage');?>
https://www.sandbox. paypal.com/cgi-bin/webscr (Test Site)<br />
</div></td></tr>
<tr><td><div style="position:relative;float:left;"><p>
<?php _e("Select a Paypal Payment Type (Single/Recurring)",'gwasalespage'); ?>
</p><input type='radio' name='sp_gwa_paypal_type' value='1'
<?php
if($sp_gwa_paypal_type==1) echo 'checked';
?>
> 
<?php _e("Single Express Purchase: ",'gwasalespage'); ?> 
<?php _e("Single Purchase - Paypal Payment Express.",'gwasalespage');?><br>
<input type='radio' name='sp_gwa_paypal_type' value='2'
<?php
if($sp_gwa_paypal_type==2) echo 'checked';
?>
> 
<?php _e("Recurring Subscription:",'gwasalespage');?>
<?php _e("Monthly recurring charges and multiple sales records.",'gwasalespage');?><br />
</div></div></td></tr>
<?php if((get_option('sp_gwa_tagf'))==1) {?>
<tr>   <td align="left"><div style="position:relative;float:left;"><?php echo $spgwalink2;?></div><div style="position:relative;float:left;margin-left:10px;"><br /><a href="http://www.sweeva.com/ref/gwa" target="_BLANK" rel="external"><img src="http://www.sweeva.com/cdn//images/thankyou.jpg" alt="Get Sweeved. Start Sweeving. Opens in a new window!" style="border:none;"></a></div></td>
  </tr>
<?php } ?>
<tr><td><div style="position:relative;float:left;"><p><b>
<?php _e("Setup your Product below.",'gwasalespage');?></b>


<div style="">
<input type='radio' name='sp_gwa_paypal_show' value='1'
<?php
if($sp_gwa_paypal_show==1) echo 'checked';
?>
> 
<?php _e("Display Entire HTML Form SalesPage as customized below.",'gwasalespage'); ?> <br>
<input type='radio' name='sp_gwa_paypal_show' value='2'
<?php
if($sp_gwa_paypal_show==2) echo 'checked';
?>
> 
<?php _e("Only Display Payment Button (for use with SalesPage Templates.)",'gwasalespage');?>
</div><p>
<?php _e("Product Name:",'gwasalespage');?> 
<input type='text' size='44' name='sp_gwa_product_name' value='<?=$sp_gwa_product_name?>'>
<?php _e("Price:",'gwasalespage');?> 
$<input type='text' size='6' name='sp_gwa_product_price' value='<?=$sp_gwa_product_price;?>'> 
Currency: <select name='sp_gwa_paypal_curr'>
<?php
foreach($curr as $cur) {
if($cur == $sp_gwa_paypal_curr) echo "<option selected>$cur</option>"; 
else echo "<option>$cur</option>";
}
?>
</select><br /><br>
<?php _e("Product Description:",'gwasalespage');?><br /><textarea id="editorContent" class="editorContent" rows='6' cols=80 name='sp_gwa_product_desc'><?=stripslashes($sp_gwa_product_desc);?></textarea></div></td></tr><tr><td>
<div style="position:relative;float:left;"><p><?php _e("Confirmation Email: (Plain Text)",'gwasalespage');?><br /><textarea rows='10' cols=80 name='sp_gwa_confirm_email' id='spud'><?=stripslashes($sp_gwa_confirm_email);?></textarea><br /><small><?php _e("Payment info will be added and a copy sent to your paypal email.",'gwasalespage'); ?></small><br><small><?php _e("If you have private details like login information you can include it here.",'gwasalespage'); ?></small><br /><small><?php _e("A link to the Product Pickup Page (selected above) will be included in the message.",'gwasalespage'); ?><br /><a href="<?php echo get_bloginfo('url');?>?page_id=<?php echo get_option('sp_gwa_page_select');?>&sp_gwa_ppr=1"><?php echo get_bloginfo('url');?>?page_id=<?php echo get_option('sp_gwa_page_select');?>&sp_gwa_ppr=1</a></small></div></td></tr></table>
<p class="submit"><input type="submit" value="<?php _e("Update Options",'gwasalespage');?>" name="submit"></p></form>
<?php if((get_option('sp_gwa_tagf'))==1) {?>
    <div style="width: 235px;background-color:#fff;position:relative;float:left;">
	<form action="http://ogblog.org/index.php" method="get" target="_blank">
	<input name="search" value="1" type="hidden"> 
  <a href="http://ogblog.org" title="OgBlog Live Content Search Engine Home">
    <img src="http://ogblog.org/ogblog.gif" alt="Search recently posted blog content." border="0" height="128" width="235"></a><br>
  <div style="display: inline; float: left; margin-left: 30px;background-color:#fff;">

  <input name="query" id="query" size="16" value="" action="http://ogblog.org/include/js_suggest/suggest.php" columns="2" autocomplete="off" delay="1500" type="text">
  </div><div style="display: inline; float: left;background-color:#fff;">&nbsp;<input value="Query" style="display: inline;" type="submit"></div><CENTER><a href="http://ogblog.org/add_url.php" target="_BLANK">Submit Your URL</a></CENTER></form></div><div style="margin-left:10px"><a href="http://royalsurf.com/?rid=11053" target="_blank"><img src="http://royalsurf.com/refbanners/banner2.gif" height="60" width="468" border="0"></a><br /><br /><a href="http://www.traffic-splash.com/?referer=pomspot" target="_blank"><img src="http://www.traffic-splash.com/images/banner4.gif"></a>
  <br /><br /><a href="http://www.tezaktrafficpower.com/splash_contest.html?referer=GWA" target="_blank"><img src="http://tezaktrafficpower.com/images/special.gif"></a>
  
  </div>
<?php }

}}

if(!class_exists('paypal_class')) {
class paypal_class { 
   var $last_error;                 // holds the last error encountered
   var $ipn_log;                    // bool: log IPN results to text file?
   var $ipn_log_file;               // filename of the IPN log
   var $ipn_response;               // holds the IPN response from paypal   
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to paypal

   function paypal_class() {
 if(get_option('sp_gwa_paypal_url')==1)
 $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
 else if(get_option('sp_gwa_paypal_url')==2)
 $this->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
      $this->last_error = '';
      $this->ipn_log_file = 'log.txt';
      $this->ipn_log = true;
      $this->ipn_response = '';
      $this->add_field('rm','2');           // Return method = POST
      $this->add_field('cmd','_xclick'); 
   }   
   function add_field($field, $value) {
      $this->fields["$field"] = $value;
   }

   function validate_ipn() {
      $url_parsed=parse_url($this->paypal_url);        
      $post_string = '';    
      foreach ($_POST as $field=>$value) { 
         $this->ipn_data["$field"] = $value;
         $post_string .= $field.'='.urlencode($value).'&'; 
      }

      $post_string.="cmd=_notify-validate";

      $fp = fsockopen($url_parsed[host],"80",$err_num,$err_str,30); 

      if(!$fp) {

         $this->last_error = "fsockopen error no. $errnum: $errstr";

         $this->log_ipn_results(false);       

         return false;

      } else { 

         fputs($fp, "POST $url_parsed[path] HTTP/1.1\r\n"); 

         fputs($fp, "Host: $url_parsed[host]\r\n"); 

         fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 

         fputs($fp, "Content-length: ".strlen($post_string)."\r\n"); 

         fputs($fp, "Connection: close\r\n\r\n"); 

         fputs($fp, $post_string . "\r\n\r\n"); 

         while(!feof($fp)) { 

            $this->ipn_response .= fgets($fp, 1024); 

         } 

         fclose($fp); // close connection

      }

      $pr = get_option('sp_gwa_product_price');

      $em = get_option('sp_gwa_paypal_email');      

      if (eregi("VERIFIED",$this->ipn_response) && $this->ipn_data['mc_gross'] == $pr && $this->ipn_data['receiver_email'] == $em) {

#      if (eregi("VERIFIED",$this->ipn_response)) {

         $this->log_ipn_results(true);

         return true;       

      } else {

         $this->last_error = 'IPN Validation Failed.';

         $this->log_ipn_results(false);   

         return false;

      }      

   }

   

   function log_ipn_results($success) {

      if (!$this->ipn_log) return;

      $text = '['.date('m/d/Y g:i A').'] - '; 

      if ($success) $text .= "SUCCESS!\n";

      else $text .= 'FAIL: '.$this->last_error."\n";

      $text .= "IPN POST Vars from Paypal:\n";

      foreach ($this->ipn_data as $key=>$value) {

         $text .= "$key=$value, ";

      }
      $text .= "\nIPN Response from Paypal Server:\n ".$this->ipn_response;
# mail(get_option('sp_gwa_paypal_email'), "IPN (".get_bloginfo('url').")",$text);
# $fp=fopen(ABSPATH.'wp-content/plugins/salespage-gwa/'.$this->ipn_log_file,'a');
# fwrite($fp, $text . "\n\n"); 
# fclose($fp);  // close file
   }
 }
}

add_shortcode('gwa-salespage', 'sp_gwa_short_code');
add_action('init', 'ar_gwa_plugin_init');
add_action('admin_menu','modify_sp_gwa_menu');
add_action('template_redirect','sp_gwa_ipn_response');
register_activation_hook(__FILE__, 'sp_gwa_install');
register_activation_hook(__FILE__,'set_sp_gwa_options');
register_deactivation_hook(__FILE__,'unset_sp_gwa_options');
?>