# Form Sentinel

Form Sentinel prevents silent lead loss on WordPress sites. The first release targets Contact Form 7 and records three distinct facts:

1. WordPress received the form submission.
2. The related email was accepted, rejected, or skipped at application level.
3. The submitted lead remains available locally for a limited retention period.

> An accepted email is not proof of inbox delivery. It means WordPress handed the message to its configured mail transport without an immediate error.

## Current release: 0.2.0-beta

This private alpha is suitable for controlled testing on staging sites and selected low-risk production sites. It provides:

- Contact Form 7 integration;
- local submission journal;
- `wp_mail_succeeded` and `wp_mail_failed` tracking;
- CF7 skipped-mail detection;
- dashboard counts and filters;
- event detail;
- common sensitive-field masking;
- automatic deletion after 30 days by default.

The beta adds CF7 configuration checks, a per-submission status timeline, CSV export, selective deletion, field exclusions, and WordPress privacy export/erasure integration.

It does not yet provide SMTP delivery confirmation, retry, remote monitoring, or support for other form plugins.

Uninstalling the plugin permanently removes its event table and settings. Export anything you need before uninstalling.

## Installation

1. Install and activate Contact Form 7.
2. Zip the `form-sentinel` directory or use the provided release ZIP.
3. In WordPress, open Plugins → Add Plugin → Upload Plugin.
4. Activate Form Sentinel.
5. Send a test submission and inspect the Form Sentinel menu.

## Minimum environment

- WordPress 6.4+
- PHP 8.0+
- Contact Form 7

## Test protocol for each pilot site

- Submit a normal form and confirm that it appears as `accepted`.
- Add an invalid SMTP configuration and confirm a `failed` event.
- Enable CF7 demo mode or `skip_mail` and confirm a `skipped` event.
- Confirm sensitive fields are masked.
- Confirm only administrators can access the journal.
- Confirm the displayed recipient and page URL are correct.
- Review the CF7 configuration checks. Confirm that intentional unused fields are only informational.
- Export a filtered CSV and delete a disposable test record.
- Test the WordPress personal-data export and erasure tools with the email used in a test submission.
- Deactivate and reactivate the plugin and confirm existing events remain available.
- Uninstall only on a disposable test instance and confirm the table is removed.

## Feedback to collect

For each tester, capture: site type, CF7 version, mail plugin/provider, result, confusing wording, missing filter, false status, and the one feature they would pay for.
