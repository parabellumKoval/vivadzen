<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropForeignKeys extends Migration
{


  public function _isForeignKeysExist(string $tableName, string $column): bool
  {  
      $fkColumns = Schema::getConnection()
          ->getDoctrineSchemaManager()
          ->listTableForeignKeys($tableName);

      return collect($fkColumns)->map(function ($fkColumn) {
          return $fkColumn->getColumns();
      })->flatten()->contains($column);
  }

  public function _dropForeignIfExist($tableName, $indexName)
  {
      Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexName) {
          
          if($this->_isForeignKeysExist($tableName, $indexName)) {
            $table->dropForeign([$indexName]);
          }
      });
  }

  public function _dropIndexIfExist($tableName, $fullIndexName, $indexName)
  {
      Schema::table($tableName, function (Blueprint $table) use ($tableName, $fullIndexName, $indexName) {
          $sm = Schema::getConnection()->getDoctrineSchemaManager();
          $indexesFound = $sm->listTableIndexes($tableName);

          if(array_key_exists($fullIndexName, $indexesFound)) {
            $table->dropIndex($fullIndexName);
          }
      });
  }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      $this->_dropForeignIfExist('ak_products', 'parent_id');

      $this->_dropForeignIfExist('ak_product_categories', 'parent_id');

      $this->_dropForeignIfExist('ak_category_product', 'category_id');
      $this->_dropForeignIfExist('ak_category_product', 'product_id');

      $this->_dropForeignIfExist('ak_attributes', 'parent_id');

      $this->_dropForeignIfExist('ak_attribute_category', 'attribute_id');
      $this->_dropForeignIfExist('ak_attribute_category', 'category_id');

      $this->_dropForeignIfExist('ak_attribute_product', 'attribute_id');
      $this->_dropForeignIfExist('ak_attribute_product', 'product_id');
      $this->_dropForeignIfExist('ak_attribute_product', 'attribute_value_id');

      $this->_dropForeignIfExist('ak_attribute_values', 'attribute_id');
      $this->_dropForeignIfExist('ak_attribute_values', 'parent_id');

      $this->_dropForeignIfExist('ak_order_product', 'product_id');
      $this->_dropForeignIfExist('ak_order_product', 'order_id');

      $this->_dropForeignIfExist('ak_carts', 'product_id');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {}
}
