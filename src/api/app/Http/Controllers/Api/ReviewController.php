<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Backpack\Store\app\Models\Category;
use App\Http\Resources\ReviewCollection;

use \Illuminate\Database\Eloquent\ModelNotFoundException;


class ReviewController extends \Backpack\Reviews\app\Http\Controllers\Api\ReviewController
{

  public function index(Request $request) {
    
    $reviewable_id = request('reviewable_id');
    $reviewable_type = request('reviewable_type', 'not_exist');
    $is_moderated = request('is_moderated', 1);

    if(request('reviewable_slug') && request('reviewable_type')) {
      $reviewable = request('reviewable_type')::where('slug', request('reviewable_slug'))->first();
      $reviewable_id = $reviewable? $reviewable->id: null;
    }

    $node_ids = Category::getCategoryNodeIdList(request('category_slug'), request('category_id'));

    $reviews = $this->review_model::query()
      ->root()
      ->when($node_ids, function($query) use($node_ids){
        $query->leftJoin('ak_category_product as cp', 'cp.product_id', '=', 'ak_reviews.reviewable_id');
        $query->whereIn('cp.category_id', $node_ids);
      })
      ->when($reviewable_id, function($query) use($reviewable_id){
        $query->where('ak_reviews.reviewable_id', $reviewable_id);
      })
      ->when($reviewable_type !== 'not_exist', function($query) use ($reviewable_type){
        if($reviewable_type === 'null') {
          $query->whereNull('ak_reviews.reviewable_type');
        }else {
          $query->where('ak_reviews.reviewable_type', $reviewable_type);
        }
      })
      ->where('ak_reviews.is_moderated', $is_moderated)
      ->orderBy('created_at', 'desc');

    // Additional info for meta
    $aggregate = $reviews->selectRaw('COUNT(ak_reviews.rating) as reviews_count, AVG(ak_reviews.rating) as reviews_avg')
      ->get();
    
    $aggregate_data = $aggregate->all()[0];

    // 
    $reviews = $reviews
      ->select('ak_reviews.*')
      ->distinct('ak_reviews.id');

    // if "count" return only total items amount 
    if(request('count', 0)) {
      return [
        "meta" => [
          "total" => $reviews->count()
        ],
        "reviews" => null
      ];
    }

    $per_page = request('per_page')? request('per_page'): config('backpack.reviews.per_page', 12);
    $reviews = $reviews->paginate($per_page);

    $resource = config('backpack.reviews.resource.medium');

    if(request('resource')) {
      if(request('resource') === 'large') {
        $resource = config('backpack.reviews.resource.large');
      }
    } 

    // return $resource::collection($reviews);
    return new ReviewCollection($reviews, [
      'resource' => $resource,
      'reviews_count' => $aggregate_data->reviews_count,
      'reviews_avg' => $aggregate_data->reviews_avg,
    ]);
  }
}