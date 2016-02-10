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

    public function translation($language)
    {
        return $this->translations->filter(function($translation) use ($language){
            return $translation instanceof Model && $translation->language_id == $language;
        })->first();
    }

    /**
     * Does the model have a translation in a given language.
     *
     * @param $language int
     * @return bool
     */
    public function isTranslatedIn($language)
    {
        return (bool)$this->translation($language);
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

        if($this->hasTranslations() && $this->isTranslatedIn($language)) {
            return $this->translation($language);
        }

        $translation = app($this->getTranslationModel());
        $translation->language_id = $language;
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

    public function isTranslatable($field)
    {
        return in_array($field, $this->translatable);
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

    protected function saveTranslations()
    {
        $saved = true;
        foreach ($this->translations as $translation) {
            if ($saved && $this->isTranslationDirty($translation)) {
                $translation->setAttribute($this->getTranslationRelationKey(), $this->getKey());
                $saved = $translation->save();
            }
        }
        return $saved;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $translation
     *
     * @return bool
     */
    protected function isTranslationDirty(Model $translation)
    {
        $dirtyAttributes = $translation->getDirty();
        return count($dirtyAttributes) > 0;
    }


    /**
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        // Update
        if ($this->exists) {

            if (count($this->getDirty()) > 0) {
                // If $this->exists and dirty, parent::save() has to return true. If not,
                // an error has occurred. Therefore we shouldn't save the translations.
                if (parent::save($options)) {
                    return $this->saveTranslations();
                }

                return false;
            }

            // If $this->exists and not dirty, parent::save() skips saving and returns
            // false. So we have to save the translations
            if ($saved = $this->saveTranslations()) {
                $this->fireModelEvent('saved', false);
                $this->fireModelEvent('updated', false);
            }

            return $saved;
        }

        // Insert
        if (parent::save($options)) {
            // We save the translations only if the instance is saved in the database.
            return $this->saveTranslations();
        }

        return false;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes)
    {
        $language = array_key_exists('language_id', $attributes) ? $attributes['language_id'] : null;
        $translation = $this->translate($language);

        foreach ($attributes as $key => $value) {
            if($this->isTranslatable($key) && $this->isFillable($key)) {
                $translation->setAttribute($key, $value);
                unset($attributes[$key]);
            }
        }

        return parent::fill($attributes);
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