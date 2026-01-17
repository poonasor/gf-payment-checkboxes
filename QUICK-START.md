# Quick Start Guide

Get started with Gravity Forms Checkbox Products in 5 minutes!

## Prerequisites ✓

- WordPress 5.8+
- Gravity Forms 2.5+
- PHP 7.4+

## Installation (2 minutes)

1. **Upload Plugin**
   - Already installed at: `/wp-content/plugins/gravityforms-payment-checkboxes/`

2. **Activate Plugin**
   ```
   WordPress Admin → Plugins → Find "Gravity Forms Checkbox Products" → Click "Activate"
   ```

3. **Verify**
   - No error messages appear
   - You're ready to go!

## Create Your First Form (3 minutes)

### Step 1: Create Form
```
Forms → New Form
Name: "Event Registration"
Click "Create Form"
```

### Step 2: Add Checkbox Products Field
```
1. In form editor, find "Pricing Fields" section (right sidebar)
2. Click "Checkbox Products"
3. Field is added to your form
```

### Step 3: Configure Products
```
1. Click the Checkbox Products field
2. In settings panel, find "Product Choices"
3. Add your products:

   Product 1:
   - Label: Lunch Ticket
   - Value: lunch_ticket (auto-filled)
   - Price: 25.00

   Product 2:
   - Label: Workshop Session
   - Value: workshop_session
   - Price: 50.00

   Product 3:
   - Label: Event T-Shirt
   - Value: event_tshirt
   - Price: 15.00

4. Click "Update" to save
```

### Step 4: Add Total Field
```
1. From "Pricing Fields", add "Total" field
2. Drag it below the Checkbox Products field
3. Done!
```

### Step 5: Test
```
1. Click "Preview" in form editor
2. Select checkboxes
3. Watch total update in real-time ✨
4. Submit a test entry
5. Check Entries to see your submission
```

## That's It! 🎉

Your checkbox products form is ready to use.

## Optional: Add Payment Gateway

### Quick Stripe Setup

1. **Install Stripe Add-On**
   ```
   Forms → Add-Ons → Install "Gravity Forms Stripe Add-On"
   ```

2. **Configure Stripe**
   ```
   Forms → Settings → Stripe
   - Add API keys
   - Enable test mode
   - Save
   ```

3. **Add Stripe Feed**
   ```
   Edit your form → Settings → Stripe → Add New
   - Name: Event Payment
   - Transaction Type: Products and Services
   - Payment Amount: Form Total
   - Map customer fields
   - Save
   ```

4. **Test Payment**
   ```
   - Preview form
   - Select products
   - Use test card: 4242 4242 4242 4242
   - Submit
   - Check Stripe dashboard ✓
   ```

## Common Use Cases

### Event Registration
```
Checkbox Products: "Select Your Add-ons"
- Lunch ($25)
- Workshop ($50)
- T-Shirt ($15)
- Materials ($10)
```

### Service Order
```
Checkbox Products: "Select Services"
- Basic Package ($99)
- SEO Package ($149)
- Content Writing ($199)
```

### Product Order
```
Checkbox Products: "Select Products"
- Product A ($29.99)
- Product B ($39.99)
- Product C ($49.99)
```

## Tips & Tricks

### Make Field Required
```
Click field → Settings → Check "Required"
```

### Change Label
```
Click field → Settings → Change "Field Label"
```

### Add Description
```
Click field → Settings → Enter "Description"
```

### Use Conditional Logic
```
Click field → Settings → Enable "Conditional Logic"
```

### Add More Choices
```
Click field → Settings → Product Choices → "Add Product Choice"
```

### Delete a Choice
```
Click the trash icon next to the choice
```

## Troubleshooting

**Field doesn't appear?**
- Check Gravity Forms is 2.5+
- Reactivate the plugin

**Total not updating?**
- Ensure you added a "Total" field
- Check browser console for errors

**Payment not working?**
- Verify payment feed is configured
- Check test mode is enabled
- Use correct test card numbers

## Next Steps

1. ✓ Create your first form (done!)
2. Read full documentation: `README.md`
3. Check installation guide: `INSTALLATION.md`
4. Review implementation: `IMPLEMENTATION-SUMMARY.md`
5. Test payment gateways
6. Go live!

## Get Help

- 📖 Read: `INSTALLATION.md` for detailed setup
- 🐛 Issues: GitHub Issues
- 💡 Ideas: Feature requests welcome

## File Locations

```
Plugin Location:
/wp-content/plugins/gravityforms-payment-checkboxes/

Important Files:
- README.md (full documentation)
- INSTALLATION.md (detailed setup)
- IMPLEMENTATION-SUMMARY.md (technical details)
```

---

**Total Time:** ~5 minutes
**Difficulty:** Easy
**Result:** Working checkbox products form! ✨
