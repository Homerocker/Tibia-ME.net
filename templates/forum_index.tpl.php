<div class="grid-x grid-padding-x">
    <? foreach ($data as $key => $board): ?>
        <div class="cell medium-6">
            <div class="callout primary">
                <h4 class="no-margin"><a href="./viewforum.php?f=<?= $board['id'] ?>">
                        <?= $board['title'] ?>
                    </a>
                </h4>
                <div>
                <? if ($board['status']['unread']): ?>
                    <span class="label success"><?= _('new') ?></span>
                <? endif; ?>
                <? if ($board['status']['hidden']): ?>
                    <span class="label primary"><?= _('hidden') ?></span>
                <? endif; ?>
                </div>
                    <?=
                    sprintf(ngettext('%d topic', '%d topics', $board['topics']),
                            $board['topics'])
                    ?><br/>
                    <?=
                    sprintf(ngettext('%d post', '%d posts', $board['posts']),
                            $board['posts'])
                    ?>
            </div>
        </div>
    <? endforeach; ?>
</div>