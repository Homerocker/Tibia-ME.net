<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';

$start = microtime(true);

$updater = new HighscoresUpdater;

$updater->fetch();

$updater->setXML();

$updater->parse();

$updater->update_worlds();

$updater->cleanup();

log_error('highscores update took ' . (microtime(true) - $start) .'s');

echo 'OK';