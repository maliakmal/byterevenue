<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, HasRoles, TwoFactorAuthenticatable;

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
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'hasRoles',
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

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function latestCampaign()
    {
        return $this->hasOne(Campaign::class)->latest('id');
    }

    public function recipientLists()
    {
        return $this->hasMany(RecipientsList::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

    public function hasEnoughTokens($required_tokens){
        return $this->tokens >= $required_tokens ? true : false;
    }

    public function deductTokens($amount)
    {
        $this->transactions()->create([
            'user_id' => $this->id,
            'amount' => abs($amount) * -1,
            'type' => 'usage',
        ]);

        return $this->decrement('tokens', $amount);
    }

    public function addTokens($amount){
        $this->transactions()->create([
            'user_id' => $this->id,
            'amount' => abs($amount),
            'type' => 'purchase',
        ]);

        return $this->increment('tokens', $amount);
    }

    public function getHasRolesAttribute()
    {
        return $this->roles()->pluck('name');
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }
}
