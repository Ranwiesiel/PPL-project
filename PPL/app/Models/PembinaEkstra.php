<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User as Authenticatable;
class PembinaEkstra extends Authenticatable
{
    public $timestamps = false;
    protected $primaryKey = 'id_pembina_ekstra';

    /**
     * The "booting" function of model
     *
     * @return void
     */
    protected static function boot() {
        static::creating(function ($model) {
            if ( ! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

     /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'pembina_ekstra';
    protected $fillable = [
        'username_pembina_ekstra',
        'password_pembina_ekstra',
        'email_pembina_ekstra',
        'google_key_pembina_ekstra',
        'no_wa_pembina_ekstra',
        'alamat_pembina_ekstra',
    ];
    
}
