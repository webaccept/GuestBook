<div class="list">
    <div class="datetime"><?=$data['publish_date'];?></div>
    <?if (!empty($data['is_login'])) {?>
    <div class="action">
        <span class="ui-icon ui-icon-comment tooltip" title="Ответить на сообщение"></span>
        <span class="ui-icon ui-icon-pencil tooltip" title="Редактировать сообщение"></span>
        <!-- <span class="ui-icon ui-icon-trash tooltip" title="Удалить сообщение / ветку "></span> -->
    </div>
    <?}?>
    <div class="text"><?=$data['message'];?></div>
    <div class="answer"></div>
</div>