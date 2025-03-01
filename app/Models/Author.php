<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'api_id'];
    
    /**
     * Get the articles for the author.
     */
    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }
    
    /**
     * Get the users who prefer this author.
     */
    public function preferredByUsers()
    {
        return $this->belongsToMany(User::class, 'user_preferences', 'preference_id', 'user_id')
            ->where('preference_type', 'author');
    }
}
