<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = ['name','email','password','phone','address','role','status'];
    protected $hidden = ['password','remember_token'];
    protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed']; }
    public function borrowOrders(): HasMany { return $this->hasMany(BorrowOrder::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function bookNotifications(): HasMany { return $this->hasMany(BookNotification::class, 'user_id'); }
    public function notifications(): HasMany { return $this->bookNotifications(); }
    public function favorites(): BelongsToMany { return $this->belongsToMany(Book::class, 'favorites')->withPivot('created_at'); }
    public function isAdmin(): bool { return $this->role === 'admin'; }
}
