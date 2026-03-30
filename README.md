# Statamic Acumbamail Integration

[![Latest Version](https://img.shields.io/packagist/v/lwekuiper/statamic-acumbamail.svg?style=flat-square)](https://packagist.org/packages/lwekuiper/statamic-acumbamail)
[![Total Downloads](https://img.shields.io/packagist/dt/lwekuiper/statamic-acumbamail.svg?style=flat-square)](https://packagist.org/packages/lwekuiper/statamic-acumbamail)

A powerful Statamic addon that seamlessly integrates your forms with Acumbamail, featuring automatic contact synchronization, custom field mapping, and multi-site support.

> 💡 **Have an idea?** We'd love to hear it! Please [open a feature request](https://github.com/lwekuiper/statamic-acumbamail/issues/new?labels=enhancement) on GitHub.

## ✨ Features

### 🆓 Lite Edition
- **Form Integration**: Connect any Statamic form to Acumbamail lists
- **Contact Sync**: Automatically create or update contacts in Acumbamail
- **Custom Fields**: Map form fields to Acumbamail merge fields
- **Consent Management**: Built-in GDPR compliance with consent field support
- **Tag Assignment**: Automatically tag contacts upon form submission

### 🚀 Pro Edition
- **Multi-Site Support**: Configure different Acumbamail settings per site
- **Site-Specific Lists**: Route form submissions to different lists based on the current site
- **Localized Configurations**: Manage separate configurations for each locale

## 📋 Requirements

- **PHP**: 8.2 or higher
- **Laravel**: 11.0 or higher
- **Statamic**: 4.0 or higher
- **Acumbamail Account**: With API access enabled

## 🚀 Installation

### Via Statamic Control Panel
1. Navigate to **Tools > Addons** in your Statamic control panel
2. Search for "Acumbamail"
3. Click **Install**

### Via Composer
```bash
composer require lwekuiper/statamic-acumbamail
```

The package will automatically register itself.

## ⚙️ Configuration

### 1. Acumbamail API Setup

Add your Acumbamail auth token to your `.env` file:

```env
ACUMBAMAIL_AUTH_TOKEN=your-auth-token-here
```

> **💡 Tip**: You can find your auth token in the API section of your Acumbamail account settings.

### 2. Publish Configuration (Optional)

To customize the addon settings, publish the configuration file:

```bash
php artisan vendor:publish --tag=statamic-acumbamail-config
```

This creates `config/statamic/acumbamail.php` where you can modify default settings.

## 🎯 Pro Edition

> **🔥 Pro Features Available**
> Unlock multi-site capabilities with the Pro edition. Requires **Statamic Pro**.

### Upgrading to Pro

After purchasing the Pro edition, enable it in your `config/statamic/editions.php`:

```php
'addons' => [
    'lwekuiper/statamic-acumbamail' => 'pro'
],
```

### Pro Benefits
- **Multi-Site Management**: Different Acumbamail configurations per site
- **Site-Specific Routing**: Route submissions based on the current site
- **Enhanced Flexibility**: Perfect for agencies managing multiple client sites

## 📖 Documentation

For the full usage guide — including form setup, field mapping, consent management, multi-site configuration, troubleshooting, and more — see [DOCUMENTATION.md](DOCUMENTATION.md).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This addon requires a license for use in production. You may use it without a license while developing locally.

## 🆘 Support

- **Documentation**: [DOCUMENTATION.md](DOCUMENTATION.md)
- **Issues**: [GitHub Issues](https://github.com/lwekuiper/statamic-acumbamail/issues)
- **Discussions**: [GitHub Discussions](https://github.com/lwekuiper/statamic-acumbamail/discussions)

## ⚖️ Disclaimer

This addon is a third-party integration and is **not** affiliated with, endorsed by, or officially connected to Acumbamail. "Acumbamail" is a trademark of Acumbamail. All product names, logos, and brands are property of their respective owners.

---

Made with ❤️ for the Statamic community
