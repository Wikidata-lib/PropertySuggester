 var selected_ids = [];
 
 

 $.widget( 'wikibase.entityselector', $.wikibase.entityselector, {
            
            _old_request: $.wikibase.entityselector.prototype._request
            ,
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
 
 function removeFromArray(arr, element) {
     arr.splice($.inArray(element, arr),1);
 }
 
function deleteFromList(evt){
    pid = evt.data;
    removeFromArray(selected_ids, pid);
    $(this).closest("li").remove();
    doQuery();
    return false;
}

function handleInput () {
    input_text =  $( "#property-chooser").val();
    pid = $("#property-chooser").next("input").val();
    if (input_text!==  "" && pid !== ""){    
        selected_ids.push(pid);
        delete_link = $("<a href='#'> x </a>").click(pid, deleteFromList);
        li_element = $("<li>" + input_text + " (" + pid + ")" + "</input></li>");
        li_element.append(delete_link);
        $("#selected-properties-list").append(li_element);
        $( "#property-chooser").val('').focus();
        doQuery();
    }
}

function doQuery() {
    url = mw.util.wikiScript( 'api' ) + "?action=wbsgetsuggestions&format=json&properties=" + selected_ids.map(encodeURIComponent).join(",") + "&language=" + wgPageContentLanguage;
    $.get(url, function( data ) {
        $("#result").html("<h3>Suggestions:</h3>");
        suggestions = data["suggestions"];
        $.each(suggestions, function (k, v) {
            $("#result").append(JSON.stringify(v) + "<br>");
        });
    });
}

$( document ).ready(function (){
    $( '#property-chooser' ).entityselector({
             url: mw.util.wikiScript( 'api' ),
             selectOnAutocomplete: true, 
             type: 'property'
    });
    
    $( '#add-property-btn' ).click(function() {
        handleInput();
    });
    

    $( '#property-chooser' ).keyup(function (e) {
        if (e.keyCode === 13) {
            handleInput();
        }
    });
    
});