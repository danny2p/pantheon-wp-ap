// Source: twentytwenty.intrinsicRatioVideos 
window.WPRecipeMaker.video = {
	init() {
		this.forceRatio();

		window.addEventListener( 'resize', function() {
			this.forceRatio();
        }.bind( this ) );
    },
	forceRatio() {
		document.querySelectorAll( '.wprm-recipe iframe, .wprm-recipe object, .wprm-recipe video, .wprm-recipe-video-container iframe, .wprm-recipe-video-container object, .wprm-recipe-video-container video' ).forEach( function( video ) {
			var ratio, iTargetWidth,
				container = video.parentNode;

			// Skip videos we want to ignore.
			if ( video.classList.contains( 'intrinsic-ignore' ) || video.parentNode.classList.contains( 'intrinsic-ignore' ) ) {
				return true;
			}

			if ( ! video.dataset.origwidth ) {
				// Get the video element proportions.
				video.setAttribute( 'data-origwidth', video.width );
				video.setAttribute( 'data-origheight', video.height );
			}

			iTargetWidth = container.offsetWidth;

			// Get ratio from proportions.
			ratio = iTargetWidth / video.dataset.origwidth;

			// Scale based on ratio, thus retaining proportions.
			video.style.width = iTargetWidth + 'px';
			video.style.height = ( video.dataset.origheight * ratio ) + 'px';
		} );
    },
};

ready(() => {
    if ( wprm_public.settings.video_force_ratio ) {
        window.WPRecipeMaker.video.init();
    }
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}