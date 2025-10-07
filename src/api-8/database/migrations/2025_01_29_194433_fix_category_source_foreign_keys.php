<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixCategorySourceForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::disableForeignKeyConstraints();

      //
      Schema::table('ak_category_source', function(Blueprint $table) {
        if($this->_isForeignKeysExist('ak_category_source', 'category_id')) {
          $this->_dropForeignIfExist('ak_category_source', 'category_id');
        }

        $table->foreign('category_id')
              ->references('id')
              ->on('ak_product_categories')
              ->onDelete('cascade');
      });
     

      Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
  
    /**
     * _dropForeignIfExist
     *
     * @param  mixed $tableName
     * @param  mixed $indexName
     * @return void
     */
    public function _dropForeignIfExist($tableName, $indexName)
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexName) { 
            if($this->_isForeignKeysExist($tableName, $indexName)) {
              $table->dropForeign([$indexName]);
            }
        });
    }
    
    /**
     * _isForeignKeysExist
     *
     * @param  mixed $tableName
     * @param  mixed $column
     * @return bool
     */
    public function _isForeignKeysExist(string $tableName, string $column): bool
    {  
        $fkColumns = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys($tableName);

        return collect($fkColumns)->map(function ($fkColumn) {
            return $fkColumn->getColumns();
        })->flatten()->contains($column);
    }
}
