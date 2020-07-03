<?php

// define ("path","require('path')");
// define ("fs","require('fs')");
// define ("express","require('express')");
// $multer = require('multer');
// define ("multer","require('multer')");
// $bodyParser = require('body-parser');
// $app = express();
// $router = express::Router();

$DIR = '../uploads';

$storage = array("destination" => function ($req, $file, $cb) {
  $cb(null, $DIR);
  }
  , "filename" => function ($req, $file, $cb) {
  // $cb(null, $file.fieldname + '-' + time() + '.' + path.extname($file->originalname));
  $file.fieldname();
  }
  );
$upload = array("storage" => $storage);

// $app->use($bodyParser->json());
// $app->use($bodyParser->urlencoded(array("extended" => true)));
// $app->get('/getCustomer', function() use ($app) {

// $app->use(function ($req, $res, $next) {
//   $res->setHeader('Access-Control-Allow-Origin', 'http://localhost');
//   $res->setHeader('Access-Control-Allow-Methods', 'POST');
//   $res->setHeader('Access-Control-Allow-Headers', 'X-Requested-With,content-type');
//   $res->setHeader('Access-Control-Allow-Credentials', true);
//   $next();
//   }
//   );

$app->get('/api', function ($req, $res) {
$res->end('file catcher example');
var_dump("$res= ",$res);
}
);

// $app->post('/api/upload', $upload->single('photo'), function ($req, $res) {
$app->post('/api/upload', function ($req, $res) {
  if (!$req->file) {
  $console->log("No file received");
  return $res->send(array("success" => false));
  } else {$console->log('file received');
  return $res->send(array("success" => true));
  }}
  );

// $PORT = $process->env->PORT || 3000;
// $app->listen($PORT, function () use (&$PORT) {
// $console->log('Slim is running on port ' + $PORT);
// }
// );
