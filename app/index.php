<?php
////usar en la terminal en la direccion del programa -> php -S localhost:666 -t app
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

//use Psr\Http\Server\RequestHandlerInterface as - El objeto controlador de solicitudes PSR15 (parámetro).
use Psr\Http\Message\ResponseInterface as Response; //respuesta
use Psr\Http\Message\ServerRequestInterface as Request; //(parámetro).
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;
use Firebase\JWT\JWT;
use Firebase\JWT\key;
use setasign\Fpdi\Tfpdf;
use setasign\Fpdi\Tcpdf\Fpdi;


require_once('C:\xampp\htdocs\Examen2\slim-php-deployment\vendor\autoload.php');
require_once('C:\xampp\htdocs\examen2\app\php\Armamento.php');
require_once('C:\xampp\htdocs\examen2\app\php\funciones.php');
require_once('C:\xampp\htdocs\examen2\app\php\Usuario.php');
require_once('C:\xampp\htdocs\examen2\app\php\venta_armamento.php');
require_once('C:\xampp\htdocs\examen2\app\JWT\token.php');
require_once('C:\xampp\htdocs\examen2\app\logs\logs.php');
require_once('C:\xampp\htdocs\examen2\app\db\Data.php');
require_once('C:\xampp\htdocs\examen2\app\db\guardar.php');


// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->group('/guardar', function (RouteCollectorProxy $group){
  $group->get('/armamento', \Guardar::class . ':descargarArmamentoCSV');
  $group->get('/logs', \Guardar::class . ':descargarLogsCSV');
  $group->get('/ventas_en_pdf', \Guardar::class . ':guardarEnPDF');
});

$app->group('/borrar', function (RouteCollectorProxy $group){
  $group->delete('/armamento', \Armamento::class . ':eliminarUno');
});

$app->group('/modificar', function (RouteCollectorProxy $group){
  $group->put('/armamento', \Armamento::class . ':modificarArmamento');
});

$app->group('/listar', function (RouteCollectorProxy $group){
  $group->get('/todo_el_armamento', \Armamento::class . ':listarArmamento');
  $group->get('/arma_por_pais', \Armamento::class . ':listarArmamentoPorPais');
  $group->get('/buscar_arma_por_id', \Armamento ::class . ':traerArmamentoPorID');
  $group->get('/compradores_por_arma', \Venta_armamento::class . ':buscarCompradoresPorArticulos');
  $group->get('/compra_fecha', \Venta_armamento::class . ':traerComprasEEUUenNoviembre');
});

$app->group('/alta', function (RouteCollectorProxy $group){
    
  $group->post('/armamento', \Armamento::class . ':cargarUno');
  $group->post('/usuario', \Usuario::class . ':cargarUno');
  $group->post('/venta', \Venta_armamento::class . ':cargarUno');
});

$app->group('/acceso', function (RouteCollectorProxy $group){
    
    $group->post('/login', \Usuario::class . ':login');
  });


$app->run();
?>