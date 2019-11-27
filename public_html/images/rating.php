<?php

define('RATING_BAR_WIDTH', 50);
define('RATING_BAR_HEIGHT', 6);
define('RATING_BAR_BORDER', 0);
define('RATING_BAR_BG_COLOR', [250, 255, 200]);
define('RATING_BAR_BORDER_COLOR', null);
define('RATING_BAR_LIKE_COLOR', [0, 220, 0]);
define('RATING_BAR_DISLIKE_COLOR', [240, 0, 0]);

header('Content-type: image/png');

$p = intval($_GET['p'] ?? 0);

if ($p < -1) {
    $p = -1;
} elseif ($p > 100) {
    $p = 100;
}

$image = imagecreatetruecolor(RATING_BAR_WIDTH, RATING_BAR_HEIGHT);

imagefill($image, RATING_BAR_BORDER, RATING_BAR_BORDER,
        imagecolorallocate($image, RATING_BAR_BG_COLOR[0],
                RATING_BAR_BG_COLOR[1], RATING_BAR_BG_COLOR[2]));

for ($i = 0; $i < RATING_BAR_BORDER; ++$i) {
    imagerectangle($image, $i, $i, RATING_BAR_WIDTH - 1 - $i,
            RATING_BAR_HEIGHT - 1 - $i,
            imagecolorallocate($image, RATING_BAR_BORDER_COLOR[0],
                    RATING_BAR_BORDER_COLOR[1], RATING_BAR_BORDER_COLOR[2]));
}

if ($p != -1) {
    $like_width = round((RATING_BAR_WIDTH - RATING_BAR_BORDER * 2) * $p / 100);

    if ($like_width > 0) {
        imagefilledrectangle($image, RATING_BAR_BORDER, RATING_BAR_BORDER,
                $like_width + RATING_BAR_BORDER - 1,
                RATING_BAR_HEIGHT - RATING_BAR_BORDER - 1,
                imagecolorallocate($image, RATING_BAR_LIKE_COLOR[0],
                        RATING_BAR_LIKE_COLOR[1], RATING_BAR_LIKE_COLOR[2]));
    }

    if ($like_width < RATING_BAR_WIDTH - RATING_BAR_BORDER * 2) {
        imagefilledrectangle($image, RATING_BAR_BORDER + $like_width,
                RATING_BAR_BORDER, RATING_BAR_WIDTH - RATING_BAR_BORDER - 1,
                RATING_BAR_HEIGHT - RATING_BAR_BORDER - 1,
                imagecolorallocate($image, RATING_BAR_DISLIKE_COLOR[0],
                        RATING_BAR_DISLIKE_COLOR[1], RATING_BAR_DISLIKE_COLOR[2]));
    }
}

imagepng($image);
imagedestroy($image);
