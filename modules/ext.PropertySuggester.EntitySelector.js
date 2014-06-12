/**
 * override usual entityselector and replace _request and _create
 * if a property is requested and we are on an Entity page.
 *
 * @see ui.suggester._request
 */

( function( $, util, mw ) {
	'use strict';

	var Item = $.wikibase.entityselector.Item;

	$.widget( 'wikibase.entityselector', $.wikibase.entityselector, {

		_oldCreate: $.wikibase.entityselector.prototype._create,

		_create: function() {
			var self = this;

			self._oldCreate.apply(self, arguments);

			var focusHandler = function( event ) {
				if ( self.__useSuggester() && self.element.val() === ''
					 && !self.options.menu.element.is( ":visible" ) ) {
					self._minTermLength = 0;
					self._cache = {}; // is done in the entityselector on eachchange too
					self.search( event );
			}
			};
			self.element.on( 'focus', focusHandler );

		},

		_oldGetData: $.wikibase.entityselector.prototype._getData,

		_getData: function( term ) {
			var self = this;

			if ( !self.__useSuggester() ) {
				return self._oldGetData( term )
			} else {
				return {
					action: 'wbsgetsuggestions',
					search: term,
					entity: self.__getEntity().getId(),
					format: 'json',
					language: self.options.language,
					type: self.options.type,
					'continue': self._cache[term] && self._cache[term].nextSuggestionOffset
						? self._cache[term].nextSuggestionOffset: 0
				};
			}
		},

		__useSuggester: function() {
			var entity = this.__getEntity();
			return this.options.type === 'property' && entity && entity.getType() === 'item' && this.__isInNewStatementView();
		},

		__getEntity: function() {
			try {
				var $entityView = this.element.closest( ':wikibase-entityview' );
			} catch (e) {
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
		 * only entityselectors in statements describing the item should get suggestions.
		 * this is hopefully exactly the case if the statement has no value.
		 * other entityselectors like source and quantifier should not get suggestions since
		 * they already have a statementview with a value.
		 */
		__isInNewStatementView: function() {
			var $statementView =  this.element.closest( ':wikibase-statementview' );
			var value = $statementView.length > 0 ? $statementView.data( 'statementview' ).option( 'value' ) : null;
			return value === null;
		}
	 });

	$.extend( $.wikibase.entityselector, {
		Item: Item
	} );

}( jQuery, util, mediaWiki ) );
