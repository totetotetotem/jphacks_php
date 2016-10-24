<?php
namespace middleware;

use Psr\Http\Message\ResponseInterface;

/**
 * Class JsonRenderer
 *
 * Render PHP view scripts into a PSR-7 Response object
 */
class JsonRenderer
{
	/**
	 * JsonRenderer constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * Render a template
	 *
	 * $data cannot contain template as a key
	 *
	 * throws RuntimeException if $templatePath . $template does not exist
	 *
	 * @param ResponseInterface $response
	 * @param array $data
	 *
	 * @return ResponseInterface
	 *
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public function render(ResponseInterface $response, array $data = [])
	{
		$status = $response->getStatusCode();
		$data['meta'] = ['status' => $status];

		$response->getBody()->write(json_encode($data));
		return $response;
	}

	public function renderAsError(ResponseInterface $response, $status_code, $message, $reason, $extra = null)
	{
		$response = $response->withStatus($status_code);
		$data['meta'] = [
			'status' => $status_code,
			'message' => $message,
			'reason' => $reason];
		if ($extra !== null) {
			$data['meta']['extra'] = $extra;
		}

		$response->getBody()->write(json_encode($data));
		return $response;
	}
}
