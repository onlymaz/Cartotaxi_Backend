<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\DataColumn;
use App\Datatables\Datatable;
use App\Http\Controllers\Controller;
use App\Http\Requests\PackageRequest;
use App\Models\Package;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Button;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Builder $builder)
    {
        if (request()->ajax()){
            $custom_columns =   [
                'Action'    =>  'admin.packages.actions',

            ];
            $packages  =   Package::from(get_table_name(Package::class).' as p')
            ->select(
                'p.name',
                'p.weight',
                'p.unit',
                'p.per_km_charges',
                'p.id',
                'p.fixed_price'
            )
            ->orderBy('p.weight','asc');
            return Datatable::create($packages,$custom_columns);
        }
        $columns         =   [

            [
                'data' =>  'name',
                'name'  =>  'p.name',
                'label' =>  'Name'
            ],
            [
                'data' =>  'weight',
                'name'  =>  'p.weight',
                'label' =>  trans('messages.weight')
            ],
            [
                'data' =>  'unit',
                'name'  =>  'p.unit',
                'label' =>  trans('messages.unit')
            ],
            [
                'data' =>  'fixed_price',
                'name'  =>  'p.fixed_price',
                'label' =>  trans('messages.fixed_price')
            ],
            [
                'data' =>  'per_km_charges',
                'name'  =>  'p.per_km_charges',
                'label' =>  trans('messages.per_km_charges')
            ],
            [
                'data' =>  'Action',
                'name'  =>  'Action',
                'label' =>  trans('messages.action')
            ],
        ];
        $columns_data   =   [];
        foreach ($columns as $column => $title){
            $columns_data[] = DataColumn::add_new($title,true);
        }
        $builder->pageLength(50);
        $html   =   $builder->columns($columns_data)
            ->dom('Bfrtip')
            ->buttons(
                Button::make('copy'),
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf')
            );

        $packages = Package::orderBy('weight', 'asc')->paginate(20);
        return view('admin.packages.index', compact('html', 'packages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.packages.create_modal');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PackageRequest $request)
    {
        Package::create([
            'name'  =>  $request->name,
            'weight'  =>  $request->weight,
            'unit'  =>  $request->unit,
            'per_km_charges'  =>  $request->per_km_charges,
            'fixed_price'  =>  $request->fixed_price,
        ]);

        return response()->json([
            'success'   =>  true
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $package    =   Package::find($id);
        return view('admin.packages.edit_modal',compact('package'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PackageRequest $request, $id)
    {
        $package    =   Package::find($id);

        $package->update([
            'name'  =>  $request->name,
            'weight'  =>  $request->weight,
            'unit'  =>  $request->unit,
            'per_km_charges'  =>  $request->per_km_charges,
            'fixed_price'  =>  $request->fixed_price,
        ]);

        return response()->json([
            'success'   =>  true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
