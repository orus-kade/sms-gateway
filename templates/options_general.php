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
    if ($saved) {
        echo '<div class="updated notice is-dismissible"><p>Сохранено.</p><button type="button" class="notice-dismiss"></button></div>
		';
    }
    ?>
    <nav class = 'nav-tab-wrapper '>
        <a class = 'nav-tab nav-tab-active' href = "admin.php?page=sms_sender_options">Общие</a>
        <a class = 'nav-tab' href="admin.php?page=sms_sender_options&notifications">Оповещения</a>
        <a class = 'nav-tab' href="admin.php?page=sms_sender_options&sending">Рассылка</a>
        <a class = 'nav-tab' href="admin.php?page=sms_sender_options&sent">Отправленные</a>
    </nav>
    <h3>Общие настройки</h3>

    <form name = 'sms_sener_options_form' method = 'post' action = ''>
        <table>
            <tr>
                <td>
                    API ключ
                </td>
                <td>
                    <input type='text' name = 'sms_sender_api_key' class = 'sms_sender_option_form_input' size = '40' value = '<?=$my_api_key?>'>
                </td>
                <td>
                    Идентификатор можно узнать в <a href="http://htmlweb.ru/user" target = '_blank'> профиле пользователя</a>  <!-- на <a href="http://htmlweb.ru/" target = '_blank'>http://htmlweb.ru</a> -->
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type = 'checkbox' name = 'sms_sender_test'
                        <?php
                            if (get_option('sms_sender_test')){
                                echo 'checked';
                            }
                        ?>
                    > Тестовый режим
                </td>
            </tr>
            <tr>
                <td>
                    <input type = 'submit' name = 'sms_sender_save_options' class='button-primary woocommerce-save-button' value = 'Сохранить'>
                </td>
            </tr>

        </table>
    </form>

    <form action="" method="post">
        <?php
            global $current_user;
            if (in_array('administrator', $current_user->roles)){
        ?>
        <h3>Права пользователей</h3>
        <p>Изменение доступно только администратору
        <fieldset class="sms_sender_fieldset">
            <legend>Кому доступна страница настроек</legend>
            <?php
            foreach ($roles->roles as $role => $value) {
                echo "<p><input type='checkbox' value = '$role' name = 'checked_roles[]'";
                if (in_array($role, $current_roles)){
                    echo ' checked';
                    if ($role == 'administrator'){
                        echo ' disabled';
                    }
                }
                echo '>';
                _ex($value['name'], 'User role', 'sms_gateway');
            }
            ?>
        </fieldset>
        <p><input type="submit" name = 'sms_sender_roles_save' value="Сохранить" class='button-primary woocommerce-save-button'>
    </form>
    <?php }
    ?>

</div>