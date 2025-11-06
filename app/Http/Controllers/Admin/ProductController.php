<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Services\PlanService;
use App\Enums\ProductTypeEnum;
use App\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;



class ProductController extends Controller
{
    protected $productType = ProductTypeEnum::class;
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = Category::all();
            $products = Product::with(['media', 'category'])
                ->latest()->paginate(10);
            return view('admin.products.index', compact('products', 'categories'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {      //dd($request->all());
         $validated = $request->validated();
        try{
            $productData = [];

            if ($request->hasFile('main_product_image')) {
                $productData['main_image'] =  $request->file('main_product_image') ?? null;
            }
            // Store additional images
            if ($request->hasFile('additional_images')) {
                $productData['additional_images'] =  $request->file('additional_images') ?? null;
            }

            $cat = Category::find($request->category_id);
            if($cat){
                $productData['type'] = $cat->slug;
            }

            $product = $this->productService->createProduct(
                array_merge($validated, $productData)
            );
        } 
        catch (Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating product: ' . $e->getMessage());
        }

        return redirect()->route('admin.products.index')
                            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load(['category','media']);
        $categories = Category::all();
        return view('admin.products.edit', ['product'=>$product, 
        'categories' => $categories]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    { //dd($request->all(), $request->validated());
        try {
            $productData = $request->validated();

            if ($request->hasFile('main_product_image')) {
                $productData['main_image'] = $request->file('main_product_image');
            }

            if ($request->hasFile('additional_images')) {
                $productData['additional_images'] = $request->file('additional_images');
            }

            if ($request->input('existing_additional_images')) {
                $productData['existing_additional_images'] = $request->input('existing_additional_images');
            }
            $cat = Category::find($request->category_id);
            if($cat){
                $productData['type'] = $cat->slug;
            }

            // Update product, 
            $this->productService->updateProduct($product, $productData);
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', 'Error updating product: ' . $e->getMessage());
        }
        return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, ProductService $productService)
    {
        try{
            $productService->deleteProduct($product);

        }catch (Exception $e) {
            return back()
                ->with('error', 'Error creating Rental Item: ' . $e->getMessage());
        }
        return redirect()->route('admin.products.index')
                            ->with('success', 'Rental Item deleted successfully.');
    }
}
