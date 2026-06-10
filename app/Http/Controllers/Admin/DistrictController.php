<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\DataColumn;
use App\Datatables\Datatable;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityDistrict;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Button;

class DistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,Builder $builder)
    {
        if ($request->ajax()){
            $custom_columns =   [
                'IsActive'    =>  'admin.districts.buttons.buttons'
            ];
            $users  =   CityDistrict::with('city.country')
                                    ->select('id','district_name','IsActive','city_id','polygons');
            return Datatable::create($users,$custom_columns);
        }
        $columns         =   [
            [
                'data' =>  'district_name',
                'name'  =>  'cd.district_name',
                'label' =>  'Name'
            ],
            [
                'data' =>  'city_name',
                'name'  =>  'c.city_name',
                'label' =>  'Detail'
            ],
            [
                'data' =>  'IsActive',
                'name'  =>  'IsActive',
                'label' =>  trans('messages.status')
            ],

        ];
        $columns_data   =   [];
        foreach ($columns as $column => $title){
            $columns_data[] = DataColumn::add_new($title,true);
        }
        $builder->pageLength(10);
        $html   =   $builder->columns($columns_data)
            ->dom('Bfrtip')
            ->buttons(
                Button::make('copy'),
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf')
            );

        $districts = CityDistrict::with('city')->orderByDesc('id')->paginate(20);
        return view('admin.districts.index', compact('html', 'districts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if ($request->ajax()){
            $district       =   CityDistrict::where('id',$request['id'])->first();
            return view('admin.districts.create',compact('district'));
        }
        return redirect()->route('districts.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $district_id    =   $request['district_id'];
        $polygons       =   $request['polygons'];
        $cityDistrict   =   CityDistrict::where('id',$district_id)->first();
        $cityDistrict->update([
            'polygons'  => $polygons
        ]);
        return 1;
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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

    public function change_status(Request $request)
    {
        $district   =   CityDistrict::find($request->id);

        $district->update([
            'IsActive'  =>  $request->value
        ]);

        return response()->json([
            'status'    =>  true
        ]);
    }
}
