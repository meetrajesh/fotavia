<?php

class rss {

    public $specification = '2.0';
    public $stylesheet = '';
    public $about = '';
    public $guid_ispermalink = FALSE;

    private $channel;
    private $image = FALSE;
    private $items = array();

    // special chars that may need to be treated specially
    private static $accents = array('é' => '[[**(e)**]]');

    public function add_channel($props) {
        $this->channel = $props;
    }

    public function add_image($props) {
        $this->image = $props;
    }

    public function add_item($props) {
        $this->items[] = $props;
    }

    public function get_output() {

        $dom = new DOMDocument('1.0', 'ISO-8859-1');
        $dom->formatOutput = TRUE;

        if (!empty($this->stylesheet)) {
            $node = $dom->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . $this->stylesheet . '"');
            $dom->appendChild($node);
        }
        
        // <rss version="2.0">
        $rss = $dom->createElement('rss');
        $rss->setAttribute('version', '2.0');        
        $dom->appendChild($rss);

        // <channel>
        $channel = $dom->createElement('channel');
        $rss->appendChild($channel);

        // <channel> properties
        foreach ($this->channel as $key => $val) {

            switch ($key) {

            case 'title':
            case 'link':
            case 'description':
            case 'language':
            case 'copyright':
            case 'lastBuildDate':
                $channel->appendChild($dom->createElement($key))->appendChild($dom->createTextNode($val));
                break;
            default:
                die('Unknown TAG ' . $key . ' on LINE ' . __LINE__ . ' of FILE ' . __FILE__);

            }

        }

        if (false !== $this->image) {

          // <image>
          $image = $dom->createElement('image');
          $channel->appendChild($image);
  
          $url   = $image->appendChild($dom->createElement('url'));
          $title = $image->appendChild($dom->createElement('title'));
          $link  = $image->appendChild($dom->createElement('link'));
  
          $url->appendChild($dom->createTextNode($this->image['url']));
          $title->appendChild($dom->createTextNode($this->image['title']));
          $link->appendChild($dom->createTextNode($this->image['link']));

        }
  
        // <item>'s
        foreach ($this->items as $item) {

            $item_node = $dom->createElement('item');
            $channel->appendChild($item_node);

            foreach ($item as $key => $val) {

                $element = $dom->createElement($key);

                switch ($key) {

                case 'title':
                case 'link':
                case 'pubDate':
                case 'guid':
                    $text = $dom->createTextNode(htmlspecialchars($val, ENT_NOQUOTES));
                    if ($key == 'guid') {
                        $element->setAttribute('isPermaLink', $this->guid_ispermalink ? 'true' : 'false');
                    }
                    break;
                case 'description':
                    $val = str_replace(array_keys(self::$accents), array_values(self::$accents), $val);
                    $text = $dom->createCDATASection($val);
                    break;
                default:
                    die('Unknown TAG ' . $key . ' on LINE ' . __LINE__ . ' of FILE ' . __FILE__);

                }

                $element->appendChild($text);
                $item_node->appendChild($element);
            }
        }

        $output = $dom->saveXML();
        return str_replace(array_values(self::$accents), array_keys(self::$accents), $output);

    }

}

?>
