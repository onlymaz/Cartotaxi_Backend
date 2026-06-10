@extends('mail.layout')

@section('content')
    <tr><td style="padding:50px 15px 0 15px;">
            <dt style="font-weight: bold; font-size:16px; width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                Hi Admin,
            </dt>
            <dt style="font-weight: normal;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                Negative Feed back for "{!! $review->first_name." <b>".$review->last_name." </b>" !!}"
            </dt >
            <dt style="font-weight: normal;width: 80%;text-align: left;padding: 5px 15px; color: #000000">Phone Number  </dt>
            <dt style="font-weight: bold;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                {!! $review->phone_number !!}
            </dt>
            <dt style="font-weight: normal;width: 80%;text-align: left;padding: 5px 15px; color: #000000">Email address  </dt>
            <dt style="font-weight: bold;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                {!! $review->email !!}
            </dt>
            <dt style="font-weight: normal;width: 80%;text-align: left;padding: 5px 15px; color: #000000">Rating  </dt>
            <dt style="font-weight: bold;width: 80%;text-align: left;padding: 5px 15px; color: #000000">
                {!! $review->rating !!}
            </dt>


            <dt style="font-weight: normal;width: 80%;text-align: left;padding:0 15px; color: #000000">
                Thanks,
            </dt>
            <dt style="font-weight: normal;width: 80%;text-align: left;padding:0 15px 20px; color: #000000">
                Team Cargo Taxi
            </dt>
            {{--<dt style="font-size:10px;font-weight: normal;width: 80%;text-align: left;padding:10px 15px 20px; color: #ff0000">
                <b>{{__('mail.this_code_will_expire')}}</b>
            </dt>--}}
        </td>
    </tr>
@endsection
