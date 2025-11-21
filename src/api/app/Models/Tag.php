<?php
namespace App\Models;

use Backpack\Tag\app\Models\Tag as BaseTag;

use Dress\Translator\app\Interfaces\TranslatableInterface;
use Dress\Translator\app\Traits\TranslatableTrait;

class Tag extends BaseTag implements TranslatableInterface
{
    use TranslatableTrait;
    
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */



    // TRANSLATOR
    public static function setupTranslatableSettings(): void
    {
        static::addCommonSettings([
            'title' => 'Теги',
            'key' => 'tag',
            'identifier' => 'label',
            'backpack_title' => 'Теги',
            'backpack_settings' => [],
        ]);

        static::addTranslatableCase([
            'fields' => [
                'label' => 'Название'
            ],
            'driver' => 'deepl',
        ]);
    }


    // public function scopeTranslatableQuery(Builder $query, $settings) {
    //   $is_active_only = isset($settings['query']['is_active_only']) 
    //       ? (bool)$settings['query']['is_active_only'] 
    //       : false;

    //   $query->when($is_active_only, function ($query) {
    //       $query->where('is_active', 1);
    //   });
    // }


    public function getBackpackEditLinkAttribute(): string 
    {
        return backpack_url('tag/' . $this->id . '/edit');
    }
}
