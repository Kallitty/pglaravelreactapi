<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
        $mainImagePath = $request->file('mainImage')->store('public/articles');
        $mainImageUrl = Storage::url($mainImagePath);

        // Store additional images
        $additionalImageUrls = [];
        if ($request->hasFile('additionalImages')) {
            foreach ($request->file('additionalImages') as $image) {
                $path = $image->store('public/articles');
                $additionalImageUrls[] = Storage::url($path);
            }
        }

        $article = Article::create([
            'title' => $request->title,
            'content' => $request->content,
            'author' => $request->user()->name,
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
    
    // Format URLs consistently
    $formatUrl = function ($url) {
        if (!$url) return null;
        return asset(str_replace('/storage', 'storage', $url));
    };
    
    return response()->json([
        'title' => $article->title,
        'content' => $article->content,
        'author' => $article->author,
        'slug' => $article->slug,
        'main_image_url' => $formatUrl($article->main_image_url),
        'additional_image_urls' => collect($article->additional_image_urls ?? [])
            ->map($formatUrl)
            ->toArray(),
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
        'existingAdditionalImages.*' => 'sometimes|string'
    ]);

    $article = Article::findOrFail($id);

    // Helper function to convert storage URL to path
    $urlToPath = fn($url) => str_replace('/storage', 'public', parse_url($url, PHP_URL_PATH));

    // Handle main image update
    if ($request->hasFile('mainImage')) {
        // Delete old main image if it exists
        if ($article->main_image_url) {
            Storage::delete($urlToPath($article->main_image_url));
        }

        // Store new main image
        $mainImagePath = $request->file('mainImage')->store('public/articles');
        $article->main_image_url = Storage::url($mainImagePath);
    }

    // Initialize with existing images
    $currentImages = $article->additional_image_urls ?? [];
    $allAdditionalImages = $currentImages;

    // Handle existing images from the form
    if ($request->has('existingAdditionalImages')) {
        // Normalize all URLs for comparison
        $normalizeUrl = fn($url) => rtrim($url, '/');
        $currentNormalized = array_map($normalizeUrl, $currentImages);
        $requestedNormalized = array_map($normalizeUrl, $request->existingAdditionalImages);
        
        // Find images to delete (present in current but not in request)
        $imagesToDelete = array_diff($currentNormalized, $requestedNormalized);
        
        // Delete removed images
        foreach ($imagesToDelete as $imageUrl) {
            Storage::delete($urlToPath($imageUrl));
        }
        
        // Keep only the requested existing images
        $allAdditionalImages = $request->existingAdditionalImages;
    }

    // Add new additional images
    if ($request->hasFile('additionalImages')) {
        foreach ($request->file('additionalImages') as $image) {
            $path = $image->store('public/articles');
            $allAdditionalImages[] = Storage::url($path);
        }
    }

    $article->additional_image_urls = $allAdditionalImages;

    // Update other fields
    $article->fill($request->only(['title', 'content', 'author']));
    
    if ($request->has('title')) {
        $article->slug = Str::slug($request->title);
    }

    $article->save();

    return response()->json([
        'message' => 'Article updated successfully',
        'article' => $article
    ]);
}
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

//     // Handle main image update
//     if ($request->hasFile('mainImage')) {
//         // Delete old main image
//         if ($article->main_image_url) {
//             $oldImagePath = str_replace('/storage', 'public', $article->main_image_url);
//             Storage::delete($oldImagePath);
//         }

//         // Store new main image
//         $mainImagePath = $request->file('mainImage')->store('public/articles');
//         $article->main_image_url = Storage::url($mainImagePath);
//     }

//     // Initialize with existing images (if they exist)
//     $allAdditionalImages = $article->additional_image_urls ?? [];

//     // Handle existing images from the form
//     if ($request->has('existingAdditionalImages')) {
//         // Compare with current images to find deleted ones
//         $imagesToKeep = $request->existingAdditionalImages;
//         $imagesToDelete = array_diff($allAdditionalImages, $imagesToKeep);
        
//         // Delete removed images
//         foreach ($imagesToDelete as $imageUrl) {
//             $imagePath = str_replace('/storage', 'public', $imageUrl);
//             Storage::delete($imagePath);
//         }
        
//         // Update the array with kept images
//         $allAdditionalImages = $imagesToKeep;
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
//     if ($request->has('title')) {
//         $article->title = $request->title;
//         $article->slug = Str::slug($request->title);
//     }

//     if ($request->has('content')) {
//         $article->content = $request->content;
//     }

//     if ($request->has('author')) {
//         $article->author = $request->author;
//     }

//     $article->save();

//     return response()->json([
//         'message' => 'Article updated successfully',
//         'article' => $article
//     ]);
// }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);

        // Delete main image
        if ($article->main_image_url) {
            $mainImagePath = str_replace('/storage', 'public', $article->main_image_url);
            Storage::delete($mainImagePath);
        }

        // Delete additional images
        if ($article->additional_image_urls) {
            foreach ($article->additional_image_urls as $imageUrl) {
                $imagePath = str_replace('/storage', 'public', $imageUrl);
                Storage::delete($imagePath);
            }
        }

        $article->delete();

        return response()->noContent();
    }
}