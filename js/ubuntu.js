/* Ubuntu Q2A Theme stuff */

/* Corners */
jQuery('.qa-sub-header-content').corner('round 5px');
jQuery('.qa-sidepanel').corner('round 5px');
jQuery('.qa-form-tall-table').corner('round 5px');
jQuery('.qa-q-list-item').corner('round 5px');
jQuery('code').corner('round 5px');
jQuery('pre').corner('round 5px');
jQuery('.awesome').corner('round 5px');
jQuery('.qa-q-view').corner('round 5px');
jQuery('.qa-a-list-item').corner('round 5px');
jQuery('.qa-top-tags-table').corner('round 5px');
jQuery('.qa-top-users-table').corner('round 5px');
jQuery('.qa-template-user .qa-form-wide-table').corner('round 5px');
jQuery('.qa-template-account .qa-form-wide-table').corner('round 5px');
jQuery('.qa-template-admin table').corner('round 5px');
jQuery('.qa-q-view-avatar img').corner('round 5px');
jQuery('.qa-a-item-avatar img').corner('round 5px');
jQuery('.qa-c-item-avatar img').corner('round 3px');
jQuery('.qa-avatar-link img').corner('round 5px');
jQuery('input[type="text"]').corner('round 5px');
jQuery('input[type="password"]').corner('round 5px');

/* Enable prettify */
jQuery('pre').addClass('prettyprint linenums' );
jQuery('code').addClass( 'prettyprint linenums' );
jQuery('.prettyprint').css( {'background': 'transparent', 'color': '#444' } );
prettyPrint();

/* Some glow to vote buttons */
jQuery('.qa-vote-up-disabled').fadeTo(200, '0.5');
jQuery('.qa-vote-down-disabled').fadeTo(200, '0.5');
jQuery('.qa-a-selection input').hover( function() {
    jQuery(this).fadeTo(200, '0.7');
}, function () {
	jQuery(this).fadeTo(200, '1');
});

jQuery('.qa-vote-buttons input').hover( function () {
	jQuery(this).fadeTo(200, '0.7');
}, function () {
	jQuery(this).fadeTo(200, '1');
});

/* Set some awesomeness */
jQuery('.qa-suggest-next').addClass( 'awesome' );
jQuery('.qa-top-tags-label').addClass( 'awesome' );
jQuery('.qa-top-users-label').addClass( 'awesome' );
jQuery('.qa-page-links-item').addClass( 'awesome' );
jQuery('.qa-vote-buttons input').click( function(){
	// Wait for ajax for a while
	setTimeout( function() {
		jQuery('#errorbox').addClass( 'awesome' );
		jQuery('#errorbox').corner('round 5px');
	}, 100);
} );
