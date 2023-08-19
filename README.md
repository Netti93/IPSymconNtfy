# Ntfy

[![IPS-Version](https://img.shields.io/badge/Symcon_Version-5.3+-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Code](https://img.shields.io/badge/Code-PHP-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Vorraussetzungen](#2-vorraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Konfiguration](#5-konfiguration)
6. [PHP-Befehlsreferenz](#6-php-befehlsreferenz)
7. [Anhang](#7-anhang)
8. [Versionshistorie](#8-versionshistorie)

### 1. Funktionsumfang

* Das Modul ermöglicht das Senden von Benachrichtigungen via [Ntfy](https://ntfy.sh).

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.3  
(es wurde meinerseits jedoch nur ab Version 6.4 aufwärts getestet/genutzt)
- ein von der IP-Symcon Instanz aus erreichbarer Ntfy-Server (selbstgehosted oder offiziell)
- ggf. Zugangsdaten / Access Token

### 3. Software-Installation

* Über den Module Store das 'Ntfy'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen:  
https://github.com/Netti93/IPSymconNtfy

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'Ntfy'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

### 5. Konfiguration

siehe [Modulbeschreibung](Ntfy/README.md#1-konfiguration)

### 6. PHP-Befehlsreferenz

siehe [Modulbeschreibung](Ntfy/README.md#2-funktionsreferenz)

### 7. Anhang

GUIDs

- Bibliothek: `{0C9C5A90-D068-C4F9-7AF3-3006D71C1899}`
- Module:
  - Ntfy: `{0D3A1E8F-EF9A-78E1-7279-8B034F033765}`

Verweise:
- https://ntfy.sh

### 8. Versionshistorie

- 1.0 @ 18.08.2023
  - Initiale Version
