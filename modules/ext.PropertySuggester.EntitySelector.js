
/**
 * override usual entityselector and replace _request and _create
 * if a property is requested and we are on an Entity page.
 *
 * @see ui.suggester._request
 */
$.widget( 'wikibase.entityselector', $.wikibase.entityselector, {

	_oldCreate: $.wikibase.entityselector.prototype._create,

	_create: function() {
		var self = this;

		self._oldCreate.apply(self, arguments);

		var inputHandler = function() {
			if ( self.__useSuggester() && self.value() === '' ) {
				self.search( '*' );
			}
		};
		self.element.on( 'input.' + this.widgetName, inputHandler );

		var focusHandler = function() {
			if ( self.__useSuggester() && self.value() === '' && !self.menu.element.is( ':visible' ) ) {
				self.search( '*' );
			}
		};
		self.element.on( 'focus', focusHandler );

	},

	_oldRequest: $.wikibase.entityselector.prototype._request,

	_request: function( request, suggest ) {
		if ( this.__useSuggester() ) {
			this._term = request.term;
			if ( !this._continueSearch ) {
				this.offset = 0;
			}

			$.extend( this.options.ajax, this.__buildOptions() );
			if ( this.options.limit !== null ) {
				this.options.ajax.params.limit = this.options.limit;
			}
			$.ui.suggester.prototype._request.apply( this, arguments );

		} else {
			this._oldRequest.apply( this, arguments );
		}
	}, 

	__useSuggester: function() {
		return this.options.type === 'property' && this.__getEntityId();
	},

	__getEntityId: function() {
		var $entityView = this.element.closest( ':wikibase-entityview');
		entity = $entityView.length > 0 ? $entityView.data( 'entityview' ).option( 'value' ) : null;
		if( entity ) {
			return entity.getId();
		} else {
			return null;
		}
	},

	__buildOptions: function() {
		var params = {
			url: this.options.url,
			timeout: this.options.timeout,
			params: {
				action: 'wbsgetsuggestions',
				entity: this.__getEntityId(),
				format: 'json',
				language: this.options.language,
				type: this.options.type,
				'continue': this.offset
			}
		}
		return params;
	}

 });
