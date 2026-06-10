@extends('mail.layout')
@section('content')
    <tr><td style="padding:50px 15px 0 15px;">
            <dt style="font-weight: bold; font-size:16px; width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                Hi {!! $user->first_name !!},
            </dt>
            <dt style="font-weight: normal;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                We received a request to reset your Cargo Texi password. Enter the following code to reset your password.
            </dt>
            <dt style="font-weight: bold;width: 80%;text-align: left;padding: 5px 15px; color: #000000">

            </dt>
            <dt style="font-weight: bold;width: 80%;text-align: left;padding: 5px 15px 30px 15px; color: #000000">
                <b>{{ $code }}</b>
            </dt>
            {{--<dt style="font-weight: normal;width: 80%;text-align: left;padding: 5px 15px 30px 15px; color: #000000">
                {{__('mail.not_authorized')}}
            </dt>--}}
            <dt style="font-weight: normal;width: 80%;text-align: left;padding:0 15px; color: #000000">
                Thanks,
            </dt>

            <dt style="font-weight: normal;width: 80%;text-align: left;padding:0 15px 20px; color: #000000">
                Team Cargo Texi
            </dt>
        </td>
    </tr>
@endsection
