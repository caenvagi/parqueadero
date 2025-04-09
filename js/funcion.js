$(document).ready(function(){     
        
    obtenerCards();  
    obtenerCardsdash()  
    $('#park-result').hide();
    
    function openIno() {
        window.open('../config/arqueo.php', '_self')
    };
    function openInf() {
        window.open("../modulos/imprimir_ticket_php/recibomens.php")
    };
        
        
        
    //ajax search parqueo
        $('#search').keyup(function(e){
            if($('#search').val()){ 
                let search = $('#search').val();
                $.ajax({
                    url: '../config/ajax/park-search.php',
                    type: 'POST',
                    data: { search }, 
                    success: function(response){                
                        let tasks = JSON.parse(response);
                        let template = '';

                        tasks.forEach(park => {
                            template += `
                            
                            <button id="btn_parqueo" name="btn_parqueo">

                                <span><img class="logo_parqueo" id="logo_parqueo" src="${park.cat_imagen}"></img></span>                    
                                <h7 class="placa_parqueo">${park.placa_cli} </h7> <br>
                                <h7 class="avisos_parqueo">Ingreso:</h7>
                                <h6>${park.fecha_ini}</h6>

                                <h7>tiempo:</h7>
                                <h6>${park.tiempo}</h6>                       

                                <h7>Valor por ${park.tar_tiempo}:</h7>
                                <h6>$ ${park.tarifas}</h6>
                                
                            </button>`
                        });
                        $('#container').html(template);
                        $('#park-result').show();
                        
                    }
                })
            }
        });
    // fin ajax search parqueo

    // ajax add parqueo 
        $('#parqueo').submit(function(e){ 
            cerrar();       
            const postData = {
                placa: $('#placa').val(),
                nombre: $('#nombre').val(),
                celular: $('#celular').val(),
                vehiculo: $('#vehiculo').val(),
                casetas: $('#casetas').val(),
                categoria: $('#categoria').val(),
                tarifas: $('#tarifas').val(),
                user: $('#user').val(),
            };
            $.post('../config/ajax/park-add.php',postData, function(response){ 
                console.log(response);
                if(response == 'abrir arqueo'){                   
                    alertify.error('¡Error! - No se ha abierto una caja');
                    openIno();
                }     
                if(response == 'existe en parqueo'){                   
                    alertify.error('¡Error! - Vehiculo se encuentra en el parqueadero.');
                } 
                if(response == 'guardado parqueo'){
                    alertify.success('¡Ok! - Vehiculo ingresado.');
                    function openInf() {
                        window.open("../modulos/imprimir_ticket_php/ticket.php")
                        };
                    openInf();    
                }  
                if(response == 'guardado cliente y parqueo'){
                    alertify.warning('¡Ok! - vehiculo y cliente registrado.'); 
                    function openInf() {
                        window.open("../modulos/imprimir_ticket_php/ticket.php")
                    };
                    openInf();              
                }                         
                obtenerCards(); 
                obtenerCardsdash()   
                $('#parqueo').trigger('reset');
                //location.reload();
            });    
            e.preventDefault();
        });    

        function cerrar(){ 
        setTimeout(function() {
            $('#contenedor-toasts').fadeIn(1000).delay(1000).fadeOut(5000);
        },); 
        };
    // fin ajax add parqueo

    // ajax add parqueo manual
    
        $('#parqueomanual').submit(function(e){ 
            cerrar();     
            const postData = {
                recibo: $('#recibo').val(),
                placa: $('#placa').val(),
                fecha_ini: $('#fecha_ini').val(),
                fecha_fin: $('#fecha_fin').val(),
                valor_manual: $('#valor_manual').val(),
                categoria: $('#categoria').val(),
                user: $('#user').val(),
                casetas: $('#casetas').val(),
                tarifas: "24", 
                periodo: "202301",                 
            };
            
            $.post('../config/ajax/park-manual.php',postData, function(response){ 
                console.log(response) ;
                console.log(postData) ;


                if(response == 'abrir arqueo'){                   
                    alertify.error('¡Error! - No se ha abierto una caja.');
                    openIno();
                } 
                if(response == 'ACTUALIZADO en pagar'){                   
                    alertify.error('¡Error! - Vehiculo se encuentra en el parqueadero.');
                } 
                if(response == 'guardado parqueo'){
                    alertify.success('¡Ok! - Vehiculo ingresado.');
                    // function openInf() {
                    //     window.open("../modulos/imprimir_ticket_php/ticket.php")
                    //     };
                    // openInf();    
                }  
                if(response == 'guardado cliente y parqueo'){
                    alertify.warning('¡Ok! - vehiculo y cliente registrado.'); 
                    // function openInf() {
                    //     window.open("../modulos/imprimir_ticket_php/ticket.php")
                    //     };
                    // openInf();              
                }                         
                $('#parqueomanual').trigger('reset') 
                
                //location.reload();
                
            });    
            e.preventDefault();
        });    
        
        function cerrar(){ 
        setTimeout(function() {
            $('#contenedor-toasts').fadeIn(1000).delay(1000).fadeOut(5000);
        },); 
        };
    // fin ajax add parqueo manual    

    // ajax list parqueo 
        // function obtenerPark(){
        //     $.ajax({
        //         url: '../config/ajax/park-list.php',
        //         type: 'GET',
        //         success: function(response){
        //             let parks = JSON.parse(response);
        //             let template = '';
        //             parks.forEach(park => {
        //                 template += `
        //                     <tr>
        //                         <td>${park.parqueo_id}</td>
        //                         <td>${park.placa_cli}</td>
        //                         <td>${park.fecha_ini}</td>
        //                         <td>$ ${park.tarifas}</td>
        //                         <td>${park.nombre}</td>
        //                         <td>${park.estado}</td>
        //                     </tr>
        //                 `
        //             });
        //             $('#parks').html(template);
        //             // console.log(response);
        //         } 
        //     })
        // }
    // fin ajax list parqueo 

    //ajax list parqueo cards 
        function formatCurrency(value, currency) {
            return new Intl.NumberFormat('es-ES', {
                style: 'currency',
                currency: 'COP',
                maximumFractionDigits: 0
            }).format(value);
        }        
        function obtenerCards(){
            $.ajax({
                
                url: '../config/ajax/park-list.php',
                type: 'POST',
                success: function(response){
                    let parks = JSON.parse(response);
                    console.log(response);
                    let template = '';
                    
                    parks.forEach(park => {
                        template +=
                            `<div parkId="${park.placa_cli}" 
                                    ticketId="${park.parqueo_id}"
                                    fechaIni="${park.fecha_ini}"
                                    fechaFin="${park.fecha_fin}"
                                    tiempoId="${park.tiempo}"
                                    valorId="${park.valor}"
                                    usuarioId="${park.usuario}"
                                    categoria="${park.categoria}"
                                    cat_nombre="${park.cat_nombre}"

                                    caja_movimientoId="1"
                                    caja_desc_movimientoId="${park.placa_cli} por ${park.tar_tiempo}"
                                    caja_egresosId="0"
                                    liquidadoId="NO"
                                    caja_tipoId="ingreso"

                                    class="col col-lg-3" 
                                    id="btn_parqueo" name="btn_parqueo">
                                    <form id="pagar" class="">
                                        
                                        <span><img class="logo_parqueo" id="logo_parqueo" src="${park.cat_imagen}"></img></span>                    
                                        <h7 class="placa_parqueo" id="placa_cli">${park.placa_cli} </h7> <br>                        
                                            
                                        <h6 class="tiempo_parqueo" id="tiempo_parqueo">${park.tiempo}</h6>             

                                        <h7 class="avisos_parqueo">Valor a pagar:</h7>
                                        <h6 class="pago_parqueo" id="pago_parqueo">$ ${formatCurrency(park.valor, 'COP')}</h6>
                                        
                                        <h6 class="pago_parqueo" id="pago_parqueo">${park.cat_nombre}</h6>

                                        
                                        <button type="submit"
                                                id="btnParqueo_pagar" 
                                                onclick=""
                                                class="btnParqueo_pagar" name="btnParqueo_pagar"  href="">
                                                    
                                                <div class="spinner-grow spinner-grow-sm text-light" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <i class="bi bi-plus-lg text-white">&nbsp;PAGAR</i>
                                        </button>
                                        
                                    </form>
                                </div> `                    
                    });
                    $('#cards').html(template);
                    // console.log(response);
                } 
            })
			

            //cargar div automaticamente 
            //setInterval(function() {obtenerCards()}, 30000);
        }   
    //ajax list parqueo cards

    //ajax list dashboard cards 
        function obtenerCardsdash(){
            $.ajax({
                url: '../config/ajax/park-list.php',
                type: 'GET',
                success: function(response){
                    let parks = JSON.parse(response);                
                    let template = '';
                    parks.forEach(park => {
                        template += `

                        
                        <button class="btn_parqueo" id="btn_parqueo" name="btn_parqueo">
        
                            <span><img class="logo_parqueo" id="logo_parqueo" src="${park.cat_imagen}"></img></span>                    
                            <h7 class="placa_parqueo">${park.placa_cli} </h7> <br>
                            <h7 class="avisos_parqueo">Ingreso:</h7>
                            <h6 class="tiempo_parqueo">${park.fecha_ini}</h6>
                            
        
                            <h7 class="avisos_parqueo">tiempo:</h7>
                            <h6 class="tiempo_parqueo">${park.tiempo}</h6>                       
        
                            <h7 class="avisos_parqueo">${park.tar_tiempo} a:</h7>
                            <h6 class="pago_parqueo">$ ${park.tarifas}</h6>
                            <h7 class="avisos_parqueo">12 Horas a:</h7>
                            <h6 class="pago_parqueo">$ ${park.tar_bloque}</h6>

                            <h7 class="ciclo_parqueo">Valor a pagar:</h7>
                            <h6 class="pago_parqueo">$ ${park.valor}</h6>
                            
                        </button>
                            `                    
                    });
                    $('#cardsDash').html(template);
                    // console.log(response);
                } 
            })
            //cargar div automaticamente 
            setInterval(function() {obtenerCardsdash()}, 30000);
        } 
    // fin ajax list dashboard cards
    
    // ajax add pago  
        $(document).on('click','.btnParqueo_pagar',function(e){ 
            let element = $(this)[0].parentElement.parentElement;                        
            let placa_cli = $(element).attr('parkId');             
            let ticketId = $(element).attr('ticketId'); 
            let fechaIni = $(element).attr('fechaIni');
            let fechaFin = $(element).attr('fechaFin');
            let tiempoId = $(element).attr('tiempoId');
            let valorId = $(element).attr('valorId');
            let usuarioId = $(element).attr('usuarioId');
            let categoria = $(element).attr('categoria');
            
            let caja_movimientoId=$(element).attr('caja_movimientoId');;
            let caja_desc_movimientoId=$(element).attr('caja_desc_movimientoId');            
            let caja_egresosId="0"
            let liquidadoId="NO"
            let caja_tipoId="INGRESO"                 

            const postData = {
                placa_cli: placa_cli, 
                ticket: ticketId, 
                fechaini: fechaIni,
                fechafin: fechaFin,
                tiempo: tiempoId,
                valor: valorId,
                usuario: usuarioId,
                categoria: categoria,

                caja_movimiento: caja_movimientoId,
                caja_desc_movimiento: caja_desc_movimientoId,
                caja_egresos: caja_egresosId,
                liquidado: liquidadoId,
                caja_tipo: caja_tipoId, 
            };
            $.post('../config/ajax/park-pagar.php',postData, function(response){ 
                console.log(response) ;
                console.log(postData) ; 
            })
            alertify.success('OK recibo generado');
            obtenerCards();
                function openInfo() {
                window.open("../modulos/imprimir_ticket_php/recibo.php") }
                //window.open("../factura/pdf_recibo.php")}
            openInfo();
            e.preventDefault(); 
        });
    // fin ajax pago 
    
    // ajax add mensualidad  ..
        $('#mensualidad').submit(function (e) {
            cerrar();
            const postData = {
                placa: $('#placa').val(),
                nombre: $('#nombre').val(),
                cedula: $('#cedula').val(),
                celular: $('#celular').val(),
                vehiculo: $('#vehiculo').val(),
                categoria: $('#categoria').val(),
                user: $('#user').val(),
                caseta: $('#caseta').val(),
                tarifas: $('#tarifas').val(),
                fechaini: $('#fechaini').val(),
                periodos: $('#periodos').val(),
                tarifasa: $('#tarifasa').val(),
                valor: $('#valor').val(),
                valor1: $('#valor1').val(),
                periodos: $('#periodos').val(),
                periodos2: $('#periodos2').val(),

            };
            $.post('../config/ajax/park-mensualidad.php', postData, function (response) {
                console.log(response);
                console.log(postData);
                if (response == 'ya se pago el periodo') {
                    alertify.error('¡Error! - ya se pago el periodo.');

                }
                if (response == 'abrir arqueo') {
                    alertify.error('¡Error! - No se ha abierto una caja.');
                    //openIno();
                }
                if (response == 'existe en parqueo') {
                    alertify.error('¡Error! - Vehiculo se encuentra en el parqueadero.');
                }
                if (response == 'guardado parqueo') {
                    alertify.success('¡Ok! - Mensualidad ingresada.');
                    //openInf();
                }
                if (response == 'guardado cliente y parqueo') {
                    alertify.warning('¡Ok! - vehiculo y cliente registrado.');
                    //openInf();
                }
                $('#mensualidad').trigger('reset');
                //location.reload();
            });
            e.preventDefault();
            openInf();            
            function cerrar() {
                setTimeout(function () {
                    $('#contenedor-toasts').fadeIn(1000).delay(1000).fadeOut(2000);
                },
                );
            }
            //  function openInf() {
            //      window.open("../modulos/imprimir_ticket_php/recibomens.php")
            //  };
        });
    // fin ajax add mensualidad 

    // ajax add cliente mensualidad  ..
        $('#mensualidad_cliente').submit(function (e) {
            cerrar();
            const postData = {
                placa: $('#placa').val(),
                nombre: $('#nombre').val(),
                cedula: $('#cedula').val(),
                celular: $('#celular').val(),
                vehiculo: $('#vehiculo').val(),
                categoria: $('#categoria').val(),
                user: $('#user').val(),
                casetas: $('#casetas').val(),
                tarifas: $('#tarifas').val(),
                fechaini: $('#fechaini').val(),
                periodos: $('#periodos').val(),
                tarifasa: $('#tarifasa').val(),
                valor: $('#valor').val(),
                //periodos2: $('#periodos2').val(),

            };
            $.post('../config/ajax/park-mensualidad-cliente.php', postData, function (response) {
                console.log(response);
                console.log(postData);
                if (response == 'ya se pago el periodo') {
                    alertify.error('¡Error! - ya se pago el periodo.');

                }
                if (response == 'abrir arqueo') {
                    alertify.error('¡Error! - No se ha abierto una caja.');
                    //openIno();
                }
                if (response == 'existe en parqueo') {
                    alertify.error('¡Error! - Vehiculo se encuentra en el parqueadero.');
                }
                if (response == 'guardado parqueo') {
                    alertify.success('¡Ok! - Mensualidad ingresada.');
                    //openInf();
                }
                if (response == 'guardado cliente y parqueo') {
                    alertify.warning('¡Ok! - vehiculo y cliente registrado.');
                    //openInf();
                }
                $('#mensualidad_cliente').trigger('reset');
                //location.reload();
            });
            e.preventDefault();

            document.getElementById("register").addEventListener("click", function () {
                window.location.href = "mensualidades.php";
            });

            function cerrar() {
                setTimeout(function () {
                    $('#contenedor-toasts').fadeIn(1000).delay(1000).fadeOut(2000);
                }
                )
            };
        }); 
    // fin ajax add mensualidad 

    //borrar el div respuestas 
        $(function(){
            $("#register").click(function(){
                $("#respuestas").hide();
            });
        });
        $(function(){
            $("#placa").blur(function(){
                $("#respuestas").show();
            });
        });
        $(function(){
            $("#placa").focus(function(){
                $("#respuestas").show();
            });
        });
    // fin borrar el div respuestas 


});