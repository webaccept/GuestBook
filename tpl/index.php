<?$this->IncludeTemplate('header',null);?>

<h2 class="demoHeaders">GuestBook</h2>
<ul class="icons auth ui-widget ui-helper-clearfix">
    <li id="dialog-comment" class="ui-state-default ui-corner-all tooltip" title="Добавить сообщение"><span class="ui-icon ui-icon-comment"></span></li>
    <li id="dialog-link" class="ui-state-default ui-corner-all tooltip" title="Авторизация"><span class="ui-icon ui-icon-person"></span></li>
    <li id="logout" class="disabled ui-state-default ui-corner-all tooltip" title="Выйти"><span class="ui-icon ui-icon-power"></span></li>
</ul>
<hr />

<div class="item"></div>

<div id="dialog-comment-form" title="Сообщение">
    <form>
        <fieldset>
            <label for="message">Сообщение:</label>
            <textarea name="message" id="message" class="text ui-widget-content ui-corner-all"></textarea>
            <input type="hidden" name="id">
            <input type="hidden" name="parent_id">
            <input type="hidden" name="action">

        </fieldset>
    </form>
</div>
<div id="dialog" title="Авторизация">
    <form>
        <fieldset>
            <label for="login">Логин:</label>
            <input type="text" name="login" id="login" class="text ui-widget-content ui-corner-all">
            <label for="password">Пароль:</label>
            <input type="password" name="password" id="password" class="text ui-widget-content ui-corner-all">
            <input type="hidden" name="action" value="auth">
        </fieldset>
    </form>
</div>
<?$this->IncludeTemplate('footer',null);?>