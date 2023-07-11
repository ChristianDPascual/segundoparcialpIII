<?php

class Usuario
{
    public $nombre;
    public $apellido;
    public $dni;
    public $mail;
    public $categoria;


    public static function login($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $mail = $parametros["mail"];

        $aux = Data :: existenciaMail($mail);
        
        if($aux["mail"] == $mail && $aux["estado"] == "activo"){
            $cat = $aux["categoria"];
            $token = token::crearToken($aux["dni"], $aux["categoria"]);
            $payload = json_encode(array("mensaje" => "OK. $cat", "token" => $token));
        }
        else
        {

            $payload = json_encode(array("mensaje" => "ERROR en el ingreso de las credenciales $mail"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function cargarUno($request, $response, $args)
    {
        $modo = token :: decodificarToken($request);
        if($modo == "admin")
        {
            $parametros = $request->getParsedBody();
            $nombre = $parametros["nombre"];
            $apellido = $parametros["apellido"];
            $dni = $parametros["dni"];
            $mail = $parametros["mail"];
            $categoria = $parametros["categoria"];
            $estado = "activo";

            if(Usuario :: existenciaRepetida($nombre,$apellido,$dni,$mail) == true)
            {
                $payload = json_encode(array("mensaje"=>"Usuario $nombre $apellido ya se encuentra registrado"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
            else
            {
                try
                {
            
                    $conStr = "mysql:host=localhost;dbname=la_despensa";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare('INSERT INTO usuario (nombre,apellido,dni,mail,categoria,estado) 
                                                VALUES (:nombre,:apellido,:dni,:mail,:categoria,:estado)');
                    $sentencia->bindValue(':nombre', $nombre);
                    $sentencia->bindValue(':apellido', $apellido);
                    $sentencia->bindValue(':dni', $dni);
                    $sentencia->bindValue(':mail', $mail);
                    $sentencia->bindValue(':categoria', $categoria);
                    $sentencia->bindValue(':estado', $estado);

                    if($sentencia->execute())
                    {
                        $payload = json_encode(array("mensaje"=>"Usuario $nombre $apellido creado con exito"));
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
                                              //nombre ----apellido-------dni-------mail
    public static function existenciaRepetida($condicion1,$condicion2,$condicion3,$condicion4)//c = condicion
    {                                               
        if(isset($condicion1) && isset($condicion2) && isset($condicion3) && isset($condicion4))
        {
            $tabla = "usuario";
            $lista =  Data :: traerTodo($tabla);
            $retorno = false;


            foreach($lista as $u)
            {
                if($u["nombre"] == $condicion1 && $u["apellido"] == $condicion2 && 
                   $u["dni"] == $condicion3 && $u["mail"] == $condicion4)
                {
                    $retorno = true;
                    break;
                }
            }
           
            return $retorno;
        }
    }

    public static function eliminarUno($request, $response, $args)
    {//peticion delete enviada por x-www-form-urlencoded formato /usuarios/{id}
        $modo = token :: decodificarToken($request);
        if($modo == "admin")
        {
            $dni = $args['dni'];

            $aux = Data :: traerUsuario($dni);

            if($aux["dni"] == $dni && $aux["estado"] == "activo")
            {
                try
                {
                    $estado = "desactivado";
                    $conStr = "mysql:host=localhost;dbname=la_despensa";
                    $user ="yo";
                    $pass ="cp35371754";
                    $pdo = new PDO($conStr,$user,$pass);
                    $sentencia = $pdo->prepare("UPDATE usuario SET estado = :estado WHERE dni =:dni");
                    $sentencia->bindValue(':estado', $estado);
                    $sentencia->bindValue(':dni', $dni);


                    if($sentencia->execute())
                    {
                        $payload = json_encode(array("mensaje"=>"Usuario eliminado con exito"));
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
                $payload = json_encode(array("mensaje"=>"no se encontro el usuario ingresado"));
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
}

?>