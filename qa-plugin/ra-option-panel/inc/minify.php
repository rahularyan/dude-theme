<?php
	class Qw_Minify_class{
		
		var $src;
		
		public function qw_compress_css($content, $css_url) {
			$this->src = $css_url;			
			
			// Normalize whitespace
			$content = preg_replace( '/\s+/', ' ', $content );

			// Remove comment blocks, everything between /* and */, unless
			// preserved with /*! ... */
			$content = preg_replace( '/\/\*[^\!](.*?)\*\//', '', $content );

			// Remove ; before }
			$content = preg_replace( '/;(?=\s*})/', '', $content );

			// Remove space after , : ; { } */ >
			$content = preg_replace( '/(,|:|;|\{|}|\*\/|>) /', '$1', $content );

			// Remove space before , ; { } ( ) >
			$content = preg_replace( '/ (,|;|\{|}|\(|\)|>)/', '$1', $content );

			// Strips leading 0 on decimal values (converts 0.5px into .5px)
			$content = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $content );

			// Strips units if value is 0 (converts 0px to 0)
			$content = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $content );

			// Converts all zeros value into short-hand
			$content = preg_replace( '/0 0 0 0/', '0', $content );

			// Shortern 6-character hex color codes to 3-character where possible
			$content = preg_replace( '/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i', '#\1\2\3', $content );
			
			$content = preg_replace_callback('/url\(\s*[\'"]?\/?(.+?)[\'"]?\s*\)/i', array($this, 'qw_replace_url_in_css'), $content);

			return trim( $content );
		}

		function qw_replace_url_in_css($matches){
			return 'url('.$this->url_to_absolute($this->src, $matches[1]).')';
		}

		public function qw_compress_js( $content) {
			$content = preg_replace( '/\/\*[^\!](.*?)\*\//', '', $content );
			
			return $content;
		}	
		function url_to_absolute( $baseUrl, $relativeUrl )
		{

			// If relative URL has a scheme, clean path and return.
			$r = $this->split_url( $relativeUrl );
			if ( $r === FALSE )
				return FALSE;
			if ( !empty( $r['scheme'] ) )
			{
				if ( !empty( $r['path'] ) && $r['path'][0] == '/' )
					$r['path'] = $this->url_remove_dot_segments( $r['path'] );
				return $this->join_url( $r );
			}

			// Make sure the base URL is absolute.
			$b = $this->split_url( $baseUrl );
			if ( $b === FALSE || empty( $b['scheme'] ) || empty( $b['host'] ) )
				return FALSE;
			$r['scheme'] = $b['scheme'];

			// If relative URL has an authority, clean path and return.
			if ( isset( $r['host'] ) )
			{
				if ( !empty( $r['path'] ) )
					$r['path'] = $this->url_remove_dot_segments( $r['path'] );
				return $this->join_url( $r );
			}
			unset( $r['port'] );
			unset( $r['user'] );
			unset( $r['pass'] );

			// Copy base authority.
			$r['host'] = $b['host'];
			if ( isset( $b['port'] ) ) $r['port'] = $b['port'];
			if ( isset( $b['user'] ) ) $r['user'] = $b['user'];
			if ( isset( $b['pass'] ) ) $r['pass'] = $b['pass'];

			// If relative URL has no path, use base path
			if ( empty( $r['path'] ) )
			{
				if ( !empty( $b['path'] ) )
					$r['path'] = $b['path'];
				if ( !isset( $r['query'] ) && isset( $b['query'] ) )
					$r['query'] = $b['query'];
				return $this->join_url( $r );
			}

			// If relative URL path doesn't start with /, merge with base path
			if ( $r['path'][0] != '/' )
			{
				$base = mb_strrchr( $b['path'], '/', TRUE, 'UTF-8' );
				if ( $base === FALSE ) $base = '';
				$r['path'] = $base . '/' . $r['path'];
			}
			$r['path'] = $this->url_remove_dot_segments( $r['path'] );
			return $this->join_url( $r );
		}

		function url_remove_dot_segments( $path )
		{
			// multi-byte character explode
			$inSegs  = preg_split( '!/!u', $path );
			$outSegs = array( );
			foreach ( $inSegs as $seg )
			{
				if ( $seg == '' || $seg == '.')
					continue;
				if ( $seg == '..' )
					array_pop( $outSegs );
				else
					array_push( $outSegs, $seg );
			}
			$outPath = implode( '/', $outSegs );
			if ( $path[0] == '/' )
				$outPath = '/' . $outPath;
			// compare last multi-byte character against '/'
			if ( $outPath != '/' &&
				(mb_strlen($path)-1) == mb_strrpos( $path, '/', 'UTF-8' ) )
				$outPath .= '/';
			return $outPath;
		}


		function split_url( $url, $decode=FALSE)
		{
			// Character sets from RFC3986.
			$xunressub     = 'a-zA-Z\d\-._~\!$&\'()*+,;=';
			$xpchar        = $xunressub . ':@% ';

			// Scheme from RFC3986.
			$xscheme        = '([a-zA-Z][a-zA-Z\d+-.]*)';

			// User info (user + password) from RFC3986.
			$xuserinfo     = '((['  . $xunressub . '%]*)' .
							 '(:([' . $xunressub . ':%]*))?)';

			// IPv4 from RFC3986 (without digit constraints).
			$xipv4         = '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})';

			// IPv6 from RFC2732 (without digit and grouping constraints).
			$xipv6         = '(\[([a-fA-F\d.:]+)\])';

			// Host name from RFC1035.  Technically, must start with a letter.
			// Relax that restriction to better parse URL structure, then
			// leave host name validation to application.
			$xhost_name    = '([a-zA-Z\d-.%]+)';

			// Authority from RFC3986.  Skip IP future.
			$xhost         = '(' . $xhost_name . '|' . $xipv4 . '|' . $xipv6 . ')';
			$xport         = '(\d*)';
			$xauthority    = '((' . $xuserinfo . '@)?' . $xhost .
						 '?(:' . $xport . ')?)';

			// Path from RFC3986.  Blend absolute & relative for efficiency.
			$xslash_seg    = '(/[' . $xpchar . ']*)';
			$xpath_authabs = '((//' . $xauthority . ')((/[' . $xpchar . ']*)*))';
			$xpath_rel     = '([' . $xpchar . ']+' . $xslash_seg . '*)';
			$xpath_abs     = '(/(' . $xpath_rel . ')?)';
			$xapath        = '(' . $xpath_authabs . '|' . $xpath_abs .
					 '|' . $xpath_rel . ')';

			// Query and fragment from RFC3986.
			$xqueryfrag    = '([' . $xpchar . '/?' . ']*)';

			// URL.
			$xurl          = '^(' . $xscheme . ':)?' .  $xapath . '?' .
							 '(\?' . $xqueryfrag . ')?(#' . $xqueryfrag . ')?$';


			// Split the URL into components.
			if ( !preg_match( '!' . $xurl . '!', $url, $m ) )
				return FALSE;

			if ( !empty($m[2]) )		$parts['scheme']  = strtolower($m[2]);

			if ( !empty($m[7]) ) {
				if ( isset( $m[9] ) )	$parts['user']    = $m[9];
				else			$parts['user']    = '';
			}
			if ( !empty($m[10]) )		$parts['pass']    = $m[11];

			if ( !empty($m[13]) )		$h=$parts['host'] = $m[13];
			else if ( !empty($m[14]) )	$parts['host']    = $m[14];
			else if ( !empty($m[16]) )	$parts['host']    = $m[16];
			else if ( !empty( $m[5] ) )	$parts['host']    = '';
			if ( !empty($m[17]) )		$parts['port']    = $m[18];

			if ( !empty($m[19]) )		$parts['path']    = $m[19];
			else if ( !empty($m[21]) )	$parts['path']    = $m[21];
			else if ( !empty($m[25]) )	$parts['path']    = $m[25];

			if ( !empty($m[27]) )		$parts['query']   = $m[28];
			if ( !empty($m[29]) )		$parts['fragment']= $m[30];

			if ( !$decode )
				return $parts;
			if ( !empty($parts['user']) )
				$parts['user']     = rawurldecode( $parts['user'] );
			if ( !empty($parts['pass']) )
				$parts['pass']     = rawurldecode( $parts['pass'] );
			if ( !empty($parts['path']) )
				$parts['path']     = rawurldecode( $parts['path'] );
			if ( isset($h) )
				$parts['host']     = rawurldecode( $parts['host'] );
			if ( !empty($parts['query']) )
				$parts['query']    = rawurldecode( $parts['query'] );
			if ( !empty($parts['fragment']) )
				$parts['fragment'] = rawurldecode( $parts['fragment'] );
			return $parts;
		}

		function join_url( $parts, $encode=FALSE)
		{
			if ( $encode )
			{
				if ( isset( $parts['user'] ) )
					$parts['user']     = rawurlencode( $parts['user'] );
				if ( isset( $parts['pass'] ) )
					$parts['pass']     = rawurlencode( $parts['pass'] );
				if ( isset( $parts['host'] ) &&
					!preg_match( '!^(\[[\da-f.:]+\]])|([\da-f.:]+)$!ui', $parts['host'] ) )
					$parts['host']     = rawurlencode( $parts['host'] );
				if ( !empty( $parts['path'] ) )
					$parts['path']     = preg_replace( '!%2F!ui', '/',
						rawurlencode( $parts['path'] ) );
				if ( isset( $parts['query'] ) )
					$parts['query']    = rawurlencode( $parts['query'] );
				if ( isset( $parts['fragment'] ) )
					$parts['fragment'] = rawurlencode( $parts['fragment'] );
			}

			$url = '';
			if ( !empty( $parts['scheme'] ) )
				$url .= $parts['scheme'] . ':';
			if ( isset( $parts['host'] ) )
			{
				$url .= '//';
				if ( isset( $parts['user'] ) )
				{
					$url .= $parts['user'];
					if ( isset( $parts['pass'] ) )
						$url .= ':' . $parts['pass'];
					$url .= '@';
				}
				if ( preg_match( '!^[\da-f]*:[\da-f.:]+$!ui', $parts['host'] ) )
					$url .= '[' . $parts['host'] . ']';	// IPv6
				else
					$url .= $parts['host'];			// IPv4 or name
				if ( isset( $parts['port'] ) )
					$url .= ':' . $parts['port'];
				if ( !empty( $parts['path'] ) && $parts['path'][0] != '/' )
					$url .= '/';
			}
			if ( !empty( $parts['path'] ) )
				$url .= $parts['path'];
			if ( isset( $parts['query'] ) )
				$url .= '?' . $parts['query'];
			if ( isset( $parts['fragment'] ) )
				$url .= '#' . $parts['fragment'];
			return $url;
		}


		function encode_url($url) {
		  $reserved = array(
			":" => '!%3A!ui',
			"/" => '!%2F!ui',
			"?" => '!%3F!ui',
			"#" => '!%23!ui',
			"[" => '!%5B!ui',
			"]" => '!%5D!ui',
			"@" => '!%40!ui',
			"!" => '!%21!ui',
			"$" => '!%24!ui',
			"&" => '!%26!ui',
			"'" => '!%27!ui',
			"(" => '!%28!ui',
			")" => '!%29!ui',
			"*" => '!%2A!ui',
			"+" => '!%2B!ui',
			"," => '!%2C!ui',
			";" => '!%3B!ui',
			"=" => '!%3D!ui',
			"%" => '!%25!ui',
		  );

		  $url = rawurlencode($url);
		  $url = preg_replace(array_values($reserved), array_keys($reserved), $url);
		  return $url;
		}
	}

?>