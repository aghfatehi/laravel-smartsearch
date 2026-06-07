<?php

namespace SmartSearch\Indexing\IndexJobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use SmartSearch\Contracts\SearchDriver;

class DeleteDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function handle(SearchDriver $driver): void
    {
        try {
            $driver->delete($this->model);
        } catch (\Throwable $e) {
            Log::error('SmartSearch: failed to delete document', [
                'model' => get_class($this->model),
                'id' => $this->model->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
