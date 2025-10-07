<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Backpack\Profile\database\seeders\ProfileSeeder;
// use Backpack\Store\database\seeders\StoreSeeder;
// use Backpack\Reviews\database\seeders\ReviewSeeder;

use Backpack\Store\app\Models\Supplier;
use Backpack\Store\app\Models\SupplierProduct;
use Backpack\Store\app\Models\Source;
use Backpack\Store\app\Models\BrandSource;
use Backpack\Store\app\Models\CategorySource;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      Supplier::where('id', '>=', 0)->delete();
      Source::where('id', '>=', 0)->delete();

      $this->fillDefaultSuppliers();
      $this->fillDefaultSources();
    }
    
    /**
     * fillDefaultSuppliers
     *
     * @return void
     */
    private function fillDefaultSuppliers() {
      Supplier::create([
          'name' => 'Dobavki',
          'type' => 'dropshipping'
      ]);

      Supplier::create([
          'name' => 'Belok',
          'type' => 'dropshipping'
      ]);

      Supplier::create([
          'name' => 'Proteinplus',
          'type' => 'dropshipping'
      ]);
      
      Supplier::create([
          'name' => 'Склад',
          'type' => 'warehouse'
      ]);
    }
    
    /**
     * fillDefaultSources
     *
     * @return void
     */
    private function fillDefaultSources() {
      Source::create([
          'name' => 'Dobavki',
          'key' => 'dobavki.ua',
          'supplier_id' => Supplier::where('name', 'Dobavki')->first()->id,
          'link' => 'https://crm.dobavki.ua/media/export/product/dobavki_opt_price.xml',
          'every_minutes' => 60,
          'settings' => [
            "item"=> "offers->offer",
            "language"=> "uk",
            "fieldCode"=> "articul",
            "fieldName"=> "name",
            "fieldBrand"=> "brand",
            "fieldPrice"=> "price",
            "fieldBarcode"=> "barcode",
            "fieldInStock"=> "available",
            "inStockRules"=> json_encode([
              ["key" => true, "value" => 1000],
              ["key" => false, "value" => 0]
            ]),
            "fieldCategory"=> "category",
            "createNewBrand"=> "1"
          ],
          'rules' => [
            [
              "type"=> "blacklist",
              "codes"=> null,
              "names"=> null,
              "brands"=> json_encode([
                ["name" => "All Be Ukraine"],
                ["name" => "Beaver Professional"],
                ["name" => "A'pieu"],
                ["name" => "Advanced Orthomolecular Research AOR"],
                ["name" => "Amix Nutrition"],
                ["name" => "Arthur Andrew Medical"],
                ["name" => "Artnaturals"],
                ["name" => "Aura Cacia"],
                ["name" => "Bach"],
                ["name" => "Beauty of Joseon"],
                ["name" => "Benton"],
                ["name" => "Blistex"],
                ["name" => "Cantu"],
                ["name" => "CELIMAX"],
                ["name" => "Cos De BAHA"],
                ["name" => "Crystal Star"],
                ["name" => "D'adamo"],
                ["name" => "Daeng Gi Meo Ri"],
                ["name" => "De La Cruz"],
                ["name" => "Deva"],
                ["name" => "DY Nutrition"],
                ["name" => "Dymatize Nutrition"],
                ["name" => "Earth Therapeutics"],
                ["name" => "Earth's Creation"],
                ["name" => "Eucerin"],
                ["name" => "EuroPharma, Terry Naturally"],
                ["name" => "Fifty 50"],
                ["name" => "Flora"],
                ["name" => "Forces of Nature"],
                ["name" => "Gaia Herbs"],
                ["name" => "Gaia Herbs Professional Solutions"],
                ["name" => "GAT"],
                ["name" => "GNC"],
                ["name" => "Godefroy"],
                ["name" => "Grab Green"],["name" => "Harney & Sons"],
                ["name" => "Hearos"],
                ["name" => "Herb Pharm"],
                ["name" => "Herbs Etc."],
                ["name" => "Heritage Store"],
                ["name" => "Hobe Labs"],
                ["name" => "InstaNatural"],
                ["name" => "Irwin Naturals"],
                ["name" => "Johnson & Johnson"],
                ["name" => "Kiss My Face"],
                ["name" => "Klean Athlete"],
                ["name" => "Kosette"],
                ["name" => "Lunette"],
                ["name" => "MacroLife Naturals"],
                ["name" => "Manyo"],
                ["name" => "Maxim Hygiene Products"],
                ["name" => "Mielle"],
                ["name" => "Mild By Nature"],
                ["name" => "Miyo"],
                ["name" => "MRM Nutrition"],
                ["name" => "Muscle Pharm"],
                ["name" => "Naturally Fresh"],
                ["name" => "NaturVet"],
                ["name" => "Neosporin"],
                ["name" => "Neutrogena"],
                ["name" => "NOSOROG"],
                ["name" => "Odor Eaters"],
                ["name" => "Ojio"],
                ["name" => "PharmaCare"],
                ["name" => "Planetary Herbals"],
                ["name" => "Priorin"],
                ["name" => "Quantum Health"],
                ["name" => "Quiz"],
                ["name" => "Rael"],
                ["name" => "Real Barrier"],
                ["name" => "Reserveage Nutrition"],
                ["name" => "Seagate"],
                ["name" => "SheaMoisture"],
                ["name" => "Similasan"],
                ["name" => "Six Star"],
                ["name" => "Some By Mi"],
                ["name" => "St. Dalfour"],
                ["name" => "Stridex"],
                ["name" => "Sunny Green"],
                ["name" => "Super Nutrition"],
                ["name" => "Superior"],
                ["name" => "Tesla"],
                ["name" => "The Seaweed Bath Co."],
                ["name" => "The Spice Lab"],
                ["name" => "Traditional Medicinals"],
                ["name" => "Tresemme"],
                ["name" => "TwinLab"],
                ["name" => "Vaseline"],
                ["name" => "Visine"],
                ["name" => "Wet n Wild"],
                ["name" => "Zesty Paws"]
              ]),
              "target"=> "brand",
              "max_price"=> null,
              "min_price"=> null,
              "overprice"=> null,
              "exchange_coff"=> ""
            ],[
              "type"=> "overprice",
              "codes"=> null,
              "names"=> null,
              "brands"=> json_encode([["name" => "Orthomol"]]),
              "target"=> "brand",
              "max_price"=> null,
              "min_price"=> null,
              "overprice"=> "1.5",
              "exchange_coff"=> ""
            ],[
              "type"=> "overprice",
              "codes"=> null,
              "names"=> null,
              "brands"=> null,
              "target"=> "all",
              "max_price"=> null,
              "min_price"=> null,
              "overprice"=> "1.4",
              "exchange_coff"=> ""
            ],[
              "type"=> "exchange",
              "codes"=> null,
              "names"=> null,
              "brands"=> null,
              "target"=> "all",
              "max_price"=> null,
              "min_price"=> null,
              "overprice"=> null,
              "exchange_coff"=> "1.0157"
            ]
          ]
        ]);

        Source::create([
          'name' => 'Belok',
          'key' => 'belok.ua',
          'supplier_id' => Supplier::where('name', 'Belok')->first()->id,
          'link' => 'https://belok.ua/prices/StlPrice.xml',
          'every_minutes' => 60,
          'settings' => [
            "item"=> "Catalog->items->item",
            "language"=> "uk",
            "fieldCode"=> "art",
            "fieldName"=> "title",
            "fieldBrand"=> "brand",
            "fieldPrice"=> "rrc",
            "fieldBarcode"=> null,
            "fieldInStock"=> "qty",
            "inStockRules" => json_encode([
              ["key" => 'Y', "value" => 1000],
              ["key" => 'N', "value" => 0]
            ]),
            "fieldCategory"=> null,
            "createNewBrand"=> "1"
          ],
          'rules' => [
            [
                "type"=> "whitelist",
                "codes"=> null,
                "names"=> null,
                "brands"=> json_encode([
                  ["name" => "AMIX"],
                  ["name" => "BlenderBottle"],
                  ["name" => "BPI"],
                  ["name" => "Fitness authority"],
                  ["name" => "Haya Labs"],
                  ["name" => "IronMaxx"],
                  ["name" => "Olimp Nutrition"],
                  ["name" => "Optimum Nutrition"],
                  ["name" => "Quamtrax"],
                  ["name" => "Redcon1"],
                  ["name" => "Sporter"]
                ]),
                "target"=> "brand",
                "in_stock"=> "",
                "max_price"=> null,
                "min_price"=> null,
                "overprice"=> null,
                "categories"=> "",
                "exchange_coff"=> ""
            ]
          ]
        ]);

        Source::create([
          'name' => 'Proteinplus.pro',
          'key' => 'proteinplus.pro',
          'supplier_id' => Supplier::where('name', 'Proteinplus')->first()->id,
          'every_minutes' => 60,
          'link' => 'https://proteinplus.pro/api/xml/f1dd6470-beaf-11e3-a65a-1cc1dec680ca.xml',
          'settings' => [
            "item"=> "items->item",
            "language"=> "uk",
            "fieldCode"=> null,
            "fieldName"=> "name",
            "fieldBrand"=> "vendor",
            "fieldPrice"=> "price",
            "fieldBarcode"=> "vendorCode",
            "fieldInStock"=> "available",
            "inStockRules"=> json_encode([
              ["key" => true, "value" => 1000],
              ["key" => false, "value" => 0]
            ]),
            "fieldCategory"=> "categoryId",
            "createNewBrand"=> "1"
          ],
          'rules' => [
            [
                "type"=> "exchange",
                "codes"=> null,
                "names"=> null,
                "brands"=> null,
                "target"=> "all",
                "max_price"=> null,
                "min_price"=> null,
                "overprice"=> null,
                "exchange_coff"=> "1.0157"
            ],[
                "type"=> "overprice",
                "codes"=> null,
                "names"=> null,
                "brands"=> null,
                "target"=> "all",
                "max_price"=> null,
                "min_price"=> null,
                "overprice"=> "1.33",
                "exchange_coff"=> ""
            ],[
                "type"=> "blacklist",
                "codes"=> null,
                "names"=> null,
                "brands"=> json_encode([
                  ["name" => "4Life"],
                  ["name" => "4yourhealth"],
                  ["name" => "7 Nutrition"],
                  ["name" => "7Sports"],
                  ["name" => "Набори"],
                  ["name" => "Activlab"],
                  ["name" => "Applied Nutrition"],
                  ["name" => "Arm & Hammer"],
                  ["name" => "Bad Ass"],
                  ["name" => "BLASTEX"],
                  ["name" => "Bluebonnet Nutrition"],
                  ["name" => "Bounty"],
                  ["name" => "BPI sports"],
                  ["name" => "California Gold Nutrition"],
                  ["name" => "Casno"],
                  ["name" => "Cellucor"],
                  ["name" => "Child Life"],
                  ["name" => "Cobra Labs"],
                  ["name" => "Country Life"],
                  ["name" => "DNA Supps (OLIMP)"],
                  ["name" => "Doctor's BEST"],
                  ["name" => "Dr. Mercola"],
                  ["name" => "Dymatize"],
                  ["name" => "ELITE Labs"],
                  ["name" => "Energy Body"],
                  ["name" => "Enzymedica"],
                  ["name" => "Euroshaker"],
                  ["name" => "EVLUTION Nutrition"],
                  ["name" => "FitLife"],
                  ["name" => "FitMax"],
                  ["name" => "Fitness Authority"],
                  ["name" => "Force Factor"],
                  ["name" => "Fortogen Nutrition"],
                  ["name" => "Futurebiotics"],
                  ["name" => "Garden Of Life"],
                  ["name" => "Gaspari Nutrition"],
                  ["name" => "Genius Nutrition"],
                  ["name" => "GNC"],
                  ["name" => "Grassberg"],
                  ["name" => "Healthy Origins"],
                  ["name" => "Hydra Cup"],
                  ["name" => "IronMaxx"],
                  ["name" => "Kevin Levrone"],
                  ["name" => "Labrada Nutrition"],
                  ["name" => "Mars"],
                  ["name" => "Maxler"],
                  ["name" => "Maxx"],
                  ["name" => "Megabol"],
                  ["name" => "MEX Nutrition"],
                  ["name" => "Mommy's Bliss"],
                  ["name" => "Monster Energy"],
                  ["name" => "Multipower"],
                  ["name" => "Muscle Pharm"],
                  ["name" => "Muscle Pharm Arnold Series"],
                  ["name" => "MuscleTech"],
                  ["name" => "Natrol"],
                  ["name" => "Natural Factors"],
                  ["name" => "Nature's Life"],
                  ["name" => "NeoCell"],
                  ["name" => "NutraBolics"],
                  ["name" => "NutraMedix"],
                  ["name" => "NutriBiotic"],
                  ["name" => "Nyam"],
                  ["name" => "Omne Diem"],
                  ["name" => "PharmaCare"],
                  ["name" => "PipingRock"],
                  ["name" => "Pro Supps"],
                  ["name" => "ProMera Sports"],
                  ["name" => "Quest Nutrition"],
                  ["name" => "Rabeko"],
                  ["name" => "Rainbow Light"],
                  ["name" => "Real Pharm"],
                  ["name" => "Redcon1"],
                  ["name" => "SAN"],
                  ["name" => "Sante"],
                  ["name" => "SFD"],
                  ["name" => "SIS"],
                  ["name" => "Slim"],
                  ["name" => "SNICKERS"],
                  ["name" => "Spider Bottle"],
                  ["name" => "SportFaza"],
                  ["name" => "Strong FIT"],
                  ["name" => "Super Nutrition"],
                  ["name" => "Swanson"],
                  ["name" => "Syntrax"],
                  ["name" => "Tesla Sports Nutrition"],
                  ["name" => "Twinlab"],
                  ["name" => "UNS"],
                  ["name" => "Wana"],
                  ["name" => "Warrior"],
                  ["name" => "Weider"],
                  ["name" => "Yamamoto nutrition"],
                  ["name" => "YumEarth"],
                  ["name" => "Zoomad Labs"]
                ]),
                "target"=> "brand",
                "max_price"=> null,
                "min_price"=> null,
                "overprice"=> null,
                "exchange_coff"=> ""
            ]
          ]
        ]);
    }

}
