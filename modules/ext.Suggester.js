 var selected_ids = [];
function deleteFromList(source){
    alert(1);
        
    return false;
}
function handleInput () {
    input_text =  $( "#property-chooser").val();
    input_id = $("#property-chooser").next("input").val();
    if (input_text!=  "" && input_id != ""){    
        selected_ids.push(input_id);
        delete_link = " <a href='#' onclick=\"deleteFromList(this)\"> x </a> ";
        li_element = "<li>" + input_text + " (" + input_id + ")" + delete_link + "</input></li>";
        $("#selected-properties-list").append(li_element);
        $( "#property-chooser").val('');
        doQuery()
    }
    //\\\"" + input_id + "\\\"
}
//onclick='deleteFromList(this)


function doQuery() {
    url = "api.php?properties=" + encodeURIComponent(selected_ids.join(","));
    $.get("http://localhost/devrepo/api.php?action=wbsearchentities&format=json&language=de&type=property&search=v", function( data ) {
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
    if (e.keyCode == 13) {
        handleInput();
    
    }
});
    
    // delete from list
    
});