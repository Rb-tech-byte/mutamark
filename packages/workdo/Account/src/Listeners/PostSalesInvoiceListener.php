<?php

namespace Workdo\Account\Listeners;

use App\Events\PostSalesInvoice;
use Workdo\Account\Services\JournalService;

class PostSalesInvoiceListener
{
    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function handle(PostSalesInvoice $event)
    {
       if(Module_is_active('Account'))
       {
           if ($event->salesInvoice->type === 'product') {
               $entry = $this->journalService->createSalesInvoiceJournal($event->salesInvoice);
               if (!$entry instanceof \Workdo\Account\Models\JournalEntry) throw new \RuntimeException('Sales journal posting failed.');
               $this->journalService->createSalesCOGSJournal($event->salesInvoice);
           } else {
               $entry = $this->journalService->createServiceInvoiceJournal($event->salesInvoice);
               if (!$entry instanceof \Workdo\Account\Models\JournalEntry) throw new \RuntimeException('Service journal posting failed.');
           }
       }
    }
}
