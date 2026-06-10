<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>User Profile </title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">

@media print {
    @page {
        size: landscape;
        margin: 5px;
    }

    .btn{
        display: none !important;
    }
}

@page {
  margin: 1cm;
}

* {
    font-family: Helvetica, Arial, sans-serif;
    font-size: small;
    margin: 0;
    padding: 0;
}

</style>
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center mb-3">User Profile </h2>
        <div class="d-flex justify-content-end mb-4">
            <a class="btn btn-primary" @if(isset($id)) href="{{ url('/downloads/pdf/'.$id) }}" @endif>Export to PDF</a>
        </div>
        <table class="table table-bordered mb-5">
            <thead>
                <tr class="table-danger">
                    <th scope="col">#</th>
                    <th scope="col">Order ID</th>
                    <th scope="col">Package</th>
                    <th scope="col">Start Location</th>
                    <th scope="col">End Location</th>
                    <th scope="col">Picked Time</th>
                    <th scope="col">Description</th>
                    <th scope="col">Fixed Price</th>
                    <th scope="col">Total Amount</th>
                    <th scope="col">Order Status</th>
                    <th scope="col">Reciver Name</th>
                    <th scope="col">Reciver Address</th>
                </tr>
            </thead>
            <tbody>
                 
            @foreach($order as $index => $single)
            <tr>
                <td>{{ ++$index }}</td>
                <td> {{ $single->id }} </td>
                <td> {{ $single->package->name }} </td>
                <td> {{ $single->start_location }} </td>
                <td> {{ $single->end_location }} </td>
                <td> {{ $single->picked_time }} </td>
                <td> {{ $single->description }} </td>
                <td> {{ $single->fixed_price }} </td>
                <td> {{ $single->total_amount }} </td>
                <td> {{ $single->order_status }} </td>
                <td> {{ $single->reciver_name }} </td>
                <td> {{ $single->reciver_address }} </td>
            </tr>
            @endforeach

            </tbody>
        </table>

    </div>

    <script src="{{ asset('js/app.js') }}" type="text/js"></script>
</body>

</html>