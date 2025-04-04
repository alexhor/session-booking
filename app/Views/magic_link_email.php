<?php
$langContext = 'lang:' . service('request')->getLocale();
$template = service('settings')->get('Email.magicLinkTemplateHtml');

echo enrichEmailTempate($template, [
    'ipAddress' => $ipAddress,
    'userAgent' => $userAgent,
    'date' => $date,
    'token' => $token,
]);
