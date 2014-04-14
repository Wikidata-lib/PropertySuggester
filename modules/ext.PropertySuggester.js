$(document).ready(function () {
    var entityChooser = $('#entity-chooser');
    entityChooser.entityselector({
        url: mw.util.wikiScript('api'),
        selectOnAutocomplete: true,
        type: 'property'
    });

    $(".button").on("click", function () {
        var $this = $(this);
        $this.siblings(".button").removeClass("selected");
        $this.addClass("selected");
    });

    $('#submit-button').on("click", function () {
        var $selected = $(".suggestion_evaluation .selected");
        var ratings = [];

        $selected.each(function () {
            var $this = $(this);
            var id = $this.parents("li").data("property");
            var label = $this.parents("li").data('label');
            var rating = $this.data('rating');
            ratings.push( {'id': id, 'label':label, 'rating': rating } );
        });

        console.log(ratings);

        var properties = [];
        $props = $(".property-entries li");
        $props.each(function () {
            var $this = $(this);
            var id = $this.data("property");
            var label = $this.data('label');
            properties.push({'id': id,'label':label})
        });
        console.log(properties);
        var  entry_id  = $(".entry").data("entry-id");
        submitJson(  entry_id,properties,ratings);


    })
});


function submitJson(entry_id,properties,ratings) {
    var evaluations = {
        entity: entry_id,
        properties: properties,
        suggestions: ratings
    };

    console.log(evaluations);
    $('input#result').val( JSON.stringify(evaluations) );
    $('#form').submit();
}

//var id = k.parent("div").data("property");