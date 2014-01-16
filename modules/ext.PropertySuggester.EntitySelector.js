
// $.widget( 'wikibase.entityselector2', $.wikibase.entityselector, {
 $.widget( 'wikibase.entityselector', $.wikibase.entityselector, {
            
            _oldRequest: $.wikibase.entityselector.prototype._request,
    
            /**
             * override usual entityselector and replace _request if a property
             * is requested and we are on an Entity page.
             * 
             * @see ui.suggester._request
             */
            _request: function( request, suggest ) {
                if (this.options.type === "property" && typeof wbEntity !== 'undefined') {
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
                    _oldRequest.apply( this, arguments );
                }
            }, 
     
 });
