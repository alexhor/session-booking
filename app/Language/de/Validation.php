<?php

return [
    'session_booking' => [
        'not_found' => 'Es wurde keine Session Buchung gefunden',
        'deleting_failed' => 'Löschen der Session Buchung ist fehlgeschlagen',
        'taken' => 'Session ist bereits von einer anderen Person belegt',
        'created' => 'Session gebucht',
        'creating_failed' => "Session konnte nicht gebucht werden",
        'deleted' => 'Session Buchung wurde gelöscht',

        'start_time' => [
            'label' => 'Startzeit',
            'required' => 'Uhrzeit der Session Buchung fehlt',
            'integer' => 'Uhrzeit Session Buchung time ist ungültig',
        ]
    ],
    'user' => [
        'not_found' => 'Es wurde kein Benutzer gefunden',
        'created' => 'Benutzer erstellt',
        'creating_failed' => 'Erstellen des Benutzers ist fehlgeschlagen',
        'updated' => 'Benutzerdaten wurden aktualisiert',
        'updating_failed' => 'Aktualisierung der Benutzerdaten ist fehlgeschlagen',
        'deleted' => 'Benutzer wurde gelöscht',
        'deleting_failed' => 'Löschen des Benutzers ist fehlgeschlagen',
        'add_to_group' => 'Benutzer wurde zur Gruppe {group} hinzugefügt',
        'add_to_group_failed' => 'Benutzer zur Gruppe {group} hinzuzufügen ist fehlgeschlagen',
        'remove_from_group' => 'Benutzer wurde aus der Gruppe {group} entfernt',
        'remove_from_group_failed' => 'Benutzer aus der Gruppe {group} zu entfernen ist fehlgeschlagen',
        'id' => [
            'label' => 'Benutzer ID',
            'required' => 'ID des Benutzers fehlt',
            'integer' => 'ID des Benutzers ist ungültig',
            'not_found' => 'Es wurde kein Benutzer gefundend',
        ],
        'email' => [
            'label' => 'E-Mail',
            'required' => 'E-Mail Adresse fehlt',
            'valid' => 'E-Mail Adresse ist ungültig',
            'not_found' => 'Es konnte kein Benutzer mit dieser E-Mail Adresse gefunden werden',
            'taken' => 'Diese E-Mail Adresse wird bereits von einem anderen Account benutzt',
            'too_long' => 'E-Mail Adresse ist zu lang',
        ],
        'firstname' => [
            'label' => 'Vorname',
            'required' => 'Vorname fehlt',
        ],
        'lastname' => [
            'label' => 'Nachname',
            'required' => 'Nachname fehlt',
        ],
    ],
    'user_authentication' => [
        'login' => 'Login erfolgreich',
        'logout' => 'Logout erfolgreich',
        'token' => [
            'label' => 'Authentifizierungs Token',
            'required' => 'Authentifizierungs Token fehlt',
            'alpha_numeric' => 'Format des Authentifizierungs Tokens ist ungültig',
            'exact_length' => 'Länge des Authentifizierungs Tokens ist ungültig',
            'invalid_or_expired' => 'Ungültiger oder abgelaufener Authentifizierungs Token',
            'send_by_email' => 'E-Mail mit Token wurde versendet',
            'too_may_requests' => 'Token wurden zu oft angefragt. Bitte in einer Minute erneut versuchen',
        ],
    ],
    'setting_invalid_value' => 'Ein ungültiger Wert wurde für die Einstellung übergeben',
    'setting_saving_failed' => 'Speichern der Einstellung ist fehlgeschlagen',
];
