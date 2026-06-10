<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Select Payment Gateway</h5>
        <button type="button" class="close" data-dismiss="modal">
            <i class="fal fa-close"></i>
        </button>
    </div>
    <div class="modal-body PaymentGateway position-relative">
        <div class="loader-layout" style="display: none;">
            <div class="loading"><i class="fal fa-sync"></i>
                <p>Please wait...</p></div>
        </div>
        <form action="" class="form" method="post">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        @foreach($gateways as $key => $gateway)
                            @if($gateway->name!='Weekly' || auth()->user()->isWeekly==1)
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <div class="custom-control custom-radio vehicle-switch">
                                                <input  value="{!! $gateway->id !!}" type="radio" id="gateway_{!! $gateway->id !!}" name="gateway" class="custom-control-input" tabindex="0">
                                                <label class="custom-control-label d-block width" for="gateway_{!! $gateway->id !!}">
                                                    <span class="pl-3 pr-3 d-block">{!! $gateway->name !!}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="form-group col-lg-12 text-center mt-3 PaymentButtons">
                    <div class="Paypal"></div>
                    <div class="CashOnDelivery">
                        <button type="button" class="btn btn-success SaveOrder">Pay</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<style>
    input[name="gateway"]{
        cursor:pointer;
    }
</style>
