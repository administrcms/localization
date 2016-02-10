# Localization package for AdministrCMS

Although it is written to work with the aministr package, you can use this one as a standalone with Laravel 5.2.

It is still a work-in-progress.

# Installation

Using [Composer](https://getcomposer.org/):

```
composer require administrcms/localization
```

Add the service provider:

```php
\Administr\Localization\LocalizationServiceProvider::class,
```

The Facade:

```php
'Locale'    => \Administr\Localization\LocalizationFacade::class,
```

Register the middleware in your App Kernel and be sure that the session middleware is registered before it:

```php
\Administr\Localization\LocalizationMiddleware::class,
```

# What it does

It basically checks and persist the current language in a session and it assumes that you are using a parameter for the language in your routes. If you are using a diffrent method for changing languages, you can register your own middleware and utilize the Localizator class included in this package.

Also it creates a table for storing the available languages and has a Language model under the namespace `Administr\Localization\Language`. If you have multilingual data in your db, it also provides a `Translatable` model, which extends the base Eloquent model and adds functionality for translations.

# Usage of translatable models

You will have to create two tables - one that stores the main model data that is not translatable and a second that will hold the translations.

They can look something like this:

```php

Schema::create('people', function (Blueprint $table) {
    $table->increments('id');
    $table->boolean('is_visible')->default(0);
    $table->timestamps();
});

Schema::create('people_translations', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('language_id')->unsigned();
    $table->integer('person_id')->unsigned();

    $table->string('name', 100);
    $table->string('position', 100);

    $table->timestamps();

    $table->foreign('language_id')->references('id')->on('administr_languages')->onDelete('cascade');
    $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade');
});

```

And the corresponding models:

```php

// Person.php

namespace App\Models;

use Administr\Localization\Models\Translatable;

class Person extends Translatable
{
    protected $table = 'people';
    protected $fillable = ['is_visible', 'name', 'position'];
    protected $translatable = ['name', 'position'];
}

// PersonTranslation.php

use Illuminate\Database\Eloquent\Model;

class PersonTranslation extends Model
{
    protected $table = 'people_translations';
    protected $fillable = ['language_id', 'name', 'position'];
}

```

And then in your app code:

```php
 // Get the first model with translation in the current app locale
$person = Person::translated()->first();
 // Translated in the language with id of 1
$person = Person::translated(1)->first();
// Translated in the language with code of 'bg' - have in mind that when you do it like this, an additional query will be made to find the language id
$person = Person::translated('bg')->first();

$person->name; // You can access the translation model properties through the main model

// Create an instance of the main model
// with a translation in the current locale.
// If you do not pass translatable fields,
// only the main model will be persisted.
Person::create([
    'name'      => 'Miroslav Vitanov',
    'position'  => 'Developer',
]);

// Updates the model and the translation that was
// created in the current locale.
Person::find(1)->update([
    'name'      => 'Miroslav Vitanov',
    'position'  => 'PHP Developer',
]);

// Translate a model in another language.
// You can pass a locale code or language id.
Person::find(1)->translate('bg')
->fill([
   'name'      => 'Мирослав Витанов',
   'position'  => 'Програмист',
])
->save();

// Check if any translations exist
$person->hasTranslations();

// Check if a translation with language id exists
$person->isTranslatedIn(1);

// Get all translations
$person->translations;

// When a model is serialized to an array,
// it will include the current translation
$person->toArray();
```