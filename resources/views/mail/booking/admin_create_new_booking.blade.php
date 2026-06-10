@extends('mail.layout')
@section('content')
<tr>
	<td style="padding:50px 15px 0 15px;">
            <dt style="font-weight: bold; font-size:16px; width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                Hi Admin,
            </dt>
             <dt style="font-weight: normal; font-size:16px; width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                Order Id {!! $order !!}
            </dt>
            <dt style="font-weight: normal;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                Customer Creating a New Order
            </dt>            
            <dt style="font-weight: bold;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
            	<a class="btn btn-success" href="{!! url('/dispatcher') !!}"> Go to WebSite </a>
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