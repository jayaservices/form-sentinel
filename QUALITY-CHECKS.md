# Release-quality checks

Before a WordPress.org release, run these checks in a WordPress development environment:

```bash
wp plugin check form-sentinel
vendor/bin/phpcs --standard=WordPress form-sentinel
wp i18n make-pot form-sentinel form-sentinel/languages/form-sentinel.pot
```

Manual admin review:

- keyboard-only navigation through filters, table selection, deletion and mail test;
- visible focus indicator and readable text at 200% zoom;
- mobile review at 320px width;
- status labels remain understandable without relying only on colour;
- administrator-only routes reject lower roles.

The source English catalog is `languages/form-sentinel.pot`; compile `form-sentinel-fr_FR.po` into its `.mo` file before distribution.
