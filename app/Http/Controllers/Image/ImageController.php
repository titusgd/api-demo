<?php

namespace App\Http\Controllers\Image;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Image;
use App\Services\Service;
use App\Services\Files\ImageUploadService;
use Illuminate\Support\Facades\Auth;

class ImageController extends Controller
{

    // // 新增圖片 範例
    // public function store(Request $request){
    //     $service = new ImageUploadService();
    //     $image_data = $request->image;
    //     $service->addImage($image_data,"error");
    //     return $service->getData();

    // }
        // 修改 範例
    // public function update(Request $request){
    //     $service = new ImageUploadService();
    //     $image_data = $request->image;
    //     $image = $service->updateImage($image_data,"error",$request->image_id);
    //     // return $image;

    // }
    public function getImage(Request $request){
        
        $service = new Service();
        $image  = Image::find($request->image_id);
        
        $fullPath = base_path() . "/storage/files/".$image->path;
        $fullPath = str_replace($image['extension'],'webp',$fullPath);
        return response()->stream(function () use ($fullPath) {
            echo file_get_contents($fullPath);
        }, 200, ['Content-Type' => 'image/webp']);
    }
    public function getImageSource(Request $request){
        
        // return $request->image_id;
        $service = new Service();
        
        $image  = Image::find($request->image_id);
        
        $fullPath = base_path() . "/storage/files/".$image->path;
        return response()->stream(function () use ($fullPath) {
            echo file_get_contents($fullPath);
        }, 200, ['Content-Type' => 'image/jpeg']);
    }
    
}
