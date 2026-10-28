<?php
namespace sarite\controller;

use sarite\web\Request;
use sarite\web\Response;
use Override;

class RedirectController extends Controller {
    #[Override]
    public function index(Request $request): Response {
        return Response::redirectOnly($this->injectedData["destination"] ?? "");
    }
}