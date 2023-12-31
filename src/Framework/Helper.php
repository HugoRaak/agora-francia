<?php

declare(strict_types=1);

namespace Framework;

use Psr\Container\ContainerInterface;

class Helper
{
    /**
     * generate a slug from a string
     *
     */
    public static function clearStr(string $string): string
    {
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', str_replace("'", ' ', $string));
        $string = is_string($string) ? $string : '';
        $string = preg_replace('/[^A-Za-z0-9\- ]/', '', $string) ?? '';
        $string = str_replace("039", ' ', str_replace("ccedil", 'c', $string));
        $string = preg_replace('/ +/', ' ', $string) ?? '';
        $string = preg_replace('/([eE]acut|[gG]rave|[cC]irc|[uU]ml|sect|pound|quot|deg)/u', '', $string) ?? '';
        return strtolower(trim($string, '-'));
    }

    /**
     * clear a string
     *
     */
    public static function createSlug(string $title, int $limit = 80): string
    {
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        $slug = is_string($slug) ? $slug : '';
        $slug = preg_replace('/[^A-Za-z0-9\ ]/', '', $slug) ?? '';
        $slug = preg_replace('/ +/', '-', $slug) ?? '';
        $slug = preg_replace('/([eE]acut|[gG]rave|[cC]irc|[uU]ml|sect|pound|quot|deg)/u', '', $slug) ?? '';
        $slug = strtolower(trim($slug, '-'));
        if (strlen($slug) > $limit) {
            // phpstan-ignore-next-line
            $lastSpace = mb_strpos($slug, '-', $limit);
            $lastSpace = is_int($lastSpace) ? $lastSpace : $limit;
            return mb_substr($slug, 0, $lastSpace);
        }
        return $slug;
    }

    /**
     * return a container definition if exists else default
     */
    public static function containerGetOrDefault(
        ContainerInterface $container,
        string $id,
        mixed $default = null
    ): mixed {
        return $container->has($id) ? $container->get($id) : $default;
    }
}
