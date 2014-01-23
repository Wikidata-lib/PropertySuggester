
$.widget( 'wikibase.entityselector', $.wikibase.entityselector, {
	/**
	 * override usual entityselector and replace _request and _create 
	 * if a property is requested and we are on an Entity page.
	 * 
	 * @see ui.suggester._request
	 */	

	_oldCreate: $.wikibase.entityselector.prototype._create,

	_create: function() {
		var self = this;

		self._oldCreate.apply(self, arguments);

		if (self.__useSuggester()) {
			var handler = function() {
				if (self.value() === '') {
					self.search('*');
				}
			};

			self.element.focus(handler);
			self.element.bind('input', handler);
		}
	},

	_oldRequest: $.wikibase.entityselector.prototype._request,

	_request: function( request, suggest ) {
		if (this.__useSuggester() && !$.isEmptyObject(JSON.parse(wbEntity).claims)) {
			this._term = request.term;
			if ( !this._continueSearch ) {
					this.offset = 0;
			}

			$.extend( this.options.ajax, {
				url: this.options.url,
				timeout: this.options.timeout,
				params: {
					action: 'wbsgetsuggestions',
					entity: wbEntityId,
					format: 'json',
					language: this.options.language,
					type: this.options.type,
					'continue': this.offset
				}
			} );
			if ( this.options.limit !== null ) {
				this.options.ajax.params.limit = this.options.limit;
			}
			$.ui.suggester.prototype._request.apply( this, arguments );
		} else {
			this._oldRequest.apply( this, arguments );
		}
	}, 

	__useSuggester: function() {
		return this.options.type === 'property' && typeof wbEntityId !== 'undefined';
	},
 });
