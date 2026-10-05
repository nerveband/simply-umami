=== Simply Umami ===
Contributors: ashrafali, ancocodet
Tags: analytics, umami, privacy, statistics
Stable tag: 1.1.0
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Connect WordPress to your own Umami Analytics instance or Umami Cloud.

== Description ==

Simply Umami adds your configured Umami tracking script to public WordPress pages. Tracking is disabled until you configure a script URL and Website ID and explicitly enable it.

Features:

* All Umami v3.4 tracker attributes, including performance, separate automatic pageview control, distinct IDs, and fetch credentials.
* Optional exclusion of signed-in administrators.
* Optional comment submit-button click events, without sending comment text.
* Dashboard pageviews, visitors, visits, bounces, and active visitors using an optional API connection.
* Self-hosted username/password authentication or Umami Cloud API keys.
* Migration from Integrate Umami settings.
* Settings remain saved when the plugin is deactivated.
* Opt-in recorder.js loading for heatmaps and session replay, with an optional custom recorder URL.
* Native custom events, event data, identity/session data, and revenue events through the official Umami tracker APIs.
* WordPress filters for page/consent gating, dynamic visitor IDs, future tracker attributes, and recorder exclusions.

Based on [Integrate Umami by Ancocodet](https://github.com/Ancocodet/wp-umami). Original authorship and GPL licensing are retained. Source and development: [GitHub](https://github.com/nerveband/simply-umami).

=== External services and privacy ===

This plugin integrates with [Umami Analytics](https://umami.is/). When you enable tracking, visitors' browsers load executable JavaScript from the script URL you configure and send analytics to its collection endpoint or the collection host you explicitly select. Umami Cloud normally uses https://cloud.umami.is/script.js. Self-hosted installations use your own server.

The tracker sends page URLs, page titles, referrers, browser language, screen dimensions, and configured event data. The collection server receives visitors' network requests. Exclude Search and Exclude Hash omit query strings and URL fragments from tracked URLs. A Before Send function, which you must supply separately on your site, can further modify or suppress tracking.

Performance collection and fixed or dynamic distinct IDs are optional. Distinct IDs can identify visitors across sessions; do not use names, email addresses, or patient identifiers. Fetch Credentials defaults to Omit; selecting Include or Same Origin changes the browser credentials sent with tracker requests.

If you explicitly enable the recorder, visitors also load recorder.js and request recording configuration from the collection server. Depending on the Umami website settings, it can send DOM snapshots, page content, clicks, scrolling, input interactions, and full URLs. Umami controls replay/heatmap enablement, sampling, masking, blocking, and duration. Tracker query/hash exclusions and Before Send do not sanitize recorder payloads. Recording is disabled by default and must not be enabled on sensitive pages without an appropriate privacy and consent assessment.

When optional API credentials are configured, WordPress requests aggregate analytics for the configured Website ID. Self-hosted credentials are sent to your configured server for authentication. Umami Cloud API keys are sent to https://api.umami.is/v1. These credentials are saved in the WordPress options database and are not added to the public tracking script. Dashboard statistics are cached for five minutes.

The plugin does not provide a consent banner or guarantee legal compliance. Site owners are responsible for notices, consent where required, and appropriate service agreements. Do not put personal information, patient information, or other sensitive data in tracked URLs, titles, referrers, or events. Cookie-free analytics does not by itself establish HIPAA compliance.

Umami Cloud: [Privacy policy](https://umami.is/privacy) and [Terms](https://umami.is/terms). For self-hosted Umami, the operator's policies apply.

== Installation ==

1. Upload the simply-umami directory to /wp-content/plugins/, or upload the plugin ZIP through Plugins > Add New.
2. Activate Simply Umami.
3. Open Settings > Simply Umami.
4. Copy the Script URL and Website ID from your Umami website's tracking code.
5. Enable analytics and save changes.
6. Purge page caches, including LiteSpeed or CDN caches, after changing tracking configuration.
7. Test as a signed-out visitor. Ignore Admins can intentionally suppress your own signed-in visits.

API credentials are optional. Tracking does not require them. For Umami Cloud, enter an API key. For self-hosted Umami, enter your username and password. Use HTTPS for production tracking and API connections.

=== Heatmaps and session replay ===

1. Enable the desired heatmap/replay features in your Umami website settings. Configure sampling, strict masking, block selectors, and maximum duration there.
2. In WordPress, expand Heatmaps and Session Replay and explicitly opt in to Load Recorder.
3. Leave Recorder URL blank to use recorder.js beside your Script URL, or provide the exact recorder URL from your Umami installation. Umami Cloud uses https://cloud.umami.is/recorder.js.
4. If assets use a CDN or separate host, configure the shared collection-host override.
5. Save, purge page caches, and verify recorder.js and successful /api/record requests as an anonymous visitor. Loading recorder.js alone is not proof of collection. A tracked session is required.

Umami Cloud availability depends on your subscription and service configuration. The plugin does not bypass plan limits. Analytics reports, funnels, goals, journeys, retention, attribution, boards, and recording playback remain in the Umami application; their data is supplied by the native tracker and recorder, not a replacement reporting engine in WordPress.

=== Custom events and extensions ===

Use Umami's native data-umami-event attributes or umami.track() for custom events and data. Revenue events use event data containing revenue and currency. Use umami.identify() for pseudonymous visitor/session data. These APIs are available after the tracker loads.

The simply_umami_tracker_attributes filter accepts the data-attribute array and saved options. It supports dynamic distinct IDs and new data attributes without editing the plugin. The simply_umami_tracking_allowed filter can prevent both scripts from being emitted on a page; the simply_umami_recorder_enabled filter can selectively exclude pages from recording. See the development README for examples. These server-side filters do not replace a browser consent manager.

== Frequently Asked Questions ==

= Why is no tracking script present? =
Check that the plugin is active, Enabled is checked, and Script URL and Website ID are filled in. Test signed out, then purge your page cache. Browser blockers, Do Not Track, domain restrictions, or disabled automatic tracking can also prevent collection after the script is present.

= Does this work with the latest Umami? =
Version 1.1.0 was smoke-tested against a real Umami v3.4.0 server: browser pageviews, dashboard statistics, recorded heatmap clicks/scrolls, replay chunks, performance data, a pseudonymous identity, and a revenue event. The dashboard accepts both v3 numeric statistics and the v2 value-object statistics format.

= Will deactivation erase my settings? =
No. Version 1.1.0 preserves settings on deactivation. Earlier versions erased settings on deactivation. An update cannot restore values that were already deleted.

= What happened to the Cache option? =
The obsolete data-cache tracker option was removed. It does not control the plugin's five-minute dashboard statistics cache.

== Screenshots ==

1. Simply Umami setup and optional integration sections, using a local synthetic test site.
2. Recorder opt-in, server requirements, and sensitive-data warnings.

== Changelog ==

= 1.1.0 =
* Add opt-in heatmap/session replay recorder.js loading and a custom recorder URL.
* Add performance, auto-pageview, distinct-id, and fetch-credentials tracker settings.
* Add filters for tracking consent/page gating, dynamic and future data attributes, and recorder exclusions.
* Load tracker and recorder in deferred execution order.
* Preserve configuration on deactivation instead of deleting all analytics settings.
* Read current Umami v3 numeric statistics and the visitors active-count response correctly.
* Use the Umami Cloud API root at https://api.umami.is/v1.
* Include the current day in the dashboard's rolling 30-day statistics window.
* Preserve custom ports and application base paths for recorder, dashboard, and API URLs.
* Enqueue scripts through WordPress and declare the recorder's dependency on the tracker.
* Remove the obsolete data-cache tracker option and correct Do Not Track guidance.
* Replace PHP 8-only cloud detection with PHP 7.4-compatible hostname matching.
* Recover the deployed Simply Umami code into its development repository and retain upstream attribution.

= 1.0.0 =
* Fork of Integrate Umami with Simply Umami branding.
* Add v3 tracker options, API integration, and a dashboard statistics widget.
* Add JavaScript-based comment submit-button event tracking.
* Trim copied tracking values and migrate legacy settings.

== Upgrade Notice ==

= 1.1.0 =
Settings now survive deactivation. If your previous settings were already erased, copy the tracking code from Umami again and explicitly enable tracking. Purge page caches after installation or configuration changes.
