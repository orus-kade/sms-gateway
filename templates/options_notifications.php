<div class = "wrap">
    <h2>Настройки SMS Gateway</h2>
    <div class="updated notice is-dismissible"><p>Вам понравился плагин? Не забудьте оставить свой <a href="https://wordpress.org/support/plugin/sms-gateway/reviews/#new-post" target = '_blank'>отзыв.</a></p><button type="button" class="notice-dismiss"></button></div>
    <?php
    if (!sms_sender_check_api_key()) {
        echo '<div class="update-nag notice sms_sender_notice_width"><p>Невозможно активировать оповещения, если не задан API ключ!</p></div>
		';
    }
    ?>

    <nav class = 'nav-tab-wrapper'>
        <a class = 'nav-tab' href = "admin.php?page=sms_sender_options">Общие</a>
        <a class = 'nav-tab  nav-tab-active' href="admin.php?page=sms_sender_options&notifications">Оповещения</a>
        <a class = 'nav-tab' href="admin.php?page=sms_sender_options&sending">Рассылка</a>
        <a class = 'nav-tab' href="admin.php?page=sms_sender_options&sent">Отправленные</a>
    </nav>
    <h3>Настройки оповещений</h3>
    <p>{order_id} - номер заказа
    <p>{order_status} - статус заказа
    <p>{date} - дата и время отправки сообщения
    <p>{total} - общая стоимость заказа
    <p>{customer} - имя заказчика
        <?php
        foreach ($messages as $msg){
        ?>
    <form class = 'sms_sender_not_form'>
        <fieldset class="sms_sender_fieldset_not">
            <legend><h3>Статус: <?=$msg->name?></h3></legend>
            <input type = 'hidden' value = <?=$msg->action?> name = 'action'>
            <input type = 'hidden' value = <?=$msg->id?> name = 'id'>
            <p><input type="checkbox" name = 'active'
            <?php
            if ($msg->active) echo 'checked';
            if (!sms_sender_check_api_key()) echo ' disabled';
            ?>
            > Активно
            <p><h4>Кому отправлять</h4>
            <table class="sms_sender_table wp-list-table widefat striped">
                <tr>
                    <td class = 'sms_sender_not_role'><input type ="checkbox" name = 'checked_client' value = 'checked_client'
                        <?php
                        $texts = json_decode($msg->text, true);
                        $users_checked = explode(',', $msg->users_checked);
                        if (in_array(0, $users_checked)) echo ' checked '
                        ?>
                        >Заказчик</td>
                    <td>Текст сообщения (используется по умочанию)<br><textarea wrap = 'hard' class = "sms_sender_msg_text" name = 'client_text'><?=$texts['client']?></textarea></td>
                </tr>
                    <?php

                    foreach ($users as $role => $value) {
                        if ($value['count'] != 0) {
                            echo '<tr><td><input type ="checkbox" class = "sms_sender_not" value="' . $role . '"';
                            if (in_array($role, $users_checked)) echo ' checked ';
                            echo 'name = "checked">';
                            _ex($r->roles[$role]['name'], 'User role', 'sms_gateway');
                            echo '<br><select multiple name = "sms_sender_select" class = "sms_sender_selector">';
                            foreach ($value['names'] as $name) {
                                echo '<option value ="' . $name->get('id') . '"';
                                if (in_array($name->get('id'), $users_checked)) echo ' selected';
                                echo '>';
                                echo $name->get('display_name');
                                echo '</option>';
                            }
                            echo '</select></td>';
                            echo '<td>Текст сообщения<br><textarea class = "sms_sender_msg_text" name = "checked_text">'.$texts[$role].'</textarea></td></tr>';
                        }
                    }
                    ?>
            </table>
            <p><input name="sms_sender_edit_btn" class="button-primary woocommerce-save-button" value="Сохранить" type="submit">
        </fieldset>
    </form>
    <?php } ?>
</div>