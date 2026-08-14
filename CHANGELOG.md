# Changelog

All notable changes to Form Sentinel will be documented here.

## [0.3.0-rc] - 2026-08-14

### Added

- Administrator-only technical email test that does not create a CF7 submission or customer lead.
- Site-scoped multisite provisioning for existing and newly created subsites.
- Upgrade, compatibility, accessibility, and release-quality checklists.
- Complete English translation catalog and French translation source catalog, ready for translator review.

## [0.2.0-beta] - 2026-08-13

### Added

- CF7 diagnostics for empty or external `From` domains, undefined mail tags, unused form fields, and demo mode.
- Per-submission technical status timeline.
- Filtered CSV export and selected-record deletion for administrators.
- Configurable field exclusions in addition to automatic sensitive-field masking.
- Integration with WordPress personal-data export and erasure requests.

### Changed

- Database schema upgrade adds a timeline field while retaining existing events.

## [0.1.0-alpha] - 2026-08-03

### Added

- Private Contact Form 7 pilot release.
- Local submission journal and event detail.
- WordPress mail accepted/failed tracking.
- Contact Form 7 skipped-email tracking.
- Masking for common sensitive field names.
- Status and form filters.
- Configurable daily data retention.
- Dependency and capability checks.
