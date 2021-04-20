<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once '../Modelo/Usuario.php';
include_once '../Auxiliar/gestionDatos.php';
session_start();
$_SESSION['allOnline'] = gestionDatos::getAllOnline();
//-----------------VISTAS
if (isset($_REQUEST['vistaLogin'])) {
    unset($_SESSION['usuarioActual']);
    header('Location: ../Vistas/login.php');
}
if (isset($_REQUEST['vistaRegistro'])) {
    unset($_SESSION['usuarioActual']);
    header('Location: ../Vistas/register.php');
}
if (isset($_REQUEST['vistaOlvidada'])) {
    unset($_SESSION['usuarioActual']);
    header('Location: ../Vistas/olvidada.php');
}

//------------------Funciones

if (isset($_REQUEST['iniciarBD'])) {
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = '6LdU7-QZAAAAAChZ7pnDbgTL--nSmYG6aJxTMj2f';
    $recaptcha_response = $_POST['recaptcha_response'];
    $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);
    if ($recaptcha->score >= 0.8) {
        //RECOGIDA DE DATOS.
        $email = $_REQUEST['emailLogin'];
        $password = $_REQUEST['passwordLogin'];

        //CONSULTA A BD.
        $usuario = gestionDatos::getUser($email, $password);
        if (isset($usuario)) {
            if (gestionDatos::isActive($email)) {
                gestionDatos::setOnline($email);
                $usuario->set_isOnline(1);
                $firstTime = gestionDatos::isFirstTime($usuario->get_idUser());
                if (!$firstTime) {
                    header('Location: ../Vistas/preferencias.php');
                } else {
                    $user = gestionDatos::getPreferencias($usuario);
                    $_SESSION['usuarioActual'] = $user;
                    $_SESSION['Preferencias'] = $user->get_preferencias();
                    $_SESSION['rolActual'] = $user->get_rol();
                    if ($user->get_rol() == 2) {
                        header('Location: ../Vistas/inicio.php');
                    } else if ($user->get_rol() == 1) {
                        header('Location: ../Vistas/inicioAdmin.php');
                    }
                }
                /*
        $friendOnline = gestionDatos::getFriendsOnline($usuario->get_idUser());
       */
            } else {
                $mensaje = "Usuario desactivado";
                $_SESSION['mensaje'] = $mensaje;
                header('Location: ../Vistas/login.php');
            }
        } else {
            $mensaje = "Credenciales erroneas.";
            $_SESSION['mensaje'] = $mensaje;
            header('Location: ../Vistas/login.php');
        }
    } else {
        $mensaje = 'Error captcha no superado.';
        $_SESSION['mensaje'] = $mensaje;
        header('Location: ../Vistas/login.php');
    }
}
if (isset($_REQUEST['registroBD'])) {
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = '6LetBuUZAAAAACJbleMS9s-GX9s5jhcdRL4gtPP8';
    $recaptcha_response = $_POST['recaptcha_response'];
    $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);
    if ($recaptcha->score >= 0.5) {
        //RECOGIDA DE DATOS.
        $email = $_REQUEST['email'];
        $password = $_REQUEST['password'];
        $nombre = $_REQUEST['nombre'];
        $edad = $_REQUEST['edad'];
        $dni = $_REQUEST['dni'];
        $telefono = $_REQUEST['telefono'];
        //GUARDAMOS DATOS
        $user = new Usuario(0, $email, $dni, 0, $nombre, $edad, $telefono, 0, 0);
        //COMPROBACIONES PREVIAS 

        if (gestionDatos::isExistDni($dni)) {
            $mensaje = "El dni introduccido ya esta en uso en la plataforma.";
            $_SESSION['mensaje'] = $mensaje;
            $user->set_dni("");
            $_SESSION['userDatos'] = $user;
            header('Location: ../Vistas/register.php');
        } else
    if (gestionDatos::isExistEmail($email)) {
            $mensaje = "El e-mail introduccido ya esta en uso en la plataforma.";
            $_SESSION['mensaje'] = $mensaje;
            $user->set_email("");
            $_SESSION['userDatos'] = $user;
            header('Location: ../Vistas/register.php');
        } else
            //INSERTAMOS EL USUARIO
            if (gestionDatos::insertUser($user, $password)) {
                $id = gestionDatos::getMaxId($email);
                gestionDatos::insertRol($id);
                $mensaje = "Usuario creado correctamente, actualmente su cuenta esta desactivada hasta ser revisada por un administrador";
                $_SESSION['mensaje'] = $mensaje;
                header('Location: ../index.php');
            } else {
                $mensaje = "fallo al insertar el usuario en la BD";
                $_SESSION['mensaje'] = $mensaje;
                $_SESSION['userDatos'] = $user;
                header('Location: ../Vistas/register.php');
            }
    } else {
        $mensaje = 'Error captcha no superado.';
        $_SESSION['mensaje'] = $mensaje;
        header('Location: ../Vistas/register.php');
    }
}
if (isset($_REQUEST['close'])) {
    unset($_SESSION['usuarioActual']);
    unset($_SESSION['Preferencias']);
    unset($_SESSION['rolActual']);
    $mensaje = 'Sesion cerrada .';
    $_SESSION['mensaje'] = $mensaje;
    header('Location: ../index.php');
}