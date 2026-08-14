# Form Sentinel 0.3.0-rc pilot checklist

## RC checks

- Follow [UPGRADE-TEST.md](UPGRADE-TEST.md) on one disposable staging site.
- Run the technical email test as an administrator. Confirm it sends to the current administrator and creates no journal entry.
- Navigate the administration page using only the keyboard and test it at a narrow mobile width.
- If multisite is used, verify an event on one subsite is not visible on another.
- Record each environment in [COMPATIBILITY-MATRIX.md](COMPATIBILITY-MATRIX.md).

## Beta checks

- Confirm the configuration panel reports an external `From` domain and a missing mail tag on a disposable CF7 form.
- Confirm intentional unused fields are reported as informational only.
- Export a filtered CSV as an administrator and check that only the selected form/status is present.
- Exclude a test field in Form Sentinel settings, submit again, and confirm it is absent from the journal.
- Delete a disposable event from the journal.
- In WordPress Tools → Export/Erase Personal Data, run a request using an email present in a test submission.

## Pilot identity

- Site name:
- Site type:
- Environment: staging / production pilot
- WordPress version:
- PHP version:
- Contact Form 7 version:
- Mail/SMTP plugin:
- Mail provider:
- Tester:
- Test date:

## Mandatory scenarios

| Scenario | Expected result | Actual result | Pass? |
|---|---|---|---|
| Normal CF7 submission | Event recorded as `accepted` | | |
| Broken mail configuration | Event recorded as `failed` with an understandable error | | |
| CF7 demo mode or `skip_mail` | Event recorded as `skipped` | | |
| Sensitive field such as `api_token` | Value stored as `[masked]` | | |
| Subscriber/editor opens admin URL | Access denied | | |
| Retention set to a short test period | Old events removed by cleanup | | |

## Product feedback

1. Could you understand the difference between “accepted” and “delivered”?
2. Did the journal help find or confirm a real problem?
3. Which information was missing from the event detail?
4. Which filter or action would save you the most time?
5. Which form plugin should be supported next?
6. Would you pay for retry, remote alerts, or an agency dashboard? Which one?
7. What wording was confusing?

## Bug report minimum

- Exact steps to reproduce.
- Expected and actual statuses.
- Screenshot with personal data hidden.
- Relevant WordPress/CF7/mail-plugin versions.
- Whether the issue reproduces with only CF7 and Form Sentinel active.
- Related PHP or mail error log, with secrets removed.
