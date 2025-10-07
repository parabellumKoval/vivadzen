<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\AttributeProduct;
use Backpack\Store\app\Models\Brand;

class FixDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transform:fix-db {method?}';

    protected $brands_to_remove = [
      // Добавки
      "A'pieu",
      "Advanced Orthomolecular Research AOR",
      "Amix Nutrition",
      "Arthur Andrew Medical",
      "Artnaturals",
      "Aura Cacia",
      "Bach",
      "Beauty of Joseon",
      "Benton",
      "Blistex",
      "Cantu",
      "CELIMAX",
      "Cos De BAHA",
      "Crystal Star",
      "D'adamo",
      "Daeng Gi Meo Ri",
      "De La Cruz",
      "Deva",
      "DY Nutrition",
      "Dymatize Nutrition",
      "Earth Therapeutics",
      "Earth's Creation",
      "Eucerin",
      "EuroPharma, Terry Naturally",
      "Fifty 50",
      "Flora",
      "Forces of Nature",
      "Gaia Herbs",
      "Gaia Herbs Professional Solutions",
      "GAT",
      "GNC",
      "Godefroy",
      "Grab Green",
      "Harney & Sons",
      "Hearos",
      "Herb Pharm",
      "Herbs Etc.",
      "Heritage Store",
      "Hobe Labs",
      "InstaNatural",
      "Irwin Naturals",
      "Johnson & Johnson",
      "Kiss My Face",
      "Klean Athlete",
      "Kosette",
      "Lunette",
      "MacroLife Naturals",
      "Manyo",
      "Maxim Hygiene Products",
      "Mielle",
      "Mild By Nature",
      "Miyo",
      "MRM Nutrition",
      "Muscle Pharm",
      "Naturally Fresh",
      "NaturVet",
      "Neosporin",
      "Neutrogena",
      "NOSOROG",
      "Odor Eaters",
      "Ojio",
      "PharmaCare",
      "Planetary Herbals",
      "Priorin",
      "Quantum Health",
      "Quiz",
      "Rael",
      "Real Barrier",
      "Reserveage Nutrition",
      "Seagate",
      "SheaMoisture",
      "Similasan",
      "Six Star",
      "Some By Mi",
      "St. Dalfour",
      "Stridex",
      "Sunny Green",
      "Super Nutrition",
      "Superior",
      "Tesla",
      "The Seaweed Bath Co.",
      "The Spice Lab",
      "Traditional Medicinals",
      "Tresemme",
      "TwinLab",
      "Vaseline",
      "Visine",
      "Wet n Wild",
      "Zesty Paws",
      // Protein+
      "4Life",
      "4yourhealth",
      "7 Nutrition",
      "7Sports",
      "Набори",
      "Activlab",
      "Applied Nutrition",
      "Arm & Hammer",
      "Bad Ass",
      "BLASTEX",
      "Bluebonnet Nutrition",
      "Bounty",
      "BPI sports",
      "California Gold Nutrition",
      "Casno",
      "Cellucor",
      "Child Life",
      "Cobra Labs",
      "Country Life",
      "DNA Supps (OLIMP)",
      "Doctor's BEST",
      "Dr. Mercola",
      "Dymatize",
      "ELITE Labs",
      "Energy Body",
      "Enzymedica",
      "Euroshaker",
      "EVLUTION Nutrition",
      "FitLife",
      "FitMax",
      "Fitness Authority",
      "Force Factor",
      "Fortogen Nutrition",
      "Futurebiotics",
      "Garden Of Life",
      "Gaspari Nutrition",
      "Genius Nutrition",
      "GNC",
      "Grassberg",
      "Healthy Origins",
      "Hydra Cup",
      "IronMaxx",
      "Kevin Levrone",
      "Labrada Nutrition",
      "Mars",
      "Maxler",
      "Maxx",
      "Megabol",
      "MEX Nutrition",
      "Mommy's Bliss",
      "Monster Energy",
      "Multipower",
      "Muscle Pharm",
      "Muscle Pharm Arnold Series",
      "MuscleTech",
      "Natrol",
      "Natural Factors",
      "Nature's Life",
      "NeoCell",
      "NutraBolics",
      "NutraMedix",
      "NutriBiotic",
      "Nyam",
      "Omne Diem",
      "PharmaCare",
      "PipingRock",
      "Pro Supps",
      "ProMera Sports",
      "Quest Nutrition",
      "Rabeko",
      "Rainbow Light",
      "Real Pharm",
      "Redcon1",
      "SAN",
      "Sante",
      "SFD",
      "SIS",
      "Slim",
      "SNICKERS",
      "Spider Bottle",
      "SportFaza",
      "Strong FIT",
      "Super Nutrition",
      "Swanson",
      "Syntrax",
      "Tesla Sports Nutrition",
      "Twinlab",
      "UNS",
      "Wana",
      "Warrior",
      "Weider",
      "Yamamoto nutrition",
      "YumEarth",
      "Zoomad Labs"
  ];  

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
      $method = $this->argument('method');

      if($method) {
        $this->{$method}();
      }else {
        // $this->fixMelatonin();

        // $this->removeProductByBrands();

        // $this->disableProductNoImages();
      }

      return 0;
    }
        
    /**
     * clearProductCode
     *
     * @return void
     */
    private function clearProductCode() {
      Product::query()->update([
        'code' => null
      ]);
    }

    /**
     * fixMelatonin
     *
     * @param  mixed $products
     * @return void
     */
    private function fixMelatonin() {
      Product::where('slug', 'melatonin')->update([
        'slug' => 'melatonin-454'
      ]);
    }
        
    /**
     * disableProductNoImages
     *
     * @return void
     */
    private function disableProductNoImages() {
      
      $disabled = 0;
      $products = Product::where('is_active', 1)->cursor();

      foreach($products as $product) {
        if((!$product->image || !$product->image['src']) && Str::startsWith($product->content, 'Категория:')) {
          $product->is_active = 0;
          $product->save();

          $disabled++;
          $this->info($product->id . "\n");
        }else {}
      }

      $this->info('total = ' . $disabled);

      // $this->info('product amount ' . $products->count());
    }

    /**
     * removeProductByBrands
     *
     * @return void
     */
    private function removeProductByBrands() {
      foreach($this->brands_to_remove as $br) {
        $brand = Brand::where('name->ru', $br)->first();

        if(!$brand) {
          $this->error($br);
        }else {
          $this->info($br);
          $products = Product::where('brand_id', $brand->id)->get();

          if(!$products) {
            $this->warning('No products in brands');
          }else {
            $this->info('product amount ' . $products->count());
            
            foreach($products as $product) {
              AttributeProduct::where('product_id', $product->id)->delete();
              $product->delete();
            }
          }
        }

      }
    }

}
