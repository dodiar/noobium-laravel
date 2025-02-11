<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Me\Article\StoreRequest;
use App\Models\Article;
use Str;
use ImageKit\ImageKit;
use App\Models\User;

class ArticleController extends Controller
{
    public function index()
    {
        // Get user id yang saat ini sedang login
        // Get article dimana user id nya yang saat ini sedang login
        // Get juga category dan uzernya siapa
        // Buat pagination nya
        $userId = auth()->id();

        $articles = Article::with(['category', 'user:id,name,email,picture'])->select([
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
            ->where('user_id', $userId)
            ->paginate();

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Articles fetched successfully.',
            ],
            'data' => $articles
        ]);
    }

    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        $validated['slug'] = Str::of($validated['title'])->slug('-') . ('-') . time();
        $validated['content_preview'] = substr($validated['content'], 0, 218) . '...';

        $imageKit = new ImageKit(
            env('IMAGEKIT_PUBLIC_KEY'),
            env('IMAGEKIT_PRIVATE_KEY'),
            env('IMAGEKIT_URL_ENDPOINT'),
        );

        $image = base64_encode(file_get_contents($request->file('featured_image')));

        $uploadImage = $imageKit->uploadFile([
            'file' => $image,
            'fileName' => $validated['slug'],
            'folder' => '/article',
        ]);

        $validated['fetured_image'] = $uploadImage->result->url;

        $userId = auth()->id();

        $createArticle = User::find($userId)->articles()->create($validated);

        if ($createArticle)
        {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'create article successfully.',
                ],
                'data' => [],
            ]);
        }

        return response()->json([
            'meta' => [
                'code' => 500,
                'status' => 'error',
                'message' => 'error article failed to create.',
            ],
            'data' => [],
        ], 500);
    }

    public function show($id)
    {
        // Get article berdasarkan id yang diberikan
        // Cek apakah article berhasil get
        // Kalu article tidak berhasil di get
        // Maka kembalikan response no foud
        // Jika berhasil di get, maka get id user saat ini login
        // Cek apakah id user yang saat ini login sama dengan id user yang ada di data article yang kita get
        // Jika tidak sama, maka kembalikan response unauthorized
        // Jika sama, maka kembalikan article dengan success
        
        $article = Article::with('category', 'user:id,name,picture')->find($id);

        if ($article)
        {
            $userId = auth()->id();

            if ($article->user_id === $userId)
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
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Unauthorized.',
                ],
                'data' =>[]
            ], 401);
        }

        return response()->json([
            'meta' => [
                'code' => 404,
                'status' => 'error',
                'message' => 'Article not found.',
            ],
            'data' =>[]
        ], 404);
    }
}
