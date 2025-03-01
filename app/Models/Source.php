<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'api_id', 'url', 'api_provider'];
    
    /**
     * Get the articles for the source.
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
    
    /**
     * Get the users who prefer this source.
     */
    public function preferredByUsers()
    {
        return $this->belongsToMany(User::class, 'user_preferences', 'preference_id', 'user_id')
            ->where('preference_type', 'source');
    }
}
