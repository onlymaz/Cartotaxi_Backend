# Live Tracking Test Guide

This guide explains how to test the live tracking functionality with realistic scenarios.

## 🚀 Quick Start

### 1. Setup Test Data
```bash
php artisan db:seed --class=LiveTrackingTestSeeder
```

This will:
- Create 5 test riders
- Set their initial locations in Vienna
- Create some test orders
- Update Firebase with locations

### 2. Open Live Map
Navigate to: `http://localhost:9000/live/map`

## 📋 Test Scenarios

### Scenario 1: Basic Rider Movement
Simulates riders moving along realistic routes.

```bash
php artisan test:live-tracking --scenario=movement
```

**What it tests:**
- ✅ Rider markers appear on map
- ✅ Markers move smoothly as riders travel
- ✅ Location updates in real-time
- ✅ Movement trails are visible (if enabled)

**Expected Result:**
- 2 riders will move along different routes
- You'll see their markers moving on the map
- Movement trails will be drawn (blue lines)

---

### Scenario 2: Riders with Active Orders
Simulates riders delivering orders with routes.

```bash
php artisan test:live-tracking --scenario=orders
```

**What it tests:**
- ✅ Riders with active orders show orange markers
- ✅ Route visualization from pickup to delivery
- ✅ Order info in sidebar and info windows
- ✅ Status updates as riders move

**Expected Result:**
- Riders will have active orders assigned
- Orange markers (busy status)
- Routes drawn from pickup to delivery location
- Order details visible in info windows

---

### Scenario 3: Offline Riders
Simulates riders going offline.

```bash
php artisan test:live-tracking --scenario=offline
```

**What it tests:**
- ✅ Riders disappear from map when offline
- ✅ Status changes to "Offline" in sidebar
- ✅ Connection status handling

**Expected Result:**
- Rider markers disappear
- Rider cards show "Offline" status
- Stats update to reflect offline riders

---

### Scenario 4: Multiple Concurrent Scenarios
Tests multiple riders in different states simultaneously.

```bash
php artisan test:live-tracking --scenario=multiple
```

**What it tests:**
- ✅ Multiple riders moving simultaneously
- ✅ Different statuses (available, on order, offline)
- ✅ System handles concurrent updates
- ✅ Performance with multiple markers

**Expected Result:**
- 3 riders in different states
- One available (green marker)
- One on order (orange marker)
- One offline (no marker)

---

### Scenario 5: All Scenarios Combined
Runs all test scenarios in sequence.

```bash
php artisan test:live-tracking --scenario=all
```

**What it tests:**
- ✅ Complete system functionality
- ✅ State transitions
- ✅ Error recovery
- ✅ System stability

---

## 🚗 Individual Rider Simulation

Simulate a single rider moving at a specific speed:

```bash
php artisan simulate:rider-movement {rider_id} --duration=60 --speed=50
```

**Parameters:**
- `rider_id`: The ID of the rider to simulate
- `--duration`: How long to simulate (seconds, default: 60)
- `--speed`: Speed in km/h (default: 50)

**Example:**
```bash
# Simulate rider ID 5 moving at 60 km/h for 2 minutes
php artisan simulate:rider-movement 5 --duration=120 --speed=60
```

**What it tests:**
- ✅ Realistic movement patterns
- ✅ Continuous location updates
- ✅ Speed-based calculations
- ✅ Long-duration tracking

---

## 🧪 Manual Test Cases

### Test Case 1: Real-Time Updates
1. Open `/live/map` in browser
2. Run: `php artisan simulate:rider-movement 1 --duration=30`
3. **Expected:** See rider marker moving in real-time on map

### Test Case 2: Multiple Riders
1. Open `/live/map` in browser
2. Run: `php artisan test:live-tracking --scenario=movement`
3. **Expected:** Multiple riders moving simultaneously

### Test Case 3: Follow Rider
1. Open `/live/map` in browser
2. Double-click a rider marker
3. Run: `php artisan simulate:rider-movement {rider_id} --duration=60`
4. **Expected:** Map auto-centers and follows the rider

### Test Case 4: Connection Status
1. Open `/live/map` in browser
2. Stop Firebase or disconnect internet
3. **Expected:** Connection status shows "Disconnected"
4. Reconnect
5. **Expected:** Status returns to "Connected"

### Test Case 5: Filter Functionality
1. Open `/live/map` in browser
2. Use status filter dropdown
3. **Expected:** Rider list filters correctly
4. Click rider card
5. **Expected:** Map centers on that rider

### Test Case 6: Movement Trails
1. Open `/live/map` in browser
2. Click "Toggle Movement Trails" button
3. Run: `php artisan simulate:rider-movement 1 --duration=60`
4. **Expected:** Blue trail line shows rider's path

### Test Case 7: Route Visualization
1. Assign an order to a rider
2. Open `/live/map` in browser
3. **Expected:** Orange route line from pickup to delivery

### Test Case 8: Info Windows
1. Open `/live/map` in browser
2. Click any rider marker
3. **Expected:** Info window shows:
   - Rider name
   - Status (Available/On Order)
   - Phone number
   - Active order details (if any)
   - Follow button

### Test Case 9: Performance with Many Riders
1. Create 20+ test riders
2. Open `/live/map` in browser
3. **Expected:** All markers load and update smoothly

### Test Case 10: Offline to Online Transition
1. Make a rider offline: Clear their lat/lng
2. Open `/live/map` in browser
3. **Expected:** Rider not visible
4. Update rider location
5. **Expected:** Rider appears on map

---

## 📊 Test Data Locations

The test seeder uses realistic Vienna locations:

- **City Center**: 48.2082, 16.3738 (Stephansplatz)
- **North**: 48.2200, 16.3600
- **South**: 48.1950, 16.3900
- **East**: 48.2100, 16.4000
- **West**: 48.2050, 16.3500
- **Airport**: 48.1103, 16.5697

---

## 🔍 Debugging

### Check Firebase Connection
```bash
# Check if Firebase is accessible
php artisan tinker
>>> \App\Utilities\FireBaseRealTimeDatabase::StoreData('test/test', ['test' => 'data']);
```

### Check Rider Locations
```bash
php artisan tinker
>>> \App\User::where('role_id', 2)->select('id', 'first_name', 'lat', 'long')->get();
```

### Monitor Updates
Open browser console (F12) and watch for:
- `Map initialized successfully`
- `Rider location updates`
- `Connection status changes`

---

## ✅ Success Criteria

A successful test should show:
1. ✅ Riders appear on map with correct markers
2. ✅ Markers move smoothly as riders travel
3. ✅ Info windows show correct data
4. ✅ Routes are drawn for active orders
5. ✅ Connection status is accurate
6. ✅ Filters work correctly
7. ✅ Follow feature works
8. ✅ Movement trails are visible
9. ✅ No console errors
10. ✅ Stable performance

---

## 🐛 Common Issues

### Issue: Riders not appearing
**Solution:**
- Check if riders have valid lat/lng in database
- Verify Firebase connection
- Check browser console for errors

### Issue: Markers not moving
**Solution:**
- Ensure test command is running
- Check Firebase updates are being sent
- Verify AJAX polling is working (check Network tab)

### Issue: Connection status shows "Error"
**Solution:**
- Check Firebase configuration
- Verify API endpoint is accessible
- Check server logs for errors

---

## 📝 Notes

- Test commands update both database and Firebase
- Real-time updates depend on Firebase connection
- AJAX polling provides fallback every 3 seconds
- Movement trails require at least 2 location points
- Route visualization needs valid start/end coordinates

