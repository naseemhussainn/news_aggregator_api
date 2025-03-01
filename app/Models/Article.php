<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title', 
        'description', 
        'content', 
        'url', 
        'image_url', 
        'source_id', 
        'category_id', 
        'external_id', 
        'published_at'
    ];
    
    protected $casts = [
        'published_at' => 'datetime',
    ];
    
    /**
     * Get the source that owns the article.
     */
    public function source()
    {
        return $this->belongsTo(Source::class);
    }
    
    /**
     * Get the category that owns the article.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Get the authors for the article.
     */
    public function authors()
    {
        return $this->belongsToMany(Author::class);
    }
    
    /**
     * Scope a query to search for articles.
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            return $query->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('content', 'like', "%{$keyword}%");
        }
        
        return $query;
    }
    
    /**
     * Scope a query to filter by date.
     */
    public function scopeByDate($query, $date)
    {
        if ($date) {
            return $query->whereDate('published_at', $date);
        }
        
        return $query;
    }
    
    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        if ($categoryId) {
            return $query->where('category_id', $categoryId);
        }
        
        return $query;
    }
    
    /**
     * Scope a query to filter by source.
     */
    public function scopeBySource($query, $sourceId)
    {
        if ($sourceId) {
            return $query->where('source_id', $sourceId);
        }
        
        return $query;
    }
    
    /**
     * Scope a query to filter by author.
     */
    public function scopeByAuthor($query, $authorId)
    {
        if ($authorId) {
            return $query->whereHas('authors', function ($q) use ($authorId) {
                $q->where('authors.id', $authorId);
            });
        }
        
        return $query;
    }
}