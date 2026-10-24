<?php
namespace sarite\data;

abstract class UserModel extends DatabaseModel {
    abstract public function displayName(): string;
}