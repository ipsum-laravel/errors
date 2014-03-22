<?php namespace Ipsum\Errors;

use Illuminate\Support\ServiceProvider;

class ErrorsServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
    public function boot()
    {
        $this->package('Ipsum/Errors', 'IpsumErrors', __DIR__);

        // $this->app in closures won't work in php 5.3
        $app = $this->app;

        // register the error handler
        $this->app->error(function(\Exception $exception, $code) use ($app) {
            return $app['error']->handleException($exception, $code);
        });

        // registrer the model no found
        $this->app->error(function(\Illuminate\Database\Eloquent\ModelNotFoundException $exception) use ($app) {
            return $app['error']->handleModelNotFoundException($exception);
        });

        // For test views
        //include __DIR__.'/routes.php';
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app->bind('error', function()
        {
            return new ErrorHandler;
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('error');
	}

}
