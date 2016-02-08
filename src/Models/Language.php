<?php

namespace Administr\Localization\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'administr_languages';
    protected $fillable = ['name', 'code'];
}
