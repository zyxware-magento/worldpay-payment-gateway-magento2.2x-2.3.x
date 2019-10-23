<?php
/**
 * Copyright © 2019 Zyxware technologies. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zyxware\KitkatCustom\Cron;

use \Zyxware\Worldpay\Model\SycnStatus as OrderSyncStatus;

class SycnStatus
{
    /**
     * Sync order status.
     *
     * @var OrderSyncStatus
     */
    protected $syncStatus;

    /**
     * @param OrderSyncStatus $syncStatus
     */
    public function __construct(OrderSyncStatus $syncStatus)
    {
        $this->syncStatus = $syncStatus;
    }

    /**
     * cron job.
     *
     * @return void
     */
    public function execute()
    {
        $this->syncStatus->sync();
    }
}
