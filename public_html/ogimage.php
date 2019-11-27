<html>
<head>
    <? if (!empty($_GET['image'])): ?>
        <meta property="og:image" content="<?= $_GET['image'] ?>"/>
    <? endif; ?>
    <script type="text/javascript">
        function get_link() {
            var url = document.getElementById("url").value;
            var image = document.getElementById("image").value;
            document.getElementById("link").setAttribute("value", "<?= $_SERVER['HTTP_HOST'] . '://' .$_SERVER['PHP_SELF'] ?>?url=" + encodeURIComponent(url) + "&" + encodeURIComponent(image));
        }
    </script>
</head>
<body>
<? if (!empty($_GET['url'])): ?>
    <script type="text/javascript">
        window.location.replace("<?= $_GET['url'] ?>");
    </script>
    <a href="<?= $_GET['url'] ?>"><?= htmlspecialchars($_GET['url']) ?></a>
<? else: ?>
<input id="url" type="text" onkeyup="get_link()"/><br/>
<input id="image" type="text" onkeyup="get_link()"/><br/>
<input id="link" type="text"/>
<? endif; ?>
</body>
</html>