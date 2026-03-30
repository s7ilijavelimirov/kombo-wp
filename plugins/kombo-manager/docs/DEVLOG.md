## [0.1.0] — 26.03.2026 — Ilija / s7codedesign

### Phase 1 — Scaffold

- Added: kombo-manager.php (plugin header, constants, bootstrap)
- Added: class-autoloader.php (PSR-4 style, kebab-case mapping)
- Added: class-kombo-manager.php (singleton main class)
- Added: class-activator.php (stub)
- Added: class-deactivator.php (stub)
- Added: class-i18n.php (Polylang string registration, text domain)
- Added: .cursor/ setup (AGENTS.md, Rules, Skills)
- Changed: Autoloader simplified (removed kebab conversion, then restored)
- Changed: I18n wired into main class via plugins_loaded hooks

### Documentation — Requirements Update

- Changed: Updated `sastanak-notes.md` with User Profile, Order — Per Delivery Date, Data Migration, Development Modules, and Open Questions sections.
- Changed: Expanded `Subscriptions` and `Registration & Activation` requirements in `sastanak-notes.md`.
- Changed: Updated `AGENTS.md` mandatory-read description for `sastanak-notes.md` to the new fully updated requirement note.

---

Rule for future entries:

- New version number only when Phase is completed or major feature shipped
- Same version = append under existing ## block, new ### section
- Never duplicate the version header for the same day

