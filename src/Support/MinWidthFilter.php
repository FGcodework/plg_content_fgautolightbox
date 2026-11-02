<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.fgautolightbox
 * @copyright   Copyright (C) 2026 FG. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace FG\Plugin\Content\Fgautolightbox\Support;

\defined('_JEXEC') or die;

/**
 * Voliteľný filter podľa SKUTOČNEJ šírky súboru na disku (nie podľa
 * zobrazenej veľkosti v článku). Ikony, odrážky a malé dekorácie tak
 * lightbox nedostanú, kým veľké fotky zobrazené v malom rozmere áno.
 *
 * Fail-open: ak sa šírku nedá zistiť (cudzí web, neexistujúci súbor, nečitateľný
 * formát), obrázok sa NEfiltruje - radšej lightbox navyše než chýbajúci.
 * SVG sa nemeria (nemá pevný rastrový rozmer) a prechádza.
 */
final class MinWidthFilter
{
    /** @var array<string, int|null> Cache v rámci requestu: cesta => šírka */
    private array $widthCache = [];

    /**
     * @param int    $minWidth   0 = filter vypnutý
     * @param string $rootPath   Absolútna cesta ku koreňu webu (JPATH_ROOT)
     * @param string $rootUri    Podadresár inštalácie v URL ('' alebo '/sub')
     * @param string $host       Hostiteľ tohto webu (pre rozpoznanie vlastných absolútnych URL)
     */
    public function __construct(
        private readonly int $minWidth,
        private readonly string $rootPath,
        private readonly string $rootUri = '',
        private readonly string $host = '',
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->minWidth > 0;
    }

    public function passes(string $src): bool
    {
        if ($this->minWidth <= 0) {
            return true;
        }

        $relative = $this->resolveLocalPath($src);

        if ($relative === null) {
            return true;
        }

        if (!\array_key_exists($relative, $this->widthCache)) {
            $this->widthCache[$relative] = $this->measure($relative);
        }

        $width = $this->widthCache[$relative];

        return $width === null || $width >= $this->minWidth;
    }

    private function measure(string $relative): ?int
    {
        if (strtolower(pathinfo($relative, PATHINFO_EXTENSION)) === 'svg') {
            return null;
        }

        $file = rtrim($this->rootPath, '/\\') . '/' . $relative;

        if (!is_file($file)) {
            return null;
        }

        $size = @getimagesize($file);

        return \is_array($size) && $size[0] > 0 ? (int) $size[0] : null;
    }

    /**
     * URL -> relatívna cesta voči koreňu webu. Vstup pochádza z obsahu
     * článku, preto je to nedôveryhodný vstup: cudzie hostiteľa a akýkoľvek
     * pokus o ".." (aj s opačnými lomkami) sa odmietnu (null = nemerať).
     */
    private function resolveLocalPath(string $src): ?string
    {
        $path = str_replace('\\', '/', $src);

        if (preg_match('#^(https?:)?//#i', $path)) {
            $host = parse_url($path, PHP_URL_HOST);

            if (!\is_string($host) || $this->host === '' || strcasecmp($host, $this->host) !== 0) {
                return null;
            }

            $path = (string) parse_url($path, PHP_URL_PATH);
        } elseif (preg_match('#^[a-z][a-z0-9+.-]*:#i', $path)) {
            return null; // iná schéma (data:, javascript:, ...)
        }

        $path = explode('#', explode('?', $path)[0])[0];
        $path = rawurldecode($path);

        if ($this->rootUri !== '' && str_starts_with($path, $this->rootUri . '/')) {
            $path = substr($path, \strlen($this->rootUri));
        }

        $path = ltrim($path, '/');

        if ($path === '' || \in_array('..', explode('/', $path), true) || str_contains($path, "\0")) {
            return null;
        }

        return $path;
    }
}
