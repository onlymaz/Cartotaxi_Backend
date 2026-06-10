<?php


namespace App\Datatables;



use Yajra\DataTables\Html\Column;

class DataColumn
{
    public static function add($data,$name,$title,$IsSearchable=true,$Isorder=true,$IsExportable=false,$isPrintable=false){
        $column =   Column::make($data)
            ->title($title)
            ->name($name)
            ->searchable($IsSearchable)
            ->orderable($Isorder)
            ->footer(false)
            ->exportable($IsExportable)
            ->printable($isPrintable);
        return $column;
    }

    public static function add_new($row,$IsSearchable=true,$Isorder=true,$IsExportable=false,$isPrintable=false){
        $column =   Column::make($row['data'])
            ->title($row['label'])
            ->name($row['name'])
            ->searchable($IsSearchable)
            ->orderable($Isorder)
            /*->footer($title)*/
            ->exportable($IsExportable)
            ->printable($isPrintable);
        return $column;
    }
}
