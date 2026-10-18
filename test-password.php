<?php

$plainPassword = "12345678";

$storedHash = '$2y$10$nyIgIqyztr/XzCFpIdiHJebD2Leso.YHxWrub2/W2l8';

var_dump(password_verify($plainPassword, $storedHash));