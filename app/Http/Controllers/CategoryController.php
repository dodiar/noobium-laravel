<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Get semua category
        // return response json semua category

        $categories = Category::all();

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Category fetched successfuly.',
            ],
            'data' => $categories,
        ]);
    }

    public function show($categorySlug)
    {
        // Get category dimana slug nya = categorySlug yg di dapat dari route
        // Cek apakah categry tersebut ada
        // Jika ada, maka cari category berdasarkan id dari categorySlug tadi lalu cari artikel nya
        // Return artikel tersebut 
        // (Jika kode ini dieksekusi, maka artinya category yg dicari tidak ada) kembalikan response error 404, category not found

        $category = Category::where('slug', $categorySlug)->first();

        if ($category)
        {
            $articles = Category::find($category->id)
            ->articles()
            ->with(['category', 'user:id,name,picture'])
            ->select([
                'id',
                'user_id',
                'category_id',
                'title',
                'slug',
                'content_preview',
                'featured_image',
                'created_at',
                'updated_at'
            ])
            ->paginate()
            ;

            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Articles fetched successfully.',
                ],
                'data' => $articles,
            ]);
        }

        return response()->json([
            'meta' => [
                'code' => 404,
                'status' => 'error',
                'message' => 'Category not found.', 
            ],
            'data' => [],
        ], 404);
    }
}
