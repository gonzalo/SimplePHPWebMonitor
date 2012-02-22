<?php
/********************************************************/
/* 
   Simple PHP Web Monitor
   
   Version: 0.2
   License: GPL (http://es.tldp.org/Otros/gples/gples.html)
   Autor: Gonzalo Cao Cabeza de Vaca
   Mailto: gonzalo(punto)cao@gmail(punto)com

   Simple PHP Web Monitor es un script en php que monitoriza el estado de las 
   webs indicadas en la configuración. Es capaz de mostrar el estado a través
   del navegador o funcionar en modo servicio notificando los cambios vía correo
   electrónico


   Uso:

   Como página web:
      - para acceder en cualquier momento copiar el archivo en una carpeta accesible 
        desde el servidor web
      - el servidor web debe ser capaz de ejecutar código PHP (lógico ¿no?)

   Como servicio:
      - es indiferente donde esté copiado el archivo
      - el servidor debe tener instalado el intérprete de php (generalmente en 
        /bin/php) puedes probar a lanzar el script a mano con:

           $php SimplePHPWebMonitor.php

      - Configuramos el crontab ($crontab -e) añadiendo la siguiente línea para que 
        se ejecute cada 10 minutos:

        0,10,20,30,40,50 * * * * /bin/php /[path_to_sript]/SimplePHPWebMonitor.php


   Opciones:
      - send_mail: si lo activamos el sistema enviará un mensaje de correo electrónico 
                   CADA VEZ que una web deja de estar accesible. El servidor debe 
                   estar configurado para el envio de correo con la orden mail.
                   Exige configurar también la dirección de correo del destinatario.
                    
      - save_log:  activa el registro de log. Exige especificar un archivo de log.
                   ATENCION: el usuario que lanza el script debe tener permisos, sobre
                   ese fichero. 
      

*/
/********************************************************/

//páginas a consultar
//puedes añadir tantas páginas como quieras mediante array_push
$sites = array();
array_push ($sites, 'http://www.google.es');
array_push ($sites, 'http://barrapunto.com');
array_push ($sites, 'http://something_that_points_to_no_where.com');
array_push ($sites, 'http://BAD_FORMED_URL?');


//save_log [1/0] = [on / off]
$save_log = 0;
$log_file = "/home/user/SimplePHPWebMonitor.log";

//send_mail [1/0] = [on / off]
$send_mail = 0;
$receiver = "tu_direccion@de_correo_com";

//configuracion de los parámetros de correo
$from = "From: SimplePHPWebMonitor ";
$to = $receiver;
$subject = "ATENCION alguna página no respondió";
$some_url_failed_flag = 0;


//isValid checks if an URL is correctly formed
function isValidURL($url_string)
{
   $urlregex = "^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
   return eregi($urlregex, $url_string);
}


//comprobamos las páginas

$log_txt = "";

foreach ($sites as $url)
{
   if (isValidURL($url)){
      $sitio = @fopen($url,"r");
      if ($sitio){
         $status = date("Y-m-d G-i")." - OK - ".$url."\r\n";
      } else {
         $status = date("Y-m-d G-i")." - FAILED TO FETCH - ".$url."\r\n";
         $some_url_failed_flag = 1;
      }
   } else { 
      $status = date("Y-m-d G-i")." - URL BAD FORMED - ".$url."\r\n";
   }
   
   //mostramos salida por consola
   echo $status;

   //la acumulamos por si la necesitamos para el log o el mail   
   $log_txt .= $status;
   
}  

//si save_log está activado guardamos el archivo de log
if ($save_log == 1)
{
   //intentamos abrir el archivo y añadir el log
   if (file_put_contents( $log_file, $log_txt, FILE_APPEND))
      echo "Archivo de log actualizado correctamente\r\n";
   else
      echo "Error, no se pudo actualizar el archivo de log. Compruebe que tiene permisos de escritura\r\n";
} 


//si send_mail está activado y ha fallado alguna dirección 
//enviamos en mensaje
if (($send_mail == 1 ) && ($some_url_failed_flag == 1 ))
{
   $mensaje .= $log_txt;
   $cabeceras = "MIME-Version: 1.0\r\n";
   $cabeceras .= "Content-type: text/html; charset=iso-8859-1\r\n";
   $cabeceras .= $from;
   mail( $to , $subject, $mensaje, $cabeceras);
}         

 
?>