<?php

namespace Administr\Localization\Database;

use Illuminate\Database\Seeder;

abstract class TranslationSeeder extends Seeder
{
    protected $model = null;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->get()->each(function($item){
            $model = app($this->model, [
                $item['info']
            ]);
            $model->save();

            foreach($item['translations'] as $lang => $translation)
            {
                $model
                    ->translate($lang)
                    ->fill($translation)
                    ->save();
            }
        });
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    abstract protected function get();
}