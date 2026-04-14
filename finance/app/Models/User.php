<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name', 
        'password',
        'email',
        'phone',
    ];
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function categories() 
    {
        return $this->hasMany(Categories::class);
    }

    public function goals() 
    {
        return $this->hasMany(Goal::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin()
    {
        return $this->role && $this->role->name === 'admin';
    }
}
