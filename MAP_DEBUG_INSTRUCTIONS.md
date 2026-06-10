# Map Debug Instructions

## Current Status
The map is showing as blank. Here's how to debug:

## Step 1: Check Browser Console
1. Open the page: `http://localhost:9000/live/map`
2. Press **F12** to open Developer Tools
3. Go to the **Console** tab
4. Look for any **RED errors**

## Step 2: Run Diagnostic
In the browser console, type:
```javascript
window.testGoogleMaps()
```

This will show:
- If Google Maps API is loaded
- Map element dimensions
- Map object status

## Step 3: Check Network Tab
1. Go to **Network** tab in DevTools
2. Filter by "JS" (JavaScript)
3. Look for `maps.googleapis.com` requests
4. Check if they return **200 OK** or show errors

## Step 4: Manual Map Test
In the browser console, try:
```javascript
// Check if map element exists
const mapEl = document.getElementById('liveTrackingMap');
console.log('Map element:', mapEl);
console.log('Dimensions:', mapEl ? mapEl.getBoundingClientRect() : 'NOT FOUND');

// Check if Google Maps is loaded
console.log('Google Maps:', typeof google !== 'undefined' ? 'LOADED' : 'NOT LOADED');
console.log('google.maps.Map:', typeof google !== 'undefined' && typeof google.maps !== 'undefined' && typeof google.maps.Map !== 'undefined' ? 'AVAILABLE' : 'NOT AVAILABLE');

// Check if map object exists
console.log('Map object:', typeof map !== 'undefined' ? map : 'NOT DEFINED');
```

## Common Issues:

1. **API Key Invalid**: Check `.env` file has `GOOGLE_MAPS_API_KEY=your_key`
2. **API Restrictions**: Google Cloud Console might be blocking localhost
3. **Network Issues**: Check if `maps.googleapis.com` is accessible
4. **Syntax Errors**: Check console for JavaScript errors

## What to Share:
Please share:
1. Any console errors (red text)
2. Output of `window.testGoogleMaps()`
3. Network tab errors for `maps.googleapis.com`
4. Screenshot of the console

