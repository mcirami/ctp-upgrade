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
            $config->set('HTML.Allowed', 'p,br,strong[style],b[style],em[style],i[style],u[style],s[style],span[style],img[src|alt]');
            $config->set('CSS.AllowedProperties', ['color', 'background-color', 'font-family', 'font-size']);
            $config->set('Cache.DefinitionImpl', null);
            $purifier = new \HTMLPurifier($config);
        }

        $clean = $purifier->purify($html);
        // Only our authenticated GIF endpoint may be embedded; no external tracking
        // images, data URLs, or arbitrary local endpoints.
        return preg_replace_callback('/<img\b[^>]*>/i', function ($match) {
            if (preg_match('~\bsrc="(/announcements/media/[a-f0-9-]{36}\.gif)"~', $match[0])) {
                return $match[0];
            }
            return '';
        }, $clean);
    }

    public static function plain(string $html): string
    {
        return html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
