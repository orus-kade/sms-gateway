//не отправлять рассылку с пустым текстом
jQuery('[name=sms_sender_send_sms]').click(function(){
    if (jQuery('.sms_sender_form_text').val().length == 0){
        jQuery('.sms_sender_text_error').removeClass('sms_sender_hidden').addClass('sms_sender_visible');
        return false;
    }
    else return true;
})
//отметить все в отправленных
jQuery('.sms_sender_check_all').click(function(){
    if (jQuery(this).attr('checked'))
        jQuery('.sms_sender_is_checkable').attr('checked', 'checked');
    else
        jQuery('.sms_sender_is_checkable').removeAttr('checked', 'checked');
})
//подсчет количества символов
jQuery('.sms_sender_form_text').on('input', function(){
    var len_kiril = 70;
    var len_lat = 160;
    var str = jQuery(this).val();
    var len  = str.length;
    if (str.match(/[а-яА-Я]/)){
        part_len = len_kiril;
    }
    else {
        part_len = len_lat;
    }
    var parts = Math.ceil(len/part_len);
    var rest = parts*part_len-len;
    if (parts > 1){
        if (rest >= part_len/2) {
            rest = (parts - 1) * part_len - len;
        }
    }
    len += '('+rest+')';
    jQuery('span[name=sms_sender_snum]').html(len);
    jQuery('span[name=sms_sender_pnum]').html(parts)
})
//чтобы выбрать получателей в конкретной группе
jQuery('.sms_sender_not').change(function(){
    if (jQuery(this).attr('checked')) {
        jQuery(this).next().next().removeAttr('disabled','disabled');
        jQuery(this).next().next().children().each(function(){
            jQuery(this).attr('selected', 'selected');
        })
    }
    else{
        jQuery(this).next().next().attr('disabled','disabled');
        jQuery(this).next().next().children().each(function(){
            jQuery(this).removeAttr('selected');
        })
    }
})

//чтобы не активировать оповещение без текста
jQuery('.sms_sender_msg_text').on('input',function(){
    if (jQuery(this).val() == 0){
        jQuery(this).parent().prev().children().attr('disabled', 'disabled');
        jQuery(this).parent().prev().children().removeAttr('checked');
    }
    else{
        jQuery(this).parent().prev().children().removeAttr('disabled');
    }
})

