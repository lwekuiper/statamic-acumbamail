# Statamic Acumbamail Documentation

## Table of Contents

- [Overview](#overview)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Field Mapping](#field-mapping)
- [GDPR Consent](#gdpr-consent)
- [Multi-Site Support (Pro)](#multi-site-support-pro)
- [Permissions](#permissions)
- [Troubleshooting](#troubleshooting)

---

## Overview

Statamic Acumbamail is an addon that connects your Statamic forms to Acumbamail. When a form is submitted, the addon automatically subscribes the contact to Acumbamail lists and maps custom fields.

### Editions

The addon is available in three editions:

**Free**:
- Connect any Statamic form to Acumbamail
- Automatic subscriber creation and updating
- GDPR consent field support
- Double opt-in support

**Lite**:
- Everything in Free
- Map form fields to Acumbamail merge fields

**Pro** (requires Statamic Pro):
- Everything in Lite
- Per-site Acumbamail configurations
- Site-specific list routing
- Localized form configurations per locale

## Requirements

- PHP 8.2+
- Laravel 11.0+
- Statamic 5.0+
- An Acumbamail account with API access

## Installation

### Via Statamic Control Panel

1. Go to **Tools > Addons**
2. Search for "Acumbamail"
3. Click **Install**

### Via Composer

```bash
composer require lwekuiper/statamic-acumbamail
```

The addon auto-registers via Laravel's package discovery. The configuration file is published automatically on install.

## Configuration

### API Credentials

Add your Acumbamail auth token to your `.env` file:

```env
ACUMBAMAIL_AUTH_TOKEN=your-auth-token-here
```

You can find your auth token in the API section of your Acumbamail account settings.

### Configuration File

The configuration file is published to `config/statamic/acumbamail.php` during installation. You can also publish it manually:

```bash
php artisan vendor:publish --tag=statamic-acumbamail-config
```

### Enabling Pro Edition

To use multi-site features, enable the Pro edition in `config/statamic/editions.php`:

```php
'addons' => [
    'lwekuiper/statamic-acumbamail' => 'pro',
],
```

## Usage

### Step 1: Create a Form

Create a Statamic form with the fields you want to send to Acumbamail. At minimum, you need an email field. Example blueprint:

```yaml
title: Newsletter Signup
fields:
  - handle: email
    field:
      type: email
      display: Email Address
      validate: required|email
  - handle: first_name
    field:
      type: text
      display: First Name
  - handle: last_name
    field:
      type: text
      display: Last Name
  - handle: consent
    field:
      type: toggle
      display: I agree to receive marketing emails
      validate: required|accepted
```

### Step 2: Configure the Integration

1. Navigate to **Tools > Acumbamail** in the control panel
2. Click on the form you want to configure
3. Fill in the configuration:
   - **Email Field** (required): Select the form field containing the subscriber's email address
   - **Consent Field** (optional): Select a boolean/toggle field for GDPR consent
   - **Lists** (required): Choose one or more Acumbamail lists to subscribe the contact to
   - **Merge Fields** (optional, Lite+): Map form fields to Acumbamail merge fields

### Step 3: Use the Form in Templates

Use the form in your Antlers templates as usual. No special markup is needed for the Acumbamail integration:

```antlers
{{ form:newsletter_signup }}
    {{ if errors }}
        <div class="alert alert-danger">
            {{ errors }}
                <p>{{ value }}</p>
            {{ /errors }}
        </div>
    {{ /if }}

    {{ if success }}
        <p>Thank you for subscribing!</p>
    {{ /if }}

    <div>
        <label for="email">Email Address</label>
        <input type="email" name="email" id="email" required>
    </div>

    <div>
        <label for="first_name">First Name</label>
        <input type="text" name="first_name" id="first_name">
    </div>

    <div>
        <label>
            <input type="checkbox" name="consent" value="1" required>
            I agree to receive marketing emails
        </label>
    </div>

    <button type="submit">Subscribe</button>
{{ /form:newsletter_signup }}
```

## Field Mapping

Use merge fields to map your Statamic form fields to Acumbamail subscriber fields. Both standard fields and custom fields from your Acumbamail account are available in the merge fields dropdown.

Fields with empty or null values are filtered out and not sent to Acumbamail.

## GDPR Consent

The consent field provides GDPR compliance by requiring explicit opt-in before sending data to Acumbamail.

**How it works:**

- If a consent field is configured, the submission is only sent to Acumbamail when the field has a truthy value
- If consent is not given, the Statamic form submission is still saved, but no data is sent to Acumbamail
- If no consent field is configured, all submissions are processed

## Multi-Site Support (Pro)

With the Pro edition and Statamic's multi-site enabled, you can configure different Acumbamail integrations per site. Each site gets its own form configuration with separate lists and field mappings.

The control panel shows a site selector on both the listing and edit pages. When a form is submitted, the addon automatically detects which site it belongs to.

## Permissions

The addon registers its own permission group under **Acumbamail** in the Statamic control panel. The following permissions are available:

| Permission | Description |
|---|---|
| **View Acumbamail** | View the form configuration listing and edit pages |
| **Edit Acumbamail** | Create, update, and delete form configurations and manage addon settings |

Permissions are nested: granting **Edit Acumbamail** automatically includes **View Acumbamail**.

Super admins always have full access.

## Troubleshooting

### Submissions not appearing in Acumbamail

1. **Check API credentials**: Verify `ACUMBAMAIL_AUTH_TOKEN` in your `.env` file
2. **Check the form configuration**: Navigate to **Tools > Acumbamail** and verify the form has a configuration with the email field and at least one list
3. **Check consent**: If a consent field is configured, ensure it resolves to a truthy value in the submission
4. **Check logs**: Look for error messages in `storage/logs/laravel.log`

### Configuration not saving

1. Ensure the `resources/acumbamail/` directory exists and is writable
2. Clear the Stache cache: `php please stache:clear`

### Lists not loading in the control panel

1. Verify your API credentials are correct
2. Check that your Acumbamail account has lists created
3. Check logs for API error messages

### Multi-site not working

1. Confirm the Pro edition is enabled in `config/statamic/editions.php`
2. Confirm Statamic Pro is installed and multi-site is configured
3. Verify site-specific configuration files exist in the correct locale subdirectories

### Debug logging

Enable detailed logging by setting `LOG_LEVEL=debug` in your `.env` file. API errors are always logged regardless of log level.
