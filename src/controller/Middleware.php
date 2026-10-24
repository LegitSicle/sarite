<?php
namespace sarite\controller;
use sarite\web\Request;
use sarite\web\Response;

interface Middleware {
    public function handle(Request $request, callable $next): Response;
}