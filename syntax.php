<?php
/**
 * Plugin Saberlink: Crosslink Dokuwiki Pages.
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Anton Caesar <caesaranton700@yahoo.de>
 */
 
// must be run within DokuWiki
if(!defined('DOKU_INC')) die();
  
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_saberlink extends DokuWiki_Syntax_Plugin {
 
    function getType() { return 'substition'; }
    function getSort() { return 30; }
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<saberlink>.*?</saberlink>', $mode, 'plugin_saberlink'); // Check for the special pattern
    }
 
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        switch ($state) {
            case DOKU_LEXER_SPECIAL:
                $url = substr($match, 11, -12); // Extracting the link from the saberlink string
                return array($state, $url);
        }
        return array();
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
    // $data is what the function handle returned.
        if($mode == 'xhtml'){
            /** @var Doku_Renderer_xhtml $renderer */
            list($state, $url) = $data; // Extract state and url from the input data
            switch ($state) {
                case DOKU_LEXER_SPECIAL:
                    $page = file_get_html($url); // Save page html content into a variable
                    $doc = new DOMDocument();  // Set up the DOMDocument for the page content
                    libxml_use_internal_errors(true); // Silences annoying errors

                    if($page != null) {
                        $doc->loadHTML($page); // Load HTML as a hierarchical DOMDocument
                        libxml_clear_errors(); // Again, deal with errors
                        $divs = $doc->getElementsByTagName('div'); // Get all div elements into a list

                        foreach($divs as $div) {
                            if($div->getAttribute('class') === 'dw-content') { // Check if the class of the div is dw-content (we need only that div)
                                $childs = $div->getElementsByTagName('*'); // Get all child elements of dw-content into a list
                                if(str_ends_with($url, "/")) {
                                    $url = substr($url, 0, -1); // If the url ends with a "/", remove it
                                }
                                $baseurl = parse_url($url, PHP_URL_HOST); // Get the base url from the url string

                                foreach($childs as $child) {
                                    if($child->hasAttribute('href')) {
                                        if(str_starts_with($child->getAttribute('href'), "/")) {
                                            $child->setAttribute('href', 'http://' . $baseurl . $child->getAttribute('href')); // If the child has a href attribute that starts with /, add the base url before it
                                        }
                                    }
                                    if($child->hasAttribute('src')) {
                                        if(str_starts_with($child->getAttribute('src'), "/")) {
                                            $child->setAttribute('src', 'http://' . $baseurl . $child->getAttribute('src')); // If the child has a src attribute that starts with /, add the base url before it
                                        }
                                    }
                                }
                                
                                $div->setAttribute('class', 'dw-content-embed'); // Rename the class to dw-content-embed, so that there are no two dw-content divs on the final page
                                // Send all the content to the renderer in a modified html container
                                $renderer->doc .= '<div class="saberlink-embed" style="border:1px solid #DDDDDD; border-radius:8px; margin:-8px; padding:8px">';
                                $renderer->doc .= '<p style="font-size: 12px; color: #888888"><span style="background-color: #EEEEEE; padding: 3px; border-radius:4px"> Embedded from: <a href="' . $url . '">' . $url . '</a></span></p>';
                                $renderer->doc .= $doc->saveHTML($div);
                                $renderer->doc .= '</div>';
                            }
                        }
                    }
            }
            return true;
        }
        return false;
    }
}
?>
