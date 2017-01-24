/**
 * override usual entityselector and replace _request and _create
 * if a property is requested and we are on an Entity page.
 *
 * @see ui.suggester._request
 */

( function ( $ ) {
	'use strict';

	var Item = $.wikibase.entityselector.Item;

	/**
	 * This widget overrides the wikibase.entityselector and extends it with functionality to
	 * suggest matching properties instead of just searching for them.
	 */
	$.widget( 'wikibase.entityselector', $.wikibase.entityselector, {

		/**
		 * @property {Function}
		 * @private
		 */
		_oldCreate: $.wikibase.entityselector.prototype._create,

		/**
		 * @inheritdoc
		 * @protected
		 */
		_create: function () {
			var self = this;

			this._oldCreate.apply( this, arguments );

			// Search for suggestions once the field is initially focused.
			// We only need to do this once, afterwards the old suggestions
			// will re-appear on focus anyway.
			this.element.one( 'focus', function ( event ) {
				if ( self._useSuggester()
					&& self.element.val() === ''
					&& !self.options.menu.element.is( ':visible' )
				) {
					self.options.minTermLength = 0;
					self._cache = {}; // is done in the entityselector on eachchange too
					self.search();
				}
			} );
		},

		/**
		 * @property {Function}
		 * @private
		 */
		_oldGetSearchApiParameters: $.wikibase.entityselector.prototype._getSearchApiParameters,

		/**
		 * @inheritdoc
		 * @protected
		 * @param {string} term
		 * @return {Object}
		 */
		_getSearchApiParameters: function ( term ) {
			var data;

			if ( !this._useSuggester() ) {
				return this._oldGetSearchApiParameters( term );
			}

			data = {
				action: 'wbsgetsuggestions',
				search: term,
				context: this._getPropertyContext(),
				format: 'json',
				language: this.options.language,
				'continue': this._cache.term === term && this._cache.nextSuggestionOffset
					? this._cache.nextSuggestionOffset
					: 0
			};

			if ( data.context === 'item' ) {
				data.entity = this._getEntity().getId();
			} else {
				data.properties = this._getPropertyId();
			}

			return data;
		},

		/**
		 * @private
		 * @return {boolean}
		 */
		_useSuggester: function () {
			var entity = this._getEntity();

			return this.options.type === 'property' && entity && entity.getType() === 'item';
		},

		/**
		 * Get the entity from the surrounding entityview or return null
		 *
		 * @private
		 * @return {wikibase.Entity|null}
		 */
		_getEntity: function () {
			var $entityView;

			try {
				$entityView = this.element.closest( ':wikibase-entityview' );
			} catch ( ex ) {
				return null;
			}

			return $entityView.length > 0
				? $entityView.data( 'entityview' ).option( 'value' )
				: null;
		},

		/**
		 * Returns the property id for the enclosing statementview or null if no property is
		 * selected yet.
		 *
		 * @private
		 * @return {string|null}
		 */
		_getPropertyId: function () {
			var $statementview,
				statement;

			try {
				$statementview = this.element.closest( ':wikibase-statementview' );
			} catch ( ex ) {
				return null;
			}

			statement = $statementview.length > 0
				? $statementview.data( 'statementview' ).option( 'value' )
				: null;

			return statement ? statement.getClaim().getMainSnak().getPropertyId() : null;
		},

		/**
		 * Returns either 'item', 'qualifier', 'reference' or null depending on the context of the
		 * entityselector. 'item' is returned in case that the selector is for a new property in an
		 * item.
		 *
		 * @private
		 * @return {string|null}
		 */
		_getPropertyContext: function () {
			if ( this._isInNewStatementView() ) {
				return 'item';
			} else if ( this._isQualifier() ) {
				return 'qualifier';
			} else if ( this._isReference() ) {
				return 'reference';
			} else {
				return null;
			}
		},

		/**
		 * @private
		 * @return {boolean}
		 */
		_isQualifier: function () {
			var $statementview = this.element.closest( ':wikibase-statementview' ),
				statementview = $statementview.data( 'statementview' );

			if ( !statementview ) {
				return false;
			}

			return this.element.closest( statementview.$qualifiers ).length > 0;
		},

		/**
		 * @private
		 * @return {boolean}
		 */
		_isReference: function () {
			var $referenceview = this.element.closest( ':wikibase-referenceview' );

			return $referenceview.length > 0;
		},

		/**
		 * detect if this is a new statement view.
		 *
		 * @private
		 * @return {boolean}
		 */
		_isInNewStatementView: function () {
			var $statementview = this.element.closest( ':wikibase-statementview' ),
				value = $statementview.length > 0
					? $statementview.data( 'statementview' ).option( 'value' )
					: null;

			return !value;
		}
	} );

	$.extend( $.wikibase.entityselector, {
		Item: Item
	} );

}( jQuery ) );
