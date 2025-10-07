<?php

namespace App\Console\Commands\Import;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Facades\App;


use Illuminate\Support\Facades\Storage;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use App\Services\ImageUploader\Facades\ImageUploader;

use App\Models\Category;
use App\Models\StoreProduct;

class ProductFromCsv extends Command
{
    protected $signature = 'import:products-from-csv {url?} {output?}';
    protected $description = 'Fetch an image from a URL using proxy server and save it locally';

    const FILE_PATH = 'vivadzen-products.csv';
    // Mode updateOrCreateItem, updateIsActive , updateCategory
    const MODE = 'updateOrCreateItem';

    private $totalRecords = 0;
    private $fieldLetters = [
      'id' => 'A',
      'type' => 'B',
      'sku' => 'C',
      'name' => 'E',
      'published' => 'F',
      'recommended' => 'G',
      'in_catalog' => 'H',
      'excerpt' => 'I',
      'description' => 'J',
      'tax_status' => 'M',
      'tax_class' => 'N',
      'in_stock' => 'O',
      'stock' => 'P',
      'ordering_enabled' => 'R',
      'sale_price' => 'Z',
      'price' => 'AA',
      'category' => 'AB',
      'images' => 'AE',
      'parent_id' => 'AH',
      'Additional sales' => 'AK',
      'position' => 'AN',
      'property_1_name' => 'AQ',
      'property_1_value' => 'AR',
      'meta_title' => 'BK',
      'meta_desc' => 'BL',
    ];


    public function handle()
    {
      $this->loadExcelFile();
    }



    /**
     * Method loadExcelFile
     *
     * @param $source $source [explicite description]
     *
     * @return void
     */
    private function loadExcelFile() {
  
      $relations_pairs = [];
      $sheet = $this->getExcelDataFromFile(self::FILE_PATH);
      $last_row = isset($this->settings['last_row']) && !empty($this->settings['last_row'])? $this->settings['last_row']: null;
      $highestRow = $last_row? $last_row : $sheet->getHighestRow();
      $this->totalRecords =  $highestRow;

      $bar = $this->output->createProgressBar($this->totalRecords);
      $bar->start();

      foreach ($sheet->getRowIterator() as $row) {

        $rowIndex = $row->getRowIndex();

        if($rowIndex > $this->totalRecords) {
          return;
        }

        if($rowIndex < 2) {
          continue;
        }

        // Create product array
        $excel_product = [
          'id' => $this->getIdCell($this->getCellValue($sheet, $rowIndex, 'id')),
          'name' => $this->getCellValue($sheet, $rowIndex, 'name'),
          'content' => $this->getCellValue($sheet, $rowIndex, 'description'),
          'excerpt' => $this->getCellValue($sheet, $rowIndex, 'excerpt'),
          'is_active' => $this->getCellValue($sheet, $rowIndex, 'published'),
          'in_stock' => $this->getInStockCell($this->getCellValue($sheet, $rowIndex, 'stock')),
          'code' => $this->getCellValue($sheet, $rowIndex, 'sku'),
          'price' => $this->getPriceCell($this->getCellValue($sheet, $rowIndex, 'price')),
          'old_price' => $this->getPriceCell($this->getCellValue($sheet, $rowIndex, 'sale_price')),
          'images' => $this->getImagesCell($this->getCellValue($sheet, $rowIndex, 'images')),
          'category' => $this->getCategoryCell($this->getCellValue($sheet, $rowIndex, 'category')),
          'parent_id' => $this->getParentIdCell($this->getCellValue($sheet, $rowIndex, 'parent_id')),
          'type' => $this->getCellValue($sheet, $rowIndex, 'type'),
          'property_1_value' => $this->getCellValue($sheet, $rowIndex, 'property_1_value')
        ];

        // TRY TO FIND EXISTE PRODUCT
        try {
          $response = $this->{self::MODE}($excel_product);

          if(self::MODE === 'updateOrCreateItem') {
            $relations_pairs[$response->id] = $excel_product['parent_id'] ?? null;
          }
        }catch(\Exception $e) {
          throw $e;
        }
        
        $bar->advance();
      }

      $this->updateRelationship($relations_pairs);

      $bar->finish();

    }

    private function updateRelationship($pairs) {
      foreach($pairs as $new_id => $old_parent_id){
        if(!$old_parent_id) continue;

        $parent = StoreProduct::where('old_id', $old_parent_id)->first();
        $child = StoreProduct::find($new_id);

        if ($parent && $child) {
            $child->update(['parent_id' => $parent->id]);
        }
      }

    }

    private function updateCategory($data) {
        $product = StoreProduct::where('old_id', $data['id'])->first();

        if (!$product || empty($data['category']) || !is_array($data['category'])) return;

        // Оставляем только самые глубокие цепочки
        $chains = $data['category'];
        $deepestChains = $this->filterDeepestChains($chains);

        $categoryIds = [];

        foreach ($deepestChains as $chain) {
            $category = Category::createOrUpdateCategoryChain($chain, 'cs');
            if ($category) $categoryIds[] = $category->id;
        }

        // Привязываем только их (detach удалит старые связи, если надо)
        $product->categories()->sync($categoryIds);

        // dd($product, $data, $categoryIds);
    }

    private function filterDeepestChains(array $chains): array
    {
        $deepest = [];

        foreach ($chains as $i => $chain) {
            $isPrefix = false;
            foreach ($chains as $j => $other) {
                if ($i === $j) continue;
                if (count($chain) < count($other) && array_slice($other, 0, count($chain)) === $chain) {
                    $isPrefix = true;
                    break;
                }
            }
            if (!$isPrefix) $deepest[] = $chain;
        }

        return $deepest;
    }


    private function updateIsActive($data) {
      $product = StoreProduct::where('old_id', $data['id'])->first();

      if($product) {
        $is_active = $data['is_active'] ?? 0;
        $product->update(['is_active' => $is_active]);
      }
    }

    private function updateOrCreateItem($data) {

      App::setLocale('cs');

      // Store images if exists
      if(!empty($data['images'])) {
        $result_items = ImageUploader::uploadMany($data['images'], 'products');
        $images_urls = array_map(function($item) {
          return $item['path'];
        }, $result_items);
      }else {
        $images_urls = [];
      }

      $product = new StoreProduct;
      $product->name = $data['name'];
      $product->short_name = $data['property_1_value'];
      $product->content = $data['content'] ?? null;
      $product->excerpt = $data['excerpt'] ?? null;
      $product->parent_id = $data['parent_id'] ?? null;
      $product->old_id = $data['id'] ?? null;
      $product->setImages($images_urls);
      $product->save();

      // Attach supplier
      $product->setProductSupplier($supplier_id = 1, $in_stock = $data['in_stock'], $price = $data['price'], $old_price = $data['old_price'], $code = $data['code']);

      return $product;
    }

    private function extractIdInt($value) {
        if (preg_match('/id:(\d+)/', $value, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }

    private function getCategoryCell($value) {
      if(empty($value)) return null;
      $sets = [];

      $chains = explode(',', $value);
      $chains = array_map(function($item) {
        return trim($item);
      }, $chains);
      
      foreach($chains as $chain) {
        $categories = explode(' > ', $chain);
        $sets[] = $categories;
      }

      return $sets;
    }

    private function getIdCell($value) {
      return !empty($value)? intval($value): null;
    }

    private function getParentIdCell($value) {
      return !empty($value)? $this->extractIdInt($value): null;
    }
    private function getInStockCell($value) {
      return !empty($value)? intval($value): 0;
    }

    private function getPriceCell($value) {
      return !empty($value)? $this->toFloat($value): null;
    }

    private function getImagesCell($value) {
      if(empty($value))
        return null;

      $urls_array = explode(',', $value);

      $urls_array = array_map(function($item) {
        return trim($item);
      }, $urls_array);

      return $urls_array;
    }


    private function getRowValues($sheet, $rowIndex)
    {
        // Получаем итератор по строкам
        $rowIterator = $sheet->getRowIterator($rowIndex, $rowIndex);
        $rowValues = [];

        // Проходим по каждой ячейке в строке
        foreach ($rowIterator as $row) {
            // Получаем ячейки из строки
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Итерируем по всем ячейкам, а не только заполненным

            // Добавляем значения ячеек в массив
            foreach ($cellIterator as $cell) {
                $rowValues[] = $cell->getValue();
            }
        }

        return $rowValues;
    }

    /**
     * Method getExcelDataFromFile
     *
     * @param $file_path $file_path [explicite description]
     *
     * @return void
     */
    private function getExcelDataFromFile($file_path) {
      $path = Storage::disk('excel')->path($file_path);

      $spreadsheet = IOFactory::load($path);
      $sheet = $spreadsheet->getActiveSheet();

      return $sheet;
    }
        

    /**
     * Extracts the hexadecimal color value from a string like "fill:ffffff".
     *
     * @param string $inputString The string to check.
     * @return string|null The hexadecimal color value (without #) if found, otherwise null.
     */
    private function extractFillValue(string $inputString): ?string
    {
        // Check if the string matches the "fill:HEX" pattern (case-insensitive).
        if (preg_match('/^fill:([0-9a-fA-F]{6})$/i', $inputString, $matches)) {
            // Return the captured hexadecimal color value.
            return mb_strtoupper($matches[1]);
        }

        // If the string doesn't match the pattern, return null.
        return null;
    }



    
    /**
     * Method getCellValue
     *
     * @param $sheet $sheet [explicite description]
     * @param $rowIndex $rowIndex [explicite description]
     * @param $name $name [explicite description]
     *
     * @return void
     */
    private function getCellValue($sheet, $rowIndex, $name) {
      $letter = $this->fieldLetters[$name] ?? null;

      if(!$letter) {
        return null;
      }

      $data = $sheet->getCell($letter.$rowIndex)->getValue();
      return empty($data)? null: mb_trim($data);
    }
    
    /**
     * Method getCellAddress
     *
     * @param $rowIndex $rowIndex [explicite description]
     * @param $name $name [explicite description]
     *
     * @return void
     */
    private function getCellAddress($rowIndex, $name) {
      return $this->getFieldLetter($name).$rowIndex;
    }

    /**
     * Method getFirstFilledCell
     *
     * @param $sheet $sheet [explicite description]
     *
     * @return void
     */
    private function getFirstFilledCell($sheet, $rowIndex) {
      $firstFilledCellAddress = null;

      foreach ($sheet->getColumnIterator() as $column) {
        $cellAddress = $column->getColumnIndex() . $rowIndex; // Например, "A2", "B2"
        $cellValue = $sheet->getCell($cellAddress)->getValue();

        if (!empty($cellValue)) {
          $firstFilledCellAddress = $cellAddress;
          break;
        }
      } 

      return $firstFilledCellAddress;
    }
    
        
    /**
     * Method getImageInCell
     *
     * @param $sheet $sheet [explicite description]
     * @param string $cellAddress [explicite description]
     *
     * @return Drawing
     */
    function getImageInCell($sheet, string $cellAddress): ?Drawing {
      foreach ($sheet->getDrawingCollection() as $drawing) {
          if ($drawing instanceof Drawing && $drawing->getCoordinates() === $cellAddress) {
              return $drawing; // Нашли нужное изображение
          }
      }
      return null; // Картинки нет
    }

    /**
     * Method toFloat
     *
     * @param $value $value [explicite description]
     *
     * @return void
     */
    private function toFloat($value) {
      if (is_numeric($value)) {
          return (float) $value; // Если уже число, просто приводим к float
      }
  
      // Удаляем пробелы и неразрывные пробелы
      $value = str_replace([" ", "\xc2\xa0"], "", $value); 
  
      // Если в числе есть запятая и точка, определяем, какой символ — десятичный
      if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
          if (strrpos($value, ',') > strrpos($value, '.')) {
              // Последняя запятая – десятичный разделитель, убираем точки (разделители тысяч)
              $value = str_replace('.', '', $value);
              $value = str_replace(',', '.', $value);
          } else {
              // Последняя точка – десятичный разделитель, убираем запятые (разделители тысяч)
              $value = str_replace(',', '', $value);
          }
      } elseif (strpos($value, ',') !== false) {
          // Если есть только запятая, заменяем её на точку (европейский стиль)
          $value = str_replace(',', '.', $value);
      }
  
      return floatval($value);
    }

}
