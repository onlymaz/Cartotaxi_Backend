<?php


namespace App\Datatables;

use Yajra\DataTables\Facades\DataTables;

class Datatable
{
    public static function create($objects,$custom_columns){

        $datatable  =   DataTables::of($objects);
        $column_raw =   [];
        
        foreach ($custom_columns as $key=> $column){
            $datatable->addColumn($key,function ($row)use($column){
                return view($column,compact('row'));
            });
            $column_raw[]   =   $key;
        }
        if (!empty($column_raw)){
            $datatable->rawColumns($column_raw);
        }
        
        // Add custom filtering for customer and rider names
        $datatable->filter(function ($query) {
            $request = request();
            
            // Global search - search across customer name, rider name, booking ID, status, payment method
            if ($request->has('search') && $request->search['value']) {
                $searchValue = $request->search['value'];
                $query->where(function($q) use ($searchValue) {
                    $q->where('u.first_name', 'like', '%' . $searchValue . '%')
                      ->orWhere('u.last_name', 'like', '%' . $searchValue . '%')
                      ->orWhereRaw("CONCAT(u.first_name, ' ', u.last_name) LIKE ?", ['%' . $searchValue . '%'])
                      ->orWhere('r.first_name', 'like', '%' . $searchValue . '%')
                      ->orWhere('r.last_name', 'like', '%' . $searchValue . '%')
                      ->orWhereRaw("CONCAT(r.first_name, ' ', r.last_name) LIKE ?", ['%' . $searchValue . '%'])
                      ->orWhere('orders.booking_id', 'like', '%' . $searchValue . '%')
                      ->orWhere('orders.order_status', 'like', '%' . $searchValue . '%')
                      ->orWhere('g.name', 'like', '%' . $searchValue . '%');
                });
            }
        });
        
        $datatable  =   $datatable->make(true);
        return $datatable;
    }
}
