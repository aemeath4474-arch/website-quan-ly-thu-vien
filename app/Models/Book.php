<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Book extends Model {
    protected $fillable = ['category_id','title','isbn','publisher','publication_year','description','cover_image','price','rental_price','total_copies','available_copies','status'];
    protected function casts(): array { return ['price'=>'decimal:2','rental_price'=>'decimal:2']; }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function authors(): BelongsToMany { return $this->belongsToMany(Author::class, 'book_authors'); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function borrowOrderItems(): HasMany { return $this->hasMany(BorrowOrderItem::class, 'book_id'); }
    public function averageRating(): float { return (float) ($this->reviews()->avg('rating') ?? 0); }
}
