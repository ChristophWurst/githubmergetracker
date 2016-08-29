<?php
/**
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @copyright Lukas Reschke 2016
 */
namespace OCA\GitHubMergeTracker;

use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IDBConnection;

class TrackedRepo {
	/** @var IConfig */
	private $config;
	/** @var string */
	private $appName;
	/** @var IDBConnection */
	private $dbConnection;
	/** @var IClientService */
	private $httpClientService;
	/** @var int */
	private $repoId;
	/** @var string */
	private $repoName;

	/**
	 * @param IConfig $config
	 * @param IDBConnection $dbConnection
	 * @param IClientService $httpClientService
	 * @param int $repoId
	 */
	public function __construct(IConfig $config,
								IDBConnection $dbConnection,
								IClientService $httpClientService,
								$repoId) {
		$this->appName = 'githubmergetracker';
		$this->config = $config;
		$this->dbConnection = $dbConnection;
		$this->httpClientService = $httpClientService;
		$this->repoId = (int)$repoId;
		$this->loadRepoMetaData();
	}

	private function loadRepoMetaData() {
		$qb = $this->dbConnection->getQueryBuilder();
		$results = $qb->select('id', 'repo')
			->from('githubmergetracker_trackedRepos')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($this->repoId)))
			->execute();
		$result = $results->fetch();
		$this->repoName = $result['repo'];
	}

	public function getLastScanTime() {
		return (int)$this->config->getAppValue($this->appName, 'lastScanTime-'.(string)$this->repoId, 0);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->repoName;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->repoId;
	}

	public function getUnresolvedPullRequests() {
		$qb = $this->dbConnection->getQueryBuilder();
		$results = $qb->select('id', 'repoId', 'issueId', 'title', 'state')
			->from('githubmergetracker_importedIssues')
			->where($qb->expr()->eq('repoId', $qb->createNamedParameter($this->getId(), \PDO::PARAM_STR)))
			->andWhere($qb->expr()->eq('state', $qb->createNamedParameter(0, \PDO::PARAM_STR)))
			->execute();
		$result = $results->fetchAll();
		return $result;
	}

	/**
	 * @param int $page
	 * @throws \Exception
	 */
	public function scan($page = 0) {
		$client = $this->httpClientService->newClient();
		try {
			$response = $client->get('https://api.github.com/repos/'.$this->getName().'/pulls?client_id='.$this->config->getSystemValue('github.client_id').'&client_secret='.$this->config->getSystemValue('github.client_secret').'&state=all&page='.$page);
			$decodedBody = json_decode($response->getBody(), true);
			if(!is_array($decodedBody)) {
				throw new \Exception('Could not decode message body');
			}
			foreach($decodedBody as $body) {
				$id = isset($body['number']) ? $body['number'] : '';
				$state = isset($body['state']) ? $body['state'] : '';
				$title = isset($body['title']) ? $body['title'] : '';

				if($id === '' || $state === '' || $title === '') {
					throw new \Exception('Invalid JSON message');
				}

				// Skip all non-closed PRs as we are not interested in them
				if($state !== 'closed') {
					continue;
				}

				// Insert if not exists
				\OCP\DB::insertIfNotExist(
					'*PREFIX*githubmergetracker_importedIssues',
					[
						'repoId' => $this->getId(),
						'issueId' => $id,
						'title' => $title,
					],
					[
						'repoId',
						'issueId',
					]
				);
			}

			$this->config->setAppValue($this->appName, 'lastScanTime-'.(string)$this->repoId, time());
			if(count($decodedBody) !== 0) {
				$this->scan($page+1);
			}
		} catch (\ParseError $e) {}
	}
}
