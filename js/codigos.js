
// oculatar div despues de cierto tiempo
$(".spinner-grow").show();
                setTimeout(function() {
                    $(".spinner-grow").hide();
                    $("#register").attr('disabled', false); //Desabilito el boton enviar
                }, 10000);