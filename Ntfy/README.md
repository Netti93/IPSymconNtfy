# Ntfy

### 1. Konfiguration

| Name                                 | Typ       | Beschreibung                                                                            |
| :----------------------------------: | :-------: | :-------------------------------------------------------------------------------------: |
| Server URL                           | string    | URL des Ntfy-Servers                                                                    |
| Server benötigt Authentifizierung    | bool      | Aktivieren, wenn der Server / das Topic eine Authentifizierung voraussetzt              |
| Benutzername                         | string    | Benutzername auf Ntfy-Server                                                            |
| Passwort                             | string    | Passwort des Ntfy-Benutzers                                                             |
| Token statt Passwort verwenden       | bool      | Aktivieren, wenn statt Benutzername & Passwort ein Zugriffstoken verwendet werden soll  |
| Zugriffstoken                        | string    | App-Token zur Authentifizierung                                                         |

### 2. Funktionsreferenz

#### Nachricht senden
`boolean NTFY_SendMessage(integer $InstanzID, string $topic, string $message, string $title, integer $priority);`  
sendet eine Nachricht an das angegebene Topic. Die Parameter $title und $priority sind optional. Mehr Infos können in der [Dokumentation](https://docs.ntfy.sh/publish/) gefunden werden.

Beispiel:
```php
NTFY_SendMessage(12345, 'MeinSymcon', 'Eine Nachricht mit vielen Worten.', 'Der Titel', 3);
```

#### Nachricht als JSON senden
`boolean NTFY_SendMessageAsJson(integer $InstanzID, string $topic, array $content);`  
sendet eine Nachricht an das angegebene Topic im JSON-Format. Eine Liste der verfügbaren Parameter kann [hier](https://docs.ntfy.sh/publish/#publish-as-json) gefunden werden.
Das Format des $content Parameters muss ein string-indiziertes Array sein.

Beispiel:
```php
NTFY_SendMessageAsJson(12345, 'MeinSymcon', array("message" => "Eine Nachricht mit Tags", "title" => "Der Titel", "tags" => ["partying_face,tada"]));
```

#### Nachricht mit HTTP Headern senden
`boolean NTFY_SendMessageWithHeaders(integer $InstanzID, string $topic, string $message, array $headers);`  
sendet eine Nachricht an das angegebene Topic mit zusätzlichen Headern wie z.B. X-Title. Eine Liste der verfügbaren Parameter kann [hier](https://docs.ntfy.sh/publish/#list-of-all-parameters) gefunden werden.
Das Format des $headers Parameters muss ein string Array sein.

Beispiel:
```php
NTFY_SendMessageWithHeaders(12345, 'MeinSymcon', 'Eine sehr wichtige Nachricht mit Titel.', array("X-Title: Der Titel", "X-Priority: 5"));
```

#### Testnachricht senden
`boolen NTFY_SendTestMessage(integer $InstanzID, string $topic);`  
sendet eine vordefinierte Testnachricht an das angegebene Topic. Diese Funktion wird auf der Konfigurationsseite vom Button "Sende Testnachricht" verwendet.