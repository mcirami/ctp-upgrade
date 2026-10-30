<?php

namespace App\Support;

class AnnouncementText
{
    public static function sanitize(string $html): string
    {
        static $purifier;
        if (!$purifier) {
            $config = \HTMLPurifier_Config::createDefault();
            // Quill places font/color styles directly on semantic inline elements,
            // not only spans (for example, <strong style="font-size:32px">).
            $config->set('HTML.Allowed', 'p,br,strong[style],b[style],em[style],i[style],u[style],s[style],span[style]');
            $config->set('CSS.AllowedProperties', ['color', 'background-color', 'font-family', 'font-size']);
            $config->set('Cache.DefinitionImpl', null);
            $purifier = new \HTMLPurifier($config);
        }

        return $purifier->purify($html);
    }

    public static function plain(string $html): string
    {
        return html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
