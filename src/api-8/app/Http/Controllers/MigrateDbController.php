<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Modification;
use App\Models\Category;
use App\Models\Review;
use App\Models\Banner;

use Backpack\Articles\app\Models\Article as NewArticle;

use Backpack\Store\app\Models\Product as NewProduct;
use Backpack\Store\app\Models\Attribute as NewAttribute;
use Backpack\Store\app\Models\Category as NewCategory;
use Backpack\Store\app\Models\Brand as NewBrand;

use Backpack\Reviews\app\Models\Review as NewReview;
use Backpack\Banners\app\Models\Banner as NewBanner;

set_time_limit(-1);
ini_set('memory_limit', '500M');
ini_set('max_execution_time', -1);

class MigrateDbController extends Controller
{


  public function all() {
    // $this->createBrands();

    // $this->createArticles();
    // $this->articlesToMultilangs();

    $this->createCategories();
    // $this->createProducts();

    // $this->createAttributes();
    // $this->createBanners();
  }

  public function articlesToMultilangs() {
    $old_articles = \DB::table('blog_posts')->select('blog_posts.*')->get();
    
    foreach($old_articles as $article) {
      $lang = 'ru';

      $n_article = new NewArticle;

      $n_article->setTranslation('title', $lang, $article->title);
      $n_article->slug = $article->slug;
      $n_article->setTranslation('content', $lang, $article->description);
      $n_article->setTranslation('excerpt', $lang, $article->introtext);
      $n_article->image = $article->image_large;
      $n_article->date = $article->publicated_at;
      $n_article->status = 'PUBLISHED';
      $n_article->extras = json_encode(['time' => $article->duration]);
      $n_article->setTranslation('seo', $lang, [
        'meta_title' => $article->seo_title,
        'meta_description' => $article->seo_description
      ]);

      $n_article->save();
    }
  }

  public function createProductReviews($reviews, $product) {

    foreach($reviews as $review) {

      $extras = [
        "owner" =>  [
            "email" => $review->email,
            "photo" => $review->file,
            "fullname" => $review->name
        ],
        "method" => "common"
      ];


      $review = NewReview::create([
        'is_moderated' => $review->is_moderated,
        'text' => $review->text,
        'extras' => $extras,
        'rating' => rand(3,5),
        'likes' => 	$review->likes,
        'dislikes' => $review->dislikes,
        'reviewable_id' => $product->id,
        'reviewable_type' => 'Backpack\Store\app\Models\Product'
      ]);
    }

  }

  public function createProducts() {

    $old_products_collection = \DB::table('products')
      ->select('products.*')
      ->where('deleted_at', null)
      ->paginate(5000);

    // 6 pages with 5000 per page

    $old_products = $old_products_collection->all();

    if(!$old_products) {
      print 'no data';
      return;
    }

    foreach($old_products as $old_product) {

        $product = new NewProduct;

        $product->old_id = $old_product->id;
        $product->code = $old_product->vendor_code? substr($old_product->vendor_code, 0, 30): null;
        $product->setTranslation('name', 'ru', $old_product->title);
        $product->slug = $old_product->slug;
        $product->setTranslation('content', 'ru', $old_product->description);
        $product->images = [
          [
            'src' => $old_product->image_large,
            'alt' => null,
            'title' => null
          ]
        ];
        $product->in_stock = $old_product->amount? $old_product->amount: 0;

        if($old_product->price_discount) {
          $product->old_price = $old_product->price;
          $product->price = $old_product->price_discount;
        }else {
          $product->price = $old_product->price;
        }

        if($old_product->seo_title || $old_product->seo_description) {
          $product->setTranslation('seo', 'ru', [
            'meta_title' => $old_product->seo_title,
            'meta_description' => $old_product->seo_description
          ]);
        }

        if($old_product->parsed_from) {
          $product->extras = [
            'parsed_from' => $old_product->parsed_from
          ];
        }

        $product->save();
        
        // Create product reviews
        // if(!$parent_id)
        //   $this->createProductReviews($base_product->reviews, $product);
        
        // Attach categories
        // if(!$parent_id)
        //   $product->categories()->attach($base_product->category_id);
        
        // Attach Attributes
        // $this->attachAttrs($product, $modification, $base_product);

        // if(!$parent_id){
        //   $parent_id = $product->id;
        // }
      

    }

    //dd($products);
  }

  private function attachAttrs($product, $modification, $base_product) {
    // WEIGHT
    $weight = (int)filter_var($modification->name, FILTER_SANITIZE_NUMBER_INT);
    $product->attrs()->attach(1, ['value' => $weight]);

    // SLIMULATION
    if($base_product->stimulation)
      $product->attrs()->attach(5, ['value' => $base_product->stimulation]);

    // RELAXATION
    if($base_product->relaxation)
      $product->attrs()->attach(6, ['value' => $base_product->relaxation]);

    // EPHORIA
    if($base_product->euphoria)
      $product->attrs()->attach(7, ['value' => $base_product->euphoria]);
  }


  public function createBrands() {
    $old_brands = \DB::table('brands')->select('brands.*')->get();

    foreach($old_brands as $old_brand) {
      $brand = new NewBrand();

      $brand->id = $old_brand->id;
      $brand->setTranslation('name', 'ru', $old_brand->name);
      $brand->slug = $old_brand->slug;
      $brand->setTranslation('content', 'ru', $old_brand->seo_text);
      $brand->images = [
        [
          'src' => $old_brand->image,
          'alt' => null,
          'title' => null,
        ]
      ];
      $brand->setTranslation('seo', 'ru', [
        'h1' => $old_brand->h1,
        'meta_title' => $old_brand->seo_title,
        'meta_description' => $old_brand->seo_description
      ]);
      $brand->setTranslation('content', 'ru', $old_brand->seo_text);
      $brand->extras = [
        'is_popular' => $old_brand->is_popular
      ];

      $brand->save();
    }
  }


}

