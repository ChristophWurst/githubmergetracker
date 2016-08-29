<?php
/**
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @copyright Lukas Reschke 2016
 */

namespace OCA\GitHubMergeTracker\BackgroundJobs;

use OC\BackgroundJob\TimedJob;
use OCA\GitHubMergeTracker\TrackedRepo;

class Scanner extends TimedJob {
	public function __construct() {
		// Run all 5 minutes
		$this->setInterval(300);
	}

	/**
	 * Makes the background job do its work
	 *
	 * @param array $argument unused argument
	 */
	public function run($argument) {
		$connection = \OC::$server->getDatabaseConnection();

		$qb = $connection->getQueryBuilder();
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
			$trackedRepo->scan();
		}
		exit();
	}

}
