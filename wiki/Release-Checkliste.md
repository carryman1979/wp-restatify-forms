# Release-Checkliste

## Vor dem Release

1. npm install (falls noch nicht vorhanden)
2. npm run build
3. Smoke-Test im WordPress-Backend:
   - Formular erstellen / speichern
   - Popup oeffnet per Trigger
   - Testversand funktioniert

## Paket bauen

- npm run package
- Ausgabe: release/wp-restatify-forms-{version}.zip

## Kurzpruefung ZIP

1. ZIP enthaelt build/block/index.js und build/block/block.json
2. ZIP enthaelt Hauptdatei wp-restatify-forms.php
3. ZIP enthaelt includes/, assets/, build/
4. ZIP enthaelt keine node_modules/

## GitHub

1. Commit auf main
2. Tag setzen (optional)
3. Release mit ZIP anlegen (optional)
