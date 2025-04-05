/*!
    * Start Bootstrap - SB Admin v7.0.3 (https://startbootstrap.com/template/sb-admin)
    * Copyright 2013-2021 Start Bootstrap
    * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
    */
    // 
// Scripts
// 

window.addEventListener('DOMContentLoaded', event => {

    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        // Uncomment Below to persist sidebar toggle between refreshes
         //if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
         //        document.body.classList.toggle('sb-sidenav-toggled');
         //}
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

});



// funcion reloj //
function mueveReloj(){   
    momentoActual = new Date();
    anio = momentoActual.getFullYear();
    mes = momentoActual.getMonth()+1;
    dia = momentoActual.getDate();
    dias = momentoActual.getDay();
    hora = momentoActual.getHours();
    minuto = momentoActual.getMinutes();
    segundo = momentoActual.getSeconds();
    
    monthNameLong = momentoActual.toLocaleString('es-CO', {month: 'long'});    

    
    if (dias === 0){
        diaSemana = "Domingo"
    }; 
    if (dias === 1){
        diaSemana = "Lunes"
    }; 
    if (dias === 2){
        diaSemana = "Martes"
    }; 
    if (dias === 3){
        diaSemana = "Miercoles"
    }; 
    if (dias === 4){
        diaSemana = "Jueves"
    }; 
    if (dias === 5){
        diaSemana = "Viernes"
    }; 
    if (dias === 6){
        diaSemana = "Sabado"
    }; 
    
    str_segundo = new String (segundo)
    if (str_segundo.length == 1)
        segundo = "0" + segundo

    str_minuto = new String (minuto)
    if (str_minuto.length == 1)
        minuto = "0" + minuto    

    //  str_hora = new String (hora)
    //  if (str_hora.length == 1)
    //      hora = "0" + hora

        var strampm;    
            if (hora >= 12) {
                strampm= "PM";
            } else {
                strampm= "AM";
            }
            hora = hora % 12;
            if (hora == 0) {
                hora = 12;
            }

    fechaImprimible = diaSemana + ", " + dia + " " + monthNameLong + " " + anio;
    horaImprimible = hora + " : " + minuto + " : "  + segundo + strampm;

    // document.form_reloj.fecha.value = fechaImprimible;
    // document.form_reloj.reloj.value = horaImprimible;
    
    document.getElementById("fecha-reloj").textContent = `${fechaImprimible}`;    
    document.getElementById("hora").textContent = `${hora}`;
    document.getElementById("minuto").textContent = `${minuto}`;
    document.getElementById("segundo").textContent = `${segundo}`;
    document.getElementById("strampm").textContent = `${strampm}`;

    setInterval("mueveReloj()",1000)
    }

    




