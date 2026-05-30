=== SI Fast Product Importer for WooCommerce ===
Contributors: innovazioneweb
Tags: woocommerce, import products, ai generator, product management
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Requires Plugins: woocommerce
WC requires at least: 5.0
WC tested up to: 9.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import high-quality, pre-configured products into your WooCommerce store with just a few clicks. Includes AI-powered product generation, a vast product database, and one-click imports.

== Description ==

SI Fast Product Importer for WooCommerce is designed for e-commerce businesses looking to expand their product range quickly and efficiently. It allows you to import high-quality, pre-configured items with just a few clicks.

Key Features:

* **Instant Product Search**: Search through a vast database of products.
* **One-Click Import**: Import products directly into your WooCommerce store.
* **AI Product Generator**: Generate unique product descriptions and details using AI providers (OpenAI, Claude, Gemini, OpenRouter).
* **Local Demo Database**: Includes a local database of 2000 products for testing.
* **Customizable Settings**: Configure SKU prefixes, default stock, and more.

== External Services ==

This plugin connects to the following external services:

**Flash Products API (flashproducts.studioimmens.com)**
This plugin queries the Flash Products remote database to fetch ready-to-import WooCommerce products. The following data is sent with each search request: search keywords, selected language, category filter, pagination parameters. No personal user data is transmitted.
- Service provided by: Studio Immens
- Terms of Service: https://studioimmens.com/termini-e-condizioni
- Privacy Policy: https://studioimmens.com/privacy-policy/

**OpenAI API (api.openai.com)**
When the OpenAI provider is enabled and configured, the plugin sends product names and optional context descriptions to OpenAI's API to generate product descriptions, excerpts, and metadata. No personal user data is transmitted.
- Service provided by: OpenAI
- Terms of Service: https://openai.com/policies/terms-of-service
- Privacy Policy: https://openai.com/policies/privacy-policy

**Anthropic Claude API (api.anthropic.com)**
When the Claude provider is enabled and configured, the plugin sends product names and optional context descriptions to Anthropic's API to generate product descriptions, excerpts, and metadata. No personal user data is transmitted.
- Service provided by: Anthropic
- Terms of Service: https://www.anthropic.com/terms
- Privacy Policy: https://www.anthropic.com/privacy

**OpenRouter API (openrouter.ai)**
When the OpenRouter provider is enabled and configured, the plugin sends product names and optional context descriptions to OpenRouter's API to generate product descriptions, excerpts, and metadata using various AI models. No personal user data is transmitted.
- Service provided by: OpenRouter
- Terms of Service: https://openrouter.ai/terms
- Privacy Policy: https://openrouter.ai/privacy

**Google Gemini API (generativelanguage.googleapis.com)**
When the Gemini provider is enabled and configured, the plugin sends product names and optional context descriptions to Google's Gemini API to generate product descriptions, excerpts, and metadata. No personal user data is transmitted.
- Service provided by: Google
- Terms of Service: https://ai.google.dev/terms
- Privacy Policy: https://policies.google.com/privacy

== Installation ==

1. Upload the `si-fast-product-importer` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your AI provider API key in the settings page if you wish to use the AI Generator.
4. Access Fast Product Importer from the sidebar menu to start importing.

== Frequently Asked Questions ==

= Does it require WooCommerce? =
Yes, this plugin is designed specifically for WooCommerce.

= How do I get an API key for AI generation? =
Each AI provider has its own signup process. You can get free or paid API keys from OpenAI, Anthropic, Google, or OpenRouter.

== Screenshots ==

1. The main product search and import interface.
2. AI Product Generator.
3. Plugin settings and logs.

== Changelog ==

= 1.1.0 =
* Security hardening: CSRF protection and data sanitization.
* Improved AI generation logic.
* Added PSR-4 autoloading.
* General code cleanup and WordPress.org standards compliance.

= 1.0.0 =
* Initial release.
* Added AI Product Generation.
* Added Local JSON Database support.
* Full WPCS compliance and security hardening.

== Upgrade Notice ==

= 1.1.0 =
Important security and compliance update. All users should upgrade.
