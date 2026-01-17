=== Gravity Forms Checkbox Products ===
Contributors: yourusername
Tags: gravity forms, products, checkbox, pricing, payments
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a checkbox-based product field to Gravity Forms for selecting multiple products with individual prices.

== Description ==

**Gravity Forms Checkbox Products** extends Gravity Forms with a powerful new field type that allows users to select multiple products using checkboxes, each with their own individual price.

= Features =

* **Checkbox Product Field** - New field type in the Pricing Fields section
* **Individual Pricing** - Each checkbox option can have its own unique price
* **Live Price Calculation** - Form total updates in real-time as selections change
* **Payment Gateway Integration** - Works seamlessly with Stripe, PayPal, and other Gravity Forms payment add-ons
* **Easy Configuration** - Simple admin interface for adding products and prices
* **Entry Details** - Selected products display clearly in entry details and notifications
* **Merge Tag Support** - Use in notifications, confirmations, and other merge tag locations
* **Conditional Logic** - Full support for Gravity Forms conditional logic
* **Responsive Design** - Mobile-friendly interface
* **Accessible** - WCAG 2.1 compliant with proper ARIA labels

= Use Cases =

* **Event Registration** - Let users select multiple add-ons (meals, workshops, etc.)
* **Product Orders** - Allow customers to choose multiple products in one form
* **Service Selection** - Enable clients to select multiple services with different prices
* **Donation Forms** - Offer multiple giving options that can be combined
* **Package Building** - Let users build custom packages by selecting features

= Requirements =

* WordPress 5.8 or higher
* Gravity Forms 2.5 or higher
* PHP 7.4 or higher

= How It Works =

1. Add the "Checkbox Products" field to your form from the Pricing Fields section
2. Configure your product choices with labels and prices
3. Add other form fields as needed (name, email, etc.)
4. Add a Total field to display the calculated total
5. Configure a payment gateway (Stripe, PayPal, etc.) if accepting payments
6. Selected products automatically integrate with Gravity Forms' pricing system

= Support =

For support, feature requests, or bug reports, please visit our [GitHub repository](https://github.com/yourusername/gf-checkbox-products).

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins > Add New
3. Search for "Gravity Forms Checkbox Products"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Log in to your WordPress admin panel
3. Navigate to Plugins > Add New > Upload Plugin
4. Choose the downloaded zip file and click "Install Now"
5. Activate the plugin

= After Installation =

1. Ensure Gravity Forms 2.5+ is installed and activated
2. Create a new form or edit an existing one
3. Look for "Checkbox Products" in the Pricing Fields section
4. Add the field and configure your product choices

== Frequently Asked Questions ==

= Does this require Gravity Forms? =

Yes, this plugin requires Gravity Forms 2.5 or higher to be installed and activated.

= Will this work with payment gateways? =

Yes! The plugin integrates seamlessly with Gravity Forms payment add-ons including Stripe, PayPal, Authorize.Net, and others.

= Can I use conditional logic with checkbox products? =

Yes, the Checkbox Products field fully supports Gravity Forms conditional logic.

= How are selected products displayed in entries? =

Selected products are displayed with their names and prices in entry details, notifications, and can be used in merge tags.

= Can users be required to select at least one product? =

Yes, you can mark the field as "Required" and users will need to select at least one option.

= Does this work with AJAX-enabled forms? =

Yes, the plugin works with both standard and AJAX-enabled forms.

= Can I limit the number of selections? =

Currently, users can select as many or as few options as they like (unless the field is required). Minimum/maximum selection limits are planned for a future release.

= Is this compatible with multi-page forms? =

Yes, the Checkbox Products field works on multi-page forms and the total calculation works across pages.

== Screenshots ==

1. Checkbox Products field in the form editor
2. Product choices configuration panel
3. Frontend display of checkbox products
4. Entry detail showing selected products
5. Integration with payment gateways

== Changelog ==

= 1.0.0 - 2024-01-17 =
* Initial release
* Checkbox Products field type
* Individual product pricing
* Live price calculations
* Payment gateway integration
* Entry display and notifications
* Merge tag support
* Conditional logic support
* Responsive and accessible design

== Upgrade Notice ==

= 1.0.0 =
Initial release of Gravity Forms Checkbox Products.

== Additional Info ==

= Documentation =

For detailed documentation, examples, and code snippets, visit our [documentation site](https://github.com/yourusername/gf-checkbox-products/wiki).

= Contributing =

We welcome contributions! Please see our [contributing guidelines](https://github.com/yourusername/gf-checkbox-products/blob/main/CONTRIBUTING.md).

= Privacy Policy =

This plugin does not collect or store any user data beyond what is collected by Gravity Forms itself.
