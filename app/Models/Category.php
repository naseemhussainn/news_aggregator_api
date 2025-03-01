<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];
    
    /**
     * Get the articles for the category.
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
    
    /**
     * Get the users who prefer this category.
     */
    public function preferredByUsers()
    {
        return $this->belongsToMany(User::class, 'user_preferences', 'preference_id', 'user_id')
            ->where('preference_type', 'category');
    }
}
