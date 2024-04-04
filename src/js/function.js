let IntervalID;

$(document).ready(function () {
    FuncCounter();

    $('.tooltip').tooltip();

    /**
     * Открываем мадальное окно Авторизации
     */
    $('#dialog-auth').on('click', function (e) {
        e.preventDefault();
        $('#dialog-auth-form').dialog('open');
    });

    /**
     * Модалка для авторизации
     */
    $('#dialog-auth-form').dialog({
        autoOpen: false,
        width: 250,
        draggable: false,
        resizable: false,
        modal: true,
        position: {
            my: 'top+25',
            at: 'top',
            of: window
        },
        buttons: [
            {
                text: 'Войти',
                click: function () {
                    let modal = $(this);
                    let form = $(this).find($('form'));

                    $.ajax({
                        type: 'POST',
                        url: '/',
                        dataType: 'html',
                        cache: false,
                        async: true,
                        data: form.serialize(),
                        beforeSend: function () {
                        },
                        complete: function () {
                        },
                        success: function (e) {
                            if (e === 'true') {
                                localStorage.setItem('auth', JSON.stringify(true));
                                modal.dialog('close');
                                getListMassage();
                            } else {
                                form.before('<p class="error">Не верный логин или пароль.</p>');
                                setTimeout(function () {
                                    $('p.error').fadeOut();
                                }, 3000);
                            }
                        },
                        error: function (error) {
                            console.log('Error: ', error);
                        }
                    });
                }
            }
        ]
    });

    /**
     * Выход
     */
    $('#logout').on('click', function (e) {
        e.preventDefault();
        localStorage.removeItem('auth');
        getListMassage();
    });

    /**
     * Открываем по клику модальное окно для написание/редактирования сообщения
     */
    $('#dialog-comment').on('click', function (e) {
        e.preventDefault();

        $('#dialog-comment-form form input[name="parent_id"]').val(0);
        $('#dialog-comment-form form textarea[name="message"]').val('');
        $('#dialog-comment-form form input[name="action"]').val('new_message');

        $.ajax({
            type: 'POST',
            url: '/',
            dataType: 'html',
            cache: false,
            async: true,
            data: {
                action: 'count',
            },
            beforeSend: function () {
            },
            complete: function () {
            },
            success: function (e) {
                $('#dialog-comment-form form input[name="id"]').val(++e);
                $('#dialog-comment-form').dialog('open');
            },
            error: function (error) {
                console.log('Error: ', error);
            }
        });
    });

    /**
     * Модалка для сообщения
     */
    $('#dialog-comment-form').dialog({
        autoOpen: false,
        draggable: false,
        modal: true,
        width: $(".wrapper").width(),
        buttons: [
            {
                text: "Отправить",
                click: function () {
                    let form = $(this).find($('form'));
                    let now = new Date();
                    let publish_date = now.format('yyyy-mm-dd HH:MM:ss');

                    if ($('#dialog-comment-form form textarea[name="message"]').val() === '') {
                        form.before('<p class="error">Вы ничего не написали. :(</p>');
                        setTimeout(function () {
                            $('p.error').fadeOut();
                        }, 3000);
                    } else {
                        if (FuncCounter()) {

                            $.ajax({
                                type: 'POST',
                                url: '/',
                                dataType: 'html',
                                cache: false,
                                async: true,
                                data: form.serialize() + '&publish_date=' + publish_date + '&auth=' + localStorage.getItem('auth'),
                                beforeSend: function () {
                                },
                                complete: function () {
                                },
                                success: function () {
                                    getListMassage();
                                    localStorage.setItem('counter', JSON.stringify(10));
                                    FuncCounter();
                                },
                                error: function (error) {
                                    console.log('Error: ', error);
                                }
                            });
                            $(this).dialog('close');
                        } else {
                            form.before('<p class="error">Данное действие пока не возможно.</p>');
                            setTimeout(function () {
                                $('p.error').fadeOut();
                            }, 3000);
                        }
                    }
                }
            }
        ]
    });

    /**
     * При наведении на иконки добавляем/удалем css класс
     */
    $('#dialog-auth, #dialog-comment, .icons li').hover(
        function () {
            $(this).addClass("ui-state-hover");
        },
        function () {
            $(this).removeClass("ui-state-hover");
        }
    );

    getListMassage();
});

/**
 * Ограничиваем отправку повторного сообщения на 10 секунд
 *
 * @returns {boolean}
 * @constructor
 */
function FuncCounter() {
    let counter = JSON.parse(localStorage.getItem('counter'));

    if (counter <= 0) {
        counter = 0;

        return true;
    }

    clearInterval(IntervalID);

    IntervalID = setInterval(function () {
        counter--;
        if (counter <= 0) {
            clearInterval(IntervalID);
        }
        localStorage.setItem('counter', counter);
    }, 1000);
}

/**
 * Читаем сообщение и взависимости авторизации показываем/прячем иконки
 */
function getListMassage() {
    let auth = (JSON.parse(localStorage.getItem('auth')) === true);
    let item = $('.item');

    if (auth === true) {
        $('#dialog-auth').addClass('disabled');
        $('#logout').removeClass('disabled');
    } else {
        $('#dialog-auth').removeClass('disabled');
        $('#logout').addClass('disabled');
    }

    item.html('');

    readXML(auth);
}

/**
 * Читаем сообщения и выводим
 *
 * @param auth
 * @param id
 * @param parent_id
 */
function readXML(auth = false, id = null, parent_id = null) {
    let item = $('.item');

    $.ajax({
        type: 'POST',
        url: '/',
        dataType: 'html',
        cache: false,
        async: true,
        data: {
            auth: auth,
            id: id,
            parent_id: parent_id,
            action: 'read',
        },
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (e) {
            if (e) {
                item.html(e);
            }
        },
        error: function (error) {
            console.log('Error: ', error);
        }
    });
}

/**
 * Открываем модалку для редактирования сообщения
 *
 * @param auth
 * @param id
 * @param parent_id
 * @constructor
 */
function EditMessage(auth, id, parent_id) {
    $.ajax({
        type: 'POST',
        url: '/',
        dataType: 'html',
        cache: false,
        async: true,
        data: {
            auth: auth,
            id: id,
            parent_id: parent_id,
            action: 'read',
        },
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (e) {
            if (e) {
                let data = JSON.parse(e);
                $('#dialog-comment-form form input[name="id"]').val(data.id);
                $('#dialog-comment-form form input[name="parent_id"]').val(data.parent_id);
                $('#dialog-comment-form form textarea[name="message"]').val(data.message.replace(/\s+/g, ' '));
                $('#dialog-comment-form form input[name="action"]').val('edit_message');
                $('#dialog-comment-form').dialog('open');
            }
        },
        error: function (error) {
            console.log('Error: ', error);
        }
    });
}

/**
 * Открываем модалку для ответа на сообщение
 *
 * @param id
 * @param parent_id
 * @constructor
 */
function AnswerMessage(id, parent_id) {
    $('#dialog-comment-form form textarea[name="message"]').val('');

    $.ajax({
        type: 'POST',
        url: '/',
        dataType: 'html',
        cache: false,
        async: true,
        data: {
            action: 'count',
        },
        beforeSend: function () {
        },
        complete: function () {
        },
        success: function (e) {
            if (e) {
                $('#dialog-comment-form form input[name="id"]').val(++e);
            }
        },
        error: function (error) {
            console.log('Error: ', error);
        }
    });
    $('#dialog-comment-form form input[name="parent_id"]').val(parent_id);
    $('#dialog-comment-form form input[name="action"]').val('new_message');
    $('#dialog-comment-form').dialog('open');
}