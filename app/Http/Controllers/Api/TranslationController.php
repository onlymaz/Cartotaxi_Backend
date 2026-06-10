<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class TranslationController extends Controller
{
	public function languagesType(){

		$language=[
			[
				'id'=>'en',
				'image'=>url('uploads/flag/englishFlag.png'),
				'name'=>'English'
			],
			[
				'id'=>'de',
				'image'=>url('uploads/flag/germanFlag.jpg'),
				'name'=>'German'
			]
		];
		$data= [
			'language' => $language
		];

		 return response()->json([
		            'status'    =>  true,
		            'messages'    =>  'Language List',
		            'data'    =>  $data,
		        ]);
	}    
	public function Language(Request $request){
		$data=[];
		$header = $request->header('X-localization');
		if($header=='de'){
			\App::setLocale($header);
			$data['translations'][$header] = __('mobile');
		}elseif($header=='en'){
			\App::setLocale($header);
			$data['translations'][$header] = __('mobile');
		}
        return response()->json([
            'status'        =>  true,
            'messages'      =>  "Translation",
            'data'          =>  $data
        ], 200);
	}
}
