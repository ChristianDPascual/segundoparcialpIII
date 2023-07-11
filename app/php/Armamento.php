<?php

class Armamento
{
    public $nombre;
    public $precio;
    public $foto;
    public $nacionalidad;
    public $marca;
    public $idProducto;


    public static function cargarUno($request, $response, $args)
    {
        $modo = token :: decodificarToken($request);

        if($modo == "admin")
        {
            $parametros = $request->getParsedBody();
            $nombre = $parametros["nombre"];
            $precio = $parametros["precio"];
            $nacionalidad = $parametros["nacionalidad"];
            $marca = $parametros["marca"];

            if(Armamento :: existenciaRepetida($nombre,$precio,$marca,$nacionalidad) == true)
            {
                $payload = json_encode(array("mensaje"=>"Armamento $nombre -- $marca ya existe"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
            else
            {
                try
                {
                    $idProducto = controlID();
                    $foto = $request->getUploadedFiles()['foto'];
                    $destinoFoto= Armamento :: moverImagen($foto,$nombre,$idProducto);
            
                    $conStr = "mysql:host=localhost;dbname=la_despensa";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare('INSERT INTO armamento (nombre,precio,foto,nacionalidad,marca,idProducto) 
                                                VALUES (:nombre,:precio,:foto,:nacionalidad,:marca,:idProducto)');
                    $sentencia->bindValue(':nombre', $nombre);
                    $sentencia->bindValue(':precio', $precio);
                    $sentencia->bindValue(':foto', $destinoFoto);
                    $sentencia->bindValue(':nacionalidad', $nacionalidad);
                    $sentencia->bindValue(':marca', $marca);
                    $sentencia->bindValue(':idProducto', $idProducto);

                    if($sentencia->execute())
                    {
                        $payload = json_encode(array("mensaje"=>"Armamento $nombre -- $marca creado con exito"));
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
            
        }
        else
        {
            $payload = json_encode(array("mensaje"=>"usuario invalido para la operacion"));
            $response->getBody()->write($payload);
            $pdo = null;
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public static function moverImagen($archivo,$nombre,$idProducto)
    {

        if(isset($archivo))
        {   
            $file = $archivo->getClientFilename();
            $tempName = $archivo->getStream()->getMetadata('uri');
            $destino = "C:/xampp\htdocs/examen2\app\imagenes_armamento/".$file;
            $nuevoNombre = "$nombre"."$idProducto".".jpg";


            if(move_uploaded_file($tempName, $destino))
            {
                echo "\nSe movio exitosamente la foto\n";
                if(rename($destino, 'C:/xampp\htdocs/examen2\app\imagenes_armamento/'.$nuevoNombre))
                {
                    echo "\nSe cambio el nombre de la foto\n";
                    $destinoFinal ='C:/xampp\htdocs/examen2\app\imagenes_armamento/'.$nuevoNombre;
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

    public static function imagenBackUp($nombre,$nombreViejo,$idProducto)
    {//traigo los datos viejos antes de hacer el cambio de ruta

        if(isset($nombre) && isset($idProducto))
        { 
            $aux = Data :: traerRutaFoto($idProducto);
            $directorioOriginal = $aux["foto"];

            $nuevoNombre = "$nombre"."$idProducto".".jpg";
            $nombreAntiguo = "$nombreViejo"."$idProducto".".jpg";

            $directorioDestino = 'C:\xampp\htdocs\examen2\app\backup_imagenes_armamento/'.$nombreAntiguo;
            $directorioFinal = "C:/xampp\htdocs/examen2\app\imagenes_armamento/".$nuevoNombre;   

            if (copy($directorioOriginal, $directorioDestino)) //ok
            {
                if (rename($directorioOriginal, $directorioFinal)) 
                {

                    return $directorioFinal;
                } 
                else 
                {
                    echo "Error al renombrar la imagen original";
                    return false;
                }
            }
            else 
            {
               
                echo "Error al copiar la imagen";
                return false;
            }
            
        }
        else
        {
            echo "Ocurrio un error con la imagen";
            return false;
        }
    }

                                               //nombre------precio-----marca------nacionalidad
    public static function existenciaRepetida($condicion1,$condicion2,$condicion3,$condicion4)//c = condicion
    {                                               
        if(isset($condicion1) && isset($condicion2) && isset($condicion3) && isset($condicion4))
        {
            $tabla = "armamento";
            $lista =  Data :: traerTodo($tabla);
            $retorno = false;


            foreach($lista as $a)
            {
                if($a["nombre"] == $condicion1 && $a["precio"] == $condicion2 && 
                   $a["marca"] == $condicion3 && $a["nacionalidad"] == $condicion4)
                {
                    $retorno = true;
                    break;
                }
            }
           
            return $retorno;
        }
    }

    public static function modificarArmamento($request, $response, $args)
    {//enviar por put x-www-form-urlencoded $putData = $request->getParsedBody();
        //para cambiarla la busco por idProducto no se puede cambiar el resto si
        $modo = token :: decodificarToken($request);
        if($modo == "admin")
        {
           
            try
            {
                $parametros = $request->getParsedBody();
                $nombre = $parametros["nombre"];
                $precio = $parametros["precio"];
                $nacionalidad = $parametros["nacionalidad"];
                $marca = $parametros["marca"];
                $idProducto = $parametros["idProducto"];
                
                $datosViejos = Data :: traerArmamentoID($idProducto);
                $nuevaUbicacion = Armamento :: imagenBackUp($nombre,$datosViejos["nombre"],$idProducto);

                if($nuevaUbicacion != false)
                {
                    $conStr = "mysql:host=localhost;dbname=la_despensa";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare("UPDATE armamento SET nombre = :nombre, precio = :precio, foto = :foto,
                                                       nacionalidad = :nacionalidad, marca = :marca 
                                                WHERE idProducto = :idProducto");
                    $sentencia->bindValue(':nombre', $nombre);
                    $sentencia->bindValue(':precio', $precio);
                    $sentencia->bindValue(':foto', $nuevaUbicacion);
                    $sentencia->bindValue(':nacionalidad', $nacionalidad);
                    $sentencia->bindValue(':marca', $marca);
                    $sentencia->bindValue(':idProducto', $idProducto);
                    
                    if($sentencia->execute())
                    {
                        $payload = json_encode(array("mensaje"=>"Armamento $nombre -- $marca modificada con exito"));
                        $response->getBody()->write($payload);
                        $pdo = null;
                        return $response->withHeader('Content-Type', 'application/json');
                    }
                }
                else
                {
                    $payload = json_encode(array("mensaje"=>"Ocurrio une error al hacer un respaldo de la imagen"));
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

    public static function eliminarUno($request, $response, $args)
    {//peticion delete enviada por x-www-form-urlencoded formato /usuarios/{id}
        $modo = token :: decodificarToken($request);
        if($modo == "admin")
        {
            $parametros = $request->getParsedBody();
            $idProducto = $parametros["idProducto"];

            $aux = Data :: traerArmamentoID($idProducto);


            if($aux['idProducto'] == $idProducto && Armamento :: borrarImagen($aux["foto"]))
            {
                try
                {
                    $conStr = "mysql:host=localhost;dbname=la_despensa";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare('DELETE FROM armamento WHERE idProducto =:idProducto');
                    $sentencia->bindValue(':idProducto', $idProducto);


                    if($sentencia->execute())
                    {
                        $pdo = null;
                        $idUsuario= token :: insertarRegistro($request);
                        $accion = $request->getUri()->getPath();
                        $fecha = $fecha = date('Y-m-d H:i:s');

                        if(Logs :: registroAcciones($idUsuario,$idProducto,$accion,$fecha))
                        {
                            $payload = json_encode(array("mensaje"=>"armamento eliminado con exito"));
                            $response->getBody()->write($payload);
                            return $response->withHeader('Content-Type', 'application/json');
                        }
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
                $payload = json_encode(array("mensaje"=>"no se encontro el armamento ingresado"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
            

        }
        else
        {
            $payload = json_encode(array("mensaje"=>"usuario invalido para la operacion"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public static function obtenerTotal($idProducto,$cantidad)
    {
        if(isset($idProducto) && $cantidad>0)
        {
            $aux = Data :: traerArmamentoID($idProducto);
            $total = $aux["precio"] * $cantidad;
            return $total;
        }
    }

    public static function borrarImagen($foto)
    {
        
        if(isset($foto))
        {
            if (unlink($foto))
            {
                echo "La foto se ha borrado exitosamente\n";
                return true;
            } 
            else 
            {
                echo "No se pudo borrar la foto\n";
                return false;
            }
    
        }
        else
        {
            echo "no existe foto para borrar\n";
            return false;
        }
    }

    public static function listarArmamento($request, $response, $args)
    {
        
        $tabla = "armamento";
        $lista =  Data :: traerTodo($tabla);
        $contador = 0;

        foreach($lista as $arma)
        {
            $contador++;
            echo $arma["nombre"]." - ".$arma["marca"]." - ".$arma["precio"]." - ".
                 $arma["nacionalidad"]." - ".$arma["idProducto"]."\n";
        }

        if($contador>0)
        {
            $payload = json_encode(array("mensaje"=>"Armamento listado correctamente"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
        else
        {
            $payload = json_encode(array("mensaje"=>"No se encontro armamento"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public static function listarArmamentoPorPais($request, $response, $args)
    {
        $parametros= $request->getQueryParams();
        $pais = $parametros["pais"];
        $tabla = "armamento";
        $lista =  Data :: traerTodo($tabla);
        $contador = 0;

        foreach($lista as $arma)
        {
            if($arma["nacionalidad"] == $pais)
            {
                $contador++;
                 echo $arma["nombre"]." - ".$arma["marca"]." - ".$arma["precio"]." - ".
                 $arma["nacionalidad"]." - ".$arma["idProducto"]."\n";
            }
        }

        if($contador>0)
        {
            $payload = json_encode(array("mensaje"=>"Armamento listado correctamente"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
        else
        {
            $payload = json_encode(array("mensaje"=>"No se encontro armamento"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public static function traerArmamentoPorID($request, $response, $args)
    {
        $modo = token :: decodificarToken($request);

        if($modo == "admin" || $modo == "comprador" || $modo == "vendedor")
        {
            $parametros= $request->getQueryParams();
            $idProducto = $parametros["idProducto"];
            $resultado = Data :: traerArmamentoID($idProducto);

            if(!is_null($resultado["nombre"]))
            {
                $nombre = $resultado["nombre"];
                $marca = $resultado["marca"];
                $precio = $resultado["precio"];
                $nacionalidad = $resultado["nacionalidad"];

                $payload = json_encode(array("mensaje"=>"$nombre $marca $precio $nacionalidad"));
                $response->getBody()->write($payload);
                 return $response->withHeader('Content-Type', 'application/json');
            }
            else
            {
                $payload = json_encode(array("mensaje"=>"Error no existe el armamento buscado"));
                $response->getBody()->write($payload);
                 return $response->withHeader('Content-Type', 'application/json');
            }
        }
        else
        {
            $payload = json_encode(array("mensaje"=>"Error de credenciales"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
        
    }
}

?>