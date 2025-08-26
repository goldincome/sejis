<?php
namespace App\Services;

use Exception;
use App\Models\{Product};
use Illuminate\Support\Str;
use App\Enums\ProductTypeEnum;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProductService
{
    //protected $folderName = 'products';
    public function createProduct(array $productData): Product
    {  //dd($productData);
        DB::beginTransaction();
        try {
            $product = Product::create($productData);
            // Primary image
            if (isset($productData['main_image'])) {
                app(ImageService::class)->addPrimaryImage($product, $productData['main_image'], $product::FOLDER_NAMES['main']);
                //$this->addPrimaryImage($product, $productData['main_image'], $storageInfo);
            }
            //Other images
            if (isset($productData['additional_images'])) {
                //$this->addImages($product, $productData['additional_images'], $storageInfo);
                app(ImageService::class)->addImages($product, $productData['additional_images'], $product::FOLDER_NAMES['gallery']);
            }
            DB::commit();
            return $product;
        }catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to create rental item: ' . $e->getMessage());
        }
    }

        // Update product of ProductService
    public function updateProduct(Product $product, array $productData): Product
    {
        DB::beginTransaction();
        try {
            $product->update($productData);

            // Handle main image update
            if (isset($productData['main_image'])) {
                //delete existing main image
                $product->clearMediaCollection($product::FOLDER_NAMES['main']);
                //Add new main image
                $image = app(ImageService::class)->addPrimaryImage($product, $productData['main_image'], $product::FOLDER_NAMES['main']);
            }
            // Handle remove unselected additional images
            if (isset($productData['existing_additional_images'])) {
                $product->getMedia($product::FOLDER_NAMES['gallery'])->whereNotIn('uuid', $productData['existing_additional_images'])
                    ->each(function($image){
                    $image->delete();
                });   
                  
            }
            
            // Handle upload additional images
            if (isset($productData['additional_images'])) {
                app(ImageService::class)->addImages($product, $productData['additional_images'], $product::FOLDER_NAMES['gallery']);
            }
            DB::commit();
            return $product->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to update rental item: ' . $e->getMessage());
        }
    }

    public function deleteProduct(Product $product)
    {
        DB::beginTransaction();
        try{
            $product->clearMediaCollection($product::FOLDER_NAMES['main']);
            $product->clearMediaCollection($product::FOLDER_NAMES['gallery']);
            $product->forceDelete();
            DB::commit();
            return true;
        }catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to delete rental item: ' . $e->getMessage());
            return false;
        } 
        
    }

    protected function productData(array $productData)
    {
        return [
                'name' => $productData['name'],
                'type' => $productData['type'],
                'category_id' => $productData['category_id'],
                'quantity' => $productData['quantity'],
                'description' => $productData['description'],
                'intro' => $productData['intro'],
                'price' => $productData['price'],
                'price_per_day' => $productData['price_per_day'] ?? null,
                'is_active' => $productData['is_active'],
        ];
    }
}
