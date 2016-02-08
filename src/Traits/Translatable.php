<?php

namespace Administr\Localization\Traits;

use Administr\Localization\Models\Language;

trait Translatable
{
    protected $translatable = [];
    protected $transaltionModel = null;

    public function language()
    {
        return $this->hasOne(Language::class);
    }

    public function translations()
    {
        return $this->hasMany($this->getTranslationModel(), $this->getTranslationRelationKey());
    }

    public function scopeTranslated($query, $language)
    {
        return $query->with(['translations' => function($q) use($language){
            $field = 'code';

            if( is_numeric($language) )
            {
                $field = 'language_id';
            }

            $q->where($field, $language);
        }]);
    }

    protected function getTranslationModel()
    {
        return $this->transaltionModel ?: get_class($this) . config('localization.model_suffix', 'Translation');
    }

    protected function getTranslationRelationKey()
    {
        return "{$this->table}_id";
    }

    public function __get($key)
    {
        if( !in_array($key, $this->translatable) )
        {
            return parent::__get($key);
        }

        return $this->translations->first()->getAttribute($key);
    }
}