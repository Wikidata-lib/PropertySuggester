/**
 * override usual entityselector and replace _request and _create
 * if a property is requested and we are on an Entity page.
 *
 * @see ui.suggester._request
 */

( function( $, util, mw ) {
	'use strict';

	var Item = $.wikibase.entityselector.Item;

	/**
	 * This widget overrides the wikibase.entityselector and extends it with functionality to
	 * suggest matching properties instead of just searching for them.
	 */
	$.widget( 'wikibase.entityselector', $.wikibase.entityselector, {

		_oldCreate: $.wikibase.entityselector.prototype._create,

		_create: function() {
			var self = this;

			self._oldCreate.apply( self, arguments );

			var focusHandler = function( event ) {
				if( self._useSuggester() && self.element.val() === ''
					&& !self.options.menu.element.is( ":visible" ) ) {
					self._minTermLength = 0;
					self._cache = {}; // is done in the entityselector on eachchange too
					self.search( event );
				}
			};
			self.element.on( 'focus', focusHandler );
		},

		_oldGetData: $.wikibase.entityselector.prototype._getData,

		/**
		 *
		 * @param {string} term
		 * @return {Object}
		 */
		_getData: function( term ) {
			var self = this;

			if( !self._useSuggester() ) {
				return self._oldGetData( term )
			} else {
				var data = {
					action: 'wbsgetsuggestions',
					search: term,
					context: this._getPropertyContext(),
					format: 'json',
					language: self.options.language,
					type: self.options.type,
					'continue': self._cache[term] && self._cache[term].nextSuggestionOffset
						? self._cache[term].nextSuggestionOffset : 0
				};
				if( data.context == 'item' ) {
					data.entity = self._getEntity().getId();
				} else {
					data.properties = self._getPropertyId();
				}
				return data;
			}
		},

		/**
		 * @return {boolean}
		 */
		_useSuggester: function() {
			var entity = this._getEntity();
			return this.options.type === 'property' && entity !== null && entity.getType() === 'item';
		},

		/**
		 * Get the entity from the surrounding entityview or return null
		 * @return {wikibase.Entity|null}
		 */
		_getEntity: function() {
			try {
				var $entityView = this.element.closest( ':wikibase-entityview' );
			} catch( e ) {
				return null;
			}
			var entity = $entityView.length > 0 ? $entityView.data( 'entityview' ).option( 'value' ) : null;
			if( entity ) {
				return entity;
			} else {
				return null;
			}
		},

		/**
		 * Returns the property id for the enclosing statementview or null if no property is selected yet.
		 *
		 * @return {string|null}
		 */
		_getPropertyId: function() {
			try {
				var $statementview = this.element.closest( ':wikibase-statementview' );
			} catch( e ) {
				return null;
			}
			var statement = $statementview.length > 0 ? $statementview.data( 'statementview' ).option( 'value' ) : null;
			if( statement ) {
				return statement.getClaim().getMainSnak().getPropertyId();
			} else {
				return null;
			}
		},

		/**
		 * Returns either 'item', 'qualifier', 'reference' or null depending on the context of the entityselector.
		 * 'item' is returned in case that the selector is for a new property in an item.
		 *
		 * @return {string}
		 */
		_getPropertyContext: function() {
			if( this._isInNewStatementView() ) {
				return 'item';
			} else if( this._isQualifier() ) {
				return 'qualifier';
			} else if( this._isReference() ) {
				return 'reference'
			} else {
				return null;
			}
		},

		/**
		 * @return {boolean}
		 */
		_isQualifier: function() {
			var $statementview = this.element.closest( ':wikibase-statementview' );
			var statementview = $statementview.data( 'statementview' );
			if( !statementview ) {
				return false;
			}
			return this.element.closest( statementview.$qualifiers ).length > 0;
		},

		/**
		 * @return {boolean}
		 */
		_isReference: function() {
			var $referenceview = this.element.closest( ':wikibase-referenceview' );
			return $referenceview.length > 0;
		},

		/**
		 * detect if this is a new statement view.
		 * @return {boolean}
		 */
		_isInNewStatementView: function() {
			var $statementview = this.element.closest( ':wikibase-statementview' );
			var value = ( $statementview.length > 0 )
				? $statementview.data( 'statementview' ).option( 'value' )
				: null;
			return value === null;
		}
	} );

	$.extend( $.wikibase.entityselector, {
		Item: Item
	} );

}( jQuery, util, mediaWiki ) );
