# Installation & Testing Guide

## Quick Start

### Prerequisites

Before installing this plugin, ensure you have:

- WordPress 5.8 or higher
- Gravity Forms 2.5 or higher installed and activated
- PHP 7.4 or higher

### Installation Steps

1. **Install the Plugin**
   ```
   Upload the entire plugin folder to:
   /wp-content/plugins/gravityforms-payment-checkboxes/
   ```

2. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Gravity Forms Checkbox Products"
   - Click "Activate"

3. **Verify Installation**
   - Check for any error messages
   - Confirm Gravity Forms is active
   - Navigate to Forms → New Form to test

## Creating Your First Checkbox Products Form

### Step 1: Create a New Form

1. Go to **Forms → New Form**
2. Enter a form name (e.g., "Event Registration")
3. Click "Create Form"

### Step 2: Add Checkbox Products Field

1. In the form editor, look for the **Pricing Fields** section on the right
2. Click on **Checkbox Products** to add it to your form
3. The field will be added to your form canvas

### Step 3: Configure Product Choices

1. Click on the Checkbox Products field to open field settings
2. Scroll down to find the **Product Choices** section
3. For each product you want to add:
   - **Label**: Enter the product name (e.g., "Lunch Ticket")
   - **Value**: Auto-generated, or enter custom value (e.g., "lunch_ticket")
   - **Price**: Enter the price (e.g., "25.00")
4. Click **Add Product Choice** to add more products
5. Example configuration:
   ```
   Label: Lunch Ticket       Value: lunch_ticket       Price: 25.00
   Label: Workshop Session   Value: workshop_session   Price: 50.00
   Label: Event T-Shirt      Value: event_tshirt       Price: 15.00
   ```

### Step 4: Add a Total Field

1. From **Pricing Fields**, add a **Total** field
2. This will display the calculated total price
3. Position it where you want the total to appear

### Step 5: Add Other Fields (Optional)

Add standard fields as needed:
- Name
- Email
- Phone
- etc.

### Step 6: Preview and Test

1. Click **Preview** in the form editor
2. Test selecting different checkboxes
3. Verify the total updates in real-time
4. Submit a test entry
5. Check the entry details to see selected products

## Payment Gateway Setup (Optional)

### Setting Up Stripe

1. **Install Gravity Forms Stripe Add-On**
   - Go to Forms → Add-Ons
   - Install and activate "Gravity Forms Stripe Add-On"

2. **Configure Stripe**
   - Go to Forms → Settings → Stripe
   - Enter your Stripe API keys
   - Enable test mode for testing

3. **Create a Stripe Feed**
   - Edit your form
   - Go to Form Settings → Stripe
   - Click "Add New"
   - Configure:
     - **Name**: "Event Registration Payment"
     - **Transaction Type**: "Products and Services"
     - **Payment Amount**: "Form Total"
   - Map your form fields to Stripe customer fields
   - Save the feed

4. **Test with Stripe**
   - Use test card: 4242 4242 4242 4242
   - Any future expiry date
   - Any 3-digit CVC
   - Submit form and verify payment in Stripe dashboard

### Setting Up PayPal

1. **Install Gravity Forms PayPal Add-On**
   - Go to Forms → Add-Ons
   - Install and activate "Gravity Forms PayPal Add-On"

2. **Configure PayPal**
   - Go to Forms → Settings → PayPal
   - Enter your PayPal email
   - Enable sandbox mode for testing

3. **Create a PayPal Feed**
   - Edit your form
   - Go to Form Settings → PayPal
   - Click "Add New"
   - Configure payment settings
   - Save the feed

## Testing Checklist

### Admin Testing

- [ ] Plugin activates without errors
- [ ] Checkbox Products field appears in Pricing Fields section
- [ ] Can add product choices with labels and prices
- [ ] Can edit existing product choices
- [ ] Can delete product choices (keeps at least one)
- [ ] Product choices save when form is saved
- [ ] Product choices load when editing existing field
- [ ] Can configure field settings (label, required, etc.)
- [ ] Can apply conditional logic to field
- [ ] Field can be duplicated
- [ ] Field can be deleted

### Frontend Testing

- [ ] Checkboxes display correctly with prices
- [ ] Prices show in proper currency format
- [ ] Can select multiple checkboxes
- [ ] Can deselect checkboxes
- [ ] Total field updates in real-time when selections change
- [ ] Required validation works (if enabled)
- [ ] Form submits successfully with selections
- [ ] Works with standard forms
- [ ] Works with AJAX-enabled forms
- [ ] Responsive on mobile devices
- [ ] Accessible with keyboard navigation

### Entry Testing

- [ ] Submissions save correctly to database
- [ ] Entry detail page shows selected products with prices
- [ ] Entry list shows summary of selections
- [ ] Can export entries with checkbox product data
- [ ] Merge tags work in notifications
- [ ] Merge tags work in confirmations
- [ ] Conditional logic based on selections works

### Payment Gateway Testing

- [ ] Stripe: Products appear as line items
- [ ] Stripe: Total calculates correctly
- [ ] Stripe: Test payment processes successfully
- [ ] Stripe: Entry shows payment status
- [ ] PayPal: Products appear correctly
- [ ] PayPal: Total calculates correctly
- [ ] PayPal: Test payment processes successfully
- [ ] Combined with other product fields works

### Edge Cases

- [ ] Form with no product choices (handles gracefully)
- [ ] Very long product labels display correctly
- [ ] Special characters in labels (quotes, apostrophes, etc.)
- [ ] Zero-priced items work
- [ ] Decimal prices (e.g., $19.99)
- [ ] Large prices (e.g., $10,000.00)
- [ ] Multiple checkbox product fields on same form
- [ ] Multi-page forms
- [ ] Conditional logic hiding/showing field
- [ ] Different currency formats (EUR, GBP, etc.)

## Troubleshooting

### Field Doesn't Appear in Editor

**Problem**: Checkbox Products field not showing in Pricing Fields section

**Solutions**:
1. Verify Gravity Forms 2.5+ is installed
2. Check that plugin is activated
3. Look for PHP errors in debug log
4. Deactivate and reactivate the plugin
5. Clear WordPress cache

### Total Not Calculating

**Problem**: Total field doesn't update when checkboxes are selected

**Solutions**:
1. Ensure you have a Total field added to form
2. Check browser console for JavaScript errors
3. Try disabling other plugins to check for conflicts
4. Verify form is not in legacy mode
5. Test with a default WordPress theme
6. Check that JavaScript is not being blocked

### Products Not in Payment Gateway

**Problem**: Selected products don't appear in Stripe/PayPal

**Solutions**:
1. Verify products have prices configured
2. Check payment feed configuration
3. Ensure feed is active
4. Test with simple form first
5. Check Gravity Forms system status
6. Review payment gateway logs

### Styles Look Broken

**Problem**: Checkbox products display incorrectly

**Solutions**:
1. Clear browser cache
2. Check theme CSS conflicts
3. Disable theme customizations temporarily
4. Use browser inspector to check CSS
5. Try adding custom CSS to fix conflicts

### Database Errors

**Problem**: Entries not saving or errors on submission

**Solutions**:
1. Check WordPress debug log
2. Verify database permissions
3. Test with minimal form
4. Check for plugin conflicts
5. Review server error logs

## Sample Forms

### Example 1: Event Registration

```
Form Fields:
1. Name (required)
2. Email (required)
3. Checkbox Products: "Event Add-ons"
   - Lunch Ticket ($25.00)
   - Workshop Session ($50.00)
   - Event T-Shirt ($15.00)
   - Printed Materials ($10.00)
4. Total
5. Stripe/PayPal
```

### Example 2: Service Order Form

```
Form Fields:
1. Company Name (required)
2. Contact Email (required)
3. Checkbox Products: "Select Services"
   - Website Design ($999.00)
   - SEO Package ($499.00)
   - Content Writing ($299.00)
   - Social Media Setup ($199.00)
4. Total
5. Submit
```

### Example 3: Donation Form

```
Form Fields:
1. Donor Name (required)
2. Email (required)
3. Checkbox Products: "Support Our Programs"
   - Education Fund ($100.00)
   - Health Services ($150.00)
   - Community Development ($200.00)
   - Emergency Relief ($250.00)
4. Total
5. Payment
```

## Getting Help

If you encounter issues:

1. Check this installation guide
2. Review the main README.md
3. Check browser console for JavaScript errors
4. Enable WordPress debugging:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
5. Check `/wp-content/debug.log` for errors
6. Search GitHub issues
7. Create a new issue with:
   - WordPress version
   - Gravity Forms version
   - PHP version
   - Steps to reproduce
   - Error messages
   - Screenshots if applicable

## Next Steps

After successful installation:

1. **Customize Styling**: Add custom CSS for your theme
2. **Configure Notifications**: Set up admin and user notifications
3. **Set Up Confirmations**: Create custom confirmation messages
4. **Configure Conditional Logic**: Show/hide fields based on selections
5. **Test Thoroughly**: Test all scenarios before going live
6. **Monitor Entries**: Check that entries are being saved correctly
7. **Test Payments**: Ensure payment gateway integration works
8. **Create Backups**: Backup your forms regularly

## Support

For additional support, visit:
- GitHub Issues: https://github.com/yourusername/gf-checkbox-products/issues
- Documentation: https://github.com/yourusername/gf-checkbox-products/wiki
