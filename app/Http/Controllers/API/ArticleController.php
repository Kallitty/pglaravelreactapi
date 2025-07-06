<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage; //not sure it is needed

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::latest()->get();
        return response()->json($articles);
    }

    public function showBySlug($slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        return response()->json($article);
    }

    public function show($id)
    {
        $article = Article::findOrFail($id);
        return response()->json($article);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'mainImage' => 'required|file|image|max:5000',
            'additionalImages.*' => 'nullable|file|image|max:5000'
        ]);

        // Store main image
        $mainImageUrl = null;
        if ($request->hasFile('mainImage')) {
            $mainImage = $request->file('mainImage');
            $imageName = time() . '-main-' . $mainImage->getClientOriginalName();
            $mainImage->move('uploads/article_images/', $imageName);
            $mainImageUrl = 'uploads/article_images/' . $imageName;
        }

        // Store additional images
        $additionalImageUrls = [];
        if ($request->hasFile('additionalImages')) {
            foreach ($request->file('additionalImages') as $image) {
                $imageName = time() . '-' . $image->getClientOriginalName();
                $image->move('uploads/article_images/', $imageName);
                $additionalImageUrls[] = 'uploads/article_images/' . $imageName;
            }
        }

        $article = Article::create([
            'title' => $request->title,
            'content' => $request->content,
            'author' => $request->user()->name,
            'slug' => Str::slug($request->title),
            'main_image_url' => $mainImageUrl,
            'additional_image_urls' => $additionalImageUrls
        ]);

        return response()->json([
            'message' => 'Article created successfully',
            'article' => $article
        ], 201);
    }

    public function view($id)
    {
        $article = Article::findOrFail($id);
        
        return response()->json([
            'title' => $article->title,
            'content' => $article->content,
            'author' => $article->author,
            'slug' => $article->slug,
            'main_image_url' => $article->main_image_url,
            'additional_image_urls' => $article->additional_image_urls,
            'created_at' => $article->created_at,
            'updated_at' => $article->updated_at
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'author' => 'sometimes|string',
            'mainImage' => 'sometimes|file|image|max:5000',
            'additionalImages' => 'sometimes|array',
            'additionalImages.*' => 'sometimes|file|image|max:5000',
            'existingAdditionalImages' => 'sometimes|array',
            'existingAdditionalImages.*' => 'sometimes|string' // URLs of existing images
        ]);

        $article = Article::findOrFail($id);

        // Update article fields
        $article->fill($request->only(['title', 'content', 'author']));
        
        if ($request->has('title')) {
            $article->slug = Str::slug($request->title);
        }

        // Handle main image update
        if ($request->hasFile('mainImage')) {
            // Delete old main image if exists
            if ($article->main_image_url && File::exists(public_path($article->main_image_url))) {
                File::delete(public_path($article->main_image_url));
            }

            // Store new main image
            $mainImage = $request->file('mainImage');
            $imageName = time() . '-main-' . $mainImage->getClientOriginalName();
            $mainImage->move('uploads/article_images/', $imageName);
            $article->main_image_url = 'uploads/article_images/' . $imageName;
        }

        // Initialize with existing additional images
        $currentAdditionalImages = $article->additional_image_urls ?? [];
        $allAdditionalImages = $currentAdditionalImages;

        // Handle existing additional images from the form
        if ($request->has('existingAdditionalImages')) {
            // Find images to delete (present in current but not in request)
            $imagesToDelete = array_diff($currentAdditionalImages, $request->existingAdditionalImages);
            
            // Delete removed images
            foreach ($imagesToDelete as $imageUrl) {
                if (File::exists(public_path($imageUrl))) {
                    File::delete(public_path($imageUrl));
                }
            }
            
            // Keep only the requested existing images
            $allAdditionalImages = $request->existingAdditionalImages;
        }

        // Add new additional images
        if ($request->hasFile('additionalImages')) {
            foreach ($request->file('additionalImages') as $image) {
                $imageName = time() . '-' . $image->getClientOriginalName();
                $image->move('uploads/article_images/', $imageName);
                $allAdditionalImages[] = 'uploads/article_images/' . $imageName;
            }
        }

        $article->additional_image_urls = $allAdditionalImages;
        $article->save();

        return response()->json([
            'message' => 'Article updated successfully',
            'article' => $article
        ]);
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);

        // Delete main image
        if ($article->main_image_url && File::exists(public_path($article->main_image_url))) {
            File::delete(public_path($article->main_image_url));
        }

        // Delete additional images
        if ($article->additional_image_urls) {
            foreach ($article->additional_image_urls as $imageUrl) {
                if (File::exists(public_path($imageUrl))) {
                    File::delete(public_path($imageUrl));
                }
            }
        }

        $article->delete();

        return response()->noContent();
    }
}

// class ArticleController extends Controller
// {
//     public function index()
//     {
//         $articles = Article::latest()->get();
//         return response()->json($articles);
//     }

//     public function showBySlug($slug)
//     {
//         $article = Article::where('slug', $slug)->firstOrFail();
//         return response()->json($article);
//     }

//     public function show($id)
//     {
//         $article = Article::findOrFail($id);
//         return response()->json($article);
//     }
// //
//             //30062025
//         // Add this helper method at the top of your controller
//         protected function getStorageUrl($path)
//         {
//             if (empty($path)) {
//                 return null;
//             }
            
//             // If it's already a full URL, return it
//             if (strpos($path, 'http') === 0) {
//                 return $path;
//             }
            
//             // For production - use the correct URL structure
//             if (app()->environment('production')) {
//                 return config('app.url').'/storage/'.str_replace('public/', '', $path);
//             }
            
//             // For local development
//             return Storage::url($path);
//         }

// //
//     public function store(Request $request)
//     {
//         $request->validate([
//             'title' => 'required|string|max:255',
//             'content' => 'required|string',
//             'mainImage' => 'required|file|image|max:5000',
//             'additionalImages.*' => 'nullable|file|image|max:5000'
//         ]);

//         // // Store main image
//         // $mainImagePath = $request->file('mainImage')->store('public/articles');
//         // $mainImageUrl = Storage::url($mainImagePath);

//         // Store main image 
//     $mainImagePath = $request->file('mainImage')->store('public/articles');
//     $mainImageUrl = $this->getStorageUrl($mainImagePath);

//         // Store additional images
//         $additionalImageUrls = [];
//         if ($request->hasFile('additionalImages')) {
//             foreach ($request->file('additionalImages') as $image) {
//                 $path = $image->store('public/articles');
//                 $additionalImageUrls[] = Storage::url($path);
//             }
//         }

//         $article = Article::create([
//             'title' => $request->title,
//             'content' => $request->content,
//             'author' => $request->user()->name,
//             'main_image_url' => $mainImageUrl,
//             'additional_image_urls' => $additionalImageUrls
//         ]);

//         return response()->json([
//             'message' => 'Article created successfully',
//             'article' => $article
//         ], 201);
//     }



//  public function view($id)
// {
//     $article = Article::findOrFail($id);
    
//     // Format URLs consistently
//     $formatUrl = function ($url) {
//         if (!$url) return null;
//         return asset(str_replace('/storage', 'storage', $url));
//     };
    
//     return response()->json([
//         'title' => $article->title,
//         'content' => $article->content,
//         'author' => $article->author,
//         'slug' => $article->slug,
//         'main_image_url' => $formatUrl($article->main_image_url),
//         'additional_image_urls' => collect($article->additional_image_urls ?? [])
//             ->map($formatUrl)
//             ->toArray(),
//         'created_at' => $article->created_at,
//         'updated_at' => $article->updated_at
//     ]);
// }





// public function update(Request $request, $id)
// {
//     $request->validate([
//         'title' => 'sometimes|string|max:255',
//         'content' => 'sometimes|string',
//         'author' => 'sometimes|string',
//         'mainImage' => 'sometimes|file|image|max:5000',
//         'additionalImages' => 'sometimes|array',
//         'additionalImages.*' => 'sometimes|file|image|max:5000',
//         'existingAdditionalImages' => 'sometimes|array',
//         'existingAdditionalImages.*' => 'sometimes|string'
//     ]);

//     $article = Article::findOrFail($id);

//     // Helper function to convert storage URL to path
//     $urlToPath = fn($url) => str_replace('/storage', 'public', parse_url($url, PHP_URL_PATH));

//     // Handle main image update
//     if ($request->hasFile('mainImage')) {
//         // Delete old main image if it exists
//         if ($article->main_image_url) {
//             Storage::delete($urlToPath($article->main_image_url));
//         }

//         // Store new main image
//         $mainImagePath = $request->file('mainImage')->store('public/articles');
//         $article->main_image_url = Storage::url($mainImagePath);
//     }

//     // Initialize with existing images
//     $currentImages = $article->additional_image_urls ?? [];
//     $allAdditionalImages = $currentImages;

//     // Handle existing images from the form
//     if ($request->has('existingAdditionalImages')) {
//         // Normalize all URLs for comparison
//         $normalizeUrl = fn($url) => rtrim($url, '/');
//         $currentNormalized = array_map($normalizeUrl, $currentImages);
//         $requestedNormalized = array_map($normalizeUrl, $request->existingAdditionalImages);
        
//         // Find images to delete (present in current but not in request)
//         $imagesToDelete = array_diff($currentNormalized, $requestedNormalized);
        
//         // Delete removed images
//         foreach ($imagesToDelete as $imageUrl) {
//             Storage::delete($urlToPath($imageUrl));
//         }
        
//         // Keep only the requested existing images
//         $allAdditionalImages = $request->existingAdditionalImages;
//     }

//     // Add new additional images
//     if ($request->hasFile('additionalImages')) {
//         foreach ($request->file('additionalImages') as $image) {
//             $path = $image->store('public/articles');
//             $allAdditionalImages[] = Storage::url($path);
//         }
//     }

//     $article->additional_image_urls = $allAdditionalImages;

//     // Update other fields
//     $article->fill($request->only(['title', 'content', 'author']));
    
//     if ($request->has('title')) {
//         $article->slug = Str::slug($request->title);
//     }

//     $article->save();

//     return response()->json([
//         'message' => 'Article updated successfully',
//         'article' => $article
//     ]);
// }


//     public function destroy($id)
//     {
//         $article = Article::findOrFail($id);

//         // Delete main image
//         if ($article->main_image_url) {
//             $mainImagePath = str_replace('/storage', 'public', $article->main_image_url);
//             Storage::delete($mainImagePath);
//         }

//         // Delete additional images
//         if ($article->additional_image_urls) {
//             foreach ($article->additional_image_urls as $imageUrl) {
//                 $imagePath = str_replace('/storage', 'public', $imageUrl);
//                 Storage::delete($imagePath);
//             }
//         }

//         $article->delete();

//         return response()->noContent();
//     }
// }