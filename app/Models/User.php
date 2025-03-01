<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

        /**
     * Get the user's preferred sources.
     */
    public function preferredSources()
    {
        return $this->belongsToMany(Source::class, 'user_preferences', 'user_id', 'preference_id')
            ->where('preference_type', 'source');
    }
    
    /**
     * Get the user's preferred categories.
     */
    public function preferredCategories()
    {
        return $this->belongsToMany(Category::class, 'user_preferences', 'user_id', 'preference_id')
            ->where('preference_type', 'category');
    }
    
    /**
     * Get the user's preferred authors.
     */
    public function preferredAuthors()
    {
        return $this->belongsToMany(Author::class, 'user_preferences', 'user_id', 'preference_id')
            ->where('preference_type', 'author');
    }
}
