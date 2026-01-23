# Distance Pricing Field Setup Guide

The Distance Pricing field allows you to automatically calculate and charge for delivery/service based on the distance from your location to the customer's address.

## Required Google APIs

To use the Distance Pricing field, you need a Google Cloud Platform account with the following APIs enabled:

### 1. Distance Matrix API

**Required** - Used to calculate the actual driving distance between two locations.

### 2. Geocoding API

**Required** - Used to convert addresses into geographic coordinates.

### 3. Places API (Optional)

**Optional** - Provides address autocomplete functionality for better user experience.

## Setup Instructions

### Step 1: Create a Google Cloud Project

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Click "Select a project" at the top of the page
3. Click "New Project"
4. Enter a project name (e.g., "My Website Distance Pricing")
5. Click "Create"

### Step 2: Enable Required APIs

1. In the Google Cloud Console, navigate to **APIs & Services** > **Library**
2. Search for and enable each of the following APIs:
   - **Distance Matrix API**
     - Click on "Distance Matrix API"
     - Click "Enable"
   - **Geocoding API**
     - Click on "Geocoding API"
     - Click "Enable"
   - **Places API** (Optional but recommended)
     - Click on "Places API"
     - Click "Enable"

### Step 3: Create API Credentials

1. Navigate to **APIs & Services** > **Credentials**
2. Click **"Create Credentials"** > **"API Key"**
3. Your new API key will be displayed
4. **Important**: Click "Restrict Key" to secure your API key

### Step 4: Restrict Your API Key (Recommended)

For security, you should restrict your API key:

#### Application Restrictions:

- Select **"HTTP referrers (web sites)"**
- Add your website domain(s):
  - `https://yourdomain.com/*`
  - `https://www.yourdomain.com/*`
  - For local development: `http://localhost/*`

#### API Restrictions:

- Select **"Restrict key"**
- Choose the following APIs:
  - Distance Matrix API
  - Geocoding API
  - Places API (if enabled)

Click **"Save"** to apply restrictions.

### Step 5: Configure Plugin Settings

1. In WordPress admin, go to **Forms** > **Checkbox Products Settings**
2. Paste your Google Maps API key in the **"Google Maps API Key"** field
3. Click **"Save Settings"**

## Using the Distance Pricing Field

### Adding the Field to Your Form

1. Edit your Gravity Form
2. In the form editor, look for **"Distance Pricing"** in the Pricing Fields section
3. Drag it onto your form

### Configuring the Field

The Distance Pricing field has the following settings:

- **Price per Mile/KM**: The amount to charge per unit of distance
  - Example: `2.50` would charge $2.50 per mile/km
- **Starting Location (Postal/Zip Code)**: Your business location
  - Example: `90210` or `M5H 2N2`
  - Can be a full address, city, or postal code
- **Free Zone Distance**: Distance within which no charge applies
  - Example: `10` means the first 10 miles/km are free
  - Only distance beyond this is charged
- **Distance Unit**: Choose between Miles or Kilometers
- **Link to Address Field**: Select which address field the user will fill in
  - You must have an Address field in your form first

### Example Configuration

**Scenario**: Delivery service with $3 per mile charge, free within 5 miles

- Price per Mile/KM: `3.00`
- Starting Location: `90210`
- Free Zone Distance: `5`
- Distance Unit: `Miles`
- Link to Address Field: Select your form's address field

**Result**:

- Customer 3 miles away: No charge (within free zone)
- Customer 10 miles away: $15.00 (5 miles × $3.00)
- Customer 20 miles away: $45.00 (15 miles × $3.00)

## How It Works

1. User fills in the address field on your form
2. When they complete the address, the plugin automatically:
   - Sends the address to Google Maps API
   - Calculates the driving distance from your location
   - Subtracts the free zone distance
   - Multiplies remaining distance by your price per unit
   - Adds the charge to the form total
3. The distance and charge are displayed to the user
4. The charge is included in the final form total and payment

## Pricing Information

Google Maps API usage is **not free** but includes a generous free tier:

- **$200 free credit per month** (covers ~40,000 distance calculations)
- After free tier: ~$5 per 1,000 distance calculations

For most small to medium businesses, the free tier is sufficient.

**Monitor your usage**: Set up billing alerts in Google Cloud Console to avoid unexpected charges.

## Troubleshooting

### "Google Maps API not loaded" Error

- Verify your API key is correctly entered in plugin settings
- Check that the Distance Matrix API is enabled in Google Cloud Console
- Clear your browser cache and reload the page

### "Could not calculate distance" Error

- Verify the starting location is a valid address/postal code
- Ensure the user's address is complete and valid
- Check that Geocoding API is enabled

### Distance Calculation Not Triggering

- Ensure you've linked the field to an Address field
- Make sure the address field is the standard Gravity Forms Address field type
- Check browser console for JavaScript errors

### API Key Restrictions Issues

- If using HTTP referrer restrictions, ensure your domain is correctly added
- For local development, add `http://localhost/*` to allowed referrers
- Verify all three APIs are selected in API restrictions

## Support

For issues specific to:

- **Google Maps API**: Check [Google Maps Platform Documentation](https://developers.google.com/maps/documentation)
- **This Plugin**: Create an issue on the GitHub repository

## Security Best Practices

1. **Always restrict your API key** - Never use an unrestricted key in production
2. **Monitor API usage** - Set up billing alerts in Google Cloud Console
3. **Use HTTPS** - Ensure your website uses HTTPS to protect the API key
4. **Rotate keys periodically** - Generate new keys every 6-12 months
5. **Keep WordPress updated** - Ensure WordPress and all plugins are up to date
