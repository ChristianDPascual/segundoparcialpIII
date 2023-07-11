<?php

class Logs
{
    public static function registroAcciones($idUsuario,$idProducto,$accion,$fecha)
    {
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_despensa";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);

            $sentencia = $pdo->prepare("INSERT INTO registro_logs (idUsuario,idProducto,accion,fecha)
                                        VALUES (:idUsuario,:idProducto,:accion,:fecha)");
            $sentencia->bindValue(':idUsuario', $idUsuario);
            $sentencia->bindValue(':idProducto', $idProducto);
            $sentencia->bindValue(':accion', $accion);
            $sentencia->bindValue(':fecha', $fecha);

            if($sentencia->execute())
            {
                $pdo =null;
                return true;
            }
            else
            {
                $pdo =null;
                return false;
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