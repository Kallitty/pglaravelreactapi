<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{   
    public function deleteProductImage($id)
    {
        // Find the product image by its ID
        $productImage = ProductImage::find($id);

        if ($productImage) {
            // Get the file path
            $filePath = public_path($productImage->image_path);

            // Check if the file exists and delete it from the storage
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            // Delete the image record from the database
            $productImage->delete();

            // Return success response
            return response()->json([
                'status' => 200,
                'message' => 'Product image deleted successfully.',
            ]);
        } else {
            // Return error response if image is not found
            return response()->json([
                'status' => 404,
                'message' => 'Product image not found.',
            ]);
        }
    }
    public function index()
    {
        // Fetch all products with their associated images
        $products = Product::with('images', 'category')->get();

        // Return response with status and products
        return response()->json([
            'status' => 200,
            'products' => $products,
        ]);
    }

    public function showBySlug($slug)
{
    $product = Product::with('product_images')->where('slug', $slug)->first();

    if ($product) {
        return response()->json([
            'status' => 200,
            'product' => $product,
        ]);
    } else {
        return response()->json([
            'status' => 404,
            'message' => 'Product not found',
        ]);
    }
}

    
            public function showing($id)
        {
            $product = Product::with('product_images')->find($id);

            if ($product) {
                // Log retrieved product data for debugging
                \Log::info("Product retrieved", ['product' => $product]);

                return response()->json([
                    'status' => 200,
                    'product' => $product,
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Product not found',
                ]);
            }
        }




    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|max:255',
            'slug' => 'required|max:255',
            'name' => 'required|max:255',
            'meta_title' => 'required|max:255',
            'brand' => 'required|max:255',
            'selling_price' => 'required|max:255',
            'original_price' => 'required|max:255',
            'qty' => 'required|integer',
            'images' => 'sometimes|array|max:8',
            'images.*' => 'mimes:jpg,jpeg,png|max:2048', // Max 2MB per image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'validation_errors' => $validator->messages(),
            ]);
        }

        // Store the product
        $product = Product::create([
            'category_id' => $request->input('category_id'),
            'slug' => $request->input('slug'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'meta_title' => $request->input('meta_title'),
            'meta_keyword' => $request->input('meta_keyword'),
            'meta_descrip' => $request->input('meta_descrip'),
            'brand' => $request->input('brand'),
            'selling_price' => $request->input('selling_price'),
            'original_price' => $request->input('original_price'),
            'qty' => $request->input('qty'),
            'featured' => $request->input('featured') ? 1 : 0,
            'popular' => $request->input('popular') ? 1 : 0,
            'status' => $request->input('status') ? 1 : 0,
        ]);

        // Handle product images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = time() . '-' . $image->getClientOriginalName();
                $image->move('uploads/product_images/', $imagePath);

                // Save image path in product_images table
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'uploads/product_images/' . $imagePath,
                ]);
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product Added Successfully.',
        ]);
    }

    public function edit($id)
    {
        $product = Product::with('images')->find($id); // Ensure to load related images

        if ($product) {
            return response()->json([
                'status' => 200,
                'product' => $product,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No Product Found',
            ]);
        }
    }

    public function delete($id)
    {
        $product = Product::with('images')->find($id);

        if ($product) {
            // Delete product images from the filesystem
            foreach ($product->images as $image) {
                $path = public_path($image->image_path);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }

            // Delete the product and related images from the database
            $product->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Product Deleted Successfully.',
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Product Not Found.',
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|max:255',
            'slug' => 'required|max:255',
            'name' => 'required|max:255',
            'meta_title' => 'required|max:255',
            'brand' => 'required|max:255',
            'selling_price' => 'required|max:255',
            'original_price' => 'required|max:255',
            'qty' => 'required|integer',
            'images' => 'sometimes|array|max:8',
            'images.*' => 'mimes:jpg,jpeg,png|max:2048', // Max 2MB per image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'validation_errors' => $validator->messages(),
            ]);
        }

        // Find the product by ID
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product Not Found',
            ]);
        }

        // Update product details
        $product->update([
            'category_id' => $request->input('category_id'),
            'slug' => $request->input('slug'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'meta_title' => $request->input('meta_title'),
            'meta_keyword' => $request->input('meta_keyword'),
            'meta_descrip' => $request->input('meta_descrip'),
            'brand' => $request->input('brand'),
            'selling_price' => $request->input('selling_price'),
            'original_price' => $request->input('original_price'),
            'qty' => $request->input('qty'),
            'featured' => $request->input('featured') ? 1 : 0,
            'popular' => $request->input('popular') ? 1 : 0,
            'status' => $request->input('status') ? 1 : 0,
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            // Optionally: Remove old images if new ones are uploaded
            foreach ($product->images as $existingImage) {
                // Delete the file from storage
                if (file_exists($existingImage->image_path)) {
                    unlink($existingImage->image_path);
                }
                // Remove the image record from the database
                $existingImage->delete();
            }

            // Upload new images
            foreach ($request->file('images') as $image) {
                $imagePath = time() . '-' . $image->getClientOriginalName();
                $image->move('uploads/product_images/', $imagePath);

                // Save new image path to the product_images table
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'uploads/product_images/' . $imagePath,
                ]);
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Product Updated Successfully',
        ]);
    }

            
    

    // public function getProductsByCategory($categorySlug)
    // {
    //     $products = Product::with('images') // Eager load images for each product
    //         ->whereHas('category', function ($query) use ($categorySlug) {
    //             $query->where('slug', $categorySlug);
    //         })
    //         ->get();

    //     return response()->json($products);
    // } 
    public function getProductsByCategory($categorySlug)
{
    $products = Product::with('images')
        ->whereHas('category', function ($query) use ($categorySlug) {
            $query->where('slug', $categorySlug);
        })
        ->paginate(12); // Changed from get() to paginate(12)

    return response()->json($products);
}


    //Related products wahala

  public function getRelatedProducts($categoryId, $productId)
{
    $relatedProducts = Product::where('category_id', $categoryId)
        ->where('id', '!=', $productId) // Exclude the current product
        ->with('product_images') // Ensure images are loaded
        ->inRandomOrder()
        ->take(3)
        ->get();

    return response()->json([
        'status' => 200,
        'relatedProducts' => $relatedProducts,
    ]);
}

    public function getNewCollections()
    {
        // Fetch recently added products, with the most recent first
        $newCollections = Product::with('product_images')  // Load images for each product
            ->orderBy('created_at', 'desc')
            ->take(8)  // Adjust the number of products as needed
            ->get();

        // Return the new collections in JSON format
        return response()->json([
            'status' => 200,
            'newCollections' => $newCollections,
        ]);
    }
        
    public function getPopularProducts()
    {
        // Fetch 4 random popular products
        $popularProducts = Product::with('product_images')  // Load images for each product
            ->where('popular', 1) 
            ->inRandomOrder() 
            ->take(5)  // Limit to 4 products
            ->get();

        // Return the popular products in JSON format
        return response()->json([
            'status' => 200,
            'popularProducts' => $popularProducts,
        ]);
    }

//     public function getRelatedProduct($categoryId, $productId)
// {
//     // Fetch four random products from the same category, excluding the current product
//     $relatedProducts = Product::where('category_id', $categoryId)
//         ->where('id', '!=', $productId) // Exclude the current product
//         ->inRandomOrder()
//         ->take(4) // Limit to four products
//         ->with('images') // Include related images for display
//         ->get();

//     return response()->json([
//         'status' => 200,
//         'relatedProducts' => $relatedProducts,
//     ]);
// }


//              public function relatedProducts($id)
//         {
//             // Find the product by its ID
//             $product = Product::find($id);

//             if (!$product) {
//                 return response()->json([
//                     'status' => 404,
//                     'message' => 'Product not found',
//                 ]);
//             }

//             // Get related products by matching category_id, excluding the current product
//             $relatedProducts = Product::where('category_id', $product->category_id)
//                                     ->where('id', '!=', $id)
//                                     ->with('product_images') // Load images
//                                     ->take(4) // Limit the number of related products
//                                     ->get();

//             return response()->json([
//                 'status' => 200,
//                 'related_products' => $relatedProducts,
//             ]);
//         }


//          public function getRelatedProducts(Request $request)
//     {
//         $categoryId = $request->query('category_id');
        
//         if (!$categoryId) {
//             return response()->json([], 400);
//         }

//         $relatedProducts = Product::where('category_id', $categoryId)
//             ->where('status', 1) // Ensure only active products are retrieved
//             ->limit(8) // Adjust limit as needed
//             ->get();

//         return response()->json($relatedProducts);
//     }

//     // Method to get fallback products if no related products are found
//     public function getFallbackProducts()
//     {
//         $fallbackProducts = Product::where('status', 1)
//             ->inRandomOrder()
//             ->limit(8) // Adjust limit as needed
//             ->get();

//         return response()->json($fallbackProducts);
//     }


}

// {
//      public function index(){
//         $products = Product::all();
//         return response()->json([
//             'status'=>200,
//             'products'=>$products,
//         ]);
//     }
 
       

// class ProductController extends Controller
// {       
//             public function index()
//         {
//             // Fetch all products with their associated images
//             $products = Product::with('images', 'category')->get();

//             // Return response with status and products
//             return response()->json([
//                 'status' => 200,
//                 'products' => $products,
//             ]);
//         }
    





//      public function show($id)
//         {
//             $product = Product::with('images')->find($id);

//             if ($product) {
//                 return response()->json([
//                     'status' => 200,
//                     'product' => $product,
//                 ]);
//             } else {
//                 return response()->json([
//                     'status' => 404,
//                     'message' => 'Product Not Found',
//                 ]);
//             }
//         }

//     public function store(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'category_id' => 'required|max:255',
//             'slug' => 'required|max:255',
//             'name' => 'required|max:255',
//             'meta_title' => 'required|max:255',
//             'brand' => 'required|max:255',
//             'selling_price' => 'required|max:255',
//             'original_price' => 'required|max:255',
//             'qty' => 'required|integer',
//             'images' => 'sometimes|array|max:8',
//             'images.*' => 'mimes:jpg,jpeg,png|max:2048', // Max 2MB per image
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => 422,
//                 'validation_errors' => $validator->messages(),
//             ]);
//         }

//         // Store the product
//         $product = Product::create([
//             'category_id' => $request->input('category_id'),
//             'slug' => $request->input('slug'),
//             'name' => $request->input('name'),
//             'description' => $request->input('description'),
//             'meta_title' => $request->input('meta_title'),
//             'meta_keyword' => $request->input('meta_keyword'),
//             'meta_descrip' => $request->input('meta_descrip'),
//             'brand' => $request->input('brand'),
//             'selling_price' => $request->input('selling_price'),
//             'original_price' => $request->input('original_price'),
//             'qty' => $request->input('qty'),
//             'featured' => $request->input('featured') ? 1 : 0,
//             'popular' => $request->input('popular') ? 1 : 0,
//             'status' => $request->input('status') ? 1 : 0,
//         ]);

//         // Handle product images
//         if ($request->hasFile('images')) {
//             foreach ($request->file('images') as $image) {
//                 $imagePath = time() . '-' . $image->getClientOriginalName();
//                 $image->move('uploads/product_images/', $imagePath);

//                 // Save image path in product_images table
//                 ProductImage::create([
//                     'product_id' => $product->id,
//                     'image_path' => 'uploads/product_images/' . $imagePath,
//                 ]);
//             }
//         }

//         return response()->json([
//             'status' => 200,
//             'message' => 'Product Added Successfully.',
//         ]);
//     }




   
//      public function edit($id)
// {
//     $product = Product::with('images')->find($id); // Ensure to load related images

//     if ($product) {
//         return response()->json([
//             'status' => 200,
//             'product' => $product,
//         ]);
//     } else {
//         return response()->json([
//             'status' => 404,
//             'message' => 'No Product Found',
//         ]);
//     }
// }



//    public function delete($id)
// {
//     $product = Product::with('images')->find($id);

//     if ($product) {
//         // Delete product images from the filesystem
//         foreach ($product->images as $image) {
//             $path = public_path($image->image_path);
//             if (File::exists($path)) {
//                 File::delete($path);
//             }
//         }

//         // Delete the product and related images from the database
//         $product->delete();

//         return response()->json([
//             'status' => 200,
//             'message' => 'Product Deleted Successfully.',
//         ]);
//     } else {
//         return response()->json([
//             'status' => 404,
//             'message' => 'Product Not Found.',
//         ]);
//     }
// }
 



//             public function update(Request $request, $id)
// {
//     // Validate the request
//     $validator = Validator::make($request->all(), [
//         'category_id' => 'required|max:255',
//         'slug' => 'required|max:255',
//         'name' => 'required|max:255',
//         'meta_title' => 'required|max:255',
//         'brand' => 'required|max:255',
//         'selling_price' => 'required|max:255',
//         'original_price' => 'required|max:255',
//         'qty' => 'required|integer',
//         'images' => 'sometimes|array|max:8',
//         'images.*' => 'mimes:jpg,jpeg,png|max:2048', // Max 2MB per image
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'status' => 422,
//             'validation_errors' => $validator->messages(),
//         ]);
//     }

//     // Find the product by ID
//     $product = Product::find($id);

//     if (!$product) {
//         return response()->json([
//             'status' => 404,
//             'message' => 'Product Not Found',
//         ]);
//     }

//     // Update product details
//     $product->update([
//         'category_id' => $request->input('category_id'),
//         'slug' => $request->input('slug'),
//         'name' => $request->input('name'),
//         'description' => $request->input('description'),
//         'meta_title' => $request->input('meta_title'),
//         'meta_keyword' => $request->input('meta_keyword'),
//         'meta_descrip' => $request->input('meta_descrip'),
//         'brand' => $request->input('brand'),
//         'selling_price' => $request->input('selling_price'),
//         'original_price' => $request->input('original_price'),
//         'qty' => $request->input('qty'),
//         'featured' => $request->input('featured') ? 1 : 0,
//         'popular' => $request->input('popular') ? 1 : 0,
//         'status' => $request->input('status') ? 1 : 0,
//     ]);

//     // Handle image uploads
//     if ($request->hasFile('images')) {
//         // Optionally: Remove old images if new ones are uploaded
//         foreach ($product->images as $existingImage) {
//             // Delete the file from storage
//             if (file_exists($existingImage->image_path)) {
//                 unlink($existingImage->image_path);
//             }
//             // Remove the image record from the database
//             $existingImage->delete();
//         }

//         // Upload new images
//         foreach ($request->file('images') as $image) {
//             $imagePath = time() . '-' . $image->getClientOriginalName();
//             $image->move('uploads/product_images/', $imagePath);

//             // Save new image path to the product_images table
//             ProductImage::create([
//                 'product_id' => $product->id,
//                 'image_path' => 'uploads/product_images/' . $imagePath,
//             ]);
//         }
//     }
//  }
  
//         return response()->json([
//             'status' => 200,
//             'message' => 'Product Updated Successfully',
//         ]);
           
//         }else {
//                     return response()->json([
//                         'status' => 404,
//                         'message' => "Product Not Found",
//                     ]);
//                 }
                
             //commented recently   
            
    // public function update(Request $request, $id)
// {
//     $validator = Validator::make($request->all(), [
//         'category_id' => 'required|max:255',
//         'slug' => 'required|max:255',
//         'name' => 'required|max:255',
//         'meta_title' => 'required|max:255',
//         'brand' => 'required|max:255',
//         'selling_price' => 'required|max:255',
//         'original_price' => 'required|max:255',
//         'qty' => 'required|max:4',
//         'image' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'status' => 422,
//             'errors' => $validator->messages(),
//         ]);
//     } else {
//         $product = Product::find($id);
//         if ($product) {
//             $product->category_id = $request->input('category_id');
//             $product->slug = $request->input('slug');
//             $product->name = $request->input('name');
//             $product->description = $request->input('description');
//             $product->meta_title = $request->input('meta_title');
//             $product->meta_keyword = $request->input('meta_keyword');
//             $product->meta_descrip = $request->input('meta_descrip');
//             $product->brand = $request->input('brand');
//             $product->selling_price = $request->input('selling_price');
//             $product->original_price = $request->input('original_price');
//             $product->qty = $request->input('qty');

//             if ($request->hasFile('image')) {
//                 $file = $request->file('image');

//                 // Check if the file is valid and matches allowed MIME types and size
//                 if ($file->isValid() && in_array($file->getClientMimeType(), ['image/jpeg', 'image/png', 'image/jpg']) && $file->getSize() <= 2048 * 1024) {
//                     $path = $product->image;
//                     if (File::exists($path)) {
//                         File::delete($path);
//                     }

//                     $extension = $file->getClientOriginalExtension();
//                     $filename = time() . '.' . $extension;
//                     $file->move('uploads/product/', $filename);
//                     $product->image = 'uploads/product/' . $filename;
//                 } else {
//                     return response()->json([
//                         'status' => 422,
//                         'errors' => [
//                             'image' => ['Invalid file type or size. Only jpg, jpeg, png files up to 2MB are allowed.']
//                         ],
//                     ]);
//                 }
//             }

//             $product->featured = $request->input('featured') == true ? '1' : '0';
//             $product->popular = $request->input('popular') == true ? '1' : '0';
//             $product->status = $request->input('status') ? '1' : '0';
//             $product->update();

//             return response()->json([
//                 'status' => 200,
//                 'message' => "Product Updated Successfully",
//             ]);
//         } else {
            //         return response()->json([
            //             'status' => 404,
            //             'message' => "Product Not Found",
            //         ]);
            //     }
            // }
            
                    
    // public function store(Request $request){
    //     $validator =Validator::make($request->all(), [
            
    //         'category_id' => 'required|max:255',
    //         'slug' => 'required|max:255',
    //         'name' => 'required|max:255',
    //         'meta_title' => 'required|max:255',
    //         'brand' => 'required|max:255',
    //         'selling_price' => 'required|max:255',
    //         'original_price' => 'required|max:255',
    //         'qty' => 'required|max:4',
    //         'image' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
            
            
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 422,
    //             'validation_errors' => $validator->messages(),
    //         ]);
    //     } else {
    //          $product = new Product();
    //           $product->category_id = $request->input('category_id');
    //           $product->slug = $request->input('slug');
    //        $product->name = $request->input('name');
    //        $product->description = $request->input('description');
           
    //        $product->meta_title = $request->input('meta_title');
    //        $product->meta_keyword = $request->input('meta_keyword');
    //        $product->meta_descrip = $request->input('meta_descrip');

    //        $product->brand = $request->input('brand');
    //        $product->selling_price = $request->input('selling_price');
    //        $product->original_price = $request->input('original_price');
    //        $product->qty = $request->input('qty');

    //         if ($request->hasFile('image'))
    //         {
    //             $file = $request->file('image');
    //             $extension =$file->getClientOriginalExtension();
    //             $filename= time().'.' .$extension;
    //             $file->move('uploads/product/', $filename);
    //             $product->image = 'uploads/product/'.$filename;

    //         }
            

    //        $product->featured = $request->input('featured')==true? '1':'0' ;
    //        $product->popular = $request->input('popular')==true? '1':'0' ;
    //        $product->status = $request->input('status') ? '1' : '0';
    //        $product->save();

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Product Added Successfully.',
    //         ]);
    //     }
       


    // }










