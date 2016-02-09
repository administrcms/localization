<?php

namespace Administr\Localization\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

abstract class Translatable extends Model
{
    protected $translatable = [];
    protected $transaltionModel = null;

    /**
     * Relationship to the languages table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function language()
    {
        return $this->hasOne(Language::class);
    }

    /**
     * Relationship to the translation table of the current model.
     *
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany($this->getTranslationModel(), $this->getTranslationRelationKey());
    }

    /**
     * Get a translation for given language.
     *
     * @param $query
     * @param $language string|int
     * @return mixed
     */
    public function scopeTranslated($query, $language = null)
    {
        $language = empty($language) ? session('lang.id') : $language;

        return $query->with(['translations' => function(HasMany $q) use($language){
            $field = 'language_id';

            if( !is_numeric($language) )
            {
                $field = 'code';
                $q->leftJoin('administr_languages', 'administr_languages.id', '=', 'language_id');
            }

            // the table name for the translations
            $translationTable = $q->getRelated()->getTable();

            // make all the translatable fields in the form of `translation_table`.`field`
            // to avoid collision on naming with the languages table
            $fields = array_map(function($field) use ($translationTable){
                return "{$translationTable}.{$field}";
            }, $this->translatable);

            // add the extra fields, that are not filled in the translatable property
            // to make the join work
            $fields[] = 'language_id';
            $fields[] = $this->getTranslationRelationKey();

            $q
                ->where($field, $language)
                ->select($fields);
        }])->first();
    }

    /**
     * Does the model have any translations.
     *
     * @return bool
     */
    public function hasTranslations()
    {
        return count($this->translations) > 0;
    }

    /**
     * Build the translation model class name.
     *
     * @return string
     */
    protected function getTranslationModel()
    {
        return $this->transaltionModel ?: get_class($this) . config('localization.model_suffix', 'Translation');
    }

    /**
     * Get the foreign key for the translation.
     *
     * @return string
     */
    protected function getTranslationRelationKey()
    {
        return "{$this->table}_id";
    }

    /**
     * Give the user access to the translated fields from the main model.
     *
     * @param string $key
     * @return mixed|null
     */
    public function __get($key)
    {
        if( !in_array($key, $this->translatable) )
        {
            return parent::__get($key);
        }

        return !empty($this->translations->first()) ? $this->translations->first()->getAttribute($key) : null;
    }
}