<?php

namespace Administr\Localization\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

abstract class Translatable extends Model
{
    protected $translatable = [];
    protected $transaltionModel = null;

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
        }]);
    }

    /**
     * Retrieve a new translation model and set its parent to the current model.
     *
     * @param int|string $language
     * @return Model
     */
    public function translate($language = null)
    {
        if( !is_numeric($language) )
        {
            $language_id = Language::where('code', $language)->first(['id']);
            $language = $language_id ? $language_id->id : session('lang.id');
        }

        $translation = app( $this->getTranslationModel() );
        $translation->setAttribute('language_id', $language);
        $this->translations->add($translation);

        return $translation;
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
     * Add the translated fields to the model when cast to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = parent::toArray();
        $hiddenAttributes = $this->getHidden();

        foreach ($this->translatable as $field) {
            if (in_array($field, $hiddenAttributes)) {
                continue;
            }

            if ($translations = $this->translations->first()) {
                $attributes[$field] = $translations->$field;
            }
        }

        return $attributes;
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