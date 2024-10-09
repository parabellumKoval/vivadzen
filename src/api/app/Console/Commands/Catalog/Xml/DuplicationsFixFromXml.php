<?php

namespace App\Console\Commands\Catalog\Xml;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use League\Csv\Reader;

use App\Traits\ProductProcessing;

use App\Models\Product;
use Backpack\Store\app\Models\SupplierProduct;

class DuplicationsFixFromXml extends Command
{
    use ProductProcessing;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xml:duplications_fix_from_xml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'We take the prepared xml file with data about duplicates and combine the products';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
			$xml = $this->getXML();
    }
    
        
    /**
     * getXML
     *
     * @return void
     */
    private function getXML() {
      $content = file_get_contents(url('/uploads/dupl_20_08_24.csv'));

      $reader = Reader::createFromString($content);
      $records = $reader->getRecords();

      foreach($records as $index => $record) {
        if($index === 0) {
          continue;
        }

        $this->info('index ' . $index);
        
        $site_codes = null;
        // site codes
        if(!empty($record[2])){
          $site_codes_array = explode(PHP_EOL, $record[2]);

          $site_codes = array_map(function($item){
            return trim($item);
          }, $site_codes_array);
        }

        $data = [
          'name' => trim($record[1]),
          'site' => $site_codes,
          'prom' => trim($record[3]),
          'protein' => trim($record[4]),
          'dobavki' => trim($record[5]),
          'barcode' => trim($record[6]),
        ];

        $prom_sp = !empty($data['prom'])? SupplierProduct::where('code', $data['prom'])->orWhere('barcode', $data['prom'])->first(): null;
        $protein_sp = !empty($data['protein'])? SupplierProduct::where('code', $data['protein'])->orWhere('barcode', $data['protein'])->first(): null;
        $dobavki_sp = !empty($data['dobavki'])? SupplierProduct::where('code', $data['dobavki'])->orWhere('barcode', $data['dobavki'])->first(): null;
        $barcode_sp = !empty($data['barcode'])? SupplierProduct::where('code', $data['barcode'])->orWhere('barcode', $data['barcode'])->first(): null;

        $all_sps = collect();
        
        if($prom_sp)
          $all_sps->push($prom_sp);
        
        if($protein_sp)
          $all_sps->push($protein_sp);
        
        if($dobavki_sp)
          $all_sps->push($dobavki_sp);
        
        if($barcode_sp)
          $all_sps->push($barcode_sp);
        
        //
        $all_sps_unique = $all_sps->unique('id')->unique('product_id');

        if($all_sps_unique->count() > 1) {
          $this->info('duplications ' . $all_sps_unique->count());
          // function from mergeSupplierProductsTrait
          $this->mergeSupplierProductsTrait($all_sps_unique);
        }
      }
    }
}
