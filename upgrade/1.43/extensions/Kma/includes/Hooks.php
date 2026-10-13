<?php
namespace Fina\Kma;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\Sanitizer;
use MediaWiki\Html\Html;

class Hooks {
    public static function onSetupAfterCache(): void {
        $GLOBALS['wgResourceModules']['ext.srf.datatables.v2.format']['dependencies'][] = 'ext.fina.tableExports';
    }

    public static function onParserFirstCallInit(Parser $parser): void {
        $parser->setFunctionHook('noparse', [self::class, 'noParse']);
    }

    public static function noParse(Parser $parser, string $text = ''): array {
        // Template:Main Page/Frame uses this exact empty image frame. Preserve its
        // local upload background: the normal wikitext CSS filter removes url().
        if (preg_match('~^<div class="([^"]+)" style="height:([0-9.]+(?:px|em|rem|vh|%));background-image:url\(\x27([^\x27]+)\x27\);"></div>$~D', trim($text), $match)
            && in_array('mp-frame-image', explode(' ', $match[1]), true)
            && self::isLocalUpload(html_entity_decode($match[3], ENT_QUOTES))) {
            $text = Html::element('div', [
                'class' => $match[1],
                'style' => 'height:' . $match[2] . ';background-image:url("' . html_entity_decode($match[3], ENT_QUOTES) . '");',
            ]);
            return ['text' => $text, 'noparse' => true, 'isHTML' => true];
        }
        // Keep literal wiki syntax and allowed HTML, without bypassing MediaWiki's HTML policy.
        return ['text' => Sanitizer::removeSomeTags($text), 'noparse' => true, 'isHTML' => true];
    }

    private static function isLocalUpload(string $url): bool {
        if (preg_match('~[\x00-\x20\x22\x27()<>\\\\]~', $url)) {
            return false;
        }
        $server = parse_url($GLOBALS['wgServer']);
        $parts = parse_url($url);
        return $parts !== false
            && ($parts['scheme'] ?? $server['scheme']) === $server['scheme']
            && ($parts['host'] ?? $server['host']) === $server['host']
            && ($parts['port'] ?? null) === ($server['port'] ?? null)
            && str_starts_with($parts['path'] ?? '', $GLOBALS['wgScriptPath'] . '/img_auth.php/');
    }
}
