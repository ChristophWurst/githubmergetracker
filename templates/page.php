<?php
	style('githubmergetracker', 'theme');
	script('githubmergetracker', 'page');

	/** @var array $_ */
	/** @var \OCA\GitHubMergeTracker\TrackedRepo[] $repos */
	$repos = $_['repos'];
?>
<div id="app">
	<div id="app-navigation">
		<ul>
			<li><a href="<?php p(\OC::$server->getURLGenerator()->linkToRoute('githubmergetracker.page.index')) ?>"><?php p($l->t('All repos')) ?></a></li>
			<?php foreach($repos as $repo): ?>
				<li><a href="<?php p(\OC::$server->getURLGenerator()->linkToRoute('githubmergetracker.page.index', ['id' => $repo->getId()])) ?>"><?php p($repo->getName()) ?></a></li>
			<?php endforeach; ?>
		</ul>
		<div id="app-settings">
			<div id="app-settings-header">
				<button class="settings-button"
						data-apps-slide-toggle="#app-settings-content"
				></button>
			</div>
			<div id="app-settings-content">
				<form id="oca-githubmergetracker-add">
					<input type="text" placeholder="nextcloud/server"/>
					<input type="submit" value="<?php p($l->t('Add')) ?>"/>
				</form>
			</div>
		</div>
	</div>

	<div id="app-content">
		<?php foreach($repos as $repo): ?>
			<?php if($_['id'] !== 0): ?>
				<?php if($_['id'] !== $repo->getId()) { continue; } ?>
			<?php endif; ?>
			<div class="section">
				<h2><?php p($repo->getName()) ?></h2>
				<?php
				$unresolvedPRs = $repo->getUnresolvedPullRequests();
				if(count($unresolvedPRs) > 0): ?>
					<ul class="pull-request-list">

					<?php foreach($repo->getUnresolvedPullRequests() as $pullRequest): ?>
						<li><a rel="noreferrer" href="https://github.com/<?php p($repo->getName()) ?>/pull/<?php p($pullRequest['issueId']) ?>"><?php p($pullRequest['title']) ?> (#<?php p($pullRequest['issueId']) ?>)</a>
							&nbsp;&nbsp;<a href="<?php p(\OC::$server->getURLGenerator()->linkToRoute('githubmergetracker.page.resolve', ['id' => $pullRequest['id'], 'repoId' => $repo->getId(), 'requesttoken' => \OCP\Util::callRegister()])) ?>" class="button"><?php p($l->t('Mark done')) ?></a></li>
					<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<?php if($repo->getLastScanTime() > 0): ?>
						<p><?php p($l->t('All done. 🚀')) ?></p>
					<?php endif; ?>
				<?php endif; ?>
				<?php if($repo->getLastScanTime() > 0): ?>
					<p><em><small><?php p($l->t('Last scan: %s', OCP\Template::relative_modified_date($repo->getLastScanTime()))) ?></small></em></p>
				<?php else: ?>
					<p><em><small><?php p($l->t('Scanning queued …') )?></small></em></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
