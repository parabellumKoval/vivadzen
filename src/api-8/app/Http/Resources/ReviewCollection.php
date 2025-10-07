<?php
 
namespace App\Http\Resources;
 
use Illuminate\Http\Resources\Json\ResourceCollection;
use \Backpack\Reviews\app\Models\Review;
 
class ReviewCollection extends ResourceCollection
{
  private $total, $last_page, $current_page, $per_page, $rating_count, $rating_avg, $resource_class;  

  public function __construct($resource, Array $options)
  {
    $this->resource_class = $options['resource'];
    $this->rating_count = $options['reviews_count'];
    $this->rating_avg = $options['reviews_avg'];

    $this->total = $resource->total();
    $this->last_page = $resource->lastPage();
    $this->current_page = $resource->currentPage();
    $this->per_page = $resource->perPage();

    $collection = $resource->getCollection();
    parent::__construct($collection);
  }

  /**
   * Transform the resource collection into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    return [
      'data' => $this->resource_class::collection($this->collection),
      'meta' => [
        'total' => $this->total,
        'current_page' => $this->current_page,
        'per_page' => $this->per_page,
        'last_page' => $this->last_page,
        'rating_count' => $this->rating_count,
        'rating_avg' => $this->rating_avg
      ]
    ];
  }
}