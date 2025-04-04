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

function enrichEmailTempate($templateHtml, $variableArray)
{
    if (preg_match_all("/\{\{ (\S+) \}\}/i", $templateHtml, $matches)) {
        $matches = array_unique($matches[1]);
        
        foreach ($matches as $key) {
            $value = null;
            
            if (preg_match('/(\w+)\s*\(\s*([^)]*)\s*\)/', $key, $functionMatches)) {
                $functionName = $functionMatches[1];
                if (in_array($functionName, service("settings")->get("Email.templatesAllowedFunctionCalls")) && function_exists($functionName)) {
                    $args = array_map(function($item) {
                        return trim(trim(trim($item, " "), "'"), '"');
                    }, explode(',', $functionMatches[2]));
                    
                    foreach ($args as &$argument) {
                        if (preg_match('/\$(\w+)/', $argument, $argMatches) && array_key_exists($argMatches[1], $variableArray)) {
                            $argument = $variableArray[$argMatches[1]];
                        }
                    }
                    
                    $value = call_user_func_array($functionName, $args);
                }
            }

            if (null == $value) $value = array_key_exists($key, $variableArray) ? esc( $variableArray[$key] ) : "";

            $templateHtml = str_replace("{{ " . $key . " }}", $value, $templateHtml);
        }
    }

    return $templateHtml;
}
