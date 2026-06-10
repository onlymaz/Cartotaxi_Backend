@extends('mail.layout')
@section('content')
<tr>
    <td style="padding:50px 15px 0 15px;">
            <dt style="font-weight: bold; font-size:16px; width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                Hi {!! $user->first_name !!} {!! $user->last_name !!},
            </dt>
            <dt style="font-weight: normal; font-size:16px; width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                The total helper price :  {!! $helper_info !!}
            </dt>
            <dt style="font-weight: normal; font-size:16px; width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                 The total number of helpers booked : {!! $helpers !!}
            </dt>
            <dt style="font-weight: normal; font-size:16px; width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                The total number of booked hours : {!! $h_hours !!}
            </dt>
            <dt style="font-weight: normal; font-size:16px; width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                The booked Helper Started From : {!! $h_start_end_time !!}
            </dt>
            <dt style="font-weight: normal;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                "Your helper booking request has been placed"
            </dt>
             <dt style="font-weight: bold;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                <a class="btn btn-success" href="{!! url('/') !!}"> Go to WebSite </a>
            </dt>
            <dt style="font-weight: normal;width: 80%;text-align: left;padding:0 15px; color: #000000">
                Thanks,
            </dt>
            <dt style="font-weight: normal;width: 80%;text-align: left;padding:0 15px 20px; color: #000000">
                Team Cargo Texi
            </dt>
        </td>
    </tr>
@endsection