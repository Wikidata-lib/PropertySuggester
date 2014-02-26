
var selected_ids = [];

function removeFromArray(arr, element) {
	 arr.splice($.inArray(element, arr),1);
 }
 
function deleteFromList(evt){
	var pid = evt.data;
	removeFromArray(selected_ids, pid);
	$(this).closest('li').remove();
	doQuery();
	return false;
}

function handleInput () {
    var propertyChooser = $( '#property-chooser');
	var input_text =  propertyChooser.val();
	var pid = propertyChooser.next('input').val();
	if (input_text!==  '' && pid !== ''){	
		selected_ids.push(pid);
		var delete_link = $('<a href="#"> x </a>').click(pid, deleteFromList);
		var li_element = $('<li>' + input_text + ' (' + pid + ')' + '</input></li>');
		li_element.append(delete_link);
		$('#selected-properties-list').append(li_element);
		propertyChooser.val('').focus();
		doQuery();
	}
}

function doQuery() {
	var url = mw.util.wikiScript( 'api' ) + '?action=wbsgetsuggestions&format=json&properties=' +
			selected_ids.map(encodeURIComponent).join(',') + '&limit=20&language=' + wgPageContentLanguage;
	$.get(url, function( data ) {
		$('#result').html('<h3>Suggestions:</h3>');
		var suggestions = data['search'];
		$.each(suggestions, function (k, v) {
			$('#result').append(JSON.stringify(v) + '<br>');
		});
	});
}

$( document ).ready(function (){
	var propertyChooser = $( '#property-chooser' );
    propertyChooser.entityselector({
		url: mw.util.wikiScript( 'api' ),
		selectOnAutocomplete: true, 
		type: 'property'
	});
	
	$( '#add-property-btn' ).click(function() {
		handleInput();
	});


    propertyChooser.keyup(function (e) {
		if (e.keyCode === 13) {
			handleInput();
		}
	});
	
});