# Form Sentinel release roadmap

## Delivery principle

Every version must be installable, testable, reversible, and useful on its own. Features move only after evidence from pilot sites, support requests, or usage data.

| Version | Target | Product outcome | Exit condition |
|---|---:|---|---|
| 0.1.0-alpha | Days 1–3 | Never lose a CF7 submission silently | 3 pilot sites log normal, failed, and skipped sends correctly |
| 0.2.0-beta | Days 4–7 | Explain the most common CF7 configuration risks | Diagnostics, privacy exporter/eraser, bulk deletion, CSV export, no critical pilot bug |
| 0.3.0-rc | Days 8–12 | Be safe and understandable for unknown sites | Compatibility matrix, accessibility review, translations, upgrade path, 10 pilot sites |
| 1.0.0 | Days 13–20 | Public WordPress.org release | Plugin Check clean, docs/assets ready, support process and telemetry-free feedback flow ready |
| 1.1.0 | Feedback-driven | Fix the highest-frequency user pain | Selected after 30–50 active installs or 15 structured interviews |
| 1.2.0 | Feedback-driven | Add the most requested free integration or diagnostic | Based on adoption and support evidence |
| Pro 1.0 | After validation | Monetize operational recovery and agency needs | Clear willingness to pay from at least 5 users/agencies |

## 0.1.0-alpha — private pilot

Included:

- capture Contact Form 7 submissions;
- record accepted, failed, skipped, and received states;
- local lead journal and details;
- masking for common sensitive field names;
- configurable retention and daily cleanup;
- explicit wording that technical acceptance is not inbox delivery.

Not included: automated tests inside a WordPress environment, retry, scheduled tests, remote alerts, licensing, or additional form builders.

## 0.2.0-beta — trust and diagnosis

Delivered:

- diagnose invalid or external `From` domains;
- detect missing/unused CF7 mail tags;
- identify `skip_mail` and demo mode causes;
- WordPress privacy exporter and eraser;
- per-field exclusion settings;
- manual and bulk deletion;
- CSV export;
- clearer status timeline per submission.

## 0.3.0-rc — release quality

Delivered for pilot:

- test-send workflow without creating a real customer lead;
- site-scoped WordPress multisite support and documented behavior;
- upgrade/migration test protocol;
- keyboard and responsive admin review checklist;
- French translation source catalog and complete English source catalog;
- Plugin Check and PHPCS release commands;
- compatibility matrix for native mail, WP Mail SMTP, Mailjet, Brevo, and maintenance-page contexts.

## 1.0.0 — WordPress.org

- public free plugin with CF7 support;
- onboarding and contextual help;
- support and bug-report template;
- screenshots, icon, banner, FAQ, privacy disclosure;
- reproducible release package;
- no remote tracking by default.

## Free versus Pro boundary

Free protects and diagnoses leads locally. Pro should save operational time across sites or add ongoing services.

Pro candidates:

- automatic retry and fallback webhook;
- scheduled synthetic tests;
- Slack, Teams, Discord, SMS, and secondary-email alerts;
- WPForms, Elementor Forms, Gravity Forms, and Fluent Forms;
- agency dashboard and multisite reports;
- remote uptime/synthetic monitoring;
- longer history, reports, and white label.

## Metrics before changing the roadmap

- activation-to-first-recorded-submission rate;
- percentage of failed or skipped sends detected;
- pilot sites without false positives;
- time to understand and fix an issue;
- support requests per active install;
- top requested integration;
- users who explicitly ask for retry, alerts, or agency reporting;
- 7-day and 30-day active-install retention where measurable without invasive tracking.

## Initial pilot cohort

Start with 3–5 controlled sites, including Cabinet Envol if appropriate. Expand to 10 sites only after the three status paths are verified. Do not publish publicly until stored personal data, uninstall behavior, upgrades, and permissions have been reviewed.
