<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

use Backpack\Store\app\Models\Order;

class Payment extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'payments';
    // protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    protected $casts = [
      'extras' => 'array'
    ];

    protected $fakeColumns = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();
    }
    
    /**
     * getExtrasData
     *
     * @return void
     */
    public function getExtrasData() {
      $html = '<table>';
      
      foreach($this->extras as $key => $value) {
        $html .= '<tr>';
        $html .= '<td>' . $key . '</td>';

        if(is_array($value)) {
          $print_array = '<pre>' . print_r($value, true) . '</pre>';
          $html .= '<td>' . $print_array . '</td>';
        }else {
          $html .= '<td>' . $value . '</td>';
        }

        $html .= '</tr>';
      }

      $html .= '</table>';
      
      return $html;

      // return '<pre>' . print_r($this->extras, true) . '</pre>';
    }
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function order()
    {
      return $this->belongsTo(Order::class, 'order_id');
    }
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
        
    /**
     * getOrderLinkAttribute
     *
     * @return void
     */
    public function getOrderLinkAttribute() {
      if(!$this->order) {
        return '–';
      }else {
        $link = url('/admin/order/' . $this->order->id . '/show');
        $code = $this->order->code;
        $date = $this->order->created_at;
        return "<a href='{$link}' target='_blank'>{$code} - {$date}</a>";
      }
    }

    /**
     * getStatusHtmlAttribute
     *
     * @return void
     */
    public function getStatusHtmlAttribute() {

      switch($this->status){
        case 'success':
            $color = 'green';
            break;
        case 'created':
            $color = 'black';
            break;
        case 'processing':
            $color = 'gray';
            break;
        case 'failure':
            $color = 'red';
            break;
        default:
            $color = 'black';
      }

      $translated_status = __('status.pay_status.' . $this->status);

      return "<span style='color: {$color}'>{$translated_status}</span>";

    }
    
    /**
     * getAmountAttribute
     *
     * @param  mixed $value
     * @return void
     */
    public function getAmountAttribute($value) {
      return $value === 0? 0: $value / 100;
    }

    // public function getExtrasAttribute($value) {
    //   return json_encode($value);
    // }

    // public function getExtrasJsonAttribute() {
    //   return json_encode($this->extras);
    // }
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
