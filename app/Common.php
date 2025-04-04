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
