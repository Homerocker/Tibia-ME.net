            </div>
            <div class="cell medium-3 hide-for-small-only">
                <? if ($show_ads): ?>
                    <!-- Sidebar -->
                    <ins class="adsbygoogle text-center"
                         style="display:block"
                         data-ad-client="ca-pub-3385318666093811"
                         data-ad-slot="6810431140"
                         data-ad-format="auto"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                    <!-- responsive 2 -->
                    <ins class="adsbygoogle text-center"
                         style="display:block"
                         data-ad-client="ca-pub-3385318666093811"
                         data-ad-slot="6944982883"
                         data-ad-format="auto"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                <? endif; ?>
            </div>
        </div>
        <div class="callout secondary text-center no-margin">
            <?= _('Tibia and TibiaME are trademarks of <a href="http://www.cipsoft.com" target="_blank">CipSoft GmbH</a>, Germany.') ?><br/>
            <?php
            list($version, $date) = explode(' ',
                    file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/changelog/CURRENT_VERSION'));
            echo 'Tibia-ME.net <a href="/changelog/CHANGELOG_', str_replace('.', '_',
                    $version), '">v', $version, '</a>&nbsp;', $date, '<br/>';
            ?>
        </div>
        <script src="/foundation/js/vendor/jquery.min.js"></script>
        <script src="/foundation/js/vendor/what-input.min.js"></script>
        <script src="/foundation/js/vendor/foundation.min.js"></script>
        <script src="/foundation/js/jquery.blockUI.js"></script>
        <script src="/foundation/js/app.js?3011"></script>
        <script>
            if (Object.prototype.toString.call(window.operamini) === "[object OperaMini]") {
                $("#content").prepend('<div class="callout alert text-center"><?= _('You are using Opera Mini in maximum compression mode. Some features may not work correctly. Please adjust your browser settings.') ?></div>');
            }
        </script>
    </body>
</html>