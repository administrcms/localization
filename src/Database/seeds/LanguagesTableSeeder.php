<?php

use Administr\Localization\Models\Language;
use Illuminate\Database\Seeder;


class LanguagesTableSeeder extends Seeder
{
    public function run()
    {
        $languages = [
            ['name' => 'English', 'code' => 'en'],
            ['name' => 'Български', 'code' => 'bg'],
        ];

        foreach($languages as $language)
        {
            Language::create($language);
        }
    }
}
