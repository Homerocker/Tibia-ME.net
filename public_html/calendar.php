<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$calendar = new Calendar;
$m = get_month(date('m'));
if (isset($_POST['id']) && Perms::get(Perms::CALENDAR_EDIT)) {
    if ($calendar->save_event($_POST) === true) {
        Document::reload_msg(_('Changes saved.'), $_SERVER['SCRIPT_NAME'] . '?month=' . $m);
    } else {
        Document::msg($calendar->get_errors());
    }
} elseif (isset($_GET['del']) && Perms::get(Perms::CALENDAR_EDIT)) {
    if ($calendar->delete_event($_GET['del'])) {
        Document::reload_msg(_('Event has been removed.'), $_SERVER['SCRIPT_NAME'] . '?month=' . $m);
    }
}
$doc = new Document(_('Calendar'), isset($_REQUEST['id']) ? [[_('Calendar'), $_SERVER['SCRIPT_NAME']]] : null);

if (isset($_REQUEST['id'])) {
    if (!empty($_REQUEST['id']) && ctype_digit($_REQUEST['id'])) {
        if (!$calendar->set_event_id($_REQUEST['id'])) {
            $doc->display('invalid_request');
            exit;
        }
    }
    $event = $calendar->get_event();
    $doc->assign([
        'event' => $event,
        'm' => $m
    ]);
    $doc->display('calendar_edit');
} else {
    $layout = $calendar->get_layout($m);
    $doc->assign([
        'events' => $calendar->get_events(),
        'layout' => $layout,
        'm' => $m
    ]);
    $doc->display('calendar');
}
