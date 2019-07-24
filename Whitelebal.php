<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\User;

class Whitelebal extends Model
{
    use \Spiritix\LadaCache\Database\LadaCacheTrait;
    
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
