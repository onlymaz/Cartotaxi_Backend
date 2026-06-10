<div class="modal-content">
    <div class="modal-body">
        <div class="sweetalert">
            <i class="fal fa-exclamation-circle text-success"></i>
            <h3>Success</h3>
            <p>Payment updated successfully</p>
            <p>Booking ID : {!! $order->booking_id !!}</p>
            <p>Transaction : {!! $payment->transactions !!}</p>
            <div class="footer">
                <button class="btn close_refresh">Ok</button>
            </div>
        </div>

    </div>
</div>
