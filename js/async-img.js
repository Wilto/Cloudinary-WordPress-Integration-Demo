(function() {
	function loadAsync() {
		var lazyimgs = document.querySelectorAll( 'img[data-srcset]' ),
			attrswap = function( img ) {
				img.src = img.getAttribute( 'data-src' );

				img.setAttribute( "srcset", img.getAttribute( 'data-srcset' ) );

				img.removeAttribute( "data-src" );
				img.removeAttribute( "data-srcset" );
			},
			supported = "IntersectionObserver" in window 
					&& "IntersectionObserverEntry" in window 
					&& "intersectionRatio" in window.IntersectionObserverEntry.prototype;

		if( supported ) {
			var imgObs = new IntersectionObserver( function( els, obs ) {
				els.forEach( function( el ) {
					if( el.isIntersecting ) {
						var img = el.target;

						attrswap( img );
						imgObs.unobserve( img );
					}
				});
			});

			[].slice.call( lazyimgs ).forEach(function(lazyimg) {
				imgObs.observe( lazyimg );
			});
		} else {
			for( i = 0; i < lazyimgs.length; i++ ){
				attrswap( lazyimgs[ i ] );
			}
		}
	};

	document.addEventListener( "DOMContentLoaded", loadAsync );
}());