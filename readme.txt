=== Form Sentinel ===
Contributors: yassineboumehdi
Tags: contact form 7, email, lead, logging, monitoring
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 0.1.0-alpha
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Record Contact Form 7 submissions and understand whether WordPress accepted, rejected, or skipped the related email.

== Description ==

Form Sentinel helps site owners detect lost form leads. The alpha version records Contact Form 7 submissions locally, masks common sensitive fields, and reports the technical email status.

An "accepted" status only means WordPress accepted the message for sending. It does not guarantee delivery to the recipient's inbox.

== Installation ==

1. Install and activate Contact Form 7.
2. Upload the `form-sentinel` folder to `/wp-content/plugins/` or install the ZIP.
3. Activate Form Sentinel.
4. Submit a Contact Form 7 form.
5. Open Form Sentinel in the WordPress administration menu.

== Changelog ==

= 0.1.0-alpha =
* First private alpha.
* Record Contact Form 7 submissions.
* Track accepted, failed, skipped, and received email states.
* Mask common sensitive field names.
* Add a filterable administration journal and event detail.
* Add configurable automatic retention.
