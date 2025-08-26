<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = $this->categoryService->getParentCategories();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(CategoryRequest $request)
    {
        try {
            $this->categoryService->createCategory($request->validated());
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    public function edit(Category $category)
    {
        $parentCategories = $this->categoryService->getParentCategories();
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        try {
            $this->categoryService->updateCategory($category, $request->validated());
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    public function destroy(Category $category)
    {
        try {
            $this->categoryService->deleteCategory($category);
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }
}