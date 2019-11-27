<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <div class="callout primary">
        <b><?= _('Nickname') ?></b>:<br/>
        <a href="./profile.php?u=<?= $_GET['ban'] ?>"><?= $sql['nickname'] ?></a><br/>
        <b><?= _('World') ?></b>:<br/> <?= $sql['world'] ?><br/>
        <label for="reason"><?= _('Reason') ?></label>
        <select id="reason" name="reason">
            <option value="1"><?= _('Spam') ?></option>
            <option value="2"><?= _('Offensive statements') ?></option>
            <option value="3"><?= _('Rules violation') ?></option>
        </select>
        <label for="description"><?= _('Description') ?> (<?= _('optional') ?>)</label>
        <textarea id="description" name="description" rows="5"></textarea>
        <?php
        if (isset($_GET['forumPost'])) {
            $query = $db->query('select *
                    from `forumPosts`
                    where `id` = \'' . intval($_GET['forumPost']) . '\'
                    and `posterID` = \'' . $sql['id'] . '\'')->fetch_assoc();
            if ($query !== null) {
                echo '<b>' . _('Message') . '</b>:<br/>';
                echo Forum::MessageHandler($query['message']) . '<br/>';
                echo '<input type="hidden" name="forumPost" value="' . $_GET['forumPost'] . '"/>';
            }
        }
        ?>
        <label for="temporal"><?= _('Type') ?></label>
        <input id="temporal" type="radio" name="expirationType" value="t" checked/><?= _('Temporal') ?><br/>
        <input type="text" name="expirationTime" accept="*N" size="3" maxlength="3" value="0"/>&nbsp;<select name="expirationTimeType">
            <option value="m"><?= _('minutes') ?></option>
            <option value="h" selected="selected"><?= _('hours') ?></option>
            <option value="d"><?= _('days') ?></option>
        </select>
        
        <input type="radio" name="expirationType" value="p"/><?= _('Permanent') ?><br/>
        <input type="hidden" name="ban" value="<?= $sql['id'] ?>"/>
        <input class="button warning" type="submit" value="<?= _('Banish') ?>"/>
    </div>
</form>