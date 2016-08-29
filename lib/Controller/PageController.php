<?php
/**
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @copyright Lukas Reschke 2016
 */
namespace OCA\GitHubMergeTracker\Controller;

use OCA\GitHubMergeTracker\TrackedRepo;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IDBConnection;
use OCP\IRequest;
use OCP\IURLGenerator;

class PageController extends Controller {
	/** @var IDBConnection */
	private $dbConnection;
	/** @var IURLGenerator */
	private $urlGenerator;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IDBConnection $dbConnection
	 * @param IURLGenerator $urlGenerator
	 */
	public function __construct($appName,
								IRequest $request,
								IDBConnection $dbConnection,
								IURLGenerator $urlGenerator) {
		parent::__construct($appName, $request);
		$this->dbConnection = $dbConnection;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param string $repo
	 * @return JSONResponse
	 */
	public function addRepoToTrack($repo) {
		\OCP\DB::insertIfNotExist(
			'*PREFIX*githubmergetracker_trackedRepos',
			[
				'repo' => strtolower($repo),
			]
		);

		return new JSONResponse();
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function resolve($id) {
		$qb = $this->dbConnection->getQueryBuilder();
		$qb->update('githubmergetracker_importedIssues')
			->set('state', $qb->createNamedParameter('1'))g
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, \PDO::PARAM_STR)))
			->execute();
		return new RedirectResponse($this->urlGenerator->linkToRoute('githubmergetracker.page.index'));
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param int $id
	 * @return TemplateResponse
	 */
	public function index($id = 0) {
		$params = [
			'repos' => [],
			'id' => $id,
		];
		$qb = $this->dbConnection->getQueryBuilder();
		$results = $qb->select('id')
			->from('githubmergetracker_trackedRepos')
			->execute();
		$results = $results->fetchAll();
		foreach($results as $result) {
			$trackedRepo = new TrackedRepo(
				\OC::$server->getConfig(),
				\OC::$server->getDatabaseConnection(),
				\OC::$server->getHTTPClientService(),
				(int)$result['id']
			);
			array_push($params['repos'], $trackedRepo);
		}

		return new TemplateResponse($this->appName, 'page', $params);
	}
}
