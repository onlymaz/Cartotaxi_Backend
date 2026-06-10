# 🚀 Quick Test Guide - Live Tracking

## ⚡ Quick Start (30 seconds)

1. **Open the map:**
   ```
   http://localhost:9000/live/map
   ```

2. **Run a test in another terminal:**
   ```bash
   php artisan test:live-tracking --scenario=movement
   ```

3. **Watch the map** - You should see riders moving!

---

## 🧪 Available Test Scenarios

### 1. Movement Test (2 riders moving)
```bash
php artisan test:live-tracking --scenario=movement
```
**Duration:** ~30 seconds  
**What you'll see:** 2 riders moving along different routes

---

### 2. Orders Test (Riders with active orders)
```bash
php artisan test:live-tracking --scenario=orders
```
**Duration:** ~40 seconds  
**What you'll see:** Riders with orange markers delivering orders

---

### 3. Offline Test (Riders going offline)
```bash
php artisan test:live-tracking --scenario=offline
```
**Duration:** ~5 seconds  
**What you'll see:** Riders disappearing from map

---

### 4. Multiple Scenarios (All at once)
```bash
php artisan test:live-tracking --scenario=multiple
```
**Duration:** ~30 seconds  
**What you'll see:** Available, busy, and offline riders simultaneously

---

### 5. Complete Test (All scenarios)
```bash
php artisan test:live-tracking --scenario=all
```
**Duration:** ~2 minutes  
**What you'll see:** Complete system test

---

## 🚗 Individual Rider Simulation

Simulate a single rider moving at realistic speed:

```bash
# Basic: 60 seconds at 50 km/h
php artisan simulate:rider-movement 1

# Custom: 2 minutes at 80 km/h
php artisan simulate:rider-movement 1 --duration=120 --speed=80

# Slow city traffic: 5 minutes at 25 km/h
php artisan simulate:rider-movement 1 --duration=300 --speed=25

# Highway speed: 3 minutes at 100 km/h
php artisan simulate:rider-movement 1 --duration=180 --speed=100
```

**To find rider IDs:**
```bash
php artisan tinker
>>> \App\User::where('role_id', 2)->select('id', 'first_name', 'last_name')->get();
```

---

## 📋 Realistic Test Cases

### Case 1: City Delivery
```bash
# 1. Assign order
php artisan tinker
>>> $rider = \App\User::where('role_id', 2)->first();
>>> $order = \App\Models\Order::create(['customer_id' => 1, 'rider_id' => $rider->id, 'booking_id' => 'CITY001', 'order_status' => 'on_way', 'start_location' => 'City Center', 'end_location' => 'Airport', 'total_amount' => 150]);

# 2. Simulate delivery
php artisan simulate:rider-movement {$rider->id} --duration=300 --speed=60
```

### Case 2: Multiple Riders
```bash
# Run in separate terminals simultaneously:
php artisan simulate:rider-movement 1 --duration=120 --speed=50 &
php artisan simulate:rider-movement 2 --duration=120 --speed=60 &
php artisan simulate:rider-movement 3 --duration=120 --speed=40 &
```

### Case 3: Status Changes
```bash
# 1. Start available (green)
php artisan simulate:rider-movement 1 --duration=30

# 2. Assign order (orange)
php artisan tinker
>>> \App\Models\Order::create(['customer_id' => 1, 'rider_id' => 1, 'booking_id' => 'STATUS001', 'order_status' => 'on_way', 'start_location' => 'A', 'end_location' => 'B', 'total_amount' => 100]);

# 3. Continue moving
php artisan simulate:rider-movement 1 --duration=60
```

---

## ✅ What to Check

When testing, verify:

1. **Map Loading:**
   - ✅ Map loads without errors
   - ✅ Riders appear as markers
   - ✅ Connection status shows "Connected"

2. **Real-Time Updates:**
   - ✅ Markers move as riders travel
   - ✅ Updates every 3 seconds
   - ✅ Smooth movement (no jitter)

3. **Features:**
   - ✅ Click marker → Info window opens
   - ✅ Double-click → Follow rider
   - ✅ Sidebar shows rider list
   - ✅ Filters work
   - ✅ Trails toggle works

4. **Orders:**
   - ✅ Orange markers for busy riders
   - ✅ Routes drawn for active orders
   - ✅ Order info in sidebar

5. **Stability:**
   - ✅ No console errors
   - ✅ No memory leaks
   - ✅ Handles multiple riders

---

## 🐛 Common Issues

**Riders not appearing?**
- Check: `SELECT id, lat, long FROM users WHERE role_id = 2`
- Verify coordinates are valid numbers
- Check browser console for errors

**Markers not moving?**
- Ensure test command is running
- Check AJAX polling (Network tab in browser)
- Verify database is updating

**Firebase errors?**
- These are OK! AJAX polling will still work
- Database updates are sufficient for testing
- Check `/live/map/ajax` endpoint directly

---

## 📊 Test Results

After running tests, you should see:

✅ **Movement Test:** 2 riders moving smoothly  
✅ **Orders Test:** Routes drawn, orange markers  
✅ **Offline Test:** Riders disappear correctly  
✅ **Multiple Test:** All scenarios working together  
✅ **Individual Test:** Single rider tracking works  

---

## 🎯 Next Steps

1. Run all test scenarios
2. Check the live map during each test
3. Verify all features work correctly
4. Test with real mobile app (when available)

---

**Need Help?** Check `LIVE_TRACKING_TEST_GUIDE.md` for detailed documentation.

