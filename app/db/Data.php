<?php

Class Data
{

    public static function existenciaMail($mail)
    {
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_despensa";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare('SELECT * FROM usuario WHERE mail = :mail');
            $sentencia->bindValue(':mail', $mail);

            if($sentencia->execute())
            {
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                $pdo = null;
                return $resultado;
            }

        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function traerTodo($tabla)
    {
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_despensa";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare('SELECT * FROM '.$tabla);

            if($sentencia->execute())
            {
                $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                $pdo = null;
                return $resultado;
            }

        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function traerArmamentoID($idProducto)
    {
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_despensa";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare('SELECT * FROM armamento WHERE idProducto = :idProducto');
            $sentencia->bindValue(':idProducto', $idProducto);

            if($sentencia->execute())
            {
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                $pdo = null;
                return $resultado;
            }

        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function traerRutaFoto($idProducto)
    {
        try
        {

            $conStr = "mysql:host=localhost;dbname=la_despensa";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare('SELECT foto FROM armamento WHERE idProducto = :idProducto');
            $sentencia->bindValue(':idProducto', $idProducto);

            if($sentencia->execute())
            {
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                $pdo = null;
                return $resultado;
            }

        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }
    public static function traerUsuario($dni)
    {
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_despensa";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare('SELECT * FROM usuario WHERE dni = :dni');
            $sentencia->bindValue(':dni', $dni);

            if($sentencia->execute())
            {
                $resultado = $sentencia->fetch(PDO :: FETCH_ASSOC);
                $pdo = null;
                return $resultado;
            }

        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

    public static function traerCompradoresPorArmamento($nombreArmamento)
    {
        try
        {
            $conStr = "mysql:host=localhost;dbname=la_despensa";
            $user ="yo";
            $pass ="cp35371754";
            $pdo = new PDO($conStr,$user,$pass);
            $sentencia = $pdo->prepare('SELECT ventas_armamento.dniComprador 
                                        FROM ventas_armamento 
                                        INNER JOIN armamento 
                                        ON ventas_armamento.idProducto = armamento.idProducto
                                         WHERE armamento.nombre = :nombreArmamento');
            $sentencia->bindValue(':nombreArmamento', $nombreArmamento);

            if($sentencia->execute())
            {
                $resultado = $sentencia->fetchALL(PDO :: FETCH_ASSOC);
                $pdo = null;
                return $resultado;
            }
        }
        catch(PDOException $e)
        {
            $pdo = null;
            echo "Error: " .$e->getMessage();
        }
    }

}

?>