
  <div class="modal fade" id="rating_model" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Rating The Rider</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form method="post">
        <div class="modal-body">
          <div class="starrating" style="float: left !important;">
            <input type="radio" style="pointer-events: " id="star_2_5" name="rating_2" value="5" class="rating_1">
          <label for="star_2_5" title="5 star" class=""></label>
            <input type="radio" style="pointer-events: " id="star_2_4" name="rating_2" value="4" class="rating_1">
          <label  for="star_2_4" title="4 star" class=""></label>
            <input type="radio" style="pointer-events: " id="star_2_3" name="rating_2" value="3" class="rating_1">
          <label  for="star_2_3" title="3 star" class=""></label>
            <input type="radio" style="pointer-events: " id="star_2_2" name="rating_2" value="2" class="checked  rating_1">
          <label for="star_2_2" title="2 star" class=""></label>
            <input type="radio" style="pointer-events: " id="star_2_1" name="rating_2" value="1" class="checked  rating_1">
          <label  for="star_2_1" title="1 star" class=""></label>
          </div>
          <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <div class="star-rating"><s><s><s><s><s></s></s></s></s></s></div>
        <div class="star-rating-rtl"><s><s><s><s><s></s></s></s></s></s></div>
        </div>
        <textarea name="comments" id="Feedback"  placeholder="Feedback"></textarea>
        <input type="hidden" name="rating_o_id" id="rating_o_id" >
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-default" data-dismiss="modal" id="submit_rating" >Submit</button>
        </div>
        </form>
      </div>
      
    </div>
  </div>
  <script type="text/javascript">
    
            $("body").on('click', '.rating_1', function() {
               //alert();
               var rating  = $(this).val();
            $("body").on('click', '#submit_rating', function() {
            var Feedback = $('#Feedback').val();
            var order_id = $('#rating_o_id').val();
            $.ajax({
                url     :   "{{url('customer.bookings.RateRider')}}",
                type    :   'post',
                data    :   {
                      oid:order_id,
                      rating :rating,
                      comments:Feedback,
                    _token   : '{!! csrf_token() !!}',
                },
                success : function(data){
                    console.log(data);
                }
                });
            });
          });
  </script>
