# Agent Workflow for wp-restatify-forms

Shared baseline:
- https://github.com/carryman1979/wp_restatify-shared/blob/main/docs/ai/AGENTS.shared.md

Repo-specific additions:
- Preserve trigger compatibility for #restatify-form-{formId} and absolute URL + hash variants.
- Keep Gutenberg link picker compatibility via both url and permalink fields.
- Keep submit security checks intact (nonce, honeypot, captcha behavior).
- Do not mask mail delivery failures as success.

Commands:
- npm run test:unit:js -- --runInBand
- npm run test:unit:js:watch
- composer run test:unit:php
