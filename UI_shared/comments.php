<?php

/**
 * Comments model.
 * 
 * @package comments
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 */
/**
 * use section name as parameter (artworks, photos, screenshots or themes) 
 */
$comments = new Comments($item_type);
if ($_SESSION['user_id'] && Document::s_get_page() == 1) {
    Notifications::view(array($comments->item_type . 'Comment', $comments->item_type . 'Like'),
            $comments->item_id);
}
$navi[] = array(
    array($section_name, './')
);
if ($item_type == 'photo') {
    $navi = array_merge($navi,
            array(
        array(User::get_link($comments->owner_id, 0), './albums.php?u=' . $comments->owner_id),
        array($comments->album_title, './photos.php?album_id=' . $comments->album_id)
    ));
}
/*
  elseif ($comments->report_form()) {
  $navi = array(
  array(_('Comments'), $_SERVER['PHP_SELF'] . $comments->query_string),
  );
  $document->assign(array(
  'page' => $document->page,
  'item_id' => $comments->item_id,
  'item_type' => $comments->item_type,
  'comment_id' => $comments->comment_id,
  'comment' => $comments->comment
  ));
  $document->display('comments_report');
  }
 * 
 */
    $document = new Document(_('Comments'), $navi, false);
    $u = Auth::get_u(null);
    $comments->fetch(15);
    $document->get_page($comments->pages);
    $document->assign(array(
        'data' => $comments->data,
        'page' => $document->page,
        'item_type' => $comments->item_type,
        'item_id' => $comments->item_id,
        'comment' => $comments->comment,
        'path' => $comments->path,
        'thumbnail' => $comments->thumbnail,
        'thumbnail_width' => $comments->thumbnail_width,
        'watch' => $comments->watch,
        'editable' => $comments->editable,
            //'reportable' => $comments->reportable
    ));
    $document->display('comments');
    $document->pages($comments->item_type . '_id', $comments->item_id);
