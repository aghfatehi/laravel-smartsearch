<?php

namespace SmartSearch\Tests\Unit;

use SmartSearch\Contracts\SearchDriver;
use SmartSearch\Indexing\IndexJobs\DeleteDocument;
use SmartSearch\Indexing\IndexJobs\IndexDocument;
use SmartSearch\Tests\Stubs\Product;
use SmartSearch\Tests\TestCase;

class ModelObserverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpProductTable();
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();
        parent::tearDown();
    }

    public function test_created_calls_index_on_driver_when_queue_disabled(): void
    {
        $this->app['config']->set('smartsearch.queue', false);

        $driver = \Mockery::mock(SearchDriver::class);
        $driver->shouldReceive('index')->once()->with(\Mockery::type(Product::class));
        $driver->shouldReceive('getName')->andReturn('database');
        $this->app->instance(SearchDriver::class, $driver);

        Product::create(['name' => 'iPhone', 'description' => 'Phone', 'price' => 5000]);
    }

    public function test_index_job_calls_driver_index(): void
    {
        $driver = \Mockery::mock(SearchDriver::class);
        $driver->shouldReceive('index')->once()->with(\Mockery::type(Product::class));

        $job = new IndexDocument(new Product());
        $job->handle($driver);
    }

    public function test_delete_job_calls_driver_delete(): void
    {
        $driver = \Mockery::mock(SearchDriver::class);
        $driver->shouldReceive('delete')->once()->with(\Mockery::type(Product::class));

        $job = new DeleteDocument(new Product());
        $job->handle($driver);
    }

    public function test_index_job_logs_on_failure(): void
    {
        $driver = \Mockery::mock(SearchDriver::class);
        $driver->shouldReceive('index')->andThrow(new \RuntimeException('ES down'));

        $this->expectException(\RuntimeException::class);

        $job = new IndexDocument(new Product());
        $job->handle($driver);
    }


}
