<?php declare(strict_types=1);
namespace OCA\Demo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class PageController extends Controller {
	private $l10n;

	public function __construct($appname,
								IRequest $request,
								$l10n) {
		parent::__construct($appname, $request);
		$this->l10n = $l10n;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$userLang = $this->l10n->getLanguageCode();
		Util::addScript('demo', 'demo-main');
		return new TemplateResponse($this->appName, 'main', ['lang' => $userLang]);
	}
}
