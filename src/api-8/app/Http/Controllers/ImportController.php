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

use Rap2hpoutre\FastExcel\FastExcel;

use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;

set_time_limit(-1);
ini_set('memory_limit', '500M');
ini_set('max_execution_time', -1);

class ImportController extends Controller
{

  public $find = 0;
  public $not_find = 0;
  public $total = 0;

  // public function import() {
  //   $file = public_path('/uploads/prom.xlsx');
  //   $array = Excel::import(new ProductsImport, $file);

  //   foreach($array as $v) {
  //     dd($v);
  //   }
  // }



  public function productsGenerator() {
    // dd($this);
    foreach (Product::cursor() as $user) {
        yield $user;
    }
    // for ($i = 1; $i <= 3; $i++) {
    //   yield $i;
    // }
  }

  public function import() {
    $file = public_path('/uploads/prom2.xlsx');
    // dd($file);
    

    $collection = (new FastExcel)->import($file, function ($line) {
      //  dd($line);
      // dd(mb_strtolower($line['Назва_позиції']));
      //  $attrs = [
      //   'weight' => $line['Вага,кг'],
      //   'weight' => $line['Ширина,см'],
      //   'weight' => $line['Висота,см'],
      //   'weight' => $line['Довжина,см'],
      //   'weight' => $line['Вага,кг'],
      //   'weight' => $line['Вага,кг'],
      //   'weight' => $line['Вага,кг'],
      //   'weight' => $line['Вага,кг'],
      //  ];
      
      $product = Product::where('import_id', $line['Унікальний_ідентифікатор'])->first();

      $this->total += 1;

      if($product) {
        $this->find += 1;
      }else {
        $this->not_find += 1;
      }

      // $products = Product::where(\DB::raw('lower(name)'), 'like', '%' . mb_strtolower($line['Назва_позиції']) . '%')->get();
      // $product = Product::where('name', '%LIKE%', $line['Назва_позиції'])->first();
      // $products = Product::search($line['Назва_позиції'])->get();
      // dd($products->all());

      // if($products->count() !== 1) {
      //   if(!$products->count()) {
      //     dd(mb_strtolower($line['Назва_позиції']));
      //     echo "\n";
      //   }else {
      //     dd($products->pluck('id', 'name'));
      //     echo "\n";
      //   }
      // }

       return [
        'code' => $line['Код_товару'],
        'name' => $line['Назва_позиції'],
        // 'attrs' => $attrs
       ];
    });

    foreach ($collection as $value) {
        // print_r ($value);
        // dd($value);
        // echo "$value\n";
    }

    dd($this->total, $this->find, $this->not_find);
    // dd($collection);
  }
}

