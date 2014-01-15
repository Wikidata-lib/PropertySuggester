
// $.widget( 'wikibase.entityselector2', $.wikibase.entityselector, {
 $.widget( 'wikibase.entityselector', $.wikibase.entityselector, {
            /**
             * override usual entityselector 
             * replace action and add current entity
             * 
             * @see ui.suggester._request
             */
            _request: function( request, suggest ) {
                    this._term = request.term;
                    if ( !this._continueSearch ) {
                            this.offset = 0;
                    }

                    $.extend( this.options.ajax, {
                            url: this.options.url,
                            timeout: this.options.timeout,
                            params: {
                                    action: 'wbsearchentities',
                                    entity: 'Q42',
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
            }, 
     
 });
