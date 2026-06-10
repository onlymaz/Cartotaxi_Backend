 <script
    src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
    crossorigin="anonymous"></script>

    <script src="https://www.gstatic.com/firebasejs/6.3.4/firebase.js"></script>
    <script>
        $(document).ready(function(){
            const config = {
                apiKey: "{{ config('firebase.api_key') }}",
                authDomain: "ultt-ce8f2.firebaseapp.com",
                databaseURL: "https://ultt-ce8f2.firebaseio.com",
                projectId: "ultt-ce8f2",
                storageBucket: "ultt-ce8f2.appspot.com",
                messagingSenderId: "1027654555881",
                appId: "1:1027654555881:web:646826f82459642ab5f878",
                measurementId: "G-4W6ZHFLBDK"
            };
            firebase.initializeApp(config);
            const messaging = firebase.messaging();
            messaging
                .requestPermission()
                .then(function () {
                    return messaging.getToken()
                })
                .then(function(token) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    console.log(token);
                    $.ajax({
                        url: '{{ url("save-device-token") }}',
                        type: 'POST',
                        data: {
                            fcm_token: token,
                            user_id:{{auth()->user()->id}}
                        },
                        dataType: 'JSON',
                        success: function (response) {
                            console.log(response)
                        },
                        error: function (err) {
                            // console.log(err)
                            console.log(" Can't do because: " + err);
                        },
                    });
                })
                .catch(function (err) {
                    console.log("Unable to get permission to notify.", err);
                });
            messaging.onMessage(function(payload) {
                const noteTitle = payload.notification.title;
                const noteOptions = {
                    body: payload.notification.body,
                    icon: payload.notification.icon,
                };
                new Notification(noteTitle, noteOptions);
            });
        });
    </script>
