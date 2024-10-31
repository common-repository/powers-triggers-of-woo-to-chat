jQuery(document).ready(function($) { 
    //abrir media
    
    $('.pac_open_media').on('click', function( event ){
        var file_frame;
        var wp_media_post_id = wp.media.model.settings.post.id; 
        var set_to_post_id = 0; 
        var targetURL;
        event.preventDefault();
        targetURL = $('#'+$(this).attr('data-url-target'));
        if ( file_frame ) {
            file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
            file_frame.open();
            return;
        } else {
            wp.media.model.settings.post.id = set_to_post_id;
        }
        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Selecione a Imagem ou Arquivo',
            button: {
                text: 'Usar este arquivo',
            },
            allowLocalEdits: false,
            displaySettings: false,
            displayUserSettings: false,
            type : ['image','application/pdf'],
            multiple: false
        });
        
        file_frame.on( 'select', function() {
            attachment = file_frame.state().get('selection').first().toJSON();
            $( targetURL ).val( attachment.url );
            wp.media.model.settings.post.id = wp_media_post_id;
        });
        file_frame.open();
    });


    $( "#wwp_form-token" ).submit(function( event ) {
        var ajaxurl = $("#ajaxurl").val();
        var licenca = $("#licenca-input").val();
        var key = $("#key-input").val();
        var code = $("#countryCode").val();
        var testnumber = $("#test-number").val();
        toggleLoad('wwp-btn-salvar');
        $.ajax({
            url: ajaxurl, 
            type: 'POST',
            data: {
                'action': 'wwp_save_settings', 
                'wwp_licenca': licenca,
                'wwp_key': key,
                'wwp_code': code,
                'wwp_testnumber': testnumber
            },
            success: function( data ){
               toggleLoad('wwp-btn-salvar');
               if (data == 'true') {
                     $('#wwp-btn-salvar').append('<span class="dashicons dashicons-yes-alt"></span>');
               } else{
                    $('#wwp-btn-salvar').append('<span class="dashicons dashicons-no"></span>');
                    alert('Dados salvos porém não foi possivel estabelecer uma conexão.');
               }
            }
        });
        return false;
    });


    function toggleLoad(id){
        var pluginurl = $("#pluginurl").val();
        id = '#'+id;
        $(id+' .dashicons').remove();
        if ($('#loadGiff').length) {
            $('#loadGiff').remove();
        } else {
            $(id).append('<img src="'+pluginurl+'/img/load.gif" id="loadGiff" />');
        }
    }

    function convertToSlug(Text){
        return Text
            .toLowerCase()
            .replace(/ /g,'-')
            .replace(/ +/g,'-')
            ;
    }

    if ($( "#accordionTriggers" ).length > 0 ) {
        var codedefault = $('#countryCode').attr('data-default');
        $('#countryCode').val(codedefault);
        $( "#accordionTriggers" ).accordion({
          heightStyle: "content"
        });
        $('#accordionTriggers input[type="checkbox"]').click(function(e) {
            e.stopPropagation();
        });
    }


    if ($( "#newactionjetbooking" ).length > 0 ) {
        $("#newactionjetbooking").change(function() {
            $("#fieldsetnewactionjetbooking").toggleClass("hide", !this.checked)
        });
        $(".slugacao").keyup(function(event) {
            $(this).val(convertToSlug($(this).val()));
        });
    }



    

    $(document).on('click', '#wwp-checkall-publico', function(event) {
        $('#wwp-table-publico input:checkbox').not(this).prop('checked', this.checked);
    });    
});

