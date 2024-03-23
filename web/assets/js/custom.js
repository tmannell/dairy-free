// makes audio player appear when 'ear' is clicked

$(function() {

    $(document).ready(function() {
        let $media_type = $('select[name="media_type"]');
        addPageConditionals($media_type);

    })

    $("#listen").click(function() {
        $("#audio-player").fadeToggle(1000)
    });


    $('select[name="media_type"]').on('change', function() {
        addPageConditionals($(this))
    })

});

function addPageConditionals(media_type) {
    if (media_type.val() === 'link') {
        $('#page-link').show()
        $('input[name="page_media"]').hide()
        $('label[for="page_media"]').hide()
    }

    if (media_type.val() === 'audio') {
        $('input[name="page_media"]').show()
        $('label[for="page_media"]').show()
        $('#page-link').hide()
    }

    if (media_type.val() === '0') {
        $('input[name="page_media"]').hide()
        $('label[for="page_media"]').hide()
        $('#page-link').hide()
    }
}
