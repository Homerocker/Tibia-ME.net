
<?php
/*
  if ($pages > 5 || ($pages === 5 && $pages !== 3) || ($pages === 4 && ($page === 1 || $page === $pages))) {
  echo '<form action="', $_SERVER['SCRIPT_NAME'], '" method="get">';
  echo '<div class="table bt">'
  . '<div class="cell">'
  . '<input type="text" name="page" accept="*N" size="2" value="', $page, '"/>'
  . '</div>';
  if (isset($params)) {
  foreach ($params as $name => $value) {
  echo '<input type="hidden" name="', htmlspecialchars($name), '" value="', htmlspecialchars($value), '"/>';
  }
  }
  echo '<div class="cell">'
  . '<input type="submit" value="', _('Go'), '"/>'
  . '</div>'
  . '</div>';
  echo '</form>';
  }
 * *
 */
?>
<? if ($show_ads): ?>
    <!-- responsive 3 -->
    <ins class="adsbygoogle text-center"
         style="display:block"
         data-ad-client="ca-pub-3385318666093811"
         data-ad-slot="8477556404"
         data-ad-format="auto"></ins>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
<? endif; ?>
<nav aria-label="Pagination">
    <ul class="pagination text-center">
        <? if ($page > 1) { ?>
            <li class="pagination-previous"><a href="<?= ($_SERVER['SCRIPT_NAME'] . $query . ($page - 1)) ?>"><?= _('prev') ?></a></li>
        <? } if ($page !== 1) { ?>
            <li><a aria-label="Page 1" href="<?= ($_SERVER['PHP_SELF'] . $query) ?>1">1</a></li>
        <? } if ($pages > 3 && $page > 2) { ?>
            <li class="ellipsis"></li>
        <? } ?>
        <li class="current"><span class="show-for-sr">You're on page </span><?= $page ?></li>
        <? if ($page !== $pages) { ?>
            <li class="ellipsis"></li>
            <li><a href="<?= ($_SERVER['PHP_SELF'] . $query . $pages) ?>"><?= $pages ?></a></li>
            <li class="pagination-next"><a href="<?= ($_SERVER['SCRIPT_NAME'] . $query . ($page + 1)) ?>"><?= _('next') ?></a></li>
        <? } ?>
    </ul>
</nav>