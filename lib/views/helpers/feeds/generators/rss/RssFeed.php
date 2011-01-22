<?php
namespace ntentan\views\helpers\feeds\generators\rss;

use ntentan\Ntentan;
use ntentan\views\helpers\feeds\generators\Generator;

class RssFeed extends Generator
{
    public function execute()
    {
        $rss = new \SimpleXMLElement('<rss></rss>');
        $rss['version'] = '2.0';
        $rss->addChild('channel');
        foreach($this->properties as $property => $value)
        {
            $rss->channel->addChild(Ntentan::camelize($property, '.', '', true), $value);
        }
        foreach($this->items as $item)
        {
            $itemElement = $rss->channel->addChild('item');
            foreach($item as $field => $value)
            {
                if($field == "published") $field = 'pubDate';
                if($field == "source") $field = 'description';
                if($field == "id") $field = 'guid';
                $value = utf8_encode($value);
                $itemElement->addChild($field, $value);
            }
        }
        return $rss->asXML();
    }
}
