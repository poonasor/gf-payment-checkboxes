# Changelog

All notable changes to the Gravity Forms Checkbox Products plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-17

### Added
- Initial release of Gravity Forms Checkbox Products plugin
- Checkbox Products custom field type in Pricing Fields section
- Admin interface for managing product choices with labels, values, and prices
- Frontend rendering of checkbox products with price display
- Real-time price calculation when checkboxes are selected/deselected
- Integration with Gravity Forms pricing system via `gform_product_info` filter
- Full payment gateway support (Stripe, PayPal, Authorize.Net, etc.)
- Entry detail page display of selected products with prices
- Entry list summary showing item count and total price
- Merge tag support for notifications and confirmations
- Conditional logic support
- Field validation (required field support)
- AJAX form support
- Multi-page form support
- Responsive CSS for mobile devices
- Accessibility features (ARIA labels, keyboard navigation)
- Admin tooltips and help text
- JavaScript API for custom integrations
- Translation-ready with .pot file
- Comprehensive documentation (README.md, INSTALLATION.md)
- Example use cases and sample forms

### Security
- Input sanitization and validation on all user inputs
- Proper escaping of output to prevent XSS
- WordPress nonce verification for admin actions
- Capability checks for admin functionality

### Performance
- Efficient asset loading (only loads on forms with checkbox product fields)
- Minification-ready code structure
- Optimized DOM queries in JavaScript
- CSS scoped to prevent conflicts

### Developer Features
- Well-documented code with inline comments
- Object-oriented architecture
- WordPress coding standards compliance
- Extensible class structure
- Hooks and filters for customization
- Debug mode support
- Public JavaScript API

---

## [Unreleased]

### Planned for 1.1.0
- Quantity input option for each checkbox
- Product images support
- Inventory tracking and stock management
- Choice-level conditional logic
- Improved admin UI with drag-and-drop reordering

### Planned for 1.2.0
- Bulk pricing and discount rules
- Product categories/groups
- Minimum and maximum selection limits
- Dynamic pricing based on other field values
- CSV import for product choices

### Planned for 2.0.0
- WooCommerce integration
- Recurring payment/subscription support
- Advanced product options (variants, attributes)
- Product search and filtering on frontend
- Enhanced reporting and analytics

---

## Version History

### Version Numbering

- **Major version (X.0.0)**: Breaking changes, major new features
- **Minor version (1.X.0)**: New features, backwards compatible
- **Patch version (1.0.X)**: Bug fixes, minor improvements

### Support Policy

- Current major version receives full support
- Previous major version receives security updates for 6 months
- Older versions are unsupported

---

## Upgrade Guide

### From Future Versions

Upgrade instructions will be added as new versions are released.

### Database Changes

Version 1.0.0 does not require any database changes. All data is stored using Gravity Forms' existing entry storage system.

---

## Bug Fixes

### 1.0.0
No bug fixes in initial release.

---

## Deprecations

### 1.0.0
No deprecations in initial release.

---

## Known Issues

### 1.0.0
- None at this time

To report issues, please visit: https://github.com/yourusername/gf-checkbox-products/issues

---

## Credits

### Contributors
- [Your Name] - Initial development

### Libraries & Dependencies
- Gravity Forms - Form framework
- WordPress - Content management system

### Special Thanks
- Gravity Forms team for excellent documentation
- WordPress community for coding standards
- All users who provide feedback and suggestions

---

## Links

- [GitHub Repository](https://github.com/yourusername/gf-checkbox-products)
- [Documentation](https://github.com/yourusername/gf-checkbox-products/wiki)
- [Issue Tracker](https://github.com/yourusername/gf-checkbox-products/issues)
- [WordPress.org Plugin Page](https://wordpress.org/plugins/gf-checkbox-products/)

---

[1.0.0]: https://github.com/yourusername/gf-checkbox-products/releases/tag/1.0.0
