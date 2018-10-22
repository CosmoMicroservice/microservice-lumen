<?php

	namespace App\Exceptions;

	use Exception;
	use Illuminate\Validation\ValidationException;
	use Illuminate\Auth\Access\AuthorizationException;
	use Illuminate\Database\Eloquent\ModelNotFoundException;
	use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
	use Symfony\Component\HttpKernel\Exception\HttpException;

	class Handler extends ExceptionHandler
	{
		/**
		 * A list of the exception types that should not be reported.
		 *
		 * @var array
		 */
		protected $dontReport = [
			AuthorizationException::class,
			ModelNotFoundException::class,
			ValidationException::class,
		];

		/**
		 * Report or log an exception.
		 *
		 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
		 *
		 * @param  \Exception $e
		 * @return void
		 */
		public function report(Exception $e)
		{
			parent::report($e);
		}

		/**
		 * Render an exception into an HTTP response.
		 *
		 * @param  \Illuminate\Http\Request $request
		 * @param  \Exception $e
		 * @return \Illuminate\Http\Response
		 */
		public function render($request, Exception $e)
		{
			$status = 400;
			$headers = [];
			$response = [
				'exception' => [
					'message' => $e->getMessage(),
					'code' => $e->getCode(),
				],
			];

			if($e instanceof HttpException) {
				$status = $e->getStatusCode();
				$headers = $e->getHeaders();
				$response['exception'] = array_add($response['exception'], 'status_code', $status);
			}

			if(env('APP_DEBUG')){
				$response['exception'] = array_add($response['exception'], 'debug.file', $e->getFile());
				$response['exception'] = array_add($response['exception'], 'debug.line', $e->getLine());
				$response['exception'] = array_add($response['exception'], 'debug.trace', $e->getTraceAsString());
			}

			return response()->json($response, $status,$headers);
		}
	}
