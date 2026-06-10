@extends('mail.layout')
@section('content')
<tr>
	<td style="padding:50px 15px 0 15px;">
            <dt style="font-weight: bold; font-size:16px; width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                Hi {!! $user->first_name !!},
            </dt>
            <dt style="font-weight: normal;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                Order ID : {!! $order !!}
            </dt>
            <dt style="font-weight: normal;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
            	Booking Order has been Assign to Rider {!! $userCR->first_name !!}
            </dt>

            <dt style="font-weight: bold;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
            	Go to WebSite
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