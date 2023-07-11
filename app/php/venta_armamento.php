<?php

class Venta_armamento
{
    public $idVenta;
    public $idProducto;
    public $dniComprador;
    public $fecha;
    public $cantidad;
    public $total;
    public $foto;

    public static function cargarUno($request, $response, $args)
    {
        $modo = token :: decodificarToken($request);
        if($modo == "admin" || $modo == "comprador" || $modo == "vendedor")
        {
            $parametros = $request->getParsedBody();
            $idProducto = $parametros["idProducto"];
            $dniComprador = $parametros["dniComprador"];
            echo $dniComprador;
            $datosArmamento = Data :: traerArmamentoID($idProducto);
            $datosUsuario = Data :: traerUsuario($dniComprador);

            
            
            if(Armamento :: existenciaRepetida($datosArmamento["nombre"],$datosArmamento["precio"],
                                               $datosArmamento["marca"],$datosArmamento["nacionalidad"]) &&
               Usuario :: existenciaRepetida($datosUsuario["nombre"],$datosUsuario["apellido"],
                                            $datosUsuario["dni"],$datosUsuario["mail"]))
            {
                try
                {
                    
                    $cantidad = $parametros["cantidad"];
                    $fecha = $parametros["fecha"];
                    $idVenta = controlID();;
                    $total = Armamento :: obtenerTotal($idProducto,$cantidad);  
                    $foto = $request->getUploadedFiles()['foto'];
                    $destinoFoto= Venta_armamento ::  guardarImagenVenta($foto,$datosArmamento["nombre"],$datosUsuario["nombre"]);
            
                    $conStr = "mysql:host=localhost;dbname=la_despensa";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare('INSERT INTO ventas_armamento (dniComprador,idProducto,fecha,
                                                                             cantidad,total,idVenta,foto) 
                                                VALUES (:dniComprador,:idProducto,:fecha,
                                                        :cantidad,:total,:idVenta,:foto)');
                    $sentencia->bindValue(':dniComprador',$dniComprador);
                    $sentencia->bindValue(':idProducto',$idProducto);
                    $sentencia->bindValue(':fecha',$fecha);
                    $sentencia->bindValue(':cantidad',$cantidad);
                    $sentencia->bindValue(':total',$total);
                    $sentencia->bindValue(':idVenta',$idVenta);
                    $sentencia->bindValue(':foto',$destinoFoto);


                    if($sentencia->execute())
                    {
                        $payload = json_encode(array("mensaje"=>"Venta realizada con exito"));
                        $response->getBody()->write($payload);
                        $pdo = null;
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
                catch(PDOException $e)
                {
                    $pdo = null;
                    echo "Error: " .$e->getMessage();
                }
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"El producto o comprador no existe"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }         
            
        }
        else
        {
            $payload = json_encode(array("mensaje"=>"usuario invalido para la operacion"));
            $response->getBody()->write($payload);
            $pdo = null;
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public static function guardarImagenVenta($archivo,$nombreArma,$nombreCliente)
    {

        if(isset($archivo))
        {   
            $file = $archivo->getClientFilename();
            $tempName = $archivo->getStream()->getMetadata('uri');
            $destino = "C:/xampp\htdocs/examen2\app\FotosArma2023/".$file;
            $nuevoNombre = "$nombreArma"."$nombreCliente".".jpg";


            if(move_uploaded_file($tempName, $destino))
            {
                echo "\nSe movio exitosamente la foto\n";
                if(rename($destino, 'C:/xampp\htdocs/examen2\app\FotosArma2023/'.$nuevoNombre))
                {
                    echo "\nSe cambio el nombre de la foto\n";
                    $destinoFinal ='C:/xampp\htdocs/examen2\app\FotosArma2023/'.$nuevoNombre;
                    return $destinoFinal;
                }
            }
            else
            {
                echo "no se movio la imagen\n";
            }
            
        }
        else
        {
            echo "Ocurrio un error con la imagen";
        }
    }

    public static function buscarCompradoresPorArticulos($request, $response, $args)
    {
        $modo = token :: decodificarToken($request);
        if($modo == "admin")
        {
            $parametros= $request->getQueryParams();
            $nombreArmamento = $parametros["nombreArmamento"];
            $lista = Data :: traerCompradoresPorArmamento($nombreArmamento);
            $contador = 0;
            foreach($lista as $dni)
            {
                $aux = Data :: traerUsuario($dni["dniComprador"]);

                if($aux["nombre"] != null)
                {
                    echo $aux["nombre"]." ".$aux["apellido"]." ".$aux["mail"]." ".$aux["dni"]
                         ." ".$aux["categoria"]."\n";
                    $contador++;
                }
            }

            if($contador>0)
            {
                $payload = json_encode(array("mensaje"=>"cantidad de compradores de $nombreArmamento es de $contador"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"No existen compradores de $nombreArmamento"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }

            
        }
        else
        {
            $payload = json_encode(array("mensaje"=>"usuario invalido para la operacion"));
            $response->getBody()->write($payload);
            $pdo = null;
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public static function traerComprasEEUUenNoviembre($request, $response, $args)
    {
        $modo = token :: decodificarToken($request);
        if($modo == "admin")
        {
            try
            {
                $tabla = "ventas_armamento";
                $lista = Data :: traerTodo($tabla);
                $f1 = "13-11-2023";
                $f2 = "16-11-2023";
                $fecha1 = validarFecha($f1);
                $fecha2 = validarFecha($f2);
                $contador = 0;

                foreach($lista as $v)
                {
                    $aux = validarFecha($v["fecha"]);
                    if($fecha1<=$aux && $fecha2>=$aux)
                    {
                        $contador++;
                        echo $v["fecha"]." dni comprador ".$v["dniComprador"]." articulo ".$v["idProducto"]." cantidad ".$v["cantidad"]." total ".$v["total"]."\n";
                    }
                }

                if($contador>0)
                {
                    $payload = json_encode(array("mensaje"=>"cantidad de ventas $contador desde $f1 a $f2"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"No existen ventas en ese periodo desde desde $f1 a $f2"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                }
            }
            catch(PDOException $e)
            {
                $pdo = null;
                echo "Error: " .$e->getMessage();
            }

        }
        else
        {
            $payload = json_encode(array("mensaje"=>"usuario invalido para la operacion"));
            $response->getBody()->write($payload);
            $pdo = null;
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
}

?>