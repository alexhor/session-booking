<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

function __matchEmailTemplateFunctionArguments($paramsString, $variableArray) {
    $startChar = false;
    $endChar = false;
    $depth = 0;
    $argString = "";

    $argArray = [];

    foreach (str_split($paramsString) as $char) {
        // A new argument has started
        if (false === $endChar) {
            // Skip spaces and commas
            if (" " == $char || "," == $char) continue;
            // Set start and end char
            switch ($char) {
                case "$":
                    $endChar = ",";
                    $argString = "$";
                    break;
                case "[":
                    $endChar = "]";
                    $argString = "[";
                    break;
                default:
                    $endChar = $char;
            }
            $startChar = $char;
        }
        // Still reading the previous argument
        else {
            // End of argument reached
            if ($endChar == $char) {
                if (0 == $depth) {
                    if ("]" == $char) $argString .= "]";
                    $argArray[] = $argString;
                    $startChar = false;
                    $endChar = false;
                    $argString = "";
                    continue;
                }
                else {
                    $depth--;
                }
            }
            else if ($startChar == $char) {
                $depth++;
            }

            $argString .= $char;
        }
    }
    // Add leftover argument
    if ("" != $argString) $argArray[] = $argString;

    foreach ($argArray as &$argument) {
        switch(substr($argument, 0, 1)) {
            case "$":
                $varName = substr($argument, 1);
                if (array_key_exists($varName, $variableArray)) {
                    $argument = $variableArray[$varName];
                }
                break;
            case "[":
                $argument = __matchEmailTemplateFunctionArguments(substr($argument, 1, -1), $variableArray);
                break;
        }
    }

    return $argArray;
}

function enrichEmailTempate($templateHtml, $variableArray)
{
    if (preg_match_all("/\{\{ ([^}]+) \}\}/i", $templateHtml, $matches)) {
        $matches = array_unique($matches[1]);
        
        foreach ($matches as $key) {
            $value = null;
            
            if (preg_match('/(\w+)\s*\(\s*([^)]*)\s*\)/', $key, $functionMatches)) {
                $functionName = $functionMatches[1];
                if (in_array($functionName, service("settings")->get("Email.templatesAllowedFunctionCalls")) && function_exists($functionName)) {
                    $args = __matchEmailTemplateFunctionArguments($functionMatches[2], $variableArray);
                    $value = call_user_func_array($functionName, $args);
                }
            }

            if (null == $value) $value = array_key_exists($key, $variableArray) ? esc( $variableArray[$key] ) : "";

            $templateHtml = str_replace("{{ " . $key . " }}", $value, $templateHtml);
        }
    }

    return $templateHtml;
}


// Prints all the html entries needed for Vite

function vite(string $entry): string
{
    return "\n" . jsTag($entry)
        . "\n" . jsPreloadImports($entry)
        . "\n" . cssTag($entry);
}

function isDev(string $entry): bool
{
    return false;
    return array_key_exists('CI_ENVIRONMENT', $_ENV) && $_ENV['CI_ENVIRONMENT'] === 'development';
}


// Helpers to print tags

function jsTag(string $entry): string
{
    $VITE_HOST = base_url('vite');
    $url = isDev($entry)
        ? $VITE_HOST . '/' . $entry
        : assetUrl($entry);

    if (!$url) {
        return '';
    }
    if (isDev($entry)) {
        return '<script type="module" src="' . $VITE_HOST . '/@vite/client"></script>' . "\n"
            . '<script type="module" src="' . $url . '"></script>';
    }
    return '<script type="module" src="' . $url . '"></script>';
}

function jsPreloadImports(string $entry): string
{
    if (isDev($entry)) {
        return '';
    }

    $res = '';
    foreach (importsUrls($entry) as $url) {
        $res .= '<link rel="modulepreload" href="'
            . $url
            . '">';
    }
    return $res;
}

function cssTag(string $entry): string
{
    // not needed on dev, it's inject by Vite
    if (isDev($entry)) {
        return '';
    }

    $tags = '';
    foreach (cssUrls($entry) as $url) {
        $tags .= '<link rel="stylesheet" href="'
            . $url
            . '">';
    }
    return $tags;
}

// Functions to locate vite files

function ViteManifest()
{
    $manifest = file_get_contents(__DIR__ . '/../public/dist/.vite/manifest.json');
    return json_decode($manifest, true);
}

function assetUrl(string $entry)
{
    $manifest = ViteManifest();

    return isset($manifest[$entry])
        ? '/dist/' . $manifest[$entry]['file']
        : '';
}

function importsUrls(string $entry)
{
    $urls = [];
    $manifest = ViteManifest();

    if (!empty($manifest[$entry]['imports'])) {
        foreach ($manifest[$entry]['imports'] as $imports) {
            $urls[] = '/dist/' . $manifest[$imports]['file'];
        }
    }
    return $urls;
}

function cssUrls(string $entry): array
{
    $urls = [];
    $manifest = ViteManifest();

    if (!empty($manifest[$entry]['css'])) {
        foreach ($manifest[$entry]['css'] as $file) {
            $urls[] = '/dist/' . $file;
        }
    }
    return $urls;
}
