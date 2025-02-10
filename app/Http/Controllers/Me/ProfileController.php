<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Me\Profile\UpdateRequest;
use App\Models\User;
use ImageKit\ImageKit;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'sucess',
                'message' => 'User data fetched successfully.',
            ],
            'data' => [
                'email' => $user->email,
                'name' => $user->name,
                'picture' => $user->picture,
            ]
        ]);
    }

    public function update(UpdateRequest $request)
    {
        $validated = $request->validated();
        $user = User::find(auth()->id());

        // Get semua request
        // Cek apakah ada request picture
        // Jika iya proses, cara proses buat objek instance imagekit
        // Ubah dulu gambar ke base64
        // Upload, masukan file, file name dan folder
        // Dapatkan URL nya
        //Masukan URL nya ke tabel
        // Jika tidak ada request picture, maka lanjut prose update
        if ($request->hasFile('picture'))
        {
            $imageKit = new ImageKit(
                env('IMAGEKIT_PUBLIC_KEY'),
                env('IMAGEKIT_PRIVATE_KEY'),
                env('IMAGEKIT_URL_ENDPOINT'),
            );

            $image = base64_encode(file_get_contents($request->file('picture')));

            $uploadImage = $imageKit->uploadFile([
                'file' => $image,
                'fileName' => $user->email,
                'folder' => '/user/profile',
            ]);

            $validated['picture'] = $uploadImage->result->url;
        }

        // Masukan semua request yang sudah di validasi

        $update = $user->update($validated);

        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'User data updated successfully.',
            ],
            'data' => [
                'email' => $user->email,
                'name' => $user->name,
                'picture' => $user->picture,
            ]
        ]);
    }
}
