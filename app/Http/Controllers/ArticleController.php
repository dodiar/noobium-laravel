<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        // Get title yang di search 
        // Cek apakah search kosong
        // (Jika ada, maka get article berdasarkan title yg di search dan buat paginasi nya)
        // (Jika tidak ada, maka get article langsung buat paginasi nya)
        // Kembalikan response json nya

        $searchQuery = $request->query('search');

        if ($searchQuery !== null)
        {
            $articles = Article::with(['category', 'user:id,name,email,picture'])
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
            ->where(['title', 'like', '%' . $searchQuery . '%'])
            ->paginate()
            ;
        } else {
            $articles = Article::with(['category', 'user:id,name,email,picture'])
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
        }

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Articles fetched successfully.',
            ],
            'data' => $articles,
        ]);
    }

    public function show($slug)
    {
        // Get article berdasarkan slug
        // Cek apakah query get article berhasil
        // Jika iya, maka kembalikan response success
        // (Jika get article gagal) kembalikan response 404, not found

        $article = Article::with(['category', 'user:id,name,email,picture'])
        ->where('slug', $slug)
        ->first()
        ;

        if ($article)
        {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Article fetched successfully.',
                ],
                'data' => $article,    
            ]);
        }

        return response()->json([
            'meta' => [
                'code' => 404,
                'status' => 'error',
                'message' => 'Article not found.',
            ],
            'data' => [],
        ], 404);
    }
}
