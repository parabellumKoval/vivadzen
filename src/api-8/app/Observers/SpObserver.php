<?php

namespace App\Observers;

use Backpack\Store\app\Models\SupplierProduct;

class SpObserver
{
    /**
     * Handle the SupplierProduct "created" event.
     *
     * @param  \App\Models\SupplierProduct  $sp
     * @return void
     */
    public function created(SupplierProduct $sp)
    {
        //
    }

    /**
     * Handle the SupplierProduct "updated" event.
     *
     * @param  \App\Models\SupplierProduct  $sp
     * @return void
     */
    public function updated(SupplierProduct $sp)
    {
    }

    /**
     * Handle the SupplierProduct "deleting" event.
     *
     * @param  \App\Models\SupplierProduct  $sp
     * @return void
     */
    public function deleting(SupplierProduct $sp) {
    }

    /**
     * Handle the SupplierProduct "restored" event.
     *
     * @param  \App\Models\SupplierProduct  $sp
     * @return void
     */
    public function restored(SupplierProduct $sp)
    {
        //
    }

    /**
     * Handle the SupplierProduct "force deleted" event.
     *
     * @param  \App\Models\SupplierProduct  $sp
     * @return void
     */
    public function forceDeleted(SupplierProduct $sp)
    {
        //
    }
}
