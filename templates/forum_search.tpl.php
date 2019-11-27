<div class="callout secondary text-center">
    <?php
    if ($results == 0) {
        echo _('Your search returned no results.');
    } else {
        printf(ngettext('Your search returned %d result.', 'Your search returned %d results.', $results), $results);
    }
    ?>
</div>
<?php
foreach ($data as $index => $result) {
    $topic = $GLOBALS['db']->query('select `forumID`, `title`
        from `forumTopics`
        where `id` = \'' . $result['topicID'] . '\'')->fetch_assoc();
    $forum = $GLOBALS['db']->query('select `title`
        from `forums`
        where `id` = \'' . $result['forumID'] . '\'')->fetch_assoc();
    ?>
    <h3>
        <a href="./viewforum.php?f=<?= $result['forumID'] ?>"><?= htmlspecialchars($result['forum_title']) ?></a>
    </h3>
    <div class="callout primary">
        <h5><a href="./viewtopic.php?t=<?= $result['topicID'] ?>"><?= htmlspecialchars($result['topic_title']) ?></a></h5>
        <?= _('Author') ?>: <?= $result['poster'] ?><br/>
        <?= $result['time'] ?>
        <div class="callout secondary"><?= $result['message'] ?></div>
    </div>
<?php
}