<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Validator;
use App\Models\Category;

class CategoryController extends Controller
{

    public function index(){
        $category = Category::all();
        return response()->json([
            'status'=>200,
            'category'=>$category,
        ]);
    }

    public function allcategory(){
        $category = Category::where('status', '0')->get();
        return response()->json([
            'status'=>200,
            'category'=>$category,
        ]);
    }



        public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'meta_title' => 'required|max:255',
        'slug' => 'required|max:255',
        'name' => 'required|max:255',
        'description' => 'sometimes|string',
        'status' => 'sometimes|boolean',
        'navdisplay' => 'sometimes|boolean',
        'featured' => 'sometimes|boolean',
        'meta_keyword' => 'sometimes|string',
        'meta_descrip' => 'sometimes|string',
        'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // Image validation
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'validation_errors' => $validator->messages(),
        ]);
    } else {
        $category = new Category();
        $category->meta_title = $request->input('meta_title');
        $category->meta_descrip = $request->input('meta_descrip');
        $category->meta_keyword = $request->input('meta_keyword');
        $category->description = $request->input('description');
        $category->slug = $request->input('slug');
        $category->name = $request->input('name');
        $category->status = $request->input('status') ? '1' : '0';
        $category->navdisplay = $request->input('navdisplay') ? '1' : '0';
        $category->featured = $request->input('featured') ? '1' : '0';

        // Handle image upload if it exists
        if ($request->hasFile('image')) {
            // Store the image
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/categories'), $imageName);

            // Save the image name in the category
            $category->image = $imageName;
        }

        $category->save();

        return response()->json([
            'status' => 200,
            'message' => 'Category Added Successfully.',
        ]);
    }
}


    
    public function edit($id)
    {
        try {
            $category = Category::findOrFail($id);

            return response()->json([
                'status' => 200,
                'category' => $category,
            ]);
             // Log the updated fields before saving
        \Log::info('Updated edditt fields (before sending):', [
            'category_id' => $category->id,
            'meta_title' => $category->meta_title,
            'slug' => $category->slug,
            'name' => $category->name,
            'description' => $category->description,
            'status' => $category->status,
            'navdisplay' => $category->navdisplay,
            'featured' => $category->featured,
            'meta_keyword' => $category->meta_keyword,
            'meta_descrip' => $category->meta_descrip,
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 404,
                'message' => 'No Category Id found',
            ]);
        }
    }

    

            //         public function update(Request $request, $id)
            // {
            //     // Validate the incoming request data
            //     $request->validate([
            //         'meta_title' => 'sometimes|string|max:255',
            //         'slug' => 'sometimes|string|max:255',
            //         'name' => 'sometimes|string|max:255',
            //         'description' => 'sometimes|string',
            //         'status' => 'sometimes|boolean',
            //         'meta_keyword' => 'sometimes|string',
            //         'meta_descrip' => 'sometimes',
            //         'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
            //     ]);
                   
            //     try {
            //         // Find the category by ID
            //         $category = Category::findOrFail($id);

            //         // Update the category fields with the request data
            //         $category->meta_title = $request->input('meta_title', $category->meta_title);
            //         $category->meta_descrip = $request->input('meta_descrip', $category->meta_descrip);
            //         $category->slug = $request->input('slug', $category->slug);
            //         $category->name = $request->input('name', $category->name);
            //         $category->description = $request->input('description', $category->description);
            //         $category->status = $request->input('status') ? '1' : '0';
            //         $category->meta_keyword = $request->input('meta_keyword', $category->meta_keyword);

            //         // Handle image upload if it exists
            //         if ($request->hasFile('image')) {
            //             // Delete the old image if it exists
            //             if ($category->image) {
            //                 $oldImagePath = public_path('uploads/categories/' . $category->image);
            //                 if (file_exists($oldImagePath)) {
            //                     unlink($oldImagePath);
            //                 }
            //             }

            //             // Store the new image
            //             $image = $request->file('image');
            //             $imageName = time() . '.' . $image->getClientOriginalExtension();
            //             $image->move(public_path('uploads/categories'), $imageName);

            //             // Update the category image
            //             $category->image = $imageName;
            //         }
                           
            //         // Save the updated category
            //         $category->save();

            //         return response()->json([
            //             'status' => 200,
            //             'message' => 'Category updated successfully',
            //             'category' => $category,
            //         ]);
            //     } catch (\Exception $e) {
            //         // Log the error and return a failure response
            //         \Log::error('Failed to update category', ['error' => $e->getMessage()]);
            //         return response()->json(['message' => 'Failed to update category', 'error' => $e->getMessage()], 500);
            //     }
            // }
         public function update(Request $request, $id)
        {
    // Validate the incoming request data
    $request->validate([
        'meta_title' => 'sometimes|string|max:255',
        'slug' => 'sometimes|string|max:255',
        'name' => 'sometimes|string|max:255',
        'description' => 'sometimes|string',
        'status' => 'sometimes|boolean',
        'navdisplay' => 'sometimes|boolean',
        'featured' => 'sometimes|boolean',
        'meta_keyword' => 'sometimes|string',
        'meta_descrip' => 'sometimes',
        'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
    ]);

    try {
        // Log the incoming request data before updating
        \Log::info('Incoming update request data:', $request->all());

        // Find the category by ID
        $category = Category::findOrFail($id);

        // Log the current state of the category before updating
        \Log::info('Current category data before update:', [
            'category_id' => $category->id,
            'meta_title' => $category->meta_title,
            'slug' => $category->slug,
            'name' => $category->name,
            'description' => $category->description,
            'status' => $category->status,
            'navdisplay' => $category->navdisplay,
            'featured' => $category->featured,
            'meta_keyword' => $category->meta_keyword,
            'meta_descrip' => $category->meta_descrip,
            'image' => $category->image,
        ]);

        // Update the category fields with the request data
        
        $category->meta_title = $request->input('meta_title', $category->meta_title);
        $category->meta_descrip = $request->input('meta_descrip', $category->meta_descrip);
        $category->slug = $request->input('slug', $category->slug);
        $category->name = $request->input('name', $category->name);
        $category->description = $request->input('description', $category->description);
        $category->status = $request->input('status') ? '1' : '0';
        $category->navdisplay = $request->input('navdisplay') ? '1' : '0';
        $category->featured = $request->input('featured') ? '1' : '0';
        $category->meta_keyword = $request->input('meta_keyword', $category->meta_keyword);

        // Log the updated fields before saving
        \Log::info('Updated category fields (before save):', [
            'category_id' => $category->id,
            'meta_title' => $category->meta_title,
            'slug' => $category->slug,
            'name' => $category->name,
            'description' => $category->description,
            'status' => $category->status,
            'navdisplay' => $category->navdisplay,
            'featured' => $category->featured,
            'meta_keyword' => $category->meta_keyword,
            'meta_descrip' => $category->meta_descrip,
        ]);

                if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($category->image) {
                    $oldImagePath = public_path('uploads/categories/' . $category->image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Store the new image
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/categories'), $imageName);

                // Update the category image
                $category->image = $imageName;
            } else {
                // Log that no new image was uploaded
                \Log::info('No new image uploaded. Retaining the current image.');
            }


                    // Save the updated category
                     $category->save();

        // Log the saved category data
        \Log::info('Category saved with new data:', [
            'category_id' => $category->id,
            'meta_title' => $category->meta_title,
            'slug' => $category->slug,
            'name' => $category->name,
            'description' => $category->description,
            'status' => $category->status,
            'navdisplay' => $category->navdisplay,
            'featured' => $category->featured,
            'meta_keyword' => $category->meta_keyword,
            'meta_descrip' => $category->meta_descrip,
            'image' => $category->image,
        ]);

        // Log the successful update
        \Log::info('Category updated successfully', ['category_id' => $category->id]);

        return response()->json([
            'status' => 200,
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);

    } catch (\Exception $e) {
        // Log the error and return a failure response
        \Log::error('Failed to update category', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Failed to update category', 'error' => $e->getMessage()], 500);
    }
}

      

            public function destroy($id)
            {
                $category = Category::findOrFail($id);
                $category->delete();

                return response()->json(['message' => 'Category deleted successfully']);
            }
}
