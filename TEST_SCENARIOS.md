# 🧪 Live Tracking Test Scenarios

## Quick Test Commands

### 1. Setup Test Data
```bash
php artisan db:seed --class=LiveTrackingTestSeeder
```

### 2. Run Test Scenarios

**Basic Movement Test:**
```bash
php artisan test:live-tracking --scenario=movement
```

**Riders with Orders:**
```bash
php artisan test:live-tracking --scenario=orders
```

**Offline Riders:**
```bash
php artisan test:live-tracking --scenario=offline
```

**Multiple Scenarios:**
```bash
php artisan test:live-tracking --scenario=multiple
```

**All Scenarios:**
```bash
php artisan test:live-tracking --scenario=all
```

### 3. Simulate Individual Rider
```bash
# Simulate rider ID 1 moving for 60 seconds at 50 km/h
php artisan simulate:rider-movement 1 --duration=60 --speed=50

# Simulate rider ID 2 moving for 2 minutes at 80 km/h
php artisan simulate:rider-movement 2 --duration=120 --speed=80
```

---

## 📋 Realistic Test Cases

### Test Case 1: City Delivery Route
**Scenario:** Rider picks up package in city center and delivers to airport

```bash
# 1. Assign order to rider
php artisan tinker
>>> $rider = \App\User::where('role_id', 2)->first();
>>> $order = \App\Models\Order::create(['customer_id' => 1, 'rider_id' => $rider->id, 'booking_id' => 'CITY001', 'order_status' => 'picking', 'start_location' => 'Vienna City Center', 'end_location' => 'Vienna Airport', 'total_amount' => 150]);

# 2. Simulate movement from city to airport
php artisan simulate:rider-movement {$rider->id} --duration=300 --speed=60
```

**Expected:**
- ✅ Orange marker (rider has order)
- ✅ Route line from city to airport
- ✅ Info window shows order details
- ✅ Status changes as rider moves

---

### Test Case 2: Multiple Riders in Different Areas
**Scenario:** 5 riders moving simultaneously in different parts of Vienna

```bash
# Get rider IDs
php artisan tinker
>>> \App\User::where('role_id', 2)->pluck('id')->toArray();

# Run movement test
php artisan test:live-tracking --scenario=movement
```

**Expected:**
- ✅ All 5 riders visible on map
- ✅ Each moving independently
- ✅ Different colored markers based on status
- ✅ Smooth updates without lag

---

### Test Case 3: Rider Status Changes
**Scenario:** Rider goes from available → on order → delivered → available

```bash
# 1. Start with available rider (green marker)
php artisan tinker
>>> $rider = \App\User::where('role_id', 2)->first();
>>> $rider->update(['lat' => '48.2082', 'long' => '16.3738']);

# 2. Assign order (marker turns orange)
>>> $order = \App\Models\Order::create(['customer_id' => 1, 'rider_id' => $rider->id, 'booking_id' => 'STATUS001', 'order_status' => 'picking', 'start_location' => 'Pickup', 'end_location' => 'Delivery', 'total_amount' => 100]);

# 3. Simulate delivery
php artisan simulate:rider-movement {$rider->id} --duration=120 --speed=50

# 4. Complete order (marker turns green again)
>>> $order->update(['order_status' => 'delivered']);
```

**Expected:**
- ✅ Marker color changes (green → orange → green)
- ✅ Status updates in sidebar
- ✅ Order info appears/disappears

---

### Test Case 4: Connection Recovery
**Scenario:** Test system recovery after connection loss

```bash
# 1. Start tracking
php artisan simulate:rider-movement 1 --duration=60

# 2. Stop the command (simulate connection loss)
# Press Ctrl+C

# 3. Restart tracking
php artisan simulate:rider-movement 1 --duration=60
```

**Expected:**
- ✅ Connection status shows "Disconnected" then "Connected"
- ✅ Map continues updating when connection restored
- ✅ No data loss

---

### Test Case 5: High-Speed Movement
**Scenario:** Rider moving at highway speed (100+ km/h)

```bash
php artisan simulate:rider-movement 1 --duration=180 --speed=100
```

**Expected:**
- ✅ Smooth marker movement
- ✅ Accurate position updates
- ✅ Trail shows high-speed path
- ✅ No jitter or lag

---

### Test Case 6: Slow City Movement
**Scenario:** Rider in traffic (20 km/h)

```bash
php artisan simulate:rider-movement 1 --duration=300 --speed=20
```

**Expected:**
- ✅ Gradual marker movement
- ✅ Frequent updates
- ✅ Accurate city navigation

---

### Test Case 7: Rider Going Offline
**Scenario:** Rider stops updating location

```bash
# 1. Rider is moving
php artisan simulate:rider-movement 1 --duration=30

# 2. Clear location (simulate going offline)
php artisan tinker
>>> $rider = \App\User::find(1);
>>> $rider->update(['lat' => null, 'long' => null]);
```

**Expected:**
- ✅ Marker disappears from map
- ✅ Rider card shows "Offline"
- ✅ Stats update

---

### Test Case 8: Follow Feature
**Scenario:** Follow a specific rider as they move

```bash
# 1. Open /live/map in browser
# 2. Double-click a rider marker (or click Follow button)
# 3. Run simulation
php artisan simulate:rider-movement {rider_id} --duration=120 --speed=60
```

**Expected:**
- ✅ Map auto-centers on rider
- ✅ Map follows rider movement
- ✅ Zoom level maintained
- ✅ Smooth following

---

### Test Case 9: Multiple Orders Same Rider
**Scenario:** Rider with multiple active orders

```bash
php artisan tinker
>>> $rider = \App\User::where('role_id', 2)->first();
>>> \App\Models\Order::create(['customer_id' => 1, 'rider_id' => $rider->id, 'booking_id' => 'MULTI1', 'order_status' => 'on_way', 'start_location' => 'Loc1', 'end_location' => 'Loc2', 'total_amount' => 100]);
>>> \App\Models\Order::create(['customer_id' => 1, 'rider_id' => $rider->id, 'booking_id' => 'MULTI2', 'order_status' => 'picking', 'start_location' => 'Loc3', 'end_location' => 'Loc4', 'total_amount' => 150]);
```

**Expected:**
- ✅ Rider shows as "On Order"
- ✅ Multiple routes visible (if supported)
- ✅ Info window shows order details

---

### Test Case 10: Edge Cases

**Test Invalid Coordinates:**
```bash
php artisan tinker
>>> $rider = \App\User::where('role_id', 2)->first();
>>> $rider->update(['lat' => '999', 'long' => '999']); # Invalid
```

**Expected:**
- ✅ Invalid coordinates filtered out
- ✅ Rider doesn't appear on map
- ✅ No errors in console

**Test Empty Coordinates:**
```bash
>>> $rider->update(['lat' => '', 'long' => '']);
```

**Expected:**
- ✅ Rider shows as offline
- ✅ No marker on map

**Test Rapid Updates:**
```bash
# Update location 10 times per second
for i in {1..100}; do
  php artisan tinker --execute="\$rider = \App\User::where('role_id', 2)->first(); \$rider->update(['lat' => (48.2082 + rand(-100,100)/10000), 'long' => (16.3738 + rand(-100,100)/10000)]);"
  sleep 0.1
done
```

**Expected:**
- ✅ System handles rapid updates
- ✅ No performance degradation
- ✅ Smooth marker movement

---

## 🎯 Testing Checklist

When testing, verify:

- [ ] Riders appear on map with correct markers
- [ ] Markers move smoothly as riders travel
- [ ] Info windows show correct data
- [ ] Routes are drawn for active orders
- [ ] Connection status is accurate
- [ ] Filters work correctly
- [ ] Follow feature works
- [ ] Movement trails are visible
- [ ] No console errors
- [ ] Stable performance with multiple riders
- [ ] Offline riders handled correctly
- [ ] Status changes reflected immediately
- [ ] Map controls work (traffic, satellite, center)
- [ ] Sidebar updates in real-time
- [ ] Stats panel shows correct counts

---

## 📊 Performance Benchmarks

**Expected Performance:**
- ✅ Map loads in < 2 seconds
- ✅ Marker updates in < 100ms
- ✅ Handles 50+ riders simultaneously
- ✅ Smooth 60fps marker movement
- ✅ No memory leaks after 1 hour

---

## 🔧 Troubleshooting

**Issue:** Riders not moving
- Check if test command is running
- Verify database updates: `SELECT id, lat, long FROM users WHERE role_id = 2`
- Check browser console for errors

**Issue:** Firebase errors
- Firebase errors are OK - AJAX polling will still work
- Database updates are sufficient for testing
- Check `/live/map/ajax` endpoint directly

**Issue:** Map not loading
- Check Google Maps API key
- Verify internet connection
- Check browser console for API errors

