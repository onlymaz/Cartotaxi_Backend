 no<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Change Status</h5>
        <button type="button" class="close" data-dismiss="modal">
            <i class="fal fa-close"></i>
        </button>
    </div>
    <div class="modal-body">
        <form class="form ajax-form-update" method="POST" data-url="{{route('bookings.change_status',$order->id)}}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="form-group col-lg-12">
                    <label>Change Status</label>
                    @if($order->order_status=='delivered')
                        <span class="badge badge-success">Delivered</span>
                    @elseif($order->order_status=='cancel')
                        <span class="badge badge-danger">Cancelled</span>
                    @elseif($order->order_status=='accident')
                        <span class="badge badge-danger">Accident</span>
                    @elseif($order->order_status=='not_received')
                        <span class="badge badge-danger">Not Received</span>
                    @elseif($order->order_status=='refused')
                        <span class="badge badge-danger">Refused</span>
                    @else
                    <select name="order_status" id="mySelect" class="form-control custom-select">

                        @if($order->order_status=='processing' )
                            <option value="picking" {!! ($order->order_status=='picking')?'selected':'' !!}>Start</option>
                        @endif
                        @if($order->order_status=='picking')
                            <option value="picked_up" {!! ($order->order_status=='picked_up')?'selected':'' !!}>Picked up</option>
                            <option value="not_received" {!! ($order->order_status=='not_received')?'selected':'' !!}>Not Received</option>
                        @endif
                        @if($order->order_status=='picked_up')
                            <option value="on_way" {!! ($order->order_status=='on_way')?'selected':'' !!}>On the way</option>
                        @endif

                        @if($order->order_status!='processing')
                            <option value="accident" {!! ($order->order_status=='accident')?'selected':'' !!}>Accident</option>
                            <option value="processing" {!! ($order->order_status=='processing')?'selected':'' !!}>processing</option>
                        @endif
                        @if($order->order_status=='on_way')
                                <option value="refused" {!! ($order->order_status=='refused')?'selected':'' !!}>Refused</option>
                                <option  id="delorder" value="delivered" {!! ($order->order_status=='delivered')?'selected':'' !!}>Delivered
                                </option>
                        @endif

                        <option value="cancel" {!! ($order->order_status=='cancel')?'selected':'' !!} >Cancelled</option>
                    </select>
                                <input type="hidden" name="signature" required>
                    @endif
                </div>
                <div class="form-group col-lg-12 text-right mt-3">
                    <div class="custom-control custom-switch" id="toggle">
                    </div>
                    <div class="form-group col-lg-12 text-left mt-3" id="boxDisplay" style="display: none">
                      <label>Reciver Name</label>
                      <input type="text" class="form-control" name="reciver_name" > 
                      <label>Reciver Address</label>
                      <input type="text" class="form-control" name="reciver_address" >
                    </div>
                    <button data-dismiss="modal" class="btn btn-danger">Cancel</button>
                    @if($order->order_status!='delivered' && $order->order_status!='cancel'  && $order->order_status!='refused' && $order->order_status!='accident' )
                    <button type="submit" class="btn btn-success" id="downloadLnk">Save</button>
                    @endif
                </div>
            </div>
        </form>
        <div id="signatures"></div>
    </div>

</div>
<script type="text/javascript">
        $(document).ready(function (){
       $('#mySelect').on('change', function() {
          var value = $(this).val();
          if(value == 'delivered')
          {
            $("#toggle").html('<div><label>Another Recvier</label></div><div><input type="checkbox" class="custom-control-input " id="another_user"  data-value="0"><label class="custom-control-label" for="another_user" onclick="showReciverBox()">');

            $('#signatures').html('<div class="row"><div class="col-md-12"><h1>E-Signature</h1><p>Sign in the canvas below and save your signature as an image!</p></div></div><div class="row"><div class="col-md-12"><canvas id="sig-canvas" style="border: 2px dotted #CCCCCC;border-radius: 15px;cursor: crosshair; width:450px;" width="620" height="160">Get a better browser, bro.</canvas> </div></div><div class="row"><button class="btn btn-default" id="sig-clearBtn">Clear Signature</button></div></div><br/><div class="row"><div class="col-md-12"><textarea id="sig-dataUrl" style="display:none" class="form-control" rows="5">DataURLforyour signature will go here!</textarea></div></div>');
    (function() {
  window.requestAnimFrame = (function(callback) {
    return window.requestAnimationFrame ||
      window.webkitRequestAnimationFrame ||
      window.mozRequestAnimationFrame ||
      window.oRequestAnimationFrame ||
      window.msRequestAnimaitonFrame ||
      function(callback) {
        window.setTimeout(callback, 1000 / 60);
      };
  })();

  var canvas = document.getElementById("sig-canvas");
  var ctx = canvas.getContext("2d");
  ctx.strokeStyle = "#222222";
  ctx.lineWidth = 4;

  var drawing = false;
  var mousePos = {
    x: 0,
    y: 0
  };
  var lastPos = mousePos;

  canvas.addEventListener("mousedown", function(e) {
    drawing = true;
    lastPos = getMousePos(canvas, e);
  }, false);

  canvas.addEventListener("mouseup", function(e) {
    drawing = false;
  }, false);

  canvas.addEventListener("mousemove", function(e) {
    mousePos = getMousePos(canvas, e);
  }, false);

  // Add touch event support for mobile
  canvas.addEventListener("touchstart", function(e) {

  }, false);

  canvas.addEventListener("touchmove", function(e) {
    var touch = e.touches[0];
    var me = new MouseEvent("mousemove", {
      clientX: touch.clientX,
      clientY: touch.clientY
    });
    canvas.dispatchEvent(me);
  }, false);

  canvas.addEventListener("touchstart", function(e) {
    mousePos = getTouchPos(canvas, e);
    var touch = e.touches[0];
    var me = new MouseEvent("mousedown", {
      clientX: touch.clientX,
      clientY: touch.clientY
    });
    canvas.dispatchEvent(me);
  }, false);

  canvas.addEventListener("touchend", function(e) {
    var me = new MouseEvent("mouseup", {});
    canvas.dispatchEvent(me);
  }, false);

  function getMousePos(canvasDom, mouseEvent) {
    var rect = canvasDom.getBoundingClientRect();
    return {
      x: mouseEvent.clientX - rect.left,
      y: mouseEvent.clientY - rect.top
    }
  }

  function getTouchPos(canvasDom, touchEvent) {
    var rect = canvasDom.getBoundingClientRect();
    return {
      x: touchEvent.touches[0].clientX - rect.left,
      y: touchEvent.touches[0].clientY - rect.top
    }
  }

  function renderCanvas() {
    if (drawing) {
      ctx.moveTo(lastPos.x, lastPos.y);
      ctx.lineTo(mousePos.x, mousePos.y);
      ctx.stroke();
      lastPos = mousePos;
    }
  }

  // Prevent scrolling when touching the canvas
  document.body.addEventListener("touchstart", function(e) {
    if (e.target == canvas) {
      e.preventDefault();
    }
  }, false);
  document.body.addEventListener("touchend", function(e) {
    if (e.target == canvas) {
      e.preventDefault();
    }
  }, false);
  document.body.addEventListener("touchmove", function(e) {
    if (e.target == canvas) {
      e.preventDefault();
    }
  }, false);

  (function drawLoop() {
    requestAnimFrame(drawLoop);
    renderCanvas();
  })();

function isCanvasBlank(canvas) {
  const context = canvas.getContext('2d');

  const pixelBuffer = new Uint32Array(
    context.getImageData(0, 0, canvas.width, canvas.height).data.buffer
  );

  return !pixelBuffer.some(color => color !== 0);
}
  function clearCanvas() {
    canvas.width = canvas.width;
  }

  // Set up the UI
  var sigText = document.getElementById("sig-dataUrl");
  var sigImage = document.getElementById("sig-image");
  var clearBtn = document.getElementById("sig-clearBtn");
  var submitBtn = document.getElementById("downloadLnk");
  clearBtn.addEventListener("click", function(e) {
    clearCanvas();
    sigText.innerHTML = "Data URL for your signature will go here!";
    sigImage.setAttribute("src", "");
  }, false);
  submitBtn.addEventListener("click", function(e) {
      const blank = isCanvasBlank(document.getElementById('sig-canvas'));
  if(blank)
  {
    $('input[name=signature]').val('');
    alert('Signature required');
  }
  else{
    var dataUrl = canvas.toDataURL();
    sigText.innerHTML = dataUrl;
    $('input[name=signature]').val(sigText.innerHTML);
  }
  }, false);
//   function download() {
//     var dt =canvas.toDataURL();
//     this.href = dt;
// };
}
)();
          }
          else{
            $('#signatures').empty();
            $('#toggle').empty();
            $('#boxDisplay').css('display','none');
          }

        });
    
    });
     function showReciverBox(){
        $("#boxDisplay").css('display','block')
     }
  
</script>