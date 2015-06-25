<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* Less handler function */
	
	function less_css( $handle ) {
		include DUDE_THEME_DIR . "/less/variables.php";
		// output css file name
		$css_path = DUDE_THEME_DIR.'/css/' . "{$handle}.css";

		// automatically regenerate files if source's modified time has changed or vars have changed
		try {

			// initialise the parser
			$less = new lessc;

			// load the cache
			$cache_path = DUDE_THEME_DIR . "/cache/{$handle}.css.cache";

			if ( file_exists( $cache_path ) )
				$cache = unserialize( file_get_contents( $cache_path ) );

			// If the cache or root path in it are invalid then regenerate
			if ( empty( $cache ) || empty( $cache['less']['root'] ) || ! file_exists( $cache['less']['root'] ) )
				$cache = array( 'vars' => $vars, 'less' => DUDE_THEME_DIR . "/less/{$handle}.less" );

			// less config
			$less->setFormatter("compressed");
			$less->setVariables($vars);

			$less_cache = $less->cachedCompile( $cache[ 'less' ], false );

			if ( !file_exists($css_path) || empty( $cache ) || empty( $cache[ 'less' ][ 'updated' ] ) || $less_cache[ 'updated' ] > $cache[ 'less' ][ 'updated' ]) {
				file_put_contents( $cache_path, serialize( array('vars' => $vars, 'less' => $less_cache ) ) );
				file_put_contents( $css_path, $less_cache[ 'compiled' ] );
			}elseif($vars !== $cache[ 'vars' ]){
				$less_cache = $less->cachedCompile( $cache[ 'less' ], true );
				file_put_contents( $cache_path, serialize( array('vars' => $vars, 'less' => $less_cache ) ) );
				file_put_contents( $css_path, $less_cache[ 'compiled' ] );			
			}
		} catch ( exception $ex ) {
			qa_fatal_error($ex->getMessage());
		}

		// return the compiled stylesheet with the query string it had if any
		$url = DUDE_THEME_URL. "/css/{$handle}.css";
		echo '<link href="'.$url.'" type="text/css" rel="stylesheet">';
	}