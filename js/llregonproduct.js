// LeadLovers options.
// 

jQuery(function ($) 
{
    // Mostra/esconde as opções de produto LeadLovers conforme o checkbox
    $('input#_leadlovers_integration_check')
        .on( 'change' , function() {
        if($(this).is(':checked')) 
        {
            //mostra o div com o select e o botão
            $( 'div.leadlovers_integration_options' ).show();
            //desabilita o text do sku
            //$( "#_sku" ).prop( "disabled", true );
        }
        else
        {
            $( 'div.leadlovers_integration_options' ).hide();
            //$( "#_sku" ).prop( "disabled", false );
        }
    }).trigger( 'change' );

    // Mostra/esconde as opções de Máquina LeadLovers conforme o checkbox
    $('input#_leadlovers_machine_check')
        .on( 'change' , function() {
        if($(this).is(':checked')) 
        {
            //mostra o div com o select e o botão
            $( 'div.leadlovers_machine_options' ).show();
            //desabilita o text do sku
            //$( "#_sku" ).prop( "disabled", true );
        }
        else
        {
            $( 'div.leadlovers_machine_options' ).hide();
            //$( "#_sku" ).prop( "disabled", false );
        }
    }).trigger( 'change' );

    
    $('#_leadlovers_integration_machine').on('change', function()
    {
        $('#_leadlovers_integration_sequence').prop('disabled', true);
        $('#_leadlovers_integration_level').prop('disabled', true);

        var selectedValue = $(this).val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                'action': 'get_leadlovers_sequence_list',
                'machine_id': selectedValue,
            },
            success: function(result){
                $('#_leadlovers_integration_sequence').html(result);
                $('#_leadlovers_integration_sequence').prop('disabled', false);
                //$('#_leadlovers_integration_sequence').css('opacity', '1');
                },
            error: function(result){
                alert('Erro no ajax!!');
            }
        });
    });

    // Evento disparado ao mudar o conteúdo do select SEQUENCE

    $('#_leadlovers_integration_sequence').on('change', function()
    {
        $('#_leadlovers_integration_level').prop('disabled', true);
     
        var selectedValue = $(this).val();
        // alert(selectedValue + ' e ' + $('#gf_leadlovers_machine'));
        $.ajax({
            url: ajaxurl,
            type: 'POST',        //Não consegui usar modo POST, não sei pq...
            dataType: 'json',
            data: {
                'action': 'get_leadlovers_level_list',
                'sequence_id': selectedValue,
                'machine_id' : $('#_leadlovers_integration_machine').val()
            },
            success: function(result){
                 $('#_leadlovers_integration_level').html(result);
                 $('#_leadlovers_integration_level').prop('disabled', false);
            },
            error: function(result){
                alert('Erro no ajax!');
            }
        });
    });
});
 