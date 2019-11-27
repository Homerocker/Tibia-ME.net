<?php

require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
CP::auth();
echo 'Default locale:&nbsp;';
if (in_array(DEFAULT_LOCALE, array_keys(LOCALES))) {
    echo '<span style="color: green;">', DEFAULT_LOCALE, '</span><br/>';
} else {
    echo '<span style="color: red;">', sprintf(_('%s is not in locales list.'),
            DEFAULT_LOCALE), '</span><br/>';
}
$writable = array(
    array(
        $_SERVER['DOCUMENT_ROOT'] . CACHE_DIR,
        true
    ),
    array(
        sys_get_temp_dir(),
        false
    ),
    array(
        session_save_path(),
        false
    ),
    array(
        $_SERVER['DOCUMENT_ROOT'] . '/../gettextextras',
        true
    ),
    array(
        $_SERVER['DOCUMENT_ROOT'] . '/../worlds.dat',
        false
    ),
    array(
        $_SERVER['DOCUMENT_ROOT'] . '/images/icons/classic',
        true
    )
);

foreach (array_keys(LOCALES) as $locale) {
    $writable[] = array(
        $_SERVER['DOCUMENT_ROOT'] . '/../locale/' . $locale,
        false
    );
}
foreach ($writable as $file) {
    if (!file_exists($file[0])) {
        echo $file[0], ':&nbsp;';
        echo '<span style="color: red;">';
        echo _('NOT FOUND');
    } elseif (is_dir($file[0]) && $file[1]) {
        if (!is_readable($file[0])) {
            echo '<span style="color: red;">';
            echo $file[0], ':&nbsp;';
            echo substr(sprintf('%o', fileperms($file[0])), -4);
            echo '</span><br/>';
            continue;
        }
        $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file[0],
                RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST);
        $i = 0;
        foreach ($dir as $node) {
            if ($node->getFilename() !== '.htaccess' && $node->isWritable()) {
                continue;
            }
            if ($node->getFilename() === '.htaccess') {
                $perm = substr(decoct($node->getPerms()), -4);
                if ($perm[2] <= 4 && $perm[3] <= 4) {
                    continue;
                }
            }
            if ($i) {
                echo '</span><br/>';
            }
            echo $node->getPathname(), ':&nbsp;';
            echo '<span style="color: red;">', substr(sprintf('%o',
                            $node->getPerms()), -4);
            ++$i;
        }
        if (!$i) {
            echo $file[0], ':&nbsp;';
            echo '<span style="color: green;">';
            echo substr(sprintf('%o', fileperms($file[0])), -4);
        }
    } elseif (is_writable($file[0])) {
        echo $file[0], ':&nbsp;';
        echo '<span style="color: green;">';
        echo substr(sprintf('%o', fileperms($file[0])), -4);
    } else {
        echo $file[0], ':&nbsp;';
        echo '<span style="color: red;">';
        echo substr(sprintf('%o', fileperms($file[0])), -4);
    }
    echo '</span><br/>';
}
$perms = (new ReflectionClass('Perms'))->getConstants();
if (array_diff($perms, array_unique($perms))) {
    echo '<span style="color: red;">Duplicate Perms IDs found</span>';
} else {
    echo '<span style="color: green;">No duplicate Perms IDs found</span>';
}
echo '<br/>open_basedir:&nbsp;';
$open_basedir = ini_get('open_basedir');
if (!$open_basedir || $open_basedir == '/') {
    echo '<span style="color: green;">OK</span>';
} else {
    echo '<span style="color: red;">'. htmlspecialchars($open_basedir).'</span>';
}
$browscap = Browscap::get_browser();
echo '<br/>Browscap: ' . ($browscap ? '<span style="color: green;">OK</span>' : '<span style="color: red;">' . strtoupper(var_export($browscap, true)) . '</span>');
echo '<br/>Imagick: ' . (extension_loaded('imagick') ? '<span style="color: green;">LOADED</span>' : '<span style="color: red;">NOT LOADED</span>');
echo '<br/>Intl: ' . (extension_loaded('intl') ? '<span style="color: green;">LOADED</span>' : '<span style="color: red;">NOT LOADED</span>');
