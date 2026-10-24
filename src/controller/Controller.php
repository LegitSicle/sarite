<?php
namespace sarite\controller;
use sarite\web\Request;
use sarite\web\Response;

abstract class Controller {
    public function __construct(
        protected array $injectedData = []
    ) {}

    abstract public function index(Request $request): Response;
}