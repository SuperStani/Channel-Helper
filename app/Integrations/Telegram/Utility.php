<?php


namespace App\Integrations\Telegram;


class Utility
{
    public static function buildKeyboardFromRawText(string $text): array
    {
        $menu = [];
        $rows = explode("\n", $text);
        foreach($rows as $key => $row) {
            $cols = explode("&&", $row);
            foreach($cols as $col) {
                $e = explode("-", $col);
                $menu[$key][] = ["text" => trim($e[0] ?? ''), "url" => trim($e[1] ?? '')];
            }
        }
        return $menu;
    }
}