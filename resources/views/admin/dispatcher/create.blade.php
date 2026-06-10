<div id="output"></div>
<div class="row">
    <div class="col-lg-5">
        @if(Session::has('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <form class="form order_form" method="post" action="{!! route('dispatcher.store') !!}">
            @csrf

            @if (auth()->user()->role_id == 1)
                <h2 class="bo-heading">{{ __('messages.book_a_order') }}</h2>
            @endif

            {{-- ─── 1 · Customer & package ─────────────────────────────── --}}
            <div class="bo-section">
                <div class="bo-section-title"><span class="bo-step">1</span> Customer &amp; package</div>
                <div class="bo-grid-2">
                    @if (auth()->user()->role_id == 1)
                        <div class="form-group">
                            <label>{{ __('messages.select_a_customer') }}</label>
                            <input type="text" name="select_customer" class="form-control" placeholder="{{ __('messages.enter_a_customer') }}">
                            <input type="hidden" name="customer_id" class="form-control">
                        </div>
                    @else
                        <input type="hidden" name="customer_id" class="form-control" value="{{ auth()->user()->id }}">
                    @endif
                    <div class="form-group">
                        <label>{{ __('messages.select_a_package') }}</label>
                        <select name="select_package" class="form-control custom-select">
                            <option value="">{{ __('messages.select_a_package') }}</option>
                            @foreach($packages as $package)
                                <option per_km_charges="{!! $package->per_km_charges !!}" value="{!! $package->id !!}">{!! $package->name !!}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __('messages.description') }}</label>
                    <textarea rows="2" name="description" class="form-control" placeholder="{{ __('messages.short_description') }}"></textarea>
                </div>
            </div>

            {{-- ─── 2 · When ───────────────────────────────────────────── --}}
            <div class="bo-section">
                <div class="bo-section-title"><span class="bo-step">2</span> When</div>
                <div class="bo-segment" role="tablist">
                    <a class="pickschedual bo-seg active" id="PickupNow">
                        <i class="fas fa-bolt"></i> Pickup Now
                    </a>
                    <a class="pickschedual bo-seg" id="ScheduleBooking">
                        <i class="far fa-calendar-alt"></i> Schedule Booking
                    </a>
                </div>

                <div class="schedulePick d-none">
                    <div class="bo-segment bo-segment-sub">
                        <label class="bo-seg active" for="helperwithout">
                            <input type="radio" class="form-control-input" value="Without" id="helperwithout" name="helper" checked>
                            Without Helper
                        </label>
                        <label class="bo-seg" for="helperwith">
                            <input type="radio" class="form-control-input" id="helperwith" value="With" name="helper">
                            With Helper
                        </label>
                    </div>

                    {{-- Schedule (without helper) --}}
                    <div class="without">
                        <div class="bo-grid-2">
                            <div class="form-group">
                                <label>{{ __('messages.schedule_date') }}</label>
                                <input type="date" value="{!! date('Y-m-d') !!}" name="picked_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>{{ __('messages.schedule_time') }}</label>
                                <input type="time" value="{!! date('H:i:s') !!}" name="picked_time" class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- Schedule with helper --}}
                    <div class="with d-none">
                        <div class="bo-grid-2">
                            <div class="form-group">
                                <label>Total Helpers</label>
                                <input type="number" name="total_helper" id="total_helper"
                                       oninput="this.value = Math.round(this.value);"
                                       class="form-control" placeholder="Enter Total Helpers" min="1">
                            </div>
                            <div class="form-group">
                                <label>Hours</label>
                                <input type="number" name="end" id="end" class="form-control"
                                       oninput="this.value = Math.round(this.value);"
                                       placeholder="Enter Hours" value="" min="1">
                            </div>
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="start"
                                       min="{!! Date('Y-m-d', strtotime('+2 days')) !!}"
                                       value="{!! Date('Y-m-d', strtotime('+2 days')) !!}"
                                       id="start" onchange="onChangeAjax(this.value)" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Time</label>
                                <select name="times" class="form-control" onchange="getTimvalue(this.value)" id="ajaxHours">
                                    <option selected="" disabled="">Select Time</option>
                                    @php $hours = App\Models\Helper::getOptionsTimes('h:i') @endphp
                                    @foreach($hours as $hour)
                                        <option>{{ date("H:i", $hour) }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="time" value="">
                    </div>
                </div>
            </div>

            {{-- ─── 3 · Route ──────────────────────────────────────────── --}}
            <div class="bo-section">
                <div class="bo-section-title"><span class="bo-step">3</span> Route</div>
                <div class="form-group">
                    <label>{{ __('messages.cargo_taxi_stand') }}</label>
                    <input type="text" name="pick_location" id="start_address" class="form-control bo-readonly"
                           placeholder="{{ __('messages.cargo_taxi_stand') }}" value="Freudenauer Hafenstraße 8-10, Vienna, Austria" readonly="" district_id='2'>
                </div>
                <div class="form-group">
                    <label>{{ __('messages.pickup_address') }}</label>
                    <input type="text" name="mid[]" id="pick_address" class="form-control pac-target-input" autocomplete="off"
                           placeholder="{{ __('messages.enter_pickup_address') }}">
                </div>
                <div id="div" class="form-group">
                    <div class="input_fields_wrap form-group">
                        <div><a class="add_field_button pointer">+ {{ __('messages.add_drop_address') }}</a></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __('messages.dropoff_end_address') }}</label>
                    <input type="text" name="end_location" id="end_address" class="form-control"
                           placeholder="{{ __('messages.enter_dropoff_end_address') }}">
                </div>
            </div>

            {{-- ─── 4 · Live estimate ──────────────────────────────────── --}}
            <div class="bo-section bo-estimate">
                <div class="bo-section-title"><span class="bo-step">4</span> Estimate</div>
                <div class="bo-est-grid">
                    <div class="bo-est-item">
                        <span class="bo-est-label">{{ __('messages.est_amount') }} ({!! $currency !!})</span>
                        <input readonly type="text" name="total_amount" class="bo-est-value" value="0">
                    </div>
                    <div class="bo-est-item">
                        <span class="bo-est-label">{{ __('messages.distance') }}</span>
                        <input readonly type="text" name="total_km" class="bo-est-value" value="0 km">
                    </div>
                    <div class="bo-est-item hiddenVal">
                        <span class="bo-est-label">{{ __('messages.total_est_time') }}</span>
                        <input readonly type="text" name="total_time" class="bo-est-value" value="0 mins">
                        <input type="hidden" name="total_time_sec" class="form-control" value="">
                        <input type="hidden" name="per_km_charges" class="form-control" value="">
                        <input type="hidden" name="start_address_district_id" class="form-control" value="">
                        <input type="hidden" name="end_address_district_id" class="form-control" value="">
                        <input type="hidden" name="fee" value="{{ optional(App\Models\HelperFee::first())->fee }}">
                        <div id="mapPrint"></div>
                    </div>
                    <div class="bo-est-item">
                        <span class="bo-est-label">Booking Fee</span>
                        <input type="number" readonly name="total_amount_fee" placeholder="—" class="bo-est-value">
                    </div>
                    <div class="bo-est-item">
                        <span class="bo-est-label">Helper Fee</span>
                        <input type="number" readonly name="helperPayment" placeholder="—" class="bo-est-value">
                    </div>
                </div>
                <input type="hidden" name="encodeString" class="form-control">
                <input type="hidden" name="unit_price" class="form-control" value="{!! $price !!}">
                <input type="hidden" name="total_km_meter" class="form-control" value="">
            </div>

            {{-- ─── Actions ────────────────────────────────────────────── --}}
            <div class="bo-actions">
                <a href="javascript:void(0)" class="btn btn-danger refresh-btn" onClick="window.location.reload();return false;" type="add">{{ __('messages.cancel') }}</a>
                <a type="button" href="#map" class="btn btn-success SaveOrderMain">{{ __('messages.next') }} <i class="fas fa-arrow-right"></i></a>
            </div>

            <textarea style="display: none;" id="polygons">{!! json_encode($polygons) !!}</textarea>
        </form>
    </div>

    <div class="col-lg-7" id="meep">
        <div class="map-box">
            <div id="map" style="height: 500px"></div>
        </div>
    </div>
</div>

<style>
    /* Bootstrap's utility CSS isn't loaded on this layout, so define the
       toggle class the show/hide logic relies on. */
    .order_form .d-none, .schedulePick.d-none { display: none !important; }

    .bo-heading { font-size: 1.25rem; font-weight: 700; margin: 0 0 1rem; }
    .order_form .bo-section { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.125rem; margin-bottom: 1rem; }
    .order_form .bo-section-title { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; margin-bottom: 0.875rem; }
    .order_form .bo-step { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: #84cc16; color: #fff; font-size: 0.75rem; font-weight: 700; }
    .order_form .bo-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1rem; }
    @media (max-width: 1200px) { .order_form .bo-grid-2 { grid-template-columns: 1fr; } }
    .order_form .form-group { margin-bottom: 0.875rem; }
    .order_form label { font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.25rem; display: block; }
    .order_form textarea.form-control { resize: vertical; min-height: 56px; }
    .order_form .bo-readonly { background: #f8fafc; color: #64748b; }

    .order_form .bo-segment { display: flex; gap: 0.5rem; margin-bottom: 0.875rem; }
    .order_form .bo-seg { flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.55rem 0.75rem; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.8125rem; font-weight: 600; color: #64748b; cursor: pointer; user-select: none; transition: all .15s; margin-bottom: 0; }
    .order_form .bo-seg:hover { border-color: #84cc16; color: #3f6212; text-decoration: none; }
    .order_form .bo-seg.active { background: #f7fee7; border-color: #84cc16; color: #3f6212; }
    .order_form .bo-seg input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }
    .order_form .bo-segment-sub .bo-seg { padding: 0.45rem 0.75rem; border-radius: 8px; }

    .order_form .bo-est-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.625rem; }
    @media (max-width: 1200px) { .order_form .bo-est-grid { grid-template-columns: repeat(2, 1fr); } }
    .order_form .bo-est-item { background: #f8fafc; border: 1px solid #eef2f7; border-radius: 10px; padding: 0.5rem 0.75rem; }
    .order_form .bo-est-label { display: block; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8; margin-bottom: 0.125rem; }
    .order_form .bo-est-value { border: none; background: transparent; width: 100%; font-size: 1.0625rem; font-weight: 700; color: #0f172a; padding: 0; outline: none; }

    .order_form .add_field_button { font-size: 0.8125rem; font-weight: 600; color: #4d7c0f; }
    .order_form .add_field_button:hover { text-decoration: underline; }
    .order_form .input_fields_wrap > div { position: relative; margin-bottom: 0.5rem; }
    .order_form .input_fields_wrap .remove_field { position: absolute; right: 0; top: 0; cursor: pointer; color: #dc2626; }

    .order_form .bo-actions { display: flex; justify-content: flex-end; gap: 0.625rem; margin-top: 0.25rem; }
    .order_form .bo-actions .btn { padding: 0.55rem 1.5rem; border-radius: 10px; font-weight: 600; }
</style>

<script type="text/javascript">
    $(document).ready(function () {
        // Pickup Now / Schedule Booking segmented control. "Pickup Now" is the
        // default: the schedule block stays hidden and the date/time inputs
        // submit their "now" defaults — same payload the backend always got.
        $('.pickschedual').click(function () {
            $('.pickschedual').removeClass('active');
            $(this).addClass('active');
            if (this.id === 'ScheduleBooking') {
                $('.schedulePick').removeClass('d-none');
            } else {
                $('.schedulePick').addClass('d-none');
            }
        });

        // Without / With helper toggle inside the schedule block.
        $("[name='helper']").on('change click', function () {
            $('.bo-segment-sub .bo-seg').removeClass('active');
            $(this).closest('.bo-seg').addClass('active');
            if (this.id === 'helperwithout') {
                $('.without').removeClass('d-none');
                $('.with').addClass('d-none');
            } else {
                $('.without').addClass('d-none');
                $('.with').removeClass('d-none');
            }
        });
    });
</script>
