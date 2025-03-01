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
     * Get all user preferences.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * Set user preferences for sources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * Set user preferences for categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * Set user preferences for authors.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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