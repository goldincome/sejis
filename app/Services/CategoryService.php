<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public function getAllCategories(): Collection
    {
        return Category::with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    public function getParentCategories(): Collection
    {
        return Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $category = Category::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
            ]);

            if (isset($data['image'])) {
                $category->addMedia($data['image'])
                    ->toMediaCollection('category');
            }

            return $category;
        });
    }

    public function updateCategory(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $category->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
            ]);

            if (isset($data['image'])) {
                $category->clearMediaCollection('category');
                $category->addMedia($data['image'])
                    ->toMediaCollection('category');
            }

            return $category;
        });
    }

    public function deleteCategory(Category $category): void
    {
        DB::transaction(function () use ($category) {
            if ($category->rentalItems()->exists()) {
                throw new \Exception('Cannot delete category with associated rental items');
            }

            if ($category->children()->exists()) {
                throw new \Exception('Cannot delete category with subcategories');
            }

            $category->delete();
        });
    }
}