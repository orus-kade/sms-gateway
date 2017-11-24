<?php
if( ! defined('WP_UNINSTALL_PLUGIN') ) exit;
// проверка пройдена успешно. Начиная от сюда удаляем опции и все остальное.
function sms_sender_uninstall(){
	global $wpdb;
    delete_option('sms_sender_api_key');
    delete_option('sms_sender_capabilities');
    delete_option('sms_sender_test');
	$table_msg = $wpdb->prefix.sms_sender_msg;
	$table_sent_msg = $wpdb->prefix.sms_sender_sent_msg;
	$sql = "DROP TABLE `$table_msg`, `$table_sent_msg`;";
	$wpdb->query($sql);
}
sms_sender_uninstall();