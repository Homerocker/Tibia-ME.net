<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2014, Tibia-ME.net
 * Contains DOM based on DOMDocument, and all its extensions.
 */

/**
 * Extension to DOMDocument::DOMElement().
 */
class DOMElementX extends DOMElement {

    /**
     * Hi from Javascript.
     * The same as DOMElement::nodeValue but with HTML tags.
     * NOT the same as DOMDocument::saveXML(DOMElement) (this one returns CHILDREN HTML ONLY)
     * @return string inner HTML of an element
     */
    public function innerHTML() {
        $innerHTML = '';
        foreach ($this->childNodes as $child) {
            $innerHTML .= $this->ownerDocument->saveHTML($child);
        }
        return $innerHTML;
    }

}

/**
 * Extended DOMDocument.
 */
class DOMDocumentX extends DOMDocument {

    /**
     * Extended DOMDocument.
     * Used to enable extensions only (via DOMDocument::registerNodeClass()),
     * see DOMDocument for accepted params, returned values, etc.
     * You may use PHP's default DOMDocument class instead if you don't need any additional functionality.
     */
    public function __construct($version = null, $encoding = null) {
        // passing params and invoking real DOMDocument, no return expected
        parent::__construct($version, $encoding);
        // registering extenstions to the DOMDocument
        $this->registerNodeClass('DOMElement', 'DOMElementX');
    }

    public function loadHTMLFile($filename, $options = null, $max_attempts = 3) {
        if (parse_url($filename, PHP_URL_HOST) === null) {
            // if attempting to load local file
            return parent::loadHTMLFile($filename, $options);
        }
        $HTMLString = curl_get_contents($filename, $max_attempts);
        return ($HTMLString === false) ? false : parent::loadHTML($HTMLString, $options);
    }

}
