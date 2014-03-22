<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   Laravel 4 Smart Errors
 * @link https://github.com/anlutro/laravel-4-smart-errors
 */

namespace Ipsum\Errors;

use Config;
use Log;
use Mail;
use Request;
use Response;
use Route;
use View;
use Cache;
use Queue;
use Carbon\Carbon;

/**
 * The class that handles the errors. Obviously
 */
class ErrorHandler
{
	/**
	 * Handle an uncaught exception. Returns a view if config.app.debug == false,
	 * otherwise returns void to let the default L4 error handler do its job.
	 *
	 * @param  Exception $exception
	 * @param  integer   $code
	 *
	 * @return View|void
	 */
	public function handleException($exception, $code = null)
	{

        if (!Config::get('app.debug')) {
            // show the friendly error message
            switch ($code) {
                case 403:
                    return Response::make(View::make('IpsumErrors::403'), 403);

                case 404:
                    return Response::make(View::make('IpsumErrors::404'), 404);
            }
        }

		$route = $this->findRoute();
		$url = Request::fullUrl();
		$client = Request::getClientIp();

		$logstr = "Uncaught Exception (handled by IpsumErrors)\nURL: $url -- Route: $route -- Client: $client\n" . $exception;

		// get any input and log it
		$input = Request::all();
		if (!empty($input)) {
			$logstr .= 'Input: ' . json_encode($input);
		}

		Log::error($logstr);

		// if debug is false end the mail
		if (!Config::get('app.debug')) {
			$timeFormat = Config::get('IpsumErrors::date-format') ?: 'Y-m-d H:i:s';

            // TODO mettre les inputs
            // Mise en cache impossible d'objet. Exemple lors d'upload
            // Serialization of 'Symfony\Component\HttpFoundation\File\UploadedFile' is not allowed in vendor/laravel/framework/src/Illuminate/Cache/FileStore.php:82
			$mailData = array(
    			array(
    				'exception' => array(
    				    'class' => get_class($exception),
    				    'message' => $exception->getMessage(),
    				    'code' => $exception->getCode(),
    				    'file' => $exception->getFile(),
    				    'line' => $exception->getLine(),
				    ),
    				'url'       => $url,
    				'route'     => $route,
    				'client'    => $client,
    				'input'     => '',
    				'time'      => date($timeFormat),
                )
            );
            if ($cache = Cache::get('IpsumErrors::logs')) {
                $mailData = array_merge($cache, $mailData);
            }

            Cache::put('IpsumErrors::logs', $mailData, Config::get('IpsumErrors::dureeCache'));
            $minutes = Carbon::now()->addMinutes(1);

            if (!$dateSend = Cache::get('IpsumErrors::dateLastSend') or Carbon::now() > $dateSend->addMinutes(Config::get('IpsumErrors::minutesBetwwenSendMail'))) {
                $this->sendMail();
            }

			// show the friendly error message
            switch ($code) {
                case 500:
                    return Response::make(View::make('IpsumErrors::500'), 500);
            }
        }

		// if debug is true, do nothing and the default exception whoops page is shown
	}

    /**
     * Send mail
     *
     * @param  Exception $exception
     *
     * @return Response
     */
    private function sendMail()
    {
        $email = Config::get('IpsumErrors::dev-email');
        $subject = 'Error report - uncaught exception - ' . Request::root();

        if (Config::get('IpsumErrors::force-email') !== false) {
            Config::set('mail.pretend', false);
        }

        $mailData = Cache::get('IpsumErrors::logs');
        Mail::queue('IpsumErrors::error-email', array('logs' => $mailData), function($msg) use($email, $subject) {
            $msg->to($email)->subject($subject);
        });
        Cache::forget('IpsumErrors::logs');
        Cache::put('IpsumErrors::dateLastSend', Carbon::now(), Config::get('IpsumErrors::dureeCache'));

    }

    /**
     * Handle Mode no found.
     *
     * @param  \Illuminate\Database\Eloquent\ModelNotFoundException $exception
     *
     * @return void
     */
    public function handleModelNotFoundException($exception)
    {
        return Response::make(View::make('IpsumErrors::404'), 404);
    }

	/**
	 * Handle an alert-level logging event.
	 *
	 * @param  string $message
	 * @param  array  $context
	 *
	 * @return void
	 */
	public function handleAlert($message, $context)
	{
        // TODO
	}

	/**
	 * Get the action or name of the current route.
	 *
	 * @return string
	 */
	protected function findRoute()
	{
		$route = Route::current();

		if (!$route) {
			return 'NA (probably a console command)';
		} elseif (($name = $route->getName()) || ($name = $route->getActionName())) {
			return $name;
		} else {
			return 'NA (unknown route)';
		}
	}

	/**
	 * Determine whether a JSON response should be returned.
	 *
	 * @return bool
	 */
	protected function json()
	{
		return Request::wantsJson() || Request::isJson() || Request::ajax();
	}
}
