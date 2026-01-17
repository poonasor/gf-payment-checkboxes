# Gravity Forms Checkbox Products

A WordPress plugin that adds a checkbox-based product field to Gravity Forms, allowing users to select multiple products with individual prices that calculate into the form total.

## Features

- **Checkbox Product Field** - New field type in the Pricing Fields section
- **Individual Pricing** - Each checkbox option can have its own unique price
- **Live Price Calculation** - Form total updates in real-time as selections change
- **Payment Gateway Integration** - Works with Stripe, PayPal, and other GF payment add-ons
- **Easy Configuration** - Simple admin interface for adding products and prices
- **Entry Details** - Selected products display in entry details and notifications
- **Merge Tag Support** - Use in notifications, confirmations, and merge tags
- **Conditional Logic** - Full Gravity Forms conditional logic support
- **Responsive & Accessible** - Mobile-friendly and WCAG 2.1 compliant

## Requirements

- WordPress 5.8 or higher
- Gravity Forms 2.5 or higher
- PHP 7.4 or higher

## Installation

### From WordPress Admin

1. Download the plugin zip file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the zip file
4. Click "Install Now" and then "Activate"

### Manual Installation

1. Upload the `gf-checkbox-products` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress

## Usage

### Basic Setup

1. Create or edit a Gravity Form
2. Add the "Checkbox Products" field from the Pricing Fields section
3. Configure your product choices:
   - **Label**: The product name displayed to users
   - **Value**: Internal identifier (auto-generated from label)
   - **Price**: Individual price for this product
4. Add a "Total" field to display the calculated total
5. (Optional) Add a payment feed for Stripe, PayPal, etc.

### Example Use Cases

#### Event Registration with Add-ons

```
Field Label: "Select Your Options"

Choices:
- Lunch Ticket ($25.00)
- Workshop Session ($50.00)
- Event T-Shirt ($15.00)
- Printed Materials ($10.00)
```

#### Service Selection Form

```
Field Label: "Select Services"

Choices:
- Basic Package ($99.00)
- SEO Optimization ($149.00)
- Content Writing ($199.00)
- Social Media Setup ($79.00)
```

#### Product Order Form

```
Field Label: "Select Products"

Choices:
- Product A ($29.99)
- Product B ($39.99)
- Product C ($49.99)
```

## File Structure

```
gf-checkbox-products/
├── gf-checkbox-products.php          # Main plugin file
├── readme.txt                         # WordPress readme
├── README.md                          # This file
├── .gitignore                         # Git ignore rules
├── includes/
│   ├── class-gf-field-checkbox-product.php      # Custom field class
│   ├── class-gf-checkbox-products-admin.php     # Admin functionality
│   └── class-gf-checkbox-products-pricing.php   # Pricing logic
├── assets/
│   ├── js/
│   │   ├── admin.js                  # Admin field settings UI
│   │   └── frontend.js               # Frontend price calculations
│   └── css/
│       ├── admin.css                 # Admin styles
│       └── frontend.css              # Frontend styles
└── languages/
    └── gf-checkbox-products.pot      # Translation template
```

## Development

### Key Classes

#### `GF_Field_Checkbox_Product`
The main field class that extends `GF_Field`. Handles:
- Field rendering on frontend
- Value submission and storage
- Entry display formatting
- Merge tag values
- Validation

#### `GF_Checkbox_Products_Admin`
Handles admin functionality:
- Field settings UI in form editor
- Admin scripts and styles
- Tooltips and help text

#### `GF_Checkbox_Products_Pricing`
Manages pricing integration:
- Integration with GF pricing system
- Payment gateway compatibility
- Frontend price calculation scripts
- Product total calculations

### Hooks & Filters

The plugin uses several Gravity Forms hooks:

```php
// Register pricing field type
add_filter('gform_pricing_fields', [$this, 'register_pricing_field']);

// Add products to order
add_filter('gform_product_info', [$this, 'add_checkbox_products_to_order'], 10, 3);

// Enqueue scripts
add_action('gform_enqueue_scripts', [$this, 'enqueue_frontend_scripts'], 10, 2);

// Field settings
add_action('gform_field_standard_settings', [$this, 'field_settings_ui'], 10, 2);
```

### JavaScript API

Frontend JavaScript provides a public API:

```javascript
// Calculate total for a specific form
GFCheckboxProducts.calculateTotal(formId);

// Trigger recalculation
GFCheckboxProducts.recalculate(formId);
```

### Customization

#### Custom CSS Classes

Add custom CSS classes in the field settings:

```css
/* Target specific checkbox products field */
.my-custom-class .gfield_checkbox_product label {
    font-weight: bold;
}

/* Card-style layout (built-in) */
.gf-checkbox-card-style .gfield_checkbox_product label {
    /* Styles are already included */
}
```

#### Modify Product Data

Use filters to modify product data:

```php
// Modify products before adding to order
add_filter('gform_product_info', function($product_info, $form, $entry) {
    // Your custom logic
    return $product_info;
}, 20, 3);
```

## Testing

### Manual Testing Checklist

- [ ] Field appears in form editor Pricing Fields section
- [ ] Can add/edit/remove product choices
- [ ] Choices save correctly
- [ ] Frontend displays checkboxes with prices
- [ ] Selecting checkboxes updates total in real-time
- [ ] Form submission saves selections
- [ ] Entry detail shows selected products
- [ ] Works with payment gateways (Stripe, PayPal)
- [ ] Conditional logic works
- [ ] AJAX forms work correctly
- [ ] Multi-page forms work correctly
- [ ] Merge tags display correctly
- [ ] Responsive on mobile devices

### Testing with Payment Gateways

1. **Stripe Testing**:
   - Use test mode with test card: 4242 4242 4242 4242
   - Verify line items appear correctly in Stripe dashboard

2. **PayPal Testing**:
   - Use PayPal sandbox mode
   - Verify product names and prices in PayPal

## Troubleshooting

### Total Not Updating

If the total isn't updating when checkboxes are selected:

1. Ensure you have a "Total" field added to your form
2. Check browser console for JavaScript errors
3. Verify Gravity Forms is version 2.5 or higher
4. Try disabling other plugins to check for conflicts

### Products Not Appearing in Payment Gateway

1. Verify the field is configured with prices
2. Check that the payment feed is properly configured
3. Ensure the form has a "Total" field
4. Test with a simple form to isolate the issue

### Field Not Appearing in Editor

1. Ensure Gravity Forms 2.5+ is installed
2. Check that the plugin is activated
3. Look for errors in WordPress debug log
4. Verify PHP version is 7.4 or higher

## Support

For support, please:

1. Check the [FAQ section](#faq)
2. Search existing [GitHub Issues](https://github.com/yourusername/gf-checkbox-products/issues)
3. Create a new issue with detailed information

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## Future Enhancements

Planned features for future versions:

### Version 1.1
- Quantity input per checkbox
- Product images
- Inventory tracking
- Choice-level conditional logic

### Version 1.2
- Bulk pricing/discounts
- Choice categories
- Min/max selection limits
- Dynamic pricing formulas

### Version 2.0
- WooCommerce integration
- Subscription support
- Advanced reporting

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Credits

Developed by [Your Name]

Built with [Gravity Forms](https://www.gravityforms.com/)

## Changelog

### 1.0.0 - 2024-01-17
- Initial release
- Checkbox Products field type
- Individual product pricing
- Live price calculations
- Payment gateway integration
- Entry display and notifications
- Merge tag support
- Conditional logic support
- Responsive and accessible design
