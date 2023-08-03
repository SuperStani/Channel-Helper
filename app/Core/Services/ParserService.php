<?php


namespace App\Core\Services;

use DOMDocument;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;


class ParserService
{
    public static function validateHTML($html): bool
    {
        $dom = new DOMDocument();

        // Suppress errors and warnings during parsing
        libxml_use_internal_errors(true);

        // Load the HTML string into the DOMDocument
        $dom->loadHTML($html);

        // Retrieve any parsing errors
        $errors = libxml_get_errors();

        // Clear the error buffer
        libxml_clear_errors();

        // Check for any parsing errors
        if (count($errors) === 0) {
            return $html; // HTML is valid
        } else {
            return false; // HTML is not valid
        }
    }

    public static function validateMarkdownToHTML($text): string|bool
    {
        try {
            $converter = new CommonMarkConverter();
            return $converter->convert($text);
        } catch (CommonMarkException $e) {
            return false;
        }
    }
}