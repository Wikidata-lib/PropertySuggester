
// var selected_ids = [];


$( document ).ready(function (){
    $( 'input.ui-autocomplete-input' ).entityselector({
             url: mw.util.wikiScript( 'api' ),
             selectOnAutocomplete: true, 
             type: 'property'
    });
    
    $( "#add-property-btn" ).click(function() {
        alert( $( "#property-chooser").val() );
        //$("#property-chooser").next("input").val()
        //$("#selected-properties-list").append("<li>b</li>")
    });
    
    // delete from list
    
});