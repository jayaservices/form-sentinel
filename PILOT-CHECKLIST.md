# Form Sentinel 0.1.0-alpha pilot checklist

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
