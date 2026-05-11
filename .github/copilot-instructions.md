# Copilot Instructions for wp-restatify-forms

Shared baseline:
- https://github.com/carryman1979/wp_restatify-shared/blob/main/docs/ai/copilot-instructions.shared.md

Repo-specific requirements:
- Do not break #restatify-form-{formId} triggers.
- Keep absolute URL + hash trigger support.
- Keep link picker entries compatible via url and permalink fields.
- Do not silently swallow mail send failures.

Required checks:
- npm run test:unit:js -- --runInBand
- composer run test:unit:php
- If link picker logic changed, validate both triggers in editor flow:
  - #restatify-booking
  - #restatify-form-kontaktformular
