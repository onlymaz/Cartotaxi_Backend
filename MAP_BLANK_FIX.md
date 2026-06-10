# Map Blank Screen - Fix Guide

## Issue
The map is showing as blank/white screen.

## Possible Causes

1. **Google Maps API not loaded**
   - Check browser console for errors
   - Verify API key is correct in `.env` file
   - Check if API key has Maps JavaScript API enabled

2. **JavaScript errors**
   - Open browser console (F12)
   - Look for red error messages
   - Check Network tab for failed requests

3. **Map container not found**
   - Element `#liveTrackingMap` should exist
   - Check if container has proper height/width

## Quick Fixes

### 1. Hard Refresh
- Windows/Linux: `Ctrl + Shift + R` or `Ctrl + F5`
- Mac: `Cmd + Shift + R`

### 2. Check Browser Console
1. Press F12 to open Developer Tools
2. Go to Console tab
3. Look for errors (red text)
4. Share any errors you see

### 3. Check Network Tab
1. Press F12 > Network tab
2. Refresh page
3. Look for `maps.googleapis.com` request
4. Check if it returns 200 OK or shows an error

### 4. Verify API Key
- Check `.env` file has `GOOGLE_MAPS_API_KEY=your_key_here`
- Verify key is valid in Google Cloud Console
- Ensure Maps JavaScript API is enabled

## What Should Happen

1. Map should initialize and show Vienna, Austria
2. Console should show: "✅ Map initialized successfully"
3. Map should be visible with Google Maps interface

## If Still Blank

Please provide:
1. Browser console errors (F12 > Console)
2. Network tab errors (F12 > Network)
3. Any error messages on the page

