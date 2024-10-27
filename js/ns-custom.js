jQuery(function($){

    var row_tmpl_empty = "<tr class='redir-row'>\
        <td width='45%'>\
        <input type='text' class='form-control' name='redir_terms' placeholder='Term,keyword,a expression,comma separated always' value='{{redir_terms}}'/>\
        </td>\
        <td width='45%'>\
        <input type='text' class='form-control' name='redir_url' placeholder='Url to redirect search term' value='{{redir_url}}'/>\
        </td>\
        <td width='auto'><button class='delete btn btn-delete'><span class='dashicons dashicons-no'></span></button></td>\
    </tr>";

    var settedRedirects = $('input[name="artesans_search_redirect_field1"]').val();

    if (settedRedirects!="") {
        var redirectsList = settedRedirects.split('[REDIRECTION]');
        var totalRedirs = redirectsList.length;

        redirectsList.forEach( function(valor, indice, array) {
            if(valor!="") {
                var new_row = row_tmpl_empty;
                valor=valor.split('[URL]');
                new_row = new_row.replace('{{redir_terms}}',valor[0]);
                new_row = new_row.replace('{{redir_url}}',valor[1]);
                $('#search_redirections_table').append(new_row);
                enableDeleters();
            }
        });
    }

    $("#add").click(function(){
        var new_row = row_tmpl_empty;
        new_row = new_row.replace('{{redir_terms}}','');
        new_row = new_row.replace('{{redir_url}}','');
        $('#search_redirections_table').append(new_row);
        enableDeleters();
    });

    function enableDeleters() {
        $(".delete").click(function(){
            $(this).parent().parent().remove();
        });
    }

    $("#save").click(function(){
        var plg_settings = "";
        $('.redir-row').each(function() {
            var keywords = $(this).find('input[name="redir_terms"]').val();
            if (keywords!="") {
                plg_settings+="[REDIRECTION]"+keywords;
            }
            var url = $(this).find('input[name="redir_url"]').val();
            if (url!="") {
                plg_settings+="[URL]"+url;
            }
            
            
        });
        
        $('input[name="artesans_search_redirect_field1"]').val(plg_settings);
        $('#artesans_settings_redirect_form input[type="submit"]').click();
        
        
        });

    
});
    