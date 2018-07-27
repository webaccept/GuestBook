<div class="list">
    <div class="datetime"><?=$data['publish_date'];?></div>
    <?if (!empty($data['is_login'])) {?>
    <div class="action">
        <span onclick="AnswerMessage(<?= $data['is_login']; ?>, <?= $data['id']; ?>, <?= $data['parent_id']; ?>);" class="ui-icon ui-icon-comment tooltip" title="Ответить на сообщение"></span>
        <span onclick="EditMessage(<?= $data['is_login']; ?>, <?= $data['id']; ?>, <?= $data['parent_id']; ?>);" class="ui-icon ui-icon-pencil tooltip" title="Редактировать сообщение"></span>
        <!-- <span id="delete" data-id="<?= $data['id']; ?>" data-parent_id="<?= $data['parent_id']; ?>" class="ui-icon ui-icon-trash tooltip" title="Удалить сообщение / ветку "></span> -->
    </div>
    <?}?>
    <div class="text"><?=$data['message'];?></div>
</div>
<div class="answer"><? $this->SubReadXML($data['is_login'], $data['id'], $data['parent_id']) ?></div>