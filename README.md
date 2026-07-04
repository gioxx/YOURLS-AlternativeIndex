# 🌐 YOURLS Alternative Index

**Transform the unused YOURLS root page into a Linktree-style profile page with social links, featured content, and custom branding.**  
Instead of showing a 403 error, visitors to your YOURLS root URL will see a beautiful, customizable link-in-bio page.

[![Latest Release](https://img.shields.io/github/v/release/gioxx/YOURLS-AlternativeIndex)](https://github.com/gioxx/YOURLS-AlternativeIndex/releases)
[![License](https://img.shields.io/github/license/gioxx/YOURLS-AlternativeIndex)](LICENSE)
[![Listed in Awesome YOURLS!](https://img.shields.io/badge/Awesome-YOURLS-C5A3BE)](https://github.com/YOURLS/awesome)

---

## 🚀 Features

- **Linktree-style profile page** served at your YOURLS root URL (`/`)
- **Profile section**: display name, tagline/bio, avatar image
- **Social icons row**: X/Twitter, Instagram, Facebook, LinkedIn, GitHub, YouTube, TikTok, Mastodon, Bluesky, Threads, Pinterest, Twitch, Discord, Telegram, WhatsApp, and more
- **Featured link buttons**: full-width cards with optional emoji, title, and URL
- **Custom colors**: background, accent (buttons & icon hover), and text colors
- **Custom CSS**: inject your own styles for full control
- **Enable/disable toggle**: turn the page on or off without deactivating the plugin
- **Update notifications**: checks GitHub for new releases and shows a badge in the admin panel
- **Simple single-file plugin** — no composer, no npm, no build step

---

## 🛠️ Installation

1. Download the plugin from [the latest release](https://github.com/gioxx/YOURLS-AlternativeIndex/releases).
2. Unzip the contents into the `user/plugins/alternative-index/` directory.
3. Activate the plugin in the YOURLS admin panel under **Plugins**.
4. Go to **Plugins → Alternative Index** and configure your page.

> **Requires YOURLS 1.9+ and PHP 7.4+**

---

## ⚙️ Usage

### Basic Configuration

1. Open the **Alternative Index** settings page from the Plugins menu.
2. **Enable** the plugin using the toggle at the top.
3. Fill in your **Profile**: name, tagline, and avatar URL.
4. Choose your **Appearance**: background color, accent color, and text color.
5. Add **Social Links**: select the platform and paste your profile URL.
6. Add **Featured Links**: enter an optional emoji, a title, and a URL.
7. Click **Save Settings** and visit your YOURLS root URL to see the result.

### How it works

| Visitor request | Result |
|---|---|
| YOURLS root URL (`/`) — plugin enabled | Shows the Alternative Index profile page |
| YOURLS root URL (`/`) — plugin disabled | Default YOURLS behavior (403 / blank) |
| Valid short URL (e.g. `/abc`) | YOURLS handles it normally |
| Admin panel | Unaffected |
| API / AJAX requests | Unaffected |

### Supported social platforms

| Platform | Key |
|---|---|
| Website / Blog | `website` |
| Email | `email` |
| GitHub | `github` |
| X (Twitter) | `twitter` or `x` |
| Instagram | `instagram` |
| Facebook | `facebook` |
| LinkedIn | `linkedin` |
| YouTube | `youtube` |
| TikTok | `tiktok` |
| Mastodon | `mastodon` |
| Bluesky | `bluesky` |
| Threads | `threads` |
| Pinterest | `pinterest` |
| Twitch | `twitch` |
| Discord | `discord` |
| Telegram | `telegram` |
| WhatsApp | `whatsapp` |

Any other value will display a generic icon with the platform's initial letter.

### Custom CSS examples

Target the main card container:
```css
.yai-card { backdrop-filter: blur(8px); }
```

Make link buttons square instead of pill-shaped:
```css
.yai-link { border-radius: 8px; }
```

Use a gradient background:
```css
body { background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460); }
```

---

## 🔧 Stored Options

| Option key | Type | Notes |
|---|---|---|
| `yai_enabled` | int | `1` = active, `0` = disabled |
| `yai_profile_name` | string | Display name |
| `yai_tagline` | string | Short bio shown below the name |
| `yai_avatar_url` | string | Full URL to profile image |
| `yai_page_title` | string | Browser tab title; defaults to profile name |
| `yai_bg_color` | string | Hex color for background |
| `yai_accent_color` | string | Hex color for hover states and button fills |
| `yai_text_color` | string | Hex color for all text |
| `yai_card_transparency` | int | Card transparency percentage when a background image is active; `100` removes the glass effect; default `55` |
| `yai_social_links` | JSON | Array of `{platform, url}` objects |
| `yai_featured_links` | JSON | Array of `{emoji, title, url}` objects |
| `yai_custom_css` | string | Raw CSS injected into the public page |
| `yai_show_powered_by` | int | `1` = show footer attribution |

---

## ⚠️ Compatibility note

This plugin and [YOURLS URL Fallback](https://github.com/gioxx/YOURLS-URLFallback) both intercept the YOURLS root URL. If you have both active, **Alternative Index takes precedence** (it runs at priority 99 and serves a page directly — URL Fallback's redirect never fires for the root URL). Use one or the other for root URL handling.

---

## 📄 License

This plugin is licensed under the [MIT License](LICENSE).

---

## 💬 About

Lovingly developed by the usually-on-vacation brain cell of [Gioxx](https://github.com/gioxx).

---

## 🤝 Contributing

Pull requests and feature suggestions are welcome!  
If you find bugs or have feature requests, [open an issue](https://github.com/gioxx/YOURLS-AlternativeIndex/issues).  
If you find it useful, leave a ⭐ on GitHub! ❤️
