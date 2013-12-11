 var selected_ids = [];
 
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
    if (input_text!=  "" && pid != ""){    
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
    url = mw.util.wikiScript( 'api' ) + "?action=wbsgetsuggestions&format=json&properties=" + selected_ids.map(encodeURIComponent).join(",");
    $.get(url, function( data ) {
        $("#result").text(JSON.stringify(data));
    });
}

$( document ).ready(function (){
    $( 'input.ui-autocomplete-input' ).entityselector({
             url: mw.util.wikiScript( 'api' ),
             selectOnAutocomplete: true, 
             type: 'property'
    });
    
    $( "#add-property-btn" ).click(function() {
        handleInput();
    });
    

    $("input.ui-autocomplete-input").keyup(function (e) {
        if (e.keyCode === 13) {
            handleInput();
        }
    });
    
});