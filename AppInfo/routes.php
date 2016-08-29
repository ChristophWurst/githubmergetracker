<?php
/**
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @copyright Lukas Reschke 2016
 */

return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'page#addRepoToTrack', 'url' => '/repo', 'verb' => 'POST'],
		['name' => 'page#resolve', 'url' => '/resolve', 'verb' => 'GET'],
	]
];