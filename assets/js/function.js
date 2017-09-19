var auth;
var id;
var parent_id;
var IntervalID;

$(document).ready(function() {

    FuncCounter();

    $( ".tooltip" ).tooltip();

    /**
     * модалка для авторизации
     */
    $( "#dialog" ).dialog({
        autoOpen: false,
        width: 250,
        draggable: false,
        resizable: false,
        modal: true,
        position:   {
            my:      "top+25",
            at:      "top",
            of:      window
        },
        buttons: [
            {
                text: "Войти",
                click: function(e) {
                    var modal = $(this);
                    var form = $(this).find($('form'));
                    $.ajax({
                        type: 'POST',
                        url: '/',
                        data: form.serialize(),
                        dataType: 'html'
                    }).done(function(e) {
                        if (e == 'false') {
                            form.before('<p class="error">Не верный логин или пароль.</p>');
                            setTimeout(function() { $('p.error').fadeOut(); }, 3000);
                        } else {
                            localStorage.setItem('auth', 1);
                            modal.dialog('close');
                            getListMassage();
                        }
                    });
                }
            }
        ]
    });

    $( "#dialog-link" ).on('click', function( event ) {
        $( "#dialog" ).dialog( "open" );
        event.preventDefault();
    });

    /**
     * выход
     */
    $( "#logout" ).on('click', function( event ) {
        localStorage.setItem('auth', 0);
        getListMassage();
        event.preventDefault();
    });

    /**
     * модалка для сообщения
     */
    $( "#dialog-comment-form" ).dialog({
        autoOpen: false,
        draggable: false,
        modal: true,
        width: $( ".wrapper" ).width(),
        buttons: [
            {
                text: "Отправить",
                click: function() {
                    var form = $(this).find($('form'));
                    var now = new Date();
                    var publish_date = now.format('yyyy-mm-dd HH:MM:ss');
                    if ($('#dialog-comment-form form textarea[name=message]').val() == '') {
                        form.before('<p class="error">Вы ничего не написали. :(</p>');
                        setTimeout(function() { $('p.error').fadeOut(); }, 3000);
                    } else {
                        if(FuncCounter()) {
                            $.ajax({
                                type: 'POST',
                                url: '/',
                                data: form.serialize() + '&publish_date=' + publish_date,
                                dataType: 'html'
                            }).done(function (e) {
                                console.log(e);
                                getListMassage();
                                localStorage.setItem('counter', 10);
                                FuncCounter();
                            });
                            $(this).dialog("close");
                        } else {
                            form.before('<p class="error">Данное действие пока не возможно.</p>');
                            setTimeout(function() { $('p.error').fadeOut(); }, 3000);
                        }
                    }
                }
            }
        ]
    });

    $( "#dialog-comment" ).on('click', function( event ) {
        $('#dialog-comment-form form input[name=parent_id]').val(0);
        $('#dialog-comment-form form textarea[name=message]').val('');
        $('#dialog-comment-form form input[name=action]').val('new_message');

        $.ajax({
            type: 'POST',
            url: '/',
            data: 'action=count',
            dataType: 'html'
        }).done(function(e) {
            $('#dialog-comment-form form input[name=id]').val(++e);
        });
        $('#dialog-comment-form').dialog( "open" );
        event.preventDefault();
    });

    $( "#dialog-link, #dialog-comment, .icons li" ).hover(
        function() {
            $( this ).addClass( "ui-state-hover" );
        },
        function() {
            $( this ).removeClass( "ui-state-hover" );
        }
    );

    getListMassage();

});

function FuncCounter()
{

    var counter = localStorage.getItem("counter") || 10;

    if (counter == 0 ) {
        return true;
    }

    clearInterval(IntervalID);

    IntervalID = setInterval(function(){
        counter--;
        if (counter == 0 ) {
            clearInterval(IntervalID);
        }
        localStorage.setItem('counter', counter);
    }, 1000);
}

function getListMassage(id, parent_id)
{
    var auth = localStorage.getItem('auth');
    var item = $('.item');
    if (auth == 1) {
        $('#dialog-link').addClass('disabled');
        $('#logout').removeClass('disabled');
    } else {
        $('#dialog-link').removeClass('disabled');
        $('#logout').addClass('disabled');
    }

    item.html('');

    readXML(auth, id, parent_id);
}

function readXML(auth, id, parent_id)
{
    $.ajax({
        type: 'POST',
        url: '/',
        data: 'auth='+ auth + '&id=' + id + '&parent_id=' + parent_id + '&action=read',
        dataType: 'html'
    }).done(function(e) {
        $('.item').html(e);
        //console.log('success');
    }).fail(function() {
        //console.log('fail');
    });
}