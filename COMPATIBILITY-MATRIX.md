# Form Sentinel 0.3.0-rc compatibility matrix

Each pilot must record exact versions, result and any workaround. A blank cell means **not yet tested**, not compatible.

| Context | Target result | Tested version | Result | Notes |
|---|---|---|---|---|
| Native WordPress mail | Capture and technical test work | | | |
| WP Mail SMTP | Capture and technical test work | | | |
| Mailjet | Capture and technical test work | | | |
| Brevo | Capture and technical test work | | | |
| Maintenance page | CF7 status remains understandable | | | |
| Subsite in multisite | Isolated journal and settings per site | | | |

## Multisite decision

Form Sentinel supports **site-scoped multisite operation**. A network activation creates one event table per existing subsite using that site's database prefix; new subsites are provisioned automatically. Events, retention settings and administrator access stay inside each subsite. There is no network-wide journal, export or reporting screen in the free version.
