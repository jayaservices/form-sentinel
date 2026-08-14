# Upgrade test: 0.1.0-alpha → 0.3.0-rc

Run this test on a disposable staging copy before updating a pilot site.

1. With `0.1.0-alpha`, submit a CF7 form and retain its event ID and status.
2. Update directly to `0.3.0-rc`; do not deactivate or uninstall the plugin.
3. Open **Form Sentinel** and verify the old event remains visible with its payload, status, recipient and timestamp.
4. Submit a new CF7 form. Verify the new event has a status timeline.
5. Confirm the existing retention setting is unchanged.
6. Run the technical email test. It must not create a customer submission in the journal.
7. Deactivate then reactivate Form Sentinel. Existing events must remain visible.
8. Only on a disposable instance, uninstall the plugin and verify its table and options are removed.

Expected migration behavior: the database keeps the same event table and only adds the `timeline` column. Existing events are not altered; they have no retroactive timeline.
