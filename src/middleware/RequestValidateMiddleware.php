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
		$schema_file = APP_ROOT_PATH . '/generated-api-schema/' . explode('?', $request->getRequestTarget())[0] . '.json';
		$response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
		if (!file_exists($schema_file)) {
			// エラー出力
			// 結局これでもパラメータがuriに含まれる時に死んでしまうのでどうしようか
			//return get_renderer()->renderAsError($response, 404, 'Invalid API endpoint', 'no spec', $schema_file);
		}

		$schema = json_decode(file_get_contents($schema_file));
		// objectじゃないとvalidatorが通らないの悲しい
		$data = json_decode($request->getBody());

		if ($data === null) {
		//data が nullであることもGETとかだと割とよくあるので一旦コメントアウト
		//	return get_renderer()->renderAsError($response, 400, 'Invalid request', 'malformed json');
		}
		
		//TODO ここでBodyNullを許可する場合と許可しない場合のValidateわける
		return $next($request, $response);

		$validator = new JsonSchema\Validator();
		$validator->check($data, $schema->input);
		if ($validator->isValid()) {
			return $next($request, $response);
		} else {
			$extra = [];
			foreach ($validator->getErrors() as $error) {
				$extra[] = sprintf('[%s] %s', $error['property'], $error['message']);
			}
			return get_renderer()->renderAsError($response, 400, 'Invalid request', 'input validation failed', $extra);
		}
	}
}
