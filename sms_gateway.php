<?php
/*
Plugin Name: SMS Gateway
Plugin URI:
Description:Вы хотите отправлять SMS через свой телефон на Woocommerce? Для использования необходимо зарегистрироваться, установить приложение SMS Шлюз http://htmlweb.ru/user/sms_gate.php на свой телефон и залогиниться в нем. Для начала работы с SMS Gateway активируйте плагин, затем перейдите на страницу настроек, чтобы добавить Ваш ключ API.
Version: 1.0
Author: oruskade
Author URI:
License: GPLv2
*/
/*  Copyright 2017  Карпус (email: orus-kade@yandex.ru)

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
*/
define('SMS_SENDER_DIR', dirname(__FILE__));
$reg_exp = array(
    '/{order_id}/',
    '/{order_status}/',
    '/{date}/',
    '/{total}/',
    '/{customer}/'
   // ''
);
add_action('plugins_loaded', 'sms_sender_load_lang');
function sms_sender_load_lang(){
    $res = load_plugin_textdomain('sms_gateway', false, 'sms-gateway/languages/');
}
//действия при активции
register_activation_hook( __FILE__, 'sms_sender_install');
function sms_sender_install(){
    //api key можно узнать в профиле пользователя на http://htmlweb.ru/user
    add_option('sms_sender_api_key', 'Не задано');
    add_option('sms_sender_capabilities', 'administrator');
    add_option('sms_sender_test', '1');
    global $wpdb;
    $table_msg = $wpdb->prefix.sms_sender_msg;
    $sql =
        "CREATE TABLE IF NOT EXISTS `$table_msg` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `name` varchar(64) NOT NULL,
		  `text` text,
		  `active` boolean NOT NULL DEFAULT FALSE,
		  `action` varchar(64) NOT NULL,
		  `users_checked` TEXT NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	";
    $wpdb->query($sql);
    $result = $wpdb->get_results("SELECT count(*) c from $table_msg");
    if ($result[0]->c == 0) {
        $sql =
            "INSERT INTO $table_msg VALUES (NULL, 'Новый заказ', NULL, FALSE, 'new_order', '0,'),
										(NULL, 'В ожидании оплаты', NULL, FALSE, 'pending', '0,'),
										(NULL, 'Не удался', NULL, FALSE, 'failed', '0,'),
										(NULL, 'На удержании', NULL, FALSE, 'on-hold', '0,'),
										(NULL, 'Обработка', NULL, FALSE, 'processing', '0,'),
										(NULL, 'Выполнен', NULL, FALSE, 'completed', '0,'),
										(NULL, 'Возвращен', NULL, FALSE, 'refunded', '0,'),
										(NULL, 'Отменен', NULL, FALSE, 'cancelled', '0,');
		";
        $wpdb->query($sql);
    }
        $table_msg = $wpdb->prefix.sms_sender_sent_msg;
        $sql =
            "CREATE TABLE IF NOT EXISTS `$table_msg` ( 
			`id` INT NOT NULL AUTO_INCREMENT,  
			`sms_id` INT NULL,
		 	`time`  DATETIME NOT NULL ,
		 	`phone` VARCHAR(20) NOT NULL ,
		 	`status` TINYINT NOT NULL ,
		 	`user_id` INT NULL ,
		 	`order_id` INT NULL ,
		 	`cost` INT NULL ,
		 	`text` TEXT NOT NULL ,
		 	`error_text` TEXT NULL ,
		 	PRIMARY KEY (`id`)) ENGINE = InnoDB;
		";
        $wpdb->query($sql);
}
//добавление actions для автоматической рассылки
function sms_sender_add_actions(){
    if (sms_sender_check_api_key()){
        global $wpdb;
        $table_msg = $wpdb->prefix.sms_sender_msg;
        $results = $wpdb->get_results("SELECT action, active FROM $table_msg");
        foreach ($results as $result) {
            if ($result->active){
                if ($result->action == 'new_order'){
                    add_action('woocommerce_new_order', 'sms_sender_new_order');
                }
                else {
                    $s = 'woocommerce_order_status_'.$result->action;
                    add_action($s, 'sms_sender_status_changed');
                }
            }
        }
    }
}
sms_sender_add_actions();
//задал ли api key
function sms_sender_check_api_key(){
    if (get_option('sms_sender_api_key') == 'Не задано' || empty(get_option('sms_sender_api_key'))) {
        return false;
    }
    else{
        return true;
    }

}
//для action new_order
function sms_sender_new_order($id){
    sms_sender_status_changed($id, true);
}
//для action изменения статусов
function sms_sender_status_changed($id, $is_new = false){
    global $wpdb;
    global $reg_exp;
    $table_msg = $wpdb->prefix.sms_sender_msg;
    $order = new WC_Order($id);
    if ($is_new){
        $sql = "SELECT * FROM $table_msg WHERE action = 'new_order'";
    }
    else {
        $status = $order->get_status();
        $sql = "SELECT * FROM $table_msg WHERE action = '$status'";
    }
    $result = $wpdb->get_results($sql);
    $texts = json_decode($result[0]->text, true);
    $users_checked = explode(',', $result[0]->users_checked);
    foreach ($users_checked as $user){
        if (is_numeric($user)){
            if ($user == 0){
                $to = preg_replace('/[^\d]/', '',$order->get_billing_phone());
                $text = $texts['client'];
                $user_id[0]=$order->get_user_id();
            }
            else{
                $to = preg_replace('/[^\d]/', '',(new WC_Customer($user))->get_billing_phone());
                $role = (new WC_Customer($user))->get_role();
                $text = $texts[$role];
                if (empty($text)) $text = $texts['client'];
                $user_id[0]=$user;
            }
            $name = $result[0]->name;
            $total = $order->get_total();
            $date = current_time('H:i:s d.m.Y');
            $customer = $order->get_billing_first_name().' '.$order->get_billing_last_name();
            $text = preg_replace($reg_exp, array($id, $name, $date, $total, $customer) , $text);
            if (strlen($to)<11){
                $err_phones[0] = $to;
                $err_user_id[0] = $user;
                sms_sender_add_errors($err_phones, $err_user_id, $text, $id);
            }
            else{
                $result = sms_sender_send_msg($to, $text);
                sms_sender_add_sent_msg($result, $user_id, $to, $id);
            }
            //return;
        }
    }
}
//добавление сслыки на настройки в меню плагинов
function plugin_settings_link($links) {
    $settings_link = '<a href="admin.php?page=sms_sender_options">Настройки</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
$plugin_file = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin_file", 'plugin_settings_link' );
//добавление страницы настройки в меню админа
add_action('admin_menu','sms_sender_add_option_page');
function sms_sender_add_option_page(){
    global $current_user;
    $options = get_option('sms_sender_capabilities');
    $options = explode(',', $options);
    if (!in_array($current_user->roles[0], $options)){
        wp_die(
            '<h1>' . __( 'Нет доступа' ) . '</h1>' .
            '<p>' . __( 'Извините, вам не разрешено просматривать эту страницу.' ) . '</p>',
            403
        );
    }
        add_menu_page('SMS Gateway Настройки', 'SMS Gateway', 'level_0', 'sms_sender_options', 'sms_sender_option_page', '
dashicons-email-alt');
}
//подключение стилей и js
add_action( 'admin_enqueue_scripts', 'sms_sender_action_javascript' );
function sms_sender_action_javascript() {
    wp_enqueue_style('sms_sender_style', WP_PLUGIN_URL.'/sms-gateway/includes/css/style.css');
    wp_enqueue_script('sms_sender_script_ajax',  WP_PLUGIN_URL.'/sms-gateway/includes/js/ajax.js', array('jquery'), null, true);
    wp_enqueue_script('sms_sender_script_forms',  WP_PLUGIN_URL.'/sms-gateway/includes/js/form.js', array('jquery'), null, true);
    //echo $_SERVER['REQUEST_URI']; exit;
    if ($_GET['page']=='sms_sender_options'){
        wp_enqueue_script('sms_sender_script_sort',  WP_PLUGIN_URL.'/sms-gateway/includes/js/sort.js', array('jquery'), null, true);
    }
}
//отправленные сообщения
function sms_sender_sent_messages(){
    global $wpdb;
    $table_msg = $wpdb->prefix.sms_sender_sent_msg;
    if (isset($_POST['sms_sender_sent_submit'])){
        $par = implode(', ', $_POST['checked']);
        $sql = "DELETE FROM $table_msg WHERE `id` IN ($par)";
        $wpdb->query($sql);
        $deleted = true;
    }
	sms_sender_get_statuses();

	if ($_GET[par] == 'sending'){
	    $results = $wpdb->get_results("SELECT * FROM $table_msg WHERE `order_id` = 0");
    }
    elseif ($_GET[par] == 'notifications'){
        $results = $wpdb->get_results("SELECT * FROM $table_msg WHERE `order_id` != 0");
    }
    else{
        $results = $wpdb->get_results("SELECT * FROM $table_msg");
    }
    $count_all = count($wpdb->get_results("SELECT * FROM $table_msg"));
    $count_notifications = count($wpdb->get_results("SELECT * FROM $table_msg WHERE `order_id` != 0"));
    $count_sending = count($wpdb->get_results("SELECT * FROM $table_msg WHERE `order_id` = 0"));
    include(SMS_SENDER_DIR.'/templates/options_sent_msgs.php');
}
//код страницы настройки основной
function sms_sender_option_page(){
    global $current_user;
    $options = get_option('sms_sender_capabilities');
    $options = explode(',', $options);
    if (!in_array($current_user->roles[0], $options)){
        wp_die(
            '<h1>' . __( 'Нет доступа' ) . '</h1>' .
            '<p>' . __( 'Извините, вам не разрешено просматривать эту страницу.' ) . '</p>',
            403
        );
    }
    if (isset($_GET['notifications'])){
        sms_sender_notifications();
    }
    elseif (isset($_GET['sending'])){
        sms_sender_form_send();
    }
    elseif (isset($_GET['sent'])){
        sms_sender_sent_messages();
    }
    else{
        sms_sender_general_options();
    }
}
//сохранение основных настроек
function sms_sender_general_options(){
    if (isset($_POST['sms_sender_save_options'])){
        $my_api_key = $_POST['sms_sender_api_key'];
        $test = isset($_POST['sms_sender_test']);
        update_option('sms_sender_api_key', $my_api_key);
        update_option('sms_sender_test', $test);
       // echo $test; exit;
        $saved = true;
    }
    if (isset($_POST['sms_sender_roles_save'])){
        $options = $_POST['checked_roles'];
        $options[] = 'administrator';
        $options = implode(',', $options);
        update_option('sms_sender_capabilities', $options);
        $saved = true;
    }

    $my_api_key = get_option('sms_sender_api_key');
    $roles = wp_roles();
    $current_roles = get_option('sms_sender_capabilities');
    $current_roles = explode(',', $current_roles);
    include (SMS_SENDER_DIR.'/templates/options_general.php');
}
//форма для рассылки
function sms_sender_form_send(){
    global $current_user;
	if (isset($_POST['sms_sender_send_sms'])){
	    if (sms_sender_check_api_key()){
            $users = get_users('role='.$_POST['selected_role']);
            if (!empty($users)){
                $phones = array();
                $user_id = array();
                $text = $_POST['sms_text'];
                foreach ($users  as $user) {
                    $cust = new WC_Customer($user->id);
                    $temp_phone = preg_replace('/[^\d]/', '', $cust->get_billing_phone());
                    if ((strlen($temp_phone)) < 11) {
                        $err_phones[] = $temp_phone;
                        $err_user_id[] = $user->id;
                    }
                    else{
                        $phones[]=$cust->get_billing_phone();
                        $user_id[]=$user->id;
                    }
                }
                $count = count($phones);
                $err_count = count($err_phones);
                if ($count >0){
                    $to = implode(', ', $phones);
                    $result = sms_sender_send_msg($to, $text);
                    sms_sender_add_sent_msg($result, $user_id, $to);
                }
                if ($err_count >0){
                    sms_sender_add_errors($err_phones, $err_user_id, $text);
                }
                $sent = true;
            }
            else{
                $error = true;
            }
        }
	}
        $roles = wp_roles();
        foreach ($roles->roles as $role => $value) {
            $users_count[$role] = count(get_users('role='.$role));
	    }
	    include(SMS_SENDER_DIR.'/templates/options_send_sms.php');
}
//отравка сообщения
function sms_sender_send_msg($to, $text){
	$param=array(
        	'api_key'=> get_option('sms_sender_api_key'), // ключ из профиля http://htmlweb.ru/user/
        	'to' => $to,
        	'text' => $text,
	        'charset' => 'utf-8'
	    );
	if (get_option('sms_sender_test')){
	    $param['test'] = 1;
	}
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 600);
	    curl_setopt($ch, CURLOPT_URL, 'http://htmlweb.ru/sendsms/api.php?send_sms&json');
	    $res = curl_exec($ch);
	    $result = json_decode($res, !0);
	  	curl_close($ch);
	  	return $result;	  	  
}
//настройки рассылки смс по событиям
function sms_sender_notifications(){
    global $wpdb;
    $table_msg = $wpdb->prefix.sms_sender_msg;
    $messages = $wpdb->get_results("SELECT * FROM $table_msg");
    $r = wp_roles();
    foreach ($r->roles as $role => $value){
        $roles[] = $role;
    }
    foreach ($roles as $role) {
        $users[$role]['names'] = get_users('role='.$role);
        $users[$role]['count'] = count($users[$role]['names']);
    }
    include (SMS_SENDER_DIR.'/templates/options_notifications.php');
}
//сохранение настроек рассылки
add_action( 'wp_ajax_sms_sender_edit_msg', 'sms_sender_edit_msg' );
//изменение настроек оповещений для изменения статуса заказа
function sms_sender_edit_msg() {
	global $wpdb;
	$checked = $_POST['checked'];
	$id = intval($_POST['id']);
	$text = json_encode($_POST['text'],  JSON_UNESCAPED_UNICODE);
	$active = $_POST['active'];  
	$table_msg = $wpdb->prefix.sms_sender_msg;	
	$sql = "UPDATE $table_msg SET  `text` = '$text', `active` = '$active', `users_checked` = '$checked'  WHERE `id` = $id";
    $wpdb->query($sql);
	$s = 'woocommerce_order_status_'.$_POST['action_name'];
	if ($_POST['active']){
        if ($_POST['action_name'] == 'new_order'){
            add_action('woocommerce_new_order', 'sms_sender_new_order');
        }
        else {
            add_action($s, 'sms_sender_status_changed');
        }
	}
	else{
        if ($_POST['action_name'] == 'new_order'){
            remove_action('woocommerce_new_order', 'sms_sender_new_order');
        }
        else {
            remove_action($s, 'sms_sender_status_changed');
        }
	}
	wp_die(); 
}
//добавить сообщение в бд
function sms_sender_add_sent_msg($result, $user_id, $to, $order_id = 0){
	global $wpdb;
	$table_msg = $wpdb->prefix.sms_sender_sent_msg;
    $sql = "INSERT INTO $table_msg VALUES";
	if (!isset($result['sms'])){
		$err = "'".$result['error']."'";
		$sms_id = 'NULL';
		$status = -1;
		$cost = 'NULL';
		$message = "'".$result['message']."'";
		$sql .= "
            (NULL,
            $sms_id, 
            '".current_time('mysql')."', 
            '$to',
            $status, 
            $user_id[0], 
            $order_id,
            $cost,
            $message,
            $err
        )";
	}
	else {
        $i = 0;
        $to = explode(',', $to);
        foreach ($result['sms'] as $msg) {
            $err = 'NULL';
            $sms_id = "'" . $msg['id'] . "'";
            $status = 0;
            $cost = "'" . $msg['cost'] . "'";
            $message = "'" . $msg['message'] . "'";
            $user = $user_id[$i];
            $phone = $to[$i++];
            $query[] = "
                (NULL,
                $sms_id, 
                '" . current_time('mysql') . "', 
                '$phone',
                $status, 
                $user, 
                $order_id,
                $cost,
                $message,
                $err
            )";
        }
        $query = implode(',', $query);
        //echo $query.'     ----    ';
        $sql .= $query;
    }
    //echo $sql;
	$result = $wpdb->query($sql);
	//exit;
}
//добавить ошибки о рассылке в бд (беда с телефонами если)
function sms_sender_add_errors($phones, $users_id, $text, $order_id = 0){
    global $wpdb;
    $table_msg = $wpdb->prefix.sms_sender_sent_msg;
    $i = 0;
    $sql = "INSERT INTO $table_msg VALUES";
    foreach ($phones as $phone) {
        $query[] = "
        (NULL, 
        NULL, 
        '" . current_time('mysql') . "',
        '$phone',
        -1,
        ".$users_id[$i++].",
        $order_id,
        NULL,
        '$text',
        '$phone - короткий номер телефона получателя!'
        )";
    }
    $query = implode(',', $query);
    $sql .= $query;
    $result = $wpdb->query($sql);
}
//обновить статусы сообщений  бд
function sms_sender_get_statuses(){
    if (!sms_sender_check_api_key()) return 0;
	global $wpdb;
	$table_msg = $wpdb->prefix.sms_sender_sent_msg;
	$sql = "SELECT id, status, sms_id FROM $table_msg WHERE `status` IN (0, 4, 5, 6)
	";
	$result = $wpdb->get_results($sql);
	if (!empty($result)){
        $my_api_key = get_option('sms_sender_api_key');
        $wpdb->query('BEGIN TRANSACTION');
        foreach ($result as $msg) {
            $sms_id = $msg->sms_id;
            $url = 'http://htmlweb.ru/sendsms/api.php?sms_id='.$sms_id.'&api_key='.$my_api_key.'&json&charset=utf-8';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_URL, $url);
            $res = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($res, true);
            $new_status = $result['status'];
            $res = $wpdb->update($table_msg,
                array('status' => $new_status),
                array('id' => $msg->id),
                array('%d'),
                array('%d')
            );
        }
        $wpdb->query('COMMIT');
    }
}
//текстовый статус сообщения
function sms_sender_get_text_status($id){
    switch ($id) {
        case -1:
            return 'Ошибка';
            break;
        case 0:
            return 'Ожидает отправки';
            break;
        case 1:
            return 'Отправлено';
            break;
        case 2:
            return 'Доставлено';
            break;
        case 3:
            return 'Не доставлено';
            break;
        case 4:
            return 'Отправлено повторно';
            break;
        case 5:
            return 'Отправлено шлюзом на телефон';
            break;
        case 6:
            return 'Отправлено на шлюз';
            break;
    }
}
