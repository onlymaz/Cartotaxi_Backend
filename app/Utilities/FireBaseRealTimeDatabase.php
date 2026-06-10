<?php


namespace App\Utilities;

use Illuminate\Support\Facades\Log;

class FireBaseRealTimeDatabase
{
    public static function RemoveData($reference){
        try {
            $database = app('firebase.database');
            $database->getReference($reference)->remove();
        } catch (\Throwable $e) {
            Log::warning('Firebase realtime remove skipped: ' . $e->getMessage());
        }
    }

    public static function StoreData($reference,$object){
        try {
            $database = app('firebase.database');
            $database->getReference($reference)->update($object);
        } catch (\Throwable $e) {
            Log::warning('Firebase realtime store skipped: ' . $e->getMessage());
        }
    }

    public static function FetchData($reference){
        try {
            $database = app('firebase.database');
            $value = $database->getReference($reference);
            return $value->getValue();
        } catch (\Throwable $e) {
            Log::warning('Firebase realtime fetch skipped: ' . $e->getMessage());
            return null;
        }
    }
}
