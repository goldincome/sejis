<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;

class ImageService
{


    //upload single image to 
    public function addPrimaryImage(Model $model, UploadedFile $image,  string $folderName)
    {
        try {
           return  $model->addMedia($image)->toMediaCollection($folderName);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //upload multiple images 
    public function addImages(Model $model, array $images, string $folderName): void
    {
        collect($images)->each(function (UploadedFile $image) use ($model, $folderName ) {
            $model->addMedia($image)->toMediaCollection($folderName);
        });
    }
}