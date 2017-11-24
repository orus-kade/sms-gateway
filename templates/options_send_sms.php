<div class = "wrap">
    <h2>Настройки SMS Gateway</h2>
    <div class="updated notice is-dismissible"><p>Вам понравился плагин? Не забудьте оставить свой <a href="https://wordpress.org/support/plugin/sms-gateway/reviews/#new-post" target = '_blank'>отзыв.</a></p><button type="button" class="notice-dismiss"></button></div>
    <?php
    if (!sms_sender_check_api_key()) {
        echo '<div class="update-nag notice sms_sender_notice_width"><p>Невозможно отправлять сообщения, если не задан API ключ!</p></div>
    ';
    }
    ?>
    <?php
    if ($sent == true){
        echo '<div class="updated notice is-dismissible"><p>Сообщений отправлено: '.$count;
        if ($err_count > 0) {
            echo ' Не отправлено: '.$err_count;
        }
        echo '  <a href = "admin.php?page=sms_sender_options&sent&par=sending">Просмотр...</a>';
        echo '</p><button type="button" class="notice-dismiss"></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Скрыть это уведомление.</span></button></div>';
    }
    if ($error == true){
        echo '<div class="error notice is-dismissible"><p>В данной групппе нет получателей!</p><button type="button" class="notice-dismiss"></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Скрыть это уведомление.</span></button></div>';
    }
    ?>
    <nav class = 'nav-tab-wrapper'>
        <a class = 'nav-tab' href = "admin.php?page=sms_sender_options">Общие</a>
        <a class = 'nav-tab' href="admin.php?page=sms_sender_options&notifications">Оповещения</a>
        <a class = 'nav-tab nav-tab-active' href="admin.php?page=sms_sender_options&sending">Рассылка</a>
        <a class = 'nav-tab' href="admin.php?page=sms_sender_options&sent">Отправленные</a>
    </nav>
    <h3>Сделать рассылку</h3>

    <form name = 'send_form' class = 'sms_sender_form' method = 'post' action = ''>
        <p><label>Текст сообщения (обязательно)</label>
            <textarea name = 'sms_text' class ='sms_sender_form_text' wrap = 'soft'><?=$text?></textarea>
        <p class = 'sms_sender_right'>Количество символов: <span name="sms_sender_snum">0</span> частей: <span name="sms_sender_pnum">0</span>
        <p>Выбрать получателей: <select class = 'sms_sender_selector' name = 'selected_role'>
<?php
foreach ($roles->roles as $role => $value) {
         echo "<option value = '$role'";
         if ($_POST['selected_role'] == $role) echo 'selected';

         echo '>';
            _ex($value[name], 'User role', 'sms_gateway');
        echo ' (Количество пользователей: '.$users_count[$role].')';
         echo '</option>';
}
?>
            </select>
        <p><input type = 'submit' name = 'sms_sender_send_sms' class='button-primary woocommerce-save-button' value = 'Отправить сообщение'
<?php
if(!sms_sender_check_api_key()) echo ' disabled';
?>
            >
         <p class = 'sms_sender_hidden sms_sender_text_error'>Введите текст сообщения
    </form>
</div>




<!--
длина одного сообщения 160 пробовала на цифры и латиница
длина с кириллицей - 70
-->