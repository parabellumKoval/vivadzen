<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Backpack\Store\app\Models\Product as BaseProduct;

use Backpack\Reviews\app\Traits\Reviewable;
use Backpack\Tag\app\Traits\Taggable;

class Product extends BaseProduct
{
    use Reviewable;
    use Taggable;

    // protected $morphClass = 'Backpack\Store\app\Models\Product';

    // public function getMorphClass()
    // {
    //     return 'Backpack\Store\app\Models\Product';
    // }
}
