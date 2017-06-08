<?php

/**
 *	@author:	a4p ASD / Andreas Dorner
 *	@company:	apps4print / page one GmbH, Nürnberg, Germany
 *
 *
 *	@version:	1.0.0
 *	@date:		01.06.2017
 *
 *
 *	a4p__tag_parser.php
 *
 *	apps4print -  -
 *
 */

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------

class a4p_admin_cms_files__tag_parser {

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------


	protected $i_pos							= 0;
	protected $b_tagOpen						= false;
	protected $b_isHtmlTag						= false;

	// ------------------------------------------------------------------------------------------------
	// ------------------------------------------------------------------------------------------------

	/**
	 * @param string $s_content
	 *
	 * @return stdClass
	 */
	public function filterTags( $s_content ) {


		$i_len									= strlen( $s_content );


		$i_tag_nr								= 1;
		$a_tags									= array();

		$s_parsed_text							= "";



		$str									= $s_content;
		for ( $i_pos = 0; $i_pos < $i_len; $i_pos++ ) {


			// ------------------------------------------------------------------------------------------------
			// kein Tag geöffnet -> suchen
			if ( !$this->b_tagOpen ) {

				#$str							= substr( $s_content, $i_pos );
				$i_tag_pos						= $this->_searchTagStart( $str );

				// ------------------------------------------------------------------------------------------------
				// öffnendes Tag
				if ( $i_tag_pos !== false ) {


					// ------------------------------------------------------------------------------------------------
					// bisherigen Text merken
					if ( $i_tag_pos > 0 ) {
						$s_bisherigerText		= substr( $str, 0, $i_tag_pos );
						$s_parsed_text			.= $s_bisherigerText;

						$str					= substr( $str, $i_tag_pos );
						$i_len					= strlen( $str );

					}
					// ------------------------------------------------------------------------------------------------


					$i_pos						= 0;

				}
				// ------------------------------------------------------------------------------------------------

			}
			// ------------------------------------------------------------------------------------------------


			// ------------------------------------------------------------------------------------------------
			// Tag geöffnet -> schliessendes suchen
			if ( $this->b_tagOpen ) {

				$i_tag_pos						= $this->_searchTagEnd( $str );


				// ------------------------------------------------------------------------------------------------
				// schliessendes Tag
				if ( $i_tag_pos !== false ) {


					// ------------------------------------------------------------------------------------------------
					// Tag entfernen -> Platzhalter einfügen
					$i_tag_start				= $i_pos;
					$i_tag_len					= $i_tag_pos;
					$s_tag_str					= substr( $str, $i_tag_start, $i_tag_len );
					array_push( $a_tags, $s_tag_str );
					// ------------------------------------------------------------------------------------------------


					$s_parsed_text				.= "_#" . $i_tag_nr++ . "#_";


					$str						= substr( $str, $i_tag_pos );
					$i_len						= strlen( $str );


					$i_pos						= 0;

				}

			}
			// ------------------------------------------------------------------------------------------------

		}



		// ------------------------------------------------------------------------------------------------
		// optimieren / Tags zusammenfassen
		foreach ( $a_tags as $i_key => $s_tag ) {

			$o_ret								= $this->_combineDoubleTags( $s_parsed_text, $a_tags );

			$s_parsed_text						= $o_ret->parsedText;
			$a_tags								= $o_ret->tagsArray;


			$o_ret								= $this->_combineNextLineTags( $s_parsed_text, $a_tags );

			$s_parsed_text						= $o_ret->parsedText;
			$a_tags								= $o_ret->tagsArray;

		}
		// ------------------------------------------------------------------------------------------------



		$o_ret									= new stdClass();
		$o_ret->parsedText						= $s_parsed_text;
		$o_ret->tagsArray						= $a_tags;


		return $o_ret;
	}

	// ------------------------------------------------------------------------------------------------

	protected function _combineDoubleTags( $s_parsed_text, $a_tags ) {


		$i_end									= count( $a_tags );

		for ( $i_key = 0; $i_key < $i_end; $i_key++ ) {

			$i_tag_nr							= $i_key + 1;


			$s_search							= "_#" . $i_tag_nr . "#__#" . ( $i_tag_nr + 1 ) . "#_";

			if ( stristr( $s_parsed_text, $s_search ) ) {

				// beide kombinieren

				$s_replace						= "_#" . $i_tag_nr . "#_";

				// platzhalter ändern
				$s_parsed_text					= str_replace( $s_search, $s_replace, $s_parsed_text );


				// Tag-Array ändern
				$a_tags[ $i_key ]				.= $a_tags[ $i_key + 1 ];
				unset( $a_tags[ $i_key + 1 ] );

				// neu indizieren
				$a_tags							= array_merge( $a_tags );


				// Platzhalter neu nummerieren
				for ( $i = $i_key + 2; $i <= $i_end; $i++ ) {

					$s_search					= "_#" . $i . "#_";
					$s_replace					= "_#" . ( $i - 1 ) . "#_";

					$s_parsed_text				= str_replace( $s_search, $s_replace, $s_parsed_text );

				}

				// Schleife beenden
				$i_key							= $i_end;
			}

		}


		$o_ret									= new stdClass();
		$o_ret->parsedText						= $s_parsed_text;
		$o_ret->tagsArray						= $a_tags;

		return $o_ret;
	}

	// ------------------------------------------------------------------------------------------------

	protected function _combineNextLineTags( $s_parsed_text, $a_tags ) {


		$i_end									= count( $a_tags );

		for ( $i_key = 0; $i_key < $i_end; $i_key++ ) {

			$i_tag_nr							= $i_key + 1;


			$s_search							= "_#" . $i_tag_nr . "#_\r\n_#" . ( $i_tag_nr + 1 ) . "#_";

			if ( stristr( $s_parsed_text, $s_search ) ) {

				// beide Tags kombinieren
				$s_replace						= "_#" . $i_tag_nr . "#_";

				// platzhalter ändern
				$s_parsed_text					= str_replace( $s_search, $s_replace, $s_parsed_text );


				// Tag-Array ändern
				$a_tags[ $i_key ]				.= "\r\n" . $a_tags[ $i_key + 1 ];
				unset( $a_tags[ $i_key + 1 ] );

				// neu indizieren
				$a_tags							= array_merge( $a_tags );


				// Platzhalter neu nummerieren
				for ( $i = $i_key + 2; $i <= $i_end; $i++ ) {

					$s_search					= "_#" . $i . "#_";
					$s_replace					= "_#" . ( $i - 1 ) . "#_";

					$s_parsed_text				= str_replace( $s_search, $s_replace, $s_parsed_text );

				}

				// Schleife beenden
				$i_key							= $i_end;
			}

		}


		$o_ret									= new stdClass();
		$o_ret->parsedText						= $s_parsed_text;
		$o_ret->tagsArray						= $a_tags;

		return $o_ret;
	}
	// ------------------------------------------------------------------------------------------------

	/**
	 * @param string $s_contentWithTags
	 * @param string $s_contentParsed
	 *
	 * @return string
	 */
	public function restoreTags( $s_contentWithTags, $s_contentParsed ) {


		// $s_contentWithTags parsen
		$o_ret__parser							= $this->filterTags( $s_contentWithTags );
		$a_tags									= $o_ret__parser->tagsArray;


		// search/replace mittels $a_tags->Array
		$s_content_tagsRestored					= $s_contentParsed;
		foreach ( $a_tags as $i_tag_nr => $s_tag_content ) {

			$s_content_tagsRestored					= str_replace( "_#" . ( $i_tag_nr + 1 ). "#_", $s_tag_content, $s_content_tagsRestored );

		}


		return $s_content_tagsRestored;
	}

	// ------------------------------------------------------------------------------------------------

	protected function _searchTagStart( $str ) {


		$i_len									= strlen( $str );
		for ( $i = 0; $i < $i_len; $i++ ) {

			// ------------------------------------------------------------------------------------------------
			// Smarty
			if ( isset( $str[ $i + 1 ] ) && ( $str[ $i ] === "[" ) && ( $str[ $i + 1 ] === "{" ) ) {

				$this->b_tagOpen				= true;

				return $i;

			// ------------------------------------------------------------------------------------------------
			// HTML
			} else if ( $str[ $i ] === "<" ) {

				$this->b_tagOpen				= true;

				$this->b_isHtmlTag				= true;

				return $i;
			}

		}


		return false;
	}

	// ------------------------------------------------------------------------------------------------

	protected function _searchTagEnd( $str ) {


		$b_searchSmarty							= true;


		// ------------------------------------------------------------------------------------------------
		// optional nur HTML-Tag suchen
		if ( $this->b_isHtmlTag ) {
			$b_searchHtml						= true;
			$b_searchSmarty						= false;
		} else {
			$b_searchHtml						= false;
		}
		// ------------------------------------------------------------------------------------------------


		// ------------------------------------------------------------------------------------------------
		// in String nach Tag-Zeichen suchen
		$i_len									= strlen( $str );
		for ( $i = 0; $i < $i_len; $i++ ) {

			// ------------------------------------------------------------------------------------------------
			// Smarty
			if ( $b_searchSmarty && isset( $str[ $i + 1 ] ) && ( $str[ $i ] === "}" ) && ( $str[ $i + 1 ] === "]" ) ) {

				$this->b_tagOpen				= false;

				return $i + 2;

			// ------------------------------------------------------------------------------------------------
			// HTML
			} else if ( $b_searchHtml && ( $str[ $i ] === ">" ) && ( $i > 0 && ( $str[ $i - 1 ] !== "-" ) ) ) {

				$this->b_tagOpen				= false;

				$this->b_isHtmlTag				= false;

				return $i + 1;
			}

		}
		// ------------------------------------------------------------------------------------------------


		return false;
	}

	// ------------------------------------------------------------------------------------------------

}

// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------
