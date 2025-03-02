<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserPreferenceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/preferences",
     *     summary="Get all user preferences",
     *     tags={"User Preferences"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User preferences retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="sources",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Source")
     *             ),
     *             @OA\Property(
     *                 property="categories",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Category")
     *             ),
     *             @OA\Property(
     *                 property="authors",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Author")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'sources' => $user->preferredSources,
            'categories' => $user->preferredCategories,
            'authors' => $user->preferredAuthors
        ]);
    }
    
    /**
     * @OA\Post(
     *     path="/api/preferences/sources",
     *     summary="Set user preferences for sources",
     *     tags={"User Preferences"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"source_ids"},
     *             @OA\Property(
     *                 property="source_ids",
     *                 type="array",
     *                 @OA\Items(type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Source preferences updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Source preferences updated successfully"),
     *             @OA\Property(
     *                 property="sources",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Source")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={"source_ids": {"The source_ids field is required."}})
     *         )
     *     )
     * )
     */
    public function setSources(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_ids' => 'required|array',
            'source_ids.*' => 'exists:sources,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user = $request->user();
        
        // Remove all current source preferences
        UserPreference::where('user_id', $user->id)
            ->where('preference_type', 'source')
            ->delete();
        
        // Add new preferences
        foreach ($request->source_ids as $sourceId) {
            UserPreference::create([
                'user_id' => $user->id,
                'preference_type' => 'source',
                'preference_id' => $sourceId
            ]);
        }
        
        return response()->json([
            'message' => 'Source preferences updated successfully',
            'sources' => Source::whereIn('id', $request->source_ids)->get()
        ]);
    }
    
    /**
     * @OA\Post(
     *     path="/api/preferences/categories",
     *     summary="Set user preferences for categories",
     *     tags={"User Preferences"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_ids"},
     *             @OA\Property(
     *                 property="category_ids",
     *                 type="array",
     *                 @OA\Items(type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category preferences updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Category preferences updated successfully"),
     *             @OA\Property(
     *                 property="categories",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Category")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={"category_ids": {"The category_ids field is required."}})
     *         )
     *     )
     * )
     */
    public function setCategories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user = $request->user();
        
        // Remove all current category preferences
        UserPreference::where('user_id', $user->id)
            ->where('preference_type', 'category')
            ->delete();
        
        // Add new preferences
        foreach ($request->category_ids as $categoryId) {
            UserPreference::create([
                'user_id' => $user->id,
                'preference_type' => 'category',
                'preference_id' => $categoryId
            ]);
        }
        
        return response()->json([
            'message' => 'Category preferences updated successfully',
            'categories' => Category::whereIn('id', $request->category_ids)->get()
        ]);
    }
    
    /**
     * @OA\Post(
     *     path="/api/preferences/authors",
     *     summary="Set user preferences for authors",
     *     tags={"User Preferences"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"author_ids"},
     *             @OA\Property(
     *                 property="author_ids",
     *                 type="array",
     *                 @OA\Items(type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author preferences updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Author preferences updated successfully"),
     *             @OA\Property(
     *                 property="authors",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Author")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={"author_ids": {"The author_ids field is required."}})
     *         )
     *     )
     * )
     */
    public function setAuthors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'author_ids' => 'required|array',
            'author_ids.*' => 'exists:authors,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user = $request->user();
        
        // Remove all current author preferences
        UserPreference::where('user_id', $user->id)
            ->where('preference_type', 'author')
            ->delete();
        
        // Add new preferences
        foreach ($request->author_ids as $authorId) {
            UserPreference::create([
                'user_id' => $user->id,
                'preference_type' => 'author',
                'preference_id' => $authorId
            ]);
        }
        
        return response()->json([
            'message' => 'Author preferences updated successfully',
            'authors' => Author::whereIn('id', $request->author_ids)->get()
        ]);
    }
}