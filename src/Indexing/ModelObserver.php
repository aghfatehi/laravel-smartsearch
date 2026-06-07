<?php

namespace SmartSearch\Indexing;

use Illuminate\Database\Eloquent\Model;
use SmartSearch\Indexing\IndexJobs\DeleteDocument;
use SmartSearch\Indexing\IndexJobs\IndexDocument;

class ModelObserver
{
    public function created(Model $model): void
    {
        $this->dispatch($model);
    }

    public function updated(Model $model): void
    {
        $this->dispatch($model);
    }

    public function deleted(Model $model): void
    {
        if (config('smartsearch.queue', true)) {
            DeleteDocument::dispatch($model);
        } else {
            app(\SmartSearch\Contracts\SearchDriver::class)->delete($model);
        }
    }

    private function dispatch(Model $model): void
    {
        if (config('smartsearch.queue', true)) {
            IndexDocument::dispatch($model);
        } else {
            app(\SmartSearch\Contracts\SearchDriver::class)->index($model);
        }
    }
}
