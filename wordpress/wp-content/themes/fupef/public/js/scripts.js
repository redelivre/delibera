jQuery(".navigation-responsive-button").click(function () {
	var effect = 'slide';
	var options = {direction: 'right'};
	var duration = 300;
	jQuery('.navigation-responsive-container').toggle(effect, options, duration);
});

jQuery(".responsive-nav a").click(function () {
	var effect = 'slide';
	var options = {direction: 'right'};
	var duration = 300;
	jQuery('.navigation-responsive-container').toggle(effect, options, duration);
});



// facebook
(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v2.4&appId=257061814493621";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));


// Instagram
(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.src="//x.instagramfollowbutton.com/follow.js";s.parentNode.insertBefore(g,s);}(document,"script"));