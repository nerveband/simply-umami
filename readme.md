# Simply Umami

![Simply Umami: Umami Analytics for WordPress, self-hosted or Cloud.](.wordpress-org/banner-1544x500.png)

Connect WordPress to Umami Analytics. Use your own Umami server or Umami Cloud.

Version 1.1.1. Requires WordPress 6.0+ and PHP 7.4+.

Submitted to WordPress.org. Waiting for review.

## Setup

1. Install the plugin ZIP and activate Simply Umami.
2. Open **Settings > Simply Umami**.
3. Copy the **Script URL** and **Website ID** from Umami.
4. Check **Enabled** and save.
5. Clear your page cache and CDN cache, if used.
6. Visit your site while signed out to check tracking. Admin visits are excluded by default.

API access is optional. Add a username and password for a self-hosted server, or an API key for Umami Cloud, to show stats in WordPress. Tracking works without these details.

## Settings

![Tracking setup and optional settings.](.wordpress-org/screenshot-1.png)

<details>
<summary>View recorder settings</summary>

![Recorder settings and privacy warning.](.wordpress-org/screenshot-2.png)

</details>

## Tracking

The plugin uses Umami's own scripts. It supports pageviews, events, revenue events, and performance data. You can skip admin visits, respect Do Not Track, limit tracking to chosen domains, and remove query strings and URL fragments.

After the tracker loads, you can send events like this:

```js
window.umami.track('purchase', { revenue: 12.5, currency: 'USD' });
```

View reports, heatmaps, and session recordings in Umami. Cloud features depend on your plan and access rights.

## Heatmaps and session replay

Recording is off by default. To use it, turn on recording in your Umami website settings, then enable **Load Recorder** in WordPress.

Leave **Recorder URL** blank to load `recorder.js` from the same location as your tracker. Set a URL if the recorder uses a different host or filename. Umami controls sampling, masking, and which page elements are blocked.

Check that Umami stores recordings. Loading the script alone does not mean recording works.

### Privacy

Recordings can capture page content and actions. Do not record pages with personal or sensitive information. Removing query strings from tracking does not remove them from recordings.

The plugin does not include a consent banner or ensure legal compliance. You are responsible for consent and privacy notices. See [readme.txt](readme.txt) for details about data sent to Umami.

## Developer hooks

Use `simply_umami_tracker_attributes` to change tracker attributes. It receives the attribute map and saved settings.

```php
add_filter( 'simply_umami_tracker_attributes', function ( $attributes, $options ) {
    $attributes['data-tag'] = 'campaign-a';
    return $attributes;
}, 10, 2 );
```

Use `simply_umami_tracking_allowed` to block all tracking on a page, or `simply_umami_recorder_enabled` to block recording only. Both receive the current decision and saved settings.

```php
add_filter( 'simply_umami_recorder_enabled', function ( $enabled, $options ) {
    return $enabled && ! is_page( array( 'private-pages', 'account' ) );
}, 10, 2 );
```

These filters do not replace browser consent controls. Check that your page cache respects any consent rules.

## Development

```sh
composer install
npm ci
npm run start
npx playwright install chromium
npm test -- --reporter=line
```

The local WordPress site runs at `http://localhost:8888`. Tested with self-hosted Umami 3.4.0. Signed-in Umami Cloud features have not been tested.

To build a ZIP, run `composer install --no-dev --optimize-autoloader`. Put `simply-umami.php`, `inc/`, `css/`, `js/`, `vendor/`, `composer.json`, `LICENSE.md`, and `readme.txt` in one `simply-umami/` folder. Leave out tests, Git files, credentials, and local settings.

## Changes and assets

Version 1.1.1 updates the settings layout and artwork. Tracking and saved settings are unchanged. See [readme.txt](readme.txt) for the full changelog.

| Asset | Files |
|---|---|
| Icon | [128×128](.wordpress-org/icon-128x128.png), [256×256](.wordpress-org/icon-256x256.png) |
| Banner | [772×250](.wordpress-org/banner-772x250.png), [1544×500](.wordpress-org/banner-1544x500.png) |
| Screenshots | [Tracking setup](.wordpress-org/screenshot-1.png), [Recorder settings](.wordpress-org/screenshot-2.png) |

The admin icon uses the same artwork as the directory icon. WordPress.org assets can be published after the plugin is approved.

Based on [Integrate Umami](https://github.com/Ancocodet/wp-umami). Licensed under [GPLv3 or later](LICENSE.md).
