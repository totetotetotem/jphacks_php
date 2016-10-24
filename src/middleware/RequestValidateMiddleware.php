<?php

class RequestValidateMiddleware
{
	/**
	 * Request Validation
	 *
	 * @param  \Psr\Http\Message\ServerRequestInterface $request PSR7 request
	 * @param  \Psr\Http\Message\ResponseInterface $response PSR7 response
	 * @param  callable $next Next middleware
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function __invoke($request, $response, $next)
	{
		$schema_file = APP_ROOT_PATH . '/generated-api-schema/' . $request->getRequestTarget() . '.json';
		$response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
		if (!file_exists($schema_file)) {
			// エラー出力
			return $response->withJson([
				'error' => [
					'message' => 'Invalid API endpoint',
					'reason' => 'no spec']], 404);
		}

		$schema = json_decode(file_get_contents($schema_file));
		// objectじゃないとvalidatorが通らないの悲しい
		$data = json_decode($request->getBody());

		if ($data === null) {
			return $response->withJson([
				'error' => [
					'message' => 'Invalid request',
					'reason' => 'malformed json']], 400);
		}

		$validator = new JsonSchema\Validator();
		$validator->check($data, $schema->input);
		if ($validator->isValid()) {
			return $next($request, $response);
		} else {
			return $response->withJson([
				'error' => [
					'message' => 'Invalid request',
					'reason' => 'input validation failed',
					'info' => $validator->getErrors()]], 400);
		}
	}
}
