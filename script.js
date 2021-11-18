$(document).ready(() => {

	$('#documentacao').on('click', () =>{
        //$('#pagina').load('documentacao.html')
        $.get('documentacao.html', data =>{
            $('#pagina').html(data)
        })
    })

    $('#suporte').on('click', () =>{
        $('#pagina').load('suporte.html')
    })


    //AJAX
    $('#competencia').on('change', e =>{

        let competencia = $(e.target).val()

        $.ajax({
            type: 'GET',
            url: 'app.php',
            data: `competencia=${competencia}`,
            dataType: 'json',
            success: dados => {
                $('#numeroVendas').html(dados.numeroVendas)
                $('#totalVendas').html(dados.totalVendas)
                $('#clientesAtivos').html(dados.clientesAtivos)
                $('#clientesInativos').html(dados.clientesInativos)

            },
            error: erro => { console.log(erro)}
        })
    })
})