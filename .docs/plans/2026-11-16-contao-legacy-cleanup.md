# Contao-Kompatibilitätscode: Behebungsplan

> Umsetzung abgeschlossen; Prüfungen und Reviews stehen in `.docs/reports/2026-11-16-legacy-cleanup.md`.

**Ziel:** Nach dem Wegfall von Contao 4.13 ausschließlich nachweislich überflüssigen Kompatibilitätscode entfernen.

**Architektur:** Bestehende Integration beibehalten. Produktionsänderungen auf den unerreichbaren Verzeichnis-Fallback begrenzen; den Test-Autoloader erst nach Prüfung seiner tatsächlichen Nutzer entfernen.

**Technik:** Contao `^5.3`, PHP `^8.2`, Symfony `^6.4 || ^7.0`, PHPUnit 9.6.

**Anforderung:** CI reparieren und alte Versionsunterstützung entfernen; diesen Plan anschließend gezielt umsetzen, keine grundsätzliche Modernisierung.

## Grenzen und Prüfschwerpunkte

- Benutzerdefinierte öffentliche Verzeichnisse bleiben unterstützt, auch wenn sie `web` heißen.
- Explizite Encore-Ausgabepfade dürfen nicht überschrieben werden.
- Die Standardkonfiguration muss ohne installierte Contao-Versionsabfrage funktionieren.
- Tests müssen ohne selbst erzeugte globale Klassen-Aliase auskommen, bevor der Autoloader entfernt wird.
- Contao 5.3 benötigt weiterhin die Absicherung für fehlende moderne Layout-APIs.

## Aufgabe 1: Unerreichbaren Standardpfad-Fallback entfernen

**Dateien:** `src/DependencyInjection/EncoreExtension.php`, `tests/DependencyInjection/EncoreExtensionTest.php`.

**Schnittstelle:** `EncoreExtension::prepend(ContainerBuilder $container): void` bleibt unverändert.

- [x] Den bisherigen Mock-Test durch Tests mit einem echten `ContainerBuilder` ergänzen. Ohne Encore-Konfiguration und ohne `extra.public-dir` muss `output_path` exakt `%kernel.project_dir%/public/build` sein. Die Tests müssen auch den später geladenen Bundle-Parameter `entrypoints_jsons` prüfen.
- [x] Explizites `output_path=custom/assets` unverändert prüfen; vorhandene Fixtures für `extra.public-dir=web` und `extra.public-dir=public` weiter abdecken. `output_path=false` muss weiterhin die vorhandene Ausnahme auslösen.
- [x] Ausgangslauf dokumentieren. Ein gezielter Test gegen den alten Fallback erfordert keinen künstlichen Contao-4-Stub: Unter der neuen Mindestversion ist der Zweig bereits unerreichbar. Für seine Entfernung dienen die Verhaltenstests als Regressionsschutz.
- [x] Versionsvergleich gegen Contao 4.12 und den automatischen `web/build`-Zweig entfernen, Standardpfad direkt setzen; den dadurch unbenutzten Import `Composer\InstalledVersions` entfernen. Keine Änderung an der Verarbeitung von `extra.public-dir`.
- [x] `php vendor/bin/phpunit -c phpunit.xml.dist --filter EncoreExtensionTest` und danach die gesamte Suite, PHPStan und ECS ausführen. Änderungen separat committen.

## Aufgabe 2: Globalen Klassen-Autoloader in Tests prüfen und gegebenenfalls entfernen

**Dateien:** `tests/bootstrap.php`, bei nachgewiesenen direkten Nutzern die jeweils betroffenen Testdateien.

**Schnittstelle:** Composer-Autoload und der Rückgabewert des Bootstraps bleiben erhalten.

- [x] Ausgangslauf unter Contao 5.3 und der neuesten kompatiblen 5.x-Version sichern. Globale Klassennamen und Alias-Nutzung in Tests sowie tatsächlich verwendeten Test-Hilfsbibliotheken erfassen; den Kandidaten nicht pauschal als überflüssig behandeln.
- [x] In einem isolierten Arbeitsstand ausschließlich `$legacyLoader` und dessen Registrierung entfernen. Die gesamte Testsuite ausführen; konkrete fehlende Klassen samt Aufrufpfad dokumentieren.
- [x] Direkte Testnutzer auf existierende `Contao\…`-Klassen umstellen. Bei benötigter Alias-Erzeugung durch Drittanbieter keine Ersatz-Alias-Schicht einführen: Blocker dokumentieren und Entfernung zurückstellen.
- [x] Nur bei grüner vollständiger Suite unter beiden Contao-Versionen die Entfernung übernehmen. Separat committen; sonst bleibt der Autoloader bestehen.

## Bewusst beibehaltene Absicherungen

- `contao/dca/tl_layout.php`: Die fehlende Palette `modern` muss unter Contao 5.3 weiterhin abgefangen werden. Entfernung erst bei einer Mindestversion, die diese Palette garantiert.
- `tests/EventListener/InjectPageEntriesListenerTest.php` und `phpstan-baseline.neon`: `LayoutEvent` und `LayoutTemplate` fehlen unter Contao 5.3. Test-Ersatzklassen und passende Analyse-Ausnahmen bleiben deshalb erforderlich. Erst nach einer weiteren Anhebung der Contao-Mindestversion durch Tests mit den echten APIs ersetzen.
- Bestehende HTML5-/Hook-Integration und bereits veraltete Bundle-APIs nicht allein wegen des Wegfalls von Contao 4.13 entfernen. Das wäre eine eigenständige Verhaltens- beziehungsweise API-Änderung.

## Abnahme

- [x] PHPUnit ohne Deprecations unter PHP 8.2–8.5 mit Contao 5.3 sowie PHP 8.5 mit der neuesten kompatiblen Contao-Version.
- [x] PHPStan und ECS ohne Fehler; Coverage-Erzeugung und Coveralls-Dry-Run erfolgreich.
- [x] Diff enthält nur die nachweisbaren Bereinigungen und ihre Tests. Keine Einführung neuer Abstraktionen oder zusätzlicher Abhängigkeiten.
