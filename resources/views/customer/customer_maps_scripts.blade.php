<script>
    $(document).ready(function (){
        $('body').on('click','.view_locations',function(){
            var elem = $(this);
            $('.view_locations').parents('.viewLocations').removeClass('active');
            var start_address       =       elem.parents('.viewLocations').find('span.start_address').html();
            var end_address         =       elem.parents('.viewLocations').find('span.end_address').html();
            elem.parents('.viewLocations').addClass('active');
            DrawLineOnMap(start_address,end_address);
        });

        showLoader();
        setTimeout(function(){
            var viewLocations   =   $('.viewLocations.active');
            var start_address       =       viewLocations.find('span.start_address').html();
            var end_address         =       viewLocations.find('span.end_address').html();
            DrawLineOnMap(start_address,end_address);
            hideLoader();
        },2000);
    });
    function DrawLineOnMap(start_address,end_address){
        var geocoder = new google.maps.Geocoder();
        var start_latlng        =   '';
        var end_latlng          =   '';
        geocoder.geocode( { 'address': start_address}, function(results, status) {
            if (status == 'OK') {
                start_latlng    =   results[0].geometry.location;
            } else {

            }
        });
        geocoder.geocode( { 'address': end_address}, function(results, status) {
            if (status == 'OK') {
                end_latlng    =   results[0].geometry.location;
                if(start_latlng){
                    SearchMap(start_latlng,end_latlng,'search_map');
                }
                else
                {
                    geocoder.geocode( { 'address': start_address}, function(results, status) {
                        if (status == 'OK') {
                            start_latlng    =   results[0].geometry.location;
                            SearchMap(start_latlng,end_latlng,'search_map');
                        }
                    });

                }
            }
        });
    }

    function SearchMap(start,end,search_map){
        var midPoint    =   window.CenterMapLatLng(start.lat(),start.lng(),end.lat(),end.lng());
        const map = new google.maps.Map(document.getElementById(search_map), {
            zoom: 12,
            center: {
                lat: midPoint.lat,
                lng: midPoint.lng
            },
            mapTypeId: "roadmap"
        });
            var start_lat = $("input[name='start_lat[]']");
            var start_long = $("input[name='start_long[]']");
            var end_lat = $("input[name='end_lat[]']");
            var end_long = $("input[name='end_long[]']");
            var n = $("input[name^='start_lat']").length;
            
            var lines=[];

            for(i=0;i<n;i++)
            {
                lines[i] = [
                    {
                        lat: parseFloat(start_lat[i].value),
                        lng: parseFloat(start_long[i].value)
                    },
                    {
                        lat: parseFloat(end_lat[i].value),
                        lng: parseFloat(end_long[i].value)
                    }
                ];
            }
        var icon = '{!! asset('images/start.png') !!}';
        addStartPoint(icon,start,map);
        icon = '{!! asset('images/end.png') !!}';
        addStartPoint(icon,end,map);
        const flightPath = new google.maps.Polyline({
            path: lines[0],
            geodesic: true,
            strokeColor: "#FF0000",
            strokeOpacity: 1,
            strokeWeight: 2
        });
        flightPath.setMap(map);
    }
    function SearchMapRealTimePoint(start,end,search_map){
        var zoom = localStorage.getItem(search_map);
        var midPoint    =   window.CenterMapLatLng(start.lat,start.lng,end.lat,end.lng);
        const map = new google.maps.Map(document.getElementById(search_map), {
            zoom: (zoom !=undefined) ? Number(zoom) :15,
            center: {
                lat: midPoint.lat,
                lng: midPoint.lng
            },
            mapTypeId: "roadmap"
        });
        const trafficLayer = new google.maps.TrafficLayer();
        trafficLayer.setMap(map);
        google.maps.event.addListener(map, 'zoom_changed', function() {
            var zoom = map.getZoom();
            localStorage.setItem(search_map,zoom);

        });
        var currentMapZoom =  map.getZoom();
        const lines = [
            {
                lat: start.lat,
                lng: start.lng
            },
            {
                lat: end.lat,
                lng: end.lng
            }
        ];
        var icon = '{!! asset('images/start.png') !!}';
        addStartPointlatLngRealTime(icon,start,map);
        icon = '{!! asset('images/end.png') !!}';
        addStartPointlatLngRealTime(icon,end,map);
        const flightPath = new google.maps.Polyline({
            path: lines,
            geodesic: true,
            strokeColor: "#000",
            strokeOpacity: 1,
            strokeWeight: 2
        });
        flightPath.setMap(map);
    }


    function addStartPoint(icon,location,map){
        var marker_start = new google.maps.Marker({
            map: map,
            icon: icon,
            draggable: false
        });
        var curpoint = new google.maps.LatLng(
            location.lat(),
            location.lng()
        );
        marker_start.setPosition(curpoint);
    }
    function addStartPointlatLngRealTime(icon,location,map){
        var marker_start = new google.maps.Marker({
            map: map,
            icon: icon,
            draggable: false
        });
        var curpoint = new google.maps.LatLng(
            location.lat,
            location.lng
        );
        marker_start.setPosition(curpoint);
    }

</script>
