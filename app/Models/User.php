<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Order;
use App\Enums\UserTypeEnum;
use App\Services\UserService;
use Laravel\Cashier\Billable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'customer_no'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
         parent::boot();
         static::creating(function ($user) {
            if (empty($user->customer_no)) {
                $user->customer_no = app(UserService::class)->generateCustomerNumber();
             }
         });
    }

    public function isAdmin(): bool
    {
        return in_array($this->user_type, [UserTypeEnum::ADMIN->value, UserTypeEnum::SUPER_ADMIN->value]);
    }

    public function isSuperAdmin(): bool
    { 
        return $this->user_type === UserTypeEnum::SUPER_ADMIN->value;
    }

    public function orders(): HasMany
    { 
        return $this->hasMany(Order::class);
    }
}
