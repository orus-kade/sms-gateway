//сохранение параметров оповещений
jQuery('[name=sms_sender_edit_btn]').click(function(event) {
    var form = event.target.parentNode.parentNode.parentNode;
    var id = form['id'].value;
    var action = form['action'].value;
    var active = form['active'].checked ? 1 : 0;
    var client_checked = form['checked_client'].checked;
    var checked = '';
    if (client_checked) {
        checked += '0,';
    }
    var client_text = form['client_text'].value;
    var texts = new Object();
    texts['client'] = client_text;
    for (var i = 0; i < form['checked_text'].length; i++) {
        index = form['checked'][i].value;
        texts[index] = form['checked_text'][i].value;
        if (form['checked'][i].checked) {
            checked += form['checked'][i].value + ',';
            select = form['checked'][i].nextSibling.nextSibling.nextSibling;
            for (var j = 0; j < select.length; j++) {
                if (select[j].selected) {
                    checked += select[j].value + ',';
                }
            }
        }
    }
    var data = {
        'action': 'sms_sender_edit_msg',
        'id': id,
        'text': texts,
        'checked': checked,
        'active': active,
        'action_name': action
    }
    jQuery.post(ajaxurl, data, function (response) {
        var fieldset = event.target.parentNode.parentNode;
        if (!(fieldset.lastChild.previousSibling.tagName == 'DIV')){
            var div = document.createElement("div");
            div.setAttribute("class", "updated notice is-dismissible");
            var p = document.createElement("p");
            var btn = document.createElement("button");
            btn.setAttribute("type", "button");
            btn.setAttribute("class", "notice-dismiss");
            var txt = document.createTextNode('Сохранено');
            p.appendChild(txt);
            div.appendChild(p);
            div.appendChild(btn);
            fieldset.insertBefore(div, fieldset.lastChild)
            jQuery('.notice-dismiss').click(function (event) {
                div.parentNode.removeChild(div);
            })
        }
    })
    return false;
})
