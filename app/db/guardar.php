<?php
use Firebase\JWT\JWT;
use Firebase\JWT\key;
include('C:\xampp\htdocs\examen2\app\libreria\TCPDF-main\tcpdf.php');

class Guardar
{
    public static function descargarArmamentoCSV($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);
            if($modo == "admin")
            {
                $ruta = './archives/armamento.csv';
                $conStr = "mysql:host=localhost;dbname=la_despensa";
                $user ="yo";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT * FROM armamento");
                $sentencia->execute();
                $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                $pdo = null;

  
                $archivo = fopen($ruta,"w");
                foreach($resultado as $a)
                {
                    $nombre = $a["nombre"];
                    $precio = $a["precio"];
                    $foto = $a["foto"];
                    $nacionalidad = $a["nacionalidad"];
                    $marca = $a["marca"];
                    $idProducto = $a["idProducto"];
                    if(fwrite($archivo,"$nombre,$precio,$foto,$nacionalidad,$marca,$idProducto\n")==0)
                    {
                        $payload = json_encode(array("mensaje"=>"Ocurrio un error al guardar el armamento"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
                fclose($archivo);

                $payload = json_encode(array("mensaje"=>"armamento guardados en un archivo.csv con exito"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');

            }
            else
            {
                $payload = json_encode(array("mensaje"=>"error de autenticacion"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            throw new Exception("usuario invalido");
        }
    }

    public static function descargarLogsCSV($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);
            if($modo == "admin")
            {
                $ruta = './archives/logs.csv';
                $conStr = "mysql:host=localhost;dbname=la_despensa";
                $user ="yo";
                $pass ="cp35371754";
                $pdo = new PDO($conStr,$user,$pass);

                $sentencia = $pdo->prepare("SELECT * FROM registro_logs");
                $sentencia->execute();
                $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                $pdo = null;

  
                $archivo = fopen($ruta,"w");
                foreach($resultado as $l)
                {
                    $idUsuario = $l["idUsuario"];
                    $idProducto = $l["idProducto"];
                    $accion = $l["accion"];
                    $fecha = $l["fecha"];
                    if(fwrite($archivo,"$idUsuario,$idProducto,$accion,$fecha\n")==0)
                    {
                        $payload = json_encode(array("mensaje"=>"Ocurrio un error al guardar los logs"));
                        $response->getBody()->write($payload);
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
                fclose($archivo);

                $payload = json_encode(array("mensaje"=>"logs guardados en un archivo.csv con exito"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');

            }
            else
            {
                $payload = json_encode(array("mensaje"=>"error de autenticacion"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            throw new Exception("usuario invalido");
        }
    }

    public static function cargarLogsCSV($request, $response, $args)
    {
        try
        {
            $modo = token :: decodificarToken($request);
            if($modo == "socio")
            {
                $ruta = './archives/logs.csv';
                $archivo = fopen($ruta,"r");
                $valores = array();
                $contador = 0;
                $aux;
                
                while(!feof($archivo))
                {             
                    $aux = explode(',',fgets($archivo));
                    if(count($aux)==4)
                    {
                        array_push($valores,$aux);
                    }
                }
                fclose($archivo);



                foreach($valores as $l)
                {
                    $conStr = "mysql:host=localhost;dbname=la_comanda";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $auxD = $l[0];
                    $auxI = $l[1];
                    $auxA = $l[2];
                    $auxF = $l[3];

                    $sentencia = $pdo->prepare("INSERT INTO testlog (dni,idServicio,accion,fecha) 
                                                VALUES (:dni,:idServicio,:accion,:fecha)");
                    $sentencia->bindValue(':dni',$auxD);
                    $sentencia->bindValue(':idServicio',$auxI);
                    $sentencia->bindValue(':accion',$auxA);
                    $sentencia->bindValue(':fecha',$auxF);
                    if($sentencia->execute())
                    {
                        $contador++;
                        $pdo = null;
                        
                    }
                }
                
                
                
                if($contador>0)
                {
                    $payload = json_encode(array("mensaje"=>"los logs se subieron a la tabla exitosamente"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"error al subir los logs a una tabla"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"error de autenticacion"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            throw new Exception("usuario invalido");
        }
    }

    public static function guardarEnPDF($request, $response, $args)
    {
        try
        {
            $parametros= $request->getQueryParams();
            $ordenamiento = $parametros["orden"];
            $tabla = "ventas_armamento";
            $lista = Data :: traerTodo($tabla);
            $fecha = date('d-m-Y');
            $fechaActual = validarFecha($fecha);
            $fecha30 = validarFecha(date('d-m-Y',strtotime($fecha.'-30 day')));
            $listaAux = array();

            $contador = 0;

            foreach($lista as $v)
            {
                $aux = validarFecha($v["fecha"]);
                if($fecha30<=$aux && $fechaActual>=$aux)
                {
                    $contador++;
                    array_push($listaAux,$v);
                }
            }

            if($contador>0)
            {
                if($ordenamiento == "menor")
                {
                    usort($listaAux, 'ordenarFechaMenorMayor');
                }
                else
                {
                    if($ordenamiento == "mayor")
                    {
                        usort($listaAux, 'ordenarFechaMayorMenor');
                    }
                }
                
                $pdf = new TCPDF();
                $pdf->AddPage();
                foreach($listaAux as $elemento)
                {
                    $pdf->writeHTML($elemento["fecha"]." usuario: ".$elemento["dniComprador"]
                    ." producto: ".$elemento["idProducto"]." cantidad: ".$elemento["cantidad"]." total: ".$elemento["total"]);
                }
                $pdf->Output('C:\xampp\htdocs\examen2\app\archives\ventas.pdf');
                //para guardar definitivamente $pdf->Output('C:\xampp\htdocs\examen2\app\archives\ventas.pdf','F');

                $payload = json_encode(array("mensaje"=>"cantidad de ventas $contador"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"No existen ventas en ese periodo"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            throw new Exception("usuario invalido");
        }
    }

}

?>