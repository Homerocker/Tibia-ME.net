<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
$document = new Document(SITE_NAME, array(
    array('Navigation link', './'),
    array('Navigation text')
));
?>

<h3>Page header</h3>
<div class="callout primary">
    Window text
    <div class="small">Small text</div>
    <a href="#">Window link</a><br/><br/>
    <div class="callout secondary">Quoted message</div><br/>
    <div class="signature">Signature</div><br/>
    Search results <span class="search_result_highlight">highlight</span><br/><br/>
    <div class="green">+1234567</div>
    <div class="red">-1234567</div><br/>
    Avatar:<br/>
    <img src="/images/404_image.gif" alt="" class="avatar"/><br/><br/>
    Previews:<br/>
    <img src="/images/404_image.gif" alt="" class="gallery_preview"/>
</div>

<?php
$document->get_page(20);
$document->pages();