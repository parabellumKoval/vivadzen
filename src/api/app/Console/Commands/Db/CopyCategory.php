<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Category;

class CopyCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:copy-categories';

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
        // $this->line('Move Categories');
        // $this->copyCategory();

        // $this->line('Move parent id');
        // $this->_moveParentId();

        // $this->line('Update category parent');
        $this->_updateCategoryParent();

        // $this->line('Move is hit property');
        // $this->moveIsHitProperty();

        return 0;
    }

    private function moveIsHitProperty() {
      $old_categories = \DB::table('categories')->select('categories.*')->orderBy('id')->get();
  
      $bar = $this->output->createProgressBar(count($old_categories));
      $bar->start();

      foreach($old_categories as $old_category) {
        if(!$old_category->is_hit) {
          continue;
        }

        $category = Category::where('old_id', $old_category->id)->first();
        
        if(!$category) {
          $this->line('Cant find category');
          continue;
        }

        $category->extras = [
          'is_hit' => 1
        ];
        $category->save();

        $bar->advance();
      }

      $bar->finish();
    }

    private function _moveParentId() {

      $new_cats = Category::whereNotNull('old_parent_id')->get();
  
      $bar = $this->output->createProgressBar(count($new_cats));
      $bar->start();

      foreach($new_cats as $new_cat) {
        $newParent = Category::where('old_id2', $new_cat->old_parent_id)->first();

        if(!$newParent) {
          continue;
        }

        $new_cat->parent_id = $newParent->id;
        $new_cat->save();

        $bar->advance();
      }

      $bar->finish();
    }

    private function _updateCategoryParent() {
      $category_child = \DB::table('category_child')->select('category_child.*')->get();
  
      $bar = $this->output->createProgressBar(count($category_child));
      $bar->start();

      foreach($category_child as $item) {
        $category = Category::where('old_id2', $item->category_id)->first();
        $childCategory = Category::where('old_id2', $item->child_id)->first();
        
        if(!$category || !$childCategory)
          continue;
  
        if($childCategory->parent_id) {
          $this->error('Parent id already exists ' . $childCategory->id);
          continue;
        }else {
          $this->info('Parent id new ' . $childCategory->id);
        }

        $childCategory->parent_id = $category->id;
        $childCategory->save();

        $bar->advance();
      }

      $bar->finish();
    }
  
    public function createCategories() {
      $old_categories = \DB::table('categories')->select('categories.*')->orderBy('id')->get();
  
      $bar = $this->output->createProgressBar(count($old_categories));
      $bar->start();

      foreach($old_categories as $old_category) {
  
        $category = new Category;
  
        $category->old_id = $old_category->id;
        $category->setTranslation('name', 'ru', $old_category->name);
        $category->slug = $old_category->slug;
        $category->setTranslation('content', 'ru', $old_category->seo_unique_text);
        $category->images = [
          [
            'src' => $old_category->logo,
            'alt' => $old_category->seo_image_alt,
            'title' => ''
          ]
        ];
  
        $category->setTranslation('seo', 'ru', [
          'h1' => $old_category->name_h1,
          'meta_title' => $old_category->seo_title,
          'meta_description' => $old_category->seo_description
        ]);  
        
        $category->extras = [
          'id' => $old_category->id
        ];
  
        $category->old_parent_id = $old_category->parent_id;
        $category->lft = $old_category->_lft;
        $category->rgt = $old_category->_rgt;
  
        $category->save();

        $bar->advance();
      }

      $bar->finish();
    }

}
