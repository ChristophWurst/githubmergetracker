$(window).load(function(){
	$('#oca-githubmergetracker-add > input[type="submit"]').click(function() {
		$.post(OC.generateUrl('/apps/githubmergetracker/repo'), {
			repo: $('#oca-githubmergetracker-add > input[type="text"]').val()
		});
		location.reload();
	});
});
