<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Me\Article\StoreRequest;
use App\Http\Requests\Me\Article\UpdateRequest;
use Str;
use ImageKit\ImageKit;
use App\Models\User;
use App\Models\Article;

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

    public function update(UpdateRequest $request, $id)
    {
        // category_id
        // title
        // Content
        // featured_image

        // Get article berdasarkan id
        // Cek apakah get article berhasil
        // Jika berhasil get article
        // Get user yang sedang login
        // Cek apakah user id article sama dengan id user yang login
        // jika iya, maka get semua request yang valid generate slug dari title
        // Generate content preview berdasarkan content
        // Lalu cek apakah ada request featured image
        // Jika iya, maka upload file gambar ke ImageKit
        // Get url hasil upload dari ImageKit
        // Lakukan Update article dengan request valid dengan hasil data yang kita generate di auto controller\
        // Cek apakah update article berhasil
        // jika iya, maka kembalikan response success
        // ( Jika line ini dieksekusi artinya tidak berhasil update article) kembalikan response error 500, gagal update article
        // ( Jika line ini dieksekusi artinya article ini bukan milik user yg login) kembalikan response eror 401, unauthorized
        // ( Jika line ini dieksekusi artinya tidak berhasil get article ) kembalikan response 404, Artcle tidak ditemukan

        $article = Article::find($id);

        if ($article)
        {
            $userId = auth()->id();

            if ($article->user_id === $userId)
            {
                $validated = $request->validated();

                $validated['slug'] = Str::of($validated['title'])->slug('-') . '-' . time();
                $validated['content_preview'] = substr($validated['content'], 0, 218) . '...';

                if ($request->hasFile('featured_image'))
                {
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

                    $validated['featured_image'] = $uploadImage->result->url;
                }

                $updateArticle = $article->update($validated);

                if ($updateArticle)
                {
                    return response()->json([
                        'meta' => [
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'Article updated successfully.',
                        ],
                        'data' =>[],
                    ]);

                    return response()->json([
                        'meta' => [
                            'code' => 500,
                            'status' => 'error',
                            'message' => 'Error article failed to update.',
                        ],
                        'data' => [],
                    ], 500);
                }

            return response()->json([
                'meta' => [
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ],
                'data' => [],
            ],401);
        }

        return response()->json([
            'meta' => [
                'code' => 404,
                'status'=> 'error',
                'messgare' => 'Article not found.',
                ],
                'data'=>[],
            ], 404);
        }
    }

    public function destroy($id)
    {
        // Get article by id
        // Cek apakah get article ada
        // Jika iya, maka get user id yg sedang login
        // Cek apakah user id article sama dengan user id dari user yg sedang login
        // Jika iya, maka delete article
        // Cek apakah article berhasil di delete
        // Jika iya, mka kembalikan response success
        // (Jika kode ini di eksekusi, maka artinya article gagal di delete) Kembalikan response error 500
        // (Jika kode ini di eksekusi, maka artinya article gagal di delete) Kembalikan response error 401, unauthorized
        // (Jika kode ini di eksekusi, maka artinya article tidak ditemukan) kembalikan response error 404, article not found

        $article = Article::find($id);

        if ($article)
        {
            $userId = auth()->id();

            if ($article->user_id === $userId)
            {
                $deleteArticle = $article->delete();

                if ($deleteArticle)
                {
                    return response()->json([
                        'meta' => [
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'Article deleted successfully.',
                        ],
                        'data' => [],
                    ]);
                }

                return response()->json([
                    'meta' => [
                        'code' => 500,
                        'status' => 'error',
                        'message' => 'Error! article failed to detele',
                    ],
                    'data' => [],
                ], 500);
            }

            return response()->json([
                'meta' => [
                    'code' => 401,
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ],
                'data' => [],
            ], 401);
        }

        return response()->json([
            'meta' => [
                'code' => 404,
                'status' => 'error',
                'message' => 'Article not found',
            ],
            'data' => [],
        ], 404);
    }
}