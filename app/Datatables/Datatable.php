<?php


namespace App\Datatables;

use Yajra\DataTables\Facades\DataTables;

class Datatable
{
    /**
     * @param mixed         $objects        Query builder for the table.
     * @param array         $custom_columns Column name => view partial map.
     * @param \Closure|null $search_filter  Optional global-search override. When
     *                                      omitted, Yajra's default per-column
     *                                      search applies. Callers whose query
     *                                      uses table aliases (e.g. the orders
     *                                      screen) pass their own closure —
     *                                      previously a single hardcoded filter
     *                                      referencing u/r/orders/g aliases ran
     *                                      for every table and broke search on
     *                                      screens without those joins.
     */
    public static function create($objects, $custom_columns, ?\Closure $search_filter = null){

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

        if ($search_filter) {
            $datatable->filter($search_filter);
        }

        $datatable  =   $datatable->make(true);
        return $datatable;
    }
}
