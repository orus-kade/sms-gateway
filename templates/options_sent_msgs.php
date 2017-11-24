<div class = "wrap">
    <h2>Настройки SMS Gateway</h2>
    <div class="updated notice is-dismissible"><p>Вам понравился плагин? Не забудьте оставить свой <a href="https://wordpress.org/support/plugin/sms-gateway/reviews/#new-post" target = '_blank'>отзыв.</a></p><button type="button" class="notice-dismiss"></button></div>
    <?php
    if (!sms_sender_check_api_key()) {
        echo '<div class="update-nag notice sms_sender_notice_width"><p>Невозможно обновить статус сообщений, если не задан API ключ!</p></div>
		';
    }
    ?>
    <?php
    if ($deleted == true){
        echo '<div class="updated notice is-dismissible"><p>Записи удалены</p><button type="button" class="notice-dismiss"></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Скрыть это уведомление.</span></button></div>';
    }
    ?>
    <nav class = 'nav-tab-wrapper'>
        <a class = 'nav-tab' href = "admin.php?page=sms_sender_options">Общие</a>
        <a class = 'nav-tab' href="admin.php?page=sms_sender_options&notifications">Оповещения</a>
        <a class = 'nav-tab' href="admin.php?page=sms_sender_options&sending">Рассылка</a>
        <a class = 'nav-tab nav-tab-active' href="admin.php?page=sms_sender_options&sent">Отправленные</a>
    </nav>
    <h3>Отправленные сообщения</h3>
    <ul class="subsubsub sms_sender_ul">
        <li class="all"><a href="admin.php?page=sms_sender_options&sent"
                           <?php if (!isset($_GET[par])) echo 'class="current"'?>
                           >Все <span class="count">(<?=$count_all?>)</span></a> |</li>
        <li class="moderated"><a href="admin.php?page=sms_sender_options&sent&par=notifications"
                <?php if ($_GET[par]=='notifications') echo 'class="current"'?>
            >Оповещения <span class="count">(<?=$count_notifications?>)</span></a> |</li>
        <li class="approved"><a href="admin.php?page=sms_sender_options&sent&par=sending"
                <?php if ($_GET[par]=='sending') echo 'class="current"'?>
            >Рассылки <span class="count">(<?=$count_sending?>)</span></a></li>
    </ul>
    <form method="post" action="">
    <table class="sms_sender_table wp-list-table widefat striped sort">
		<thead>
            <tr>
                <th><input type="checkbox" class = 'sms_sender_check_all'></th>
                <td>Дата и время
                    <?php
                    if (!($_GET[par]=='sending')){
                        echo '<td>№ заказа';
                    }
                    ?>
                <td>Статус смс
                <td>Пользователь
                <td>Телефон
                <td>Ошибка
                <td>Текст сообщения
                <td>Цена
            </tr>
		</thead>
		<tbody>
            <tr>
<?php
foreach ($results as $msg) {
    echo '<th><input type="checkbox" class = "sms_sender_is_checkable" value = '.$msg->id.' name = "checked[]"</th>';
    echo '<td>'.date('H:i:s d.m.Y',strtotime($msg->time));
    if  (!($_GET[par]=='sending')){
        if ($msg->order_id == 0){
            echo '<td>--';
        }
        else{
?>
    <td><a href="post.php?post=<?=$msg->order_id?>&action=edit"><?=$msg->order_id?></a>
<?php } }

if ($msg->user_id == 0){
        $user_id = '-';
        $username = 'Гость';
    }
    else{
        $user_id = $msg->user_id;
        $user = new WC_Customer($user_id);
        $username = $user->get_username();
        $username = '<a href="user-edit.php?user_id='.$user_id.'&wp_http_referer=%2Fwp-admin%2Fusers.php">'.$username.'</a>';
    }
?>

        <td><?php echo sms_sender_get_text_status($msg->status);?>
        <td><?=$username?>
        <td><?=$msg->phone?>
        <td><?=$msg->error_text?>
        <td><?=$msg->text?>
        <td><?=$msg->cost?>
    </tr>
<?php
}
?>
        </tbody>
    </table>
        <p><input type="submit" name="sms_sender_sent_submit" class = 'button-primary woocommerce-save-button' value = 'Удалить выбранные записи'>
    </form>
</div>
