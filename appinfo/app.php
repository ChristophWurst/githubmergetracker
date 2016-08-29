<?php
/**
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @copyright Lukas Reschke 2016
 */

namespace OCA\GitHubMergeTracker\AppInfo;

use OCP\AppFramework\App;

$app = new App('githubmergetracker');
$container = $app->getContainer();

$container->query('OCP\INavigationManager')->add(function () use ($container) {
	$urlGenerator = $container->query('OCP\IURLGenerator');
	$l10n = $container->query('OCP\IL10N');
	return [
		'id' => 'githubmergetracker',
		'href' => $urlGenerator->linkToRoute('githubmergetracker.page.index'),
		'icon' => $urlGenerator->imagePath('contacts', 'app.svg'),
		'name' => $l10n->t('GitHub Merge Tracker'),
		'order' => 99,
	];
});
