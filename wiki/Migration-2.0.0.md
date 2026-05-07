# Migration 2.0.0 (Shared + Branding)

## Zielbild

- Produktname: Restatify-Forms
- Plugin-Slug: wp_restatify-forms
- Website: https://www.restatify.tech
- Shared-Package: wp_restatify-shared (public, GPL-2.0-or-later)

## Release-Entscheidungen

- Major-Release: 2.0.0
- Shared-Package wird versionsgenau geladen
- Teilen nur bei exakt gleicher Shared-Version

## Kompatibilitaetsregel

1. Plugin prueft beim Boot, welche Shared-Version es benoetigt.
2. Ist genau diese Version bereits geladen, wird sie wiederverwendet.
3. Ist sie nicht geladen, laedt das Plugin seine eigene mitgelieferte Version.
4. Unterschiedliche Versionen koennen parallel laufen, ohne sich zu ueberschreiben.

## Datenhaltung

- Templates und Form-Konfiguration bleiben plugin-spezifisch.
- Keine Verlagerung der Form-Daten in die Shared-Komponente.

## Operator-Checkliste

1. Vor Update Datenbank-Backup erstellen.
2. Update auf 2.0.0 einspielen.
3. Form-Editor, Versandmodus Mail/Endpoint und Captcha-Einstellungen pruefen.
4. Trigger-Link und Gutenberg-Block pruefen.
