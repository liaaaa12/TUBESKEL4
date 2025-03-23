<?php

namespace App\Observers;

use App\Models\VendorBarang;

class VendorBarangObserver
{
    /**
     * Handle the VendorBarang "created" event.
     */
    public function created(VendorBarang $vendorBarang): void
    {
        //
    }

    /**
     * Handle the VendorBarang "updated" event.
     */
    public function updated(VendorBarang $vendorBarang): void
    {
        //
    }

    /**
     * Handle the VendorBarang "deleted" event.
     */
    public function deleted(VendorBarang $vendorBarang): void
    {
        //
    }

    /**
     * Handle the VendorBarang "restored" event.
     */
    public function restored(VendorBarang $vendorBarang): void
    {
        //
    }

    /**
     * Handle the VendorBarang "force deleted" event.
     */
    public function forceDeleted(VendorBarang $vendorBarang): void
    {
        //
    }
}
